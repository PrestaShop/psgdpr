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
        if (Tools::getValue('action') !== 'AddLog') {
            $this->ajaxDie();
        }

        $customer_token = (string) Tools::getValue('customer_token');
        $guest_token = (string) Tools::getValue('guest_token');

        $id_module = (int) Tools::getValue('id_module');

        $context = Context::getContext();
        $customer = $context->customer;

        // The log is written for the customer or guest of the session, never for ids taken from the request
        if ($customer->isLogged() === true) {
            $token = sha1($customer->secure_key);
            if (hash_equals($token, $customer_token)) {
                GDPRLog::addLog((int) $customer->id, 'consent', $id_module);
            }
        } else {
            $id_guest = (int) $context->cart->id_guest;
            $token = sha1('psgdpr' . $id_guest . $_SERVER['REMOTE_ADDR'] . date('Y-m-d'));
            if (hash_equals($token, $guest_token)) {
                GDPRLog::addLog(0, 'consent', $id_module, $id_guest);
            }
        }

        $this->ajaxDie();
    }
}
