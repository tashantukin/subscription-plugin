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
$result = callAPI("GET", $admin_token['access_token'], $url, false);

$user_email = $result['Email'];
$user_display_name = $result['DisplayName'];
$user_avatar = $result['Media'][0]['MediaUrl'];



//get the status customf field values of iscancelled, ispaused etc

foreach($result['CustomFields'] as $cf) 
{ 
    
if ($cf['Name'] == 'isCancelled' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $is_cancelled = $cf['Values'][0];
     
 }
 if ($cf['Name'] == 'isPaused' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $is_paused = $cf['Values'][0];
     
 }

}


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
<script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal/js/pagination.min.js"></script>
<link href="https://bootstrap.arcadier.com/adminportal/css/pagination.css" rel="stylesheet" type="text/css">
<script src="https://js.stripe.com/v3/"></script>
<div class="page-content">

                <div class="navigation-bar page-topnav">

                    <!-- <a href="index.php" class="mybtn btn-back"><img src="images/back.svg" alt="Back">Back</a> -->
                   <a class="btn-back" href="index.php"><i class="icon icon-arrowleft"></i> Back</a> 

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

                                            <a href="javascript:void(0)" onclick="<?php echo $is_cancelled == "true" ? "" : "subcriptionControl()" ?>" id="subscriptions-control" class="cancel-buttons <?php echo $is_paused == "true" ? "resume" : "pause" ?>"><?php echo $is_paused == 'true' ? 'Resume Subscription' : 'Pause Subscription' ?></a>
        
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
                                        // $sub_total
                                        $amount = $invoice['lines']['data'][0]['plan']['amount'] / 100;
                                        $amount = number_format((float)$amount,2);
                                        echo "<tr class='border-hover'>";
                                            echo  "<td data-th='Timestamp' class='clickable-row' style='cursor: pointer; cursor: hand;' data-href='invoice_details.php?invoiceId=". $invoice['id'] .  "&userguid=" . $user_guid  . "&customerId=". $customer_id . "&status= ". ucfirst($status)  . "&subsId=". $subs_id . "'>"  . date('d/m/Y H:i', $invoice['created']) .  "</td>";
                                            echo   "<td data-th='Invoice ID'>" . $invoice['number'] . "</td>";
                                            echo   "<td data-th='Package'>" . $invoice['lines']['data'][0]['plan']['nickname'] . "</td>";
                                            echo  "<td data-th='Amount'>" . $currency .' ' .'$'  . $amount  . "</td>";
                                            echo   "<td data-th='Collection Method'>" . $collection_method . "</td>";
                                            echo  "<td data-th='Status'>" . ucfirst($invoice['status']) ."</td>";
                                        echo "</tr>";                                       
                                     }
                                ?>
                                  
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <nav class="text-center" id="pagination-userslist" aria-label="Page navigation">
                            <div class="paginationjs">
                                <div class="paginationjs-pages">
                                    <ul>
                                        <li class="paginationjs-prev disabled"><a>«</a></li>
                                        <li class="paginationjs-page J-paginationjs-page active" data-num="1"><a>1</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="2"><a href="">2</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="3"><a href="">3</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="4"><a href="">4</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="5"><a href="">5</a></li>
                                        <li class="paginationjs-ellipsis disabled"><a>...</a></li>
                                        <li class="paginationjs-page paginationjs-last J-paginationjs-page" data-num="11"><a href="">11</a></li>
                                        <li class="paginationjs-next J-paginationjs-next" data-num="2" title="Next page"><a href="">»</a></li>
                                    </ul>
                                </div>
                            </div>
                        </nav>
                </div>

                <div id="subcriptionControl" class="popup modal-change-pwd">
                    <div class="popup-wrapper">
                            <div class="modal-header">
                                <div class="text-center">
                                    <h5></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:void(0)" class="close-popup" onclick="popup_close(this)" data-dismiss="modal"><i class="icon icon-close"></i></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="popup-content text-center">
                                <div class="form-group">
                                <p>Are you sure you want to <?php echo $is_paused == "true" ? "resume" : "pause" ?> the subscription?</p>
                                </div>
                            </div>
                            <div class="popup-footer text-center">
                                <button type="button" onclick="popup_close(this)" class="mybtn btn-grey">Cancel</button>
                                <button type="button"  id="pause-subs" onclick="subscriptionPlay(this)" class="mybtn btn-blue" subs-id="<?php echo $subs_id; ?>" user-guid ="<?php echo $user_guid; ?>" status="<?php echo $is_paused == "true" ? "resume" : "pause" ?>">Okay</button>
                            </div>
                    </div>
                </div>

                <div id="cancelSubscription" class="popup modal-change-pwd">
                    <div class="popup-wrapper">
                            <div class="modal-header">
                                <div class="text-center">
                                    <h5></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:void(0)" class="close-popup" onclick="popup_close(this)" data-dismiss="modal"><i class="icon icon-close"></i></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="popup-content text-center">
                                <div class="form-group">
                                <p>Are you sure you want to cancel the subscription?</p>
                                </div>
                            </div>
                            <div class="popup-footer text-center">
                                <button type="button" id="cancel-subs" onclick="popup_close(this)" class="mybtn btn-blue" subs-id="<?php echo $subs_id;  ?>" user-guid ="<?php echo $user_guid; ?>">Yes</button>
                                <button type="button" onclick="popup_close(this)" class="mybtn btn-grey">No</button>
                                
                            </div>
                    </div>
                </div>
                <!-- End custom user field-->

                <div class="box-change-password">

                    <a href="javascript:void(0)" onclick="<?php echo $is_cancelled == "true" ? "" : "cancelSubscription()" ?>"  class="mybtn btn-default"><?php echo $is_cancelled == "true" ? 'Cancelled' : 'Cancel Subscription' ?></a>

                </div>

            </div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>

<script>


jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });
});


function subcriptionControl()
    {
        var $modal = $("#subcriptionControl");
        $("#cover").show();
        $modal.fadeIn();
    }

    function cancelSubscription()
    {
        var $modal = $("#cancelSubscription");
        $("#cover").show();
        $modal.fadeIn();
    }


    function subscriptionPlay(ele) {



        var that = jQuery(ele);



        that.parents('.popup').fadeOut();



        jQuery("#cover").fadeOut();


        if($("#subscriptions-control").hasClass("resume")){
            $("#subscriptions-control").text("Pause Subscription");
            $("#subscriptions-control").removeClass("resume");
           // $("#subcriptionControl").find(".popup-content .form-group").text("Are you sure you want to pause the subscription?");
        }else {
            $("#subscriptions-control").text("Resume Subscription")
            $("#subscriptions-control").addClass("resume");
           // $("#subcriptionControl").find(".popup-content .form-group").text("Are you sure you want to resume the subscription?");
        }
    }

    function popup_close(ele) {

        var that = $(ele);

        that.parents('.popup').fadeOut();

        $("#cover").fadeOut();

    }


</script>

<!-- end footer -->





