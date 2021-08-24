<?php

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$id = $content['id'];
$userguid = $content['userguid'];
require_once('stripe-php/init.php');
$stripe_secret_key = getSecretKey();

$stripe = new \Stripe\StripeClient($stripe_secret_key);

// cancel the subscription
$cancel_subscription = $stripe->subscriptions->cancel($id, []);
echo json_encode(['result' =>  $cancel_subscription]);



//update the custom field of the user
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$stripe_secret_key = getSecretKey();
$userToken = $_COOKIE["webapitoken"];
$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];

$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
$packageCustomFields = callAPI("GET", null, $url, false);

foreach ($packageCustomFields as $cf) {
    if ($cf['Name'] == 'subscription_status' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
           $subs_status = $cf['Code'];
    }


    if ($cf['Name'] == 'isCancelled' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $subs_is_cancelled = $cf['Code'];
    }

}


$data = [
    'CustomFields' => [
        [
            'Code' =>  $subs_status,
            'Values' => ["canceled"],
        ],

        [
            'Code' =>  $subs_is_cancelled,
            'Values' => ["true"],
        ]
    ],
];

$url = $baseUrl . '/api/v2/users/' . $userguid;
$result = callAPI("PUT", $admin_token['access_token'], $url, $data);
// echo json_encode(['data' =>  $result ]);





?>