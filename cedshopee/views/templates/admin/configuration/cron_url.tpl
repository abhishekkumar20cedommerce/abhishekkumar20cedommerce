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
 * @package   CedShopee
 */
 -->

<div class="row">
	<div class="col-sm-3 text-right">{l s='Cron Secure Key' mod='cedshopee'}</div>
	<div class="col-sm-8">
		<div class="input-group col-lg-8">
			<input type="text" required="required" id="code" name="CEDSHOPEE_CRON_SECURE_KEY"
				value="{$CEDSHOPEE_CRON_SECURE_KEY|escape:'htmlall':'UTF-8'}" />
			<span class="input-group-btn">
				<a href="javascript:gencode(8);" class="btn btn-default"><i class="icon-random"></i>
					{l s='Generate' mod='cedshopee'}</a>
			</span>
		</div>
		<p class="help-block">
			{l s='This cron secure key need to set in the parameters of following cron urls' mod='cedshopee'}
		</p>
	</div>
</div>

<div class="row">
	<div class="col-sm-3 text-right">{l s='Shopee Cron Urls' mod='cedshopee'}</div>
	<div class="col-sm-8">
		<table class="table">
			<thead>
				<tr>
					<th><strong>{l s='Cron Name' mod='cedshopee'}</strong></th>
					<th><strong>{l s='Cron Url' mod='cedshopee'}</strong></th>
					<th><strong>{l s='Recommended Time' mod='cedshopee'}</strong></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td>Upload Product at Shopee</td>
					<td scope="row">
						{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cedshopee&controller=cron&method=uploadProduct&secure_key={$cron_secure_key|escape:'htmlall':'UTF-8'}
					</td>
					<td>ONCE A DAY</td>
				</tr>
				<tr>
					<td>Sync Inventory at Shopee</td>
					<td scope="row">
						{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cedshopee&controller=cron&method=updateInventory&secure_key={$cron_secure_key|escape:'htmlall':'UTF-8'}
					</td>
					<td>ONCE A DAY</td>
				</tr>
				<tr>
					<td>Sync Price at Shopee</td>
					<td scope="row">
						{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cedshopee&controller=cron&method=updatePrice&secure_key={$cron_secure_key|escape:'htmlall':'UTF-8'}
					</td>
					<td>ONCE A DAY</td>
				</tr>
				<tr>
					<td>Order Import</td>
					<td scope="row">
						{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cedshopee&controller=cron&method=fetchorder&secure_key={$cron_secure_key|escape:'htmlall':'UTF-8'}
					</td>
					<td>PER 1 HOUR</td>
				</tr>
				<tr>
					<td>Sync Order Status from Shopee</td>
					<td scope="row">
						{$base_url|escape:'htmlall':'UTF-8'}index.php?fc=module&module=cedshopee&controller=cron&method=syncorderstatus&secure_key={$cron_secure_key|escape:'htmlall':'UTF-8'}
					</td>
					<td>ONCE A DAY</td>
				</tr </tbody>
		</table>
	</div>
</div>