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

        $id_customer = (int) Tools::getValue('id_customer');
        $customer_token = Tools::getValue('customer_token');

        $id_module = (int) Tools::getValue('id_module');

        $id_guest = (int) Tools::getValue('id_guest');
        $guest_token = Tools::getValue('guest_token');

        $customer = Context::getContext()->customer;

        if ($customer->isLogged() === true) {
            $token = sha1($customer->secure_key);
            if (!isset($customer_token) || $customer_token == $token) {
                GDPRLog::addLog($id_customer, 'consent', $id_module);
            }
        } else {
            $token = sha1('psgdpr' . Context::getContext()->cart->id_guest . $_SERVER['REMOTE_ADDR'] . date('Y-m-d'));
            if (!isset($guest_token) || $guest_token == $token) {
                GDPRLog::addLog($id_customer, 'consent', $id_module, $id_guest);
            }
        }

        $this->ajaxDie();
    }
}
