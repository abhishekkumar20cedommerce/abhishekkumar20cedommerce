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
			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-6">
						<div class="panel" id="formAddPaymentPanel">
					        <div class="panel-heading">
					          <i class="icon-truck"></i>
					          Return Details
					        </div>
					        <div class="table-responsive">
					            <table class="table">
					              	<tbody>
					              		{foreach $return_data as $key => $value}
					              		  {foreach $value as $index => $val}
											<tr>
												<td>
													{$index|escape:'htmlall':'UTF-8'}
												</td>
												<td>
													{$val|escape:'htmlall':'UTF-8'}
												</td>
											</tr>
										  {/foreach}
										{/foreach}
					              	</tbody>
					            </table>
					        </div>
				        </div>
					</div>
					
					<div class="col-lg-6">
						<div class="panel" id="formAddPaymentPanel">
					        <div class="panel-heading">
					          <i class="icon-image"></i>
					          Images
					        </div>
					        <div class="table-responsive">
					            <table class="table">
					              	<tbody>
					              		{foreach $images as $index => $value}
										<tr>
											<td>
												{$index|escape:'htmlall':'UTF-8'}
											</td>
											<td>
												{$value|escape:'htmlall':'UTF-8'}
											</td>
										</tr>
										{/foreach}
					              	</tbody>
					            </table>
					        </div>
				        </div>
					</div>	
				</div>
			</div>
		</div>
	</div>

	<div class="panel-body">
		<div class="row">
			<div class="col-lg-12">
				<div class="row">
					<div class="col-lg-6">
						<div class="panel" id="formAddPaymentPanel">
					        <div class="panel-heading">
					          <i class="icon-user"></i>
					          User Details
					        </div>
					        <div class="table-responsive">
					            <table class="table">
					              	<tbody>
					              		{foreach $user as $key => $value}
											<tr>
												<td>
													{$key|escape:'htmlall':'UTF-8'}
												</td>
												<td>
													{$value|escape:'htmlall':'UTF-8'}
												</td>
											</tr>
										{/foreach}
					              	</tbody>
					            </table>
					        </div>
				        </div>
					</div>
					
					<div class="col-lg-6">
						<div class="panel" id="formAddPaymentPanel">
					        <div class="panel-heading">
					          <i class="icon-shopping-cart"></i>
					          Item Details
					        </div>
					        <div class="table-responsive">
					            <table class="table">
					              	<tbody>
					              		{foreach $item as $index => $value}
										<tr>
											<td>
												{$index|escape:'htmlall':'UTF-8'}
											</td>
											<td>
												{$value|escape:'htmlall':'UTF-8'}
											</td>
										</tr>
										{/foreach}
					              	</tbody>
					            </table>
					        </div>
				        </div>
					</div>	
				</div>
			</div>
		</div>
	</div>
</div>

<script>
    function closeMessage(){
        $("#error-message").hide();
     }
 </script>