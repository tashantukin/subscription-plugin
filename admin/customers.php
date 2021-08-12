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

foreach($result['Records'] as $user ) {

    $user_name =  $user['DisplayName'];
    $user_email = $user['Email'];
    

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
                    error_log($status);


                    //start date = created

                }
                
            }
        }
    }
    

}