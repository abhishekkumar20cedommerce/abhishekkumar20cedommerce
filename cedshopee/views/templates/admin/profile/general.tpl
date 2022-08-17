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
 * @package   Cedshopee
 */
-->
<div class="row">
    <div class="col-sm-8 col-sm-offset-1">
        <div class="form-wrapper">
            <div class="form-group row">
                <input type="hidden" name="profileId"
                        {if isset($profileId) && !empty($profileId)}
                            value="{$profileId|escape:'htmlall':'UTF-8'}"
                        {else}
                            value=""
                        {/if}
                >
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4 required">
                    {l s='Title' mod='cedshopee'}
                </label>
                <div class="col-lg-8">
                    <input type="text" name="title" class="" required="required"
                            {if isset($general) && isset($general['title']) && $general['title']}
                        value="{$general['title']|escape:'htmlall':'UTF-8'}" {else} value=""
                            {/if}>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Status' mod='cedshopee'}
                </label>
                <div class="col-lg-8">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="status" id="active_on" value="1" checked="checked">
                        <label for="active_on">{l s='Enable' mod='cedshopee'}</label>
        				<input type="radio" name="status" id="active_off" value="0"
                                {if isset($general) && isset($general['status']) && $general['status'] == '0'}
                            checked="checked" {/if}>
        				<label for="active_off">{l s='Disable' mod='cedshopee'}</label>
        				<a class="slide-button btn"></a>
        		    </span>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Manufacturer' mod='cedshopee'}
                </label>
                <div class="col-lg-8">
                    <select name="product_manufacturer[]" class="" multiple="multiple">
                    <option value="">--</option>
                    {foreach $productManufacturer as $manufacturer}
                    <option value="{$manufacturer['id_manufacturer']|escape:'htmlall':'UTF-8'}"
                    {if isset($general['product_manufacturer']) && count($general['product_manufacturer'])}
                      {foreach $general['product_manufacturer'] as $key => $manufact}
                        {if $manufacturer['id_manufacturer'] == $manufact}
                          selected="selected"
                        {/if}
                      {/foreach}
                    {/if}>
                    {$manufacturer['name']|escape:'htmlall':'UTF-8'}
                    </option>
                    {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Shop' mod='cedshopee'}
                </label>
                <div class="col-lg-8">
                    <select name="profile_store[]" class="" required="required" multiple="multiple">
                    <option value="">--</option>
                    {foreach $Shops as $shop}
                    <option value="{$shop['id_shop']|escape:'htmlall':'UTF-8'}"
                    {if isset($general['profile_store']) && count($general['profile_store'])} 
                     {foreach $general['profile_store'] as $key => $store}
                       {if $shop['id_shop'] == $store}
                         selected="selected"
                       {/if} 
                     {/foreach}
                    {/if} >
                    {$shop['name']|escape:'htmlall':'UTF-8'}
                    </option>
                    {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Language' mod='cedshopee'}
                </label>
                <div class="col-lg-8">
                    <select name="profile_language" class="" required="required">
                    <option value="">--</option>
                    {foreach $Languages as $language}
                    <option value="{$language['id_lang']|escape:'htmlall':'UTF-8'}" {if isset($general['profile_language']) && $general['profile_language'] == $language['id_lang']} selected="selected" {/if}>{$language['name']|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label class="control-label col-lg-4">
                    {l s='Profile Categories' mod='cedshopee'}
                </label>
                <div class="col-lg-8" id="cedshopee_cat_tree">
                {$storeCategories}   {* HTML, cannot escape*}
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-1"></div>
</div>
<script>
    $(document).ready(function () {
        var checked = [];
        $('#categories-treeview li input[type=checkbox]').each(function (d) {
          if (this.checked == true) {
              checked.push(d);
          }
        });
        $('#categories-treeview li input[type=checkbox]').on('change', function () {
            if (this.checked == false) {
                var res = confirm("If you remove category from profile all the products of this category will be removed from this profile " +
                    "also the profile data will be removed from product like shipping data and attribute mapping." +
                    " Do you really want to remove category from this profile ?");
                if (res == false) {
                    this.checked = true;
                    $(this).parent('span').addClass("tree-selected");

                }
            }
        });
        $("#uncheck-all-categories-treeview").on('click', function () {
            var res = confirm("If you remove category from profile all the products of this category will be removed from this profile " +
                "also the profile data will be removed from product like shipping data and attribute mapping." +
                " Do you really want to remove category from this profile ?");
            if (res == false) {
                $('#categories-treeview li input[type=checkbox]').each(function (d) {
                   if (checked.indexOf(d) > -1) {
                    this.checked = true;
                    $(this).parent('span').addClass("tree-selected");
                   }
                })
            }
        })
    })
</script>