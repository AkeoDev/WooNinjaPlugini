<?php
require( '../../../wp-load.php' );

if ( ! current_user_can( 'manage_woocommerce' ) ) {
	wp_die( 'Unauthorized access.' );
}

$nonce = isset( $_REQUEST['wolf_attack'] ) ? sanitize_text_field( $_REQUEST['wolf_attack'] ) : '';
if ( ! wp_verify_nonce( $nonce, 'verify-dpd' ) ) {
	die( 'Security check' );
}

if ( isset( $_GET['export'] ) ) {
	$post_ids = array_map( 'absint', explode( ',', sanitize_text_field( $_GET['ids'] ) ) );

	$list = array();
	$c    = 0;

	foreach ( $post_ids as $value ) {
		$order = wc_get_order( $value );

		$tip = 'D';
		if ( $order->get_payment_method() == 'cod' ) {
			$tip = 'D-COD';
		}
		$list[ $c ] = $tip . ';';

		$qty   = 0;
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$qty += $item['qty'];
		}
		$list[ $c ] .= $qty . ';';

		$price = 0;
		if ( $tip == 'D-COD' ) {
			$price = $order->get_total();
		}
		$list[ $c ] .= $price . ';';

		$list[ $c ] .= ';'; // COD ref.number

		$list[ $c ] .= 'ref_' . $order->get_id() . ';'; // Customer par. Ref

		$list[ $c ] .= ';'; // cus_adres_1

		$list[ $c ] .= str_replace( '"', '', $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ) . ';';

		$list[ $c ] .= ';'; // Name line 2

		$list[ $c ] .= $order->get_shipping_address_1() . ';';

		$list[ $c ] .= ';'; // Address line 2

		$list[ $c ] .= 'SLO;'; // Country code

		$list[ $c ] .= $order->get_shipping_postcode() . ';';

		$list[ $c ] .= $order->get_shipping_city() . ';';

		$list[ $c ] .= $order->get_billing_phone() . ';';

		$list[ $c ] .= ';'; // Phone 2

		$list[ $c ] .= $order->get_billing_email() . ';';

		$list[ $c ] .= $order->get_customer_note() . ';';

		$c++;
	}

	$csvOutput = '';
	foreach ( $list as $line ) {
		$csvOutput .= $line . "\n";
	}

	header( 'Content-length: ' . strlen( $csvOutput ) );
	header( 'Content-Author: Spletni Moduli - DPD Izvoz WooCommerce' );
	header( 'Content-Description: File Transfer' );
	header( 'Content-Type: text/csv' );
	header( 'Content-Disposition: attachment; filename=izvozDPD-' . date( 'Y-m-d' ) . '.csv' );
	header( 'Content-Transfer-Encoding: binary' );
	header( 'Pragma: public' );

	echo iconv( 'UTF-8', 'WINDOWS-1250', $csvOutput );

	exit;
}
