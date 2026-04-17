<?php
/**
 * WooNinja Mimovrste - pomožne funkcije.
 *
 * @package WooNinja_Mimovrste
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Preveri ali je WooCommerce aktiven.
 *
 * @return bool
 */
function mimovrste_iswc_active() {
	return in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true );
}

/**
 * Admin menu.
 */
function mimovrste_xml_menu() {
	add_menu_page(
		__( 'WooNinja - Mimovrste XML', 'wooninja-mimovrste' ),
		__( 'WooNinja - Mimovrste XML', 'wooninja-mimovrste' ),
		'manage_options',
		'mimovrste-xml-exporter',
		'mimovrste_init'
	);
}
