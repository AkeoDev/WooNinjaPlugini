<?php
global $APIBaseUrl;


class minimaxAPI {
    public $Lokalizacija = "SI";
    public $ApiTokenEndpoint;
    public $APIBaseUrl;
    public $access_token;
    public $org_id;
    public $get_data;
    public $countryCode;
    public $params;
    public $warehouse;
    public $itemId;
    public $zaloga;
    public $country_id;
    public $country_api;
    public $date;
    public $date_api;
    public $url;
    public $data;
    public $DateIssued;
    public $order;
    public $podatki_json;
    public $podatki;

    public $vatRateId;
    public $vatPercent;
    public $currencyCode;
    public $currencyId;
    public $vatRateCode;
    public $IRreportTemplateCode;
    public $DOreportTemplateCode;
    public $countryId;
    public $IRreportTemplateId;
    public $DOreportTemplateId;
    public $itemCode;
    public $customerId;
    public $post_item_info;
    public $postItem;
    public $podatki_item;
    public $token_data;
    public $authead;
    public $hide_block;
 

    function __construct($client_id, $client_secret,$username, $password, $testLogin = false) { 
    
    
        $this->params = array(
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'password',
            'username'      => $username,
            'password'      => $password,
            'scope'         => 'minimax.si'
        );
    
        $this->ApiTokenEndpoint = "https://moj.minimax.".$this->Lokalizacija."/".$this->Lokalizacija."/AUT/oauth20/token";
        $this->APIBaseUrl = "https://moj.minimax.".$this->Lokalizacija."/".$this->Lokalizacija."/API/";

        $token_data = '';
        if($testLogin) {
        $token_data = $this->getToken($this->ApiTokenEndpoint,$this->params);
        $this->access_token = @$token_data->access_token;
        $this->authead = $this->access_token ?? false;
        return;
        } else {


        $token_data = $this->getToken($this->ApiTokenEndpoint,$this->params);
        $this->access_token = @$token_data->access_token;

        
        $this->get_data = $this->callAPI('GET', $this->APIBaseUrl . 'api/currentuser/orgs', false, $this->access_token);
        
        if (get_option('MiniMax_invoice_organizacija')) {
            $this->org_id = get_option('MiniMax_invoice_organizacija');

        } else {
            $this->org_id = $this->get_data->Rows[0]->Organisation->ID;
        }

        global $hide_block;
        if (!is_null($this->org_id)) {

            if (get_option('MiniMax_invoice_organizacija')) {
                $this->org_id = get_option('MiniMax_invoice_organizacija');

            } else {

                $this->org_id = $this->get_data->Rows[0]->Organisation->ID;
            }
            $hide_block = "";  
            } else {
                echo '<p style="color: red;">Pri povezovanju z MiniMaxom je prišlo do napake.</p>';
                $hide_block = "hide_settings";
        }
       
		$račun = $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/issuedinvoices',false, $this->access_token);

		$vatRateCode = "S";
		$date = "2024-03-03";
		$currencyCode = "EUR";
	
		$get_data_var = $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/vatrates/code(' .$vatRateCode .')?date=' .$date, false, $this->access_token);

		$get_dataa = $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/currencies/code(' .$currencyCode .')', false, $this->access_token);
        
    }
    }

 
    public function getToken($url)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->params));
        curl_setopt($curl, CURLOPT_URL, $url);

        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/x-www-form-urlencoded'
        ));
            
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        //curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        
        // EXECUTE:
        $result = curl_exec($curl);

        if (!$result)
        {
            die("Povezava ni uspela");
        }
    
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $json = json_decode($body);
        
        curl_close($curl);
        
        return $json;
    }

 

    public function callAPI($method, $url, $data, $access_token)
    {

        $curl = curl_init();
        switch ($method)
        {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                break;
            default:
                if ($data) $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);

        $authorization = "Authorization: Bearer " . $access_token; // Prepare the authorisation token
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
            'Accept: application/json',
            $authorization
        )); // Inject the token into the header
        

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        // EXECUTE:
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        if ($http_code === 404) {
            return '';
        }
        if (isset($error_msg)) {
            // TODO - Handle cURL error accordingly
            
            die("Connection Failure");
        }

        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);

        $headers = $this->get_headers_from_curl_response($result);	
 
       
        curl_close($curl);
        
        if ($http_code >= 400) {
            // Create an array with error information
            $error_response = [
                'success' => false,
                'error' => "HTTP error with status code: $http_code",
                'response' => json_decode($body, true) // Decode body to PHP array
            ];
        
            // Return a JSON encoded string of the error response
            return json_encode($error_response);
        }

        if($method == "POST" AND $http_code == 201)
        {

            if(preg_match("/^Location:\s*(.*)$/mi", $header, $matches))
            {
     
                $locationurl = trim($matches[1]);
                //print $locationurl .PHP_EOL;
    
                $get_data = $this->callAPI('GET', $locationurl, false, $access_token);

                return $get_data;

            }
            return "";
        }
        else
        {
            $json = json_decode($body);
            //_r($json);
            return $json;
        }
    }



    public function get_headers_from_curl_response($response)
    {
        $headers = array();
    
        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));
 
        foreach (explode("\r\n", $header_text) as $i => $line)
            if ($i === 0)
                $headers['http_code'] = $line;
            else
            {
                list ($key, $value) = explode(': ', $line);
    
                $headers[$key] = $value;
            }
    
        return $headers;

    }


    // UPDATE STOCK ENTRY
      function updateStockEntry($update_stock_new) {
        return $this->callAPI('PUT', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stockentry/'.$update_stock_new, false, $this->access_token);
    }  

     function GetStockEntry($stockEntryId) {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stockentry',$stockEntryId,false, $this->access_token);
    } 
    // GET STOCK ENTRY

    function stockentry() {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stockentry', false, $this->access_token);
    }

    // ORGANIZATION
    function organization() {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id, false, $this->access_token);
    }

    // SEND STOCK ITEMS ON INVOICE CREATE

    function send_stock_item($podatki_stock) {

        return $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stockentry',$podatki_stock, $this->access_token);
    }
    // GET STOCK FOR ITEM //
    
     function getStockForItem($minimax_id) {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stocks/'.$minimax_id, false, $this->access_token);
      }

          // GET STOCK FOR ITEM //
    
     function getStockForItem_all() {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stocks', false, $this->access_token);
      }

      
    /// ŠTEVILČENJE ///
    function getStevilcenje() {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/document-numbering',false, $this->access_token);

        }

    /// SKLADIŠČE ///


    function getSkladisca() {
        

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/'.$this->org_id.'/warehouses', false, $this->access_token);
        
        }
        

function getDDV($date,$country_id) {

    return $this->callAPI('GET', "{$this->APIBaseUrl}api/orgs/{$this->org_id}/vatrates/?date={$date}&countryID={$country_id}", false, $this->access_token);
}

    /// DRŽAVE ///

    function getDrzave() {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/countries', false, $this->access_token);

    }
    function getCountryByCode($countryCode) {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/countries/code('.$countryCode.')', false, $this->access_token);
    }

    /// NAČIN PLAČILA ///

    function getNacinPlacila() {

        return $this->callAPI('GET',$this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/paymentMethods', false, $this->access_token);
    }

    function get_all_items() {

        return $this->callAPI('GET',$this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items', false, $this->access_token);
    }

    /// ITEM ///

    public function posttItems($item_data) {
        
        return  $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items', $item_data, $this->access_token);

    }
    
    public function create_customer($podatki) 
    {
        return $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/customers',$podatki, $this->access_token);
    }

    public function get_customer() {
        
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/customers',false, $this->access_token);
    }

     function get_IssuedInvoice($invoice_data) {
        return $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/issuedinvoices',$invoice_data, $this->access_token);
    } 

    

    // Get report-templates
    public function get_report_templates() {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/report-templates',false, $this->access_token);
    }

    // Get vat rate ID
    public function vatRate($date,$vatRateCode) {
       
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/vatrates/code(' .$vatRateCode .')?date=' .$date, false, $this->access_token);
    }
    public function get_items($jsonStr) {
    return $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items', $jsonStr, $this->access_token);
    }

    public function update_items($itemId,$update_data) {
        return $this->callAPI('PUT', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items/'. $itemId , $update_data, $this->access_token);
        }

    public function get_data_currency() {
        $currencyCode = "EUR";
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/currencies/code(' .$currencyCode .')', false, $this->access_token);
    }

    public function get_report_template_id() {
        $IRreportTemplateCode = "IR";
        $date = "2022-03-03";
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/report-templates?SearchString=' .$IRreportTemplateCode .'&PageSize=100' .$date, false, $this->access_token);
    }

    public function DOreportTemplateId() 
    {
        $date = "2022-03-03";
        $DOreportTemplateCode = "DO";
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/report-templates?SearchString=' .$DOreportTemplateCode .'&PageSize=100' .$date, false, $this->access_token);
    }

    public function check_code($hash) 
    {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items/code('. $hash . ')', false, $this->access_token);
    }

    public function customer_by_code($customerCode) {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/customers/code('. $customerCode . ')', false, $this->access_token);
    }

    // ANALYTUCS

    public function analytics() {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/analytics', false, $this->access_token);
    }

    public function analyticsByID($analyticId) {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/analytics/'. $analyticId , false, $this->access_token);
    }

    // STOCK

    public function stock() {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stocks', false, $this->access_token);
    }

    public function stockItem($itemId) {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/stocks/'. $itemId, false, $this->access_token);
    }


    // UPDATE CUSTOMER

    public function update_customer($customerId,$update_customer_data) {
        return $this->callAPI('PUT', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/customers/'. $customerId , $update_customer_data, $this->access_token);
        }

    // GET EMPLOYEES NEW
    public function GetEmployees() {

        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/employees', false, $this->access_token);
    }

    /**
     * 
     * get_employees
     * 
     * Search for an employee by name
     *
     * @param string $name
     * @return mixed
     */
    public function get_employees($name)
    {
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/employees?SearchString=' .$name .'&PageSize=100', false, $this->access_token);
    }

    /**
     * create_employee
     * 
     * Create a new employee on Minimax
     *
     * @param string $data
     * @return mixed
     */
    public function create_employee($data)
    {
        return $this->callAPI('POST', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/employees', $data, $this->access_token);
    }

    // PDF
    public function invoice_pdf($issuedInvoiceId,$rowVersion) {
       // $issuedInvoiceId = $send->IssuedInvoiceId;
       return $this->callAPI('PUT', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/issuedinvoices/' .$issuedInvoiceId .'/actions/'."IssueAndGeneratePdf".'?rowVersion='.$rowVersion,false, $this->access_token);
  } 
    // IZDAJ RAČUN
    public function CustomActionIssuedInvoice($issuedInvoiceId,$rowVersion) {
        // $issuedInvoiceId = $send->IssuedInvoiceId;
        return $this->callAPI('PUT', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/issuedinvoices/' .$issuedInvoiceId .'/actions/'."issue".'?rowVersion='.$rowVersion,false, $this->access_token);
    } 

  public function getGeneralVatRate($country_id,$date,$code) {
 
        return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/'. $this->org_id . '/vatrates/code(' . $code . ')?date=' . $date . '&countryID='.$country_id,false, $this->access_token);
  
  }
  public function getVatRate($country_code) {
     
    return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/'. $this->org_id . '/vatrates?date='.date('Y-m-d').'&countryID='.$country_code.'',false, $this->access_token);
  }
  

  public function getNonTaxableRate() {

    return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/vatrates/code(N)?date=' . date('c'),false, $this->access_token);

  }

  public function GetCurrencies($currencyCode) {

  return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/currencies/code(' .$currencyCode .')', false, $this->access_token);
  }

  public function GetItemsSettings() {

    return $this->callAPI('GET', $this->APIBaseUrl . 'api/orgs/' .$this->org_id .'/items/settings', false, $this->access_token);
    }

}

?>
