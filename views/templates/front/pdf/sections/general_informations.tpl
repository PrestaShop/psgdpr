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


{$customerData = $customerInfo['data'][0]}
{$customerHeader = $customerInfo['headers']}

<h2>{l s='General info' d='Modules.Psgdpr.Shop'}</h2>
<br>
<table width="100%">
    <tr>
        <td width="47%">
            <table id="total-tab" width="100%">
                {foreach from=$customerData key=key item=data}
                {if $key <= 8}
                <tr>
                    <td class="grey" width="50%">
                        {$customerHeader[$key]}
                    </td>
                    <td class="white" width="50%">
                        {$customerData[$key]}
                    </td>
                </tr>
                {/if}
                {/foreach}
            </table>
        </td>
        <td width="5%"></td>
        <td width="47%">
            <table id="total-tab" width="100%">
                {foreach from=$customerData key=key item=data}
                {if $key > 8}
                <tr>
                    <td class="grey" width="50%">
                        {$customerHeader[$key]}
                    </td>
                    <td class="white" width="50%">
                        {$customerData[$key]}
                    </td>
                </tr>
                {/if}
                {/foreach}
            </table>
        </td>
    </tr>
</table>
