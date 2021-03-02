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
class psgdprExportDataToPdfModuleFrontController extends ModuleFrontController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * @throws PrestaShopDatabaseException
     */
    public function initContent()
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            exit('bad token');
        }

        GDPRLog::addLog($customer->id, 'exportPdf', 0);
        $this->exportDataToPdf($customer->id);
        exit();
    }

    /**
     * @param int $id_customer
     *
     * @throws PrestaShopException
     */
    public function exportDataToPdf($id_customer)
    {
        $pdf = new PDF($this->module->getCustomerData('customer', $id_customer), 'PSGDPRModule', Context::getContext()->smarty);
        $pdf->render(true);
    }
}
