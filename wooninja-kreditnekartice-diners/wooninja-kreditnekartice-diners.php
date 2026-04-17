<?php
/**
* Plugin Name: WooNinja - Diners
* Plugin URI: https://wooninja.si
* Description: Možnost plačila preko Diners gatewaya v WooCommerce
* Version: 3.0.0
* Author: Humanfrog d.o.o.
* Author URI: https://wooninja.si
* Text Domain: wooninja-kreditnekartice-diners
*
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

include( plugin_dir_path( __FILE__ ) . '/wooninja/functions.php' );

if ( kreditnekarticediners_iswc_active() ) {

	add_action( 'plugins_loaded', 'woocommerce_gateway_bankart_diners_init', 0 );

} else {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible"><p><strong>WooNinja - Diners</strong> - Plugin ne deluje brez Woocommerce</p></div>
		<?php
	} );
	return false;
}


function woocommerce_gateway_bankart_diners_init() {

	class BankartDiners extends WC_Payment_Gateway {

		function __construct() {
			$this->id                 = "wc_bankart_diners";
			$this->method_title       = __( "Diners plačevanje", 'wooninja-kreditnekartice-diners' );
			$this->method_description = __( "Plačila z Diners", 'wooninja-kreditnekartice-diners' );
			$this->title              = __( "Plačilo z Diners", 'wooninja-kreditnekartice-diners' );
			$this->icon               = plugin_dir_url( __FILE__ ) . "img/dines-club-checkout.png";
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




        // Build the administration fields for this specific Gateway
        public function init_form_fields() {

            $statuses = wc_get_order_statuses();

            $this->form_fields = array(

                'language_plugin' => array(
                    'title'             => __( 'Language of the plugin', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => array(
                                "sl"    => "Slovenian",
                                "en"    => "English",
                                "de"    => "German",
                                "me"    =>  "Montenegrin",
                                "mk"    =>  "Macedonian",
                    ),
                    'default'   => "sl"
                ),

                'enabled' => array(
                    'title'     => __( 'Omogoči plačila', 'wc_bankart_diners' ),
                    'label'     => __( 'Omogoči', 'wc_bankart_diners' ),
                    'type'      => 'checkbox',
                    'default'   => 'no',
                ),
                'title' => array(
                    'title'     => __( 'Naziv plačila', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wc_bankart_diners' ),
                    'default'   => __( 'Diners', 'wc_bankart_diners' ),
                ),
                'description' => array(
                    'title'     => __( 'Opis', 'wc_bankart_diners' ),
                    'type'      => 'textarea',
                    'desc_tip'  => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wc_bankart_diners' ),
                    'default'   => __( 'Varno plačilo z vašo Diners kartico.', 'wc_bankart_diners' ),
                    'css'       => 'max-width:350px;'
                ),
                
                'environment' => array(
                    'title'     => __( 'Testni način', 'wc_bankart_diners' ),
                    'label'     => __( 'Omogoči', 'wc_bankart_diners' ),
                    'type'      => 'checkbox',
                    'description' => __( 'Plačevanje z Diners v testnem načinu.', 'wc_bankart_diners' ),
                    'default'   => 'yes',
                ),     

                'failed_transaction' => array(
                    'title'     => __( 'Poročaj o neuspelih transakcijah', 'wc_bankart_diners' ),
                    'label'     => __( 'Omogoči', 'wc_bankart_diners' ),
                    'type'      => 'checkbox',
                    'description' => __( 'Administratorju strani bo poslan report o neuspeli transakciji.', 'wc_bankart_diners' ),
                    'default'   => 'no',
                ),
                'test_ips' => array(
                    'title'     => __( 'Omejite iz katerih IP naslovov je možen dostop v testnem načinu', 'wc_bankart_diners' ),
                    'type'      => 'textarea',
                    'desc_tip'  => __( 'Za več IP-jev lahko ločite z vejico.V kolikor je polje prazno, je testni način dostopen vsem.', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                    'css'       => 'max-width:350px;'
                ),
                'language' => array(
                    'title'             => __( 'Jezik nakupne strani', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'description'       => __( 'Jezik v katerem se bo prikazala stran za vpis podatkov kreditne kartice', 'wc_bankart_diners' ),
                    'options'           => array(
                                "SI" => "Slovenian",
                                "HR" => "Croatian",
                                "US" => "English",
                                "MKD" => "Macedonian",
                                "SR" => "Serbian",
                                "IT" => "Italian",
                                "DE" => "German",
                                "ESP" => "Spanish",
                    ),
                    'default'   => "SI"
                ),
                'valuta' => array(
                    'title'             => __( 'Valuta trgovine', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'description'       => __( 'Koda valute ki se pošilja na Diners', 'wc_bankart_diners' ),
                    'options'           => array(
                                "EUR" => "EUR",
                                "HRK" => "HRK",
                                "USD" => "USD",
                                "MKD" => "MKD",
                    ),
                    'default'   => "EUR"
                ),

                'Transakcija' => array(
                    'title'             => __( 'Vrsta transakcije (action)', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => array(
                                "1" => "Purchase",
                                //"2" => "Credit",
                                //"3" => "Void Purchase",
                                "4" => "Authorization",
                                //"5" => "Capture",
                                //"6" => "Void Credit",
                                //"7" => "Void Capture",
                                //"9" => "Void Authorization",
                    ),
                    'default'   => "4",
                    'description' => __( '
                        "Authorization" je primerno za naročanje fizičnih artiklov, ki jih pošljete kupcu na dom. <br> 
                        "Purchase" je primeren za takojšnje plačilo npr. plačilo novice, ki jo lahko takoj prebereš - digitalni produkti.<br>
                        "Purchase" transakcije je potrebno ročno potrditi v administraciji Bankart back-end portala, ko izdelek pošljete stranki.', 'wc_bankart_diners' ),
                ),
                'obroki' => array(
                    'title'     => __( 'Plačevanje z obroki', 'wc_bankart_diners' ),
                    'label'     => __( 'Omogoči', 'wc_bankart_diners' ),
                    'type'      => 'checkbox',
                    'description' => __( '', 'wc_bankart_diners' ),
                    'default'   => 'no',
                ),

                'obroki_stevilo' => array(
                    'title'             => __( 'Število obrokov', 'wc_bankart_diners-diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'description'       => __( 'Število obrokov, ki jih omogočate strankam. POZOR to mora biti dogovorjeno z banko.', 'wc_bankart_diners-diners' ),
                    'options'           => array(
                                "3"     => "3",
                                "6"     => "6",
                                "12"    => "12",
                                "24"    =>  "24",
                                "36"    =>  "36"
                    ),
                    'default'   => "12"
                ),

                'status_success' => array(
                    'title'             => __( 'Status ob uspeli transakciji', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => $statuses,
                    'default'   => "completed"
                ),

                'status_failed' => array(
                    'title'             => __( 'Status ob spodleteli transakciji', 'wc_bankart_diners' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => $statuses,
                    'default'   => "failed"
                ),

                'send_mail' => array(
                    'title'             => __( 'Pošlji uporabniku e-mail ob naročilu', 'wc_bankart_diners' ),
                    'desc'              => 'Takoj ko ga preumseri na Diners (pred vpisom podatkov o kartici)',
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => array(
                        "da" => "Da",
                        "ne" => "Ne"
                    ),
                    'default'   => "Da"
                ),

                


                'production_enviroment' => array(
                    'title'       => __( 'Produkcijski podatki Diners', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),

                'api_key' => array(
                    'title'     => __( 'API Key:', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od Bankart)', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                ),
                'shared_secret' => array(
                    'title'     => __( 'Shared Secret:', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od Bankart)', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                ),
                'public_integration_key' => array(
                    'title'     => __( 'Public Integration Key:', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od Bankart)', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                ),
                'api_username' => array(
                    'title'     => __( 'Api username', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od Bankart)', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                ),
                'api_password' => array(
                    'title'     => __( 'Api password: ', 'wc_bankart_diners' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od Bankart)', 'wc_bankart_diners' ),
                    'default'   => __( '', 'wc_bankart_diners' ),
                ),


            );   



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
                'wc-diners-blocks-integration', 
                plugin_dir_url( __DIR__ ) . 'build/index.js', 
                $dependencies, 
                $version, 
                true 
            );

            return array( 'wc-diners-blocks-integration' );
        }
        /**
         * Check If The Gateway Is Available For Use
         *
         * @return bool
         */
        public function is_available() {
            $order = null;

            if ($this->environment == "yes" && $this->test_ips != "" ) {
                if (!in_array($_SERVER["REMOTE_ADDR"], explode(",", $this->test_ips) )) {
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
            global $woocommerce;
            $order = wc_get_order( $order_id );

            if ( $this->send_mail == "Da" ) {
                $order->update_status('on-hold', __( 'Čakamo na plačilo z Diners', 'woocommerce' ));
            }

            $installments = $order->get_meta( 'diners_installments' );

    

            require_once "wooninja/DinersGW.php";

           //Set ID
            $bankart = new \WooNinja\DinersGW();
            $bankart->setLogin(
                htmlspecialchars_decode(trim($this->api_username)),
                htmlspecialchars_decode(trim($this->api_password)),
                htmlspecialchars_decode(trim($this->api_key)),
                htmlspecialchars_decode(trim($this->shared_secret))
            );


            $uniqid = uniqid();
        
            //Set data
            $data = array(
                "transactionId" =>  $order_id,
                "amount"        =>  $order->get_total(),
                "currency"      =>  $this->valuta,
                "language"      =>  $this->language,
                "uniqid"  =>  $uniqid,
                "customer"      =>  array(
                    "identification"    =>  $order->get_customer_id(),
                    "firstName"         =>  $order->get_billing_first_name(),
                    "lastName"          =>  $order->get_billing_last_name(),
                    "address"           =>  $order->get_billing_address_1(),
                    "city"              =>  $order->get_billing_city(),
                    "postcode"          =>  $order->get_billing_postcode(),
                    "country"           =>  $order->get_billing_country(),
                    "email"             =>  $order->get_billing_email(),
                ),
            );


            // Ali so nastavljeni obroki?
            if ( $this->obroki == "yes" ) {
                $data['installments'] =  sprintf("%02d", $installments);
            }

            /* 
            * Sproži klic
            * Trenutno podpiramo dva tipa
            * Debit (takoj pobere)
            * Preautorize (pobere po zaključki)
            *
            */
            if($this->Transakcija == 4){
                //Preauth
                $call = $bankart->transaction_preauthorize($data);
            }elseif($this->Transakcija == 1){
                //Debit
                $call = $bankart->transaction_debit($data);
            }


            // --------- Posljemo zahtevo po inicalizaciji nakupa oz. HPP strani. ------------------------
            if ($call->returnType == "ERROR")  // neuspesna inicializacija
            {

                //Izved mail o napaki stranki

                //Izpiši error

                wc_add_notice( __('Napaka pri plačilu: ', 'woothemes') . $call->errors->error->message, 'error' );
                return;

            }
            else  // uspesna inicializacija
            {
                // Narocilo zapisemo v bazo.
                $order->add_meta_data( 'uniqid_bankart', $uniqid, true ); // enolicen ID, ki je odvisen od casa kdaj je bila funkcija klicana
                $order->save();
                /*
                add_post_meta( $order->get_id(), "_track_id_bankart", $paymentPipe->getTrackID() ); // enolicen ID, ki je odvisen od casa kdaj je bila funkcija klicana
                add_post_meta( $order->get_id(), "_payment_id_bankart", $paymentPipe->getPaymentId() ); // enolicen ID, ki je odvisen od casa kdaj je bila funkcija klicana
                add_post_meta( $order->get_id(), "payment_id_bankart", $paymentPipe->getPaymentId() ); // enolicen ID, ki je odvisen od casa kdaj je bila funkcija klicana
                */

                // Kupca preusmerimo na HPP stran, katere URL smo dobili iz inicializacijskega sporocila.
                return array(
                    'result' => 'success',
                    'redirect' => (string)$call->redirectUrl
                );

            }


        }


        public function payment_fields(){

          ?>
            <div id="custom_input">
                <p class="form-row form-row-wide">
                    <?php
                        if ( $description = $this->get_description() ) {
                          echo wpautop( wptexturize( $description ) );
                        }

                        if ( $this->obroki == "yes" ){

                            if(empty($this->obroki_stevilo)){
                                $this->obroki_stevilo = 12;
                            }
                    ?>      <label for="diners_installments" class=""><?php echo 'Število obrokov'; ?></label>
                            <select name="diners_installments" id="diners_installments">
                                <?php
                                for ($i=0; $i <= $this->obroki_stevilo; $i++) { 
                                ?>
                                    <option value="<?php echo $i;?>"><?php echo $i;?></option>
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
    * Add the Bankart to WooCommerce
    **/
    function woocommerce_add_gateway_bankart_diners_gateway($methods) {
        $methods[] = 'BankartDiners';
        return $methods;
    }
    
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_bankart_diners_gateway' );

} 


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_bankart_diners_action_links' );
function woocommerce_gateway_bankart_diners_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=bankartdiners' ) . '">' . __( 'Nastavitve plačevanja z Diners', 'wc_bankart_diners' ) . '</a>',
    );
 
    // Merge our new link with the default ones
    return array_merge( $plugin_links, $links );    
}


function load_bankart_diners_wp_admin_style() {
        wp_register_style( 'bankart_diners_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
        wp_enqueue_style( 'bankart_diners_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_bankart_diners_wp_admin_style' );




add_action('woocommerce_after_cart_table', function() {
?>
    <br>
    <img src="<?php echo plugin_dir_url( __FILE__ ) . "img/dines-club-checkout.png";?>" alt="">
    <br>
<?php
});


add_action( 'woocommerce_checkout_update_order_meta', 'sm_kreditnediners_custom_payment_update_order_meta' );
function sm_kreditnediners_custom_payment_update_order_meta( $order_id ) {

    if ( ! isset( $_POST['payment_method'] ) || sanitize_text_field( $_POST['payment_method'] ) != 'wc_bankart_diners' )
        return;

    $installments = isset( $_POST['diners_installments'] ) ? absint( $_POST['diners_installments'] ) : 0;
    $order = wc_get_order( $order_id );
    if ( $order ) {
        $order->update_meta_data( 'diners_installments', $installments );
        $order->save();
    }
}


//Capture the payment
add_action( 'woocommerce_order_status_completed', 'woo_kreditnekarticediners_capture');


add_action('woocommerce_blocks_loaded', 'diners_gateway_block_support');
function diners_gateway_block_support() {

    require_once __DIR__ . '/includes/class-wc-diners-gateway-blocks-support.php';

    // Register the payment method type
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register(new WC_Diners_Gateway_Blocks_Support());
        }
    );
}

add_action( 'before_woocommerce_init', 'diners_cart_checkout_blocks_compatibility' );

function diners_cart_checkout_blocks_compatibility() {

    if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				__FILE__,
				false // true (compatible, default) or false (not compatible)
			);
    }
		
}
?>