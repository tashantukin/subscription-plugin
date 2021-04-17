<!-- begin header -->
<!-- <meta http-equiv="Content-Type" content="text/html; charset=utf-8" /> -->
<link rel='stylesheet prefetch' href='https://maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css'>
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css" rel="stylesheet">
<!-- <link href="css/style.css" rel="stylesheet">
<link href="css/registration.css" rel="stylesheet"> -->
<!-- end header -->

<?php

require_once('stripe-php/init.php');
$stripe_secret_key = getSecretKey();
$stripe = new \Stripe\StripeClient($stripe_secret_key);
$customer = $stripe->customers->create([
    'name'=> 'Onoda Sakamichi',
    'description' => 'sample description',
    'email' => 'email@example.com',
    'payment_method' => 'pm_card_visa',
]);
echo $customer;

$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https') === FALSE ? 'http' : 'https';
$urlexp =   explode("/", parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)); 
$host  = $urlexp[0];
//echo('host' . $host);
$host1 = $urlexp[1];
//echo('host1' . $host1);
$host2 = $urlexp[2];
////echo('host2' . $host2);
$host3 = $urlexp[3];
//echo('host3' . $host3);
$host4 = $urlexp[4];
////echo('host4' . $host4);
$host5 = $urlexp[5]; 
//echo('host5' . $host5);

$shortURL = '/subscribe';
$pathURL =  'https://' . $host1 .  '/user/plugins' .'/' . $host3 . '/' . 'css/'. 'style.css';
echo($pathURL)
?>

<!-- begin header -->
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<head>
<!-- <link href="css/styles.css" rel="stylesheet" type="text/css">       
<link href=<?php echo $pathURL ?> rel="stylesheet" type="text/css">                       -->
</head>
<!-- end header -->


<?php
include 'callAPI.php';
include 'admin_token.php';
$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$customFieldPrefix = getCustomFieldPrefix();
$url = $baseUrl . '/api/v2/marketplaces/';
$marketplaceInfo = callAPI("GET", null, $url, false);



?>

<div class="subscription-content">
    <div class="container">	
        <div class="subs-cont-top">
            <span>SAMPLE HEADER</span>
        </div>
        <div class="subs-cont-mid">
            <p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Dicta eos quo numquam laudantium asperiores quidem officia at alias dolor eaque necessitatibus explicabo iure quod, iste, nulla fugit eum laborum veritatis.
            </p>
            <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Fugit maxime harum numquam animi non qui possimus nihil veritatis impedit in adipisci officia modi sed, doloremque recusandae repellendus ea distinctio eos.
            </p>
        </div>
        <div class="subs-cont-bot">
			<div class="subscription-option">
                <?php
                $apikey = '';
              
                ?>
                <div class="subs-option">
					<div class="subs-price"><?php echo $amount ?></div>
					<div class="subs-type">Month</div>
					<div class="subs-discount"></div>
					<div class="membership-description">
						<p>Full Membership</p>
						<p>Full Membership</p>
						<p>Full Membership</p>
						<p>Full Membership</p>
						<p>Full Membership</p>
					</div>
					<div class="btn-subscribe"><a href="<?php echo $link ?>;"><?php echo $plandata['name']; ?></a></div>
                </div>
               
            </div>
        </div>	
    </div>
</div>