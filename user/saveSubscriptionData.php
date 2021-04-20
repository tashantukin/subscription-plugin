<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$status = $content['status'];
$id = $content['id'];
$start_date = $content['start_date'];
$end_date = $content['end_date'];

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
    if ($cf['Name'] == 'subscription_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $subs_id = $cf['Code'];
    }
    if ($cf['Name'] == 'subscription_start_date' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $subs_start_date = $cf['Code'];
    }
    if ($cf['Name'] == 'subscription_end_date' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $subs_end_date = $cf['Code'];
    }
}

$data = [
    'CustomFields' => [
        [
            'Code' =>  $subs_status,
            'Values' => [$status],
        ],

        [
            'Code' =>  $subs_id,
            'Values' => [$id],
        ],

        [
            'Code' =>  $subs_start_date,
            'Values' => [$start_date],
        ],

        [
            'Code' =>  $subs_end_date,
            'Values' => [$end_date],
        ],
    ],
];

$url = $baseUrl . '/api/v2/users/' . $userId;
$result = callAPI("PUT", $admin_token['access_token'], $url, $data);
echo json_encode(['data' =>  $result ]);
?>