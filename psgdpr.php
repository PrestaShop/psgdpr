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
if (!defined('_PS_VERSION_')) {
    exit;
}

$autoloadPath = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

use PrestaShop\PrestaShop\Adapter\ServiceLocator;
use PrestaShop\PrestaShop\Core\Crypto\Hashing;

class Psgdpr extends Module
{
    public $adminControllers = [
        'adminAjax' => 'AdminAjaxPsgdpr',
        'adminDownloadInvoices' => 'AdminDownloadInvoicesPsgdpr',
    ];

    private $settings_data_consent = [
        'switchCreationForm' => 'psgdpr_creation_form_switch',
        'accountCreationForm' => 'psgdpr_creation_form',
        'switchCustomerForm' => 'psgdpr_customer_form_switch',
        'accountCustomerForm' => 'psgdpr_customer_form',
    ];

    private $presetMessageAccountCreation = [
        'en' => 'I agree to the terms and conditions and the privacy policy',
        'cb' => 'I agree to the terms and conditions and the privacy policy',
        'es' => 'Acepto las condiciones generales y la política de confidencialidad',
        'ag' => 'Acepto las condiciones generales y la política de confidencialidad',
        'br' => 'Acepto las condiciones generales y la política de confidencialidad',
        'mx' => 'Acepto las condiciones generales y la política de confidencialidad',
        'de' => 'Ich akzeptiere die Allgemeinen Geschäftsbedingungen und die Datenschutzrichtlinie',
        'qc' => 'Acepto las condiciones generales y la política de confidencialidad',
        'fr' => 'J\'accepte les conditions générales et la politique de confidentialité',
        'it' => 'Accetto le condizioni generali e la politica di riservatezza',
        'nl' => 'Ik accepteer de Algemene voorwaarden en het vertrouwelijkheidsbeleid',
        'pl' => 'Akceptuję ogólne warunki użytkowania i politykę prywatności',
        'pt' => 'Aceito as condições gerais e a política de confidencialidade',
        'ru' => 'Я соглашаюсь на использование указанных в этой форме данных компанией xxxxx для (i) изучения моего запроса, (ii) ответа и, при необходимости, (iii) управления возможными договорными отношениями.',
    ];

    private $presetMessageAccountCustomer = [
        'en' => 'By submitting this form, I accept that the data entered is used by xxxxx so they can (i) acknowledge your request, (ii) replay and, if necessary, (iii) manage the contractual relationship that may result.',
        'cb' => 'By submitting this form, I accept that the data entered is used by xxxxx so they can (i) acknowledge your request, (ii) replay and, if necessary, (iii) manage the contractual relationship that may result.',
        'es' => 'Al enviar este formulario, acepto que xxxxx utilice los datos que he facilitado para (i) conocer mi solicitud, (ii) darle respuesta, si fuera el caso, (iii) encargarse de la gestión de la relación contractual que pudiera derivarse de ella.',
        'ag' => 'Al enviar este formulario, acepto que xxxxx utilice los datos que he facilitado para (i) conocer mi solicitud, (ii) darle respuesta, si fuera el caso, (iii) encargarse de la gestión de la relación contractual que pudiera derivarse de ella.',
        'mx' => 'Al enviar este formulario, acepto que xxxxx utilice los datos que he facilitado para (i) conocer mi solicitud, (ii) darle respuesta, si fuera el caso, (iii) encargarse de la gestión de la relación contractual que pudiera derivarse de ella.',
        'br' => 'Al enviar este formulario, acepto que xxxxx utilice los datos que he facilitado para (i) conocer mi solicitud, (ii) darle respuesta, si fuera el caso, (iii) encargarse de la gestión de la relación contractual que pudiera derivarse de ella.',
        'de' => 'Mit dem Absenden dieses Formulars erkläre ich mich damit einverstanden, dass die eingegebenen Daten von XXXXX zu folgenden Zwecken verwendet werden: a) um Ihre Anfrage zur Kenntnis zu nehmen, b) um darauf zu antworten und gegebenenfalls c) das daraus resultierende Vertragsverhältnis zu verwalten.',
        'fr' => 'En soumettant ce formulaire, j\'accepte que les données renseignées soient utilisées par xxxxx pour lui permettre (i) de prendre connaissance de votre demande, (ii) y répondre ainsi que, le cas échéant, (iii) assurer la gestion de la relation contractuelle qui pourrait en découler.',
        'qc' => 'En soumettant ce formulaire, j\'accepte que les données renseignées soient utilisées par xxxxx pour lui permettre (i) de prendre connaissance de votre demande, (ii) y répondre ainsi que, le cas échéant, (iii) assurer la gestion de la relation contractuelle qui pourrait en découler.',
        'it' => 'Inviando questo formulario acconsento all’utilizzo dei dati da me inseriti da parte di XXXXX ai fini (i) della ricezione e (ii) dell’elaborazione della mia richiesta e, se del caso, (iii) della gestione dell’eventuale relazione contrattuale.',
        'nl' => 'Door dit formulier te verzenden, accepteer ik dat de ingevulde gegevens worden gebruikt door xxxxx om (i) kennis te nemen van uw verzoek, (ii) dit te beantwoorden en indien van toepassing, (iii) de contractuele relatie die hieruit zou kunnen voortkomen, te beheren.',
        'pl' => 'Przesyłając ten formularz, wyrażam zgodę na wykorzystywanie wprowadzonych danych przez xxxxx, aby umożliwić: (I) zapoznanie się z moją prośbą, (II) udzielenie odpowiedzi oraz, w stosownych przypadkach, (III) zapewnić zarządzanie stosunkiem umownym, który może z tego wyniknąć.',
        'pt' => 'Ao enviar este formulário, aceito que os dados informados sejam utilizados pela xxxxx para que (i) tomem conhecimento de sua solicitação (ii) para respondê-la, se necessário, (iii) assegurem a gestão da relação contratual que poderá resultar desta circunstância.',
        'ru' => 'Я соглашаюсь с Общими условиями и Политикой защиты персональных данных',
    ];

    /**
     * @var string
     */
    private $output;

    /**
     * @var bool
     */
    private $ps_version;

    /**
     * @var string
     */
    private $js_path;

    /**
     * @var string
     */
    private $css_path;

    /**
     * @var string
     */
    private $img_path;

    /**
     * @var string
     */
    private $docs_path;

    /**
     * @var string
     */
    private $logo_path;

    /**
     * @var string
     */
    private $module_path;

    public function __construct()
    {
        // Settings
        $this->name = 'psgdpr';
        $this->tab = 'administration';
        $this->version = '1.4.3';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '1001fe84b4dede19725b8826e32165b7';

        // bootstrap -> always set to true
        $this->bootstrap = true;

        parent::__construct();

        $this->output = '';

        $this->displayName = $this->l('Official GDPR compliance');
        $this->description = $this->l('Make your store comply with the General Data Protection Regulation (GDPR).');
        $this->ps_version = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

        // Settings paths
        $this->js_path = $this->_path . 'views/js/';
        $this->css_path = $this->_path . 'views/css/';
        $this->img_path = $this->_path . 'views/img/';
        $this->docs_path = $this->_path . 'docs/';
        $this->logo_path = $this->_path . 'logo.png';
        $this->module_path = $this->_path;

        // Confirm uninstall
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
    }

    /**
     * install()
     *
     * @return bool
     *
     * @throws PrestaShopException
     */
    public function install()
    {
        $languages = Language::getLanguages(false);
        $tmp = [];

        foreach ($this->settings_data_consent as $value) {
            if ($value === 'psgdpr_creation_form') {
                foreach ($languages as $lang) {
                    $tmp[Tools::strtoupper($value)][$lang['id_lang']] = isset($this->presetMessageAccountCreation[$lang['iso_code']]) ?
                        $this->presetMessageAccountCreation[$lang['iso_code']] :
                        $this->presetMessageAccountCreation['en'];
                    Configuration::updateValue(Tools::strtoupper($value), $tmp[Tools::strtoupper($value)], true);
                }
            } elseif ($value === 'psgdpr_customer_form') {
                foreach ($languages as $lang) {
                    $tmp[Tools::strtoupper($value)][$lang['id_lang']] = isset($this->presetMessageAccountCreation[$lang['iso_code']]) ?
                        $this->presetMessageAccountCreation[$lang['iso_code']] :
                        $this->presetMessageAccountCreation['en'];
                    Configuration::updateValue(Tools::strtoupper($value), $tmp[Tools::strtoupper($value)]);
                }
            } else {
                Configuration::updateValue(Tools::strtoupper($value), 1);
            }
        }
        unset($tmp);

        require_once __DIR__ . '/sql/install.php'; // sql querries

        $hook = [
            'displayCustomerAccount',
            'displayGDPRConsent',
            'actionAdminControllerSetMedia',
            'additionalCustomerFormFields',
            'actionCustomerAccountAdd',
        ];

        // register hook used by the module
        if (parent::install() &&
            $this->installTab() &&
            $this->registerHook($hook) &&
            $this->createAnonymousCustomer()) {
            return true;
        } else { // if something wrong return false
            $this->_errors[] = $this->l('There was an error during the uninstallation. Please contact us through Addons website.');

            return false;
        }
    }

    /**
     * uninstall()
     *
     * @return bool
     */
    public function uninstall()
    {
        foreach ($this->settings_data_consent as $value) {
            Configuration::deleteByName($value);
        }

        require_once __DIR__ . '/sql/uninstall.php'; // sql querriers

        // unregister hook
        if (parent::uninstall() && $this->uninstallTab()) {
            return true;
        } else {
            $this->_errors[] = $this->l('There was an error on module uninstall. Please contact us through Addons website');

            return false;
        }
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @return bool
     */
    public function installTab()
    {
        $result = true;

        foreach ($this->adminControllers as $controller_name) {
            $tab = new Tab();
            $tab->class_name = $controller_name;
            $tab->module = $this->name;
            $tab->active = true;
            $tab->id_parent = -1;
            $tab->name = array_fill_keys(
                Language::getIDs(false),
                $this->displayName
            );
            $result = $result && $tab->add();
        }

        return $result;
    }

    /**
     * uninstall tab
     *
     * @return bool
     */
    public function uninstallTab()
    {
        $result = true;

        foreach ($this->adminControllers as $controller_name) {
            $id_tab = (int) Tab::getIdFromClassName($controller_name);
            $tab = new Tab($id_tab);

            if (Validate::isLoadedObject($tab)) {
                $result = $result && $tab->delete();
            }
        }

        return $result;
    }

    /**
     * load dependencies in the configuration of the module
     */
    public function loadAsset()
    {
        // Load CSS
        $css = [
            $this->css_path . 'fontawesome-all.min.css',
            $this->css_path . 'datatables.min.css',
            $this->css_path . 'faq.css',
            $this->css_path . 'menu.css',
            $this->css_path . 'back.css',
            $this->css_path . $this->name . '.css',
        ];

        $this->context->controller->addCSS($css, 'all');

        // Load JS
        $jss = [
            $this->js_path . 'vue.min.js',
            $this->js_path . 'datatables.min.js',
            $this->js_path . 'faq.js',
            $this->js_path . 'menu.js',
            $this->js_path . 'back.js',
            $this->js_path . 'sweetalert.min.js',
            _PS_ROOT_DIR_ . 'js/tiny_mce/tiny_mce.js',
            _PS_ROOT_DIR_ . 'js/admin/tinymce.inc.js',
            $this->js_path . 'jszip.min.js',
            $this->js_path . 'pdfmake.min.js',
            $this->js_path . 'vfs_fonts.js',
            $this->js_path . 'buttons.html5.min.js',
        ];

        $this->context->controller->addJS($jss);

        // Clean memory
        unset($jss, $css);
    }

    /**
     * @return array
     */
    public function loadFaq()
    {
        return [
            [
                'title' => $this->trans('Data accessibility'),
                'blocks' => [
                    [
                        'question' => $this->trans('How can a customer retrieve all of his personal data?'),
                        'answer' => $this->trans('From his customer account, a new tab called My Personal Data is available and your customer can retrieve all of his personal data collected by your shop and installed modules, in PDF or CSV format.'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Customer consent'),
                'blocks' => [
                    [
                        'question' => $this->trans('There is no consent confirmation checkbox in the contact form. Isn\'t this a requirement?'),
                        'answer' => $this->trans('No, it is not a requirement as the customer gives consent by clicking on the Submit message button. Only a message is required to give your customers more information about the use of personal data on your website. We are currently working on a new version of the contact form, it will be available really soon for your online store.'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Data erasure'),
                'blocks' => [
                    [
                        'question' => $this->trans('How will a customer ask for all of his personal data to be deleted ?'),
                        'answer' => $this->trans('The customer will send a message from the contact form for any rectification and erasure requests, justifying his request.'),
                    ],
                    [
                        'question' => $this->trans('There is no Remove Data button in the customer account. Isn\'t this a requirement?'),
                        'answer' => $this->trans("No, the Remove Data button in the customer account is not an obligation. For the data erasure requests, your customers can request data removal only under certain circumstances, that is the reason why we decided not to include an automatic Remove Data button in their customer account.\n\nThey can, however, contact you anytime via your contact form, in this case, you can review their request and once you accept it, you will be able to remove their personal data directly in the configuration page of our Official GDPR Compliance module."),
                    ],
                    [
                        'question' => $this->trans('How to remove the personal data of a customer?'),
                        'answer' => $this->trans("If the request is valid, from the Personal Data Management tab of this module, any customer can be found by typing the first few letters of his name or email address in the search bar.\nBefore deleting any data, we recommend you to download all the invoices of the involved customer. After deleting the data with the “Remove data” button, the customer’s orders can’t be legally deleted, they just won’t be linked to any account. This allows you to keep precise statistics of your shop."),
                    ],
                    [
                        'question' => $this->trans('After removing all personal data of a customer from my database, what will happen to his orders?'),
                        'answer' => $this->trans("Due to other legal obligations, his orders will still be stocked but they are no longer associated with the customer.\nOnly the name, shipping, and billing information must be kept in the order details page for legal reasons, invoicing, and accounting.\nAccording to the Rec.30;Art.7(1)(c)"),
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
        $moduleAdminLink = $this->context->link->getAdminLink('AdminModules', true, [], ['configure' => $this->name]);

        $id_lang = $this->context->language->id;
        $id_shop = $this->context->shop->id;

        $this->loadAsset(); // load js and css
        $this->postProcess(); // execute submit form

        $this->getRegisteredModules(); // register modules which trying to register to GDPR in database
        $module_list = $this->loadRegisteredModules(); // return module registered in database

        // controller url
        $adminController = $this->context->link->getAdminLink($this->adminControllers['adminAjax']);
        $adminControllerInvoices = $this->context->link->getAdminLink($this->adminControllers['adminDownloadInvoices']);

        $iso_lang = Language::getIsoById($id_lang);
        // get readme
        switch ($iso_lang) {
            case 'fr':
                $doc = $this->docs_path . 'readme_fr.pdf';
                break;
            default:
                $doc = $this->docs_path . 'readme_en.pdf';
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
        unset($tmp);

        // assign var to smarty
        $this->context->smarty->assign([
            'module_name' => $this->name,
            'id_shop' => $id_shop,
            'module_version' => $this->version,
            'moduleAdminLink' => $moduleAdminLink,
            'id_lang' => $id_lang,
            'psgdpr_adminController' => $adminController,
            'adminControllerInvoices' => $adminControllerInvoices,
            'faq' => $this->loadFaq(),
            'doc' => $doc,
            'youtubeLink' => $youtubeLink,
            'cmspage' => $CMS,
            'cmsConfPage' => $cmsConfPage,
            'orderLink' => $orderLink,
            'cartLink' => $cartLink,
            'module_display' => $this->displayName,
            'module_path' => $this->module_path,
            'logo_path' => $this->logo_path,
            'img_path' => $this->img_path,
            'modules' => $module_list,
            'logs' => GDPRLog::getLogs(),
            'languages' => $this->context->controller->getLanguages(),
            'defaultFormLanguage' => (int) $this->context->employee->id_lang,
            'currentPage' => $currentPage,
            'ps_base_dir' => Tools::getHttpHost(true),
            'ps_version' => _PS_VERSION_,
            'isPs17' => $this->ps_version,
        ]);

        $this->output .= $this->context->smarty->fetch($this->local_path . 'views/templates/admin/menu.tpl');

        return $this->output;
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

            foreach ($this->settings_data_consent as $value) {
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

            $this->output .= $this->displayConfirmation($this->l('Saved with success !'));
        }
    }

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function hookActionAdminControllerSetMedia()
    {
        $controller = Dispatcher::getInstance()->getController();

        if ($controller !== 'AdminOrders') {
            return;
        }

        $id_order = (int) Tools::getValue('id_order');

        $order = new Order($id_order);
        $customerExist = (bool) Customer::customerIdExistsStatic($order->id_customer);

        if ($customerExist === true) {
            return;
        }

        Media::addJsDefL('psgdprNoAddresses', $this->l('Customer data deleted by official GDPR module.'));

        $this->context->controller->addCSS($this->css_path . 'overrideAddress.css');
        $this->context->controller->addJS($this->js_path . 'overrideAddress.js');
    }

    /**
     * @return array|FormField[]
     */
    public function hookAdditionalCustomerFormFields()
    {
        $id_lang = Context::getContext()->language->id;
        $currentPage = Context::getContext()->controller->php_self;
        // identity or authentification
        switch ($currentPage) {
            case 'identity':
                $active = Configuration::get('PSGDPR_CUSTOMER_FORM_SWITCH');
                $label = Configuration::get('PSGDPR_CUSTOMER_FORM', $id_lang);
                break;
            case 'authentication':
            case 'registration':
            case 'order':
            case 'order-confirmation':
                $active = Configuration::get('PSGDPR_CREATION_FORM_SWITCH');
                $label = Configuration::get('PSGDPR_CREATION_FORM', $id_lang);
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
     */
    public function hookActionCustomerAccountAdd(array $params)
    {
        if (!isset($params['newCustomer']) || !isset($params['newCustomer']->id)) {
            return;
        }

        $id_customer = $params['newCustomer']->id;
        $id_guest = Context::getContext()->cart->id_guest;
        GDPRLog::addLog($id_customer, 'consent', 0, $id_guest);
    }

    /**
     * load all the registered modules and add the displayname and logopath in each module
     *
     * @return array who contains id_module, message, displayName, logoPath
     */
    public function loadRegisteredModules()
    {
        $languages = Language::getLanguages(false);

        $modules = GDPRConsent::getAllRegisteredModules();
        if (count($modules) < 1) {
            return [];
        }

        $physical_uri = $this->context->shop->physical_uri;

        $module_list = [];
        foreach ($modules as $module) {
            /** @var Module|false $moduleInstance */
            $moduleInstance = Module::getInstanceById($module['id_module']);

            if ($moduleInstance === false) {
                continue;
            }

            $module['active'] = GDPRConsent::getConsentActive($module['id_module']);
            foreach ($languages as $lang) {
                $module['message'][$lang['id_lang']] = GDPRConsent::getConsentMessage($module['id_module'], $lang['id_lang']);
            }
            $module['displayName'] = $moduleInstance->displayName;
            $module['logoPath'] = Tools::getHttpHost(true) . $physical_uri . 'modules/' . $moduleInstance->name . '/logo.png';

            array_push($module_list, $module);
        }

        return $module_list;
    }

    /**
     * @return string
     */
    public function hookDisplayCustomerAccount()
    {
        $context = Context::getContext();
        $id_customer = $context->customer->id;

        $url = Context::getContext()->link->getModuleLink($this->name, 'gdpr', [], true);

        $this->context->smarty->assign([
            'front_controller' => $url,
            'id_customer' => $id_customer,
            'ps_version' => $this->ps_version,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/front/customerAccount.tpl');
    }

    /**
     * Allow to return the checkbox to display in modules
     *
     * @param array $params
     *
     * @return string html content to display
     */
    public function hookDisplayGDPRConsent($params)
    {
        // get id_lang
        $id_lang = Context::getContext()->language->id;

        if (!isset($params['id_module'])) {
            return '';
        }

        $id_module = (int) $params['id_module'];

        $active = GDPRConsent::getConsentActive($id_module);
        if ($active === false) {
            return '';
        }
        $message = GDPRConsent::getConsentMessage($id_module, $id_lang);

        $url = Context::getContext()->link->getModuleLink($this->name, 'FrontAjaxGdpr', [], true);

        $id_customer = Context::getContext()->customer->id;
        $id_guest = 0;
        if ($id_customer == null) {
            $id_guest = Context::getContext()->cart->id_guest;
            $id_customer = 0;
        }
        $this->context->smarty->assign([
            'ps_version' => $this->ps_version,
            'psgdpr_id_guest' => $id_guest,
            'psgdpr_id_customer' => $id_customer,
            'psgdpr_customer_token' => sha1(Context::getContext()->customer->secure_key),
            'psgdpr_guest_token' => sha1('psgdpr' . $id_guest . $_SERVER['REMOTE_ADDR'] . date('Y-m-d')),
            'psgdpr_id_module' => $id_module,
            'psgdpr_consent_message' => $message,
            'psgdpr_front_controller' => $url,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/displayGDPRConsent.tpl');
    }

    /**
     * Get a module list of module trying to register to GDPR
     */
    public function getRegisteredModules()
    {
        $modulesRegister = Hook::getHookModuleExecList('registerGDPRConsent'); // get modules using the gdpr hook

        if (empty($modulesRegister)) { // if 0 module stop
            return;
        }

        foreach ($modulesRegister as $module) { // foreach module hook
            if ($module['id_module'] != $this->id) { // ignore gdpr module
                $this->addModuleConsent($module); // regsiter module in database
            }
        }
    }

    /**
     * register the module in database
     *
     * @param array $module module to register in database
     */
    public function addModuleConsent($module)
    {
        $id_shop = Context::getContext()->shop->id;
        if (GDPRConsent::checkIfExist($module['id_module'], $id_shop) === true) { // check if the module hase been already register
            return;
        }

        // create GDPRConsent object in order to register it
        $moduleConsent = new GDPRConsent();
        $moduleConsent->id_module = $module['id_module'];
        $moduleConsent->message = 'Enim quis fugiat consequat elit minim nisi eu occaecat occaecat deserunt aliquip nisi ex deserunt.';
        $moduleConsent->active = true;

        $moduleConsent->date_add = date('Y-m-d H:i:s');
        $moduleConsent->date_upd = date('Y-m-d H:i:s');

        $moduleConsent->save(); // save the module in database
        unset($moduleConsent);
    }

    /**
     * @param string $delete
     * @param mixed $value
     *
     * @return array
     */
    public function getCustomerData($delete, $value)
    {
        $data = [];
        switch ($delete) {
            case 'customer':
                $customer = new Customer((int) $value);
                $dataFromPrestashop = $this->getCustomerDataFromPrestashop($customer);
                $dataFromModules = $this->getCustomerDataFromModules($customer);
                $data['data']['prestashopData'] = $dataFromPrestashop;
                $data['data']['modulesData'] = $dataFromModules;
                break;
            case 'email':
                $customer = ['email' => $value];
                $dataFromModules = $this->getCustomerDataFromModules($customer);
                $data['data']['modulesData'] = $dataFromModules;
                break;
            case 'phone':
                $customer = ['phone' => $value];
                $dataFromModules = $this->getCustomerDataFromModules($customer);
                $data['data']['modulesData'] = $dataFromModules;
                break;
        }

        return $data;
    }

    /**
     * @param Customer $customer
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getCustomerDataFromPrestashop(Customer $customer)
    {
        $id_lang = Context::getContext()->language->id;
        $data = [];

        $stats = $customer->getStats();
        $customerLanguage = Language::getLanguage($customer->id_lang);
        $gender = new Gender($customer->id_gender, $id_lang);
        $genderName = $gender->name;
        unset($gender);

        $data['customerInfo'] = [
            'id_customer' => $customer->id,
            'gender' => $genderName,
            'firstname' => $customer->firstname,
            'lastname' => $customer->lastname,
            'birthday' => $customer->birthday,
            'age' => $this->getAgeCustomer($customer->id),
            'email' => $customer->email,
            'siret' => $customer->siret,
            'ape' => $customer->ape,
            'company' => $customer->company,
            'website' => $customer->website,
            'last_visit' => $stats['last_visit'],
            'language' => $customerLanguage['name'],
            'date_add' => $customer->date_add,
        ];

        // get orders
        $data['orders'] = [];
        $orderList = Order::getCustomerOrders($customer->id);

        if (count($orderList) >= 1) {
            foreach ($orderList as $index => $order) {
                $orderObject = new Order($order['id_order']);
                $productsOrder = $orderObject->getProducts();
                $currency = Currency::getCurrency($order['id_currency']);

                $data['orders'][] = [
                    'id_order' => $order['id_order'],
                    'reference' => $order['reference'],
                    'payment' => $order['payment'],
                    'date_add' => $order['date_add'],
                    'order_state' => $order['order_state'],
                    'order_state_color' => $order['order_state_color'],
                    'total_paid_tax_incl' => number_format($order['total_paid_tax_incl'], 2) . ' ' . $currency['iso_code'],
                    'nb_products' => $order['nb_products'],
                    'products' => [],
                ];
                foreach ($productsOrder as $product) {
                    $data['orders'][$index]['products'][] = [
                        'id_product' => $product['product_id'],
                        'id_product_attribute' => $product['product_attribute_id'],
                        'product_reference' => $product['product_reference'],
                        'product_name' => $product['product_name'],
                        'product_quantity' => $product['product_quantity'],
                    ];
                }
                unset($orderObject);
            }
        }

        // get carts
        $data['carts'] = [];
        $cartList = Cart::getCustomerCarts($customer->id, false);

        if (count($cartList) >= 1) {
            foreach ($cartList as $index => $cart) {
                $cartObject = new Cart($cart['id_cart']);
                $productsCart = $cartObject->getProducts();

                $data['carts'][] = [
                    'id_cart' => $cart['id_cart'],
                    'nb_products' => count($productsCart),
                    'products' => [],
                    'date_add' => $cart['date_add'],
                ];
                foreach ($productsCart as $product) {
                    $data['carts'][$index]['products'][] = [
                        'id_product' => $product['id_product'],
                        'id_product_attribute' => $product['id_product_attribute'],
                        'product_reference' => $product['reference'],
                        'product_name' => $product['name'],
                        'product_quantity' => $product['cart_quantity'],
                        'total_wt' => $product['total_wt'],
                    ];
                }
                unset($cartObject);
            }
        }

        // get addresses
        $data['addresses'] = $customer->getAddresses($id_lang);

        // get messages
        $data['messages'] = [];
        $messageList = CustomerThread::getCustomerMessages($customer->id);

        if (count($messageList) >= 1) {
            foreach ($messageList as $index => $message) {
                $data['messages'][] = [
                    'id_customer_thread' => $message['id_customer_thread'],
                    'message' => $message['message'],
                    'ip' => (int) $message['ip_address'] == $message['ip_address'] ? long2ip((int) $message['ip_address']) : $message['ip_address'],
                    'date_add' => $message['date_add'],
                ];
            }
        }

        // get connections
        $data['connections'] = $customer->getLastConnections();

        // get referrers
        if (version_compare(_PS_VERSION_, '8.0.0', '<')) {
            // @phpstan-ignore-next-line
            $data['referrer'] = Referrer::getReferrers($customer->id);
        }

        return $data;
    }

    /**
     * @param mixed $customer
     *
     * @return array
     *
     * @throws PrestaShopException
     */
    public function getCustomerDataFromModules($customer)
    {
        $modulesData = Hook::getHookModuleExecList('actionExportGDPRData'); // get modules using the export gdpr hook

        if (empty($modulesData)) {
            return [];
        }

        $customer = (array) $customer;
        $data = [];

        foreach ($modulesData as $module) { // foreach module hook on the actionExportGDPRData
            $moduleInstance = Module::getInstanceById($module['id_module']);
            $result = Hook::exec('actionExportGDPRData', $customer, $module['id_module']);
            $data[$moduleInstance->displayName] = json_decode($result);
        }

        return $data;
    }

    /**
     * @param string $delete
     * @param mixed $value
     */
    public function deleteCustomer($delete, $value)
    {
        switch ($delete) {
            case 'customer':
                $customer = new Customer((int) $value);
                if (Validate::isLoadedObject($customer)) {
                    $this->deleteDataFromModules($customer);
                    $this->deleteDataFromPrestashop($customer);
                }
                break;
            case 'email':
                $data = ['email' => $value];
                $this->deleteDataFromModules($data);
                GDPRLog::addLog(0, 'delete', 0, 0, $value);
                break;
            case 'phone':
                $data = ['phone' => $value];
                $this->deleteDataFromModules($data);
                GDPRLog::addLog(0, 'delete', 0, 0, $value);
                break;
        }
    }

    /**
     * @param Customer $customer
     *
     * @return bool
     */
    public function deleteDataFromPrestashop(Customer $customer)
    {
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        $queries = [];

        // assign order to an anonymous account in order to keep stats -> let customer->delete() do the job
        // $queries[] = "UPDATE `"._DB_PREFIX_."orders` SET id_customer = ".(int)Configuration::get('PSGDPR_ANONYMOUS_CUSTOMER').",
        //         id_address_delivery = ".(int)Configuration::get('PSGDPR_ANONYMOUS_ADDRESS').",
        //         id_address_invoice = ".(int)Configuration::get('PSGDPR_ANONYMOUS_ADDRESS')."
        //         WHERE id_customer = ".(int)$customer->id;

        // assign cart to an anonymous account in order to keep stats
        $queries[] = 'UPDATE `' . _DB_PREFIX_ . 'cart` SET id_customer = ' . (int) Configuration::get('PSGDPR_ANONYMOUS_CUSTOMER') . ',
                id_address_delivery = ' . (int) Configuration::get('PSGDPR_ANONYMOUS_ADDRESS') . ',
                id_address_invoice = ' . (int) Configuration::get('PSGDPR_ANONYMOUS_ADDRESS') . '
                WHERE id_customer = ' . (int) $customer->id;

        // delete address of the customer
        // $queries[] = "DELETE FROM `"._DB_PREFIX_."address` WHERE id_customer = ".(int)$customer->id; // let customer->delete() do the job

        // delete cart rule associated to the customer
        $queries[] = 'DELETE FROM `' . _DB_PREFIX_ . 'cart_rule` WHERE id_customer = ' . (int) $customer->id;

        // delete specific price belong to the customer
        $queries[] = 'DELETE FROM `' . _DB_PREFIX_ . 'specific_price` WHERE id_customer = ' . (int) $customer->id;

        // delete message send by the customer
        $queries[] = 'DELETE FROM `' . _DB_PREFIX_ . 'message` WHERE id_customer = ' . (int) $customer->id;

        // delete all messages send by the customer
        $customerMessages = CustomerThread::getCustomerMessages($customer->id);
        foreach ($customerMessages as $message) {
            $queries[] = 'DELETE FROM `' . _DB_PREFIX_ . 'customer_message` WHERE id_customer_message = ' . (int) $message['id_customer_message'];
        }
        $queries[] = 'DELETE FROM `' . _DB_PREFIX_ . 'customer_thread` WHERE id_customer = ' . (int) $customer->id;

        foreach ($queries as $query) {
            if (Db::getInstance()->execute($query) === false) {
                return false;
            }
        }

        GDPRLog::addLog((int) $customer->id, 'delete', 0, 0);

        return $customer->delete(); // delete the customer
    }

    /**
     * @param mixed $customer
     *
     * @throws PrestaShopException
     */
    public function deleteDataFromModules($customer)
    {
        $modulesData = Hook::getHookModuleExecList('actionDeleteGDPRCustomer'); // get modules using the deletion gdpr hook
        if ($modulesData == false) {
            return;
        }

        foreach ($modulesData as $module) { // foreach module hook on the actionDeleteGDPRCustomer
            if ($module['id_module'] != $this->id) { // exclude gdpr module
                $customer = (array) $customer;
                Hook::exec('actionDeleteGDPRCustomer', $customer, $module['id_module']);
            }
        }
    }

    /**
     * @return bool
     *
     * @throws PrestaShopException
     */
    public function createAnonymousCustomer()
    {
        /** @var Hashing $crypto */
        $crypto = ServiceLocator::get(Hashing::class);

        $query = 'SELECT id_customer, email, passwd FROM `' . _DB_PREFIX_ . 'customer` c WHERE email = "anonymous@psgdpr.com" or email = "anonymous@anonymous.com"';
        $anonymousCustomer = Db::getInstance()->getRow($query);

        if (isset($anonymousCustomer['id_customer'])) {
            if ($anonymousCustomer['passwd'] === 'prestashop') {
                $customer = new Customer((int) $anonymousCustomer['id_customer']);
                $customer->passwd = $crypto->hash(Tools::passwdGen(64)); // Generate a long random password
                $customer->save();
            }

            $id_address = Address::getFirstCustomerAddressId($anonymousCustomer['id_customer']);

            Configuration::updateValue('PSGDPR_ANONYMOUS_CUSTOMER', $anonymousCustomer['id_customer']);
            Configuration::updateValue('PSGDPR_ANONYMOUS_ADDRESS', $id_address);

            return true;
        }

        // create an anonymous customer
        $customer = new Customer();
        $customer->id_gender = 1;
        $customer->lastname = 'Anonymous';
        $customer->firstname = 'Anonymous';
        $customer->email = 'anonymous@psgdpr.com';
        $customer->passwd = $crypto->hash(Tools::passwdGen(64)); // Generate a long random password
        $customer->active = false;

        if ($customer->save() == false) {
            return false;
        }

        // create an anonymous address
        $address = new Address();
        $address->id_customer = $customer->id;
        $address->alias = 'Anonymous';
        $address->company = 'Anonymous';
        $address->lastname = 'Anonymous';
        $address->firstname = 'Anonymous';
        $address->address1 = 'Anonymous';
        $address->postcode = '00000';
        $address->phone = '0000000000';
        $address->phone_mobile = '0000000000';
        $address->vat_number = '0000';
        $address->dni = '0000';
        $address->postcode = '00000';
        $address->id_country = (int) Configuration::get('PS_COUNTRY_DEFAULT');
        $address->city = 'Anonymous';
        if ($address->save() == false) {
            return false;
        }

        Configuration::updateValue('PSGDPR_ANONYMOUS_CUSTOMER', $customer->id);
        Configuration::updateValue('PSGDPR_ANONYMOUS_ADDRESS', $address->id);

        unset($customer, $address);

        return true;
    }

    /**
     * Return customer name for the given id.
     *
     * @param int $id_customer
     *
     * @return string Customer lastname + firstname
     */
    public function getCustomerNameById($id_customer)
    {
        $name = Db::getInstance()->getValue(
            "SELECT CONCAT(firstname, ' ', lastname) as name
            FROM `" . _DB_PREFIX_ . 'customer`
            WHERE id_customer = ' . (int) $id_customer
        );

        if (empty($name)) {
            return '';
        }

        return $name;
    }

    /**
     * Return the age of the customer
     *
     * @param int $id_customer
     *
     * @return int customer age
     */
    public function getAgeCustomer($id_customer)
    {
        return (int) Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->getValue('SELECT TIMESTAMPDIFF(YEAR, birthday, CURDATE()) AS age
            FROM `' . _DB_PREFIX_ . 'customer` c
            WHERE active = 1
            AND id_customer = ' . (int) $id_customer . '
            AND birthday IS NOT NULL AND birthday != "0000-00-00" ' . Shop::addSqlRestriction());
    }
}
