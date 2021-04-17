<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];
$customFieldPrefix = getCustomFieldPrefix();

$customer_id = $content['customer_id'];
$payment_id = $content['payment_id'];
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
      $stripe = new \Stripe\StripeClient($stripe_secret_key);
        try {
          $payment_method = $stripe->paymentMethods->retrieve(
              $payment_id
          );
          $payment_method->attach([
            'customer' => $customer_id,
          ]);
        // echo json_encode(['result' =>  $attach]);

        } catch (Exception $e) {
          //return $response->withJson($e->jsonBody);
          echo json_encode(['result' => $e]);
        }

  // Set the default payment method on the customer
  $stripe->customers->update($customer_id, [
    'invoice_settings' => [
      'default_payment_method' => $payment_id
    ]
  ]);

 // Create the subscription
 $subscription = $stripe->subscriptions->create([
    'customer' => $customer_id,
    'items' => [
      [
        'price' => $plan_id ,
      ],
    ],
    'expand' => ['latest_invoice.payment_intent'],
  ]);

 // return $response->withJson($subscription);

  echo json_encode(['result' => $subscription]);


?>

