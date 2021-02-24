<?php

include 'callAPI.php';
include 'admin_token.php';
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);
//3.get the value of long page url
$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
$urlexp =   explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); 
$host  = $urlexp[0];
error_log('host' . $host);
$host1 = $urlexp[1];
error_log('host1' . $host1);
$host2 = $urlexp[2];
error_log('host2' . $host2);
$host3 = $urlexp[3];
error_log('host3' . $host3);
$host4 = $urlexp[4];
error_log('host4' . $host4);
$host5 = $urlexp[5]; 
error_log('host5' . $host5);

$shortURL = '/subscribe';
$pathURL =  '/' .  'user' .'/' . $host2 . '/' . $host3 . '/'. 'subscribe.php';
// POST THE DATA
$data = [
    'Key' => $shortURL,
    'Value' => $pathURL,

];
$url = $baseUrl . '/api/v2/rewrite-rules';
$result = callAPI("POST", $admin_token['access_token'], $url, $data);

$styles = [
    'Key' => '/subscribe/css/styles.css',
    'Value' => '/' .  'user' .'/' . $host2 . '/' . $host3 . '/'. 'css' . '/' . 'styles.css',

];

$url = $baseUrl . '/api/v2/rewrite-rules';
$result = callAPI("POST", $admin_token['access_token'], $url, $styles);

?>