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

<div class="panel" id="wrapper_element">
    <div class="panel-body">
        <div class="table-responsive">
            <div id="content table-responsive-row clearfix">
                <table id="carrier_mapping_container" class="table list">
                    <thead>
                    <tr>
                        <td class="text-center" >{l s='PS Status' mod='cedshopee'}  </td>
                        <td class="text-center" >{l s='Shopee Status' mod='cedshopee'}  </td>
                    </tr>
                    </thead>
                    <tbody>
                    {if $carrier_mappings}
                        {foreach $carrier_mappings as $index => $carrier_mapping}
                            <tr id="option-value-row{$index|escape:'htmlall':'UTF-8'}">
                                <td class="text-left">
                                    <select
                                            name="CEDSHOPEE_STATUS_MAPPING[{$index|escape:'htmlall':'UTF-8'}][id_order_state]"
                                            class="form-control"
                                    >
                                        <option value=""></option>
                                        {foreach $order_carriers as $order_carrier}
                                            <option
                                                    {if $carrier_mapping['id_order_state']==$order_carrier['id_order_state']}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$order_carrier['id_order_state']|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$order_carrier['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                
                                <td class="text-left">
                                    <select
                                            name="CEDSHOPEE_STATUS_MAPPING[{$index|escape:'htmlall':'UTF-8'}][id_marketplace_carrier]"
                                            class="form-control"
                                    >
                                        <option value=""></option>
                                        {foreach $marketplace_carriers as $marketplace_carrier}
                                            <option
                                                    {if $carrier_mapping['id_marketplace_carrier']==$marketplace_carrier['id_marketplace_carrier']}
                                                        selected="selected"
                                                    {/if}
                                                    value="{$marketplace_carrier['id_marketplace_carrier']|escape:'htmlall':'UTF-8'}"
                                            >
                                                {$marketplace_carrier['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    </select>
                                </td>
                                <td class="text-left">
                                    <button
                                            type="button"
                                            onclick="$('#option-value-row{$index|escape:'htmlall':'UTF-8'}').remove();"
                                            data-toggle="tooltip"
                                            rel="tooltip"
                                            class="btn btn-outline-primary"
                                            title="Remove"
                                    >
                                        <i class="material-icons">delete</i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {/if}
                    </tbody>
                    <tfoot>
                    <tr>
                        <td colspan="4">
                            <button
                                    type="button"
                                    onclick="addCarrierMapping();"
                                    class="btn btn-outline-primary add"
                            >
                                {l s='Add Order Status Mapping' mod='cedshopee'}
                            </button>
                        </td>
                    </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    {if isset($index)}
        var option_value_row = {$index+1|escape:'htmlall':'UTF-8'};
    {else}
        var option_value_row = 0;
    {/if}
    function addCarrierMapping() {
        html  = '<tr id="option-value-row'+option_value_row+'">';


        html += '<td class="text-left">';
        html += '<select name="CEDSHOPEE_STATUS_MAPPING[' + option_value_row + '][id_order_state]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $order_carriers as $order_carrier}
        html += '<option value="{$order_carrier["id_order_state"]|escape:'htmlall':'UTF-8'}">';
        html +='{$order_carrier["name"]|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';

        html += '<td class="text-left">';
        html += '<select name="CEDSHOPEE_STATUS_MAPPING[' + option_value_row + '][id_marketplace_carrier]" class="form-control" >';
        html += '<option value=""></option>';
        {foreach $marketplace_carriers as $marketplace_carrier}
        html += '<option value="{$marketplace_carrier["id_marketplace_carrier"]|escape:'htmlall':'UTF-8'}">';
        html +='{$marketplace_carrier["name"]|escape:'htmlall':'UTF-8'}';
        html +='</option>';
        {/foreach}
        html += '  </select></td>';
        html += '<td class="text-left"><button type="button" onclick="$(\'#option-value-row' + option_value_row + '\').remove();" data-toggle="tooltip" rel="tooltip" class="btn btn-outline-primary" title="Remove">' ;
        html += '<i class="material-icons">delete</i>';
        html += '</button>';
        html += '</td>';
        html += '</tr>';
        $('#carrier_mapping_container' + ' tbody').append(html);
        option_value_row++;
    }
</script>
