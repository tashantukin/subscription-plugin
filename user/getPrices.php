<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];
$customFieldPrefix = getCustomFieldPrefix();

// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);

require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey('sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');

$plan_id='';
$plan_data= [];
foreach ($marketplaceInfo['CustomFields'] as $cf) {
    if ($cf['Name'] == 'plan_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $plan_id = $cf['Values'][0];
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
     
     echo json_encode(['name' =>  $package_name, 'price' => $price, 'description' => $details1]);
 }
?>

