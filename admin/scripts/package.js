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
  
  function savePackageDetails()
  {
    var data = { 'package_name' : $('#package_name').val(), 'price': $('#price_per_month').val(), 'details': $('#subscription-details').val(), 'timezone' : timezone_offset_minutes };
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
    
  
  $("#save-btn").on("click", function(){
		var $apiKey = $("#live-secret-key").val();
		
		if ($apiKey == ""){
			$("#live-secret-key").addClass("error-con");
		}
		else{
			$("#live-secret-key").removeClass("error-con");
		}
		if (!$(".error-con").length){
    //	updateTrialCount(trialDaysFieldId, trialDaysFieldCode, $trialCount);
    saveKeys();
		//toastr.success('No. of Trial Days Successfully Saved', 'Success!');
		}
  });
    
  $("#connect-save-btn").on("click", function(){
		// var $apiKey = $("#live-secret-key").val();
		
		// if ($apiKey == ""){
		// 	$("#live-secret-key").addClass("error-con");
		// }
		// else{
		// 	$("#live-secret-key").removeClass("error-con");
		// }
		// if (!$(".error-con").length){
    //	updateTrialCount(trialDaysFieldId, trialDaysFieldCode, $trialCount);
    savePackageDetails();
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
