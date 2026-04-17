<?php
/*
Plugin Name: WooNinja - NestPay Diners
Plugin URI: https://wooninja.si
Description: Možnost plačila z Diners preko NestPay sistema v WooCommerce spletni trgovini.
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-nestpay-diners
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php
				printf(
					/* translators: %s: plugin name */
					esc_html__( 'Vtičnik %s za svoje delovanje potrebuje aktiven vtičnik WooCommerce.', 'wooninja-nestpay-diners' ),
					'<strong>' . esc_html( 'WooNinja - NestPay Diners' ) . '</strong>'
				);
			?></p>
		</div>
		<?php
	} );

	return false;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_nestpay_diners_init', 0 );

function woocommerce_gateway_nestpay_diners_init() {

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wooninja-nestpay-diners', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * Gateway class
	 */
	class NestPay_diners extends WC_Payment_Gateway {

		// Setup our Gateway's id, description and other values
		function __construct() {

			$this->id               = 'wc_nestpay_diners';
			$this->method_title     = __( 'NestPay DINERS', 'wooninja-nestpay-diners' );
			$this->method_description = __( 'Plačila z NestPay DINERS', 'wooninja-nestpay-diners' );
			$this->title            = __( 'Plačilo z NestPay DINERS', 'wooninja-nestpay-diners' );
			$this->icon             = plugin_dir_url( __FILE__ ) . 'img/dinerslogo.png';
			$this->has_fields       = true;
			$this->supports         = array();

			$this->init_form_fields();
			$this->init_settings();

			// Turn these settings into variables we can use
			foreach ( $this->settings as $setting_key => $value ) {
				$this->$setting_key = $value;
			}

			// Save settings
			if ( is_admin() ) {
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			}
		}

		// Build the administration fields for this specific Gateway
		public function init_form_fields() {
			$statuses = wc_get_order_statuses();

			$this->form_fields = array(
				'enabled' => array(
					'title'   => __( 'Omogoči plačila', 'wooninja-nestpay-diners' ),
					'label'   => __( 'Omogoči', 'wooninja-nestpay-diners' ),
					'type'    => 'checkbox',
					'default' => 'no',
				),
				'title' => array(
					'title'    => __( 'Naziv plačila', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wooninja-nestpay-diners' ),
					'default'  => __( 'Kreditne kartice (Diners)', 'wooninja-nestpay-diners' ),
				),
				'description' => array(
					'title'    => __( 'Opis', 'wooninja-nestpay-diners' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wooninja-nestpay-diners' ),
					'default'  => __( 'Varno plačilo z vašo kreditno kartico (Diners).', 'wooninja-nestpay-diners' ),
					'css'      => 'max-width:350px;',
				),
				'environment' => array(
					'title'       => __( 'Testni način', 'wooninja-nestpay-diners' ),
					'label'       => __( 'Omogoči', 'wooninja-nestpay-diners' ),
					'type'        => 'checkbox',
					'description' => __( 'Plačevanje z NestPay Diners v testnem načinu.', 'wooninja-nestpay-diners' ),
					'default'     => 'no',
				),
				'test_ips' => array(
					'title'    => __( 'Omejite iz katerih IP naslovov je možen dostop v testnem načinu', 'wooninja-nestpay-diners' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Za več IP-jev lahko ločite z vejico. V kolikor je polje prazno, je testni način dostopen vsem.', 'wooninja-nestpay-diners' ),
					'default'  => '',
					'css'      => 'max-width:350px;',
				),
				'send_details' => array(
					'title'       => __( 'Administratorju pripni podrobnosti o transakciji', 'wooninja-nestpay-diners' ),
					'label'       => __( 'Omogoči', 'wooninja-nestpay-diners' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
				'failed_transaction' => array(
					'title'       => __( 'Poročaj o neuspelih transakcijah', 'wooninja-nestpay-diners' ),
					'label'       => __( 'Omogoči', 'wooninja-nestpay-diners' ),
					'type'        => 'checkbox',
					'description' => __( 'Administratorju strani bo poslan report o neuspeli transakciji.', 'wooninja-nestpay-diners' ),
					'default'     => 'no',
				),
				'language' => array(
					'title'       => __( 'Jezik nakupne strani', 'wooninja-nestpay-diners' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Jezik v katerem se bo prikazala stran za vpis podatkov kreditne kartice', 'wooninja-nestpay-diners' ),
					'options'     => array(
						'sl' => 'Slovenski',
						'en' => 'Angleški',
					),
					'default'     => 'sl',
				),
				'transaction_type' => array(
					'title'       => __( 'Tip transakcije', 'wooninja-nestpay-diners' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						'Auth'    => 'Auth',
						'PreAuth' => 'PreAuth',
					),
					'default'     => 'Auth',
					'description' => __(
						'"PreAuth" je primerno za naročanje fizičnih artiklov, ki jih pošljete kupcu na dom. <br>
						"PreAuth" transakcije je potrebno ročno potrditi v administraciji NestPay back-end portala, ko izdelek pošljete stranki. Če uporabljate NestPay API podatke, lahko PreAuth naročilo ročno potrdite tudi v administraciji posameznega naročila. <br>
						"Auth" je primeren za takojšnje plačilo npr. plačilo novice, ki jo lahko takoj prebereš - digitalni produkti.',
						'wooninja-nestpay-diners'
					),
				),
				'completed_status' => array(
					'title'       => __( 'Status ob uspešnem plačilu', 'wooninja-nestpay-diners' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Status naročila ko je plačilo uspešno', 'wooninja-nestpay-diners' ),
					'default'     => 'wc-completed',
					'options'     => $statuses,
				),
				'failed_status' => array(
					'title'       => __( 'Status ob neuspešnem plačilu', 'wooninja-nestpay-diners' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Status naročila ko je plačilo spodletelo', 'wooninja-nestpay-diners' ),
					'default'     => 'wc-failed',
					'options'     => $statuses,
				),
				'test_enviroment' => array(
					'title'       => __( 'Testni podatki NestPay DINERS', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'urlforpaymenttest' => array(
					'title'    => __( 'URL *', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => 'https://testsecurepay.eway2pay.com/fim/est3Dgate',
				),
				'TranPortalID_test' => array(
					'title'    => __( 'Merchant ID *', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'ID vašega NestPay Diners računa (ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '0',
				),
				'TranPortalPWD_test' => array(
					'title'    => __( 'StoreKey *', 'wooninja-nestpay-diners' ),
					'type'     => 'password',
					'desc_tip' => __( 'StoreKey vašega NestPay Diners računa (ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '',
				),
				'api_explain_test' => array(
					'title'       => __( 'Dodatni podatki, ki omogočijo opravljanje transakcij v pogledu posameznega naročila<br>znotraj Woocommerce platforme, namesto v administraciji NestPay Diners back-end portala:', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'PaymentInitAPI_test' => array(
					'title'    => __( 'API URL', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '',
				),
				'API_id_test' => array(
					'title'   => __( 'API ID', 'wooninja-nestpay-diners' ),
					'type'    => 'text',
					'default' => '',
				),
				'API_pass_test' => array(
					'title'   => __( 'API Geslo', 'wooninja-nestpay-diners' ),
					'type'    => 'password',
					'default' => '',
				),
				'production_enviroment' => array(
					'title'       => __( 'Produkcijski podatki NestPay Diners', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'urlforpaymentproduction' => array(
					'title'    => __( 'URL *', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '',
				),
				'TranPortalID' => array(
					'title'    => __( 'Merchant ID *', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'ID vašega NestPay računa (ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '0',
				),
				'TranPortalPWD' => array(
					'title'    => __( 'StoreKey *', 'wooninja-nestpay-diners' ),
					'type'     => 'password',
					'desc_tip' => __( 'StoreKey vašega NestPay računa (ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '',
				),
				'api_explain_prod' => array(
					'title'       => __( 'Dodatni podatki, ki omogočijo opravljanje transakcij v pogledu posameznega naročila<br>znotraj Woocommerce platforme, namesto v administraciji NestPay back-end portala:', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'PaymentInitAPI' => array(
					'title'    => __( 'API URL', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od NestPay)', 'wooninja-nestpay-diners' ),
					'default'  => '',
				),
				'API_id' => array(
					'title'   => __( 'API ID', 'wooninja-nestpay-diners' ),
					'type'    => 'text',
					'default' => '',
				),
				'API_pass' => array(
					'title'   => __( 'API Geslo', 'wooninja-nestpay-diners' ),
					'type'    => 'password',
					'default' => '',
				),
				'oblika' => array(
					'title'       => __( 'Oblikovanje plačilne strani', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'logo' => array(
					'title'    => __( 'Logo', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'Slika ki se pokaže ob preusmeritvi na plačilno stran', 'wooninja-nestpay-diners' ),
					'default'  => 'http://placehold.it/450x150?text=Vaš+logotip',
				),
				'background' => array(
					'title'    => __( 'Barva ozadja', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'Barva ozadja ki se pokaže ob preusmeritvi na plačilno stran', 'wooninja-nestpay-diners' ),
					'default'  => '#FFFFFF',
				),
				'text_color' => array(
					'title'    => __( 'Barva besedila', 'wooninja-nestpay-diners' ),
					'type'     => 'text',
					'desc_tip' => __( 'Barva besedila ki se pokaže ob preusmeritvi na plačilno stran', 'wooninja-nestpay-diners' ),
					'default'  => '#000000',
				),
				'custom_del' => array(
					'title'       => __( 'Tečaj', 'wooninja-nestpay-diners' ),
					'type'        => 'title',
					'description' => '',
				),
				'tecaj' => array(
					'title'   => __( 'HRK/EUR tečaj (xx.xx)', 'wooninja-nestpay-diners' ),
					'type'    => 'text',
					'default' => '7.59',
				),
			);
		}


		/**
		 * Check If The Gateway Is Available For Use
		 *
		 * @return bool
		 */
		public function is_available() {
			if ( $this->environment == 'yes' && $this->test_ips != '' ) {
				$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				if ( ! in_array( $remote_addr, explode( ',', $this->test_ips ), true ) ) {
					return false;
				}
			}

			return parent::is_available();
		}

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */
		function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$nonce = wp_create_nonce( 'order_id_' . $order_id );

			$redirect_url = plugin_dir_url( __FILE__ ) . 'predir.php?nonce=' . $nonce . '&orderId=' . $order_id;

			if ( function_exists( 'icl_object_id' ) ) {
				$redirect_url .= '&lang=' . ICL_LANGUAGE_CODE;
			}

			return array(
				'result'   => 'success',
				'redirect' => $redirect_url,
			);
		}
	}

	/**
	 * Add the NestPay Diners gateway to WooCommerce
	 */
	function woocommerce_add_gateway_nestpay_diners_gateway( $methods ) {
		$methods[] = 'NestPay_diners';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_nestpay_diners_gateway' );
}


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_nestypay_diners_action_links' );
function woocommerce_gateway_nestypay_diners_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=nestpay_diners' ) ) . '">' . esc_html__( 'Nastavitve plačevanja z NestPay [DINERS]', 'wooninja-nestpay-diners' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}


function load_nestpay_diners_wp_admin_style() {
	wp_register_style( 'nestpay_diners_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
	wp_enqueue_style( 'nestpay_diners_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_nestpay_diners_wp_admin_style' );

if ( ! in_array( 'woo-nestpay/woo-nestpay.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	function nestpay_diners_verfied() {
		?>
		<br>
		<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'img/visa_verified-master_secure_code.png' ); ?>" alt="">
		<br>
		<?php
	}

	add_action( 'woocommerce_after_checkout_form', 'nestpay_diners_verfied' );
	add_action( 'woocommerce_after_cart_table', 'nestpay_diners_verfied' );
}


//// Transaction history
add_action( 'admin_init', 'nestpay_diners_load_admin_hooks' );

/**
 * Load the admin hooks
 */
function nestpay_diners_load_admin_hooks() {
	add_action( 'add_meta_boxes_shop_order', 'nestpay_diners_meta_box' );
	add_action( 'add_meta_boxes_woocommerce_page_wc-orders', 'nestpay_diners_meta_box' );
}


/**
 * Add the meta box on the single order page
 */
function nestpay_diners_meta_box() {
	$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
		? wc_get_page_screen_id( 'shop-order' )
		: 'shop_order';
	add_meta_box( 'nestpay_diners-box', __( 'Diners (NestPay)', 'wooninja-nestpay-diners' ), 'nestpay_diners_box_content', $screen, 'side', 'high' );
}

/**
 * Create the meta box content on the single order page
 */
function nestpay_diners_box_content() {
	global $post_id, $theorder;

	if ( $theorder instanceof WC_Order ) {
		$order = $theorder;
	} else {
		$order = wc_get_order( $post_id );
	}
	$settings = new NestPay_diners();

	$method = $order ? $order->get_payment_method() : '';

	if ( $method != 'wc_nestpay_diners' ) {
		echo '<small>' . esc_html__( 'This order was not processed via NestPay Diners.', 'wooninja-nestpay-diners' ) . '</small>';
		return;
	}

	if ( $settings->environment == 'yes' ) {
		$clientid = $settings->TranPortalID_test;
		$name     = $settings->API_id_test;
		$password = $settings->API_pass_test;
		$url      = $settings->PaymentInitAPI_test;
	} else {
		$clientid = $settings->TranPortalID;
		$name     = $settings->API_id;
		$password = $settings->API_pass;
		$url      = $settings->PaymentInitAPI;
	}

	if ( $url == '' ) {
		return;
	}

	$oid = $order->get_id();

	$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
	<CC5Request>
	<Name>{NAME}</Name>
	<Password>{PASSWORD}</Password>
	<ClientId>{CLIENTID}</ClientId>
	<OrderId>{OID}</OrderId>
	<Mode>P</Mode>
	<Extra><ORDERHISTORY>QUERY</ORDERHISTORY></Extra>
	</CC5Request>";

	$request = str_replace( '{NAME}', $name, $request );
	$request = str_replace( '{PASSWORD}', $password, $request );
	$request = str_replace( '{CLIENTID}', $clientid, $request );
	$request = str_replace( '{OID}', $oid, $request );

	$response = wp_remote_post( $url, array(
		'timeout'   => 90,
		'sslverify' => true,
		'body'      => $request,
	) );

	if ( is_wp_error( $response ) ) {
		return;
	}

	$result = wp_remote_retrieve_body( $response );
	$xml    = simplexml_load_string( $result );

	if ( ! $xml ) {
		return;
	}

	$extra = isset( $xml->Extra->TRX1 ) ? (string) $xml->Extra->TRX1 : '';
	$extra = preg_split( '/\s+/', $extra );

	$xml_response = isset( $xml->Response ) ? (string) $xml->Response : '';
	$extra_0      = isset( $extra[0] ) ? $extra[0] : '';
	$extra_1      = isset( $extra[1] ) ? $extra[1] : '';

	$nonce = wp_create_nonce( 'nestpay_diners_transaction' );

	?>

	<div class="print-actions nestpay-spletni-moduli">

		<?php if ( isset( $xml->ErrMsg ) && '' != (string) $xml->ErrMsg ) { ?>
			<span style="color: red;">
				<?php echo esc_html( $xml->ErrMsg ); ?> - ProcReturnCode: <?php echo esc_html( $xml->ProcReturnCode ); ?>
			</span>
			<hr>
		<?php } ?>

		<?php echo esc_html__( 'Transaction status:', 'wooninja-nestpay-diners' ); ?>
		<br>

		<strong>
		<?php
		if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'D' ) {
			esc_html_e( 'Unsuccessful transaction', 'wooninja-nestpay-diners' );
		}

		if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'A' ) {
			esc_html_e( 'Successful transaction (preauthorization)', 'wooninja-nestpay-diners' );
		}

		if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'C' ) {
			esc_html_e( 'Successful transaction', 'wooninja-nestpay-diners' );
		}

		if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'S' ) {
			esc_html_e( 'Settled transaction', 'wooninja-nestpay-diners' );
		}

		if ( $xml_response == 'Approved' && $extra_0 == 'C' && $extra_1 == 'C' ) {
			esc_html_e( 'Payment refunded', 'wooninja-nestpay-diners' );
		}
		?>
		</strong>

		<hr>

		<?php if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'A' ) { ?>
			<a href="" data-order-id="<?php echo esc_attr( $oid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="button js-confirm-transaction"><?php esc_html_e( 'Potrdi transakcijo (Confirm)', 'wooninja-nestpay-diners' ); ?></a>
			<a href="" data-order-id="<?php echo esc_attr( $oid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="button js-refund-transaction"><?php esc_html_e( 'Vrni transakcijo (Refund)', 'wooninja-nestpay-diners' ); ?></a>
		<?php } ?>

		<?php if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'S' ) { ?>
			<a href="" data-order-id="<?php echo esc_attr( $oid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="button js-refund-transaction"><?php esc_html_e( 'Vrni transakcijo (Refund)', 'wooninja-nestpay-diners' ); ?></a>
		<?php } ?>

		<?php if ( $xml_response == 'Approved' && $extra_0 == 'S' && $extra_1 == 'C' ) { ?>
			<a href="" data-order-id="<?php echo esc_attr( $oid ); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>" class="button js-refund-transaction"><?php esc_html_e( 'Vrni transakcijo (Refund)', 'wooninja-nestpay-diners' ); ?></a>
		<?php } ?>

		<br>

		<small>
			<?php esc_html_e( 'Akcije Partial refund (delno vračilo) in Partial confirm (delna potrditev) je možno urediti le znotraj administracije NestPay back-end portala.', 'wooninja-nestpay-diners' ); ?>
		</small>

	</div>

	<script>
		jQuery(".js-refund-transaction").click( function(e) {
			e.preventDefault();

			var order_id = jQuery(this).attr("data-order-id");
			var nonce = jQuery(this).attr("data-nonce");
			var data = {
				'action': 'nestpay_diners_refund_transaction',
				'order_id': order_id,
				'_wpnonce': nonce
			};

			jQuery.post(ajaxurl, data, function(response) {
				location.reload();
			});
		});

		jQuery(".js-confirm-transaction").click( function(e) {
			e.preventDefault();

			var order_id = jQuery(this).attr("data-order-id");
			var nonce = jQuery(this).attr("data-nonce");
			var data = {
				'action': 'nestpay_diners_confirm_transaction',
				'order_id': order_id,
				'_wpnonce': nonce
			};

			jQuery.post(ajaxurl, data, function(response) {
				location.reload();
			});
		});
	</script>

	<?php
}

add_action( 'wp_ajax_nestpay_diners_refund_transaction', 'nestpay_diners_refund_transaction' );
function nestpay_diners_refund_transaction() {

	check_ajax_referer( 'nestpay_diners_transaction' );

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( -1, 403 );
	}

	$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
	$oid      = $order_id;

	$settings = new NestPay_diners();

	if ( $settings->environment == 'yes' ) {
		$clientid = $settings->TranPortalID_test;
		$name     = $settings->API_id_test;
		$password = $settings->API_pass_test;
		$url      = $settings->PaymentInitAPI_test;
	} else {
		$clientid = $settings->TranPortalID;
		$name     = $settings->API_id;
		$password = $settings->API_pass;
		$url      = $settings->PaymentInitAPI;
	}

	if ( $url == '' ) {
		wp_die();
	}

	$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
	<CC5Request>
	<Name>{NAME}</Name>
	<Password>{PASSWORD}</Password>
	<ClientId>{CLIENTID}</ClientId>
	<OrderId>{OID}</OrderId>
	<Type>Credit</Type>
	</CC5Request>";

	$request = str_replace( '{NAME}', $name, $request );
	$request = str_replace( '{PASSWORD}', $password, $request );
	$request = str_replace( '{CLIENTID}', $clientid, $request );
	$request = str_replace( '{OID}', $oid, $request );

	$response = wp_remote_post( $url, array(
		'timeout'   => 90,
		'sslverify' => true,
		'body'      => $request,
	) );

	wp_die();
}


add_action( 'wp_ajax_nestpay_diners_confirm_transaction', 'nestpay_diners_confirm_transaction' );
function nestpay_diners_confirm_transaction() {

	check_ajax_referer( 'nestpay_diners_transaction' );

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_die( -1, 403 );
	}

	$order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
	$oid      = $order_id;

	$settings = new NestPay_diners();

	if ( $settings->environment == 'yes' ) {
		$clientid = $settings->TranPortalID_test;
		$name     = $settings->API_id_test;
		$password = $settings->API_pass_test;
		$url      = $settings->PaymentInitAPI_test;
	} else {
		$clientid = $settings->TranPortalID;
		$name     = $settings->API_id;
		$password = $settings->API_pass;
		$url      = $settings->PaymentInitAPI;
	}

	if ( $url == '' ) {
		wp_die();
	}

	$request = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
	<CC5Request>
	<Name>{NAME}</Name>
	<Password>{PASSWORD}</Password>
	<ClientId>{CLIENTID}</ClientId>
	<OrderId>{OID}</OrderId>
	<Type>PostAuth</Type>
	</CC5Request>";

	$request = str_replace( '{NAME}', $name, $request );
	$request = str_replace( '{PASSWORD}', $password, $request );
	$request = str_replace( '{CLIENTID}', $clientid, $request );
	$request = str_replace( '{OID}', $oid, $request );

	$response = wp_remote_post( $url, array(
		'timeout'   => 90,
		'sslverify' => true,
		'body'      => $request,
	) );

	wp_die();
}
