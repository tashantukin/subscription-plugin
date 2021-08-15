<?php
include 'callAPI.php';
include 'admin_token.php';


$subs_id = $_GET['subsId'];
$user_guid = $_GET['userguid'];
$customer_id = $_GET['customerId'];
$status = $_GET['status'];

$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$timezoneOffset =  $_POST['storageValue'];
$timezone_name = timezone_name_from_abbr("", $timezoneOffset*60, false);
date_default_timezone_set($timezone_name);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];

$customFieldPrefix = getCustomFieldPrefix();
$stripe_secret_key = getSecretKey();
error_log($stripe_secret_key);
require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey($stripe_secret_key);

$url = $baseUrl . '/api/v2/users/'. $user_guid; 
$result = callAPI("GET", $userToken, $url, false);

$user_email = $result['Email'];
$user_display_name = $result['DisplayName'];
$user_avatar = $result['Media'][0]['MediaUrl'];


//subscription details

$subscription = \Stripe\Subscription::retrieve($subs_id);
error_log(json_encode($subscription));
error_log(json_encode($subscription->items->data[0]->price->nickname));

$end_date = $subscription->current_period_end;
error_log(date('d/m/Y H:i', $end_date));
$start_date = $subscription->current_period_start;
error_log(date('d/m/Y H:i', $start_date));
$subs_name = $subscription->items->data[0]->price->nickname;
error_log($subs_name);
$subs_amount = $subscription->items->data[0]->price->unit_amount /100;
error_log($subs_amount);

$joined_date = $subscription->created;

// $retrieve =  $stripe->invoices->all(['customer' => 'cus_JIvgAmFgcxpxIm']); 

$invoices  = \Stripe\Invoice::all(['customer' => $customer_id]);

// var_dump($invoices);
// echo json_encode($invoices);

$invoice_total = count((array)$invoices->data);

?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/subscription.css">
<script src="https://js.stripe.com/v3/"></script>
<div class="page-content">

                <div class="navigation-bar page-topnav">

                    <a href="index.php" class="mybtn btn-back"><img src="images/back.svg" alt="Back">Back</a>

                </div>

                <div class="profile-bar page-topnav">

                    <div class="col-sm-6 col-md-6">
                        <h5>DISPLAY IMAGE</h5>

                        <div class="profile-avtar">
    
                            <a href="#">
    
                                <img src="<?php echo $user_avatar  ?>" alt="Merchant Avtar">
    
                            </a>
    
                            <span class="profile-meta">
    
                                <span class="label-plugin-name"><?php echo $user_display_name ?></span>
    
                                <span class="meta-label"><?php echo $user_email ?></span>
    
                                <a href="#" class="view-user-details">View user details</a>
    
                                <!-- <span class="profile-title">WaterSoda</span> -->
    
                            </span>
    
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-6">
                  

                            <ul class="w-list-items">

                                <li>

                                    <label>Subscription ID</label>

                                    <p><?php echo strtoupper($subs_id) ?></p>

                                </li>

                                <li>

                                    <label>Status</label>

                                    <p><?php echo $status ?></p>

                                </li>

                                

                            </ul>

                    </div>

                    

                </div>

                <div class="row wrap-panel">
                    <div class="new-sub-container">
                        <div class="plugin-large-title">Subscription Details</div>

                        <div class="sub-container-sub">
                                <div class="col-sm-6 col-md-6">
        
                                
        
                                    <ul class="w-list-items">
        
                                        <li>
        
                                            <label>Start Date</label>
        
                                            <p><?php echo date('d/m/Y', $start_date)  ?></p>

                                            

        
                                        </li>
        
                                        <li>
        
                                            <label>Last Charged Date</label>
        
                                            <p><?php echo date('d/m/Y', $start_date)  ?></p>

                                            <!-- <a href="#" class="cancel-buttons">Pause Subscription</a> -->
        
                                        </li>
        
                                        <li>
        
                                            <label>Next Charge Date</label>
        
                                            <p><?php echo date('d/m/Y', $end_date)  ?></p>

                                            <a href="#" class="cancel-buttons">Pause Subscription</a>
        
                                        </li>
        
                                        
        
                                    </ul>
        
                            
        
                            </div>
        
                            <div class="col-sm-6 col-md-6">
        
                                
        
                                    <ul class="w-list-items">
        
                                        <li>
        
                                            <label>Package</label>
        
                                            <p><?php echo $subs_name ?> </p>
        
                                        </li>
        
                                        <li>
        
                                            <label>Charge Frequency</label>
        
                                            <p>Monthly</p>
        
                                        </li>
        
                                        
                                    </ul>
        
                            
        
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        
    
                        
                    </div>
                    

                </div>
                <!-- custom user field-->
                <div class="wrap-panel">
                    <div class="custom-user-field-sec">
                        <div class="merchant-commission-table scheduler-tbl">
                            <table class="table">
                                <thead>
                               
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Invoice ID</th>
                                        <th>Package</th>
                                        <th>Amount</th>
                                        <th>Collection Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                     foreach($invoices['data'] as $invoice) {
                                        $collection_method = $invoice['billing_reason'] == 'subscription_create' ? 'First Payment' : 'Charge automatically';
                                        $currency = strtoupper($invoice['lines']['data'][0]['plan']['currency']);
                                        $amount = $invoice['lines']['data'][0]['plan']['amount'] / 100;
                                        echo "<tr class='border-hover'>";
                                            echo  "<td data-th='Timestamp'>" . date('d/m/Y H:i', $invoice['created']) .  "</td>";
                                            echo   "<td data-th='Invoice ID'>" . $invoice['number'] . "</td>";
                                            echo   "<td data-th='Package'>" . $invoice['lines']['data'][0]['plan']['nickname'] . "</td>";
                                            echo  "<td data-th='Amount'>" . $currency .' ' . $amount  . "</td>";
                                            echo   "<td data-th='Collection Method'>" . $collection_method . "</td>";
                                            echo  "<td data-th='Status'>" . ucfirst($invoice['status']) ."</td>";
                                        echo "</tr>";                                       
                                     }
                                ?>
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- End custom user field-->

                <div class="box-change-password">

                    <a href="javascript:void(0)" class="mybtn btn-default">Cancel Subscription</a>

                </div>

            </div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>







