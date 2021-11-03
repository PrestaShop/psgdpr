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

<h2>{l s='Messages' mod='psgdpr'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        <th class="header" valign="middle">{l s='IP' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Message' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Date' mod='psgdpr'}</th>
    </tr>

    {if count($messages) >= 1}
    {foreach from=$messages item=message}
    <tr>
        <td class="center white">{$message['ip']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$message['message']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$message['date_add']|escape:'html':'UTF-8'}</td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td colspan="3" class="center white">{l s='No messages' mod='psgdpr'}</td>
    </tr>
    {/if}
</table>
