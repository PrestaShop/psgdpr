<?php
/**
* 2007-2018 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2018 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class GDPRConsent extends ObjectModel
{
    public $id;
    public $id_module;
    public $active;
    public $error;
    public $error_message;
    public $date_add;
    public $date_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'psgdpr_consent',
        'primary' => 'id_gdpr_consent',
        'multilang' => true,
        'multilang_shop' => true,
        'fields' => array(
            // Config fields
            'id_module' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true),
            'error'  => array('type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false),
            'error_message'  => array('type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => false),
            // Lang fields
            'message' => array('type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 4000),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    public function add($autoDate = true, $nullValues = false)
    {
        $return = parent::add($autoDate, $nullValues);

        return $return;
    }

    /**
     * Return the list of all the modules registered on our hook and active
     *
     * @param  int $id_lang language of the shop
     *
     * @return array who contains id_module, message
     */
    public static function getAllRegisteredModules()
    {
        $sql = 'SELECT psgdpr.id_gdpr_consent, psgdpr.id_module FROM `'._DB_PREFIX_.'psgdpr_consent` psgdpr';

        $result = Db::getInstance()->executeS($sql);

        return $result;
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
        $sql = 'SELECT psgdprl.message FROM `'._DB_PREFIX_.'psgdpr_consent` psgdpr
            LEFT JOIN '._DB_PREFIX_.'psgdpr_consent_lang psgdprl ON (psgdpr.id_gdpr_consent = psgdprl.id_gdpr_consent)
            WHERE psgdpr.id_module = '.(int)$id_module.' AND psgdprl.id_lang ='.(int)$id_lang;

        $result = Db::getInstance()->getValue($sql);

        return $result;
    }

    /**
     * Return the Consent module active
     *
     * @param int $id_module id of the specified module
     *
     * @return int if the module consent is enable or not
     */
    public static function getConsentActive($id_module)
    {
        $sql = 'SELECT psgdpr.active FROM `'._DB_PREFIX_.'psgdpr_consent` psgdpr
            WHERE psgdpr.id_module = '.(int)$id_module;

        $result = (bool) Db::getInstance()->getValue($sql);

        return $result;
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
        $sql = 'SELECT id_module FROM `'._DB_PREFIX_.'psgdpr_consent` psgdpr
            LEFT JOIN '._DB_PREFIX_.'psgdpr_consent_lang psgdprl ON (psgdpr.id_gdpr_consent = psgdprl.id_gdpr_consent)
            WHERE psgdpr.id_module = '.(int)$id_module.' AND psgdprl.id_shop ='.(int)$id_shop;
        $result = Db::getInstance()->getRow($sql);

        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}
