(function() {
var scriptSrc = document.currentScript.src;
var packagePath = scriptSrc.replace('/scripts/package.js', '').trim();
var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
var packageId = re.exec(scriptSrc.toLowerCase())[1];
document.addEventListener('DOMContentLoaded', function () {
const HOST = window.location.host;
var customFieldPrefix = packageId.replace(/-/g, "");
var userId = $('#userGuid').val();
var accessToken = 'Bearer ' + getCookie('webapitoken');
var rrpStatusExist = false;
var rrpStatusFieldId = 0;
var code = "";
var timezone_offset_minutes = new Date().getTimezoneOffset();
    timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
//switch

  
function validatePK(pKey,el) {
  $('.error').text('');
  el.removeClass('error-con');
 try {
      var stripe = Stripe(pKey);
      stripe.createToken('pii', {personal_id_number: 'test'})
          .then(result =>
          {
          //console.log(result);
          if (result.token) {
          
          }
          // public key is valid :o)
          else {
          //e = true;
          el.addClass('error-con');
          $('.error').text('Invalid Publishable key provided.');
          }
          
      })
  }catch(err){
     // console.log('err ' + err);
      el.addClass('error-con');
      $('.error').text('Please provide Publishable key instead.');
  }
  
  }
function validateSK(sKey, el)
  {
    $('.errorSecret').text('');
    el.removeClass('error-con');
    var data = { 'secret_key' : sKey};
    console.log(data);
    var apiUrl = packagePath + '/validate_secretkey.php';
   $.ajax({
       url: apiUrl,          
       method: 'POST',
       contentType: 'application/json',
       data: JSON.stringify(data),
       success: function($result) {
        // console.log($result);
         var isvalid = JSON.parse($result);
        // console.log(isvalid);
        // console.log(isvalid.result.code);
        
         if (isvalid.result != 'Valid') {

           if (isvalid.result.code == 'secret_key_required') {
              el.addClass('error-con');
              $('.errorSecret').text('Please provide Secret key instead.');
            } else {
              //console.log()
              el.addClass('error-con');
              $('.errorSecret').text('Invalid Secret key provided.');
             }
          
          }
        
       },
       error: function ($result) {
          
       }
   });
}
function MakeConnectSubscriptionUnedit(plan_type, plan_id) {

  var e = false;
  jQuery("#connect-subscription-marketplace .required").each(function () {
      var val = jQuery(this).val();
      var attr = jQuery(this).attr('id');
      if (jQuery.trim(val) == '')
      {
          e = true;
          jQuery(this).addClass('error-con');
      }

  });
  if ($('#package_name').val().length > 30)
  {
      e = true;
      jQuery('#package_name').addClass('error-con');
  }
  if (!e)
  {
     savePackageDetails(plan_type,plan_id);
      jQuery("#package_name").prop("readonly", true);
      jQuery("#price_per_month").prop("readonly", true);
      jQuery("#subscription-details").prop("readonly", true);

      jQuery("#connect-save-btn").hide();
      jQuery("#connect-edit-btn").show();
  }

}
function MakeUneditable() {
  var e = false;
  jQuery("#live_secret_key .required").each(function () {
      var val = jQuery(this).val();
      var attr = jQuery(this).attr('id');
      if (jQuery.trim(val) == '')
      {
          e = true;
          jQuery(this).addClass('error-con');
      }
  });

 
  if (!$(".error-con").length)
  {
      saveKeys();
      jQuery("#live-secret-key").prop("readonly", true);
      jQuery("#live-publishable-key").prop("readonly", true);
      jQuery("#save-btn").hide();
      jQuery("#edit-btn").show();
  }
}

function getMarketplaceCustomFields(callback){
  var apiUrl = '/api/v2/marketplaces'
  $.ajax({
      url: apiUrl,
      method: 'GET',
      contentType: 'application/json',
      success: function(result) {
          if (result) {
              callback(result.CustomFields);
          }
      }
  });
  
}

  function saveURL() {
    var apiUrl = packagePath + '/save_custom_url.php';
   $.ajax({
       url: apiUrl,          
       headers: {
           'Authorization':  accessToken,
       },
       method: 'POST',
       contentType: 'application/json',
     //  data: JSON.stringify(data),
       success: function(response) {
          console.log(response)
         
       },
       error: function (jqXHR, status, err) {
             toastr.error('---');
       }
   });
 
  }
  
function saveKeys() {
  var data = {  'secretKey': $('#live-secret-key').val(), 'publishableKey': $('#live-publishable-key').val(), 'package_name'  :  $('#package_name').val(), 'price' : $('#price_per_month').val(), 'details' : $('#subscription-details').val() };
   var apiUrl = packagePath + '/save_keys.php';
  $.ajax({
      url: apiUrl,          
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(data),
      success: function($result) {
          console.log($result);
           toastr.success('Key is saved successfully');
       
      },
      error: function ($result) {
         
      }
  });
}
  
  function savePackageDetails(plan_type, plan_id)
  {
    var data = { 'package_name' : $('#package_name').val(), 'price': $('#price_per_month').val(), 'details': $('#subscription-details').val(), 'timezone' : timezone_offset_minutes, 'plan_type': plan_type, 'plan_id' : plan_id };
    console.log(data);
    var apiUrl = packagePath + '/save_details.php';
   $.ajax({
       url: apiUrl,          
       method: 'POST',
       contentType: 'application/json',
       data: JSON.stringify(data),
       success: function($result) {
           console.log($result);
            toastr.success('Package details are saved successfully');
        
       },
       error: function ($result) {
          
       }
   });
  }
  

  $(document).ready(function ()
  {


    //validiate PK
    $('#live-publishable-key').on('keyup', function ()
    {
      if ($(this).val()) {
        validatePK($(this).val(), $(this));
      }
    });


    $('#live-secret-key').on('keyup', function ()
    {
      if ($(this).val()) {
        validateSK($(this).val(), $(this));
      }
    });






     // MakeUneditable()
     jQuery("#live-secret-key").prop("readonly", true);
     jQuery("#live-publishable-key").prop("readonly", true);
     jQuery("#save-btn").hide();
    jQuery("#edit-btn").show();
    
    jQuery("#package_name").prop("readonly", true);
      jQuery("#price_per_month").prop("readonly", true);
      jQuery("#subscription-details").prop("readonly", true);

      jQuery("#connect-save-btn").hide();
      jQuery("#connect-edit-btn").show();

     if (document.getElementById("price_per_month") != null)
     {
         document.getElementById("price_per_month").onkeypress = function (event) {
             var charCode = document.getElementById("price_per_month").value.toString();
             if (charCode.includes("."))
             {
                 var numb = charCode.split(".")[1];
                 if (numb != null && numb.length > 1)
                 {
                     console.log("Only 2 decimal places allowed");
                     return false;
                 }
             }
         };
     }


    getMarketplaceCustomFields(function(result) {
      $.each(result, function(index, cf) {
        
          if (cf.Name == 'stripe_api_key' && cf.Code.startsWith(customFieldPrefix)) {
            var api_key = cf.Values[0];
            $('#live-secret-key').val(api_key);
          }
          if (cf.Name == 'stripe_pub_key' && cf.Code.startsWith(customFieldPrefix)) {
            var account_url = cf.Values[0];
            $('#live-publishable-key').val(account_url);
          }

          
      })
  });

    saveURL();;
    jQuery('.login-list-item .tog').click(function () {
      jQuery(this).closest('.login-list-item').toggleClass("open");
  });

  //toggle per item
  jQuery('.tog1').click(function () {
    jQuery(this).closest('.accord_item').toggleClass("open");
});


  getMarketplaceCustomFields(function(result) {
      $.each(result, function(index, cf) {
         //code here
          
      })
  });
    
  
    $("#save-btn").on("click", function ()
    {
    
      MakeUneditable()
	// 	var $apiKey = $("#live-secret-key").val();
		
	// 	if ($apiKey == ""){
	// 		$("#live-secret-key").addClass("error-con");
	// 	}
	// 	else{
	// 		$("#live-secret-key").removeClass("error-con");
	// 	}
	// 	if (!$(".error-con").length){
  //   //	updateTrialCount(trialDaysFieldId, trialDaysFieldCode, $trialCount);
  //  // saveKeys();
	// 	//toastr.success('No. of Trial Days Successfully Saved', 'Success!');
	// 	}
  });
    
    $("#connect-save-btn").on("click", function ()
    {
      
      MakeConnectSubscriptionUnedit($(this).find('#save').attr('plan-type'), $(this).find('#save').attr('plan-id'))
		// var $apiKey = $("#live-secret-key").val();
		
		// if ($apiKey == ""){
		// 	$("#live-secret-key").addClass("error-con");
		// }
		// else{
		// 	$("#live-secret-key").removeClass("error-con");
		// }
		// if (!$(".error-con").length){
    //	updateTrialCount(trialDaysFieldId, trialDaysFieldCode, $trialCount);
    
		//toastr.success('No. of Trial Days Successfully Saved', 'Success!');
		// }
  })  
    
    
    


});

function getCookie (name) {
    var value = '; ' + document.cookie;
    var parts = value.split('; ' + name + '=');
    if (parts.length === 2) return parts.pop().split(';').shift();
  }
 

});
})();
