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
     <i class="fa fa-list"></i> {l s='Customer activity list' d='Modules.Psgdpr.Admin'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
 </h3>
 <p>
     {l s='Keep track of your customer activity related to data accessibility, consent and erasure.' d='Modules.Psgdpr.Admin'}
 </p>
 <br>
 <div>
     <table id="customerLog" class="table table-striped table-bordered">
         <thead>
             <tr class="table-header">
                 <th class="text-center"><b>{l s='Client name/ID' d='Modules.Psgdpr.Admin'}</b></th>
                 <th class="text-center"><b>{l s='Type of request' d='Modules.Psgdpr.Admin'}</b></th>
                 <th class="text-center"><b>{l s='Submission date' d='Modules.Psgdpr.Admin'}</b></th>
             </tr>
         </thead>
         <tbody>
             {foreach from=$logs item=log}
               <tr>
                 <td class="text-center">{$log.client_name|escape:'htmlall':'UTF-8'}</td>
                 {if $log.request_type eq 1}
                 <td class="text-center">{l s='Consent confirmation' d='Modules.Psgdpr.Admin'}</td>
                 {/if}
                 {if $log.request_type eq 2}
                 <td class="text-center">{l s='Accessibility' d='Modules.Psgdpr.Admin'} (pdf)</td>
                 {/if}
                 {if $log.request_type eq 3}
                 <td class="text-center">{l s='Accessibility' d='Modules.Psgdpr.Admin'} (csv)</td>
                 {/if}
                 {if $log.request_type eq 4}
                 <td class="text-center">{l s='Erasure' d='Modules.Psgdpr.Admin'}</td>
                 {/if}
                 <td class="text-center">{$log.date_add|escape:'htmlall':'UTF-8'}</td>
             </tr>
             {/foreach}
         </tbody>
     </table>
 </div>
</div>
