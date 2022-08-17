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

<div>
    <span class="btn-group-action">
        <a class="btn btn-success center" title="View Shopee Data"
           onclick="viewDetails('{$product_id|escape:'htmlall':'UTF-8'}')">
            <i class="icon-eye"></i>
        </a>
    </span>
</div>

<div id="myModal" class="modal">
    <!-- Modal content -->
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="popup_content"> Loading.......</div>
    </div>
</div>

<script type="text/javascript">
    function viewDetails(product_id) {
        modal.style.display = "block";
        $.ajax({
            type: "POST",
            data: {
                ajax: true,
                controller: 'AdminCedShopeeProduct',
                action: 'viewDetails',
                product_id: product_id
            },
            success: function (json) {
                console.log(json);
                if (json) {
                    var response = JSON.parse(json);
                    // var response = json;
                    if (response.success) {
                        console.log(response);
                        var html = '';
                        var response = response.message;
                        console.log(response);
                        $.each(response, function (key, value) {
                            if (typeof value == 'object') {
                                var inner_html = '';
                                $.each(value, function (k, v) {
                                    if (typeof v == 'object') {
                                        var inner_html1 = '';
                                        $.each(v, function (index, vval) {
                                            inner_html1 += '<p class="text-left" >' + index + ' : ' + vval + '</p>';
                                        });
                                        html += '<tr><td class="text-left" >' + key + '</td><td class="text-left" >' + inner_html1 + '</td><tr>';
                                    } else {
                                        inner_html += '<p class="text-left" >' + k + ' : ' + v + '</p>';
                                    }
                                });
                                html += '<tr><td class="text-left" >' + key + '</td><td class="text-left" >' + inner_html + '</td><tr>';
                            } else {
                                html += '<tr><td class="text-left" >' + key + '</td><td class="text-left" >' + value + '</td><tr>';
                            }
                        });
                        $("#popup_content").html('<h2> Response : </h2><table class="table table-hover table-bordered">' + html + '</table>');
                    } else {
                        var html = '';
                        var response = response.message;
                        if (typeof response == 'object') {
                            $.each(response[0], function (key, value) {
                                html += '<tr><td class="text-left" >' + key + '</td><td class="text-left" >' + value + '</td><tr>';
                            });
                            $("#popup_content").html('<h2> Error : </h2><table class="table table-hover table-bordered">' + html + '</table>');
                        } else {
                            html += '<tr><td class="text-left" >Message</td><td class="text-left" >' + response + '</td><tr>';
                            $("#popup_content").html('<h2> Error : </h2><table class="table table-hover table-bordered">' + html + '</table>');
                        }
                    }
                }
            }
            ,
            statusCode: {
                500: function (xhr) {
                    if (window.console) console.log(xhr.responseText);
                },
                400: function (response) {
                    alert('<span style="color:Red;">Error While Uploading Please Check</span>');
                },
                404: function (response) {
                    alert('<span style="color:Red;">Error While Uploading Please Check</span>');
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                if (window.console) console.log(xhr.responseText);
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);

            },
        });
    }

    $('#myModal').delegate('.close', 'click', function() {
        $(this).parent().remove();
        modal.style.display = "none";
    });
</script>

<style type="text/css">
    /* The Modal (background) */
    .modal {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 999; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        overflow: auto; /* Enable scroll if needed */
        background-color: rgb(0, 0, 0); /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4); /* Black w/ opacity */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 5em 0 0 19em;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
    }

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
<script type="text/javascript">
    var modal = document.getElementById('myModal');
    var span = document.getElementsByClassName("close")[0];
    span.onclick = function () {
        modal.style.display = "none";
        $("#popup_content").html('Loading........');
    }
</script>
