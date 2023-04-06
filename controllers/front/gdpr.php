<?php

use Symfony\Component\Translation\Exception\InvalidArgumentException;

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
class psgdprgdprModuleFrontController extends ModuleFrontController
{
    /**
     * @var Psgdpr
     */
    public $module;

    /**
     * @var bool
     */
    public $display_column_right;
    /**
     * @var bool
     */
    public $display_column_left;

    /**
     * @throws PrestaShopException
     */
    public function initContent()
    {
        $this->display_column_right = false;
        $this->display_column_left = false;
        $context = Context::getContext();
        if (empty($context->customer->id)) {
            Tools::redirect('index.php');
        }

        parent::initContent();

        $params = [
            'token' => sha1($context->customer->secure_key),
        ];

        $this->context->smarty->assign([
            'psgdpr_contactUrl' => $this->context->link->getPageLink('contact', true, $this->context->language->id),
            'psgdpr_front_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'gdpr', $params, true),
            'psgdpr_csv_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'ExportCustomerData', array_merge(['type' => 'csv'], $params), true),
            'psgdpr_pdf_controller' => Context::getContext()->link->getModuleLink('psgdpr', 'ExportCustomerData', array_merge(['type' => 'pdf'], $params), true),
            'psgdpr_ps_version' => (bool) version_compare(_PS_VERSION_, '1.7', '>='),
            'psgdpr_id_customer' => Context::getContext()->customer->id,
        ]);

        $this->context->smarty->tpl_vars['page']->value['body_classes']['page-customer-account'] = true;

        $this->setTemplate('module:psgdpr/views/templates/front/account_gdpr_page.tpl');
    }

    /**
     * Get breadcrumb links
     *
     * @return array
     *
     * @throws InvalidArgumentException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();
        $breadcrumb['links'][] = $this->addMyAccountToBreadcrumb();
        $breadcrumb['links'][] = [
           'title' => $this->trans('GDPR - Personal data', [], 'Modules.Psgdpr.Shop'),
           'url' => $this->context->link->getModuleLink($this->module->name, 'gdpr', [], true),
        ];

        return $breadcrumb;
    }

    /**
     * Set media of module
     *
     * @return bool
     */
    public function setMedia(): bool
    {
        $js_path = $this->module->getPathUri() . '/views/js/';
        $css_path = $this->module->getPathUri() . '/views/css/';

        parent::setMedia();

        $this->context->controller->addJS($js_path . 'front.js');
        $this->context->controller->addCSS($css_path . 'account-gdpr-page.css');

        return true;
    }
}
