<?php

/*
 *  $Id: ccwsclient.php 2769 2006-01-05 19:25:05Z nyergler $
 *  $Date: 2006-01-06 05:25:05 +1000 (Fri, 06 Jan 2006) $
 *  copyright 2005-2006, Nathan R. Yergler, Creative Commons
 *  proxy code contribued by Jonathan Guerin
 *  
 *  Licensed under the MIT License
 *  see docs/LICENSE for more information
 *
 *  Provides PHP client to the CC web services.
 * 
 */

require_once(dirname(__FILE__).'/minixml.inc.php');

/*
 *  Web Service Client Configuration
 *  --------------------------------
 *  
 *  $WS_ROOT defines the web service root; 
 *           see http://api.creativecommons.org for details
 *  $PROXY   defines the proxy to use when making web service requests;
 *           without the "http://".  For example, "proxy:8080";
 *  $USE_PROXY set to true to enable proxying
 */

$WS_ROOT = "http://api.creativecommons.org/rest/1.5/";
$PROXY = "proxy:8080"; 
$USE_PROXY = false; 

function fopenEnabled() {

   // return TRUE if we can open URLs with fopen
   if (ini_get("allow_url_fopen")) {
      return TRUE;
   }

   return FALSE;

} // fopenEnabled

function retrieveFile($path) {
   // retrieve the specified path from the web services root or, 
   // if unavailable, from the local static cache

   global $WS_ROOT;

   // try to retrieve the information from the CC web services
   $result = file_get_contents_proxy($WS_ROOT.$path);
  
   if (!($result === FALSE)) {
      return $result;
   }

} // retrieveFile

function licenseClasses() {
   
   $l_classes = array();

   // retrieve the license 
   $xml = retrieveFile(""); 

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

   $uri = 'license/'.$lclass."/"; 
   $questions = array();

   // retrieve the license 
   $xml = retrieveFile($uri); 
 
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

   // assemble the license-get answers URL
   $answers_url = "";

   foreach ($answers as $field_id=>$value) {
      $answers_url .= $field_id . "=" .$value . "&"; 
   } // for each answer

   // make the web service request
   $uri = $WS_ROOT."license/" . $lic_class . "/get?" . $answers_url;
   $xml = file_get_contents_proxy($uri);

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

function file_get_contents_proxy($uri)
{
    global $PROXY;
    global $USE_PROXY;
	
    if ($USE_PROXY) {
        // proxy enabled
        $context = stream_context_create(
	    array('http'=> 
                array('request_fulluri' => true, 'proxy'=>"tcp://$PROXY")
	    ));
	
        return file_get_contents($uri, false, $context);
    } else {
	// proxy disabled, fall back to normal retrieval
        return file_get_contents($uri);
    }

} // file_get_contents_proxy

?>
