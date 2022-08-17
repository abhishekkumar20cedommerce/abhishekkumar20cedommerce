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
    <div class="form-group">

        <div class="form-group">
            <label class="control-label col-lg-3">{l s='Api Mode' mod='cedshopee'}</label>
            <div class="col-lg-8">
                <select name="CEDSHOPEE_MODE" id="CEDSHOPEE_MODE" onchange="apiMode(this.value);">
                    <option value="1" {if ($api_mode == 1)} selected="selected" {/if} >Live</option>
                    <option value="2" {if ($api_mode == 2)} selected="selected" {/if} >Sandbox</option>
                </select>
            </div>
        </div>


        <div style="display: none;" id="live">
            <div class="form-group">
                <label class="control-label col-lg-3 required">{l s='Live Api Url' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_LIVE_API_URL" id="CEDSHOPEE_LIVE_API_URL" value="{$live_api_url|escape:'htmlall':'UTF-8'}" readonly />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Live Partner ID' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_LIVE_PARTNER_ID" id="CEDSHOPEE_LIVE_PARTNER_ID" value="{$live_partner_id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Live Shop ID' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_LIVE_SHOP_ID" id="CEDSHOPEE_LIVE_SHOP_ID" value="{$live_shop_id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Live Signature' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_LIVE_SIGNATURE" id="CEDSHOPEE_LIVE_SIGNATURE" value="{$live_signature|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
        </div>

        <div style="display: none;" id="sandbox">
            <div class="form-group">
                <label class="control-label col-lg-3 required">{l s='Sandbox Api Url' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_SANDBOX_API_URL" id="CEDSHOPEE_SANDBOX_API_URL" value="{$sandbox_api_url|escape:'htmlall':'UTF-8'}" readonly />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Sandbox Partner ID' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_SANDBOX_PARTNER_ID" id="CEDSHOPEE_SANDBOX_PARTNER_ID" value="{$sandbox_partner_id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Sandbox Shop ID' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_SANDBOX_SHOP_ID" id="CEDSHOPEE_SANDBOX_SHOP_ID" value="{$sandbox_shop_id|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3 required"> {l s='Sandbox Signature' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_SANDBOX_SIGNATURE" id="CEDSHOPEE_SANDBOX_SIGNATURE" value="{$sandbox_signature|escape:'htmlall':'UTF-8'}" />
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3"> {l s='Redirect URI' mod='cedshopee'}</label>
            <div class="col-lg-9">
                <input type="text" name="CEDSHOPEE_REDIRECT_URI" id="CEDSHOPEE_REDIRECT_URI" value="{$redirect_uri|escape:'htmlall':'UTF-8'}" readonly />
            </div>
        </div>

        <div class="form-group">
            <label class="control-label col-lg-3"> </label>
            <div class="col-lg-9">
                <input onclick="getValidationCredentials();" type="button" name="CEDSHOPEE_BUTTON" value="Validate Credentials" id="CEDSHOPEE_BUTTON" class="btn btn-primary" />
            </div>
        </div>

        <div class="form-group">
            <div class="form-group">
                <label class="control-label col-lg-3 "> {l s='Access Token' mod='cedshopee'}</label>
                <div class="col-lg-9">
                    <input type="text" name="CEDSHOPEE_ACCESS_TOKEN" id="CEDSHOPEE_ACCESS_TOKEN" value="{$access_token|escape:'htmlall':'UTF-8'}" readonly />
                </div>
            </div>
            <label class="control-label col-lg-3"> </label>
            <div class="col-lg-9">
                <input onclick="fetchToken();" type="button" name="CEDSHOPEE_TOKEN" value="Get Token" id="CEDSHOPEE_TOKEN" class="btn btn-primary" />
                <p> Get token will fetch after validate credential</p>
            </div>
        </div>

        <div id="response" style="display: none;"></div>
    </div>

</div>

<script type="text/javascript">
    $(document).ready(function() {

        // API Mode
        var api_mode = $("#CEDSHOPEE_MODE").val();
        // alert(api_mode);
        if (api_mode == 1) {
            $("#live").css("display", "block");
            $("#sandbox").css("display", "none");
            $("#CEDSHOPEE_LIVE_API_URL").val("https://partner.shopeemobile.com/api/v2/");
        } else if (api_mode == 2) {
            $("#sandbox").css("display", "block");
            $("#live").css("display", "none");
            $("#CEDSHOPEE_SANDBOX_API_URL").val("https://partner.test-stable.shopeemobile.com/api/v2/");
        }
    });

    function apiMode(value) {
        // alert(value);
        if(value == 1) {
            $("#live").css("display", "block");
            $("#sandbox").css("display", "none");
            $("#CEDSHOPEE_LIVE_API_URL").val("https://partner.shopeemobile.com/api/v2/");
        } else if (value == 2) {
            $("#sandbox").css("display", "block");
            $("#live").css("display", "none");
            $("#CEDSHOPEE_SANDBOX_API_URL").val("https://partner.test-stable.shopeemobile.com/api/v2/");
        }
    }

    function getValidationCredentials()
    {
        var api_mode = $("#CEDSHOPEE_MODE").val();
        if (api_mode == 1) {
            var api_url = $("#CEDSHOPEE_LIVE_API_URL").val();
            var partner_id = $("#CEDSHOPEE_LIVE_PARTNER_ID").val();
            var shop_id = $("#CEDSHOPEE_LIVE_SHOP_ID").val();
            var timestamp = {$timestamp|escape:'htmlall':'UTF-8'};
            var shop_signature = $("#CEDSHOPEE_LIVE_SIGNATURE").val();
        } else {
            var api_url = $("#CEDSHOPEE_SANDBOX_API_URL").val();
            var partner_id = $("#CEDSHOPEE_SANDBOX_PARTNER_ID").val();
            var shop_id = $("#CEDSHOPEE_SANDBOX_SHOP_ID").val();
            var timestamp = {$timestamp|escape:'htmlall':'UTF-8'};
            var shop_signature = $("#CEDSHOPEE_SANDBOX_SIGNATURE").val();
        }

        var redirect_url = $("#CEDSHOPEE_REDIRECT_URI").val();

        if((api_url.length != 0) && (partner_id.length != 0) && (shop_signature.length != 0) && (redirect_url.length != 0)) // && (shop_id.length != 0)
        {
            $.ajax({
                type: "POST",
                url: 'ajax-tab.php',
                data: {
                    'controller' : 'AdminModules',
                    'configure' : 'cedshopee',
                    'method' : 'ajaxProcessGenerateToken',
                    'action' : 'generateToken',
                    'token' : '{$token|escape:'htmlall':'UTF-8'}',
                    'timestamp': timestamp,
                    'partner_id' : partner_id,
                    'ajax' : true,
                    'shop_signature' : shop_signature,
                    'redirect_url' : redirect_url,
                },
                dataType: 'json',
                success: function(json) {

                    console.log(json);
                    if (!json['success']) {
                        $('.error-message').remove();
                        $('#response').after('<div class="error-message" style="margin-left:2px; color:red;">'+json['message']+'</div>');
                    }

                    if (json['success']) {
                        if((partner_id.length == 0) || (json['message'].length == 0)){
                            alert('Please fill in all the above details.');
                            return false;
                        } else {
                            // var url = api_url+'shop/auth_partner?partner_id='+partner_id+'&redirect='+redirect+'&sign='+ json['message']+'&timestamp='+timestamp+'';
                            var url = api_url+'shop/auth_partner?partner_id='+partner_id +'&timestamp='+timestamp+'&redirect='+redirect_url+'&sign='+ json['message'];
                            console.log(api_url);
                            window.open(url);
                        }
                    }
                },
                error: function(xhr, ajaxOptions, thrownError) {
                    alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                }
            });
        } else {
            if(api_url.length == 0)
            {
                alert("API URL is empty, please select API Mode");
            }

            if(partner_id.length == 0)
            {
                alert("Partner ID is empty, please fill in Partner ID");
            }

            if(shop_signature.length == 0)
            {
                alert("Signature is empty, please fill in Shopee key");
            }
        }
    }
    function  fetchToken() {
        var api_mode = $("#CEDSHOPEE_MODE").val();
        if (api_mode == 1) {
            var api_url = $("#CEDSHOPEE_LIVE_API_URL").val();
        } else {
            var api_url = $("#CEDSHOPEE_SANDBOX_API_URL").val();
        }

        $.ajax({
            type: "POST",
            url: 'ajax-tab.php',
            data: {
                'controller' : 'AdminModules',
                'configure' : 'cedshopee',
                'method' : 'ajaxProcessGetAccessToken',
                'action' : 'GetAccessToken',
                'token' : '{$token|escape:'htmlall':'UTF-8'}',
                'api_url' : api_url,
                'authorize_code' : '{$authorize_code|escape:'htmlall':'UTF-8'}',
                'ajax' : true,
            },
            dataType: 'json',
            success: function(json) {

                console.log(json);
                if (!json['success']) {
                    $('.error-message').remove();
                    $('#response').after('<div class="error-message" style="margin-left:2px; color:red;">'+json['message']+'</div>');
                }

                if (json['success']) {
                    $('#response').after('<div class="error-message" style="margin-left:2px; color:green;">'+json['message']+'</div>');
                    setTimeout(function () {
                        window.location.href = json['controller_url']; //will redirect to your blog page (an ex: blog.html)
                    }, 2000);
                }
            },
            error: function(xhr, ajaxOptions, thrownError) {
                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
            }
        });
    }

    function closeErrorMessage() {
        $("#error-text").html('');
        $("#error-message").hide();
        $("#response").css("display", "none");
    }

    function closeSuccessMessage() {
        $("#success-text").html('');
        $("#success-message").hide();
        $("#response").css("display", "none");
    }

</script>