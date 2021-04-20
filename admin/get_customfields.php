<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$userId = $content['userguid'];

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
// $userToken = $_COOKIE["webapitoken"];
// $url = $baseUrl . '/api/v2/users/'; 
// $result = callAPI("GET", $userToken, $url, false);
// $userId = $result['ID'];
$start_date = null;
$end_date = null;

$url = $baseUrl . '/api/v2/users/' . $userId; 
$buyer = callAPI("GET", $admin_token['access_token'], $url, false);  

foreach($buyer['CustomFields'] as $cf) 
{ 
    
if ($cf['Name'] == 'subscription_start_date' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $start_date = $cf['Values'][0];
     
 }
  if ($cf['Name'] == 'subscription_end_date' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $end_date = $cf['Values'][0];
    
}

}
echo json_encode(['start' =>  $start_date, 'end' => $end_date ]);
?>
