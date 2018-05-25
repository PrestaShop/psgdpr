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

class GDPRLog extends ObjectModel
{
    public $id_gdpr_log;
    public $id_customer;
    public $id_guest;
    public $client_name;
    public $id_module;
    public $request_type;
    public $data_add;
    public $data_upd;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'psgdpr_log',
        'primary' => 'id_gdpr_log',
        'multishop' => true,
        'fields' => array(
            // Config fields
            'id_gdpr_log' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'id_customer' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'id_guest' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'client_name' => array('type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => false),
            'id_module' => array('type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => false),
            'request_type'  => array('type' => self::TYPE_BOOL, 'validate' => 'isInt', 'required' => true),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        )
    );

    /**
     * log consent
     *
     * @param int $id_customer id of the current logged customer
     * @param int $id_guest    id of the current guest
     * @param int $id_module   id of the module
     * @param bool $consent    true or false
     */
    public static function addLog($id_customer, $request_type, $id_module, $id_guest = false, $value = null)
    {
        $psgdpr = Module::getInstanceByName('psgdpr');
        if ($id_customer ==! 0) {
            $client_name = $psgdpr->getCustomerNameById((int)$id_customer);
            $id_guest = 0;
        } elseif ($value) {
            $client_name = $value;
        } else {
            $client_name = $psgdpr->l('Guest client : ID').$id_guest;
        }
        switch ($request_type) {
            case 'consent':
                $request_type = 1;
                break;
            case 'exportPdf':
                $request_type = 2;
                break;
            case 'exportCsv':
                $request_type = 3;
                break;
            case 'delete':
                $request_type = 4;
                break;
        }

        $sql = 'SELECT * FROM `'._DB_PREFIX_.'psgdpr_log`
            WHERE date_add = NOW()
            AND date_upd = NOW()
            AND id_customer = '.(int)$id_customer.'
            AND id_guest = '.(int)$id_guest.'
            AND client_name = "'.pSQL($client_name).'"
            AND id_module = '.(int)$id_module.'
            AND request_type = '.(int)$request_type;

        $exist = Db::getInstance()->getRow($sql);

        if (!$exist) {
            $sqlInsert = 'INSERT INTO `'._DB_PREFIX_.'psgdpr_log`(id_customer, id_guest, client_name, id_module, request_type, date_add, date_upd)
                VALUES ('.(int)$id_customer.', '.(int)$id_guest.', "'.pSQL($client_name).'", '.(int)$id_module.', '.(int)$request_type.', now(), now())';

            Db::getInstance()->execute($sqlInsert);
        }
    }

    public static function getLogs()
    {
        $sql = 'SELECT * FROM `'._DB_PREFIX_.'psgdpr_log`';
        $logs = Db::getInstance()->executeS($sql);

        $result = array();
        foreach ($logs as $log) {
            $module_name = '';
            if ($log['id_module'] ==! 0) {
                $module = Module::getInstanceById($log['id_module']);
                if ($module) {
                    $module_name = $module->displayName;
                }
            }
            array_push($result, array(
                'id_gdpr_log' => $log['id_gdpr_log'],
                'id_customer' => $log['id_customer'],
                'id_guest' => $log['id_guest'],
                'client_name' => $log['client_name'],
                'module_name' => $module_name,
                'id_module' => $log['id_module'],
                'request_type' => $log['request_type'],
                'date_add' => $log['date_add'],
            ));
            unset($module);
        }

        return $result;
    }
}
