<?php
/**
 * Plugin Name: WooNinja - Bankart
 * Plugin URI: https://wooninja.si
 * Description: Možnost plačila preko Bankart gatewaya v WooCommerce
 * Version: 3.0.0
 * Author: Humanfrog d.o.o.
 * Author URI: https://wooninja.si
 * Text Domain: wooninja-kreditnekartice
 *
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include plugin_dir_path( __FILE__ ) . '/wooninja/functions.php';

if ( kreditnekartice_iswc_active() ) {

	add_action( 'plugins_loaded', 'woocommerce_gateway_bankart_init', 0 );

} else {
	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong>WooNinja - Bankart</strong> - Plugin ne deluje brez Woocommerce</p>
		</div>
		<?php
	} );
	return false;
}

function woocommerce_gateway_bankart_init() {

	/**
	 * Gateway class
	 */
	class Bankart extends WC_Payment_Gateway {

		/**
		 * Setup our Gateway's id, description and other values.
		 */
		function __construct() {

			$this->id                 = "wc_bankart";
			$this->method_title       = __( "Bankart", 'wooninja-kreditnekartice' );
			$this->method_description = __( "Plačila z Bankart", 'wooninja-kreditnekartice' );
			$this->title              = __( "Plačilo z Bankart", 'wooninja-kreditnekartice' );
			$this->icon               = plugin_dir_url( __FILE__ ) . "img/visa-cards-checkout.png";
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

		/**
		 * Build the administration fields for this specific Gateway.
		 */
		public function init_form_fields() {
			$statuses = wc_get_order_statuses();

			$this->form_fields = array(

				'language_plugin' => array(
					'title'   => __( 'Language of the plugin', 'wooninja-kreditnekartice' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => array(
						"auto"  => "Automatic (WPML, WordPress Locale,..)",
						"sl_SI" => "Slovenian",
						"mk_MK" => "Macedonian",
						"en"    => "English",
					),
					'default' => "auto",
				),

				'enabled' => array(
					'title'   => __( 'Omogoči plačila', 'wooninja-kreditnekartice' ),
					'label'   => __( 'Omogoči', 'wooninja-kreditnekartice' ),
					'type'    => 'checkbox',
					'default' => 'no',
				),
				'title' => array(
					'title'    => __( 'Naziv plačila', 'wooninja-kreditnekartice' ),
					'type'     => 'text',
					'desc_tip' => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wooninja-kreditnekartice' ),
					'default'  => __( 'Kreditne kartice', 'wooninja-kreditnekartice' ),
				),
				'description' => array(
					'title'    => __( 'Opis', 'wooninja-kreditnekartice' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wooninja-kreditnekartice' ),
					'default'  => __( 'Varno plačilo z vašo kreditno kartico (Activa, Maestro, MasterCard, Visa in Visa Electron).', 'wooninja-kreditnekartice' ),
					'css'      => 'max-width:350px;',
				),

				'environment' => array(
					'title'       => __( 'Testni način', 'wooninja-kreditnekartice' ),
					'label'       => __( 'Omogoči', 'wooninja-kreditnekartice' ),
					'type'        => 'checkbox',
					'description' => __( 'Plačevanje z Bankart v testnem načinu.', 'wooninja-kreditnekartice' ),
					'default'     => 'yes',
				),

				'failed_transaction' => array(
					'title'       => __( 'Poročaj o neuspelih transakcijah', 'wooninja-kreditnekartice' ),
					'label'       => __( 'Omogoči', 'wooninja-kreditnekartice' ),
					'type'        => 'checkbox',
					'description' => __( 'Administratorju strani bo poslan report o neuspeli transakciji.', 'wooninja-kreditnekartice' ),
					'default'     => 'no',
				),
				'test_ips' => array(
					'title'    => __( 'Omejite iz katerih IP naslovov je možen dostop v testnem načinu', 'wooninja-kreditnekartice' ),
					'type'     => 'textarea',
					'desc_tip' => __( 'Za več IP-jev lahko ločite z vejico.V kolikor je polje prazno, je testni način dostopen vsem.', 'wooninja-kreditnekartice' ),
					'default'  => '',
					'css'      => 'max-width:350px;',
				),
				'language' => array(
					'title'       => __( 'Jezik nakupne strani', 'wooninja-kreditnekartice' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Jezik v katerem se bo prikazala stran za vpis podatkov kreditne kartice', 'wooninja-kreditnekartice' ),
					'options'     => array(
						"sl" => "Slovenian",
						"en" => "English",
						"de" => "German",
						"me" => "Montenegrin",
						"mk" => "Macedonian",
					),
					'default'     => "sl",
				),
				'valuta' => array(
					'title'       => __( 'Valuta trgovine', 'wooninja-kreditnekartice' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Koda valute ki se pošilja na Bankart', 'wooninja-kreditnekartice' ),
					'options'     => array(
						"EUR" => "EUR",
						"HRK" => "HRK",
						"USD" => "USD",
						"MKD" => "MKD",
					),
					'default'     => "EUR",
				),

				'Transakcija' => array(
					'title'       => __( 'Vrsta transakcije (action)', 'wooninja-kreditnekartice' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'options'     => array(
						"1" => "Purchase",
						"4" => "Authorization",
					),
					'default'     => "4",
					'description' => __( '
						"Authorization" je primerno za naročanje fizičnih artiklov, ki jih pošljete kupcu na dom. <br>
						"Purchase" je primeren za takojšnje plačilo npr. plačilo novice, ki jo lahko takoj prebereš - digitalni produkti.<br>
						"Purchase" transakcije je potrebno ročno potrditi v administraciji Bankart back-end portala, ko izdelek pošljete stranki.', 'wooninja-kreditnekartice' ),
				),
				'obroki' => array(
					'title'       => __( 'Plačevanje z obroki', 'wooninja-kreditnekartice' ),
					'label'       => __( 'Omogoči', 'wooninja-kreditnekartice' ),
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),

				'obroki_stevilo' => array(
					'title'       => __( 'Število obrokov', 'wooninja-kreditnekartice' ),
					'type'        => 'select',
					'class'       => 'wc-enhanced-select',
					'description' => __( 'Število obrokov, ki jih omogočate strankam. POZOR to mora biti dogovorjeno z banko.', 'wooninja-kreditnekartice' ),
					'options'     => array(
						"3"  => "3",
						"6"  => "6",
						"12" => "12",
						"24" => "24",
					),
					'default'     => "12",
				),

				'status_success' => array(
					'title'   => __( 'Status ob uspeli transakciji', 'wooninja-kreditnekartice' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => $statuses,
					'default' => "completed",
				),

				'status_failed' => array(
					'title'   => __( 'Status ob spodleteli transakciji', 'wooninja-kreditnekartice' ),
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => $statuses,
					'default' => "failed",
				),

				'send_mail' => array(
					'title'   => __( 'Pošlji uporabniku e-mail ob naročilu', 'wooninja-kreditnekartice' ),
					'desc'    => 'Takoj ko ga preumseri na Bankart (pred vpisom podatkov o kartici)',
					'type'    => 'select',
					'class'   => 'wc-enhanced-select',
					'options' => array(
						"da" => "Da",
						"ne" => "Ne",
					),
					'default' => "Da",
				),

				'custom_back' => array(
					'title'       => __( 'Preusmeri nazaj v trgovino (gumb povezava)', 'wooninja-kreditnekartice' ),
					'type'        => 'text',
					'description' => __( "Uporabite to, če ne uporabljate privzete nastavitve trgovine Woo.", 'wooninja-kreditnekartice' ),
				),

				'production_enviroment' => array(
					'title'       => __( 'Produkcijski podatki Bankart', 'wooninja-kreditnekartice' ),
					'type'        => 'title',
					'description' => "",
				),

				'api_key' => array(
					'title'    => __( 'API Key:', 'wooninja-kreditnekartice' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od Bankart)', 'wooninja-kreditnekartice' ),
					'default'  => '',
				),
				'shared_secret' => array(
					'title'    => __( 'Shared Secret:', 'wooninja-kreditnekartice' ),
					'type'     => 'password',
					'desc_tip' => __( '(ste prejeli od Bankart)', 'wooninja-kreditnekartice' ),
					'default'  => '',
				),
				'public_integration_key' => array(
					'title'    => __( 'Public Integration Key:', 'wooninja-kreditnekartice' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od Bankart)', 'wooninja-kreditnekartice' ),
					'default'  => '',
				),
				'api_username' => array(
					'title'    => __( 'Api username', 'wooninja-kreditnekartice' ),
					'type'     => 'text',
					'desc_tip' => __( '(ste prejeli od Bankart)', 'wooninja-kreditnekartice' ),
					'default'  => '',
				),
				'api_password' => array(
					'title'    => __( 'Api password: ', 'wooninja-kreditnekartice' ),
					'type'     => 'password',
					'desc_tip' => __( '(ste prejeli od Bankart)', 'wooninja-kreditnekartice' ),
					'default'  => '',
				),

			);

		}

		/**
		 * Check if the gateway is available for use.
		 *
		 * @return bool
		 */
		public function is_available() {
			if ( $this->environment == "yes" && $this->test_ips != "" ) {
				$remote_addr = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
				$allowed_ips = array_map( 'trim', explode( ",", $this->test_ips ) );
				if ( ! in_array( $remote_addr, $allowed_ips, true ) ) {
					return false;
				}
			}

			return parent::is_available();
		}

		/**
		 * Process the payment and return the result.
		 *
		 * @param int $order_id
		 * @return array|void
		 */
		function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );

			if ( ! $order ) {
				wc_add_notice( __( 'Napaka: Naročilo ni bilo najdeno.', 'wooninja-kreditnekartice' ), 'error' );
				return;
			}

			if ( $this->send_mail == "Da" ) {
				$order->update_status( 'on-hold', __( 'Čakamo na plačilo z Bankart', 'wooninja-kreditnekartice' ) );
			}

			$installments = $order->get_meta( 'installments' );

			require_once "wooninja/BankartGW.php";

			$bankart = new \WooNinja\BankartGW();
			$bankart->setLogin(
				htmlspecialchars_decode( trim( $this->api_username ) ),
				htmlspecialchars_decode( trim( $this->api_password ) ),
				htmlspecialchars_decode( trim( $this->api_key ) ),
				htmlspecialchars_decode( trim( $this->shared_secret ) )
			);

			$uniqid = uniqid();

			$data = array(
				"transactionId" => $order_id,
				"amount"        => $order->get_total(),
				"currency"      => $this->valuta,
				"language"      => $this->language,
				"uniqid"        => $uniqid,
				"customer"      => array(
					"identification" => $order->get_customer_id(),
					"firstName"      => $order->get_billing_first_name(),
					"lastName"       => $order->get_billing_last_name(),
					"address"        => $order->get_billing_address_1(),
					"city"           => $order->get_billing_city(),
					"postcode"       => $order->get_billing_postcode(),
					"country"        => $order->get_billing_country(),
					"email"          => $order->get_billing_email(),
				),
			);

			if ( $this->obroki == "yes" ) {
				$data['installments'] = sprintf( "%02d", $installments );
			}

			if ( $this->Transakcija == 4 ) {
				$call = $bankart->transaction_preauthorize( $data );
			} elseif ( $this->Transakcija == 1 ) {
				$call = $bankart->transaction_debit( $data );
			}

			if ( $call->returnType == "ERROR" ) {
				wc_add_notice( __( 'Napaka pri plačilu: ', 'wooninja-kreditnekartice' ) . esc_html( $call->errors->error->message ), 'error' );
				return;
			}

			$order->add_meta_data( 'uniqid_bankart', sanitize_text_field( $uniqid ), true );
			$order->save();

			return array(
				'result'   => 'success',
				'redirect' => (string) $call->redirectUrl,
			);
		}

		/**
		 * Display payment fields on checkout.
		 */
		public function payment_fields() {
			?>
			<div id="custom_input">
				<p class="form-row form-row-wide">
					<?php
					if ( $description = $this->get_description() ) {
						echo wp_kses_post( wpautop( wptexturize( $description ) ) );
					}

					if ( $this->obroki == "yes" ) {
						if ( empty( $this->obroki_stevilo ) ) {
							$this->obroki_stevilo = 12;
						}
						?>
						<label for="installments"><?php esc_html_e( 'Število obrokov', 'wooninja-kreditnekartice' ); ?></label>
						<select name="installments" id="installments">
							<?php
							for ( $i = 0; $i <= $this->obroki_stevilo; $i++ ) {
								?>
								<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
								<?php
							}
							?>
						</select>
						<?php
					}
					?>
				</p>
			</div>
			<?php
		}

	}

	/**
	 * Add the Bankart to WooCommerce.
	 */
	function woocommerce_add_gateway_bankart_gateway( $methods ) {
		$methods[] = 'Bankart';
		return $methods;
	}

	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_bankart_gateway' );

}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_bankart_action_links' );
function woocommerce_gateway_bankart_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bankart' ) ) . '">' . esc_html__( 'Nastavitve plačevanja z Bankart', 'wooninja-kreditnekartice' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

function load_bankart_wp_admin_style() {
	wp_register_style( 'bankart_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
	wp_enqueue_style( 'bankart_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_bankart_wp_admin_style' );

add_action( 'woocommerce_after_cart_table', function () {
	?>
	<br>
	<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . "img/visa_verified-master_secure_code.png" ); ?>" alt="">
	<br>
	<?php
} );

add_action( 'woocommerce_checkout_update_order_meta', 'sm_kreditne_custom_payment_update_order_meta' );
function sm_kreditne_custom_payment_update_order_meta( $order_id ) {

	if ( ! isset( $_POST['payment_method'] ) || sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) != 'wc_bankart' ) {
		return;
	}

	if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['woocommerce-process-checkout-nonce'] ) ), 'woocommerce-process_checkout' ) ) {
		return;
	}

	$installments = isset( $_POST['installments'] ) ? absint( $_POST['installments'] ) : 0;
	$order = wc_get_order( $order_id );
	if ( $order ) {
		$order->update_meta_data( 'installments', $installments );
		$order->save();
	}
}

add_action( 'woocommerce_order_status_completed', 'woo_kreditnekartice_capture' );

add_action( 'before_woocommerce_init', 'kreditnekartice_features_compatibility' );

function kreditnekartice_features_compatibility() {

	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {

		// HPOS: this plugin reads and writes orders through the CRUD layer only
		// (wc_get_order / wc_get_orders / update_meta_data), so it is compatible.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);

		// Blocks: this plugin ships no Blocks payment method integration,
		// so it is only available on the classic (shortcode) checkout.
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'cart_checkout_blocks',
			__FILE__,
			false
		);
	}
}
