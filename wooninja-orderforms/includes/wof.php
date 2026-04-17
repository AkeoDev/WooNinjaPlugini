<?php

class WOF {

	const wof_post_type = 'wof';


	private $id;
	private $name;
	private $title;
	private $content;
	private $content_css;
	private $wof_redirect;
	private $custom_redirect;
	private $wof_products;


	static $found_forms = 0;
	private static $current = null;

	private function __construct( $post = null ) {
		$post = get_post( $post );

		if ( $post && self::wof_post_type == get_post_type( $post ) ) {
			$this->id = $post->ID;
			$this->name = $post->post_name;
			$this->title = $post->post_title;
			$this->content = $post->post_content;

			/// Meta

			$this->content_css     = get_post_meta ( $this->id, 'content_css' , true );

			$this->wof_redirect    = get_post_meta ( $this->id, 'wof_redirect' , true );
			$this->custom_redirect = get_post_meta ( $this->id, 'custom_redirect' , true );
			$this->wof_products    = get_post_meta ( $this->id, 'wof_products' , true );

			$this->language    	   = get_post_meta ( $this->id, 'language' , true );

		}

	}

	public static function counter() {
		return self::$found_forms;
	}


	public static function register_post_type() {
		register_post_type( self::wof_post_type, array(
			'labels' => array(
				'name' => __( 'Order forms', 'wof-sm' ),
				'singular_name' => __( 'Order form', 'wof-sm' ) ),
			'rewrite' => false,
			'query_var' => false ) );
	}

	public static function fetch_data( $arguments = '' ) {
		$defaults = array(
			'post_status' => 'any',
			'posts_per_page' => -1,
			'offset' => 0,
			'orderby' => 'ID',
			'order' => 'ASC' );

		$arguments = wp_parse_args( $arguments, $defaults );

		$arguments['post_type'] = self::wof_post_type;

		$query = new WP_Query();
		$posts = $query->query( $arguments );

		self::$found_forms = $query->found_posts;

		$objekti = array();

		foreach ( $posts as $post ) {
			$objekti[] = new self( $post );
		}

		return $objekti;
	}

	public function id() {
		return $this->id;
	}

	public function name() {
		return $this->name;
	}

	public function title() {
		return $this->title;
	}

	public function content() {
		return $this->content;
	}

	public function content_css() {
		return $this->content_css;
	}

	public function wof_redirect() {
		return $this->wof_redirect;
	}

	public function custom_redirect() {
		return $this->custom_redirect;
	}

	public function wof_products() {
		$array = unserialize($this->wof_products);
		if ( empty($array) ) {
			$array = array();
		}
		return $array;
	}

	public function language() {
		return $this->language;
	}

	public function get_shortcode( ) {
		$title = str_replace( array( '"', '[', ']' ), '', $this->title );
		$shortcode = sprintf( '[wof id="%1$d" title="%2$s"]', $this->id, $title );
		return $shortcode;
	}

	public static function get_current() {
		return self::$current;
	}

	public static function get_instance( $post ) {
		$post = get_post( $post );

		if ( ! $post || self::wof_post_type != get_post_type( $post ) ) {
			return false;
		}

		$form = new self( $post );

		self::$current = $form;

		return $form;
	}

	public static function parseContent( $post ){ 


		/// PRODUCT CHOOSER
		$select = "<select name='product_id' id='sm-product-select' class='sm-product-select'>";

		$products = $post->wof_products();

		$sorter = array();


		foreach ( $products as $key => $value) {

			/// Check if we have multilanguage
		   if(function_exists('icl_object_id')) {
		   	$translate = icl_object_id( $value, "product", true, $post->language() );
		   	$product = wc_get_product( $translate );
		    $product_title = get_the_title( $translate );
		    $value = ( $translate );
		   } else {
		   	$product = wc_get_product( $value );
		    $product_title = $product->get_title();
		   }

		   $sorter[] = array( "title" => $product_title, "id" => $value );


		}

		sort($sorter);


		foreach ( $sorter as $key => $value) {
			
			/// Check if we have multilanguage
		   if(function_exists('icl_object_id')) {
		   	$translate = icl_object_id( $value["id"], "product", true, $post->language() );
		   	$product = wc_get_product( $translate );
		    $product_title = get_the_title( $translate );
		    $value = ( $translate );
		   } else {
		   	$value = $value["id"];
		   	$product = wc_get_product( $value );
		    $product_title = $product->get_title();
		   }
		   $select .= "<option value='" . $value . "'> " . $product_title . " - " . wc_price( $product->get_price() ) . " </option>";

		}


		$select .= "</select>";

		if ( isset($_GET["product_id"]) ) {
			$product_id = $_GET["product_id"];
		} else {
			if ( !empty($products) ) {
				$product_id = $products[0];
			} else {
				$product_id = "0";
			}
		}

		$hidden_input = "<input type='hidden' name='product_id' value='" . $product_id . "' />";
		/// PRODUCT CHOOSER END


		/// PAYMENT OPTIONS
		$payment_options = '<ul class="sm-payment-methods">';
			if ( $available_gateways = WC()->payment_gateways->get_available_payment_gateways() ) {
				// Chosen Method
				if ( sizeof( $available_gateways ) )
				current( $available_gateways )->set_current();
					foreach ( $available_gateways as $gateway ) {
						$payment_options .= '<li class="alphaw payment_method_' . $gateway->id . '">
							<input id="payment_method_' . $gateway->id . '" type="radio" 
							class="sm-payment-method input-radio" 
							name="payment_method" 
							value="'. esc_attr( $gateway->id ) .'" 
							checked
							/>
							<label for="payment_method_' . $gateway->id . '">' . $gateway->get_title() . '</label>
						</li>';
					}
			}
		$payment_options .= '</ul>';
		/// PAYMENT OPTIONS END


	    $parse = array( 
	        /// PRODUCT CHOOSER
	        '[product-select]'     =>   $select, 
	        '[product-single]'	   =>   $hidden_input,

	        /// PRODUCT CONTENT

	        /// Payment options
	        '[payment-options]'	   =>   $payment_options,

	    ); 
	    return str_ireplace(array_keys($parse),$parse,$post->content()); 
	} 

	public static function save_form( $post ) {

		  $update = array(
		      'ID'           => $post->id(),
		      'post_title'   => $_POST["post_title"],
		      'post_content' => $_POST["wof_content"],
		      'post_status' => 'publish',
		  );

		// Update the post in database
		$result =  wp_update_post( $update );

		update_post_meta( $post->id(), 'content_css', $_POST["wof_content_css"] ); 

		update_post_meta( $post->id(), 'wof_redirect', $_POST["wof_redirect"] ); 
		update_post_meta( $post->id(), 'custom_redirect', $_POST["custom_redirect"] ); 
		update_post_meta( $post->id(), 'wof_products', serialize( $_POST["wof_products"] ) ); 
		/// Multilanguage
		if(function_exists('icl_object_id')) {
			update_post_meta( $post->id(), 'language', $_POST["language"] ); 
		}
		
	}

	public static function create_form( ) {

		  $insert = array(
		  	  'post_type'    => "wof",
		      'post_title'   => $_POST["post_title"],
		      'post_content' => $_POST["wof_content"],
		      'post_status'  => 'publish',
		  );


		// Insert the post into the database
		$result =  wp_insert_post( $insert );

		if ( $result ) {
			add_post_meta( $result, 'content_css', $_POST["wof_content_css"] ); 

			add_post_meta( $result, 'wof_redirect', $_POST["wof_redirect"] ); 
			add_post_meta( $result, 'custom_redirect', $_POST["custom_redirect"] ); 
			add_post_meta( $result, 'wof_products', serialize( $_POST["wof_products"] ) ); 

			/// Multilanguage
			if(function_exists('icl_object_id')) {
				add_post_meta( $result, 'language', $_POST["language"] ); 
			}

		}


		$url =  add_query_arg( array( 'post' => $result ), menu_page_url( 'wof', false ) ) ;

		wp_redirect( $url );
		exit;

	}

	public static function get_default_content() {
		echo '
<label for="name">Ime</label>
<input type="text" name="name" id="name" class="sm-name">

<label for="surname">Priimek</label>
<input type="text" name="surname" id="surname" class="sm-surname">

<label for="address">Naslov</label>
<input type="text" name="address" id="address" class="sm-address">

<label for="post">Pošta</label>
<input type="text" name="post" id="post" class="sm-post">

<label for="zip">Poštna št.</label>
<input type="text" name="zip" id="zip" class="sm-zip">

<label for="phone">Telefon</label>
<input type="text" name="phone" id="phone" class="sm-phone">

<label for="email">Email</label>
<input type="text" name="email" id="email" class="sm-email">

[product-select]

<label for="payment_options">Način plačila</label>
[payment-options]

<button type="submit" class="sm-submit">Kupi</button>
		';

	}

	public static function get_default_content_css() {
		echo '
.sm-form {
	position: relative;
}
.sm-loader {
	position: absolute;
	left: 0;
	top: 0;
	width: 100%;
	height: 100%;
	background: rgba(0,0,0, 0.15);
	border-radius: 5px;
}
.sm-loader img {
	position: absolute;
	left: 50%;
	top: 50%;
	margin-left: -25px;
	margin-top: -25px;
}
.sm-name {
	
}

.sm-surname {
	
}

.sm-post {
	
}

.sm-zip {
	
}

.sm-phone {
	
}

.sm-email {
	
}

.sm-product-select {
	
}

.sm-submit {
	
}
		';

	}

}


function wof_current() {
	if ( $current = WOF::get_current() ) {
		return $current;
	}
}