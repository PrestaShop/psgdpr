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
use Category;
use Context;
use Currency;
use Customer;
use Gender;
use Group;
use Language;
use Order;
use PrestaShopException;
use Product;
use Shop;
use Tools;
use Validate;
use Link;
use PrestaShop\PrestaShop\Core\Localization\Locale;
use PrestaShop\Module\Psgdpr\Repository\CartRepository;
use PrestaShop\PrestaShop\Adapter\Entity\CustomerThread;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\AddressInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\BoughtProductInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\CartInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\DiscountInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\GeneralInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\GroupInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\LastConnectionInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\MessageInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\OrderInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\OrdersInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\PersonalInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ProductsInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\SentEmailInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\Subscriptions;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewableCustomer;
use PrestaShop\PrestaShop\Core\Domain\Customer\QueryResult\ViewedProductInformation;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;
use PrestaShopBundle\Translation\TranslatorInterface;


class CustomerService
{
    /**
     * @var Context
     */
    private $context; // - ‘@=service(“prestashop.adapter.legacy.context”).getContext()’

    /**
     * @var Locale $locale
     */
    private $locale;

    /**
     *
     * @var TranslatorInterface $translator
     */
    private $translator;

    /**
     * @var CartRepository
     */
    private $cartRepository;


    /**
     * @var Link
     */
    private $link;

    /**
     * @param Locale $locale
     * @param TranslatorInterface $translator
     */
    public function __construct(
        Context $context,
        Locale $locale,
        TranslatorInterface $translator,
        CartRepository $cartRepository,
        Link $link
    ) {
        $this->context = $context;
        $this->locale = $locale;
        $this->translator = $translator;
        $this->cartRepository = $cartRepository;
        $this->link = $link;
    }

    /**
     * Fetch customer data with id
     *
     * @return ViewableCustomer
     */
    public function getViewableCustomer(int $customerId)
    {
        $customer = new Customer($customerId);

        if(!$customer) {
            throw new PrestaShopException('Customer not found');
        }

        return new ViewableCustomer(
            new CustomerId($customer->id),
            $this->getGeneralInformation($customer),
            $this->getPersonalInformation($customer),
            $this->getCustomerOrders($customer),
            $this->getCustomerCarts($customer),
            $this->getCustomerProducts($customer),
            $this->getCustomerMessages($customer),
            $this->getCustomerDiscounts($customer),
            $this->getLastEmailsSentToCustomer($customer),
            $this->getLastCustomerConnections($customer),
            $this->getCustomerGroups($customer),
            $this->getCustomerAddresses($customer)
        );
    }

    /**
     * @param Customer $customer
     *
     * @return GeneralInformation
     */
    private function getGeneralInformation(Customer $customer)
    {
        return new GeneralInformation(
            $customer->note,
            Customer::customerExists($customer->email)
        );
    }

    /**
     * @param Customer $customer
     *
     * @return PersonalInformation
     */
    private function getPersonalInformation(Customer $customer)
    {
        $customerStats = $customer->getStats();

        $gender = new Gender($customer->id_gender);

        if ($gender->name) {
            $socialTitle = $gender->name;
        } else {
            $socialTitle = $this->translator->trans('Unknown', [], 'Admin.Orderscustomers.Feature');
        }

        if ($customer->birthday && '0000-00-00' !== $customer->birthday) {
            $birthday = Tools::displayDate($customer->birthday);
        } else {
            $birthday = $this->translator->trans('Unknown', [], 'Admin.Orderscustomers.Feature');
        }

        $registrationDate = Tools::displayDate($customer->date_add, true);
        $lastUpdateDate = Tools::displayDate($customer->date_upd, true);

        if ($customerStats['last_visit']) {
            $lastVisitDate = Tools::displayDate($customerStats['last_visit'], true);
        } else {
            $lastVisitDate = $this->translator->trans('Never', [], 'Admin.Global');
        }

        $customerShop = new Shop($customer->id_shop);
        $customerLanguage = new Language($customer->id_lang);
        $customerSubscriptions = new Subscriptions($customer->newsletter,$customer->optin);

        return [
            $customer->firstname,
            $customer->lastname,
            $customer->email,
            $customer->isGuest(),
            $socialTitle,
            $birthday,
            $registrationDate,
            $lastUpdateDate,
            $lastVisitDate,
            $customerShop->name,
            $customerLanguage->name,
            $customerSubscriptions,
            $customer->active
        ];
    }

    /**
     * @param Customer $customer
     *
     * @return OrdersInformation
     */
    private function getCustomerOrders(Customer $customer)
    {
        $validOrders = [];
        $invalidOrders = [];

        // Get orders for this customer
        $orders = Order::getCustomerOrders($customer->id, true);
        $ordersTotal = 0;

        foreach ($orders as $order) {
            $order['total_paid_tax_incl_not_formated'] = $order['total_paid_tax_incl'];
            $order['total_paid_tax_incl'] = $this->locale->formatPrice(
                $order['total_paid_tax_incl'],
                Currency::getIsoCodeById((int) $order['id_currency'])
            );

            if (!isset($order['order_state'])) {
                $order['order_state'] = $this->translator->trans(
                    'There is no status defined for this order.',
                    [],
                    'Admin.Orderscustomers.Notification'
                );
            }

            $customerOrderInformation = new OrderInformation(
                (int) $order['id_order'],
                Tools::displayDate($order['date_add']),
                $order['payment'],
                $order['order_state'],
                (int) $order['nb_products'],
                $order['total_paid_tax_incl']
            );

            if ($order['valid']) {
                $validOrders[] = $customerOrderInformation;
                $ordersTotal += $order['total_paid_tax_incl_not_formated'] / $order['conversion_rate'];
            } else {
                $invalidOrders[] = $customerOrderInformation;
            }
        }

        return new OrdersInformation(
            $this->locale->formatPrice($ordersTotal, $this->context->getContext()->currency->iso_code),
            $validOrders,
            $invalidOrders
        );
    }

    /**
     * @param Customer $customer
     *
     * @return CartInformation[]
     */
    private function getCustomerCarts(Customer $customer)
    {
        $cartList = $this->cartRepository->findCartsByCustomerId(new CustomerId($customer->id));

        $customerCarts = [];

        foreach ($cartList as $row) {
            $cart = new Cart((int) $row['id_cart']);

            $customerCarts[] = new CartInformation(
                sprintf('%06d', $row['id_cart']),
                Tools::displayDate($row['date_add'], true),
                $this->locale->formatPrice($cart->getOrderTotal(true), $row['currency_iso_code']),
                $row['carrier_name']
            );
        }

        return $customerCarts;
    }

    /**
     * @param Customer $customer
     *
     * @return ProductsInformation
     */
    private function getCustomerProducts(Customer $customer)
    {
        $boughtProducts = [];
        $notOrderedProducts = [];

        $products = $customer->getBoughtProducts();
        foreach ($products as $product) {
            $boughtProducts[] = new BoughtProductInformation(
                (int) $product['id_order'],
                Tools::displayDate($product['date_add'], false),
                $product['product_name'],
                $product['product_quantity']
            );
        }

        $notOrderedProducts = $this->cartRepository->findProductsCartsNotOrderedByCustomerId(new CustomerId($customer->id));

        foreach ($notOrderedProducts as $productData) {
            $product = new Product(
                $productData['id_product'],
                false,
                $this->context->language->id,
                $productData['id_shop']
            );

            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            $productUrl = $this->link->getProductLink(
                $product->id,
                $product->link_rewrite,
                Category::getLinkRewrite($product->id_category_default, $this->context->language->id),
                null,
                null,
                $productData['cp_id_shop']
            );

            $viewedProducts[] = new ViewedProductInformation(
                (int) $product->id,
                $product->name,
                $productUrl
            );
        }

        return new ProductsInformation(
            $boughtProducts,
            $viewedProducts
        );
    }

    /**
     * @param Customer $customer
     *
     * @return MessageInformation[]
     */
    private function getCustomerMessages(Customer $customer)
    {
        $customerMessages = [];
        $messages = CustomerThread::getCustomerMessages($customer->id);

        $messageStatuses = [
            'open' => $this->translator->trans('Open', [], 'Admin.Orderscustomers.Feature'),
            'closed' => $this->translator->trans('Closed', [], 'Admin.Orderscustomers.Feature'),
            'pending1' => $this->translator->trans('Pending 1', [], 'Admin.Orderscustomers.Feature'),
            'pending2' => $this->translator->trans('Pending 2', [], 'Admin.Orderscustomers.Feature'),
        ];

        foreach ($messages as $message) {
            $status = isset($messageStatuses[$message['status']]) ?
                $messageStatuses[$message['status']] :
                $message['status'];

            $currentMessage = substr(
                strip_tags(
                    html_entity_decode($message['message'], ENT_NOQUOTES, 'UTF-8')
                ),
                0,
                75
            );

            $customerMessages[] = new MessageInformation(
                (int) $message['id_customer_thread'],
                $currentMessage,
                $status,
                Tools::displayDate($message['date_add'], true)
            );
        }

        return $customerMessages;
    }

    /**
     * @param Customer $customer
     *
     * @return DiscountInformation[]
     */
    private function getCustomerDiscounts(Customer $customer)
    {
        $discounts = CartRule::getAllCustomerCartRules($customer->id);

        $customerDiscounts = [];

        foreach ($discounts as $discount) {
            $availableQuantity = $discount['quantity'] > 0 ? (int) $discount['quantity_for_user'] : 0;

            $customerDiscounts[] = new DiscountInformation(
                (int) $discount['id_cart_rule'],
                $discount['code'],
                $discount['name'],
                (bool) $discount['active'],
                $availableQuantity
            );
        }

        return $customerDiscounts;
    }

    /**
     * @param Customer $customer
     *
     * @return SentEmailInformation[]
     */
    private function getLastEmailsSentToCustomer(Customer $customer)
    {
        $emails = $customer->getLastEmails();
        $customerEmails = [];

        foreach ($emails as $email) {
            $customerEmails[] = new SentEmailInformation(
                Tools::displayDate($email['date_add'], true),
                $email['language'],
                $email['subject'],
                $email['template']
            );
        }

        return $customerEmails;
    }

    /**
     * @param Customer $customer
     *
     * @return LastConnectionInformation[]
     */
    private function getLastCustomerConnections(Customer $customer)
    {
        $connections = $customer->getLastConnections();
        $lastConnections = [];

        if (!is_array($connections)) {
            $connections = [];
        }

        foreach ($connections as $connection) {
            $httpReferer = $connection['http_referer'] ?
                preg_replace('/^www./', '', parse_url($connection['http_referer'], PHP_URL_HOST)) :
                $this->translator->trans('Direct link', [], 'Admin.Orderscustomers.Notification');

            $lastConnections[] = new LastConnectionInformation(
                $connection['id_connections'],
                Tools::displayDate($connection['date_add']),
                $connection['pages'],
                $connection['time'],
                $httpReferer,
                $connection['ipaddress']
            );
        }

        return $lastConnections;
    }

    /**
     * @param Customer $customer
     *
     * @return GroupInformation[]
     */
    private function getCustomerGroups(Customer $customer)
    {
        $groups = $customer->getGroups();
        $customerGroups = [];

        foreach ($groups as $groupId) {
            $group = new Group($groupId);

            $customerGroups[] = new GroupInformation(
                (int) $group->id,
                $group->name[$this->context->language->id]
            );
        }

        return $customerGroups;
    }

    /**
     * @param Customer $customer
     *
     * @return AddressInformation[]
     */
    private function getCustomerAddresses(Customer $customer)
    {
        $addresses = $customer->getAddresses($this->context->language->id);
        $customerAddresses = [];

        foreach ($addresses as $address) {
            $company = $address['company'] ?: '--';
            $fullAddress = sprintf(
                '%s %s %s %s',
                $address['address1'],
                $address['address2'] ?: '',
                $address['postcode'],
                $address['city']
            );

            $customerAddresses[] = new AddressInformation(
                (int) $address['id_address'],
                $company,
                sprintf('%s %s', $address['firstname'], $address['lastname']),
                $fullAddress,
                $address['country'],
                (string) $address['phone'],
                (string) $address['phone_mobile']
            );
        }

        return $customerAddresses;
    }
}
