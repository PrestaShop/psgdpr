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

<h2>{l s='Last connections' d='Modules.Psgdpr.Shop'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        {foreach from=$lastConnections['headers'] item=header}
        <th class="header" valign="middle">{$header}</th>
        {/foreach}
    </tr>

    {if count($lastConnections['data']) >= 1}
    {foreach from=$lastConnections['data'] item=connection}
    <tr>
        <td class="center white">{$connection['connectionId']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$connection['httpReferer']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$connection['pagesViewed']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$connection['totalTime']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$connection['ipAddress']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$connection['date']|escape:'html':'UTF-8'}</td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td colspan="5" class="center white">{l s='No connections' d='Modules.Psgdpr.Shop'}</td>
    </tr>
    {/if}
</table>
