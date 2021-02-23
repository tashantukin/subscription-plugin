(function () {
  /* globals $ */
  var scriptSrc = document.currentScript.src;
  var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
  var packageId = re.exec(scriptSrc.toLowerCase())[1];
  var packagePath = scriptSrc.replace("/scripts/scripts.js", "").trim();
  var customFieldPrefix = packageId.replace(/-/g, "");
  const HOST = window.location.host;
  var hostname = window.location.hostname;
  var urls = window.location.href.toLowerCase();
  var userId = $("#userGuid").val();
  function waitForElement(elementPath, callBack) {
    window.setTimeout(function () {
      if ($(elementPath).length) {
        callBack(elementPath, $(elementPath));
      } else {
        waitForElement(elementPath, callBack);
      }
    }, 500);
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

  function deleteCart() {
    var data = { userId: userId };
    var apiUrl = packagePath + "/delete_cart.php";
    $.ajax({
      url: apiUrl,
      method: "POST",
      contentType: "application/json",
      data: JSON.stringify(data),
      success: function (response) {
        location.reload();
      },
      error: function (jqXHR, status, err) {},
    });
  }

  $(document).ready(function () {
    getMarketplaceCustomFields(function (result) {
      $.each(result, function (index, cf) {
        if (cf.Name == "Delete Cart" && cf.Code.startsWith(customFieldPrefix)) {
          code = cf.Code;
          pluginStatus = cf.Values[0];
          console.log(pluginStatus);
          if (pluginStatus == "true") { 
            if (userId) {
              $(".header .main-nav ul .cart-menu .cart-item-counter").append(
                '<span class="cart-delete-item"></li>'
              );
              var imgLink =
                "http://" +
                hostname +
                "/user/plugins/" +
                packageId +
                "/images/delete.svg";
              var img = document.createElement("img");
              img.src = imgLink;
              $(".header .main-nav ul .cart-delete-item").append(img);
              $(".cart-delete-item img").addClass("delete");
              $(".cart-menu .cart_anchor").append($(".cart-delete-item"));
            }
          }
        }
      });
    });

    $(document).on("click", ".delete", function () {
      if (confirm("Are you sure you want empty your cart?")) {
        deleteCart();
      }
    });
  });
})();
