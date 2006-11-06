<?php

/*
 *  $Id: cc_ajax.php 2769 2006-01-05 19:25:05Z nyergler $
 *  $Date: 2006-01-05 14:25:05 -0500 (Thu, 05 Jan 2006) $
 *  copyright 20052-2006, Nathan R. Yergler, Creative Commons
 *  
 *  Licensed under the MIT License
 *  see docs/LICENSE for more information
 *
 */

require_once(dirname(__FILE__).'/ws_client.php');

// *************************************************************************
// scriptHeader($base='.')
//
// Outputs the javascript <script> tags necessary for the license chooser to
// function properly.  $base specifies the the path to the cc_ajax package.
// If not specified, a relative link is constructed.

function scriptHeader($base='.') {

printf ('
    <script type="text/javascript" src="%s/js/behaviour.js"> </script>
    <script type="text/javascript" src="%s/js/tw-sack.js"> </script>
    <script type="text/javascript" src="%s/js/chooser.js"> </script>
', $base, $base, $base);

} // scriptHeader

// *************************************************************************
// licenseChooser($action, $base='.')
//
// Renders an HTML license choser.  $action specifies the form action to take
// and $base specified the base portion of the URL.
//
// XXX For example, if the files are served at...

function licenseChooser($action='choose.php', $base='.') {

printf ('
         <div id="license_selection" class="wrap">
            <form name="license_options" method="post" action="%s">

            <input name="license_name" type="hidden" 
                   value="" />
            <input name="license_uri"  type="hidden"  
                   value="" />
            <input name="license_rdf"  type="hidden"  
                   value="" />
            <input name="license_html" type="hidden"  
                   value="" />

<div id="licenseSelector" name="licenseSelector" class="wrap">
<table>
               <tr><th><nobr>Selected&nbsp;License:</nobr></th>
                   <td id="newlicense_name">(none)</td>
               <td>
               <img id="working" 
                    src="%s/images/Throbber-small.gif" 
                    style="display:none; float:right; margin:0px;padding;0px;"/>
                   </td>
               </tr>
               <tr><th><nobr>License type:</nobr></th>
                 <td colspan="2">
             <select id="licenseClass">
                          <option id="-">(none)</option>',
$action, $base);

    $license_classes = licenseClasses();
    echo $license_classes;
    foreach($license_classes as $key => $l_id) {
          echo '<option value="' . $key . '" >' . $l_id . '</option>';
    }; // for each...

echo '
             </select>
</td></tr>
<tr><td>&nbsp;</td>
<td colspan="2">
         <div id="license_options" class="wrap">
         </div>
</td></tr>
<tr><td colspan="2"><input id="choose" type="submit" value="Choose!"/></td></tr>
</table>
</div>
';

} // licenseChooser

?>
