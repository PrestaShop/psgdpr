<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\Psgdpr\Exception\Customer\ExportException;
use PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataFactory;
use PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataService;
use PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataToCsv;
use PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataToPdf;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use Symfony\Component\HttpFoundation\Response;

class psgdprExportCustomerDataModuleFrontController extends ModuleFrontController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * Init content
     */
    public function initContent()
    {
        parent::initContent();

        $exportType = Tools::getValue('type');

        switch ($exportType) {
            case ExportCustomerDataToCsv::TYPE:
                $this->exportToCsv();
                break;
            case ExportCustomerDataToPdf::TYPE:
                $this->exportToPdf();
                break;
            default:
                $this->exportToCsv();
                break;
        }
    }

    /**
     * Export customer data to CSV
     *
     * @return void
     *
     * @throws ExportException
     */
    private function exportToCsv(): void
    {
        /** @var ExportCustomerDataService $exportCustomerDataService */
        $exportCustomerDataService = $this->module->get('PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataService');

        /** @var ExportCustomerDataFactory $exportFactory */
        $exportFactory = $this->module->get('PrestaShop\Module\Psgdpr\Service\Export\ExportFactory');

        if ($this->customerIsAuthenticated() === false) {
            Tools::redirect('connexion?back=my-account');
        }

        $customerId = new CustomerId(Context::getContext()->customer->id);

        try {
            $exportStrategy = $exportFactory->getStrategyByType(ExportCustomerDataToPdf::TYPE);

            $csvFile = $exportCustomerDataService->exportCustomerData($customerId, $exportStrategy);

            $csvName = 'personal-data-' . '_' . date('Y-m-d_His') . '.csv';

            $response = new Response($csvFile);
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $csvName . '";');
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Transfer-Encoding', 'binary');

            $response->send();

            exit();
        } catch (ExportException $e) {
            throw new ExportException('A problem occurred while exporting customer to csv. please try again');
        }
    }

    /**
     * Export customer data to pdf
     *
     * @return void
     *
     * @throws ExportException
     */
    public function exportToPdf(): void
    {
        /** @var ExportCustomerDataService $exportCustomerDataService */
        $exportCustomerDataService = $this->module->get('PrestaShop\Module\Psgdpr\Service\Export\ExportCustomerDataService');

        /** @var ExportCustomerDataFactory $exportFactory */
        $exportFactory = $this->module->get('PrestaShop\Module\Psgdpr\Service\Export\ExportFactory');

        if ($this->customerIsAuthenticated() === false) {
            Tools::redirect('connexion?back=my-account');
        }

        $customerId = new CustomerId(Context::getContext()->customer->id);

        try {
            $exportStrategy = $exportFactory->getStrategyByType(ExportCustomerDataToCsv::TYPE);

            $exportCustomerDataService->exportCustomerData($customerId, $exportStrategy);
            exit();
        } catch (ExportException $e) {
            throw new ExportException('A problem occurred while exporting customer to pdf. please try again');
        }
    }

    /**
     * Check if customer is authenticated
     *
     * @return bool
     */
    private function customerIsAuthenticated(): bool
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            return false;
        }

        return true;
    }
}
