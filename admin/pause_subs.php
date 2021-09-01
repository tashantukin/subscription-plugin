
<?php

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$id = $content['id'];
$action = $content['action'];
$userguid = $content['userguid'];
require_once('stripe-php/init.php');
$stripe_secret_key = getSecretKey();

$stripe = new \Stripe\StripeClient($stripe_secret_key);

if ($action == 'pause') {
    $pause_subscription = $stripe->subscriptions->update(
        $id,
        [
          'pause_collection' => [
            'behavior' => 'void',
          ],
        ]
      );
      echo json_encode(['result' =>  $pause_subscription]);
}else {
    $resume_subscription = $stripe->subscriptions->update(
        $id,
        [
          'pause_collection' => '',
        ]
      );
      echo json_encode(['result' =>  $resume_subscription]);
}


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


    if ($cf['Name'] == 'isPaused' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $subs_is_paused = $cf['Code'];
    }

}

if ($action == 'pause') {
    $status =  'paused';
    $is_paused = 'true';
}else {
    $status = 'active';
    $is_paused = 'false';
}

$data = [
    'CustomFields' => [
        [
            'Code' =>  $subs_status,
            'Values' => [ $status],
        ],

        [
            'Code' =>  $subs_is_paused,
            'Values' => [$is_paused],
        ]
    ],
];

$url = $baseUrl . '/api/v2/users/' . $userguid;
$result = callAPI("PUT", $admin_token['access_token'], $url, $data);

?>












































