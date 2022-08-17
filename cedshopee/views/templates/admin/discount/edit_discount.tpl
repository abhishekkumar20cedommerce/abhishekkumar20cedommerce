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
		<span id="default-error-message-text">{l s='Error' mod='cedshopee'}</span>
	</div>
</div>
<form method="post">
	<div class="panel">
		<div class="panel-heading">
			<i class="icon-tags"></i>
			{if isset($id) && !empty($id)}
				{l s=' Edit Discount: ' mod='cedshopee'}{$id|escape:'htmlall':'UTF-8'}
			{else} {l s=' Add New Discount' mod='cedshopee'}
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
							<input type="hidden" name="discount_id"
									{if isset($discount_id) && !empty($discount_id)}
										value="{$discount_id|escape:'htmlall':'UTF-8'}"
									{else}
										value=""
									{/if}
							/>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-4 required">
								{l s='Discount Name' mod='cedshopee'}
							</label>
							<div class="col-lg-8">
								<input type="text" name="discount_name" class="" required="required"
										{if isset($discount) && isset($discount['discount_name']) && $discount['discount_name']}
									value="{$discount['discount_name']|escape:'htmlall':'UTF-8'}" {else} value=""
										{/if}>
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-4 required">
								{l s='Start Date' mod='cedshopee'}
							</label>
							<div class="col-lg-8">
								<input type="datetime" name="start_date" class="datetime form-control" required="required"
										{if isset($discount) && isset($discount['start_date']) && $discount['start_date']}
									value="{$discount['start_date']|escape:'htmlall':'UTF-8'}" {else} value=""
										{/if}>
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-4 required">
								{l s='End Date' mod='cedshopee'}
							</label>
							<div class="col-lg-8">
								<input type="datetime" name="end_date" class="datetime form-control" required="required"
										{if isset($discount) && isset($discount['end_date']) && $discount['end_date']}
									value="{$discount['end_date']|escape:'htmlall':'UTF-8'}" {else} value=""
										{/if}>
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-4">
								{l s='Items' mod='cedshopee'}
							</label>
							<div class="col-lg-8">
								<input type="text" name="items" id="shopee_category" value="" style="width: 96%; padding: 1%; margin-top: 8px; margin-bottom: 8px;" />
								<div id="shopee-items" class="well well-sm" style="height: 150px; overflow: auto;">
									{foreach $shopeeItems as $key => $shopeeItemArray}
										{foreach $shopeeItemArray as $itemData}
											{if !empty($discount['items'])}
												{foreach $discount['items'] as $index => $shopee_item_id}
													{if $itemData['shopee_item_id'] == $shopee_item_id}
														<div id="shopee-item{$shopee_item_id|escape:'htmlall':'UTF-8'}" class="form-control"> <i class="icon-minus-circle"></i> {$itemData['name']|escape:'htmlall':'UTF-8'}
															<input type="hidden" name="shopee_items[]" value="{$shopee_item_id|escape:'htmlall':'UTF-8'}" />
														</div>
													{/if}
												{/foreach}
											{else}
												<div id="shopee-item{$itemData['shopee_item_id']|escape:'htmlall':'UTF-8'}" class="form-control"> <i class="icon-minus-circle"></i> {$itemData['name']|escape:'htmlall':'UTF-8'}
													<input type="hidden" name="shopee_items[]" value="{$itemData['shopee_item_id']|escape:'htmlall':'UTF-8'}" />
												</div>
											{/if}
										{/foreach}
									{/foreach}
								</div>
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

	$('input[name=\'items\']').autocomplete({
		delay: 500,
		source: function(request, response) {
			$.ajax({
				url: 'index.php?controller=AdminCedShopeeDiscount&method=ajaxProcessAutocomplete&token={$token|escape:'htmlall':'UTF-8'}',
				data: {
					action : 'autocomplete',
					ajax : true,
					filter_name : encodeURIComponent(request.term)
				},
				dataType: 'json',
				success: function(json) {
					json.unshift({
						shopee_item_id: 0,
						name: '--None--'
					});

					response($.map(json, function(item) {
						console.log(item);
						return {
							label: item.name,
							value: item.shopee_item_id
						}
					}));
				}
			});
		},
		select: function(event, ui) {
			// console.log(ui.item);
			$('input[name=\'items\']').val( ui.item.label);
			$('#shopee-items' + ui.item.value).remove();
			//$('#shopee-items').append('<div id="shopee-items' + ui.item.value + '" class="form-control"><i class="icon-minus-circle"></i>' + ui.item.label + '<input type="hidden" name="shopee_items[]" value="' + ui.item.value + '" /></div>');
			$('#shopee-items').append('<div id="shopee-items' + ui.item.value + '" class="form-control"><i class="icon-minus-circle"></i>' + ui.item.label + '<input type="hidden" name="shopee_items[]" value="' + ui.item.value + '" /></div>');
			return false;
		},
		focus: function(event, ui) {
			return false;
		}
	});
	$('#shopee-items').delegate('.icon-minus-circle', 'click', function() {
		$(this).parent().remove();
	});

	$('.datetime').datetimepicker({
		dateFormat: 'yy-mm-dd',
		timeFormat: 'h:m'
	});
</script>