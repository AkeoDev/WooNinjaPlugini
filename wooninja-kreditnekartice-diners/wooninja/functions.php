<?php

function kreditnekarticediners_iswc_active() {
    include_once(ABSPATH.'wp-admin/includes/plugin.php');

    if(is_multisite()) {

        if(is_plugin_active_for_network('woocommerce/woocommerce.php')) {
            return true;
        } else {
            // If not network activated, loop through all blogs to find if it's activated somewhere
            $sites = get_sites();
            foreach ($sites as $site) {
                switch_to_blog($site->blog_id);
                if(is_plugin_active('woocommerce/woocommerce.php')) {
                    restore_current_blog();
                    return true;
                }
                restore_current_blog();
            }
        }
    } else {
        // If it's a single site, check if WooCommerce is active
        if(is_plugin_active('woocommerce/woocommerce.php')) {
            return true;
        }
    }

    return false;
}



/**
 * WooCommerce capture payment
 *
 * Po zaključku prevzemi €
 */

function woo_kreditnekarticediners_capture( $order_id ) {

    $order = wc_get_order($order_id);
    $type = $order->get_payment_method();

    if($type == "wc_bankart_diners"){

        $settings = new BankartDiners();


        //Ali je preauth
        if($settings->Transakcija == 4){
            //Check if already claimed
            $capture = $order->get_meta( 'capture_complete' );

            if(empty($capture)){
                //Ni še capturano
                $reference = $order->get_meta( 'bankart_reference_id' );

                if(!empty($reference)){
                    require_once plugin_dir_path( __FILE__ ) . "DinersGW.php";
                    $bankartgw = new \WooNinja\DinersGW();
                    $bankartgw->setLogin(
                        htmlspecialchars_decode(trim($settings->api_username)),
                        htmlspecialchars_decode(trim($settings->api_password)),
                        htmlspecialchars_decode(trim($settings->api_key)),
                        htmlspecialchars_decode(trim($settings->shared_secret))
                    );

                    $data = array(
                        "transactionId"                 =>  $order_id,
                        "amount"                        =>  $order->get_total(),
                        "currency"                      =>  $settings->valuta,
                        "referenceTransactionId"        =>  $reference,
                    );

                    $call = $bankart->transaction_capture($data);

                    $order->add_meta_data( 'capture_complete', true, true );
                    $order->save();
                }else{
                    $order->add_order_note( __('Zajem sredstev ni bil uspešen', 'woothemes'), 'error' );
                }
            }

        }


    }
}


?>
