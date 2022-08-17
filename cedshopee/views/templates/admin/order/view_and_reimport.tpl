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

 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   Cedshopee
 */
-->

<span class="btn-group-action">
    <input style="display: none;" type="button" class="btn btn-info btn-lg" data-toggle="modal" data-target="#module-modal-cedebay{$id|escape:'htmlall':'UTF-8'}" id="module-modal-cedebay-button{$id|escape:'htmlall':'UTF-8'}">
    <button  type="button" class="btn btn-info btn-sm" onclick="prettyPrint('{$id|escape:'htmlall':'UTF-8'}');"><i class="material-icons">edit</i></button>
</span>

<div id="module-modal-cedebay{$id|escape:'htmlall':'UTF-8'}" class="modal  modal-vcenter fade" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title module-modal-title">{l s='Order Edit and Re Import' mod='cedshopee'}</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <textarea rows="25" name="feed_content{$id|escape:'htmlall':'UTF-8'}" class="form-control" id="feed_content{$id|escape:'htmlall':'UTF-8'}">{$order_data|escape:'htmlall':'UTF-8'}</textarea>
                        </div>
                        <div class="text-center">
                            <button type="button" onclick="saveandresend{$id|escape:'htmlall':'UTF-8'}(this,'{$id|escape:'htmlall':'UTF-8'}');" class="btn btn-primary">{l s='Save and Reimport' mod='cedshopee'}</button>
                        </div>
                    </div>
                    <div id="progress{$id|escape:'htmlall':'UTF-8'}">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    function prettyPrint(row_id) {
        var ugly = document.getElementById('feed_content'+row_id).value;
        var obj = JSON.parse(ugly);
        var pretty = JSON.stringify(obj, undefined, 4);
        document.getElementById('feed_content'+row_id).value = pretty;
        document.getElementById('module-modal-cedebay-button'+row_id).click();
    }
    function saveandresend{$id|escape:'htmlall':'UTF-8'}(button_object,id) {
        button_object.disabled=true;
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedShopeeFailedOrder',
                action: 'resubmitFeed',
                token: "{$token|escape:'htmlall':'UTF-8'}",
                feed_content:$('#feed_content'+id).val(),
                id:"{$id|escape:'htmlall':'UTF-8'}",
            },
            success: function(response) { console.log(response);
                response = JSON.parse(response);

                button_object.disabled=false;
                if (response.message && response.success == true) {

                    var success_message = response.message;
                    $("#progress{$id|escape:'htmlall':'UTF-8'}").append('<li class="alert alert-success" >'+success_message+'</li>');

                } else {
                    $("#progress{$id|escape:'htmlall':'UTF-8'}").append('<li class="alert alert-danger">'+ response.message+'</li>');
                }
            },
            statusCode: {
                500: function(xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#progress").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                },
                404: function (response) {
                    $("#progress").append('<span style="color:Red;">Some error while reimport contact developer</span>');
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }
</script>