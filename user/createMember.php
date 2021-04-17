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
$customer_name = $content['full_name'];
$customer_email = $content['email'];
//adddress
$city = $content['city'];
$country = $content['country'];
$line1 = $country['line1'];
$postal_code = $country['postal_code'];
$state = $content['state'];
$contact_number = $content['contact_number'];
// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);
$stripe_secret_key = getSecretKey();
require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey($stripe_secret_key);

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
        'name'=> $customer_name,
        'email' => $customer_email,
        'phone' => $contact_number,
        'address' => [
            
                'city' => $city,
                'country' => $country,
                'line1' => $line1,
                'postal_code' => $postal_code,
                'state' => $state

            ]
        //'payment_method' => $payment
    ]);

    $customer_id =  $customer->id;
    echo json_encode(['result' =>  $customer_id]);
 }
?>

