<!--
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedMlibre
 */
-->
<div class="panel">
    <div class="panel-heading">
        {l s='Tax information' mod='cedshopee'}
    </div>
    <div class="panel-body">
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="product_listing_type">
                {l s='Invoice Option' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_tax_info[invoice_option]" id="product_listing_type">
                    <option value="">Select Option</option>
                    {if isset($invoice_types) && count($invoice_types) > 0}
                        {foreach $invoice_types as $type_key => $typesValue}
                            <option value="{$type_key|escape:'htmlall':'UTF-8'}"
                                    {if isset($tax)
                                    && isset($tax['invoice_option'])
                                    && $tax['invoice_option']|escape:'htmlall':'UTF-8' == {$type_key|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$typesValue|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="mlibre_item_condition">
                {l s='Vat option' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_tax_info[vat_option]" id="mlibre_item_condition">
                    <option value="">Select Option</option>
                    {if isset($vat_opt) && count($vat_opt) > 0}
                        {foreach $vat_opt as $optkey => $optval}
                            <option value="{$optkey|escape:'htmlall':'UTF-8'}"
                                    {if isset($tax)
                                    && isset($tax['vat_option'])
                                    && $tax['vat_option']|escape:'htmlall':'UTF-8' == {$optkey|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$optval|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="enable_local_pickup">
                {l s='Origin' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_tax_info[origin]" id="mlibre_item_condition">
                    <option value="">Select Option</option>
                    {if isset($origin) && count($origin) > 0}
                        {foreach $origin as $originkey => $originval}
                            <option value="{$originkey|escape:'htmlall':'UTF-8'}"
                                    {if isset($tax)
                                    && isset($tax['origin'])
                                    && $tax['origin']|escape:'htmlall':'UTF-8' == {$originkey|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$originval|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>

        <div class="form-group row" id="price_rule_value">
            <label class="control-label col-lg-3">Tax Code</label>
            <div class="col-lg-4">
                <input type="text" name="Profile_tax_info[tax_code]" id="price_rule_val"
                        {if isset($tax)
                        && isset($tax['tax_code'])}
                            value="{$tax['tax_code']|escape:'htmlall':'UTF-8'}" {else} value=""
                        {/if}
                >
                <p class="help-block">
                    Tax Code (Only for IN region)
                </p>
            </div>
        </div>
        <div class="form-group row" id="price_rule_value">
            <label class="control-label col-lg-3">HS Code</label>
            <div class="col-lg-4">
                <input type="text" name="Profile_tax_info[hs_code]" id="price_rule_val"
                        {if isset($tax)
                        && isset($tax['hs_code'])}
                            value="{$tax['hs_code']|escape:'htmlall':'UTF-8'}" {else} value=""
                        {/if}

                       "
                >
                <p class="help-block">
                    HS Code (Only for IN region)
                </p>
            </div>
        </div>
    </div>
    <div class="panel-heading">
        {l s='complaint Policy (Required Only for PL region)' mod='cedshopee'}
    </div>
    <div class="panel-body">
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="product_listing_type">
                {l s='Warranty Time ' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_complain_policy[warranty_time]" id="product_warranty_type">
                    <option value="">Select Option</option>
                    {if isset($warranty_time) && count($warranty_time) > 0}
                        {foreach $warranty_time as $time_key => $timeValue}
                            <option value="{$time_key|escape:'htmlall':'UTF-8'}"
                                    {if isset($complain)
                                    && isset($complain['warranty_time'])
                                    && $complain['warranty_time']|escape:'htmlall':'UTF-8' == {$time_key|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$timeValue|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="mlibre_item_condition">
                {l s='IS Entrepreneur Warranty' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_complain_policy[exclude_warranty]" id="mlibre_item_condition">
                    <option value="">Select Option</option>
                    {if isset($exclude_ent_warranty) && count($exclude_ent_warranty) > 0}
                        {foreach $exclude_ent_warranty as $exclude_ent_warrantykey => $exclude_ent_warrantyval}

                            <option value="{$exclude_ent_warrantykey|escape:'htmlall':'UTF-8'}"
                                    {if isset($complain)
                                    && isset($complain['exclude_warranty'])
                                    && $complain['exclude_warranty']|escape:'htmlall':'UTF-8' == {$exclude_ent_warrantykey|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$exclude_ent_warrantyval|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
                <p class="help-block">If True means,I exclude warranty complaints for entrepreneur</p>
            </div>
        </div>
        <div class="form-group row">
            <label class="control-label col-lg-3 text-right" for="enable_local_pickup">
                {l s='complaint address' mod='cedshopee'}
            </label>
            <div class="col-lg-4">
                <select name="Profile_complain_policy[address_id]" id="mlibre_item_condition">
                    <option value="">Select Option</option>
                    {if isset($address_list) && count($address_list) > 0}
                        {foreach $address_list as $address_listkey => $address_listval}
                            <option value="{$address_listkey|escape:'htmlall':'UTF-8'}"
                                    {if isset($complain)
                                    && isset($complain['address_id'])
                                    && $complain['address_id']|escape:'htmlall':'UTF-8' == {$address_listkey|escape:'htmlall':'UTF-8'}
                                    } selected="selected"
                                    {/if}
                            >{$address_listval|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
        </div>

{*        <div class="form-group row" id="price_rule_value">*}
{*            <label class="control-label col-lg-3">Tax Code</label>*}
{*            <div class="col-lg-4">*}
{*                <input type="text" name="Profile_complain_policy[extra_info]" id="price_rule_val"*}
{*                        {if isset($complain)*}
{*                        && isset($complain['extra_info'])}*}
{*                            value="{$complain['extra_info']|escape:'htmlall':'UTF-8'}" {else} value=""*}
{*                        {/if}*}
{*                >*}
{*                <p class="help-block">*}
{*                    Additional information for warranty claim. Should be less than 1000 characters.*}
{*                </p>*}
{*            </div>*}
{*        </div>*}
        <div class="form-group row" id="price_rule_value">
            <label class="control-label col-lg-3">Additional Information</label>
            <div class="col-lg-4">
                <input type="text" name="Profile_complain_policy[extra_info]" id="price_rule_val"
                    {if isset($complain)
                    && isset($complain['extra_info'])}
                       value="{$complain['extra_info']|escape:'htmlall':'UTF-8'}" {else} value=""
                {/if}

                "
                >
                <p class="help-block">
                    Additional information for warranty claim. Should be less than 1000 characters.
                </p>
            </div>
        </div>
    </div>
</div>

