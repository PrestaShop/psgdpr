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

{$data = $carts['data']}
{$headers = $carts['headers']}

<h2>{l s='Carts' d='Modules.Psgdpr.Shop'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        <th class="header" valign="middle">{$headers[0]}</th>
        <th colspan="2" class="header" valign="middle">{$headers[1]}</th>
        <th colspan="2" class="header" valign="middle">{$headers[2]}</th>
    </tr>
    {if count($data) >= 1}
        {foreach from=$data item=cart}
        <tr class="separator">
            <td class="center white"><b>#{$cart['cartId']|escape:'html':'UTF-8'}</b></td>
            <td colspan="2" class="center white">{$cart['totalProducts']|escape:'html':'UTF-8'}</td>
            <td colspan="2" class="center white">{$cart['creationDate']|escape:'html':'UTF-8'}</td>
        </tr>
        {if count($cart['products']) >= 1}
        <tr>
            <td colspan="3" class="center white"><b>{l s='Product(s) in the cart' d='Modules.Psgdpr.Shop'} :</b></td>
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
                    {foreach from=$cart['products'] item=product}
                    <tr>
                        <td class="center white">{$product['reference']|escape:'html':'UTF-8'}</td>
                        <td class="center white">{$product['name']|escape:'html':'UTF-8'}</td>
                        <td class="center white">{$product['quantity']|escape:'html':'UTF-8'}</td>
                    </tr>
                    {/foreach}
                </table>
            </td>
        </tr>
        {/if}
        {/foreach}
    {else}
    <tr>
        <td colspan="5" class="center white">{l s='No carts' d='Modules.Psgdpr.Shop'}</td>
    </tr>
    {/if}
</table>
