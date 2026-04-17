<?php
/*
	Licence processing code
	Copyright 2020 WooNinja (info@wooninja.si)

*/

namespace WooNinja;

require dirname(__FILE__) . '/../vendor/autoload.php';


use Endroid\QrCode\QrCode AS QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;

class WooUPNQR {

	/**
	 * Transliterate Slovenian/Croatian characters for banking system compatibility
	 * Even though ISO-8859-2 supports special characters, some banking systems don't handle them correctly
	 */
	private function transliterate($text) {
		$search = array('Č', 'Š', 'Ž', 'Đ', 'Ć', 'č', 'š', 'ž', 'đ', 'ć');
		$replace = array('C', 'S', 'Z', 'D', 'C', 'c', 's', 'z', 'd', 'c');
		return str_replace($search, $replace, $text);
	}

	function generateData($data){

		$content = '';

		foreach($data AS $key => $value){
			if($key != "random"){
				if(!empty($content)){
					$content .= "\n" . trim($value);
				}else{
					$content = $value;
				}
			}
		}

		$ctr = $this->generateControlNumber($content);

		$content = $content . "\n" . $ctr . "\n";

		return $content;

	}


	/*
	*
	* https://www.zbs-giz.si/placilni-promet/
	* Kontrolno vsoto izračunate tako, da seštejetedolžino vpisanih polj in ločil od vključno polja 1 do vključno polja 19.Kontrolna vsota = strlen(N1) + strlen(N2) + ... + strlen(N19)  + 19
	* Če je »Kontrolnavsota« manjša od 100, ji dodatevodilno ničlo. 
	* Npr. Ko je kontrolna vsota »123«, vpišete»123«. Koje kontrolna vsota »98«, vpišete»098«	
	*
	*/

	function generateControlNumber($input) {

		$controlnumber = 0;

		foreach(preg_split("/((\r?\n)|(\r\n?))/", $input) as $line){
			// Convert to ISO-8859-2 for consistent length calculation
			// Since Slovenian chars (ČŠŽ) are already transliterated to CSZ,
			// this mainly handles any remaining special characters
			$line_iso = iconv('UTF-8', 'ISO-8859-2//TRANSLIT', $line);
			$controlnumber = $controlnumber + strlen($line_iso);
		}

		$controlnumber = $controlnumber + 19;

		return sprintf("%03d", $controlnumber);
	}


	function getImage($data){

		// Transliterate Slovenian characters (ČŠŽ → CSZ) for banking compatibility
		$text_fields = array('placnik_ime', 'placnik_naslov', 'placnik_kraj',
							  'podjetje_ime', 'podjetje_naslov', 'podjetje_kraj', 'namen');
		foreach ($text_fields as $field) {
			if (isset($data[$field])) {
				$data[$field] = $this->transliterate($data[$field]);
			}
		}

		// Format amount: multiply by 100 (convert to cents) and pad to 11 digits
		// According to UPN-QR standard: amount must be in cents with leading zeros
		// Example: 24.99 EUR → 2499 cents → "00000002499"
		$amount_float = floatval(str_replace(',', '.', $data['znesek'])); // Handle both . and , as decimal separator
		$amount_cents = round($amount_float * 100); // Convert to cents and round
		$data['znesek'] = sprintf("%011d", $amount_cents);

		//Replace " " in trr
		$data['trr'] = str_replace(array(" "), array(""), $data['trr']);

		$content = $this->generateData($data);

		$qrCode = new QrCode($content);
		$qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::MEDIUM());
		$qrCode->setEncoding('ISO-8859-2');
		$qrCode->setValidateResult(false);
		$qrCode->setSize(200);
		$qrCode->setMargin(0);
		$qrCode->setRoundBlockSize(false);

		
		$qrCode->writeFile(dirname(__FILE__) . '/../tfpdf/download/qrcode-' . $data['random'] . '.png');
	}
}

?>