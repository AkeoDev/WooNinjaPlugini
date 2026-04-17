<?php
/**
* Plugin Name: WooNinja - Najnižja cena (PID Direktiva)
* Plugin URI: https://wooninja.si
* Description: Lowest price vtičnik omogoča prikazovanje najnižje cene pri izdelkih v zadnjih 30 dneh.
* Version: 2.0.0
* Author: Humanfrog d.o.o.
* Author URI: https://wooninja.si
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wooninja-lowestprice
**/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function plugin_add_settings_link_pid( $links ) {
	$settings_link = '<a href="options-general.php?page=lowestPrice-options">' . esc_html__( 'Nastavitve', 'wooninja-lowestprice' ) . '</a>';
	$links[] = $settings_link;
	return $links;
}

$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link_pid' );


function lowest_price_options_panel() {
	add_menu_page('Lowest price nastavitve', 'Najnižja cena (PID Direktiva)', 'manage_options', 'lowestPrice-options', 'lowestprice_settings', plugin_dir_url(__FILE__) . '/WooNina_icon.png');
	add_submenu_page('lowestPrice-options', 'Zgodovina', 'Zgodovina', 'manage_options', 'lowestPrice-options-zgodovina', 'lowestprice_settings_zgodovina');
	add_submenu_page('lowestPrice-options', 'Dokumentacija', 'Dokumentacija', 'manage_options', 'lowestPrice-options-dokumentacija', 'lowestprice_settings_dokumentacija');
}
add_action('admin_menu','lowest_price_options_panel');

function lowestprice_settings() {
	include("admin/settings.php");
}

function lowestprice_settings_dokumentacija() {
	include("admin/documentation.php");
}

function lowestprice_settings_zgodovina() {
	include("admin/history.php");
}

add_action('archive_prices_event', 'archive_prices_function');

function create_table_lowestprice() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$charset_collate = $wpdb->get_charset_collate();

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ( ! $table_exists ) {
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			product_id bigint(20) NOT NULL,
			variation_data varchar(255) NOT NULL,
			price float NOT NULL,
			price_history longtext NOT NULL,
			lowest_price float NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option('processed_product_count', 0);
	}
}

add_action( 'wp_ajax_process_batch', 'archive_prices_function' );

function archive_prices_function() {
	check_ajax_referer( 'lowestprice_batch_nonce', '_ajax_nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( 'Unauthorized', 403 );
	}

	global $wpdb;
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$charset_collate = $wpdb->get_charset_collate();

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ( ! $table_exists ) {
		$sql = "CREATE TABLE $table_name (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			product_id bigint(20) NOT NULL,
			variation_data varchar(255) NOT NULL,
			price float NOT NULL,
			price_history longtext NOT NULL,
			lowest_price float NOT NULL,
			created_at datetime NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	$batch_size = 100;
	$last_processed_id = isset( $_POST['last_processed_id'] ) ? absint( $_POST['last_processed_id'] ) : 0;

	$all_product_ids = wc_get_products(array(
		'status' => 'publish',
		'return' => 'ids',
		'orderby' => 'id',
		'order' => 'ASC',
		'limit' => -1
	));

	$filtered_ids = array_filter($all_product_ids, function($id) use ($last_processed_id) {
		return $id > $last_processed_id;
	});
	$product_ids = array_slice($filtered_ids, 0, $batch_size);

	foreach ($product_ids as $product_id) {
		$product = wc_get_product($product_id);

		if ($product->is_type('variable')) {
			$variations = $product->get_available_variations();
			foreach ($variations as $variation) {
				$variation_product = wc_get_product($variation['variation_id']);
				update_variation_product_price($variation_product, $table_name);
			}
		} else {
			update_simple_product_price($product, $table_name);
		}
	}

	$last_id_in_batch = end($product_ids);

	$products_processed_so_far = isset( $_POST['products_processed_so_far'] ) ? absint( $_POST['products_processed_so_far'] ) : 0;

	$total_products = count($all_product_ids);
	$processed_this_batch = count($product_ids);
	$processed_so_far = $products_processed_so_far + $processed_this_batch;
	$percent_complete = ($processed_so_far / $total_products) * 100;

	$count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

	update_option('processed_product_count', $processed_so_far);

	wp_send_json(array(
		'last_id' => $last_id_in_batch,
		'percent' => $percent_complete,
		'products_stored' => $count,
		'processed_this_batch' => $processed_this_batch
	));
}

function update_product_price( $product_id ) {
	global $wpdb;

	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return;
	}

	$table_name = $wpdb->prefix . 'lowest_product_price_archive';

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ( $table_exists ) {
		if ( $product->is_type( 'variable' ) ) {
			$variations = $product->get_available_variations();
			foreach ( $variations as $variation ) {
				$variation_product = wc_get_product( $variation['variation_id'] );
				update_variation_product_price( $variation_product, $table_name );
			}
		} else {
			update_simple_product_price( $product, $table_name );
		}
	}
}
add_action( 'woocommerce_update_product', 'update_product_price' );

function update_simple_product_price( $product, $table_name ) {
	global $wpdb;

	$product_id = $product->get_id();
	$price = $product->get_price();
	$timestamp = current_time( 'mysql' );

	if ( get_transient( "price_update_lock_{$product_id}" ) ) {
		return;
	}

	set_transient( "price_update_lock_{$product_id}", true, 5 );

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE product_id = %d", $product_id ) );

	if ( ! $row ) {
		$wpdb->insert(
			$table_name,
			array(
				'product_id'     => $product_id,
				'variation_data' => '',
				'price'          => $price,
				'price_history'  => wp_json_encode( array( array( 'price' => $price, 'timestamp' => $timestamp ) ) ),
				'lowest_price'   => $price,
				'created_at'     => $timestamp,
			)
		);
	} else {
		$price_history = $row->price_history;
		$price_history = json_decode( $price_history, true );

		if ( ! is_array( $price_history ) ) {
			$price_history = array();
		}

		$last_price_record = end($price_history);

		if (is_array($last_price_record) && isset($last_price_record['price']) && $last_price_record['price'] != $price) {
			$price_history[] = array('price' => $price, 'timestamp' => $timestamp);
		}

		// Filter out prices older than 30 days
		$price_history = array_filter( $price_history, function( $price_record ) {
			$price_date = strtotime( $price_record['timestamp'] );
			$days_diff = floor( ( time() - $price_date ) / ( 60 * 60 * 24 ) );
			return $days_diff <= 30;
		});

		$prices = array_map( function( $price_record ) {
			return $price_record['price'];
		}, $price_history );

		$lowest_price = (!empty($prices)) ? min($prices) : $price;

		$last_price_record = end($price_history);
		if (!is_array($last_price_record) || !isset($last_price_record['price']) || $last_price_record['price'] != $price) {
			$price_history[] = array('price' => $price, 'timestamp' => $timestamp);
		}

		$wpdb->update(
			$table_name,
			array(
				'price'         => $price,
				'price_history' => wp_json_encode( $price_history ),
				'lowest_price'  => $lowest_price,
			),
			array( 'id' => $row->id )
		);
	}
}

function update_variation_product_price( $variation_product, $table_name ) {
	global $wpdb;

	$variation_id = $variation_product->get_id();
	$price = $variation_product->get_price();
	$attributes = $variation_product->get_variation_attributes();
	$timestamp = current_time( 'mysql' );

	$variation_data = wp_json_encode( array( 'attributes' => $attributes, 'price' => $price ) );

	$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE product_id = %d", $variation_id ) );

	if ( ! $row ) {
		$wpdb->insert(
			$table_name,
			array(
				'product_id'     => $variation_id,
				'variation_data' => $variation_data,
				'price'          => $price,
				'price_history'  => wp_json_encode( array( array( 'price' => $price, 'timestamp' => $timestamp ) ) ),
				'lowest_price'   => $price,
				'created_at'     => $timestamp,
			)
		);
	} else {
		$price_history = json_decode( $row->price_history, true );
		if ( ! is_array( $price_history ) ) {
			$price_history = array();
		}
		$price_history[] = array( 'price' => $price, 'timestamp' => $timestamp );

		// Filter out prices older than 30 days
		$price_history = array_filter( $price_history, function( $price_record ) {
			$price_date = strtotime( $price_record['timestamp'] );
			$days_diff = floor( ( time() - $price_date ) / ( 60 * 60 * 24 ) );
			return $days_diff <= 30;
		});

		$prices = array_map( function( $price_record ) {
			return $price_record['price'];
		}, $price_history );

		$wpdb->update(
			$table_name,
			array(
				'variation_data' => $variation_data,
				'price'          => $price,
				'price_history'  => wp_json_encode( $price_history ),
				'lowest_price'   => min( $prices ),
			),
			array( 'id' => $row->id )
		);
	}
}

function display_lowest_price() {
	global $product; global $wpdb;
	$product_id = $product->get_id();

	$product_id = get_the_ID();
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$timestamp = current_time( 'mysql' );

	/**
	 *   This function works on action woocommerce_after_shop_loop_item and on single product.
	 *   On single product it works only for simple products.
	 **/
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
	if ($table_exists) {
		if ($product) {
			$product_type = $product->get_type();

			if ($product_type == 'simple') {
				$product = new WC_Product_Simple( $product_id );

				$msg = '';

				$price = $product->is_on_sale() ? $product->get_sale_price() : $product->get_regular_price();

				$price_with_tax = wc_get_price_including_tax($product, ['price' => $price]);

				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE product_id = %d", $product_id ) );

				$lowest_price = $wpdb->get_var( $wpdb->prepare(
					"SELECT lowest_price FROM $table_name WHERE product_id = %d",
					$product_id
				) );

				if ($row) {
					$price_history = $row->price_history;
					$price_history = json_decode($price_history, true);

					if (!is_array($price_history)) {
						$price_history = array();
					}

					$current_time = time();

					// Filter out prices older than 30 days
					$price_history = array_filter($price_history, function ($price_record) use ($current_time) {
						if (!isset($price_record['timestamp'])) {
							return false;
						}

						$price_date = strtotime($price_record['timestamp']);

						if (!$price_date) {
							return false;
						}

						$days_diff = ($current_time - $price_date) / (60 * 60 * 24);

						return $days_diff <= 30;
					});

					if (empty($price_history)) {
						$price_history[] = [
							'price' => $price,
							'timestamp' => current_time( 'mysql' )
						];
					}

					$prices = array_map(function ($price_record) {
						return $price_record['price'];
					}, $price_history);

					$lowest_price = !empty($prices) ? min($prices) : null;
					$lowest_price_with_tax = ($lowest_price !== null) ? wc_get_price_including_tax($product, ['price' => $lowest_price]) : null;

					if ($lowest_price == $price) {
						if (get_option('lowest_price_format_equal_show') == '1') {
							$msg = get_option('lowest_price_format_equal') ?: 'Najnižja cena v zadnjih 30 dneh je enaka trenutni.';
						}
					} else {
						$msg = get_option('lowest_price_format') ?: 'Najnižja cena v zadnjih 30 dneh: ';
					}

					$color = get_option('lowest_price_format_color') ?: '';

					if (get_option('display_lowest_price_default') == '1' || get_option('display_lowest_price_product_page') == '1' ) {
						if ($lowest_price == $price) {
							echo '<p class="lowest-price-equal" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . '</p>';
						} else {
							echo '<p class="lowest-price-new" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . ' ' . wp_kses_post( wc_price( $lowest_price_with_tax ) ) . '</p>';
						}
					}
				}
			}
		}
	}
}

function display_variation_lowest_price_loop() {
	global $product;
	global $wpdb;
	$product_id = $product->get_id();

	$product_id = get_the_ID();
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ($table_exists) {
		$lowest_price = $wpdb->get_var($wpdb->prepare(
			"SELECT lowest_price FROM $table_name WHERE product_id = %d",
			$product_id
		));
		$price = $product->is_on_sale() ? $product->get_sale_price() : $product->get_regular_price();

		if ($product) {
			$product_type = $product->get_type();
			if ($product_type == 'simple') {
				$product = new WC_Product_Simple($product_id);
			} elseif ($product_type == 'variable') {
				$product = new WC_Product_Variable($product_id);
			}
		}
		$msg = '';

		if ($product_type == 'variable') {
			$variations = $product->get_available_variations();

			if ($variations) {
				$lowest_variation_price = PHP_INT_MAX;
				foreach ($variations as $variation) {
					$variation_id = $variation['variation_id'];
					$variation_price = get_post_meta($variation_id, '_price', true);
					$lowest_variation_price = min($lowest_variation_price, $variation_price);
				}

				if ($lowest_variation_price == $price) {
					$msg = get_option('lowest_price_format_equal') ?: 'Najnižja cena v zadnjih 30 dneh je enaka trenutni.';
				} else {
					$msg = get_option('lowest_price_format_variable') ?: 'Najnižja cena v zadnjih 30 dneh: ';
				}

				$color = get_option('lowest_price_format_color') ?: '';

				if (get_option('display_lowest_price_default') == '1') {
					if ($lowest_variation_price == $price) {
						echo '<p class="lowest-price-equal" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . '</p>';
					} else {
						echo '<p class="lowest-price-new" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . ' ' . wp_kses_post( wc_price( $lowest_variation_price ) ) . '</p>';
					}
				}
			}
		}
	}
}

add_action('woocommerce_after_shop_loop_item','display_variation_lowest_price_loop');

function display_lowest_price_shortcode() {
	global $product; global $wpdb;
	$product_id = $product->get_id();

	$product_id = get_the_ID();
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$timestamp = current_time( 'mysql' );

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
	if ($table_exists) {
		if ($product) {
			$product_type = $product->get_type();

			if ($product_type == 'simple') {
				$product = new WC_Product_Simple( $product_id );

				$msg = '';

				$price = $product->is_on_sale() ? $product->get_sale_price() : $product->get_regular_price();
				$row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE product_id = %d", $product_id ) );

				$lowest_price = $wpdb->get_var( $wpdb->prepare(
					"SELECT lowest_price FROM $table_name WHERE product_id = %d",
					$product_id
				) );

				if ($row) {
					$price_history = $row->price_history;
					$price_history = json_decode( $price_history, true );

					if ( ! is_array( $price_history ) ) {
						$price_history = array();
					}

					// Filter out prices older than 30 days
					$price_history = array_filter( $price_history, function( $price_record ) {
						$price_date = strtotime( $price_record['timestamp'] );
						$days_diff = floor( ( time() - $price_date ) / ( 60 * 60 * 24 ) );
						return $days_diff <= 30;
					});

					$prices = array_map( function( $price_record ) {
						return $price_record['price'];
					}, $price_history );

					$lowest_price = !empty($prices) ? min($prices) : null;

					$lowest_price_with_tax = ($lowest_price != null) ? wc_get_price_including_tax($product, ["price" => $lowest_price]) : null;
					if ($prices) {
						if ($lowest_price == $price) {
							if (get_option('lowest_price_format_equal_show') == '1') {
								$msg = get_option('lowest_price_format_equal') ?: 'Najnižja cena v zadnjih 30 dneh je enaka trenutni.';
							}
						} else {
							$msg = get_option('lowest_price_format') ?: 'Najnižja cena v zadnjih 30 dneh: ';
						}

						$color = get_option('lowest_price_format_color') ?: '';

						if ($lowest_price == $price) {
							echo '<p class="lowest-price-equal" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . '</p>';
						} else {
							echo '<p class="lowest-price-new" style="color:' . esc_attr( $color ) . '">' . esc_html( $msg ) . ' ' . wp_kses_post( wc_price( $lowest_price_with_tax ) ) . '</p>';
						}
					}
				}
			}
		}
	}
}

if (get_option('lowest_price_shortcode_use') == '1') {
	add_shortcode( 'display_lowest_price', 'display_lowest_price_shortcode');
}

add_action('woocommerce_single_product_summary', 'display_lowest_price', 30);

if (!get_option('display_lowest_price_product_page')) {
	add_action('woocommerce_after_shop_loop_item','display_lowest_price');
}

function display_price_changes_admin( $product_id ) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lowest_product_price_archive';
	$product = wc_get_product( $product_id );

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ($table_exists) {
		if ($product->is_type('variation')) {
			$parent_id = $product->get_parent_id();
			$row = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE product_id IN (%d, %d)", $product_id, $parent_id));
		} else {
			$row = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %d", $product_id));
		}
		if ( ! $row ) {
			echo '<p>' . esc_html__( 'Zgodovine cen za ta izdelek ni bilo mogoče najti.', 'wooninja-lowestprice' ) . '</p>';
			return;
		}

		if ( $product->is_type( 'simple' ) ) {
			display_simple_product_price_history( $row );
		} else {
			display_variable_product_price_history( $row );
		}
	}
}

function display_simple_product_price_history($rows)
{
	echo '<table border="1" class="wp-list-table widefat fixed striped">';
	echo '<tr style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">';
	echo '<th>' . esc_html__( 'Datum spremembe', 'wooninja-lowestprice' ) . '</th><th>' . esc_html__( 'Cena', 'wooninja-lowestprice' ) . '</th></tr>';

	if ($rows) {
		foreach ($rows as $row) {
			$price_history = json_decode($row->price_history, true);

			// Filter out prices older than 30 days
			$filtered_price_history = array_filter($price_history, function ($price_record) {
				$price_date = strtotime($price_record['timestamp']);
				$days_diff = (time() - $price_date) / (60 * 60 * 24);
				return $days_diff <= 30;
			});

			$displayed_prices = [];

			$lowest_price = null;
			foreach ( $filtered_price_history as $price_record ) {
				if ( $lowest_price === null || $price_record['price'] < $lowest_price ) {
					$lowest_price = $price_record['price'];
				}
			}

			foreach ($filtered_price_history as $price_record) {
				$price_date = date('Y-m-d', strtotime($price_record['timestamp']));
				$price = $price_record['price'];

				if (!in_array($price, $displayed_prices)) {
					$highlight = $price == $lowest_price ? ' style="background: #D2DE26"' : '';
					echo '<tr' . $highlight . '><td>' . esc_html( $price_date ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
					$displayed_prices[] = $price;
				}
			}
		}
	}
	echo '</table>';
}

function display_variable_product_price_history($rows)
{
	echo '<table border="1" class="wp-list-table widefat fixed striped">';
	echo '<tr style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">';
	echo '<th>' . esc_html__( 'Atribut', 'wooninja-lowestprice' ) . '</th><th>' . esc_html__( 'Datum spremembe', 'wooninja-lowestprice' ) . '</th><th>' . esc_html__( 'Cena', 'wooninja-lowestprice' ) . '</th></tr>';

	if ($rows) {
		foreach ($rows as $row) {
			if (!empty($row->variation_data)) {
				$variation_data = json_decode($row->variation_data, true);
				$attributes = $variation_data['attributes'];
				$price_history = json_decode($row->price_history, true);

				// Filter out prices older than 30 days
				$filtered_price_history = array_filter($price_history, function ($price_record) {
					$price_date = strtotime($price_record['timestamp']);
					$days_diff = (time() - $price_date) / (60 * 60 * 24);
					return $days_diff <= 30;
				});

				$attribute_string = '';
				foreach ($attributes as $attribute_name => $attribute_value) {
					$attribute_string .= ucfirst($attribute_value);
				}

				$displayed_prices = [];

				$lowest_price = null;
				foreach ( $filtered_price_history as $price_record ) {
					if ( $lowest_price === null || $price_record['price'] < $lowest_price ) {
						$lowest_price = $price_record['price'];
					}
				}

				foreach ($filtered_price_history as $price_record) {
					$price_date = date('Y-m-d', strtotime($price_record['timestamp']));
					$price = $price_record['price'];

					if (!in_array($price, $displayed_prices)) {
						$highlight = $price == $lowest_price ? ' style="background: #D2DE26"' : '';
						echo '<tr' . $highlight . '><td>' . esc_html( $attribute_string ) . '</td><td>' . esc_html( $price_date ) . '</td><td>' . esc_html( $price ) . '</td></tr>';
						$displayed_prices[] = $price;
					}
				}
			}
		}
	}

	echo '</table>';
}

add_action( 'add_meta_boxes', 'add_display_price_changes_meta_box' );
function add_display_price_changes_meta_box() {
	add_meta_box(
		'display_price_changes_meta_box',
		'Zgodovina sprememb cen',
		'display_price_changes_meta_box_callback',
		'product', 'normal', 'default' );
}

function get_rows_for_product($product_id)
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ($table_exists) {
		$row = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %d", $product_id));
		return $row;
	}
}

function display_price_changes_meta_box_callback( $post ) {
	$product_id = $post->ID;
	$product = wc_get_product( $product_id );

	global $wpdb;
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ( ! $product ) {
		echo '<p>' . esc_html__( 'Napaka: Ni mogoče pridobiti podatkov o izdelku.', 'wooninja-lowestprice' ) . '</p>';
		return;
	}
	if ($table_exists) {
		if ( $product->is_type( 'variable' ) ) {
			$variations = $product->get_children();

			foreach ( $variations as $variation_id ) {
				$variation_product = wc_get_product( $variation_id );
				$attributes = $variation_product->get_attributes();
				$attribute_string = '';

				foreach ( $attributes as $attribute_name => $attribute_value ) {
					$attribute_string .= ucfirst( $attribute_name ) . ': ' . ucfirst( $attribute_value );
				}

				echo '<h4>' . esc_html__( 'Atributi: ', 'wooninja-lowestprice' ) . esc_html( $attribute_string ) . '</h4>';
				$rows = get_rows_for_product( $variation_id );

				display_variable_product_price_history( $rows );
			}
		} else {
			$rows = get_rows_for_product( $product_id );
			display_simple_product_price_history( $rows );
		}
	} else {
		echo '<p>' . esc_html__( 'Zgodovine cen ni bilo mogoče najti.', 'wooninja-lowestprice' ) . '</p>';
	}
}

// AJAX for variable product on front end
function enqueue_custom_script() {
	wp_enqueue_script( 'custom-script', plugins_url('/js/variation-ajax.js', __FILE__), array( 'jquery' ), '1.0', true );
	wp_localize_script('custom-script', 'message_display_lowest_price', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'lowestprice_variation_nonce' ),
	));
}
add_action( 'wp_enqueue_scripts', 'enqueue_custom_script' );

function enqueue_custom_scripts() {
	wp_enqueue_script('jquery-ui-progressbar');
	wp_enqueue_style('jquery-ui-css', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

	wp_localize_script('jquery-ui-progressbar', 'lowestprice_admin', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'nonce'    => wp_create_nonce( 'lowestprice_batch_nonce' ),
	));
}
add_action('admin_enqueue_scripts', 'enqueue_custom_scripts');

function get_variation_product_price_row($product_id, $table_name) {
	global $wpdb;

	$table_name = $wpdb->prefix . 'lowest_product_price_archive';

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );
	if ($table_exists) {
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE product_id = %d", $product_id));
		return $row;
	}
}

function display_lowest_price_for_selected_attribute($row, $attribute_name, $attribute_value, $variation_product) {
	if (!$row) {
		return null;
	}

	$variation_data = json_decode($row->variation_data, true);

	if ($variation_data['attributes'][$attribute_name] === $attribute_value) {
		$price_history = json_decode($row->price_history, true);

		if (!is_array($price_history)) {
			$price_history = [];
		}

		// Filter out prices older than 30 days
		$current_time = time();
		$price_history = array_filter($price_history, function($price_record) use ($current_time) {
			$price_date = strtotime($price_record['timestamp']);
			$days_diff = ($current_time - $price_date) / (60 * 60 * 24);
			return $days_diff <= 30;
		});

		$prices = array_map(function($price_record) {
			return $price_record['price'];
		}, $price_history);

		$lowest_price = !empty($prices) ? min($prices) : null;

		return ($lowest_price !== null) ? wc_get_price_including_tax($variation_product, ['price' => $lowest_price]) : null;
	}

	return null;
}

function get_lowest_price_callback() {
	check_ajax_referer( 'lowestprice_variation_nonce', '_ajax_nonce' );

	global $wpdb;
	$attribute_name  = isset( $_POST['attribute_name'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute_name'] ) ) : '';
	$attribute_value = isset( $_POST['attribute_value'] ) ? sanitize_text_field( wp_unslash( $_POST['attribute_value'] ) ) : '';
	$product_id      = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
	$table_name = $wpdb->prefix . 'lowest_product_price_archive';

	$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) );

	if ($table_exists) {
		$product = wc_get_product($product_id);
		$lowest_price = null;

		if ($product->is_type('variable')) {
			$variations = $product->get_available_variations();

			$equal_price = false;
			foreach ($variations as $variation) {
				$variation_product = wc_get_product($variation['variation_id']);
				$variation_id = $variation_product->get_id();

				$current = $variation_product->get_price();
				$current = wc_get_price_including_tax($variation_product,['price' => $current]);

				$row = get_variation_product_price_row($variation_id, $table_name);

				$current_lowest_price = display_lowest_price_for_selected_attribute($row, $attribute_name, $attribute_value, $variation_product);

				if ($current_lowest_price !== null && ($lowest_price === null || $current_lowest_price < $lowest_price)) {
					$lowest_price = $current_lowest_price;
				}
				if ($lowest_price == $current) {
					$equal_price = true;
				}
			}
		}

		if ($lowest_price !== null) {
			if ($equal_price) {
				$msg = esc_html__( 'Najnižja cena v zadnjih 30 dneh je enaka trenutni.', 'wooninja-lowestprice' );
				$lowest_price_show = '';
			} else {
				$msg = get_option('lowest_price_format_variable') ?: 'Najnižja cena v zadnjih 30 dneh:';
				$lowest_price_show = wc_price($lowest_price);
			}
			$color = get_option('lowest_price_format_color') ?: '';

			echo '<p class="lowest-price-msg" style="color:' . esc_attr($color) . '">' . esc_html($msg) . ' ' . wp_kses_post( $lowest_price_show ) . '</p>';
		}
	}
	wp_die();
}

add_action('wp_ajax_get_lowest_price', 'get_lowest_price_callback');
add_action('wp_ajax_nopriv_get_lowest_price', 'get_lowest_price_callback');

function display_message_lowest_price() {
	echo '<div id="custom-message"></div>';
}
add_action('woocommerce_before_add_to_cart_button','display_message_lowest_price');
