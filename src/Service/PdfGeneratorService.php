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

// require _PS_MODULE_DIR_.'psgdpr/psgdpr.php';

namespace PrestaShop\Module\Psgdpr\Service;

use Configuration;
use Context;
use HTMLTemplate;
use Shop;
use Smarty;
use Tools;

class PdfGeneratorService extends HTMLTemplate
{
    /**
     * @var array
     */
    public $customerData;

    /**
     * @var bool
     */
    public $available_in_your_account = false;

    /**
     * @var Context
     */
    public $context;

    /**
     * @param array $customerData
     * @param Smarty $smarty
     */
    public function __construct($customerData, Smarty $smarty)
    {
        $this->customerData = $customerData;
        $this->smarty = $smarty;
        $this->context = Context::getContext();

        $firstname = $this->customerData['personalinformations']['data'][0]['firstname'];
        $lastname = $this->customerData['personalinformations']['data'][0]['lastname'];
        $this->title = "{$firstname} {$lastname}";
        $this->date = Tools::displayDate(date('Y-m-d H:i:s'));

        $this->shop = new Shop((int) Context::getContext()->shop->id);
    }

    /**
     * Returns the template's HTML footer
     *
     * @return string HTML footer
     */
    public function getFooter()
    {
        $shop_address = $this->getShopAddress();
        $this->smarty->assign([
            'available_in_your_account' => $this->available_in_your_account,
            'shop_address' => $shop_address,
            'shop_fax' => Configuration::get('PS_SHOP_FAX'),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
            'shop_details' => Configuration::get('PS_SHOP_DETAILS'),
            'free_text' => '',
        ]);

        return $this->smarty->fetch($this->getTemplate('footer'));
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $ordersList = $this->customerData['orders'];
        $productsOrderedList = $this->customerData['productsOrdered'];
        $cartsList = $this->customerData['carts'];
        $productsCartList = $this->customerData['productsInCart'];

        foreach ($ordersList['data'] as $index => $order) {
            $ordersList['data'][$index]['products'] = [];

            foreach ($productsOrderedList['data'] as $product) {
                if ($product['orderReference'] == $order['reference']) {
                    $ordersList['data'][$index]['products'][] = $product;
                }
            }
        }

        foreach ($cartsList['data'] as $index => $cart) {
            $cartsList['data'][$index]['products'] = [];

            foreach ($productsCartList['data'] as $product) {
                if ($product['cartId'] == $cart['cartId']) {
                    $cartsList['data'][$index]['products'][] = $product;
                }
            }
        }

        $this->smarty->assign([
            'customerInfo' => [
                'headers' => $this->customerData['personalinformations']['headers'],
                'data' => array_map(function ($infos) {
                    return array_values($infos);
                }, $this->customerData['personalinformations']['data']),
            ],
            'addresses' => $this->customerData['addresses'],
            'orders' => $ordersList,
            'carts' => $cartsList,
            'messages' => $this->customerData['messages'],
            'lastConnections' => $this->customerData['lastConnections'],
            'discounts' => $this->customerData['discounts'],
            'lastSentEmails' => $this->customerData['lastSentEmails'],
            'groups' => $this->customerData['groups'],
            'modules' => $this->customerData['modules'],
        ]);

        // Generate templates after, to be able to reuse data above
        $this->smarty->assign([
            'style' => $this->smarty->fetch($this->getGDPRTemplate('style')),
            'general_informations_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/general_informations')),
            'addresses_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/addresses')),
            'orders_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/orders')),
            'carts_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/carts')),
            'messages_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/messages')),
            'last_connections_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/last_connections')),
            'discounts_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/discounts')),
            'last_sent_emails_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/last_sent_emails')),
            'groups_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/groups')),
            'modules_section' => $this->smarty->fetch($this->getGDPRTemplate('sections/modules')),
        ]);

        return $this->smarty->fetch($this->getGDPRTemplate('personal_data'));
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        return 'personal-data-' . date('Y-m-d') . '.pdf';
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'personal-data-' . date('Y-m-d') . '.pdf';
    }

    /**
     * If the template is not present in the theme directory, it will return the default template
     * in _PS_PDF_DIR_ directory
     *
     * @param string $template_name
     *
     * @return string
     */
    protected function getGDPRTemplate($template_name)
    {
        return _PS_MODULE_DIR_ . 'psgdpr/views/templates/front/pdf/' . $template_name . '.tpl';
    }
}
