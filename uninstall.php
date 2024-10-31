<?php 

/**
 * product designer plugin uninstall
 *
 *
 */

	if (!defined('WP_UNINSTALL_PLUGIN')) {
	    die;
	}
	global $wp, $wpdb, $wp_query, $api_path, $plugin_path, $plugin_url;
	$store_domain = get_site_url();
	$url = $api_path . 'api/v1/saas/uninstall-data?merchant_domain=' . $store_domain . '&store_type=woocommerce';
	$response = wp_remote_get( $url );
	$result   = wp_remote_retrieve_body( $response );
	$res = json_decode($result, true);
	$plan = strtolower($res['data']['plan_type']);
	$table_name = $wpdb->prefix . 'multipleshippingaddress';
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);

	$url  = $api_path . 'api/v1/saas/uninstall-plugin';
    $body = array(
        'type' => 'uninstall',
        'merchant_domain' => $store_domain,
        'store_type' => 'woocommerce',
        'plan_type' => $plan
        );
    if($plan == 'paid') {
    	$body['remove_subscription'] = 1;
    }
    $args = array(
        'method'      => 'POST',
        'timeout'     => 45,
        'sslverify'   => false,
        'headers'     => array(
            'Content-Type'  => 'application/json',
        ),
        'body'        => json_encode($body),
    );

    $request = wp_remote_post( $url, $args );
    $response = wp_remote_retrieve_body( $request );
    $resArr = json_decode($response, true);
    if($resArr['status'] == 1) {
    	wp_redirect( admin_url( 'plugins.php' ) );
    }
?>