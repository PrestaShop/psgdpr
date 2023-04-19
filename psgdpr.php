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

use Doctrine\ORM\EntityManager;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsent;
use PrestaShop\Module\Psgdpr\Entity\PsgdprConsentLang;
use PrestaShop\Module\Psgdpr\Repository\ConsentRepository;
use PrestaShop\Module\Psgdpr\Repository\LoggerRepository;
use PrestaShop\Module\Psgdpr\Service\LoggerService;
use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use PrestaShopBundle\Entity\Lang;
use PrestaShopBundle\Entity\Repository\LangRepository;
use PrestaShopBundle\Service\Routing\Router;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Psgdpr extends Module
{
    const SQL_QUERY_TYPE_INSTALL = 'install';
    const SQL_QUERY_TYPE_UNINSTALL = 'uninstall';

    /**
     * @var array
     */
    public $settings_data_consent = [
        'switchCreationForm' => 'psgdpr_creation_form_switch',
        'accountCreationForm' => 'psgdpr_creation_form',
        'switchCustomerForm' => 'psgdpr_customer_form_switch',
        'accountCustomerForm' => 'psgdpr_customer_form',
    ];

    /**
     * @var array
     */
    private $hooksUsedByModule = [
        'displayCustomerAccount',
        'displayGDPRConsent',
        'actionAdminControllerSetMedia',
        'additionalCustomerFormFields',
        'actionCustomerAccountAdd',
    ];

    private $presetMessageAccountCreation = [
        'en' => 'I agree to the terms and conditions and the privacy policy',
        'cb' => 'I agree to the terms and conditions and the privacy policy',
        'es' => 'Acepto las condiciones generales y la política de confidencialidad',
        'ag' => 'Acepto las condiciones generales y la política de confidencialidad',
        'br' => 'Acepto las condiciones generales y la política de confidencialidad',
        'mx' => 'Acepto las condiciones generales y la política de confidencialidad',
        'de' => 'Ich akzeptiere die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie',
        'qc' => 'J\'accepte les conditions générales et la politique de confidentialité',
        'fr' => 'J\'accepte les conditions générales et la politique de confidentialité',
        'it' => 'Accetto le condizioni generali e la politica di riservatezza',
        'nl' => 'Ik accepteer de Algemene voorwaarden en het vertrouwelijkheidsbeleid',
        'pl' => 'Akceptuję ogólne warunki użytkowania i politykę prywatności',
        'pt' => 'Aceito as condições gerais e a política de confidencialidade',
        'ru' => 'Я соглашаюсь на использование указанных в этой форме данных компанией xxxxx для (i) изучения моего запроса, (ii) ответа и, при необходимости, (iii) управления возможными договорными отношениями.',
    ];

    /**
     * @var string
     */
    private $output;

    public function __construct()
    {
        $this->name = 'psgdpr';
        $this->tab = 'administration';
        $this->version = '2.0.1';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '1001fe84b4dede19725b8826e32165b7';

        $this->bootstrap = true;

        parent::__construct();

        $this->output = '';

        $this->displayName = $this->trans('Official GDPR compliance', [], 'Modules.Psgdpr.Shop');
        $this->description = $this->trans('Make your store comply with the General Data Protection Regulation (GDPR).', [], 'Modules.Psgdpr.Shop');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Psgdpr.Shop');
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => _PS_VERSION_];
    }

    /**
     * Telling PrestaShop that this module is using the new translation system (XLF files)
     *
     * @return bool
     */
    public function isUsingNewTranslationSystem(): bool
    {
        return true;
    }

    /**
     * install()
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public function install(): bool
    {
        try {
            $languages = Language::getLanguages(false);
            $temp = [];

            foreach ($this->settings_data_consent as $value) {
                if ($value === 'psgdpr_creation_form') {
                    foreach ($languages as $lang) {
                        $temp[Tools::strtoupper($value)][$lang['id_lang']] = isset($this->presetMessageAccountCreation[$lang['iso_code']]) ?
                            $this->presetMessageAccountCreation[$lang['iso_code']] :
                            $this->presetMessageAccountCreation['en'];
                        Configuration::updateValue(Tools::strtoupper($value), $temp[Tools::strtoupper($value)], true);
                    }
                } elseif ($value === 'psgdpr_customer_form') {
                    foreach ($languages as $lang) {
                        $temp[Tools::strtoupper($value)][$lang['id_lang']] = isset($this->presetMessageAccountCreation[$lang['iso_code']]) ?
                            $this->presetMessageAccountCreation[$lang['iso_code']] :
                            $this->presetMessageAccountCreation['en'];
                        Configuration::updateValue(Tools::strtoupper($value), $temp[Tools::strtoupper($value)]);
                    }
                } else {
                    Configuration::updateValue(Tools::strtoupper($value), 1);
                }
            }

            parent::install();
            $this->registerHook($this->hooksUsedByModule);
            $this->executeQuerySql(self::SQL_QUERY_TYPE_UNINSTALL);
            $this->executeQuerySql(self::SQL_QUERY_TYPE_INSTALL);
        } catch (PrestaShopException $e) {
            /** @var LegacyLogger $legacyLogger */
            $legacyLogger = $this->get('prestashop.adapter.legacy.logger');
            $legacyLogger->error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->_errors[] = $this->trans('There was an error during install. Please contact us through Addons website. (for developers, consult shop logs)', [], 'Modules.Psgdpr.Shop');
        }

        return empty($this->_errors);
    }

    /**
     * uninstall()
     *
     * @return bool
     */
    public function uninstall(): bool
    {
        try {
            foreach ($this->settings_data_consent as $value) {
                Configuration::deleteByName($value);
            }

            parent::uninstall();
            $this->executeQuerySql(self::SQL_QUERY_TYPE_UNINSTALL);
        } catch (PrestaShopException $e) {
            /** @var LegacyLogger $legacyLogger */
            $legacyLogger = $this->get('prestashop.adapter.legacy.logger');

            $legacyLogger->error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->_errors[] = $this->_errors[] = $this->trans('There was an error during uninstall. Please contact us through Addons website. (for developers, consult shop logs)', [], 'Modules.Psgdpr.Shop');
        }

        return empty($this->_errors);
    }

    /**
     * @return array
     */
    public function loadFaq()
    {
        return [
            [
                'title' => $this->trans('Data accessibility', [], 'Modules.Psgdpr.Admin'),
                'blocks' => [
                    [
                        'question' => $this->trans('How can a customer retrieve all of their personal data?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans('From their customer account, a new tab called "My Personal Data" is available and your customer can retrieve all of their personal data collected by your store and installed modules, in PDF or CSV format.', [], 'Modules.Psgdpr.Admin'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Customer consent', [], 'Modules.Psgdpr.Admin'),
                'blocks' => [
                    [
                        'question' => $this->trans('There is no consent confirmation checkbox in the contact form. Isn\'t this a requirement?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans('No, it is not a requirement as the customer gives consent by clicking on the Submit message button. Only a message is required to give your customers more information about the use of personal data on your website. We are currently working on a new version of the contact form, it will be available really soon for your online store.', [], 'Modules.Psgdpr.Admin'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Data erasure', [], 'Modules.Psgdpr.Admin'),
                'blocks' => [
                    [
                        'question' => $this->trans('How can a customer ask for all of their personal data to be deleted?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans('The customer will send a message through the contact form for any rectification and erasure requests, justifying their request.', [], 'Modules.Psgdpr.Admin'),
                    ],
                    [
                        'question' => $this->trans('There is no "Remove Data" button in the customer account. Isn\'t this a requirement?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans('No, the "Remove Data" button in the customer account is not mandatory. For data erasure requests, your customers can request data removal only under certain circumstances, that is the reason why we decided not to include an automatic "Remove Data" button in their customer account.\n\nThey can, however, contact you anytime via your contact form, in this case, you can review their request and once you accept it, you will be able to remove their personal data directly from the configuration page of the Official GDPR Compliance module.', [], 'Modules.Psgdpr.Admin'),
                    ],
                    [
                        'question' => $this->trans('How to remove the personal data of a customer?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans("If the request is valid, from the Personal Data Management tab of this module, any customer can be found by typing the first few letters of their name or email address in the search bar.\nBefore deleting any data, we recommend downloading all the invoices of the involved customer. After deleting the data with the “Remove data” button, the customer’s orders can’t be legally deleted, they just won’t be linked to any account anymore. This allows you to keep precise statistics of your store.", [], 'Modules.Psgdpr.Admin'),
                    ],
                    [
                        'question' => $this->trans('After removing all personal data of a customer from my database, what will happen to their orders?', [], 'Modules.Psgdpr.Admin'),
                        'answer' => $this->trans("Due to other legal obligations, their orders will still be stored but they are no longer associated with the customer.\nOnly the name, shipping, and billing information must be kept in the order details page for legal reasons, invoicing, and accounting.\nAccording to the Rec.30;Art.7(1)(c)", [], 'Modules.Psgdpr.Admin'),
                    ],
                ],
            ],
        ];
    }

    /**
     * Load the configuration form
     *
     * @return string
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function getContent()
    {
        $this->loadAssets();
        $this->postProcess();

        /** @var Router $router */
        $router = $this->get('router');

        /** @var LoggerRepository $loggerRepository */
        $loggerRepository = $this->get('PrestaShop\Module\Psgdpr\Repository\LoggerRepository');

        $moduleAdminLink = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $this->getRegisteredModules();
        $moduleList = $this->loadRegisteredModules();

        $apiController = $router->generate('psgdpr_api_index');

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
        foreach ($this->settings_data_consent as $index => $value) {
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

        $this->context->smarty->assign([
            'customerLink' => $this->context->link->getAdminLink('AdminCustomers', true, [], ['viewcustomer' => '', 'id_customer' => 0]),
            'module_name' => $this->name,
            'id_shop' => $id_shop,
            'module_version' => $this->version,
            'moduleAdminLink' => $moduleAdminLink,
            'id_lang' => $id_lang,
            'api_controller' => $this->getAdminLinkWithoutToken($apiController),
            'admin_token' => $this->getTokenFromAdminLink($apiController),
            'faq' => $this->loadFaq(),
            'doc' => $this->getReadmeByLang($isoLang),
            'youtubeLink' => $this->getYoutubeLinkByLang($isoLang),
            'cmspage' => $CMS,
            'cmsConfPage' => $cmsConfPage,
            'orderLink' => $orderLink,
            'cartLink' => $cartLink,
            'module_display' => $this->displayName,
            'module_path' => $this->getPathUri(),
            'logo_path' => $this->getPathUri() . 'logo.png',
            'img_path' => $this->getPathUri() . 'views/img/',
            'modules' => $moduleList,
            'logs' => $loggerRepository->findAll(),
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'currentPage' => $currentPage,
            'ps_base_dir' => Tools::getHttpHost(true),
            'ps_version' => _PS_VERSION_,
        ]);

        $this->output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu.tpl');

        return $this->output;
    }

    /**
     * Remove the token from an admin link and return only the url
     *
     * @param string $link
     *
     * @return string
     */
    private function getAdminLinkWithoutToken(string $link): string
    {
        $pos = strpos($link, '?');

        if (false === $pos) {
            return $link;
        }

        return substr($link, 0, $pos);
    }

    /**
     * Get token from an admin controller link
     *
     * @param string $link
     *
     * @return string
     */
    public function getTokenFromAdminLink(string $link): string
    {
        parse_str((string) parse_url($link, PHP_URL_QUERY), $result);

        if (is_array($result['_token'])) {
            throw new \PrestaShopException('Invalid token');
        }

        return $result['_token'];
    }

    /**
     * load dependencies in the configuration of the module
     *
     * @return void
     */
    public function loadAssets(): void
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
            'faq.js',
            'menu.js',
            'back.js',
            'lib/sweetalert.min.js',
            'lib/jszip.min.js',
            'lib/pdfmake.min.js',
            'lib/vfs_fonts.js',
            'lib/buttons.html5.min.js',
        ];

        $prefix = $this->getPathUri() . 'views/';
        $jsFiles = preg_filter('/^/', $prefix . 'js/', $jsFiles);
        $cssFiles = preg_filter('/^/', $prefix . 'css/', $cssFiles);

        $this->context->controller->addCSS($cssFiles, 'all');
        $this->context->controller->addJS($jsFiles);
        $this->context->controller->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
        $this->context->controller->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
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
    private function submitDataConsent()
    {
        /** @var ConsentRepository $consentRepository */
        $consentRepository = $this->get('PrestaShop\Module\Psgdpr\Repository\ConsentRepository');

        if (Tools::isSubmit('submitDataConsent')) {
            /** @var LangRepository $langRepository */
            $langRepository = $this->get('prestashop.core.admin.lang.repository');
            $languages = $langRepository->findAll();
            $shopId = $this->context->shop->id;

            foreach ($this->settings_data_consent as $value) {
                if ($value === 'psgdpr_creation_form' || $value === 'psgdpr_customer_form') {
                    $values = [];

                    /** @var Lang $language */
                    foreach ($languages as $language) {
                        $values[$value][$language->getId()] = Tools::getValue($value . '_' . $language->getId());
                    }

                    Configuration::updateValue(Tools::strtoupper($value), $values[$value], true);
                } else {
                    Configuration::updateValue(Tools::strtoupper($value), Tools::getValue($value));
                }
            }

            $moduleList = $consentRepository->findAllRegisteredModules();

            foreach ($moduleList as $module) {
                $psgdprConsent = new PsgdprConsent();
                $psgdprConsent->setId($module['id_gdpr_consent']);
                $psgdprConsent->setModuleId($module['id_module']);
                $psgdprConsent->setActive(Tools::getValue('psgdpr_switch_registered_module_' . $module['id_module']));

                /** @var Lang $language */
                foreach ($languages as $language) {
                    $psgdprConsentLang = new PsgdprConsentLang();
                    $psgdprConsentLang->setLang($language);
                    $psgdprConsentLang->setMessage(Tools::getValue('psgdpr_registered_module_' . $module['id_module'] . '_' . $language->getId()));
                    $psgdprConsentLang->setShopId($shopId);
                    $psgdprConsent->addConsentLang($psgdprConsentLang);
                }

                $consentRepository->createOrUpdateConsent($psgdprConsent);
            }

            $this->output .= $this->displayConfirmation($this->getTranslator()->trans('Saved with success !', [], 'Modules.Psgdpr.Shop'));
        }
    }

    /**
     * load all the registered modules and add the displayname and logopath in each module
     *
     * @return array
     */
    private function loadRegisteredModules(): array
    {
        /** @var ConsentRepository $consentRepository */
        $consentRepository = $this->get('PrestaShop\Module\Psgdpr\Repository\ConsentRepository');

        $languages = Language::getLanguages(false);
        $moduleList = $consentRepository->findAllRegisteredModules();

        if (count($moduleList) < 1) {
            return [];
        }

        $physicalUri = $this->context->shop->physical_uri;

        return array_map(function ($module) use ($consentRepository, $languages, $physicalUri) {
            /** @var Module|false $currentModuleInfos */
            $currentModuleInfos = Module::getInstanceById($module['id_module']);

            if ($currentModuleInfos === false) {
                return;
            }

            $module['active'] = $consentRepository->findModuleConsentIsActive($module['id_module']);

            foreach ($languages as $lang) {
                $module['message'][$lang['id_lang']] = $consentRepository->findModuleConsentMessage($module['id_module'], $lang['id_lang']);
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
    private function getRegisteredModules()
    {
        $modulesRegistered = Hook::getHookModuleExecList('registerGDPRConsent');

        if (empty($modulesRegistered)) {
            return;
        }

        foreach ($modulesRegistered as $module) {
            if ($module['id_module'] != $this->id) {
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
    private function addModuleConsent(array $module): void
    {
        /** @var LangRepository $langRepository */
        $langRepository = $this->get('prestashop.core.admin.lang.repository');

        /** @var ConsentRepository $consentRepository */
        $consentRepository = $this->get('PrestaShop\Module\Psgdpr\Repository\ConsentRepository');

        $languages = $langRepository->findAll();
        $shopId = $this->context->shop->id;
        $consentExistForModule = $consentRepository->findModuleConsentExist($module['id_module']);

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

        $consentRepository->createOrUpdateConsent($psgdprConsent);
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
                $docPathUri = $this->getPathUri() . 'docs/readme_fr.pdf';
                break;
            default:
                $docPathUri = $this->getPathUri() . 'docs/readme_en.pdf';
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
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionAdminControllerSetMedia()
    {
        $controller = Dispatcher::getInstance()->getController();

        if ($controller !== 'AdminOrders') {
            return false;
        }

        $orderId = (int) Tools::getValue('id_order');

        $order = new Order($orderId);
        $isCustomerExist = (bool) Customer::customerIdExistsStatic($order->id_customer);

        if ($isCustomerExist === true) {
            return false;
        }

        Media::addJsDefL('psgdprNoAddresses', $this->trans('Customer data deleted by the Official GDPR module.', [], 'Modules.Psgdpr.Shop'));

        $this->context->controller->addCSS($this->getPathUri() . '/views/css/overrideAddress.css');
        $this->context->controller->addJS($this->getPathUri() . '/views/js/overrideAddress.js');
    }

    /**
     * @return array|FormField[]
     */
    public function hookAdditionalCustomerFormFields()
    {
        $context = Context::getContext();

        $langId = $context->language->id;
        $currentPage = $context->controller->php_self;

        switch ($currentPage) {
            case 'identity':
                $active = Configuration::get('PSGDPR_CUSTOMER_FORM_SWITCH');
                $label = Configuration::get('PSGDPR_CUSTOMER_FORM', $langId);
                break;
            case 'authentication':
            case 'registration':
            case 'order':
            case 'order-confirmation':
                $active = Configuration::get('PSGDPR_CREATION_FORM_SWITCH');
                $label = Configuration::get('PSGDPR_CREATION_FORM', $langId);
                break;
            default:
                $label = '';
                $active = false;
                break;
        }

        if ($active == false) {
            return [];
        }

        $formField = new FormField();
        $formField->setName('psgdpr');
        $formField->setType('checkbox');
        $formField->setLabel($label);
        $formField->setRequired(true);

        return [$formField];
    }

    /**
     * @param array $params
     *
     * @return bool
     */
    public function hookActionCustomerAccountAdd(array $params): bool
    {
        /** @var LoggerService $loggerService */
        $loggerService = $this->get('PrestaShop\Module\Psgdpr\Service\LoggerService');

        if (!isset($params['newCustomer']) || !isset($params['newCustomer']->id)) {
            return false;
        }

        $customer = new Customer($params['newCustomer']->id);
        $customerFullName = $customer->firstname . ' ' . $customer->lastname;

        $loggerService->createLog($customer->id, LoggerService::REQUEST_TYPE_CONSENT_COLLECTING, 0, 0, $customerFullName);

        return true;
    }

    /**
     * @return string
     */
    public function hookDisplayCustomerAccount(): string
    {
        $context = Context::getContext();

        $this->context->smarty->assign([
            'frontController' => $context->link->getModuleLink($this->name, 'gdpr', [], true),
            'customerId' => $context->customer->id,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/front/account_gdpr_box.tpl');
    }

    /**
     * Allow to return the checkbox to display in modules
     *
     * @param array $params
     *
     * @return string html content to display
     */
    public function hookDisplayGDPRConsent(array $params): string
    {
        /** @var ConsentRepository $consentRepository */
        $consentRepository = $this->get('PrestaShop\Module\Psgdpr\Repository\ConsentRepository');

        if (!isset($params['id_module'])) {
            return '';
        }

        $moduleId = (int) $params['id_module'];

        if (false === $consentRepository->findModuleConsentIsActive($moduleId)) {
            return '';
        }

        $message = $consentRepository->findModuleConsentMessage($moduleId, $this->context->language->id);
        $url = $this->context->link->getModuleLink($this->name, 'FrontAjaxGdpr', [], true);

        $customerId = $this->context->customer->id;
        $guestId = 0;

        if ($customerId == null) {
            $guestId = $this->context->cart->id_guest;
            $customerId = 0;
        }

        $this->context->smarty->assign([
            'psgdpr_id_guest' => $guestId,
            'psgdpr_id_customer' => $customerId,
            'psgdpr_customer_token' => sha1($this->context->customer->secure_key),
            'psgdpr_guest_token' => sha1('psgdpr' . $guestId . $_SERVER['REMOTE_ADDR'] . date('Y-m-d')),
            'psgdpr_id_module' => $moduleId,
            'psgdpr_consent_message' => $message,
            'psgdpr_front_controller' => $url,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/display_rgpd_consent.tpl');
    }

    /**
     * Execute raw sql query from specific folder
     * Used mainly for the install or uninstall of the psgdpr module
     *
     * @return bool
     */
    private function executeQuerySql(string $folder): bool
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->get('doctrine.orm.entity_manager');

        $finder = (new Finder())
            ->files()
            ->in(__DIR__ . '/sql/' . $folder)
            ->name('*.sql')
        ;

        $hasExecutedAtLeastOneQuery = false;

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $contents = $file->getContents();

            if ($contents === '') {
                continue;
            }

            $hasExecutedAtLeastOneQuery = true;
            $query = str_replace('PREFIX_', _DB_PREFIX_, $contents);

            $entityManager->getConnection()->executeQuery($query);
        }

        return $hasExecutedAtLeastOneQuery;
    }
}
