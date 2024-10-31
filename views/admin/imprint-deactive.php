<?php
/**

 * ImprintNext Deactivate Wizard
 */
global $wp, $wpdb, $wp_query, $api_path, $plugin_path, $plugin_url;
$store_domain = get_site_url();
$url = $api_path . 'api/v1/saas/uninstall-data?merchant_domain=' . $store_domain . '&store_type=woocommerce';
$response = wp_remote_get( $url );
$result   = wp_remote_retrieve_body( $response );
$res = json_decode($result, true);
$plan = strtolower($res['data']['plan_type']);
?>
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
					<h3> Sorry to see you are leaving!</h3>
					<p>We are constantly working to make the application better, for which we need your valuable feadback.</p>
					<p>Please let us know the reason for leaving us</p>
					<form id="feedbackForm" style="border:1px solid #ccc;padding:50px;">
						<input type="hidden" name="merchant_domain" value="<?php echo esc_url( $store_domain ); ?>"/>
						<input type="hidden" name="store_domain" value="<?php echo esc_url( $store_domain ); ?>"/>
						<input type="hidden" name="store_type" value="woocommerce"/>
						<input type="hidden" name="plan_type" value="<?php echo $plan; ?>"/>
						<input type="hidden" name="type" value="inactive"/>
						<p style="text-align:left;">
							<select id="reason" name="reason">
								<option value="">Please select your issue!</option>
								<option value="It is temporary">It is temporary</option>
								<option value="Wrongly installed">Wrongly installed</option>
								<option value="Difficult to setup">Difficult to setup</option>
								<option value="Not having the desired feature">Not having the desired feature</option>
								<option value="UI/UX is not upto mark">UI/UX is not upto mark</option>
								<option value="Cost is very high">Cost is very high</option>
								<option value="Performance is very slow">Performance is very slow</option>
								<option value="Others">Others</option>
							</select>
						</p>
						<p style="text-align:left;">
							<textarea id="comment" name="comment" placeholder="Add your comment." style="width:300px;height:120px;"></textarea>
						</p>
						<?php if($plan == 'paid') { ?>
						<p style="text-align:left;">
							<input type="checkbox" id="remove_subscription" name="remove_subscription" value="1" /> Do you want to remove your subscription?
						</p>
						<?php } ?>
						<a href="javascript:void(0);" class="btn custom-btn btn-primary" id="imprint_deactive" style="border-width:2px;font-weight: 500;border-color: #3d66db;background-color: #3d66db;font-size: 14px;padding: 14px 26px;color: #fff;text-transform: uppercase;text-align: center;vertical-align: middle;cursor: pointer;text-decoration: none;margin:91px;">Deactivate</a>
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
						<p>Please do not refresh, we are processing your request.</p>					
					</div>
				</section>
				<!-- CONTENT END -->
			</div>
		</div>
	</section>
</div>
