<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2016 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

// require _PS_MODULE_DIR_.'psgdpr/psgdpr.php';

class HTMLTemplatePSGDPRModule extends HTMLTemplate
{
    public $personalData;
    public $available_in_your_account = false;

    /**
     * @param array $personalData
     * @param $smarty
     */
    public function __construct($personalData, $smarty)
    {
        $this->personalData = $personalData;
        $this->smarty = $smarty;
        $this->context = Context::getContext();

        $firstname = $this->personalData['prestashopData']['customerInfo']['firstname'];
        $lastname = $this->personalData['prestashopData']['customerInfo']['lastname'];
        $this->title = $firstname.' '.$lastname;
        $this->date = Tools::displayDate(date("Y-m-d H:i:s"));

        $this->shop = new Shop((int)Context::getContext()->shop->id);
    }

    /**
     * Returns the template's HTML header
     *
     * @return string HTML header
     */
    // public function getHeader()
    // {
    //     $this->assignCommonHeaderData();

    //     $this->smarty->assign(array(
    //         'header' => $this->l('PERSONAL DATA'),
    //     ));

    //     return $this->smarty->fetch($this->getTemplate('header'));
    // }

    /**
     * Returns the template's HTML footer
     *
     * @return string HTML footer
     */
    public function getFooter()
    {
        $shop_address = $this->getShopAddress();
        $this->smarty->assign(array(
            'available_in_your_account' => $this->available_in_your_account,
            'shop_address' => $shop_address,
            'shop_fax' => Configuration::get('PS_SHOP_FAX'),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
            'shop_details' => Configuration::get('PS_SHOP_DETAILS'),
            'free_text' => ''
        ));
        return $this->smarty->fetch($this->getTemplate('footer'));
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     */
    public function getContent()
    {
        $this->smarty->assign(array(
            'customerInfo' => $this->personalData['prestashopData']['customerInfo'],
            'addresses' => $this->personalData['prestashopData']['addresses'],
            'orders' => $this->personalData['prestashopData']['orders'],
            'carts' => $this->personalData['prestashopData']['carts'],
            'messages' => $this->personalData['prestashopData']['messages'],
            'connections' => $this->personalData['prestashopData']['connections'],
            'modules' => $this->personalData['modulesData'],
        ));

        $tpls = array(
            'style_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.style-tab')),
            'generalInfo_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.generalInfo-tab')),
            'orders_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.orders-tab')),
            'carts_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.carts-tab')),
            'addresses_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.addresses-tab')),
            'messages_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.messages-tab')),
            'connections_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.connections-tab')),
            'modules_tab' => $this->smarty->fetch($this->getGDPRTemplate('personalData.modules-tab')),
        );
        $this->smarty->assign($tpls);

        return $this->smarty->fetch($this->getGDPRTemplate('personalData'));
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        return 'personalData-'.date("Y-m-d").'.pdf';
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return 'personalData-'.date("Y-m-d").'.pdf';
    }

    /**
     * If the template is not present in the theme directory, it will return the default template
     * in _PS_PDF_DIR_ directory
     *
     * @param $template_name
     *
     * @return string
     */
    protected function getGDPRTemplate($template_name)
    {
        $template = rtrim(_PS_MODULE_DIR_.'psgdpr/views/templates/front/pdf/', DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$template_name.'.tpl';

        return $template;
    }
}
