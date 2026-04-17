<html>
<head>
<title>3D</title>
<meta http-equiv="Content-Language" content="tr">
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-9">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="now">
</head>
<body>
<?php
 $orgClientId = "16INT20548";
 $orgOid = "24666ORDER256712jbsafd";
 $orgAmount = "91.96";
 $orgOkUrl = "http://specerija.si/test/success.php";
 $orgFailUrl = "http://specerija.si/test/fail.php";
 $orgTransactionType = "Auth";
 $orgInstallment = "0";
 $orgRnd = microtime();

 $orgCurrency = "949";
$clientId = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgClientId));
 $oid = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOid));
 $amount = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgAmount));
 $okUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgOkUrl));
 $failUrl = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgFailUrl));
 $transactionType = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgTransactionType));
 $installment = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgInstallment));
 $rnd = str_replace("|", "\\|", str_replace("\\", "\\\\", microtime()));
 $currency = str_replace("|", "\\|", str_replace("\\", "\\\\", $orgCurrency));
 $storeKey = str_replace("|", "\\|", str_replace("\\", "\\\\", "Hca66208"));

 $plainText = $clientId . "|" . $oid . "|" . $amount . "|" . $okUrl . "|" . $failUrl . "|" .
$transactionType . "|" . $installment . "|" . $rnd . "||||" . $currency . "|" . $storeKey;

 $hashValue = hash('sha512', $plainText);
 $hash = base64_encode (pack('H*',$hashValue)) ;
 $description = "";
$xid = "";
$lang="";
$email="";
$userid="";
?>
<center>
<form method="post" action="https://testsecurepay.intesasanpaolocard.com/fim/est3dgate">
<table>
<tr>
<td>Credit Card Number</td>
<td><input type="text" name="pan" size="20" />
</tr>
<tr>
<td>CVV</td>
<td><input type="text" name="cv2" size="4" value="" /></td>
</tr>
<tr>
<td>Expiration Date Year</td>
<td><input type="text" name="Ecom_Payment_Card_ExpDate_Year"
value="" /></td>
</tr>
<tr>
<td>Expiration Date Month</td>
<td><input type="text" name="Ecom_Payment_Card_ExpDate_Month"
value="" /></td>
</tr>
<tr>
<td>Choosing Visa / Master Card</td>
<td><select name="cardType">
<option value="1">Visa</option>
<option value="2">MasterCard</option>

</select>
</tr>
<tr>
<td align="center" colspan="2"><input type="submit"
value="Complete Payment" /></td>
</tr>
</table>
<input type="hidden" name="clientid" value="<?php echo $orgClientId ?>">
<input type="hidden" name="amount" value="<?php echo $orgAmount ?>">
<input type="hidden" name="oid" value="<?php echo $orgOid ?>">
<input type="hidden" name="okurl" value="<?php echo $orgOkUrl ?>">
<input type="hidden" name="failUrl" value="<?php echo $orgFailUrl ?>">
<input type="hidden" name="TranType" value="<?php echo $orgTransactionType ?>">
<input type="hidden" name="Instalment" value="<?php echo $orgInstallment ?>">
<input type="hidden" name="currency" value="<?php echo $orgCurrency ?>">
<input type="hidden" name="rnd" value="<?php echo $orgRnd ?>">
<input type="hidden" name="hash" value="<?php echo $hash ?>">
<input type="hidden" name="storetype" value="3D_PAY_HOSTING">
<input type="hidden" name="hashAlgorithm" value="ver2">
<input type="hidden" name="lang" value="en">
</form>
</center>
</body>
</html>