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
        {if isset($returnsn) && !empty($returnsn)}
            {l s=' View Return: ' mod='cedshopee'}{$returnsn|escape:'htmlall':'UTF-8'}
        {else} {l s='View Return' mod='cedshopee'}
        {/if}
    </div>
    <div class="panel-body">
       <div class="row">
		    <div class="col-sm-8 col-sm-offset-1">
		        <div class="form-wrapper">
		            <div class="form-group row">
		                <input type="hidden" name="id"
	                        {if isset($id) && !empty($id)}
	                            value="{$id|escape:'htmlall':'UTF-8'}"
	                        {else}
	                            value=""
	                        {/if}
		                />
		            </div>
		            <div class="form-group row">
		                <label class="control-label col-lg-4 required">
		                    {l s='Shopee Return ID' mod='cedshopee'}
		                </label>
		                <div class="col-lg-8">
		                    <input type="text" name="returnsn" class="" required="required" {if !empty(returnsn)} value="{$returnsn|escape:'htmlall':'UTF-8'}" {else} value="" {/if} />
		                </div>
		            </div>
		            <div class="form-group row">
		                <label class="control-label col-lg-4 required">
		                    {l s='Email' mod='cedshopee'}
		                </label>
		                <div class="col-lg-8">
		                    <input type="email" name="email" class=" form-control" {if !empty($email)} value="{$email|escape:'htmlall':'UTF-8'}" {else} value="" {/if} />
		                </div>
		            </div>
		            <div class="form-group row">
		                <label class="control-label col-lg-4 required">
		                    {l s='Dispute Reason' mod='cedshopee'}
		                </label>
		                <div class="col-lg-8">
		                    <select name="dispute_reason">
		                    	<option value="NON_RECEIPT">Non Receipt</option>
		                    	<option value="OTHER">Other</option>
		                    	<option value="NOT_RECEIVED">Not Received</option>
		                    	<option value="UNKNOWN">Unknown</option>
		                    </select>
		                </div>
		            </div>
		            <div class="form-group row">
		                <label class="control-label col-lg-4">
		                    {l s='Dispute Description' mod='cedshopee'}
		                </label>
		                <div class="col-lg-8">
		                   <textarea name="dispute_text_reason" value="">{if !empty($dispute_text_reason)}{$dispute_text_reason|escape:'htmlall':'UTF-8'}{/if}</textarea>
		                </div>
		            </div>
		        </div>
		    </div>
		    <div class="col-sm-1"></div>
		</div>
    </div>
    <div class="panel-footer">
        <button type="submit" value="1" id="test_form_submit_btn" name="submitProfileSave"
                class="btn btn-default pull-right">
            <i class="process-icon-save"></i> {l s='Save' mod='cedshopee'}
        </button>
        <a class="btn btn-default" id="back-return-controller" data-token="{$token|escape:'htmlall':'UTF-8'}" href="{$controllerUrl|escape:'htmlall':'UTF-8'}">
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