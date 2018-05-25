<?php
/**
* 2007-2018 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*/

class psgdprExportDataToPdfModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            die('bad token');
        }

        GDPRLog::addLog($customer->id, 'exportPdf', 0);
        $this->exportDataToPdf($customer->id);
        exit();
    }

    public function exportDataToPdf($id_customer)
    {
        $pdf = new PDF($this->module->getCustomerData('customer', $id_customer), 'PSGDPRModule', Context::getContext()->smarty);
        $pdf->render(true);
    }
}
