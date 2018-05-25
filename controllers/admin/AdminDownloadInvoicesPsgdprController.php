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

class AdminDownloadInvoicesPsgdprController extends ModuleAdminController
{
    public function postProcess()
    {
        $id_customer = (int)Tools::getValue('id_customer');
        if (isset($id_customer)) {
            $this->downloadInvoices($id_customer);
        }
    }

    /**
     * download all invoices from specific customer into one .pdf file
     *
     * @param  int $id_customer
     */
    public function downloadInvoices($id_customer)
    {
        $order_invoice_collection = $this->getCustomerInvoiceList($id_customer);
        if (!count($order_invoice_collection)) {
            return;
        }
        $this->generatePDF($order_invoice_collection, PDF::TEMPLATE_INVOICE);
    }

    /**
     * get all the invoices from specific customer into a list
     *
     * @param int $id_customer
     * @return array collection of orders
     */
    public function getCustomerInvoiceList($id_customer)
    {
        $order_invoice_list = Db::getInstance()->executeS('SELECT oi.*
            FROM `'._DB_PREFIX_.'order_invoice` oi
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE o.id_customer ='.(int)$id_customer.'
            AND oi.number > 0');
        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }

    /**
     * generate a .pdf file
     */
    public function generatePDF($object, $template)
    {
        $pdf = new PDF($object, $template, Context::getContext()->smarty);
        $pdf->render(true);
    }
}
