<?php
/*
 * Plugin Name: Riaxe Product Customizer
 * Plugin URI: https://imprintnext.com
 * Description: Online product designer tool for woocommerce.
 * Version: 2.0.0
 * Author: Imprintnext
 * Author Email: support@imprintnext.com
 *
 * Requires at least: 4.4
 * Tested up to: 6.6.1
 * WC requires at least: 3.0.0
 * WC tested up to: 9.1.2
 *
 * Copyright: © 2022 imprintnext.com
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! class_exists( 'InkXEProductDesignerLite' ) ) {

	class InkXEProductDesignerLite {

		/**
		 * Plugin name
		 *
		 * @var name.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $name = 'Imprintnext Product Designer - Lite';

		/**
		 * Slug
		 *
		 * @var slug.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $slug = 'ink-pd';

		/**
		 * Plugin path
		 *
		 * @var plugin_path.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $plugin_path;

		/**
		 * Plugin url
		 *
		 * @var plugin_url.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $plugin_url;

		/**
		 * End point
		 *
		 * @var end_points.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $end_points;

		/**
		 * Ajax Nonce
		 *
		 * @var ajax_nonce_string.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $ajax_nonce_string;

		/**
		 * Version
		 *
		 * @var version.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $version = '1.0.0';

		/**
		 * Nonce
		 *
		 * @var nonce.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $nonce = '';

		/**
		 * Token
		 *
		 * @var token.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $token = '';

		/**
		 * Header
		 *
		 * @var header.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $header = array();

		/**
		 * Salt
		 *
		 * @var salt.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $salt = 'imprintNext';

		/**
		 * Customization path
		 *
		 * @var custom_path.
		 *
		 * @access public
		 * @static
		 * @since 1.0.0
		 */
		public $custom_path = 'customize';

		/**
		 * InkXEProductDesignerLite construct.
		 *
		 * @access public
		 * @static
		 * @since  1.0.0
		 * @return void
		 */
		public function __construct() {
			$this->plugin_path = plugin_dir_path( __FILE__ );
			$this->plugin_url = plugin_dir_url( __FILE__ );
			$this->ajax_nonce_string = $this->slug . '_ajax';
			$this->api_path = $this->ink_pd_get_api_url();
			$GLOBALS['plugin_path'] = $this->plugin_path;
			$GLOBALS['plugin_url'] = $this->plugin_url;
			$GLOBALS['end_points'] = $this->end_points;
			$GLOBALS['api_path'] = $this->api_path;
			$this->token = $this->encryption( get_site_url() );
			$GLOBALS['token'] = $this->token;

			// Hook up to the init action.
			add_action( 'plugins_loaded', array( $this, 'init_woocommerce_action_filters' ) );

			add_action( 'rest_api_init', array( $this, 'inkxe_register_custom_routes' ) );

			add_action( 'wp_ajax_install-imprint', array( $this, 'ink_pd_add_option' ) );
			add_action( 'wp_ajax_nopriv_install-imprint', array( $this, 'ink_pd_add_option' ) );
			add_action( 'activated_plugin', array( $this, 'ink_pd_activation_redirect' ) );

			// To create multiple shipping address table for quotation module.
			register_activation_hook( __FILE__, array( &$this, 'create_table_shipping_address' ) );

			// Create Designer Page and assign designer template
			register_activation_hook( __FILE__, array(&$this, 'create_designer_page') );

			//initiate deactivation process
			register_deactivation_hook( __FILE__, array( &$this, 'deactivate_user_store' ) );
		}

		public function customencrypt(){
		?>
		<script>
				/**
				* Get encrypted order data string.
				*
				* @param {string} urlToEncode - order data string
				* @param {string} secretKey - secret key
				*/
				function cartencrypt(url, secretKey,idName) {
					myArray = url.split("?");
					urlToEncode = myArray[1];
					primaryUrl = myArray[0];
					let enCodedBit, encryptedTxt = '', cipherKey = '5',begin = "?";
					urlToEncode += secretKey;

				for (let i = 0; i < urlToEncode.length; i++) {
						enCodedBit = String.fromCharCode(urlToEncode[i].charCodeAt(0) + cipherKey.charCodeAt(0));
						encryptedTxt += enCodedBit;
					}
					encryptedTxt = "?"+(window.btoa(encryptedTxt));
					document.getElementById(idName).href= primaryUrl+encryptedTxt;
				}

				function cartdecrypt(encodedURL,SECRET_KEY) {
					primaryURL = encodedURL.split('?')[0];
					encodedURL = window.atob(encodedURL.split('?')[1]);
					let deCodeBit, decryptedTxt = '', cipherKey = '5';
					for (let i = 0; i < encodedURL.length; i++) {
					deCodeBit = String.fromCharCode(encodedURL[i].charCodeAt(0) - cipherKey.charCodeAt(0));
					decryptedTxt += deCodeBit;
				}
				$completeUrl = primaryURL+"?"+decryptedTxt.split(SECRET_KEY[0])[0].split('&').toString().replaceAll(',','&');
				return $completeUrl;
				}
			</script>
		<?php
		}

		/**
		 * GET Designer API URL.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return string
		 */
		public function ink_pd_get_api_url() {
			$xepath = get_site_url();
			$inkxe_dir = get_option( 'inkxe_dir' );
			if ( ! $inkxe_dir ) {
				$inkxe_dir = 'designer';
			}
			$url = 'https://cloud.imprintnext.io/';
			return $url;
		} // END ink_pd_get_api_url()

		/**
		 * Initializing Actions and Filters.
		 *
		 * @access public
		 * @since  1.0.0
		 * @return void
		 */
		public function init_woocommerce_action_filters() {
			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'ink_pd_admin_scripts' ) );
				add_action( 'wp_ajax_admin_load_thumbnails', array( $this, 'ink_pd_admin_load_thumbnails' ) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'ink_pd_save_images' ) );
				add_action( 'admin_init', array( $this, 'ink_pd_media_columns' ) );
				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'ink_pd_add_product_fields' ) );
				add_action( 'woocommerce_order_item_add_action_buttons', array( $this, 'ink_pd_action_woocommerce_order_item_add_action_buttons' ) );
				add_action( 'admin_menu', array( $this, 'ink_pd_add_menu_item' ) );
			} else {
				add_action( 'wp_enqueue_scripts', array( $this, 'ink_pd_register_scripts_and_styles' ) );
				add_action( 'woocommerce_add_order_item_meta', array( $this, 'ink_pd_wdm_add_print_status_values_to_order_item_meta' ), 1, 2 );
				add_action( 'woocommerce_before_calculate_totals', array( $this, 'ink_pd_add_custom_total_price' ), 10, 1 );
				add_action('woocommerce_after_add_to_cart_button', array($this, 'ink_pd_customize_button'), 10, 0);
				add_action( 'woocommerce_before_add_to_cart_quantity', array( $this, 'ink_pd_display_dropdown_variation_add_cart' ), 10, 0 );
				add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'ink_pd_after_add_to_cart_quantity' ), 10, 0 );
				add_action( 'template_redirect', array( $this, 'ink_pd_template_redirect') );
				add_filter( 'woocommerce_available_variation', array( $this, 'ink_pd_alter_variation_json' ), 10, 3 );
				add_filter( 'woocommerce_thankyou', array( $this, 'create_order_files' ) );
				add_filter( 'woocommerce_add_cart_item_data', array( $this, 'ink_pd_add_cart_item_custom_data_vase' ), 10, 2 );
				add_filter( 'woocommerce_get_item_data', array( $this, 'ink_pd_filter_woocommerce_get_item_data' ), 10, 2 );
				add_filter( 'woocommerce_cart_item_thumbnail', array( $this, 'ink_pd_inkxe_customize_product_image' ), 10, 3 );
				add_filter( 'woocommerce_cart_item_name', array( $this, 'ink_pd_add_edit_info_button' ), 10, 3 );
				add_filter( 'woocommerce_cart_item_quantity', array( $this, 'ink_pd_disable_customize_product_cart_item_quantity' ), 10, 3 );
				add_filter( 'wc_get_template_part', array( $this, 'imp_override_woocommerce_template_part' ), 10, 3 );
				add_filter( 'woocommerce_get_item_data', array( $this, 'display_custom_field_as_item_data' ), 20, 2 );
				add_filter( 'template_include', array( $this, 'ink_pd_change_page_template'), 99 ); 
			}
			add_filter( 'theme_page_templates', array( $this, 'ink_pd_add_page_template_to_dropdown'), 10, 3 );	
		} // END includes()

		/**
		 *
		 * Alter Variation JSON
		 *
		 * This hooks into the data attribute on the variations form for each variation
		 * we can get the additional image data here!
		 *
		 * @param mixed $variation_data  Description of the parameter.
		 * @param mixed $wc_product_variable  Description of the parameter.
		 * @param mixed $variation_obj  Description of the parameter.
		 * @return bool
		 */
		public function ink_pd_alter_variation_json( $variation_data, $wc_product_variable, $variation_obj ) {
			$img_ids = $this->get_all_image_ids( $variation_data['variation_id'] );
			$images = $this->get_all_image_sizes( $img_ids );
			$variation_data['additional_images'] = $images;
			return $variation_data;
		}

		/** =============================
		 *
		 * Is Enabled
		 *
		 * Check whether inkxe is enabled for this product
		 */
		public function is_enabled() {
			global $post;
			$pid = $post->ID;
			if ( $pid ) {
				$disable_inkxe = get_post_meta( $pid, 'disable_inkxe', true );
				return ( $disable_inkxe && 'yes' == $disable_inkxe ) ? false : true;
			}
			return false;
		}

		/** =============================
		 *
		 * Get Product ID from Slug
		 *
		============================= */
		public function get_post_id_from_slug() {
			global $wpdb;
			$url = ( ! empty( $_SERVER['REQUEST_URI'] ) ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
			$slug = str_replace( array( '/product/', '/' ), '', $url );
			$result = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT	ID FROM $wpdb->posts WHERE post_type = \"product\" AND post_name = \"%s\"',
					$slug
				)
			);
			return $result;
		}

		/** =============================
		 *
		 * Add custom product fields
		 *
		============================= */
		public function ink_pd_add_product_fields() {
			global $woocommerce, $post;
			echo '<div class="options_group">';
			// Disable inkxe.
			woocommerce_wp_checkbox(
				array(
					'id' => 'disable_inkxe',
					'label' => __( 'Disable InkXE?', 'ink-pd' ),
				)
			);
			echo '</div>';

		}

		/**
		 *
		 * !Add new column to media screen for Image IDs
		 */
		public function ink_pd_media_columns() {
			add_filter( 'manage_media_columns', array( $this, 'ink_pd_media_id_col' ) );
			add_action( 'manage_media_custom_column', array( $this, 'ink_pd_media_id_col_val' ), 10, 2 );
		}

		/**
		 *
		 * Add new column to media screen for Image IDs
		 *
		 * @param mixed $cols  Description of the parameter.
		 */
		public function ink_pd_media_id_col( $cols ) {
			$cols['mediaid'] = 'Image ID';
			return $cols;
		}
		/**
		 *
		 * !Add new column to media screen for Image IDs
		 *
		 * @param mixed $column_name  Description of the parameter.
		 * @param mixed $id  Description of the parameter.
		 */
		public function ink_pd_media_id_col_val( $column_name, $id ) {
			if ( 'mediaid' == $column_name ) {
				echo esc_attr( $id );
			}
		}

		/**  =============================
		 *
		 * Edit Screen Functions
		 */
		public function ink_pd_admin_scripts() {
			global $post, $pagenow;

			if ( ( $post && ( 'product' == get_post_type( $post->ID ) && ( 'post.php' == $pagenow || 'post-new.php' == $pagenow ) ) ) || ( 'admin.php' == $pagenow ) ) {
				wp_enqueue_script( $this->slug, plugins_url( 'assets/admin/js/admin-scripts.js?rvn=' . rand(1,100000), __FILE__ ), array( 'jquery' ), '2.0.1', true );
				wp_enqueue_style( 'jck_wt_admin_css', plugins_url( 'assets/admin/css/admin-styles.css?rvn=' . rand(1,100000), __FILE__ ), false, '2.0.1' );

				$vars = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( $this->ajax_nonce_string ),
					'siteurl' => $this->api_path,
					'token' => $this->token,
					'shop_url' => get_site_url(),
					'slug' => $this->slug,
				);
				wp_localize_script( $this->slug, 'ink_pd_vars', $vars );
			} else {
				wp_enqueue_script( $this->slug, plugins_url( 'assets/admin/js/downloadsvg.js', __FILE__ ), array( 'jquery' ), '2.0.1', true );
				$vars = array(
					'ajaxurl' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( $this->ajax_nonce_string ),
					'siteurl' => $this->api_path,
					'slug' => $this->slug,
				);
				wp_localize_script( $this->slug, 'ink_pd_vars', $vars );
			}
		}
		/**  =============================
		 *
		 * Save variation gallery images
		 *
		 * @param int $post_id  Description of the parameter.
		 */
		public function ink_pd_save_images( $post_id ) {
			if ( isset( $_REQUEST['_product_image_gallery'] ) ) {
				foreach ( sanitize_post( wp_unslash( $_REQUEST['_product_image_gallery'] ) ) as $var_id => $variation_image_gallery ) {
					update_post_meta( $var_id, '_product_image_gallery', $variation_image_gallery );
				}
			}
		}
		/**  =============================
		 *
		 * Save variation gallery images
		 */
		public function ink_pd_admin_load_thumbnails() {
			$var_id = ( isset( $_GET['varID'] ) ) ? sanitize_text_field( wp_unslash( $_GET['varID'] ) ) : 0;
			$attachments = get_post_meta( $var_id, '_product_image_gallery', true );
			$attachments_exp = array_filter( explode( ',', $attachments ) );
			$img_ids = array();?>
			<ul class="wooThumbs">
				<?php if ( ! empty( $attachments_exp ) ) { ?>
					<?php
					foreach ( $attachments_exp as $id ) {
						$img_ids[] = $id;
						?>
						<li class="image" data-attachment_id="<?php echo esc_attr( $id ); ?>">
							<a href="#" class="delete" title="Delete image"><?php echo wp_get_attachment_image( $id, 'thumbnail' ); ?></a>
						</li>
					<?php } ?>
				<?php } ?>
			</ul>
			<input type="hidden" class="variation_image_gallery" name="_product_image_gallery[<?php echo esc_attr( $var_id ); ?>]" value="<?php echo esc_attr( $attachments ); ?>">

			<?php
			wp_die();
		}

		/**  =============================
		 *
		 * Frontend Scripts and Styles
		 */
		public function ink_pd_register_scripts_and_styles() {
			global $jck_wt, $woocommerce;

			if ( ( function_exists( 'is_product' ) && is_product() ) ) {
				global $product, $post;
				if ( get_post_meta( $post->ID, 'is_customizable', true ) == 'imprint_designer' ) {
					if ( get_current_user_id() ) {
						$nounce = get_current_user_id();
					} else {
						WC()->session = new WC_Session_Handler();
						WC()->session->init();
						if ( is_array( WC()->session->get_session_cookie() ) ) {
							$nounce = WC()->session->get_session_cookie()[0];
						} else {
							$cart_item_id = $woocommerce->cart->add_to_cart( $post->ID );
							$woocommerce->cart->remove_cart_item( $cart_item_id );
							?>
							<script type="text/javascript">
								var url = window.location.href;    
								window.location.href = url;
							</script>
							<?php
						}
					}
					?>
					<?php
				}
			}
			// Cart Image.

			if ( is_cart() ) {
				$this->load_file( $this->slug . '-css', '/assets/frontend/css/cart-style.css' );
				$this->load_file( $this->slug . '-script', '/assets/frontend/js/cart-image.js', true );
				wp_localize_script( $this->slug . '-script', 'ink_pd_vars', array( 'siteurl' => get_option( 'siteurl' ) ) );
			}

			// Checkout page.
			if ( is_checkout() ) {
				// Create order files after order placed.
				$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
				$order_url_data = explode( '/', sanitize_text_field( wp_unslash( $request_uri ) ) );
				if ( 'order-received' == $order_url_data['2'] ) {
					$order_id = $order_url_data['3'];
					$this->create_order_files( $order_id );
				}
			}
		} // end register_scripts_and_styles

		/**
		 *
		 * Helper function for registering and enqueueing scripts and styles.
		 *
		 * @param mixed $name  The ID to register with WordPress.
		 * @param mixed $file_path The path to the actual file.
		 * @param bool  $is_script Optional argument for if the incoming file_path is a JavaScript source file.
		 */
		private function load_file( $name, $file_path, $is_script = false ) {
			$url = plugins_url( $file_path, __FILE__ );
			$file = plugin_dir_path( __FILE__ ) . $file_path;

			if ( file_exists( $file ) ) {
				if ( $is_script ) {
					wp_register_script( $name, $url, array( 'jquery' ), $this->$version, true ); // depends on jquery.
					wp_enqueue_script( $name );
				} else {
					wp_register_style( $name, $url, array(), $this->$version );
					wp_enqueue_style( $name );
				} // end if
			} // end if
		}

		/**
		 * Get all attached Image IDs
		 *
		 * @param int $id The product or variation ID.
		 */
		public function get_all_image_ids( $id ) {
			$all_images = array();
			$show_gallery = false;

			// Main Image.
			if ( has_post_thumbnail( $id ) ) {
				$all_images[] = get_post_thumbnail_id( $id );
			} else {
				$prod = get_post( $id );
				$prod_parent_id = $prod->post_parent;
				if ( $prod_parent_id && has_post_thumbnail( $prod_parent_id ) ) {
					$all_images[] = get_post_thumbnail_id( $prod_parent_id );
				} else {
					$all_images[] = 'placeholder';
				}
				$show_gallery = true;
			}
			// WooThumb Attachments.
			if ( get_post_type( $id ) == 'product_variation' ) {
				// New changes.
				$wt_attachments = array_filter( explode( ',', get_post_meta( $id, '_product_image_gallery', true ) ) );
				$all_images = array_merge( $all_images, $wt_attachments );
			}
			// Gallery Attachments.
			if ( get_post_type( $id ) == 'product' || $show_gallery ) {
				$product = get_product( $id );
				$attach_ids = $product->get_gallery_attachment_ids();
				if ( ! empty( $attach_ids ) ) {
					$all_images = array_merge( $all_images, $attach_ids );
				}
			}
			return $all_images;
		}

		/**
		 * Get required image sizes based
		 *
		 * @param array $img_ids  on array of image IDs.
		 */
		public function get_all_image_sizes( $img_ids ) {
			$images = array();
			if ( ! empty( $img_ids ) ) {
				foreach ( $img_ids as $img_id ) {
					if ( 'placeholder' == $img_id ) {
						$images[] = array(
							'large' => array( wc_placeholder_img_src( 'large' ) ),
							'single' => array( wc_placeholder_img_src( 'shop_single' ) ),
							'thumb' => array( wc_placeholder_img_src( 'thumbnail' ) ),
							'alt' => '',
							'title' => '',
						);
					} else {
						if ( ! array_key_exists( $img_id, $images ) ) {
							$attachment = $this->wp_get_attachment( $img_id );
							$images[] = array(
								'large' => wp_get_attachment_image_src( $img_id, 'large' ),
								'single' => wp_get_attachment_image_src( $img_id, apply_filters( 'single_product_large_thumbnail_size', 'shop_single' ) ),
								'thumb' => wp_get_attachment_image_src( $img_id, 'thumbnail' ),
								'alt' => $attachment['alt'],
								'title' => $attachment['title'],
							);
						}
					}
				}
			}
			return $images;
		}

		/**
		 * Get required image based on attachment id
		 *
		 * @param int $attachment_id  Attachment ID.
		 */
		public function wp_get_attachment( $attachment_id ) {
			$attachment = get_post( $attachment_id );
			return array(
				'alt' => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
				'caption' => $attachment->post_excerpt,
				'description' => $attachment->post_content,
				'href' => get_permalink( $attachment->ID ),
				'src' => $attachment->guid,
				'title' => $attachment->post_title,
			);
		}

		/**
		 * Update additional information to the order item
		 *
		 * @param int   $item_id  Item Id.
		 * @param mixed $values  Value.
		 */
		public function ink_pd_wdm_add_print_status_values_to_order_item_meta( $item_id, $values ) {
			wc_add_order_item_meta( $item_id, 'custom_design_id', $values['custom_design_id'] );
			if ( isset( $values['extra'] ) && ! empty( $values['extra'] ) ) {
				foreach ( $values['extra'] as $key => $value ) {
					wc_add_order_item_meta( $item_id, $key, $value );
				}
			}
		}

		/**
		 * Create order artwork file on order success.
		 *
		 * @param int $order_id  Order Id.
		 */
		public function create_order_files( $order_id ) {
			$store_id = get_current_blog_id() ? get_current_blog_id() : 1;
			$url = $this->api_path . 'api/v1/orders/create-order-files/' . $order_id . '?store_id=' . $store_id;
			$response = wp_remote_get( $url, array( 'headers' => 'x-impcode:' . $this->token ) );
			$result   = wp_remote_retrieve_body( $response );			
		}

		/**
		 * Method to add download-button on order page
		 *
		 * @param int $order_id  Order Id.
		 */
		public function ink_pd_action_woocommerce_order_item_add_action_buttons( $order_id ) {
			global $wpdb;
			$id = $order_id->id;
			$order_items = $wpdb->prefix . 'woocommerce_order_items';
			$order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';
			$items = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT order_item_id FROM  %1s WHERE order_id = %d',
					$order_items,
					$id
				)
			);
			$is_customize = 0;
			foreach ( $items as $item ) {
				$item_id = $item->order_item_id;
				$meta_id = $wpdb->get_var(
					$wpdb->prepare(
						"SELECT meta_id FROM %1s WHERE meta_key='custom_design_id' AND meta_value!='' AND order_item_id=%d",
						$order_item_meta,
						$item_id
					)
				);
				if ( $meta_id ) {
					$is_customize = 1;
				}
			}
			if ( 1 == $is_customize ) {
				?>
				<button type="button" onclick="downloadSVG()" class="button generate-items">Download Designs</button>;
				<?php
			} else {
				echo '';
			}

		}

		/**
		 * Method to add custom cart data
		 *
		 * @param obj $cart_item_meta  Cart Item meta.
		 * @param int $product_id  Product Id.
		 */
		public function ink_pd_add_cart_item_custom_data_vase( $cart_item_meta, $product_id ) {
			global $woocommerce;
			$refid = get_post_meta( $product_id, 'custom_design_id', true );
			if ( ! isset( $cart_item_meta['custom_design_id'] ) ) {
				$cart_item_meta['custom_design_id'] = $refid;
			}
			return $cart_item_meta;
		}

		/**
		 * Method to get item data
		 *
		 * @param array $item_data  Item data.
		 * @param array $cart_item  Cart item.
		 */
		public function ink_pd_filter_woocommerce_get_item_data( $item_data, $cart_item ) {
			$item_data = array();
			// Both Simple Product and configure product variation data.
			if ( is_array( $cart_item['variation'] ) ) {
				foreach ( $cart_item['variation'] as $name => $value ) {
					if ( '' === $value ) {
						continue;
					}
					$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
					// If this is a term slug, get the term's nice name.
					if ( taxonomy_exists( $taxonomy ) ) {
						$term = get_term_by( 'slug', $value, $taxonomy );
						if ( ! is_wp_error( $term ) && $term && $term->name ) {
							$value = $term->name;
						}
						$label = wc_attribute_label( $taxonomy );
						// If this is a custom option slug, get the options name.
					} else {
						$value = apply_filters( 'woocommerce_variation_option_name', $value );
						$product_attributes = $cart_item['data']->get_attributes();
						if ( isset( $product_attributes[ str_replace( 'attribute_', '', $name ) ] ) ) {
							$label = wc_attribute_label( str_replace( 'attribute_', '', $name ) );
						} else {
							$label = $name;
						}
					}
					$item_data[] = array(
						'key' => $label,
						'value' => $value,
					);
				}
			}
			// Format item data ready to display.
			foreach ( $item_data as $key => $data ) {
				// Set hidden to true to not display meta on cart.
				if ( ! empty( $data['hidden'] ) ) {
					unset( $item_data[ $key ] );
					continue;
				}
				$item_data[ $key ]['key'] = ! empty( $data['key'] ) ? $data['key'] : $data['name'];
				$item_data[ $key ]['display'] = ! empty( $data['display'] ) ? $data['display'] : $data['value'];
			}
			return $item_data;
		}

		/**
		 * Method to add custom price
		 *
		 * @param obj $cart_object  Cart object.
		 */
		public function ink_pd_add_custom_total_price( $cart_object ) {
			foreach ( $cart_object->get_cart() as $key => $value ) {
				$product_id = $value['product_id'];
				$variant_id = $value['variation_id'];
				$quantity = $value['quantity'];
				// For Imprintnext tier pricing.
				if ( ! $value['custom_design_id'] ) {
					$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
					$tier_price_data = array();
					$common_tier_price = array();
					$variant_tier_price = array();
					$price = 0;
					$variant_price = 0;
					$same_for_all_variants = false;
					$is_tier = false;
					if ( ! empty( $meta_data_content ) ) {
						$tier_price_data = $meta_data_content[0];
						$is_tier = true;
						if ( 'true' == $tier_price_data['pricing_per_variants'] ) {
							$same_for_all_variants = true;
							foreach ( $tier_price_data['price_rules'][0]['discounts'] as $discount ) {
								$common_tier_price[] = array(
									'upper_limit' => $discount['upper_limit'],
									'lower_limit' => $discount['lower_limit'],
									'discount' => $discount['discount'],
									'discountType' => $tier_price_data['discount_type'],
								);
							}
						} else {
							foreach ( $tier_price_data['price_rules'] as $variant ) {
								foreach ( $variant['discounts'] as $discount ) {
									$variant_tier_price[ $variant['id'] ][] = array(
										'upper_limit' => $discount['upper_limit'],
										'lower_limit' => $discount['lower_limit'],
										'discount' => $discount['discount'],
										'discountType' => $tier_price_data['discount_type'],
									);
								}
							}
						}
					}
					if ( $is_tier ) {
						$price = $value['data']->get_price();
						$variant_price = ( true === $same_for_all_variants ? $this->get_price_after_tier_discount( $common_tier_price, $price, $quantity ) : $this->get_price_after_tier_discount( $variant_tier_price[ $variant_id ], $price, $quantity ) );
						$value['data']->set_price( $variant_price );
					}
				}
				if ( '' != $value['_other_options']['product-price'] ) {
					$value['data']->set_price( $value['_other_options']['product-price'] );
				}
			}
		}

		/**
		 * Method to get preview images
		 *
		 * @param int $custom_design_id  Custom design id.
		 * @param int $product_id  Product id.
		 */
		public function get_customize_preview_images_details( $custom_design_id, $product_id ) {
			$result = array();
			$result['httpCode'] = 0;
			$xepath = $this->api_path;
			$url = $xepath . 'api/v1/preview-images?custom_design_id=' . $custom_design_id . '&product_id=' . $product_id;
			$response = wp_remote_get( $url, array( 'headers' => 'x-impcode:' . $this->token ) );
			$server_output   = wp_remote_retrieve_body( $response );
			$result['httpCode'] = wp_remote_retrieve_response_code( $response );
			$result['customPreviewImagesData'] = $server_output;
			return $result;
		}

		/**
		 * Method to get customize product image
		 *
		 * @param mixed $product_customize_image  Custom image string.
		 * @param array $cart_item  cart item.
		 * @param mixed $cart_item_key  cart item key.
		 */
		public function ink_pd_inkxe_customize_product_image( $product_customize_image, $cart_item, $cart_item_key ) {
			$ref_id = $cart_item['custom_design_id'];
			$product_id = $cart_item['variation_id'];
			if ( 0 == $product_id ) {
				$product_id = $cart_item['product_id'];
			}
			if ( $ref_id ) {
				$result = $this->get_customize_preview_images_details( $ref_id, $product_id );
				if ( 200 == $result['httpCode'] ) {
					$customize_data = json_decode( $result['customPreviewImagesData'], true );
					if ( ! array_key_exists( 'status', $customize_data ) ) {
						$product_customize_image = '';
						$i = 0;
						$is_print = 1;
						foreach ( $customize_data[ $ref_id ] as $key => $value ) {
							// For custom size.
							if ( $is_print ) {
								if ( $value['variableDecorationSize'] ) {
									?>
									<b>Custom Size: <?php echo esc_attr( $value['variableDecorationSize'] ) . ' ' . esc_attr( $value['variableDecorationUnit'] ); ?></b>;
									<?php
									$is_print = 0;
								}
							}
							$class = 'attachment-shop_thumbnail wp-post-image';
							// Default cart thumbnail class.
							$src = $value['customImageUrl'][ $i ];
							$product_customize_image .= '<img';
							$product_customize_image .= ' src="' . $src . '"';
							$product_customize_image .= ' class="' . $class . '"';
							$product_customize_image .= ' width="75" height="75" />';
							$i++;
						}
					}
				}
			}
			return $product_customize_image;
		}

		/**
		 * Method to add edit button
		 *
		 * @param mixed $product_get_name  Custom image string.
		 * @param array $cart_item  cart item.
		 * @param mixed $cart_item_key  cart item key.
		 */
		public function ink_pd_add_edit_info_button( $product_get_name, $cart_item, $cart_item_key ) {
			if ( is_cart() ) {
				global $wpdb;
				$xepath = get_site_url();
				$ref_id = $cart_item['custom_design_id'];
				$simple_product_id = $cart_item['variation_id'];
				$product_id = $cart_item['product_id'];
				if ( 0 == $simple_product_id ) {
					$simple_product_id = $product_id;
				}
				$quantity = $cart_item['quantity'];
				$result = $this->get_customize_preview_images_details( $ref_id, $simple_product_id );
				if ( 200 == $result['httpCode'] ) {
					$customize_data = json_decode( $result['customPreviewImagesData'], true );
					$a = '';
					$value = $customize_data[ $ref_id ][0];
					$is_name_and_number = $value['nameAndNumber'];
					$display_edit = $value['display_edit'];
					$wc_attributes = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
					$sql = "SELECT attribute_name FROM $wc_attributes WHERE attribute_label = '" . $value['sizeAttr'] . "'";
					$items = $wpdb->get_results( $sql );
					$vid = $value['simpleProductId'];
					?>
					<br/><br/>
					<?php
					$api_url = $this->ink_pd_get_api_url();
					$setting_url = $api_url . 'api/v1/settings/carts';
					$setting_array = $this->get_general_setting( $setting_url );
					$cart_edit_enabled = $setting_array['is_enabled'];
					$action = $setting_array['cart_item_edit_setting'] ? $setting_array['cart_item_edit_setting'] : 'add';
					if ( $display_edit ) {
						if ( $cart_edit_enabled ) {
							$url = $xepath . '/product-designer/?id=' . $product_id . '&vid=' . $vid . '&dpid=' . $ref_id . '&qty=1&cart_item_id=' . $cart_item_key . '&action=' . $action;
							?>
							<div id='editButton<?php echo esc_attr( $cart_item_key ); ?>'><a type='button' data-toggle='tooltip'  title='Edit' class='btn button btn-primary'  href='<?php echo esc_url_raw( $url ); ?>'>Edit</a><i class='icon-edit'></i></div>
							<?php
						}
					}
				}
			}
			return $product_get_name;
		}

		/**
		 * Multiple shipping address management
		 */
		public function create_table_shipping_address() {
			global $wpdb;
			$table = $wpdb->prefix . 'multipleshippingaddress';
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) );
			if ( $wpdb->get_var( $query ) != $table ) {
				$sql = "CREATE TABLE IF NOT EXISTS $table (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`first_name` varchar(50) NOT NULL,
				`last_name` varchar(50) NOT NULL,
				`address_line_one` varchar(200) NOT NULL,
				`address_line_two` varchar(200) NOT NULL,
				`company` varchar(200) NOT NULL,
				`city` varchar(50) NOT NULL,
				`postcode` varchar(10) NOT NULL,
				`country` varchar(7) NOT NULL,
				`state` varchar(7) NOT NULL,
				`mobile_no` varchar(13) NOT NULL,
				`user_id` int(11) NOT NULL,
				`is_default` int(11) NOT NULL DEFAULT '0',
				 PRIMARY KEY  (id)
				)ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8";
				dbDelta( $sql );
			}
			delete_option( 'imprintnext_setup' );
		}

		/**
		 * Method to get order list
		 *
		 * @param obj $request  Request Object.
		 */
		public function inkxe_get_orders( $request ) {
			global $wpdb;
			$page = $request['per_page'] * ( $request['page'] - 1 );
			$post_per_page = $request['per_page'];
			$search_str = explode( ' ', trim( $request['search'] ) );
			$search = addslashes( $search_str[0] );
			$filter = addslashes( $request['sku'] );
			$print_type = $request['print_type'];
			$is_customize = $request['is_customize'];
			$last_order_id = $request['last_id'];
			$order_by = ( '' != $request['order_by'] ) ? $request['order_by'] : 'xe_id';
			$order = $request['order'];
			$from = $request['from'];
			$to = $request['to'];
			$order_status = $request['order_status'];
			$customer_id = $request['customer_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_query = ( 'xe_id' == $order_by ) ? 'ORDER BY p.post_date' : 'ORDER BY pm.meta_value';
			$date_range_query = ( '' != $from && '' != $to ) ? ' AND p.post_date >= "' . $from . '" AND p.post_date <= "' . $to . '"' : '';
			$query_by_id = ( 0 != $last_order_id ) ? ' AND p.ID > ' . $last_order_id : '';
			$status_query = ( '' == $order_status || 'kiosk' == $order_status ) ? " AND p.post_status != 'trash'" : " AND p.post_status = 'trash'";
			$table_order = $wpdb->prefix . 'posts';
			$table_order_meta = $wpdb->prefix . 'postmeta';
			$table_order_item = $wpdb->prefix . 'woocommerce_order_items';
			$table_order_item_meta = $wpdb->prefix . 'woocommerce_order_itemmeta';
			$sql = "SELECT DISTINCT p.ID, p.post_date, p.post_status FROM $table_order as p INNER JOIN $table_order_meta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total' OR pm.meta_key='_customer_user') AND pm.meta_key != 'kiosk_order') INNER JOIN $table_order_item as woi ON pm.post_id = woi.order_id INNER JOIN $table_order_item_meta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order'" . $status_query;

			$count = "SELECT COUNT(DISTINCT p.ID) as total FROM $table_order as p INNER JOIN $table_order_meta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total' OR pm.meta_key='_customer_user')) INNER JOIN $table_order_item as woi ON pm.post_id = woi.order_id INNER JOIN $table_order_item_meta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order'" . $status_query;

			if ( 'kiosk' == $order_status ) {
				$count = "SELECT COUNT(DISTINCT *) FROM $table_order as p INNER JOIN $table_order_meta as pm ON (p.ID = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $table_order_item as woi ON pm.post_id = woi.order_id INNER JOIN $table_order_item_meta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
				$sql = "SELECT DISTINCT * FROM $table_order as p INNER JOIN $table_order_meta as pm ON (p.ID = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $table_order_item as woi ON pm.post_id = woi.order_id INNER JOIN $table_order_item_meta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
				$custom_orders = $sql;
			} else {
				$custom_orders = "SELECT DISTINCT p.ID FROM $table_order as p INNER JOIN $table_order_meta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total') AND pm.meta_key != 'kiosk_order') INNER JOIN $table_order_item as woi ON pm.post_id = woi.order_id INNER JOIN $table_order_item_meta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
			}

			if ( isset( $customer_id ) && 0 != $customer_id ) {
				$sql .= " AND pm.meta_key = '_customer_user' AND pm.meta_value = '$customer_id'";
				$count .= " AND pm.meta_key = '_customer_user' AND pm.meta_value = '$customer_id'";
			}

			if ( isset( $is_customize ) && 0 != $is_customize ) {
				$sql .= " AND woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0";
				$count .= " AND woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0";
			}

			if ( isset( $print_type ) && '' != $print_type ) {
				$sql .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN ($print_type)";
				$count .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN ($print_type)";
				$custom_orders .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN ($print_type)";
			}

			if ( isset( $filter ) && '' != $filter ) {
				$product_id = wc_get_product_id_by_sku( $filter );
				$sql .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
				$count .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
				$custom_orders .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
			}

			if ( isset( $search ) && '' != $search ) {
				$sql .= " AND (p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%'))";
				$count .= " AND (p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%'))";
				$custom_orders .= " AND p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%')";
			}

			$sql .= " $date_range_query $order_query $order LIMIT $page, $post_per_page";
			$count .= " $date_range_query $order_query $order";
			$total_records = $wpdb->get_results( $count );
			$custom_orders .= " $date_range_query $order_query $order";
			$custom_order_list = $wpdb->get_results( $custom_orders, ARRAY_A );
			$custom_order_list = array_column( $custom_order_list, 'ID' );
			$result = $wpdb->get_results( $sql );
			$response = array();
			$output['total_records'] = $total_records[0]->total;
			foreach ( $result as $key => $value ) {
				$customer_id = get_post_meta( $value->ID, '_customer_user', true );
				$first_name = '';
				$last_name = '';
				if ( 0 != $customer_id ) {
					$user = get_user_by( 'id', $customer_id );
					$user_name = explode( ' ', $user->display_name );
					$first_name = $user->first_name;
					$last_name = $user->last_name;
				}
				$response[ $key ]['id'] = $value->ID;
				$response[ $key ]['order_number'] = $value->ID;
				$order = wc_get_order( $value->ID );
				$response[ $key ]['order_total_quantity'] = $order->get_item_count();
				$response[ $key ]['customer_first_name'] = ( '' != $first_name ) ?
					$first_name : get_post_meta( $value->ID, '_billing_first_name', true );
				$response[ $key ]['customer_last_name'] = ( '' != $last_name ) ?
					$last_name : get_post_meta( $value->ID, '_billing_last_name', true );
				$response[ $key ]['created_date'] = $value->post_date;
				$response[ $key ]['total_amount'] = get_post_meta( $value->ID, '_order_total', true );
				$response[ $key ]['currency'] = get_post_meta( $value->ID, '_order_currency', true );
				$response[ $key ]['is_customize'] = 0;
				if ( in_array( $value->ID, $custom_order_list ) ) {
					$response[ $key ]['is_customize'] = 1;
				}
				$response[ $key ]['production'] = '';
				$response[ $key ]['status'] = substr( $value->post_status, 3 );
			}
			if ( 'xe_id' != $order_by ) {
				$customer_first_name = array_column( $response, 'customer_first_name' );
				$customer_first_name = array_map( 'strtolower', $customer_first_name );
				array_multisort( $customer_first_name, ( 'desc' == strtolower( $order ) ) ? SORT_DESC : SORT_ASC, $response );
			}
			$output['order_details'] = $response;
			return rest_ensure_response( $output );
		}

		function imp_get_orders_latest_version_wc($request) {
	        global $wpdb;
	        $page = $request['per_page'] * ($request['page'] - 1);
	        $post_per_page = $request['per_page'];
	        $searchStr = explode(" ", trim($request['search']));
	        $search = addslashes($searchStr[0]);
	        $filter = addslashes($request['sku']);
	        $print_type = $request['print_type'];
	        if (!empty($print_type)) {
	        	$print_type = implode(",",json_decode($print_type,true));
	        }
	        $is_customize = $request['is_customize'];
	        $last_order_id = $request['last_id'];
	        $order_by = ($request['order_by'] != '') ? $request['order_by'] : 'xe_id';
	        $order = $request['order'];
	        $from = $request['from'];
	        $to = $request['to'];
	        $order_status = $request['order_status'];
			$customer_id = $request['customer_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if (is_multisite()) {
				switch_to_blog($store_id);
			}
	        $order_query = ($order_by == 'xe_id') ? 'ORDER BY p.date_created_gmt' : 'ORDER BY woa.first_name';
	        $date_range_query = ($from != '' && $to != '') ? ' AND p.date_created_gmt >= "' . $from . '" AND p.date_created_gmt <= "' . $to . '"' : '';
	        $status_query = ($order_status == '' || $order_status == 'kiosk') ? " AND p.status != 'trash'" : " AND p.status = 'trash'";
	        //$tableOrder = $wpdb->prefix . "posts";
	        $tableOrder = $wpdb->prefix . "wc_orders";
	        $tableOrderMeta = $wpdb->prefix . "postmeta";
	        $tableOrderAddress = $wpdb->prefix . "wc_order_addresses";
	        $tableOrderItem = $wpdb->prefix . "woocommerce_order_items";
	        $tableOrderItemMeta = $wpdb->prefix . "woocommerce_order_itemmeta";

	        $sql .= "select DISTINCT p.id, p.customer_id, p.date_created_gmt, p.total_amount, p.currency, p.status, woa.first_name, woa.last_name from $tableOrder as p INNER JOIN $tableOrderItem as woi ON p.id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id INNER JOIN $tableOrderAddress  as woa ON p.id = woa.order_id where status != 'wc-checkout-draft'".$status_query;
	        $count .= "select COUNT(DISTINCT p.id) as total from $tableOrder as p  INNER JOIN $tableOrderItem as woi ON p.id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id where p.status != 'wc-checkout-draft'".$status_query;

	        if ($order_status == 'kiosk') {
	            $count = "SELECT COUNT(DISTINCT *) FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.id = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
	            $sql = $customOrders = "SELECT DISTINCT * FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.id = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
	        } else {
	        	$customOrders = "SELECT DISTINCT id FROM $tableOrder as p INNER JOIN $tableOrderItem as woi ON p.id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.status != 'wc-checkout-draft' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0')";            
	        }


	        if (isset($customer_id) && $customer_id != 0) {
	            $sql .= " AND p.customer_id = '$customer_id'";
	            $count .= " AND p.customer_id = '$customer_id'";
	        }

	        if (isset($print_type) && $print_type != '') {
	            $sql .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN (" . $print_type . ")";
	            $count .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN (" . $print_type . ")";
	        } else {
	        	if (isset($is_customize) && $is_customize != 0) {
		            $sql .= " AND (woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0 OR woim.meta_key = 'promotional_id')";
		            $count .= " AND (woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0 OR woim.meta_key = 'promotional_id')";
		        }
	        }

	        if (isset($filter) && $filter != '') {
	            $product_id = wc_get_product_id_by_sku($filter);
	            $sql .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
	            $count .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
	            $customOrders .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
	        }

	        if (isset($search) && $search != '') {
	            $sql .= " AND (p.id LIKE '%$search%' OR (woa.first_name LIKE '%$search%') OR (woa.last_name LIKE '%$search%'))";
	            $count .= " AND (p.id LIKE '%$search%' OR (woa.first_name LIKE '%$search%') OR (woa.last_name LIKE '%$search%'))";
	            $customOrders .= " AND p.id LIKE '%$search%' OR (woa.first_name LIKE '%$search%') OR (woa.last_name LIKE '%$search%')";
	        }
			

	        $sql .= " $date_range_query $order_query $order LIMIT $page, $post_per_page";
	        $count .= " $date_range_query $order_query $order";
	        $totalRecords = $wpdb->get_results($count);
	        $customOrders .= " $date_range_query $order_query $order";
	        $customOrderList = $wpdb->get_results($customOrders, ARRAY_A);
	        $customOrderList = array_column($customOrderList, 'id');
	        $result = $wpdb->get_results($sql);
	        $response = array();
	        $output['total_records'] = $totalRecords[0]->total;
	        if (!empty($result)) {
		        foreach ($result as $key => $value) {
					$response[$key]['id'] = $value->id;
					$response[$key]['order_number'] = $value->id;
					$order = wc_get_order($value->id);
					$response[$key]['order_total_quantity'] = $order->get_item_count();
					$response[$key]['customer_id']	= $value->customer_id;
					$response[$key]['customer_first_name'] = $value->first_name;
					$response[$key]['customer_last_name'] = $value->last_name;
					$response[$key]['created_date'] = $value->date_created_gmt;
					$response[$key]['total_amount'] = $value->total_amount;
					$response[$key]['currency'] = $value->currency;
					$response[$key]['is_customize'] = 0;
					if (in_array($value->id, $customOrderList)) {
						$response[$key]['is_customize'] = 1;
					}
					$response[$key]['production'] = '';
					$response[$key]['status'] = substr($value->status, 3);
				}
	        } else {
	        	// this is for upgraded version for wc
	        	$order_query = ($order_by == 'xe_id') ? 'ORDER BY p.post_date' : 'ORDER BY pm.meta_value';
		        //$meta_query = ($order_by=='customer')?'_billing_first_name':'_order_total';
		        $date_range_query = ($from != '' && $to != '') ? ' AND p.post_date >= "' . $from . '" AND p.post_date <= "' . $to . '"' : '';
		        $status_query = ($order_status == '' || $order_status == 'kiosk') ? " AND p.post_status != 'trash'" : " AND p.post_status = 'trash'";

		        $tableOrder = $wpdb->prefix . "posts";
		        $tableOrderMeta = $wpdb->prefix . "postmeta";
		        $tableOrderItem = $wpdb->prefix . "woocommerce_order_items";
		        $tableOrderItemMeta = $wpdb->prefix . "woocommerce_order_itemmeta";
		        
		        $sql = "SELECT DISTINCT p.ID, p.post_date, p.post_status FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total' OR pm.meta_key='_customer_user') AND pm.meta_key != 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order'".$status_query;

		        $count = "SELECT COUNT(DISTINCT p.ID) as total FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total' OR pm.meta_key='_customer_user')) INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order'".$status_query;

		        if ($order_status == 'kiosk') {
		            $count = "SELECT COUNT(DISTINCT *) FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.ID = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
		            $sql = $customOrders = "SELECT DISTINCT * FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.ID = pm.post_id AND pm.meta_key = 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND woim.meta_key = 'custom_design_id' AND (woim.meta_value != '' AND woim.meta_value != '0') ";
		        }else{
		            $customOrders = "SELECT DISTINCT p.ID FROM $tableOrder as p INNER JOIN $tableOrderMeta as pm ON (p.ID = pm.post_id AND (pm.meta_key = '_billing_first_name' OR pm.meta_key = '_billing_last_name' OR pm.meta_key = '_order_total') AND pm.meta_key != 'kiosk_order') INNER JOIN $tableOrderItem as woi ON pm.post_id = woi.order_id INNER JOIN $tableOrderItemMeta as woim ON woi.order_item_id = woim.order_item_id WHERE p.post_type = 'shop_order' AND (woim.meta_key = 'custom_design_id' OR woim.meta_key = 'promotional_id') AND (woim.meta_value != '' AND woim.meta_value != '0') ";
		            
		        }


		        if (isset($customer_id) && $customer_id != 0) {
		            $sql .= " AND pm.meta_key = '_customer_user' AND pm.meta_value = '$customer_id'";
		            $count .= " AND pm.meta_key = '_customer_user' AND pm.meta_value = '$customer_id'";
		        }

		        if (isset($print_type) && $print_type != '') {
		            $sql .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN (" . $print_type . ")";
		            $count .= " AND woim.meta_key = 'print_type' AND woim.meta_value IN (" . $print_type . ")";
		        } else {
		        	if (isset($is_customize) && $is_customize != 0) {
			            $sql .= " AND (woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0 OR woim.meta_key = 'promotional_id')";
			            $count .= " AND (woim.meta_key = 'custom_design_id' AND woim.meta_value != '' AND woim.meta_value != 0 OR woim.meta_key = 'promotional_id')";
			        }
		        }

		        if (isset($filter) && $filter != '') {
		            $product_id = wc_get_product_id_by_sku($filter);
		            $sql .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
		            $count .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
		            $customOrders .= " AND woim.meta_key = '_product_id' AND woim.meta_value = $product_id";
		        }

		        if (isset($search) && $search != '') {
		            $sql .= " AND (p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%'))";
		            $count .= " AND (p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%'))";
		            $customOrders .= " AND p.ID LIKE '%$search%' OR (pm.meta_key = '_billing_first_name' AND pm.meta_value LIKE '%$search%') OR (pm.meta_key = '_billing_last_name' AND pm.meta_value LIKE '%$search%')";
		        }

		        $sql .= " $date_range_query $order_query $order LIMIT $page, $post_per_page";
		        $count .= " $date_range_query $order_query $order";
		        $totalRecords = $wpdb->get_results($count);
		        $customOrders .= " $date_range_query $order_query $order";
		        $customOrderList = $wpdb->get_results($customOrders, ARRAY_A);
		        $customOrderList = array_column($customOrderList, 'ID');
		        $result = $wpdb->get_results($sql);
		        $response = array();
		        $output['total_records'] = $totalRecords[0]->total;
		        foreach ($result as $key => $value) {
					$customer_id = get_post_meta($value->ID, '_customer_user', true);
					$first_name = "";
					$last_name = "";
					if( $customer_id!=0 ){
						$user = get_user_by('id', $customer_id);
		                $first_name = $user->first_name;
						$last_name = $user->last_name;
					}
					$response[$key]['id'] = $value->ID;
					$response[$key]['order_number'] = $value->ID;
					$order = wc_get_order($value->ID);
					$response[$key]['order_total_quantity'] = $order->get_item_count();
					$response[$key]['customer_id']	= $customer_id;
					$response[$key]['customer_first_name'] = ($first_name != "") ? 
															$first_name : get_post_meta($value->ID, '_billing_first_name', true);
					$response[$key]['customer_last_name'] = ($last_name != "") ? 
															$last_name : get_post_meta($value->ID, '_billing_last_name', true);
					$response[$key]['created_date'] = $value->post_date;
					$response[$key]['total_amount'] = get_post_meta($value->ID, '_order_total', true);
					$response[$key]['currency'] = get_post_meta($value->ID, '_order_currency', true);
					$response[$key]['is_customize'] = 0;
					if (in_array($value->ID, $customOrderList)) {
						$response[$key]['is_customize'] = 1;
					}
					$response[$key]['production'] = '';
					$response[$key]['status'] = substr($value->post_status, 3);
				}
		        if ($order_by != 'xe_id') {
		            $customer_first_name = array_column($response, 'customer_first_name');
		            $customer_first_name = array_map('strtolower', $customer_first_name);
		            array_multisort($customer_first_name, (strtolower($order) == 'desc') ? SORT_DESC : SORT_ASC, $response);
		        }
	        }

	        $output['order_details'] = $response;
	        return rest_ensure_response($output);
	    }

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_order_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['page'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to get max number of records' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1,
			);
			$args['per_page'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to get max number of records ' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1000,
			);
			$args['sku'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter based on product sku' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['print_type'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter base on print methods' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => '',
			);
			$args['is_customize'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter customized orders' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['order_by'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used sort the response' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'xe_id',
			);
			$args['order'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used for sort order' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'DESC',
			);
			$args['search'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['from'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['to'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['customer_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['order_status'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);

			$args['order_status'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['store_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter order list by store id', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1,
			);
			return $args;
		}

		/**
		 * Method to get product list
		 *
		 * @param obj $request  Request Object.
		 */
		public function inkxe_get_products( $request ) {
			global $wpdb;
			$page = $request['range'] * ( $request['page'] - 1 );
			$post_per_page = $request['range'];
			$search = addslashes( $request['search'] );
			$category = $request['category'];
			$is_customize = $request['is_customize'];
			$is_catalog = $request['is_catalog'];
			$last_product_id = $request['last_id'];
			$order_by = $request['order_by'];
			$order = $request['order'];
			$fetch = $request['fetch'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$is_admin = $request['is_admin'] ? $request['is_admin'] : 0;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_query = ( 'post_date' == $order_by ) ? 'ORDER BY p.post_date' : 'ORDER BY p.post_title';
			$join_query = ' p.ID = pm.post_id';
			if ( 'top' == $request['order_by'] ) {
				$join_query = " (p.ID = pm.post_id AND pm.meta_key='total_sales')";
				$order_query = 'ORDER BY pm.meta_value';
			}
			$sub_cat_array = array();
			if ( isset( $category ) && '' != $category ) {
				$cat_array = explode( ',', $category );
				foreach ( $cat_array as $cat ) {
					$sub_cat_array[] = $cat;
					$sub_cat = get_terms( 'product_cat', array( 'child_of' => $cat ) );
					foreach ( $sub_cat as $sub ) {
						$sub_cat_array[] = $sub->term_id;
					}
				}
			}
			$category = ! empty( $sub_cat_array ) ? implode( ',', $sub_cat_array ) : $category;
			$table_product = $wpdb->prefix . 'posts';
			$table_product_meta = $wpdb->prefix . 'postmeta';
			$table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
			$table_term_relationship = $wpdb->prefix . 'term_relationships';
			$result1 = $wpdb->get_results( "SELECT DISTINCT p.ID FROM $table_product as p LEFT JOIN $table_product_meta as pm ON p.ID = pm.post_id AND pm.meta_key = 'custom_design_id' WHERE p.post_type = 'product' AND p.post_status = 'publish' AND pm.meta_key = 'custom_design_id' AND pm.meta_value != ''", ARRAY_N );
			$res = implode(
				',',
				array_map(
					function ( $a ) {
						return implode(
							',',
							$a
						);
					},
					$result1
				)
			);

			$result_catalog = $wpdb->get_results( "SELECT DISTINCT p.ID FROM $table_product as p LEFT JOIN $table_product_meta as pm ON p.ID = pm.post_id AND pm.meta_key = 'is_catalog' WHERE p.post_type = 'product' AND p.post_status = 'publish' AND pm.meta_key = 'is_catalog' AND pm.meta_value != ''", ARRAY_N );
			$res_catalog = implode(
				',',
				array_map(
					function ( $a ) {
						return implode(
							',',
							$a
						);
					},
					$result_catalog
				)
			);

			$sql = "SELECT DISTINCT p.ID, p.post_title, p.post_date, p.post_status FROM $table_product as p INNER JOIN $table_product_meta as pm ON $join_query INNER JOIN $table_term_relationship as tr ON pm.post_id = tr.object_id INNER JOIN $table_term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE p.post_type = 'product' AND p.post_status = 'publish'";
			$count = "SELECT COUNT(DISTINCT p.ID) as total FROM $table_product as p INNER JOIN $table_product_meta as pm ON $join_query INNER JOIN $table_term_relationship as tr ON pm.post_id = tr.object_id INNER JOIN $table_term_taxonomy as tt ON tr.term_taxonomy_id = tt.term_taxonomy_id WHERE p.post_type = 'product' AND p.post_status = 'publish'";
			if ( isset( $category ) && '' != $category ) {
				$sql .= ' AND tt.term_id IN (' . $category . ')';
				$count .= ' AND tt.term_id IN (' . $category . ')';
			}

			if ( empty( $fetch ) ) {
				if ( isset( $is_customize ) && 0 != $is_customize ) {
					$sql .= " AND pm.meta_key = 'custom_design_id' AND pm.meta_value != ''";
					$count .= " AND pm.meta_key = 'custom_design_id' AND pm.meta_value != ''";
				}
				if ( isset( $is_catalog ) && 0 != $is_catalog ) {
					$sql .= " AND pm.meta_key = 'is_catalog' AND pm.meta_value != ''";
					$count .= " AND pm.meta_key = 'is_catalog' AND pm.meta_value != ''";
				}
				if ( isset( $is_catalog ) && 0 == $is_catalog && '' != $result_catalog ) {
					$sql .= " AND p.ID NOT IN ($result_catalog)";
					$count .= " AND p.ID NOT IN ($result_catalog)";
				}
				if ( isset( $is_customize ) && 0 == $is_customize && '' != $res ) {
					$sql .= " AND p.ID NOT IN ($res)";
					$count .= " AND p.ID NOT IN ($res)";
				}
			}
			if ( isset( $search ) && '' != $search ) {
				$sql .= " AND p.post_title LIKE '%$search%' OR (pm.meta_key = '_sku' AND pm.meta_value LIKE '%$search%') ";
				$count .= " AND p.post_title LIKE '%$search%' OR (pm.meta_key = '_sku' AND pm.meta_value LIKE '%$search%') ";
			}
			$sql .= " $date_range_query $order_query $order LIMIT $page, $post_per_page";
			$total_records = $wpdb->get_results( $count );
			$result = $wpdb->get_results( $sql );
			$response = array();
			$output['total_records'] = $total_records[0]->total;

			foreach ( $result as $key => $value ) {
				$image_thumb = array();
				$product = wc_get_product( $value->ID );
				$response[ $key ]['id'] = $value->ID;
				$response[ $key ]['template_suffix'] = get_post_meta( $value->ID, 'is_customizable', true );
				$response[ $key ]['storefront_url'] = $product->get_permalink();
				$response[ $key ]['name'] = $value->post_title;
				$response[ $key ]['type'] = $product->get_type();
				$response[ $key ]['sku'] = get_post_meta( $value->ID, '_sku', true );
				$response[ $key ]['price'] = get_post_meta( $value->ID, '_price', true );
				if ( 1 != $product->get_manage_stock() && 'instock' == $product->get_stock_status() ) {
					$response[ $key ]['stock'] = 10000;
				} else {
					$response[ $key ]['stock'] = $product->get_stock_quantity();
				}
				if ( 1 == $is_admin ) {
					$response[ $key ]['is_sold_out'] = false;
				} else {
					if ( 1 == $product->get_manage_stock() && 'outofstock' == $product->get_stock_status() ) {
						$all_variation = $product->get_children();
						if ( ! empty( $all_variation ) ) {
							foreach ( $all_variation as $key_var => $product_variation_id ) {
								$variation_obj = wc_get_product( $product_variation_id );
								if ( 1 == $variation_obj->get_manage_stock() && 'outofstock' == $variation_obj->get_stock_status() ) {
									$response[ $key ]['is_sold_out'] = true;
								} else {
									if ( 0 == $variation_obj->get_stock_quantity() ) {
										$response[ $key ]['is_sold_out'] = true;
									} else {
										$response[ $key ]['is_sold_out'] = false;
										break;
									}
								}
							}
						} else {
							$response[ $key ]['is_sold_out'] = true;
						}
					} else {
						$all_variation = $product->get_children();
						if ( ! empty( $all_variation ) ) {
							foreach ( $all_variation as $key_var => $product_variation_id ) {
								$variation_obj = wc_get_product( $product_variation_id );
								if ( 1 == $variation_obj->get_manage_stock() && 'outofstock' == $variation_obj->get_stock_status() ) {
									$response[ $key ]['is_sold_out'] = true;
								} else {
									$response[ $key ]['is_sold_out'] = false;
									break;
								}
							}
						} else {
							$response[ $key ]['is_sold_out'] = false;
						}
					}
				}
				if ( 'all' == $fetch ) {
					$response[ $key ]['custom_design_id'] = get_post_meta( $value->ID, 'custom_design_id', true ) ? get_post_meta( $value->ID, 'custom_design_id', true ) : '';
					$response[ $key ]['is_decorated_product'] = get_post_meta( $value->ID, 'is_decorated_product', true ) ? get_post_meta( $value->ID, 'is_decorated_product', true ) : 0;
					$response[ $key ]['is_redesign'] = $product->get_attribute( 'pa_xe_is_designer' ) ? $product->get_attribute( 'pa_xe_is_designer' ) : 0;
				}
				$variation_id = $value->ID;
				$i = 0;
				if ( 'variable' == $product->get_type() ) {
					$args = array(
						'post_type' => 'product_variation',
						'post_status' => array( 'publish' ),
						'order' => 'ASC',
						'post_parent' => $value->ID, // get parent post-ID.
					);
					$variations = get_posts( $args );
					if ( ! empty( $variations ) ) {
						$variation_id = $variations[0]->ID;
						$variation = wc_get_product( $variation_id );
						$image_id = $variation->get_image_id();
						if ( 0 != $image_id ) {
							$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							$image_thumb[] = $image_src[0] . '?rvn=' . $i;
							$i++;
						}
						// New changes on product variation gallary.
						$gallery_image_ids = get_post_meta( $variation_id, '_product_image_gallery', true );
						if ( empty( $gallery_image_ids ) ) {
							$gallery_image_ids = get_post_meta( $variation_id, 'variation_image_gallery', true );
						}
						$gallery_image_ids = array_filter( explode( ',', $gallery_image_ids ) );
						foreach ( $gallery_image_ids as $id ) {
							$image_src = wp_get_attachment_image_src( $id, 'thumbnail' );
							$image_thumb[] = $image_src[0] . '?rvn=' . $i;
							$i++;
						}
					} else {
						$image_id = get_post_meta( $value->ID, '_thumbnail_id', true );
						if ( 0 != $image_id ) {
							$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							$image_thumb[] = $image_src[0] . '?rvn=' . $i;
							$i++;
						}
						$gallery_image_ids = get_post_meta( $value->ID, '_product_image_gallery', true );
						if ( '' != $gallery_image_ids ) {
							$gallery_image_ids = explode( ',', $gallery_image_ids );
							foreach ( $gallery_image_ids as $id ) {
								$image_src = wp_get_attachment_image_src( $id, 'thumbnail' );
								$image_thumb[] = $image_src[0] . '?rvn=' . $i;
								$i++;
							}
						}
					}
				} else {
					$image_id = get_post_meta( $value->ID, '_thumbnail_id', true );
					if ( 0 != $image_id ) {
						$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
						$image_thumb[] = $image_src[0] . '?rvn=' . $i;
						$i++;
					}
					$gallery_image_ids = get_post_meta( $value->ID, '_product_image_gallery', true );
					if ( '' != $gallery_image_ids ) {
						$gallery_image_ids = explode( ',', $gallery_image_ids );
						foreach ( $gallery_image_ids as $id ) {
							$image_src = wp_get_attachment_image_src( $id, 'full' );
							$image_thumb[] = $image_src[0] . '?rvn=' . $i;
							$i++;
						}
					}
				}
				// If product price is empty updated variation price.
				if ( empty( $response[ $key ]['price'] ) ) {
					$response[ $key ]['price'] = get_post_meta( $variation_id, '_price', true );
				}
				$response[ $key ]['variation_id'] = $variation_id;
				$response[ $key ]['image'] = $image_thumb;
				$response[ $key ]['printable'] = ( get_post_meta( $value->ID, 'is_customizable', true ) == 'imprint_designer' ) ? 1 : 0;
			}
			$output['products'] = $response;
			return rest_ensure_response( $output );
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_product_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['range'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['page'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1,
			);
			$args['search'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['catagory'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['order_by'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'post_date',
			);
			$args['order'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'DESC',
			);
			$args['is_customize'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['fetch'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to display all products', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['store_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to display all products per store wise', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => '',
			);
			$args['is_admin'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to display all products per store wise', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => '',
			);
			return $args;
		}

		/**
		 * Method to get attribute options list
		 *
		 * @param obj $request  Request Object.
		 */
		public function inkxe_get_attribute_options( $request ) {
			global $wpdb;
			$product_id = $request['product_id'];
			$attribute = $request['attribute'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$table_post = $wpdb->prefix . 'posts';
			$table_post_meta = $wpdb->prefix . 'postmeta';
			$meta_key = 'attribute_' . $attribute;
			$sql = "SELECT DISTINCT p.ID, pm.meta_value FROM $table_post as p INNER JOIN $table_post_meta as pm ON p.ID = pm.post_id WHERE p.post_type = 'product_variation' AND p.post_parent = $product_id AND pm.meta_key = '$meta_key'";
			$variants = $wpdb->get_results( $sql );
			if ( empty( $variants ) ) {
				$product = wc_get_product( $product_id );
				$variations = (array) $product->get_available_variations();
				$first_attribute = $variations[0]['attributes'];
				reset( $first_attribute );
				$first_key = key( $first_attribute );
				$attribute = str_replace( 'attribute_', '', $first_key );
				$sql = "SELECT DISTINCT p.ID, pm.meta_value FROM $table_post as p INNER JOIN $table_post_meta as pm ON p.ID = pm.post_id WHERE p.post_type = 'product_variation' AND p.post_parent = $product_id AND pm.meta_key = '$first_key'";
				$variants = $wpdb->get_results( $sql );
			}
			$table_term_options = $wpdb->prefix . 'terms';
			$table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
			$table_term_relationship = $wpdb->prefix . 'term_relationships';
			$sql = "SELECT DISTINCT t.term_id, t.name, t.slug FROM $table_term_options as t INNER JOIN $table_term_taxonomy as tt ON t.term_id = tt.term_id INNER JOIN $table_term_relationship as tr ON tt.term_taxonomy_id = tr.term_taxonomy_id WHERE tr.object_id = $product_id AND tt.taxonomy = '$attribute'";
			$result = $wpdb->get_results( $sql );
			$response = array();
			$key = 0;

			// For Tier Price.
			$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
			$tier_price_data = array();
			$common_tier_price = array();
			$variant_tier_price = array();
			$is_tier = false;
			$same_for_all_variants = $is_tier;
			if ( ! empty( $meta_data_content ) ) {
				$tier_price_data = $meta_data_content[0];
				$is_tier = true;

				if ( 'true' == $tier_price_data['pricing_per_variants'] ) {
					$same_for_all_variants = true;
					foreach ( $tier_price_data['price_rules'][0]['discounts'] as $discount ) {
						$common_tier_price[] = array(
							'quantity' => $discount['lower_limit'],
							'discount' => $discount['discount'],
							'discountType' => $tier_price_data['discount_type'],
						);
					}
				} else {
					foreach ( $tier_price_data['price_rules'] as $variant ) {
						foreach ( $variant['discounts'] as $discount ) {
							$variant_tier_price[ $variant['id'] ][] = array(
								'quantity' => $discount['lower_limit'],
								'discount' => $discount['discount'],
								'discountType' => $tier_price_data['discount_type'],
							);
						}
					}
				}
			}
			// End.

			foreach ( $result as $key => $value ) {
				$v_key = array_search( $value->slug, array_column( (array) $variants, 'meta_value' ) );
				if ( ! is_bool( $v_key ) ) {
					$response[ $key ]['id'] = $value->term_id;
					$response[ $key ]['slug'] = $value->slug;
					$response[ $key ]['name'] = $value->name;
					$response[ $key ]['variant_id'] = $variants[ $v_key ]->ID;
					$pvariant = wc_get_product( $variants[ $v_key ]->ID );
					if ( 1 != $pvariant->get_manage_stock() && 'instock' == $pvariant->get_stock_status() ) {
						$stock_quantity = 999999999;
					} else {
						$stock_quantity = $pvariant->get_stock_quantity();
					}
					$response[ $key ]['inventory']['stock'] = $stock_quantity;
					$response[ $key ]['inventory']['min_quantity'] = 1;
					$response[ $key ]['inventory']['max_quantity'] = $stock_quantity;
					$response[ $key ]['inventory']['quantity_increments'] = 1;
					$response[ $key ]['price'] = $pvariant->price;
					$i = 0;
					if ( 0 != $pvariant->image_id ) {
						$image_src = wp_get_attachment_image_src( $pvariant->image_id, 'full' );
						$image_src_thumb = wp_get_attachment_image_src( $pvariant->image_id, 'thumbnail' );
						$response[ $key ]['sides'][ $i ]['image']['src'] = $image_src[0];
						$response[ $key ]['sides'][ $i ]['image']['thumbnail'] = $image_src_thumb[0];
						$i++;
					}
					// New changes.
					$attachments = get_post_meta( $variants[ $v_key ]->ID, '_product_image_gallery', true );
					if ( empty( $attachments ) ) {
						$attachments = get_post_meta( $variants[ $v_key ]->ID, 'variation_image_gallery', true );
					}
					$attachments_exp = array_filter( explode( ',', $attachments ) );
					foreach ( $attachments_exp as $id ) {
						$image_src = wp_get_attachment_image_src( $id, 'full' );
						$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
						$response[ $key ]['sides'][ $i ]['image']['src'] = $image_src[0];
						$response[ $key ]['sides'][ $i ]['image']['thumbnail'] = $image_src_thumb[0];
						$i++;
					}
					if ( $is_tier ) {
						$response[ $key ]['tier_prices'] = ( true === $same_for_all_variants ? $this->create_tier_price( $common_tier_price, $pvariant->price ) : $this->create_tier_price( $variant_tier_price[ $variants[ $v_key ]->ID ], $pvariant->price ) );
					}
					$key++;
				}
			}
			return rest_ensure_response( $response );
		}

		/**
		 * Method to create tier price
		 *
		 * @param array $tier_price_rule  Price rule.
		 * @param mixed $variant_price  Variant price.
		 */
		public function create_tier_price( $tier_price_rule, $variant_price ) {
			$tier_price = array();
			foreach ( $tier_price_rule as $tier ) {
				$this_tier = array();
				$this_tier['quantity'] = $tier['quantity'];
				$this_tier['percentage'] = ( 'percentage' == $tier['discountType'] ? $tier['discount'] : number_format( ( $tier['discount'] / $variant_price ) * 100, 2 ) );
				$this_tier['price'] = ( 'flat' == $tier['discountType'] ? ( $variant_price - $tier['discount'] ) : ( $variant_price - ( ( $tier['discount'] / 100 ) * $variant_price ) ) );
				$this_tier['discount'] = $tier['discount'] . '_' . $tier['discountType'];
				$tier_price[] = $this_tier;
			}

			return $tier_price;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_options_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['product_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['attribute'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'pa_xe_color',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * Method to get attribute type
		 *
		 * @param int $id attribute id.
		 */
		public function get_attribute_type( $id ) {
			global $wpdb;
			$attribute = $wpdb->get_row(
				$wpdb->prepare(
					"
	                SELECT *
	                FROM {$wpdb->prefix}woocommerce_attribute_taxonomies
	                WHERE attribute_id = %d
	             	",
					$id
				)
			);
			return $attribute;
		}

		/**
		 * Method to get product attribute
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_product_attribute( $request ) {
			global $wpdb;
			$product_id = $request['product_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$product = wc_get_product( $product_id );
			$attributes = $product->get_attributes();
			$response = array();
			$i = 0;
			foreach ( $attributes as $attribute ) {
				$attribute_details = $this->get_attribute_type( $attribute['id'] );
				$attribute_type = $attribute_details->attribute_type;
				if ( 'xe_is_designer' != $attribute_details->attribute_label ) {
					$response[ $i ]['id'] = $attribute['id'];
					$response[ $i ]['name'] = $attribute_details->attribute_label;
					$j = 0;
					foreach ( $attribute['options'] as $option ) {
						$term = get_term_by( 'id', $option, 'pa_' . $attribute_details->attribute_name );
						$response[ $i ]['options'][ $j ]['id'] = $option;
						$response[ $i ]['options'][ $j ]['name'] = $term->name;
						$j++;
					}
					$i++;
				}
			}
			return rest_ensure_response( $response );
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_attributes_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['product_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_product_image_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['product_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['variant_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['details'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * Method to get product count
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_product_count( $request ) {
			global $wpdb;
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$table_product = $wpdb->prefix . 'posts';
			$sql = "SELECT COUNT(DISTINCT ID) as total FROM $table_product WHERE post_type = 'product' AND post_status = 'publish'";
			$total_records = $wpdb->get_results( $sql );
			$output['total'] = $total_records[0]->total;
			$output['vc'] = WC_VERSION;
			return rest_ensure_response( $output );
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function get_multiple_shipping_address_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['userId'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function delete_multiple_shipping_address_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function update_multiple_shipping_address_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['request'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function create_multiple_shipping_address_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['request'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);

			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function create_customer_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['request'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function update_customer_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['request'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['user_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function delete_customer_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['user_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function countries_arguments() {
			$args = array();
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function states_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['country_code'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function country_code_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['country_code'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function country_state_code_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['country_code'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['state_code'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function user_count_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['customer_no_order'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			$args['from_date'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			$args['to_date'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			$args['search'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['quote'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			$args['notification'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
			);
			return $args;
		}

		/**
		 * Method to get product count
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_category_products( $request ) {
			global $wpdb;
			$post_per_page = 10;
			$args = array(
				'taxonomy' => 'product_cat',
				'parent' => 0,
				'include' => $ids,
			);
			$product_categories = get_terms( $args );
			$j = 0;
			foreach ( $product_categories as $key => $value ) {
				$cats = array();
				$args = array(
					'taxonomy' => 'product_cat',
					'child_of' => $value->term_id,
					'include' => $ids,
				);
				$sub_categories = get_terms( $args );
				$cats[] = $value->slug;
				foreach ( $sub_categories as $subcat ) {
					$cats[] = $subcat->slug;
				}
				$products = wc_get_products(
					array(
						'numberposts' => 7,
						'post_status' => 'published',
						'category' => $cats,
					)
				);
				$response = array();
				foreach ( $products as $k => $product ) {
					$response[ $k ]['id'] = $product->id;
					$response[ $k ]['name'] = $product->name;
					$response[ $k ]['type'] = $product->get_type();
					$response[ $k ]['sku'] = $product->sku;
					$response[ $k ]['price'] = $product->price;
					$i = 0;
					$image_thumb = array();
					if ( 'variable' == $product->get_type() ) {
						$args = array(
							'post_type' => 'product_variation',
							'post_status' => array( 'publish' ),
							'post_parent' => $product->id,
						);
						$variations = get_posts( $args );
						if ( ! empty( $variations ) ) {
							$variation_id = $variations[0]->ID;
							$variation = wc_get_product( $variation_id );
							$image_id = $variation->get_image_id();
							if ( 0 != $image_id ) {
								$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
								$image_thumb[] = $image_src[0] . '?rvn=' . $i;
								$i++;
							}
							// New changes.
							$gallery_image_ids = get_post_meta( $variation_id, '_product_image_gallery', true );
							if ( empty( $gallery_image_ids ) ) {
								$gallery_image_ids = get_post_meta( $variation_id, 'variation_image_gallery', true );
							}
							$gallery_image_ids = array_filter( explode( ',', $gallery_image_ids ) );
							foreach ( $gallery_image_ids as $id ) {
								$image_src = wp_get_attachment_image_src( $id, 'thumbnail' );
								$image_thumb[] = $image_src[0] . '?rvn=' . $i;
								$i++;
							}
						} else {
							$image_id = get_post_meta( $product->id, '_thumbnail_id', true );
							if ( 0 != $image_id ) {
								$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
								$image_thumb[] = $image_src[0] . '?rvn=' . $i;
								$i++;
							}
							$gallery_image_ids = get_post_meta( $product->id, '_product_image_gallery', true );
							if ( '' != $gallery_image_ids ) {
								$gallery_image_ids = explode( ',', $gallery_image_ids );
								foreach ( $gallery_image_ids as $id ) {
									$image_src = wp_get_attachment_image_src( $id, 'thumbnail' );
									$image_thumb[] = $image_src[0] . '?rvn=' . $i;
									$i++;
								}
							}
						}
					} else {
						$image_id = get_post_meta( $product->id, '_thumbnail_id', true );
						if ( 0 != $image_id ) {
							$image_src = wp_get_attachment_image_src( $image_id, 'thumbnail' );
							$image_thumb[] = $image_src[0] . '?rvn=' . $i;
							$i++;
						}
						$gallery_image_ids = get_post_meta( $product->id, '_product_image_gallery', true );
						if ( '' != $gallery_image_ids ) {
							$gallery_image_ids = explode( ',', $gallery_image_ids );
							foreach ( $gallery_image_ids as $id ) {
								$image_src = wp_get_attachment_image_src( $id, 'full' );
								$image_thumb[] = $image_src[0] . '?rvn=' . $i;
								$i++;
							}
						}
					}
					$response[ $k ]['image'] = $image_thumb;
				}
				$output['categories'][ $j ]['id'] = $value->term_id;
				$output['categories'][ $j ]['name'] = $value->name;
				$output['categories'][ $j ]['products'] = $response;
				$j++;

			}
			return rest_ensure_response( $output );
		}

		/**
		 * Method to get product images
		 *
		 * @param obj $request Request object.
		 */
		public function product_images( $request ) {
			$product_id = $request['product_id'];
			$variant_id = $request['variant_id'];
			$details = $request['details'];
			$response = array();
			$id = ( $product_id != $variant_id ) ? $variant_id : $product_id;
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$product = wc_get_product( $id );
			$attributes = $product->get_attributes();
			$i = 0;
			if ( 0 != $product->image_id ) {
				$image_src = wp_get_attachment_image_src( $product->image_id, 'full' );
				$image_src_thumb = wp_get_attachment_image_src( $product->image_id, 'thumbnail' );
				$response['images'][ $i ]['src'] = $image_src[0];
				$response['images'][ $i ]['thumbnail'] = $image_src_thumb[0];
				$i++;
			}
			if ( $product_id != $variant_id ) {
				// New changes.
				$attachments = get_post_meta( $variant_id, '_product_image_gallery', true );
				if ( empty( $attachments ) ) {
					$attachments = get_post_meta( $variant_id, 'variation_image_gallery', true );
				}
				$attachments_exp = array_filter( explode( ',', $attachments ) );
				// If variation gallary images does not exist fetch product gallery image.
				if ( empty( $attachments_exp ) ) {
					$attachments = get_post_meta( $product_id, '_product_image_gallery', true );
					$attachments_exp = array_filter( explode( ',', $attachments ) );
				}
				foreach ( $attachments_exp as $id ) {
					$image_src = wp_get_attachment_image_src( $id, 'full' );
					$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
					$response['images'][ $i ]['src'] = $image_src[0];
					$response['images'][ $i ]['thumbnail'] = $image_src_thumb[0];
					$i++;
				}
			} else {
				foreach ( $product->gallery_image_ids as $id ) {
					$image_src = wp_get_attachment_image_src( $id, 'full' );
					$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
					$response['images'][ $i ]['src'] = $image_src[0];
					$response['images'][ $i ]['thumbnail'] = $image_src_thumb[0];
					$i++;
				}
			}

			// For Tier Price.
			$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
			$tier_price_data = array();
			$common_tier_price = array();
			$variant_tier_price = array();
			$same_for_all_variants = false;
			$is_tier = false;
			if ( ! empty( $meta_data_content ) ) {
				$tier_price_data = $meta_data_content[0];
				$is_tier = true;

				if ( 'true' == $tier_price_data['pricing_per_variants'] ) {
					$same_for_all_variants = true;
					foreach ( $tier_price_data['price_rules'][0]['discounts'] as $discount ) {
						$common_tier_price[] = array(
							'quantity' => $discount['lower_limit'],
							'discount' => $discount['discount'],
							'discountType' => $tier_price_data['discount_type'],
						);
					}
					$response['tier_prices'] = $this->create_tier_price( $common_tier_price, $product->price );
				} else {
					foreach ( $tier_price_data['price_rules'] as $variant ) {
						foreach ( $variant['discounts'] as $discount ) {
							$variant_tier_price[ $variant['id'] ][] = array(
								'quantity' => $discount['lower_limit'],
								'discount' => $discount['discount'],
								'discountType' => $tier_price_data['discount_type'],
							);
						}
					}
					$response['tier_prices'] = $this->create_tier_price( $variant_tier_price[ $variant_id ], $product->price );
				}
			}
			// End.

			if ( $details ) {
				$response['name'] = get_the_title( $product_id );
				$response['price'] = $product->price;
				$attribute = array();
				if ( $product_id != $variant_id ) {
					foreach ( $attributes as $key => $value ) {
						$key = urldecode( $key );
						$attr_term_details = get_term_by( 'slug', $value, $key );
						if ( empty( $attr_term_details ) ) {
							$attr_term_details = get_term_by( 'name', $value, $key );
						}

						$term = wc_attribute_taxonomy_id_by_name( $key );
						$attr_name = wc_attribute_label( $key );
						$attr_val_id = $attr_term_details->term_id;
						$attr_val_name = $attr_term_details->name;
						$attribute[ $attr_name ]['id'] = $attr_val_id;
						$attribute[ $attr_name ]['name'] = $attr_val_name;
						$attribute[ $attr_name ]['attribute_id'] = $term;
					}
				} else {
					foreach ( $attributes as $attr_key => $attributelist ) {
						if ( 'pa_xe_is_designer' != $attr_key ) {
							foreach ( $attributelist['options'] as $key => $value ) {
								$term = wc_attribute_taxonomy_id_by_name( $attributelist['name'] );
								$attr_name = wc_attribute_label( $attributelist['name'] );
								$attr_val_id = $value;
								$attr_term_details = get_term_by( 'id', absint( $value ), $attributelist['name'] );
								$attr_val_name = $attr_term_details->name;
								$attribute[ $attr_name ]['id'] = $attr_val_id;
								$attribute[ $attr_name ]['name'] = $attr_val_name;
								$attribute[ $attr_name ]['attribute_id'] = $term;
							}
						}
					}
				}
				$response['attributes'] = $attribute;
			}
			return rest_ensure_response( $response );
		}

		/**
		 * Method to get wc paths
		 *
		 * @param obj $request Request object.
		 */
		public function wc_paths( $request ) {
			$output['abspath'] = ABSPATH;
			$output['wc_abspath'] = WC_ABSPATH;
			$output['id'] = $GLOBALS['user_id'];
			return rest_ensure_response( $output );
		}

		/**
		 * Method to get all attributes
		 */
		public function list_all_attributes() {
			global $wpdb;
			$table_term = $wpdb->prefix . 'terms';
			$table_term_taxonomy = $wpdb->prefix . 'term_taxonomy';
			$table_taxonomy = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$sql = "SELECT attribute_id as id, concat('pa_', attribute_name) as slug, attribute_label as name, attribute_type as type FROM $table_taxonomy WHERE attribute_type='select'";
			$attributes = $wpdb->get_results( $sql );
			$attribute_list = array();
			foreach ( $attributes as $key => $attrubute ) {
				if ( 'xe_is_designer' != $attrubute->name ) {
					$attribute_list[ $key ] = $attrubute;
					$query = "SELECT t.term_id as id, t.name, t.slug FROM $table_term as t INNER JOIN  $table_term_taxonomy as tt ON t.term_id = tt.term_id WHERE tt.taxonomy = '$attrubute->slug'";
					$terms = $wpdb->get_results( $query );
					$attribute_list[ $key ]->terms = $terms;
				}
			}
			return rest_ensure_response( $attribute_list );
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_customer_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['orderby'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'id',
			);
			$args['order'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => 'DESC',
			);
			$args['page'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1,
			);
			$args['per_page'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 3,
			);
			$args['pagination'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 1,
			);
			$args['search'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['from_date'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['from_date'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['customer_no_order'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => '',
			);
			$args['quote'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			$args['notification'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',
				'default' => '',
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_country_state_name() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['countryState'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);

			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_customer_details() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['customer_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_product_categories() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['categories_option'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_product_attributes() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_product_attributes_terms() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			$args['attribute_name'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'string',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_create_product_attributes_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['attributes_option'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_get_order_details_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['order_option'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * We can use this function to contain our arguments for the example product endpoint.
		 */
		public function inkxe_order_item_details_arguments() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['order_item_option'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',

			);
			return $args;
		}

		/**
		 * Method to get customer list
		 *
		 * @param obj $request Request object.
		 */
		public function list_all_customers( $request ) {
			global $wpdb;
			$page = $request['per_page'] * ( $request['page'] - 1 );
			$pagination = $request['pagination'];
			$per_page = ( 0 != $pagination ) ? $request['per_page'] : -1;
			if ( ! empty( $request['fetch'] ) && ! empty( $request['search'] ) ) {
				$per_page = -1;
			}
			$search = addslashes( $request['search'] );
			$order_by = $request['orderby'];
			if ( 'name' == $order_by ) {
				$order_by = 'username';
			}
			$order = $request['order'];
			$from_date = $request['from_date'] ? $request['from_date'] : '';
			$to_date = $request['to_date'] ? $request['to_date'] : '';
			$customer_no_order = $request['customer_no_order'] ? $request['customer_no_order'] : '';
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$fetch = $request['fetch'] ? $request['fetch'] : '';
			$notification = $request['notification'] ? $request['notification'] : '';
			$inclusive = true;
			$meta_query_value = '';
			$compare = '';
			$order_count = array();
			if ( 'true' == $customer_no_order ) {
				$meta_query_value = 0;
				$compare = '=';
				$order_count = array(
					'relation' => 'OR',
					array(
						'key' => '_order_count',
						'compare' => 'NOT EXISTS',
						'value' => '',
					),
					array(
						'key' => '_order_count',
						'value' => $meta_query_value,
						'compare' => $compare,
					),
				);
			} else {
				$meta_query_value = 0;
				$compare = '>';
				$order_count = array(
					array(
						'key' => '_order_count',
						'value' => $meta_query_value,
						'compare' => $compare,
					),
				);
			}
			$customer_list = array();
			$meta_query_array = array();
			if ( empty( $fetch ) && empty( $notification ) ) {
				$meta_query_array = $order_count;
			}
			$args = array(
				'role' => 'Customer',
				'orderby' => $order_by,
				'order' => $order,
				'number' => $per_page,
				'offset' => $page,
				'search' => '*' . esc_attr( $search ) . '*',
				'meta_query' => $meta_query_array,
				'date_query' => array(
					'relation' => 'OR',
					array(
						'before' => $to_date,
						'after' => $from_date,
						'inclusive' => $inclusive,
					),
				),
			);
			$wp_user_query = new WP_User_Query( $args );
			$users = $wp_user_query->get_results();
			$total_user = (int) $wp_user_query->get_total();
			$i = 0;
			foreach ( $users as $user ) {
				$total_orders = get_user_meta( $user->ID, '_order_count', true ) ? get_user_meta( $user->ID, '_order_count', true ) : 0;
				$order_id = '';
				if ( 'false' == $customer_no_order && $total_orders > 0 ) {
					$customer = new WC_Customer( $user->ID );
					$last_order = $customer->get_last_order();
					$order_id = $last_order->get_id();
				}
				$user_data = get_user_meta( $user->ID );
				$user_name = explode( ' ', $user->display_name );
				$first_name = ! isset( $user_data['billing_first_name'][0] ) ? $user_name[0] : $user_data['billing_first_name'][0];
				$last_name = ! isset( $user_data['billing_last_name'][0] ) ? $user_name[1] : $user_data['billing_last_name'][0];
				$customer_list[ $i ]['id'] = $user->ID;
				$customer_list[ $i ]['first_name'] = $first_name;
				$customer_list[ $i ]['last_name'] = $last_name;
				$customer_list[ $i ]['email'] = $user->user_email;
				$customer_list[ $i ]['date_created'] = $user->user_registered;
				$customer_list[ $i ]['total_orders'] = $total_orders;
				$customer_list[ $i ]['last_order_id'] = $order_id;
				$i++;
			}
			$customer_response['total_user'] = $total_user;
			$customer_response['customer_list'] = $customer_list;
			return rest_ensure_response( $customer_response );

		}

		/**
		 * This function is where we register our routes for our example endpoint.
		 */
		public function inkxe_register_custom_routes() {
			$GLOBALS['user_id'] = get_current_user_id();
			// register_rest_route() handles more arguments but we are going to stick to the basics for now.
			if ( version_compare( WC_VERSION, '8.3.1', '>=' ) ) {
		        // new version code
				register_rest_route("InkXEProductDesignerLite", '/orders', array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array($this, 'imp_get_orders_latest_version_wc'),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_order_arguments(),
					'permission_callback' => '__return_true',
				));
		    } else {
				// register_rest_route() handles more arguments but we are going to stick to the basics for now.
				register_rest_route("InkXEProductDesignerLite", '/orders', array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array($this, 'inkxe_get_orders'),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_order_arguments(),
					'permission_callback' => '__return_true',
				));
		    }

			register_rest_route(
				'InkXEProductDesignerLite',
				'/product_details_with_variations',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_product_details_variation' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/products',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_products' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/options',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_attribute_options' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_options_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product/attributes',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_product_attribute' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_attributes_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product/count',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_product_count' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/categories/products',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_category_products' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/path',
				array(
					'methods' => 'GET',
					'callback' => array( $this, 'wc_paths' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/product/images',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'product_images' ),
					'args' => $this->inkxe_get_product_image_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/attributes',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'list_all_attributes' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/multiple_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_multiple_shipping_address' ),
					'args' => $this->get_multiple_shipping_address_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/delete_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'delete_multiple_shipping_address' ),
					'args' => $this->delete_multiple_shipping_address_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/update_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'update_multiple_shipping_address' ),
					'args' => $this->update_multiple_shipping_address_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/create_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'create_multiple_shipping_address' ),
					'args' => $this->create_multiple_shipping_address_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/create_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_create_customer' ),
					'args' => $this->create_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/update_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_update_customer' ),
					'args' => $this->update_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/delete_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_delete_customer' ),
					'args' => $this->delete_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/get_countries',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_countries' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/get_states',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_states' ),
					'args' => $this->states_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/get_country_name',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_country_name' ),
					'args' => $this->country_code_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/get_state_name',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_state_name' ),
					'args' => $this->country_state_code_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer_count',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'user_count' ),
					'args' => $this->user_count_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/order_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_order_shipping_address' ),
					'args' => $this->inkxe_order_shipping_address(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/store_order_statuses',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_store_order_statuses' ),
					'args' => $this->inkxe_order_statuses(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/multiple_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_multiple_shipping_address' ),
					'args' => $this->get_multiple_shipping_address_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/delete_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'delete_multiple_shipping_address' ),
					'args' => $this->delete_multiple_shipping_address_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/update_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'update_multiple_shipping_address' ),
					'args' => $this->update_multiple_shipping_address_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/create_shipping_address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'create_multiple_shipping_address' ),
					'args' => $this->create_multiple_shipping_address_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/create_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_create_customer' ),
					'args' => $this->create_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/update_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_update_customer' ),
					'args' => $this->update_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/delete_customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_delete_customer' ),
					'args' => $this->delete_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/get_countries',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_countries' ),
					'args' => $this->countries_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/get_states',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_states' ),
					'args' => $this->states_arguments(),

				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/get_country_name',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_country_name' ),
					'args' => $this->country_code_arguments(),

				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer/get_state_name',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'get',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_state_name' ),
					'args' => $this->country_state_code_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/orders/archive',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'archive_order' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customers',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'list_all_customers' ),
					'args' => $this->inkxe_get_customer_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/country_state_name',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_country_state_name' ),
					'args' => $this->inkxe_country_state_name(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer_details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_customer_details' ),
					'args' => $this->inkxe_get_customer_details(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/products_categories',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_product_categories' ),
					'args' => $this->inkxe_get_product_categories(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/products/attributes',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_product_attributes' ),
					'args' => $this->inkxe_get_product_attributes(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/products/attributes/terms',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_product_attributes_terms' ),
					'args' => $this->inkxe_get_product_attributes_terms(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/products/attributes/create',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'create_product_attributes' ),
					'args' => $this->inkxe_create_product_attributes_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/order_details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_order_details' ),
					'args' => $this->inkxe_get_order_details_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/order_item_details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_order_item_details' ),
					'args' => $this->inkxe_order_item_details_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/multi_store',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'all_blogs_list' ),
				)
			);
			/*soumya*/
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product-details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_products_details' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_product_details_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product-variants',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_products_variants' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_product_variants_arguments(),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product-categories',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_products_categories' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->inkxe_get_product_categories_arguments(),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/products-save-tier',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_save_tier' ),
				)
			);

			register_rest_route(
				'InkXEProductDesignerLite',
				'/product-get-tier',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_tier' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/product-description',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_product_description' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/products-remove-categories',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_remove_categories' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/create-product-catagories',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_create_categories' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/categories-subcategories',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_get_categories_subcategories' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/check-create-attribute',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_check_create_attribute' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/create-new-attribute',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_create_new_attribute' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/create-attribute',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'inkxe_create_attribute' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/single-customer',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_customer_details_with_order' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/total-user-count',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'total_customer_count' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer-id',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_customer_id' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/quote-customer-details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_quote_customer_details' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/attributes-terms',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_attributes_terms' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/variants-combination',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'save_variants_combination' ),
				)
			);
			/*soumya*/

			/*Malay Order custom Routes*/
			register_rest_route(
				'InkXEProductDesignerLite',
				'/order-log',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_order_log' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/update-order-status',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'update_order_status' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/customer-address',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_customer_address' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/create-order',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'create_custom_order' ),
				)
			);
			register_rest_route(
				'InkXEProductDesignerLite',
				'/line-item-details',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'get_line_item_details' ),
				)
			);
			/*End*/

			/*Malay Cart custom Routes*/
			register_rest_route(
				'InkXEProductDesignerLite',
				'/add-item-to-cart',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'POST',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'imprint_addtocart' ),
				)
			);
			/*End*/

			/*Mukesh Product enabled disabled customization*/
			register_rest_route(
				'InkXEProductDesignerLite',
				'/cusomize-enabled',
				array(
					// By using this constant we ensure that when the WP_REST_Server changes our readable endpoints will work as intended.
					'methods' => 'GET',
					// Here we register our callback. The callback is fired when this endpoint is matched by the WP_REST_Server class.
					'callback' => array( $this, 'im_product_cusomize_enabled' ),
					// Here we register our permissions callback. The callback is fired before the main callback to check if the current user can access the endpoint.
					'args' => $this->im_update_customize_enabled_details_arguments(),
				)
			);
		}

		/**
		 * Method to archive an order
		 *
		 * @param obj $request Request object.
		 */
		function archive_order($request) {
			$request_parameter = $request->get_params();
			$status = 0;
			$statusRes = 0;
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if (is_multisite()) {
				switch_to_blog($store_id);
			}
			$order_ids = json_decode($request_parameter['order_id']);
			foreach ($order_ids as $id) {
				if (wp_trash_post($id)) {
					$status = 1;
				} else {
					$order = wc_get_order($id);
					if (!empty($order)) {
						$statusRes = $order->update_status('trash');
					}
					if ($statusRes > 0) {
						$status = 1;
					}
				}
			}
			$response['status'] = $status;
			return rest_ensure_response($response);
		}

		/**
		 * Method to check if template is assigned
		 *
		 * @param array $categories Category ids.
		 */
		public function is_template_assign( $categories ) {
			$result = '';
			$url = $this->api_path . 'api/v1/template-products?prodcatID=' . $categories;
			$response = wp_remote_get( $url, array( 'headers' => 'x-impcode:' . $this->token ) );
			$result   = wp_remote_retrieve_body( $response );			
			return json_decode( $result, true );
		}


		/**
		 * Method to display dropdown variation to add to cart
		 */
		public function ink_pd_display_dropdown_variation_add_cart() {
			global $product;
			if ( $product->is_type( 'variable' ) ) {
				$custom_design_id = get_post_meta( get_the_ID(), 'custom_design_id', true );
				if ( ! $custom_design_id ) {
					$custom_design_id = 0;
				}
				$args = array(
					'post_type' => 'product_variation',
					'post_status' => array( 'publish' ),
					'post_parent' => get_the_ID(), // get parent post-ID.
				);
				$variations = get_posts( $args );
				$variation_id = $variations[0]->ID;
				?>
				<script>
					jQuery(document).ready(function($) {
						$('input.variation_id').change( function(){
							var url = $("#customize").attr('href');
							if (typeof url !== 'undefined') {
								var currentUrl = new URL(url);
								var storeId = currentUrl.searchParams.get('store_id') ? currentUrl.searchParams.get('store_id'):1;
								var url1Split = url.split("&pbti=");
								var qty = $("input[name=quantity]").val();
								var dpid = <?php echo esc_attr( $custom_design_id ); ?>;
								if( '' != $('input.variation_id').val() ) {
									var var_id = $('input.variation_id').val();
									if(url.search("vid")!=-1) {
										var urlSplit = url.split("&vid=");
										url = urlSplit[0]+"&vid="+var_id;
									} else {
										url = url+"&vid="+var_id;
									}
								} else {
									var pro_id = <?php echo esc_attr( $variation_id ); ?>;
									if(url.search("vid")!=-1) {
										var urlSplit = url.split("&vid=");
										url = urlSplit[0]+"&vid="+pro_id;
									} else {
										url = url+"&vid="+pro_id;
									}
								}
								if ( dpid != '' ) {
									url = url+"&dpid="+dpid;
								}
								if(url.search('pbti') != -1) {
									url = url+"&pbti="+url1Split[1];
								} else {
									url = url+"&pbti="+url1Split[1];
								}
								if ( dpid != '' ) {
									url = url+"&dpid="+dpid;
								}
								let tempUrl = url.split("&qty");
								url = tempUrl[0]+"&qty="+qty+"&store_id="+storeId;
								$('#customize').attr('href',url);
							}
						});
					});
				</script>
				<?php
			}
		}

		/**
		 * Method to add customize button on product page
		 */
		public function ink_pd_after_add_to_cart_quantity() {
			?>
		<script>
			jQuery(document).ready(function($) {
				$("input[name=quantity]").bind("change paste keyup", function() {
					var qty = $("input[name=quantity]").val();
					var url = $("#customize").attr('href');
					if (typeof url !== 'undefined') {
						var currentUrl = new URL(url);
						var storeId = currentUrl.searchParams.get('store_id') ? currentUrl.searchParams.get('store_id') : 1;
						url = url.split("&qty");
						url = url[0]+"&qty="+qty+"&store_id="+storeId;
						$("#customize").attr('href',url);
					}
				});
			});
			</script>
			<?php
		}

		/**
		 * Method to get cart quantity
		 *
		 * @param int   $product_quantity product quantity.
		 * @param mixed $cart_item_key cart item key.
		 * @param array $cart_item cart item.
		 */
		public function ink_pd_disable_customize_product_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ) {
			$custom_design_id = $cart_item['custom_design_id'];
			if ( $custom_design_id ) {
				$product_quantity = sprintf( '' . $cart_item['quantity'] . ' <input type="hidden" name="cart[%s][qty]" value="' . $cart_item['quantity'] . '" />', $cart_item_key );
			}
			return $product_quantity;
		}

		/**
		 * Method to override wc template
		 *
		 * @param obj   $template template.
		 * @param mixed $slug slug.
		 * @param mixed $name name.
		 */
		public function imp_override_woocommerce_template_part( $template, $slug, $name ) {
			global $product;
			if ( get_post_meta( $product->id, 'is_customizable', true ) == 'imprint_designer' ) {
				$template_directory = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/woocommerce/';
				if ( $name ) {
					$path = $template_directory . "{$slug}-{$name}.php";
				} else {
					$path = $template_directory . "{$slug}.php";
				}
				return file_exists( $path ) ? $path : $template;
			} else {
				return $template;
			}
		}

		/**
		 * Method to get multiple shippin address
		 *
		 * @param obj $request Request object.
		 */
		public function get_multiple_shipping_address( $request ) {
			global $wpdb;
			$result = array();
			$user_id = $request['userId'];
			$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
			if ( $wpdb->get_var( $query ) == $shipping_address ) {
				$sql = 'SELECT *  FROM ' . $shipping_address . ' WHERE user_id=' . $user_id;
				$result = $wpdb->get_results( $sql );

			}
			return $result;
		}

		/**
		 * Method to get multiple shippin address
		 *
		 * @param obj $request Request object.
		 */
		public function delete_multiple_shipping_address( $request ) {
			global $wpdb;
			$result = array();
			$id = $request['id'];
			$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
			if ( $wpdb->get_var( $query ) == $shipping_address ) {
				$sql = 'DELETE  FROM ' . $shipping_address . ' WHERE id = "' . $id . '"';
				$status = $wpdb->query( $sql );
				if ( $status ) {
					$result = array(
						'status' => '1',
						'message' => 'Deleted Successfully',
					);
				} else {
					$result = array(
						'status' => '0',
						'message' => $wpdb->show_errors(),
					);
				}
			}
			return $result;
		}

		/**
		 * Method to get multiple shippin address
		 *
		 * @param obj $request Request object.
		 */
		public function update_multiple_shipping_address( $request ) {
			global $wpdb;
			$result = array();
			$id = $request['id'];
			$first_name = $request['request']['first_name'];
			$last_name = $request['request']['last_name'];
			$address_1 = $request['request']['address_1'];
			$address_2 = $request['request']['address_2'];
			$company = $request['request']['company'];
			$city = $request['request']['city'];
			$post_code = $request['request']['post_code'];
			$country = $request['request']['country'];
			$state = $request['request']['state'];
			$mobile_no = $request['request']['mobile_no'];
			if ( 0 == $id ) {
				if ( ! empty( $request['request']['user_id'] ) ) {
					$user_id = $request['request']['user_id'];
					$update_user_meta = array(
						'shipping_address_1' => $address_1,
						'shipping_address_2' => $address_2,
						'shipping_city' => $city,
						'shipping_state' => $state,
						'shipping_postcode' => $post_code,
						'shipping_country' => $country,
						'shipping_company' => $company,
						'shipping_first_name' => $first_name,
						'shipping_last_name' => $last_name,
						'shipping_phone' => $mobile_no,
						'is_default' => $is_default,
					);
					$status = 0;
					foreach ( $update_user_meta as $key => $value ) {
						update_user_meta( $user_id, $key, $value );
						$status = 1;
					}
					if ( $status ) {
						$result = array(
							'status' => '1',
							'message' => 'Updated Successfully',
						);
					} else {
						$result = array(
							'status' => '0',
							'message' => $wpdb->show_errors(),
						);
					}
				} else {
					$result = array(
						'status' => '0',
						'message' => 'user id empty',
					);
				}
			} else {
				$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
				$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
				if ( $wpdb->get_var( $query ) == $shipping_address ) {
					$data = array(
						'first_name' => $first_name,
						'last_name' => $last_name,
						'address_line_one' => $address_1,
						'address_line_two' => $address_2,
						'company' => $company,
						'city' => $city,
						'postcode' => $post_code,
						'country' => $country,
						'state' => $state,
						'mobile_no' => $mobile_no,
					);
					$status = $wpdb->update( $shipping_address, $data, array( 'id' => $id ) );
					if ( $status ) {
						$result = array(
							'status' => '1',
							'message' => 'Updated Successfully',
						);
					} else {
						$result = array(
							'status' => '0',
							'message' => $wpdb->show_errors(),
						);
					}
				}
			}
			return $result;
		}

		/**
		 * Method to get multiple shippin address
		 *
		 * @param obj $request Request object.
		 */
		public function create_multiple_shipping_address( $request ) {
			global $wpdb;
			$result = array();
			$user_id = $request['request']['user_id'];
			$first_name = $request['request']['first_name'] ? $request['request']['first_name'] : '';
			$last_name = $request['request']['last_name'] ? $request['request']['last_name'] : '';
			$address_1 = $request['request']['address_1'];
			$address_2 = $request['request']['address_2'];
			$company = $request['request']['company'] ? $request['request']['company'] : '';
			$city = $request['request']['city'];
			$post_code = $request['request']['post_code'];
			$country = $request['request']['country'];
			$state = $request['request']['state'];
			$mobile_no = $request['request']['mobile_no'] ? $request['request']['mobile_no'] : '';
			$store_id = $request['request']['store_id'] ? $request['request']['store_id'] : 1;
			$is_default = 1;
			/*check for shipping address*/
			$shipping_address_1 = get_user_meta( $user_id, 'shipping_address_1', true );
			if ( empty( $shipping_address_1 ) ) {
				if ( is_multisite() ) {
					switch_to_blog( $store_id );
				}
				$user_dtails = get_userdata( $user_id );
				$user_email = $user_dtails->data->user_email;
				$user_meta_data = array(
					'billing_address_1' => $address_1,
					'billing_address_2' => $address_2,
					'billing_city' => $city,
					'billing_state' => $state,
					'billing_postcode' => $post_code,
					'billing_country' => $country,
					'billing_email' => $user_email,
					'billing_phone' => $billing_phone,
					'billing_company' => $company_name,
					'shipping_address_1' => $address_1,
					'shipping_address_2' => $address_2,
					'shipping_city' => $city,
					'shipping_state' => $state,
					'shipping_postcode' => $post_code,
					'shipping_country' => $country,
					'shipping_company' => $company,
					'shipping_first_name' => $first_name,
					'shipping_last_name' => $last_name,
					'shipping_phone' => $mobile_no,
					'is_default' => $is_default,

				);
				$status = 0;
				foreach ( $user_meta_data as $key => $value ) {
					update_user_meta( $user_id, $key, $value );
					$status = 1;
				}
				if ( $status ) {
					$result = array(
						'status' => '1',
						'message' => 'Updated Successfully',
					);
				} else {
					$result = array(
						'status' => '0' . $user_id,
						'message' => $wpdb->show_errors(),
					);
				}
			} else {
				$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
				$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
				if ( $wpdb->get_var( $query ) == $shipping_address ) {
					$data = array(
						'first_name' => $first_name,
						'last_name' => $last_name,
						'address_line_one' => $address_1,
						'address_line_two' => $address_2,
						'company' => $company,
						'city' => $city,
						'postcode' => $post_code,
						'country' => $country,
						'state' => $state,
						'mobile_no' => $mobile_no,
						'user_id' => $user_id,
					);
					$status = $wpdb->insert( $shipping_address, $data );
					if ( $status ) {
						$result = array(
							'status' => '1',
							'message' => 'Created Successfully',
						);
					} else {
						$result = array(
							'status' => '0',
							'message' => $wpdb->show_errors(),
						);
					}
				}
			}
			return $result;
		}

		/**
		 * Method to create customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_create_customer( $request ) {
			global $wpdb;
			$result = array();
			$user_email = $request['request']['user_email'];
			$user_password = $request['request']['user_password'];
			$user_name = preg_split( '/@/', $user_email );
			$user_name = $wpdb->escape( $user_name['0'] );
			$user_email = $wpdb->escape( $user_email );
			$user_password = $wpdb->escape( $user_password );
			$first_name = $request['request']['first_name'];
			$last_name = $request['request']['last_name'];
			$company_name = $request['request']['company_name'];
			$company_url = $request['request']['company_url'];
			$user_role = 'customer';
			$store_id = $request['request']['store_id'] ? $request['request']['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$is_default = $request['request']['is_default'] ? $request['request']['is_default'] : 1;

			/*set default shipping address*/

			/*Billing  Details*/
			$billing_email = $user_email;
			$billing_phone = $request['request']['billing_phone'] ? $request['request']['billing_phone'] : '';
			$billing_address_1 = $request['request']['billing_address_1'] ? $request['request']['billing_address_1'] : '';
			$billing_address_2 = $request['request']['billing_address_2'] ? $request['request']['billing_address_2'] : '';
			$billing_city = $request['request']['billing_city'] ? $request['request']['billing_city'] : '';
			$billing_state = $request['request']['billing_state_code'] ? $request['request']['billing_state_code'] : '';
			$billing_postcode = $request['request']['billing_postcode'] ? $request['request']['billing_postcode'] : '';
			$billing_country = $request['request']['billing_country_code'] ? $request['request']['billing_country_code'] : '';
			/*Billing  Details*/

			/*Shipping Details*/
			$shipping_address_1 = $request['request']['shipping_address_1'] ? $request['request']['shipping_address_1'] : '';
			$shipping_address_2 = $request['request']['shipping_address_2'] ? $request['request']['shipping_address_2'] : '';
			$shipping_city = $request['request']['shipping_city'] ? $request['request']['shipping_city'] : '';
			$shipping_state = $request['request']['shipping_state_code'] ? $request['request']['shipping_state_code'] : '';
			$shipping_postcode = $request['request']['shipping_postcode'] ? $request['request']['shipping_postcode'] : '';
			$shipping_country = $request['request']['shipping_country_code'] ? $request['request']['shipping_country_code'] : '';
			/*Shipping Details*/
			if ( $user_email ) {
				/*check user email*/
				$check_user_email = get_user_by( 'email', $user_email );
				if ( empty( $check_user_email ) ) {
					$user_id = wp_insert_user(
						array(
							'user_login' => $user_name . time(),
							'user_pass' => $user_password,
							'first_name' => $first_name,
							'last_name' => $last_name,
							'user_email' => $user_email,
							'role' => $user_role,
							'user_url' => $company_url,
						)
					);
					if ( $user_id ) {
						$user_meta_data = array(
							'billing_first_name' => $first_name,
							'billing_last_name' => $last_name,
							'billing_address_1' => $billing_address_1,
							'billing_address_2' => $billing_address_2,
							'billing_city' => $billing_city,
							'billing_state' => $billing_state,
							'billing_postcode' => $billing_postcode,
							'billing_country' => $billing_country,
							'billing_email' => $billing_email,
							'billing_phone' => $billing_phone,
							'billing_company' => $company_name,
							'shipping_address_1' => $shipping_address_1,
							'shipping_address_2' => $shipping_address_2,
							'shipping_city' => $shipping_city,
							'shipping_state' => $shipping_state,
							'shipping_postcode' => $shipping_postcode,
							'shipping_country' => $shipping_country,
							'shipping_company' => $company_name,
							'shipping_first_name' => $first_name,
							'shipping_last_name' => $last_name,
							'shipping_phone' => $billing_phone,
							'is_default' => $is_default,
							'_order_count' => 0,
						);
						$status = 0;
						foreach ( $user_meta_data as $key => $value ) {
							add_user_meta( $user_id, $key, $value );
							$status = 1;
						}
						if ( $status ) {
							$result = array(
								'status' => '1',
								'message' => 'Register Successfully',
								'user_id' => $user_id,
							);
						} else {
							$result = array(
								'status' => '0',
								'message' => 'Error',
							);
						}
					}
				} else {
					$result = array(

						'status' => '0',
						'message' => 'Email id already exists. Please try another one',
					);
				}
			} else {
				$result = array(
					'status' => '0',
					'message' => 'user email empty',
				);
			}
			return $result;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_update_customer( $request ) {
			global $wpdb;
			$result = array();
			$user_id = $request['user_id'];
			$first_name = $request['request']['first_name'];
			$last_name = $request['request']['last_name'];
			$company_name = $request['request']['company_name'];
			$company_url = $request['request']['company_url'];
			$is_default = $request['request']['is_default'];
			$billing_address_1 = $request['request']['billing_address_1'];
			$billing_address_2 = $request['request']['billing_address_2'];
			$billing_city = $request['request']['billing_city'];
			$billing_state_code = $request['request']['billing_state_code'];
			$billing_postcode = $request['request']['billing_postcode'];
			$billing_country_code = $request['request']['billing_country_code'];
			$billing_phone = $request['request']['billing_phone'];
			/*SHIPPING INFORMATION*/
			$shipping_address_1 = $request['request']['shipping_address_1'];
			$shipping_address_2 = $request['request']['shipping_address_2'];
			$shipping_city = $request['request']['shipping_city'];
			$shipping_state_code = $request['request']['shipping_state_code'];
			$shipping_postcode = $request['request']['shipping_postcode'];
			$shipping_country_code = $request['request']['shipping_country_code'];
			$store_id = $request['request']['store_id'] ? $request['request']['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			if ( ! empty( $user_id ) ) {
				$user_data = array(
					'ID' => $user_id,
					'first_name' => $first_name,
					'last_name' => $last_name,
					'user_url' => $company_url,
				);
				$user_status = wp_update_user( $user_data );
				if ( $user_status ) {
					$update_user_meta = array(
						'billing_address_1' => $billing_address_1,
						'billing_address_2' => $billing_address_2,
						'billing_country' => $billing_country_code,
						'billing_state' => $billing_state_code,
						'billing_city' => $billing_city,
						'billing_postcode' => $billing_postcode,
						'billing_phone' => $billing_phone,
						'billing_company' => $company_name,
						'shipping_address_1' => $shipping_address_1,
						'shipping_address_2' => $shipping_address_2,
						'shipping_city' => $shipping_city,
						'shipping_state' => $shipping_state_code,
						'shipping_postcode' => $shipping_postcode,
						'shipping_country' => $shipping_country_code,
						'shipping_phone' => $billing_phone,
						'is_default' => $is_default,
					);
					$cnt = 0;
					foreach ( $update_user_meta as $key => $value ) {
						update_user_meta( $user_id, $key, $value );
						$cnt = 1;
					}
					if ( $cnt ) {
						$result = array(
							'status' => '1',
							'message' => 'Updated Successfully',
						);
					} else {
						$result = array(
							'status' => '0',
							'message' => 'User meta updated error',
						);
					}
				} else {
					$result = array(
						'status' => '0',
						'message' => 'user data updated error',
					);
				}
			} else {
				$result = array(
					'status' => '0',
					'message' => 'user email empty',
				);
			}
			return $result;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_delete_customer( $request ) {
			global $wpdb;
			$result = array();
			$status = 0;
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			require_once ABSPATH . 'wp-admin/includes/user.php';
			if ( ! empty( $request['user_id'] ) ) {
				foreach ( $request['user_id'] as $user_id ) {
					wp_delete_user( $user_id );
					$status = 1;
				}
			}
			if ( 1 == $status ) {
				$result = array(
					'status' => '1',
					'message' => 'Deleted Successfully',
				);
			} else {
				$result = array(
					'status' => '0',
					'message' => $wpdb->show_errors(),
				);
			}
			return $result;
		}

		/**
		 * Method to get country list
		 */
		public function get_countries() {
			global $wpdb;
			$result = array();
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$countries_obj = new WC_Countries();
			$countries = $countries_obj->__get( 'countries' );
			$i = 0;
			foreach ( $countries as $key => $value ) {
				$countries_code = $key;
				$countries_name = $value;
				$result[ $i ]['countries_code'] = $countries_code;
				$result[ $i ]['countries_name'] = html_entity_decode( $countries_name );
				$i++;
			}
			return $result;
		}

		/**
		 * Method to get states
		 *
		 * @param obj $request Request object.
		 */
		public function get_states( $request ) {
			global $wpdb;
			$country_code = $request['country_code'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$result = array();
			$countries_obj = new WC_Countries();
			$country_states_array = $countries_obj->get_states( $country_code );
			$i = 0;
			foreach ( $country_states_array as $skey => $svalue ) {
				$state_code = $skey;
				$state_name = $svalue;
				$result[ $i ]['state_code'] = $state_code;
				$result[ $i ]['state_name'] = html_entity_decode( $state_name );
				$i++;
			}
			return $result;
		}

		/**
		 * Method to get country name
		 *
		 * @param obj $request Request object.
		 */
		public function get_country_name( $request ) {
			$country_code = $request['country_code'];
			return WC()->countries->countries[ $country_code ] ? WC()->countries->countries[ $country_code ] : '';

		}

		/**
		 * Method to get state name
		 *
		 * @param obj $request Request object.
		 */
		public function get_state_name( $request ) {
			$state_name = '';
			$country_code = $request['country_code'];
			$state_code = $request['state_code'];
			$state_name = WC()->countries->states[ $country_code ][ $state_code ] ? WC()->countries->states[ $country_code ][ $state_code ] : '';
			return $state_name;
		}

		/**
		 * Method to get user count
		 *
		 * @param obj $request Request object.
		 */
		public function user_count( $request ) {
			$user_count = 0;
			$customer_no_order = $request['customer_no_order'];
			$from_date = $request['from_date'];
			$to_date = $request['to_date'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$search = addslashes( $request['search'] );
			$quote = $request['quote'] ? $request['quote'] : '';
			$notification = $request['notification'] ? $request['notification'] : '';
			$inclusive = true;
			$meta_query_value = '';
			$compare = '';
			if ( 'true' == $customer_no_order ) {
				$meta_query_value = 0;
				$compare = '=';
			} else {
				$meta_query_value = 0;
				$compare = '>';
			}
			$meta_query_array = array();
			if ( empty( $quote ) && empty( $notification ) ) {
				$meta_query_array = array(
					array(
						'key' => '_order_count',
						'value' => $meta_query_value,
						'compare' => $compare,
					),
				);
			}
			$args = array(
				'role' => 'Customer',
				'search' => $search,
				'meta_query' => $meta_query_array,
				'date_query' => array(
					'relation' => 'OR',
					array(
						'before' => $to_date,
						'after' => $from_date,
						'inclusive' => $inclusive,
					),
				),
			);
			$user = get_users( $args );
			$user_count = count( $user );
			return $user_count;
		}

		/**
		 * Method to declare arguments for shipping address
		 */
		public function inkxe_order_shipping_address() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['shipping_data'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);

			return $args;
		}

		/**
		 * Method to declare arguments for order status
		 */
		public function inkxe_order_statuses() {
			$args = array();
			// Here we are registering the schema for the filter argument.
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);

			return $args;
		}

		/**
		 * Method to get order shipping address
		 *
		 * @param obj $request Request object.
		 */
		public function get_order_shipping_address( $request ) {
			global $wpdb;
			$response_array = array();
			$customer_id = $request['shipping_data']['customerId'];
			$shipping_id = $request['shipping_data']['shippingId'];
			if ( ! empty( $customer_id ) && ! empty( $shipping_id ) ) {
				/*check table*/
				$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
				$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
				if ( $wpdb->get_var( $query ) == $shipping_address ) {
					$sql = 'SELECT *  FROM ' . $shipping_address . ' WHERE user_id=' . $customer_id . ' AND id= ' . $shipping_id;
					$response_array = $wpdb->get_results( $sql );
				}
			}
			return $response_array;
		}

		/**
		 * Method to get order status
		 *
		 * @param obj $request Request object.
		 */
		public function get_store_order_statuses( $request ) {
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_status = wc_get_order_statuses();
			$order_status_array = array();
			if ( ! empty( $order_status ) ) {
				$i = 0;
				foreach ( $order_status as $key => $value ) {
					$order_status_array[ $i ]['value'] = $value;
					$order_status_array[ $i ]['key'] = str_replace( 'wc-', '', $key );
					$i++;
				}
			}
			return $order_status_array;
		}

		/**
		 * Method to get general settings
		 *
		 * @param mixed $url Request object.
		 */
		public function get_general_setting( $url ) {
			$response = wp_remote_get( $url, array( 'headers' => 'x-impcode:' . $this->token ) );
			$result     = wp_remote_retrieve_body( $response );			
			return json_decode( $result, true );
		}

		/**
		 * Method to get country state name
		 *
		 * @param obj $request Request object.
		 */
		public function get_country_state_name( $request ) {
			$country_code = $request['countryState']['countryCode'];
			$state_code = $request['countryState']['stateCode'];
			$country_name = WC()->countries->countries[ $country_code ];
			$state_name = WC()->countries->states[ $country_code ][ $state_code ];
			$response_array = array(
				'countryName' => $country_name ? $country_name : $country_code,
				'stateName' => $state_name ? $state_name : $state_code,
			);
			return $response_array;
		}

		/**
		 * Method to get customer details
		 *
		 * @param obj $request Request object.
		 */
		public function get_customer_details( $request ) {
			$customer_details = array();
			$customer_id = $request['customer_id'];
			$last_order_id = 0;
			$order_value = 0;
			$total_order = 0;
			if ( $customer_id > 0 ) {
				$result = wc_get_customer_last_order( $customer_id );
				$order_data = json_decode( $result, true );
				if ( ! empty( $order_data ) ) {
					$last_order_id = $order_data['id'];
				}
				$order_total_array = array(
					'numberposts' => -1,
					'meta_key' => '_customer_user',
					'meta_value' => $customer_id,
					'post_type' => array( 'shop_order' ),
					'post_status' => 'any',

				);
				$customer_order_total = get_posts( $order_total_array );
				foreach ( $customer_order_total as $customer_order ) {
					$order = wc_get_order( $customer_order );
					$order_value += $order->get_total();
				};
				$total_order = wc_get_customer_order_count( $customer_id );
				$customer_details['last_order_id'] = $last_order_id;
				$customer_details['order_value'] = $order_value;
				$customer_details['total_order'] = $total_order;
				$user_data = get_userdata( $customer_id );
				$customer_details['customer_id'] = $user_data->ID;
				$customer_details['first_name'] = get_user_meta( $customer_id, 'first_name', true );
				$customer_details['last_name'] = get_user_meta( $customer_id, 'last_name', true );
				$customer_details['email'] = $user_data->user_email;
				$customer_details['user_registered'] = date( 'M m, Y', strtotime( $user_data->user_registered ) );
				/*GET BILLING DETAILS*/
				$customer_details['billing_address']['billing_address_1'] = get_user_meta( $customer_id, 'billing_address_1', true );
				$customer_details['billing_address']['billing_address_2'] = get_user_meta( $customer_id, 'billing_address_2', true );
				$customer_details['billing_address']['billing_city'] = get_user_meta( $customer_id, 'billing_city', true );
				$customer_details['billing_address']['billing_state_code'] = get_user_meta( $customer_id, 'billing_state', true );
				$customer_details['billing_address']['billing_zip'] = get_user_meta( $customer_id, 'billing_postcode', true );
				$customer_details['billing_address']['billing_country_code'] = get_user_meta( $customer_id, 'billing_country', true );
				$customer_details['billing_address']['billing_phone'] = get_user_meta( $customer_id, 'billing_phone', true );
				$customer_details['billing_address']['billing_country'] = WC()->countries->countries[ get_user_meta( $customer_id, 'billing_country', true ) ];
				$customer_details['billing_address']['billing_state'] = WC()->countries->states[ get_user_meta( $customer_id, 'billing_country', true ) ][ get_user_meta( $customer_id, 'billing_state', true ) ];

				/**GET SHIPPING DETAILS*/
				$customer_details['shipping_address']['shipping_address_1'] = get_user_meta( $customer_id, 'shipping_address_1', true );
				$customer_details['shipping_address']['shipping_address_2'] = get_user_meta( $customer_id, 'shipping_address_2', true );
				$customer_details['shipping_address']['shipping_city'] = get_user_meta( $customer_id, 'shipping_city', true );
				$customer_details['shipping_address']['shipping_state_code'] = get_user_meta( $customer_id, 'shipping_state', true );
				$customer_details['shipping_address']['shipping_zip'] = get_user_meta( $customer_id, 'shipping_postcode', true );
				$customer_details['shipping_address']['shipping_country_code'] = get_user_meta( $customer_id, 'shipping_country', true );
				$customer_details['shipping_address']['shipping_country'] = WC()->countries->countries[ get_user_meta( $customer_id, 'shipping_country', true ) ];
				$customer_details['shipping_address']['shipping_state'] = WC()->countries->states[ get_user_meta( $customer_id, 'shipping_country', true ) ][ get_user_meta( $customer_id, 'shipping_state', true ) ];
			}
			return $customer_details;
		}

		/**
		 * Method to get product categories
		 *
		 * @param obj $request Request object.
		 */
		public function get_product_categories( $request ) {
			$categories_option = $request['categories_option'];
			$name = $categories_option['name'] ? $categories_option['name'] : '';
			$store_id = $categories_option['store_id'] ? $categories_option['store_id'] : 1;
			$product_id = $categories_option['product_id'];
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$taxonomy = 'product_cat';
			$order = 'desc';
			$orderby = 'id';
			$show_count = 0; // 1 for yes, 0 for no
			$pad_counts = 0; // 1 for yes, 0 for no
			$hierarchical = 0; // 1 for yes, 0 for no
			$title = $name;
			$empty = 0;
			$args = array(
				'taxonomy' => $taxonomy,
				'orderby' => $orderby,
				'order' => $order,
				'show_count' => $show_count,
				'pad_counts' => $pad_counts,
				'hierarchical' => $hierarchical,
				'name' => $title,
				'hide_empty' => $empty,
			);
			if ( $product_id > 0 ) {
				$terms = get_the_terms( $product_id, 'product_cat' );
				$all_categories = $terms;
			} else {
				$all_categories = get_categories( $args );
			}

			return $all_categories;
		}

		/**
		 * Method to get product attributes
		 *
		 * @param obj $request Request object.
		 */
		public function get_product_attributes( $request ) {
			global $woocommerce;
			$product_attributes_list = array();
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$get_product_attributes = wc_get_attribute_taxonomies();
			if ( ! empty( $get_product_attributes ) ) {
				$i = 0;
				foreach ( $get_product_attributes as $key => $value ) {
					$product_attributes_list[ $i ]['id'] = $value->attribute_id;
					$product_attributes_list[ $i ]['name'] = $value->attribute_label;
					$product_attributes_list[ $i ]['slug'] = $value->attribute_name;
					$product_attributes_list[ $i ]['type'] = $value->attribute_type;
					$product_attributes_list[ $i ]['order_by'] = $value->attribute_orderby;
					$i++;
				}
			}
			return $product_attributes_list;
		}

		/**
		 * Method to get attribute terms
		 *
		 * @param obj $request Request object.
		 */
		public function get_product_attributes_terms( $request ) {
			global $woocommerce;
			$product_attributes_term_list = array();
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$attribute_name = $request['attribute_name'] ? $request['attribute_name'] : '';
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$taxonomy_terms = get_terms( wc_attribute_taxonomy_name( $attribute_name ), 'orderby=name&hide_empty=0' );
			if ( ! empty( $taxonomy_terms ) ) {
				foreach ( $taxonomy_terms as $key => $value ) {
					$product_attributes_term_list[ $key ]['id'] = $value->term_id;
					$product_attributes_term_list[ $key ]['name'] = $value->name;
					$product_attributes_term_list[ $key ]['slug'] = $value->slug;
					$product_attributes_term_list[ $key ]['description'] = $value->description;
					$product_attributes_term_list[ $key ]['menu_order'] = 0;
					$product_attributes_term_list[ $key ]['count'] = $value->count;
				}
			}
			return $product_attributes_term_list;
		}

		/**
		 * Method to create product attribute
		 *
		 * @param obj $request Request object.
		 */
		public function create_product_attributes( $request ) {
			$color_id_array = array();
			$attributes_option = $request['attributes_option'];
			$store_id = $attributes_option['store_id'] ? $attributes_option['store_id'] : '';
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$color_id = $attributes_option['color_id'] ? $attributes_option['color_id'] : '';
			$attribute_name = $attributes_option['name'];
			$term = wc_get_attribute( $color_id );
			$taxonomy = $term->slug;
			$slug_name = preg_replace( '/\s+/', '-', strtolower( $attribute_name ) );
			$insert_term = wp_insert_term(
				$attribute_name, // new term.
				$taxonomy, // taxonomy.
				array(
					'description' => '',
					'slug' => $slug_name,
					'parent' => 0,
				)
			);
			if ( ! is_wp_error( $insert_term ) ) {
				$color_id_array['id'] = $insert_term['term_id'];
			} else {
				$color_id_array['id'] = 0;
			}

			return $color_id_array;

		}

		/**
		 * Method to get order details
		 *
		 * @param obj $request Request object.
		 */
		public function get_order_details( $request ) {
			$order_option = $request['order_option'];
			$store_id = $order_option['store_id'] ? $order_option['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_id = $order_option['order_id'] ? $order_option['order_id'] : '';
			$order_response = array();
			$billing_address = array();
			$shipping_address = array();
			if ( ! empty( $store_id ) && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );

				/** Billing address */
				$billing_address['first_name'] = $order->get_billing_first_name();
				$billing_address['last_name'] = $order->get_billing_last_name();
				$billing_address['company'] = $order->get_billing_company();
				$billing_address['address_1'] = $order->get_billing_address_1();
				$billing_address['address_2'] = $order->get_billing_address_2();
				$billing_address['city'] = $order->get_billing_city();
				$billing_address['state'] = $order->get_billing_state();
				$billing_address['postcode'] = $order->get_billing_postcode();
				$billing_address['country'] = $order->get_billing_country();
				$billing_address['email'] = $order->get_billing_email();
				$billing_address['phone'] = $order->get_billing_phone();

				/** Shipping addres */
				$shipping_address['first_name'] = $order->get_shipping_first_name();
				$shipping_address['last_name'] = $order->get_shipping_last_name();
				$shipping_address['company'] = $order->get_shipping_company();
				$shipping_address['address_1'] = $order->get_shipping_address_1();
				$shipping_address['address_2'] = $order->get_shipping_address_2();
				$shipping_address['city'] = $order->get_shipping_city();
				$shipping_address['state'] = $order->get_shipping_state();
				$shipping_address['postcode'] = $order->get_shipping_postcode();
				$shipping_address['country'] = $order->get_shipping_country();
				if ( is_multisite() ) {
					$blog_details = get_blog_details( $store_id );
					$siteurl = $blog_details->siteurl;
				} else {
					$siteurl = get_home_url();
				}
				$items_array = array();
				if ( ! empty( $order->get_items() ) ) {
					$i = 0;
					foreach ( $order->get_items() as $item_id => $item ) {
						$product = $item->get_product();
						$product_sku = null;
						if ( is_object( $product ) ) {
							$product_sku = $product->get_sku();
						}
						$product_id = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$variation_id = isset( $variation_id ) && $variation_id > 0 ? $variation_id : $product_id;
						$items_array[ $i ]['id'] = $item->get_id();
						$items_array[ $i ]['product_id'] = $product_id;
						$items_array[ $i ]['variant_id'] = $variation_id;
						$items_array[ $i ]['name'] = $item->get_name();
						$items_array[ $i ]['sku'] = $product_sku;
						$items_array[ $i ]['quantity'] = $item->get_quantity();
						$item_total = $order->get_item_meta( $item_id, '_line_total', true );
						$items_array[ $i ]['price'] = $item_total / $item->get_quantity();
						$items_array[ $i ]['total'] = $item_total;
						$meta_data = $item->get_meta_data();
						$formatted_meta = array();
						$product_image_array = array();
						$j = 0;
						$k = 0;
						foreach ( $meta_data as $meta ) {
							$name = str_replace( 'pa_', '', $meta->key );
							if ( 'custom_design_id' == $name ) {
								$custom_design_id = $meta->value;
								$formatted_meta[ $j ] = $custom_design_id;
								$j++;
							}
						}
						$attributes = $product->get_attributes();
						if ( 0 != $product->image_id ) {
							$image_src = wp_get_attachment_image_src( $product->image_id, 'full' );
							$image_src_thumb = wp_get_attachment_image_src( $product->image_id, 'thumbnail' );
							$product_image_array[ $k ]['src'] = $image_src[0];
							$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
							$k++;
						}
						if ( $product_id != $variant_id ) {
							$attachments = get_post_meta( $variant_id, 'variation_image_gallery', true );
							$attachments_exp = array_filter( explode( ',', $attachments ) );
							foreach ( $attachments_exp as $id ) {
								$image_src = wp_get_attachment_image_src( $id, 'full' );
								$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
								$product_image_array[ $k ]['src'] = $image_src[0];
								$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
								$k++;
							}
						} else {
							foreach ( $product->gallery_image_ids as $id ) {
								$image_src = wp_get_attachment_image_src( $id, 'full' );
								$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
								$product_image_array[ $k ]['src'] = $image_src[0];
								$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
								$k++;
							}
						}
						$items_array[ $i ]['custom_design_id'] = $formatted_meta[0];
						$items_array[ $i ]['images'] = $product_image_array;
						$i++;
					}
				}
				// getting fees.
				foreach ( $order->get_fees() as $fee_item_id => $fee_item ) {
					$order_data['fee_lines'][] = array(
						'id' => $fee_item_id,
						'title' => $fee_item['name'],
						'tax_class' => ( ! empty( $fee_item['tax_class'] ) ) ? $fee_item['tax_class'] : null,
						'total' => wc_format_decimal( $order->get_line_total( $fee_item ), $dp ),
						'total_tax' => wc_format_decimal( $order->get_line_tax( $fee_item ), $dp ),
					);
				}

				$shipping_cost = 0;
				// getting shipping.
				foreach ( $order->get_shipping_methods() as $shipping_item_id => $shipping_item ) {
					$shipping_cost = wc_format_decimal( $shipping_item['cost'], $dp );
				}

				// getting taxes.
				$total_tax = 0;
				foreach ( $order->get_tax_totals() as $tax_code => $tax ) {
					$total_tax = wc_format_decimal( $tax->amount, $dp );
				}
				$order_response = array(
					'id' => $order->get_id(),
					'order_number' => $order->get_id(),
					'customer_first_name' => $order->get_billing_first_name(),
					'customer_last_name' => $order->get_billing_last_name(),
					'customer_email' => $order->get_billing_email(),
					'customer_id' => $order->get_customer_id(),
					'created_date' => $order->get_date_created()->format( 'Y-m-d H:i:s' ),
					'note' => $order->get_customer_order_notes(),
					'total_amount' => wc_format_decimal( $order->get_total(), $dp ),
					'total_tax' => $total_tax,
					'total_discounts' => wc_format_decimal( $order->get_total_discount(), $dp ),
					'total_shipping' => $shipping_cost,
					'currency' => $order->get_currency(),
					'note' => $order->get_customer_note(),
					'status' => $order->get_status(),
					'total_orders' => wc_get_customer_order_count( $order->get_customer_id() ),
					'billing' => $billing_address,
					'shipping' => $shipping_address,
					'payment' => $order->get_payment_method_title(),
					'store_url' => $siteurl,
					'orders' => $items_array,

				);
			}

			return $order_response;
		}

		/**
		 * Method to get order item details
		 *
		 * @param obj $request Request object.
		 */
		public function get_order_item_details( $request ) {
			$order_item_response = array();
			$order_items = array();
			$order_item_option = $request['order_item_option'];
			$store_id = $order_item_option['store_id'] ? $order_item_option['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_id = $order_item_option['order_id'] ? $order_item_option['order_id'] : '';
			if ( ! empty( $store_id ) && ! empty( $order_id ) ) {
				$order = wc_get_order( $order_id );
				if ( ! empty( $order->get_items() ) ) {
					$i = 0;
					foreach ( $order->get_items() as $item_id => $item ) {
						$product = $item->get_product();
						$product_sku = null;
						if ( is_object( $product ) ) {
							$product_sku = $product->get_sku();
						}
						$product_id = $item->get_product_id();
						$variation_id = $item->get_variation_id();
						$variation_id = isset( $variation_id ) && $variation_id > 0 ? $variation_id : $product_id;
						$id = isset( $variation_id ) && $variation_id > 0 ? $item->get_variation_id() : $item->get_product_id();
						$thumbnail = get_the_post_thumbnail_url( $product_id );
						$order_items[ $i ]['item_id'] = $item->get_id();
						$order_items[ $i ]['product_id'] = $product_id;
						$order_items[ $i ]['variant_id'] = $variation_id;
						$order_items[ $i ]['product_name'] = $item->get_name();
						$order_items[ $i ]['product_sku'] = $product_sku;
						$order_items[ $i ]['quantity'] = $item->get_quantity();
						$order_items[ $i ]['price'] = $product->get_price();
						$order_items[ $i ]['images'] = array(
							array(
								'src' => $thumbnail,
								'thumbnail' => $thumbnail,
							),
						);
						$item_total = $order->get_item_meta( $item_id, '_line_total', true );
						$order_items[ $i ]['total'] = $item_total;
						$meta_data = $item->get_meta_data();
						$product = wc_get_product( $id );
						$attributes = $product->get_attributes();
						$attribute = array();
						if ( $product_id != $variation_id ) {
							foreach ( $attributes as $key => $value ) {
								$key = urldecode( $key );
								$attr_term_details = get_term_by( 'slug', $value, $key );
								if ( empty( $attr_term_details ) ) {
									$attr_term_details = get_term_by( 'name', $value, $key );
								}
								$term = wc_attribute_taxonomy_id_by_name( $key );
								$attr_name = wc_attribute_label( $key );
								$attr_val_id = $attr_term_details->term_id;
								$attr_val_name = $attr_term_details->name;
								$attribute[ $attr_name ]['id'] = $attr_val_id;
								$attribute[ $attr_name ]['name'] = $attr_val_name;
								$attribute[ $attr_name ]['attribute_id'] = $term;
								$attribute[ $attr_name ]['hex-code'] = '';
							}
						} else {
							foreach ( $attributes as $attr_key => $attributelist ) {
								if ( 'pa_xe_is_designer' != $attr_key && 'pa_is_catalog' != $attr_key ) {
									foreach ( $attributelist['options'] as $key => $value ) {
										$term = wc_attribute_taxonomy_id_by_name( $attributelist['name'] );
										$attr_name = wc_attribute_label( $attributelist['name'] );
										$attr_val_id = $value;
										$attr_term_details = get_term_by( 'id', absint( $value ), $attributelist['name'] );
										$attr_val_name = $attr_term_details->name;
										$attribute[ $attr_name ]['id'] = $attr_val_id;
										$attribute[ $attr_name ]['name'] = $attr_val_name;
										$attribute[ $attr_name ]['attribute_id'] = $term;
										$attribute[ $attr_name ]['hex-code'] = '';
									}
								}
							}
						}
						$order_items[ $i ]['attributes'] = $attribute;
						$formatted_meta = array();
						foreach ( $meta_data as $meta ) {
							$name = str_replace( 'pa_', '', $meta->key );
							if ( 'custom_design_id' == $name ) {
								$name = 'ref_id';
							}
							$order_items[ $i ][ $name ] = $meta->value;
						}
						$i++;
					}
					$order_item_response['order_id'] = $order_id;
					$order_item_response['order_incremental_id'] = $order_id;
					$order_item_response['customer_id'] = $order->get_customer_id();
					$order_item_response['store_id'] = $store_id;
					$order_item_response['order_items'] = $order_items;
				}
			}
			return $order_item_response;
		}

		/**
		 * Method to get list of blogs
		 */
		public function all_blogs_list() {
			$blogs_list_array = array();
			if ( is_multisite() ) {
				$all_blog = get_sites();
				if ( ! empty( $all_blog ) ) {
					foreach ( $all_blog as $key => $value ) {
						$blogs_list_array[ $key ]['store_id'] = $value['blog_id'];
						$blogs_list_array[ $key ]['store_url'] = $value['domain'];
						$blogs_list_array[ $key ]['is_active'] = $value['public'];
					}
				}
			}
			return $blogs_list_array;
		}

		/**
		 * Method to save registration
		 *
		 * @param int $user_id user id.
		 */
		public function registration_save( $user_id ) {
			if ( ! empty( $user_id ) ) {
				update_user_meta( $user_id, '_order_count', 0 );
			}
		}

		/**
		 * Method to update customer\
		 */
		public function ink_pd_add_menu_item() {
			add_menu_page( 'Imprintnext Dashboard', 'Riaxe Product Customizer', 'manage_options', 'imprintnext_dashboard', array( $this, 'imprintnext_dashboard_content' ), plugins_url('assets/frontend/img/XE.png', __FILE__), 26 );
		}

		/**
		 * Method to get dashboard content
		 */
		public function imprintnext_dashboard_content() {
			/* delete_option("imprintnext_setup"); */
			$installed = get_option( 'imprintnext_setup' );
			$current_page = admin_url(sprintf('admin.php?%s', http_build_query($_GET)));
			$url_components = parse_url($current_page);
			parse_str($url_components['query'], $params);
			if(isset($params['status']) && $params['status'] == 1)
			{
				deactivate_plugins( plugin_basename( __FILE__ ), true );
				wp_redirect( admin_url( 'plugins.php' ) );
			}
			if(isset($params['action']) && $params['action'] == 'deactive')
			{
				include $plugin_path . 'views/admin/imprint-deactive.php';
			} else if ( $installed ) {
				include $plugin_path . 'views/admin/imprint-dashboard.php';
			} else {
				$this->generate_keys( 'ImprintNext', 'read_write' );
				include $plugin_path . 'views/admin/imprint-setup.php';
			}
		}

		/**
		 * Method to update customer
		 */
		public function ink_pd_add_option() {
			$option = isset( $_POST['option'] ) ? sanitize_text_field( wp_unslash( $_POST['option'] ) ) : '';
			$opt_value = ( isset( $_POST['opt_value'] ) && '' != $_POST['opt_value'] ) ? sanitize_text_field( wp_unslash( $_POST['opt_value'] ) ) : 1;
			delete_option( $option );
			if ( ! isset( $option ) || '' == $option || ! isset( $opt_value ) || '' == $opt_value ) {
				die(
					json_encode(
						array(
							'status' => 0,
							'message' => 'Missing required information.',
						)
					)
				);
			}
			add_option( $option, $opt_value );
			die(
				json_encode(
					array(
						'status' => 1,
						'message' => 'ImprintNext installed successfully.',
						'option' => $option,
						'value'  => $opt_value,
					)
				)
			);
		}

		/**
		 * Method to redirect after plugin activation
		 *
		 * @param mixed $plugin Name of the plugin.
		 */
		public function ink_pd_activation_redirect( $plugin ) {
			if ( plugin_basename( __FILE__ ) == $plugin ) {
				$this->generate_keys( 'ImprintNext', 'read_write' );
				exit( esc_attr( wp_redirect( admin_url( 'admin.php?page=imprintnext_dashboard' ) ) ) );
			}
		}

		/**
		 * Method to generate keys
		 *
		 * @param mixed $app_name App name.
		 * @param mixed $scope Scope.
		 */
		public function generate_keys( $app_name, $scope ) {
			global $wpdb;

			$description = sprintf(
				'%s - API (%s)',
				wc_trim_string( wc_clean( $app_name ), 170 ),
				gmdate( 'Y-m-d H:i:s' )
			);
			$user  = wp_get_current_user();

			// Created API keys.
			$permissions     = in_array( $scope, array( 'read', 'write', 'read_write' ), true ) ? sanitize_text_field( $scope ) : 'read';
			$consumer_key    = 'ck_' . wc_rand_hash();
			$consumer_secret = 'cs_' . wc_rand_hash();
			$table_name = $wpdb->prefix . 'woocommerce_api_keys';
			$wpdb->query( "DELETE  FROM $table_name WHERE description = '" . $description . "'" );
			$wpdb->insert(
				$table_name,
				array(
					'user_id'         => $user->ID,
					'description'     => $description,
					'permissions'     => $permissions,
					'consumer_key'    => wc_api_hash( $consumer_key ),
					'consumer_secret' => $consumer_secret,
					'truncated_key'   => substr( $consumer_key, -7 ),
				),
				array(
					'%d',
					'%s',
					'%s',
					'%s',
					'%s',
					'%s',
				)
			);
			$_SESSION['imprintnext']['c_key'] = $consumer_key;
			$_SESSION['imprintnext']['c_secret'] = $consumer_secret;

			/*
			 Return array
			 return array(
				'key_id'          => $wpdb->insert_id,
				'user_id'         => $user->ID,
				'consumer_key'    => $consumer_key,
				'consumer_secret' => $consumer_secret,
				'key_permissions' => $permissions,
			);
			*/
		}

		/**
		 * Method to update customer
		 */
		public function inkxe_get_product_details_arguments() {
			$args = array();
			$args['product_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_products_details( $request ) {
			$product_resposne = array();
			$product_id = $request['product_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$product = wc_get_product( $product_id );
			$variation_ids  = $product->get_children();
			$attributes = $product->get_attributes();
			$category_ids = $product->get_category_ids();
			if ( ! empty( $category_ids ) ) {
				$i = 0;
				foreach ( $category_ids as $category_id ) {
					$product_cat = get_term_by( 'id', $category_id, 'product_cat' );
					$categories[ $i ] = array(
						'id' => $product_cat->term_id,
						'name' => $product_cat->name,
						'slug' => $product_cat->slug,
						'parent_id' => $product_cat->parent,
					);
					$i++;
				}
			}
			$price = 0;
			if ( ! empty( $product->get_sale_price() ) ) {
				$price = $product->get_sale_price();
			} elseif ( ! empty( $product->get_price() ) ) {
				$price = $product->get_price();
			}
			$response = array();
			if ( ! empty( $attributes ) ) {
				$i = 0;
				foreach ( $attributes as $attribute ) {
					$attribute_details = $this->get_attribute_type( $attribute['id'] );
					$attribute_type = $attribute_details->attribute_type;
					if ( 'xe_is_designer' != $attribute_details->attribute_label ) {
						$response[ $i ]['id'] = $attribute['id'];
						$response[ $i ]['name'] = $attribute_details->attribute_label;
						$j = 0;
						foreach ( $attribute['options'] as $option ) {
							$term = get_term_by( 'id', $option, 'pa_' . $attribute_details->attribute_name );
							$response[ $i ]['options'][ $j ]['id'] = $option;
							$response[ $i ]['options'][ $j ]['name'] = $term->name;
							$j++;
						}
						$i++;
					}
				}
			}
			$product_resposne['variation_id'] = $variation_ids[0] ? $variation_ids[0] : '';
			$product_resposne['categories'] = $categories;
			$product_resposne['id'] = $product->get_id();
			$product_resposne['name'] = $product->get_name();
			$product_resposne['sku'] = $product->get_sku();
			$product_resposne['type'] = $product->get_type();
			$product_resposne['description'] = preg_replace( '/\r|\n/', '', $product->get_description() );
			$product_resposne['price'] = $price;
			$product_resposne['stock_quantity'] = $product->get_stock_quantity();
			$product_resposne['attributes'] = $response;
			return $product_resposne;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_product_details_variation( $request ) {
			global $wpdb;
			$product_response = array();
			$variations = array();
			$images = array();
			$prefix = $wpdb->prefix;
			$attribute_taxonomies = $prefix . 'woocommerce_attribute_taxonomies';
			$product_id = $request['product_id'] ? $request['product_id'] : 0;
			$color_attr = $request['color_attr'] ? $request['color_attr'] : '';
			$product = wc_get_product( $product_id );
			$variation_ids  = $product->get_children();
			$attributes = $product->get_attributes();
			$manage_stock = $product->manage_stock;
			$stock_status = $product->stock_status;					
			// Get the variation quantity.
			if ( 1 != $manage_stock && 'instock' == $stock_status ) {
				$stock_qty = 1000;
			} else {
				$stock_qty = $product->get_stock_quantity(); // Stock qty.
			}
			// For product images.
			$i = 0;
			if ( $product->get_image_id() != 0 ) {
				$image_src = wp_get_attachment_image_src( $product->get_image_id(), 'full' );
				$image_src_thumb = wp_get_attachment_image_src( $product->get_image_id(), 'thumbnail' );
				$images[ $i ]['src'] = $image_src[0];
				$images[ $i ]['thumbnail'] = $image_src_thumb[0];
				$i++;
			}
			$attachments_exp = $product->get_gallery_image_ids();
			if ( ! empty( $attachments_exp ) ) {
				foreach ( $attachments_exp as $id ) {
					$image_src = wp_get_attachment_image_src( $id, 'full' );
					$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
					$images[ $i ]['src'] = $image_src[0];
					$images[ $i ]['thumbnail'] = $image_src_thumb[0];
					$i++;
				}
			}
			// End.
			$price = 0;
			if ( ! empty( $product->get_sale_price() ) ) {
				$price = $product->get_sale_price();
			} elseif ( ! empty( $product->get_price() ) ) {
				$price = $product->get_price();
			}
			$response = array();
			if ( ! empty( $attributes ) ) {
				$i = 0;
				foreach ( $attributes as $attribute ) {
					$attribute_details = $this->get_attribute_type( $attribute['id'] );
					if ( $attribute['visible'] ) {
						foreach ( $attribute['options'] as $j => $option ) {
							$term = get_term_by( 'id', $option, 'pa_' . $attribute_details->attribute_name );
							$response[ $i ]['values'][ $j ] = $term->name;
						}
						$response[ $i ]['id'] = $attribute['id'];
						$response[ $i ]['product_id'] = $product_id;
						$response[ $i ]['name'] = $attribute_details->attribute_label;
						$response[ $i ]['position'] = $attribute['position'];
						$i++;
					}
				}
			}

			/*Fetch all Variation Data*/
			if ( ! empty( $variation_ids ) ) {
				foreach ( $variation_ids as $k => $variation_id ) {
					$vimages = array();
					$vimage_src = array();
					$vimage_src_thumb = array();
					$variation = wc_get_product( $variation_id );
					$manage_stock = $variation->manage_stock;
					$stock_status = $variation->stock_status;					
					// Get the variation quantity.
					if ( 1 != $manage_stock && 'instock' == $stock_status ) {
						$stock_qty = 1000;
					} else {
						$stock_qty = $variation->get_stock_quantity(); // Stock qty.
					}
					$variations[ $k ]['id'] = $variation->get_id();
					$variations[ $k ]['price'] = $variation->get_price();
					$variations[ $k ]['sku'] = $variation->get_sku();
					$variations[ $k ]['inventory_quantity'] = $stock_qty;
					$l = 0;
					if ( 0 != $variation->get_image_id() ) {
						$vimage_src = wp_get_attachment_image_src( $variation->get_image_id(), 'full' );
						$vimage_src_thumb = wp_get_attachment_image_src( $variation->get_image_id(), 'thumbnail' );
						$vimages[ $l ]['src'] = $vimage_src[0];
						$vimages[ $l ]['thumbnail'] = $vimage_src_thumb[0];
						$l++;
					}
					$attachments_exp = $variation->get_gallery_image_ids();
					if ( ! empty( $attachments_exp ) ) {
						foreach ( $attachments_exp as $id ) {
							$vimage_src = wp_get_attachment_image_src( $id, 'full' );
							$vimage_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
							$vimages[ $l ]['src'] = $vimage_src[0];
							$vimages[ $l ]['thumbnail'] = $vimage_src_thumb[0];
							$l++;
						}
					} else {
						// Added parent product images.
						$attachments_exp = $product->get_gallery_image_ids();
						if ( ! empty( $attachments_exp ) ) {
							foreach ( $attachments_exp as $id ) {
								$vimage_src = wp_get_attachment_image_src( $id, 'full' );
								$vimage_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
								$vimages[ $l ]['src'] = $image_src[0];
								$vimages[ $l ]['thumbnail'] = $image_src_thumb[0];
								$l++;
							}
						}
					}
					$variations[ $k ]['images'] = $vimages;
					$j = 0;
					foreach ( $variation->get_attributes() as $taxonomy => $terms_slug ) {
						// To get the taxonomy object.
						$taxonomy_obj = get_taxonomy( $taxonomy );
						$term_details = get_term_by( 'slug', $terms_slug, $taxonomy );
						$taxonomy_name = $taxonomy_obj->name;
						$taxonomy_label = $taxonomy_obj->label;
						$name = str_replace( 'pa_', '', $taxonomy_name );
						$sql = 'SELECT * FROM ' . $attribute_taxonomies . ' WHERE attribute_name ="' . $name . '"';
						$results = $wpdb->get_results( $sql, ARRAY_A );
						if ( ! empty( $results ) ) {
							$variations[ $k ][ $results[0]['attribute_label'] ] = $term_details->name;
							if ( $results[0]['attribute_label'] == $color_attr ) {
								$variations[ $k ]['color_look_up'] = $term_details->term_id;
							}
						}
						$j++;
					}
				}
			} else {
				// For simple type product.
				$variations[0]['id'] = $product->get_id();
				$variations[0]['price'] = $price;
				$variations[0]['sku'] = $product->get_sku();
				$variations[0]['inventory_quantity'] = $stock_qty;
				$variations[0]['images'] = $images;
			}
			/*End*/
			$product_response['product_id'] = $product->get_id();
			$product_response['product_name'] = $product->get_name();
			$product_response['sku'] = $product->get_sku();
			$product_response['product_type'] = $product->get_type();
			$product_response['product_description'] = preg_replace( '/\r|\n/', '', $product->get_description() );
			$product_response['price'] = $price;
			$product_response['tax'] = 0;
			$product_response['stock_quantity'] = $stock_qty;
			$product_response['options'] = $response;
			$product_response['variants'] = $variations;
			if ( ! empty( $variations ) && ! empty( $variations[0]['images'] ) ) {
				$product_response['images'] = $variations[0]['images'];
			} else {
				$product_response['images'] = $images;
			}
			return $product_response;
		}

		/**
		 * Method to update customer
		 */
		public function inkxe_get_product_variants_arguments() {
			$args = array();
			$args['product_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_products_variants( $request ) {
			global $wpdb;
			$product_variation_array = array();
			$product_id = $request['product_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$prefix = $wpdb->prefix;
			$attribute_taxonomies = $prefix . 'woocommerce_attribute_taxonomies';
			$product = wc_get_product( $product_id );
			$prouct_name = $product->get_name() ? $product->get_name() : '';
			$src = wp_get_attachment_url( $product->get_image_id() ) ? wp_get_attachment_url( $product->get_image_id() ) : '';
			if ( ! empty( $product->get_children() ) ) {
				$current_products = $product->get_children();
				foreach ( $product->get_children() as $key => $values ) {
					$attributes_array = array();
					$product_ariation_id = $values;
					$max_regular_price = $product->get_variation_regular_price( 'max' );
					$url = get_permalink( $product_ariation_id );
					$variation_obj = wc_get_product( $product_ariation_id );
					$manage_stock = $variation_obj->manage_stock;
					$stock_status = $variation_obj->stock_status;
					$sale_price = $variation_obj->sale_price;
					$regular_price = $variation_obj->regular_price;
					$description = $variation_obj->description;
					$sku = $variation_obj->sku;
					$price = $variation_obj->price;
					// Get the variation quantity.
					if ( 1 != $manage_stock && 'instock' == $stock_status ) {
						$stock_qty = 1000;
					} else {
						$stock_qty = $variation_obj->get_stock_quantity(); // Stock qty.
					}

					$variation_attributesobj = new WC_Product_Variation( $product_ariation_id );
					$j = 0;
					foreach ( $variation_attributesobj->get_attributes() as $taxonomy => $terms_slug ) {
						// To get the taxonomy object.
						$taxonomy_obj = get_taxonomy( $taxonomy );
						$term_details = get_term_by( 'slug', $terms_slug, $taxonomy );
						$taxonomy_name = $taxonomy_obj->name;
						$taxonomy_label = $taxonomy_obj->label;
						$name = str_replace( 'pa_', '', $taxonomy_name );
						$sql = 'SELECT * FROM ' . $attribute_taxonomies . ' WHERE attribute_name =' . $name;
						$results = $wpdb->get_results( $sql, ARRAY_A );
						$attributes_array[ $j ]['id'] = $results[0]['attribute_id'];
						$attributes_array[ $j ]['name'] = $results[0]['attribute_label'];
						$attributes_array[ $j ]['option'] = $term_details->name;
						$j++;
					}

					/** Get image data */

					$product_variation_array[ $key ]['id'] = $product_ariation_id;
					$product_variation_array[ $key ]['description'] = $description;
					$product_variation_array[ $key ]['permalink'] = $url;
					$product_variation_array[ $key ]['sku'] = $sku;
					$product_variation_array[ $key ]['price'] = $price;
					$product_variation_array[ $key ]['regular_price'] = $regular_price;
					$product_variation_array[ $key ]['sale_price'] = $sale_price;
					$product_variation_array[ $key ]['manage_stock'] = $manage_stock;
					$product_variation_array[ $key ]['stock_status'] = $stock_status;
					$product_variation_array[ $key ]['stock_quantity'] = $stock_qty;
					$product_variation_array[ $key ]['image'] = array(
						'id' => $product_ariation_id,
						'src' => $src,
						'name' => $prouct_name,
					);
					$product_variation_array[ $key ]['attributes'] = $attributes_array;

				}
			}
			return $product_variation_array;
		}

		/**
		 * Method to update customer
		 */
		public function inkxe_get_product_categories_arguments() {
			$args = array();
			$args['product_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 20,
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_products_categories( $request ) {
			$categories = array();
			$product_id = $request['product_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$categories_response = wp_get_post_terms( $product_id, 'product_cat', array( 'fields' => 'ids' ) );
			if ( ! empty( $categories_response ) ) {
				foreach ( $categories_response as $key => $value ) {
					$categories[ $key ]['id'] = $value;
				}
			}
			return $categories;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_save_tier( $request ) {
			$status = false;
			$request_parameter = $request->get_params();
			$product_id = $request_parameter['product_id'];
			$tier_data = $request_parameter['tier_data'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			if ( ! empty( $product_id ) ) {
				$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
				if ( ! empty( $meta_data_content ) ) {
					delete_post_meta( $product_id, 'imprintnext_tier_content' );
				}
				$result = add_post_meta( $product_id, 'imprintnext_tier_content', $tier_data );
				if ( $result ) {
					$status = true;
				}
			}
			return $status;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_tier( $request ) {
			$tier_content = array();
			$request_parameter = $request->get_params();
			$product_id = $request_parameter['product_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$product = wc_get_product( $product_id );
			$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
			if ( ! empty( $meta_data_content ) ) {
				$tier_content = $meta_data_content[0];
			}
			$tier_content['name'] = $product->name;
			$tier_content['price'] = $product->price;
			return $tier_content;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_product_description( $request ) {
			$request_parameter = $request->get_params();
			$product_id = $request_parameter['product_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$product = wc_get_product( $product_id );
			$description = $product->get_description() ? $product->get_description() : '';
			return $description;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_remove_categories( $request ) {
			$cat_status = 0;
			$request_parameter = $request->get_params();
			$cat_id = $request_parameter['cat_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			if ( wp_delete_term( $cat_id, 'product_cat' ) ) {
				$cat_status = 1;
			}
			return $cat_status;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_create_categories( $request ) {
			$cat_response = array();
			$request_parameter = $request->get_params();
			$cat_id = $request_parameter['cat_id'];
			$cat_name = $request_parameter['cat_name'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$cat_data = term_exists( $cat_name, 'product_cat' );
			if ( 0 == $cat_data && null == $cat_data ) {
				$cat_res = wp_insert_term(
					$cat_name,
					'product_cat',
					array(
						'parent' => $cat_id,
					)
				);
				if ( is_wp_error( $cat_res ) ) {
					$cat_response = array();
				} else {
					$cat_response = array(
						'status' => 1,
						'catatory_id' => $cat_res['term_id'],
						'message' => 'Catagories saved',
					);
				}
			} else {
				$cat_response = array(
					'status' => 0,
					'message' => 'Category already exist.',
				);
			}
			return $cat_response;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_get_categories_subcategories( $request ) {
			$categories = array();
			$request_parameter = $request->get_params();
			$name = $request_parameter['name'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$taxonomy = 'product_cat';
			$order = 'desc';
			$orderby = 'id';
			$empty = 0;
			$args = array(
				'taxonomy' => $taxonomy,
				'orderby' => $orderby,
				'order' => $order,
				'hide_empty' => $empty,
			);
			$categories_list = get_categories( $args );
			$i = 0;
			if ( ! empty( $categories_list ) ) {
				foreach ( $categories_list as $cat ) {
					if ( $cat->parent < 1 ) {
						$sub_args = array(
							'taxonomy' => $taxonomy,
							'orderby' => 'name',
							'order' => 'ASC',
							'child_of' => $cat->cat_ID,
							'hide_empty' => $empty,
						);
						$sub_categories_list = get_categories( $sub_args );
						$j = 0;
						$sub_categories = array();
						foreach ( $sub_categories_list as $sub_category ) {
							$sub_categories[ $j ] = array(
								'id' => $sub_category->term_id,
								'name' => htmlspecialchars_decode( $sub_category->name, ENT_NOQUOTES ),
								'slug' => $sub_category->slug,
								'parent_id' => $sub_category->parent,
							);
							$j++;
						}
						$categories[ $i ] = array(
							'id' => $cat->term_id,
							'name' => htmlspecialchars_decode( $cat->name, ENT_NOQUOTES ),
							'slug' => $cat->slug,
							'parent_id' => $cat->parent,
							'sub_catagory' => $sub_categories,
						);
						$i++;
					}
				}
			}
			return $categories;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_check_create_attribute( $request ) {
			$is_exist_attr = false;
			$request_parameter = $request->get_params();
			$att_name = $request_parameter['name'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			if ( taxonomy_exists( wc_attribute_taxonomy_name( $att_name ) ) ) {
				$is_exist_attr = true;
			}
			return $is_exist_attr;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_create_new_attribute( $request ) {
			$create_attr = false;
			global $wpdb;
			$request_parameter = $request->get_params();
			$attribute = $request_parameter['attribute'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );
			$attr_id = $wpdb->insert_id;
			if ( ! empty( $term ) ) {
				$wpdb->insert( $wpdb->prefix . 'terms', $term );
				$term_id = $wpdb->insert_id;
				$taxonomy = 'pa_' . $attribute['attribute_name'];
				$term_taxonomy_color = array(
					'term_id' => $term_id,
					'taxonomy' => $taxonomy,
					'description' => '',
					'parent' => '0',
					'count' => '0',
				);
				$wpdb->insert( $wpdb->prefix . 'term_taxonomy', $term_taxonomy_color );
				$create_attr = true;
			}
			do_action( 'woocommerce_attribute_added', $attr_id, $attribute );
			flush_rewrite_rules();
			delete_transient( 'wc_attribute_taxonomies' );
			return $create_attr;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function inkxe_create_attribute( $request ) {
			$create_attr = 0;
			global $wpdb;
			$request_parameter = $request->get_params();
			$attribute = $request_parameter['attribute'];
			$term = $request_parameter['term'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$table_name = $wpdb->prefix . 'terms';
			$table_taxonomy = $wpdb->prefix . 'term_taxonomy';
			if ( ! empty( $term ) ) {
				$attr_slug = wc_attribute_taxonomy_name( $attribute['attribute_name'] );
				$taxval = $wpdb->get_var( "SELECT ts.term_id FROM $table_taxonomy as ts
	                join $table_name as tm on ts.term_id = tm.term_id
	                WHERE ts.taxonomy = '$attr_slug' and tm.name ='" . $term['name'] . "'" );
				if ( ! $taxval ) {
					$wpdb->insert( $wpdb->prefix . 'terms', $term );
					$term_id = $wpdb->insert_id;
					$taxonomy = 'pa_' . strtolower( $attribute['attribute_name'] );
					$term_taxonomy_color = array(
						'term_id' => $term_id,
						'taxonomy' => $taxonomy,
						'description' => '',
						'parent' => '0',
						'count' => '0',
					);
					$wpdb->insert( $wpdb->prefix . 'term_taxonomy', $term_taxonomy_color );
					$create_attr = 1;
				}
			}
			return $create_attr;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_customer_details_with_order( $request ) {
			global $wpdb;
			$store_response = array();
			$request_parameter = $request->get_params();
			$user_id = $request_parameter['user_id'];
			$store_id = $request_parameter['store_id'];
			$is_line_items = $request_parameter['is_line_items'];
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$get_customer_data = get_user_by( 'ID', $user_id );
			$get_cust_data_delete = (array) $get_customer_data->roles;
			if ( '' == trim( $get_cust_data_delete[0] ) ) {
				return false;
			}
			$get_customer_data = (array) $get_customer_data->data;
			$customer_id = $get_customer_data['ID'];
			$first_name = get_user_meta( $customer_id, 'first_name', true ) ? get_user_meta( $customer_id, 'first_name', true ) : '';
			$last_name = get_user_meta( $customer_id, 'last_name', true ) ? get_user_meta( $customer_id, 'last_name', true ) : '';
			$get_customer_data['store_id'] = $store_id;
			$order_details = $this->get_order_data(
				array(
					'id' => $get_customer_data['ID'],
				),
				'customer',
				$is_line_tems
			);
			$total_spent = $order_details['total_spent'];
			$prepare_order = $order_details['prepare_order'];
			$total_order_count = $order_details['orders_count'];
			$last_order_detail = ( ! empty( $prepare_order ) && $prepare_order > 0 ) ? $prepare_order : null;
			$last_order_detail_id = '';
			$last_order = wc_get_customer_last_order( $customer_id );
			$last_order_time = '';
			if ( ! empty( $last_order ) ) {
				$last_order_detail_id = $last_order->get_id();
				$last_order_time = $last_order_detail[0]['created_date'];
			}
			$i = 0;
			$country_name = '';
			$state_name = '';

			/*GET BILLING DETAILS*/
			$get_customer_data['billing']['first_name'] = get_user_meta( $customer_id, 'billing_first_name', true );
			$get_customer_data['billing']['last_name'] = get_user_meta( $customer_id, 'billing_last_name', true );
			$get_customer_data['billing']['address_1'] = get_user_meta( $customer_id, 'billing_address_1', true );
			$get_customer_data['billing']['address_2'] = get_user_meta( $customer_id, 'billing_address_2', true );
			$get_customer_data['billing']['city'] = get_user_meta( $customer_id, 'billing_city', true );
			$get_customer_data['billing']['state'] = get_user_meta( $customer_id, 'billing_state', true );
			$get_customer_data['billing']['postcode'] = get_user_meta( $customer_id, 'billing_postcode', true );
			$get_customer_data['billing']['country'] = get_user_meta( $customer_id, 'billing_country', true );
			$get_customer_data['billing']['email'] = get_user_meta( $customer_id, 'billing_email', true );
			$get_customer_data['billing']['phone'] = get_user_meta( $customer_id, 'billing_phone', true );

			/**GET SHIPPING DETAILS*/
			$shipping_address[ $i ]['address_1'] = get_user_meta( $customer_id, 'shipping_address_1', true );
			$shipping_address[ $i ]['address_2'] = get_user_meta( $customer_id, 'shipping_address_2', true );
			$shipping_address[ $i ]['city'] = get_user_meta( $customer_id, 'shipping_city', true );
			$shipping_address[ $i ]['state'] = get_user_meta( $customer_id, 'shipping_state', true );
			$shipping_address[ $i ]['postcode'] = get_user_meta( $customer_id, 'shipping_postcode', true );
			$shipping_address[ $i ]['country'] = get_user_meta( $customer_id, 'shipping_country', true );
			$shipping_address[ $i ]['mobile_no'] = get_user_meta( $customer_id, 'shipping_phone', true );
			$shipping_address[ $i ]['id'] = '0';
			$shipping_address[ $i ]['is_default'] = 1;
			if ( get_user_meta( $customer_id, 'shipping_state', true ) && get_user_meta( $customer_id, 'shipping_country', true ) ) {
				$country_code = get_user_meta( $customer_id, 'shipping_country', true );
				$state_code = get_user_meta( $customer_id, 'shipping_state', true );
				$country_name = WC()->countries->countries[ $country_code ] ? WC()->countries->countries[ $country_code ] : '';
				$state_name = WC()->countries->states[ $country_code ][ $state_code ] ? WC()->countries->states[ $country_code ][ $state_code ] : '';
			}
			$shipping_address[ $i ]['country_name'] = $country_name;
			$shipping_address[ $i ]['state_name'] = $state_name;
			$multiple_shipping_address = $wpdb->prefix . 'multipleshippingaddress';
			$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $multiple_shipping_address ) );
			$shipping_response = array();
			if ( $wpdb->get_var( $query ) == $multiple_shipping_address ) {
				$sql = 'SELECT *  FROM ' . $multiple_shipping_address . ' WHERE user_id=' . $customer_id;
				$shipping_response = $wpdb->get_results( $sql );
			}
			if ( ! empty( $shipping_response ) ) {
				foreach ( $shipping_response as $key => $value ) {
					$i++;
					$shipping_address[ $i ]['first_name'] = $value['first_name'];
					$shipping_address[ $i ]['last_name'] = $value['last_name'];
					$shipping_address[ $i ]['company'] = '';
					$shipping_address[ $i ]['address_1'] = $value['address_line_one'];
					$shipping_address[ $i ]['address_2'] = $value['address_line_two'];
					$shipping_address[ $i ]['city'] = $value['city'];
					$shipping_address[ $i ]['postcode'] = $value['postcode'];
					$shipping_address[ $i ]['country'] = $value['country'];
					$shipping_address[ $i ]['state'] = $value['state'];
					$shipping_address[ $i ]['mobile_no'] = $value['mobile_no'];
					$shipping_address[ $i ]['id'] = $value['id'];
					$shipping_address[ $i ]['country_name'] = WC()->countries->countries[ $value['country'] ] ? WC()->countries->countries[ $value['country'] ] : '';
					$shipping_address[ $i ]['state_name'] = WC()->countries->states[ $value['country'] ][ $value['state'] ] ? WC()->countries->states[ $value['country'] ][ $value['state'] ] : '';
				}
			}
			$customer_order_details = array(
				'id' => $customer_id,
				'first_name' => $first_name,
				'last_name' => $last_name,
				'email' => $get_customer_data['user_email'],
				'profile_pic' => get_avatar_url( $customer_id ),
				'total_orders' => $total_order_count,
				'total_order_amount' => $total_spent,
				'average_order_amount' => ( ! empty( $prepare_order ) && $prepare_order > 0 ) ? $total_spent / count( $prepare_order ) : 0,
				'last_order' => $last_order_time,
				'last_order_id' => $last_order_detail_id,
				'date_created' => date( 'd/M/Y H:i:s', strtotime( $get_customer_data['user_registered'] ) ),
				'billing_address' => $get_customer_data['billing'],
				'shipping_address' => $shipping_address,
				'orders' => ( ! empty( $prepare_order ) && count( $prepare_order ) > 0 ) ? $prepare_order : array(),
			);
			$store_response = $customer_order_details;
			return $store_response;
		}

		/**
		 * Method to update customer
		 *
		 * @param array $customers Customer array.
		 * @param mixed $cust_flag Customer flag.
		 * @param mixed $is_line_tems Is line item.
		 */
		public function get_order_data( $customers, $cust_flag, $is_line_tems = 0 ) {
			$total_spent = 0;
			$order_options = array();
			$line_orders = array();
			$order_options['customer'] = $customers['id'];
			$prepare_order = array();
			$get_orders = get_posts(
				array(
					'numberposts' => -1,
					'meta_key' => '_customer_user',
					'meta_value' => $customers['id'],
					'post_type' => wc_get_order_types(),
					'post_status' => array_keys( wc_get_order_statuses() ),
				)
			);
			if ( isset( $get_orders ) && count( $get_orders ) > 0 ) {
				$i = 0;
				foreach ( $get_orders as $order_value ) {
					$order_id = $order_value->ID;
					$order = wc_get_order( $order_id );
					$currency = $order->get_currency() ? $order->get_currency() : null;
					$total = $order->get_total() ? $order->get_total() : 0.00;
					$created_date = $order->get_date_created()->format( 'j/M/Y H:i:s' );
					$order_status = $order->get_status();
					$first_name = $order->get_billing_first_name() ? $order->get_billing_first_name() : '';
					$last_name = $order->get_billing_last_name() ? $order->get_billing_last_name() : '';
					if ( 'customer' === $cust_flag ) {
						if ( ! empty( $order->get_items() ) ) {
							$quantity = 0;
							foreach ( $order->get_items() as $item_id => $item_values ) {
								$quantity += $item_values['quantity'];
							}
						}
						$prepare_order[] = array(
							'id' => $order_id,
							'currency' => $currency,
							'created_date' => $created_date,
							'total_amount' => $total, // tax will be incl.
							'quantity' => $quantity,
						);
						// Added order line items.
						if ( $is_line_tems ) {
							if ( ! empty( $order->get_items() ) ) {
								$line = 0;
								$line_orders = array();
								foreach ( $order->get_items() as $item_id => $item ) {
									$product = $item->get_product();
									$product_sku = null;
									if ( is_object( $product ) ) {
										$product_sku = $product->get_sku();
									}
									$product_id = $item->get_product_id();
									$variation_id = $item->get_variation_id();
									$variation_id = isset( $variation_id ) && $variation_id > 0 ? $variation_id : $product_id;
									$line_orders[ $line ]['id'] = $item->get_id();
									$line_orders[ $line ]['product_id'] = $product_id;
									$line_orders[ $line ]['variant_id'] = $variation_id;
									$line_orders[ $line ]['name'] = $item->get_name();
									$line_orders[ $line ]['sku'] = $product_sku;
									$line_orders[ $line ]['quantity'] = $item->get_quantity();
									$line_orders[ $line ]['price'] = $item->get_subtotal();
									$line_orders[ $line ]['total'] = $order->get_item_meta( $item_id, '_line_total', true );
									$meta_data = $item->get_meta_data();
									$formatted_meta = array();
									$product_image_array = array();
									$j = 0;
									$k = 0;
									foreach ( $meta_data as $meta ) {
										$name = str_replace( 'pa_', '', $meta->key );
										if ( 'custom_design_id' == $name ) {
											$custom_design_id = $meta->value;
											$formatted_meta[ $j ] = $custom_design_id;
											$j++;
										}
									}
									if ( 0 != $product->image_id ) {
										$image_src = wp_get_attachment_image_src( $product->image_id, 'full' );
										$image_src_thumb = wp_get_attachment_image_src( $product->image_id, 'thumbnail' );
										$product_image_array[ $k ]['src'] = $image_src[0];
										$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
										$k++;
									}
									if ( $product_id != $variant_id ) {
										$attachments = get_post_meta( $variant_id, 'variation_image_gallery', true );
										$attachments_exp = array_filter( explode( ',', $attachments ) );
										foreach ( $attachments_exp as $id ) {
											$image_src = wp_get_attachment_image_src( $id, 'full' );
											$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
											$product_image_array[ $k ]['src'] = $image_src[0];
											$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
											$k++;
										}
									} else {
										foreach ( $product->gallery_image_ids as $id ) {
											$image_src = wp_get_attachment_image_src( $id, 'full' );
											$image_src_thumb = wp_get_attachment_image_src( $id, 'thumbnail' );
											$product_image_array[ $k ]['src'] = $image_src[0];
											$product_image_array[ $k ]['thumbnail'] = $image_src_thumb[0];
											$k++;
										}
									}
									$line_orders[ $line ]['custom_design_id'] = $formatted_meta[0];
									$line_orders[ $line ]['images'] = $product_image_array;
									$line++;
								}
							}
							$prepare_order[ $i ]['lineItems'] = $line_orders;
						}
						$total_spent = $total_spent + $total;
					} else {
						$prepare_order[] = array(
							'id' => $order_id,
							'order_number' => $order_id,
							'customer_first_name' => $first_name,
							'customer_last_name' => $last_name,
							'created_date' => $created_date,
							'total_amount' => $total,
							'currency' => $currency,
							'status' => $order_status,

						);
					}
					$i++;
				}
			}
			$order_details = array(
				'prepare_order' => $prepare_order,
				'total_spent' => $total_spent,
				'orders_count' => count( $get_orders ),
			);
			return $order_details;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function total_customer_count( $request ) {
			$total_user = 0;
			$request_parameter = $request->get_params();
			$store_id = $request_parameter['store_id'];
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$count_args = array(
				'role' => 'Customer',
			);
			$wp_user_query = new \WP_User_Query( $count_args );
			$total_user = (int) $wp_user_query->get_total();
			return $total_user;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_customer_id( $request ) {
			global $wpdb;
			$customer_ids = array();
			$request_parameter = $request->get_params();
			$store_id = $request_parameter['store_id'];
			$per_page = $request_parameter['per_page'];
			$page = $request_parameter['page'];
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$args = array(
				'role' => 'Customer',
				'offset' => $page ? ( $page - 1 ) * $per_page : 0,
				'number' => $per_page,

			);
			$users = get_users( $args );
			if ( ! empty( $users ) ) {
				$i = 0;
				foreach ( $users as $user ) {
					$customer_ids[ $i ]['id'] = $user->ID;
					$i++;
				}
			}
			return $customer_ids;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_quote_customer_details( $request ) {
			$customer_details = array();
			global $wpdb;
			$request_parameter = $request->get_params();
			$customer_id = $request_parameter['customer_id'];
			$store_id = $request_parameter['store_id'];
			$ship_id = $request_parameter['ship_id'];
			$is_address = $request_parameter['is_address'];
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$store_response = get_userdata( $customer_id );
			if ( ! empty( $store_response ) ) {
				$store_response = (array) $store_response->data;
				$customer_details['customer']['id'] = $store_response['ID'];
				$customer_details['customer']['email'] = $store_response['user_email'];
				$first_name = get_user_meta( $customer_id, 'first_name', true ) ? get_user_meta( $customer_id, 'first_name', true ) : '';
				$last_name = get_user_meta( $customer_id, 'last_name', true ) ? get_user_meta( $customer_id, 'last_name', true ) : '';
				$customer_details['customer']['name'] = $first_name . ' ' . $last_name;
				$customer_details['customer']['phone'] = get_user_meta( $customer_id, 'billing_phone', true );

				if ( true == $is_address ) {
					$customer_details['customer']['billing_address']['first_name'] = ! empty( get_user_meta( $customer_id, 'billing_first_name', true ) ) ? get_user_meta( $customer_id, 'billing_first_name', true ) : $first_name;
					$customer_details['customer']['billing_address']['last_name'] = ! empty( get_user_meta( $customer_id, 'billing_last_name', true ) ) ? get_user_meta( $customer_id, 'billing_last_name', true ) : $last_name;
					$customer_details['customer']['billing_address']['address_1'] = get_user_meta( $customer_id, 'billing_address_1', true );
					$customer_details['customer']['billing_address']['address_2'] = get_user_meta( $customer_id, 'billing_address_2', true );
					$customer_details['customer']['billing_address']['city'] = get_user_meta( $customer_id, 'billing_city', true );
					$customer_details['customer']['billing_address']['state'] = get_user_meta( $customer_id, 'billing_state', true );
					$customer_details['customer']['billing_address']['postcode'] = get_user_meta( $customer_id, 'billing_postcode', true );
					$customer_details['customer']['billing_address']['country'] = get_user_meta( $customer_id, 'billing_country', true );
					$customer_details['customer']['billing_address']['email'] = get_user_meta( $customer_id, 'billing_email', true );
					$customer_details['customer']['billing_address']['phone'] = get_user_meta( $customer_id, 'billing_phone', true );
					$customer_details['customer']['billing_address']['company'] = '';

					if ( 0 == $ship_id ) {
						$customer_details['customer']['shipping_address'][0]['id'] = 0;
						$customer_details['customer']['shipping_address'][0]['first_name'] = get_user_meta( $customer_id, 'shipping_first_name', true );
						$customer_details['customer']['shipping_address'][0]['last_name'] = get_user_meta( $customer_id, 'shipping_last_name', true );
						$customer_details['customer']['shipping_address'][0]['company'] = get_user_meta( $customer_id, 'company', true ) ? get_user_meta( $customer_id, 'company', true ) : '';
						$customer_details['customer']['shipping_address'][0]['address_1'] = get_user_meta( $customer_id, 'shipping_address_1', true );
						$customer_details['customer']['shipping_address'][0]['address_2'] = get_user_meta( $customer_id, 'shipping_address_2', true ) ? get_user_meta( $customer_id, 'shipping_address_2', true ) : '';
						$customer_details['customer']['shipping_address'][0]['city'] = get_user_meta( $customer_id, 'shipping_city', true );
						$customer_details['customer']['shipping_address'][0]['state'] = get_user_meta( $customer_id, 'shipping_state', true );
						$customer_details['customer']['shipping_address'][0]['postcode'] = get_user_meta( $customer_id, 'shipping_postcode', true );
						$customer_details['customer']['shipping_address'][0]['country'] = get_user_meta( $customer_id, 'shipping_country', true );
						$customer_details['customer']['shipping_address'][0]['is_default'] = 1;
						$customer_details['customer']['shipping_address'][0]['phone'] = get_user_meta( $customer_id, 'shipping_phone', true );

						$country_name = WC()->countries->countries[ get_user_meta( $customer_id, 'shipping_country', true ) ] ? WC()->countries->countries[ get_user_meta( $customer_id, 'shipping_country', true ) ] : get_user_meta( $customer_id, 'shipping_country', true );
						$state_name = WC()->countries->states[ get_user_meta( $customer_id, 'shipping_country', true ) ][ get_user_meta( $customer_id, 'shipping_state', true ) ] ? WC()->countries->states[ get_user_meta( $customer_id, 'shipping_country', true ) ][ get_user_meta( $customer_id, 'shipping_state', true ) ] : get_user_meta( $customer_id, 'shipping_state', true );

						$customer_details['customer']['shipping_address'][0]['country_name'] = $country_name;
						$customer_details['customer']['shipping_address'][0]['state_name'] = $state_name;
					} else {
						$shipping_address = $wpdb->prefix . 'multipleshippingaddress';
						$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address ) );
						if ( $wpdb->get_var( $query ) == $shipping_address ) {
							$sql = 'SELECT *  FROM ' . $shipping_address . ' WHERE user_id=' . $customer_id . ' AND id=' . $ship_id;
							$result = $wpdb->get_results( $sql );
							if ( ! empty( $result ) ) {
								$customer_details['customer']['shipping_address'][0]['id'] = $result[0]->id;
								$customer_details['customer']['shipping_address'][0]['first_name'] = $result[0]->first_name;
								$customer_details['customer']['shipping_address'][0]['last_name'] = $result[0]->first_name;
								$customer_details['customer']['shipping_address'][0]['company'] = $result[0]->company;
								$customer_details['customer']['shipping_address'][0]['address_1'] = $result[0]->address_line_one;
								$customer_details['customer']['shipping_address'][0]['address_2'] = $result[0]->address_line_two;
								$customer_details['customer']['shipping_address'][0]['city'] = $result[0]->city;
								$customer_details['customer']['shipping_address'][0]['state'] = $result[0]->state;
								$customer_details['customer']['shipping_address'][0]['postcode'] = $result[0]->postcode;
								$customer_details['customer']['shipping_address'][0]['country'] = $result[0]->country;
								$customer_details['customer']['shipping_address'][0]['is_default'] = $result[0]->is_default;
								$customer_details['customer']['shipping_address'][0]['phone'] = $result[0]->mobile_no;
								$country_name = WC()->countries->countries[ $result->country ] ? WC()->countries->countries[ $result[0]->country ] : $result[0]->country;
								$state_name = WC()->countries->states[ $result->country ][ $result->state ] ? WC()->countries->states[ $result[0]->country ][ $result[0]->state ] : $result[0]->state;
								$customer_details['customer']['shipping_address'][0]['country_name'] = $country_name;
								$customer_details['customer']['shipping_address'][0]['state_name'] = $state_name;
							}
						}
					}
				}
			}
			return $customer_details;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_order_log( $request ) {
			global $wpdb;
			$request_parameter = $request['order_option'];
			$order_id = $request_parameter['order_id'];
			$store_id = $request_parameter['store_id'];
			$date_format = $request_parameter['date_format'];
			$store_response = array();
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$store_resp = wc_get_order( $order_id );
			if ( ! empty( $store_resp->get_id() ) && $store_resp->get_id() > 0 ) {
				$store_response[] = array(
					'order_id' => $store_resp->get_id(),
					'message' => $store_resp->get_status(),
					'log_type' => 'order_status',
					'status' => 'new',
					'created_at' => date(
						$date_format,
						strtotime(
							$store_resp->get_date_created()
						)
					),
					'updated_at' => date(
						$date_format,
						strtotime(
							$store_resp->get_date_modified()
						)
					),
				);

				/**
				 * Woocommerce has no payment history logic. So we need to break one
				 * record to multiple histories.  If customer paid for the order then,
				 * paid details will be pushed to the histiry
				 */
				if ( ! empty( $store_resp->get_date_paid() ) ) {
					$store_response[] = array(
						'order_id' => $store_resp->get_id(),
						'message' => ! empty( $store_resp->get_date_paid()->date( 'j/M/Y g:i:s' ) ) ? 'Paid' : 'Not-paid',
						'date_paid' => ! empty( $store_resp->get_date_paid()->date( 'j/M/Y g:i:s' ) )
						? $store_resp->get_date_paid()->date( 'j/M/Y g:i:s' ) : null,
						'payment_method' => ! empty( $store_resp->get_payment_method() )
						? $store_resp->get_payment_method() : null,
						'payment_method_title' => ! empty( $store_resp->get_payment_method_title() )
						? $store_resp->get_payment_method_title() : null,
						'log_type' => 'payment_status',
						'status' => 'new',
						'created_at' => date(
							$date_format,
							strtotime(
								$store_resp->get_date_created()
							)
						),
						'updated_at' => date(
							$date_format,
							strtotime(
								$store_resp->get_date_modified()
							)
						),
					);
				}
			}
			return $store_response;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function update_order_status( $request ) {
			global $woocommerce;
			$request_parameter = $request['order_option'];
			$status = $request_parameter['status'];
			$order_id = $request_parameter['order_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;

			$order_status = '';
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order = wc_get_order( $order_id );
			if ( ! empty( $order ) ) {
				$status_response['id'] = $order->update_status( $status );
			}
			if ( $status_response['id'] > 0 ) {
				$order_status = 'success';
			}
			return $order_status;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_customer_address( $request ) {
			global $woocommerce;
			$request_parameter = $request['order_option'];
			$customer_id = $request_parameter['customer_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$customer_details = array();

			/*GET BILLING DETAILS*/
			$customer_details['billing']['first_name'] = get_user_meta( $customer_id, 'first_name', true );
			$customer_details['billing']['last_name'] = get_user_meta( $customer_id, 'last_name', true );
			$customer_details['billing']['address_1'] = get_user_meta( $customer_id, 'billing_address_1', true );
			$customer_details['billing']['address_2'] = get_user_meta( $customer_id, 'billing_address_2', true );
			$customer_details['billing']['city'] = get_user_meta( $customer_id, 'billing_city', true );
			$customer_details['billing']['state'] = get_user_meta( $customer_id, 'billing_state', true );
			$customer_details['billing']['postcode'] = get_user_meta( $customer_id, 'billing_postcode', true );
			$customer_details['billing']['country'] = get_user_meta( $customer_id, 'billing_country', true );
			$customer_details['billing']['email'] = get_user_meta( $customer_id, 'billing_email', true );
			$customer_details['billing']['phone'] = get_user_meta( $customer_id, 'billing_phone', true );

			/*GET SHIPPING DETAILS*/
			$customer_details['shipping']['first_name'] = ! empty( get_user_meta( $customer_id, 'shipping_first_name', true ) ) ? get_user_meta( $customer_id, 'shipping_first_name', true ) : get_user_meta( $customer_id, 'first_name', true );
			$customer_details['shipping']['last_name'] = ! empty( get_user_meta( $customer_id, 'shipping_last_name', true ) ) ? get_user_meta( $customer_id, 'shipping_last_name', true ) : get_user_meta( $customer_id, 'last_name', true );
			$customer_details['shipping']['address_1'] = get_user_meta( $customer_id, 'shipping_address_1', true );
			$customer_details['shipping']['address_2'] = get_user_meta( $customer_id, 'shipping_address_2', true );
			$customer_details['shipping']['city'] = get_user_meta( $customer_id, 'shipping_city', true );
			$customer_details['shipping']['postcode'] = get_user_meta( $customer_id, 'shipping_postcode', true );
			$customer_details['shipping']['country'] = get_user_meta( $customer_id, 'shipping_country', true );
			$customer_details['shipping']['state'] = get_user_meta( $customer_id, 'shipping_state', true );

			return $customer_details;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function create_custom_order( $request ) {
			global $wpdb;
			global $woocommerce;
			$query_array = $request['order_option'];
			$store_id = $query_array['store_id'] ? $query_array['store_id'] : 1;
			$customer_id = $query_array['customer_id'];
			$quote_id = $query_array['quote_id'] ? $query_array['quote_id'] : 0;
			$is_artwork = $query_array['is_artwork'] ? $query_array['is_artwork'] : 0;
			$is_rush = $query_array['is_rush'] ? $query_array['is_rush'] : 0;
			$rush_type = $query_array['rush_type'] ? $query_array['rush_type'] : '';
			$rush_amount = $query_array['rush_amount'] ? $query_array['rush_amount'] : 0;
			$discount_type = $query_array['discount_type'] ? $query_array['discount_type'] : '';
			$discount_amount = $query_array['discount_amount'] ? $query_array['discount_amount'] : 0;
			$shipping_type = $query_array['shipping_type'] ? $query_array['shipping_type'] : '';
			$shipping_amount = $query_array['shipping_amount'] ? $query_array['shipping_amount'] : 0;
			$design_total = $query_array['design_total'] ? $query_array['design_total'] : 0;
			$quote_total = $query_array['quote_total'] ? $query_array['quote_total'] : 0;
			$shipping_id = $query_array['shipping_id'] ? $query_array['shipping_id'] : 0;
			$note = $query_array['note'] ? $query_array['note'] : '';
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order = wc_create_order();
			$customer_details = array();

			/*GET BILLING DETAILS*/
			$customer_details['billing']['first_name'] = get_user_meta( $customer_id, 'first_name', true );
			$customer_details['billing']['last_name'] = get_user_meta( $customer_id, 'last_name', true );
			$customer_details['billing']['address_1'] = get_user_meta( $customer_id, 'billing_address_1', true );
			$customer_details['billing']['address_2'] = get_user_meta( $customer_id, 'billing_address_2', true );
			$customer_details['billing']['city'] = get_user_meta( $customer_id, 'billing_city', true );
			$customer_details['billing']['state'] = get_user_meta( $customer_id, 'billing_state', true );
			$customer_details['billing']['postcode'] = get_user_meta( $customer_id, 'billing_postcode', true );
			$customer_details['billing']['country'] = get_user_meta( $customer_id, 'billing_country', true );
			$customer_details['billing']['email'] = get_user_meta( $customer_id, 'billing_email', true );
			$customer_details['billing']['phone'] = get_user_meta( $customer_id, 'billing_phone', true );

			/*GET SHIPPING DETAILS*/
			$customer_details['shipping']['first_name'] = ! empty( get_user_meta( $customer_id, 'shipping_first_name', true ) ) ? get_user_meta( $customer_id, 'shipping_first_name', true ) : get_user_meta( $customer_id, 'first_name', true );
			$customer_details['shipping']['last_name'] = ! empty( get_user_meta( $customer_id, 'shipping_last_name', true ) ) ? get_user_meta( $customer_id, 'shipping_last_name', true ) : get_user_meta( $customer_id, 'last_name', true );
			$customer_details['shipping']['address_1'] = get_user_meta( $customer_id, 'shipping_address_1', true );
			$customer_details['shipping']['address_2'] = get_user_meta( $customer_id, 'shipping_address_2', true );
			$customer_details['shipping']['city'] = get_user_meta( $customer_id, 'shipping_city', true );
			$customer_details['shipping']['postcode'] = get_user_meta( $customer_id, 'shipping_postcode', true );
			$customer_details['shipping']['country'] = get_user_meta( $customer_id, 'shipping_country', true );
			$customer_details['shipping']['state'] = get_user_meta( $customer_id, 'shipping_state', true );

			$order->set_address( $customer_details['billing'], 'billing' );

			if ( 0 == $shipping_id ) {
				/* Get  shipping from store*/
				$order->set_address( $customer_details['shipping'], 'shipping' );
			} else {
				/* Get multipleshippingaddress */
				$data = array(
					'customerId' => $customer_id,
					'shippingId' => $shipping_id,
				);
				$shipping = array();
				$shipping_address = array();
				if ( ! empty( $customer_id ) && ! empty( $shipping_id ) ) {
					/* Check table*/
					$shipping_address_table = $wpdb->prefix . 'multipleshippingaddress';
					$query = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $shipping_address_table ) );
					if ( $wpdb->get_var( $query ) == $shipping_address_table ) {
						$sql = 'SELECT *  FROM ' . $shipping_address_table . ' WHERE user_id=' . $customer_id . ' AND id=' . $shipping_id;
						$shipping_address = $wpdb->get_results( $sql );
					}
				}
				if ( ! empty( $shipping_address ) ) {
					$shipping['shipping']['first_name'] = $shipping_address[0]['first_name'];
					$shipping['shipping']['last_name'] = $shipping_address[0]['last_name'];
					$shipping['shipping']['address_1'] = $shipping_address[0]['address_line_one'];
					$shipping['shipping']['address_2'] = $shipping_address[0]['address_line_two'];
					$shipping['shipping']['city'] = $shipping_address[0]['city'];
					$shipping['shipping']['postcode'] = $shipping_address[0]['postcode'];
					$shipping['shipping']['country'] = $shipping_address[0]['country'];
					$shipping['shipping']['state'] = $shipping_address[0]['state'];

					$order->set_address( $shipping['shipping'], 'shipping' );
				}
			}
			// Set other details.
			$order->set_customer_id( $customer_id );
			$order->set_currency( get_woocommerce_currency() );
			$order->set_prices_include_tax( 0 );
			$order->set_customer_note( isset( $query_array['note'] ) ? $query_array['note'] : '' );
			// $order->set_status( 'wc-processing' );.

			$produt_data = $query_array['product_data'];

			// Line items.
			foreach ( $produt_data as $line_item ) {
				$produt_array['product_id'] = $line_item['product_id'];
				$produt_array['variation_id'] = $line_item['variant_id'];

				$produt_array['quantity'] = $line_item['quantity'];
				$produt_array['meta_data'] = array(
					array(
						'key' => 'custom_design_id',
						'value' => $line_item['custom_design_id'] ? $line_item['custom_design_id'] : 0,
					),
					array(
						'key' => 'artwork_type',
						'value' => $line_item['artwork_type'],
					),
					array(
						'key' => 'design_cost',
						'value' => $line_item['design_cost'],
					),
				);
				$product = wc_get_product( isset( $line_item['variant_id'] ) && $line_item['variant_id'] > 0 ? $line_item['variant_id'] : $line_item['product_id'] );
				$price = $product->get_price() + ( $line_item['design_cost'] / $line_item['quantity'] );
				$product->set_price( $price );
				$product_item_id = $order->add_product( $product, $line_item['quantity'], $produt_array );
				wc_add_order_item_meta( $product_item_id, 'custom_design_id', $line_item['custom_design_id'] ? $line_item['custom_design_id'] : 0 );
				wc_add_order_item_meta( $product_item_id, 'artwork_type', $line_item['artwork_type'] );
				wc_add_order_item_meta( $product_item_id, 'design_cost', $line_item['design_cost'] );
			}
			// Fee items.
			$order_id = $order->get_id();

			$order->add_meta_data( 'is_vat_exempt', 'yes', true );
			$fees = array(
				'rush',
				'shipping',
				'tax',
				'discount',
			);
			foreach ( $fees as $fee ) {
				if ( isset( $query_array[ $fee . '_amount' ] ) && '' != $query_array[ $fee . '_amount' ] && $query_array[ $fee . '_amount' ] > 0 ) {
					$lable = ( 'rush' == $fee ) ? 'Rush Surcharge' : ucwords( $fee );
					$amount = ( 'tax' == $fee ) ? ( $design_total * $query_array[ $fee . '_amount' ] ) / 100 : $query_array[ $fee . '_amount' ];
					$amount = ( 'discount' == $fee ) ? -$amount : $amount;

					$item_id = wc_add_order_item(
						$order_id,
						array(
							'order_item_name' => $lable,
							'order_item_type' => 'fee',
						)
					);
					if ( $item_id ) {
						wc_add_order_item_meta( $item_id, '_line_total', $amount );
						wc_add_order_item_meta( $item_id, '_line_tax', 0 );
						wc_add_order_item_meta( $item_id, '_line_subtotal', $amount );
						wc_add_order_item_meta( $item_id, '_line_subtotal_tax', 0 );
					}
				}
			}
			// Set calculated totals.
			$order->calculate_totals();

			$order->set_total( $quote_total );
			// Save order to database (returns the order ID).
			$order_id = $order->save();

			$order->update_status( 'wc-processing' );

			// Update order meta data.
			$order->update_meta_data( '_quote_id', $quote_id );
			$order->update_meta_data( '_is_artwork', $is_artwork );
			$order->update_meta_data( '_is_rush', $is_rush );
			$order->update_meta_data( '_rush_type', $rush_type );
			$order->update_meta_data( '_rush_amount', $rush_amount );
			$order->update_meta_data( '_discount_type', $discount_type );
			$order->update_meta_data( '_discount_amount', $discount_amount );
			$order->update_meta_data( '_shipping_type', $shipping_type );
			$order->update_meta_data( '_shipping_amount', $shipping_amount );
			$order->update_meta_data( '_design_total', $design_total );
			$order->update_meta_data( '_quote_total', $quote_total );

			return array(
				'id' => $order_id,
			);
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_line_item_details( $request ) {
			$request_parameter = $request['order_option'];
			$order_id = $request_parameter['order_id'];
			$store_id = $request_parameter['store_id'] ? $request_parameter['store_id'] : 1;
			$order_item_id = $request_parameter['item_id'];
			$is_customer = $request_parameter['is_customer'];

			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$order_response = array();
			$i = 0;
			if ( ! empty( $store_id ) && ! empty( $order_id ) && ! empty( $order_item_id ) ) {
				$order = wc_get_order( $order_id );
				$item = new \WC_Order_Item_Product( $order_item_id );
				$product = $item->get_product();
				$product_sku = null;
				if ( is_object( $product ) ) {
					$product_sku = $product->get_sku();
					$product_description = $product->get_description();
				}
				$image_id = $product->get_image_id();
				$image_url = wp_get_attachment_image_url( $image_id, 'full' );
				$thumbnail_url = wp_get_attachment_image_url( $image_id, 'thumbnail' );
				$order_response['order_id'] = $order_id;
				$order_response['order_number'] = $order_id;
				$order_response['item_id'] = $order_item_id;
				$order_response['product_id'] = $item->get_product_id();
				$variation_id = $item->get_variation_id();
				$id = isset( $variation_id ) && $variation_id > 0 ? $item->get_variation_id() : $item->get_product_id();
				$order_response['variant_id'] = isset( $variation_id ) && $variation_id > 0 ? $item->get_variation_id() : $item->get_product_id();
				$order_response['name'] = $item->get_name();
				$order_response['quantity'] = $item->get_quantity();
				$order_response['sku'] = $product_sku;
				$order_response['description'] = $product_description;
				if ( true == $is_customer ) {
					$order_response['price'] = $product->get_price();
					$order_response['total'] = $item->get_total();
				}
				$order_response['images'][] = array(
					'src' => $image_url,
					'thumbnail' => $thumbnail_url,
				);

				$item_product = wc_get_product( $item->get_product_id() );
				$category_ids = $item_product->get_category_ids();
				$product = wc_get_product( $id );
				$attributes = $product->get_attributes();
				$order_response['categories'] = $category_ids;
				$attribute = array();
				if ( $order_response['product_id'] != $order_response['variant_id'] ) {
					foreach ( $attributes as $key => $value ) {
						$key = urldecode( $key );
						$attr_term_details = get_term_by( 'slug', $value, $key );
						if ( empty( $attr_term_details ) ) {
							$attr_term_details = get_term_by( 'name', $value, $key );
						}
						$term = wc_attribute_taxonomy_id_by_name( $key );
						$attr_name = wc_attribute_label( $key );
						$attr_val_id = $attr_term_details->term_id;
						$attr_val_name = $attr_term_details->name;
						$attribute[ $attr_name ]['id'] = $attr_val_id;
						$attribute[ $attr_name ]['name'] = $attr_val_name;
						$attribute[ $attr_name ]['attribute_id'] = $term;
						$attribute[ $attr_name ]['hex-code'] = '';
					}
				} else {
					foreach ( $attributes as $attr_key => $attributelist ) {
						if ( 'pa_xe_is_designer' != $attr_key && 'pa_is_catalog' != $attr_key ) {
							foreach ( $attributelist['options'] as $key => $value ) {
								$term = wc_attribute_taxonomy_id_by_name( $attributelist['name'] );
								$attr_name = wc_attribute_label( $attributelist['name'] );
								$attr_val_id = $value;
								$attr_term_details = get_term_by( 'id', absint( $value ), $attributelist['name'] );
								$attr_val_name = $attr_term_details->name;
								$attribute[ $attr_name ]['id'] = $attr_val_id;
								$attribute[ $attr_name ]['name'] = $attr_val_name;
								$attribute[ $attr_name ]['attribute_id'] = $term;
								$attribute[ $attr_name ]['hex-code'] = '';
							}
						}
					}
				}
				$order_response['attributes'] = $attribute;
				if ( true == $is_customer ) {
					$custom_design_id = $item->get_meta( 'custom_design_id' ) ? $item->get_meta( 'custom_design_id' ) : 0;
					$order_response['custom_design_id'] = $custom_design_id;
					$customer_details = array();
					$order = wc_get_order( $order_id );
					$order_response['customer_id'] = $order->get_customer_id();
					$user_data = get_userdata( $order->get_customer_id() );
					$order_response['customer_email'] = $user_data->data->user_email;
					$order_response['customer_first_name'] = get_user_meta( $order->get_customer_id(), 'first_name', true );
					$order_response['customer_last_name'] = get_user_meta( $order->get_customer_id(), 'last_name', true );

					// BILLING INFORMATION.
					$order_response['billing']['first_name'] = $order->get_billing_first_name();
					$order_response['billing']['last_name'] = $order->get_billing_last_name();
					$order_response['billing']['company'] = $order->get_billing_company() ? $order->get_billing_company() : '';
					$order_response['billing']['address_1'] = $order->get_billing_address_1();
					$order_response['billing']['address_2'] = $order->get_billing_address_2();
					$order_response['billing']['city'] = $order->get_billing_city();
					$order_response['billing']['state'] = $order->get_billing_state();
					$order_response['billing']['country'] = $order->get_billing_country();
					$order_response['billing']['postcode'] = $order->get_billing_postcode();

					// SHIPPING INFORMATION.
					$order_response['shipping']['first_name'] = $order->get_shipping_first_name() ? $order->get_shipping_first_name() : '';
					$order_response['shipping']['last_name'] = $order->get_shipping_last_name() ? $order->get_shipping_last_name() : '';
					$order_response['shipping']['address_1'] = $order->get_shipping_address_1() ? $order->get_shipping_address_1() : '';
					$order_response['shipping']['address_2'] = $order->get_shipping_address_2() ? $order->get_shipping_address_2() : '';
					$order_response['shipping']['city'] = $order->get_shipping_city() ? $order->get_shipping_city() : '';
					$order_response['shipping']['state'] = $order->get_shipping_state() ? $order->get_shipping_state() : '';
					$order_response['shipping']['country'] = $order->get_shipping_country() ? $order->get_shipping_country() : '';
					$order_response['shipping']['postcode'] = $order->get_shipping_postcode() ? $order->get_shipping_postcode() : '';
					// $orderResponse['customer_details'] = $customer_details;.
				}
			}
			return $order_response;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function get_attributes_terms( $request ) {
			global $product;
			$request_parameter = $request->get_params();
			$store_id = $request_parameter['store_id'];
			$attributes = $request_parameter['attributes'];
			$getproduct_data = array();
			$store_response = array();
			if ( is_multisite() ) {
				switch_to_blog( $store_id );
			}
			$taxonomy_name = 'xe_is_designer';
			$taxonomy_id = wc_attribute_taxonomy_id_by_name( $taxonomy_name ) ? wc_attribute_taxonomy_id_by_name( $taxonomy_name ) : '';
			$store_response['taxonomy_id'] = $taxonomy_id;
			$product_attributes = array();
			$get_attribute_combinations = array();
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $prod_attributekey => $prod_attribute ) {
					$get_attr_terms_list = array();
					$taxonomy = wc_get_attribute( $prod_attribute['attribute_id'] );
					if ( isset( $getproduct_data ) && ! empty( $getproduct_data ) ) {
						$attr_key = array_search(
							$prod_attribute['attribute_id'],
							array_column(
								$getproduct_data['attributes'],
								'id'
							)
						);
						if ( ! is_bool( $attr_key ) ) {
							foreach ( $getproduct_data['attributes'][ $attr_key ]['options'] as $option ) {
								$term = get_term_by( 'name', $option, $taxonomy->slug );
								$get_attr_terms_list[] = $term->name;
							}
						}
					}
					// Append Attribute Term slugs.
					if ( ! empty( $prod_attribute['attribute_options'] ) ) {
						foreach ( $prod_attribute['attribute_options'] as $attr_termkey => $attr_term ) {
							if ( 'simple' == $product_type ) {
								$get_attr_terms_list = array();
							}
							// Get Product Assoc. Terms from API.
							$term = get_term_by( 'id', $attr_term, $taxonomy->slug );
							$get_attr_terms_list[] = $term->name;
							$get_attribute_combinations[ $prod_attributekey ][] = $prod_attributekey . '___' . $term->name;
						}
					}
					$product_attributes['attributes'][] = array(
						'id' => $prod_attribute['attribute_id'],
						'variation' => true,
						'visible' => true,
						'options' => array_unique( $get_attr_terms_list ),
					);
				}
				$store_response['product_attributes'] = $product_attributes;
			}
			return $store_response;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function save_variants_combination( $request ) {
			$variants_per_combination = array();
			$request_parameter = $request->get_params();
			$store_id = $request_parameter['store_id'];
			$old_variant_id = $request_parameter['old_variant_id'];
			$variations_for_product_id = $request_parameter['variations_for_product_id'];
			$attachments = get_post_meta( $old_variant_id, '_product_image_gallery', true );
			$attachments_exp = array_filter( explode( ',', $attachments ) );
			$i = 0;
			foreach ( $attachments_exp as $id ) {
				$image_src = wp_get_attachment_image_src( $id, 'full' );
				$image = $image_src[0];
				$finfo = getimagesize( $image );
				$type = $finfo['mime'];
				$filename = basename( $image );
				$dir_path = explode( '/uploads', $image );
				$sub_dir = explode( $filename, $dir_path[1] );
				$attachment = array(
					'guid' => $image,
					'post_mime_type' => $type,
					'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content' => '',
					'post_status' => 'inherit',
				);
				$attach_id  = wp_insert_attachment( $attachment, $sub_dir[0] . basename( $filename ), $variations_for_product_id );
				$var_image_id = '';
				if ( ! empty( $attach_id ) ) {
					$var_image_id = $attach_id;
				}
				$variants_per_combination[0]['key']  = '_product_image_gallery';
				$variants_per_combination[0]['value'] = $var_image_id;
				$i++;
			}
			return $variants_per_combination;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function imprint_addtocart( $request ) {
			global $woocommerce;
			global $wpdb;
			// $this->check_prerequisites();.
			$all_post_put_vars = $request['params'];
			$is_pricing_round_up = 0;
			$pricing_round_up_type = '';
			$table_attr_axonomy = $wpdb->prefix . 'woocommerce_attribute_taxonomies';
			$store_response = array();
			$settings_details = $all_post_put_vars['settings'];
			$store = $all_post_put_vars['store_id'];
			$custom_design_id = $all_post_put_vars['custom_design_id'];
			$tier_price_settings = ($settings_details['cart_setting']['tier_price']) ? $settings_details['cart_setting']['tier_price'] : [];
			$is_pricing_round_up = $settings_details['general_settings']['currency']['is_price_round_up'];
			$pricing_round_up_type = $settings_details['general_settings']['currency']['price_round_up_type'];
			$action = $settings_details['cart_setting']['cart_edit']['cart_item_edit_setting'] ? $settings_details['cart_setting']['cart_edit']['cart_item_edit_setting'] : 'add';
			$cart_item_id = ( isset( $all_post_put_vars['cart_item_id'] ) && '' != $all_post_put_vars['cart_item_id'] ) ? $all_post_put_vars['cart_item_id'] : 0;
			$cart_info = array();
			$customer_data = array();
			$customer_id = 0;
			if ( isset( $all_post_put_vars['customer_id'] ) ) {
				$customer_id = intval( $all_post_put_vars['customer_id'] );
				if ( 0 == $customer_id || $customer_id <= 0 ) {
					$customer_id = 0;
				}
			}
			$cart_data = json_decode( stripslashes( $all_post_put_vars['product_data'] ), true );
			if ( isset( $all_post_put_vars['customer_data'] ) ) {
				$customer_data = json_decode( stripslashes( $all_post_put_vars['customer_data'] ), true );
			}
			// For Tier Price.
			if ( ! empty( $cart_data ) ) {
				$product_id = $cart_data[0]['product_id'];
				$total_qty = ( isset( $cart_data[0]['total_qty'] ) && '' != $cart_data[0]['total_qty'] ) ? $cart_data[0]['total_qty'] : 0;
				if ( array_key_exists( 'is_enabled', $tier_price_settings ) ) {
					if ( $tier_price_settings['is_enabled'] ) {
						$meta_data_content = get_post_meta( $product_id, 'imprintnext_tier_content' );
						$tier_price_data = array();
						$common_tier_price = array();
						$variant_tier_price = array();
						$samefor_all_variants = false;
						$is_tier = false;
						if ( ! empty( $meta_data_content ) ) {
							$tier_price_data = $meta_data_content[0];
							$is_tier = true;
							if ( 'true' == $tier_price_data['pricing_per_variants'] ) {
								$samefor_all_variants = true;
								foreach ( $tier_price_data['price_rules'][0]['discounts'] as $discount ) {
									$common_tier_price[] = array(
										'upper_limit' => $discount['upper_limit'],
										'lower_limit' => $discount['lower_limit'],
										'discount' => $discount['discount'],
										'discountType' => $tier_price_data['discount_type'],
									);
								}
							} else {
								foreach ( $tier_price_data['price_rules'] as $variant ) {
									foreach ( $variant['discounts'] as $discount ) {
										$variant_tier_price[ $variant['id'] ][] = array(
											'upper_limit' => $discount['upper_limit'],
											'lower_limit' => $discount['lower_limit'],
											'discount' => $discount['discount'],
											'discountType' => $tier_price_data['discount_type'],
										);
									}
								}
							}
						}
					}
				}
			}
			// End.
			$add_cart = '';
			$params['user_id'] = $all_post_put_vars['user_id'];
			foreach ( $cart_data as $cart ) {
				$cart = (array) $cart;
				if ( $cart['qty'] > 0 ) {
					$cart_meta = array();
					$price = 0;
					$success = 0;
					$id = ( isset( $cart['variant_id'] ) && $cart['variant_id'] != $cart['product_id'] ) ? $cart['variant_id'] : $cart['product_id'];
					$product_id = $cart['product_id'];
					if ( $id != $product_id ) {
						$product = wc_get_product( $id );
					} else {
						$product = wc_get_product( $product_id );
					}
					if ( $product->price ) {
						$price = $product->price;
					}
					$variation = array();
					foreach ( $cart['options'] as $key => $value ) {
						if ( false == strpos( $key, '_id' ) ) {
							if ( '' != $value ) {
								$attr_slug = $wpdb->get_var( "SELECT attribute_name FROM $table_attr_axonomy WHERE attribute_label = '$key'" );
								if ( '' != $attr_slug ) {
									$variation[ 'attribute_pa_' . $attr_slug ] = $value;
								} else {
									$variation[ 'attribute_' . $key ] = $value;
								}
							}
						}
					}
					foreach ( $cart['attributes'] as $akey => $val ) {
						if ( false === strpos( $akey, 'custom_image_' ) && '' != $val ) {
							$cart_meta['extra'][ $akey ] = $val;
						}
					}
					// For Tier Pricing.
					$variant_price = 0;
					$tier_qty = 0;
					if ( $total_qty ) {
						$tier_qty = $total_qty;
					} else {
						$tier_qty = $cart['qty'];
					}
					if ( $is_tier ) {
						$variant_price = ( true === $samefor_all_variants ? $this->get_price_after_tier_discount( $common_tier_price, $price, $tier_qty ) : $this->get_price_after_tier_discount( $variant_tier_price[ $cart['variant_id'] ], $price, $tier_qty ) );
						$tier_price = $variant_price;
						$price = $variant_price;
					}
					// End.
					if ( $cart['is_variable_decoration'] ) {
						$final_unit_price = $cart['added_price'];
					} else {
						$final_unit_price = $price + $cart['added_price'];
					}
					// Calculate Round Up pricing.
					if ( $is_pricing_round_up ) {
						if ( 'upper' == $pricing_round_up_type ) {
							$final_unit_price = ceil( $final_unit_price );
						} else {
							$final_unit_price = floor( $final_unit_price );
						}
					}
					$cart_meta['_other_options']['product-price'] = $final_unit_price;
					$cart_meta['custom_design_id'] = $custom_design_id;
					$variation = (array) $variation;
					// Only for simple products.
					if ( $product_id == $id ) {
						$id = 0;
					}
					$params['cartData'][] = array(
						'product_id' => $product_id,
						'qty' => $cart['qty'],
						'variant_id' => $id,
						'variation' => $variation,
						'meta' => $cart_meta,
					);
					$params['product_id'] = $product_id;
					$params['qty'] = $cart['qty'];
					$params['variant_id'] = $id;
					$params['variation'] = $variation;
					$params['meta'] = $cart_meta;
				}
			}
			$add_cart = $this->woocomm_add_to_cart( $params );
			if ( true == $add_cart['status'] ) {
				$store_response = array(
					'status' => 1,
					'message' => 'Record saved into application',
					'url' => $add_cart['cart_url'],
					'customDesignId' => $custom_design_id,
				);
			} else {
				$store_response = array(
					'status' => 0,
					'message' => array(
						'is_Fault' => 1,
					),
				);
			}
			return $store_response;
		}

		/**
		 * Method to update customer
		 */
		public function im_update_customize_enabled_details_arguments() {
			$args = array();
			$args['product_id'] = array(
				// description should be a human readable description of the argument.
				'description' => esc_html__( 'The filter parameter is used to filter the collection of colors', 'my-text-domain' ),
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
				'default' => 0,
			);
			$args['printable'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			$args['store_id'] = array(
				// type specifies the type of data that the argument should be.
				'type' => 'absint',
			);
			return $args;
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $request Request object.
		 */
		public function im_product_cusomize_enabled( $request ) {
			$product_resposne = array();
			$product_id = $request['product_id'];
			$store_id = $request['store_id'] ? $request['store_id'] : 1;
			$is_customize_product = $request['printable'] ? $request['printable'] : 0;
			$imprint_designer = '';
			if ( $is_customize_product ) {
				$imprint_designer = 'imprint_designer';
			}
			$product_resposne['status'] = 0;
			if ( update_post_meta( $product_id, 'is_customizable', $imprint_designer ) ) {
				$product_resposne['status'] = 1;
			}
			return $product_resposne;
		}

		/**
		 * Method to update customer
		 *
		 * @param array $tier_price_rule Tier price rule.
		 * @param mixed $price Price.
		 * @param int   $quantity Quantity.
		 */
		public function get_price_after_tier_discount( $tier_price_rule, $price, $quantity ) {
			$return_price = $price;
			foreach ( $tier_price_rule as $tier ) {
				if ( $quantity >= $tier['lower_limit'] && $quantity <= $tier['upper_limit'] ) {
					$return_price = ( 'flat' == $tier['discountType'] ? ( $price - $tier['discount'] ) : ( $price - ( ( $tier['discount'] / 100 ) * $price ) ) );
					break;
				} elseif ( $quantity > $tier['upper_limit'] ) {
					$return_price = ( 'flat' == $tier['discountType'] ? ( $price - $tier['discount'] ) : ( $price - ( ( $tier['discount'] / 100 ) * $price ) ) );
				}
			}
			return $return_price;
		}

		/**
		 * Check any prerequisites for our REST request.
		 */
		private function check_prerequisites() {
			if ( defined( 'WC_ABSPATH' ) ) {
				// WC 3.6+ - Cart and other frontend functions are not included for REST requests.
				include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
				include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
			}

			if ( null === WC()->session ) {
				$session_class = apply_filters( 'woocommerce_session_handler', 'WC_Session_Handler' );

				WC()->session = new $session_class();
				WC()->session->init();
			}

			if ( null === WC()->customer ) {
				WC()->customer = new WC_Customer( get_current_user_id(), true );
			}

			if ( null === WC()->cart ) {
				WC()->cart = new WC_Cart();

				// We need to force a refresh of the cart contents from session here (cart contents are normally refreshed on wp_loaded, which has already happened by this point).
				WC()->cart->get_cart();
			}
		}

		/**
		 * Method to update customer
		 *
		 * @param obj $param Param array.
		 */
		private function woocomm_add_to_cart( $param ) {
			global $wpdb;
			if ( defined( 'WC_ABSPATH' ) ) {
				// WC 3.6+ - Cart and other frontend functions are not included for REST requests.
				include_once WC_ABSPATH . 'includes/wc-cart-functions.php';
				include_once WC_ABSPATH . 'includes/wc-notice-functions.php';
				include_once WC_ABSPATH . 'includes/wc-template-hooks.php';
			}

			$user_id = $param['user_id'];
			WC()->session = new WC_Session_Handler();
			WC()->session->init();
			$wc_session_data = WC()->session->get_session( $user_id );

			// Get the persistent cart may be _woocommerce_persistent_cart can be in your case check in user_meta table.
			// $full_user_meta = get_user_meta($user_id,'_woocommerce_persistent_cart_1',true);.

			WC()->customer = new WC_Customer( get_current_user_id(), true );
			// Create new Cart Object.
			WC()->cart = new WC_Cart();
			WC()->cart->get_cart();
			WC()->cart->empty_cart();

			// Add old cart data to newly created cart object.
			if ( $wc_session_data['cart'] ) {
				foreach ( maybe_unserialize( $wc_session_data['cart'] ) as $single_user_meta ) {
					$meta['custom_design_id'] = isset( $single_user_meta['custom_design_id'] ) ? $single_user_meta['custom_design_id'] : '';
					$meta['_other_options']['product-price'] = isset( $single_user_meta['_other_options']['product-price'] ) ? $single_user_meta['_other_options']['product-price'] : '';
					$meta['extra'] = ( isset( $single_user_meta['extra'] ) && ! empty( $single_user_meta['extra'] ) ) ? $single_user_meta['extra'] : '';
					WC()->cart->add_to_cart( $single_user_meta['product_id'], $single_user_meta['quantity'], $single_user_meta['variant_id'], $single_user_meta['variation'], $meta );
				}
			}
			if ( ! empty( $param['cartData'] ) ) {
				foreach ( $param['cartData'] as $cart ) {
					$cart_key = WC()->cart->add_to_cart( $cart['product_id'], $cart['qty'], $cart['variant_id'], $cart['variation'], $cart['meta'] );
				}
			}

			$updated_cart = array();
			foreach ( WC()->cart->cart_contents as $key => $val ) {
				unset( $val['data'] );
				$updated_cart[ $key ] = $val;
			}

			// If there is a current session cart, overwrite it with the new cart.
			if ( $wc_session_data ) {
				$wc_session_data['cart'] = serialize( $updated_cart );
				$serialized_obj = maybe_serialize( $wc_session_data );
				$table_name = $wpdb->prefix . 'woocommerce_sessions';
				// Update the wp_session table with updated cart data.
				$sql = "UPDATE $table_name SET `session_value`= '" . $serialized_obj . "' WHERE  `session_key` = '" . $user_id . "'";
				// Execute the query.
				$rez = $wpdb->query( $sql );
			}

			$response['status'] = true;
			$response['cart_url'] = WC()->cart->get_cart_url();
			return $response;
		}

		/**
		 * Method to update customer
		 *
		 * @param string $string String to encrypt.
		 */
		private function encryption( $string ) {
			$encrypted_val = '';
			if ( '' != $string ) {
				$key = 5;
				$encrypte = $string . '-' . $this->salt;
				$encrypte = base64_encode( $encrypte );
				for ( $i = 0, $k = strlen( $encrypte ); $i < $k; $i++ ) {
					$char = substr( $encrypte, $i, 1 );
					$keychar = substr( $key, ( $i % strlen( $key ) ) - 1, 1 );
					$char = chr( ord( $char ) + ord( $keychar ) );
					$encrypted_val .= $char;
				}
				$encrypted_val = base64_encode( $encrypted_val );
				$encrypted_val = base64_encode( $encrypted_val );
			}
			return $encrypted_val;
		}

		/**
		 * Method to update customer
		 *
		 * @param array $cart_data Cart Data.
		 * @param array $cart_item Cart Item Data.
		 */
		public function display_custom_field_as_item_data( $cart_data, $cart_item ) {
			$extra_attribute = $cart_item['extra'];
			if ( isset( $cart_item['extra'] ) && ! empty( $cart_item['extra'] ) ) {
				foreach ( $cart_item['extra'] as $key => $value ) {
					$cart_data[] = array(
						'key' => $key,
						'value' => sanitize_text_field( $value ),
						'display' => sanitize_text_field( $value ),
					);
				}
			}
			return $cart_data;
		}

		/**
		 * Method to deactivate user store		 
		 */
		public function deactivate_user_store() {
			exit( esc_attr( wp_redirect( admin_url( 'admin.php?page=imprintnext_dashboard&action=deactive' ) ) ) );
		}

		/**
		* Method to create designer page
		*/
		public function create_designer_page() {
			$page_data = array(
				'post_status' => 'publish',
				'post_type' => 'page',
				'post_author' => 1,
				'post_name' => 'riaxe-designer',
				'post_title' => 'Riaxe Product Designer',
				'post_content' => '',
				'post_parent' => 0,
				'comment_status' => 'closed'
			);
			$page_id = wp_insert_post($page_data);
			update_post_meta( $page_id, '_wp_page_template', 'full-width-page-template.php' );
		}

		/**
		* Method to add customize button on product listing page
		*/
		public function ink_pd_customize_button() {
			global $jck_wt, $woocommerce;
			$productId = get_the_ID();
			$product = wc_get_product($productId);
			$categories = implode(",", $product->get_category_ids());
			$resTempAssign = $this->is_template_assign($categories);
			$isAssignTemp = 0;
			if ($resTempAssign['is_enabled']) {
				$isAssignTemp = 1;
			}
			$customDesignId = get_post_meta($productId, 'custom_design_id', true);
			$isCustomizable = get_post_meta($productId, 'is_customizable', true);
			foreach ($product->attributes as $key => $value) {
				$attrTaxoName = $value->get_name();
				if ($attrTaxoName == "pa_xe_is_designer") {
					if (get_term_by('id', $value->get_options()[0], $attrTaxoName)->name == 1) {
						$response = 1;
					} else {
						$response = 0;
					}
				}
			}
			if ($isCustomizable) {
				$xepath = get_site_url();
				if ($product->get_type() == 'variable') {
					$args = array(
						'post_type' => 'product_variation',
						'post_status' => array('publish'),
						'post_parent' => $productId, // get parent post-ID
					);
					$variations = get_posts($args);
					$variation_id = $variations[0]->ID;
				} else {
					$variation_id = $productId;
				}
				if ( get_current_user_id() ) {
					$nounce = get_current_user_id();
				} else {
					WC()->session = new WC_Session_Handler();
					WC()->session->init();
					if ( is_array( WC()->session->get_session_cookie() ) ) {
						$nounce = WC()->session->get_session_cookie()[0];
					} else {
						$cart_item_id = $woocommerce->cart->add_to_cart( $post->ID );
						$woocommerce->cart->remove_cart_item( $cart_item_id );
						?>
						<script type="text/javascript">
							var url = window.location.href;    
							window.location.href = url;
						</script>
						<?php
					}
				}
				$useragent = $_SERVER['HTTP_USER_AGENT'];
				$urlParams = '?id='.$productId.'&key='.$this->token.'&token='.$nounce;				
				$url = $xepath . '/' . $this->custom_path . $urlParams;
				/* get current store id*/
				$currentBlogId = get_current_blog_id() ? get_current_blog_id() : 1;
				// Default quantity pass
				//$url = $url . "&qty=1&store_id=" . $currentBlogId;
				if ($currentBlogId > 1) {
					$checkCustomizeButton = $this->api_path . 'api/v1/multi-store/customize-button/' . $currentBlogId;
					$responseArray = $this->getGeneralSetting($checkCustomizeButton);
					if ($responseArray['data'] == 1) {
						echo '<a style="margin-left: 5%;" id="customize" href="' . $url . '" class="customize-btn button disabled alt" >Customize</a>';
					}
				} else {
					echo '<a style="margin-left: 5%;" id="customize" href="' . $url . '" class="customize-btn button disabled alt" >Customize</a>';
				}
				?>
	            <script>
	                jQuery(document).ready(function($) {
	                    jQuery("#customize").removeClass('disabled');
	                });
	            </script>
	            <?php

			}
		}

		function template_array(){
			$templates = [];
			$templates['full-width-page-template.php'] = __('Product Designer', 'text-domain' );
			return $templates;
		}

		/**
		* Add page templates.
		*
		* @param  array  $templates  The list of page templates
		*
		* @return array  $templates  The modified list of page templates
		*/
		function ink_pd_add_page_template_to_dropdown($page_templates,$theme,$post)
		{
			$temps = $this->template_array();

			foreach($temps as $tk => $tv){
				$page_templates[$tk] = $tv;
			}
		   return $page_templates;
		}

		/**
		 * Change the page template to the selected template on the dropdown
		 * 
		 * @param $template
		 *
		 * @return mixed
		 */
		function ink_pd_change_page_template($template)
		{
		   
	        global $post,$wp,$wp_query,$wpdb;
	        $template_slug = get_page_template_slug(get_the_ID());

	        $templates = $this->template_array();
	        if(isset($templates[$template_slug])){
	        	$template = plugin_dir_path( __FILE__ ) . 'templates/'.$template_slug;
	        }
		    return $template;
		}

		public function ink_pd_template_redirect( ){
			$get_url = explode('/', $_SERVER['REQUEST_URI']);
			$urlLastPart = $get_url[count($get_url) - 1];
			$designerPage = explode('?',$urlLastPart);
			$pageName = $designerPage[0];
		    if ($pageName == $this->custom_path) {
		        global $wp_query;
		        $wp_query->is_404 = false;
		        status_header(200);
		        include(dirname(__FILE__) . '/templates/product-designer.php');
		        exit();
		    }
		    if ( ( function_exists( 'is_product' ) && is_product() ) ) {
				if ( isset( $_REQUEST['tin'] ) && !empty( $_REQUEST['tin'] ) ) {
					$urlParams = "?id=".$_REQUEST['id']."&tin=".$_REQUEST['tin']."&key=".$_REQUEST['key'];				
					$url = get_site_url() . '/' . $this->custom_path . $urlParams;
			        exit( esc_attr( wp_redirect($url) ) );
			    }

			}
		}


	}  // END class.
} // END if class exists

$inkxe_productdesigner_class = new InkXEProductDesignerLite();
