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
class AdminAjaxPsgdprController extends ModuleAdminController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * This function allow to delete users
     *
     * @throws PrestaShopException
     */
    public function ajaxProcessDeleteCustomer()
    {
        $delete = Tools::getValue('delete');
        $value = Tools::getValue('value');

        $response = ['success' => $this->module->deleteCustomer($delete, $value)];
        if (!$response['success'] && $delete === 'email' && $this->module->hasCustomerAccount($value)) {
            $response['error'] = 'customer_exists';
        }

        $this->renderJson(json_encode($response));
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
        // disabled customers are listed too, their data has to be erasable: only the anonymous account of the module is left out
        $anonymousCustomerId = (int) Configuration::get('PSGDPR_ANONYMOUS_CUSTOMER');
        foreach ($searches as $search) {
            if (!empty($search) && $results = Customer::searchByName($search, 50)) {
                foreach ($results as $result) {
                    if ((int) $result['id_customer'] !== $anonymousCustomerId) {
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
        $this->renderJson(json_encode($to_return));
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

        $this->renderJson(json_encode($return['data']));
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

        $this->renderJson(json_encode($order_invoice_list));
    }

    /**
     * Render the JSON answer and stop, like ajaxDie() did: ajaxDie() no longer exists on PrestaShop 9,
     * ajaxRender() does not exist before 1.7.5. The calling action is passed on so the
     * actionAjaxDie<Controller><Action>Before hook keeps its name.
     *
     * @param string $json
     *
     * @throws PrestaShopException
     */
    private function renderJson($json)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $method = $backtrace[1]['function'];

        if (method_exists($this, 'ajaxRender')) {
            $this->ajaxRender($json, null, $method);
            exit;
        }

        $this->ajaxDie($json, null, $method);
    }
}
