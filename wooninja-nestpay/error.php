<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"><?php
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
$settings = new NestPay();
$return_oid = isset( $_POST["ReturnOid"] ) ? absint( $_POST["ReturnOid"] ) : 0;
$order = wc_get_order( $return_oid );
$order->update_status( $settings->failed_status, __( 'Transaction failed!', 'woocommerce' ) );

$cancelUrl = get_permalink( wc_get_page_id( 'checkout' ) );
$lang = $order ? $order->get_meta( 'wpml_language' ) : '';

if(isset($lang) ) {
	$cancelUrl = apply_filters( 'wpml_permalink', $cancelUrl, $lang );
}

if ($settings->failed_transaction == "yes") {

    add_filter( 'wp_mail_content_type', 'smset_html_content_type' );
    if ( ! function_exists( "smset_html_content_type" ) ) { function smset_html_content_type() {
        return 'text/html'; } }
    }

    $to      = get_bloginfo('admin_email');
    $subject = __( 'Transaction fail report #', 'woocommerce' ) . $return_oid;
    $body    = '';
    ob_start();

    ?>

        OrderID: <?php if ( isset( $_POST["ReturnOid"] ) ) { echo esc_html( sanitize_text_field( $_POST["ReturnOid"] ) ); } ?>  <br>
        AuthCode: <?php if ( isset( $_POST["AuthCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["AuthCode"] ) ); } ?>  <br>
        TransId: <?php if ( isset( $_POST["TransId"] ) ) { echo esc_html( sanitize_text_field( $_POST["TransId"] ) ); } ?>  <br>
        Response: <?php if ( isset( $_POST["Response"] ) ) { echo esc_html( sanitize_text_field( $_POST["Response"] ) ); } ?>  <br>
        ProcReturnCode: <?php if ( isset( $_POST["ProcReturnCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["ProcReturnCode"] ) ); } ?>  <br>
        mdStatus: <?php if ( isset( $_POST["mdStatus"] ) ) { echo esc_html( sanitize_text_field( $_POST["mdStatus"] ) ); } ?>  <br>
        EXTRA_TRXDATE: <?php if ( isset( $_POST["EXTRA_TRXDATE"] ) ) { echo esc_html( sanitize_text_field( $_POST["EXTRA_TRXDATE"] ) ); } ?>  <br>
    
    <?php
    $body = ob_get_clean();


    $sent = wp_mail( $to, $subject, $body );
    remove_filter( 'wp_mail_content_type', 'smset_html_content_type' );

}

?><HTML>
<HEAD>
<TITLE><?php echo bloginfo("name");?> - <?php _e( 'Napaka pri transakciji', 'wc-nestpay' ); ?></TITLE>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">

<meta http-equiv="refresh" content="3;URL='<?php echo $cancelUrl; ?>'" />   

<style>
	body, a {           
		font-family: 'Roboto', sans-serif;
	}
    
	a {
		font-size: 22px;
	}
</style>
</HEAD>

<BODY>

<CENTER>

<?php if (!empty($settings->logo)) { ?>
	<div class="row">
	  <div class="col-md-12" style="text-align:center; margin-bottom: 25px;">
		<img src="<?php echo esc_url( $settings->logo ); ?>">
	  </div>
	</div>
<?php
}
?>

<div class="row">
  <div class="col-md-12" style="text-align:center; margin-bottom: 25px;">
    <FONT color="RED" ><?php _e( 'Plačilo ni bilo uspešno - vaša kartica ni bila bremenjena. Za več informacij kontaktirajte vašo banko ali trgovino v kateri nakupujete.', 'wc-nestpay' ); ?><br>
	<a href="<?php echo $cancelUrl;?>"><?php _e( 'Nazaj na nakupovanje', 'wc-nestpay' ); ?></a>
	</FONT>
  </div>
</div>

</CENTER>
<CENTER>
<hr>
</CENTER>

</BODY>
</HTML>
