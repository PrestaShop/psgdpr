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

class AdminAjaxPsgdprController extends ModuleAdminController
{
    /**
     * This function allow to delete users
     *
     * @param id $id_customer id of the user to delete
     * @return array $log log of the process
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
     * @param string input value of the search
     * @return array customers list
     */
    public function ajaxProcessSearchCustomers()
    {
        $searches = explode(' ', Tools::getValue('customer_search'));
        $customers = array();
        $searches = array_unique($searches);
        foreach ($searches as $search) {
            if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                foreach ($results as $result) {
                    if ($result['active']) {
                        $result['fullname_and_email'] = $result['firstname'].' '.$result['lastname'].' - '.$result['email'];
                        $customers[$result['id_customer']] = $result;
                    }
                }
            }
        }
        if (count($customers) && !Tools::getValue('sf2')) {
            $customerList = array();
            foreach ($customers as $customer) {
                array_push($customerList, array(
                    'id_customer' => $customer['id_customer'],
                    'firstname' => $customer['firstname'],
                    'lastname' => $customer['lastname'],
                    'email' => $customer['email'],
                    'birthday' => $customer['birthday'],
                    'nb_orders' => Order::getCustomerNbOrders($customer['id_customer']),
                    'customerData' => array(),
                ));
            }
            $to_return = array(
                'customers' => $customerList,
                'found' => true
            );
        } else {
            $to_return = Tools::getValue('sf2') ? array() : array('found' => false);
        }
        die(json_encode($to_return));
    }

    /**
     * Return all collected for the giver customer
     *
     * @param int $id_customer
     * @return array customers data
     */
    public function ajaxProcessGetCustomerData()
    {
        $delete = Tools::getValue('delete');
        $value = Tools::getValue('value');

        $return = $this->module->getCustomerData($delete, $value);

        die(json_encode($return['data']));
    }

    /**
     * check if there are orders associated to the customer
     *
     * @param id $id_customer
     * redirect to the invoices controller
     */
    public function ajaxProcessDownloadInvoicesByCustomer()
    {
        $id_customer = Tools::getValue('id_customer');

        $order_invoice_list = Db::getInstance()->executeS('SELECT oi.*
            FROM `'._DB_PREFIX_.'order_invoice` oi
            LEFT JOIN `'._DB_PREFIX_.'orders` o ON (o.`id_order` = oi.`id_order`)
            WHERE o.id_customer ='.(int)$id_customer.'
            AND oi.number > 0');

        die(json_encode(count($order_invoice_list)));
    }
}
