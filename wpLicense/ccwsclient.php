<?php

require_once(dirname(__FILE__).'/minixml.inc.php');

$WS_ROOT = "http://api.creativecommons.org/rest/1.0/";

function licenseClasses() {
   global $WS_ROOT;
   
   $l_classes = array();

   // retrieve the license 
   $xml = file_get_contents($WS_ROOT);

   // parse the classes into a hash
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot();
   $root =& $root->getElement('licenses');
   $licenses = $root->getAllChildren('license');

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
   $xml = file_get_contents($uri);
   // $cobj=curl_init($uri);
   // curl_setopt($cobj, CURLOPT_RETURNTRANSFER, 1);
   // $xml=curl_exec($cobj);
   // curl_close($cobj);
 
   // parse the classes into a hash
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot();
   $root =& $root->getElement('licenseclass');
   $fields = $root->getAllChildren('field');

   foreach ($fields as $field) {
    $f_id = strval($field->attribute('id'));
    $questions[$f_id] = array();

    $el =& $field->getElement('label');
    $questions[$f_id]['label'] = strval($el->getValue());

    $el =& $field->getElement('description');
    $questions[$f_id]['description'] = strval($el->getValue());

    $el =& $field->getElement('type');
    $questions[$f_id]['type'] = strval($el->getValue());

    $questions[$f_id]['options'] = array();

    foreach ($field->getAllChildren('enum') as $enum) {
       $el =& $enum->getElement('label');
       $questions[$f_id]['options'][(string)$enum->attribute('id')] = (string)$el->getValue();
    } // for each enum

   } // foreach

   return $questions;
} // licenseQuestions

function issueLicense($lic_class, $answers) {

   global $WS_ROOT;
   $result = array();

   // do some brain-dead validation on $answers
   if (!isset($answers['jurisdiction'])) {
      $answers['jurisdiction'] = '-';
   } 

   // assemble the answers XML fragment
   $answers_xml = "<answers><license-" . $lic_class . ">";

   foreach ($answers as $field_id=>$value) {
      $answers_xml .= "<" . $field_id . ">" . $value . "</" . $field_id . ">";
   } // for each answer

   $answers_xml .= "</license-" . $lic_class . "></answers>";

   // make the web service request
   $uri = $WS_ROOT."license/" . $lic_class . "/issue?answers=" . urlencode($answers_xml);
   $xml = file_get_contents($uri);

   // extract the license information
   $xmlDoc = new MiniXMLDoc();
   $xmlDoc->fromString($xml);

   $root =& $xmlDoc->getRoot();
   $root =& $root->getElement('result');

   $el =& $root->getElement('license-uri');
   $result["uri"] = strval($el->getValue());

   $el =& $root->getElement('license-name');
   $result["name"] = strval($el->getValue());

   $el =& $root->getElement('rdf');
   $result["rdf"] = $el->toString();

   // use a regexp to extract the HTML to avoid any problems with miniXml
   preg_match("/(<html>)([\s\S]*)(<\/html>)/", $xml, $matches);
   $result["html"] = $matches[2];

   return $result;
} // issueLicense
?>
