<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\Psgdpr\Service;

use Cart;
use CartRule;
use Context;
use Currency;
use Customer;
use Gender;
use Group;
use Language;
use Order;
use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\DiscountInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use Tools;

class ExportService
{
    /**
     * @var Context $context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Transform viewableCustomer data for CSV export
     *
     * @return array
     */
    public function transformViewableCustomerToCsv(Customer $customer)
    {
        $transformedData = [
            'personalInformations' => $this->getPersonnalInformations($customer),
            'addressesInformations' => $this->getAddressesInformations($customer),
            'ordersInformations' => $this->getOrdersInformations($customer),
            'productsOrderedInformations' => $this->getProductsOrderedInformations($customer),
            'cartsInformations' => $this->getCartsInformations($customer),
            'productsInCartInformation' => $this->getProductsInCartInformation($customer),
            'messagesInformations' => $this->getMessagesInformations($customer),
            'lastConnectionsInformations' => $this->getLastConnectionsInformations($customer),
            'discountsInformations' => $this->getDiscountsInformations($customer),
            'lastSentEmailsInformations' => $this->getLastSentEmailsInformations($customer),
            'groupsInformations' => $this->getGroupsInformations($customer),
        ];

        $buffer = fopen('php://output', 'w');
        ob_start();

        foreach ($transformedData as $value) {
            fputcsv($buffer, $value['headers']);

            foreach ($value['data'] as $data) {
                fputcsv($buffer, $data);
            }

            fputcsv($buffer, []);
        }

        $file = ob_get_clean();
        fclose($buffer);

        return $file;
    }

    /**
     * Get customer personal informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getPersonnalInformations(Customer $customer)
    {
        $customerGender = new Gender($customer->id_gender, $this->context->language->id);
        $customerLanguage = Language::getLanguage($customer->id_lang);
        $customerStats = $customer->getStats();

        $genderName = $customerGender->name;

        return [
            'headers' => [
                'Social title',
                'First name',
                'Last name',
                'Birthday',
                'Email',
                'Language',
                'Registration date',
                'Last visit date',
                'Is guest',
                'Shop name',
                'Is newsletter subscribed',
                'Is partner offers subscribed',
                'Siret',
                'Ape',
                'Website',
                'Personal note'
            ],
            'data' => [
                [
                    $genderName,
                    $customer->firstname,
                    $customer->lastname,
                    $customer->birthday,
                    $customer->email,
                    $customerLanguage['name'],
                    $customer->date_add,
                    $customerStats['last_visit'],
                    json_encode($customer->is_guest),
                    $customer->company,
                    json_encode($customer->newsletter),
                    json_encode($customer->optin),
                    $customer->siret,
                    $customer->ape,
                    $customer->website,
                    $customer->note,
                ],
            ],
        ];
    }

    /**
     * Get viewableCustomer addresses informations
     *
     * @param Customer $viewableCustomer
     *
     * @return array
     */
    private function getAddressesInformations(Customer $customer)
    {
        $customerAddresses = $customer->getAddresses($this->context->language->id);

        return [
            'headers' => [
                'Alias',
                'Company',
                'Full name',
                'Full address',
                'Country name',
                'Phone',
                'Phone mobile',
            ],
            'data' => array_map(function ($address) {
                $fullAddressName = $address['firstname'] . ' ' . $address['lastname'];
                $fullAddress = $address['address1'] . ' ' . $address['address2'] . ' ' . $address['postcode'] . ' ' . $address['city'];

                return [
                    $address['alias'],
                    $address['company'],
                    $fullAddressName,
                    $fullAddress,
                    $address['country'],
                    $address['phone'],
                    $address['phone_mobile'],
                ];
            }, $customerAddresses),
        ];
    }

    /**
     * Get customer orders informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getOrdersInformations(Customer $customer)
    {
        $orderList = Order::getCustomerOrders($customer->id);

        return [
            'headers' => [
                'Reference',
                'Payment',
                'Order status',
                'Total paid with taxes',
                'Date of order',
            ],
            'data' => array_map(function ($order) {
                $currency = Currency::getCurrency($order['id_currency']);
                $totalPaid = number_format($order['total_paid_tax_incl'], 2) . ' ' . $currency['iso_code'];

                return [
                    $order['reference'],
                    $order['payment'],
                    $order['order_state'],
                    $totalPaid,
                    $order['date_add']
                ];
            }, $orderList),
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getProductsOrderedInformations(Customer $customer)
    {
        $orderList = Order::getCustomerOrders($customer->id);
        $productsOrdered = [];

        foreach ($orderList as $order) {
            $currentOrder = new Order($order['id_order']);
            $productsInOrder = $currentOrder->getProducts();

            $productsOrdered += array_map(function ($product) use ($currentOrder) {
                return [
                    $currentOrder->reference,
                    $product['product_reference'],
                    $product['product_name'],
                    $product['product_quantity'],
                ];
            }, $productsInOrder);
        }

        return [
            'headers' => [
                'Reference',
                'Product reference',
                'Product name',
                'Product quantity',
            ],
            'data' => $productsOrdered,
        ];
    }

    /**
     * Get customer carts informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getCartsInformations(Customer $customer)
    {
        $cartList = Cart::getCustomerCarts($customer->id, false);

        return [
            'headers' => [
                'Cart id',
                'Total',
                'Cart creation date',
            ],
            'data' => array_map(function ($cart) {
                $currentCart = new Cart($cart['id_cart']);
                $productsCart = $currentCart->getProducts();

                return [
                    $cart['id_cart'],
                    count($productsCart),
                    $cart['date_add'],

                ];
            }, $cartList),
        ];
    }

    /**
     * Get customer products in cart informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getProductsInCartInformation(Customer $customer)
    {
        $cartList = Cart::getCustomerCarts($customer->id, false);
        $productsInCart = [];

        foreach ($cartList as $cart) {
            $currentCart = new Cart($cart['id_cart']);
            $productsList = $currentCart->getProducts();

            $productsInCart += array_map(function ($product) use ($currentCart) {

                return [
                    $currentCart->id,
                    $product['reference'],
                    $product['name'],
                    $product['quantity'],
                ];
            }, $productsList);
        }

        return [
            'headers' => [
                'Cart id',
                'Product reference',
                'Product name',
                'Product quantity',
            ],
            'data' => $productsInCart,
        ];
    }

    /**
     * Get customer messages informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getMessagesInformations(Customer $customer)
    {
        $customerMessages = CustomerThread::getCustomerMessages($customer->id);

        return [
            'headers' => [
                'ip',
                'message',
                'date_add',
            ],
            'data' => array_map(function ($message) {
                $ipAddress = $message['ip_address'];

                if ((int) $message['ip_address'] == $message['ip_address']) {
                    $ipAddress = long2ip((int) $message['ip_address']);
                }

                return [
                    $ipAddress,
                    $message['message'],
                    $message['date_add'],
                ];
            }, $customerMessages),
        ];
    }

    /**
     * Get viewableCustomer last connections informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getLastConnectionsInformations(Customer $customer)
    {
        $lastConnections = $customer->getLastConnections();

        return [
            'headers' => [
                'Connection id',
                'Connection date',
                'Pages viewed',
                'Total time',
                'Http referer',
                'Ip address',
            ],
            'data' => array_map(function ($connection) {
                $ipAddress = $connection['ipaddress'];

                if ((int) $connection['ipaddress'] == $connection['ipaddress']) {
                    $ipAddress = long2ip((int) $connection['ipaddress']);
                }

                return [
                    $connection['id_connections'],
                    $connection['date_add'],
                    $connection['pages'],
                    $connection['time'],
                    $connection['http_referer'],
                    $ipAddress,
                ];
            }, $lastConnections)
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getDiscountsInformations(Customer $customer)
    {
        $discountsList = CartRule::getAllCustomerCartRules($customer->id);

        return [
            'headers' => [
                'Discount id',
                'Code',
                'Name',
                'Description',
                'Is active',
                'Available quantity',
            ],
            'data' => array_map(function ($discount) {
                return [
                    $discount['id_cart_rule'],
                    $discount['code'],
                    $discount['name'],
                    $discount['description'],
                    json_encode($discount['active']),
                    $discount['quantity'],
                ];
            }, $discountsList)
        ];
    }

    /**
     * Get customer sent emails informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getLastSentEmailsInformations(Customer $customer)
    {
        $emails = $customer->getLastEmails();

        return [
            'headers' => [
                'Date',
                'Language',
                'Subject',
                'Template',
            ],
            'data' => array_map(function ($email) {
                return [
                    Tools::displayDate($email['date_add'], true),
                    $email['language'],
                    $email['subject'],
                    $email['template']
                ];
            }, $emails)
        ];
    }

    /**
     * Get customer groups informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getGroupsInformations(Customer $customer)
    {
        $groupsidList = $customer->getGroups();

        return [
            'headers' => [
                'Group id',
                'Name',
            ],
            'data' => array_map(function ($groupId) {
                $currentGroup = new Group($groupId);
                $languageId = $this->context->language->id;

                return [
                    $currentGroup->id,
                    $currentGroup->name[$languageId]
                ];
            }, $groupsidList)
        ];
    }
}
