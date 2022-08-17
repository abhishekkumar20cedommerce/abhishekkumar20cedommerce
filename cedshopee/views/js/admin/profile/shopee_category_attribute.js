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
 * @package   CedShopee
 */

$('input[name=\'shopee_category\']').autocomplete({
    delay: 500,
    source: function (request, response) {
        $.ajax({
            url: 'index.php?controller=AdminCedShopeeProfile&method=ajaxProcessAutocomplete&ajax=1&action=autocomplete&token={$token}&filter_name=' +  encodeURIComponent(request.term),
            dataType: 'json',
            success: function (json) {
                response($.map(json, function (item) {
                    return {
                        label: item.name,
                        value: item.category_id
                    }
                }));
            }
            });
    },
    select: function (event, ui) {
        $('input[name=\'shopee_category\']').attr('value', ui.item.label);
        $('input[name=\'shopee_category_id\']').attr('value', ui.item.value);
        fetchShopeeAttributes(ui.item.value);
        return false;
    },
    focus: function (event, ui) {
        return false;
    }
    });

function fetchShopeeAttributes(category_id)
{
    $.ajax({
        url: 'index.php?controller=AdminCedShopeeProfile&method=ajaxProcessAttributesByCategory&ajax=1&action=attributesByCategory&token={$token}&category_id='+category_id+'&profile_id='+profile_id,
        cache: false,
        beforeSend: function () {
            document.getElementById('overlay').style.display = 'block';
            $('#cancel-plan, #revise-plan').attr('disabled', true);
            $('#cancel-plan').after('<span class="laybuy-loading fa fa-spinner" style="margin-left:2px"></span>');
        },
        complete: function () {
            document.getElementById('overlay').style.display = 'none';
            $('#cancel-plan, #revise-plan').attr('disabled', false);
            $('.laybuy-loading').remove();
        },
        success: function (json) {
            console.log(json);
            document.getElementById('mapping_values').innerHTML=json;
        },
        error: function (xhr, ajaxOptions, thrownError) {
            alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
        }
        });
}
function toggleOptions(panel_id)
{
    var panel = document.getElementById('panel'+panel_id);
    var store_attribute_id = $('select[name="profile_attribute_mapping['+panel_id+'][store_attribute]"]').val();
    var shopee_attribute_id = $('select[name="profile_attribute_mapping['+panel_id+'][shopee_attribute]"]').val();

    var show_option_mapping = $('select[name="profile_attribute_mapping['+panel_id+'][store_attribute]"]').find('option:selected');
    if (show_option_mapping) {
        show_option_mapping = show_option_mapping.attr("show_option_mapping");
    }
       
    if (store_attribute_id && shopee_attribute_id && parseInt(show_option_mapping)) {
        if ($(panel).css('display') == 'none') {
            $(panel).show();
        } else {
            $(panel).hide();
        }
    } else {
        if (!store_attribute_id) {
            alert("Please Select Attribute First");
        }
        if (!shopee_attribute_id) {
            alert("Please Select Shopee Attribute First");
        }
    }
}
function getStoreOptions(data)
{
    if (data.value.length > 0) {
        var id = data.getAttribute('data-id');
        
        $('input[name="profile_attribute_mapping['+id+'][option][store_attribute]"]').autocomplete({
            delay: 500,
            source: function (request, response) {
                var name = encodeURIComponent(request.term);
                var manufacturer_id = $('select[name="profile_attribute_mapping['+id+'][store_attribute]"]').val();
                $.ajax({
                    url: 'index.php?controller=AdminCedShopeeProfile&method=ajaxProcessGetStoreOptions&ajax=1&action=getStoreOptions&token={$token}&filter_name=' + name + '&catId='+$('[name="shopee_category_id"]').val()+'&attribute_id='+manufacturer_id,
                    dataType: 'json',
                    success: function (json) {
                        response($.map(json, function (item) {
                            if (manufacturer_id=='product-manufacturer_id') {
                                return {
                                    label: item.name,
                                    value: item.manufacturer_id
                                }
                            } else {
                                return {
                                    label: item.name,
                                    value: item.option_value_id
                                }
                            }
                                
                        }));
                    }
                    });
            },
            select: function (event, ui) {
                $('input[name="profile_attribute_mapping['+id+'][option][store_attribute]"]').attr('value',ui.item.label);
                $('input[name="profile_attribute_mapping['+id+'][option][store_attribute_id]"]').attr('value',ui.item.value);
                return false;
            },
            focus: function (event, ui) {
                return false;
            }
            });
    }
}
    window.onload = function () {
        if ($('[name="shopee_category_id"]') && $('[name="shopee_category_id"]').val()) {
            var category_id = $('[name="shopee_category_id"]').val();
            fetchShopeeAttributes(category_id);
        }
    }
    function addAttribute(c_object,attribute_id)
    {
        var attribute_row = $("#option_mapping"+attribute_id+" tr").length-1;
        var store_attribute = $('input[name="profile_attribute_mapping['+attribute_id+'][option][store_attribute]"]').val();
        var shopee_attribute = $('input[name="profile_attribute_mapping['+attribute_id+'][option][shopee_attribute]"]').val();
        var store_attribute_id = $('input[name="profile_attribute_mapping['+attribute_id+'][option][store_attribute_id]"]').val();
        html = '<tr id="attribute-row' + attribute_row +'">';
        html += '<td class="left"><input type="text" name="profile_attribute_mapping['+attribute_id+'][option]['+attribute_row+'][store_attribute]" value="'+store_attribute+'" /><input type="hidden" name="profile_attribute_mapping['+attribute_id+'][option]['+attribute_row+'][store_attribute_id]" value="'+store_attribute_id+'" /></td>';
        html += '<td class="left"><input type="text" name="profile_attribute_mapping['+attribute_id+'][option]['+attribute_row+'][shopee_attribute]" value="'+shopee_attribute+'" /></td>';
        html += '<td class="left"><a onclick="$(\'#attribute-row' + attribute_row + '\').remove();" class="button"><?php echo "Remove"; ?></a></td>';
        html += '</tr>';
        $(c_object).parent().parent().parent().parent().children('thead').after(html)
        attribute_row++;
        $('input[name="profile_attribute_mapping['+attribute_id+'][option][store_attribute]"]').val("");
        $('input[name="profile_attribute_mapping['+attribute_id+'][option][shopee_attribute]"]').val("");
        $('input[name="profile_attribute_mapping['+attribute_id+'][option][store_attribute_id]"]').val("");
    }