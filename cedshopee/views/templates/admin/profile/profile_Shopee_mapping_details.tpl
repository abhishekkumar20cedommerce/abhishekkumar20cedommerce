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
 * @package   cedzalando
 */
-->

<tbody>
{if !isset($model['error'])}
{foreach $model as $attribute_key => $attribute}
        <tr>
            <td>
                {if $attribute['is_mandatory'] == true}
                    <span style="color: red">*</span>
                {/if}
                {$attribute['display_attribute_name']|escape:'htmlall':'UTF-8'}

            </td>
            <td>
                <input type="text" class="form-control" name="profile_attribute_mapping[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}][default_values]"
                        {if isset($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['default_values']) && $profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['default_values']}
                            value="{$profileTechnicalDetails[{$attribute['attribute_id']}]['default_values']}"
                        {else}
                            value=""
                        {/if}
                >
            </td>
            <td>
                <select name="profile_attribute_mapping[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}][store_attribute]" id="{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}">
                    <option value=""></option>
                    <optgroup value="0" label="Features">
                        {if isset($storeFeatures)}
                            {foreach $storeFeatures as $feature}
                                {if isset($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['store_attribute']) && ($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['store_attribute']=="attribute-{$feature['id_feature']}") }
                                    <option selected="selected"
                                            value="attribute-{$feature['id_feature']|escape:'htmlall':'UTF-8'}">{$feature['name']|escape:'htmlall':'UTF-8'}</option>
                                {else}
                                    <option value="attribute-{$feature['id_feature']|escape:'htmlall':'UTF-8'}">{$feature['name']|escape:'htmlall':'UTF-8'}</option>
                                {/if}
                            {/foreach}
                        {/if}
                    </optgroup>
                        <optgroup value="0" label="Product fields">

                            {foreach $product_field as $key => $system_attribute}

                                {if isset($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['store_attribute']) && ($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['store_attribute']=="product-{$system_attribute['Field']}") }
                                    <option selected="selected"
                                            value="product-{$system_attribute['Field']|escape:'htmlall':'UTF-8'}">{str_replace('_', ' ',$system_attribute['Field']|escape:'htmlall':'UTF-8')}</option>
                                {else}
                                    <option value="product-{$system_attribute['Field']|escape:'htmlall':'UTF-8'}">{str_replace('_', ' ',$system_attribute['Field']|escape:'htmlall':'UTF-8')}</option>
                                {/if}
                            {/foreach}
                        </optgroup>
                </select>
            </td>

            <td>
                {if isset($attribute['attribute_value_list']) && !empty($attribute['attribute_value_list'])}
                    <select name="profile_attribute_mapping[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}][shopee_option]" id="{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}">
                        <option value="">Select Option</option>
                        {foreach $attribute['attribute_value_list'] as $val}
                            {if isset($profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['shopee_option']) && $profileTechnicalDetails[{$attribute['attribute_id']|escape:'htmlall':'UTF-8'}]['shopee_option'] ==  $val['value_id']}
                                <option selected="selected"
                                        value="{$val['value_id']|escape:'htmlall':'UTF-8'}">{$val['display_value_name']|escape:'htmlall':'UTF-8'}</option>
                            {else}
                                <option value="{$val['value_id']|escape:'htmlall':'UTF-8'}">{$val['display_value_name']|escape:'htmlall':'UTF-8'}</option>
                            {/if}
                        {/foreach}
                    </select>
                {/if}
            </td>
        </tr>
{/foreach}
{else}
    <span style="color: red; font-size: 20px">{$model['message']}</span>
{/if}
</tbody>
