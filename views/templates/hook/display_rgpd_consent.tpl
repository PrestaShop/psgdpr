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
{block name='gdpr_checkbox'}
    <div class="gdpr_consent gdpr_module_{$psgdpr_id_module|escape:'htmlall':'UTF-8'}">
        <span class="custom-checkbox">
            <label class="psgdpr_consent_message">
                <input id="psgdpr_consent_checkbox_{$psgdpr_id_module|escape:'htmlall':'UTF-8'}" name="psgdpr_consent_checkbox" type="checkbox" value="1" class="psgdpr_consent_checkboxes_{$psgdpr_id_module|escape:'htmlall':'UTF-8'}">
                <span><i class="material-icons rtl-no-flip checkbox-checked psgdpr_consent_icon"></i></span>
                <span>{$psgdpr_consent_message nofilter}</span>{* html data *}
            </label>
        </span>
    </div>
{/block}
{literal}
<script type="text/javascript">
    var psgdpr_front_controller = "{/literal}{$psgdpr_front_controller|escape:'htmlall':'UTF-8'}{literal}";
    psgdpr_front_controller = psgdpr_front_controller.replace(/\amp;/g,'');
    var psgdpr_id_customer = "{/literal}{$psgdpr_id_customer|escape:'htmlall':'UTF-8'}{literal}";
    var psgdpr_customer_token = "{/literal}{$psgdpr_customer_token|escape:'htmlall':'UTF-8'}{literal}";
    var psgdpr_id_guest = "{/literal}{$psgdpr_id_guest|escape:'htmlall':'UTF-8'}{literal}";
    var psgdpr_guest_token = "{/literal}{$psgdpr_guest_token|escape:'htmlall':'UTF-8'}{literal}";

    document.addEventListener('DOMContentLoaded', function() {
        let psgdpr_id_module = "{/literal}{$psgdpr_id_module|escape:'htmlall':'UTF-8'}{literal}";
        let parentForm = $('.gdpr_module_' + psgdpr_id_module).closest('form');

        let toggleFormActive = function() {
            let parentForm = $('.gdpr_module_' + psgdpr_id_module).closest('form');
            let checkbox = $('#psgdpr_consent_checkbox_' + psgdpr_id_module);
            let element = $('.gdpr_module_' + psgdpr_id_module);
            let iLoopLimit = 0;

            // by default forms submit will be disabled, only will enable if agreement checkbox is checked
            if (element.prop('checked') != true) {
                element.closest('form').find('[type="submit"]').attr('disabled', 'disabled');
            }
            $(document).on("change" ,'.psgdpr_consent_checkboxes_' + psgdpr_id_module, function() {
                if ($(this).prop('checked') == true) {
                    $(this).closest('form').find('[type="submit"]').removeAttr('disabled');
                } else {
                    $(this).closest('form').find('[type="submit"]').attr('disabled', 'disabled');
                }

            });
        }

        // Triggered on page loading
        toggleFormActive();

        $(document).on('submit', parentForm, function(event) {
            $.ajax({
                type: 'POST',
                url: psgdpr_front_controller,
                data: {
                    ajax: true,
                    action: 'AddLog',
                    id_customer: psgdpr_id_customer,
                    customer_token: psgdpr_customer_token,
                    id_guest: psgdpr_id_guest,
                    guest_token: psgdpr_guest_token,
                    id_module: psgdpr_id_module,
                },
                error: function (err) {
                    console.log(err);
                }
            });
        });
    });
</script>
{/literal}
