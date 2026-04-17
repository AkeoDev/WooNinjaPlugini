<?php
/*
Plugin Name: WooNinja - LeanPay
Plugin URI: https://wooninja.si
Description: Varno plačilo na obroke preko sistema LeanPay v WooCommerce spletni trgovini.
Version: 1.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-leanpay
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php
				printf(
					/* translators: %s: plugin name */
					esc_html__( 'Vtičnik %s za svoje delovanje potrebuje aktiven vtičnik WooCommerce.', 'wooninja-leanpay' ),
					'<strong>WooNinja - LeanPay</strong>'
				);
			?></p>
		</div>
		<?php
	} );

	return false;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_leanpay_init', 0 );

function woocommerce_gateway_leanpay_init() {

	load_plugin_textdomain( 'wooninja-leanpay', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * Gateway class
	 */
	class LeanPay extends WC_Payment_Gateway {

		function __construct() {
			$this->id                 = 'wc_leanpay';
			$this->method_title       = __( 'LeanPay', 'wooninja-leanpay' );
			$this->method_description = __( 'Plačila z LeanPay', 'wooninja-leanpay' );
			$this->title              = __( 'Plačilo z LeanPay', 'wooninja-leanpay' );
			$this->icon               = plugin_dir_url( __FILE__ ) . 'img/leanpay-logo.png';
			$this->has_fields         = true;
			$this->supports           = array();

			$this->init_form_fields();
			$this->init_settings();

			foreach ( $this->settings as $setting_key => $value ) {
				$this->$setting_key = $value;
			}

			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}
		}

		public function init_form_fields() {
			$statuses = wc_get_order_statuses();

			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Omogoči plačila', 'wooninja-leanpay' ),
					'label'   => __( 'Omogoči', 'wooninja-leanpay' ),
					'type'    => 'checkbox',
					'default' => 'no',
				),
				'title' => array(
					'title'    => __( 'Naziv plačila', 'wooninja-leanpay' ),
					'type'     => 'text',
					'desc_tip' => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wooninja-leanpay' ),
					'default'  => __( 'LeanPay – Hitro plačilo na obroke!', 'wooninja-leanpay' ),
				),
				'description' => array(
					'title'    => __( 'Opis', 'wooninja-leanpay' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wooninja-leanpay' ),
					'default'  => __( 'Varno plačilo na obroke preko sistema LeanPay', 'wooninja-leanpay' ),
					'css'      => 'max-width:350px;',
				),
				'environment' => array(
					'title'       => __( 'Testni način', 'wooninja-leanpay' ),
					'label'       => __( 'Omogoči', 'wooninja-leanpay' ),
					'type'        => 'checkbox',
					'description' => __( 'Plačevanje z LeanPay v testnem načinu.', 'wooninja-leanpay' ),
					'default'     => 'no',
				),
				'test_ips' => array(
					'title'    => __( 'Omejite iz katerih IP naslovov je možen dostop v testnem načinu', 'wooninja-leanpay' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Za več IP-jev lahko ločite z vejico. V kolikor je polje prazno, je testni način dostopen vsem.', 'wooninja-leanpay' ),
					'default'  => '',
					'css'      => 'max-width:350px;',
				),
				'send_details' => array(
					'title'   => __( 'Administratorju pripni podrobnosti o transakciji', 'wooninja-leanpay' ),
					'label'   => __( 'Omogoči', 'wooninja-leanpay' ),
					'type'    => 'checkbox',
					'default' => 'no',
				),
				'failed_transaction' => array(
					'title'       => __( 'Poročaj o neuspelih transakcijah', 'wooninja-leanpay' ),
					'label'       => __( 'Omogoči', 'wooninja-leanpay' ),
					'type'        => 'checkbox',
					'description' => __( 'Administratorju strani bo poslan report o neuspeli transakciji.', 'wooninja-leanpay' ),
					'default'     => 'no',
				),
				'language' => array(
					'title'       => __( 'Jezik nakupne strani', 'wooninja-leanpay' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Jezik v katerem se bo prikazala stran za vpis podatkov kreditne kartice', 'wooninja-leanpay' ),
					'options'     => array(
						'sl' => 'Slovenski',
					),
					'default'     => 'sl',
				),
				'completed_status' => array(
					'title'       => __( 'Status ob uspešnem plačilu', 'wooninja-leanpay' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Status naročila ko je plačilo uspešno', 'wooninja-leanpay' ),
					'default'     => 'wc-completed',
					'options'     => $statuses,
				),
				'failed_status' => array(
					'title'       => __( 'Status ob neuspešnem plačilu', 'wooninja-leanpay' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Status naročila ko je plačilo spodletelo', 'wooninja-leanpay' ),
					'default'     => 'wc-failed',
					'options'     => $statuses,
				),
				'API_id' => array(
					'title'   => __( 'API ID', 'wooninja-leanpay' ),
					'type'    => 'text',
					'default' => '',
				),
				'API_secret' => array(
					'title'   => __( 'API Secret Pass', 'wooninja-leanpay' ),
					'type'    => 'text',
					'default' => '',
				),
			);
		}

		/**
		 * Check if the gateway is available for use.
		 *
		 * @return bool
		 */
		public function is_available() {
			if ( $this->test_ips !== '' && $this->environment === 'yes' ) {
				$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				if ( ! in_array( $remote_addr, array_map( 'trim', explode( ',', $this->test_ips ) ), true ) ) {
					return false;
				}
			}

			return parent::is_available();
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$nonce = wp_create_nonce( 'order_id_' . $order_id );

			$redirect_order_id = $order_id;
			if ( function_exists( 'icl_object_id' ) ) {
				$redirect_order_id .= '&lang=' . ICL_LANGUAGE_CODE;
			}

			return array(
				'result'   => 'success',
				'redirect' => plugin_dir_url( __FILE__ ) . 'predir.php?nonce=' . $nonce . '&orderId=' . $redirect_order_id,
			);
		}

	}

	/**
	 * Add the LeanPay gateway to WooCommerce.
	 */
	function woocommerce_add_gateway_leanpay_gateway( $methods ) {
		$methods[] = 'LeanPay';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_leanpay_gateway' );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_leanpay_action_links' );
function woocommerce_gateway_leanpay_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=leanpay' ) ) . '">' . esc_html__( 'Nastavitve plačevanja z LeanPay', 'wooninja-leanpay' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

function load_leanpay_wp_admin_style() {
	wp_register_style( 'leanpay_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
	wp_enqueue_style( 'leanpay_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_leanpay_wp_admin_style' );

add_action( 'admin_init', 'leanpay_load_admin_hooks' );

/**
 * Load the admin hooks.
 */
function leanpay_load_admin_hooks() {
	add_action( 'add_meta_boxes_shop_order', 'leanpay_meta_box' );
	add_action( 'add_meta_boxes_woocommerce_page_wc-orders', 'leanpay_meta_box' );
}

/**
 * Add the meta box on the single order page.
 */
function leanpay_meta_box() {
	$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';
	add_meta_box( 'leanpay-box', __( 'LeanPay – Hitro plačilo na obroke', 'wooninja-leanpay' ), 'leanpay_box_content', $screen, 'side', 'high' );
}

/**
 * Create the meta box content on the single order page.
 */
function leanpay_box_content() {
	global $post_id, $theorder;

	if ( $theorder instanceof WC_Order ) {
		$order = $theorder;
	} else {
		$order = wc_get_order( $post_id );
	}

	if ( ! $order ) {
		echo '<small>' . esc_html__( 'Order not found.', 'wooninja-leanpay' ) . '</small>';
		return;
	}

	$method = $order->get_payment_method();

	if ( $method !== 'wc_leanpay' ) {
		echo '<small>' . esc_html__( 'This order was not processed via LeanPay.', 'wooninja-leanpay' ) . '</small>';
		return;
	}

	$leanpay_status = $order->get_meta( 'leanpay_order_status' );
	if ( ! empty( $leanpay_status ) ) {
		echo "<ul class='order_notes leanpay'><li class='note system-note'><div class='note_content' style='background:linear-gradient(90deg, #ff6a71, #ffa667) !important;'>";
		echo '<p style="font-weight:600;color:#fff;">' . esc_html( $leanpay_status ) . '</p>';
		echo '</div></li></ul>';
		echo '<style>ul.order_notes.leanpay li.system-note .note_content::after{border-color:#ff6a71 transparent;}</style>';
	}
}

add_filter( 'woocommerce_available_payment_gateways', 'change_payment_gateway', 20, 1 );

/**
 * Remove LeanPay gateway if cart total is outside the allowed range.
 *
 * @param array $gateways
 * @return array
 */
function change_payment_gateway( $gateways ) {
	if ( WC()->cart->total < 200 || WC()->cart->total > 3000 ) {
		unset( $gateways['wc_leanpay'] );
	}
	return $gateways;
}
