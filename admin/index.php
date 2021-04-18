<?php
require_once('stripe-php/init.php');
include 'callAPI.php';
include 'admin_token.php';

// Query to get package custom fields
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();

// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);
$stripe_secret_key =  getSecretKey();
//stripe secret key to be fetched on custom fields
\Stripe\Stripe::setApiKey($stripe_secret_key);
$stripe = \Stripe\Product::all();
$products =  $stripe->data;
$plan_type = 'new';
//print_r($products);
$key = array_search('Arcadier Subscription', array_column($products, 'name'));
// echo gettype($key);
error_log('key' . $key);
if (gettype($key) == integer) {
    $plan_id='';
    $plan_type = 'existing';
    foreach($marketplaceInfo['CustomFields'] as $cf) {
        if ($cf['Name'] == 'plan_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
            $plan_id = $cf['Values'][0];
           // echo ($plan_id);
        }
    }
    if (!empty($plan_id)) {
       // echo 'plan id ' . $plan_id;
        $stripe = \Stripe\Price::retrieve($plan_id);
    //    echo $stripe;
        $package_name = $stripe->nickname;
        $price = $stripe->unit_amount;
        $metadata= $stripe->metadata;
        $details = json_encode($metadata);
        $details1 = implode(',', json_decode($details, true));

    }
   
}
else {
   echo 'not found';
}

?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/subscription.css">
<div class="page-content" id="payments-content">
    <div class="gutter-wrapper">
        <div class="panel-box">
            <div class="page-content-top">
                <div class="subscription-title">
                    <h4>Setup a membership fee for your merchant</h4>
                    <h5>Membership &amp; Subscription</h5>
                </div>
            </div>
        </div>
        <div class="panel-box goo-panel-box">
            <div class="page-content-top">
                <div><i class="icon icon-pay_link1 icon-3x"></i></div>
                <div>
                    <p><span class="goo-translate-name">Link your Subscription account to your Marketplace</span></p>
                    <p class="payment-stripe-info">If you change your live secret keys, all your merchants will have to
                        <strong class="red-bold-strong">re-onboard</strong> to your <strong
                            class="red-bold-strong">new</strong> Subscription account before they can start selling
                        again!
                    </p>
                </div>
                <div>&nbsp;</div>
            </div>
            <div class="page-content-btm">
                <form name="live_secret_key" id="live_secret_key" action="#">
                    <div class="tracking-id show-right-broder">
                        <p class="google-analytics-id-txt goo-lowercase"><span class="red-bold-strong">LIVE</span>
                            PUBLISHABLE
                            KEY</p>
                        <input type="text" id="live-publishable-key" name="live-publishable-key" value=""
                            class="form-control required" placeholder="pk_test_51INpZ6Lpsmplepublishablekey">
                    </div>
                    <div class="tracking-id show-right-broder mt-20">
                        <p class="google-analytics-id-txt goo-lowercase"><span class="red-bold-strong">LIVE</span>
                            SECRET KEY
                        </p>
                        <input type="text" id="live-secret-key" name="live-secret-key" value=""
                            class="form-control required" placeholder="sk_test_51INpZ6LpiOi48zknrwesamplesecretkey">
                    </div>
                    <div class="mt-20">
                        <div id="save-btn" class="btn-area"><input type="button" class="btn-blue"
                                onclick="MakeUneditable()" value="Save" name="save"></div>
                        <div class="btn-area" id="edit-btn" style="display: none;"><a href="javascript:void(0);"
                                class="btn-blue" onclick="SaveConfirm()">Edit</a></div>
                    </div>
                    <div class="mt-10">&nbsp;</div>
                </form>
            </div>
        </div>
        <div class="panel-box subscription-form">
            <div class="page-content-top ">
                <h4>Subscription Details</h4>
            </div>
            <form name="connect-subscription-marketplace" id="connect-subscription-marketplace" action="">
                <div class="form-area">
                    <div class="form-element">
                        <label for="package_name">Package name</label>
                        <input type="text" name="package_name" id="package_name" maxlength="30" class="txt required" value= "<?php echo $package_name ?>" placeholder="Premium, Enterprise .." >
                    </div>
                    <div class="form-element">
                        <label for="price_per_month">Price per month</label>
                        <input type="number" name="price_per_month" id="price_per_month" class="txt required" value= "<?php echo $price ?>" placeholder="0.00" >
                    </div>
                    <div class="form-element">
                        <label for="subscription-details">Subscription details (e.g. what your merchant gets from
                            subscribing)</label>
                        <textarea type="text" name="subscription-details" id="subscription-details"
                            class="txt" placeholder='Optional:' ><?php echo $details1 ?></textarea>
                    </div>

                    <div class="sync-data">
                        <div class="btn-area" id="connect-save-btn"> <a href="javascript:void(0);"
                                class="btn-blue" id="save" plan-type="<?php echo $plan_type; ?>" plan-id="<?php echo $plan_id ?>">Save</a></div>
                        <div class="btn-area" id="connect-edit-btn" style="display: none;"> <a
                                href="javascript:void(0);" class="btn-blue"
                                onclick="SaveConnectSubscriptionConfirm()">Edit</a></div>
                    </div>

                </div>
            </form>
        </div>

    </div>


    <div class="popup popup-save-confirm" id="link-subscription-account" style="display: none;">
        <div class="popup-wrapper">
            <div class="popup-body">
                <div align="center">
                    <h2>Are you sure you want to edit this? </h2>
                </div>
                <div class="text-center content-text">You will <span class="red-bold">lose all your merchants</span>, as
                    they would have to re-onboard to your new subscription account in their payment settings before they
                    can start selling again.</div>
            </div>
            <div class="popup-footer text-center">
                <input onclick="popupConfirm_close('link-subscription-account');" class="mybtn btn-grey" type="button"
                    value="Cancel" name="cancel">
                <input onclick="popupConfirm_ok();" class="mybtn btn-blue" type="button" value="Okay" name="cancel">
            </div>
            <a href="javascript:void(0)" class="close-popup"
                onclick="popupConfirm_close('link-subscription-account');"><i class="icon icon-close"></i> </a>
        </div>
    </div>

    <div class="popup popup-save-confirm" id="connect-subscription-account">
        <div class="popup-wrapper">
            <div class="popup-body">
                <div align="center">
                    <h2>Are you sure you want to edit this? </h2>
                </div>
                <div class="text-center content-text">You will <span class="red-bold">lose all your merchants</span>, as
                    they would have to re-onboard to your new subscription account in their payment settings before they
                    can start selling again.</div>
            </div>
            <div class="popup-footer text-center">
                <input onclick="popupConfirm_close('connect-subscription-account');" class="mybtn btn-grey"
                    type="button" value="Cancel" name="cancel">
                <input onclick="popupCoonectStrip_ok();" class="mybtn btn-blue" type="button" value="Okay"
                    name="cancel">
            </div>
            <a href="javascript:void(0)" class="close-popup"
                onclick="popupConfirm_close('connect-subscription-account');"><i class="icon icon-close"></i> </a>
        </div>
    </div>


    <div id="cover" style="display: none;"></div>
</div>

<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>

<script type="text/javascript">
    // Set link subscription functions
    function popupConfirm_close(closeid) {
        jQuery("#cover").fadeOut();
        jQuery("#" + closeid).fadeOut();
    }

    function popupConfirm_ok() {

        jQuery("#cover").fadeOut();
        jQuery("#link-subscription-account").fadeOut();
        jQuery("#live-secret-key").prop("readonly", false);
        jQuery("#live-publishable-key").prop("readonly", false);
        jQuery("#edit-btn").hide();
        jQuery("#save-btn").show();
    }


    function SaveConfirm() {
        var e = false;
        jQuery("#live_secret_key .required").each(function () {
            var val = jQuery(this).val();
            var attr = jQuery(this).attr('id');
            if (jQuery.trim(val) == '')
            {
                e = true;
                jQuery(this).addClass('error-con');
            }
        });
        if (!e)
        {
            jQuery("#cover").fadeIn();
            jQuery("#link-subscription-account").fadeIn();
        }
    }



    function popupCoonectStrip_ok() {
        jQuery("#cover").fadeOut();
        jQuery("#connect-subscription-account").fadeOut();
        jQuery("#package_name").prop("readonly", false);
        jQuery("#price_per_month").prop("readonly", false);
        jQuery("#subscription-details").prop("readonly", false);

        jQuery("#connect-edit-btn").hide();
        jQuery("#connect-save-btn").show();
    }

    function SaveConnectSubscriptionConfirm() {

        var e = false;
        jQuery("#live_secret_key .required").each(function () {
            var val = jQuery(this).val();
            var attr = jQuery(this).attr('id');
            if (jQuery.trim(val) == '')
            {
                e = true;
                jQuery(this).addClass('error-con');
            }

        });

        if (!e)
        {
            jQuery("#cover").fadeIn();
            jQuery("#connect-subscription-account").fadeIn();

        }

    }

    jQuery(document).ready(function () {



    });

</script>








<!-- end footer -->