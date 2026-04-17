<?php
/*
Plugin Name: WooNinja - Order Forms
Plugin URI: https://wooninja.si
Description: Vtičnik omogoča kreiranje dinamičnih naročilnic za Woocommerce
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-orderforms
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


define( 'WOF_VERSION', '2.0.0' );

define( 'WOF_PLUGIN', __FILE__ );

define( 'WOF_PLUGIN_BASENAME', plugin_basename( WOF_PLUGIN ) );

define( 'WOF_PLUGIN_NAME', trim( dirname( WOF_PLUGIN_BASENAME ), '/' ) );

define( 'WOF_PLUGIN_DIR', untrailingslashit( dirname( WOF_PLUGIN ) ) );


	  // code that requires WooCommerce
	require_once WOF_PLUGIN_DIR . '/includes/functions.php';
	require_once WOF_PLUGIN_DIR . '/includes/wof.php';

	if ( is_admin() ) {
		require_once WOF_PLUGIN_DIR . '/administration/admin.php';
	}



	add_action( 'plugins_loaded', 'wof' );
	function wof() {
		/* Shortcodes */
		add_shortcode( 'wof', 'wof_form' );
	}

	function wof_form( $atts ) {



		$atts = shortcode_atts( array(
			'id' => '0',
			'title' => 'Default title'
		), $atts, 'wof' );

		$post = WOF::get_instance( $atts["id"] );

		$append = "";
		/// Multilanguage
		if(function_exists('icl_object_id')) {
			$append = "lang: " .ICL_LANGUAGE_CODE;
		} 


		$content = "<!-- SPLETNI MODULI ORDER FORM - " . $post->title() . " " . $append ." -->\n";

		/// STYLE
		$content .= "<style>\n" . $post->content_css() . "\n</style>\n\n";



		/// FORM 
		$nonce = wp_create_nonce( 'wof-order' );

		/// Multilanguage
		if(function_exists('icl_object_id')) {
			$formAction = apply_filters( 'wpml_permalink', plugins_url() ."/woo-orderforms/order.php?_nonce=" . $nonce, $post->language() );
		} else {
			$formAction = plugins_url() ."/woo-orderforms/order.php?_nonce=" . $nonce;

		}
		$content .= "\n <form method='POST' id='wof-form-" . $post->id() . "' class='sm-form' action='". $formAction ."'>  \n";

		$content .= $post->parseContent( $post );

		$content .= "\n <input type='hidden' value='" . $post->id() . "' name='form_id' > \n";

		$content .= "\n <div class='sm-loader' style='display: none;'> <img src='".plugins_url() ."/woo-orderforms/includes/img/ajax-loader.gif' /> </div> \n";

		$content .= "\n </form>  \n";

		/// JAVASCRIPT 
		$content .= "<script>\n
document.querySelector('#wof-form-" . $post->id() . "').addEventListener('submit', function(e){
   document.querySelector('#wof-form-" . $post->id() . " .sm-loader').style.display = 'block';
});
\n</script>\n\n";



		$content .= "<!-- END SPLETNI MODULI ORDER FORM - " . $post->title() . " " . $append ." -->\n";


		return $content;
	}


	add_action( 'init', 'wof_init' );
	function wof_init() {

        wp_register_style( 'wof_codemirror', plugin_dir_url( __FILE__ )  . '/administration/js/codemirror/codemirror.css', array(), '1.0.0' );
        wp_enqueue_style( 'wof_codemirror' );



        wp_register_script( 'wof_codemirror', plugin_dir_url( __FILE__ )  . '/administration/js/codemirror/codemirror.js', array('jquery'), '1.0.0' );
        wp_enqueue_script( 'wof_codemirror' );



        wp_register_script( 'wof_admin', plugin_dir_url( __FILE__ )  . '/administration/js/main.js', array('jquery', 'wof_codemirror'), '1.0.0' );
        wp_enqueue_script( 'wof_admin' );

		wof_register_post_types();


	}


	/**
	 * Add the meta box on the single order page
	 */
	function wof_meta_box() {
		add_meta_box( 'wof-box', __( 'WOF Form', 'wof-sm' ), 'wof_box_content', 'shop_order', 'side', 'low' );
	}

	/**
	 * Create the meta box content on the single order page
	 */
	function wof_box_content() {
		global $post_id;
		$form_id = get_post_meta( $post_id, 'wof_form', true );
		if ( !$form_id ) {
			return;
		}
		$form = WOF::get_instance( $form_id );

		$url = admin_url( 'admin.php?page=wof&post=' . absint( $form->id() ) );
		$edit_link = add_query_arg( array( 'action' => 'edit' ), $url );

		?>
		<div class="print-actions">
			<a href="<?php echo $edit_link;?>"><?php echo $form->title();?></a>
		</div>
		<?php 
	}

	add_action( 'admin_init', 'wof_load_admin_hooks' );

	/**
	 * Load the admin hooks
	 */
	function wof_load_admin_hooks() {		
		// Hooks
		add_action( 'add_meta_boxes_shop_order', 'wof_meta_box' );
	}
