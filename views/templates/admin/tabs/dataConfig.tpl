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
        <i class="fa fa-cogs"></i> {l s='Data visualization and automatic actions' d='Modules.Psgdpr.Admin'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <form method="post" action="{$moduleAdminLink|escape:'htmlall':'UTF-8'}&page=account" class="form-horizontal">
        <div>
            <p>{l s='Find here listed all personal data collected by PrestaShop and your installed modules.' d='Modules.Psgdpr.Admin'}</p>
            <p>{l s='These data will be used at 2 different levels :' d='Modules.Psgdpr.Admin'}</p>
            <ul>
                <li>{l s='When a customer requests access to his data: he gets a copy of his personal data collected on your store.' d='Modules.Psgdpr.Admin'}</li>
                <li>{l s='When a customer requests data erasure: if you accept his request, his data will be removed permanently.' d='Modules.Psgdpr.Admin'}</li>
            </ul>
            <br>

            <div class="panel panel-box col-lg-12">
                <h3>
                    <i class="fa fa-list"></i> {l s='Compliant module list' d='Modules.Psgdpr.Admin'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
                </h3>
                <p>{l s='Find here listed all the elements that are GDPR compliant.' d='Modules.Psgdpr.Admin'}</p>

                <div class="registered-modules">
                    <div class="module-card">
                        <div class="module-card-content">
                            <div class="module-card-img">
                                <img src="{$img_path|escape:'htmlall':'UTF-8'}PrestaShop_logo_puffin.png" width="45" heigh="45">
                            </div>
                            <div class="module-card-title">
                                <span>{l s='PrestaShop data' d='Modules.Psgdpr.Admin'}</span>
                            </div>
                        </div>
                    </div>
                    {foreach from=$modules item=module}
                    <div class="module-card">
                        <div class="module-card-content">
                            <div class="module-card-img">
                                <img src="{$module.logoPath|escape:'htmlall':'UTF-8'}" width="45" heigh="45">
                            </div>
                            <div class="module-card-title">
                                <span>{$module.displayName|escape:'htmlall':'UTF-8'}</span>
                            </div>
                        </div>
                    </div>
                    {/foreach}
                </div>

                <article class="alert alert-info" role="alert" data-alert="warning">
                    <ul>
                        <li>{l s='Please make sure that you have access to the latest version of these modules to fully benefit the GDPR update.' d='Modules.Psgdpr.PersonalDataManagement'}</li>
                        <li>{l s='If they are still not displayed in the block above, we invite you to contact their respective developers to have more information about these modules. ' d='Modules.Psgdpr.PersonalDataManagement'}</li>
                    </ul>
                </article>
            </div>

        </div>
    </form>
</div>

<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-database"></i> {l s='Manage customer\'s personal data' d='Modules.Psgdpr.PersonalDataManagement'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>

    <div id="customerSearchBlock">
        <form id="search" class="form-horizontal" action="" @submit.prevent="onSubmit">
            {* SEARCH CUSTOMER BLOCK *}
            <div class="form-group" style="margin-bottom: 0px !important">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                    <div class="text-right">
                        <label class="control-label">
                            <span class="label-tooltip" data-original-title="{l s='Search for an existing customer by typing the first letters of his/her name or email.' d='Modules.Psgdpr.PersonalDataManagement'}">
                                {l s='Search for a customer name OR email' d='Modules.Psgdpr.PersonalDataManagement'}
                            </span>
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                    <div class="input-group"> <span class="input-group-addon"><i class="fa fa-search"></i></span> <input @keyup="searchCustomers()" v-model="phraseSearched" class="form-control"> </div>
                    <div class="help-block">
                        <p>{l s='Eg: john doe ...' d='Modules.Psgdpr.PersonalDataManagement'}</p>
                    </div>
                </div>
            </div>
            {* SEARCH CUSTOMER BLOCK *}
        </form>

        <article v-if="typeof customers != 'undefined' && customers.length >= 1" class="alert alert-info" role="alert" data-alert="info" style="margin-bottom: 0px !important">
            {l s='To visualize all the data that your store has collected from a specific customer, please click on the corresponding customer block' d='Modules.Psgdpr.PersonalDataManagement'}
        </article>
        <article v-if="found == false && phraseSearched.length > 0" class="alert alert-warning" role="alert" data-alert="warning">
            <p>{l s='There is no result in the customer data base for' d='Modules.Psgdpr.PersonalDataManagement'} : (( phraseSearched ))</p>
            <p v-if="!isEmail && !isPhoneNumber">{l s='If you are looking for someone without a customer account, please search for the complete email address or phone number he left.' d='Modules.Psgdpr.PersonalDataManagement'}</p>
            <p v-if="isEmail || isPhoneNumber">{l s='However you can continue the erasure process for this address (only for modules that have done the GDPR update).' d='Modules.Psgdpr.PersonalDataManagement'}</p>
        </article>
        <div class="customerCards">
            <div v-for="(customer, index) in customers" :id="'customer_'+customer.idCustomer" class="customerCard is-collapsed">
                <div class="panel card-inner" @click="getCustomerInfos('customer', customer.idCustomer, 'customer_'+customer.idCustomer, index)">
                    <div class="panel-heading">
                        <span>(( customer.firstname ))</span> (( customer.lastname ))<span class="pull-right">#(( customer.idCustomer ))</span>
                    </div>
                    <div class="panel-body">
                        <span>(( customer.email ))</span>
                        <br>
                        <span class="text-muted">{l s='Orders number' d='Modules.Psgdpr.PersonalDataManagement'}: (( customer.nb_orders ))</span>
                    </div>
                    <div class="panel-footer">
                        <a @click.stop :href="customerLink.replace(/(id_customer=|customers\/)0/gi, '$1'+customer.idCustomer)" target="_blank" class="btn btn-default fancybox"><i class="icon-search"></i> {l s='Details' d='Modules.Psgdpr.PersonalDataManagement'}</a>
                        <button type="button" @click.stop="deleteCustomerData('customer', customer.idCustomer, index)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' d='Modules.Psgdpr.PersonalDataManagement'}</button>
                        <a @click.stop="downloadInvoices(customer.idCustomer, index)" class="btn btn-primary pull-right"><i class="icon-download"></i> {l s='Download invoices' d='Modules.Psgdpr.PersonalDataManagement'}</a>
                    </div>
                </div>
                <div class="panel card-expander" v-if="customer.customerData">
                    <div class="panel-body">
                        <div v-if="customer.customerData.personalinformations" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='General information' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <div class="col-lg-12">
                                <div class="col-lg-6">
                                    <div class="form-horizontal">
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Gender' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].gender ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Name' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].firstname )) (( customer.customerData.personalinformations.data[0].lastname ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Birth date' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].birthday ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Age' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].age ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Email' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].email ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Language' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].language ))</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-horizontal">
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Creation date' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].dateAdd ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Last visit' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].lastVisit ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Siret' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].siret ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Ape' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].ape ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Company' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].company ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Website' d='Modules.Psgdpr.PersonalDataManagement'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.personalinformations.data[0].website ))</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="customer.customerData.addresses" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Addresses' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.addresses.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Alias' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Company' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Full name' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Full address' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Country' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Phone' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Mobile phone' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="address in customer.customerData.addresses.data">
                                        <td>(( address.alias ))</td>
                                        <td>(( address.company ))</td>
                                        <td>(( address.fullName ))</td>
                                        <td>(( address.fullAddress ))</td>
                                        <td>(( address.country ))</td>
                                        <td>(( address.phone ))</td>
                                        <td>(( address.mobilePhone ))</td>
                                        <td>(( address.dateAdd ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No addresses' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.orders" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Orders' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.orders.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Reference' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Payment' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Order state' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Total paid' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="order in customer.customerData.orders.data">
                                        <td><a :href="'{$orderLink|escape:'htmlall':'UTF-8'}'+'&id_order='+order.id_order+'&vieworder'" target="_blank"><b>(( order.reference ))</b></a></td>
                                        <td>(( order.payment ))</td>
                                        <td>(( order.state ))</td>
                                        <td>(( order.totalPaid ))</td>
                                        <td>(( order.date ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No orders' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.carts" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Carts' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.carts.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Id' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Total product(s)' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="cart in customer.customerData.carts.data">
                                        <td><a :href="'{$cartLink|escape:'htmlall':'UTF-8'}'+'&id_cart='+cart.id_cart+'&viewcart'" target="_blank"><b>#(( cart.cartId ))</b></a></td>
                                        <td>(( cart.totalProducts ))</td>
                                        <td>(( cart.creationDate ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No carts' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.messages" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Messages' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.messages.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='IP' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Message' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="message in customer.customerData.messages.data">
                                        <td>(( message.ipAddress ))</td>
                                        <td>(( message.message ))</td>
                                        <td>(( message.creationDate ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No messages' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.lastConnections" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Last connections' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.lastConnections.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Id' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Http referer' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Page viewed' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Time on the page' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='IP address' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="connection in customer.customerData.lastConnections.data">
                                        <td>(( connection.connectionId ))</td>
                                        <td>(( connection.httpReferer ))</td>
                                        <td>(( connection.pagesViewed ))</td>
                                        <td>(( connection.totalTime ))</td>
                                        <td>(( connection.ipAddress ))</td>
                                        <td>(( connection.date ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No connections' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.discounts" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Discounts' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.discounts.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Id' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Code' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Name' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Description' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="discount in customer.customerData.discounts.data">
                                        <td>(( discount.discountId ))</td>
                                        <td>(( discount.code ))</td>
                                        <td>(( discount.name ))</td>
                                        <td>(( discount.description ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No discount' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.lastSentEmails" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Last sent emails' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.lastSentEmails.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Creation date' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Language' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Subject' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Template' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="email in customer.customerData.lastSentEmails.data">
                                        <td>(( email.creationDate ))</td>
                                        <td>(( email.language ))</td>
                                        <td>(( email.subject ))</td>
                                        <td>(( email.template ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No emails' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.groups" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Groups' d='Modules.Psgdpr.PersonalDataManagement'} <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="customer.customerData.groups.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Id' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                        <th>{l s='Name' d='Modules.Psgdpr.PersonalDataManagement'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="group in customer.customerData.groups.data">
                                        <td>(( group.groupId ))</td>
                                        <td>(( group.name ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No groups' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>

                        <div v-if="customer.customerData.modules" v-for="module in customer.customerData.modules" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i>(( module.name )) <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="module.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th v-for="headerName in module.headers">(( headerName ))</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in module.data">
                                        <td v-for="value in row">(( value ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No data' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="(isEmail || isPhoneNumber) && found == false" id="phone-and-email" class="customerCard is-collapsed">
                <div v-if="isPhoneNumber" class="panel card-inner" @click="getCustomerInfos('phone', phraseSearched, 'phone-and-email')">
                    <div class="panel-heading">
                        <span>{l s='PHONE' d='Modules.Psgdpr.PersonalDataManagement'}</span>
                        <br>
                    </div>
                    <div class="panel-body" style="padding:23px;">
                        <span>(( phraseSearched ))</span>
                    </div>
                    <div class="panel-footer">
                        <button type="button" @click.stop="deleteCustomerData('phone', phraseSearched)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' d='Modules.Psgdpr.PersonalDataManagement'}</button>
                    </div>
                </div>

                <div v-if="isEmail" class="panel card-inner" @click="getCustomerInfos('email', phraseSearched, 'phone-and-email')">
                    <div class="panel-heading">
                        <span>{l s='EMAIL' d='Modules.Psgdpr.PersonalDataManagement'}</span>
                        <br>
                    </div>
                    <div class="panel-body" style="padding:23px;">
                        <span>(( phraseSearched ))</span>
                    </div>
                    <div class="panel-footer">
                        <button type="button" @click.stop="deleteCustomerData('email', phraseSearched)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' d='Modules.Psgdpr.PersonalDataManagement'}</button>
                    </div>
                </div>

                <div class="panel card-expander">
                    <div class="panel-body">
                        <div v-if="thirdPartyModuleData" v-for="module in thirdPartyModuleData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i>(( module.name )) <small>{l s='Personal data' d='Modules.Psgdpr.PersonalDataManagement'}</small>
                            </h3>
                            <table v-if="module.data.length > 0" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th v-for="headerName in module.headers">(( headerName ))</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="row in module.data">
                                        <td v-for="value in row">(( value ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No data' d='Modules.Psgdpr.PersonalDataManagement'}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
