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

 <style>
    .control-label{
        text-align: right;
        margin-bottom: 0;
        padding-top: 7px;
    }
</style>
 <div class="bootstrap" id="error-message" style="display: none;">
     <div class="alert alert-danger" id="error-text">
         <button type="button" class="close" onclick="closeMessage()">×</button>
         <span id="default-error-message-text">Error</span>
     </div>
 </div>
<form method="post">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-tags"></i>
            {if isset($profileId) && !empty($profileId)}
                {l s=' Edit profile: ' mod='cedshopee'}{$profileId|escape:'htmlall':'UTF-8'}
            {else} {l s=' New profile' mod='cedshopee'}
            {/if}
        </div>
        <div class="panel-body">
            <div class="productTabs">
                <ul class="tab nav nav-tabs">
                    <li class="tab-row active">
                        <a class="tab-page" href="#general" data-toggle="tab">
                            <i class="icon-file-text"></i> {l s='Profile Info' mod='cedshopee'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#defaultAttributeMapping" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Default Attribute Mapping' mod='cedshopee'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#shopeeCategoryAttribute" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Shopee Category & Attributes' mod='cedshopee'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#logisticsWholesale" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Logistics & Wholesale' mod='cedshopee'}
                        </a>
                    </li>
                    <li class="tab-row">
                        <a class="tab-page" href="#tax_info" data-toggle="tab">
                            <i class="icon-wrench"></i> {l s='Tax info' mod='cedshopee'}
                        </a>
                    </li>
                </ul>
            </div>
            <div class="tab-content">
                <div class="panel tab-pane fade in active row" id="general">
                    {include file="./general.tpl"}
                </div>
                <div class="panel tab-pane" id="defaultAttributeMapping">
                    {include file="./default_attribute_mapping.tpl"}
                </div>
                <div class="panel tab-pane" id="shopeeCategoryAttribute">
                    {include file="./shopee_category_attribute.tpl"}
                </div>
                <div class="panel tab-pane" id="logisticsWholesale">
                    {include file="./profile_logistics_wholsale.tpl"}
                </div>
                <div class="panel tab-pane" id="tax_info">
                    {include file="./profile_tax_info.tpl"}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="test_form_submit_btn" name="submitProfileSave"
                    class="btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='cedshopee'}
            </button>
            <a class="btn btn-default" id="back-profile-controller" data-token="{$token|escape:'htmlall':'UTF-8'}" href="{$controllerUrl|escape:'htmlall':'UTF-8'}">
                <i class="process-icon-cancel"></i> {l s='Cancel' mod='cedshopee'}
            </a>
        </div>
    </div>
</form>
 <script>
    function closeMessage(){
        $("#error-message").hide();
     }
 </script>