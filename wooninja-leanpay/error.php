<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><?php
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
$settings = new LeanPay();
$return_oid = isset( $_GET["ReturnOid"] ) ? absint( $_GET["ReturnOid"] ) : 0;
$order = wc_get_order( $return_oid );
$order->update_status( $settings->failed_status, __( 'Transaction failed!', 'wc-leanpay' ) );

$cancelUrl = get_permalink( wc_get_page_id( 'checkout' ) );

?><HTML>
<HEAD>
<TITLE><?php echo bloginfo("name");?> - Napaka pri transakciji</TITLE>
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

<meta http-equiv="refresh" content="3;URL='<?php echo $cancelUrl; ?>'" />   


</HEAD>

<BODY>

<CENTER>

<FONT color="RED" size="6">Plačilo ni bilo uspešno - vaša kartica ni bila bremenjena. Za več informacij kontaktirajte vašo banko ali trgovino v kateri nakupujete.<br>

<a href="<?php echo $cancelUrl;?>">Nazaj na nakupovanje</a>

</FONT>
<P><P>
</CENTER>
<CENTER>
<hr>
</CENTER>

</BODY>
</HTML>
