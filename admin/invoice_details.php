<?php
include 'callAPI.php';
include 'admin_token.php';

$invoice_id = $_GET['invoiceId'];
$subs_id = $_GET['subsId'];
$user_guid = $_GET['userguid'];
$customer_id = $_GET['customerId'];
$status = $_GET['status'];

// $contentBodyJson = file_get_contents('php://input');
// $content = json_decode($contentBodyJson, true);
// $timezoneOffset =  $_POST['storageValue'];
// $timezone_name = timezone_name_from_abbr("", $timezoneOffset*60, false);
// date_default_timezone_set($timezone_name);

// $baseUrl = getMarketplaceBaseUrl();
// $admin_token = getAdminToken();
// $userToken = $_COOKIE["webapitoken"];

// $customFieldPrefix = getCustomFieldPrefix();
$stripe_secret_key = getSecretKey();
// error_log($stripe_secret_key);
require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey($stripe_secret_key);

// $url = $baseUrl . '/api/v2/users/'. $user_guid; 
// $result = callAPI("GET", $userToken, $url, false);

// $user_email = $result['Email'];
// $user_display_name = $result['DisplayName'];
// $user_avatar = $result['Media'][0]['MediaUrl'];


// //subscription details

// $subscription = \Stripe\Subscription::retrieve($subs_id);
// error_log(json_encode($subscription));
// error_log(json_encode($subscription->items->data[0]->price->nickname));

// $end_date = $subscription->current_period_end;
// error_log(date('d/m/Y H:i', $end_date));
// $start_date = $subscription->current_period_start;
// error_log(date('d/m/Y H:i', $start_date));
// $subs_name = $subscription->items->data[0]->price->nickname;
// error_log($subs_name);
// $subs_amount = $subscription->items->data[0]->price->unit_amount /100;
// error_log($subs_amount);

// $joined_date = $subscription->created;

$invoice_details = \Stripe\Invoice::retrieve($invoice_id);
//invoice id -> number
// timestamp  -> created
//payment id -> charge



//pacckage -> lines[data][0][plan][nicckname]



// charge frequency -> monthly

//billing period start   "period_end": 1629018706,
                        //  "period_start": 1629018706,
//billng period end 
//status ->  status

//payment id ->

error_log(json_encode($invoice_details));
$amount = $invoice_details['amount_paid'] / 100;
$amount =  number_format((float)$amount,2);
$currency = strtoupper($invoice_details['currency']);
$sub_total =  $invoice_details['lines']['data'][0]['plan']['amount'] / 100;
$sub_total = number_format((float)$sub_total,2);


$discount_amount =  $invoice_details['discount'] != null ? $invoice_details['total_discount_amounts'][0]['amount'] / 100 : '0.00';
$discount_amount =  number_format((float)$discount_amount,2);
$discount_code = $invoice_details['discount'] != null ? strtoupper($invoice_details['discount']['coupon']['id']): '';

?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/subscription.css">
<script src="https://js.stripe.com/v3/"></script>
<div class="page-content" id="subscription-content">
                <div class="gutter-wrapper">
                    <div class="page-topnav"> <a class="btn-back" href='customer_details.php?<?php echo "subsId=" . $subs_id . "&userguid=" . $user_guid . "&customerId=". $customer_id . "&status= ". ucfirst($status) ?>'><i class="icon icon-arrowleft"></i> Back</a> </div>
                    <div class="panel-box">
                        <div class="sub-detail-title">Invoice Details</div>
                        <div class="sub-detail-col-wrapper">
                            <div>
                                <div class="sub-detail-top-left col-md-4">Invoice ID: <span><?php echo strtoupper($invoice_details['number']); ?> </span></div>
                                <div class="sub-detail-top-right col-md-4">Timestamp: <span><?php echo date('d/m/Y H:i', $invoice_details['created']); ?></span></div>
                                <div class="sub-detail-top-payment-id col-md-4">
                                    Payment ID: <span><?php echo strtoupper($invoice_details['charge']); ?></span>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="sub-detail-row">
                                <div>
                                    <div class="col-md-4">
                                        <div class="subplan-label">Package</div>
                                        <div class="subplan-value"><?php echo $invoice_details['lines']['data'][0]['plan']['nickname'] ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subplan-label">Billing Period Start</div>
                                        <div class="subplan-value"><?php echo date('d/m/Y', $invoice_details['period_start'])  ?></div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subplan-label">Payment ID</div>
                                        <div class="subplan-value">Visa</div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="subplan-info-box">
                                    <div class="col-md-4">
                                        <div class="subplan-label">Charge Frequency</div>
                                        <div class="subplan-value">Monthly</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subplan-label">Billing Period End</div>
                                        <div class="subplan-value"><?php echo date('d/m/Y', $invoice_details['period_end']) ?>)</div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="subplan-label">Status</div>
                                        <div class="subplan-value"><?php echo strtoupper($invoice_details['status']) ?></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="sub-detail-row">
                                <div>
                                    <div class="pull-left subplan-value">Subtotal</div>
                                    <div class="pull-right subplan-value"> <?php echo  $currency .' ' . '$' . $sub_total  ?></div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <div class="sub-detail-row">
                                <div class="subplan-info-box">
                                    <div class="pull-left subplan-value"><span class="subplan-label">Discount Code: </span><?php echo $discount_code ?></div>
                                    <div class="pull-right subplan-value">-USD $<?php echo $discount_amount ?></div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="subplan-info-box">
                                    <div class="pull-left subplan-label">TOTAL</div>
                                    <div class="pull-right subplan-total-price"> <?php echo  $currency .' ' . '$' . $amount  ?> </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>







