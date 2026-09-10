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

use PrestaShop\Module\Psgdpr\Service\FrontResponder\FrontResponderFactory;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

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

        if ($this->customerIsAuthenticated() === false) {
            Tools::redirect('connexion?back=my-account');
        }

        $exportType = Tools::getValue('type');

        /** @var FrontResponderFactory $frontResponderFactory */
        $frontResponderFactory = $this->module->get('PrestaShop\Module\Psgdpr\Service\FrontResponder\FrontResponderFactory');

        $customerId = new CustomerId(Context::getContext()->customer->id);

        $frontResponderStrategy = $frontResponderFactory->getStrategyByType($exportType);
        $frontResponderStrategy->export($customerId);
    }

    /**
     * Check if customer is authenticated
     *
     * @return bool
     */
    private function customerIsAuthenticated(): bool
    {
        $customer = Context::getContext()->customer;

        // WHY: the token is derived from the customer's secure key, which only exists once they are
        // logged in. Hashing before the login check meant every visitor who was going to be rejected
        // anyway went through sha1(null) first, which is deprecated since PHP 8.1 and fills the log
        // on any shop where this controller is reachable. Rejecting first also avoids hashing for a
        // request that cannot be authenticated.
        if ($customer->isLogged() === false) {
            return false;
        }

        // Compared with hash_equals so the check does not leak the token through its timing, and to
        // match the strict comparison the other front controller already uses for the same token.
        return hash_equals(sha1((string) $customer->secure_key), (string) Tools::getValue('token'));
    }
}
