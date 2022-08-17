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
 * @package   Cedthemarket
 */
 -->
<form method="post">
    <div class="panel">
        <div class="panel-heading">
            Shopee Brand Mapping
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <div id="content table-responsive-row clearfix">

                    <div>
                        <div> <H3 class="text-center" > Select Profile </H3>
                        <select class="form-control store-brand-change" id="store_brand_id" onchange="addBrandOption(this.value)" name="store_brand_id">
                            <option value=""> -- Please Select profile -- </option>
                            {foreach $profiles as $profile}
                                {if isset($profile) && isset($already_mapped_brands['profile_id']) && $profile['id'] == $already_mapped_brands['profile_id']}
                                    <option selected="selected" value="{$profile['id']}">{$profile['title']|escape:'htmlall':'UTF-8'}</option>
                                {else}
                                    <option value="{$profile['id']|escape:'htmlall':'UTF-8'}">{$profile['title']|escape:'htmlall':'UTF-8'}</option>
                                {/if}
                            {/foreach}

                        </select>
                            {foreach $profiles as $profile}
                                {if isset($profile) && isset($already_mapped_brands['profile_id']) && $profile['id'] == $already_mapped_brands['profile_id']}
                                    <input hidden name ='profileid' value="{$profile['id']}">
                                {/if}
                            {/foreach}
                        </div>
                    </div>
                    <table id="mapping-values" class="table list">
                        <thead>
                        <tr>
                            <td class="text-center" > Prestashop Brand </td>
                            <td class="text-center" > Shopee Brand </td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr id="brand-value-row">
                            <td class="text-center" >
                                <select class="form-control store-brand-change" id="store_brand_id" name="store_brand_id">
                                    <option value=""> -- Please Select Store Brand -- </option>
                                    {foreach $store_brands as $store_brand}
                                        {if isset($already_mapped_brands['store_brand_id']) &&
                                        $store_brand['id'] == $already_mapped_brands['store_brand_id']}
                                            <option selected="selected" value="{$store_brand['id']}">{$store_brand['name']|escape:'htmlall':'UTF-8'}</option>
                                        {else}
                                            <option value="{$store_brand['id']|escape:'htmlall':'UTF-8'}">{$store_brand['name']|escape:'htmlall':'UTF-8'}</option>
                                        {/if}
                                    {/foreach}

                                </select>
                            </td>
                            <td class="text-right" id="brand-option">
                                <select  id="cedshopee_brand_id" name="cedshopee_brand_id">
                                    <option value="0"> -- Please Select Shopee Brand -- </option>
                                    {foreach $brand_row as $brand}
                                        {if isset($already_mapped_brands['shopee_brand_id']) &&
                                        $brand['brand_id'] == $already_mapped_brands['shopee_brand_id']}
                                            <option selected="selected" value="{$brand['brand_id']}">{$brand['original_brand_name']|escape:'htmlall':'UTF-8'}</option>
                                        {else}
                                            <option value="{$brand['brand_id']|escape:'htmlall':'UTF-8'}">{$brand['original_brand_name']|escape:'htmlall':'UTF-8'}</option>
                                        {/if}
                                    {/foreach}
                                </select>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="test_form_submit_btn" name="savebrandmapping"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='cedshopee'}
            </button>
            <a class="btn btn-default" id="back-option-controller" data-token="{$token|escape:'htmlall':'UTF-8'}" href="{$controllerUrl|escape:'htmlall':'UTF-8'}">
                <i class="process-icon-cancel"></i> {l s='Cancel' mod='cedshopee'}
            </a>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(document).ready(function () {
        $(".livesearch").chosen({
            'width' : "100%"
        });

        var url = '{$controllerUrl}';
    });

    function addBrandOption(profileId)
    {
        console.log(profileId);
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',

            data: {
                ajax: true,
                controller: 'AdminCedShopeeBrand',
                // configure: 'cedhtgdropship',
                method: 'ajaxProcessGetBrandOption',
                action: 'getBrandOption',
                token: '{$token|escape:'htmlall':'UTF-8'}',
                profile_id : profileId,
            },

            success: function(json) {
                console.log(json)
                var result = $.parseJSON(json);
                $.each(result, function (i, item) {
                    $('#cedshopee_brand_id').append($('<option>', {
                        // console.log(item);
                        value: item.brand_id,
                        text : item.original_brand_name
                    }));
                });
                html = "<input hidden name ='profileid' value ='"+profileId+"'}>";
                $('#mapping-values' + ' tbody').append(html);
            },
        });
    }
</script>