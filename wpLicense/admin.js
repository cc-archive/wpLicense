function showSelector() {
   document.getElementById('licenseSelector').style.display="block";
   return 0;
} // showSelector

function cancelChanges() {
   document.getElementById('working').style.display="none";
   document.getElementById('licenseSelector').style.display="none";
   document.getElementById('newlicense_name').innerHTML = "";

   return 1;
} // cancelChanges

function showWorking() {
   document.getElementById('working').style.display="block";
   // document.getElementById("license_options").innerHTML = "<em>working...</em>";
} // showWorking

function hideWorking() {
   document.getElementById('working').style.display="none";
}

function retrieveQuestions() {
  cmbLC = document.getElementById("licenseClass");
  x_getLicenseQuestions(cmbLC.value, retrieveQuestions_cb);
} // retrieveQuestions

function retrieveQuestions_cb(result) {
   document.getElementById("license_options").innerHTML = result;
   hideWorking();
   updateLicense();
} // retrieveQuestions_cb

function selectClass() {
  showWorking();
  retrieveQuestions();
} // selectClass

function updateLicense() {
  showWorking();
  lic_opts = document.getElementById("license_options");
  // collect the question answers into an array
  var answers = new Array();
  var input_fields = document.getElementsByTagName("select");

  i = 0;
  for (i = 0; i < input_fields.length; i++) {
        // see if this is a license question
        if (!input_fields.item(i).hasAttribute("lic_q")) {continue; }
        answers[answers.length] = input_fields.item(i).getAttribute("id") + ":" +  input_fields.item(i).value;

  } // for each child node

  answers = answers.join();
  // call the server side license issue function
  x_getLicense(document.getElementById("licenseClass").value, answers, updateLicense_cb);
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
   document.getElementById("newlicense_name").innerHTML = href_text;

   hideWorking();
} // updateLicense_cb

