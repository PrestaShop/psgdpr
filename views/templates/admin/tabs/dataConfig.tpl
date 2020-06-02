{**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-cogs"></i> {l s='Data visualization and automatic actions' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>
    <form method="post" action="{$moduleAdminLink|escape:'htmlall':'UTF-8'}&page=account" class="form-horizontal">
        <div>
            <p>{l s='Find here listed all personal data collected by PrestaShop and your installed modules.' mod='psgdpr'}</p>
            <p>{l s='These data will be used at 2 different levels :' mod='psgdpr'}</p>
            <ul>
                <li>{l s='When a customer requests access to his data: he gets a copy of his personal data collected on your store.' mod='psgdpr'}</li>
                <li>{l s='When a customer requests data erasure: if you accept his request, his data will be removed permanently.' mod='psgdpr'}</li>
            </ul>
            <br>

            <div class="panel panel-box col-lg-12">
                <h3>
                    <i class="fa fa-list"></i> {l s='Compliant module list' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
                </h3>
                <p>{l s='Find here listed all the elements that are GDPR compliant.' mod='psgdpr'}</p>

                <div class="registered-modules">
                    <div class="module-card">
                        <div class="module-card-content">
                            <div class="module-card-img">
                                <img src="{$img_path|escape:'htmlall':'UTF-8'}PrestaShop_logo_puffin.png" width="45" heigh="45">
                            </div>
                            <div class="module-card-title">
                                <span>{l s='PrestaShop data' mod='psgdpr'}</span>
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
                        <li>{l s='Please make sure that you have access to the latest version of these modules to fully benefit the GDPR update.' mod='psgdpr'}</li>
                        <li>{l s='If they are still not displayed in the block above, we invite you to contact their respective developers to have more information about these modules. ' mod='psgdpr'}</li>
                    </ul>
                </article>
            </div>

        </div>
    </form>
</div>

<div class="panel col-lg-10 right-panel">
    <h3>
        <i class="fa fa-database"></i> {l s='Manage customer\'s personal data' mod='psgdpr'} <small>{$module_display|escape:'htmlall':'UTF-8'}</small>
    </h3>

    <div id="customerSearchBlock">
        {*<br>*}
        <form id="search" class="form-horizontal" action="" v-on:submit.prevent="onSubmit">
            {* SEARCH CUSTOMER BLOCK *}
            <div class="form-group" style="margin-bottom: 0px !important">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-4">
                    <div class="text-right">
                        <label class="control-label">
                            <span class="label-tooltip" data-original-title="{l s='Search for an existing customer by typing the first letters of his/her name or email.' mod='psgdpr'}">
                                {l s='Search for a customer name OR email' mod='psgdpr'}
                            </span>
                        </label>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-7 col-lg-3">
                    <div class="input-group"> <span class="input-group-addon"><i class="fa fa-search"></i></span> <input v-on:keyup="searchCustomers()" v-model="customer_search" class="form-control"> </div>
                    <div class="help-block">
                        <p>{l s='Eg: john doe ...' mod='psgdpr'}</p>
                    </div>
                </div>
                {*<div>*}
                    {*<a id="search-tag" v-on:click="searchCustomers()" type="button" class="btn btn-primary"> {l s='Search' mod='psgdpr'}</a>*}
                {*</div>*}
            </div>
            {* SEARCH CUSTOMER BLOCK *}
        </form>


        <article v-if="typeof customers != 'undefined' && customers.length >= 1" class="alert alert-info" role="alert" data-alert="info" style="margin-bottom: 0px !important">
            {l s='To visualize all the data that your store has collected from a specific customer, please click on the corresponding customer block' mod='psgdpr'}
        </article>
        <div class="customerCards">
            <div v-for="(customer, index) in customers" :id="'customer_'+customer.id_customer" class="customerCard is-collapsed">
                <div class="panel card-inner" v-on:click="toggleMore('customer', customer.id_customer, 'customer_'+customer.id_customer, index)">
                    <div class="panel-heading">
                        <span>(( customer.firstname ))</span> (( customer.lastname ))<span class="pull-right">#(( customer.id_customer ))</span>
                    </div>
                    <div class="panel-body">
                        <span>(( customer.email ))</span>
                        <br>
                        <span class="text-muted">{l s='Orders number' mod='psgdpr'}: (( customer.nb_orders ))</span>
                    </div>
                    <div class="panel-footer">
                        <a v-on:click.stop :href="customer_link+customer.id_customer" target="_blank" class="btn btn-default fancybox"><i class="icon-search"></i> {l s='Details' mod='psgdpr'}</a>
                        <button type="button" v-on:click.stop="deleteCustomer('customer', customer.id_customer, index)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' mod='psgdpr'}</button>
                        <a v-on:click.stop="downloadInvoices(customer.id_customer, index)" class="btn btn-primary pull-right"><i class="icon-download"></i> {l s='Download invoices' mod='psgdpr'}</a>
                    </div>
                </div>
                <div class="panel card-expander">
                    {* <div class="panel-heading">
                        <span>{l s='Customer data' mod='psgdpr'}</span><span class="pull-right">#(( customer.id_customer ))</span>
                    </div> *}
                    <div class="panel-body">

                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='General information' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <div class="col-lg-12">
                                <div class="col-lg-6">
                                    <div class="form-horizontal">
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Gender' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.gender ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Name' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.firstname )) (( customer.customerData.prestashopData.customerInfo.lastname ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Birth date' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.birthday ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Age' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.age ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Email' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.email ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Language' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.language ))</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="form-horizontal">
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Creation date' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.date_add ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Last visit' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.last_visit ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Siret' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.siret ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Ape' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.ape ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Company' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.company ))</p>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <label class="control-label col-lg-3"><b>{l s='Website' mod='psgdpr'}</b></label>
                                            <div class="col-lg-9">
                                                <p class="form-control-static">(( customer.customerData.prestashopData.customerInfo.website ))</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Addresses' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <table v-if="customer.customerData.prestashopData.addresses.length >= 1" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Alias' mod='psgdpr'}</th>
                                        <th>{l s='Company' mod='psgdpr'}</th>
                                        <th>{l s='Name' mod='psgdpr'}</th>
                                        <th>{l s='Address' mod='psgdpr'}</th>
                                        <th>{l s='Phone(s)' mod='psgdpr'}</th>
                                        <th>{l s='Country' mod='psgdpr'}</th>
                                        <th>{l s='Date' mod='psgdpr'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(address, index) in customer.customerData.prestashopData.addresses">
                                        <td>(( address.alias ))</td>
                                        <td>(( address.company ))</td>
                                        <td>(( address.firstname )) (( address.lastname ))</td>
                                        <td>(( address.address1 )), (( address.address2)), (( address.postcode )) (( address.city ))</td>
                                        <td>(( address.phone )) (( address.phone_mobile ))</td>
                                        <td>(( address.country ))</td>
                                        <td>(( address.date_add ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No addresses' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Orders' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <table v-if="customer.customerData.prestashopData.orders.length >= 1" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Reference' mod='psgdpr'}</th>
                                        <th>{l s='Payment' mod='psgdpr'}</th>
                                        <th>{l s='Order state' mod='psgdpr'}</th>
                                        <th>{l s='Total paid' mod='psgdpr'}</th>
                                        <th>{l s='Date' mod='psgdpr'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(order, index) in customer.customerData.prestashopData.orders">
                                        <td><a :href="'{$orderLink|escape:'htmlall':'UTF-8'}'+'&id_order='+order.id_order+'&vieworder'" target="_blank"><b>(( order.reference ))</b></a></td>
                                        <td>(( order.payment ))</td>
                                        <td>(( order.order_state ))</td>
                                        <td>(( order.total_paid_tax_incl ))</td>
                                        <td>(( order.date_add ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No orders' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Carts' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <table v-if="customer.customerData.prestashopData.carts.length >= 1" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Id' mod='psgdpr'}</th>
                                        <th>{l s='Total product(s)' mod='psgdpr'}</th>
                                        <th>{l s='Date' mod='psgdpr'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(cart, index) in customer.customerData.prestashopData.carts">
                                        <td><a :href="'{$cartLink|escape:'htmlall':'UTF-8'}'+'&id_cart='+cart.id_cart+'&viewcart'" target="_blank"><b>#(( cart.id_cart ))</b></a></td>
                                        <td>(( cart.nb_products ))</td>
                                        <td>(( cart.date_add ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No carts' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Messages' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <table v-if="customer.customerData.prestashopData.messages.length >= 1" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='IP' mod='psgdpr'}</th>
                                        <th>{l s='Message' mod='psgdpr'}</th>
                                        <th>{l s='Date' mod='psgdpr'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(message, index) in customer.customerData.prestashopData.messages">
                                        <td>(( message.ip ))</td>
                                        <td>(( message.message ))</td>
                                        <td>(( message.date_add ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No messages' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.prestashopData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Last connections' mod='psgdpr'} <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <table v-if="customer.customerData.prestashopData.connections.length >= 1" class="table table-bordered table-hover addresses-table">
                                <thead>
                                    <tr>
                                        <th>{l s='Origin request' mod='psgdpr'}</th>
                                        <th>{l s='Page viewed' mod='psgdpr'}</th>
                                        <th>{l s='Time on the page' mod='psgdpr'}</th>
                                        <th>{l s='IP address' mod='psgdpr'}</th>
                                        <th>{l s='Date' mod='psgdpr'}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(connection, index) in customer.customerData.prestashopData.connections">
                                        <td>(( connection.http_referer ))</td>
                                        <td>(( connection.pages ))</td>
                                        <td>(( connection.time ))</td>
                                        <td>(( connection.ipaddress ))</td>
                                        <td>(( connection.date_add ))</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No connections' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                        <div v-if="customer.customerData.modulesData" v-for="(module, index) in customer.customerData.modulesData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Module' mod='psgdpr'}: (( index )) <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <div v-if="module instanceof Array">
                                <table v-if="module.length >= 1" v-for="table in module" class="table table-bordered table-hover addresses-table">
                                    <thead>
                                        <tr>
                                            <th v-for="(val, key, i) in table">(( key ))</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td v-for="(val, key, i) in table">(( val ))</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else>
                                    <article class="alert alert-warning" role="alert" data-alert="warning">
                                        {l s='No data' mod='psgdpr'}
                                    </article>
                                </div>
                            </div>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No data' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="isEmail() && found == false" id="email" class="customerCard is-collapsed">
                <div class="panel card-inner" v-on:click="toggleMore('email', customer_search, 'email')">
                    <div class="panel-heading">
                        <span>{l s='EMAIL' mod='psgdpr'}</span>
                        <br>
                    </div>
                    <div class="panel-body" style="padding:23px;">
                        <span>(( customer_search ))</span>
                    </div>
                    <div class="panel-footer">
                        <button type="button" v-on:click.stop="deleteCustomer('email', customer_search)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' mod='psgdpr'}</button>
                    </div>
                </div>
                <div class="panel card-expander">
                    <div class="panel-body">
                        <div v-if="dataMail" v-for="(module, index) in dataMail.modulesData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Module' mod='psgdpr'}: (( index )) <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <div v-if="module instanceof Array">
                                <table v-if="module.length >= 1" v-for="table in module" class="table table-bordered table-hover addresses-table">
                                    <thead>
                                        <tr>
                                            <th v-for="(val, key, i) in table">(( key ))</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td v-for="(val, key, i) in table">(( val ))</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else>
                                    <article class="alert alert-warning" role="alert" data-alert="warning">
                                        {l s='No data' mod='psgdpr'}
                                    </article>
                                </div>
                            </div>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No data' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div v-if="isPhoneNumber() && found == false" id="phone" class="customerCard is-collapsed">
                <div class="panel card-inner" v-on:click="toggleMore('phone', customer_search, 'phone')">
                    <div class="panel-heading">
                        <span>{l s='PHONE' mod='psgdpr'}</span>
                        <br>
                    </div>
                    <div class="panel-body" style="padding:23px;">
                        <span>(( customer_search ))</span>
                    </div>
                    <div class="panel-footer">
                        <button type="button" v-on:click.stop="deleteCustomer('phone', customer_search)" class="btn btn-danger pull-right"><i class="icon-trash"></i> {l s='Remove data' mod='psgdpr'}</button>
                    </div>
                </div>
                <div class="panel card-expander">
                    <div class="panel-body">
                        <div v-if="dataPhone" v-for="(module, index) in dataPhone.modulesData" class="panel panel-box col-lg-12">
                            <h3>
                                <i class="fa fa-account"></i> {l s='Module' mod='psgdpr'}: (( index )) <small>{l s='Personal data' mod='psgdpr'}</small>
                            </h3>
                            <div v-if="module instanceof Array">
                                <table v-if="module.length >= 1" v-for="table in module" class="table table-bordered table-hover addresses-table">
                                    <thead>
                                        <tr>
                                            <th v-for="(val, key, i) in table">(( key ))</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td v-for="(val, key, i) in table">(( val ))</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div v-else>
                                    <article class="alert alert-warning" role="alert" data-alert="warning">
                                        {l s='No data' mod='psgdpr'}
                                    </article>
                                </div>
                            </div>
                            <div v-else>
                                <article class="alert alert-warning" role="alert" data-alert="warning">
                                    {l s='No data' mod='psgdpr'}
                                </article>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <article v-if="found == false && customer_search.length > 0" class="alert alert-warning" role="alert" data-alert="warning">
            <p>{l s='There is no result in the customer data base for' mod='psgdpr'} : (( customer_search ))</p>
            <p v-if="!isEmail() && !isPhoneNumber()">{l s='If you are looking for someone without a customer account, please search for the complete email address or phone number he left.' mod='psgdpr'}</p>
            <p v-if="isEmail() || isPhoneNumber()">{l s='However you can continue the erasure process for this address (only for modules that have done the GDPR update).' mod='psgdpr'}</p>
        </article>
    </div>
</div>
