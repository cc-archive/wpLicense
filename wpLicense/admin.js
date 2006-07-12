function getBlogUrl() {
   return document.getElementById('blog_url').value;
} // getBlogUrl

function showSelector() {
   new Effect.BlindDown('licenseSelector');
   //document.getElementById('licenseSelector').style.display="block";
   // return 0;
} // showSelector

function cancelChanges() {
   new Effect.DropOut('licenseSelector');
   document.getElementById('working').style.display="none";
   // document.getElementById('licenseSelector').style.display="none";
   document.getElementById('newlicense_name').innerHTML = "";

   return 1;
} // cancelChanges

function showWorking() {
   document.getElementById('working').style.display="block";

} // showWorking

function hideWorking() {
   document.getElementById('working').style.display="none";
}

function retrieveQuestions() {
  cmbLC = document.getElementById("licenseClass");

  blog_url = getBlogUrl() + '/wp-content/plugins/wpLicense/admin.php';

  ajax = new sack(blog_url);
  ajax.element='license_options';
  ajax.onLoaded = function() {showWorking();};
  ajax.onCompletion = function() {updateLicense(); hideWorking(); Behaviour.apply(); };
  ajax.setVar('func', 'questions');
  ajax.setVar('class', cmbLC.value);

  ajax.runAJAX();
} // retrieveQuestions

function selectClass() {
  retrieveQuestions();
} // selectClass

function updateLicense() {
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
  blog_url = getBlogUrl() + '/wp-content/plugins/wpLicense/admin.php';

  ajax = new sack(blog_url);
  ajax.onCompletion = function() {updateLicense_cb(ajax.response); };
  ajax.setVar('func', 'issue');
  ajax.setVar('class', document.getElementById("licenseClass").value);
  ajax.setVar('answers', answers);

  ajax.runAJAX();

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

} // updateLicense_cb


/* Register Javascript Rules */
var adminRules = {
	'#showLicenseChooser' : function(el){
		el.onclick = function(){
                        showSelector();
		} // onclick
	}, // showLicenseChooser
	'#removeLicense' : function(el){
             el.onclick = function(){
                msg = 'This will remove your current license selection; are you sure?';
                if (confirm(msg)) {
                   document.license_options.remove_license.value = '__remove';
                   document.license_options.submit();
                } 
             } // onclick
	}, // removeLicense
        '#licenseClass' : function (el) {
             el.onchange = function() {
                selectClass();
             } // onchange
        }, // licenseClass
        '#cancel' : function (el) {
             el.onclick = function() {
                cancelChanges();
             } // onclick
        }, // #cancel
        '#license_options select' : function (el) {
             el.onchange = function() {alert ('foo');
                updateLicense();
             } // onchange
        }, // #lic_questions select
        'select.lic_q' : function (el) {
             el.onchange = function() {alert ('foo');
                updateLicense();
             } // onchange
             el.onfocus = function() { alert ('bar'); }
        }, // #lic_questions select
     };

Behaviour.register(adminRules);
