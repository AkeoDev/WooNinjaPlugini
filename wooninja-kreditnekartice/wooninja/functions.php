<?php

function kreditnekartice_iswc_active() {
    if (in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins')))) {
        return true;
    }else{
        return false;
    }
}



/**
 * WooCommerce capture payment
 *
 * Po zaključku prevzemi €
 */

function woo_kreditnekartice_capture( $order_id ) {

    $order = wc_get_order($order_id);
    $type = $order->get_payment_method();

    if($type == "wc_bankart"){

        $settings = new Bankart();


        //Ali je preauth
        if($settings->Transakcija == 4){
            //Check if already claimed
            $capture = $order->get_meta( 'capture_complete' );

            if(empty($capture)){
                //Ni še capturano
                $reference = $order->get_meta( 'bankart_reference_id' );

                if(!empty($reference)){
                    require_once plugin_dir_path( __FILE__ ) . "BankartGW.php";
                    $bankartgw = new \WooNinja\BankartGW();
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

                    $call = $bankartgw->transaction_capture($data);

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
