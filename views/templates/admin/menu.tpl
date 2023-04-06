{**
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
 *}

 <div id="modulecontent" class="clearfix">
 <div id="psgdpr-menu">
     <div class="col-lg-2">
         <div class="list-group" v-on:click.prevent>
             <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('getStarted') }" v-on:click="makeActive('getStarted')"><i class="fa fa-gavel"></i> {l s='Get started' d='Modules.Psgdpr.Admin'}</a>
             <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('dataConfig') }" v-on:click="makeActive('dataConfig')"><i class="fa fa-user-secret"></i> {l s='Personal data management' d='Modules.Psgdpr.Admin'}</a>
             <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('dataConsent') }" v-on:click="makeActive('dataConsent')"><i class="fa fa-check-square"></i> {l s='Consent checkbox customization' d='Modules.Psgdpr.Admin'}</a>
             <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('customerActivity') }" v-on:click="makeActive('customerActivity')"><i class="fa fa-user-circle"></i> {l s='Customer activity tracking' d='Modules.Psgdpr.Admin'}</a>
             <a href="#" class="list-group-item" v-bind:class="{ 'active': isActive('faq') }" v-on:click="makeActive('faq')"><i class="fa fa-question-circle"></i> {l s='Help' d='Modules.Psgdpr.Admin'}</a>
         </div>
         <div class="list-group" v-on:click.prevent>
             <a class="list-group-item" style="text-align:center"><i class="icon-info"></i> {l s='Version' d='Modules.Psgdpr.Admin'} {$module_version|escape:'htmlall':'UTF-8'} | <i class="icon-info"></i> PrestaShop {$ps_version|escape:'htmlall':'UTF-8'}</a>
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
     {include file="./tabs/help.tpl"}
 </div>

</div>

{* Use this if you want to send php var to your js *}
<script type="text/javascript">
 var base_url = "{$ps_base_dir|escape:'htmlall':'UTF-8'}";
 var moduleName = "{$module_name|escape:'htmlall':'UTF-8'}";
 var currentPage = "{$currentPage|escape:'htmlall':'UTF-8'}";
 var moduleAdminLink = "{$moduleAdminLink|escape:'htmlall':'UTF-8'}";
 var apiController = "{$api_controller|escape:'htmlall':'UTF-8'}";
 var adminToken = "{$admin_token|escape:'htmlall':'UTF-8'}";
 var customerLink = "{$customerLink|escape:'htmlall':'UTF-8'}";

 var messageSuccessCopy = "{l s='Url has been copied to the clipboard!' d='Modules.Psgdpr.Admin' js=1}";
 var messageSuccessInvoices = "{l s='Invoices have been successfully downloaded.' d='Modules.Psgdpr.Admin' js=1}";
 var messageErrorInvoices = "{l s='No invoices available for this customer.' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteTitle = "{l s='Are you sure?' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteText = "{l s='Attention! This action is irreversible. Please make sure you have downloaded all of the customer’s invoices (if he has any) before clicking on Confirm erasure.' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteCancelText = "{l s='Cancel action' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteConfirmText = "{l s='Confirm Erasure' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteSuccess = "{l s='The customer\'s data has been successfully deleted!' d='Modules.Psgdpr.Admin' js=1}";
 var messageDeleteError = "{l s='An error occurred while deleting the customer !' d='Modules.Psgdpr.Admin' js=1}";
 var datatableExport = "{l s='Export' d='Modules.Psgdpr.Admin' js=1}";
</script>
