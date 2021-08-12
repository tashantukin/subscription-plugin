
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/settings.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/subscription.css">
</head>

<div class="page-content" id="transaction-content">
                <div class="gutter-wrapper">
                    <div class="panel-box">
                        <div class="page-content-top">
                            <div><i class="icon icon-subscribe-invoice icon-3x"></i></div>
                            <div>
                                <p>Keep track of your monthly subscription invoices.&nbsp;</p>
                                <p>You will also receive an email after each payment is made.</p>
                            </div>
                        </div>
                    </div>
                    <div class="listing-area listing-table subscription-invoce-tbl">
                        <table class="table" id="no-more-tables">
                            <thead>
                                <tr>
                                    <td>Timestamp <span class="sorting-indicator asc"><i class="icon icon-asc"></i></span> <span class="sorting-indicator desc"><i class="icon icon-desc"></i></span></td>
                                    <td data-sorter="false">Invoice ID</td>
                                    <td data-sorter="false">Plan</td>
                                    <td data-sorter="false">Cycle</td>
                                    <td data-sorter="false">Amount</td>
                                    <td data-sorter="false">Status</td>
                                    <td data-sorter="false"><span class="hide">Action</span></td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td data-title="Timestamp"><a href="subscription-detail-require-action.html">DD/MM/YYYY<br>
                                            00:00</a></td>
                                    <td data-title="Invoice ID"><a href="subscription-detail-require-action.html">01892</a></td>
                                    <td data-title="Plan"><a href="subscription-detail-require-action.html">Scale</a></td>
                                    <td data-title="Cyle"><a href="subscription-detail-require-action.html">Monthly</a></td>
                                    <td data-title="Amount"><a href="subscription-detail-require-action.html">$399.00</a></td>
                                    <td data-title="Status"><a href="subscription-detail-require-action.html"><span class="sub-inv-status-require-action">Requires Action</span></a></td>
                                    <td data-title="Action"><a href="subscription-detail-require-action.html"><i class="icon icon-arrowright"></i></a></td>
                                </tr>
                               
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>



<?php
require_once('stripe-php/init.php');


\Stripe\Stripe::setApiKey('sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');
  
  // Token is created using Stripe Checkout or Elements!
  // Get the payment token ID submitted by the form:
  //$token = $_POST['stripeToken'];

  $stripe = new \Stripe\StripeClient('sk_test_51INpZ6LpiOi48zknrweuYlbv7lThIzaBNcn4dgyXSXZHNeAolscJsVo9YdHYmbH4EPW1ty4ByRicFi5KvAPMjC5V00CatSNcjd');

  $retrieve =  $stripe->invoices->all(['customer' => 'cus_Jr1AuCHzvy0oV3']);  //\Stripe\Invoice::all(['customer' => 'cus_Jr1AuCHzvy0oV3']);

//  ($subs_id);
  echo json_encode(['result' => $retrieve]);
 
//   $charge = \Stripe\Invoices::create([
//     'amount' => 999,
//     'currency' => 'usd',
//     'description' => 'Example charge',
//     'source' => $token,
//   ]);



//   $stripe->invoices->all(['limit' => 3]);
?>