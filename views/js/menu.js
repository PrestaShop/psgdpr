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
$(window).ready(function() {
    moduleAdminLink = moduleAdminLink.replace(/\amp;/g,'');

    window.vMenu = new Vue({
        el: '#psgdpr-menu',
        data: {
            selectedTabName : currentPage,
        },
        methods: {
            makeActive: function(item){
                this.selectedTabName = item;
                if (ps_version) { // if on 1.7
                    window.history.pushState({} , '', moduleAdminLink+'&page='+item );
                } else { // if on 1.6
                    window.history.pushState({} , '', moduleAdminLink+'&configure='+moduleName+'&module_name='+moduleName+'&page='+item );
                }
            },
            isActive : function(item){
                if (this.selectedTabName == item) {
                    $('.psgdpr_menu').addClass('addons-hide');
                    $('#'+item).removeClass('addons-hide');
                    return true;
                }
            }
        }
    });
});
