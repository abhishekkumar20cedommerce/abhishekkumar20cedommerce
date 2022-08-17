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

<div class="col-lg-5">

    <select class="" name="CEDSHOPEE_PRICE_VARIANT_TYPE" id="price_variation_type" onchange="priceVariation(this)">
        <option value="default"> {l s='Default Prestashop Price' mod='cedshopee'}</option>
        <option
                {if (isset($CEDSHOPEE_PRICE_VARIANT_TYPE) && trim($CEDSHOPEE_PRICE_VARIANT_TYPE) == 'increase_fixed')}
                    selected="selected"
                {/if}
                value="increase_fixed"> {l s='Increase By Fixed Amount' mod='cedshopee'}</option>
        <option
                {if (isset($CEDSHOPEE_PRICE_VARIANT_TYPE) && trim($CEDSHOPEE_PRICE_VARIANT_TYPE) == 'decrease_fixed')}
                    selected="selected"
                {/if}
                value="decrease_fixed"> {l s='Decrease By Fixed Amount' mod='cedshopee'}</option>
        <option
                {if (isset($CEDSHOPEE_PRICE_VARIANT_TYPE) && trim($CEDSHOPEE_PRICE_VARIANT_TYPE) == 'increase_per')}
                    selected="selected"
                {/if}
                value="increase_per"> {l s='Increase By Fixed Percentage' mod='cedshopee'}</option>
        <option
                {if (isset($CEDSHOPEE_PRICE_VARIANT_TYPE) && trim($CEDSHOPEE_PRICE_VARIANT_TYPE) == 'decrease_per')}
                    selected="selected"
                {/if}
                value="decrease_per"> {l s='Decrease By Fixed Percentage' mod='cedshopee'}</option>
    </select>
    <p class="help-block">
        {l s='Select the price variation if you want to send different product price at shopee' mod='cedshopee'}
    </p>

</div>
<script>
    $(document).ready(function () {
        var type = $("#price_variation_type").val();
        var fixed_price = $('#fixed_price').val();
        var fixed_per = $('#fixed_per').val();
        if ((type == 'increase_fixed' || type == 'decrease_fixed')) {
            $('#fixed_per').closest('.form-group').hide();
            $('#fixed_price').closest('.form-group').show();
        } else if ((type == 'increase_per' || type == 'decrease_per')) {
            $('#fixed_price').closest('.form-group').hide();
            $('#fixed_per').closest('.form-group').show();
        } else {
            $('#fixed_price').closest('.form-group').hide();
            $('#fixed_per').closest('.form-group').hide();
        }
    });
    function priceVariation(value) {
        var type = value.value;
        if (type == 'increase_fixed' || type == 'decrease_fixed') {
            $('#fixed_per').closest('.form-group').hide();
            $('#fixed_price').closest('.form-group').show();
        } else if (type == 'increase_per' || type == 'decrease_per') {
            $('#fixed_price').closest('.form-group').hide();
            $('#fixed_per').closest('.form-group').show();
        } else {
            $('#fixed_price').closest('.form-group').hide();
            $('#fixed_per').closest('.form-group').hide();
        }
    }
</script>