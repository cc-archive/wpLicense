// $Id: client.js 2769 2006-01-05 19:25:05Z nyergler $
// $Date: 2006-01-05 14:25:05 -0500 (Thu, 05 Jan 2006) $
//
// client-side functions for the CC AJAX license chooser.
//
// copyright 2005, Nathan R. Yergler, Creative Commons
// licensed under the MIT License.
// see docs/LICENSE for more information.

// WS_ROOT_URL should point to the location of ws_proxy.php on your server
// For example:
// WS_ROOT_URL = 'http://yergler.net/cc_ajax/wx_proxy.php'; 
//
// Note that due to browser security restrictions, this *must* be in the same
// domain your license chooser is served from.

WS_ROOT_URL = 'http://localhost/cc_ajax/ws_proxy.php'; 

// retrieve the list of license classes from the web service 
// and populate the license class drop-down
function loadClasses() {

    function updateUi(responseData) {
	// get a handle to the <select> tag
	var select = $("#licenseClass");
	select.empty();

	// iterate over the license classes
	$("//license", responseData).each( function() {

		var option = $("<option>" + this.text() + "</option>");
		option.id(this.id());

		select.append(option);

	});	

    } // updateUi

    $.ajax({
	url: WS_ROOT_URL,
	type: "GET",
	dataType: "xml",
	success: updateUi,
	});

} // loadClasses


function showSelector(event) {
   // clear the questions div, just in case
   $("#license_options").empty();

   // show the license class selector
   $("#licenseSelector").css("display", "block");

   return true;
} // showSelector

function cancelChanges() {

   // hide the selector
   $("#licenseSelector").css("display", "none");
   $("#working").css("display", "none");
   $("#newlicense_name").empty();

   document.license_options.reset();

   return true;
} // cancelChanges

function showWorking() {

   $("#working").css("display", "block");
   $("#choose").attr("disabled", "true");

} // showWorking

function hideWorking() {

   $("#working").css("display", "none");
   $("#choose").attr("disabled", "false");
}

function retrieveQuestions() {

  license_class = $("#licenseClass").val();
  showWorking();

  $("#license_options").load(WS_ROOT_URL,
	{func:'questions',
	 class:license_class},
	function() {
	   updateLicense();
	   updateBindings();
	}
   );

} // retrieveQuestions

function updateLicense() {
  lic_opts = document.getElementById("license_options");

  // collect the question answers into an array
  var answers = new Array();
  var input_fields = document.getElementsByTagName("select");

  showWorking();

  i = 0;
  for (i = 0; i < input_fields.length; i++) {
        // see if this is a license question
        if (!input_fields.item(i).hasAttribute("lic_q")) {continue; }
        answers[answers.length] = input_fields.item(i).getAttribute("id") + ":" +  input_fields.item(i).value;

  } // for each child node

  answers = answers.join();
  license_class = $("#licenseClass").val();

  // call the server side license issue function
  $.ajax({
	url: WS_ROOT_URL,
	type: "POST",
	data: "func=issue&class=" + license_class + "&answers=" + answers,
	success: function(result) {
			hideWorking();
			updateLicense_cb(result);
		 }
	});

} // updateLicense

function updateLicense_cb(result) {

   // explode the returned string back into an array
   var licenseInfo = new Array();
   pairs = result.split(';');

   for (i=0; i < pairs.length; i++) {
      item_info = pairs[i].split(':');
      key = item_info.shift();
      licenseInfo[key] = item_info.join(':');
   } // for each pair


   // update the form
   document.license_options.license_name.value = licenseInfo['name'];
   document.license_options.license_uri.value  = licenseInfo['uri'];
   document.license_options.license_rdf.value  = licenseInfo['rdf'];
   document.license_options.license_html.value = licenseInfo['html'];

   // construct and assign the html link
   href_text = '<a href="' + licenseInfo['uri'] + '">' + licenseInfo['name'] + '</a>';

   $("#newlicense_name").empty().append($(href_text));

} // updateLicense_cb


function updateBindings() {

   $("#licenseClass").change(retrieveQuestions);
   $("#cancel").click(cancelChanges);
   $("#license_options > select").change(updateLicense);
   $("select[@lic_q]").change(updateLicense);

}

$(document).ready(updateBindings);
