<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class AdminAjaxPsgdprController extends ModuleAdminController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * This function allow to delete users
     */
    public function ajaxProcessDeleteCustomer()
    {
        $delete = Tools::getValue('delete');
        $value = Tools::getValue('value');
        $this->module->deleteCustomer($delete, $value);
    }

    /**
     * Return all customers matches for the search
     *
     * @throws PrestaShopDatabaseException
     */
    public function ajaxProcessSearchCustomers()
    {
        $searches = explode(' ', Tools::getValue('customer_search'));
        $customers = [];
        $searches = array_unique($searches);
        foreach ($searches as $search) {
            if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                foreach ($results as $result) {
                    if ($result['active']) {
                        $result['fullname_and_email'] = $result['firstname'] . ' ' . $result['lastname'] . ' - ' . $result['email'];
                        $customers[$result['id_customer']] = $result;
                    }
                }
            }
        }
        if (!empty($customers) && !Tools::getValue('sf2')) {
            $customerList = [];
            foreach ($customers as $customer) {
                array_push($customerList, [
                    'id_customer' => $customer['id_customer'],
                    'firstname' => $customer['firstname'],
                    'lastname' => $customer['lastname'],
                    'email' => $customer['email'],
                    'birthday' => $customer['birthday'],
                    'nb_orders' => Order::getCustomerNbOrders($customer['id_customer']),
                    'customerData' => [],
                ]);
            }
            $to_return = [
                'customers' => $customerList,
                'found' => true,
            ];
        } else {
            $to_return = Tools::getValue('sf2') ? [] : ['found' => false];
        }
        $this->ajaxDie(json_encode($to_return));
    }

    /**
     * Return all collected for the giver customer
     *
     * @throws PrestaShopException
     */
    public function ajaxProcessGetCustomerData()
    {
        $delete = Tools::getValue('delete');
        $value = Tools::getValue('value');

        $return = $this->module->getCustomerData($delete, $value);

        $this->ajaxDie(json_encode($return['data']));
    }

    /**
     * check if there are orders associated to the customer
     *
     * @throws PrestaShopDatabaseException
     */
    public function ajaxProcessDownloadInvoicesByCustomer()
    {
        $id_customer = Tools::getValue('id_customer');

        $order_invoice_list = (int) Db::getInstance()->getValue('SELECT COUNT(1)
            FROM `' . _DB_PREFIX_ . 'order_invoice` oi
            LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE o.id_customer =' . (int) $id_customer . '
            AND oi.number > 0');

        $this->ajaxDie(json_encode($order_invoice_list));
    }
}
