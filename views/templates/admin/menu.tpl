{*
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
*}

<div id="modulecontent" class="clearfix">
    <div id="psgdpr-menu">
        <div class="col-lg-2">
            <div class="list-group" v-on:click.prevent>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('getStarted') }" v-on:click="makeActive('getStarted')"><i class="fa fa-gavel"></i> {l s='Get started' mod='psgdpr'}</a>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('dataConfig') }" v-on:click="makeActive('dataConfig')"><i class="fa fa-user-secret"></i> {l s='Personal data management' mod='psgdpr'}</a>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('dataConsent') }" v-on:click="makeActive('dataConsent')"><i class="fa fa-check-square"></i> {l s='Consent checkbox customization' mod='psgdpr'}</a>
                <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('customerActivity') }" v-on:click="makeActive('customerActivity')"><i class="fa fa-user-circle"></i> {l s='Customer activity tracking' mod='psgdpr'}</a>
                {if ($apifaq != '')}
                    <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('faq') }" v-on:click="makeActive('faq')"><i class="fa fa-question-circle"></i> {l s='Help' mod='psgdpr'}</a>
                {/if}
            </div>
            <div class="list-group" v-on:click.prevent>
                <a class="list-group-item" style="text-align:center"><i class="icon-info"></i> {l s='Version' mod='psgdpr'} {$module_version|escape:'htmlall':'UTF-8'} | <i class="icon-info"></i> PrestaShop {$ps_version|escape:'htmlall':'UTF-8'}</a>
            </div>
        </div>
    </div>

    {* list your admin tpl *}
    <div id="getStarted" class="psgdpr_menu addons-hide">
        {include file="./tabs/getStarted.tpl"}
    </div>

    <div id="dataConfig" class="psgdpr_menu addons-hide">
        {include file="./tabs/dataConfig.tpl"}
    </div>

    <div id="dataConsent" class="psgdpr_menu addons-hide">
        {include file="./tabs/dataConsent.tpl"}
    </div>

    <div id="customerActivity" class="psgdpr_menu addons-hide">
        {include file="./tabs/customerActivity.tpl"}
    </div>

    <div id="faq" class="psgdpr_menu addons-hide">
        {if ($apifaq != '')}
            {include file="./tabs/help.tpl"}
        {/if}
    </div>

</div>

{* Use this if you want to send php var to your js *}
{literal}
<script type="text/javascript">
    var base_url = "{/literal}{$ps_base_dir|escape:'htmlall':'UTF-8'}{literal}";
    var isPs17 = "{/literal}{$isPs17|escape:'htmlall':'UTF-8'}{literal}";
    var moduleName = "{/literal}{$module_name|escape:'htmlall':'UTF-8'}{literal}";
    var currentPage = "{/literal}{$currentPage|escape:'htmlall':'UTF-8'}{literal}";
    var moduleAdminLink = "{/literal}{$moduleAdminLink|escape:'htmlall':'UTF-8'}{literal}";
    var adminController = "{/literal}{$adminController|escape:'htmlall':'UTF-8'}{literal}";
    var adminControllerInvoices = "{/literal}{$adminControllerInvoices|escape:'htmlall':'UTF-8'}{literal}";
    var ps_version = "{/literal}{$isPs17|escape:'htmlall':'UTF-8'}{literal}";
    var customer_link = "{/literal}{$customer_link|escape:'htmlall':'UTF-8'}{literal}";

    var messageSuccessCopy = "{/literal}{l s='Url has been copied to the clipboard!' mod='psgdpr'}{literal}";
    var messageSuccessInvoices = "{/literal}{l s='Invoices have been successfully downloaded.' mod='psgdpr'}{literal}";
    var messageErrorInvoices = "{/literal}{l s='No invoices available for this customer.' mod='psgdpr'}{literal}";
    var messageDeleteTitle = "{/literal}{l s='Are you sure?' mod='psgdpr'}{literal}";
    var messageDeleteText = "{/literal}{l s='Attention! This action is irreversible. Please make sure you have downloaded all of the customer’s invoices (if he has any) before clicking on Confirm erasure.' mod='psgdpr'}{literal}";
    var messageDeleteCancelText = "{/literal}{l s='Cancel action' mod='psgdpr'}{literal}";
    var messageDeleteConfirmText = "{/literal}{l s='Confirm Erasure' mod='psgdpr'}{literal}";
    var messageDeleteSuccess = "{/literal}{l s='The customer\'s data has been successfully deleted!' mod='psgdpr'}{literal}";
</script>
{/literal}
