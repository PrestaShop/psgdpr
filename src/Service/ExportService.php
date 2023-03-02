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
use Hook;
use Language;
use Module;
use Order;
use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShopBundle\Translation\TranslatorComponent;
use Psgdpr;
use Tools;

class ExportService
{
    /**
     * @var Psgdpr $module
     */
    private $module;

    /**
     * @var Context $context
     */
    private $context;

    /**
     * @var Translator $translator
     */
    private $translator;

    /**
     * ExportService constructor.
     *
     * @param Context $context
     * @return void
     */
    public function __construct(Psgdpr $module, Context $context, TranslatorComponent $translator)
    {
        $this->module = $module;
        $this->context = $context;
        $this->translator = $translator;
    }

    /**
     * Transform customer data for CSV export
     *
     * @param Customer $customer
     *
     * @return string
     */
    public function transformCustomerToCsv(Customer $customer): string
    {
        $prestashopCustomerData = [
            'personal Informations' => $this->getPersonalInformations($customer),
            'addresses' => $this->getAddressesInformations($customer),
            'orders' => $this->getOrdersInformations($customer),
            'products ordered' => $this->getProductsOrderedInformations($customer),
            'carts' => $this->getCartsInformations($customer),
            'products In cart' => $this->getProductsInCartInformation($customer),
            'messagess' => $this->getMessagesInformations($customer),
            'last connections' => $this->getLastConnectionsInformations($customer),
            'discounts' => $this->getDiscountsInformations($customer),
            'last sent emails' => $this->getLastSentEmailsInformations($customer),
            'groups' => $this->getGroupsInformations($customer),
        ];

        $thirdPartyCustomerData = $this->getThirdPartyModulesInformations($customer);

        $result = array_merge($prestashopCustomerData, $thirdPartyCustomerData);

        $buffer = fopen('php://output', 'w');
        ob_start();

        foreach ($result as $key => $value) {
            fputcsv($buffer, [strtoupper($key)]);
            fputcsv($buffer, $value['headers']);

            foreach ($value['data'] as $data) {
                fputcsv($buffer, $data);
            }

            fputcsv($buffer, []);
        }

        $file = ob_get_clean();
        fclose($buffer);

        if (empty($file)) {
            return '';
        }

        return $file;
    }

    /**
     * Get customer personal informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getPersonalInformations(Customer $customer): array
    {
        $customerGender = new Gender($customer->id_gender, $this->context->language->id);
        $customerLanguage = Language::getLanguage($customer->id_lang);
        $customerStats = $customer->getStats();

        $genderName = $customerGender->name;

        return [
            'headers' => [
                $this->translator->trans('Social title', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('First name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Last name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Birthday', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Email', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Language', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Registration date', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Last visit date', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Is guest', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Shop name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Is newsletter subscribed', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Is partner offers subscribed', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Siret', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Ape', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Website', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Personal note', [], 'Modules.Psgdpr.Export'),
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
     * Get customer addresses informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getAddressesInformations(Customer $customer): array
    {
        $customerAddresses = $customer->getAddresses($this->context->language->id);

        return [
            'headers' => [
                $this->translator->trans('Alias', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Company', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Full name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Full address', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Country name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Phone', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Phone mobile', [], 'Modules.Psgdpr.Export'),
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
    private function getOrdersInformations(Customer $customer): array
    {
        $orderList = Order::getCustomerOrders($customer->id);

        return [
            'headers' => [
                $this->translator->trans('Reference', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Payment', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('status', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Total paid with taxes', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Date of order', [], 'Modules.Psgdpr.Export'),
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
    private function getProductsOrderedInformations(Customer $customer): array
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
                $this->translator->trans('Order reference', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Reference', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Quantity', [], 'Modules.Psgdpr.Export'),
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
    private function getCartsInformations(Customer $customer): array
    {
        $cartList = Cart::getCustomerCarts($customer->id, false);

        return [
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Total', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Creation date', [], 'Modules.Psgdpr.Export'),
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
    private function getProductsInCartInformation(Customer $customer): array
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
                $this->translator->trans('Cart id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Reference', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Quantity', [], 'Modules.Psgdpr.Export'),
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
    private function getMessagesInformations(Customer $customer): array
    {
        $customerMessages = CustomerThread::getCustomerMessages($customer->id);

        return [
            'headers' => [
                $this->translator->trans('Ip address', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Message', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Creation date', [], 'Modules.Psgdpr.Export'),
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
     * Get customer last connections informations
     *
     * @param Customer $customer
     *
     * @return array
     */
    private function getLastConnectionsInformations(Customer $customer): array
    {
        $lastConnections = $customer->getLastConnections();

        return [
            'headers' => [
                $this->translator->trans('id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Date', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Pages viewed', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Total time', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Http referer', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Ip address', [], 'Modules.Psgdpr.Export'),
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
    private function getDiscountsInformations(Customer $customer): array
    {
        $discountsList = CartRule::getAllCustomerCartRules($customer->id);

        return [
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Code', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Description', [], 'Modules.Psgdpr.Export'),
            ],
            'data' => array_map(function ($discount) {
                return [
                    $discount['id_cart_rule'],
                    $discount['code'],
                    $discount['name'],
                    $discount['description'],
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
    private function getLastSentEmailsInformations(Customer $customer): array
    {
        $emails = $customer->getLastEmails();

        return [
            'headers' => [
                $this->translator->trans('Date', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Language', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Subject', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Template', [], 'Modules.Psgdpr.Export'),
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
    private function getGroupsInformations(Customer $customer): array
    {
        $groupsidList = $customer->getGroups();

        return [
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
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

    /**
     * @param Customer $customer
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    private function getThirdPartyModulesInformations(Customer $customer): array
    {
        $thirdPartyModulesList = Hook::getHookModuleExecList('actionExportGDPRData');
        $thirdPartyModuleData = [];

        foreach ($thirdPartyModulesList as $module) {
            $moduleInfos = Module::getInstanceById($module['id_module']);
            $moduleData = json_decode(Hook::exec('actionExportGDPRData', (array) $customer, $module['id_module']));
            $entryName = 'MODULE : ' . $moduleInfos->displayName;

            if (!is_array($moduleData)) {
                continue;
            }

            foreach ($moduleData as $data) {
                $dataToArray = json_decode(json_encode($data), true);

                $thirdPartyModuleData[$entryName]['headers'] = array_keys($dataToArray);
                $thirdPartyModuleData[$entryName]['data'][] = array_values($dataToArray);
            }
        }

        return $thirdPartyModuleData;
    }
}
