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

$sql = array();

$sql[] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psgdpr_consent` (
        `id_gdpr_consent` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_module` int(10) unsigned NOT NULL,
        `active` int(10) NOT NULL,
        `error` int(10),
        `error_message` text,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_gdpr_consent`, `id_module`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psgdpr_consent_lang` (
        `id_gdpr_consent` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_lang` int(10) unsigned NOT NULL,
        `message` text,
        `id_shop` int(10) unsigned NOT NULL,
        PRIMARY KEY (`id_gdpr_consent`, `id_lang`, `id_shop`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

$sql[] = ' CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'psgdpr_log` (
        `id_gdpr_log` int(10) unsigned NOT NULL AUTO_INCREMENT,
        `id_customer` int(10) unsigned NULL,
        `id_guest` int(10) unsigned NULL,
        `client_name` varchar(250),
        `id_module` int(10) unsigned NOT NULL,
        `request_type` int(10) NOT NULL,
        `date_add` datetime NOT NULL,
        `date_upd` datetime NOT NULL,
        PRIMARY KEY (`id_gdpr_log`)
        ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=UTF8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
