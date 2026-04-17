<?php
use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Moneta_Gateway_Blocks_Support extends AbstractPaymentMethodType {
	
	private $gateway;
	
	protected $name = 'wc_moneta'; // Payment gateway ID

	public function initialize() {
		// Get payment gateway settings
	//	$this->settings = get_option( "woocommerce_{$this->name}_settings", array() );
	$this->settings = get_option('woocommerce_wc_moneta_settings', array());

	}

	public function is_active() {
		return ! empty( $this->settings[ 'enabled' ] ) && 'yes' === $this->settings[ 'enabled' ];
	} 

	public function get_payment_method_script_handles() {

		wp_register_script(
			'wc-moneta-blocks-integration',
			plugin_dir_url( __DIR__ ) . 'build/index.js',
			array(
                'react',
				'wc-blocks-registry',
				'wc-settings',
				'wp-element',
				'wp-html-entities',
			),
			filemtime( plugin_dir_path( __DIR__ ) . 'build/index.js' ),
			true
		);

		return array( 'wc-moneta-blocks-integration' );
	}

	public function get_payment_method_data() {
		return array(
			'title'       => $this->settings['title'],
			'description' => $this->settings['description'],
			'supports'    => isset($this->settings['supports']) ? $this->settings['supports'] : array('products', 'refunds'),
		);
	}
}
