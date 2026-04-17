<?php
require_once( dirname( __FILE__ ) . '/../../../wp-load.php' );
$settings = new NestPay();
$return_oid = isset( $_POST["ReturnOid"] ) ? absint( $_POST["ReturnOid"] ) : 0;
$order = wc_get_order( $return_oid );


$oldurl = $order->get_checkout_order_received_url( );
$lang = $order ? $order->get_meta( 'wpml_language' ) : '';

if(isset($lang) ) {
	$oldurl = apply_filters( 'wpml_permalink', $oldurl, $lang );
}
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
            color: <?php echo esc_attr( $settings->text_color ); ?>;
            font-family: 'Roboto', sans-serif;
          }
          a {
            font-size: 12px;
          }
        </style>

        <style>
            body {
                background: <?php echo esc_attr( $settings->background ); ?>;
                padding-top: 70px;
                padding-bottom: 70px;
            }
        </style>

        <?php
        if ( isset( $_POST["Response"] ) && sanitize_text_field( $_POST["Response"] ) == "Approved" )
        {
        ?>

         <meta http-equiv="refresh" content="4;URL='<?php echo esc_url( $oldurl ); ?>'" />
        <?php } else{
        ?>

        <?php
        }
        ?>

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

            if ( !isset( $_POST["ReturnOid"] ) )
            {
              header("Location: ".plugin_dir_url( __FILE__ ) . "error.php");
            } 


            if ( isset( $_POST["Response"] ) && sanitize_text_field( $_POST["Response"] ) == "Approved" )
            {
              
              $order->update_status( $settings->completed_status , __( 'Transaction done, awaiting delivery!', 'woocommerce' ));
              add_filter( 'wp_mail_content_type', 'smset_html_content_type' );
              if ( ! function_exists( "smset_html_content_type" ) ) { function smset_html_content_type() {
                  return 'text/html'; } }
              }

              $to      = get_bloginfo('admin_email');
              $subject = __( 'Transakcija je uspela!', 'woocommerce' );
              $body    = '';
              ob_start();

              ?>

                  OrderID: <?php if( isset( $_POST["ReturnOid"] ) ) { echo esc_html( sanitize_text_field( $_POST["ReturnOid"] ) ); } ?>  <br>
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
                  <?php _e( 'Transakcija je uspela!', 'woocommerce' ); ?><BR>
                  <?php _e( 'Hvala za vaše naročilo.', 'woocommerce' ); ?>


                </FONT>

                  <br><br>

                  <?php
                  if ( $settings->send_details == "yes" ) {
                  ?>
                    OrderID: <?php if( isset( $_POST["ReturnOid"] ) ) { echo esc_html( sanitize_text_field( $_POST["ReturnOid"] ) ); } ?>  <br>
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



            <?php } ?>

              

        </div>
      </div>
    </div>



    </body>
</html>