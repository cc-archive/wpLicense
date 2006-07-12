<?php

$WS_ROOT = "http://api.creativecommons.org/rest/1.0";

function licenseClasses() {
   $uri="http://api.creativecommons.org/rest/1.0/";

   // retrieve the license 
   $cobj=curl_init($uri);
   curl_setopt($cobj,CURLOPT_RETURNTRANSFER,1);
   $xml=curl_exec($cobj);
   curl_close($cobj);

   // parse the classes into a hash
   $xmldoc = simplexml_load_string($xml);
   $l_classes = array();

   foreach ($xmldoc->xpath('//license') as $license) {
      $l_classes[(string) $license['id']] = htmlentities((string) $license);
   } // foreach

   return $l_classes;
} // licenseClasses

function licenseQuestions($lclass) {
   $uri = "http://api.creativecommons.org/rest/1.0/license/" . $lclass . "/";

   // retrieve the license 
   $cobj=curl_init($uri);
   curl_setopt($cobj, CURLOPT_RETURNTRANSFER, 1);
   $xml=curl_exec($cobj);
   curl_close($cobj);
 
   // parse the classes into a hash
   $xmldoc = simplexml_load_string($xml);
   $questions = array();

   foreach ($xmldoc->xpath('//field') as $field) {
    $f_id = (string) $field['id'];
    $questions[$f_id] = array();

    $questions[$f_id]['label'] = (string) $field->label[0];
    $questions[$f_id]['description'] = (string) $field->description[0];
    $questions[$f_id]['type'] = (string) $field->type[0];
    $questions[$f_id]['options'] = array();

    foreach ($field->enum as $enum) {
       $questions[$f_id]['options'][(string)$enum['id']] = (string)$enum->label[0];
    } // for each enum

   } // foreach

   return $questions;
} // licenseQuestions

function issueLicense($lic_class, $answers) {

   // assemble the answers XML fragment
   $answers_xml = "<answers><license-" . $lic_class . ">";

   foreach ($answers as $field_id=>$value) {
      $answers_xml .= "<" . $field_id . ">" . $value . "</" . $field_id . ">";
   } // for each answer

   $answers_xml .= "</license-" . $lic_class . "></answers>";

   // make the web service request
   $uri = "http://api.creativecommons.org/rest/license/" . $lic_class . "/issue?answers=" . urlencode($answers_xml);
   $cobj=curl_init($uri);
   curl_setopt($cobj, CURLOPT_RETURNTRANSFER, true);
   $xml=curl_exec($cobj);
   curl_close($cobj);

   $xmldoc = simplexml_load_string($xml);
   $result = array();

   $result["uri"] = (string)$xmldoc->{"license-uri"};
   $result["name"] = (string)$xmldoc->{"license-name"};
   $result["rdf"] = (string)$xmldoc->rdf->asXML();
   $result["html"] = (string)$xmldoc->html->asXML();

   return $result;
} // issueLicense
?>
