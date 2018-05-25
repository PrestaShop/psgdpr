<?php
/**
* 2007-2016 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2015 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class psgdprFrontAjaxGdprModuleFrontController extends FrontController
{
    /**
     * Store if the client consented or not to GDPR on a specific module for statistic purpose only
     */
    public function displayAjaxAddLog()
    {
        $id_customer = (int)Tools::getValue('id_customer');
        $customer_token = Tools::getValue('customer_token');

        $id_module = (int)Tools::getValue('id_module');

        $id_guest = (int)Tools::getValue('id_guest');
        $guest_token = Tools::getValue('guest_token');

        $customer = Context::getContext()->customer;

        if ($customer->isLogged() === true) {
            $token = sha1($customer->secure_key);
            if (!isset($customer_token) || $customer_token == $token) {
                GDPRLog::addLog($id_customer, 'consent', $id_module);
            }
        } else {
            $token = sha1('psgdpr'.Context::getContext()->cart->id_guest.$_SERVER['REMOTE_ADDR'].date('Y-m-d'));
            if (!isset($guest_token) || $guest_token == $token) {
                GDPRLog::addLog($id_customer, 'consent', $id_module, $id_guest);
            }
        }
    }
}
