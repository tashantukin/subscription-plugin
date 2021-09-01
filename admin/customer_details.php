<?php
include 'callAPI.php';
include 'admin_token.php';


$subs_id = $_GET['subsId'];
$user_guid = $_GET['userguid'];
$customer_id = $_GET['customerId'];
$status = $_GET['status'];

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

$url = $baseUrl . '/api/v2/users/'. $user_guid; 
$result = callAPI("GET", $admin_token['access_token'], $url, false);

$user_email = $result['Email'];
$user_display_name = $result['DisplayName'];
$user_avatar = $result['Media'][0]['MediaUrl'];



//get the status customf field values of iscancelled, ispaused etc

foreach($result['CustomFields'] as $cf) 
{ 
    
if ($cf['Name'] == 'isCancelled' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $is_cancelled = $cf['Values'][0];
     
 }
 if ($cf['Name'] == 'isPaused' && substr($cf['Code'], 0, strlen($customFieldPrefix)) == $customFieldPrefix) {
    $is_paused = $cf['Values'][0];
     
 }

}


//subscription details

$subscription = \Stripe\Subscription::retrieve($subs_id);
error_log(json_encode($subscription));
error_log(json_encode($subscription->items->data[0]->price->nickname));

$end_date = $subscription->current_period_end;
error_log(date('d/m/Y H:i', $end_date));
$start_date = $subscription->current_period_start;
error_log(date('d/m/Y H:i', $start_date));
$subs_name = $subscription->items->data[0]->price->nickname;
error_log($subs_name);
$subs_amount = $subscription->items->data[0]->price->unit_amount /100;
error_log($subs_amount);

$joined_date = $subscription->created;
$status = $subscription->status;

if ($status == 'canceled') {
    $status  = 'cancelled';
}

if ($subscription->pause_collection != null) {
    $status = 'paused';
}

// $retrieve =  $stripe->invoices->all(['customer' => 'cus_JIvgAmFgcxpxIm']); 

$invoices  = \Stripe\Invoice::all(['customer' => $customer_id]);

// var_dump($invoices);
// echo json_encode($invoices);

$invoice_total = count((array)$invoices->data);

?>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="css/settings.css">
<link rel="stylesheet" href="css/style.css">
<link rel="stylesheet" href="css/pagination.css">
<link rel="stylesheet" href="css/subscription.css">
<!-- <script type="text/javascript" src="https://bootstrap.arcadier.com/adminportal/js/pagination.min.js"></script> -->
<!-- <link href="https://bootstrap.arcadier.com/adminportal/css/pagination.css" rel="stylesheet" type="text/css"> -->
<script src="https://js.stripe.com/v3/"></script>
<div class="page-content">

                <div class="navigation-bar page-topnav">

                    <!-- <a href="index.php" class="mybtn btn-back"><img src="images/back.svg" alt="Back">Back</a> -->
                   <a class="btn-back" href="index.php"><i class="icon icon-arrowleft"></i> Back</a> 

                </div>

                <div class="profile-bar page-topnav">

                    <div class="col-sm-6 col-md-6">
                        <h5>DISPLAY IMAGE</h5>

                        <div class="profile-avtar">
    
                            <a href="#">
    
                                <img src="<?php echo $user_avatar  ?>" alt="Merchant Avtar">
    
                            </a>
    
                            <span class="profile-meta">
    
                                <span class="label-plugin-name"><?php echo $user_display_name ?></span>
    
                                <span class="meta-label"><?php echo $user_email ?></span>
    
                                <a href="/admin/usermanager/userdetail?userid=0&userguid=<?php echo $user_guid;?>" class="view-user-details">View user details</a>
    
                                <!-- <span class="profile-title">WaterSoda</span> -->
    
                            </span>
    
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-6">
                  

                            <ul class="w-list-items">

                                <li>

                                    <label>Subscription ID</label>

                                    <p><?php echo strtoupper($subs_id) ?></p>

                                </li>

                                <li>

                                    <label>Status</label>

                                    <p id="subs-status"><?php  echo $is_paused == 'true' ? 'Paused' : ucfirst($status) ?></p>

                                </li>

                                

                            </ul>

                    </div>

                    

                </div>

                <div class="row wrap-panel">
                    <div class="new-sub-container">
                        <div class="plugin-large-title">Subscription Details</div>

                        <div class="sub-container-sub">
                                <div class="col-sm-6 col-md-6">
        
                                
        
                                    <ul class="w-list-items">
        
                                        <li>
        
                                            <label>Start Date</label>
        
                                            <p><?php echo date('d/m/Y', $start_date)  ?></p>

                                            

        
                                        </li>
        
                                        <li>
        
                                            <label>Last Charged Date</label>
        
                                            <p><?php echo date('d/m/Y', $start_date)  ?></p>

                                            <!-- <a href="#" class="cancel-buttons">Pause Subscription</a> -->
        
                                        </li>
        
                                        <li>
        
                                            <label>Next Charge Date</label>
        
                                            <p><?php echo date('d/m/Y', $end_date)  ?></p>

                                            <a href="javascript:void(0)" onclick="<?php echo $is_cancelled == "true" ? "" : "subcriptionControl()" ?>" id="subscriptions-control" class="cancel-buttons <?php echo $is_paused == "true" ? "resume" : "pause" ?>"><?php echo $is_paused == 'true' ? 'Resume Subscription' : 'Pause Subscription' ?></a>
        
                                        </li>
        
                                        
        
                                    </ul>
        
                            
        
                            </div>
        
                            <div class="col-sm-6 col-md-6">
        
                                
        
                                    <ul class="w-list-items">
        
                                        <li>
        
                                            <label>Package</label>
        
                                            <p><?php echo $subs_name ?> </p>
        
                                        </li>
        
                                        <li>
        
                                            <label>Charge Frequency</label>
        
                                            <p>Monthly</p>
        
                                        </li>
        
                                        
                                    </ul>
        
                            
        
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        
                        
                    </div>
                    

                </div>
                <!-- custom user field-->
                <div class="wrap-panel">
                    <div class="custom-user-field-sec">

                    <div class="plugin-large-title">Invoices</div>

                        <div class="merchant-commission-table scheduler-tbl">
                            <table class="table" id="invoices">
                                <thead>
                               
                                    <tr>
                                        <th>Timestamp</th>
                                        <th>Invoice ID</th>
                                        <th>Package</th>
                                        <th>Amount</th>
                                        <th>Collection Method</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php 
                                     foreach($invoices['data'] as $invoice) {
                                        $collection_method = $invoice['billing_reason'] == 'subscription_create' ? 'First Payment' : 'Charge automatically';
                                        $currency = strtoupper($invoice['lines']['data'][0]['plan']['currency']);
                                        // $sub_total
                                        $amount = $invoice['lines']['data'][0]['plan']['amount'] / 100;
                                        $amount = number_format((float)$amount,2);
                                        echo "<tr class='border-hover'>";
                                            echo  "<td data-th='Timestamp' class='clickable-row' style='cursor: pointer; cursor: hand;' data-href='invoice_details.php?invoiceId=". $invoice['id'] .  "&userguid=" . $user_guid  . "&customerId=". $customer_id . "&status= ". ucfirst($status)  . "&subsId=". $subs_id . "'>"  . date('d/m/Y H:i', $invoice['created']) .  "</td>";
                                            echo   "<td data-th='Invoice ID'>" . $invoice['number'] . "</td>";
                                            echo   "<td data-th='Package'>" . $invoice['lines']['data'][0]['plan']['nickname'] . "</td>";
                                            echo  "<td data-th='Amount'>" . $currency .' ' .'$'  . $amount  . "</td>";
                                            echo   "<td data-th='Collection Method'>" . $collection_method . "</td>";
                                            echo  "<td data-th='Status'>" . ucfirst($invoice['status']) ."</td>";
                                        echo "</tr>";                                       
                                     }
                                ?>
                                  
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- <nav class="text-center" id="pagination-userslist" aria-label="Page navigation">
                            <div class="paginationjs">
                                <div class="paginationjs-pages">
                                    <ul>
                                        <li class="paginationjs-prev disabled"><a>«</a></li>
                                        <li class="paginationjs-page J-paginationjs-page active" data-num="1"><a>1</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="2"><a href="">2</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="3"><a href="">3</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="4"><a href="">4</a></li>
                                        <li class="paginationjs-page J-paginationjs-page" data-num="5"><a href="">5</a></li>
                                        <li class="paginationjs-ellipsis disabled"><a>...</a></li>
                                        <li class="paginationjs-page paginationjs-last J-paginationjs-page" data-num="11"><a href="">11</a></li>
                                        <li class="paginationjs-next J-paginationjs-next" data-num="2" title="Next page"><a href="">»</a></li>
                                    </ul>
                                </div>
                            </div>
                        </nav> -->

                        <nav class="text-center" aria-label="Page navigation">
                         <ul class="pagination">
                             <li class="previous-page"> <a href="javascript:void(0)" aria-label=Previous><span aria-hidden=true>&laquo;</span></a></li>
                          </ul>
                         </nav>
                </div>

                <div id="subcriptionControl" class="popup modal-change-pwd">
                    <div class="popup-wrapper">
                            <div class="modal-header">
                                <div class="text-center">
                                    <h5></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:void(0)" class="close-popup" onclick="popup_close(this)" data-dismiss="modal"><i class="icon icon-close"></i></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="popup-content text-center">
                                <div class="form-group">
                                <p>Are you sure you want to <?php echo $is_paused == "true" ? "resume" : "pause" ?> the subscription?</p>
                                </div>
                            </div>
                            <div class="popup-footer text-center">
                                <button type="button" onclick="popup_close(this)" class="mybtn btn-grey">Cancel</button>
                                <button type="button"  id="pause-subs" onclick="subscriptionPlay(this)" class="mybtn btn-blue" subs-id="<?php echo $subs_id; ?>" user-guid ="<?php echo $user_guid; ?>" status="<?php echo $is_paused == "true" ? "resume" : "pause" ?>">Okay</button>
                            </div>
                    </div>
                </div>

                <div id="cancelSubscription" class="popup modal-change-pwd">
                    <div class="popup-wrapper">
                            <div class="modal-header">
                                <div class="text-center">
                                    <h5></h5>
                                </div>
                                <div class="pull-right">
                                    <a href="javascript:void(0)" class="close-popup" onclick="popup_close(this)" data-dismiss="modal"><i class="icon icon-close"></i></a>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                            <div class="popup-content text-center">
                                <div class="form-group">
                                <p>Are you sure you want to cancel the subscription?</p>
                                </div>
                            </div>
                            <div class="popup-footer text-center">
                                <button type="button" id="cancel-subs" onclick="popup_close(this)" class="mybtn btn-blue" subs-id="<?php echo $subs_id;  ?>" user-guid ="<?php echo $user_guid; ?>">Yes</button>
                                <button type="button" onclick="popup_close(this)" class="mybtn btn-grey">No</button>
                                
                            </div>
                    </div>
                </div>
                <!-- End custom user field-->

                <div class="box-change-password">

                    <a href="javascript:void(0)" onclick="<?php echo $is_cancelled == "true" ? "" : "cancelSubscription()" ?>"  class="mybtn btn-default"><?php echo $is_cancelled == "true" ? 'Cancelled' : 'Cancel Subscription' ?></a>

                </div>

            </div>
<!-- begin footer -->
<script type="text/javascript" src="scripts/package.js"></script>
<!-- <script type="text/javascript" src="scripts/pagination.js"></script> -->
<script type="text/javascript" src="scripts/jquery.dataTables.js"></script>

<script>


jQuery(document).ready(function($) {
    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });

    // $('#invoices').DataTable(
    //     {
    //     // "paging":   false,
    //     // "order": [[ 1, "desc" ]],
    //     "lengthMenu": [[20], [20]],
    //     "ordering": false,
    //     "info":     false,
    //     "searching" :false,
    //     "pagingType": "simple_numbers"
    //     // "columnDefs": [{ orderable: false, targets: [5] }]
    //     }
    // );

    waitForElement('#invoices_wrapper',function(){
        var pagediv =  "<div class ='paging' id = 'pagination-insert'> </div>";
        $('#invoices_paginate').appendTo($('#pagination-insert'));
        $('#invoices_wrapper').append(pagediv);
    });
    waitForElement('#invoices_paginate',function(){
       
        $('#invoices_paginate').appendTo($('#pagination-userslist .paginationjs-pages'));
        $('#pagination-userslist .paginationjs-pages ul').remove();
    });

    waitForElement('#invoices_length',function(){
         $('#invoices_length').css({ display: "none" });
    });

});


function subcriptionControl()
    {
        var $modal = $("#subcriptionControl");
        $("#cover").show();
        $modal.fadeIn();
    }

    function cancelSubscription()
    {
        var $modal = $("#cancelSubscription");
        $("#cover").show();
        $modal.fadeIn();
    }


    function subscriptionPlay(ele) {



        var that = jQuery(ele);



        that.parents('.popup').fadeOut();



        jQuery("#cover").fadeOut();


        if($("#subscriptions-control").hasClass("resume")){
            $("#subscriptions-control").text("Pause Subscription");
            $("#subscriptions-control").removeClass("resume");
           // $("#subcriptionControl").find(".popup-content .form-group").text("Are you sure you want to pause the subscription?");
        }else {
            $("#subscriptions-control").text("Resume Subscription")
            $("#subscriptions-control").addClass("resume");
           // $("#subcriptionControl").find(".popup-content .form-group").text("Are you sure you want to resume the subscription?");
        }
    }

    function popup_close(ele) {

        var that = $(ele);

        that.parents('.popup').fadeOut();

        $("#cover").fadeOut();

    }

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





