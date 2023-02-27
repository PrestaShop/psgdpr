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

use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;

class ExportService
{
    /**
     * Transform customer data for CSV export
     *
     * @return array
     */
    public function transformViewableCustomerToCsv(ViewableCustomer $customer)
    {
        $transformedData = [
            'personalInformations' => $this->getPersonnalInformations($customer),
            'ordersInformations' => $this->getOrdersInformations($customer),
            'cartsInformations' => $this->getCartsInformations($customer),
            'boughtProductsInformations' => $this->getBoughtInformations($customer),
            'viewedProductsInformations' => $this->getViewedProductsInformations($customer),
            'discountsInformations' => $this->getDiscountsInformations($customer),
            'sentEmailsInformations' => $this->getSentEmailsInformations($customer),
            'lastConnectionsInformations' => $this->getLastConnectionsInformations($customer),
            'groupsInformations' => $this->getGroupsInformations($customer),
            'addressesInformations' => $this->getAddressesInformations($customer),
            'generalInformations' => $this->getGeneralInformations($customer),
            'messagesInformations' => $this->getMessagesInformations($customer),
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

        return ob_get_clean();
    }

    /**
     * Get customer personal informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getPersonnalInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'First name',
                'Last name',
                'Email',
                'Is guest',
                'Social title',
                'Birthday',
                'Registration date',
                'LastUpdate date',
                'Last visit date',
                'Rank by sales',
                'Shop name',
                'Language name',
                'Is newsletter subscribed',
                'Is partner offers subscribed',
                'Is active',
            ],
            'data' => [
                [
                    $customer->getPersonalInformation()->getFirstName(),
                    $customer->getPersonalInformation()->getLastName(),
                    $customer->getPersonalInformation()->getEmail(),
                    json_encode($customer->getPersonalInformation()->isGuest()),
                    $customer->getPersonalInformation()->getSocialTitle(),
                    $customer->getPersonalInformation()->getBirthday(),
                    $customer->getPersonalInformation()->getRegistrationDate(),
                    $customer->getPersonalInformation()->getLastUpdateDate(),
                    $customer->getPersonalInformation()->getLastVisitDate(),
                    $customer->getPersonalInformation()->getRankBySales(),
                    $customer->getPersonalInformation()->getShopName(),
                    $customer->getPersonalInformation()->getLanguageName(),
                    json_encode($customer->getPersonalInformation()->getSubscriptions()->isNewsletterSubscribed()),
                    json_encode($customer->getPersonalInformation()->getSubscriptions()->isPartnerOffersSubscribed()),
                    json_encode($customer->getPersonalInformation()->isActive()),
                ],
            ],
        ];
    }

    /**
     * Get customer orders informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getOrdersInformations(ViewableCustomer $customer)
    {
        $originalMergedOrders = array_merge(
            $customer->getOrdersInformation()->getValidOrders(),
            $customer->getOrdersInformation()->getInvalidOrders()
        );

        return [
            'headers' => [
                'Order id',
                'Order placed date',
                'Payment method name',
                'Order status',
                'Order products count',
                'Total paid',
            ],
            'data' => array_map(function ($order) {
                return [
                    $order->getOrderId(),
                    $order->getOrderPlacedDate(),
                    $order->getPaymentMethodName(),
                    $order->getOrderStatus(),
                    $order->getOrderProductsCount(),
                    $order->getTotalPaid(),
                ];
            }, $originalMergedOrders),
        ];
    }

    /**
     * Get customer carts informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getCartsInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Cart id',
                'Cart creation date',
                'Cart total',
                'Carrier name',
            ],
            'data' => array_map(function ($cart) {
                return [
                    $cart->getCartId(),
                    $cart->getCartCreationDate(),
                    $cart->getCartTotal(),
                ];
            }, $customer->getCartsInformation()),
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getBoughtInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Order id',
                'Bought date',
                'Product name',
                'Bought quantity',
            ],
            'data' => array_map(function ($product) {
                return [
                    $product->getOrderId(),
                    $product->getBoughtDate(),
                    $product->getProductName(),
                    $product->getBoughtQuantity(),
                ];
            }, $customer->getProductsInformation()->getBoughtProductsInformation()),
        ];
    }

    /**
     * Get customer viewed products informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getViewedProductsInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Product id',
                'Product name',
                'Product url',
            ],
            'data' => array_map(function ($product) {
                return [
                    $product->getProductId(),
                    $product->getProductName(),
                    $product->getProductUrl(),
                ];
            }, $customer->getProductsInformation()->getViewedProductsInformation()),
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getDiscountsInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Discount id',
                'Code',
                'Name',
                'Is active',
                'Available quantity',
            ],
            'data' => array_map(function ($discount) {
                return [
                    $discount->getDiscountId(),
                    $discount->getCode(),
                    $discount->getName(),
                    json_encode($discount->isActive()),
                    $discount->getAvailableQuantity(),
                ];
            }, $customer->getDiscountsInformation()),
        ];
    }

    /**
     * Get customer sent emails informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getSentEmailsInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Date',
                'Language',
                'Subject',
                'Template',
            ],
            'data' => array_map(function ($email) {
                return [
                    $email->getDate(),
                    $email->getLanguage(),
                    $email->getSubject(),
                    $email->getTemplate(),
                ];
            }, $customer->getSentEmailsInformation()),
        ];
    }

    /**
     * Get customer last connections informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getLastConnectionsInformations(ViewableCustomer $customer)
    {
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
                return [
                    $connection->getConnectionId(),
                    $connection->getConnectionDate(),
                    $connection->getPagesViewed(),
                    $connection->getTotalTime(),
                    $connection->getHttpReferer(),
                    $connection->getIpAddress(),
                ];
            }, $customer->getLastConnectionsInformation()),
        ];
    }

    /**
     * Get customer groups informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getGroupsInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Group id',
                'Name',
            ],
            'data' => array_map(function ($group) {
                return [
                    $group->getGroupId(),
                    $group->getName(),
                ];
            }, $customer->getGroupsInformation()),
        ];
    }

    /**
     * Get customer addresses informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getAddressesInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Address id',
                'Company',
                'Full name',
                'Full address',
                'Country name',
                'Phone',
                'Phone mobile',
            ],
            'data' => array_map(function ($address) {
                return [
                    $address->getAddressId(),
                    $address->getCompany(),
                    $address->getFullName(),
                    $address->getFullAddress(),
                    $address->getCountryName(),
                    $address->getPhone(),
                    $address->getPhoneMobile(),
                ];
            }, $customer->getAddressesInformation()),
        ];
    }

    /**
     * Get customer orders informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getGeneralInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Private note',
                'Customer by same email exists',
            ],
            'data' => [
                [
                    $customer->getGeneralInformation()->getPrivateNote(),
                    json_encode($customer->getGeneralInformation()->getCustomerBySameEmailExists()),
                ],
            ],
        ];
    }

    /**
     * Get customer messages informations
     *
     * @param ViewableCustomer $customer
     *
     * @return array
     */
    private function getMessagesInformations(ViewableCustomer $customer)
    {
        return [
            'headers' => [
                'Id customer thread',
                'Id shop',
                'Id lang',
                'Id contact',
                'Id customer',
                'Id order',
                'Id product',
                'Status',
                'Email',
                'Token',
                'Date add',
                'Date update',
                'Id customer message',
                'Id employee',
                'Message',
                'File name',
                'Ip address',
                'User agent',
                'Private',
                'Read',
            ],
            'data' => CustomerThread::getCustomerMessages($customer->getCustomerId()),
        ];
    }
}
