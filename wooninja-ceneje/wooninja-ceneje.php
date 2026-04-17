<?php
/**
 * Plugin Name: WooNinja - Ceneje.si XML
 * Plugin URI: https://wooninja.si
 * Description: Enostaven izvoz produktov v format XML za povezavo s Ceneje.si.
 * Version: 2.0.0
 * Author: Humanfrog d.o.o.
 * Author URI: https://wooninja.si
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wooninja-ceneje
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WOONINJA_CENEJE_VERSION', '2.0.0' );

include plugin_dir_path( __FILE__ ) . 'wooninja/functions.php';

if ( ! ceneje_iswc_active() ) {
	add_action( 'admin_notices', function () {
		printf(
			'<div class="notice notice-error is-dismissible"><p><strong>%s</strong> - Plugin ne deluje brez WooCommerce.</p></div>',
			'WooNinja - Ceneje.si XML'
		);
	} );
	return;
}

add_action( 'admin_menu', 'ceneje_xml_menu' );
add_action( 'admin_menu', 'register_ceneje_values' );

function register_ceneje_values() {
	register_setting( 'cenejeplug_options_group', 'cenejeplug_settings', 'cenejeplug_validate' );
	register_setting( 'delivery_options_group', 'deliverya_settings' );
	register_setting( 'delivery_options_group', 'deliveryb_settings' );
	register_setting( 'delivery_options_group', 'deliveryc_settings' );
}

function cenejeplug_validate( $input ) {
	return array_map( 'wp_filter_nohtml_kses', (array) $input );
}

/**
 * Generira XML datoteko s produkti.
 *
 * @param array $query_args WP_Query argumenti za izbiro produktov.
 * @return void
 */
function ceneje_generate_xml( $query_args ) {
	global $xml, $xml_format_outer, $izdelek_xml_outer, $product;

	$loop = new WP_Query( $query_args );

	$upload_dir  = wp_upload_dir();
	$file        = $upload_dir['basedir'] . '/ceneje-datasource.xml';

	$xml = new DOMDocument( '1.0', 'UTF-8' );
	$xml->formatOutput = true;

	$xml_format_outer = $xml->createElement( 'xml' );
	$xml_format_outer->setAttribute( 'version', '1.0' );
	$xml_format_outer->setAttribute( 'encoding', 'UTF-8' );
	$izdelek_xml_outer = $xml->createElement( 'CNJExport' );

	if ( $loop->have_posts() ) {
		while ( $loop->have_posts() ) {
			$loop->the_post();
			ceneje_output_product();
		}
	}

	wp_reset_postdata();

	$xml->save( $file, LIBXML_NOEMPTYTAG );
}

/**
 * Admin nastavitvena stran.
 */
function ceneje_init() {
	$cenejeplug_options = get_option( 'cenejeplug_settings' );
	$upload_dir         = wp_upload_dir();
	$xml_url            = $upload_dir['baseurl'] . '/ceneje-datasource.xml';
	?>
	<div class="wrap">
		<h2>WooNinja - Ceneje XML</h2>

		<div class="info-wrap">
			<p><b>Preden bo povezava aktivna, morate zagnati spodnjo opcijo "Ročni izvoz izdelkov".</b></p>
			<p><b>XML URL:</b></p>
			<?php echo esc_url( $xml_url ); ?>
			<p>Ta URL pošljete na Ceneje.si, saj preko te datoteke prevzemajo vaše izdelke.<br>
				Obrnite se na svojega skrbnika in on bo uredil na strani Ceneje.si.</p>
		</div>

		<div class="options-wrap">
			<h2>Avtomatski izvoz izdelkov</h2>
			<p>Izberite, ali želite, da se vaši izdelki avtomatsko izvažajo. XML datoteka se bo avtomatsko posodobila, ko boste shranili izdelek.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'cenejeplug_options_group' ); ?>
				<input type="radio" name="cenejeplug_settings[radio1]" value="item1" <?php checked( 'item1', isset( $cenejeplug_options['radio1'] ) ? $cenejeplug_options['radio1'] : '' ); ?> />Avtomatsko izvozi vse izdelke<br>
				<input type="radio" name="cenejeplug_settings[radio1]" value="item2" <?php checked( 'item2', isset( $cenejeplug_options['radio1'] ) ? $cenejeplug_options['radio1'] : '' ); ?> />Avtomatsko izvozi izbrane izdelke<br>
				<input type="radio" name="cenejeplug_settings[radio1]" value="item3" <?php checked( 'item3', isset( $cenejeplug_options['radio1'] ) ? $cenejeplug_options['radio1'] : '' ); ?> />Ne želim, da se izdelki avtomatsko izvozijo<br>
				<?php submit_button(); ?>
			</form>
		</div>

		<div class="delivery-wrap">
			<h2>Nastavitve dostave</h2>
			<form method="post" action="options.php">
				<?php settings_fields( 'delivery_options_group' ); ?>
				<h4>Cena dostave</h4>
				<input type="text" name="deliverya_settings" value="<?php echo esc_attr( get_option( 'deliverya_settings' ) ); ?>" />&euro;<br>
				<h4>Min čas dostave (v dneh)</h4>
				<input type="text" name="deliveryb_settings" value="<?php echo esc_attr( get_option( 'deliveryb_settings' ) ); ?>" /><br>
				<h4>Max čas dostave (v dneh)</h4>
				<input type="text" name="deliveryc_settings" value="<?php echo esc_attr( get_option( 'deliveryc_settings' ) ); ?>" /><br>
				<?php submit_button(); ?>
			</form>
		</div>

		<div class="settings-wrap">
			<h2>Ročni izvoz izdelkov</h2>
			<p>Izvoz na Ceneje.si</p>

			<form method="post" action="">
				<?php wp_nonce_field( 'ceneje_manual_export', 'ceneje_export_nonce' ); ?>
				<input type="submit" name="all_products" class="button" value="Vsi produkti" />
				<input type="submit" name="checked_products" class="button" value="Označeni produkti" />
			</form>

			<?php
			if ( isset( $_POST['ceneje_export_nonce'] ) && wp_verify_nonce( $_POST['ceneje_export_nonce'], 'ceneje_manual_export' ) ) {
				if ( isset( $_POST['all_products'] ) ) {
					ceneje_generate_xml( array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
					) );
					echo '<div class="updated"><p>Izvoženi so vsi produkti.</p></div>';
				}

				if ( isset( $_POST['checked_products'] ) ) {
					ceneje_generate_xml( array(
						'post_type'      => 'product',
						'posts_per_page' => -1,
						'post_status'    => 'publish',
						'meta_query'     => array(
							array(
								'key'   => 'custom_ceneje_text_field_title',
								'value' => 'yes',
							),
						),
					) );
					echo '<div class="updated"><p>Izvoženi so označeni produkti.</p></div>';
				}
			}
			?>
		</div>
	</div>
	<?php
}

/**
 * WooCommerce product checkbox za Ceneje izvoz.
 */
function cfwcceneje_create_custom_field() {
	woocommerce_wp_checkbox( array(
		'id'          => 'custom_ceneje_text_field_title',
		'label'       => __( 'Izvozim izdelek na Ceneje.si?', 'wooninja-ceneje' ),
		'class'       => 'cfwcceneje-custom-field',
		'desc_tip'    => true,
		'description' => __( 'Označite, če želite izvoziti izdelek na Ceneje.si', 'wooninja-ceneje' ),
	) );
}
add_action( 'woocommerce_product_options_general_product_data', 'cfwcceneje_create_custom_field' );

function cfwcceneje_save_custom_field( $post_id ) {
	$product = wc_get_product( $post_id );
	$title   = isset( $_POST['custom_ceneje_text_field_title'] ) ? 'yes' : '';
	$product->update_meta_data( 'custom_ceneje_text_field_title', sanitize_text_field( $title ) );
	$product->save();
}
add_action( 'woocommerce_process_product_meta', 'cfwcceneje_save_custom_field' );

/**
 * Avtomatski izvoz XML ob shranjevanju produkta.
 */
add_action( 'woocommerce_process_product_meta', 'ceneje_xml', 10, 1 );
function ceneje_xml() {
	$cenejeplug_options = get_option( 'cenejeplug_settings' );
	$mode               = isset( $cenejeplug_options['radio1'] ) ? $cenejeplug_options['radio1'] : 'item3';

	if ( $mode === 'item1' ) {
		ceneje_generate_xml( array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );
	} elseif ( $mode === 'item2' ) {
		ceneje_generate_xml( array(
			'post_type'      => 'product',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => array(
				array(
					'key'   => 'custom_ceneje_text_field_title',
					'value' => 'yes',
				),
			),
		) );
	}
}

/**
 * Generira XML za posamezen produkt.
 */
function ceneje_output_product() {
	global $xml, $xml_format_outer, $product;

	$items_xml     = $xml->createElement( 'Item' );
	$permalink     = get_permalink();
	$product_id    = get_the_ID();
	$price         = floatval( $product->get_price() );
	$regular_price = floatval( $product->get_regular_price() );

	$tax_rates = WC_Tax::get_base_tax_rates( $product->get_tax_class() );
	$tax_rate  = ! empty( $tax_rates ) ? reset( $tax_rates ) : array( 'rate' => 0 );

	$description = $product->get_description();
	$opis        = ! empty( $description ) ? '<![CDATA[' . $description . ']]>' : '';

	$ean = '';
	if ( is_plugin_active( 'ean-for-woocommerce/ean-for-woocommerce.php' ) ) {
		$ean = get_post_meta( $product_id, '_alg_ean', true );
	}

	$slika  = wp_get_attachment_url( $product->get_image_id() );
	$zaloga = $product->get_stock_status();

	$terms            = get_the_terms( $product_id, 'product_cat' );
	$product_cat_name = '';
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		$product_cat_name = $terms[0]->name;
	}

	$delivery_cost = get_option( 'deliverya_settings', '' );
	$min_time      = get_option( 'deliveryb_settings', '' );
	$max_time      = get_option( 'deliveryc_settings', '' );

	$davcna_stopnja    = floatval( $tax_rate['rate'] );
	$davek_calc        = $davcna_stopnja / 100;
	$price_incl_tax    = $price + ( $price * $davek_calc );
	$regular_price_tax = $regular_price + ( $regular_price * $davek_calc );

	$fields = array(
		'ID'                  => $product->get_id(),
		'name'                => $product->get_name(),
		'description'         => htmlentities( $opis, ENT_XML1 ),
		'specifications'      => '',
		'link'                => $permalink,
		'mainImage'           => $slika,
		'moreImages'          => '',
		'videoUrl'            => '',
		'price'               => round( $price_incl_tax, 2 ),
		'regularPrice'        => round( $regular_price_tax, 2 ),
		'clubPrice'           => '',
		'curCode'             => get_woocommerce_currency(),
		'stockText'           => $product->get_stock_quantity(),
		'stock'               => $zaloga,
		'inStoreAvailability' => '',
		'fileUnder'           => $product_cat_name,
		'cenCategoryId'       => '',
		'brand'               => '',
		'EAN'                 => $ean,
		'CUIN'                => '',
		'productCode'         => $product->get_sku(),
		'productModel'        => '',
		'condition'           => '',
		'warranty'            => '',
		'coupon'              => '',
		'couponCode'          => '',
		'gift'                => '',
		'deliveryCost'        => $delivery_cost,
		'deliveryTimeMin'     => $min_time,
		'deliveryTimeMax'     => $max_time,
		'groupId'             => '',
		'attributes'          => '',
	);

	foreach ( $fields as $tag => $value ) {
		$items_xml->appendChild( $xml->createElement( $tag, $value ) );
	}

	$xml->appendChild( $xml_format_outer );
	$xml_format_outer->appendChild( $items_xml );
}
