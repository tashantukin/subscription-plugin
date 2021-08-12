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
  const protocol = window.location.protocol;
  const token = getCookie('webapitoken');
  const baseURL = window.location.hostname;
  var day, month, year;
  const url = window.location.href.toLowerCase();

  var timezone_offset_minutes = new Date().getTimezoneOffset();
      timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
  function getCookie(name){
  var value = '; ' + document.cookie;
  var parts = value.split('; ' + name + '=');
  if (parts.length === 2) {
      return parts.pop().split(';').shift();
  }
}

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

  function english_ordinal_suffix(dt)
  {
    return dt.getDate()+(dt.getDate() % 10 == 1 && dt.getDate() != 11 ? 'st' : (dt.getDate() % 10 == 2 && dt.getDate() != 12 ? 'nd' : (dt.getDate() % 10 == 3 && dt.getDate() != 13 ? 'rd' : 'th'))); 
  }
    
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
                
                var startdate = dateDetails.start == null ? "" : new Date(dateDetails.start * 1000).format(`${day}/${month}/${year}`);
               if (dateDetails.start != null) {
                  if (day == 'do') {
                    var ordinal = english_ordinal_suffix(new Date(dateDetails.start * 1000));
                    startdate = startdate.substring(startdate.indexOf("/") + 1);
                    startdate = `${ordinal}/${startdate}`;
                    console.log('here')
                  }
                }
                  
                var enddate = dateDetails.end == null ? "" : new Date(dateDetails.end * 1000).format(`${day}/${month}/${year}`);
                if (dateDetails.end != null) {
                  if (day == 'do') {
                    var ordinal = english_ordinal_suffix(new Date(dateDetails.end * 1000));
                    enddate = enddate.substring(enddate.indexOf("/") + 1);
                    enddate = `${ordinal}/${enddate}`;
                    console.log('here')
                  }
                }

                var separator = dateDetails.start == null ? "" : '-';
                el.text(`${startdate} ${separator} ${enddate}`);
              
			},
			error: function (jqXHR, status, err)
			{
				// toastr.error('Error!');
			}
		});
  }
  

  function getDateFormat()
  {
    axios({
      method: "GET",
      url: `${protocol}//${baseURL}/api/v2/marketplaces/`,
      headers: {
        'Authorization': `Bearer ${token}`
      }
    }).then((response) =>
    {
      var dateFormat = response.data.Settings['features-control']['date-format-settings'];

     day = dateFormat['day'].toLowerCase();
     month = dateFormat['month'].toLowerCase();
     year = dateFormat['year'].toLowerCase();

      console.log(dateFormat);

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

  function appendColumns()
  {
    getDateFormat();
          //append the header
        
  }
  $(document).ready(function ()
  {
    
    if ($('body').hasClass('user-page')) {
        
      var axiosCDN = `<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.js"></script>`;
      $('body').append(axiosCDN);

        waitForElement("#no-more-tables", function ()
        {
          appendColumns();
        })
      
      
       //filter
       $('#search_btn').on('click', function (e)
       {
        waitForElement("#no-more-tables", function ()
        {
          appendColumns();
        })
      
       })
      
      //enter key

      $(document).on('keypress',function(e) {
        if(e.which == 13) {
          waitForElement("#no-more-tables", function ()
        {
          appendColumns();
        })

        }
    });
      
      
      
    }

  //setting the local timezone to local storage upon login
    
    if (pathname.indexOf('/admin/dashboard/index') > -1) {
     
      localStorage.setItem("timezone_offset", timezone_offset_minutes);


      $.ajax({  
        type: "POST",  
        url:  packagePath + '/customers.php',  
        data: { storageValue: localStorage.getItem("timezone_offset") }
         });
    
      console.log('timezone set');

    }


                         
  });
})();
