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

class psgdprExportDataToCsvModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $customer = Context::getContext()->customer;
        $secure_key = sha1($customer->secure_key);
        $token = Tools::getValue('psgdpr_token');

        if ($customer->isLogged() === false || !isset($token) || $token != $secure_key) {
            die('bad token');
        }

        GDPRLog::addLog($customer->id, 'exportCsv', 0);
        $this->exportDataToCsv($customer->id);
    }

    public function exportDataToCsv($id_customer)
    {
        $data = $this->module->getCustomerData('customer', $id_customer);

        $customerInfo = $data['data']['prestashopData']['customerInfo'];
        $addresses = $data['data']['prestashopData']['addresses'];
        $orders = $data['data']['prestashopData']['orders'];
        $carts = $data['data']['prestashopData']['carts'];
        $messages = $data['data']['prestashopData']['messages'];
        $connections = $data['data']['prestashopData']['connections'];
        $modules = $data['data']['modulesData'];

        // Open the output stream
        $fh = fopen('php://output', 'w');
        $delimiter = "\t";

        // Start output buffering (to capture stream contents)
        ob_start();

        // GENERAL INFO
        $line = array(Tools::strtoupper($this->module->l('General info')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Gender'),
            $this->module->l('Name'),
            $this->module->l('Birth date'),
            $this->module->l('Age'),
            $this->module->l('Email'),
            $this->module->l('Language'),
            $this->module->l('Creation account data'),
            $this->module->l('Last visit'),
            $this->module->l('Siret'),
            $this->module->l('Ape'),
            $this->module->l('Company'),
            $this->module->l('Website'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        $line = array(
            $customerInfo['gender'],
            $customerInfo['firstname'].' '.$customerInfo['lastname'],
            $customerInfo['birthday'],
            $customerInfo['age'],
            $customerInfo['email'],
            $customerInfo['language'],
            $customerInfo['date_add'],
            $customerInfo['last_visit'],
            $customerInfo['siret'],
            $customerInfo['ape'],
            $customerInfo['company'],
            $customerInfo['website'],
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);
        // GENERAL INFO

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // ADDRESSES
        $line = array(Tools::strtoupper($this->module->l('Addresses')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Alias'),
            $this->module->l('Company'),
            $this->module->l('Name'),
            $this->module->l('Address'),
            $this->module->l('Phone(s)'),
            $this->module->l('Country'),
            $this->module->l('Date'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($addresses) >= 1) {
            foreach ($addresses as $address) {
                $line = array(
                    $address['alias'],
                    $address['company'],
                    $address['firstname'].' '.$address['lastname'],
                    $address['address1'].' '.$address['address2'],
                    $address['phone'].' '.$address['phone_mobile'],
                    $address['country'],
                    $address['date_add'],
                );
                fputcsv($fh, $line, $delimiter);
                unset($line);
            }
        } else {
            $line = array($this->module->l('No addresses'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // ADDRESSES

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // ORDERS
        $line = array(Tools::strtoupper($this->module->l('Orders')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Reference'),
            $this->module->l('Payment'),
            $this->module->l('Order state'),
            $this->module->l('Total paid'),
            $this->module->l('Date'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($orders) >= 1) {
            foreach ($orders as $order) {
                $line = array(
                    $order['reference'],
                    $order['payment'],
                    $order['order_state'],
                    $order['total_paid_tax_incl'],
                    $order['date_add'],
                );
                fputcsv($fh, $line, $delimiter);
                unset($line);
            }
        } else {
            $line = array($this->module->l('No orders'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // ORDERS

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // PRODUCTS IN ORDER
        if (count($orders) >= 1) {
            $line = array(Tools::strtoupper($this->module->l('Products bought')));
            fputcsv($fh, $line, $delimiter);

            $line = array(
                $this->module->l('Order ref'),
                $this->module->l('Product ref'),
                $this->module->l('Name'),
                $this->module->l('Quantity'),
            );
            fputcsv($fh, $line, $delimiter);
            unset($line);

            foreach ($orders as $order) {
                $products = $order['products'];
                foreach ($products as $product) {
                    $line = array(
                        $order['reference'],
                        $product['product_reference'],
                        $product['product_name'],
                        $product['product_quantity'],
                    );
                    fputcsv($fh, $line, $delimiter);
                    unset($line);
                }
            }
        }
        // PRODUCTS IN ORDER

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // CARTS
        $line = array(Tools::strtoupper($this->module->l('Carts')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Id'),
            $this->module->l('Total products'),
            $this->module->l('Date'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($carts) >= 1) {
            foreach ($carts as $cart) {
                $line = array(
                    '#'.$cart['id_cart'],
                    $cart['nb_products'],
                    $cart['date_add'],
                );
                fputcsv($fh, $line, $delimiter);
                unset($line);
            }
        } else {
            $line = array($this->module->l('No carts'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // CARTS

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // PRODUCTS IN CART
        $line = array(Tools::strtoupper($this->module->l('Product(s) still in cart')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Cart ID'),
            $this->module->l('Product reference'),
            $this->module->l('Name'),
            $this->module->l('Quantity'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($carts) >= 1) {
            foreach ($carts as $cart) {
                $products = $cart['products'];
                if (count($products) >= 1) {
                    foreach ($products as $product) {
                        $line = array(
                            '#'.$cart['id_cart'],
                            $product['product_reference'],
                            $product['product_name'],
                            $product['product_quantity'],
                        );
                        fputcsv($fh, $line, $delimiter);
                        unset($line);
                    }
                } else {
                    $line = array($this->module->l('No products'));
                    fputcsv($fh, $line, $delimiter);
                    unset($line);
                }
            }
        } else {
            $line = array($this->module->l('No carts'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // PRODUCTS IN CART

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // MESSSAGES
        $line = array(Tools::strtoupper($this->module->l('Messages')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('IP'),
            $this->module->l('Message'),
            $this->module->l('Date'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($messages) >= 1) {
            foreach ($messages as $message) {
                $line = array(
                    $message['ip'],
                    $message['message'],
                    $message['date_add'],
                );
                fputcsv($fh, $line, $delimiter);
                unset($line);
            }
        } else {
            $line = array($this->module->l('No messages'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // MESSAGES

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // CONNECTIONS
        $line = array(Tools::strtoupper($this->module->l('Last connections')));
        fputcsv($fh, $line, $delimiter);

        $line = array(
            $this->module->l('Origin request'),
            $this->module->l('Page viewed'),
            $this->module->l('Time on the page'),
            $this->module->l('IP address'),
            $this->module->l('Date'),
            $this->module->l('Country'),
            $this->module->l('Date'),
        );
        fputcsv($fh, $line, $delimiter);
        unset($line);

        if (count($connections) >= 1) {
            foreach ($connections as $connection) {
                $line = array(
                    $connection['http_referer'],
                    $connection['pages'],
                    $connection['time'],
                    $connection['ipaddress'],
                    $connection['date_add'],
                );
                fputcsv($fh, $line, $delimiter);
                unset($line);
            }
        } else {
            $line = array($this->module->l('No connections'));
            fputcsv($fh, $line, $delimiter);
            unset($line);
        }
        // CONNECTIONS

        // empty line
        $line = array();
        fputcsv($fh, $line, $delimiter);

        // MODULES
        if (count($modules) >= 1) {
            foreach ($modules as $index => $module) {
                $line = array(Tools::strtoupper('Module : '.$index));
                fputcsv($fh, $line, $delimiter);
                unset($line);
                if (count($module) >= 1 && is_array($module)) {
                    foreach ($module as $table) {
                        foreach ($table as $key => $value) {
                            $line[] = $key;
                        }
                        fputcsv($fh, $line, $delimiter);
                        unset($line);
                        foreach ($table as $key => $value) {
                            $line[] = $value;
                        }
                        fputcsv($fh, $line, $delimiter);
                        unset($line);
                    }
                } else {
                    $line[] = $module;
                    fputcsv($fh, $line, $delimiter);
                    unset($line);
                }
                // empty line
                $line = array();
                fputcsv($fh, $line, $delimiter);
            }
        }
        // MODULES

        // Get the contents of the output buffer
        $csv = ob_get_clean();

        // Set the filename of the download
        $filename = 'personalData-'.date('Y-m-d');

        // Output CSV-specific headers
        header('Content-Description: File Transfer');
        //header('Content-Type: application/octet-stream');
        header('Content-Type: application/vnd.ms-excel;');
        header("Content-Type: application/x-msexcel;");
        header('Content-Disposition: attachment; filename="' . $filename . '.csv";');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');

        $csv = chr(255).chr(254).iconv("UTF-8", "UTF-16LE", $csv);

        exit($csv);
    }
}
