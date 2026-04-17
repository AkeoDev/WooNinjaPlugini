<?php

require_once('../../../wp-load.php');
require_once('includes/wof.php');
$nonce = isset( $_GET['_nonce'] ) ? sanitize_text_field( $_GET['_nonce'] ) : '';

if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'wof_order_nonce' ) ) {
	wp_die( __( 'Security check failed.', 'wof-sm' ) );
}


	global $wp;
	define("WOOCOMMERCE_API_DIR", ABSPATH . "wp-content/plugins/woocommerce/includes/api/" );

	$form_id = isset( $_POST["form_id"] ) ? absint( $_POST["form_id"] ) : 0;
	$form = WOF::get_instance( $form_id );


	///////////////////////////////////////////////
	///// DEFAULT POST VALUES ARE EMPTY
	///////////////////////////////////////////////


	if ( !isset( $_POST["name"] ) ) {
		$_POST["name"] = "";
	}

	if ( !isset( $_POST["surname"] ) ) {
		$_POST["surname"] = "";
	}

	if ( !isset( $_POST["address"] ) ) {
		$_POST["address"] = "";
	}

	if ( !isset( $_POST["post"] ) ) {
		$_POST["post"] = "";
	}

	if ( !isset( $_POST["zip"] ) ) {
		$_POST["zip"] = "";
	}

	if ( !isset( $_POST["email"] ) ) {
		$_POST["email"] = "";
	}

	if ( !isset( $_POST["phone"] ) ) {
		$_POST["phone"] = "";
	}

	if ( !isset( absint( $_POST["product_id"] ) ) ) {
		absint( $_POST["product_id"] ) = "0";
	}

	if ( !isset( $_POST["payment_method"] ) ) {
		$_POST["payment_method"] = "";
		echo __("Wrong payment method.", "wof-sm");
		return;
	}


	/// If we are in multilanguage mode, we have to get correct product ID for translated content
	if( function_exists( 'icl_object_id' ) ) {
		absint( $_POST["product_id"] ) = icl_object_id( absint( $_POST["product_id"] ), "product", true, $form->language() );
	}

	///////////////////////////////////////////////
	///////////////////////////////////////////////

	$address = array(
                'first_name'          => sanitize_text_field( $_POST["name"] ),
                'last_name'           => sanitize_text_field( $_POST["surname"] ),
                'address_1'           => sanitize_text_field( $_POST["address"] ),
                'city'                => sanitize_text_field( $_POST["post"] ),
                'postcode'            => sanitize_text_field( $_POST["zip"] ),
	            'email'				  => sanitize_email( $_POST["email"] ),
	            'phone'				  => sanitize_text_field( $_POST["phone"] ),
	        );

	//// Get product
	$product = wc_get_product( absint( $_POST["product_id"] ) );


	/// Check if product exists
	if ( !empty($product) ) {
		
		/// Get shipping classes
		$classes = get_the_terms( absint( $_POST["product_id"] ), 'product_shipping_class' );

		/// Create order
		$order = wc_create_order();


		/// Add to cart, so that we can get shipping packages
        WC()->cart->add_to_cart( absint( $_POST["product_id"] ) , 1 );


		foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {
	        /// Add product to order
		    $order->add_product( wc_get_product( absint( $_POST["product_id"] ) ), 1 );

		}



	    /// Get shipping packages
        $package = WC()->cart->get_shipping_packages();
        if ( !empty($package[0] ) ) {
	        $package = $package[0];
		    $shipping = WC()->shipping->calculate_shipping_for_package( $package );

		    if ( !empty($shipping["rates"]) ) {
		     $first_key = key($shipping["rates"]);
		   	 $order->add_shipping( $shipping["rates"][$first_key] );
		    }
        }


        $order->set_address( $address, 'billing' );
        $order->set_address( $address, 'shipping' );


        $order->calculate_shipping();
        $order->calculate_totals();


        /// Payment methods
	    $order = wc_get_order( $order->get_id() );
	    $available_gateways = WC()->payment_gateways->payment_gateways();
	    $order->reduce_order_stock( );
	    $order->update_status('processing', __( 'Naročilo v obdelavi', 'woocommerce' ));
	    $payment_method = isset( $_POST["payment_method"] ) ? sanitize_text_field( $_POST["payment_method"] ) : '';
	    if ( ! isset( $available_gateways[ $payment_method ] ) ) {
	        wp_die( __( 'Invalid payment method.', 'wof-sm' ) );
	    }
	    $order->set_payment_method( $available_gateways[ $payment_method ] );
	    $result = $available_gateways[ $payment_method ]->process_payment( $order->get_id() );
		WC()->cart->empty_cart();

		/// Meta
		$meta = add_post_meta( $order->get_id(), "wof_form", $form->id() );


		// Redirect to success/confirmation/payment page
		if ( $result['result'] == 'success' ) {

			$result = apply_filters( 'woocommerce_payment_successful_result', $result, $order->get_id() );

			if ( is_ajax() ) {
				echo '<!--WC_START-->' . json_encode( $result ) . '<!--WC_END-->';
				exit;
			} else {

				/// If we have multilanguage situation, we have to set correct language link

				if(function_exists('icl_object_id')) {
					$link = apply_filters( 'wpml_permalink', $result['redirect'], $form->language() );
				} else {
					$link = $result['redirect'];
				}

				if ( $form->wof_redirect() == "woo_thankyou" ) {
					wp_redirect( $link );
				} else {
					wp_redirect( $form->custom_redirect() );
				}

				exit;
			}

		}


	} else {
		echo __("This product does not exist!", "wof-sm");
	}

