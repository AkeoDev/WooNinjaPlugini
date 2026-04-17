<?php
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
$settings = new LeanPay();
$return_oid = isset( $_GET["ReturnOid"] ) ? absint( $_GET["ReturnOid"] ) : 0;
$order = wc_get_order( $return_oid );
$oldurl = $order->get_checkout_order_received_url();

?>
<!doctype html>
<!--[if lt IE 7]>      <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang=""> <![endif]-->
<!--[if IE 7]>         <html class="no-js lt-ie9 lt-ie8" lang=""> <![endif]-->
<!--[if IE 8]>         <html class="no-js lt-ie9" lang=""> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang=""> <!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo bloginfo("name");?></title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet" integrity="sha256-MfvZlkHCEqatNoGiOXveE8FIwMzZg4W85qfrfIFBfYc= sha512-dTfge/zgoMYpP7QbHy4gWMEGsbsdZeCXz7irItjcC3sPUFtf0kuFbDz/ixG7ArTxmDjLXDmezHubeNikyKGVyQ==" crossorigin="anonymous">
             

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
            font-family: 'Roboto', sans-serif;
          }
          a {
            font-size: 12px;
          }
        </style>

        <style>
            body {
                padding-top: 70px;
                padding-bottom: 70px;
            }
        </style>
      <?php
      if ( isset( $_GET["ReturnOid"] ) ) { ?>
        <meta http-equiv="refresh" content="3;URL='<?php echo $oldurl; ?>'" />
      <?php } ?>
    </head>
    <body>
    <div class="container">
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
        <div class="col-md-12">
          
            <?php
            // get Merchant Notification parameters

            if ( !isset( $_GET["ReturnOid"] ) )
            {
              header("Location: ".plugin_dir_url( __FILE__ ) . "error.php?Error=NoReturnIdFound");
            }
              
              $order->update_status( $settings->completed_status , __( 'Transaction done, awaiting delivery!', 'wc-leanpay' ));
              add_filter( 'wp_mail_content_type', 'smset_html_content_type' );
              if ( ! function_exists( "smset_html_content_type" ) ) { function smset_html_content_type() {
                  return 'text/html'; } }
              }

              $to      = get_bloginfo('admin_email');
              $subject = __( 'Transaction succeeded.', 'wc-leanpay' );
              $body    = '';
              ob_start();

              ?>

                  OrderID: <?php if( isset( $_GET["ReturnOid"] ) ) { echo esc_html( absint( $_GET["ReturnOid"] ) ); } ?>  <br>
                  AuthCode: <?php if( isset( $_POST["AuthCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["AuthCode"] ) ); } ?>  <br>
                  TransId: <?php if( isset( $_POST["TransId"] ) ) { echo esc_html( sanitize_text_field( $_POST["TransId"] ) ); } ?>  <br>
                  Response: <?php if( isset( $_POST["Response"] ) ) { echo esc_html( sanitize_text_field( $_POST["Response"] ) ); } ?>  <br>
                  ProcReturnCode: <?php if( isset( $_POST["ProcReturnCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["ProcReturnCode"] ) ); } ?>  <br>
                  mdStatus: <?php if( isset( $_POST["mdStatus"] ) ) { echo esc_html( sanitize_text_field( $_POST["mdStatus"] ) ); } ?>  <br>
                  EXTRA_TRXDATE: <?php if( isset( $_POST["EXTRA_TRXDATE"] ) ) { echo esc_html( sanitize_text_field( $_POST["EXTRA_TRXDATE"] ) ); } ?>  <br>
              
              <?php
              $body = ob_get_clean();

              if ( $settings->send_details == "yes" ) {
                wp_mail( $to, $subject, $body );
              }
              remove_filter( 'wp_mail_content_type', 'smset_html_content_type' );

            ?>
              <CENTER>
                <FONT size="6" color="GREEN">
                  <?php _e( 'Transakcija je uspela!', 'wc-leanpay' ); ?><BR>
                  <?php _e( 'Hvala za vaše naročilo.', 'wc-leanpay' ); ?><BR>
                  <?php _e( 'Preusmerjam...', 'wc-leanpay' ); ?>


                </FONT>

                  <br><br>

                  <?php
                  if ( $settings->send_details == "yes" ) {
                  ?>
                    OrderID: <?php if( isset( $_GET["ReturnOid"] ) ) { echo esc_html( absint( $_GET["ReturnOid"] ) ); } ?>  <br>
                    AuthCode: <?php if( isset( $_POST["AuthCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["AuthCode"] ) ); } ?>  <br>
                    TransId: <?php if( isset( $_POST["TransId"] ) ) { echo esc_html( sanitize_text_field( $_POST["TransId"] ) ); } ?>  <br>
                    Response: <?php if( isset( $_POST["Response"] ) ) { echo esc_html( sanitize_text_field( $_POST["Response"] ) ); } ?>  <br>
                    ProcReturnCode: <?php if( isset( $_POST["ProcReturnCode"] ) ) { echo esc_html( sanitize_text_field( $_POST["ProcReturnCode"] ) ); } ?>  <br>
                    mdStatus: <?php if( isset( $_POST["mdStatus"] ) ) { echo esc_html( sanitize_text_field( $_POST["mdStatus"] ) ); } ?>  <br>
                    EXTRA_TRXDATE: <?php if( isset( $_POST["EXTRA_TRXDATE"] ) ) { echo esc_html( sanitize_text_field( $_POST["EXTRA_TRXDATE"] ) ); } ?>  <br>
                  <?php
                  }
                  ?>
  

              </CENTER>



              

        </div>
      </div>
    </div>



    </body>
</html>