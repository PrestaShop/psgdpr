<?php

namespace PrestaShop\Module\Psgdpr\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Context;
use Psgdpr;
use Tools;
use Language;
use Configuration;
use CMS;
use PrestaShop\Module\Psgdpr\Service\LoggerService;

class PsgdprController extends FrameworkBundleAdminController
{
    /**
     * @var Psgdpr
     */
    private $module;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var LoggerService
     */
    private $loggerService;

    /**
     * PsxlegalassistantController constructor.
     *
     * @param Psgdpr $module
     * @param Context $context
     */
    public function __construct(
        Psgdpr $module,
        Context $context,
        LoggerService $loggerService
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->loggerService = $loggerService;
    }

    public function renderApp()
    {
        $moduleAdminLink = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->module->name]);

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $this->postProcess();
        $this->loadAssets();

        // $this->getRegisteredModules(); // register modules which trying to register to GDPR in database
        // $module_list = $this->loadRegisteredModules(); // return module registered in database
        $module_list = [];

        // controller url
        $adminController = $this->context->link->getAdminLink($this->module->adminControllers['adminAjax']);
        $adminControllerInvoices = $this->context->link->getAdminLink($this->module->adminControllers['adminDownloadInvoices']);

        $iso_lang = Language::getIsoById($id_lang);
        // get readme
        switch ($iso_lang) {
            case 'fr':
                $doc = $this->module->getPathUri() . 'docs/readme_fr.pdf';
                break;
            default:
                $doc = $this->module->getPathUri() . 'docs/readme_en.pdf';
                break;
        }

        // youtube video
        switch ($iso_lang) {
            case 'fr':
                $youtubeLink = 'https://www.youtube.com/watch?v=a8NctC1hXUQ&feature=youtu.be';
                break;
            default:
                $youtubeLink = 'https://www.youtube.com/watch?v=xen38Xl5gRY&feature=youtu.be';
                break;
        }

        // order page link
        $orderLink = $this->context->link->getAdminLink('AdminOrders');
        // cart page link
        $cartLink = $this->context->link->getAdminLink('AdminCarts');

        // get current page
        $currentPage = 'getStarted';
        $page = Tools::getValue('page');
        if (!empty($page)) {
            $currentPage = Tools::getValue('page');
        }

        $CMS = CMS::getCMSPages($id_lang, null, true, $id_shop);
        $cmsConfPage = $this->context->link->getAdminLink('AdminCmsContent');

        $tmp = [];
        $languages = Language::getLanguages(false);

        // assign data consent settings to smarty
        foreach ($this->module->settings_data_consent as $index => $value) {
            if ($value === 'psgdpr_creation_form' || $value === 'psgdpr_customer_form') {
                foreach ($languages as $lang) {
                    $tmp[$value][$lang['id_lang']] = Configuration::get(Tools::strtoupper($value), $lang['id_lang']);
                    $this->context->smarty->assign($index, $tmp[$value]);
                }
            } else {
                $tmp[$value] = Configuration::get(Tools::strtoupper($value));
                $this->context->smarty->assign($index, $tmp[$value]);
            }
        }
        unset($tmp);

        return $this->render(
            '@Modules/psgdpr/views/templates/admin/menu.html.twig',
            [
                'customer_link' => $this->context->link->getAdminLink('AdminCustomers', true, [], ['viewcustomer' => '', 'id_customer' => 0]),
                'module_name' => $this->module->name,
                'id_shop' => $id_shop,
                'module_version' => $this->module->version,
                'moduleAdminLink' => $moduleAdminLink,
                'id_lang' => $id_lang,
                'psgdpr_adminController' => $adminController,
                'adminControllerInvoices' => $adminControllerInvoices,
                // 'faq' => $this->loadFaq(),
                'doc' => $doc,
                'youtubeLink' => $youtubeLink,
                'cmspage' => $CMS,
                'cmsConfPage' => $cmsConfPage,
                'orderLink' => $orderLink,
                'cartLink' => $cartLink,
                'module_display' => $this->module->displayName,
                'module_path' => $this->module->getPathUri(),
                'logo_path' => $this->module->getPathUri() . 'logo.png',
                'img_path' => $this->module->getPathUri() . 'views/img/',
                'modules' => $module_list,
                'logs' => $this->loggerService->getLogs(),
                'languages' => $this->context->controller->getLanguages(),
                'defaultFormLanguage' => (int) $this->context->employee->id_lang,
                'currentPage' => $currentPage,
                'ps_base_dir' => Tools::getHttpHost(true),
                'ps_version' => _PS_VERSION_,
                'isPs17' => $this->module->ps_version,
            ]
        );
    }

        /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $this->submitDataConsent();
    }

    /**
     * save data consent tab
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function submitDataConsent()
    {
        if (Tools::isSubmit('submitDataConsent')) {
            $languages = Language::getLanguages(false);

            foreach ($this->module->settings_data_consent as $value) {
                if ($value === 'psgdpr_creation_form' || $value === 'psgdpr_customer_form') {
                    $values = [];
                    foreach ($languages as $lang) {
                        $values[$value][$lang['id_lang']] = Tools::getValue($value . '_' . $lang['id_lang']);
                    }
                    Configuration::updateValue(Tools::strtoupper($value), $values[$value], true);
                } else {
                    Configuration::updateValue(Tools::strtoupper($value), Tools::getValue($value));
                }
            }

            $modules = GDPRConsent::getAllRegisteredModules();
            foreach ($modules as $module) {
                $GDPRConsent = new GDPRConsent($module['id_gdpr_consent']);
                foreach ($languages as $lang) {
                    $GDPRConsent->message[$lang['id_lang']] = Tools::getValue('psgdpr_registered_module_' . $module['id_module'] . '_' . $lang['id_lang']);
                }
                $GDPRConsent->active = Tools::getValue('psgdpr_switch_registered_module_' . $module['id_module']);
                $GDPRConsent->date_upd = date('Y-m-d H:i:s');
                $GDPRConsent->save();
            }

            $this->output .= $this->module->displayConfirmation($this->module->getTranslator()->trans('Saved with success !', [], 'Modules.Psgdpr.General'));
        }
    }

    private function loadAssets()
    {
        $cssFiles = [
            'fontawesome-all.min.css',
            'datatables.min.css',
            'faq.css',
            'menu.css',
            'back.css',
            $this->module->name . '.css',
        ];

        $jssFiles = [
            'vue.min.js',
            'datatables.min.js',
            'faq.js',
            'menu.js',
            'back.js',
            'sweetalert.min.js',
            _PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
            _PS_JS_DIR_ . 'admin/tinymce.inc.js',
            'jszip.min.js',
            'pdfmake.min.js',
            'vfs_fonts.js',
            'buttons.html5.min.js',
        ];

        try {
            foreach ($cssFiles as $file) {
                $this->context->controller->addCSS($this->module->getPathUri() . $file);
            }

            foreach ($jssFiles as $file) {
                $this->context->controller->addJS($this->module->getPathUri() . $file);
            }

            $this->context->controller->addJS($this->module->getPathUri() . $file);
            $this->context->controller->addJS($this->module->getPathUri() . $file);
        } catch (\Throwable $th) {
            return false;
        }

        return true;
    }

}
