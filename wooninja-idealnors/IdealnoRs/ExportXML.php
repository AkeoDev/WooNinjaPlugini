<?php

namespace IdealnoRs;

use IdealnoRs\Hooks;
use \WP_Query;
use \DOMDocument;

if (!defined('ABSPATH')) exit;


/**
 * Export lass 
 */
class ExportXML{  

    // XML class
    public $xml = null;
    public $allproducts = [];
    public $export_type = '';
    public $lang = '';
    public $ddv = 0;
    public $clubdiscount = 0;
    public $deliverycost = 0;
    public $deliverymin = 0;
    public $deliverymax = 0;
    public $skip_macro_products = null;
    public $skip_macro_categories = null;
    public $enableutm = null;
    public $utmenabled = 'false';
    public $utm_source = '';
    public $utm_medium = '';
    public $utm_campaign = '';
    public $categories = [];

    // Main export class
    public function __construct()
    {

        $this->export_type = get_option('woo_idealnors_export_type', 'all');
        $this->lang = get_option('woo_idealnors_defaultlang', 'sl');
        $this->ddv = get_option('woo_idealnors_taxrate', 0);
        $this->clubdiscount = get_option('woo_idealnors_clubprice', 0);

        $this->deliverycost = get_option('woo_idealnors_delivery_price', 0);
        $this->deliverymin = get_option('woo_idealnors_delivery_min', 0);
        $this->deliverymax = get_option('woo_idealnors_delivery_max', 0);

        $this->categories = Hooks::getCenejeCategories($this->lang);

        // Check have we macro setting for products
        if(get_option('woo_idealnors_ignore_products', 'false') === 'true'){

            // Get ignored products string
            $this->skip_macro_products = preg_split('/\r\n|[\r\n]/', get_option('woo_idealnors_ignore_products_value', ''));

        }

        // Check have we macro setting for categories
        if(get_option('woo_idealnors_ignore_categories', 'false') === 'true'){

            // Get ignored products string
            $this->skip_macro_categories = preg_split('/\r\n|[\r\n]/', get_option('woo_idealnors_ignore_categories_value', ''));
            
        }

        // Get UTM params
        $this->utmenabled = get_option('woo_idealnors_export_utm', 'false');
        $this->utm_source = get_option('woo_idealnors_utm_source', '');
        $this->utm_medium = get_option('woo_idealnors_utm_medium', '');
        $this->utm_campaign = get_option('woo_idealnors_utm_campaign', '');

    }

    /**
     * getALLProducts
     *
     * Functions returns all Woo products
     * 
     * @return class
     */
    public static function getAllProducts(){

        // Woo query arguments
        $arguments = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
        ];
  
        // Get all wp products 
        $loop = new WP_Query($arguments);

        // Return class instance
        return $loop;
    }


    /**
     * getALLProducts
     *
     * Functions returns all Woo products
     * 
     * @return class
     */
    public static function getSpecificProducts(){

        // Woo query arguments
        $arguments = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'meta_query' => [
                [
                  'key' => 'woo_idealnors_field_exportproduct',
                  'value' => 'on'
                ]
            ],
        ];
  
        // Get all wp products 
        $loop = new WP_Query($arguments);

        // Return class instance
        return $loop;
    }

    /**
     * generateXML
     * 
     * Generate new XML from WooCommerce products
     * 
     * @return void
     */
    public function generateXML(){

        // Init products variable
        $products = null;
    
        // Switch on export type
        switch ($this->export_type) {

            case 'all':

                $products = self::getAllProducts();

                break;

            case 'specific':

                $products = self::getSpecificProducts();

                break;
        
            default:
                
                break;
        }


        // All of the error messages will be stored here
        $errorList = [];

        // Initialize class
        $xml = new DOMDocument('1.0', 'UTF-8');

        // Nice output formating
        $xml->formatOutput = true;

        // Create main root element
        $root = $xml->createElement('CNJExport');
      
        // Check if variable has passed switch
        if($products !== null){

            // Check do we have posts at all
            if ($products->have_posts()){

                // Map trought all products and fetch product info (referenced to $product)
                while($products->have_posts()){ 
                    
                    // Get product data
                    $products->the_post();

                    // Include product info holder variable
                    global $product;

                    // Create parent element for this product loop
                    $item = $xml->createElement('Item');

                    // Get product id 
                    $id = $product->get_id();

                    // Get post meta info
                    $meta = get_post_meta($id);

                    // Check if we have meta for this product
                    if($meta['woo_idealnors_field_exportproduct'] === NULL){

                        // Skip this product
                        continue;
                    }

                    // Check do we have ignore this product enabled
                    if(isset($meta['woo_idealnors_field_ignoreproduct']) && current($meta['woo_idealnors_field_ignoreproduct']) === 'on'){
                        
                        // Skip this product since ignore this product setting is set on product page
                        continue;
                    }



                    // Check do we have macro skip enabled for products
                    if(!is_null($this->skip_macro_products)){

                        // Check if macro skip is set in field
                        if(in_array((string)$id, $this->skip_macro_products)){

                            // Skip product
                            continue;
                        }
                    }

                    // Check do we have macro skip enabled for categories
                    if(!is_null($this->skip_macro_categories)){

                        // Check if macro skip is set in field
                        if(in_array(current($meta['woo_idealnors_field_categoryid']), $this->skip_macro_categories)){

                            // Skip product
                            continue;
                        }
                    }
                

                    // Append product ID with CDATA
                    $productelement = $item->appendChild($xml->createElement('ID'));
                    $productid = $xml->createCDATASection($id);
                    $productelement->appendChild($productid);

                    // Append product name
                    $name = $item->appendChild($xml->createElement('name'));
                    $productname = $xml->createCDATASection($product->get_name());
                    $name->appendChild($productname);

                    // Append description to the parent element
                    $description = $item->appendChild($xml->createElement('description'));
                    $description_text = $xml->createCDATASection('<span>' . $product->get_description() . '</span>');
                    $description->appendChild($description_text);

                    // Append specification NOTE: need to check this non default
                    $specification = $item->appendChild($xml->createElement('specification'));
                    $productspecification = $xml->createCDATASection('');
                    $specification->appendChild($productspecification);

                    // Append link to the parent element
                    $link = $item->appendChild($xml->createElement('link'));
                    $productlink = $xml->createCDATASection($this->generateUTM($product->get_permalink()));
                    $link->appendChild($productlink);

        
                    // Get post main image
                    $image = get_the_post_thumbnail_url($product->get_id());

                    // Check if returned value is bool or url value
                    if($image === false){

                        // Push error to the list, we cant continue without main image
                        array_push($errorList, sprintf(Config::$translates[10][$this->lang], (string)$id, 'Main image is missing'));

                        // Skip this loop
                        continue;
                    } 

                    // Append image to the parent element
                    $mainimage = $item->appendChild($xml->createElement('mainImage'));
                    $productimage = $xml->createCDATASection($image);
                    $mainimage->appendChild($productimage);


                    // Attachments for this products
                    $attachments = $product->get_gallery_image_ids();


                    // Hold attachment url temp.
                    $urls = [];

                    // Parse trough Woo attachments
                    foreach( $attachments as $attachment_id){

                        // Additional image url
                        $additionalimg = wp_get_attachment_url($attachment_id);

                        // Skip if img is not found
                        if($additionalimg === false) continue;

                        // Push url if valid
                        array_push($urls, $additionalimg);
                    }

                    
                    // Append image to the parent element
                    $addimages = $item->appendChild($xml->createElement('moreImages'));
                    $moreimagesvalue = $xml->createCDATASection(implode(',', $urls));
                    $addimages->appendChild($moreimagesvalue);

                    // Check meta for video
                    if(is_array($meta['woo_idealnors_field_video'])){
                        $videometa = current($meta['woo_idealnors_field_video']);
                    }else{
                        $videometa = '';
                    }

                    // Create video element
                    $video = $item->appendChild($xml->createElement('videoUrl'));
                    $productvideo = $xml->createCDATASection($videometa);
                    $video->appendChild($productvideo);


                    // Get price of the product without tax
                    $price_sale = floatval($product->get_price());
                    $price_regular = floatval($product->get_regular_price());

                    // Append current price (if sale this will be sale price, if not it will be === regular price)
                    $item->appendChild($xml->createElement('price', $price_sale));   
                   
                    // Append static price, sale will be compared to this
                    $item->appendChild($xml->createElement('regularPrice', $price_regular));   

                    // Append club price if percentage is set
                    $clubprice = $price_regular * (1 - floatval($this->clubdiscount) / 100);
                    $item->appendChild($xml->createElement('clubPrice', $clubprice));   
        
                    // Get store products currency
                    $item->appendChild($xml->createElement('curCode', get_woocommerce_currency()));   

                    // Stock text
                    $stocktext = '';
                    $stockvalue = '';

                    // Switch by default stock status
                    switch ($product->get_stock_status()) {

                        case 'instock':
                            
                                $stocktext = Config::$translates[11][$this->lang];
                                $stockvalue = 'in stock';
                            break;
                        
                        case 'outofstock':
                            
                                $stocktext = Config::$translates[13][$this->lang];
                                $stockvalue = 'out of stock';
                            break;

                        case 'onbackorder':

                                $stocktext = Config::$translates[12][$this->lang];
                                $stockvalue = 'preorder';

                            break;
                        
                        default:
                            $stocktext = 'Na zalogi';
                            break;
                    }


                    // Append stock text
                    $item->appendChild($xml->createElement('stockText', $stocktext));   

                    // Append ceneje required stock text
                    $item->appendChild($xml->createElement('stock', $stockvalue));

                    // Append store physical location
                    $store = $xml->createElement('inStoreAvailability');

                    // Format address string 
                    $addressdata = $xml->createCDATASection(WC()->countries->get_base_address(). ', '.  (WC()->countries->get_base_address_2() !== "" ? WC()->countries->get_base_address_2() . ', ' : "" ). ' ' .   WC()->countries->get_base_postcode() . ' ' .   WC()->countries->get_base_city());
                    $storeadress = $xml->createElement('store');
                    $storeadress->appendChild($addressdata);

                    $stock = 0;

                    /**
                     * There are 2 types of stock information
                     * 
                     * First is with stock number + stock info text
                     * Second is without stock number but it has stock info text
                     * 
                     * So we will check below both types 
                     */ 


                    // Check if this 
                    if(is_int($product->get_stock_quantity())){

                        // Set stock value when there is real number
                        $stock = $product->get_stock_quantity(); 
                    }else{

                        // Check status is real out of stock but without quantity number
                        if($stockvalue === 'out of stock'){

                            // Set 0 since its out of stock
                            $stock = 0;
                        }else{
                            
                            // Set 5 as a fake number since we dont know how much inventory does have on stock
                            $stock = 5;
                        }
                    }

                    // Append store address
                    $store->appendChild($storeadress);
                    $store->appendChild($xml->createElement('availability', 'today'));
                    $store->appendChild($xml->createElement('quantity', $stock));


                    // Append this child with other children
                    $item->appendChild($store);

                    // Skip if this is not an array
                    if(!is_array($meta['woo_idealnors_field_categoryid'])) continue;

                    // Category string for this products 
                    $idcat = current($meta['woo_idealnors_field_categoryid']);

                    // Check category info
                    $found = $this->findCategory($idcat);

                    // Check if we have found result (empty array is not found)
                    if(count($found) > 0){

                        // Split string by delimeter ->
                        $catparts = explode(' -> ', $found[5]);

                        // Remove empty cat (L0 usually)
                        if($catparts[0] === '-') array_shift($catparts);

                        // Stick it together again with the normal accepted delimeter 
                        $catstring = implode(' - ', $catparts);

        
                    }else{

                        // Skip product, this is required info
                        continue;
                    }
 

                    // Append ceneje required stock text
                    $category = $item->appendChild($xml->createElement('fileUnder'));
                    $prodcategory = $xml->createCDATASection($catstring);
                    $category->appendChild($prodcategory);

                    // Append ceneje category id NOTE: need to fix this
                    $item->appendChild($xml->createElement('cenCategoryId', $meta['woo_idealnors_field_categoryid'] ? current($meta['woo_idealnors_field_categoryid']) : ''));

                    // Check meta for brand
                    if(is_array($meta['woo_idealnors_field_brand'])){
                        $brandmeta = current($meta['woo_idealnors_field_brand']);
                    }else{
                        $brandmeta = '';
                    }
                    
                    // Append brand name to the list
                    $brand = $item->appendChild($xml->createElement('brand'));
                    $prodbrand = $xml->createCDATASection($brandmeta);
                    $brand->appendChild($prodbrand);

                    // Check meta for EAN
                    if(is_array($meta['woo_idealnors_field_ean'])){
                        $eanmeta = current($meta['woo_idealnors_field_ean']);
                    }else{
                        $eanmeta = '';
                    }

                    // Append EAN code
                    $ean = $item->appendChild($xml->createElement('EAN'));
                    $prodean = $xml->createCDATASection($eanmeta);
                    $ean->appendChild($prodean);

                    // Check meta for CUIN
                    if(is_array($meta['woo_idealnors_field_cuin'])){
                        $cuinmeta = current($meta['woo_idealnors_field_cuin']);
                    }else{
                        $cuinmeta = '';
                    }

                    // Append CUIN code (ceneje unique id)
                    $cuin = $item->appendChild($xml->createElement('CUIN'));
                    $prodcuin = $xml->createCDATASection($cuinmeta);
                    $cuin->appendChild($prodcuin);

                    // Append product code (optional)
                    $code = $item->appendChild($xml->createElement('productCode'));
                    $prodcode = $xml->createCDATASection('');
                    $code->appendChild($prodcode);

                    // Append product model (optional)
                    $model = $item->appendChild($xml->createElement('productModel'));
                    $prodmodel = $xml->createCDATASection('');
                    $model->appendChild($prodmodel);

                    // Check meta for condition
                    if(is_array($meta['woo_idealnors_field_condition'])){
                        $conditionmeta = current($meta['woo_idealnors_field_condition']);
                    }else{
                        $conditionmeta = '';
                    }

                    // Append product condition
                    $cond = $item->appendChild($xml->createElement('condition'));
                    $prodcond = $xml->createCDATASection($conditionmeta);
                    $cond->appendChild($prodcond);

                    // Check meta for warranty
                    if(is_array($meta['woo_idealnors_field_warranty'])){
                        $warrantymeta = current($meta['woo_idealnors_field_warranty']);
                    }else{
                        $warrantymeta = '';
                    }

                    // Append product warranty
                    $warranty = $item->appendChild($xml->createElement('warranty'));
                    $prodwarranty = $xml->createCDATASection($warrantymeta);
                    $warranty->appendChild($prodwarranty);

                    // Create coupon holders
                    $coupon = '';
                    $coupon_value = '';

                    // Get coupon info
                    $couponres = $this->getCoupon($id);

                    // Check do we have coupons for this product
                    if($couponres !== false){

                        // Found coupon, assign values
                        $coupon = $couponres['coupon_name'];
                        $coupon_value = $couponres['coupon_value'];

                    }

                    // Append product coupon text
                    $coupontext = $item->appendChild($xml->createElement('coupon'));
                    $coupontextval = $xml->createCDATASection($coupon);
                    $coupontext->appendChild($coupontextval);

                    // Append product coupon code value
                    $couponvalue = $item->appendChild($xml->createElement('couponCode'));
                    $couponcodevalue = $xml->createCDATASection($coupon_value);
                    $couponvalue->appendChild($couponcodevalue);

                    
                    // Check meta for gift
                    if(is_array($meta['woo_idealnors_field_gift'])){
                        $giftmeta = current($meta['woo_idealnors_field_gift']);
                    }else{
                        $giftmeta = '';
                    }


                    // Append gift element
                    $gift = $item->appendChild($xml->createElement('gift'));
                    $productgift = $xml->createCDATASection($giftmeta);
                    $gift->appendChild($productgift);

                    // Append price for delivery element
                    $item->appendChild($xml->createElement('deliveryCost', $this->deliverycost));
                
                    // Append min delivery time neeeded
                    $item->appendChild($xml->createElement('deliveryTimeMin', $this->deliverymin));

                    // Append max delivery time neeeded
                    $item->appendChild($xml->createElement('deliveryTimeMax', $this->deliverymax));
   
                    // Append group id 
                    $item->appendChild($xml->createElement('groupId'));

                    // Create atrributes parent
                    $attributes = $xml->createElement('attributes');

                    // Check meta for gender
                    if(is_array($meta['woo_idealnors_field_gender'])){
                        $gendermeta = current($meta['woo_idealnors_field_gender']);
                    }else{
                        $gendermeta = '';
                    }

                    // Append gender element to the attributes
                    $attributes->appendChild($xml->createElement('gender', $gendermeta));

                    // Check meta for color
                    if(is_array($meta['woo_idealnors_field_color'])){
                        $colormeta = current($meta['woo_idealnors_field_color']);
                    }else{
                        $colormeta = '';
                    }
                    
                    // Append color element to the attributes
                    $color = $xml->createElement('color');
                    $colorval = $xml->createCDATASection($colormeta);
                    $color->appendChild($colorval);
                    $attributes->appendChild($color);

                    // Check meta for size
                    if(is_array($meta['woo_idealnors_field_size'])){
                        $sizemeta = current($meta['woo_idealnors_field_size']);
                    }else{
                        $sizemeta = '';
                    }
                    
                    // Append size element to the attributes
                    $attributes->appendChild($xml->createElement('size', $sizemeta));

                    // Check meta for size
                    if(is_array($meta['woo_idealnors_field_agegroup'])){
                        $agemeta = current($meta['woo_idealnors_field_agegroup']);
                    }else{
                        $agemeta = '';
                    }
                    
                    // Append age element to the attributes
                    $attributes->appendChild($xml->createElement('ageGroup', $agemeta));

                    // Get products atrributes
                    foreach($this->getAttributes($product) as $attr){

                        // Create attribute element
                        $attributexml = $xml->createElement('attribute');

            
                        // Create name of the attribute
                        $namevalue = $xml->createCDATASection($attr[0]);
                        $name = $xml->createElement('name');
                        $name->appendChild($namevalue);

                        // Append name element to the attribute
                        $attributexml->appendChild($name);

                        // Create XML values element
                        $attributevaluesxml = $xml->createElement('values');

                        // Add each attribute value to the XML
                        foreach($attr[1] as $attribute_value){

                            // Append this attribute values
                            $term = $xml->createCDATASection($attribute_value);
                            $termvalue = $xml->createElement('value');
                            $termvalue->appendChild($term);
                            $attributevaluesxml->appendChild($termvalue);

                        }

                        // Append atribute values to the attribute
                        $attributexml->appendChild($attributevaluesxml); 
                        
                        // Append specific attribute
                        $attributes->appendChild($attributexml);
                    }


                    // Finnaly append whole thing to the product
                    $item->appendChild($attributes);

                    $root->appendChild($item);

                }
            }
        }

        // Append whole root to the dom element
        $xml->appendChild($root);

        // Craft path for save of xml file
        $path = get_home_path() .'/wp-content/uploads/' . Config::$filename;

        // Save it
        $xml->save($path);
           
    }

    /**
     * getCoupon
     * 
     * Find generated coupon for specific product id
     *
     * @param [int] $id
     * @return void
     */
    private function getCoupon($id){

        // get all coupons that are published 
        $coupons = get_posts(array(
            'posts_per_page'   => -1,
            'post_type'        => 'shop_coupon',
            'post_status'      => 'publish',
        ));

        // loop through the coupons
        foreach($coupons as $coupon){

            // get the product ids meta value
            $product_ids = get_post_meta($coupon->ID, 'product_ids', true);

            // make sure something has been saved
            if (!empty($product_ids)){

                // convert from comma separated string to array
                $id_list = explode(',', $product_ids);

                // loop over each ID
                foreach($id_list as $product_id){

                    // get the product for each ID
                    $product = get_post($product_id);

                    // Check if this is our product
                    if($product->ID === $id){

                        // Return coupon values if found
                        return [
                            'coupon_name' => $coupon->post_title,
                            'coupon_value' => $coupon->post_excerpt
                        ];  
                    }
                }
            }
        }

        return false;
    }


    /**
     * getAttributes
     * 
     * Returns list of the attributes
     *
     * @param [Object] $product
     * @return array
     */
    private function getAttributes($product){

        // Get all atrributes from this product
        $attributes = $product->get_attributes();

        // Final returned array
        $final = [];

        // Check do we have any attributes
        if(!is_string($attributes) && count($attributes) > 0){

            // Map trough all attributes
            foreach($attributes as $key => $attr){

                // Woo has 2 type of attributes temporary and they have 0 id and permanent > 0 id
                $id = $attr->get_id();

            
                // This is permanent id, so attribute can have multiple values
                if($id > 0){

                    // This will get us attribute selected values
                    $terms = $attr->get_terms();

                    // Create temp holder
                    $tempTerms = [];

                    // Check if we got results or null 
                    if(!is_null($terms)){
                        
                        // Map trough terms
                        foreach($terms as $term){

                            // Push term name to the temp array
                            array_push($tempTerms, $term->name); 
                        }
                    }

                    // Get this attribute
                    $wcattr = wc_get_attribute($id);

                    // Push final holder of attributes
                    array_push($final, [$wcattr->name, $tempTerms]);

                }else{

                    // Temp can only have one attribute term so we will hard code this
                    $name = $attr->get_name();
                    $value = $attr->get_options()[0];

                    // Push values to the list
                    array_push($final, [$name, [$value]]);
                }   
            }

            return $final;
        }

        // Return empty if products were not found
        return [];
    }

    /**
     * findCategory
     * 
     * Finds category id and returns array with category info
     *
     * @return array
     */
    private function findCategory($catid){

        // Map trough categories
        foreach($this->categories as $key => $categoryline){

            // Skip first line
            if($key === 0) continue;

            // Check if given category matches selected category id
            if($catid === $categoryline[3]){

                // If yes returns this line
                return $categoryline;
            }
        }

        // Nothing found return empty array
        return [];
    }


    /**
     * generateUTM
     *
     * Generate UTM parameters and return final link
     * 
     * @param string $url
     * @return string
     */
    private function generateUTM($url){

        // Check if UTM is enabled
        if($this->utmenabled === 'true'){

            // State holdere
            $firstQuery = false;

            // Check if url string contains first param before
            if(strpos($url, '?') !== false) {
                
                // Found param already
                $firstQuery = true;
            }

    
            if($this->utm_source !== ''){

                // Craft string
                $url .= ($firstQuery ? '&' : '?') . 'utm_source=' . $this->utm_source;

                // Set first query to true
                $firstQuery = true;
            }

            if($this->utm_medium !== ''){

                $url .= ($firstQuery ? '&' : '?') . 'utm_medium=' . $this->utm_medium;

                // Set first query to true if this is first element and source is skipped
                $firstQuery = true;
            }


            // Check if campaign is set
            if($this->utm_campaign !== ''){

                $url .= ($firstQuery ? '&' : '?') . 'utm_campaign=' . $this->utm_campaign;
            }


            // Return URL string to the 
            return $url;
        }

        // Return original URL if not enabled
        return $url;

    }
}
