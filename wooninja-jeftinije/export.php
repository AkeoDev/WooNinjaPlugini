<?php
/**
 * WooNinja Jeftinije - CSV export.
 *
 * @package WooNinja_Jeftinije
 */

require '../../../wp-load.php';

if ( ! current_user_can( 'manage_woocommerce' ) ) {
	wp_die( 'Unauthorized access.' );
}

$nonce = isset( $_REQUEST['wolf_attack'] ) ? sanitize_text_field( $_REQUEST['wolf_attack'] ) : '';
if ( ! wp_verify_nonce( $nonce, 'verify-Jeftinje' ) ) {
	wp_die( 'Security check' );
}

$error = array();

function ObdelajAttribut( $ime, $product, $variation = false, $parent_product = false, $link = false ) {
	global $error;

	$list = array();

	$blockjeftinje = get_post_meta( $product->get_id(), '_jeftinje', true );
	if ( 'yes' === $blockjeftinje ) {
		return $list;
	}

	$post_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'all' ) );
	if ( $variation ) {
		$post_categories = wp_get_post_terms( $parent_product, 'product_cat', array( 'fields' => 'all' ) );
	}

	$cats = array();
	foreach ( $post_categories as $cw ) {
		$cats[] = $cw->name;
	}
	$cats = implode( ',', $cats );

	$brand_terms = wp_get_post_terms( $product->get_id(), 'brand', array( 'fields' => 'all' ) );
	if ( $variation ) {
		$brand_terms = wp_get_post_terms( $parent_product, 'brand', array( 'fields' => 'all' ) );
	}

	$brands = '';
	if ( ! is_wp_error( $brand_terms ) ) {
		$brand_names = array();
		foreach ( $brand_terms as $_cw ) {
			$brand_names[] = $_cw->name;
		}
		$brands = implode( ',', $brand_names );
	}

	$product_id = $product->get_id();
	$per_page   = 255;
	$page       = 1;
	$attributes = maybe_unserialize( get_post_meta( $product_id, '_product_attributes', true ) );

	$dta = array();

	if ( is_array( $attributes ) ) {
		foreach ( $attributes as $key => $value ) {
			$attributes[ $key ] = array_map( 'wc_clean', $value );
		}

		$args = apply_filters( 'woocommerce_ajax_admin_get_variations_args', array(
			'post_type'      => 'product_variation',
			'post_status'    => array( 'private', 'publish' ),
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => array( 'menu_order' => 'ASC', 'ID' => 'DESC' ),
			'post_parent'    => $product_id,
		), $product_id );

		$dta = getAttributes( $args );
	}

	$zaloga = $product->is_in_stock() ? 'Na zalogi' : 'Ni na zalogi';
	$image  = wp_get_attachment_url( $product->get_image_id() );

	$attachment_ids = $product->get_gallery_image_ids();
	$gallery_urls   = array();
	foreach ( $attachment_ids as $attachment_id ) {
		$gallery_urls[] = wp_get_attachment_url( $attachment_id );
	}
	$atts = implode( ',', $gallery_urls );

	$ean            = get_post_meta( $product_id, 'ean_code', true );
	$stvar_artikla  = get_post_meta( $product_id, 'stvar_artikla', true );
	$productDescription = get_option( 'jeftinje_default_opis', 'woocommerce' );
	$demandMoreImages   = get_option( 'jeftinje_moreimages', 'da' );

	if ( $product->get_price() > 0 && $product->is_visible() && ! $variation && ( $product->is_type( 'simple' ) || $product->is_type( 'variable' ) ) ) {
		$post_obj = get_post( $product_id );
		$linkus   = add_query_arg( array( 'utm_source' => 'jeftinje.hr' ), get_permalink( $product_id ) );

		$description = jeftinije_get_description( $productDescription, $post_obj, $product );

		if ( empty( $image ) ) {
			$error[] = 'Produktu manjka glavna slika - ' . $product->get_title();
		}
		if ( empty( $atts ) && 'da' === $demandMoreImages ) {
			$error[] = 'Produktu manjkajo dodatne slike - ' . $product->get_title();
		}

		$list[] = '"' . $product->get_id() . '","' . $brands . '",' . str_replace( ',', '.', wc_get_price_including_tax( $product ) ) . ',"EUR","' . $ime . '","' . $description . '","' . $linkus . '","' . $zaloga . '","' . $ean . '","","' . $stvar_artikla . '","","' . $image . '","' . $cats . '","","","","","' . $atts . '"';
	}

	if ( $product->get_price() > 0 && $variation ) {
		$product_parent = wc_get_product( $parent_product );
		$title          = $product_parent->get_title() . ' - ' . $ime;
		$zaloga         = $product->is_in_stock() ? 'Na zalogi' : 'Ni na zalogi';
		$post_obj       = get_post( $product_parent->get_id() );

		$parent_gallery = $product_parent->get_gallery_image_ids();
		$parent_urls    = array();
		foreach ( $parent_gallery as $attachment_id ) {
			$parent_urls[] = wp_get_attachment_url( $attachment_id );
		}
		$atts = implode( ',', $parent_urls );

		if ( empty( $image ) ) {
			$error[] = 'Produktu manjka glavna slika - ' . $product_parent->get_title();
		}
		if ( empty( $atts ) && 'da' === $demandMoreImages ) {
			$error[] = 'Produktu manjkajo dodatne slike - ' . $product_parent->get_title();
		}

		$description = jeftinije_get_description( $productDescription, $post_obj, $product_parent );
		$linkus      = add_query_arg( $link, get_permalink( $parent_product ) );
		$list[]      = '"' . $product->get_id() . '","' . $brands . '",' . str_replace( ',', '.', wc_get_price_including_tax( $product ) ) . ',"EUR","' . $title . '","' . $description . '","' . $linkus . '","' . $zaloga . '","' . $ean . '","","' . $stvar_artikla . '","","' . $image . '","' . $cats . '","","","","","' . $atts . '"';
	}

	foreach ( $dta as $value_ ) {
		$list[] = $value_;
	}

	return $list;
}

/**
 * Pridobi opis produkta glede na nastavitev.
 */
function jeftinije_get_description( $type, $post_obj, $product ) {
	switch ( $type ) {
		case 'short':
			$raw = $product->get_short_description();
			break;
		case 'custom':
			$raw = get_post_meta( $product->get_id(), '_jeftinje_opis', true );
			break;
		default:
			$raw = $post_obj->post_content;
			break;
	}
	return htmlentities( str_replace( '"', '&quot;', preg_replace( "/\r\n|\r|\n/", '<br/>', $raw ) ), ENT_QUOTES );
}

function getAttributes( $args ) {
	$variations = get_posts( $args );
	$list       = array();

	if ( ! $variations ) {
		return $list;
	}

	foreach ( $variations as $variation ) {
		$variation_id   = absint( $variation->ID );
		$variation_data = wc_get_product_variation_attributes( $variation_id );
		$variation_data['variation_id'] = $variation->ID;

		if ( count( $variation_data ) <= 1 ) {
			continue;
		}

		$ime   = '';
		$count = count( $variation_data ) - 1;
		$c     = 1;
		$link  = array( 'utm_source' => 'jeftinje.hr' );

		foreach ( $variation_data as $key => $value ) {
			if ( 'variation_id' === $key ) {
				continue;
			}
			$ime .= ( $count === $c ) ? $value : $value . ' + ';
			$link[ urlencode( $key ) ] = urlencode( $value );
			$c++;
		}

		$produktus      = new WC_Product_Variation( $variation_id );
		$allowVariation = ( 'da' === get_option( 'jeftinje_attributi', 'da' ) );

		$obdelaj = ObdelajAttribut( $ime, $produktus, $allowVariation, $args['post_parent'], $link );
		foreach ( $obdelaj as $valuew ) {
			$list[] = $valuew;
		}
	}

	return $list;
}

// Export logic.
if ( ! isset( $_GET['export'] ) ) {
	wp_die( 'Missing export parameter.' );
}

if ( isset( $_GET['ids'] ) ) {
	$raw_ids  = sanitize_text_field( $_GET['ids'] );
	$post_ids = array_map( 'absint', explode( ',', $raw_ids ) );
	$args     = array(
		'post_type'      => 'product',
		'posts_per_page' => 10000,
		'fields'         => 'ids',
		'post__in'       => $post_ids,
	);
	$loop       = new WP_Query( $args );
	$categories = '';
} else {
	$args = array(
		'post_type'      => 'product',
		'posts_per_page' => 10000,
		'fields'         => 'ids',
	);

	$categories = isset( $_GET['category'] ) ? sanitize_text_field( $_GET['category'] ) : '';

	if ( ! empty( $categories ) && 'false' !== $categories ) {
		$cat_ids  = array_filter( explode( '$', $categories ) );
		$cat_ids  = array_map( 'absint', $cat_ids );
		$operator = ( count( $cat_ids ) > 1 ) ? 'OR' : 'AND';

		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'terms'    => $cat_ids,
				'field'    => 'term_id',
				'operator' => $operator,
			),
		);
	} else {
		$categories = '';
	}

	$loop = new WP_Query( $args );
}

$list      = array();
$csvOutput = '"ID","BRAND","PRICE","CURCODE","NAME","DESCRIPTION","LINK","IN_STOCK","EAN","UPC","STVARTIKLA","MODARTIKLA","SLIKAVELIKA","FILEUNDER","GARANCIJA","CUIN","KUPON","DARILO","MOREIMAGES"';
$csvOutput .= "\r\n";

while ( $loop->have_posts() ) {
	$loop->the_post();
	global $product;
	$name    = $product->get_title();
	$obdelaj = ObdelajAttribut( $name, $product );

	foreach ( $obdelaj as $value ) {
		$list[] = $value;
	}
}

wp_reset_postdata();

if ( ! empty( $error ) ) {
	echo '<h1>' . esc_html__( 'Pri izvozu so zaznane težave:', 'wooninja-jeftinije' ) . '</h1>';
	echo wp_kses_post( implode( '<br>', array_map( 'esc_html', $error ) ) );
	return;
}

foreach ( $list as $line ) {
	$csvOutput .= $line . "\r\n";
}

$filename = 'exports/izvoz-jeftinje-hr';
if ( ! empty( $categories ) ) {
	$cat_arr = explode( '$', $categories );
	$cat_arr = array_filter( array_map( 'absint', $cat_arr ) );
	sort( $cat_arr );
	$filename .= '-' . implode( '-', $cat_arr );
}

file_put_contents( $filename . '.csv', iconv( 'utf-8', 'windows-1250//IGNORE', $csvOutput ) );
?>
<html>
<head>
	<title>Izvoz Jeftinje.hr</title>
	<meta charset="UTF-8">
	<style>
		body { font-family: 'Roboto', sans-serif; }
		.container { max-width: 960px; margin: 50px auto; text-align: center; }
	</style>
</head>
<body>
	<div class="container">
		<h3>Link do izvoza izdelkov, ki ga pošljete na Jeftinje.hr:</h3>
		<?php $file_url = esc_url( plugin_dir_url( __FILE__ ) . $filename . '.csv' ); ?>
		<a href="<?php echo $file_url; ?>" download class="button"><?php echo $file_url; ?></a>
	</div>
</body>
</html>
