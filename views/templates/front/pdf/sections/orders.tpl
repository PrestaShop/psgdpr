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

{$data = $orders['data']}
{$headers = $orders['headers']}


<h2>{l s='Orders' d='Modules.Psgdpr.Shop'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        <th class="header" valign="middle">{$headers[0]}</th>
        <th class="header" valign="middle">{$headers[1]}</th>
        <th class="header" valign="middle">{$headers[2]}</th>
        <th class="header" valign="middle">{$headers[3]}</th>
        <th class="header" valign="middle">{$headers[4]}</th>
    </tr>

    {if count($data) >= 1}
    {foreach from=$data item=order}
    <tr class="separator">
        <td class="center white"><b>{$order['reference']|escape:'html':'UTF-8'}</b></td>
        <td class="center white">{$order['payment']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$order['state']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$order['totalPaid']|escape:'html':'UTF-8'}</td>
        <td class="center white">{$order['date']|escape:'html':'UTF-8'}</td>
    </tr>
    <tr>
        <td colspan="3" class="center white"><b>{l s='Product(s) in the order' d='Modules.Psgdpr.Shop'} :</b></td>
        <td colspan="2" class="center white"></td>
    </tr>
    <tr>
        <td class="center white"></td>
        <td colspan="4" class="center white">
            <table id="total-tab" width="100%">
                <tr>
                    <th class="header" valign="middle"><i>{l s='Reference' d='Modules.Psgdpr.Shop'}</i></th>
                    <th class="header" valign="middle"><i>{l s='Name' d='Modules.Psgdpr.Shop'}</i></th>
                    <th class="header" valign="middle"><i>{l s='Quantity' d='Modules.Psgdpr.Shop'}</i></th>
                </tr>
                {foreach from=$order['products'] item=product}
                <tr>
                    <td class="center white">{$product['reference']|escape:'html':'UTF-8'}</td>
                    <td class="center white">{$product['name']|escape:'html':'UTF-8'}</td>
                    <td class="center white">{$product['quantity']|escape:'html':'UTF-8'}</td>
                </tr>
                {/foreach}
            </table>
        </td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td colspan="5" class="center white">{l s='No orders' d='Modules.Psgdpr.Shop'}</td>
    </tr>
    {/if}
</table>
