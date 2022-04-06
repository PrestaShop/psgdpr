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
        <i class="fa fa-question-circle"></i> {l s='Help & contact' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <div class="helpContentParent">
        <div class="helpContentLeft">
            <div class="left">
                <img src="{$logo_path|escape:'htmlall':'UTF-8'}" alt=""/>
            </div>
            <div class="right">
                <p><span class="data_label" style="color:#00aff0;"><b>{l s='This module allows you to :' mod='psgdpr'}</b></span></p>
                <br>
                <div>
                    <div class="numberCircle">1</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Erase any customer account with his/her personal data collected by your shop if requested by the customer' mod='psgdpr'}
                    </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">2</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Add a consent confirmation checkbox in a module form that collects personal data and customize it' mod='psgdpr'}
                    </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">3</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='Allow your customer to consult and export their personal data collected by your shop on their customer account' mod='psgdpr'}
                    </p>
                    </div>
                </div>
                <div>
                    <div class="numberCircle">4</div>
                    <div class="numberCircleText">
                    <p class="numberCircleText">
                        {l s='View all your customers’ actions related to their personal data' mod='psgdpr'}
                    </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="helpContentRight">
            <div class="helpContentRight-sub">
                <b>{l s='Need help ?' mod='psgdpr'}</b><br>
                {l s='Find here the documentation of this module' mod='psgdpr'}
                <a class="btn btn-primary" href="{$doc|escape:'htmlall':'UTF-8'}" target="_blank" style="margin-left:20px;" href="#">
                    <i class="fa fa-book"></i>&nbsp;{l s='Documentation' mod='psgdpr'}</a>
                </a>
                <br><br>
                <div class="tab-pane panel" id="faq">
                    <div class="panel-heading"><i class="icon-question"></i> {l s='FAQ' mod='psgdpr'}</div>
                    {foreach from=$faq item=category name='faq'}
                        <span class="faq-h1">{$category.title|escape:'htmlall':'UTF-8'}</span>
                        <ul>
                            {foreach from=$category.blocks item=qa}
                                {if !empty($qa.question)}
                                    <li>
                                        <span class="faq-h2"><i class="icon-info-circle"></i> {$qa.question|escape:'htmlall':'UTF-8'}</span>
                                        <p class="faq-text hide">
                                            {$qa.answer|escape:'htmlall':'UTF-8'|replace:"\n":"<br />"}
                                        </p>
                                    </li>
                                {/if}
                            {/foreach}
                        </ul>
                        {if !$smarty.foreach.faq.last}<hr/>{/if}
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
