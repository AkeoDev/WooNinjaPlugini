<?php
require_once('../../../wp-load.php');

//global $woocommerce;

$sConfirmationID        = RequestString("ConfirmationID", 32);
$sTarifficationError    = RequestNumber("TARIFFICATIONERROR", 0, 1, 1);
$sConfirmationSignature = RequestString("ConfirmationSignature", 128);
$sConfirmationIDStatus  = RequestString("ConfirmationIDStatus", 32);
$sIP                    = GetServerVariable('REMOTE_ADDR');
$nError                 = 0;


if(
    ($sIP=="213.229.249.103") || 
    ($sIP=="213.229.249.104") || 
    ($sIP=="213.229.249.117") || 
    ($sIP=="193.77.55.136") || 
    ($sIP=="193.77.55.137") || 
    ($sIP=="193.77.253.105")
  ) {

  $order = wc_get_order($sConfirmationID);
  $settings = new Moneta();

  if ($sConfirmationID!="") {
    if ($sTarifficationError == "1") {
      $order->update_status($settings->failed_status, __( 'Plačilo je spodletelo.', 'woocommerce' ));
    } else {
      $nError = 0;
      $order->update_status($settings->completed_status, __( 'Plačilo izvedeno preko VALÚ', 'woocommerce' ));

    }
  }
}

$sError = "<error>" . intval( $nError ) . "</error>";
echo $sError;
exit;


//#######################################################################
//## Funkcije
//#######################################################################

//#######################################################################
//##  Request($sParameterName)
//#######################################################################
function Request($sParameterName) {
  $sReturn = "";
  if (isset($_POST[$sParameterName])) {
    $sReturn = $_POST[$sParameterName];
  } elseif (isset($_GET[$sParameterName])) {
    $sReturn = $_GET[$sParameterName];
  }

  return sanitize_text_field( $sReturn );
}

//#######################################################################
//##  RequestString($sParameterName, $nMaxLength)
//#######################################################################
function RequestString($sParameterName, $nMaxLength) {
  $sReturn = Request($sParameterName);
  if(strlen($sReturn)>$nMaxLength) {
    $sReturn = substr($sReturn, 0, $nMaxLength);
  }

  return $sReturn;
}

//#######################################################################
//##  RequestNumber($sParameterName, $nMin, $nMax, $nDefault)
//#######################################################################
function RequestNumber($sParameterName, $nMin, $nMax, $nDefault) {
  $nReturn = intval(Request($sParameterName));
  if(!(($nReturn>=$nMin) && ($nReturn<=$nMax))) {
    $nReturn = $nDefault;
  }

  return $nReturn;
}

//##############################################################
//## GetServerVariable($sVARIABLE)
//##############################################################
function GetServerVariable($sVARIABLE) {
  $sReturn = "";
  if(isset($_SERVER[$sVARIABLE])) {
    $sReturn = $_SERVER[$sVARIABLE];
  }

  return $sReturn;
}