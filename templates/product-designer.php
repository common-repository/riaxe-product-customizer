<?php
include(dirname(__FILE__, 5) . '/wp-load.php');

get_header(); 

defined( 'ABSPATH' ) || exit;

global $product, $api_path; 

if ( post_password_required() ) {
  echo get_the_password_form(); // WPCS: XSS ok.
  return;
}
$length = 8;
$min = 1 . str_repeat(0, $length-1);
$max = str_repeat(9, $length);
$random = mt_rand($min, $max); 

wp_enqueue_style( 'designer-style', $api_path . 'static/css/inx-main.css?rvn=' . $random , false, '1.0.0' );
?>

<div id="root" style="min-height:600px;"></div>
<?php
  wp_enqueue_script( 'designer-script1', $api_path . 'config.js?rvn=' . $random, array(), '1.0.0', true );
  wp_enqueue_script( 'designer-script', $api_path . 'static/js/inx-main.js?rvn=' . $random, array(), '1.0.0', true );
 
get_footer(); ?>

<style>
.col-full{
    max-width: 100%!important;
}
.content-area{
    width: 100%!important;
}
</style>