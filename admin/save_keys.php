<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);

$api_key = $content['secretKey'];
$account_url = $content['publishableKey'];
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();

// Query to get marketplace id
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);

// Query to get package custom fields
$url = $baseUrl . '/api/developer-packages/custom-fields?packageId=' . getPackageID();
$packageCustomFields = callAPI("GET", $admin_token['access_token'], $url, false);

$ApiKey = '';
$AccountURL = '';

foreach ($packageCustomFields as $cf) {

    if ($cf['Name'] == 'stripe_api_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $ApiKey = $cf['Code'];
    }

    if ($cf['Name'] == 'stripe_pub_key' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
        $pubKey  = $cf['Code'];
    }
    
}
$data = [
    'CustomFields' => [
        [
            'Code' => $ApiKey,
            'Values' => [$api_key],
        ],
        [
            'Code' =>  $pubKey,
            'Values' => [$account_url],
        ],

    ],
];

echo json_encode(['data' =>  $data]);


$url = $baseUrl . '/api/v2/marketplaces/';
$result = callAPI("POST", $admin_token['access_token'], $url, $data);
// echo json_encode(['result' =>  $result]);


// save package details in custom tables

$package_details = array('PackageName' => $campaign_name, 'Price' => $price, 'Details' => $details, 'Interval' => $interval, 'DateCreated' => $dates);
$url =  $baseUrl . '/api/v2/plugins/'. getPackageID() .'/custom-tables/Package/rows';
$result =  callAPI("POST",$admin_token['access_token'], $url, $package_details);

?>

