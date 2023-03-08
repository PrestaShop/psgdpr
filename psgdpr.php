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

use PrestaShop\PrestaShop\Adapter\LegacyLogger;
use PrestaShop\PrestaShop\Core\Domain\Customer\ValueObject\CustomerId;

class Psgdpr extends Module
{
    CONST SQL_QUERY_TYPE_INSTALL = 'install';
    CONST SQL_QUERY_TYPE_UNINSTALL = 'uninstall';

    /** @var LegacyLogger */
    private $logger;

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
     * @var bool
     */
    public $psVersionIs17;

    public function __construct()
    {
        $this->name = 'psgdpr';
        $this->tab = 'administration';
        $this->version = '2.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '1001fe84b4dede19725b8826e32165b7';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->trans('Official GDPR compliance', [], 'Modules.Psgdpr.General');
        $this->description = $this->trans('Make your store comply with the General Data Protection Regulation (GDPR).', [], 'Modules.Psgdpr.General');
        $this->psVersionIs17 = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall this module?', [], 'Modules.Psgdpr.General');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        $this->logger = $this->get('prestashop.adapter.legacy.logger');
    }

    /**
     * @return bool
     */
    public function isUsingNewTranslationSystem()
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
            $this->installTab();
            $this->registerHook($this->hooksUsedByModule);
            $this->executeQuerySql(self::SQL_QUERY_TYPE_INSTALL);
            $this->createAnonymousCustomer();
        } catch (PrestaShopException $e) {
            $this->logger->error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->_errors[] = $this->trans('There was an error during install. Please contact us through Addons website. (for developers, consult shop logs)', [], 'Modules.Psgdpr.General');
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
            $this->logger->error($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            $this->_errors[] = $this->_errors[] = $this->trans('There was an error during uninstall. Please contact us through Addons website. (for developers, consult shop logs)', [], 'Modules.Psgdpr.General');
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
                'title' => $this->trans('Data accessibility', [], 'Modules.Psgdpr.Faq'),
                'blocks' => [
                    [
                        'question' => $this->trans('How can a customer retrieve all of his personal data?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans('From his customer account, a new tab called My Personal Data is available and your customer can retrieve all of his personal data collected by your shop and installed modules, in PDF or CSV format.', [], 'Modules.Psgdpr.Faq'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Customer consent', [], 'Modules.Psgdpr.Faq'),
                'blocks' => [
                    [
                        'question' => $this->trans('There is no consent confirmation checkbox in the contact form. Isn\'t this a requirement?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans('No, it is not a requirement as the customer gives consent by clicking on the Submit message button. Only a message is required to give your customers more information about the use of personal data on your website. We are currently working on a new version of the contact form, it will be available really soon for your online store.', [], 'Modules.Psgdpr.Faq'),
                    ],
                ],
            ],
            [
                'title' => $this->trans('Data erasure', [], 'Modules.Psgdpr.General', [], 'Modules.Psgdpr.Faq'),
                'blocks' => [
                    [
                        'question' => $this->trans('How will a customer ask for all of his personal data to be deleted ?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans('The customer will send a message from the contact form for any rectification and erasure requests, justifying his request.', [], 'Modules.Psgdpr.Faq'),
                    ],
                    [
                        'question' => $this->trans('There is no Remove Data button in the customer account. Isn\'t this a requirement?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans("No, the Remove Data button in the customer account is not an obligation. For the data erasure requests, your customers can request data removal only under certain circumstances, that is the reason why we decided not to include an automatic Remove Data button in their customer account.\n\nThey can, however, contact you anytime via your contact form, in this case, you can review their request and once you accept it, you will be able to remove their personal data directly in the configuration page of our Official GDPR Compliance module.", [], 'Modules.Psgdpr.Faq'),
                    ],
                    [
                        'question' => $this->trans('How to remove the personal data of a customer?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans("If the request is valid, from the Personal Data Management tab of this module, any customer can be found by typing the first few letters of his name or email address in the search bar.\nBefore deleting any data, we recommend you to download all the invoices of the involved customer. After deleting the data with the “Remove data” button, the customer’s orders can’t be legally deleted, they just won’t be linked to any account. This allows you to keep precise statistics of your shop.", [], 'Modules.Psgdpr.Faq'),
                    ],
                    [
                        'question' => $this->trans('After removing all personal data of a customer from my database, what will happen to his orders?', [], 'Modules.Psgdpr.Faq'),
                        'answer' => $this->trans("Due to other legal obligations, his orders will still be stocked but they are no longer associated with the customer.\nOnly the name, shipping, and billing information must be kept in the order details page for legal reasons, invoicing, and accounting.\nAccording to the Rec.30;Art.7(1)(c)", [], 'Modules.Psgdpr.Faq'),
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
        /** @var Router $router */
        $router = $this->get('router');
        $psxlegalassistantControllerLink = $router->generate('psgdpr_admin_index');

        \Tools::redirectAdmin($psxlegalassistantControllerLink);
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

        Media::addJsDefL('psgdprNoAddresses', $this->trans('Customer data deleted by official GDPR module.', [], 'Modules.Psgdpr.General'));

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
     * @return mixed
     */
    public function hookActionCustomerAccountAdd(array $params): mixed
    {
        /** @var LoggerService $loggerService */
        $loggerService = $this->get('psgdpr.service.logger');

        if (!isset($params['newCustomer']) || !isset($params['newCustomer']->id)) {
            return false;
        }

        $customerId = new CustomerId($params['newCustomer']->id);
        $guestId = Context::getContext()->cart->id_guest;

        $loggerService->createLog($customerId, LoggerService::REQUEST_TYPE_CONSENT_COLLECTING, 0, $guestId);
    }

    /**
     * @return mixed
     */
    public function hookDisplayCustomerAccount(): mixed
    {
        $context = Context::getContext();

        $this->context->smarty->assign([
            'frontController' => Context::getContext()->link->getModuleLink($this->name, 'gdpr', [], true),
            'customerId' => $context->customer->id,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/front/account-gdpr-box.tpl');
    }

    /**
     * Allow to return the checkbox to display in modules
     *
     * @param array $params
     *
     * @return string html content to display
     */
    public function hookDisplayGDPRConsent($params): mixed
    {
        $context = Context::getContext();

        if (!isset($params['id_module'])) {
            return false;
        }

        $moduleId = (int) $params['id_module'];

        GDPRConsent::getConsentActive($moduleId);

        if ($active === false) {
            return false;
        }

        $message = GDPRConsent::getConsentMessage($moduleId, $context->language->id);

        $url = $context->link->getModuleLink($this->name, 'FrontAjaxGdpr', [], true);

        $id_customer = $context->customer->id;
        $id_guest = 0;
        if ($id_customer == null) {
            $id_guest = $context->cart->id_guest;
            $id_customer = 0;
        }
        $this->context->smarty->assign([
            'ps_version' => $this->psVersionIs17,
            'psgdpr_id_guest' => $id_guest,
            'psgdpr_id_customer' => $id_customer,
            'psgdpr_customer_token' => sha1($context->customer->secure_key),
            'psgdpr_guest_token' => sha1('psgdpr' . $id_guest . $_SERVER['REMOTE_ADDR'] . date('Y-m-d')),
            'psgdpr_id_module' => $moduleId,
            'psgdpr_consent_message' => $message,
            'psgdpr_front_controller' => $url,
        ]);

        return $this->fetch('module:' . $this->name . '/views/templates/hook/display-rgpd-consent.tpl');
    }

    /**
     * Execute raw sql query from specific folder
     * Used mainly for the install or uninstall of the psgdpr module
     *
     * @return bool
     */
    private function executeQuerySql(string $folder): bool
    {
        $sqlInstallFiles = scandir(dirname(__DIR__, 1) . '/sql/' . $folder);

        if (empty($sqlInstallFiles)) {
            return false;
        }

        foreach ($sqlInstallFiles as $file) {
            if (strpos($file, '.sql') === false) {
                continue;
            }

            $sqlInstallFile = dirname(__DIR__, 1) . '/sql/' . $folder . '/' . $sqlInstallFiles;

            $query = str_replace('PREFIX_', _DB_PREFIX_ , file_get_contents($sqlInstallFile));

            if (empty($query)) {
                continue;
            }

            try {
                /** @var EntityManager $entityManager */
                $entitymanager = $this->get('doctrine.orm.entity_manager');

                $entitymanager->getConnection()->executeQuery($query);
            } catch (Exception $e) {
                return false;
            }
        }
    }
}
