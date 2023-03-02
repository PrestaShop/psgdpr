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
use DateTime;
use Gender;
use Group;
use Hook;
use Language;
use Module;
use Order;
use PDF;
use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShopBundle\Translation\TranslatorComponent;
use Psgdpr;
use Tools;

class ExportService
{
    CONST EXPORT_TYPE_CSV = 'csv';
    CONST EXPORT_TYPE_PDF = 'pdf';

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
     * Transform customer data for export
     *
     * @param Customer $customer
     *
     * @return string
     */
    public function exportCustomerData(Customer $customer, string $exportType): string
    {
        $customerData = [
            'personalinformations' => $this->getPersonalInformations($customer),
            'addresses' => $this->getAddressesInformations($customer),
            'orders' => $this->getOrdersInformations($customer),
            'productsOrdered' => $this->getProductsOrderedInformations($customer),
            'carts' => $this->getCartsInformations($customer),
            'productsInCart' => $this->getProductsInCartInformation($customer),
            'messages' => $this->getMessagesInformations($customer),
            'lastConnections' => $this->getLastConnectionsInformations($customer),
            'discounts' => $this->getDiscountsInformations($customer),
            'lastSentEmails' => $this->getLastSentEmailsInformations($customer),
            'groups' => $this->getGroupsInformations($customer),
            'modules' => $this->getThirdPartyModulesInformations($customer),
        ];

        switch ($exportType) {
            case self::EXPORT_TYPE_CSV:
                return $this->exportCustomerToCsv($customerData);
            case self::EXPORT_TYPE_PDF:
                return $this->exportCustomerToPdf($customerData);
        }
    }

    /**
     * Generate CSV file from customer data
     *
     * @param array $customerData
     * @return string
     */
    private function exportCustomerToCsv(array $customerData)
    {
        $buffer = fopen('php://output', 'w');
        ob_start();

        foreach ($customerData as $key => $value) {
            if ($key === 'modules') {
                foreach ($value as $thirdPartyValue) {
                    $this->insertDataInCsv($buffer, $thirdPartyValue);
                }

                continue;
            }

            $this->insertDataInCsv($buffer, $value);
        }

        $file = ob_get_clean();
        fclose($buffer);

        if (empty($file)) {
            return '';
        }

        return $file;
    }

    /**
     * Insert data in CSV file
     *
     * @param mixed $buffer
     * @param mixed $value
     *
     * @return void
     */
    private function insertDataInCsv($buffer, $value)
    {
        fputcsv($buffer, [strtoupper($value['name'])]);
        fputcsv($buffer, $value['headers']);

        foreach ($value['data'] as $data) {
            fputcsv($buffer, $data);
        }

        fputcsv($buffer, []);
    }

    /**
     * Generate PDF file from customer data
     *
     * @param array $customerData
     */
    private function exportCustomerToPdf(array $customerData)
    {
        $pdfFile = new PDF([$customerData], 'PsgdprModule', $this->context->smarty);
        $pdfFile->render(true);
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

        $today = new Datetime(date('m.d.y'));
        $age = $today->diff(new DateTime($customer->birthday));

        return [
            'name' => 'personal informations',
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
                    'gender' => $genderName,
                    'firstname' => $customer->firstname,
                    'lastname' => $customer->lastname,
                    'birthday' => $customer->birthday,
                    'age' => $age->y,
                    'email' => $customer->email,
                    'language' => $customerLanguage['name'],
                    'dateAdd' => $customer->date_add,
                    'lastVisit' => $customerStats['last_visit'],
                    'isGuest' => json_encode($customer->is_guest),
                    'company' => $customer->company,
                    'isNewsletterSubscribed' => json_encode($customer->newsletter),
                    'isPartnerOffersSubscribed' => json_encode($customer->optin),
                    'siret' => $customer->siret,
                    'ape' => $customer->ape,
                    'website' => $customer->website,
                    'note' => $customer->note,
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
            'name' => 'addresses',
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
                $fullName = $address['firstname'] . ' ' . $address['lastname'];
                $fullAddress = $address['address1'] . ' ' . $address['address2'] . ' ' . $address['postcode'] . ' ' . $address['city'];

                return [
                    'alias' => $address['alias'],
                    'company' => $address['company'],
                    'fullName' => $fullName,
                    'fullAddress' => $fullAddress,
                    'country' => $address['country'],
                    'phone' => $address['phone'],
                    'mobilePhone' => $address['phone_mobile'],
                    'dateAdd' => $address['date_add'],
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
            'name' => 'orders',
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
                    'reference' =>$order['reference'],
                    'payment' => $order['payment'],
                    'state' => $order['order_state'],
                    'totalPaid' => $totalPaid,
                    'date' => $order['date_add']
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
                    'orderReference' => $currentOrder->reference,
                    'reference' => $product['product_reference'],
                    'name' => $product['product_name'],
                    'quantity' => $product['product_quantity'],
                ];
            }, $productsInOrder);
        }

        return [
            'name' => 'products ordered',
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
            'name' => 'carts',
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Total', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Creation date', [], 'Modules.Psgdpr.Export'),
            ],
            'data' => array_map(function ($cart) {
                $currentCart = new Cart($cart['id_cart']);
                $productsCart = $currentCart->getProducts();

                return [
                    'cartId' => $cart['id_cart'],
                    'totalProducts' => count($productsCart),
                    'creationDate' => $cart['date_add'],

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
                    'cartId' => $currentCart->id,
                    'reference' => $product['reference'],
                    'name' => $product['name'],
                    'quantity' => $product['quantity'],
                ];
            }, $productsList);
        }

        return [
            'name' => 'products in cart',
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
            'name' => 'messages',
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
                    'ipAddress' => $ipAddress,
                    'message' => $message['message'],
                    'creationDate' => $message['date_add'],
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
            'name' => 'last connections',
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
                    'connectionId' => $connection['id_connections'],
                    'date' => $connection['date_add'],
                    'pagesViewed' => $connection['pages'],
                    'totalTime' => $connection['time'],
                    'httpReferer' => $connection['http_referer'],
                    'ipAddress' => $ipAddress,
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
            'name' => 'discounts',
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Code', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Description', [], 'Modules.Psgdpr.Export'),
            ],
            'data' => array_map(function ($discount) {
                return [
                    'discountId' => $discount['id_cart_rule'],
                    'code' => $discount['code'],
                    'name' => $discount['name'],
                    'description' => $discount['description'],
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
            'name' => 'last sent emails',
            'headers' => [
                $this->translator->trans('Date', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Language', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Subject', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Template', [], 'Modules.Psgdpr.Export'),
            ],
            'data' => array_map(function ($email) {
                return [
                    'creationDate' => Tools::displayDate($email['date_add'], true),
                    'language' => $email['language'],
                    'subject' => $email['subject'],
                    'template' => $email['template']
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
            'name' => 'groups',
            'headers' => [
                $this->translator->trans('Id', [], 'Modules.Psgdpr.Export'),
                $this->translator->trans('Name', [], 'Modules.Psgdpr.Export'),
            ],
            'data' => array_map(function ($groupId) {
                $currentGroup = new Group($groupId);
                $languageId = $this->context->language->id;

                return [
                    'groupId' => $currentGroup->id,
                    'groupName' => $currentGroup->name[$languageId]
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

                $thirdPartyModuleData[$moduleInfos->name]['name'] = $entryName;
                $thirdPartyModuleData[$moduleInfos->name]['headers'] = array_keys($dataToArray);
                $thirdPartyModuleData[$moduleInfos->name]['data'][] = array_values($dataToArray);
            }
        }

        return $thirdPartyModuleData;
    }
}
