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

namespace PrestaShop\Module\Psgdpr\Domain\Export;

use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShop\PrestaShop\Adapter\Entity\Module;
use PrestaShop\PrestaShop\Core\CommandBus\CommandBusInterface;
use PrestaShop\PrestaShop\Core\Domain\Customer\Query\GetCustomerForViewing;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use Psgdpr;

class Export
{
    /**
     * @var int
     */
    private $customerId;

    /**
     * @var ViewableCustomer
     */
    private $customer;

    /**
     * Export constructor.
     *
     * @param int $customerId
     *
     * @return void
     */
    public function __construct(int $customerId)
    {
        $this->customerId = $customerId;

        $this->fetchCustomerData();
    }

    /**
     * @return string
     */
    public function toCsv(): string
    {
        $csvPath = dirname(__DIR__, 4) . '/export-files';
        $csvName = $this->customerId . '_' . date('Y-m-d_His') . '.csv';

        if (!is_dir($csvPath)) {
            mkdir($csvPath, 0777, true);
        }

        $csvFile = fopen($csvPath . '/' . $csvName, 'w');

        $transformedData = $this->transformCustomerData();

        if (false === $csvFile) {
            throw new \Exception('Cannot create csv file');
        }

        foreach ($transformedData as $value) {
            fputcsv($csvFile, $value['headers']);

            foreach ($value['data'] as $data) {
                fputcsv($csvFile, $data);
            }

            fputcsv($csvFile, []);
        }

        return $csvName;
    }

    /**
     * Transform customer data for CSV export
     *
     * @return array
     */
    private function transformCustomerData()
    {
        return [
            'personal_informations' => $this->getPersonnalInformations(),
            'orders_informations' => $this->getOrdersInformations(),
            'carts_informations' => $this->getCartsInformations(),
            'bought_products_informations' => $this->getBoughtInformations(),
            'viewed_products_informations' => $this->getViewedProductsInformations(),
            'discounts_informations' => $this->getDiscountsInformations(),
            'sent_emails_informations' => $this->getSentEmailsInformations(),
            'last_connections_informations' => $this->getLastConnectionsInformations(),
            'groups_informations' => $this->getGroupsInformations(),
            'addresses_informations' => $this->getAddressesInformations(),
            'general_informations' => $this->getGeneralInformations(),
            'messages_informations' => $this->getMessagesInformations(),
        ];
    }

    /**
     * Fetch customer data with id
     *
     * @return void
     */
    private function fetchCustomerData()
    {
        /** @var Psgdpr $module */
        $module = Module::getInstanceByName('psxlegalassistant');

        /** @var CommandBusInterface $queryBus */
        $queryBus = $module->get('prestashop.core.query_bus');

        $this->customer = $queryBus->handle(new GetCustomerForViewing($this->customerId));
    }

    /**
     * Get customer personal informations
     *
     * @return array
     */
    private function getPersonnalInformations()
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
                    $this->customer->getPersonalInformation()->getFirstName(),
                    $this->customer->getPersonalInformation()->getLastName(),
                    $this->customer->getPersonalInformation()->getEmail(),
                    json_encode($this->customer->getPersonalInformation()->isGuest()),
                    $this->customer->getPersonalInformation()->getSocialTitle(),
                    $this->customer->getPersonalInformation()->getBirthday(),
                    $this->customer->getPersonalInformation()->getRegistrationDate(),
                    $this->customer->getPersonalInformation()->getLastUpdateDate(),
                    $this->customer->getPersonalInformation()->getLastVisitDate(),
                    $this->customer->getPersonalInformation()->getRankBySales(),
                    $this->customer->getPersonalInformation()->getShopName(),
                    $this->customer->getPersonalInformation()->getLanguageName(),
                    json_encode($this->customer->getPersonalInformation()->getSubscriptions()->isNewsletterSubscribed()),
                    json_encode($this->customer->getPersonalInformation()->getSubscriptions()->isPartnerOffersSubscribed()),
                    json_encode($this->customer->getPersonalInformation()->isActive()),
                ],
            ],
        ];
    }

    /**
     * Get customer orders informations
     *
     * @return array
     */
    private function getOrdersInformations()
    {
        $originalMergedOrders = array_merge(
            $this->customer->getOrdersInformation()->getValidOrders(),
            $this->customer->getOrdersInformation()->getInvalidOrders()
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
     * @return array
     */
    private function getCartsInformations()
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
            }, $this->customer->getCartsInformation()),
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @return array
     */
    private function getBoughtInformations()
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
            }, $this->customer->getProductsInformation()->getBoughtProductsInformation()),
        ];
    }

    /**
     * Get customer viewed products informations
     *
     * @return array
     */
    private function getViewedProductsInformations()
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
            }, $this->customer->getProductsInformation()->getViewedProductsInformation()),
        ];
    }

    /**
     * Get customer discounts informations
     *
     * @return array
     */
    private function getDiscountsInformations()
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
            }, $this->customer->getDiscountsInformation()),
        ];
    }

    /**
     * Get customer sent emails informations
     *
     * @return array
     */
    private function getSentEmailsInformations()
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
            }, $this->customer->getSentEmailsInformation()),
        ];
    }

    /**
     * Get customer last connections informations
     *
     * @return array
     */
    private function getLastConnectionsInformations()
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
            }, $this->customer->getLastConnectionsInformation()),
        ];
    }

    /**
     * Get customer groups informations
     *
     * @return array
     */
    private function getGroupsInformations()
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
            }, $this->customer->getGroupsInformation()),
        ];
    }

    /**
     * Get customer addresses informations
     *
     * @return array
     */
    private function getAddressesInformations()
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
            }, $this->customer->getAddressesInformation()),
        ];
    }

    /**
     * Get customer orders informations
     *
     * @return array
     */
    private function getGeneralInformations()
    {
        return [
            'headers' => [
                'Private note',
                'Customer by same email exists',
            ],
            'data' => [
                [
                    $this->customer->getGeneralInformation()->getPrivateNote(),
                    json_encode($this->customer->getGeneralInformation()->getCustomerBySameEmailExists()),
                ],
            ],
        ];
    }

    /**
     * Get customer messages informations
     *
     * @return array
     */
    private function getMessagesInformations()
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
            'data' => CustomerThread::getCustomerMessages($this->customerId),
        ];
    }
}
