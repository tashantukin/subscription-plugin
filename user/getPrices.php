<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];
$customFieldPrefix = getCustomFieldPrefix();
$stripe_secret_key = getSecretKey();
error_log($stripe_secret_key);
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

//get the subscription status here, if nothing found, means new user, else, check the status value
$url = $baseUrl . '/api/v2/users/' . $userId; 
$user = callAPI("GET", $admin_token['access_token'], $url, false);  

error_log('user ' .  json_encode($user));
 
$subs_id ='';
$subs_status='new';
//$key = array_search('subscription_status', array_column($user['CustomFields'][0], 'Name'));

//error_log('key ' . $key);
//if (in_array('subscription_id',$user['CustomFields'][0]))  {
foreach($user['CustomFields'] as $cf) {
    // $key = array_search('subscription_status', array_column($cf, 'Name'));
        //error_log('exists');
            if ($cf['Name'] == 'subscription_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                $subs_id = $cf['Values'][0];
                
            }
            if ($cf['Name'] == 'subscription_status' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                $subs_status = $cf['Values'][0];
                
            }
        }

// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);

require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey($stripe_secret_key);

$plan_id='';
$plan_data= [];
foreach ($marketplaceInfo['CustomFields'] as $cf) {
    if ($cf['Name'] == 'plan_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $plan_id = $cf['Values'][0];
    }
}

if ($subs_id) {
    $subscription = \Stripe\Subscription::retrieve($subs_id);
    error_log(json_encode($subscription));
    error_log(json_encode($subscription->items->data[0]->price->nickname));

    $end_date = $subscription->current_period_end;
    $start_date = $subscription->current_period_start;
    $subs_name = $subscription->items->data[0]->price->nickname;
    $subs_amount = $subscription->items->data[0]->price->unit_amount /100;
}

if (!empty($plan_id)) {

    // echo 'plan id ' . $plan_id;
     $stripe = \Stripe\Price::retrieve($plan_id);
 //    echo $stripe;
     $package_name = $stripe->nickname;
     $price = $stripe->unit_amount / 100;
     $metadata= $stripe->metadata;
     $details = json_encode($metadata);
     $details1 = implode(',', json_decode($details, true));
     
    echo json_encode(['name' =>  $package_name, 'price' => $price, 'description' => $details1, 'id'=> $plan_id, 'status' => $subs_status, 'sub_id' => $subs_id, 'start_date'=> $start_date, 'end_date' => $end_date ,'current_plan' =>  $subs_name, 'current_amount' => $subs_amount ] );
 }
?>

