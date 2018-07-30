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

class psgdprgdprModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $this->display_column_right = false;
        $this->display_column_left = false;
        $context = Context::getContext();
        if (empty($context->customer->id)) {
            Tools::redirect('index.php');
        }

        parent::initContent();

        $ps_version = (bool)version_compare(_PS_VERSION_, '1.7', '>=');

        $params = array(
            'psgdpr_token' => sha1($context->customer->secure_key),
        );

        $this->context->smarty->assign(array(
            'psgdpr_contactUrl' => $this->context->link->getPageLink('contact', true, $this->context->language->id),
            'psgdpr_front_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'gdpr', $params, true),
            'psgdpr_csv_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'ExportDataToCsv', $params, true),
            'psgdpr_pdf_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'ExportDataToPdf', $params, true),
            'psgdpr_ps_version' => (bool)version_compare(_PS_VERSION_, '1.7', '>='),
            'psgdpr_id_customer' => Context::getContext()->customer->id,
        ));

        $this->context->smarty->tpl_vars['page']->value['body_classes']['page-customer-account'] =  true;

        $this->setTemplate('module:psgdpr/views/templates/front/customerPersonalData.tpl');
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        return $breadcrumb;
    }


    public function setMedia()
    {
        $js_path = $this->module->getPathUri().'/views/js/';
        $css_path = $this->module->getPathUri().'/views/css/';

        parent::setMedia();
        $this->context->controller->addJS($js_path.'front.js');
        $this->context->controller->addCSS($css_path.'customerPersonalData.css');
    }
}
