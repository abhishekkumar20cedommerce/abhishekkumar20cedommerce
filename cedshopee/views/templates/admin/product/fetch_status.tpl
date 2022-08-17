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
 * @package   Cedtradera
 */
-->

<div class="panel">
    <h3><i class="icon-tag"></i> {l s='Shopee Update Product Status' mod='cedshopee'}</h3>

    <div class="row">
        <div class="buttons"><button id="fetchStatusAction" data-token="{$token|escape:'htmlall':'UTF-8'}" class="btn btn-primary" onclick="processReport();">Process Update Status</button></div>
    </div>
    <div class="row">
        <div id="myModal" class="modal">
            <div class="modal-content">
                <span class="close" id="close_model">&times;</span>
                <div id="popup_content_loader"><p>Please wait processing status update........</p><p>This will take time as Product is large</p></div>
            </div>
        </div>
        <ol id="progress" style="display: initial;">
        </ol>
    </div>
</div>
<script type="text/javascript">
    var modal = document.getElementById('myModal');
    var span = document.getElementById("close_model");
    span.onclick = function() {
        modal.style.display = "none";
    }
</script>
<script type="text/javascript">
    var pagination_offset = 0;
    var pagination_entries_per_page = 100;
    function processReport() {
        modal.style.display = "block";
        sendUpdateRequest(pagination_offset, pagination_entries_per_page);
    }
    function sendUpdateRequest(pagination_offset, pagination_entries_per_page){
        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                ajax: true,
                controller: 'AdminCedShopeeUpdateStatus',
                action: 'updateStatus',
                token: "{$token|escape:'htmlall':'UTF-8'}",
                pagination_offset: pagination_offset,
                pagination_entries_per_page: pagination_entries_per_page
            },
            success: function (response) {
                var obj = JSON.parse(response);
                if (obj) {
                    // var obj = response;
                    if (obj.success) {
                        if(obj.pagination_offset) {
                            $("#progress").append('<li class="alert alert-success" >' + obj.message + '</li>');
                            sendUpdateRequest(obj.pagination_offset, pagination_entries_per_page);
                        } else if(obj.message)
                            $("#progress").append('<li class="alert alert-success" >' + obj.message + '</li>');
                    } else {
                        $("#progress").append('<li class="alert alert-danger" >' + obj.message + '</li>');
                        sendUpdateRequest(pagination_offset, pagination_entries_per_page);
                    }
                }
                modal.style.display = "none";
            },
            statusCode: {
                500: function (xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    $("#progress").append('<span style="color:Red;">Error While Uploading Please Check</span>');
                },
                404: function (response) {
                    $("#progress").append('<span style="color:Red;">Error While Uploading Please Check</span>');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }
</script>
<style type="text/css">
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 20%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>
