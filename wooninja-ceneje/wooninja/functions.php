<?php
/**
 * WooNinja Ceneje - pomožne funkcije.
 *
 * @package WooNinja_Ceneje
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preveri ali je WooCommerce aktiven.
 *
 * @return bool
 */
function ceneje_iswc_active() {
	return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
}

/**
 * Admin menu.
 */
function ceneje_xml_menu() {
	add_menu_page(
		__( 'WooNinja - Ceneje XML', 'wooninja-ceneje' ),
		__( 'WooNinja - Ceneje XML', 'wooninja-ceneje' ),
		'manage_options',
		'ceneje-xml-exporter',
		'ceneje_init'
	);
}
