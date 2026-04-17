<?php
class UpayApi
{
    private $client_id;
    private $client_secret;
    private $base_url;
    private $access_token;
    private $params;
    private $url_api;
    public function __construct($client_id, $client_secret)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        
        $this->params = array(
            'grant_type' => 'client_credentials',
            'scope' => 'UPNPlusTraderApi',
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
        );

        if (get_option('uPayType') == 'uPayTest') {
            $this->base_url = 'https://test-id.upay.si';
            $this->url_api = 'https://test-api.upay.si/UPN';
        } else {
            $this->base_url = 'https://id.upay.si';
            $this->url_api = 'https://api.upay.si/UPN';
        }

        $this->ApiTokenEndpoint = $this->base_url."/realms/upay/protocol/openid-connect/token";
        

        $token_data = $this->getToken($this->ApiTokenEndpoint,$this->params);

        $this->access_token = $token_data->access_token;

    }

    private function getToken($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($this->params));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-type: application/x-www-form-urlencoded',
            "Authorization: Bearer " . $this->access_token,
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        $result = curl_exec($curl);

        if (!$result) {
            wp_die("Connection failed: ".curl_error($curl));
        }

        $json = json_decode($result);
        curl_close($curl);

        return $json;
    }


    public function callAPIupay($method, $url, $data, $access_token) {
        $curl = curl_init();
   
        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);
                if ($data) {
                    $data_json = json_encode($data);
               
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                if ($data) {
                    $data_json = json_encode($data);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, http_build_query($data));
                }
        }
    
        // OPTIONS:
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_NOBODY, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);

        // EXECUTE:
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
       
        if (!$result) {
            wp_die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
        }
        curl_close($curl);
        //return $result;
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $header = substr($result, 0, $header_size);
        $body = substr($result, $header_size);
 
        $headers = $this->get_headers_from_curl_response($result);

        curl_close($curl);

        $json = json_decode($body);
        return $json;
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


    public function getAccessToken(){
        return $this->access_token;
    }

    // Generiranje nove reference uPay naloga
    public function createNewUPNReference($generateRequest) {
        return $this->callAPIupay('POST', $this->url_api.'/CreateNewUPNReference', $generateRequest, $this->access_token);
    }

    public function getQRCodeByReference($reference) {
        $url = $this->url_api . '/REF/' . $reference . '/QR';
        return $this->callAPIupay('GET', $url, false, $this->access_token);
    }

    // Base niz QR kode in UPN obrazca naloga preko reference
    public function getQRBaseByReference($reference) {
        $url = $this->url_api . '/REF/' .$reference . '/QRBase64';
        return $this->callAPIupay('GET', $url, false, $this->access_token);
    }

    // Podatki o nalogu - STATUS NALOGA
    public function getStatus($guid_id) {
        $url = $this->url_api .$guid_id . '/Status';
        return $this->callAPIupay('GET', $url, false, $this->access_token);
    }

    // Vsi podatki naloga
    public function getAllData($guid_id) {
        $url = $this->url_api .$guid_id . '/All';
        return $this->callAPIupay('GET', $url, false, $this->access_token);
    }

    public function getAllDataByReference($reference) {
        $url = $this->url_api .'/REF/'.$reference . '/All';
        return $this->callAPIupay('GET', $url, false, $this->access_token);
    }

    // Spreminjanje statusa nalogov
    public function changeStatus($guid_id) {
        $url = $this->url_api . $guid_id . 'CloseUpn/43OE3I';
        return $this->callAPIupay('GET',$url, false, $this->access_token);
    }

}
