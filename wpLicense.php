<?php
/*
Plugin Name: Content License
Plugin URI: http://yergler.net/projects/wplicense
Description: Allows selection of a <a href="http://creativecommons.org">Creative Commons</a> license for blog content.
Version: 0.2
Author: Nathan R. Yergler <nathan@yergler.net>
Author URI: http://yergler.net
*/

/*  Copyright 2005  Nathan R. Yergler  (email : nathan@yergler.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require('wpLicense/Sajax.php');
require('wpLicense/ccwsclient.php');
require('wpLicense/admin.php');

/* Template Functions */

function licenseRdf($display=1) {
   if ($display == 1) {
      echo get_option('cc_content_license_rdf');
   } else {
      return get_option('cc_content_license_rdf');
   }

} // licenseRdf

function licenseHtml($display=1) {
   if ($display == 1) {
      echo str_replace('\"', '"', get_option('cc_content_license_html'));
   } else {
      return str_replace('\"', '"', get_option('cc_content_license_html'));
   }

} // licenseHtml

function licenseUri() {
   echo get_option('cc_content_license_uri');
} // licenseUri

/* Admin functions */

function license_options() {
   global $post_msg;
echo '
<div class="wrap">
         <div id="statusmsg">'.$post_msg.'</div>
         <h2>Content License</h2>
<p>This page allows you to choose a 
<a href="http://creativecommons.org">Creative Commons</a> license 
for your content.  This license may be included in templates using 
functions provided by the plugin
<a href="http://yergler.net/projects/wplicense/wplicense-function-reference"
   target="_blank">
(function reference)</a>.</p>

         <div id="license_selection" class="wrap">
            <form name="license_options" method="post" 
                  action="' . $_SERVER[REQUEST_URI] . '">

            <input name="submitted"    type="hidden" value="true" />
            <input name="license_name" type="hidden" 
                   value="'.get_option('cc_content_license').'" />
            <input name="license_uri"  type="hidden"  
                   value="'.get_option('cc_content_license_uri').'" />
            <input name="license_rdf"  type="hidden"  
                   value="" />
            <input name="license_html" type="hidden"  
                   value="" />

            <table>
               <tr><th>Current License:</th><td>
         <a href="'.get_option('cc_content_license_uri').'">'.get_option('cc_content_license').'</a> (<a href="#" onclick="showSelector();">change</a>)
               </td></tr>

               <tr><th>&nbsp;</th><td>

<div id="licenseSelector" name="licenseSelector"
     class="wrap" style="display:none;">
<table>
               <tr><th>Selected&nbsp;License:</th>
                   <td id="newlicense_name">(none)
                   </td>
               </tr>
               <tr><th>&nbsp;</th>
                 <td>
            License type: <select id="licenseClass" onchange="selectClass();">
                          <option id="-">--</option>
';
    $l_classes = licenseClasses();
    foreach($l_classes as $key => $l_id) {
          echo '<option value="' . $key . '" >' . $l_id . '</option>';
    }; // for each...

  echo '          </select>
         <div id="license_options" class="wrap">
         </div>
</td></tr>
               <tr><td colspan="2" align="center">
                 <img id="working" src="'.get_bloginfo('wpurl').'/wp-content/plugins/wpLicense/progress.gif" style="display:none; width: 100%; height: 10px;"/>
               </td></tr>
</table>
</div>

                 </td></tr>

             <tr><th>Include work metadata?</th>
                 <td><input type="checkbox" name="workMeta" '.(get_option('cc_include_work')=='1'?"checked":"").'" ></td>
             </tr>
             <tr><th>Creator</th>
                 <td><input class="disabled" name="creator" value="'.get_option('cc_creator').'" ></td>
             </tr>
             <tr><th>Copyright Holder</th>
                 <td><input name="holder" value="'.get_option('cc_copyright_holder').'" ></td>
             </tr>
<!--             <tr><th>Include per-post license information?</th>
                 <td><input type="checkbox" name="perPost" '.(get_option('cc_per_post')=='1'?"checked":"").'" ></td> 
             </tr> -->

               <tr><th>&nbsp;</th>
                   <td><input type="submit" value="save" />
                       <input type="reset"  value="cancel" 
                              onclick="cancelChanges();"/>
                   </td>
               </tr>
               </td></tr>

            </table>
            </form>
         </div>

      </div>
      ';
} // license_options

function cc_addAdminPage() {
	if (function_exists('add_options_page')) {
		add_options_page('Content License', '<img src="'.get_bloginfo('wpurl').'/wp-content/plugins/wpLicense/cc_admin.png" style="padding-right: 3px; position: relative; top: 2px;">Content License', 5, basename(__FILE__), 'license_options');
		}
} // addAdminPage


function license_js_header() {

    echo '<script type="text/javascript" src="';
    bloginfo("wpurl");
    echo '/wp-content/plugins/wpLicense/admin.js"> </script>';

} // license_js_header

function sajax_header() {
    echo "<script>";
    sajax_show_javascript();
    echo "</script>";
} // sajax_header

function post_form() {
    global $post_msg;

    if (!isset($_POST['submitted']) ) {
        $post_msg = '';
    } else {
        // check if the license was changed
	if ($_POST['license_uri'] != get_option('cc_content_license_uri')) {
           // store the new license information
           update_option('cc_content_license', $_POST['license_name']);
           update_option('cc_content_license_uri', $_POST['license_uri']);
           update_option('cc_content_license_rdf', $_POST['license_rdf']);
           update_option('cc_content_license_html', $_POST['license_html']);
        }

        // store the settings
        update_option('cc_copyright_holder', $_POST['holder']);
        update_option('cc_creator', $_POST['creator']);

        if (isset($_POST['workMeta'])) {
           update_option('cc_include_work', '1');
        } else {
           update_option('cc_include_work', '0');
        }
        if (isset($_POST['perPost'])) {
           update_option('cc_per_post', '1');
        } else {
           update_option('cc_per_post', '0');
        }

        // check if we're including work metadata
        if (get_option('cc_include_work') == '1') {
           // generate the work RDF and update the license_rdf
           $work_rdf = '<Work rdf:about="">
   <dc:title>'.get_bloginfo('name').'</dc:title>
   <dc:date>'.date('Y').'</dc:date>
   <dc:description>'.get_bloginfo('description').'</dc:description>
   <dc:creator><Agent>
      <dc:title>'.get_option('cc_creator').'</dc:title>
   </Agent></dc:creator>
   <dc:rights><Agent>
      <dc:title>'.get_option('cc_copyright_holder').'</dc:title>
   </Agent></dc:rights>
   <dc:source rdf:resource="source"/>
   <license rdf:resource="'.get_option('cc_content_license_uri').'" />
</Work>';

           update_option('cc_content_license_rdf', preg_replace('/<Work [\s\S]*<\/Work>/', $work_rdf, get_option('cc_content_license_rdf')));
           update_option('cc_content_license_html', preg_replace('/<Work [\s\S]*<\/Work>/', $work_rdf, get_option('cc_content_license_html')));

        }

        $post_msg = "<h3>License information updated.</h3>";
    }

} // post_form

/* Sajax Loading */

sajax_init();
// $sajax_debug_mode = 1;
sajax_export("getLicenseQuestions");
sajax_export("getLicense");

sajax_handle_client_request();

/* action/filter registration */
add_action('admin_menu', 'cc_addAdminPage');
add_action('admin_head', 'sajax_header');
add_action('admin_head', 'license_js_header');
add_action('admin_head', 'post_form');


?>
