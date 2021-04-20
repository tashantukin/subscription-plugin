(function ()
{
  var scriptSrc = document.currentScript.src;
  var pathname = (
    window.location.pathname + window.location.search
  ).toLowerCase();
  var packagePath = scriptSrc.replace("/scripts/scripts.js", "").trim();
  console.log(packagePath);
  var re = /([a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12})/i;
  var packageId = re.exec(scriptSrc.toLowerCase())[1];
  const HOST = window.location.host;
  
  const url = window.location.href.toLowerCase();
  //get coupon value to display in Admin transaction details page

  function waitForElement(elementPath, callBack)
  {
    window.setTimeout(function ()
    {
      if ($(elementPath).length) {
        callBack(elementPath, $(elementPath));
      } else {
        waitForElement(elementPath, callBack);
      }
    }, 500);
  }
  const formatter = new Intl.NumberFormat("en-US", {
    minimumFractionDigits: 2,
  });
    
    function getUserCustomfields(id, el)
    {
        
		var data = { 'userguid': id };
		var apiUrl = packagePath + '/get_customfields.php';
		$.ajax({
			url: apiUrl,
			method: 'POST',
			contentType: 'application/json',
			data: JSON.stringify(data),
			success: function (result)
            {

                var dateDetails = result != null || result != "" ? JSON.parse(result) : null;
                
                var startdate = dateDetails.start == null ? "" : new Date(dateDetails.start * 1000).format("dd/mm/yyyy");
                var enddate = dateDetails.end == null ? "" : new Date(dateDetails.end * 1000).format("dd/mm/yyyy");
                var separator = dateDetails.start == null ? "" : '-';
                el.text(`${startdate} ${separator} ${enddate}`);
              
			},
			error: function (jqXHR, status, err)
			{
				// toastr.error('Error!');
			}
		});
    }
  $(document).ready(function ()
  {
    //   if (url.indexOf("/admin/usermanager/") >= 0) {

      if ($('body').hasClass('user-page')) {
          console.log('userpage')

            waitForElement("#no-more-tables", function ()
            {

              //append the header
              
              var billing = `<th data-column="6" tabindex="0" scope="col" role="columnheader" aria-disabled="false" aria-controls="no-more-tables" unselectable="on" aria-sort="none" aria-label="User Type: No sort applied, activate to apply an ascending sort" style="user-select: none;"><div class="tablesorter-header-inner">Billing Cycle</div></th>`;
              $('#no-more-tables thead tr th:nth-child(5)').after(billing);
              $('#no-more-tables thead tr th:nth-child(7)').attr('data-column', '7');
              $('#no-more-tables thead tr th:nth-child(8)').attr('data-column', '8');
              $('#no-more-tables thead tr th:nth-child(9)').attr('data-column', '9');

              $("tbody tr:not(.loaded)").each(function ()
              {
                  var userguid = $(this).attr('data-guid');
                  var newTd = `<td><a class="btn-details" id="billingcycle"></a></td>`;
                  $('td:nth-child(5)', $(this)).after(newTd);
                  getUserCustomfields(userguid, $('#billingcycle', $(this)))
                  $(this).addClass('loaded');
              
              });
            })
          }
                  
            
  });
})();