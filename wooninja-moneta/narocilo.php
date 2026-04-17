<?php
require_once('../../../wp-load.php');
require_once('utils.inc.php');

global $woocommerce;
$settings = new Moneta();


// Branje parametra ConfirmationID
$sConfirmationID = Utils_RequestString("ConfirmationID", 32);
// Najdi naročilo
$order = wc_get_order($sConfirmationID);


  Utils_ResponseExpires();
  
  //$sMyName = "http://www.ponudnik.si/moneta/nakup/nakup.php";
  $sMyName = plugin_dir_url( __FILE__ ) . "narocilo.php";
  
  $sStatus         = "";
  $sProviderData   = "";
  
  $sStatus      = "";
  $sData        = "";
  


  // Iskanje ConfirmationID nakupa
  if(!$order)
  {
    $sStatus = "ConfirmationID ne obstaja.";
  }
  else
  {
	  $sPurchaseStatus = $order->get_status();


    //$sProviderData     = $myMoneta->Get_ProviderData();
	if ($sPurchaseStatus=="pending")
    {
      $sStatus = "Čakam na potrditev...";

    }
    else if ($sPurchaseStatus=="failed")
    {
      $sStatus = "Plačilo ni bilo uspešno - TARIFFICATIONERROR=1.";

      if ( isset($woocommerce) ) {
         $woocommerce->cart->empty_cart(); 
      }


    }
    else if ("wc-".$sPurchaseStatus==$settings->completed_status)
    {
      $sStatus = "Potrjevanje uspešno.";
      $sData = "<h1>Zahvaljujemo se vam za nakup.</h1>";
      
      //$myMoneta->SetPurchaseStatus("prikazano", $sConfirmationID);

    }
    else if ($sPurchaseStatus=="completed")
    {
      $sStatus = "Potrjevanje uspešno.";
      $sData = "<h1>Zahvaljujemo se vam za nakup.</h1>";
      
      //$myMoneta->SetPurchaseStatus("prikazano", $sConfirmationID);

    }
    else
    {
      $sStatus = "Čakam na potrditev...";
    }

    $nakupData = "";

    if ($settings->mode == "nakup") {

      // generiramo vrstice naroèila
      $counter = 0;
      foreach ($order->get_items() as $key => $value) {

        // $sDescription, $sPrice, $sTaxRate, $sQuantity, $sUnit, $sArticleNumber
        $sProviderData = $sProviderData.MakeOrderLine($value["name"], $value["line_total"]/$value["qty"], $value["line_tax"], $value["qty"], "kos", "x");

        $append = "";

        if ($counter > 0) {
          $append = $counter;
        }

        $taxRate = round((($value["line_tax"]/$value["line_total"])*100),2);
        $line_price = round((($value["line_total"] + $value['line_tax'])/$value["qty"]),2);

        //get Tax Rate
        $_tax = new WC_Tax();
        $_product = $order->get_product_from_item( $value );
        $product_tax_class = $_product->get_tax_class();
        $tax =  $_tax->get_rates($product_tax_class);
        $rates = array_shift($tax);

        ob_start();
      ?>
        <?php         
        if ($counter > 0) {
        ?>
          <meta name="PageCode<?php echo $append;?>" content="<?php echo $settings->pagecode;?>">
        <?php         
        }
        ?>
         <meta name="Price<?php echo $append;?>" content="<?php echo $line_price; ?>">
         <meta name="Quantity<?php echo $append;?>" content="<?php echo $value["qty"];?>">
         <meta name="VATRate<?php echo $append;?>" content="<?php echo $rates['rate'];?>">
         <meta name="Description<?php echo $append;?>" content="<?php echo $value["name"];?>">
         <meta name="Currency<?php echo $append;?>" content="EUR">

      <?php
        $d = ob_get_clean();
        $nakupData .= $d;
        $counter++;
        

      }

    } else {


      // generiramo glavo naroèila
      $sDiscount = round($order->get_discount_total(),2);
      $sProviderData = MakeOrderHead("", 
          $order->billing_first_name, 
          $order->billing_last_name, "", 
          $order->billing_address_1, "", 
          $order->billing_postcode, 
          $order->billing_city, 
          $order->billing_country, 
          $order->billing_phone, 
          $order->billing_email, 
          $order->order_total, 
          $sConfirmationID,
          $sDiscount);  // Added discount parameter

      // generiramo vrstice naroèila
      foreach ($order->get_items() as $key => $value) {

        //get Tax Rate
        $_tax = new WC_Tax();
        $_product = $order->get_product_from_item( $value );
        $product_tax_class = $_product->get_tax_class();
        $tax =  $_tax->get_rates($product_tax_class);
        $rates = array_shift($tax);

        // $sDescription, $sPrice, $sTaxRate, $sQuantity, $sUnit, $sArticleNumber
        $sProviderData = $sProviderData.MakeOrderLine($value["name"], round(($value["line_total"]+$value["line_tax"])/$value["qty"],2), $rates['rate'], $value["qty"], "kos", "x");
      }

      /// Poštnina
      if ( $order->get_total_shipping() > 0 ) {

        $taxRate = round(($order->get_shipping_tax()/$order->get_shipping_total())*100,2);

        $sProviderData = $sProviderData.MakeOrderLine("Poštnina", round($order->get_shipping_total()+$order->get_shipping_tax(),2), $rates['rate'], 1, "kos", "x");
      }


      // generiramo zakljuèek naroèila
      $sProviderData = $sProviderData.MakeOrderEnd();

    }


  }

  $settings = new Moneta();


 // HTML vsebina plaèljive strani
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
  <head>
    <title>VALÚ - Potrjevanje nakupa</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="HandheldFriendly" content="true" />
    <meta name="viewport" content="width=device-width, height=device-height, user-scalable=no" />

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

<?php
  if ($sStatus=="Čakam na potrditev...")
  {
?>
    <meta http-equiv="refresh" content="3; url=<?php echo $sMyName;?>?ConfirmationID=<?php echo $sConfirmationID;?>" />

    <?php 
    if ($settings->mode == "narocilo") {
      echo $sProviderData;
    }
    ?>

<?php
  }
?>

<?php
  if ($sStatus=="Potrjevanje uspešno.")
  {
?>
    <meta http-equiv="refresh" content="3; url=<?php echo $order->get_checkout_order_received_url( ); ?>" />
<?php
  }
?>

    <?php 
    if ($settings->mode == "nakup") {
      echo $nakupData;
    }
    ?>



  </head>
  <body bgcolor="<?php echo esc_attr( $settings->background ); ?>">
    <div style="margin: auto; width: 300px; margin-top: 20px">
      <img src="img/valu-logo.png" alt="VALÚ" style="margin-bottom: 20px" />
      <?php if (!empty($settings->logo)) { ?>
        <img src="<?php echo esc_url( $settings->logo ); ?>" alt="#" title="#" class="img-responsive" style="margin-bottom: 10px">
      <?php
      }
      ?>
      <br /><b>Status nakupa:</b> <?php echo $sStatus;?>
      <br /><?php echo $sData;?>
    </div>
  </body>
</html>