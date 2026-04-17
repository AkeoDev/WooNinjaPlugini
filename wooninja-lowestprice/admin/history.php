<?php
/**
 * WooNinja Lowest Price - zgodovina cen.
 *
 * @package WooNinja_LowestPrice
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<h1><?php esc_html_e( 'Zgodovina', 'wooninja-lowestprice' ); ?></h1>
<?php
global $wpdb;
$table_name    = $wpdb->prefix . 'lowest_product_price';
$price_changes = $wpdb->get_results( "SELECT * FROM $table_name" );

echo '<table class="wp-list-table widefat fixed striped posts">';
echo '<tr style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">';
echo '<th class="manage-column">' . esc_html__( 'Izdelek', 'wooninja-lowestprice' ) . '</th>';
echo '<th class="manage-column">' . esc_html__( 'Cena', 'wooninja-lowestprice' ) . '</th>';
echo '<th class="manage-column">' . esc_html__( 'Najnižja cena (v zadnjih 30 dneh)', 'wooninja-lowestprice' ) . '</th>';
echo '<th class="manage-column">' . esc_html__( 'Datum', 'wooninja-lowestprice' ) . '</th>';
echo '</tr>';

foreach ( $price_changes as $price_change ) {
	$product      = wc_get_product( $price_change->product_id );
	if ( ! $product ) {
		continue;
	}
	$price        = $price_change->price;
	$lowest_price = $price_change->lowest_price;
	$date         = $price_change->created_at;

	echo '<tr>';
	echo '<td>' . esc_html( $product->get_name() ) . '</td>';
	echo '<td>' . esc_html( $price ) . '</td>';
	echo '<td>' . esc_html( $lowest_price ) . '</td>';
	echo '<td>' . esc_html( $date ) . '</td>';
	echo '</tr>';
}

echo '</table>';
