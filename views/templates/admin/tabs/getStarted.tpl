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
 <div class="panel col-lg-10 right-panel">
 <h3>
     <i class="fa fa-info-circle"></i> {l s='Get started' d='Modules.Psgdpr.Admin'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
 </h3>
 <h2>{l s='Welcome to your GDPR module' d='Modules.Psgdpr.Admin'}</h2>
 <br>
 <p>{l s='This interface will help you to become familiar with the GDPR and give you some guidance to help you become compliant with this regulation.' d='Modules.Psgdpr.Admin'}</p>
 <p>{l s='This module meets the main regulation\'s requirements concerning personal data of your customers including :' d='Modules.Psgdpr.Admin'}</p>
 <ol type="1">
     <li>{l s='The right to access their personal data and data portability' d='Modules.Psgdpr.Admin'}</li>
     <li>{l s='The right to obtain rectification and/or erasure of their personal data' d='Modules.Psgdpr.Admin'}</li>
     <li>{l s='The right to give and withdraw consent' d='Modules.Psgdpr.Admin'}</li>
 </ol>
 <p>{l s='It also allows you to keep a record of processing activities (especially for access, consent and erasure).' d='Modules.Psgdpr.Admin'}</p>
 <p><b>{l s='Follow our 3 steps to configure your module and help you to become GDPR compliant!' d='Modules.Psgdpr.Admin'}</b></p>

 <div class="row">
     <div class="col-lg-1"></div>
     <div class="col-lg-3">
         <div class="psgdpr-card" data-target="dataConfig">
             <div class="card-header">
                 <h4 class="card-title-size"><i class="fa fa-eye"></i> <span class="card-title">{l s='Manage' d='Modules.Psgdpr.Admin'}</span></h4>
             </div>
             <div class="card-body">
                 <p class="card-text">{l s='See our Personal data management tab to visualize the data collected by PrestaShop and community modules and manage your customers’ personal data.' d='Modules.Psgdpr.Admin'}</p>
             </div>
         </div>
     </div>
     <div class="col-lg-3">
         <div class="psgdpr-card" data-target="dataConsent">
             <div class="card-header">
                 <h4 class="card-title-size"><i class="fa fa-pencil-alt"></i> <span class="card-title">{l s='Customize' d='Modules.Psgdpr.Admin'}</span></h4>
             </div>
             <div class="card-body">
                 <p class="card-text">{l s='Customize the consent confirmation checkboxes and consent request message on the different forms of your store, especially for account creation and newsletter subscription.' d='Modules.Psgdpr.Admin'}</p>
             </div>
         </div>
     </div>
     <div class="col-lg-3">
         <div class="psgdpr-card" data-target="customerActivity">
             <div class="card-header">
                 <h4 class="card-title-size"><i class="fa fa-search"></i> <span class="card-title">{l s='Keep track' d='Modules.Psgdpr.Admin'}</span></h4>
             </div>
             <div class="card-body">
                 <p class="card-text">{l s='Visualize all of your customers’ actions related to their data and manage the erasure requests.' d='Modules.Psgdpr.Admin'}</p>
             </div>
         </div>
     </div>
     <div class="col-lg-1"></div>
 </div>
 <br>

 <div role="alert" data-alert="info" class="alert alert-info">
     {l s='Note: Please make sure that you have access to the latest version of your installed module(s) to fully benefit the features of our GDPR module. If one or several of your modules do not provide their data list, we invite you to contact directly the developers of these modules.' d='Modules.Psgdpr.Admin'}
 </div>

 <br>

 <h3>{l s='More information about GDPR' d='Modules.Psgdpr.Admin'}</h3>

 <div class="row">
     <div class="col-lg-2"></div>
     <a href="http://ec.europa.eu/justice/article-29/structure/data-protection-authorities/" target="_blank">
         <div class="col-lg-2">
             <div class="psgdpr-card-useful-link">
                 <img src="{$img_path|escape:'htmlall':'UTF-8'}souris.png">
                 <h4 class="card-link">{l s='Data protection authorities websites' d='Modules.Psgdpr.Admin'}</h4>
             </div>
         </div>
     </a>
     <a href="{$doc|escape:'htmlall':'UTF-8'}" target="_blank">
         <div class="col-lg-2">
             <div class="psgdpr-card-useful-link">
                 <img src="{$img_path|escape:'htmlall':'UTF-8'}pdf.png">
                 <h4 class="card-link">{l s='Module\'s documentation' d='Modules.Psgdpr.Admin'}</h4>
             </div>
         </div>
     </a>
     <a href="{$youtubeLink|escape:'htmlall':'UTF-8'}" target="_blank">
         <div class="col-lg-2">
             <div class="psgdpr-card-useful-link">
                 <img src="{$img_path|escape:'htmlall':'UTF-8'}youtube.png">
                 <h4 class="card-link">{l s='Video' d='Modules.Psgdpr.Admin'}</h4>
             </div>
         </div>
     </a>
     <a href="http://build.prestashop.com/news/prestashop-and-gdpr/" target="_blank">
         <div class="col-lg-2">
             <div class="psgdpr-card-useful-link">
                 <img src="{$img_path|escape:'htmlall':'UTF-8'}journal.png">
                 <h4 class="card-link">{l s='Build article' d='Modules.Psgdpr.Admin'}</h4>
             </div>
         </div>
     </a>
     <div class="col-lg-2"></div>
 </div>

 <div role="alert" data-alert="info" class="alert alert-info">
     {l s='Note: These features are intended to help you to become GDPR compliant. However using them does not guarantee that your site is fully compliant with GDPR requirements.' d='Modules.Psgdpr.Admin'} <b>{l s='It is your own responsibility' d='Modules.Psgdpr.Admin'}</b> {l s='to configure the modules and take all necessary actions to ensure compliance. For any questions, we recommend you to contact a lawyer specializing in personal data legislation questions.' d='Modules.Psgdpr.Admin'}
 </div>
</div>
