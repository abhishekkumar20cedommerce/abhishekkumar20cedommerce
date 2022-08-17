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

<div id="myModal" class="modal">
    <div class="modal-content">
        <span class="close" id="close_model">&times;</span>
        <div id="popup_content_cedshopee" class="data"> {l s='Loading.......' mod='cedshopee' }</div>
    </div>
</div>
<script type="text/javascript">
    function viewLogData(data) {
        modal.style.display = "block";
        console.log(data);
        $("#popup_content_cedshopee").html(data);
    }
    //--></script>
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
        margin: 10% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 60%; /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }
    .data{
        max-height: 300px;
        overflow: auto;
        padding: 10px;
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
    var span = document.getElementById("close_model");
    span.onclick = function() {
        modal.style.display = "none";
        $("#popup_content_cedshopee").html('Loading........');
    }
</script>