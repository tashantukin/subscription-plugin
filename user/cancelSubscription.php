<?php

include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$id = $content['id'];
require_once('stripe-php/init.php');
// \Stripe\Stripe::setApiKey('sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');

$stripe = new \Stripe\StripeClient(
    'sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');

// cancel the subscription
$cancel_subscription = $stripe->subscriptions->cancel($id, []);
echo json_encode(['result' =>  $cancel_subscription]);
?>