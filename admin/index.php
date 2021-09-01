<?php
include 'callAPI.php';
include 'admin_token.php';
$contentBodyJson = file_get_contents('php://input');
$content = json_decode($contentBodyJson, true);
$timezoneOffset =  $_POST['storageValue'];
$timezone_name = timezone_name_from_abbr("", $timezoneOffset*60, false);
date_default_timezone_set($timezone_name);

$baseUrl = getMarketplaceBaseUrl();
$admin_token = getAdminToken();
$userToken = $_COOKIE["webapitoken"];

$customFieldPrefix = getCustomFieldPrefix();
$stripe_secret_key = getSecretKey();
error_log($stripe_secret_key);
require_once('stripe-php/init.php');
\Stripe\Stripe::setApiKey($stripe_secret_key);

$url = $baseUrl . '/api/v2/users/'; 
$result = callAPI("GET", $userToken, $url, false);
$userId = $result['ID'];
$url = $baseUrl . '/api/v2/admins/' . $userId .'/users/?role=merchant&pageSize=1000'; 
$result = callAPI("GET", $userToken, $url, false);
// var_dump($result);


?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/subscription.css">
 <!-- Pagination js -->
 
 <!-- <script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal/js/pagination.min.js"></script> -->
<script src="https://js.stripe.com/v3/"></script>

 <!-- bootstrap style -->

<!-- Pagination style -->
<!-- <link href="https://bootstrap.arcadier.com/adminportal_pre/css/bootstrap.min.css" rel="stylesheet" type="text/css"> -->
<!-- <link href="https://bootstrap.arcadier.com/adminportal/css/pagination.css" rel="stylesheet" type="text/css"> -->



<div class="page-content">
                <div class="gutter-wrapper">

                <div class="page-topnav" style="height: 5px;">
   
                <div class="float">
                        <a class="btn-info-plug-in" href="https://support.arcadier.com/hc/en-us/articles/900006649666-Subscriptions-Plugin" target="_blank" >How to use this Plug-In?</a>
                    </div>
              </div>
                    <div class="panel-box border-none">
                        
                        <div class="page-content-top">
                            <div class="row">
                                <div class="col-sm-8">
                                    <h4>Setup a membership fee for your merchant</h4>
                                    <h5>The Membership &amp; Subscriptions plug-in allows marketplace administrators to charge merchants a recurring membership subscription fee when they join your marketplace.</h5>
                                </div>
                                <div class="col-sm-4">
                                    <div class="auto btn-scheduler btn-area pull-right text-right"> <a href="settings.php" class="blue-btn">Subscription Settings</a>
                                    <!-- <button type="button" class="btn cmn-btn-blue">Subscription Settings</button> -->
                                </div>
                                </div>
                            </div>
                        </div>
                      
                    </div>
                    
                    
                    <div class="filter-bar page-topnav" style="margin-bottom: 0;box-shadow: none;">

                    <form action="" class="form-inline form-filter">

                        <!-- filter -->

                            <div class="sassy-filter lg-filter">

                                

                                    <div class="sassy-flex">

                                        <div class="sassy-l grey_filter">

                                            <div>

                                                <div class="group-search">

                                                    <div class="group-search-flex">

                                                        <label for="" class="sassy-label">Filter by:</label> 

                                                        <span class="sassy-search">
                                                           <input class="form-control" name="keywords" id="keywords" placeholder="Search by merchant name, status or package plan">
                                                        </span>
                                                    </div>

                                                </div>

                                            </div>

                                        </div>



                                    </div>

                                

                            </div></form>

                            <!-- filter -->

                    

                </div>
                    

                    <div class="panel-box">
                    	<div class="merchant-commission-table scheduler-tbl">
                                <table class="table" id="invoices">
                                    <thead>
                                        <tr>
                                            <th>Merchant Name</th>
                                            <th>Merchant Email</th>
                                            <th>Status</th>
                                            <th>Date Joined</th>
                                            <th>Package Plan</th>
                                            <th>Billing Cycle</th>
                                           
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <?php

                                            foreach($result['Records'] as $user ) {

                                                $user_name =  $user['DisplayName'];
                                                $user_email = $user['Email'];
                                                $user_id =$user['ID'];
                                                

                                                if ($user['CustomFields'] != null) {


                                                    foreach($user['CustomFields'] as $cf) {

                                                        if ($cf['Name'] == 'subscription_id' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
                                                            $subs_id = $cf['Values'][0];
                                                
                                                        error_log($subs_id);    
                                                        
                                                    if ($subs_id) {
                                                            $subscription = \Stripe\Subscription::retrieve($subs_id);
                                                            error_log(json_encode($subscription));
                                                            error_log(json_encode($subscription->items->data[0]->price->nickname));
                                                        
                                                            $end_date = $subscription->current_period_end;
                                                            // $date = date('d/m/Y H:i', $serverdate);
                                                            error_log(date('d/m/Y H:i', $end_date));
                                                            $start_date = $subscription->current_period_start;
                                                            error_log(date('d/m/Y H:i', $start_date));
                                                            $subs_name = $subscription->items->data[0]->price->nickname;
                                                            error_log($subs_name);
                                                            $subs_amount = $subscription->items->data[0]->price->unit_amount /100;
                                                            error_log($subs_amount);

                                                            $status = $subscription->status;
                                                            if ($status == 'canceled'){
                                                                $status = 'cancelled';
                                                            }

                                                            if ($subscription->pause_collection != null) {
                                                                $status = 'paused';
                                                            }

                                                           
                                                            $joined_date = $subscription->created;
                                                            $customer_id = $subscription->customer;
                                                            error_log($status);

                                                            echo "<tr class='border-hover'>";

                                                            echo  "<td data-th='Merchant Name' class='clickable-row' style='cursor: pointer; cursor: hand;' data-href='customer_details.php?subsId=". $subs_id . "&userguid=" . $user_id . "&customerId=". $customer_id . "&status= ". ucfirst($status)  ." '>" .  $user_name. "</td>";
                                                            echo  "<td data-th='Merchant Email'>" .  $user_email. "</td>";
                                                            echo  "<td data-th='Status'>" .  ucfirst($status) . "</td>";
                                                            echo  "<td data-th='Date Joined'>" .  date('d/m/Y', $joined_date). "</td>";
                                                            echo  "<td data-th='Package Plan'>" .  $subs_name . "</td>";
                                                            echo  "<td data-th='Billing Cycle'>" .  date('d/m/Y',$start_date)  . '-' . date('d/m/Y',$end_date) . "</td>";

                                                            echo " </tr>";
                                                        }
                                                            
                                                    }
                                                }
                                            }
                        

                                         }         


                                     ?>  
                                        
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <nav class="text-center" aria-label="Page navigation">
                <ul class="pagination">
                    <li class="previous-page"> <a href="javascript:void(0)" aria-label=Previous><span aria-hidden=true>&laquo;</span></a></li>
                </ul>
            </nav>

                </div>
            </div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>
<script type="text/javascript" src="scripts/jquery.dataTables.js"></script>

<script>


jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });

    $('#invoices').DataTable(
        {
        // "paging":   false,
        // "order": [[ 1, "desc" ]],
       "lengthMenu": [[20], [20]],
       "ordering": false,
        "info":     false,
        "searching" :true,
        // "pagingType": "simple_numbers"
        // "columnDefs": [{ orderable: false, targets: [5] }]
        }
    );

    waitForElement('#invoices_filter',function(){
        $('#invoices_filter input').addClass('form-control');
        $('#invoices_filter input').attr('placeholder', 'Search by merchant name, status or package plan');
        $('#invoices_filter input').appendTo($('.group-search-flex span'));
        
        $('#keywords').hide();
        $('#invoices_filter label').hide();

      
    });

    waitForElement('#invoices_length',function(){
         $('#invoices_length').css({ display: "none" });
    });


    waitForElement('#invoices_paginate',function(){
         $('#invoices_paginate').css({ display: "none" });
    });



    

});


function waitForElement(elementPath, callBack){
	window.setTimeout(function(){
	if($(elementPath).length){
			callBack(elementPath, $(elementPath));
	}else{
			waitForElement(elementPath, callBack);
	}
	},10)
}
</script>

<script>
var numRows = $("#invoices tbody tr").length;
//  alert(numRows);
var limitperpage = 20;
$("#invoices tbody tr:gt(" + (limitperpage - 1) + ")").hide();
var totalpages = Math.ceil(numRows / limitperpage);
//  alert(totalpages);
$(".pagination").append("<li class ='current-page active'><a href='javascript:void(0)'>" + 1 + "</a></li>");

for (var i = 2; i <= totalpages; i++) {
    $(".pagination").append("<li class='current-page'> <a href='javascript:void(0)'>" + i + "</a></li>");
}
$(".pagination").append("<li id='next-page'><a href='javascript:void(0)' aria-label=Next><span aria-hidden=true>&raquo;</span></a></li>");

// Function that displays new items based on page number that was clicked
$(".pagination li.current-page").on("click", function() {
    // Check if page number that was clicked on is the current page that is being displayed
    if ($(this).hasClass('active')) {
        return false; // Return false (i.e., nothing to do, since user clicked on the page number that is already being displayed)
    } else {
        var currentPage = $(this).index(); // Get the current page number
        $(".pagination li").removeClass('active'); // Remove the 'active' class status from the page that is currently being displayed
        $(this).addClass('active'); // Add the 'active' class status to the page that was clicked on
        $("#invoices tbody tr").hide(); // Hide all items in loop, this case, all the list groups
        var grandTotal = limitperpage * currentPage; // Get the total number of items up to the page number that was clicked on

        // Loop through total items, selecting a new set of items based on page number
        for (var i = grandTotal - limitperpage; i < grandTotal; i++) {
            $("#invoices tbody tr:eq(" + i + ")").show(); // Show items from the new page that was selected
        }
    }
});

// Function to navigate to the next page when users click on the next-page id (next page button)
$("#next-page").on("click", function() {
    var currentPage = $(".pagination li.active").index(); // Identify the current active page
    // Check to make sure that navigating to the next page will not exceed the total number of pages
    if (currentPage === totalpages) {
        return false; // Return false (i.e., cannot navigate any further, since it would exceed the maximum number of pages)
    } else {
        currentPage++; // Increment the page by one
        $(".pagination li").removeClass('active'); // Remove the 'active' class status from the current page
        $("#invoices tbody tr").hide(); // Hide all items in the pagination loop
        var grandTotal = limitperpage * currentPage; // Get the total number of items up to the page that was selected

        // Loop through total items, selecting a new set of items based on page number
        for (var i = grandTotal - limitperpage; i < grandTotal; i++) {
            $("#invoices tbody tr:eq(" + i + ")").show(); // Show items from the new page that was selected
        }

        $(".pagination li.current-page:eq(" + (currentPage - 1) + ")").addClass('active'); // Make new page number the 'active' page
    }
});

// Function to navigate to the previous page when users click on the previous-page id (previous page button)
$("#previous-page").on("click", function() {
    var currentPage = $(".pagination li.active").index(); // Identify the current active page
    // Check to make sure that users is not on page 1 and attempting to navigating to a previous page
    if (currentPage === 1) {
        return false; // Return false (i.e., cannot navigate to a previous page because the current page is page 1)
    } else {
        currentPage--; // Decrement page by one
        $(".pagination li").removeClass('active'); // Remove the 'activate' status class from the previous active page number
        $("#invoices tbody tr").hide(); // Hide all items in the pagination loop
        var grandTotal = limitperpage * currentPage; // Get the total number of items up to the page that was selected

        // Loop through total items, selecting a new set of items based on page number
        for (var i = grandTotal - limitperpage; i < grandTotal; i++) {
            $("#invoices tbody tr:eq(" + i + ")").show(); // Show items from the new page that was selected
        }

        $(".pagination li.current-page:eq(" + (currentPage - 1) + ")").addClass('active'); // Make new page number the 'active' page
    }
});
</script>


<!-- end footer -->


