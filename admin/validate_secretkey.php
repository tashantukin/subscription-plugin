<?php
require_once('stripe-php/init.php');
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$secret_key = $content['secret_key'];

// \Stripe\Stripe::setApiKey($secret_key);
  
try {
    \Stripe\Stripe::setApiKey($secret_key);

    // create a test customer to see if the provided secret key is valid
    $response = \Stripe\Customer::create(["description" => "Test Customer - Validate Secret Key"]); 
    echo json_encode(['result' =>  'Valid']);
    return true;
}
// error will be thrown when provided secret key is not valid
catch (\Stripe\Error\InvalidRequest $e) {
    // Invalid parameters were supplied to Stripe's API
    $body = $e->getJsonBody();
    $err  = $body['error'];
    echo json_encode(['result' =>  $err]);
   

    return false;
}
catch (\Stripe\Error\Authentication $e) {
    // Authentication with Stripe's API failed
    // (maybe you changed API keys recently)
    $body = $e->getJsonBody();
    $err  = $body['error'];
    echo json_encode(['result' =>  $err]);
    
    return false;
}
catch (\Stripe\Error\Base $e) {
    // Display a very generic error to the user, and maybe send
    // yourself an email
    $body = $e->getJsonBody();
    $err  = $body['error'];
    echo json_encode(['result' =>  $err]);
    return false;
}
catch (Exception $e) {
    // Something else happened, completely unrelated to Stripe
    $body = $e->getJsonBody();
    $err  = $body['error'];
    echo json_encode(['result' =>  $err , 'body' => $body ]);
    return false;
}       

























// try {
//     $stripe = new \Stripe\StripeClient($secret_key);
//     echo json_encode(['result' =>   $stripe]);

//  $products =  $stripe->products->all(['limit' => 3]);
// //   Token is created using Stripe Checkout or Elements!
// //   Get the payment token ID submitted by the form:
//  $token = $_POST['stripeToken'];
// $tokens =  $stripe->tokens->create([
//     'card' => [
//       'number' => '4242424242424242',
//       'exp_month' => 4,
//       'exp_year' => 2022,
//       'cvc' => '314',
//     ],
//   ]);
// echo json_encode(['result' =>   $tokens]);
    
//  }catch(Error $e) {
//    echo json_encode(['result' =>  $e]);
//  }
 

