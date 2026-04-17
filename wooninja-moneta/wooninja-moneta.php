<?php
/*
Plugin Name: WooNinja - VALÚ Moneta
Plugin URI: https://wooninja.si
Description: Možnost plačila preko VALÚ Moneta sistema v WooCommerce spletni trgovini.
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-moneta
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    add_action( 'admin_notices', function() {
    ?>
    <div class="notice notice-error is-dismissible">
        <p><?php echo esc_html( 'Vtičnik VALÚ Moneta za svoje delovanje potrebuje aktiven vtičnik WooCommerce.' ); ?></p>
    </div>
    <?php
    } );

    return false;
}





add_action('plugins_loaded', 'woocommerce_gateway_moneta_init', 0);

function woocommerce_gateway_moneta_init() {





	/**
 	 * Localisation
	 */
	load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    
	/**
 	 * Gateway class
 	 */
	class Moneta extends WC_Payment_Gateway {


		// Setup our Gateway's id, description and other values
		function __construct() {

		    // The global ID for this Payment method
		    $this->id = "wc_moneta";
		 
		    // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
		    $this->title = __( "VALÚ", 'wc-moneta' );
		 
		    // The description for this Payment Gateway, shown on the actual Payment options page on the backend
		    $this->description = __( "Plačila z VALÚ Moneta", 'wc-moneta' );
		 
		    // The title to be used for the vertical tabs that can be ordered top to bottom
		    $this->title = __( "Plačilo z moneto", 'wc-moneta' );
		 
            $this->enabled = $this->get_option( 'enabled' );
            
		    // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
		    $this->icon = null;
		 
		    // Bool. Can be set to true if you want payment fields to show on the checkout 
		    // if doing a direct integration, which we are doing in this case
		    $this->has_fields = true;
		 
		    // Supports the default credit card form
            $this->supports = array(
                'products'
            );
		    // This basically defines your settings which are then loaded with init_settings()
		    $this->init_form_fields();
		 
		    // After init_settings() is called, you can get the settings and load them into variables, e.g:
		    // $this->title = $this->get_option( 'title' );
		    $this->init_settings();
		     
		    // Turn these settings into variables we can use
		    foreach ( $this->settings as $setting_key => $value ) {
		        $this->$setting_key = $value;
 		    }
 		    

		    // Save settings
		    if ( is_admin() ) {
		        // Versions over 2.0
		        // Save our administration options. Since we are not going to be doing anything special
		        // we have not defined 'process_admin_options' in this class so the method in the parent
		        // class will be used instead
		        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		    }       

		} // End __construct()



	    // Build the administration fields for this specific Gateway
	    public function init_form_fields() {
	    	$potrditev = plugin_dir_url( __FILE__ ) . "confirmer.php";
	    	$narocilo = plugin_dir_url( __FILE__ ) . "narocilo.php";

            $statuses = wc_get_order_statuses();

	        $this->form_fields = array(
	            'enabled' => array(
	                'title'     => __( 'Omogoči plačila', 'wc-moneta' ),
	                'label'     => __( 'Omogoči', 'wc-moneta' ),
	                'type'      => 'checkbox',
 	                'default'   => 'no',
	            ),
	            'title' => array(
	                'title'     => __( 'Naziv plačila', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wc-moneta' ),
	                'default'   => __( 'VALÚ Moneta', 'wc-moneta' ),
	            ),
	            'description' => array(
	                'title'     => __( 'Opis', 'wc-moneta' ),
	                'type'      => 'textarea',
	                'desc_tip'  => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wc-moneta' ),
	                'default'   => __( 'Varno plačilo z vašim mobilnim telefonom (za vse številke slovenskih mobilnih operaterjev)', 'wc-moneta' ),
	                'css'       => 'max-width:350px;'
	            ),
	            'environment' => array(
	                'title'     => __( 'Testni način', 'wc-moneta' ),
	                'label'     => __( 'Omogoči', 'wc-moneta' ),
	                'type'      => 'checkbox',
	                'description' => __( 'Plačevanje z VALÚ v testnem načinu.', 'wc-moneta' ),
	                'default'   => 'no',
	            ),

                'mode' => array(
                    'title'       => __( 'Plačilni način', 'wc-moneta' ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( '
                        "Naročilo" je primerno za naročanje fizičnih artiklov, ki jih pošljete kupcu na dom. <br>
                        "Nakup" je primeren za takojšnje plačilo npr. plačilo novice, ki jo lahko takoj prebereš - digitalni produkti. <br>
                        "Naročilo" transakcije je potrebno ročno potrditi v administraciji VALÚ back-end portala, ko izdelek pošljete stranki. ', 'wc-moneta' ),
                    'default'     => 'nakup',
                    'options'     => array(
                        'nakup'          => __( 'Nakup', 'wc-moneta' ),
                        'narocilo' => __( 'Naročilo', 'wc-moneta' )
                    )
                ),

                'completed_status' => array(
                    'title'       => __( 'Status ob uspešnem plačilu', 'wc-moneta' ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Status naročila ko je plačilo uspešno', 'wc-moneta' ),
                    'default'     => 'wc-completed',
                    'options'     => $statuses
                ),


                'failed_status' => array(
                    'title'       => __( 'Status ob neuspešnem plačilu', 'wc-moneta' ),
                    'type'        => 'select',
                    'class'       => 'wc-enhanced-select',
                    'description' => __( 'Status naročila ko je plačilo spodletelo', 'wc-moneta' ),
                    'default'     => 'wc-failed',
                    'options'     => $statuses
                ),


                'pagecode' => array(
                    'title'     => __( 'PageCode', 'wc-moneta' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'V primeru plačilenga načina "Nakup". V primeru da ponudnik želi v eni transakciji prodati dve ali več storitev hkrati, potem naj pošlje zahtevek
za številko PageCode na email naslov: podpora.moneta@telekom.si', 'wc-moneta' ),
                    'default'   => __( '', 'wc-moneta' ),
                ),

				'test_enviroment' => array(
					'title'       => __( 'Testni podatki Monete', 'woocommerce' ),
					'type'        => 'title',
					'description' => "",
				),

	            'url_test' => array(
	                'title'     => __( 'URL do VALÚ Moneta', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( '(ste prejeli od VALÚ)', 'wc-moneta' ),
	                'default'   => __( 'https://test.moneta.si/placevanje/TarifficationE.dll', 'wc-moneta' ),
	            ),

	            'TARIFFICATIONID_test' => array(
	                'title'     => __( 'TARIFFICATIONID', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'ID vašega VALÚ računa (ste prejeli od VALÚ)', 'wc-moneta' ),
	                'default'   => __( '0', 'wc-moneta' ),
	            ),




	            


				'production_enviroment' => array(
					'title'       => __( 'Produkcijski podatki VALÚ', 'woocommerce' ),
					'type'        => 'title',
					'description' => "",
				),

	            'url_production' => array(
	                'title'     => __( 'URL do VALÚ', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( '(ste prejeli od VALÚ)', 'wc-moneta' ),
	                'default'   => __( '0', 'wc-moneta' ),
	            ),

	            'TARIFFICATIONID_production' => array(
	                'title'     => __( 'TARIFFICATIONID', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'ID vašega VALÚ računa (ste prejeli od VALÚ)', 'wc-moneta' ),
	                'default'   => __( '0', 'wc-moneta' ),
	            ),







				'oblika' => array(
					'title'       => __( 'Oblikovanje plačilne strani', 'woocommerce' ),
					'type'        => 'title',
					'description' => "",
				),
	            'logo' => array(
	                'title'     => __( 'Logo', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'Slika ki se pokaže ob preusmeritvi na VALÚ', 'wc-moneta' ),
	                'default'   => __( '', 'wc-moneta' ),
	            ),
	            'background' => array(
	                'title'     => __( 'Barva ozadja', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'Barva ozadja ki se pokaže ob preusmeritvi na VALÚ', 'wc-moneta' ),
	                'default'   => __( '#FFFFFF', 'wc-moneta' ),
	            ),
	            'text_color' => array(
	                'title'     => __( 'Barva besedila', 'wc-moneta' ),
	                'type'      => 'text',
	                'desc_tip'  => __( 'Barva besedila ki se pokaže ob preusmeritvi na VALÚ', 'wc-moneta' ),
	                'default'   => __( '#000000', 'wc-moneta' ),
	            ),

				'api_details' => array(
					'title'       => __( 'Podatki za VALÚ', 'woocommerce' ),
					'type'        => 'title',
					'description' => "
					URL za potrditev:<br>
					<code>$potrditev</code> 
					<br><br> 
					URL za naročilo: <br> 
					<code>$narocilo</code><br> <br> 
					Mail ki ga lahko pošljete Moneti: <br><br> 
					<textarea style='width: 50%; height: 300px'>
Pozdravljeni! 

Pošiljamo vam potrebne podatke za vzpostavitev plačevanja z Moneto na naši spletni trgovini.

URL naslov za potrdilo je:
$potrditev
URL naslov za naročilo pa: 
$narocilo

Lep pozdrav!
					</textarea>
					",
				)
	        );   



		}


		/**
		 * Check If The Gateway Is Available For Use
		 *
		 * @return bool
		 */
        public function is_available() {
            return 'yes' === $this->enabled;
        }
        


        public function get_payment_method_script_handles() {

            $asset_path   = plugin_dir_path( __DIR__ ) . 'build/index.asset.php';
            $version      = null;
            $dependencies = array();
            if( file_exists( $asset_path ) ) {
                $asset        = require $asset_path;
                $version      = isset( $asset[ 'version' ] ) ? $asset[ 'version' ] : $version;
                $dependencies = isset( $asset[ 'dependencies' ] ) ? $asset[ 'dependencies' ] : $dependencies;
            }

            wp_register_script( 
                'wc-moneta-blocks-integration', 
                plugin_dir_url( __DIR__ ) . 'build/index.js', 
                $dependencies, 
                $version, 
                true 
            );

            return array( 'wc-moneta-blocks-integration' );
        }

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id
		 * @return array
		 */

		function process_payment( $order_id ) {
			global $woocommerce;
			$order = wc_get_order( $order_id );
			$order->update_status('pending', __( 'Čakamo na plačilo z Moneto', 'woocommerce' ));
			// Return thankyou redirect
			$nonce = wp_create_nonce( 'order_id_'.$order_id );

			return array(
				'result' => 'success',
				'redirect' => plugin_dir_url( __FILE__ ) . "redirect.php?nonce=". $nonce ."&orderId=". $order_id
			);
		}

	}
	
	/**
 	* Add the Moneta to WooCommerce
 	**/
	function woocommerce_add_gateway_moneta_gateway($methods) {
		$methods[] = 'Moneta';
		return $methods;
	}
	
	add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_moneta_gateway' );

} 


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_moneta_action_links' );
function woocommerce_gateway_moneta_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=moneta' ) . '">' . __( 'Nastavitve plačevanja z VALÚ', 'wc-moneta' ) . '</a>',
    );
 
    // Merge our new link with the default ones
    return array_merge( $plugin_links, $links );    
}


function load_moneta_wp_admin_style() {
        wp_register_style( 'moneta_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
        wp_enqueue_style( 'moneta_wp_admin_css' );


}
add_action( 'admin_enqueue_scripts', 'load_moneta_wp_admin_style' );


add_action('woocommerce_blocks_loaded', 'moneta_gateway_block_support');
function moneta_gateway_block_support() {
    // Ensure the gateway class is included
    require_once __DIR__ . '/includes/class-wc-moneta-gateway-blocks-support.php';

    // Register the payment method type
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register(new WC_Moneta_Gateway_Blocks_Support());
        }
    );
}

add_action( 'before_woocommerce_init', 'moneta_cart_checkout_blocks_compatibility' );

function moneta_cart_checkout_blocks_compatibility() {

    if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				__FILE__,
				false // true (compatible, default) or false (not compatible)
			);
    }
		
}