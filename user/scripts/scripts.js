(function () {
  /* globals $ */
  var scriptSrc = document.currentScript.src;
  var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
  var packageId = re.exec(scriptSrc.toLowerCase())[1];
  var pathname = (window.location.pathname + window.location.search).toLowerCase();
  var packagePath = scriptSrc.replace("/scripts/scripts.js", "").trim();
  var customFieldPrefix = packageId.replace(/-/g, "");
  const HOST = window.location.host;
  var hostname = window.location.hostname;
  const protocol = window.location.protocol;
  const baseURL = window.location.hostname;
  var urls = window.location.href.toLowerCase();
  var userId = $("#userGuid").val();
  var customer_id;
  var plan_id;
  var isSubscriptionValid = 0;
    // var stripe;
  
  function getAddressData()
  {
    var addressData = {
      'line1': $('#myaddress').val(),
      'city': $('#city').val(),
      'country': $("#country option:selected").val(),
      'state': $('#state').val(),
      'postal_code' : $('#postal-code').val()
    }
    localStorage.setItem("address", JSON.stringify(addressData))

    console.log(JSON.parse(localStorage.getItem("address")));
    var addressInfo1 = JSON.parse(localStorage.getItem("address"));
    console.log((addressInfo1['line1']));
  }

  function getMarketplaceCustomFields(callback) {
    var apiUrl = "/api/v2/marketplaces";
    $.ajax({
      url: apiUrl,
      method: "GET",
      contentType: "application/json",
      success: function (result) {
        if (result) {
          callback(result.CustomFields);
        }
      },
    });
  }

  function waitForElement(elementPath, callBack) {
    window.setTimeout(function () {
      if ($(elementPath).length) {
        callBack(elementPath, $(elementPath));
      } else {
        waitForElement(elementPath, callBack);
      }
    }, 700);
  }

  //function disablePages()
  //{
    //navigate to user settings
    //disable seller pages
    if (pathname.indexOf('/user/marketplace/dashboard') > -1
      || pathname.indexOf('/user/item/list') > -1
      || pathname.indexOf('/user/item/upload') > -1
      || pathname.indexOf('/user/manage/orders') > -1
      || pathname.indexOf('/user/marketplace/sales') > -1
      || pathname.indexOf('/user/marketplace/custom-delivery-methods') > -1
      || pathname.indexOf('/user/order/orderhistory') > -1
      || pathname.indexOf('/user/order/cart') > -1
      || pathname.indexOf('/user/chat/get-inbox') > -1) {
      getPlanData('Pages');
      // console.log(isSubscriptionValid);
        // if (isSubscriptionValid != 1) {
         
        // // $('#subscriptionstab').click();
        // }
     
     }
 // }
  function getPlanData(page){
      var apiUrl = packagePath + '/getPrices.php';
     
    $.ajax({
      url: apiUrl,
      method: 'POST',
      contentType: 'application/json',
           
      success: function (result)
      {
        result = JSON.parse(result);

        console.log(result);
     
        var startDate = new Date(); 
        var currentDate = moment(startDate);
        var startDateMoment = moment(startDate).format('DD/MM/YYYY');
        var endDateMoment = currentDate.add(1, 'months').format('DD/MM/YYYY');
        var endDateMoment2 = currentDate.add(2, 'months').format('DD/MM/YYYY');
        plan_id = result.id;
        if (result.status == 'active' || (result.status == 'canceled' && moment(startDateMoment).isSameOrBefore(endDateMoment, 'day'))) {

          isSubscriptionValid = 1;
          
          if (page == 'Settings') {
            $('#cancelsubs').attr("sub-id", result.sub_id);
            $('#subscription-name').text(result.name);
            $('.subscription-step1').addClass('hide');
            $('.subscription-step2').removeClass('hide');
            var status = result.status == 'canceled' ? 'Cancelled' : result.status;
            $('#status').text(status);
            $('#nxtbilling').text(endDateMoment);

            //billing starts on --
            $('#billingstart').text(startDateMoment);
            $('#subs-name').text(result.name);
            $('#subs-desc').text(result.description)
            $('#package-name').text(result.name);
           

          }
          
        } else {
          urls = `${protocol}//${baseURL}/user/marketplace/seller-settings`;
          window.location.href = urls;
          }
          
			},
			error: function(jqXHR, status, err) {
			//	toastr.error('Error!');
			}
		});
	
    }
    
  function subscribe(card, stripe)
  {
    var addressInfo = JSON.parse(localStorage.getItem("address"));
    console.log((addressInfo['line1']));
    var apiUrl = packagePath + '/createMember.php';
      var data = {
        'full_name': `${$('#input-firstName').val()} ${$('#input-lastName').val()}`,
        'email': $('#notification-email').val(),
        'contact_number': $('#input-contactNumber').val(),
        'line1': addressInfo['line1'],
        'city': addressInfo['city'],
        'country': addressInfo['country'],
        'state': addressInfo['state'],
        'postal_code': $('#postal-code').val()
        }
		$.ajax({
            url: apiUrl,
            
			method: 'POST',
            contentType: 'application/json',
           data: JSON.stringify(data),
			success: function(result) {
                result = JSON.parse(result);
                var customerId = result.result

                createPaymentMethod(customerId, card, stripe)
      
			},
			error: function(jqXHR, status, err) {
			//	toastr.error('Error!');
			}
		});
	
    }

  function saveSubscriptionData(id, status)
  {
    var apiUrl = packagePath + '/saveSubscriptionData.php';
    var data = {
      'id': id,
      'status' : status
    }
      
      $.ajax({
              url: apiUrl,
              
        method: 'POST',
              contentType: 'application/json',
            data: JSON.stringify(data),
        success: function(result) {
          result = JSON.parse(result);
          console.log(`cf ${result}`);
        
        },
        error: function(jqXHR, status, err) {
        //	toastr.error('Error!');
        }
      });
  }
  
  function createPaymentMethod(customerId, card, stripe)
  {
      
      // const customerId = customer_id;
      // Set up payment method for recurring usage
      let billingName = 'Onoda Sakamichi';
    
      let priceId =  plan_id  //= document.getElementById('priceId').innerHTML.toUpperCase();
    
      stripe
        .createPaymentMethod({
          type: 'card',
          card: card,
          billing_details: {
            name: billingName,
          },
        })
        .then((result) => {
          if (result.error) {
            displayError(result);
          } else {
              console.log(result.paymentMethod.id);
            createSubscription(
              customerId,result.paymentMethod.id);
          }
        });
  }
    
  function createSubscription(customerId, paymentId)
  {
      var apiUrl = packagePath + '/createSubscription.php';
      var data = { 'customer_id': customerId,  'payment_id' : paymentId }
      $.ajax({
          url: apiUrl,
          
        method: 'POST',
              contentType: 'application/json',
                data: JSON.stringify(data),
        success: function(result) {
          result = JSON.parse(result);
          saveSubscriptionData(result.result.id, result.result.status);
          // console.log(result.result.plan.nickname);
          // console.log(result.result.plan['nickname']);
          $('#cancelsubs').attr("sub-id", result.result.id);
          $('#status').text(result.result.status);
          $('#subscription-name').text(result.result.plan.nickname);
          $('#nxtbilling').text(new Date(result.result.current_period_end * 1000).format("dd/mm/yyyy"))
          
          //console.log(result.result);
          $('.subscription-step1').addClass('hide');
          $('.subscription-step2').removeClass('hide');

        },
        error: function(jqXHR, status, err) {
        //	toastr.error('Error!');
        }
    });
  }

  function cancelSubscription(id)
  {
    var apiUrl = packagePath + '/cancelSubscription.php';
      var data = { 'id': id  }
      $.ajax({
          url: apiUrl,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(result) {
        result = JSON.parse(result);
          console.log(result);

          if (result.result.status == 'canceled') {
            saveSubscriptionData(result.result.id, result.result.status);

          }
        },
        error: function(jqXHR, status, err) {
        //	toastr.error('Error!');
        }
    });
  }

  function delete_subscription(id){
      show_conformation_subscription(id);
  }
  function cancel_remove_subscription(){
      var target =  jQuery("#subscription-remove");
      var cover = jQuery("#cover");
      target.fadeOut();
      cover.fadeOut();
      jQuery(".my-btn.btn-saffron").attr('data-id','');
      console.log("cancel remove item..");
  }
  function show_conformation_subscription(id)
  {
     
      var target =  jQuery("#subscription-remove");
      var cover = jQuery("#cover");
      target.fadeIn();
      cover.fadeIn();
    //  jQuery("#okayConfirm").attr('data-key',key);
      jQuery("#okayConfirm").attr('data-id',id);
  }
  function confirm_remove_subscription(ele){
      var that = jQuery(ele);
      var id = that.attr('data-id');
      //var key = that.attr('data-key');
      target = ''
     // if(key == 'item'){
          target = jQuery('.subscription-row');
     // }
     cancelSubscription(id);
      target.fadeOut(500, function() {
          // target.remove(); 
          target.find("#status").text("Cancelled");
          target.find(".cmn-clr-theme a").removeAttr("href");
          target.find(".cmn-clr-theme a").removeAttr("onclick");
          target.find(".cmn-clr-theme a").addClass("disabled");
          target.show();
          cancel_remove();
      });
  }
  
  $(document).ready(function ()
  {

    //home page upon logging in
    if ($('body').hasClass('page-home')) {
      getPlanData('Homepage');
    }
  
    if (pathname.indexOf('/user/marketplace/seller-settings') > -1 || pathname.indexOf('/user/marketplace/be-seller') > -1) {

      //next button
      $('#address #next-tab').on('click', function(e){
       
        $('#payment_acceptance #next-tab').text('NEXT');
        var attrbts = $('#payment_acceptance #next-tab').prop("attributes");
        // loop through element1 attributes and apply them on element2.
        $.each(attrbts, function() {
          $('#subscriptions #next-tab').attr(this.name, this.onclick);
          
        });
        $('#payment_acceptance #next-tab').removeAttr('onclick');
      });
     

      
      
      $('#payment_acceptance #next-tab').on('click', function(e){
        // e.preventDefault();
        // e.stopImmediatePropagation();
        // return false;
        $('#subscriptionstab').click();
       
      });




     // $('#payment_acceptance #next-tab').removeAttr('onclick');
      var itemsUrl = `${protocol}//${baseURL}/user/item/list`;
     // window.location.href = urls;
      $('#subscriptions #next-tab').attr('href', itemsUrl);
      
      var subscriptionTabHeader = `<li> <a href="#subscriptions" aria-expanded="false" id="subscriptionstab"><span>SUBSCRIPTIONS</span></a></li>`
      $('#setting-tab').append(subscriptionTabHeader);

      var cancelSubModal = `<div class="popup-area item-remove-popup" id="subscription-remove">
      <div class="wrapper">
        <div class="title-area text-capitalize">
          <h1>Cancel Subscription</h1>
        </div>
        <div class="content-area">
          <p>Are you sure you want to cancel your subscription?</p>
        </div>
        <div class="btn-area">
          <div class="pull-left">
            <input id="cancel" type="button" value="CANCEL" class="my-btn btn-black">
          </div>
          <div class="pull-right">
            <input id="okayConfirm" data-key="" data-id=""  type="button" value="OKAY" class="my-btn btn-saffron"
          </div>
          <div class="clearfix"></div>
        </div>
      </div>
    </div>
      <div id="cover"></div>`
  
      $('body').append(cancelSubModal);
      var imageLink = `${location.protocol}//${hostname}/user/plugins/${packageId}/images/strip-gateway.svg`;
      var subscriptionContent = `
      <div class="tab-pane" id="subscriptions">
      
            <div class="container">

                <div class="seller-common-box">

                    <div class="subscription-step1">

                        <div class="seller-setting-p"><b id="subs-name">Subscription Package</b></div>

                    <div class="row">

                        <div class="col-sm-7">

                            <div class="item-form-group subscription-title-box">

                                <h3 class="subscription-package-title">Subscription Details</h3>

                                <p class="grey-colot-txt" id="subs-desc"></p>

                            </div>

                            <div class="item-form-group">

                                <h3 class="subscription-package-title">Enter your payment information as below</h3>

                                <div class="method-main">

                                    <div class="method-form">

                                        <div id="card-element"></div>
                                            <!-- Used to display Element errors. -->
                                            <div id="card-errors" role="alert"></div>
                                            <p id="card-errors" style="margin-bottom: 10px; line-height: inherit; color: #eb1c26; font-weight: bold;"></p>
                                        
                                            </div>
    
                                            <div class="method-img">
    
                                                <img src=${imageLink}>
    
                                            </div>
    
                                        </div>
                                        
                            </div>

                          
                        </div>

                        <div class="col-sm-5">

                            <form class="couponSubscription" method="post">

                                <div class="subscription-package-box">

                                <div class="subsc-package-title">

                                    <a href="javacrtipt:void(0);" id="package-name">{Package Name} </a>

                                    <p class="grey-colot-txt">Billing starts on: <span class="subsc-darkgrey-colot-txt" id="billingstart">DD/MM/YYYY</span> reoccurs monthly</p>

                                </div>

                                <div class="item-form-group row">

                                    <div class="col-md-9">

                                        <label>Coupon code</label>

                                        <input class="required" id="first-name" name="first-name" type="text" value="">

                                    </div>

                                    <div class="col-md-3">

                                        <label>&nbsp;</label>

                                        <div class="atag-color">

                                            <a href="javascript:void(0);">Apply</a>

                                        </div>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                                <div class="row">

                                    <div class="col-md-9">

                                        <p class="grey-colot-txt">Discount amount</p>

                                    </div>

                                    <div class="col-md-3 text-right">

                                        <a href="javascript:void(0);">USD $0.00</a>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                                <hr>

                                <div class="row">

                                    <div class="col-md-7">

                                        <p class="grey-colot-txt"><strong>Discount amount</strong></p>

                                    </div>

                                    <div class="col-md-5 package-price">

                                        <a href="javascript:void(0);"><strong><span>USD $15.00</span> / <span>Month</span></strong></a>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                            </div>  

                            </form>

                            <div class="text-center">

                                <a class="my-btn btn-red" id="paynowPackage" href="javacrtipt:void(0);">PAY NOW</a>

                            </div>

                            

                        </div>

                    </div>                        

                </div>

              

              <div class="subscription-step2 hide">

                <div class="seller-setting-p"><b>Subscription Package</b></div>

                <div class="subscription-list-head">

                    <div class="col-md-3">Package name</div>

                    <div class="col-md-3">Next Billing cycle</div>

                    <div class="col-md-2">Status</div>

                    <div class="col-md-4"></div>

                    <div class="clearfix"></div>

                </div> 

                <div class="subscription-list-body">
                    <div class="subscription-row" data-key="item" data-id="2">
                        <div class="col-md-3" id="subscription-name">{Package name here}</div>

                        <div class="col-md-3" id="nxtbilling">{DD/MM/YYYY}</div>

                        <div class="col-md-2" id="status">Active</div>

                        <div class="col-md-4"><span class="cmn-clr-theme"><a href="javascript:void(0)" id="cancelsubs" sub-id="">Cancel subscription</a></span></div>

                        <div class="clearfix"></div>
                    </div>
                    
                    <div class="clearfix"></div>

                </div>  

                <div class="text-center">

                    <a class="my-btn btn-red" href="" id="next-tab">

                        SAVE

                    </a>

                </div>                  

              </div>

            </div>

            </div>

                <!-- End Delivery box -->

                
                <!-- <div class="next-tab-area">

                    <a class="my-btn btn-red" href="javascript:void(0);" id="next-tab" onclick="validateTab('#subscriptions')">

                        SAVE

                    </a>

                </div>-->
      </div>`
      $('#payment_acceptance').after(subscriptionContent);
      getPlanData('Settings');
      var script = document.createElement('script');
        script.onload = function () {
            //do stuff with the script
            var stripe = Stripe('pk_test_51INpZ6LpiOi48zknh0lXElbb6kJGlYOfrhrnf4TkpVAXFmkWynQJzIo38kVyjFP7oi1x6lbe3oioCmSjxVCQaHTV00hXbGEhX0');
            var elements = stripe.elements();
            var card = elements.create('card', { hidePostalCode: true, style: style });
          var style = {
            base: {
              'lineHeight': '1.35',
              'fontSize': '1.11rem',
              'color': '#495057',
              'fontFamily': 'apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif'
            }
          };
          if ($('#card-element').length) {
            card.mount('#card-element');
            }

            // Create a token or display an error the form is submitted.
            var submitButton = document.getElementById('paynowPackage');
            if (submitButton) {
                submitButton.addEventListener('click',
                    function(event) {
                        event.preventDefault();
                        $("#paynowPackage").attr("disabled", "disabled");
                        stripe.createToken(card).then(function(result) {
                            if (result.error) {
                                // Inform the user if there was an error
                                var errorElement = document.getElementById('card-errors');
                                errorElement.textContent = result.error.message;

                            // $("#payNowButton").removeAttr("disabled");
                            } else {
                                console.log(result.token.card);
                                console.log(result.token.id)
                                
                                subscribe(card, stripe)
                                

                                // Send the result.token.id to a php file and use the token to create the subscription
                            // SubscriptionManager.PayNowSubmit(result.token.id, e);
                            }
                        });

                    });
            } 
          
        }
            script.src = "https://js.stripe.com/v3/";

            document.head.appendChild(script); //or something of the likes

                  // Create an instance of the card Element
            $('#card-element').css("width", "30em");

      $("#address-form .my-btn").click(function ()
      {
        getAddressData();
      })

    //   jQuery("#paynowPackage").click(function(){

    
    //  });
    
    jQuery("#subscriptionstab").click(function(){
       

      $(this).attr("data-toggle", "tab")
 
    });
     jQuery("#cancelsubs").click(function(){
       

       delete_subscription($(this).attr('sub-id'));
  
     });
      
    
     jQuery("#cancel").click(function(){

      cancel_remove_subscription(2)
 
     });
      
     jQuery("#okayConfirm").click(function(){

      confirm_remove_subscription($(this))
 
     });
      
    
    };
    
      // var scriptLink = `${location.protocol}//${hostname}/user/plugins/${packageId}/scripts/scripts.js`;
     
  });

  
})();
