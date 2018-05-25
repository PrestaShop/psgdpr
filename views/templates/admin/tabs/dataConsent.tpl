{*
* 2007-2018 PrestaShop
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2018 PrestaShop SA
* @license   http://addons.prestashop.com/en/content/12-terms-and-conditions-of-use
* International Registered Trademark & Property of PrestaShop SA
*}
<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-wrench"></i> {l s='Configure your checkboxes' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <form method="post" action="{$moduleAdminLink|escape:'htmlall':'UTF-8'}&page=dataConsent" class="form-horizontal">
        <div>
            <p>{l s='Please customize your consent request messages in the dedicated fields below :' mod='psgdpr'}</p>
            <article class="alert alert-info" role="alert" data-alert="info">
                {l s='We recommend you to put a link to your confidentiality policy page in each of your custom messages. Be aware that a dedicated confidentiality policy page is required on your website; if you do not have one yet, please click' mod='psgdpr'} <a target="_blank" href="{$cmsConfPage|escape:'htmlall':'UTF-8'}">{l s='here' mod='psgdpr'}</a>.
            </article>
            <br><br>
            {* SWITCH CREATION ACCOUNT MESSAGE *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Account creation form' mod='psgdpr'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="yes" type="radio" name="psgdpr_creation_form_switch" id="switch_account_creation_on" data-toggle="collapse" data-target="#account_creation_message:not(.in)" value="1" {if $switchCreationForm eq 1}checked="checked"{/if}>
                        <label for="switch_account_creation_on" class="radioCheck">{l s='YES' mod='psgdpr'}</label>

                        <input class="no" type="radio" name="psgdpr_creation_form_switch" id="switch_account_creation_off" data-toggle="collapse" data-target="#account_creation_message.in" value="0" {if $switchCreationForm eq 0}checked="checked"{/if}>
                        <label for="switch_account_creation_off" class="radioCheck">{l s='NO' mod='psgdpr'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            {* SWITCH CREATION ACCOUNT MESSAGE *}
            {if $switchCreationForm eq 1}
            <div id="account_creation_message" class="collapse in">
            {else}
            <div id="account_creation_message" class="collapse">
            {/if}
            {* ACCOUNT CREATION MESSAGE *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label class="control-label">
                                {l s='Consent request message' mod='psgdpr'}
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-6">
                        <textarea class="autoload_rte" name="psgdpr_creation_form_{$language.id_lang|escape:'htmlall':'UTF-8'}" text="" rows="4" cols="80">{$accountCreationForm[$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                        <div class="help-block">
                            <p>{l s='This message will be displayed on the customer creation form' mod='psgdpr'}</p>
                        </div>
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'javascript'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* ACCOUNT CREATION MESSAGE *}
            </div>

            {* SWITCH CUSTOMER ACCOUNT AREA MESSAGE *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="boldtext control-label">{l s='Customer account area' mod='psgdpr'}</label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="yes" type="radio" name="psgdpr_customer_form_switch" id="switch_account_customer_on" data-toggle="collapse" data-target="#account_customer_message:not(.in)" value="1" {if $switchCustomerForm eq 1}checked="checked"{/if}>
                        <label for="switch_account_customer_on" class="radioCheck">{l s='YES' mod='psgdpr'}</label>

                        <input class="no" type="radio" name="psgdpr_customer_form_switch" id="switch_account_customer_off" data-toggle="collapse" data-target="#account_customer_message.in" value="0" {if $switchCustomerForm eq 0}checked="checked"{/if}>
                        <label for="switch_account_customer_off" class="radioCheck">{l s='NO' mod='psgdpr'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            {* SWITCH CUSTOMER ACCOUNT AREA MESSAGE *}
            {if $switchCustomerForm eq 1}
            <div id="account_customer_message" class="collapse in">
            {else}
            <div id="account_customer_message" class="collapse">
            {/if}
            {* CUSTOMER ACCOUNT AREA MESSAGE *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label class="control-label">
                                {l s='Consent request message' mod='psgdpr'}
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-6">
                        <textarea class="autoload_rte" name="psgdpr_customer_form_{$language.id_lang|escape:'htmlall':'UTF-8'}" text="" rows="4" cols="80">{$accountCustomerForm[$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                        <div class="help-block">
                            <p>{l s='This message will be displayed in the My personal information tab in the customer account' mod='psgdpr'}</p>
                        </div>
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'javascript'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* CUSTOMER ACCOUNT AREA MESSAGE *}
            </div>

            {if count($modules) >= 1}
            {foreach from=$modules item=module}
            {* REGISTERED SWITCH MODULE *}
            <div class="form-group">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                    <div class="text-right">
                        <label class="control-label"><b>{$module.displayName|escape:'htmlall':'UTF-8'}</b></label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-7">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input class="yes" type="radio" name="psgdpr_switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}" id="switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}_on" data-toggle="collapse" data-target="#registered_module_message_{$module.id_module|escape:'htmlall':'UTF-8'}:not(.in)" value="1" {if $module.active eq 1}checked="checked"{/if}>
                        <label for="switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}_on" class="radioCheck">{l s='YES' mod='psgdpr'}</label>

                        <input class="no" type="radio" name="psgdpr_switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}" id="switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}_off" data-toggle="collapse" data-target="#registered_module_message_{$module.id_module|escape:'htmlall':'UTF-8'}.in" value="0" {if $module.active eq 0}checked="checked"{/if}>
                        <label for="switch_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}_off" class="radioCheck">{l s='NO' mod='psgdpr'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
            {* REGISTERED SWITCH MODULE *}
            {if $module.active eq 1}
            <div id="registered_module_message_{$module.id_module|escape:'htmlall':'UTF-8'}" class="collapse in">
            {else}
            <div id="registered_module_message_{$module.id_module|escape:'htmlall':'UTF-8'}" class="collapse">
            {/if}
            {* REGISTERED MODULE CONSENT MESSAGE *}
            {foreach from=$languages item=language}
                {if $languages|count > 1}
                    <div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
                {/if}
                <div class="form-group">
                    <div class="col-xs-12 col-sm-12 col-md-5 col-lg-3">
                        <div class="text-right">
                            <label class="control-label">
                                <p>{l s='Consent request message' mod='psgdpr'}</p>
                                <img src="{$module.logoPath|escape:'htmlall':'UTF-8'}" width="50" heigh="50">
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-7 col-lg-6">
                        <textarea class="autoload_rte" name="psgdpr_registered_module_{$module.id_module|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" text="" rows="4" cols="80">{$module.message[$language.id_lang]|escape:'htmlall':'UTF-8'}</textarea>
                        <div class="help-block">
                            <p>{l s='This message will be accomplanied by a checkbox' mod='psgdpr'}</p>
                        </div>
                    </div>
                    {if $languages|count > 1}
                        <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                            <button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
                                {$language.iso_code|escape:'htmlall':'UTF-8'}
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                {foreach from=$languages item=lang}
                                <li><a class="currentLang" data-id="{$lang.id_lang|escape:'htmlall':'UTF-8'}" href="javascript:hideOtherLanguage({$lang.id_lang|escape:'javascript'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
                                {/foreach}
                            </ul>
                        </div>
                    {/if}
                </div>
                {if $languages|count > 1}
                    </div>
                {/if}
            {/foreach}
            {* REGISTERED MODULE CONSENT MESSAGE*}
            </div>
            {/foreach}
            {/if}

        </div>
        <article class="alert alert-info" role="alert" data-alert="info">
            {l s='For other installed modules requiring consent confirmation, they will be displayed in this tab only if they have done the GDPR update. The corresponding fields will automatically appear in order for you to customize the consent confirmation checkboxes.' mod='psgdpr'}
        </article>
        <div class="panel-footer">
            <button type="submit" value="1" id="submitDataConsent" name="submitDataConsent" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='psgdpr'}
            </button>
        </div>
    </form>
</div>