<?php
require_once('../../../wp-load.php');
$settings = new LeanPay();

$raw_post = file_get_contents( 'php://input' );

$decoded  = json_decode( $raw_post );
if(!empty($decoded)){
	$orderId = preg_replace("/[^0-9]/", "", strtok($decoded->vendorTransactionId,"-"));
	$order = wc_get_order($orderId);
	$vendorTransactionId = $decoded->vendorTransactionId;
    $md5secretPass = md5($settings->API_secret);
    $amount = (string)number_format($decoded->amount, 2, ".", "");
    $status = $decoded->status;
	switch( $decoded->status ) {
	    case 'SUCCESS' :
	        $leanPayTransactionId = $decoded->leanPayTransactionId;
	        $md5hashValidation = md5($leanPayTransactionId.$vendorTransactionId.$md5secretPass.$amount.$status);
	        $order->update_status( $settings->completed_status, sprintf( __( 'Vaš nakup je bil izveden uspešno.', 'wc-leanpay' ), get_woocommerce_currency(), $order->get_total() ) );
	        if($decoded->md5Signature == $md5hashValidation){
	        	$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status)); $order->save();
	    	} else {
	    		$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status) . ". NOTICE: md5 hashes do not match, compare order attributes and values in woocommerce and leanpay vendor dashboard."); $order->save();
	    	}
	        break;

	    case 'FAILED' :
	        $leanPayTransactionId = "null";
	        $md5hashValidation = md5($leanPayTransactionId.$vendorTransactionId.$md5secretPass.$amount.$status);
	        $order->update_status( $settings->failed_status, sprintf( __( 'Transakcija ni bila uspešno izvedena.', 'wc-leanpay' ) ) );
	        if($decoded->md5Signature == $md5hashValidation){
	        	$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status)); $order->save();
	    	} else {
	    		$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status) . ". NOTICE: md5 hashes do not match, compare order attributes and values in woocommerce and leanpay vendor dashboard."); $order->save();
	    	}
	        break;

	    case 'CANCELED':
	        $leanPayTransactionId = "null";
	        $md5hashValidation = md5($leanPayTransactionId.$vendorTransactionId.$md5secretPass.$amount.$status);
	        $order->update_status( $settings->failed_status, sprintf( __( 'Transakcija je bila preklicana.', 'wc-leanpay' ) ) );
	        if($decoded->md5Signature == $md5hashValidation){
	        	$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status)); $order->save();
	    	} else {
	    		$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status) . ". NOTICE: md5 hashes do not match, compare order attributes and values in woocommerce and leanpay vendor dashboard."); $order->save();
	    	}
	        break;

	    case 'EXPIRED':
	        $leanPayTransactionId = "null";
	        $md5hashValidation = md5($leanPayTransactionId.$vendorTransactionId.$md5secretPass.$amount.$status);
	        $order->update_status( $settings->failed_status, sprintf( __( 'Vaša seja je potekla.', 'wc-leanpay' ) ) );
	        if($decoded->md5Signature == $md5hashValidation){
	        	$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status)); $order->save();
	    	} else {
	    		$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status) . ". NOTICE: md5 hashes do not match, compare order attributes and values in woocommerce and leanpay vendor dashboard."); $order->save();
	    	}
	        break;

	    default:
	    	$leanPayTransactionId = "null";
	        $order->update_status( $settings->failed_status, sprintf( __( 'Prišlo je do napake, prosimo poskusite ponovno.', 'wc-leanpay' ) ) );
	        $md5hashValidation = md5($leanPayTransactionId.$vendorTransactionId.$md5secretPass.$amount.$status);
	        if($decoded->md5Signature == $md5hashValidation){
	        	$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status)); $order->save();
	    	} else {
	    		$order->update_meta_data("leanpay_order_status","LeanPay Response Status: " . strtoupper($decoded->status) . ". NOTICE: md5 hashes do not match, compare order attributes and values in woocommerce and leanpay vendor dashboard."); $order->save();
	    	}
	        break;
    }
// $method = get_post_meta( $post_id, '_payment_method', true ); <-- set_post_meta za prikaz statusa v administraciji
    echo $decoded->status;
} else {
	echo "No input found";
}
