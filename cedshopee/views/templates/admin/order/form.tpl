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

<div id="shopee-overlay">
	<div class="overlay-content">
		<img src="{$imgUrl|escape:'htmlall':'UTF-8'}">
	</div>
</div>

<style>
	.order_info {
		margin-top: 5px;
	}

	.control-label {
		text-align: right;
		margin-bottom: 0;
		padding-top: 7px;
	}

	.overlay-content {
		position: relative;
		top: 50%; /* 25% from the top */
		width: 100%; /* 100% width */
		text-align: center; /* Centered text/links */
	}

	#shopee-overlay {
		position: fixed; /* Sit on top of the page content */
		display: none; /* Hidden by default */
		width: 100%; /* Full width (cover the whole page) */
		height: 100%; /* Full height (cover the whole page) */
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background-color: rgba(0, 0, 0, 0.2); /* Black background with opacity */
		z-index: 2; /* Specify a stack order in case you're using a different order for other elements */
		cursor: pointer; /* Add a pointer on hover */
	}
</style>
<div class="bootstrap" id="order-error-message" style="display: none;">
	<div class="alert alert-danger" id="error-text">
		<button type="button" class="close" onclick="closeErrorMessage()">×</button>
		<span id="shopee-error-message">Error</span>
	</div>
</div>
<div class="bootstrap" id="order-success-message" style="display: none;">
	<div class="alert alert-success" id="success-text">
		<button type="button" class="close" onclick="closeSuccessMessage()">×</button>
		<span id="shopee-success-message">Success</span>
	</div>
</div>

<div class="panel">
	<div class="panel-heading">
		<i class="icon-credit-card"></i> {l s='ORDER DETAIL' mod='cedshopee'}
	</div>
	<div class="panel-body">
		<div class="row m-0 p-0">
			<div class="col-lg-6 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-briefcase"></i> {l s='ORDER INFO' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Shopee order ID : </strong>
							</div>
							<div class="col-lg-6">
								{if isset($ordersn) && isset($ordersn)}
									{$ordersn|escape:'htmlall':'UTF-8'}
									<input type="hidden" id="ordersn" name="ordersn"
										   value=" {$ordersn|escape:'htmlall':'UTF-8'}">
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Prestashop order ID : </strong>
							</div>
							<div class="col-lg-6">
								{if isset($id_order)}
									{$id_order|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Order date : </strong>
							</div>
							<div class="col-lg-6">
								{if isset($order_placed_date) && isset($order_placed_date)}
									{$order_placed_date|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Order status : </strong>
							</div>
							<div class="col-lg-6">
								{if isset($order_status) && $order_status}
									{$order_status|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Payment Method :</strong>
							</div>
							<div class="col-lg-6">
								{if isset($payment_method) && $payment_method}
									{$payment_method|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-6 text-center">
								<strong>Shipping Carrier :</strong>
							</div>
							<div class="col-lg-6">
								{if isset($shipping_carrier) && $shipping_carrier}
									{$shipping_carrier|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-6 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-user"></i> {l s='SHIPPING DETAIL' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="row order_info">
							<div class="col-lg-4">
								<strong>Customer Name :</strong>
							</div>
							<div class="col-lg-8">
								{if isset($customer_details['name'])}
									{$customer_details['name']|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-4">
								<strong>Customer Phone :</strong>
							</div>
							<div class="col-lg-8">
								{if isset($customer_details['phone'])}
									{$customer_details['phone']|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row order_info">
							<div class="col-lg-4">
								<strong>Customer email :</strong>
							</div>
							<div class="col-lg-8">
								{if isset($email)}
									{$email|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>

						<div class="row order_info">
							<div class="col-lg-4">
								<strong>Customer Address :</strong>
							</div>
							<div class="col-lg-8">
								<p>
									{if isset($customer_details['full_address'])}
										{$customer_details['full_address']|escape:'htmlall':'UTF-8'}
									{/if}
								</p>

							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row m-0 p-0">
			<div class="col-lg-8 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-shopping-cart"></i> {l s='ORDER ITEMS' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table" id="">
								<thead>
								<tr>
									<th class=""><strong class="title_box ">Product</strong></th>
									<th class=""><strong class="title_box ">Item / Variation Sku</strong></th>
									<th class=""><strong class="title_box ">Price</strong></th>
									<th class=""><strong class="title_box ">Quantity</strong></th>
									<th class=""><strong class="title_box ">Total</strong></th>
								</tr>
								</thead>
								<tbody>
								{if isset($items) && count($items)}
									{foreach $items as $item}
										<tr class="product-line-row">
											<td>
												{if isset($item['model_name']) & !empty($item['model_name'])}
													{$item['item_name']|escape:'htmlall':'UTF-8'} ({$item['model_name']|escape:'htmlall':'UTF-8'})
												{else}
													{$item['item_name']|escape:'htmlall':'UTF-8'}
												{/if}
											</td>
											<td>
												{if isset($item['model_sku']) & !empty($item['model_sku'])}
													{$item['item_sku']|escape:'htmlall':'UTF-8'} / {$item['variation_sku']|escape:'htmlall':'UTF-8'}
												{else}
													{$item['item_sku']|escape:'htmlall':'UTF-8'}
												{/if}
											</td>
											<td>
												{if isset($item['model_discounted_price']) & !empty($item['model_discounted_price'])}
													{$item['model_discounted_price']|escape:'htmlall':'UTF-8'}
												{else}
													{$item['model_original_price']|escape:'htmlall':'UTF-8'}
												{/if}
											</td>
											<td>{$item['model_quantity_purchased']|escape:'htmlall':'UTF-8'}</td>
											<td>
												{if isset($item['model_discounted_price']) & !empty($item['model_discounted_price'])}
													{$item['model_quantity_purchased']|escape:'htmlall':'UTF-8' * $item['model_discounted_price']|escape:'htmlall':'UTF-8'}
												{else}
													{$item['model_quantity_purchased']|escape:'htmlall':'UTF-8' * $item['model_original_price']|escape:'htmlall':'UTF-8'}
												{/if}
											</td>
										</tr>
									{/foreach}
								{/if}
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="col-lg-4 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-money"></i> {l s='ORDER TOTAL' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="row">
							<div class="col-lg-6"><strong> {l s='Subtotal :' mod='cedshopee'}</strong></div>
							<div class="col-lg-6">
								{if isset($sub_total)}
									{$sub_total|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
						<div class="row">
							<div class="col-lg-6"><strong>Shipping :</strong></div>
							<div class="col-lg-6">
								{if isset($shipping_fee)}
									{$shipping_fee|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
{*						<div class="row">*}
{*							<div class="col-lg-6"><strong>Escrow Amount :</strong></div>*}
{*							<div class="col-lg-6">*}
{*								{if isset($escrow_amount)}*}
{*									{$escrow_amount|escape:'htmlall':'UTF-8'}*}
{*								{/if}*}
{*							</div>*}
{*						</div>*}
						<div class="row" style="border-top: 0.01rem solid #DDDDDD; padding-top: 2px;">
							<div class="col-lg-6"><strong>Total :</strong></div>
							<div class="col-lg-6">
								{if isset($order_total)}
									{$order_total|escape:'htmlall':'UTF-8'}
								{/if}
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="row m-0 p-0">
			<div class="col-lg-12 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-ship"></i> {l s='SHIP ORDER' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="form-group row">
							<label class="control-label col-lg-3 required text-right"
								   for="ship_ordersn">
                                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
			                       The ordersn number of Order" data-html="true">
                                   {l s='Order ID' mod='cedshopee'}
                                </span>
							</label>
							<div class="col-lg-9">
								<input type="text" name="ordersn" id="ship_ordersn" value="{$ordersn|escape:'htmlall':'UTF-8'}">
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-3 required text-right"
								   for="ship_tracking_no">
                                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
			                       Tracking No. to Ship Order" data-html="true">
                                   {l s='Tracking No.' mod='cedshopee'}
                                </span>
							</label>
							<div class="col-lg-9">
								<input type="text" name="tracking_no" id="ship_tracking_no" value="">
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<button onclick="shipCompleteOrder('{$ordersn|escape:'htmlall':'UTF-8'}',
								'{$tracking_no|escape:'htmlall':'UTF-8'}')"
								class="btn btn-success" title="Ship Whole Order">
							<i class="icon-ship"></i> Ship Order
						</button>
					</div>
					<div id="shopee_shipwhole_response">

					</div>
				</div>
			</div>
		</div>

		<div class="row m-0 p-0">
			<div class="col-lg-12 m-0 p-0">
				<div class="panel">
					<div class="panel-heading">
						<i class="icon-ban"></i> {l s='CANCEL ORDER' mod='cedshopee'}
					</div>
					<div class="panel-body">
						<div class="form-group row">
							<label class="control-label col-lg-3 required text-right"
								   for="cancel_ordersn">
                                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
			                       The ordersn number of Order" data-html="true">
                                   {l s='Order ID' mod='cedshopee'}
                                </span>
							</label>
							<div class="col-lg-9">
								<input type="text" name="ordersn" id="cancel_ordersn" value="{$ordersn|escape:'htmlall':'UTF-8'}">
							</div>
						</div>
						<div class="form-group row">
							<label class="control-label col-lg-3 required text-right"
								   for="cancel_reason">
                                <span title="" data-toggle="tooltip" class="label-tooltip" data-original-title="
			                       Cancellation Reason to place Cancel request for Order" data-html="true">
                                   {l s='Cancel Reason' mod='cedshopee'}
                                </span>
							</label>
							<div class="col-lg-9">
								<select name="cancel_reason" id="cancel_reason">
									<option value="OUT_OF_STOCK">OUT OF STOCK</option>
									<option value="CUSTOMER_REQUEST">CUSTOMER REQUEST</option>
									<option value="UNDELIVERABLE_AREA">UNDELIVERABLE AREA</option>
									<option value="COD_NOT_SUPPORTED">COD NOT SUPPORTED</option>
								</select>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<button onclick="cancelOrder('{$ordersn|escape:'htmlall':'UTF-8'}',
								document.getElementById('cancel_reason').value,)"
								class="btn btn-danger" title="Cancel Whole Order"
								style="align: right;"><i class="icon-ban"></i> Cancel Order
						</button>
					</div>
					<div id="shopee_cancelwhole_response">

					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="panel-footer">
		<a class="btn btn-default" id="back-shopee-order-details" data-token="{$token|escape:'htmlall':'UTF-8'}"
		   href="{$controllerUrl|escape:'htmlall':'UTF-8'}">
			<i class="process-icon-cancel"></i> Back
		</a>
	</div>
</div>
<script type="text/javascript">
	function shipCompleteOrder(ordersn, tracking_no) {
        $.ajax({
          	type: "POST",
          	data: {
          		ajax: true,
          		controller: 'AdminCedShopeeOrder',
          		action: 'shipOrder',
          		token: $('#token').attr('data-token'),
          		ordersn: ordersn,
          		tracking_number: tracking_no 
          	},
          	success: function(response){
          		console.log(response);
          		if(response){
          			response = JSON.parse(response);
	          		if(response.success)
	          			$('#shopee_shipwhole_response').html('<span style="color:green;font-size:14px;font-weight:bold;">'+response.message+'</span>').delay(5000).fadeOut();
	          		else
	          			$('#shopee_shipwhole_response').html('<span style="color:Red;font-size:14px;font-weight:bold;">'+response.message+'</span>').delay(5000).fadeOut();
          		}
          	},
          	statusCode: {
          	500: function(xhr) {
              if(window.console) console.log(xhr.responseText);
            },
          	400: function (response) {
             alert('<span style="color:Red;">Error While Uploading Please Check</span>');
          	},
          	404: function (response) {
             
            	alert('<span style="color:Red;">Error While Uploading Please Check</span>');
          		}
          	},
          	error: function(xhr, ajaxOptions, thrownError) {
            	if(window.console) console.log(xhr.responseText);
            	alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

        	},
      	});
	}

	function cancelOrder(ordersn, cancel_reason) {
        $.ajax({
          	type: "POST",
          	data: {
          		ajax: true,
          		controller: 'AdminCedShopeeOrder',
          		action: 'cancelOrder',
          		token: $('#token').attr('data-token'),
          		ordersn: ordersn,
          		cancel_reason: document.getElementById('cancel_reason').value  
          	},
          	success: function(response){
          		console.log(response);
          		if(response){
          			response = JSON.parse(response);
	          		if(response.success)
	          			$('#shopee_cancelwhole_response').html('<span style="color:green;font-size:14px;font-weight:bold;">'+response.message+'</span>').delay(5000).fadeOut();
	          		else
	          			$('#shopee_cancelwhole_response').html('<span style="color:Red;font-size:14px;font-weight:bold;">'+response.message+'</span>').delay(5000).fadeOut();
          		}
          	},
          	statusCode: {
          	500: function(xhr) {
              if(window.console) console.log(xhr.responseText);
            },
          	400: function (response) {
             alert('<span style="color:Red;">Error While Uploading Please Check</span>');
          	},
          	404: function (response) {
             
            	alert('<span style="color:Red;">Error While Uploading Please Check</span>');
          		}
          	},
          	error: function(xhr, ajaxOptions, thrownError) {
            	if(window.console) console.log(xhr.responseText);
            	alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

        	},
      	});
	}
</script>