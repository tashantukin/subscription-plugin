<?php

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$package_name = $content['package_name'];
$price = $content['price'];
$details = $content['details'];
$interval = 'Monthly';
$timezone_offset = $content['timezone'];
require_once('stripe-php/init.php');

\Stripe\Stripe::setApiKey('sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');

//create the Product name, default will be 'Arcadier subs...'
  $stripe = \Stripe\Product::create([
     'name'=> 'Arcadier Subscription'
  ]);

  $productId = $stripe->id;

  //create the Pricing for the created package
  $stripePlan = \Stripe\Price::create(
    [
      'unit_amount' => $price,
      'currency' => 'usd',
      'nickname' => $package_name,
      'recurring' => ['interval' => 'month'],
      'product' => $productId,
      'metadata' => array('desription' => $details)
    ]);

$planId = $stripePlan->id;

$tz = date_default_timezone_get();
$timezone_name = timezone_name_from_abbr("", $timezone_offset*60, false);
date_default_timezone_set($timezone_name);

$date = date("d/m/Y H:i"); 
$timestamp = $timezone_offset*60;

$date1 = strtotime($timestamp);
$now = new DateTime($timezone_name);
$now->format('Y-m-d H:i:s');    // MySQL datetime format
$dates = $now->getTimestamp(); 

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();

// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);

// Query to get package custom fields
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
$packageCustomFields = callAPI("GET", $admin_token['access_token'], $url, false);

$plan_id = '';

foreach ($packageCustomFields as $cf) {

    if ($cf['Name'] == 'plan_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
         $plan_id = $cf['Code'];
    }
}
$data = [
    'CustomFields' => [
        [
            'Code' => $plan_id,
            'Values' => [$planId],
        ]

    ],
];

echo json_encode(['data' =>  $data]);

$url = $baseUrl . '/api/v2/marketplaces/';
$result = callAPI("POST", $admin_token['access_token'], $url, $data);



// save package details in custom tables

$package_details = array('PackageName' => $package_name, 'Price' => $price, 'Details' => $details, 'Interval' => $interval, 'PlanID' => $planId, 'ProductID' => $productId );

$url =  $baseUrl . '/api/v2/plugins/'. getPackageID() .'/custom-tables/Package/rows';
$result =  callAPI("POST",$admin_token['access_token'], $url, $package_details);


  // Token is created using Stripe Checkout or Elements!
  // Get the payment token ID submitted by the form:



// echo  $stripe['Stripe\Product JSON']['id'];

// $product_id =  $stripe->id;

// $stripe->products->update(
//     'prod_JFdQvDasq76lDk ',
//     ['metadata' => ['order_id' => '6735']]
//   );

//   $stripe = \Stripe\Product::update(
//     'prod_JFdQvDasq76lDk',
//     ['name'=> 'Gold Fish edited']);

// echo $stripe->name;

//create a plan / pricing




//echo $stripe;

?>

