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

<h2>{l s='Last sent emails' mod='psgdpr'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        <th class="header" valign="middle">{l s='Date' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Language' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Subject' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Template' mod='psgdpr'}</th>
    </tr>

    {if count($lastSentEmails) >= 1}
    {foreach from=$lastSentEmails item=email}
    <tr>
        <td class="center white">{$email['creationDate']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$email['language']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$email['subject']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$email['template']|escape:'html':'UTF-8'}</td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td colspan="5" class="center white">{l s='No emails' mod='psgdpr'}</td>
    </tr>
    {/if}
</table>
