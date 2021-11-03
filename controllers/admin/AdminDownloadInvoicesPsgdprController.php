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
class AdminDownloadInvoicesPsgdprController extends ModuleAdminController
{
    /**
     * Download invoice
     */
    public function postProcess()
    {
        $id_customer = (int) Tools::getValue('id_customer');

        if (!empty($id_customer)) {
            $this->downloadInvoices($id_customer);
        }
    }

    /**
     * download all invoices from specific customer into one .pdf file
     *
     * @param int $id_customer
     */
    public function downloadInvoices($id_customer)
    {
        $order_invoice_collection = $this->getCustomerInvoiceList($id_customer);

        if (empty($order_invoice_collection)) {
            return;
        }

        $this->generatePDF($order_invoice_collection, PDF::TEMPLATE_INVOICE);
    }

    /**
     * get all the invoices from specific customer into a list
     *
     * @param int $id_customer
     *
     * @return array|ObjectModel[]
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getCustomerInvoiceList($id_customer)
    {
        $order_invoice_list = Db::getInstance()->executeS('SELECT oi.*
            FROM `' . _DB_PREFIX_ . 'order_invoice` oi
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE o.id_customer =' . (int) $id_customer . '
            AND oi.number > 0');

        if (empty($order_invoice_list)) {
            return [];
        }

        return ObjectModel::hydrateCollection('OrderInvoice', $order_invoice_list);
    }

    /**
     * generate a .pdf file
     *
     * @param ObjectModel[] $object
     * @param string $template
     *
     * @throws PrestaShopException
     */
    public function generatePDF($object, $template)
    {
        $pdf = new PDF($object, $template, Context::getContext()->smarty);
        $pdf->render(true);
    }
}
