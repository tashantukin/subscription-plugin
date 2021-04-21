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
  var stripePubKey;
  var plan_amount;
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

  
  function appendScript() {
    var validationScript = `<script>
    

  var callbackOnboardCustom = function (result) {
    if (result === "Success") {
      //  window.location = '/user/item/list';
      console.log('success');
      $('#subscriptionstab').click();
    } else {
        toastr.error('Failed to onboard as a merchant. You may have used this account already in another marketplace.', 'Oops! Something went wrong.');
    }
};
   
function ValidateCustom(target, targetTabIndex, isNext, optionalSkipDelivery, isSpaceTime, isMerchant) {
  var checkTab;
  var check;
  target = $(target);
  var targetTab = $(target).attr('href');
  var activeTabIndex = $("#setting-tab li.active").index();

  while (targetTabIndex !== activeTabIndex) {
      var activeLi = $("#setting-tab li")[activeTabIndex];
      if (targetTabIndex > activeTabIndex) {
          checkTab = $(activeLi).next('li').children('a').attr("href");
          check = $(activeLi).next('li').children('a');
          if (!ValidateTabCustom(activeLi, targetTab, target, checkTab, check, optionalSkipDelivery, isSpaceTime, isMerchant))
              return false;
          activeTabIndex++;
      } else {
          checkTab = $(activeLi).prev('li').children('a').attr("href");
          check = $(activeLi).prev('li').children('a');
          if (!ValidateTabCustom(activeLi, targetTab, target, checkTab, check, optionalSkipDelivery, isSpaceTime, isMerchant))
              return false;
          activeTabIndex--;
      }
  }
  if (isNext && targetTabIndex === activeTabIndex) {
      $(target).trigger('click');
  }

  $('#setting-tab a[href="' + target + '"]').tab('show');
}
  
 function ValidateTabCustom(activeLi, targetTab, target, nextTab, next, optionalSkipDelivery, isSpaceTime, isMerchant) {
  var validate = false;
  var isUpdateUser = false;
  var checkTab = $(activeLi).children('a').attr("href");
  var check = $(activeLi).children('a');
  var activeTab = $(activeLi).children('a').attr("href");
  var active = $(activeLi).children('a');
  var continueTab = target;
  var data = {};
  var url = null;
  var initialDisplayName = $('#hdn-DisplayName').val();
  var initialFirstName = $('#hdn-FirstName').val();
  var initialLastName = $('#hdn-LastName').val();
  var initialNotificationEmail = $('#hdn-NotificationEmail').val();
  var initialContactNumber = $('#hdn-ContactNumber').val();
  var initialDescription = $('#hdn-Description').val();
  var initialTimeZoneId = $('#hdn-TimeZone').val();
  var initialSellerLocation = $('#hdn-SellerLocation').val();

  $(".next-tab-area").show();

  switch (checkTab) {
     
      case '#payment_acceptance':

          var errorMessage = 'You need to link the mandatory payment method before proceeding.';

          if ($(".payment-input").length === 0) {
              validate = true;
              toastr.error(errorMessage, 'Oops! Something went wrong.');
              return false;
          }

          $(".payment-input").each(function (d) {
              var self = $(this);

              var isMandatory = self.find("#isMandatory");
              var isLinked = self.find(".isLinked");
              var isVerified = self.find("#isVerified");

              if (isMandatory.val() === "true" && (isLinked.html() === 'No account linked yet' || isVerified.val() === "false")) {
                  validate = true;
                  toastr.error(errorMessage, 'Oops! Something went wrong.');
                  return false;
              } else {
                  if ($('#ENABLE_DELIVERY_PICKUP').val() === "true")
                      url = '/Settings/GetShippingMethod';
                  else
                      url = continueTab.length === 0 ? '/Settings/OnboardMerchant' : url;
              }
          });

          break;


      case '#delivery_method':
        

          if (optionalSkipDelivery == null) { optionalSkipDelivery = false }

          if (optionalSkipDelivery) {
              
              url = continueTab.length === 0 ? '/Settings/OnboardMerchant' : url;
          } else {
              if ($('.delivery-inner').find('.delivery-row').length > 0 || $('.location-inner').find('.delivery-row').length > 0) {
                  url = continueTab.length === 0 ? '/Settings/OnboardMerchant' : url;
              } else {
                  validate = true;
                  toastr.error('Please enter atleast one Delivery service or Pick up location.', 'Oops! Something went wrong.');
              }
          }

          break;
  }
  if (!validate) {
      if (url != null) {
          if (url.toLowerCase() === '/settings/onboardmerchant') {
              let token = $('input[name="__RequestVerificationToken"]').val();
              data['__RequestVerificationToken'] = token;
              $.ajax({
                  url: url,
                  type: 'POST',
                  data: data,
                  success: function (result) {
                    callbackOnboardCustom(result);
                  },
                  error: function (xhr, status, ex) {
                      
                  }
              });
          } else if ($('#ENABLE_DELIVERY_PICKUP').val() === "true" && url.toLowerCase() === '/settings/getshippingmethod') {
              $.ajax({
                  url: url,
                  type: 'GET',
                  success: function (result) {
                      callbackGetShippingMethod(result, isSpaceTime);
                  },
                  error: function (xhr, status, ex) {

                  }
              });

              $.ajax({
                  url: '/Settings/GetPickupAddress',
                  type: 'GET',
                  success: function (result) {
                      callbackGetPickupAddress(result);
                  },
                  error: function (xhr, status, ex) {

                  }
              });
          } else if (url.toLowerCase() === '/settings/updateuser') {
              let token = $('input[name="__RequestVerificationToken"]').val();
              data['__RequestVerificationToken'] = token;
              $.ajax({
                  url: url,
                  type: 'POST',
                  data: data,
                  success: function (result) {
                      if ($('#ENABLE_DELIVERY_PICKUP').val() === "true") {
                          GetAddress();
                      }
                  },
                  error: function (xhr, status, ex) {

                  }
              });

          } else if ($('#ENABLE_DELIVERY_PICKUP').val() === "true" && url === '') {
              GetAddress();
          }
      }
      $(next).attr('data-toggle', 'tab');
      $(active).removeAttr('data-toggle');
      if (url === 'loadPaymentTab') {
          if ($(next).length === 1) {
              $('.nav-tabs a[href="#payment_acceptance"]').tab('show');
              return false;
          } else {
              window.location = "/user/marketplace/index";
          }
      }
      return true;
  } else {
      $('.nav-tabs a[href="' + checkTab + '"]').tab('show');
      return false;
  }
}
  
    </script>`;

    $('body').append(validationScript); 
  }
  function displayError(event) {
  // changeLoadingStatePrices(false);
    let displayError = document.getElementById('card-errors');
    if (event.error) {
      displayError.textContent = event.error.message;
    } else {
      displayError.textContent = '';
    }
  }
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
     
        var startDate = new Date(result.start_date * 1000); 
        var currentDate = moment(new Date()).format('DD/MM/YYYY');
        var startDateMoment = moment(startDate, 'DD.MM.YYYY HH:mm') //.format('DD.MM.YYYY HH:mm');
        var endDate =new Date(result.end_date * 1000); 
          //moment(result.end_date, 'DD.MM.YYYY HH:mm')
        console.log(startDateMoment);
        var endDateMoment = moment(endDate, 'DD.MM.YYYY HH:mm')
        console.log(endDate);
        console.log(moment(startDateMoment,'DD.MM.YYYY HH:mm').isSameOrBefore(endDateMoment, 'day'));

        var endDateMoment2 = moment(endDate).format('DD/MM/YYYY');
        var startDateMoment2 = moment(startDate).format('DD/MM/YYYY');
        plan_id = result.id;
        plan_amount = parseFloat(result.price).toFixed(2);
        //billing starts on --
        $('#billingstart').text(currentDate);
        $('#subs-name').text(result.name);
        $('#subs-desc').text(result.description)
        $('#package-name').text(result.name);
        $('.package-price span').first().text(`USD $${parseFloat(result.price).toFixed(2)}`);


        if (result.status == 'active' || (result.status == 'canceled' && moment(startDateMoment).isSameOrBefore(endDate, 'day'))) {

          isSubscriptionValid = 1;

          //verify if the user is merchant

          console.log($('.navigation .dropdown a').attr('href'));

          if ( $('.navigation .dropdown a').attr('href') != 'user/marketplace/be-seller')
          {
            console.log('merchant page')
            if (page == 'Settings') {
              $('#cancelsubs').attr("sub-id", result.sub_id);
              $('#subscription-name').text(result.name);
              $('.subscription-step1').addClass('hide');
              $('.subscription-step2').removeClass('hide');
              var status = result.status == 'canceled' ? 'Cancelled' : result.status;
              $('#status').text(status);
              $('#nxtbilling').text(endDateMoment2);
          
            }
            
          } 


        }
        else {
  
          $('.header.user-login .dropdown .seller-nav.dropdown-menu').hide()
          if (page != 'Settings') {
            console.warn('in else');
        
          urls = `${protocol}//${baseURL}/user/marketplace/seller-settings`;
          window.location.href = urls;
         }
          
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

  function saveSubscriptionData(result)
  {
    var apiUrl = packagePath + '/saveSubscriptionData.php';
    var data = {
      'id': result.id,
      'status': result.status,
      'start_date': result.current_period_start,
      'end_date' : result.current_period_end

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
      var data = { 'customer_id': customerId,  'payment_id' : paymentId ,'coupon_id' : $('#couponcode').val()}
      $.ajax({
          url: apiUrl,
          
        method: 'POST',
              contentType: 'application/json',
                data: JSON.stringify(data),
        success: function(result) {
          result = JSON.parse(result);
          saveSubscriptionData(result.result);
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

  function validateCoupon(id)
  {
    var apiUrl = packagePath + '/getCoupon.php';
      var data = { 'id': id  }
      $.ajax({
          url: apiUrl,
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function(result) {
        result = JSON.parse(result);
          console.log(result);
          var discountAmount = 0;
          var isPercent = false;
          discountAmount = result.result.amount_off != null ? parseFloat(result.result.amount_off/100).toFixed(2) : (result.result.percent_off, isPercent = true);
          
          console.log(`plan amount ${plan_amount}`);
          console.log(`discount amount ${discountAmount}`);

          console.log(isPercent);
          if (isPercent) {
            console.log('ispercent');
            discountAmount = parseFloat((plan_amount * discountAmount) / 100).toFixed(2);
          }

          console.log(`plan amount ${plan_amount}`);
          console.log(`discount amount ${discountAmount}`);

          $('#discountAmount').text(`USD $${discountAmount}`);
          var total = parseFloat(plan_amount - discountAmount).toFixed(2);
          console.log(`total ${total}`);

          $('.package-price span').first().text(`USD $${total}`);

          // if (result.result.status == 'canceled') {
          //   saveSubscriptionData(result.result.id, result.result.status);

          // }
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

    //home page upon logging in
    if ($('body').hasClass('page-home')) {


      getPlanData('Homepage');
    }
  
    if (pathname.indexOf('/user/marketplace/seller-settings') > -1 || pathname.indexOf('/user/marketplace/be-seller') > -1) {
     
      appendScript();

      if ($('#maketplace-type').val() == 'spacetime') {
        
        //skip delivery button 
        // delivery-btn-skip
        var onclickAttrskipdel = $('.delivery-btn-skip').attr('onclick');
        onclickAttrskipdel = onclickAttrskipdel.replace("Validate", "ValidateCustom");
        $('.delivery-btn-skip').attr('onclick', onclickAttrskipdel);

        //delivery button Save
        $('#delivery_method #next-tab').text('NEXT');
        var onclickAttrdel = $('#delivery_method .next-tab-area #next-tab').attr('onclick');
        onclickAttrdel = onclickAttrdel.replace("Validate", "ValidateCustom");
        $('#delivery_method  .next-tab-area #next-tab').attr('onclick', onclickAttrdel);
        
      }
      
      else {
        $('#payment_acceptance #next-tab').text('NEXT');
        var onclickAttr = $('#payment_acceptance .next-tab-area #next-tab').attr('onclick');
        onclickAttr = onclickAttr.replace("Validate", "ValidateCustom");
        $('#payment_acceptance .next-tab-area #next-tab').attr('onclick', onclickAttr);
        
      
      }
        //redirect Save button to item details page
        // var itemsUrl = `${protocol}//${baseURL}/user/item/list`;
        // $('#subscriptions #next-tab').attr('href', itemsUrl);

      //next button
      // $('#address #next-tab').on('click', function(e){
       
     

        
      //   var attrbts = $('#payment_acceptance #next-tab').prop("attributes");
      //   // loop through element1 attributes and apply them on element2.
      //   $.each(attrbts, function() {
      //     $('#subscriptions #next-tab').attr(this.name, this.onclick);
          
      //   });
      //   $('#payment_acceptance #next-tab').removeAttr('onclick');
      // });
     
      // $('#payment_acceptance #next-tab').on('click', function(e){
      //   // e.preventDefault();
      //   // e.stopImmediatePropagation();
      //   // return false;
      //   
       
     // });

     // $('#payment_acceptance #next-tab').removeAttr('onclick');
    
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

                                        <input class="required" id="couponcode" name="first-name" type="text" value="">

                                    </div>

                                    <div class="col-md-3">

                                        <label>&nbsp;</label>

                                        <div class="atag-color">

                                            <a id="apply" href="javascript:void(0);">Apply</a>

                                        </div>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                                <div class="row">

                                    <div class="col-md-9">

                                        <p class="grey-colot-txt">Discount amount</p>

                                    </div>

                                    <div class="col-md-3 text-right">

                                        <a href="javascript:void(0);"id="discountAmount">USD $0.00</a>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                                <hr>

                                <div class="row">

                                    <div class="col-md-7">

                                        <p class="grey-colot-txt"><strong>Total amount</strong></p>

                                    </div>

                                    <div class="col-md-5 package-price">

                                        <a href="javascript:void(0);"><strong><span>USD $15.00</span> / <span>Month</span></strong></a>

                                    </div>

                                    <div class="clearfix"></div>

                                </div>

                            </div>  

                            </form>

                            <div class="text-center">

                                <a class="my-btn btn-red" id="paynowPackage" href="javascript:void(0);">PAY NOW</a>

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

                    <a class="my-btn btn-red" href="javascript:void(0)"  id="next-tab">

                        SAVE

                    </a>

                </div>                  

              </div>

            </div>

            </div>

                <!-- End Delivery box -->

                
                <!-- <div class="next-tab-area">

                    // <a class="my-btn btn-red" href="javascript:void(0);" id="next-tab" onclick="validateTab('#subscriptions')">

                    //     SAVE

                    // </a>

                </div>-->
      </div>`
      $('#payment_acceptance').after(subscriptionContent);
      getPlanData('Settings');
      
      var script = document.createElement('script');
      script.onload = function ()
      {
        getMarketplaceCustomFields(function(result) {
          $.each(result, function(index, cf) {
            
              if (cf.Name == 'stripe_pub_key' && cf.Code.startsWith(customFieldPrefix)) {
               stripePubKey = cf.Values[0];
              }
          })

            if (stripePubKey) {
              //do stuff with the script
              var stripe = Stripe(stripePubKey);
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
              
              card.on('change', function (event) {
                displayError(event);
              });
            }
        });
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
      
      
      
     $(".subscription-step2 #next-tab").on('click', function ()
     {
         window.location = '/user/item/list';
     })

     $("#apply").click(function ()
     {
       console.log('apply clicked')
     
      if ($('#couponcode').val() != '') {

         validateCoupon($('#couponcode').val())
      }
         
     })

    
    };
  
  });

  
})();
