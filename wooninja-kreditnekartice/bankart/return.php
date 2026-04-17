<?php
/*
*
* Return postback
*
*/

require_once('../../../../wp-load.php');
require_once "../wooninja/BankartGW.php";


//Iz uniqid dobimo order
$uniqid_value = isset( $_GET["uniqid"] ) ? sanitize_text_field( $_GET["uniqid"] ) : '';
$orders = wc_get_orders( array(
    'limit'      => 1,
    'meta_key'   => 'uniqid_bankart',
    'meta_value' => $uniqid_value,
) );
$order = ! empty( $orders ) ? $orders[0] : false;
$settings = new Bankart();

$shop_url = get_permalink( woocommerce_get_page_id( 'shop' ) );
if( strlen( $settings->custom_back ) > 0) {
	$shop_url = $settings->custom_back;
}

//Check if order confirmed
if("wc-" . $order->get_status() == $settings->status_success){
	//Potrjen
	
	$url = $order->get_checkout_order_received_url();
	
	?>
	<head>
	  <meta http-equiv="refresh" content="5; url=<?=$url?>">
	</head> 
	
	<div style="width: 350px; margin: auto; height: 100px; margin-top: 50px; text-align: center">
		<h1><?= __('Plačilo je uspešno.', 'woothemes') ?></h1>
		<br />
		<img src="../img/visa-cards-checkout.png" />
		<br /><br />
		<a href="<?= $shop_url ?>" style="text-decoration: none; color: black; font-weight: bold"><?= __('Nazaj v trgovino', 'woothemes') ?></a>
	</div>
	<?php
}elseif("wc-" . $order->get_status() == $settings->status_failed){
	//Failano
	?>
	<div style="width: 350px; margin: auto; height: 100px; margin-top: 50px; text-align: center">
		<h1><?= __('Plačilo NI bilo uspešno.', 'woothemes') ?></h1>
		<br />
		<img src="../img/visa-cards-checkout.png" />
		<br /><br />
		<a href="<?= $shop_url ?>" style="text-decoration: none; color: black; font-weight: bold"><?= __('Nazaj v trgovino', 'woothemes') ?></a>
	</div>
	<?php
}else{
	//Čakamo
	?>
	<head>
	  <meta http-equiv="refresh" content="5">
	</head> 
	<div style="width: 350px; margin: auto; height: 100px; margin-top: 50px; text-align: center">
		<h1><?= __('Čakamo potrditev plačila', 'woothemes') ?></h1>
		<br />
		<img src="../img/visa-cards-checkout.png" />
		<br /><br />
		<a href="<?= $shop_url ?>" style="text-decoration: none; color: black; font-weight: bold"><?= __('Nazaj v trgovino', 'woothemes') ?></a>
	</div>
	<?php
}
?>

