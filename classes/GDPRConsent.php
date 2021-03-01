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
class GDPRConsent extends ObjectModel
{
    /**
     * @var int
     */
    public $id_module;

    /**
     * @var bool
     */
    public $active;

    /**
     * @var bool
     */
    public $error;

    /**
     * @var string
     */
    public $error_message;

    /**
     * @var string|string[] Translated field
     */
    public $message;

    /**
     * @var string
     */
    public $date_add;

    /**
     * @var string
     */
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'psgdpr_consent',
        'primary' => 'id_gdpr_consent',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => [
            // Config fields
            'id_module' => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'error' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false],
            'error_message' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => false],
            // Lang fields
            'message' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000],
            'date_add' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * Return the list of all the modules registered on our hook and active
     *
     * @return array who contains id_module, message
     */
    public static function getAllRegisteredModules()
    {
        $modules = Db::getInstance()->executeS('
            SELECT psgdpr.id_gdpr_consent, psgdpr.id_module
            FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            INNER JOIN `' . _DB_PREFIX_ . 'module` module ON (module.id_module = psgdpr.id_module)'
        );

        if (empty($modules)) {
            return [];
        }

        return $modules;
    }

    /**
     * Return the Consent Message registered for a specificed module in the right language
     *
     * @param int $id_module id of the specified module
     * @param int $id_lang id of the language used
     *
     * @return string the Consent Message
     */
    public static function getConsentMessage($id_module, $id_lang)
    {
        $message = Db::getInstance()->getValue('
            SELECT psgdprl.message FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            LEFT JOIN ' . _DB_PREFIX_ . 'psgdpr_consent_lang psgdprl ON (psgdpr.id_gdpr_consent = psgdprl.id_gdpr_consent)
            WHERE psgdpr.id_module = ' . (int) $id_module . ' AND psgdprl.id_lang =' . (int) $id_lang
        );

        if (empty($message)) {
            return '';
        }

        return $message;
    }

    /**
     * Return the Consent module active
     *
     * @param int $id_module id of the specified module
     *
     * @return bool if the module consent is enable or not
     */
    public static function getConsentActive($id_module)
    {
        return (bool) Db::getInstance()->getValue('
            SELECT psgdpr.active FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            WHERE psgdpr.id_module = ' . (int) $id_module
        );
    }

    /**
     * Allow to know if the module has been already added in the database
     *
     * @param int $id_module id of the module
     * @param int $id_shop id of the current shop
     *
     * @return bool true if the module already exist or false if not
     */
    public static function checkIfExist($id_module, $id_shop)
    {
        return (bool) Db::getInstance()->getValue('
            SELECT id_module FROM `' . _DB_PREFIX_ . 'psgdpr_consent` psgdpr
            LEFT JOIN ' . _DB_PREFIX_ . 'psgdpr_consent_lang psgdprl ON (psgdpr.id_gdpr_consent = psgdprl.id_gdpr_consent)
            WHERE psgdpr.id_module = ' . (int) $id_module . ' AND psgdprl.id_shop =' . (int) $id_shop
        );
    }
}
