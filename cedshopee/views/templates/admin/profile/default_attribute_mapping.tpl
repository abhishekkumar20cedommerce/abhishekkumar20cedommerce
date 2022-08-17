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
 * @package   cedshopee
 */
-->
<div class="panel">
    <div class="panel-heading">
        <div class="row">
            <div class="col-sm-6 col-lg-6 col-lg-6">
                {l s='Shopee Attribute' mod='cedshopee'}
            </div>
            <div class="col-sm-6 col-lg-6 col-lg-6">
                {l s='Store Attributes' mod='cedshopee'}
            </div>
        </div>
    </div>
    <div class="panel-body">
        <table class="table table-bordered">
            <tbody>
            {if isset($ShopeeDefaultValues) && $ShopeeDefaultValues}
                {foreach $ShopeeDefaultValues as $attr}
                    <tr>
                        <td>
                            {if $attr['required'] == true}
                                <span style="color: red">*</span>
                            {/if}
                            {$attr['title']|escape:'htmlall':'UTF-8'}
                        </td>
                        <td >
                        {if $attr['code'] != 'days_to_ship'}
                            <select name="defaultMapping[{$attr['code']|escape:'htmlall':'UTF-8'}]" id="{$attr['code']|escape:'htmlall':'UTF-8'}">
                                <option value=""></option>
                                    {foreach $storeSystemAttributes as $key => $system_attribute}
                                        {if isset($defaultMapping[{$attr['code']}]) && ($defaultMapping[{$attr['code']}]=="{$key}") }
                                            <option selected="selected"
                                                    value="{$key|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                                        {else}
                                            <option value="{$key|escape:'htmlall':'UTF-8'}">{$system_attribute|escape:'htmlall':'UTF-8'}</option>
                                        {/if}
                                    {/foreach}
                            </select>
                        {else}
                        <input type="text" name="defaultMapping[{$attr['code']|escape:'htmlall':'UTF-8'}]" id="{$attr['code']|escape:'htmlall':'UTF-8'}" class="" {if isset($defaultMapping[{$attr['code']|escape:'htmlall':'UTF-8'}]) && ({$attr['code']|escape:'htmlall':'UTF-8'} == 'days_to_ship') } value="{$defaultMapping[{$attr['code']|escape:'htmlall':'UTF-8'}]|escape:'htmlall':'UTF-8'}" {else} value="" {/if}>
                        {/if}
                        </td>
                    </tr>
                {/foreach}
            {/if}
            </tbody>
        </table>
    </div>
</div>
<script>
    function colorMapping(val) {
        console.log(val);
    }
</script>