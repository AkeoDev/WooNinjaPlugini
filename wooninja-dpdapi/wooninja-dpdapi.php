<?php
/*
Plugin Name: WooNinja - DPD WebLabel API
Plugin URI: https://wooninja.si
Description: Izvoz preko API v DPD WebLabel
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-dpdapi
*/

define( 'DPD_VERSION', '2.0.0' );

define( 'DPD_PLUGIN', __FILE__ );

define( 'DPD_PLUGIN_BASENAME', plugin_basename( DPD_PLUGIN ) );

define( 'DPD_PLUGIN_NAME', trim( dirname( DPD_PLUGIN_BASENAME ), '/' ) );

define( 'DPD_PLUGIN_DIR', untrailingslashit( dirname( DPD_PLUGIN ) ) );

define( 'DPD_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


	/**
	 * Administration menu
	 */
	add_action( 'admin_menu', 'dpd_administration', 9 );

	function dpd_administration() {
		add_menu_page(
			__( 'DPD WebLabel', 'wooninja-dpdapi' ),
			__( 'DPD WebLabel', 'wooninja-dpdapi' ),
			'manage_options',
			'sm-dpd',
			'dpd_settings',
			'dashicons-feedback'
		);

		$settings = add_submenu_page(
			'sm-dpd',
			__( 'Nastavitve', 'wooninja-dpdapi' ),
			__( 'DPD WebLabel', 'wooninja-dpdapi' ),
			'manage_options',
			'sm-dpd',
			'dpd_settings'
		);

	}


	function dpd_settings() {
		include( "admin/settings.php" );
	}


	add_action( 'admin_init', 'dpd_load_admin_hooks' );

	/**
	 * Load the admin hooks
	 */
	function dpd_load_admin_hooks() {
		add_action( 'add_meta_boxes_shop_order', 'dpd_meta_box' );
		wp_register_style( 'dpd_wp_admin_css', plugin_dir_url( __FILE__ ) . 'admin/styles/style.css', false, DPD_VERSION );
		wp_enqueue_style( 'dpd_wp_admin_css' );
	}


	add_action( 'wp_ajax_smdpd_create_parcel', 'smdpd_create_parcel' );

	function smdpd_create_parcel() {
		check_ajax_referer( 'sync_products', 'security' );

		$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
		$order    = wc_get_order( $order_id );
		$address  = $order->get_address();

		if ( is_file( __DIR__ . '/api/vendor/autoload.php' ) ) {
			require __DIR__ . '/api/vendor/autoload.php';
		}

		$api     = new DPD\API();
		$numbers = $api->generateParcel( DPD\Form\ParcelGeneration::newInstance()
			->setUsername( get_option( 'apiUserName' ) )
			->setPassword( get_option( 'apiPassword' ) )
			->setName1( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() )
			->setStreet( $address['address_1'] )
			->setCity( $order->get_shipping_city() )
			->setCountry( 'SI' )
			->setPcode( $order->get_shipping_postcode() )
			->setWeight( '1' )
			->setNumOfParcel( 1 )
			->setParcelType( 'D' )
		);

		$order->add_order_note( 'Ustvarjen paket v DPD z številko: ' . $numbers[0] );
		$order->update_meta_data( 'dpd_parcel', $numbers[0] );
		$order->save();

		$redirect_url = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
		?>

		<html>
			<head>
				<link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&subset=latin,latin-ext" rel="stylesheet" type="text/css">
				<meta http-equiv="Refresh" content="2; URL=<?php echo esc_url( $redirect_url ); ?>" />
				<style>
					h3 {
						font-family: 'Open Sans', sans-serif;
					}
				</style>
			</head>
			<body>
				<h3>Paket je bil uspešno poslan v DPD ! <strong>Preusmerjam na naročilo...</strong></h3>
			</body>
		</html>

		<?php
	}


	add_action( 'wp_ajax_smdpd_print_parcel', 'smdpd_print_parcel' );

	function smdpd_print_parcel() {
		check_ajax_referer( 'sync_products', 'security' );

		$order_id = isset( $_GET['order'] ) ? absint( $_GET['order'] ) : 0;
		$order    = wc_get_order( $order_id );

		$parcel = array( $order->get_meta( 'dpd_parcel' ) );

		if ( is_file( __DIR__ . '/api/vendor/autoload.php' ) ) {
			require __DIR__ . '/api/vendor/autoload.php';
		}

		$api = new DPD\API();

		$data = array(
			'username' => get_option( 'apiUserName' ),
			'password' => get_option( 'apiPassword' ),
			'parcels'  => $parcel[0],
		);

		$numbers = $api->printParcel( $data );

		header( 'Content-type: application/octet-stream' );
		header( 'Content-Disposition: attachment; filename=parcel-' . $parcel[0] . '.pdf' );
		echo $numbers['errlog'];
	}


	/**
	 * Add the meta box on the single order page
	 */
	function dpd_meta_box() {
		add_meta_box( 'dpd-box', __( 'DPD WebLabel', 'wooninja-dpdapi' ), 'dpd_box_content', 'shop_order', 'side', 'low' );
	}

	/**
	 * Create the meta box content on the single order page
	 */
	function dpd_box_content() {
		global $post_id;

		$order = wc_get_order( $post_id );

		$invoice = $order->get_meta( 'dpd_parcel' );

		if ( is_file( __DIR__ . '/api/vendor/autoload.php' ) ) {
			require __DIR__ . '/api/vendor/autoload.php';
		}

		$api = new DPD\API();

		?>

		<div class="print-actions smdpd-spletni-moduli">

			<?php if ( $invoice == '' ) { ?>

				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=smdpd_create_parcel&spletnimoduli=true&order=' . $post_id ), 'sync_products', 'security' ) ); ?>" class="button"><?php esc_html_e( 'Kreiraj paket v WebLabel', 'wooninja-dpdapi' ); ?></a>

			<?php } else {

				$status = $api->getParcelStatus( get_option( 'dpd_api_token', '' ), $invoice );

				?>

				<?php esc_html_e( 'Številka paketa:', 'wooninja-dpdapi' ); ?> <br>
				<strong><?php echo esc_html( $invoice ); ?></strong>

				<br><br>

				<?php esc_html_e( 'Status pošiljke:', 'wooninja-dpdapi' ); ?> <br>
				<strong><?php echo esc_html( $status ); ?></strong>
				<br><br>

				<a href="<?php echo esc_url( $api->getTrackingUrl( $invoice, 'en_SI' ) ); ?>" target="_blank" class="button"><?php esc_html_e( 'Tracking pošiljke', 'wooninja-dpdapi' ); ?></a>

				<br>

				<hr>

				<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-ajax.php?action=smdpd_print_parcel&spletnimoduli=true&order=' . $post_id ), 'sync_products', 'security' ) ); ?>" class="button button-primary" style="width: 100%;text-align:center;"><?php esc_html_e( 'Natisni nalepko', 'wooninja-dpdapi' ); ?></a>

			<?php } ?>

		</div>

		<?php
	}


	if ( ! class_exists( 'Woo_DPD_Export' ) ) {

		class Woo_DPD_Export {

			public function __construct() {
				if ( is_admin() ) {
					add_action( 'admin_footer', array( $this, 'custom_bulk_admin_footer' ) );
					add_action( 'load-edit.php', array( $this, 'custom_bulk_action' ) );
					add_action( 'admin_notices', array( $this, 'custom_bulk_admin_notices' ) );
				}
			}


			/**
			 * Step 1: add the custom Bulk Action to the select menus
			 */
			function custom_bulk_admin_footer() {
				global $post_type;

				if ( $post_type == 'shop_order' ) {
					?>
					<script type="text/javascript">
						jQuery(document).ready(function() {
							jQuery('<option>').val('export').text('<?php echo esc_js( __( 'Izvozi v DPD - Weblabel', 'wooninja-dpdapi' ) ); ?>').appendTo("select[name='action']");
							jQuery('<option>').val('export').text('<?php echo esc_js( __( 'Izvozi v DPD - Weblabel', 'wooninja-dpdapi' ) ); ?>').appendTo("select[name='action2']");
						});
					</script>
					<?php
				}
			}


			/**
			 * Step 2: handle the custom Bulk Action
			 */
			function custom_bulk_action() {
				global $typenow;
				$post_type = $typenow;

				if ( $post_type == 'shop_order' ) {

					$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
					$action        = $wp_list_table->current_action();

					$allowed_actions = array( 'export' );
					if ( ! in_array( $action, $allowed_actions ) ) {
						return;
					}

					check_admin_referer( 'bulk-posts' );

					if ( isset( $_REQUEST['post'] ) ) {
						$post_ids = array_map( 'intval', $_REQUEST['post'] );
					}

					if ( empty( $post_ids ) ) {
						return;
					}

					$sendback = remove_query_arg( array( 'exported', 'untrashed', 'deleted', 'ids' ), wp_get_referer() );
					if ( ! $sendback ) {
						$sendback = admin_url( "edit.php?post_type=$post_type" );
					}

					$pagenum  = $wp_list_table->get_pagenum();
					$sendback = add_query_arg( 'paged', $pagenum, $sendback );

					switch ( $action ) {
						case 'export':
							$exported = 0;
							foreach ( $post_ids as $post_id ) {
								if ( ! $this->perform_export( $post_id ) ) {
									wp_die( __( 'Error exporting orders.', 'wooninja-dpdapi' ) );
								}
								$exported++;
							}

							$sendback = add_query_arg( array( 'exported' => $exported, 'ids' => join( ',', $post_ids ) ), $sendback );
							break;

						default:
							return;
					}

					$sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

					wp_redirect( $sendback );
					exit();
				}
			}


			/**
			 * Step 3: display an admin notice on the Posts page after exporting
			 */
			function custom_bulk_admin_notices() {
				global $post_type, $pagenow;

				if ( $pagenow == 'edit.php' && $post_type == 'shop_order' && isset( $_REQUEST['exported'] ) && (int) $_REQUEST['exported'] ) {
					$message = sprintf( _n( 'Izvoz naročila uspel.', '%s naročil je bilo uspešno izvoženih.', absint( $_REQUEST['exported'] ), 'wooninja-dpdapi' ), number_format_i18n( absint( $_REQUEST['exported'] ) ) );
					echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';
					$nonce = wp_create_nonce( 'verify-dpd' );
					$ids   = isset( $_GET['ids'] ) ? esc_attr( sanitize_text_field( $_GET['ids'] ) ) : '';
					echo "<script type='text/javascript'>
					window.onload = function(){
						window.open('" . esc_url( plugin_dir_url( __FILE__ ) . 'export.php?export=true&wolf_attack=' . $nonce . '&alpha=true&ids=' . $ids ) . "', '_blank');
					}
					</script>";
				}
			}

			function perform_export( $post_id ) {
				return true;
			}
		}
	}

	new Woo_DPD_Export();


} else {

	function DPD_dpd_error() {
		$class   = 'error';
		$message = __( 'Modul za integracijo v DPD WebLabel zahteva WooCommerce.', 'wooninja-dpdapi' );
		echo '<div class="' . esc_attr( $class ) . '"><p>' . esc_html( $message ) . '</p></div>';
	}
	add_action( 'admin_notices', 'DPD_dpd_error' );

}
