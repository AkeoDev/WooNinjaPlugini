<?php
/*
*
* Return postback
*
*/

require_once('../../../../wp-load.php');
require_once "../wooninja/DinersGW.php";


$rawData = file_get_contents("php://input");

if(!empty($rawData)){

	$xml = simplexml_load_string($rawData);


	$order = wc_get_order((int)$xml->transactionId);

	$settings = new BankartDiners();

	//Check result
	if((string)$xml->result == 'OK'){
		//Confirm order
		$order->update_status( $settings->status_success , __( 'Transaction done, awaiting delivery!', 'wc_bankart_diners' ));

		//Dodamo opombo
		$order->add_order_note(__("Plačano preko Diners z ID: " . (string)$xml->purchaseId));

		//Dodamo reference ID
		$order->add_meta_data( 'bankart_reference_id', (string)$xml->referenceId, true );
		$order->save();

		print "OK";

	}else{
		//Failed order
		$order->update_status( $settings->status_failed , __( 'Transaction failed.', 'wc_bankart_diners' ));

		//Dodamo opombo
		$order->add_order_note(__("Zgrešeno plačilo preko Diners z ID: " . (string)$xml->purchaseId) . ". " . __("Razlog: ") .(string)$xml->errors->error->adapterMessage);

		print "OK";
	}
}


