<?php
require_once('../../../wp-load.php');
$settings = new NestPay_diners();



$sConfirmationID = isset( $_GET["orderId"] ) ? absint( $_GET["orderId"] ) : 0;
$order = wc_get_order( $sConfirmationID );

if ( isset( $_GET["lang"] ) ) {
	$lang = sanitize_text_field( $_GET["lang"] );
} else {
	$lang = $settings->language;
}


if ( $settings->environment == "yes" ) {
	$orgClientId = $settings->TranPortalID_test;
	$storeKey 	  = $settings->TranPortalPWD_test;
	$link 		  = $settings->urlforpaymenttest;
} else {
	$orgClientId = $settings->TranPortalID;
	$storeKey 	  = $settings->TranPortalPWD;
	$link 		  = $settings->urlforpaymentproduction;
}

?><html>
<head>
<title><?php _e( 'Preusmerjam na plačilno stran ...', 'wc-nestpay_diners' ); ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="Content-Language" content="tr">
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

 $orgCurrency = "978";

 $orgAmount = $order->get_total();

 /// Temporary currency conversion - NestPay do not support multicurrency yet!
 $curr = $order->get_currency();
 if ( $curr == "MKD" ) {

 	/// Conversion to EUR
 	$orgCurrency = "807";
 	//$orgAmount = number_format((float)$orgAmount * 0.0162919515, 2, '.', '');;

 }
if ( $curr == "USD" ) {

 	/// Conversion to EUR
 	$orgCurrency = "840";
 	//$orgAmount = number_format((float)$orgAmount * 0.0162919515, 2, '.', '');;

 }

 if ( $curr == "RSD" ) {

 	/// Conversion to EUR
 	$orgCurrency = "941";

 }


 if ( $curr == "HRK" ) {

 	/// Conversion to EUR
 	$orgCurrency = "191";
 	$orgAmount = number_format((float)$orgAmount * $settings->tecaj, 2, '.', '');;

 }




 $orgOkUrl = plugin_dir_url( __FILE__ ) . "success.php";
 $orgFailUrl = plugin_dir_url( __FILE__ ) . "error.php";
 $orgTransactionType = $settings->transaction_type;
 $orgInstallment = "";
 $orgRnd = date("his");



 $clientId = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgClientId));
 $oid = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOid));
 $amount = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgAmount));
 $okUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOkUrl));
 $failUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgFailUrl));
 $transactionType = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgTransactionType));
 $installment = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgInstallment));
 $rnd = str_replace("|", "\\|", str_replace("\\", "\\\\", date("his")));
 $currency = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgCurrency));
 $storeKey = str_replace("|", "\\|", str_replace("\\", "\\\\", $storeKey));

 $plainText = $clientId . "|" . $oid . "|" . $amount . "|" . $okUrl . "|" . $failUrl . "|" .
 $transactionType . "|" . $installment . "|" . $rnd . "||||" . $currency . "|" . $storeKey;

 $hashValue = hash('sha512', $plainText);
 $hash = base64_encode (pack('H*',$hashValue)) ;
 $description = "";
	$xid = "";
	$email="";
	$userid="";


?>
<center>
<form method="post" id="nestpay" action="<?php echo $link;?>">

<div class="row">
  <div class="col-md-12" style="text-align:center; margin-bottom: 25px;">
    <?php if (!empty($settings->logo)) { ?>
    <img src="<?php echo esc_url( $settings->logo ); ?>">
    <?php
    }
    ?>
    <?php _e( 'Preusmerjam na plačilno stran ...', 'wc-nestpay_diners' ); ?>	
  </div>
</div>


<input type="hidden" name="shopurl" value="<?php echo get_bloginfo( "url" ) ?>">
<input type="hidden" name="clientid" value="<?php echo $orgClientId ?>">
<input type="hidden" name="amount" value="<?php echo $orgAmount ?>">
<input type="hidden" name="oid" value="<?php echo $orgOid ?>">
<input type="hidden" name="okurl" value="<?php echo $orgOkUrl ?>">
<input type="hidden" name="failUrl" value="<?php echo $orgFailUrl ?>">
<input type="hidden" name="TranType" value="<?php echo $orgTransactionType ?>">
<input type="hidden" name="currency" value="<?php echo $orgCurrency ?>">
<input type="hidden" name="rnd" value="<?php echo $orgRnd ?>">
<input type="hidden" name="hash" value="<?php echo $hash ?>">
<input type="hidden" name="storetype" value="3D_PAY_HOSTING">
<input type="hidden" name="hashAlgorithm" value="ver2">
<input type="hidden" name="lang" value="<?php echo $lang;?>">
<input type="hidden" name="refreshtime" value="2" />

</form>
</center>
<script>
	document.getElementById("nestpay").submit();
</script>
</body>
</html>