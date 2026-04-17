<?php
require_once('../../../wp-load.php');
$settings = new LeanPay();


$sConfirmationID = isset( $_GET["orderId"] ) ? absint( $_GET["orderId"] ) : 0;
$order 			 = wc_get_order( $sConfirmationID );
$orderTotal 	 = $order->get_total();
$firstName 		 = $order->get_billing_first_name();
$lastName 		 = $order->get_billing_last_name();
$phoneNumber 	 = $order->get_billing_phone();
$bilAddress 	 = $order->get_billing_address_1();
$bilPostcode 	 = $order->get_billing_postcode();
$bilCity 		 = $order->get_billing_city();
$apiKey 		 = $settings->API_id;

if($settings->environment == "yes"){
	$link = "https://lapp.leanpay.si/vendor/checkout";
	$tokenLink = "https://lapp.leanpay.si/vendor/token";
} else {
	$link = "https://app.leanpay.si/vendor/checkout";
	$tokenLink = "https://app.leanpay.si/vendor/token";
}

if ( isset( $_GET["lang"] ) ) {
	$lang = sanitize_text_field( $_GET["lang"] );
} else {
	$lang = $settings->language;
}


/*if ( $settings->environment == "yes" ) {
	$orgClientId = $settings->TranPortalID_test;
	$storeKey 	  = $settings->TranPortalPWD_test;
	$link 		  = $settings->urlforpaymenttest;
} else {
	$orgClientId = $settings->TranPortalID;
	$storeKey 	  = $settings->TranPortalPWD;
	$link 		  = $settings->urlforpaymentproduction;
}*/

?><html>
<head>
<title>3D</title>
<meta http-equiv="Content-Language" content="tr">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
<link href='https://fonts.googleapis.com/css?family=Roboto&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
<link href='<?php echo plugin_dir_url( __FILE__ );?>/styles/style.css' rel='stylesheet' type='text/css'>


<style>
	body {
		font-family: 'Roboto', sans-serif;
        background: <?php echo esc_attr( $settings->background ); ?>;
        padding-top: 70px;
        padding-bottom: 70px;
	}
	input {
		border: 1px solid #ccc;
	}
	.process {

		padding: 15px;
		background: #1db47c;
		color: #FFF;
		border-radius: 5px;
		border: none;
		display: inline-block;
		margin-top: 25px;
	}
</style>
</head>
<body>
<?php
 

 $orgOid = $sConfirmationID;
 $time = time();
 $transactionId = $sConfirmationID . "-" . $time;


 $orgCurrency = "978";

 $orgAmount = $order->get_total();

 $orgOkUrl = plugin_dir_url( __FILE__ ) . "success.php?ReturnOid={$sConfirmationID}";
 $orgFailUrl = plugin_dir_url( __FILE__ ) . "error.php?ReturnOid={$sConfirmationID}";
 //$orgTransactionType = $settings->transaction_type;
 $orgInstallment = "";
 $orgRnd = date("his");



 //$clientId = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgClientId));
 $oid = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOid));
 $amount = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgAmount));
 $okUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOkUrl));
 $failUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgFailUrl));
 //$transactionType = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgTransactionType));
 $installment = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgInstallment));
 $rnd = str_replace("|", "\\|", str_replace("\\", "\\\\", date("his")));
 $currency = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgCurrency));
 //$storeKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));

 //$plainText = $clientId . "|" . $oid . "|" . $amount . "|" . $okUrl . "|" . $failUrl . "|" .
 //$transactionType . "|" . $installment . "|" . $rnd . "||||" . $currency . "|" . $storeKey;

 //$hashValue = hash('sha512', $plainText);
 //$hash = base64_encode (pack('H*',$hashValue)) ;
 $description = "";
  $xid = "";
  $email="";
  $userid="";

/*
echo $plainText;
echo "<br>";
echo $hash;
echo "<br>";*/

?>
<center>
<form method="post" id="leanpay" action="<?php echo $link;?>">

<?php if (!empty($settings->logo)) { ?>
<div class="row">
  <div class="col-md-12" style="text-align:center; margin-bottom: 25px;">
    <img src="<?php echo esc_url( $settings->logo ); ?>">
  </div>
</div>
<?php
}
	$randTranId = rand();
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $tokenLink);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n\"vendorApiKey\" : \"{$apiKey}\",\n\"vendorTransactionId\" : \"{$transactionId}\",\n\"amount\" : {$orderTotal},\n\"successUrl\" : \"{$orgOkUrl}\",\n\"errorUrl\" : \"{$orgFailUrl}\",\n\"vendorPhoneNumber\" : \"{$phoneNumber}\",\n\"vendorFirstName\" : \"{$firstName}\",\n\"vendorLastName\" : \"{$lastName}\",\n\"vendorAddress\": \"{$bilAddress}\",\n\"vendorZip\": \"{$bilPostcode}\",\n\"vendorCity\": \"{$bilCity}\",\n\"language\": \"{$lang}\"\n}");
	curl_setopt($ch, CURLOPT_POST, 1);

	$headers = array();
	$headers[] = "Content-Type: application/json";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

	$result = curl_exec($ch);
	if (curl_errno($ch)) {
	echo 'Error:' . curl_error($ch);
	}
	curl_close ($ch);

	$responseToken = json_decode($result);

?>

  <input type="hidden" name="token" value="<?php echo $responseToken->token; ?>" />
  <input type="submit" value="Buy with Leanpay" style="display:none;">

</form>
</center>
<script>
	document.getElementById("leanpay").submit();
</script>
</body>
</html>