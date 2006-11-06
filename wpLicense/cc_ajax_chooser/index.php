<?php
     /* Creative Commons cc-ajax license chooser
      * Sample Web Services Client
      * copyright 2005-2006, Creative Commons, Nathan R. Yergler
      *
      * See docs/LICENSE for details.
      */

   require_once(dirname(__FILE__).'/html_ui.php');

?>

<html>
  <head>
    <title>Creative Commons AJAX License Chooser Demo</title>

<!-- insert the Javascript include statements -->
<?php scriptHeader($base='.'); ?>

<style>
th { 
    white-space: nowrap; 
}

img {
    display:none; 
    float:right; 
    margin:0px;
    padding:0px;
}

</style>
  </head>

  <body>

<?php licenseChooser($action='choose.php', $base='.'); ?>

  </body>
</html>
