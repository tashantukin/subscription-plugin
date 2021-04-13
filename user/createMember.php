<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];
$customFieldPrefix = getCustomFieldPrefix();

$card_id = $content['card_id'];

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
    //create customer
    $customer = \Stripe\Customer::create([
        'name'=> 'Onoda Sakamichi',
        'description' => 'sample description',
        'email' => 'nmfnavarro@gmail.com'
        //'payment_method' => $payment
    ]);

    $customer_id =  $customer->id;
    echo json_encode(['result' =>  $customer_id]);
 }
?>

