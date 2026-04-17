<?php
/*
	Licence processing code
	Copyright 2020 WooNinja (info@wooninja.si)

*/

namespace WooNinja;

class DinersGW {
	private $xml;
	private $result;

	private $gateway_url = "https://gateway.bankart.si/transaction";
	private $bankart_api_username;
	private $bankart_api_password;
	private $bankart_apikey;
	private $bankart_sharedsecret;

	function build_signature() {

		$method 		= 'POST';
		$xml 			= $this->xml;
		$contentType 	= 'text/xml; charset=utf-8';
		$date 			= new \DateTime('now', new \DateTimeZone('UTC'));
		$timestamp 		= $date->format('D, d M Y H:i:s T');
		$additionals 	= '';
		$requestUri 	= '/transaction';


		$signatureMessage = join("\n", [$method, md5($xml), $contentType, $timestamp, $additionals, $requestUri]);

		$digest = hash_hmac('sha512', $signatureMessage, $this->bankart_sharedsecret, true);
		$signature = base64_encode($digest);

		return array(
			"timestamp" => $timestamp,
			"contentType" => $contentType,
			"signature" => $signature,
		);
	}

	private function send_query() {
		
		$signature = $this->build_signature();

		//Build signature
		$ch = curl_init($this->gateway_url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->xml);
		curl_setopt($ch, CURLOPT_POST, 1);

		$call_header = array(
		    'Authorization: Gateway '. $this->bankart_apikey . ':' . $signature['signature'],
		    'Date: '. $signature['timestamp'],
		    'Content-Type: '. $signature['contentType'],
		    //'X-Integration-Help: ' . $this->bankart_sharedsecret
		);

		curl_setopt($ch, CURLOPT_HTTPHEADER, $call_header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);

		//Execute
		$server_output = curl_exec($ch);

		//Catch erros
		if (curl_errno($ch)) {
		    $error_msg = curl_error($ch);
		}
		curl_close($ch);

		if (isset($error_msg)) {
		    die("Error conecting to gateway " . $error_msg);
		}

		$result = $this->parse_result($server_output);

		return $result;

	}


	private function parse_result($server_output) {
		return simplexml_load_string($server_output);
	}

	// Translates old languages to new
	private function language_transform($lang) {

		//Check if language in array
		$old_languages = array("SI","HR","MKD","SR","IT","DE","ESP");
		$new_languages = array("sl","en","mk","en","en","de","en");


		$return =  str_replace($old_languages, $new_languages, $lang);

		if(!empty($return)){
			return $return;
		}else{
			return "en";
		}

	}



	function transaction_debit($data) {
		$xml = new \SimpleXMLElement('<transaction />');
		$xml->addAttribute('xmlns','http://gateway.bankart.si/Schema/V2/Transaction');

		$xml->addChild("username",$this->bankart_api_username);
		$xml->addChild("password",$this->bankart_api_password);
		$xml->addChild("language",$this->language_transform($data['language']));

		$debit = $xml->addChild("debit");
		$debit->addChild("transactionId",$data['transactionId']);

		$customer = $debit->addChild("customer");
		$customer->addChild("identification",$data['customer']['identification']);
		$customer->addChild("firstName",$data['customer']['firstName']);
		$customer->addChild("lastName",$data['customer']['lastName']);
		$customer->addChild("billingAddress1",$data['customer']['address']);
		$customer->addChild("billingCity",$data['customer']['city']);
		$customer->addChild("billingPostcode",$data['customer']['postcode']);
		$customer->addChild("billingCountry",$data['customer']['country']);
		$customer->addChild("email",$data['customer']['email']);

		if($data['installments']){
			//Installments
			$extraData = $debit->addChild("extraData",$data['installments']);
			$extraData->addAttribute('key','userField1');
		}
		//

		$debit->addChild("amount",$data['amount']);
		$debit->addChild("currency",$data['currency']);
		$debit->addChild("successUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/return.php?id=" . $data['uniqid']));	
		$debit->addChild("cancelUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/return.php?id=" . $data['uniqid']));
		$debit->addChild("callbackUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/postback.php"));
		
		$debit->addChild("transactionIndicator","SINGLE");


		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
	
		$this->xml = trim($dom->saveXML());

		//Send query
		$result = $this->send_query();

		if($result['success'] == "false"){
			return "There was an error with payment " . $result['errors']['error']['message'];
		}else{
			return $result;
		}

	}



	function transaction_preauthorize($data) {
		$xml = new \SimpleXMLElement('<transaction />');
		$xml->addAttribute('xmlns','http://gateway.bankart.si/Schema/V2/Transaction');

		$xml->addChild("username",$this->bankart_api_username);
		$xml->addChild("password",$this->bankart_api_password);
		$xml->addChild("language",$this->language_transform($data['language']));

		$preauthorize = $xml->addChild("preauthorize");
		$preauthorize->addChild("transactionId",$data['transactionId']);

		$customer = $preauthorize->addChild("customer");
		$customer->addChild("identification",$data['customer']['identification']);
		$customer->addChild("firstName",$data['customer']['firstName']);
		$customer->addChild("lastName",$data['customer']['lastName']);
		$customer->addChild("billingAddress1",$data['customer']['address']);
		$customer->addChild("billingCity",$data['customer']['city']);
		$customer->addChild("billingPostcode",$data['customer']['postcode']);
		$customer->addChild("billingCountry",$data['customer']['country']);
		$customer->addChild("email",$data['customer']['email']);


		if($data['installments']){
			//Installments
			$extraData = $preauthorize->addChild("extraData",$data['installments']);
			$extraData->addAttribute('key','userField1');
		}

		$preauthorize->addChild("amount",$data['amount']);
		$preauthorize->addChild("currency",$data['currency']);
		$preauthorize->addChild("successUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/return.php?id=" . $data['uniqid']));	
		$preauthorize->addChild("cancelUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/return.php?id=" . $data['uniqid']));
		$preauthorize->addChild("callbackUrl",get_site_url(null,"/wp-content/plugins/woo-kreditnekartice-diners/bankart/postback.php"));
		

		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
	
		$this->xml = trim($dom->saveXML());

		//Send query
		$result = $this->send_query();

		if($result['success'] == "false"){
			return "There was an error with payment " . $result['errors']['error']['message'];
		}else{
			return $result;
		}

	}



	function transaction_capture($data) {
		$xml = new \SimpleXMLElement('<transaction />');
		$xml->addAttribute('xmlns','http://gateway.bankart.si/Schema/V2/Transaction');

		$xml->addChild("username",$this->bankart_api_username);
		$xml->addChild("password",$this->bankart_api_password);

		$capture = $xml->addChild("capture");
		$debit->addChild("transactionId",$data['transactionId']);
		$debit->addChild("referenceTransactionId",$data['referenceTransactionId']);

		$debit->addChild("amount",$data['amount']);
		$debit->addChild("currency",$data['currency']);


		$dom = dom_import_simplexml($xml)->ownerDocument;
		$dom->formatOutput = true;
	
		$this->xml = trim($dom->saveXML());

		//Send query
		$result = $this->send_query();

		if($result['success'] == "false"){
			return "There was an error with payment " . $result['errors']['error']['message'];
		}else{
			return $result;
		}

	}

	function setLogin($bankart_api_username,$bankart_api_password,$bankart_apikey,$bankart_sharedsecret) {
		$this->bankart_api_username 	= $bankart_api_username;
		$this->bankart_api_password 	= sha1($bankart_api_password);
		$this->bankart_apikey 			= $bankart_apikey;
		$this->bankart_sharedsecret 	= $bankart_sharedsecret;

	}
}


?>
