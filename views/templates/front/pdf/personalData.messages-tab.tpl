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
