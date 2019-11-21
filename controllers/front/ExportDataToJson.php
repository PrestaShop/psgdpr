<?php
/**
 * 2007-2019 PrestaShop
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA
 * @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
 * International Registered Trademark & Property of PrestaShop SA
 */

class psgdprExportDataToJsonModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            die('bad token');
        }

        GDPRLog::addLog($customer->id, 'exportJson', 0);
        $this->exportDataToJson($customer->id);
    }

    public function exportDataToJson($id_customer)
    {
        $data = $this->module->getCustomerData('customer', $id_customer);

        $customerInfo = $data['data']['prestashopData']['customerInfo'];
        $addresses = $data['data']['prestashopData']['addresses'];
        $orders = $data['data']['prestashopData']['orders'];
        $carts = $data['data']['prestashopData']['carts'];
        $messages = $data['data']['prestashopData']['messages'];
        $connections = $data['data']['prestashopData']['connections'];
        $modules = $data['data']['modulesData'];

        $json = [];

        // GENERAL INFO
        $json['general_info'] = [
            'gender' => $customerInfo['gender'],
            'firstname' => $customerInfo['firstname'],
            'lastname' => $customerInfo['firstname'],
            'birth_date' => $customerInfo['birthday'],
            'age' => $customerInfo['age'],
            'email' => $customerInfo['email'],
            'language' => $customerInfo['language'],
            'creation_date' => $customerInfo['date_add'],
            'last_visit_date' => $customerInfo['last_visit'],
            'siret' => $customerInfo['siret'],
            'ape' => $customerInfo['ape'],
            'company' => $customerInfo['company'],
            'website' => $customerInfo['website'],
        ];

        // ADDRESSES
        $json['addresses'] = [];

        foreach ($addresses as $address) {
            $json['addresses'][] = [
                'alias' => $address['alias'],
                'company' => $address['company'],
                'firstname' => $address['firstname'],
                'lastname' => $address['lastname'],
                'address1' => $address['address1'],
                'address2' => $address['address2'],
                'phone' => $address['phone'],
                'phone_mobile' => $address['phone_mobile'],
                'country' => $address['country'],
                'date_add' => $address['date_add'],
            ];
        }

        // ORDERS
        $json['orders'] = [];

        foreach ($orders as $order) {
            $line = [
                'reference' => $order['reference'],
                'payment_method' => $order['payment'],
                'order_state' => $order['order_state'],
                'total_paid_tax_incl' => $order['total_paid_tax_incl'],
                'date' => $order['date_add'],
                'products' => [],
            ];

            $products = $order['products'];

            foreach ($products as $product) {
                $line['products'][] = [
                    'reference' => $product['product_reference'],
                    'name' => $product['product_name'],
                    'quantity' => (int)$product['product_quantity'],
                ];
            }

            $json['orders'][] = $line;
        }

        // CARTS
        $json['carts'] = [];

        foreach ($carts as $cart) {
            $line = [
                'id' => (int)$cart['id_cart'],
                'nb_products' => $cart['nb_products'],
                'date_add' => $cart['date_add'],
                'products' => [],
            ];

            $products = $cart['products'];

            foreach ($products as $product) {
                $line['products'][] = [
                    'reference' => $product['product_reference'],
                    'name' => $product['product_name'],
                    'quantity' => (int)$product['product_quantity'],
                ];
            }

            $json['carts'][] = $line;
        }

        // MESSSAGES
        $json['messages'] = [];

        foreach ($messages as $message) {
            $json['messages'][] = [
                'ip' => $message['ip'],
                'message' => $message['message'],
                'date' => $message['date_add'],
            ];
        }

        // CONNECTIONS
        $json['connections'] = [];

        foreach ($connections as $connection) {
            $json['connections'][] = [
                'http_referer' => $connection['http_referer'],
                'page_viewed' => $connection['pages'],
                'time_on_the_page' => $connection['time'],
                'ip_address' => $connection['ipaddress'],
                'date' => $connection['date_add'],
            ];
        }

        // MODULES
        $json['modules'] = $modules;

        // Set the filename of the download
        $filename = 'personalData-' . date('Y-m-d');

        // Output CSV-specific headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/json;');
        header('Content-Disposition: attachment; filename="' . $filename . '.json";');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $content = json_encode($json, JSON_PRETTY_PRINT);

        exit($content);
    }
}
