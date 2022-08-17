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

<button type="button" id="validate" name="submitValidateCredentials" class="btn btn-primary" value="">{l s='Validate' mod='cedshopee'}</button>
<div id="validate_credential_result" style="display: none;"></div>

<script type="text/javascript">
$('#validate').on('click', function() {

  $.ajax({
      url: 'index.php?controller=AdminModules&configure=cedshopee&method=ajaxProcessValidateApi&ajax=1&action=validateApi&token={$token|escape:'htmlall':'UTF-8'}',
      type: 'post',
      data: {
        'action' : 'validateApi',
        'ajax' : true
      },
      dataType: 'json',
      cache: false,
      beforeSend: function() {
          $('#form-save, #form-back').attr('disabled', true);
          openNav();
      },
      complete: function() {
          $('#form-save, #form-back').attr('disabled', false);
          closeNav();
      },
      success: function(result) {
          
        if(result){
       
          if (!result.success) {
              $('.error-message').remove();
              $('#validate_credential_result').css('display', 'block');
              $('#validate_credential_result').html('<span class="error-message" style="margin-left:2px; color:red;">'+result.message+'</span>').delay(5000).fadeOut();
          }

          if (result.success) {
              $('.success-message').remove();
              validateresult(result.message)
          }
        } else {
          $('#validate_credential_result').css('display', 'block');
          $('#validate_credential_result').html('<span class="error-message" style="margin-left:2px; color:red;">No response from Shopee</span>').delay(5000).fadeOut();
        }
      },
      error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
  });
});

function validateresult(result) {
  $.ajax({
      url: 'index.php?controller=AdminModules&configure=cedshopee&method=ajaxProcessValidateResult&ajax=1&action=validateResult&token={$token|escape:'htmlall':'UTF-8'}',
      type: 'post',
      data: {
        'result' : result,
        'action' : 'validateResult',
        'ajax' : true
      },
      dataType: 'json',
      cache: false,
      success: function(result) {
          if (!result.success) {
              $('.error-message').remove();
              $('#validate_credential_result').css('display', 'block');
              $('#validate_credential_result').html('<span class="error-message" style="margin-left:2px; color:red;">'+result.message+'</span>').delay(5000).fadeOut();
          }

          if (result.success) {
              $('.success-message').remove();
              $('#validate_credential_result').css('display', 'block');
              $('#validate_credential_result').html('<span class="error-message" style="margin-left:2px; color:green;">'+result.message+'</span>').delay(5000).fadeOut();
              // $('#validate').after('<div class="success-message" style="margin-left:2px; color:green;">'+json['message']+'</div>');
              //$('#cedshopee_validate_status').val(json['message']);
              $("#validate").attr('disabled',true);
          }
      },
      error: function(xhr, ajaxOptions, thrownError) {
          alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
      }
  });
}
</script>
<style>
    .overlay {
      height: 100%;
      width: 100%;
      display: none;
      position: fixed;
      z-index: 1;
      top: 0;
      left: 0;
      background-color: rgb(0,0,0);
      background-color: rgba(0,0,0, 0.9);
    }

    .overlay-content {
      position: relative;
      top: 25%;
      width: 100%;
      text-align: center;
      margin-top: 30px;
    }
    .cedshopee-loading {
      position: relative;
      top: 25%;
      width: auto;
      text-align: center;
      margin-top: 30px;
    }
    .overlay a {
      padding: 8px;
      text-decoration: none;
      font-size: 36px;
      color: #818181;
      display: block;
      transition: 0.3s;
    }

    .overlay .closebtn {
      position: absolute;
      top: 20px;
      right: 45px;
      font-size: 60px;
    }

    @media screen and (max-height: 250px) {
      .overlay .closebtn {
        font-size: 40px;
        top: 15px;
        right: 35px;
      }
    }
  </style>
<div id="myNav" class="overlay">
  <a href="javascript:void(0)" class="closebtn" onclick="closeNav()">&times;</a>
  <div class="overlay-content">
    <img class="cedshopee-loading fa fa-spinner" style="margin-left:2px" src='{$base_url|escape:'htmlall':'UTF-8'}modules/cedshopee/views/img/cedshopee_loader.gif'>
  </div>
</div>
<script>
    function openNav() {
        document.getElementById("myNav").style.display = "block";
    }

    function closeNav() {
        document.getElementById("myNav").style.display = "none";
    }
</script>