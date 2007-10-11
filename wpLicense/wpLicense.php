<?php
/*
Plugin Name: wpLicense
Plugin URI: http://wiki.creativecommons.org/WpLicense
Description: Allows selection of a <a href="http://creativecommons.org">Creative Commons</a> license for blog content.
Version: 0.9
Author: Nathan R. Yergler <nathan@creativecommons.org>
Author URI: http://wiki.creativecommons.org/User:NathanYergler
*/

/*  Copyright 2005-2007,
    Creative Commons (email : software@creativecommons.org), 
    Nathan R. Yergler (email : nathan@creativecommons.org)

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

/* Template Functions */

function licenseHtml($display=1, $show_button=1) {

   $license_uri = get_option('cc_content_license_uri');
   $license_img = get_option('cc_content_license_img');
   $license_name = get_option('cc_content_license');
  
   if ($show_button) {
     $image = <<< END_OF_STMT
<img src="${license_img}" alt="${license_name}"/><br/>${license_name}
END_OF_STMT;
  } else {
     $image = '';
  }

  $result = <<< END_OF_STMT
<div class="wp_license" style="text-align:center;">
<a rel="license" href="${license_uri}">$image</a>
</div>
END_OF_STMT;

   if ($display == 1) {
      echo $result;
   } else {
      return $result;
   }

} // licenseHtml

function licenseUri() {
   return get_option('cc_content_license_uri');
} // licenseUri

function isLicensed() {
  // returns True if a license is selected
  return get_option('cc_content_license');
} // isLicensed

function cc_showLicenseHtml() {
   if (isLicensed()) {
      echo '<div class="license_block">'.licenseHtml(0, get_option('cc_include_footer')).'</div>';
   }
} // cc_showLicenseHtml

function cc_rss2_ns() {

echo 'xmlns:creativeCommons="http://backend.userland.com/creativeCommonsRssModule"';

} // cc_rss2_ns

function cc_rss2_head() {
     if (isLicensed()) {
     	echo '<creativeCommons:license>'.licenseUri().'</creativeCommons:license>';
     }

} // cc_rss2_head

function cc_atom_head() {
     if (isLicensed()) {
     	echo '<link rel="license" type="text/html" href="'.licenseUri().'" />';
     }

} // cc_atom_head



/* Admin functions */

function license_options() {
   global $post_msg;

$defaults = array("license_name" => get_option('cc_content_license'),
	     	  "license_uri"  => get_option('cc_content_license_uri'),
	    );
$wp_url = get_bloginfo('wpurl');
$license_url = get_option('cc_content_license_uri');
$include_footer = get_option('cc_include_footer')=='1'?"checked":"";

if (! $license_url) { $jswidget_extra = "want_a_license=no_license_by_default"; }
else                { $jswidget_extra = ""; }

echo <<< END_OF_ADMIN
<div class="wrap">
         <div id="statusmsg">${post_msg}</div>
         <h2>Content License</h2>
<p>This page allows you to choose a 
<a href="http://creativecommons.org">Creative Commons</a> license 
for your content.  If you select "Include License Badge", the default
Creative Commons badge, link and RDF will be included in the standard footer.
</p>

<p>If you wish to display the license information in a non-standard 
way, or in a custom location, you may do so using 
functions provided by the plugin
<a href="http://wiki.creativecommons.org/WpLicense_Function_Reference"
   target="_blank">
(function reference)</a>.</p>

<form name="license_options" method="post" action="${_SERVER[REQUEST_URI]}">

<script type="text/javascript" src="http://api.creativecommons.org/jswidget/tags/0.92/complete.js?${jswidget_extra}">
</script>

<input id="cc_js_seed_uri" type="hidden" value="${license_url}" />
<input name="blog_url" id="blog_url" type="hidden" value="${wp_url}" />
<input name="remove_license" type="hidden" value="false" />
<input name="submitted" type="hidden" value="wplicense" />

              <tr><td colspan="2">&nbsp;</td></tr>
              <tr><th>Include license badge in default footer?</th>
                  <td><input type="checkbox" name="includeFooter" ${include_footer}" ></td> 
             </tr> 
               
               <tr><th>&nbsp;</th>
                   <td><input type="submit" value="save" />
                       <input type="reset"  value="cancel" id="cancel" />
                   </td>
               </tr>
               </td></tr>
            </table>
            </form>
         </div>

      </div>

END_OF_ADMIN;

} // license_options

// Add the Content License link to the options page listing
function cc_addAdminPage() {
	if (function_exists('add_options_page')) {
		add_options_page('Content License', '<img src="'.get_bloginfo('wpurl').'/wp-content/plugins/wpLicense/images/cc_admin.png" style="padding-right: 3px; position: relative; top: 2px;">Content License', 5, basename(__FILE__), 'license_options');
		}
} // addAdminPage


// Include the necessary java-script libraries 	 
function wplicense_header() { 	 
	  	 
   if (strpos($_SERVER['REQUEST_URI'], "wpLicense") === FALSE) return; 	 
	  	 
   $css_url = get_bloginfo("wpurl") . "/wp-content/plugins/wplicense/wplicense.css"; 	 
	  	
   echo "<link rel=\"stylesheet\" href=\"${css_url}\" />";
} // wplicense_header


// Initialize the WordPress content variables
function init_content_license($fullreset=false, $just_license_reset=false) {

  // call non-destructive add for each option
  add_option('cc_content_license', '');
  add_option('cc_content_license_uri', '');
  add_option('cc_content_license_img', '');

  add_option('cc_copyright_holder', '');
  add_option('cc_creator', '');
  add_option('cc_include_work', '0');
  add_option('cc_per_post', '0');

  add_option('cc_include_footer', '1');

  // if reset is True, destructively reset the values
  if ($fullreset == true) {
     echo "full reest " . $fullreset . "<P>";
     update_option('cc_content_license', '');
     update_option('cc_content_license_uri', '');
     update_option('cc_content_license_img', '');

     update_option('cc_copyright_holder', '');
     update_option('cc_creator', '');
     update_option('cc_include_work', '0');
     update_option('cc_per_post', '0');

     update_option('cc_include_footer', '1');

  } // if resetting

  if ($just_license_reset) {
    update_option('cc_content_license', '');
    update_option('cc_content_license_uri', '');
    update_option('cc_content_license_img', '');
  } // if just resetting license details but not other prefs
  
} // init_content_license

function post_form() {
    global $post_msg;

    // check for standard return (using web services
    if ( (isset($_POST['submitted'])) && ($_POST['submitted'] == 'wplicense')) {
        // check if the license should be removed
        if ($_POST['remove_license'] == '__remove' || 
           ($_POST['cc_js_result_uri'] == '' && get_option('cc_content_license_uri') != '')) {
           init_content_license(false, true);

           $post_msg = "<h3>License information removed.</h3>";
	   return;
        } // remove license

        // check if the license was changed
	if ($_POST['cc_js_result_uri'] != get_option('cc_content_license_uri')) {
           // store the new license information
           update_option('cc_content_license', $_POST['cc_js_result_name']);
           update_option('cc_content_license_uri', $_POST['cc_js_result_uri']);
           update_option('cc_content_license_img', $_POST['cc_js_result_img']);

        }

        // store the settings

        if (isset($_POST['includeFooter'])) {
           update_option('cc_include_footer', '1');
        } else {
           update_option('cc_include_footer', '0');
        }

        $post_msg = "<h3>License information updated.</h3>";
    } // standard web services post 


} // post_form

/* admin interface action registration */
add_action('admin_menu', 'cc_addAdminPage');
add_action('admin_head', 'wplicense_header');
add_action('admin_head', 'post_form');

/* content action/filter registration */

// show global RDF + HTML, if turned on
add_action('wp_footer', 'cc_showLicenseHtml');

// feed licensing
add_action('rss2_ns', 'cc_rss2_ns');
add_action('rss2_head', 'cc_rss2_head');
add_action('atom_head', 'cc_atom_head');

// widget support
require(dirname(__FILE__) . '/widget.php');

?>
