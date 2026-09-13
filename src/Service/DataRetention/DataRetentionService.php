<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Psgdpr\Service\DataRetention;

use Configuration;
use Context;
use Language;
use Mail;
use PrestaShop\Module\Psgdpr\Repository\CustomerRepository;
use PrestaShop\Module\Psgdpr\Service\CustomerService;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use Psgdpr;
use Validate;

/**
 * Enforces the GDPR storage-limitation principle (art. 5.1.e) by anonymizing
 * customers inactive beyond a merchant-configured retention period.
 *
 * Disabled by default. The merchant/DPO sets the period; nothing is ever
 * deleted automatically unless explicitly enabled.
 *
 * Legal model (CNIL): active base -> intermediate archive -> deletion.
 * We anonymize the PERSON (PII) while the underlying orders/invoices are
 * preserved by the existing CustomerService anonymization primitive, so
 * accounting/tax retention duties (Code de commerce art. L123-22: 10 years,
 * LPF art. L102 B: 6 years) are respected.
 *
 * "Inactive" = the most recent of (last connection, last valid order) is older
 * than the configured period.
 */
class DataRetentionService
{
    const CONFIG_ENABLED = 'PSGDPR_RETENTION_ENABLED';
    const CONFIG_INACTIVITY_DAYS = 'PSGDPR_RETENTION_INACTIVITY_DAYS';
    const CONFIG_WARN_DAYS = 'PSGDPR_RETENTION_WARN_DAYS';

    /**
     * Safety cap: max customers processed per cron run, to stay safe on large bases.
     */
    const BATCH_LIMIT = 200;

    /**
     * @var Psgdpr
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * @param Psgdpr $module
     * @param Context $context
     * @param CustomerRepository $customerRepository
     * @param CustomerService $customerService
     * @param LoggerService $loggerService
     */
    public function __construct(
        Psgdpr $module,
        Context $context,
        CustomerRepository $customerRepository,
        CustomerService $customerService,
        LoggerService $loggerService
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->customerRepository = $customerRepository;
        $this->customerService = $customerService;
        $this->loggerService = $loggerService;
    }

    /**
     * Cron entry point. Runs the two-phase retention job:
     *  1. warn customers about to be anonymized (grace period),
     *  2. anonymize those warned long enough ago and still inactive.
     *
     * @return array{warned: int, anonymized: int} counters for logging/monitoring
     */
    public function processRetention(bool $dryRun = false): array
    {
        $result = ['warned' => 0, 'anonymized' => 0, 'dry_run' => $dryRun];

        // A live run respects the enable switch; a dry run always previews,
        // sending nothing and deleting nothing.
        if (!$dryRun && (bool) Configuration::get(self::CONFIG_ENABLED) === false) {
            return $result;
        }

        $inactivityDays = (int) Configuration::get(self::CONFIG_INACTIVITY_DAYS);
        $warnDays = (int) Configuration::get(self::CONFIG_WARN_DAYS);

        // Misconfiguration guard: never run with a zero/negative period.
        if ($inactivityDays <= 0) {
            return $result;
        }

        $result['anonymized'] = $this->anonymizeWarnedCustomers($inactivityDays, $warnDays, $dryRun);
        $result['warned'] = $this->warnInactiveCustomers($inactivityDays, $warnDays, $dryRun);

        return $result;
    }

    /**
     * Phase 1 — send a warning email (with a reconnection link) to inactive
     * customers who have not already been warned within the current grace window.
     * Logging back in updates the customer's last activity and removes them from
     * the next run's selection.
     *
     * @param int $inactivityDays
     * @param int $warnDays
     * @param bool $dryRun when true, count candidates only — send no email, write no log
     *
     * @return int number of customers warned (or that would be warned, in dry run)
     */
    private function warnInactiveCustomers(int $inactivityDays, int $warnDays, bool $dryRun): int
    {
        $warned = 0;
        $candidates = $this->customerRepository->findInactiveCustomers($inactivityDays, self::BATCH_LIMIT);

        foreach ($candidates as $customer) {
            $customerId = (int) $customer['id_customer'];
            $lastWarning = $this->customerRepository->findLastRetentionWarningDate($customerId, (int) $this->module->id);

            // Already warned and still inside the grace window: leave it for phase 2.
            if ($lastWarning !== null && strtotime($lastWarning) > strtotime('-' . $warnDays . ' days')) {
                continue;
            }

            if ($dryRun) {
                ++$warned;
                continue;
            }

            if ($this->sendWarningEmail($customer) === false) {
                continue;
            }

            $this->loggerService->createLog(
                $customerId,
                LoggerService::REQUEST_TYPE_RETENTION_WARNING,
                (int) $this->module->id,
                0,
                (string) $customer['email']
            );
            ++$warned;
        }

        return $warned;
    }

    /**
     * Phase 2 — anonymize customers who were warned at least $warnDays ago and
     * are still inactive. Reuses the module's existing anonymization primitive,
     * which preserves orders/invoices for legal retention.
     *
     * @param int $inactivityDays
     * @param int $warnDays
     * @param bool $dryRun when true, count candidates only — anonymize nothing, write no log
     *
     * @return int number of customers anonymized (or that would be, in dry run)
     */
    private function anonymizeWarnedCustomers(int $inactivityDays, int $warnDays, bool $dryRun): int
    {
        $anonymized = 0;
        $candidates = $this->customerRepository->findInactiveCustomers($inactivityDays, self::BATCH_LIMIT);

        foreach ($candidates as $customer) {
            $customerId = (int) $customer['id_customer'];
            $lastWarning = $this->customerRepository->findLastRetentionWarningDate($customerId, (int) $this->module->id);

            // Only anonymize once a warning has been sent and the grace window has elapsed.
            if ($lastWarning === null || strtotime($lastWarning) > strtotime('-' . $warnDays . ' days')) {
                continue;
            }

            if ($dryRun) {
                ++$anonymized;
                continue;
            }

            $this->customerService->deleteCustomerDataFromPrestashop(new CustomerId($customerId));
            $this->customerService->deleteCustomerDataFromModules((string) $customer['email']);

            $this->loggerService->createLog(
                $customerId,
                LoggerService::REQUEST_TYPE_SCHEDULED_ANONYMIZATION,
                (int) $this->module->id,
                0,
                'retention:' . $inactivityDays . 'd'
            );
            ++$anonymized;
        }

        return $anonymized;
    }

    /**
     * Send the pre-anonymization warning email.
     *
     * @param array $customer associative row from findInactiveCustomers()
     *
     * @return bool whether the mail was accepted for sending
     *
     * @todo Add the `retention_warning` mail templates under mails/<iso>/
     *       (html + txt) before enabling in production.
     */
    private function sendWarningEmail(array $customer): bool
    {
        // Send in the customer's own language; fall back to the context language.
        $languageId = isset($customer['id_lang']) ? (int) $customer['id_lang'] : 0;
        if ($languageId <= 0) {
            $languageId = (int) $this->context->language->id;
        }

        $reconnectUrl = $this->context->link->getPageLink('my-account', true);

        // Localize the subject in the customer's language (body is localized via
        // the per-iso mail template directory).
        $locale = null;
        $lang = new Language($languageId);
        if (Validate::isLoadedObject($lang)) {
            $locale = $lang->locale;
        }

        return (bool) Mail::Send(
            $languageId,
            'retention_warning',
            $this->module->getTranslator()->trans('Your account is about to be anonymized', [], 'Modules.Psgdpr.Email', $locale),
            [
                '{firstname}' => (string) $customer['firstname'],
                '{lastname}' => (string) $customer['lastname'],
                '{reconnect_url}' => $reconnectUrl,
            ],
            (string) $customer['email'],
            trim($customer['firstname'] . ' ' . $customer['lastname']),
            null,
            null,
            null,
            null,
            _PS_MODULE_DIR_ . $this->module->name . '/mails/'
        );
    }
}
