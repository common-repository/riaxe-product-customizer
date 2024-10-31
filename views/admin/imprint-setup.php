<?php
/**

 * ImprintNext Setup Wizard
 */

global $wp, $wpdb, $wp_query, $api_path, $plugin_path, $plugin_url;
$store_domain = get_site_url();
$str = rand();
$token = hash( 'sha256', $str );
$user_id = get_current_user_id();
$user_data = get_userdata( $user_id );
$roles = $user_data->roles;
$tenant_name = '';
if ( is_admin() && in_array( 'administrator', $roles ) ) {
	$tenant_name = $user_data->data->user_nicename;
}
$admin_email       = get_option( 'admin_email' );
$store_address     = get_option( 'woocommerce_store_address' ) ? get_option( 'woocommerce_store_address' ) : '';
$store_address_2   = get_option( 'woocommerce_store_address_2' ) ? get_option( 'woocommerce_store_address_2' ) : '';
$store_city        = get_option( 'woocommerce_store_city' ) ? get_option( 'woocommerce_store_city' ) : '';
$store_postcode    = get_option( 'woocommerce_store_postcode' ) ? get_option( 'woocommerce_store_postcode' ) : '';
$woocommerce_default_country    = get_option( 'woocommerce_default_country' ) ? get_option( 'woocommerce_default_country' ) : '';
$state = '';
$country = '';
if ( ! empty( $woocommerce_default_country ) ) {
	$default_array = explode( ':', $woocommerce_default_country );
	$country = $default_array[0] ? $default_array[0] : '';
	$state = $default_array[1] ? $default_array[1] : '';
}
$merchant_info = array(
	'tenant_name' => $tenant_name,
	'email' => $admin_email,
	'address1' => $store_address,
	'address2' => $store_address_2,
	'city' => $store_city,
	'state' => $state,
	'country' => $country,
	'zipcode' => $store_postcode,
);
$json_merchant_info = json_encode( $merchant_info );
?>
<h1>ImprintNext Setup Wizard</h1>
<div class="content-full-area">
	<section class="content-section">
		<div class="content-inner">
			<div class="content-top d-none">
				<div class="left-section">
					<!-- Text -->
				</div>
				<div class="right-section"></div>
			</div>
			<div class="content-main" id="setup-msg">
				<!-- CONTENT START -->
				<section class="profile-section">  
					<h3> Welcome to ImprintNext</h3>
					<p>You are one step away of the ImprintNext setup.</p>
						<p>Please click proceed to complete the setup.</p>
						<a href="javascript:void(0);" class="btn custom-btn btn-primary" id="imprint_setup" style="border-width:2px;font-weight: 500;border-color: #3d66db;background-color: #3d66db;font-size: 14px;padding: 14px 26px;color: #fff;text-transform: uppercase;text-align: center;vertical-align: middle;cursor: pointer;text-decoration: none;">Proceed</a>

					<form id="setupFrm">
						<input type="hidden" name="store" value="woocommerce"/>
						<input type="hidden" name="store_domain" value="<?php echo esc_url( $store_domain ); ?>"/>
						<input type="hidden" name="c_key" value="<?php echo esc_attr( $_SESSION['imprintnext']['c_key'] ); ?>"/>
						<input type="hidden" name="c_secret" value="<?php echo esc_attr( $_SESSION['imprintnext']['c_secret'] ); ?>"/>						
						<input type="hidden" name="merchant_info" value='<?php echo esc_attr( $json_merchant_info ); ?>'/>					
					</form>
				</section>
				<!-- CONTENT END -->
			</div>
		</div>
		<div class="content-inner" style="display:none" id="setup-process">
			<div class="content-top d-none">
				<div class="left-section">
					<!-- Text -->
				</div>
				<div class="right-section"></div>
			</div>
			<div class="content-main">
				<!-- CONTENT START -->
				<section class="profile-section scrollbar">  
					<h3> Please wait</h3>
					<div>
						<p><image src="<?php echo esc_url( $plugin_url . 'assets/frontend/img/loading.gif' ); ?>"/></p>
						<p>Please do not refresh,  ImprintNext setup is going on.</p>					
					</div>
				</section>
				<!-- CONTENT END -->
			</div>
		</div>
	</section>
</div>
