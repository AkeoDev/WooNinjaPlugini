<?php

declare(strict_types=1);

require dirname(__FILE__) . '/../src/vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use MpApiClient\Brand\BrandClient;
use MpApiClient\Common\Authenticators\ClientIdAuthenticator;
use MpApiClient\MpApiClient;
use MpApiClient\MpApiClientOptions;
use MpApiClient\Category\CategoryClient;
use MpApiClient\Category\Entity\Parameter;
//$clien_id = 'c25740ce70818433e16f5d17148ef2e4f700115eb1d7ec9b491bad6793e5de22';

class MyApiClass {
    public $categoryClient;
    public $brand;
    public $params;

    public function __construct() {
        $clientId = get_option('mimovrste_clienID');

        if ($clientId) {
            // Initialize request authenticator
            $authenticator = new ClientIdAuthenticator($clientId);

            // Create CurlHandler stack and Guzzle http client
            $handler = new CurlHandler();
            $handlerStack = HandlerStack::create($handler);
            $handlerStack->push($authenticator->getHandler());

            $options = new MpApiClientOptions($authenticator);
            $options->setTimeout(30);

            $httpClient = new Client($options->getGuzzleOptionsArray());

            // Create CategoryClient and BrandClient
            $this->categoryClient = new CategoryClient($httpClient, 'my-app-name');
            $this->brand = new BrandClient($httpClient, 'my-app-name');
        }
    }

    public function getCategoryList() {
        if ($this->categoryClient) {
            return $this->categoryClient->list();
        }

        return null; // or handle this case appropriately
    }

  /*   public function getCategoryDetails($categoryId) {
        if ($this->categoryClient) {
            return $this->categoryClient->getDetails($categoryId);
        }

        return null; // or handle this case appropriately
    } */
    public function getCategoryParameters($categoryId) {
        $cachedParams = get_transient('cached_category_params_' . $categoryId);
    
        if (false === $cachedParams) {
            if ($this->categoryClient) {
                $cachedParams = $this->categoryClient->getParameters($categoryId);
                set_transient('cached_category_params_' . $categoryId, $cachedParams, 3600); // Cache for 1 hour
            } else {
                return null; // or handle this case appropriately
            }
        }
    
        return $cachedParams;
    }
    

    public function getBrand() {
        if ($this->brand) {
            return $this->brand->list();
        }
        return null; // or handle this case appropriately
    }

/*     public function getBrand() {
        if ($this->brand) {
            return $this->brand->list();
        }
        return null; // or handle this case appropriately
    } */
}

?>