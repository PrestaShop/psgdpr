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

{foreach from=$modules item=module key=key}
<h2>{$module['name']}</h2>
<br>

<table id="summary-tab" width="100%">
    <tr>
        {foreach from=$module['headers'] item=data key=index}
        <th class="header" valign="middle">{$data}</th>
        {/foreach}
    </tr>

    <tr>
        {foreach from=$module['data'] item=entry key=index}
            {foreach from=$entry item=value key=index}
                <td class="center white">{$value}</td>
            {/foreach}
        {/foreach}
    </tr>
</table>
{/foreach}
