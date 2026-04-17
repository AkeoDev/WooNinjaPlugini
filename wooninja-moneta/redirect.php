<?php
require_once('../../../wp-load.php');
$settings = new Moneta();


$sConfirmationID = isset( $_GET["orderId"] ) ? absint( $_GET["orderId"] ) : 0;

/// Preveri v katerem načinu smo (test/produkcija) in nastavi pravilne URL-je
/// Sestavimo Monetin URL za plaèevanje (Odjemalec spletni brskalnik)


if ( $settings->environment == "yes" ) {
	$sTarifficationE  = $settings->url_test . "?TARIFFICATIONID=" . $settings->TARIFFICATIONID_test . "&ConfirmationID=" . $sConfirmationID;
} else {
	$sTarifficationE  = $settings->url_production . "?TARIFFICATIONID=" . $settings->TARIFFICATIONID_production . "&ConfirmationID=" . $sConfirmationID;
}


if ( !wp_verify_nonce( $_GET["nonce"], 'order_id_'.$sConfirmationID  ) ) {
	echo "Napaka!";
	return;
}



?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />
	<meta http-equiv="refresh" content="3; url=<?php echo $sTarifficationE;?>" />
	<script type="text/javascript">
	  WebFontConfig = {
	    google: { families: [ 'Roboto::latin,latin-ext' ] }
	  };
	  (function() {
	    var wf = document.createElement('script');
	    wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
	      '://ajax.googleapis.com/ajax/libs/webfont/1/webfont.js';
	    wf.type = 'text/javascript';
	    wf.async = 'true';
	    var s = document.getElementsByTagName('script')[0];
	    s.parentNode.insertBefore(wf, s);
	  })(); </script>
		<style>
		body, a {
     		color: <?php echo esc_attr( $settings->text_color ); ?>;
			font-family: 'Roboto', sans-serif;
		}
		a {
			font-size: 11px;
		}
	</style>
  </head>
  <body bgcolor="<?php echo esc_attr( $settings->background ); ?>">
    <div style="margin: auto; width: 300px; margin-top: 20px">
      <img src="img/valu-logo.png" alt="VALÚ" style="margin-bottom: 20px" />
	    <?php if (!empty($settings->logo)) { ?>
	      <img src="<?php echo esc_url( $settings->logo ); ?>" alt="#" title="#" class="img-responsive">
	    <?php
	    }
	    ?>

	    <h3>Preusmeritev plačevanja na VALÚ.</h3>
	    <a href='<?php echo $sTarifficationE;?>'>Naročilo - <?php echo $sTarifficationE;?></a>
	    <br /><br />
	</div>
  </body>
</html>