<?php

// Error reporting disabled in production

  // Utils_ResponseExpires()
  // Utils_UploadFile_GetContent($sFileName)
  // Utils_DownloadFile_PutFile($sFileName, $sFileExtension, $sFileData)
  // Utils_Request($sParameterName) 
  // Utils_RequestNumber($sParameterName, $nMin, $nMax, $nDefault)
  // Utils_RequestFloat($sParameterName, $fltMin, $fltMax, $fltDefault)
  // Utils_RequestString($sParameterName, $nMaxLen)
  // Utils_Validate_String($sData)
  // Utils_CInt($sNum)
  // Utils_CDbl($sNum)
  // Utils_ParseCityHouse($sAddress, &$sCity, &$sHouse)
  // Utils_GetParameterValue($sData, $sParameterName)

  //#######################################################################
  //##  Utils_ResponseExpires()
  //####################################################################### 
  function Utils_ResponseExpires()
  {
    header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); 
    header ("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); 
    header ("Cache-Control: no-cache"); // HTTP/1.1 
    header ("Pragma: no-cache"); // HTTP/1.0 
    return 0;
  }
  
  //#######################################################################
  //##  Utils_UploadFile_GetContent($sFileName)
  //#######################################################################
  function Utils_UploadFile_GetContent($sFileName)
  {
    $data = "";
    $imgfile      = $_FILES[$sFileName]['tmp_name'];
    $imgfile_type = $_FILES[$sFileName]['type'];
    $imgfile_name = $_FILES[$sFileName]['name'];
    if (is_uploaded_file($imgfile))
    {
      $file = fopen($imgfile,'rb');
      $data = fread($file,filesize($imgfile));
      fclose($file);
    }
    return $data;
  }
  
  //#######################################################################
  //##  Utils_DownloadFile_PutFile($sFileName, $sFileData)
  //#######################################################################
  function Utils_DownloadFile_PutFile($sFileName, $sFileExtension, $sFileData)
  {
    $iFileLen = strlen($sFileData);    
    $datum = date("D, d M Y H:i:s")." GMT";
    header("Content-Type: application/$sFileExtension");
    header("Accept-Ranges: bytes");
    header("Last-Modified: $datum");    
    header("Expires: $datum");
    header("Content-Disposition: inline; filename=$sFileName.$sFileExtension");
    header("Pragma: no-cache");
    //header("Cache-Control: no-cache, must-revalidate");
    header("Cache-control: private");
    header("Content-Length: $iFileLen\n");    
    echo "$sFileData"; 
  }
  
  //##############################################################
  //## Utils_GetServerVariable($sVARIABLE)
  //##############################################################
  function Utils_GetServerVariable($sVARIABLE)
  {
    $sReturn = "";
    if(isset($_SERVER[$sVARIABLE]))
    {
      $sReturn = $_SERVER[$sVARIABLE];
    }
    return $sReturn;
  }

  //#######################################################################
  //##  Utils_Request($sParameterName)
  //#######################################################################
  function Utils_Request($sParameterName) 
  { 
    $sReturn = "";
    if (isset($_POST[$sParameterName])) 
    {
      $sReturn = $_POST[$sParameterName];
    }
    elseif (isset($_GET[$sParameterName]))
    {
      $sReturn = $_GET[$sParameterName];
    }
    return sanitize_text_field( $sReturn );
  } 
  
  //#######################################################################
  //##  Utils_RequestNumber($sParameterName, $nMin, $nMax, $nDefault)
  //#######################################################################
  function Utils_RequestNumber($sParameterName, $nMin, $nMax, $nDefault)
  {
    $nNum = Utils_Request($sParameterName);
	if($nNum=="")
	{
	  $nNum = "".$nDefault;
	}
    $nReturn = intval($nNum);
    if(!(($nReturn>=$nMin) && ($nReturn<=$nMax)))
    {
      $nReturn = $nDefault;
    }
    return $nReturn;
  }
  
  //#######################################################################
  //##  Utils_RequestFloat($sParameterName, $fltMin, $fltMax, $fltDefault)
  //#######################################################################
  function Utils_RequestFloat($sParameterName, $fltMin, $fltMax, $fltDefault)
  {
    $fltNum = Utils_Request($sParameterName);
    $fltReturn = floatval(str_replace(",",".","".$fltNum));
    if(!(($fltReturn>=$fltMin) && ($fltReturn<=$fltMax)))
    {
      $fltReturn = $fltDefault;
    }
    return $fltReturn;
  }

  //#######################################################################
  //##  Utils_RequestString($sParameterName, $nMaxLen)
  //#######################################################################
  function Utils_RequestString($sParameterName, $nMaxLen)
  {
    $sReturn = Utils_Request($sParameterName);
    if(strlen($sReturn)>$nMaxLen)
    {
      $sReturn = substr($sReturn, 0, $nMaxLen);
    }
    return Utils_Validate_String($sReturn);
  }
  
  //#######################################################################
  //##  Utils_Validate_String($sData)
  //#######################################################################  
  function Utils_Validate_String($sData)
  {
    $sValidChars = array (0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0,
	                        0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
            	            1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                        	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                        	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                        	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                        	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1,
                        	1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0,
                        	0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0,
                        	0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 0, 1, 0,
                        	0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        	0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        	0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0,
                        	1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
                        	0, 0, 0, 0, 0, 0, 1, 0, 1, 0, 0, 0, 0, 0, 0, 0,
                        	1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
    $iLen = strlen($sData);    
    for ($i=0; $i<$iLen; $i++)    
    {      
      if ($sValidChars[ord($sData[$i])]==0)
      {
        $ta = $sData[$i];      
        $sData[$i] = chr(32);
      }
    }
    $sData = str_replace("'", "''", $sData);
    $sData = str_replace("%", " ",  $sData);
	$sData = str_replace("\\", " ", $sData);
    return $sData;
  }

  //#######################################################################
  //##  Utils_CInt($sNum)
  //#######################################################################   
  function Utils_CInt($sNum)
  {
    return intval($sNum);
  }
  
  //#######################################################################
  //##  Utils_CDbl($sNum)
  //#######################################################################  
  function Utils_CDbl($sNum)
  {
    return floatval(str_replace(",", ".", "".$sNum));
  }
  
  //#######################################################################
  //##  Utils_ParseCityHouse($sAddress, &$sCity, &$sHouse)
  //#######################################################################  
  function Utils_ParseCityHouse($sAddress, &$sCity, &$sHouse)
  {    
    $sData = "".$sAddress;
    $iLen = strlen($sData);
    $sHouse = "";
    $sCity = "";
    
    $a = 0;
    if($iLen>0)
    {
      for($i=$iLen-1;$i>-1;$i--)
      {
        if($a==0)
        {
          if($sData[$i]==" ")
          {
            $a=1;
          }
          else
          {
            $sHouse = $sData[$i].$sHouse;
          }
        }
        else
        {
          $sCity = $sData[$i].$sCity;
        }        
      }      
    }    
    return 0;
  }
  
  //#######################################################################
  //##  Utils_GetParameterValue($sData, $sParameterName)
  //#######################################################################  
  function Utils_GetParameterValue($sData, $sParameterName)
  {
    $sReturn = "";
    $nFrom = strpos($sData, $sParameterName."=");
  
    if(!($nFrom === false))
    {
      $nFrom = $nFrom + strlen($sParameterName) + 1;
	  $nTo = strpos($sData, "#", $nFrom);
	  if($nTo === false)
	  {
	    $nTo = strlen($sData);
	  }
	  if($nTo>$nFrom)
	  {
	    $sReturn = substr($sData, $nFrom, $nTo-$nFrom);
	  }
    }
 
    return $sReturn;
  }
?>


<?php


  ################################################################
  // # MakeOrderHead  D: Order head
  // ################################################################
  function MakeOrderHead($sTaxNumber, $sFirstName, $sLastName, $sCompanyName, $sStreet, $sHouse, $sPostCode, $sCity, $sCountry, $sTelephone, $sEmail, $sPrice, $sConfirmationID, $sDiscount) {
    $EOL = "\r\n";
    $sCurrency = "EUR";
    $Date = "" . gmdate("d.m.Y");

    
    //$sXML = "<meta name=\"Order\" content=\"" . $EOL .
    $sXML = "<meta name=\"Order\" content=\"" .
    "<ORDER>" . $EOL .
    "<ORDER_HEAD>" . $EOL .
    "  <CustomerTaxNumber>" .   $sTaxNumber . "</CustomerTaxNumber>" . $EOL .
    "  <CustomerFirstName>" .   $sFirstName . "</CustomerFirstName>" . $EOL .
    "  <CustomerMiddleName></CustomerMiddleName>" . $EOL .
    "  <CustomerLastName>" .    $sLastName . "</CustomerLastName>" . $EOL .
    "  <CustomerNameSuffix></CustomerNameSuffix>" . $EOL .
    "  <CustomerCompanyName>" . $sCompanyName . "</CustomerCompanyName>" . $EOL .
    "  <CustomerStreet>" .      $sStreet . "</CustomerStreet>" . $EOL .
    "  <CustomerHouse>" .       $sHouse . "</CustomerHouse>" . $EOL .
    "  <CustomerPostCode>" .    $sPostCode . "</CustomerPostCode>" . $EOL .
    "  <CustomerCity>" .        $sCity . "</CustomerCity>" . $EOL .
    "  <CustomerState></CustomerState>" . $EOL .
    "  <CustomerCountry>" .     $sCountry . "</CustomerCountry>" . $EOL .
    "  <CustomerTelephone>" .   $sTelephone . "</CustomerTelephone>" . $EOL .
    "  <CustomerFax></CustomerFax>" . $EOL .
    "  <CustomerEMail>" .       $sEmail . "</CustomerEMail>" . $EOL .
    "  <DeliveryFirstName>" .   $sFirstName . "</DeliveryFirstName>" . $EOL .
    "  <DeliveryMiddleName></DeliveryMiddleName>" . $EOL .
    "  <DeliveryLastName>" .    $sLastName . "</DeliveryLastName>" . $EOL .
    "  <DeliveryNameSuffix></DeliveryNameSuffix>" . $EOL .
    "  <DeliveryCompanyName>" . $sCompanyName . "</DeliveryCompanyName>" . $EOL .
    "  <DeliveryStreet>" .      $sStreet . "</DeliveryStreet>" . $EOL .
    "  <DeliveryHouse>" .       $sHouse . "</DeliveryHouse>" . $EOL .
    "  <DeliveryPostCode>" .    $sPostCode . "</DeliveryPostCode>" . $EOL .
    "  <DeliveryCity>" .        $sCity . "</DeliveryCity>" . $EOL .
    "  <DeliveryState></DeliveryState>" . $EOL .
    "  <DeliveryCountry>" .     $sCountry . "</DeliveryCountry>" . $EOL .
    "  <DeliveryEMail>" .       $sEmail . "</DeliveryEMail>" . $EOL .
    "  <DeliveryTelephone>" .   $sTelephone . "</DeliveryTelephone>" . $EOL .
    "  <DeliveryFax></DeliveryFax>" . $EOL .
    "  <Currency>" .            $sCurrency . "</Currency>" .$EOL .
    "  <Price>" .               ($sPrice - $sDiscount) . "</Price>" . $EOL .
    "  <Discount>" .            $sDiscount . "</Discount>" . $EOL .
    "  <PriceNoDiscount>" .     $sPrice . "</PriceNoDiscount>" . $EOL .
    "  <DeliveryPrice>0</DeliveryPrice>" . $EOL .
    "  <DateFrom>" .            $Date . "</DateFrom>" . $EOL .
    "  <DateTo>" .              $Date . "</DateTo>" . $EOL .
    "  <OrderNumberInternal>" . $sConfirmationID . "</OrderNumberInternal>" . $EOL .
    "  <OrderCreated>" .        $Date . "</OrderCreated>" . $EOL .
    "  <NotificationDate>" .    $Date . "</NotificationDate>" . $EOL .
    "  <DeliveryType>OWN</DeliveryType>" . $EOL .
    "</ORDER_HEAD>" . $EOL .
    "<ORDER_LINE>";
    return $sXML;
  }


//################################################################
//# MakeOrderLine  D: add order line
//################################################################ 
function MakeOrderLine($sDescription, $sPrice, $sTaxRate, $sQuantity, $sUnit, $sArticleNumber)
{
 $sXML = "";
 $EOL = "\r\n";
 
  $nPriceNoTax    = round((Utils_CDbl($sPrice) * 100) / (100 + Utils_CDbl($sTaxRate)), 2);
  $nPriceSum      = round((Utils_CDbl($sPrice) * Utils_CInt($sQuantity)), 2);    
  $nPriceSumNoTax = round((Utils_CDbl($nPriceSum) * 100) / (100 + Utils_CDbl($sTaxRate)), 2);
  $nPriceTax      = round($nPriceSum - $nPriceSumNoTax, 2);
  
  $sXML = "<PRODUCT>".$EOL.
         "   <PageDescription>".$sDescription."</PageDescription>".$EOL.
         "   <Price>".$sPrice."</Price>".$EOL.
         "   <PriceNoTax>".$nPriceNoTax."</PriceNoTax>".$EOL.
         "   <TaxRate>".$sTaxRate."</TaxRate>".$EOL.
         "   <Quantity>".$sQuantity."</Quantity>".$EOL.
         "   <PriceSum>".$nPriceSum."</PriceSum>".$EOL.
         "   <PriceSumNoTax>".$nPriceSumNoTax."</PriceSumNoTax>".$EOL.
         "   <PriceTax>".$nPriceTax."</PriceTax>".$EOL.
         "   <Unit>".$sUnit."</Unit>".$EOL.
         "   <ArticleNumber>".$sArticleNumber."</ArticleNumber>".$EOL.
         " </PRODUCT>";
  return $sXML;
}


//################################################################
//# MakeOrderEnd  D: Close XML order
//################################################################ 
function MakeOrderEnd()
{
  $sXML= "";
  $EOL = "\r\n";
  $sXML= "  </ORDER_LINE>".$EOL.
         "</ORDER>\">";
  return $sXML;
}


  // #######################################################################
  // ##  CInt($sNum)
  // #######################################################################
  function CInt($sNum) {
    return intval($sNum);
  }

  // #######################################################################
  // ##  CDbl($sNum)
  // #######################################################################
  function CDbl($sNum) {
    return floatval(str_replace(",", ".", $sNum));
  }
  // ##############################################################################

?>