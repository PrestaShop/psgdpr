<?php

use PrestaShop\Module\Psgdpr\Service\LoggerService;

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
class psgdprFrontAjaxGdprModuleFrontController extends FrontController
{
    /**
     * Store if the client consented or not to GDPR on a specific module for statistic purpose only
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function display()
    {
        /** @var LoggerService $loggerService */
        $loggerService = $this->get('PrestaShop\Module\Psgdpr\Service\LoggerService');

        if (Tools::getValue('action') !== 'AddLog') {
            $this->ajaxRender();

            return false;
        }

        $customerId = (int) Tools::getValue('id_customer');
        $customerToken = (string) Tools::getValue('customer_token');

        $moduleId = (int) Tools::getValue('id_module');

        $guestId = (int) Tools::getValue('id_guest');
        $guestToken = (string) Tools::getValue('guest_token');

        $customer = Context::getContext()->customer;
        $customerFullName = $customer->firstname . ' ' . $customer->lastname;

        if ($customer->isLogged() === true) {
            $token = sha1($customer->secure_key);
            if ($customerToken === $token) {
                $loggerService->createLog($customerId, LoggerService::REQUEST_TYPE_CONSENT_COLLECTING, $moduleId, 0, $customerFullName);
            }
        } else {
            $token = sha1('psgdpr' . Context::getContext()->cart->id_guest . $_SERVER['REMOTE_ADDR'] . date('Y-m-d'));
            if ($guestToken === $token) {
                $loggerService->createLog($customerId, LoggerService::REQUEST_TYPE_CONSENT_COLLECTING, $moduleId, $guestId);
            }
        }

        $this->ajaxRender();

        return true;
    }
}
