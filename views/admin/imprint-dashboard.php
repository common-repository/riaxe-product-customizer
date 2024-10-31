<?php 
	//ImprintNext Admin Dashboard 
	global $wp, $wpdb, $wp_query, $api_path, $plugin_path, $plugin_url;
	$domain = get_site_url();
	header( 'Content-Security-Policy: frame-ancestors '.$domain );
	wp_enqueue_style( 'dashboard-style', $api_path . 'admin/styles-inx.css' , false, '1.0.0' );
?>

<div class="fixed-header horizontal-menu horizontal-app-menu dashboard" style="background: #ebedf1;">
  <app-root></app-root>
<?php
	wp_enqueue_script( 'dashboard-script', $api_path . 'admin/runtime-es2015-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script1', $api_path . 'admin/runtime-es5-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script2', $api_path . 'admin/polyfills-es5-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script3', $api_path . 'admin/polyfills-es2015-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script4', $api_path . 'admin/scripts-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script5', $api_path . 'admin/main-es2015-inx.js', array(), '1.0.0', true );
  wp_enqueue_script( 'dashboard-script6', $api_path . 'admin/main-es5-inx.js', array(), '1.0.0', true );
 ?>
</div>
<script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)return a();e.attachEvent?e.attachEvent("onload",a):e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
<script type="text/javascript">window.Beacon('init', 'eb1a4d84-7513-4ac4-bd2e-34e92dec4899')</script>