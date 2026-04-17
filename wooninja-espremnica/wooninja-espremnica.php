<?php
/**
 * Plugin Name: WooNinja - eSpremnica
 * Plugin URI: https://wooninja.si
 * Description: Izvoz naročil eSpremnica
 * Version: 2.0.0
 * Author: Humanfrog d.o.o.
 * License: GPLv2 or later
 * Text Domain: wooninja-espremnica
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( version_compare( get_option( 'woocommerce_db_version', '0' ), '3', '<' ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Žal uporabljate starejšo verzijo WooCommerce kot jo podpira eSpremnica Pošta Slovenije modul. Prosimo nadgradite WooCommerce.', 'wooninja-espremnica' ); ?></p>
		</div>
		<?php
	} );

	return false;
}

if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
	add_action( 'admin_notices', function() {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php esc_html_e( 'Vtičnik eSpremnica za svoje delovanje potrebuje aktiven vtičnik WooCommerce.', 'wooninja-espremnica' ); ?></p>
		</div>
		<?php
	} );

	return false;
}

require dirname( __FILE__ ) . '/inc/espremnica-csv-api.php';
$espremnica_csv_export = new Woo_eSpremnica_CSV_API_Export();

function load_script() {
	wp_enqueue_style(
		'sweetalerts2',
		plugins_url( 'inc/css/sweetalert2.css', __FILE__ )
	);

	wp_enqueue_script(
		'sweetalert2',
		plugins_url( 'inc/js/sweetalert2.js', __FILE__ ),
		array( 'jquery', 'common' ),
		false,
		true
	);

	wp_enqueue_script(
		'jquery-tiptip',
		plugins_url( 'inc/js/jquery.tipTip.js', __FILE__ ),
		array( 'jquery', 'common' ),
		false,
		true
	);
}

function sm_wf_plugin_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=sm_espremnica_csv' ) ) . '">' . esc_html__( 'Nastavitve', 'wooninja-espremnica' ) . '</a>',
	);

	return array_merge( $plugin_links, $links );
}

add_action( 'admin_menu', 'opt_espremnica_csv_administration', 9 );
add_action( 'admin_enqueue_scripts', 'load_script' );

function opt_espremnica_csv_administration() {
	add_menu_page(
		__( 'eSpremnica', 'wooninja-espremnica' ),
		__( 'eSpremnica', 'wooninja-espremnica' ),
		'manage_options',
		'opt_espremnica_csv',
		'opt_espremnica_csv_settings',
		'dashicons-feedback'
	);

	add_submenu_page(
		'opt_espremnica_csv',
		__( 'Nastavitve', 'wooninja-espremnica' ),
		__( 'Nastavitve', 'wooninja-espremnica' ),
		'manage_options',
		'opt_espremnica_csv',
		'opt_espremnica_csv_settings'
	);

	add_submenu_page(
		'opt_espremnica_csv',
		__( 'Pregled pošiljk', 'wooninja-espremnica' ),
		__( 'Pregled pošiljk', 'wooninja-espremnica' ),
		'manage_options',
		'opt_espremnica_queue',
		'opt_espremnica_queue'
	);
}

function opt_espremnica_queue() {
	include dirname( __FILE__ ) . '/admin/espremnica-view-all.php';
}

function opt_espremnica_csv_settings() {
	include dirname( __FILE__ ) . '/admin/settings.php';
}

function get_espremnica_csv_dodatne_storitve() {
	$dodatne_storitve = file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/dodatneStoritve.json' );
	$dodatne_storitve = json_decode( $dodatne_storitve, true );
	$dodatne_storitve = array_values( $dodatne_storitve );

	return $dodatne_storitve;
}

function get_espremnica_csv_vrste_posiljk() {
	$vrste_posiljke = file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/vrstePosiljk.json' );
	$vrste_posiljke = json_decode( $vrste_posiljke, true );
	$vrste_posiljke = array_values( $vrste_posiljke );

	return $vrste_posiljke;
}

function get_espremnica_csv_koda_drzave() {
	return returnCountriesDataBy2LetterCode();
}

function vrniKodoDrzave( $country, $countries ) {
	if ( isset( $countries[ $country ] ) ) {
		return $countries[ $country ]['country_code_iso'];
	}

	return '705';
}

function returnCountriesDataBy2LetterCode() {
	$result = array();

	$countries_data = file_get_contents( plugin_dir_path( __FILE__ ) . 'inc/drzave.json' );
	$countries_data = json_decode( $countries_data, true );
	$countries_data = array_values( $countries_data );

	foreach ( $countries_data as $index => $country_data ) {
		$country_name          = $country_data['name'];
		$country_code_2_letter = $country_data['alpha-2'];
		$country_code_iso_     = $country_data['country-code'];

		$country = array(
			'country_name'          => $country_name,
			'country_code_2_letter' => $country_code_2_letter,
			'country_code_iso'      => $country_code_iso_,
		);

		$result[ $country_code_2_letter ] = $country;
	}

	return $result;
}

abstract class Espremnica_CSV_Meta_Box {

	public static function add() {
		$screens = array( 'shop_order' );
		foreach ( $screens as $screen ) {
			add_meta_box(
				'espremnica_csv_order_options',
				'eSpremnica',
				array( self::class, 'html' ),
				$screen,
				'side'
			);
		}
	}

	public static function save( $post_id ) {
		if ( ! isset( $_POST['espremnica_csv_meta_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['espremnica_csv_meta_nonce'] ) ), 'espremnica_csv_meta_save' ) ) {
			return;
		}

		$order = wc_get_order( $post_id );
		if ( ! $order ) {
			return;
		}

		$vse_storitve = get_espremnica_csv_dodatne_storitve();

		if ( isset( $_POST['espremnica_csv_dodatne_storitve'] ) ) {
			$dodatne_storitve = array_map( 'sanitize_text_field', wp_unslash( $_POST['espremnica_csv_dodatne_storitve'] ) );
			$order->update_meta_data(
				'espremnica_csv_dodatne_storitve',
				$dodatne_storitve
			);

			foreach ( $dodatne_storitve as $key => $value ) {
				foreach ( $vse_storitve as $str ) {
					if ( $key == $str['code'] ) {
						if ( isset( $str['additional'] ) ) {
							foreach ( $str['additional'] as $add ) {
								$field_value = isset( $_POST[ $add['field'] ] ) ? sanitize_text_field( wp_unslash( $_POST[ $add['field'] ] ) ) : '';
								$order->update_meta_data( $add['field'], $field_value );
							}
						}
					}
				}
			}
		} else {
			$order->update_meta_data( 'espremnica_csv_dodatne_storitve', array() );
		}

		if ( isset( $_POST['espremnica_csv_vrste_posiljk'] ) ) {
			$order->update_meta_data(
				'espremnica_csv_vrste_posiljk',
				sanitize_text_field( wp_unslash( $_POST['espremnica_csv_vrste_posiljk'] ) )
			);

			$order->update_meta_data( 'espremnica_csv_saved_before', 'yes' );
		}

		if ( isset( $_POST['opomba_espremnica_csv'] ) ) {
			$order->update_meta_data(
				'opomba_espremnica_csv',
				sanitize_textarea_field( wp_unslash( $_POST['opomba_espremnica_csv'] ) )
			);

			$order->update_meta_data( 'espremnica_csv_saved_before', 'yes' );
		}

		$order->save();
	}

	public static function html( $post ) {
		wp_nonce_field( 'espremnica_csv_meta_save', 'espremnica_csv_meta_nonce' );

		$order = wc_get_order( $post->ID );

		$odposlano       = $order->get_meta( 'espremnica_odposlano' );
		$saved_before    = $order->get_meta( 'espremnica_csv_saved_before' );
		$deletedSticker  = $order->get_meta( 'sticker_deleted_cron' );

		$espremnica_csv_dodatne_storitve_selected = $order->get_meta( 'espremnica_csv_dodatne_storitve' );

		$izbrana_vrsta = $order->get_meta( 'espremnica_csv_vrste_posiljk' );
		if ( ! $izbrana_vrsta ) {
			$izbrana_vrsta = array();
		}
		$opomba        = $order->get_meta( 'opomba_espremnica_csv' );

		$st_paketa = $order->get_meta( 'sprejemna_stevilka' );

		$hide          = '';
		$hideNoPackage = 'display:none;';

		if ( $st_paketa == '' ) {
			$hide          = 'display:none;';
			$hideNoPackage = '';
		}

		if ( ! $opomba ) {
			$opomba = $order->get_customer_note();
		}

		if ( $saved_before == '' ) {
			$default_dodatne_storitve                 = get_option( 'espremnica_csv_default_dodatne_storitve', array() );
			$espremnica_csv_dodatne_storitve_selected  = $default_dodatne_storitve;
			$izbrana_vrsta                             = get_option( 'espremnica_csv_default_vrste_posiljk', array() );
		}

		$enabled_vrste_posiljk  = get_option( 'espremnica_csv_vrste_posiljk', array() );
		$vse_vrste_posiljk      = get_espremnica_csv_vrste_posiljk();

		$enabled_dodatne_storitve = get_option( 'espremnica_csv_dodatne_storitve', array() );
		$vse_dodatne_storitve     = get_espremnica_csv_dodatne_storitve();

		$order = wc_get_order( $post->ID );
		?>

		<style>
			.inside .woocommerce-help-tip {
				margin-left: 0;
			}
		</style>

		<div class="js-espremnica-csv-form">
			<strong><?php esc_html_e( 'Vrsta pošiljke:', 'wooninja-espremnica' ); ?></strong> <br>
			<select name="espremnica_csv_vrste_posiljk">
				<option value="-1"><?php esc_html_e( '-- Izberi privzeti način vrste pošiljke --', 'wooninja-espremnica' ); ?></option>
				<?php
				foreach ( $vse_vrste_posiljk as $vrsta ) {
					if ( in_array( $vrsta['id'], $enabled_vrste_posiljk, true ) ) {
						$click = '';
						if ( ! $vrsta['enable'] ) {
							$click = 'onclick="alert(\'' . esc_js( 'Storitev je trenutno v izdelavi. Kontaktirajte nas za več informacij!' ) . '\');return false;"';
							?>
							<div class="disabled">
							<?php
						}
						?>

						<option <?php echo $click; ?> value="<?php echo esc_attr( $vrsta['id'] ); ?>" <?php if ( in_array( $vrsta['id'], $izbrana_vrsta, true ) ) { echo "selected='selected'"; } ?>> <?php echo esc_html( $vrsta['title'] ); ?></option>
						<br>

						<?php
						$click = '';
						if ( ! $vrsta['enable'] ) {
							?>
							</div>
							<?php
						}
					}
				}
				?>
			</select><br><br>
			<strong><?php esc_html_e( 'Dodatne storitve pošiljke:', 'wooninja-espremnica' ); ?></strong><br>
			<?php
			if ( ! is_array( $espremnica_csv_dodatne_storitve_selected ) ) {
				$espremnica_csv_dodatne_storitve_selected = array();
			}

			foreach ( $vse_dodatne_storitve as $str ) {
				if ( in_array( $str['id'], $enabled_dodatne_storitve, true ) && ( $str['id'] !== 'ODK' || ( $str['id'] === 'ODK' && $order->get_date_paid( 'view' ) === null ) ) ) {
					?>
					<input type="checkbox" name="espremnica_csv_dodatne_storitve[]" value="<?php echo esc_attr( $str['id'] ); ?>" <?php if ( in_array( $str['id'], $espremnica_csv_dodatne_storitve_selected, true ) ) { echo 'checked'; } ?>> <?php echo esc_html( $str['title'] ); ?> <?php echo wc_help_tip( $str['description'] ); ?>
					<br>
					<?php
				}
			}
			?>
			<br>
			<strong><?php esc_html_e( 'Opomba za kurirja:', 'wooninja-espremnica' ); ?></strong>
			<textarea name="opomba_espremnica_csv" id="opomba_espremnica_csv" style="width: 100%; height: 200px;"><?php echo esc_textarea( $opomba ); ?></textarea>

		</div>

		<br>

		<div class="stevilka-paketa-espremnice" style="<?php echo esc_attr( $hide ); ?>">
			<h3><?php esc_html_e( 'Številka paketa:', 'wooninja-espremnica' ); ?> <br><span class="number" style="display:block; padding: 4px; background: #2ecc71;color: #FFF;text-align:center; border-radius:5px;"><?php echo esc_html( $st_paketa ); ?></span><?php
				if ( $odposlano === 'DA' ) {
					echo '<span style="display:block;padding:4px;color:green;text-align:center;">' . esc_html__( 'ODPOSLANO', 'wooninja-espremnica' ) . '</span>';
				} else {
					echo '<span style="display:block;padding:4px;color:red;text-align:center;">' . esc_html__( 'NI ODPOSLANO', 'wooninja-espremnica' ) . '</span>';
				}
			?></h3>
		</div>

		<a href="" class="create-espremnica-csv button" data-order-id="<?php echo esc_attr( $post->ID ); ?>" style="<?php echo esc_attr( $hideNoPackage ); ?>width: 100%;text-align:center;">
			<?php esc_html_e( 'Kreiraj nalepko', 'wooninja-espremnica' ); ?>
		</a>
		<?php
		if ( $deletedSticker !== 'DA' ) {
			?>
			<a href="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'inc/download/eSpremnica-' . md5( $st_paketa ) . '.pdf' ); ?>" target="_blank" class="print-from-espremnice-api button" data-order-id="<?php echo esc_attr( $post->ID ); ?>" style="<?php echo esc_attr( $hide ); ?>width: 100%;text-align:center;">
				<?php esc_html_e( 'Natisni nalepko', 'wooninja-espremnica' ); ?>
			</a>
			<?php
		} else {
			?>
			<span style="display:block;width:100%;text-align:left;"><?php esc_html_e( 'Prenos nalepke ni mogoč, ker je bila datoteka po 30 dneh zaradi čiščenja prostora izbrisana', 'wooninja-espremnica' ); ?></span>
			<?php
		}
		if ( $odposlano !== 'DA' ) {
			?>
			<br>
			<br>
			<a href="" class="delete-espremnica-entry-pending button" data-order-id="<?php echo esc_attr( $post->ID ); ?>" style="<?php echo esc_attr( $hide ); ?>width: 100%;text-align:center;">
				<?php esc_html_e( 'Izbriši nalepko', 'wooninja-espremnica' ); ?>
			</a>
			<?php
		}
		?>

		<div class="ajax-loader-espremnica-csv" style="text-align:center; display:none;">
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'inc/img/ajax-loader.gif' ); ?>" alt="">
		</div>

		<style>
			.select2 {
				max-width: 100%;
			}
		</style>

		<?php
	}
}

add_action( 'add_meta_boxes', array( 'Espremnica_CSV_Meta_Box', 'add' ) );
add_action( 'save_post', array( 'Espremnica_CSV_Meta_Box', 'save' ) );

function postaSlovenijeCheckDeleteFiles() {
	$files = glob( plugin_dir_path( __FILE__ ) . 'inc/download/*.pdf' );
	foreach ( $files as $filename ) {
		if ( time() - filemtime( $filename ) > 30 * 24 * 3600 ) {
			unlink( $filename );
		}
	}

	$daysago = date( 'd.m.Y', strtotime( '-30 days' ) );

	$orders = wc_get_orders( array(
		'status'     => 'any',
		'limit'      => -1,
		'meta_query' => array(
			array(
				'key'   => 'sticker_generated',
				'value' => $daysago,
			),
		),
	) );

	foreach ( $orders as $order ) {
		$order->update_meta_data( 'sticker_deleted_cron', 'DA' );
		$order->save();
	}
}

function GetPostaTotal( $in ) {
	return str_replace( '.', ',', $in );
}

if ( ! wp_next_scheduled( 'postaSlovenijeCheckDeleteFiles' ) ) {
	wp_schedule_event( time(), 'hourly', 'postaSlovenijeCheckDeleteFiles' );
}
add_action( 'postaSlovenijeCheckDeleteFiles', 'postaSlovenijeCheckDeleteFiles', 10, 1 );
