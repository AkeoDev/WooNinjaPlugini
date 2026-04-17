<?php
/*
Plugin Name: WooNinja - SMS API
Plugin URI: https://wooninja.si
Description: Pošiljanje SMS sporočil preko SMSapi.si
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-smsapi
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( version_compare( get_option( 'woocommerce_db_version', '0' ), '3', '<' ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Žal uporabljate starejšo verzijo WooCommerce kot jo podpira SMSApi modul. Prosimo nadgradite WooCommerce.', 'wooninja-smsapi' ); ?></p>
		</div>
		<?php
	} );

	return false;
}

if ( ! class_exists( 'SMSApiAdmin' ) ) {

	class SMSApiAdmin {

		public function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_menu', array( $this, 'admin_menu' ), 9 );
				add_action( 'admin_enqueue_scripts', array( $this, 'load_script' ) );
				add_action( 'wp_ajax_sms_api_send', array( $this, 'sms_api_send' ) );
			}
		}

		public static function load_script() {
			wp_enqueue_style( 'sweetalerts2', plugins_url( 'inc/css/sweetalert2.css', __FILE__ ) );

			wp_enqueue_script(
				'sweetalert2',
				plugins_url( 'inc/js/sweetalert2.js', __FILE__ ),
				array( 'jquery', 'common' ),
				false,
				true
			);
		}

		public function admin_menu() {
			add_menu_page(
				__( 'SMSApi', 'wooninja-smsapi' ),
				__( 'SMSApi', 'wooninja-smsapi' ),
				'manage_options',
				'smsapi',
				array( $this, 'settings' ),
				'dashicons-feedback'
			);

			add_submenu_page(
				'smsapi',
				__( 'Nastavitve', 'wooninja-smsapi' ),
				__( 'Nastavitve', 'wooninja-smsapi' ),
				'manage_options',
				'smsapi',
				array( $this, 'settings' )
			);

			add_submenu_page(
				'smsapi',
				__( 'Pošlji sporočilo', 'wooninja-smsapi' ),
				__( 'Pošlji sporočilo', 'wooninja-smsapi' ),
				'manage_options',
				'smsapi_send',
				array( $this, 'send_sms' )
			);
		}

		public function settings() {
			include dirname( __FILE__ ) . '/admin/settings.php';
		}

		public function send_sms() {
			include dirname( __FILE__ ) . '/admin/send_sms.php';
		}

		public function sms_api_send() {
			check_ajax_referer( 'smsapi_send_nonce', '_nonce' );

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( array( 'error' => 'Unauthorized' ) );
			}

			$api = new SMSApi();

			if ( ! isset( $_POST['sendAs'] ) ) {
				$sendAs = get_option( 'smsapi_default', 'phone' );
			} else {
				$sendAs = sanitize_text_field( wp_unslash( $_POST['sendAs'] ) );
			}

			$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
			$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

			$response = $api->sendSms( $phone, $message, $sendAs );

			$return = array();

			if ( $response[0] == '-1' ) {
				$return['status'] = 'error';
				$return['error']  = $response[2];
			} else {
				$return['status'] = 'success';
			}

			wp_send_json( $return );
		}

	}

	class SMSApi {

		private $api_username;
		private $api_pass;
		private $sender;

		public function __construct() {
			$this->api_username = get_option( 'smsapi_api-uporabniško-ime', '' );
			$this->api_pass     = get_option( 'smsapi_api-geslo', '' );
			$this->sender       = get_option( 'smsapi_telefon-pošiljatelja', '' );
		}

		public function getCredits() {
			$response = wp_remote_get( 'https://www.smsapi.si/preveri-stanje-kreditov?un=' . urlencode( $this->api_username ) . '&ps=' . urlencode( $this->api_pass ) );
			if ( is_wp_error( $response ) ) {
				return '0';
			}
			return wp_remote_retrieve_body( $response );
		}

		public function sendSms( $phone, $message, $sendAs = '' ) {

			if ( '' === $sendAs ) {
				$sendAs = get_option( 'smsapi_default', 'phone' );
			}

			$url  = 'https://www.smsapi.si/poslji-sms';
			$data = array(
				'un'   => $this->api_username,
				'ps'   => $this->api_pass,
				'from' => $this->sender,
				'to'   => $phone,
				'm'    => $message,
				'cc'   => '386',
			);

			if ( 'id' === $sendAs ) {
				$data['sid']   = '1';
				$data['sname'] = get_option( 'smsapi_id-pošiljatelja', '' );
			}

			$response = wp_remote_post( $url, array(
				'timeout'   => 15,
				'sslverify' => true,
				'body'      => $data,
			) );

			if ( is_wp_error( $response ) ) {
				return array( '-1', '', 'HTTP request failed' );
			}

			$body = wp_remote_retrieve_body( $response );
			return explode( '#', $body );
		}

	}

	abstract class SMSApi_Meta_Box {

		public static function add() {
			$screens = array( 'shop_order' );
			foreach ( $screens as $screen ) {
				add_meta_box(
					'smsapi_meta_box',
					__( 'Pošlji SMS stranki', 'wooninja-smsapi' ),
					array( self::class, 'html' ),
					$screen,
					'side'
				);
			}
		}

		public static function html( $post ) {

			$order = wc_get_order( $post->ID );
			if ( ! $order ) {
				echo '<p>' . esc_html__( 'Naročilo ni bilo najdeno.', 'wooninja-smsapi' ) . '</p>';
				return;
			}

			$default = get_option( 'smsapi_default', 'phone' );
			$nonce   = wp_create_nonce( 'smsapi_send_nonce' );
			?>

			<?php esc_html_e( 'Pošlji kot:', 'wooninja-smsapi' ); ?> <br>
			<select name="smsapi_default" class="smsapi-default" style="width:100%;">
				<option value="phone" <?php selected( $default, 'phone' ); ?>><?php esc_html_e( 'Telefon pošiljatelja', 'wooninja-smsapi' ); ?></option>
				<option value="id" <?php selected( $default, 'id' ); ?>><?php esc_html_e( 'ID pošiljatelja', 'wooninja-smsapi' ); ?></option>
			</select>

			<br>

			<?php esc_html_e( 'Telefonska št:', 'wooninja-smsapi' ); ?> <br>
			<input type="text" style="width:100%;" class="smsapi-phone-number" value="<?php echo esc_attr( $order->get_billing_phone() ); ?>">

			<?php esc_html_e( 'Vsebina SMS:', 'wooninja-smsapi' ); ?>
			<textarea placeholder="" class="smsapi-phone-message" name="" id="" style="width:100%;height: 100px;"></textarea>

			<a href="#" class="send-to-sms-api button" data-order-id="<?php echo esc_attr( $post->ID ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" style="width: 100%;text-align:center;">
				<?php esc_html_e( 'Pošlji SMS', 'wooninja-smsapi' ); ?>
			</a>

			<div class="ajax-loader-smsapi" style="text-align:center; display:none;">
				<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'inc/img/ajax-loader.gif' ); ?>" alt="">
			</div>

			<script>
			jQuery(document).ready(function($) {

				$(".send-to-sms-api").on("click", function(e) {
					e.preventDefault();

					$(this).hide();
					$(".ajax-loader-smsapi").show();

					var $btn = $(this);
					var data = {
						'action': 'sms_api_send',
						'_nonce': $btn.data("nonce"),
						'order_id': $btn.data("order-id"),
						'sendAs': $(".smsapi-default").val(),
						'phone': $(".smsapi-phone-number").val(),
						'message': $(".smsapi-phone-message").val()
					};

					jQuery.post(ajaxurl, data, function(response) {
						if (response.status == "success") {
							swal('Bravo...', 'SMS sporočilo je bilo poslano.', 'success')
							$(".smsapi-phone-message").val("");
							$(".ajax-loader-smsapi").hide();
							$(".send-to-sms-api").show();
						} else {
							swal('Ups...', 'Prišlo je do napake pri pošiljanju SMS sporočila.<br>(Št. napake: '+response.error+' )', 'error')
							$(".send-to-sms-api").show();
							$(".ajax-loader-smsapi").hide();
						}
					});
				});

			});
			</script>

			<?php
		}
	}

	add_action( 'add_meta_boxes', array( 'SMSApi_Meta_Box', 'add' ) );

	new SMSApiAdmin();

}