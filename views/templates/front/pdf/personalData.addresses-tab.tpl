{**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<h2>{l s='Addresses' mod='psgdpr'}</h2>
<br>
<table id="summary-tab" width="100%">
    <tr>
        <th class="header" valign="middle">{l s='Alias' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Company' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Name' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Address' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Phone(s)' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Country' mod='psgdpr'}</th>
        <th class="header" valign="middle">{l s='Date' mod='psgdpr'}</th>
    </tr>

    {if count($addresses) >= 1}
    {foreach from=$addresses item=address}
    <tr>
        <td class="center white">{$address['alias']}</td>
        <td class="center white">{$address['company']}</td>
        <td class="center white">{$address['firstname']} {$address['lastname']}</td>
        <td class="center white">{$address['address1']} {$address['address2']} {$address['postcode']} {$address['city']}</td>
        <td class="center white">{$address['phone']} {$address['phone_mobile']}</td>
        <td class="center white">{$address['country']}</td>
        <td class="center white">{$address['date_add']}</td>
    </tr>
    {/foreach}
    {else}
    <tr>
        <td colspan="7" class="center white">{l s='No addresses' mod='psgdpr'}</td>
    </tr>
    {/if}
</table>

