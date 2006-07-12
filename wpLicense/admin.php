<?php

function getLicenseQuestions($class) {

   $result = '<table>';
   $fields = licenseQuestions($class);

   foreach ($fields as $f_id=>$f_data) {
      $result .= '<tr><th><nobr>' . $f_data['label'] . '</nobr></th><td>';

      // generate the appropriate widget
      if ($f_data['type'] == 'enum') {
         $result .= '<select id="'.$f_id.'" lic_q="true" onchange="updateLicense()" size="1">';

         foreach ($f_data['options'] as $enum_id=>$enum_val) {
            $result .= '<option value="'. $enum_id . '">' . $enum_val . '</option>';
         } // for each option

         $result .= '</select>';

      } // if type is enumeration
      $result .= '</td></tr>';
   } // for each field...

   $result .= '</table>';

   return $result;

} // getLicenseQuestions

function getLicense($lclass, $answer_string) {

   $answers = array();

   $as_vals = explode(",", $answer_string);

   foreach ($as_vals as $a) {
      list($key, $val) = explode(":", $a);
      $answers[$key] = $val;
   } 

   // get the license html, rdf, etc.
   $license_info = issueLicense($lclass, $answers);
   $flatten = array();

   foreach ($license_info as $field=>$value) {
      $flatten[] = "$field:$value";
   } // for each bit of license information

   return implode(";", $flatten);
} // getLicense

?>