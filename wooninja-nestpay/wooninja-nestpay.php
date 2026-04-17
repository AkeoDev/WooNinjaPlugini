<?php
/*
Plugin Name: WooNinja - NestPay
Plugin URI: https://wooninja.si
Description: Možnost plačila z kreditne kartico preko NestPay sistema v WooCommerce spletni trgovini.
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-nestpay
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	add_action( 'admin_notices', function() {
	?>
	<div class="notice notice-error is-dismissible">
		<p>Vtičnik <strong><?php echo esc_html( 'WooNinja - NestPay' ); ?></strong> za svoje delovanje potrebuje aktiven vtičnik WooCommerce.</p>
	</div>
	<?php
	} );

	return false;
}



add_action('plugins_loaded', 'woocommerce_gateway_nestpay_init', 0);

function woocommerce_gateway_nestpay_init() {


    /**
     * Localisation
     */
    load_plugin_textdomain('wc-gateway-name', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
    
    /**
     * Gateway class
     */
    class NestPay extends WC_Payment_Gateway {


        // Setup our Gateway's id, description and other values
        function __construct() {

            // The global ID for this Payment method
            $this->id = "wc_nestpay";
         
            // The Title shown on the top of the Payment Gateways Page next to all the other Payment Gateways
            $this->method_title = __( "NestPay", 'wc-nestpay' );
         
            // The description for this Payment Gateway, shown on the actual Payment options page on the backend
            $this->method_description = __( "Plačila z NestPay", 'wc-nestpay' );
         
            // The title to be used for the vertical tabs that can be ordered top to bottom
            $this->title = __( "Plačilo z NestPay", 'wc-nestpay' );
         
            // If you want to show an image next to the gateway's name on the frontend, enter a URL to an image.
            $this->icon = plugin_dir_url( __FILE__ ) . "img/visa-cards-checkout.png";
         
            // Bool. Can be set to true if you want payment fields to show on the checkout 
            // if doing a direct integration, which we are doing in this case
            $this->has_fields = true;
         
            // Supports the default credit card form
            $this->supports = array( );
         
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
                    'title'     => __( 'Omogoči plačila', 'wc-nestpay' ),
                    'label'     => __( 'Omogoči', 'wc-nestpay' ),
                    'type'      => 'checkbox',
                    'default'   => 'no',
                ),
                'title' => array(
                    'title'     => __( 'Naziv plačila', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'Naziv plačila ki ga kupec vidi ob nakupu.', 'wc-nestpay' ),
                    'default'   => __( 'Kreditne kartice (Activa, Maestro, MasterCard, Visa in Visa Electron)', 'wc-nestpay' ),
                ),
                'description' => array(
                    'title'     => __( 'Opis', 'wc-nestpay' ),
                    'type'      => 'textarea',
                    'desc_tip'  => __( 'Opis plačila ki ga kupec vidi ob nakupu', 'wc-nestpay' ),
                    'default'   => __( 'Varno plačilo z vašo kreditno kartico (Activa, Maestro, MasterCard, Visa in Visa Electron).', 'wc-nestpay' ),
                    'css'       => 'max-width:350px;'
                ),
                'environment' => array(
                    'title'     => __( 'Testni način', 'wc-nestpay' ),
                    'label'     => __( 'Omogoči', 'wc-nestpay' ),
                    'type'      => 'checkbox',
                    'description' => __( 'Plačevanje z NestPay v testnem načinu.', 'wc-nestpay' ),
                    'default'   => 'no',
                ),
                'test_ips' => array(
                    'title'     => __( 'Omejite iz katerih IP naslovov je možen dostop v testnem načinu', 'wc-nestpay' ),
                    'type'      => 'textarea',
                    'desc_tip'  => __( 'Za več IP-jev lahko ločite z vejico.V kolikor je polje prazno, je testni način dostopen vsem.', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                    'css'       => 'max-width:350px;'
                ),
                'send_details' => array(
                    'title'     => __( 'Administratorju pripni podrobnosti o transakciji', 'wc-nestpay' ),
                    'label'     => __( 'Omogoči', 'wc-nestpay' ),
                    'type'      => 'checkbox',
                    'description' => __( '', 'wc-nestpay' ),
                    'default'   => 'no',
                ),    

                'failed_transaction' => array(
                    'title'     => __( 'Poročaj o neuspelih transakcijah', 'wc-bankart' ),
                    'label'     => __( 'Omogoči', 'wc-bankart' ),
                    'type'      => 'checkbox',
                    'description' => __( 'Administratorju strani bo poslan report o neuspeli transakciji.', 'wc-bankart' ),
                    'default'   => 'no',
                ),

                'language' => array(
                    'title'             => __( 'Jezik nakupne strani', 'wc-nestpay' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'description'       => __( 'Jezik v katerem se bo prikazala stran za vpis podatkov kreditne kartice', 'wc-nestpay' ),
                    'options'           => array(
                                "sl" => "Slovenski",
                                "en" => "Angleški",
                    ),
                    'default'   => "sl"
                ),
                
                'transaction_type' => array(
                    'title'             => __( 'Tip transakcije', 'wc-nestpay' ),
                    'type'              => 'select',
                    'class'             => 'wc-enhanced-select',
                    'options'           => array(
                                "Auth" => "Auth",
                                "PreAuth" => "PreAuth",
                    ),
                    'default'   => "Auth",
                    'description' => __( '
                        "PreAuth" je primerno za naročanje fizičnih artiklov, ki jih pošljete kupcu na dom. <br> 
                        "PreAuth" transakcije je potrebno ročno potrditi v administraciji NestPay back-end portala, ko izdelek pošljete stranki. Če uporabljate NestPay API podatke, lahko PreAuth naročilo ročno potrdite tudi v administraciji posameznega naročila. <br>
                        Auth" je primeren za takojšnje plačilo npr. plačilo novice, ki jo lahko takoj prebereš - digitalni produkti. ', 'wc-moneta' ),
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

                'test_enviroment' => array(
                    'title'       => __( 'Testni podatki NestPay', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),

                'urlforpaymenttest' => array(
                    'title'     => __( 'URL *', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( 'https://testsecurepay.eway2pay.com/fim/est3Dgate', 'wc-nestpay' ),
                ),


                'TranPortalID_test' => array(
                    'title'     => __( 'Merchant ID *', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'ID vašega NestPay računa (ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '0', 'wc-nestpay' ),
                ),

                'TranPortalPWD_test' => array(
                    'title'     => __( 'StoreKey *', 'wc-nestpay' ),
                    'type'      => 'password',
                    'desc_tip'  => __( 'StoreKey vašega NestPay računa (ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                ),



                'api_explain_test' => array(
                    'title'       => __( 'Dodatni podatki, ki omogočijo opravljanje transakcij v pogledu posameznega naročila<br>znotraj Woocommerce platforme, namesto v administraciji NestPay back-end portala:', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),

                'PaymentInitAPI_test' => array(
                    'title'     => __( 'API URL', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                ),


                'API_id_test' => array(
                    'title'     => __( 'API ID', 'wc-nestpay' ),
                    'type'      => 'text',
                    'default'   => __( '', 'wc-nestpay' ),
                ),


                'API_pass_test' => array(
                    'title'     => __( 'API Geslo', 'wc-nestpay' ),
                    'type'      => 'password',
                    'default'   => __( '', 'wc-nestpay' ),
                ),




                


                'production_enviroment' => array(
                    'title'       => __( 'Produkcijski podatki NestPay', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),

                'urlforpaymentproduction' => array(
                    'title'     => __( 'URL *', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                ),


                'TranPortalID' => array(
                    'title'     => __( 'Merchant ID *', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'ID vašega NestPay računa (ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '0', 'wc-nestpay' ),
                ),

                'TranPortalPWD' => array(
                    'title'     => __( 'StoreKey *', 'wc-nestpay' ),
                    'type'      => 'password',
                    'desc_tip'  => __( 'StoreKey vašega NestPay računa (ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                ),

                'api_explain_prod' => array(
                    'title'       => __( 'Dodatni podatki, ki omogočijo opravljanje transakcij v pogledu posameznega naročila<br>znotraj Woocommerce platforme, namesto v administraciji NestPay back-end portala:', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),

                'PaymentInitAPI' => array(
                    'title'     => __( 'API URL', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( '(ste prejeli od NestPay)', 'wc-nestpay' ),
                    'default'   => __( '', 'wc-nestpay' ),
                ),



                'API_id' => array(
                    'title'     => __( 'API ID', 'wc-nestpay' ),
                    'type'      => 'text',
                    'default'   => __( '', 'wc-nestpay' ),
                ),


                'API_pass' => array(
                    'title'     => __( 'API Geslo', 'wc-nestpay' ),
                    'type'      => 'password',
                    'default'   => __( '', 'wc-nestpay' ),
                ),





                'oblika' => array(
                    'title'       => __( 'Oblikovanje plačilne strani', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),
                'logo' => array(
                    'title'     => __( 'Logo', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'Slika ki se pokaže ob preusmeritvi na Moneto', 'wc-nestpay' ),
                    'default'   => __( 'http://placehold.it/450x150?text=Vaš+logotip', 'wc-nestpay' ),
                ),
                'background' => array(
                    'title'     => __( 'Barva ozadja', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'Barva ozadja ki se pokaže ob preusmeritvi na Moneto', 'wc-nestpay' ),
                    'default'   => __( '#FFFFFF', 'wc-nestpay' ),
                ),
                'text_color' => array(
                    'title'     => __( 'Barva besedila', 'wc-nestpay' ),
                    'type'      => 'text',
                    'desc_tip'  => __( 'Barva besedila ki se pokaže ob preusmeritvi na Moneto', 'wc-nestpay' ),
                    'default'   => __( '#000000', 'wc-nestpay' ),
                ),


                'custom_del' => array(
                    'title'       => __( 'Tečaj', 'woocommerce' ),
                    'type'        => 'title',
                    'description' => "",
                ),
                'tecaj' => array(
                    'title'     => __( 'HRK/EUR tečaj (xx.xx)', 'wc-nestpay' ),
                    'type'      => 'text',
                    'default'   => __( '7.59', 'wc-nestpay' ),
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
                'wc-nestpay-blocks-integration', 
                plugin_dir_url( __DIR__ ) . 'build/index.js', 
                $dependencies, 
                $version, 
                true 
            );

            return array( 'wc-nestpay-blocks-integration' );
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


            /*if ( $this->mode == "nakup" ) {

                    $needs_shipping = true;

                    $product_shipping = 0;

                    $product_virtual  = 0;

                    foreach ( WC()->cart->cart_contents as $cart_item_key => $values ) {
                       $_product    = apply_filters( 'woocommerce_cart_item_product', $values['data'], $values, $cart_item_key );
                        if ( $_product->is_virtual() ) {
                            $product_virtual++;
                        } else {
                            $product_shipping++;
                        }
                     }

                     if ( $product_virtual > 0 ) {
                        $needs_shipping = false;
                     }

                    if ( $needs_shipping ) {
                        return false;
                    }

            }*/

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
            ///$order->update_status('on-hold', __( 'Čakamo na plačilo z NestPay', 'woocommerce' ));
            // Return thankyou redirect
            $nonce = wp_create_nonce( 'order_id_'.$order_id );

            if ( function_exists('icl_object_id') ) {
                 $order_id .= "&lang=".ICL_LANGUAGE_CODE;
            }

            return array(
                'result' => 'success',
                'redirect' => plugin_dir_url( __FILE__ ) . "predir.php?nonce=". $nonce ."&orderId=". $order_id
            );
        }

    }
    
    /**
    * Add the Moneta to WooCommerce
    **/
    function woocommerce_add_gateway_nestpay_gateway($methods) {
        $methods[] = 'NestPay';
        return $methods;
    }
    
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_nestpay_gateway' );

} 


// Add custom action links
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woocommerce_gateway_nestypay_action_links' );
function woocommerce_gateway_nestypay_action_links( $links ) {
    $plugin_links = array(
        '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=checkout&section=nestpay' ) . '">' . __( 'Nastavitve plačevanja z NestPay', 'wc-nestpay' ) . '</a>',
    );
 
    // Merge our new link with the default ones
    return array_merge( $plugin_links, $links );    
}


function load_nestpay_wp_admin_style() {
        wp_register_style( 'nestpay_wp_admin_css', plugin_dir_url( __FILE__ ) . '/styles/style.css', false, '1.0.0' );
        wp_enqueue_style( 'nestpay_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'load_nestpay_wp_admin_style' );


function nestpay_verfied() {
?>
    <br>
    <img src="<?php echo plugin_dir_url( __FILE__ ) . "img/visa_verified-master_secure_code.png";?>" alt="">
    <br>
<?php
}
    
add_action('woocommerce_after_checkout_form', 'nestpay_verfied' );
add_action('woocommerce_after_cart_table', 'nestpay_verfied' );



//// Transaction history
add_action( 'admin_init', 'nestpay_load_admin_hooks' );

/**
 * Load the admin hooks
 */
function nestpay_load_admin_hooks() {

   // Hooks
   add_action( 'add_meta_boxes_shop_order', 'nestpay_meta_box' );
   add_action( 'add_meta_boxes_woocommerce_page_wc-orders', 'nestpay_meta_box' );

}


/**
 * Add the meta box on the single order page
 */
function nestpay_meta_box() {
    $screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
        ? wc_get_page_screen_id( 'shop-order' )
        : 'shop_order';
    add_meta_box( 'nestpay-box', __( 'Credit Cards (NestPay)', 'ceb-sm' ), 'nestpay_box_content', $screen, 'side', 'high' );
}

/**
 * Create the meta box content on the single order page
 */
function nestpay_box_content() {
    global $post_id, $theorder;

    if ( $theorder instanceof WC_Order ) {
        $order = $theorder;
    } else {
        $order = wc_get_order( $post_id );
    }
    $settings = new NestPay();

    if ( ! $order ) {
        echo "<small>Order not found.</small>";
        return;
    }

    $method = $order->get_payment_method();

    if ( $method != "wc_nestpay" ) {
        echo "<small>This order was not processed via NestPay.</small>";

        return;
    }



    if ( $settings->environment == "yes" ) {

         $clientid    = $settings->TranPortalID_test;
         $name        = $settings->API_id_test;
         $password    = $settings->API_pass_test;
         $url         = $settings->PaymentInitAPI_test;

    } else {

         $clientid    = $settings->TranPortalID;
         $name        = $settings->API_id;
         $password    = $settings->API_pass;
         $url         = $settings->PaymentInitAPI;

    }

    if ( $url == "" ) {
        return;
    }


    $oid = $order->get_id();

    $request= "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
    <CC5Request>
    <Name>{NAME}</Name>
    <Password>{PASSWORD}</Password>
    <ClientId>{CLIENTID}</ClientId>
    <OrderId>{OID}</OrderId>
    <Mode>P</Mode>
    <Extra><ORDERHISTORY>QUERY</ORDERHISTORY></Extra>
    </CC5Request>";

    $request=str_replace("{NAME}",$name,$request);
    $request=str_replace("{PASSWORD}",$password,$request);
    $request=str_replace("{CLIENTID}",$clientid,$request);
    $request=str_replace("{OID}",$oid,$request);
    $remote_addr = isset( $_SERVER["REMOTE_ADDR"] ) ? sanitize_text_field( $_SERVER["REMOTE_ADDR"] ) : '';
    $request=str_replace("{IP}", $remote_addr ,$request);        

    
    $ch = curl_init();                              
    curl_setopt($ch, CURLOPT_URL,$url);             
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);     
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);          
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
    $result = curl_exec($ch);


    if (curl_errno($ch)) 
    {
        //print curl_error($ch);
    } 
    else 
    {
        curl_close($ch);
    }

    $xml = simplexml_load_string($result);


    $extra = $xml->Extra->TRX1;
    $extra = preg_split('/\s+/', $extra);


?>

    <div class="print-actions nestpay-spletni-moduli">

        <?php
        if (isset($xml->ErrMsg) && "" != $xml->ErrMsg ) {
        ?>
            <span style="color: red;">
                <?php echo $xml->ErrMsg;?> - ProcReturnCode: <?php echo $xml->ProcReturnCode;?>
            </span>
            <hr>
        <?php
        }
        ?>

        Transaction status:
        <br>
        
        <strong>
        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "D") {
        ?>
            Unsuccessful transaction
        <?php
            }
        ?>

        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "A") {
        ?>
           Successful transaction (preauthorization)
        <?php
            }
        ?>


        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "C") {
        ?>
           Successful transaction
        <?php
            }
        ?>

        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "S") {
        ?>
           Settled transaction
        <?php
            }
        ?>

        <?php
            if ($xml->Response == "Approved" && $extra[0] == "C" && $extra[1] == "C") {
        ?>
            Payment refunded
        <?php
            }
        ?>

        </strong>

        <hr>


        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "A") {
        ?>
            <a href="" data-order-id="<?php echo $oid;?>" class="button js-confirm-transaction">Potrdi transakcijo (Confirm)</a>
            <a href="" data-order-id="<?php echo $oid;?>" class="button js-refund-transaction">Vrni transakcijo (Refund)</a>
        <?php
            }
        ?>

        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "S") {
        ?>
            <a href="" data-order-id="<?php echo $oid;?>" class="button js-refund-transaction">Vrni transakcijo (Refund)</a>
        <?php
            }
        ?>
        <?php
            if ($xml->Response == "Approved" && $extra[0] == "S" && $extra[1] == "C") {
        ?>
            <a href="" data-order-id="<?php echo $oid;?>" class="button js-refund-transaction">Vrni transakcijo (Refund)</a>
        <?php
            }
        ?>


        <br>

        <small>
            Akcije Partial refund (delno vračilo) in Partial confirm (delna potrditev) je možno urediti le znotraj administracije NestPay back-end portala.
        </small>

    </div>

    <script>

        jQuery(".js-refund-transaction").click( function(e) {
            e.preventDefault();

            var order_id = jQuery(this).attr("data-order-id");
            var data = {
                'action': 'nestpay_refund_transaction',
                'order_id': order_id
            };

            jQuery.post(ajaxurl, data, function(response) {
                location.reload(); 
            });


        });
        jQuery(".js-confirm-transaction").click( function(e) {
            e.preventDefault();

            var order_id = jQuery(this).attr("data-order-id");
            var data = {
                'action': 'nestpay_confirm_transaction',
                'order_id': order_id
            };

            jQuery.post(ajaxurl, data, function(response) {
                location.reload(); 
            });


        });

    </script>

<?php


}

add_action( 'wp_ajax_nestpay_refund_transaction', 'nestpay_refund_transaction' );
function nestpay_refund_transaction() {
    check_ajax_referer( 'nestpay_transaction_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( -1, 403 );
    }

    $order_id = intval( $_POST['order_id'] );
    $oid = $order_id;

    $settings = new NestPay();

    if ( $settings->environment == "yes" ) {

         $clientid    = $settings->TranPortalID_test;
         $name        = $settings->API_id_test;
         $password    = $settings->API_pass_test;
         $url         = $settings->PaymentInitAPI_test;

    } else {

         $clientid    = $settings->TranPortalID;
         $name        = $settings->API_id;
         $password    = $settings->API_pass;
         $url         = $settings->PaymentInitAPI;

    }

    if ( $url == "" ) {
        return;
    }

    $request= "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
    <CC5Request>
    <Name>{NAME}</Name>
    <Password>{PASSWORD}</Password>
    <ClientId>{CLIENTID}</ClientId>
    <OrderId>{OID}</OrderId>    
    <Type>Credit</Type>
    </CC5Request>";
    
    $request=str_replace("{NAME}",$name,$request);
    $request=str_replace("{PASSWORD}",$password,$request);
    $request=str_replace("{CLIENTID}",$clientid,$request);
    $request=str_replace("{OID}",$oid,$request);
    $request=str_replace("{IP}", $_SERVER["REMOTE_ADDR"] ,$request);        

    
    $ch = curl_init();                              
    curl_setopt($ch, CURLOPT_URL,$url);             
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);     
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);          
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
    $result = curl_exec($ch);


    if (curl_errno($ch)) 
    {
        //print curl_error($ch);
    } 
    else 
    {
        curl_close($ch);
    }

    $xml = simplexml_load_string($result);


    wp_die(); // this is required to terminate immediately and return a proper response
}


add_action( 'wp_ajax_nestpay_confirm_transaction', 'nestpay_confirm_transaction' );
function nestpay_confirm_transaction() {
    check_ajax_referer( 'nestpay_transaction_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        wp_die( -1, 403 );
    }

    $order_id = intval( $_POST['order_id'] );
    $oid = $order_id;

    $settings = new NestPay();

    if ( $settings->environment == "yes" ) {

         $clientid    = $settings->TranPortalID_test;
         $name        = $settings->API_id_test;
         $password    = $settings->API_pass_test;
         $url         = $settings->PaymentInitAPI_test;

    } else {

         $clientid    = $settings->TranPortalID;
         $name        = $settings->API_id;
         $password    = $settings->API_pass;
         $url         = $settings->PaymentInitAPI;

    }

    if ( $url == "" ) {
        return;
    }

    $request= "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
    <CC5Request>
    <Name>{NAME}</Name>
    <Password>{PASSWORD}</Password>
    <ClientId>{CLIENTID}</ClientId>
    <OrderId>{OID}</OrderId>    
    <Type>PostAuth</Type>
    </CC5Request>";
    
    $request=str_replace("{NAME}",$name,$request);
    $request=str_replace("{PASSWORD}",$password,$request);
    $request=str_replace("{CLIENTID}",$clientid,$request);
    $request=str_replace("{OID}",$oid,$request);
    $request=str_replace("{IP}", $_SERVER["REMOTE_ADDR"] ,$request);        

    
    $ch = curl_init();                              
    curl_setopt($ch, CURLOPT_URL,$url);             
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);     
    curl_setopt($ch, CURLOPT_TIMEOUT, 90);          
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
    
    $result = curl_exec($ch);


    if (curl_errno($ch)) 
    {
        //print curl_error($ch);
    } 
    else 
    {
        curl_close($ch);
    }

    $xml = simplexml_load_string($result);


    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('woocommerce_blocks_loaded', 'nestpay_gateway_block_support');
function nestpay_gateway_block_support() {

    require_once __DIR__ . '/includes/class-wc-nestpay-gateway-blocks-support.php';

    // Register the payment method type
    add_action(
        'woocommerce_blocks_payment_method_type_registration',
        function( Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry ) {
            $payment_method_registry->register(new WC_Nestpay_Gateway_Blocks_Support());
        }
    );
}

add_action( 'before_woocommerce_init', 'nestpay_cart_checkout_blocks_compatibility' );

function nestpay_cart_checkout_blocks_compatibility() {

    if( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
				'cart_checkout_blocks',
				__FILE__,
				false // true (compatible, default) or false (not compatible)
			);
    }
		
}
