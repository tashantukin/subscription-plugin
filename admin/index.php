<?php
include 'callAPI.php';
include 'admin_token.php';
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

$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];
$url = $baseUrl . '/api/v2/admins/' . $userId .'/users/?role=merchant&pageSize=1000'; 
$result = callAPI("GET", $userToken, $url, false);
// var_dump($result);


?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/subscription.css">
<script src="https://js.stripe.com/v3/"></script>
<div class="page-content">
                <div class="gutter-wrapper">

                <div class="page-topnav" style="height: 5px;">
   
                <div class="float">
                        <a class="btn-info-plug-in" href="https://support.arcadier.com/hc/en-us/articles/900006649666-Subscriptions-Plugin" target="_blank" >How to use this Plug-In?</a>
                    </div>
              </div>
                    <div class="panel-box border-none">
                        
                        <div class="page-content-top">
                            <div class="row">
                                <div class="col-sm-8">
                                    <h4>Setup a membership fee for your merchant</h4>
                                    <h5>The Membership &amp; Subscriptions plug-in allows marketplace administrators to charge merchants a recurring membership subscription fee when they join your marketplace.</h5>
                                </div>
                                <div class="col-sm-4">
                                    <div class="auto btn-scheduler btn-area pull-right text-right"> <a href="settings.php" class="blue-btn">Subscription Settings</a>
                                    <!-- <button type="button" class="btn cmn-btn-blue">Subscription Settings</button> -->
                                </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                    
                    
                    <div class="filter-bar page-topnav" style="margin-bottom: 0;box-shadow: none;">

                    <form action="" class="form-inline form-filter">

                        <!-- filter -->

                            <div class="sassy-filter lg-filter">

                                

                                    <div class="sassy-flex">

                                        <div class="sassy-l grey_filter">

                                            <div>

                                                <div class="group-search">

                                                    <div class="group-search-flex">

                                                        <label for="" class="sassy-label">Filter by:</label> 

                                                        <span class="sassy-search">
                                                           <input class="form-control" name="keywords" id="keywords" placeholder="Search">
                                                        </span>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>



                                    </div>

                                

                            </div></form>

                            <!-- filter -->

                    

                </div>
                    

                    <div class="panel-box">
                    	<div class="merchant-commission-table scheduler-tbl">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Merchant Name</th>
                                            <th>Merchant Email</th>
                                            <th>Status</th>
                                            <th>Date Joined</th>
                                            <th>Package Plan</th>
                                            <th>Billing Cycle</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        

                                        <?php

                                            foreach($result['Records'] as $user ) {

                                                $user_name =  $user['DisplayName'];
                                                $user_email = $user['Email'];
                                                $user_id =$user['ID'];
                                                

                                                if ($user['CustomFields'] != null) {


                                                    foreach($user['CustomFields'] as $cf) {

                                                        if ($cf['Name'] == 'subscription_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                                                            $subs_id = $cf['Values'][0];
                                                
                                                        error_log($subs_id);    
                                                        
                                                    if ($subs_id) {
                                                            $subscription = \Stripe\Subscription::retrieve($subs_id);
                                                            error_log(json_encode($subscription));
                                                            error_log(json_encode($subscription->items->data[0]->price->nickname));
                                                        
                                                            $end_date = $subscription->current_period_end;
                                                            // $date = date('d/m/Y H:i', $serverdate);
                                                            error_log(date('d/m/Y H:i', $end_date));
                                                            $start_date = $subscription->current_period_start;
                                                            error_log(date('d/m/Y H:i', $start_date));
                                                            $subs_name = $subscription->items->data[0]->price->nickname;
                                                            error_log($subs_name);
                                                            $subs_amount = $subscription->items->data[0]->price->unit_amount /100;
                                                            error_log($subs_amount);

                                                            $status = $subscription->status;
                                                            $joined_date = $subscription->created;
                                                            $customer_id = $subscription->customer;
                                                            error_log($status);

                                                            echo "<tr class='border-hover'>";

                                                            echo  "<td data-th='Merchant Name' class='clickable-row' style='cursor: pointer; cursor: hand;' data-href='customer_details.php?subsId=". $subs_id . "&userguid=" . $user_id . "&customerId=". $customer_id . "&status= ". ucfirst($status)  ." '>" .  $user_name. "</td>";
                                                            echo  "<td data-th='Merchant Email'>" .  $user_email. "</td>";
                                                            echo  "<td data-th='Status'>" .  ucfirst($status) . "</td>";
                                                            echo  "<td data-th='Date Joined'>" .  date('d/m/Y', $joined_date). "</td>";
                                                            echo  "<td data-th='Package Plan'>" .  $subs_name . "</td>";
                                                            echo  "<td data-th='Billing Cycle'>" .  date('d/m/Y',$start_date)  . '-' . date('d/m/Y',$end_date) . "</td>";

                                                            echo " </tr>";
                                                        }
                                                            
                                                    }
                                                }
                                            }
                        

                                         }         


                                     ?>  



                                            <!-- <td data-th="Merchant Name">Seller 1</td>
                                            <td data-th="Merchant Email">mail@mail.com</td>
                                            <td data-th="Status">Active</td>
                                            <td data-th="Date Joined">25/07/2021</td>
                                            <td data-th="Package Plan">Premium</td>
                                            <td data-th="Billing Cycle">25/07/2021 - 25/08/2021</td> -->
                                                                            
                                                                       
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>

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
</script>

<!-- end footer -->


