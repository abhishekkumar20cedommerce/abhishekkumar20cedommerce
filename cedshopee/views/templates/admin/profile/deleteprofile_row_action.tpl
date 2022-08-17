<!--
 /**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)pause
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedLazada
 */
 -->

<a id="delete-at-shopee-row-{$id|escape:'htmlall':'UTF-8'}" style="cursor: pointer;" onclick='deleteAtShopee("{$href|escape:'html':'UTF-8'}",{$id|escape:'html':'UTF-8'})' title="{$action|escape:'html':'UTF-8'}">
    <i class="icon-trash"></i> {$action|escape:'html':'UTF-8'}
</a>
<script>
    function deleteAtShopee(hrefUrl,id)
    {
        var res = confirm('Are you sure?');
        console.log(res);
        if (res == true) {

            var a = document.getElementById('delete-at-shopee-row-'+id);
            a.href = hrefUrl;
        }
    }
</script>