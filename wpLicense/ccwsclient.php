<?php

require_once(dirname(__FILE__).'/minixml.inc.php');

$WS_ROOT = "http://api.creativecommons.org/rest/1.0/";

function licenseClasses() {
   global $WS_ROOT;
   
   $l_classes = array();

   // retrieve the license 
   $cobj=curl_init($WS_ROOT);
   curl_setopt($cobj, CURLOPT_RETURNTRANSFER, 1);
   $xml=curl_exec($cobj);
   curl_close($cobj);

   // parse the classes into a hash
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot()->getElement('licenses');
   $licenses =& $root->getAllChildren('license');

   foreach ($licenses as $l) {
      $l_classes[strval($l->attribute('id'))] = strval($l->getValue());
   }

   return $l_classes;

} // licenseClasses

function licenseQuestions($lclass) {
   global $WS_ROOT;

   $uri = $WS_ROOT.'license/'.$lclass."/";
   $questions = array();

   // retrieve the license 
   $cobj=curl_init($uri);
   curl_setopt($cobj, CURLOPT_RETURNTRANSFER, 1);
   $xml=curl_exec($cobj);
   curl_close($cobj);
 
   // parse the classes into a hash
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot()->getElement('licenseclass');
   $fields =& $root->getAllChildren('field');

   foreach ($fields as $field) {
    $f_id = strval($field->attribute('id'));
    $questions[$f_id] = array();

    $questions[$f_id]['label'] = strval($field->getElement('label')->getValue());
    $questions[$f_id]['description'] = strval($field->getElement('description')->getValue());
    $questions[$f_id]['type'] = strval($field->getElement('type')->getValue());
    $questions[$f_id]['options'] = array();

    foreach ($field->getAllChildren('enum') as $enum) {
       $questions[$f_id]['options'][(string)$enum->attribute('id')] = (string)$enum->getElement('label')->getValue();
    } // for each enum

   } // foreach

   return $questions;
} // licenseQuestions

function issueLicense($lic_class, $answers) {

   global $WS_ROOT;
   $result = array();

   // assemble the answers XML fragment
   $answers_xml = "<answers><license-" . $lic_class . ">";

   foreach ($answers as $field_id=>$value) {
      $answers_xml .= "<" . $field_id . ">" . $value . "</" . $field_id . ">";
   } // for each answer

   $answers_xml .= "</license-" . $lic_class . "></answers>";

   // make the web service request
   $uri = $WS_ROOT."license/" . $lic_class . "/issue?answers=" . urlencode($answers_xml);
   $cobj=curl_init($uri);
   curl_setopt($cobj, CURLOPT_RETURNTRANSFER, true);
   $xml=curl_exec($cobj);
   curl_close($cobj);

   // extract the license information
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot()->getElement('result');

   $result["uri"] = strval($root->getElement('license-uri')->getValue());
   $result["name"] = strval($root->getElement('license-name')->getValue());
   $result["rdf"] = $root->getElement('rdf')->toString();
   $result["html"] = $root->getElement('html')->toString();

   // $xmldoc = simplexml_load_string($xml);

   //$result["uri"] = (string)$xmldoc->{"license-uri"};
   //$result["name"] = (string)$xmldoc->{"license-name"};
   //$result["rdf"] = (string)$xmldoc->rdf->asXML();
   //$result["html"] = (string)$xmldoc->html->asXML();

   return $result;
} // issueLicense
?>
