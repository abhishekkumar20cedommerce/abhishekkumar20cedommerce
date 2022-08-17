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

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-tag"></i> {l s='Logistics' mod='cedshopee'}
    </div>
    <div class="panel-body">

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th width="1" style="text-align: center;"><input type="checkbox" onclick="$('input[name*=\'selected\']').attr('checked', this.checked);"/></th>
                    <th><label class="control-label" for="input-logistics">{l s='Logistics' mod='cedshopee'}</label></th>
                    <th><label class="control-label" for="input-is_free">{l s='Is Free' mod='cedshopee'}</label></th>
                    <th><label class="control-label" for="input-size-selection">{l s='Size Selection (needed if selected logistics have fee_type = SIZE_SELECTION)' mod='cedshopee'}</label></th>
                </tr>
                </thead>
                <tbody>
                {if isset($logistics_list) && $logistics_list}
                    {foreach $logistics_list as $count => $logistic}

                        <tr>
                            <td style="text-align: center;">
                                {if isset($logistics[{$count|escape:'htmlall':'UTF-8'}]['selected']) && ($logistic['logistic_id'] == $logistics[{$count|escape:'htmlall':'UTF-8'}]['selected'])}
                                    <input type="checkbox" name="logistics[{$count|escape:'htmlall':'UTF-8'}][selected]" value="{$logistic['logistic_id']|escape:'htmlall':'UTF-8'}" checked="checked"/>
                                {else}
                                    <input type="checkbox" name="logistics[{$count|escape:'htmlall':'UTF-8'}][selected]" value="{$logistic['logistic_id']|escape:'htmlall':'UTF-8'}"/>
                                {/if}
                            </td>
                            <td>
                                <input type="hidden" name="logistics[{$count|escape:'htmlall':'UTF-8'}][logistics]" value="{$logistic['logistic_id']|escape:'htmlall':'UTF-8'}" class="form-control" />

                                <input type="text" name="logistics[{$count|escape:'htmlall':'UTF-8'}][logistic_name]" value="{$logistic['logistic_name']|escape:'htmlall':'UTF-8'}" class="form-control" readonly="readonly" />
                            </td>
                            <td>
                                <select name="logistics[{$count|escape:'htmlall':'UTF-8'}][is_free]" id="input-is_free" class="form-control">
                                    <option value="0" {if isset($logistics[{$count|escape:'htmlall':'UTF-8'}]['is_free']) && ($logistics[{$count|escape:'htmlall':'UTF-8'}]['is_free'] == '0') } selected="selected" {/if} >Disabled</option>
                                    <option value="1" {if isset($logistics[{$count|escape:'htmlall':'UTF-8'}]['is_free']) && ($logistics[{$count|escape:'htmlall':'UTF-8'}]['is_free'] == '1') } selected="selected" {/if} >Enabled</option>
                                </select>
                            </td>
                            <td>
                                <select name="logistics[{$count|escape:'htmlall':'UTF-8'}][size_selection]" id="input-size-selection" class="form-control" >
                                    {if isset($logistic['fee_type']) && ($logistic['fee_type'] == 'SIZE_SELECTION') }
                                        {foreach $logistic['sizes'] as $key => $size}
                                            <option value="{$size['size_id']|escape:'htmlall':'UTF-8'}"
                                                    {if isset($logistic[{$count|escape:'htmlall':'UTF-8'}]['size_selection']) && ($logistic[{$count|escape:'htmlall':'UTF-8'}]['size_selection'] == $size['size_id']) }
                                                        selected="selected"
                                                    {/if} >
                                                {$size['name']|escape:'htmlall':'UTF-8'}
                                            </option>
                                        {/foreach}
                                    {else}
                                    <option value=""></option>
                                    {/if}
                                </select>
                            </td>
                        </tr>
                    {/foreach}
                {/if}
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-tag"></i> {l s='Wholesale' mod='cedshopee'}
    </div>
    <div class="panel-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                <tr>
                    <th><label class="control-label" for="input-wholesale_min">{l s='Wholesale Min' mod='cedshopee'}</label></th>
                    <th><label class="control-label" for="input-wholesale_max">{l s='Wholesale Max' mod='cedshopee'}</label></th>
                    <th><label class="control-label" for="input-wholesale_unit_price">{l s='Wholesale Price' mod='cedshopee'}</label></th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td><input type="text" name="wholesale[wholesale_min]" value="{if isset($selectedWholesale['wholesale_min']) && ($selectedWholesale['wholesale_min']) } {$selectedWholesale['wholesale_min']|escape:'htmlall':'UTF-8'} {/if}" placeholder="" id="input-wholesale_min" class="form-control" /></td>
                    <td><input type="text" name="wholesale[wholesale_max]" value="{if isset($selectedWholesale['wholesale_max']) && ($selectedWholesale['wholesale_max']) } {$selectedWholesale['wholesale_max']|escape:'htmlall':'UTF-8'} {/if}" placeholder="" id="input-wholesale_max" class="form-control"/></td>
                    <td><input type="text" name="wholesale[wholesale_unit_price]" value="{if isset($selectedWholesale['wholesale_unit_price']) && ($selectedWholesale['wholesale_unit_price']) } {$selectedWholesale['wholesale_unit_price']|escape:'htmlall':'UTF-8'} {/if}" placeholder="" id="input-wholesale_unit_price" class="form-control" /></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>