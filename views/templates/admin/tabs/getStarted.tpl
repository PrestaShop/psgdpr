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
        <i class="fa fa-info-circle"></i> {l s='Get started' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <h2>{l s='Welcome to your GDPR module' mod='psgdpr'}</h2>
    <br>
    <p>{l s='This interface will help you to become familiar with the GDPR and give you some guidance to help you become compliant with this regulation.' mod='psgdpr'}</p>
    <p>{l s='This module meets the main regulation\'s requirements concerning personal data of your customers including :' mod='psgdpr'}</p>
    <ol type="1">
        <li>{l s='The right to access their personal data and data portability' mod='psgdpr'}</li>
        <li>{l s='The right to obtain rectification and/or erasure of their personal data' mod='psgdpr'}</li>
        <li>{l s='The right to give and withdraw consent' mod='psgdpr'}</li>
    </ol>
    <p>{l s='It also allows you to keep a record of processing activities (especially for access, consent and erasure).' mod='psgdpr'}</p>
    <p><b>{l s='Follow our 3 steps to configure your module and help you to become GDPR compliant !' mod='psgdpr'}</b></p>

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-3">
            <div class="psgdpr-card" data-target="dataConfig">
                <div class="card-header">
                    <h4 class="card-title-size"><i class="fa fa-eye"></i> <span class="card-title">{l s='Manage' mod='psgdpr'}</span></h4>
                </div>
                <div class="card-body">
                    <p class="card-text">{l s='See our Personal data management tab to visualize the data collected by PrestaShop and community modules and manage your customers’ personal data.' mod='psgdpr'}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="psgdpr-card" data-target="dataConsent">
                <div class="card-header">
                    <h4 class="card-title-size"><i class="fa fa-pencil-alt"></i> <span class="card-title">{l s='Customize' mod='psgdpr'}</span></h4>
                </div>
                <div class="card-body">
                    <p class="card-text">{l s='Customize the consent confirmation checkboxes and consent request message on the different forms of your store, especially for account creation and newsletter subscription.' mod='psgdpr'}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="psgdpr-card" data-target="customerActivity">
                <div class="card-header">
                    <h4 class="card-title-size"><i class="fa fa-search"></i> <span class="card-title">{l s='Keep track' mod='psgdpr'}</span></h4>
                </div>
                <div class="card-body">
                    <p class="card-text">{l s='Visualize all of your customers’ actions related to their data and manage the erasure requests.' mod='psgdpr'}</p>
                </div>
            </div>
        </div>
        <div class="col-lg-1"></div>
    </div>
    <br>

    <div role="alert" data-alert="info" class="alert alert-info">
        {l s='Note : Please make sure that you have access to the latest version of your installed module(s) to fully benefit the features of our GDPR module. If one or several of your modules do not provide their data list, we invite you to contact directly the developers of these modules.' mod='psgdpr'}
    </div>

    <br>

    <h3>{l s='More information about GDPR' mod='psgdpr'}</h3>

    <div class="row">
        <div class="col-lg-1"></div>
        <a href="http://ec.europa.eu/justice/article-29/structure/data-protection-authorities/" target="_blank">
            <div class="col-lg-2">
                <div class="psgdpr-card-useful-link">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}souris.png">
                    <h4 class="card-link">{l s='Data protection authorities websites' mod='psgdpr'}</h4>
                </div>
            </div>
        </a>
        <a href="https://addons.prestashop.com/en/free-prestashop-modules/31944-gdpr-whitepaper-.html" target="_blank">
            <div class="col-lg-2">
                <div class="psgdpr-card-useful-link">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}carnet.png">
                    <h4 class="card-link">{l s='PrestaShop GDPR whitepaper' mod='psgdpr'}</h4>
                </div>
            </div>
        </a>
        <a href="{$doc|escape:'htmlall':'UTF-8'}" target="_blank">
            <div class="col-lg-2">
                <div class="psgdpr-card-useful-link">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}pdf.png">
                    <h4 class="card-link">{l s='Module\'s documentation' mod='psgdpr'}</h4>
                </div>
            </div>
        </a>
        <a href="{$youtubeLink|escape:'htmlall':'UTF-8'}" target="_blank">
            <div class="col-lg-2">
                <div class="psgdpr-card-useful-link">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}youtube.png">
                    <h4 class="card-link">{l s='Video' mod='psgdpr'}</h4>
                </div>
            </div>
        </a>
        <a href="http://build.prestashop.com/news/prestashop-and-gdpr/" target="_blank">
            <div class="col-lg-2">
                <div class="psgdpr-card-useful-link">
                    <img src="{$img_path|escape:'htmlall':'UTF-8'}journal.png">
                    <h4 class="card-link">{l s='Build article' mod='psgdpr'}</h4>
                </div>
            </div>
        </a>
        <div class="col-lg-1"></div>
    </div>

    <div role="alert" data-alert="info" class="alert alert-info">
        {l s='Note : These features are intended to help you to become GDPR compliant. However using them does not guarantee that your site is fully compliant with GDPR requirements. It is ' mod='psgdpr'} <b>{l s='It is your own responsibility' mod='psgdpr'}</b> {l s='to configure the modules and take all necessary actions to ensure compliance. For any questions, we recommend you to contact a lawyer specializing in personal data legislation questions.' mod='psgdpr'}
    </div>
</div>
