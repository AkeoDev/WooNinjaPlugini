<?php
/**
 * Plugin Name: WooNinja - Mimovrste XML
 * Plugin URI: https://wooninja.si
 * Description: Enostaven izvoz produktov v format XML za povezavo z Mimovrste.com.
 * Version: 2.0.0
 * Author: Humanfrog d.o.o.
 * Author URI: https://wooninja.si
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wooninja-mimovrste
 */

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

include plugin_dir_path( __FILE__ ) . '/wooninja/functions.php';
include plugin_dir_path( __FILE__ ) . '/inc/api.php';

if ( ! mimovrste_iswc_active() ) {
	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong>WooNinja - Mimovrste XML</strong> - Plugin ne deluje brez WooCommerce</p>
		</div>
		<?php
	} );
	return false;
}

/**
 * Administration menu
 */
add_action( 'admin_menu', 'mimovrste_xml_menu' );
add_action( 'admin_menu', 'register_my_values' );

function register_my_values() {
	register_setting( 'my-cool-values', 'all' );
	register_setting( 'myplug_options_group', 'myplug_settings', 'myplug_validate' );
}

function myplug_validate( $input ) {
	return array_map( 'wp_filter_nohtml_kses', (array) $input );
}

/**
 * Settings page
 */
function mimovrste_init() {
	$command = null;
	if ( isset( $_REQUEST['command'] ) ) {
		$command = sanitize_text_field( wp_unslash( $_REQUEST['command'] ) );
	}

	if (
		'shrani-podatke' === $command
		&& isset( $_POST['mimovrste_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mimovrste_nonce'] ) ), 'mimovrste_shrani_podatke' )
	) {
		$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
		if ( 'Shrani' === $tip ) {
			update_option( 'mimovrste_clienID', sanitize_text_field( wp_unslash( $_POST['mimovrste_clienID'] ) ) );
			update_option( 'mimovrste_brandID', sanitize_text_field( wp_unslash( $_POST['mimovrste_brandID'] ) ) );
		}
	}
	?>
	<div class="wrap">

	<h2><?php esc_html_e( 'WooNinja - Mimovrste XML', 'wooninja-mimovrste' ); ?></h2>
	<div class="options-wrap">
	<form method="post" action="">
		<?php wp_nonce_field( 'mimovrste_shrani_podatke', 'mimovrste_nonce' ); ?>
		<input type="hidden" name="command" value="shrani-podatke">
		<b><?php esc_html_e( 'Povezava z MALL PARTNER', 'wooninja-mimovrste' ); ?></b><br>
		<i><?php esc_html_e( 'Za pravilno uporabo vticnika, je potrebno vzpostaviti povezavo z', 'wooninja-mimovrste' ); ?> <b>Mall PARTNER.</b></i><br>
		<i><?php esc_html_e( 'Client ID lahko pridobite na tej', 'wooninja-mimovrste' ); ?> <a href="https://new-partners.mallgroup.com/partner/"><?php esc_html_e( 'povezavi', 'wooninja-mimovrste' ); ?></a>. <?php esc_html_e( 'V kolikor API kljuc se ni kreiran, ga v sekciji API keys dodate.', 'wooninja-mimovrste' ); ?></i><br>
		<i><?php esc_html_e( 'Izdelki pri izvozu potrebujejo Brand ID. Zeljeno znamko najdete na tej', 'wooninja-mimovrste' ); ?> <a href="https://new-partners.mallgroup.com/brands/"><?php esc_html_e( 'povezavi', 'wooninja-mimovrste' ); ?></a>.</i><br>

		<p>Client ID</p>
		<input type="text" name="mimovrste_clienID" value="<?php echo esc_attr( get_option( 'mimovrste_clienID' ) ); ?>"><br>
		<p>Brand ID</p>
		<input type="text" name="mimovrste_brandID" value="<?php echo esc_attr( get_option( 'mimovrste_brandID' ) ); ?>"><br>
		<button style="margin-top: 20px;" type="submit" name="Submit" class="button button-primary upn-button" value="Shrani"><?php esc_html_e( 'Shrani podatke', 'wooninja-mimovrste' ); ?></button>
	</form>
	</div>
	<div class="info-wrap">
		<p><b><?php esc_html_e( 'Preden bo povezava aktivna, morate zagnati spodnjo opcijo "Rocni izvoz izdelkov".', 'wooninja-mimovrste' ); ?></b></p>
		<p><b>XML URL:</b></p>
		<?php echo esc_url( home_url( '/wp-content/uploads/mimovrste-datasource.xml' ) ); ?>
		<p><?php esc_html_e( 'Ta URL posljete na Mimovrste, saj preko te datoteke prevzemajo vase izdelke.', 'wooninja-mimovrste' ); ?><br>
		<?php esc_html_e( 'Obrnite se na svojega skrbnika in on bo uredil na strani Mimovrste.', 'wooninja-mimovrste' ); ?></p>
	</div>

	<div class="options-wrap">
	<h2><?php esc_html_e( 'Avtomatski izvoz izdelkov', 'wooninja-mimovrste' ); ?></h2>
	<p><?php esc_html_e( 'Izberite, ali zelite, da se vasi izdelki avtomatsko izvazajo. XML datoteka se bo avtomatsko posodobila, ko boste shranili izdelek.', 'wooninja-mimovrste' ); ?></p>
	<form method="post" action="options.php">
	<?php
	settings_fields( 'myplug_options_group' );
	$myplug_options = get_option( 'myplug_settings' );
	$radio_value    = isset( $myplug_options['radio1'] ) ? $myplug_options['radio1'] : '';
	?>
	<input type="radio" name="myplug_settings[radio1]" value="item1" <?php checked( 'item1', $radio_value ); ?> /><?php esc_html_e( 'Avtomatsko izvozi vse izdelke', 'wooninja-mimovrste' ); ?><br />
	<input type="radio" name="myplug_settings[radio1]" value="item2" <?php checked( 'item2', $radio_value ); ?> /><?php esc_html_e( 'Avtomatsko izvozi izbrane izdelke', 'wooninja-mimovrste' ); ?><br />
	<input type="radio" name="myplug_settings[radio1]" value="item3" <?php checked( 'item3', $radio_value ); ?> /><?php esc_html_e( 'Ne zelim, da se izdelki avtomatsko izvozijo', 'wooninja-mimovrste' ); ?><br />
	<?php submit_button(); ?>
	</form>
	</div>

	<!--Settings & export products-->
	<div class="settings-wrap">
	<h2><?php esc_html_e( 'Rocni izvoz izdelkov', 'wooninja-mimovrste' ); ?></h2>
	<p><?php esc_html_e( 'Izvoz na Mimovrste.com', 'wooninja-mimovrste' ); ?></p>

	<form method="post" action="">
		<?php wp_nonce_field( 'mimovrste_manual_export', 'mimovrste_export_nonce' ); ?>
		<input type="submit" name="all_products" value="<?php esc_attr_e( 'Vsi produkti', 'wooninja-mimovrste' ); ?>"/>
		<input type="submit" name="checked_products" value="<?php esc_attr_e( 'Oznaceni produkti', 'wooninja-mimovrste' ); ?>"/>
	</form>

	<?php
	if (
		isset( $_POST['mimovrste_export_nonce'] )
		&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mimovrste_export_nonce'] ) ), 'mimovrste_manual_export' )
	) {
		if ( isset( $_POST['all_products'] ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			);
			mimovrste_generate_xml( $args );
			echo '<p>' . esc_html__( 'Izvozeni so vsi produkti', 'wooninja-mimovrste' ) . '</p>';
		}

		if ( isset( $_POST['checked_products'] ) ) {
			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
				'meta_query'     => array(
					array(
						'key'   => 'custom_text_field_title',
						'value' => 'yes',
					),
				),
			);
			mimovrste_generate_xml( $args );
			echo '<p>' . esc_html__( 'Izvozeni so oznaceni produkti', 'wooninja-mimovrste' ) . '</p>';
		}
	}
	?>
	</div>

	</div>
	<?php
}

function get_api_category_list() {
	$categoryList = get_transient( 'cached_category_list' );

	if ( false === $categoryList ) {
		$apiInstance  = new MyApiClass();
		$categoryList = $apiInstance->getCategoryList();

		set_transient( 'cached_category_list', $categoryList, 3600 );
	}

	return $categoryList;
}

function fetch_category_parameters_ajax() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'fetch_params_nonce' ) ) {
		wp_send_json_error( 'Nonce verification failed!', 400 );
	}

	$categoryId = isset( $_POST['categoryId'] ) ? sanitize_text_field( wp_unslash( $_POST['categoryId'] ) ) : '';
	if ( empty( $categoryId ) ) {
		wp_send_json_error( 'No category ID provided!', 400 );
	}

	$apiInstance  = new MyApiClass();
	$categoryParams = $apiInstance->getCategoryParameters( $categoryId );

	ob_start();
	mimovrste_render_category_params_table( $categoryParams );
	$html = ob_get_clean();

	wp_send_json_success( $html );
}
add_action( 'wp_ajax_prev_fetch_category_parameters', 'fetch_category_parameters_ajax' );
add_action( 'wp_ajax_fetch_category_parameters', 'fetch_category_parameters_ajax' );

function my_enqueue_scripts() {
	wp_enqueue_script( 'my-ajax-script', plugin_dir_url( __FILE__ ) . 'wooninja/js/category.js', array( 'jquery' ) );
	wp_localize_script( 'my-ajax-script', 'my_ajax_object', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce'   => wp_create_nonce( 'fetch_params_nonce' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'my_enqueue_scripts' );

function cfwcmimo_create_custom_field() {
	global $post;
	$apiInstance = new MyApiClass();

	$args = array(
		'id'          => 'custom_text_field_title',
		'label'       => __( 'Izvozim izdelek na Mimovrste?', 'cfwcmimo' ),
		'class'       => 'cfwcmimo-custom-field',
		'desc_tip'    => true,
		'description' => __( 'Oznacite, ce zelite izvoziti izdelek na Mimovrste.com', 'cfwcmimo' ),
	);
	woocommerce_wp_checkbox( $args );

	$args2 = array(
		'id'          => 'custom_product_ean',
		'label'       => __( 'EAN', 'cfwcmimo' ),
		'placeholder' => __( 'Vnesite EAN', 'cfwcmimo' ),
		'desc_tip'    => true,
		'description' => __( 'Vnesite EAN', 'cfwcmimo' ),
	);
	woocommerce_wp_text_input( $args2 );

	$categoryList = $apiInstance->getCategoryList();
	$cat_value    = array( '' => 'Izberite kategorijo' );
	if ( $categoryList ) {
		foreach ( $categoryList as $category ) {
			$cat_value[ $category->getCategoryId() ] = $category->getTitle();
		}
	}

	$args3 = array(
		'id'          => 'mimovrste_category',
		'label'       => __( 'Kategorija', 'cfwcmimo' ),
		'placeholder' => __( 'Izberite kategorijo', 'cfwcmimo' ),
		'desc_tip'    => true,
		'description' => __( 'Izberite kategorijo', 'cfwcmimo' ),
		'options'     => $cat_value,
		'class'       => 'cfwcmimo-category-dropdown',
	);
	woocommerce_wp_select( $args3 );

	?>
	<div id="parameters_preloader" style="display:none;">
		<h3 style="padding: 10px;"><?php esc_html_e( 'Nalaganje parametrov. Prosimo pocakajte..', 'wooninja-mimovrste' ); ?></h3>
	</div>
	<?php

	echo '<div id="your_params_container"></div>';

	$selectedCategoryId = get_post_meta( $post->ID, 'mimovrste_category', true );
	$savedCategoryParams = get_post_meta( $post->ID, 'mimovrste_category_params', true );

	if ( ! empty( $selectedCategoryId ) && ! empty( $savedCategoryParams ) ) {
		mimovrste_render_category_params_table( $savedCategoryParams, $post->ID );
	}
}
add_action( 'woocommerce_product_options_general_product_data', 'cfwcmimo_create_custom_field' );

/**
 * Render category parameters as an HTML table.
 *
 * @param array|object $categoryParams The category parameters to render.
 * @param int|null     $post_id        Optional post ID to load saved values.
 */
function mimovrste_render_category_params_table( $categoryParams, $post_id = null ) {
	echo '<table style="padding-left: 10px;">';
	foreach ( $categoryParams as $param ) {
		$paramId = $param->getParamId();
		$title   = $param->getTitle();
		$values  = $param->getValues();

		$savedValue = '';
		if ( $post_id ) {
			$savedValue = get_post_meta( $post_id, 'param_' . $paramId, true );
		}

		echo '<tr><td>' . esc_html( $title ) . ':</td><td>';

		if ( is_object( $values ) && method_exists( $values, 'count' ) && $values->count() > 0 ) {
			echo '<select name="param_' . esc_attr( $paramId ) . '">';
			foreach ( $values as $value ) {
				$valueId  = $value->getValue();
				$text     = $value->getText();
				$selected = selected( $valueId, $savedValue, false );
				echo '<option value="' . esc_attr( $valueId ) . '" ' . $selected . '>' . esc_html( $text ) . '</option>';
			}
			echo '</select>';
		} else {
			echo '<input type="text" name="param_' . esc_attr( $paramId ) . '" value="' . esc_attr( $savedValue ) . '">';
		}

		echo '</td></tr>';
	}
	echo '</table>';
}

add_action( 'add_meta_boxes', 'mimovrste_long_text' );

function mimovrste_long_text() {
	add_meta_box(
		'mimovrste_long_text_meta_box',
		__( 'Mimovrste - opis izdelka', 'wooninja-mimovrste' ),
		'mimovrste_add_custom_content_meta_box',
		'product',
		'normal',
		'low'
	);
}

function mimovrste_add_custom_content_meta_box( $post ) {
	$long_text = get_post_meta( $post->ID, '_mimovrste_long_text', true );
	if ( ! $long_text ) {
		$long_text = '';
	}
	wp_editor( $long_text, '_mimovrste_long_text' );
}

add_action( 'woocommerce_after_single_product_summary', 'display_long_text_editor', 99 );

function display_long_text_editor() {
	global $product;
	$long_desc = get_post_meta( $product->get_id(), '_mimovrste_long_text', true );
	if ( ! $long_desc ) {
		return;
	}
	echo '<div>';
	echo wp_kses_post( $long_desc );
	echo '</div>';
}

function cfwcmimo_save_custom_field( $post_id ) {
	$product = wc_get_product( $post_id );
	$title   = isset( $_POST['custom_text_field_title'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_text_field_title'] ) ) : '';
	$product->update_meta_data( 'custom_text_field_title', $title );

	$cat_title = isset( $_POST['mimovrste_category'] ) ? sanitize_text_field( wp_unslash( $_POST['mimovrste_category'] ) ) : '';
	$product->update_meta_data( 'mimovrste_category', $cat_title );

	$brand_title = isset( $_POST['mimovrste_brand'] ) ? sanitize_text_field( wp_unslash( $_POST['mimovrste_brand'] ) ) : '';
	$product->update_meta_data( 'mimovrste_brand', $brand_title );

	$ean_value = isset( $_POST['custom_product_ean'] ) ? sanitize_text_field( wp_unslash( $_POST['custom_product_ean'] ) ) : '';
	$ean_value = preg_replace( '/\D/', '', $ean_value );

	if ( strlen( $ean_value ) === 14 && '0' === $ean_value[0] ) {
		$ean_value = substr( $ean_value, 1 );
	}

	if ( strlen( $ean_value ) === 8 ) {
		$ean_value = str_pad( $ean_value, 13, '0', STR_PAD_LEFT );
	}

	if ( ctype_digit( $ean_value ) && strlen( $ean_value ) === 13 ) {
		$product->update_meta_data( 'custom_product_ean', $ean_value );
	}

	$selectedCategoryId = $cat_title;
	update_post_meta( $post_id, 'mimovrste_category', $selectedCategoryId );

	if ( ! empty( $selectedCategoryId ) ) {
		$apiInstance    = new MyApiClass();
		$categoryParams = $apiInstance->getCategoryParameters( $selectedCategoryId );

		update_post_meta( $post_id, 'mimovrste_category_params', $categoryParams );

		foreach ( $categoryParams as $param ) {
			$paramId = $param->getParamId();
			if ( isset( $_POST[ 'param_' . $paramId ] ) ) {
				update_post_meta( $post_id, 'param_' . $paramId, sanitize_text_field( wp_unslash( $_POST[ 'param_' . $paramId ] ) ) );
			}
		}
	}

	$long_text = isset( $_POST['_mimovrste_long_text'] ) ? wp_kses_post( wp_unslash( $_POST['_mimovrste_long_text'] ) ) : '';
	$product->update_meta_data( '_mimovrste_long_text', $long_text );

	$product->save();
}
add_action( 'woocommerce_process_product_meta', 'cfwcmimo_save_custom_field' );

/**
 * Create mimovrste xml file on product save (auto-export).
 */
add_action( 'woocommerce_process_product_meta', 'mimovrste_xml', 10, 1 );
function mimovrste_xml() {
	$myplug_options = get_option( 'myplug_settings' );
	$allProducts    = isset( $myplug_options['radio1'] ) ? $myplug_options['radio1'] : '';

	if ( 'item1' === $allProducts ) {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		);
		mimovrste_generate_xml( $args );
	} elseif ( 'item2' === $allProducts ) {
		$args = array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'custom_text_field_title',
					'value' => 'yes',
				),
			),
		);
		mimovrste_generate_xml( $args );
	}
}

/**
 * Generate the Mimovrste XML file from a WP_Query arguments array.
 *
 * @param array $args WP_Query arguments.
 */
function mimovrste_generate_xml( $args ) {
	$loop = new WP_Query( $args );

	$filename     = 'mimovrste-datasource.xml';
	$filelocation = get_home_path() . 'wp-content/uploads/';
	$file         = $filelocation . $filename;

	global $xml, $izdelek_xml_outer, $product;
	$xml = new DOMDocument( '1.0', 'UTF-8' );
	$xml->formatOutput = true;
	$izdelek_xml_outer = $xml->createElement( 'ITEMS' );

	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) {
			$loop->the_post();
			mimovrste_output_product();
		}
	}

	wp_reset_postdata();

	$xml->save( $file, LIBXML_NOEMPTYTAG );
}

/**
 * Output a single product as an XML ITEM element.
 */
function mimovrste_output_product() {
	global $xml, $izdelek_xml_outer, $product;

	$tax_rates       = WC_Tax::get_rates( $product->get_tax_class() );
	$vat_rate        = ! empty( $tax_rates ) ? reset( $tax_rates )['rate'] : 0;
	$price_incl_taxes = wc_get_price_including_tax( $product );

	$status      = $product->get_status();
	$status_name = '';
	if ( 'publish' === $status ) {
		$status_name = 'live';
	} elseif ( 'draft' === $status ) {
		$status_name = 'draft';
	}

	$type     = $product->get_type();
	$prodopis = $product->get_description();
	$mimovrste_product_opis = get_post_meta( get_the_ID(), '_mimovrste_long_text', true );

	if ( ! empty( $mimovrste_product_opis ) ) {
		$opis = '<![CDATA[' . $mimovrste_product_opis . ']]>';
	} else {
		$opis = '<![CDATA[' . $prodopis . ']]>';
	}

	$short_description = get_the_excerpt( $product->get_id() );

	$product_weight = $product->get_weight();
	$product_width  = $product->get_width();
	$product_height = $product->get_height();
	$product_length = $product->get_length();

	$dimension_sum = (int) $product_width + (int) $product_height + (int) $product_length;
	$longest_side  = max( $product_width, $product_height, $product_length );

	if ( $dimension_sum <= 175 && $longest_side <= 100 && $product_weight <= 20 ) {
		$dimension = 'smallbox';
	} else {
		$dimension = 'bigbox';
	}

	$ean         = get_post_meta( get_the_ID(), 'custom_product_ean', true );
	$category_id = get_post_meta( get_the_ID(), 'mimovrste_category', true );

	$stevilcnaZaloga = $product->get_stock_status();
	$zaloga          = ( 'instock' === $stevilcnaZaloga ) ? 'DA' : 'NE';

	$slika = wp_get_attachment_url( $product->get_image_id() );

	$product_name = esc_html( $product->get_name() );
	if ( ! $category_id ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'Kategorija je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}
	if ( ! get_option( 'mimovrste_brandID' ) ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'Brand ID je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}
	if ( ! $dimension ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'Dimenzija je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}
	if ( ! $ean ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'EAN je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}
	if ( ! $short_description ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'Kratek opis je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}
	if ( ! $prodopis ) {
		echo '<p><b>' . $product_name . ':</b> ' . esc_html__( 'Opis je obvezen podatek.', 'wooninja-mimovrste' ) . '</p>';
	}

	$productID = get_the_ID();
	$items_xml = $xml->createElement( 'ITEM' );
	$items_xml->appendChild( $xml->createElement( 'ID', $product->get_sku() ) );
	$items_xml->appendChild( $xml->createElement( 'STAGE', $status_name ) );
	$items_xml->appendChild( $xml->createElement( 'CATEGORY_ID', $category_id ) );
	$items_xml->appendChild( $xml->createElement( 'BRAND_ID', get_option( 'mimovrste_brandID' ) ) );
	$items_xml->appendChild( $xml->createElement( 'TITLE', $product->get_name() ) );
	$items_xml->appendChild( $xml->createElement( 'SHORTDESC', wp_strip_all_tags( $short_description ) ) );
	$items_xml->appendChild( $xml->createElement( 'LONGDESC', htmlentities( $opis, ENT_XML1 ) ) );
	$items_xml->appendChild( $xml->createElement( 'PRIORITY', 1 ) );
	$items_xml->appendChild( $xml->createElement( 'PACKAGE_SIZE', $dimension ) );
	$items_xml->appendChild( $xml->createElement( 'BARCODE', $ean ) );
	$items_xml->appendChild( $xml->createElement( 'PRICE', $price_incl_taxes ) );
	$items_xml->appendChild( $xml->createElement( 'VAT', $vat_rate ) );

	$savedCategoryParams = get_post_meta( $productID, 'mimovrste_category_params', true );

	if ( $savedCategoryParams ) {
		foreach ( $savedCategoryParams as $param ) {
			$paramId    = $param->getParamId();
			$savedValue = get_post_meta( $productID, 'param_' . $paramId, true );

			$paramElement = $xml->createElement( 'PARAM' );
			$paramElement->appendChild( $xml->createElement( 'NAME', $paramId ) );
			$paramElement->appendChild( $xml->createElement( 'VALUE', $savedValue ) );
			$items_xml->appendChild( $paramElement );
		}
	}

	$image_link = wp_get_attachment_image_url( get_post_thumbnail_id( $productID ), 'single-post-thumbnail' );
	if ( $image_link ) {
		$media = $xml->createElement( 'MEDIA' );
		$media->appendChild( $xml->createElement( 'URL', $image_link ) );
		$media->appendChild( $xml->createElement( 'MAIN', 'true' ) );
		$items_xml->appendChild( $media );
	}

	$product_gallery_attachment_ids = $product->get_gallery_image_ids();
	if ( ! empty( $product_gallery_attachment_ids ) ) {
		foreach ( $product_gallery_attachment_ids as $attachment_id ) {
			$gallery_image_link = wp_get_attachment_url( $attachment_id );
			if ( $gallery_image_link ) {
				$media = $xml->createElement( 'MEDIA' );
				$media->appendChild( $xml->createElement( 'URL', $gallery_image_link ) );
				$media->appendChild( $xml->createElement( 'MAIN', 'false' ) );
				$items_xml->appendChild( $media );
			}
		}
	}

	$items_xml->appendChild( $xml->createElement( 'DELIVERY_DELAY', 0 ) );

	$xml->appendChild( $izdelek_xml_outer );
	$izdelek_xml_outer->appendChild( $items_xml );
}
