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

{capture name=path}
    <a href="{$link->getPageLink('xxx', true)|escape:'html':'UTF-8'}">
        {l s='My personal data' mod='psgdpr'}
    </a>
    <span class="navigation-pipe">{$navigationPipe}</span>
    <span class="navigation_page">{l s='GDPR' mod='psgdpr'}</span>
{/capture}

{block name='page_content'}
<div>
    <h1 class="page-heading bottom-indent">{l s='My personal data' mod='psgdpr'}</h1>
    <div class="col-xs-12 psgdprinfo16">
        <h4 class="info-title">{l s='Access to my data' mod='psgdpr'}</h4>
        <p>{l s='At any time, you have the right to retrieve the data you have provided to our site. Click on "Get my data" to automatically download a copy of your personal data on a pdf or csv file.' mod='psgdpr'}.</p>
        <a class="btn btn-primary psgdprgetdatabtn16" target="_blank" href="{$psgdpr_csv_controller|escape:'htmlall':'UTF-8'}">{l s='GET MY DATA TO CSV' mod='psgdpr'}</a>
        <a class="btn btn-primary psgdprgetdatabtn16" target="_blank" href="{$psgdpr_pdf_controller|escape:'htmlall':'UTF-8'}">{l s='GET MY DATA TO PDF' mod='psgdpr'}</a>
    </div>
    <br>
    <div class="col-xs-12 psgdprinfo16">
        <h4>{l s='Rectification & Erasure requests' mod='psgdpr'}</h4>
        <p>{l s='You have the right to modify all the personal information found in the "My Account" page. For any other request you might have regarding the rectification and/or erasure of your personal data, please contact us through our' mod='psgdpr'} <a href="{$contactUrl|escape:'htmlall':'UTF-8'}">{l s='contact page' mod='psgdpr'}</a>. {l s='We will review your request and reply as soon as possible.' mod='psgdpr'}</p>
    </div>

    <ul class="footer_links clearfix">
        <li>
            <a class="btn btn-default button button-small" href="{$link->getPageLink("my-account", true)|escape:'html':'UTF-8'}">
                <span>
                    <i class="icon-chevron-left"></i> {l s='Back to Your Account' mod='psgdpr'}
                </span>
            </a>
        </li>
        <li>
            <a class="btn btn-default button button-small" href="{$base_dir}">
                <span><i class="icon-chevron-left"></i> {l s='Home' mod='psgdpr'}</span>
            </a>
        </li>
    </ul>
</div>
{literal}
<script type="text/javascript">
    var psgdpr_front_controller = "{/literal}{$psgdpr_front_controller|escape:'htmlall':'UTF-8'}{literal}";
    var psgdpr_id_customer = "{/literal}{$psgdpr_front_controller|escape:'htmlall':'UTF-8'}{literal}";
    var psgdpr_ps_version = "{/literal}{$psgdpr_ps_version|escape:'htmlall':'UTF-8'}{literal}";
</script>
{/literal}
{/block}
