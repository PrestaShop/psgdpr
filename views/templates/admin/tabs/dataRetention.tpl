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
<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-history"></i> {l s='Data retention' d='Modules.Psgdpr.Admin'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>

    <p>{l s='Automatically anonymize customers who have been inactive for too long, to comply with the GDPR storage-limitation principle (art. 5.1.e).' d='Modules.Psgdpr.Admin'}</p>
    <p>{l s='A customer is considered inactive when both his last connection and his last valid order are older than the period below.' d='Modules.Psgdpr.Admin'}</p>

    <article class="alert alert-info" role="alert" data-alert="info">
        <ul>
            <li>{l s='Personal data is anonymized, but orders and invoices are preserved to respect your legal accounting retention duties (e.g. 10 years).' d='Modules.Psgdpr.Admin'}</li>
            <li>{l s='Inactive customers receive a warning email with a reconnection link before anonymization. Logging back in resets their inactivity.' d='Modules.Psgdpr.Admin'}</li>
            <li>{l s='This job runs through the cron tasks module (ps_cronjobs). Make sure a cron is scheduled for this module.' d='Modules.Psgdpr.Admin'}</li>
        </ul>
    </article>

    <form method="post" action="{$link->getAdminLink('AdminModules', true, [], ['configure' => 'psgdpr', 'page' => 'dataRetention'])|escape:'htmlall':'UTF-8'}" class="form-horizontal">

        <div class="form-group">
            <label class="control-label col-lg-4">
                {l s='Enable automatic anonymization' d='Modules.Psgdpr.Admin'}
            </label>
            <div class="col-lg-8">
                <span class="switch prestashop-switch fixed-width-lg">
                    <input type="radio" name="PSGDPR_RETENTION_ENABLED" id="PSGDPR_RETENTION_ENABLED_on" value="1"{if $retention_enabled} checked="checked"{/if}>
                    <label for="PSGDPR_RETENTION_ENABLED_on">{l s='Yes' d='Modules.Psgdpr.Admin'}</label>
                    <input type="radio" name="PSGDPR_RETENTION_ENABLED" id="PSGDPR_RETENTION_ENABLED_off" value="0"{if !$retention_enabled} checked="checked"{/if}>
                    <label for="PSGDPR_RETENTION_ENABLED_off">{l s='No' d='Modules.Psgdpr.Admin'}</label>
                    <a class="slide-button btn"></a>
                </span>
                <p class="help-block">{l s='Disabled by default. Nothing is ever anonymized while this is off.' d='Modules.Psgdpr.Admin'}</p>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-4">
                {l s='Inactivity period (in days)' d='Modules.Psgdpr.Admin'}
            </label>
            <div class="col-lg-2">
                <input type="number" min="1" step="1" name="PSGDPR_RETENTION_INACTIVITY_DAYS" value="{$retention_inactivity_days|intval}" class="form-control">
            </div>
            <div class="col-lg-6">
                <p class="help-block">{l s='Recommended: 1095 days (3 years) for inactive customers, per CNIL guidance. Confirm the right duration with your DPO.' d='Modules.Psgdpr.Admin'}</p>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-4">
                {l s='Warning lead time (in days)' d='Modules.Psgdpr.Admin'}
            </label>
            <div class="col-lg-2">
                <input type="number" min="0" step="1" name="PSGDPR_RETENTION_WARN_DAYS" value="{$retention_warn_days|intval}" class="form-control">
            </div>
            <div class="col-lg-6">
                <p class="help-block">{l s='How long before anonymization the warning email is sent. Must be shorter than the inactivity period.' d='Modules.Psgdpr.Admin'}</p>
            </div>
        </div>

        <div class="panel-footer">
            <button type="submit" name="submitDataRetention" class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' d='Modules.Psgdpr.Admin'}
            </button>
        </div>
    </form>

    <hr>

    <h3>
        <i class="fa fa-history"></i> {l s='Scheduling' d='Modules.Psgdpr.Admin'}
    </h3>
    <p>{l s='PrestaShop has no built-in scheduler. Run the retention job from your server crontab with the console command below (it runs with the full PrestaShop container, including anonymization).' d='Modules.Psgdpr.Admin'}</p>
    <p>{l s='If the ps_cronjobs module is installed, the job also runs via its cron hook.' d='Modules.Psgdpr.Admin'}</p>

    <div class="form-group">
        <label class="control-label col-lg-4">{l s='Console command' d='Modules.Psgdpr.Admin'}</label>
        <div class="col-lg-8">
            <input type="text" class="form-control" onclick="this.select();" readonly="readonly" value="php bin/console psgdpr:data-retention:run">
            <p class="help-block">
                {l s='Example crontab entry (daily at 3am):' d='Modules.Psgdpr.Admin'}
                <br>
                <code>0 3 * * * cd /path/to/your/shop &amp;&amp; php bin/console psgdpr:data-retention:run</code>
            </p>
        </div>
    </div>
</div>
