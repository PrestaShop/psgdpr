<?php

namespace PrestaShop\Module\Psgdpr\Controller\Admin;

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController;
use Context;
use Psgdpr;
use Tools;
use Language;
use Configuration;
use CMS;
use Hook;
use Module;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsent;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsentLang;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use PrestaShop\Module\Psgdpr\Exception\CannotLoadAssetsException;
use PrestaShop\Module\Psgdpr\Repository\ConsentRepository;
use PrestaShopBundle\Entity\Lang;

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
     * @var ConsentRepository
     */
    private $consentRepository;

    /**
     * PsxlegalassistantController constructor.
     *
     * @param Psgdpr $module
     * @param Context $context
     */
    public function __construct(
        Psgdpr $module,
        Context $context,
        LoggerService $loggerService,
        ConsentRepository $consentRepository
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->loggerService = $loggerService;
        $this->consentRepository = $consentRepository;
    }

    public function renderApp()
    {
        $this->postProcess();

        $moduleAdminLink = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->module->name]);

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $this->getRegisteredModules();
        $moduleList = $this->loadRegisteredModules();

        /** @var Router $router */
        $router = $this->get('router');

        $apiController = $router->generate('psgdpr_api');
        $invoiceController = $router->generate('psgdpr_download_invoices');

        $isoLang = Language::getIsoById($id_lang);

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

        // $this->setTemplate($this->module->getPathUri() . 'menu.tpl');

        return $this->render(
            '@Modules/psgdpr/views/templates/admin/menu.html.twig',
            [
                'assets' => $this->getAssets(),
                'customer_link' => $this->context->link->getAdminLink('AdminCustomers', true, [], ['viewcustomer' => '', 'id_customer' => 0]),
                'module_name' => $this->module->name,
                'id_shop' => $id_shop,
                'module_version' => $this->module->version,
                'moduleAdminLink' => $moduleAdminLink,
                'id_lang' => $id_lang,
                'psgdpr_adminController' => $apiController,
                'adminControllerInvoices' => $invoiceController,
                // 'faq' => $this->loadFaq(),
                'doc' => $this->getReadmeByLang($isoLang),
                'youtubeLink' => $this->getYoutubeLinkByLang($isoLang),
                'cmspage' => $CMS,
                'cmsConfPage' => $cmsConfPage,
                'orderLink' => $orderLink,
                'cartLink' => $cartLink,
                'module_display' => $this->module->displayName,
                'module_path' => $this->module->getPathUri(),
                'logo_path' => $this->module->getPathUri() . 'logo.png',
                'img_path' => $this->module->getPathUri() . 'views/img/',
                'modules' => $moduleList,
                'logs' => $this->loggerService->getLogs(),
                'languages' => $this->context->controller->getLanguages(),
                'defaultFormLanguage' => (int) $this->context->employee->id_lang,
                'currentPage' => $currentPage,
                'ps_base_dir' => Tools::getHttpHost(true),
                'ps_version' => _PS_VERSION_,
                'isPs17' => $this->module->psVersionIs17,
            ]
        );
    }

    /**
     * Triggered when form is submitted
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $this->submitDataConsent();
    }

    /**
     * Handle post values send by the submitted form
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

            $moduleList = $this->consentRepository->findAllRegisteredModules();

            foreach($moduleList as $module) {
                $psgdprConsent = new PsgdprConsent();
                $psgdprConsent->setId($module['id_gdpr_consent']);
                $psgdprConsent->setActive(Tools::getValue('psgdpr_switch_registered_module_' . $module['id_module']));

                foreach ($languages as $lang) {
                    $psgdprConsentLang = new PsgdprConsentLang();

                    $psgdprConsentLang->setLang($lang['id_lang']);
                    $psgdprConsentLang->setMessage(Tools::getValue('psgdpr_registered_module_' . $module['id_module'] . '_' . $lang['id_lang']));

                    $psgdprConsent->addConsentLang($psgdprConsentLang);
                }

                $this->consentRepository->addConsent($psgdprConsentLang);
            }

            $this->output .= $this->module->displayConfirmation($this->module->getTranslator()->trans('Saved with success !', [], 'Modules.Psgdpr.General'));
        }
    }

    /**
     * load all the registered modules and add the displayname and logopath in each module
     *
     * @return array
     */
    public function loadRegisteredModules(): array
    {
        $languages = Language::getLanguages(false);
        $moduleList = $this->consentRepository->findAllRegisteredModules();

        if (count($moduleList) < 1) {
            return [];
        }

        $physicalUri = $this->context->shop->physical_uri;

        return array_map(function ($module) use ($languages, $physicalUri) {
            /** @var Module|false $currentModuleInfos */
            $currentModuleInfos = Module::getInstanceById($module['id_module']);

            if ($currentModuleInfos === false) {
                return;
            }

            $module['active'] = $this->consentRepository->findModuleConsentIsActive($module['id_module']);

            foreach ($languages as $lang) {
                $module['message'][$lang['id_lang']] = $this->consentRepository->findModuleConsentMessage($module['id_module'], $lang['id_lang']);
            }

            $module['displayName'] = $currentModuleInfos->displayName;
            $module['logoPath'] = Tools::getHttpHost(true) . $physicalUri . 'modules/' . $currentModuleInfos->name . '/logo.png';

            return $module;
        }, $moduleList);
    }

    /**
     * Get a module list of module trying to register to GDPR
     *
     * @return void
     */
    public function getRegisteredModules()
    {
        $modulesRegistered = Hook::getHookModuleExecList('registerGDPRConsent');

        if (empty($modulesRegistered)) {
            return;
        }

        foreach ($modulesRegistered as $module) {
            if ($module['id_module'] != $this->module->id) {
                $this->addModuleConsent($module);
            }
        }
    }

    /**
     * register the module in database
     *
     * @param array $module module to register in database
     *
     * @return void
     */
    public function addModuleConsent(array $module)
    {
        // Get all languages vie the Lang repository
        $langRepository = $this->container->get('prestashop.core.admin.lang.repository');
        $languages = $langRepository->findAll();

        $shopId = $this->context->shop->id;
        $consentExistForModule = $this->consentRepository->findModuleConsentExist($module['id_module'], $shopId);

        if (true === $consentExistForModule) {
            return;
        }

        $psgdprConsent = new PsgdprConsent();
        $psgdprConsent->setModuleId($module['id_module']);
        $psgdprConsent->setActive(true);

            /** @var Lang $language */
        foreach ($languages as $language) {
            $psgdprConsentLang = new PsgdprConsentLang();
            $psgdprConsentLang->setLang($language);
            $psgdprConsentLang->setMessage('Enim quis fugiat consequat elit minim nisi eu occaecat occaecat deserunt aliquip nisi ex deserunt.');
            $psgdprConsentLang->setShopId($shopId);
            $psgdprConsent->addConsentLang($psgdprConsentLang);
        }

        $this->consentRepository->addConsent($psgdprConsent);
    }

    /**
     * Retrieve the readme file by language
     *
     * @param string $isoLang
     *
     * @return string
     */
    private function getReadmeByLang($isoLang): string
    {
        switch ($isoLang) {
            case 'fr':
                $docPathUri = $this->module->getPathUri() . 'docs/readme_fr.pdf';
                break;
            default:
                $docPathUri = $this->module->getPathUri() . 'docs/readme_en.pdf';
                break;
        }

        return $docPathUri;
    }

    /**
     * Retrieve the youtube link by language
     *
     * @param string $isoLang
     *
     * @return string
     */
    private function getYoutubeLinkByLang($isoLang): string
    {
        switch ($isoLang) {
            case 'fr':
                $youtubeLink = 'https://www.youtube.com/watch?v=a8NctC1hXUQ&feature=youtu.be';
                break;
            default:
                $youtubeLink = 'https://www.youtube.com/watch?v=xen38Xl5gRY&feature=youtu.be';
                break;
        }

        return $youtubeLink;
    }

    /**
     * Load js and css assets
     *
     * @return bool
     */
    private function getAssets(): array
    {
        $cssFiles = [
            'lib/fontawesome-all.min.css',
            'lib/datatables.min.css',
            'faq.css',
            'back.css',
        ];

        $jsFiles = [
            'lib/vue.min.js',
            'lib/datatables.min.js',
            'lib/sweetalert.min.js',
            'lib/jszip.min.js',
            'lib/pdfmake.min.js',
            'lib/vfs_fonts.js',
            'lib/buttons.html5.min.js',
            'faq.js',
            'menu.js',
            'back.js',
        ];

        // $prefix = _PS_ROOT_DIR_ . $this->module->getpathuri() . 'views/';
        $prefix = $this->module->getpathuri() . 'views/';
        $jsFiles = preg_filter('/^/', $prefix . 'js/', $jsFiles);
        $cssFiles = preg_filter('/^/', $prefix . 'css/', $cssFiles);

        return [
            'css' => $cssFiles,
            'js' => $jsFiles
        ];

        // try {
        //     foreach ($cssFiles as $file) {
        //         // dump(_PS_ROOT_DIR_ . $this->module->getPathUri() . 'views/css/' . $file);
        //         $this->context->controller->addCSS(_PS_ROOT_DIR_ . $this->module->getPathUri() . 'views/css/' . $file);
        //     }

        //     foreach ($jsFiles as $file) {
        //         $this->context->controller->addJS(_PS_ROOT_DIR_ . $this->module->getPathUri() . 'views/js/' . $file);
        //     }

        //     $this->context->controller->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
        //     $this->context->controller->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
        // } catch (\Exception $exception) {
        //     return false;
        // }

        // dump($this->context->controller);
        // die;

        // return true;
    }

}
