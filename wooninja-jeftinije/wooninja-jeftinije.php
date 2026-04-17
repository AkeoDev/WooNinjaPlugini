<?php
/*
Plugin Name: WooNinja - Jeftinije
Plugin URI: https://wooninja.si
Description: Enostaven izvoz podatkov o produktih v formatu za Jeftinje
Version: 2.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-jeftinije
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_writable( dirname( __FILE__ ) . '/exports' ) ) {
	add_action( 'admin_notices', function () {
		?>
		<div class="notice notice-error is-dismissible">
			<p><?php
				printf(
					/* translators: %s: plugin name */
					esc_html__( 'Vtičnik %s zahteva pravice pisanja (777) v imeniku "exports".', 'wooninja-jeftinije' ),
					'<strong>' . esc_html( 'WooNinja - Jeftinije' ) . '</strong>'
				);
			?></p>
		</div>
		<?php
	} );
}

if ( ! class_exists( 'Woo_Jeftinje_Export' ) ) {

	class Woo_Jeftinje_Export {

		public function __construct() {

			if ( is_admin() ) {
				add_action( 'admin_footer', array( $this, 'custom_bulk_admin_footer' ) );
				add_action( 'load-edit.php', array( $this, 'custom_bulk_action' ) );
				add_action( 'admin_notices', array( $this, 'custom_bulk_admin_notices' ) );

				add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'sm_wf_plugin_action_links' ) );

				add_action( 'admin_menu', array( $this, 'jeftinjesi_administration' ), 9 );

				add_action( 'woocommerce_product_options_general_product_data', array( $this, 'woo_add_custom_general_fields' ) );
				add_action( 'woocommerce_process_product_meta', array( $this, 'woo_add_custom_general_fields_save' ) );

				add_action( 'woocommerce_product_quick_edit_end', function () {
					global $post;
					?>
					<br class="clear">
					<label class="alignleft">
						<span class="title"><?php esc_html_e( 'Izklopi Jeftinje.hr za ta produkt', 'wooninja-jeftinije' ); ?></span>
						<span class="input-text-wrap">
							<input type="text" name="_jeftinje" class="text" placeholder="" value="<?php echo esc_attr( get_post_meta( $post->ID, '_jeftinje', true ) ); ?>">
						</span>
					</label>
					<?php
				} );

				add_action( 'woocommerce_product_quick_edit_save', function ( $product ) {
					if ( $product->is_type( 'simple' ) || $product->is_type( 'external' ) ) {
						$post_id = $product->get_id();
						if ( isset( $_REQUEST['_jeftinje'] ) ) {
							$jeftinje_value = sanitize_text_field( wp_unslash( $_REQUEST['_jeftinje'] ) );
							update_post_meta( $post_id, '_jeftinje', wc_clean( $jeftinje_value ) );
						}
					}
				}, 10, 1 );

				add_action( 'manage_product_posts_custom_column', function ( $column, $post_id ) {
					if ( 'name' === $column ) {
						?>
						<div class="hidden _jeftinje_inline" id="_jeftinje_inline_<?php echo esc_attr( $post_id ); ?>">
							<div id="_jeftinje"><?php echo esc_html( get_post_meta( $post_id, '_jeftinje', true ) ); ?></div>
						</div>
						<?php
					}
				}, 99, 2 );
			}
		}

		function woo_add_custom_general_fields() {
			global $post;

			echo '<div class="options_group">';

			$cbvalue = get_post_meta( $post->ID, '_jeftinje', true );

			woocommerce_wp_checkbox(
				array(
					'id'          => '_jeftinje',
					'label'       => __( 'Izklopi produkt na Jeftinje.hr', 'wooninja-jeftinije' ),
					'value'       => $cbvalue,
					'cbvalue'     => 'yes',
					'desc_tip'    => 'true',
					'description' => '',
				)
			);

			woocommerce_wp_textarea_input(
				array(
					'id'          => '_jeftinje_opis',
					'label'       => __( 'Opis produkta za Jeftinje.hr', 'wooninja-jeftinije' ),
					'placeholder' => '',
					'description' => '',
					'value'       => get_post_meta( $post->ID, '_jeftinje_opis', true ),
				)
			);

			echo '</div>';
		}

		function woo_add_custom_general_fields_save( $post_id ) {
			if ( isset( $_POST['_jeftinje'] ) ) {
				$jeftinje_field = sanitize_text_field( wp_unslash( $_POST['_jeftinje'] ) );
				update_post_meta( $post_id, '_jeftinje', $jeftinje_field );
			}

			if ( isset( $_POST['_jeftinje_opis'] ) ) {
				$jeftinje_opis = sanitize_textarea_field( wp_unslash( $_POST['_jeftinje_opis'] ) );
				update_post_meta( $post_id, '_jeftinje_opis', $jeftinje_opis );
			}
		}

		/**
		 * Administration menu.
		 */
		public function jeftinjesi_administration() {
			add_menu_page(
				__( 'Jeftinje.hr', 'wooninja-jeftinije' ),
				__( 'Jeftinje.hr', 'wooninja-jeftinije' ),
				'manage_options',
				'sm_jeftinjesi',
				array( $this, 'jeftinjesi_settings' ),
				'dashicons-feedback'
			);

			add_submenu_page(
				'sm_jeftinjesi',
				__( 'Nastavitve', 'wooninja-jeftinije' ),
				__( 'Jeftinje.hr', 'wooninja-jeftinije' ),
				'manage_options',
				'sm_jeftinjesi',
				array( $this, 'jeftinjesi_settings' )
			);

		}

		public function jeftinjesi_settings() {
			include dirname( __FILE__ ) . '/admin/settings.php';
		}

		public function sm_wf_plugin_action_links( $links ) {
			$plugin_links = array(
				'<a href="' . esc_url( admin_url( 'admin.php?page=sm_jeftinjesi' ) ) . '">' . esc_html__( 'Nastavitve', 'wooninja-jeftinije' ) . '</a>',
			);

			return array_merge( $plugin_links, $links );
		}

		/**
		 * Add the custom Bulk Action to the select menus.
		 */
		function custom_bulk_admin_footer() {
			global $post_type, $pagenow;

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			$category = 'false';
			if ( isset( $_GET['product_cat'] ) ) {
				$category = sanitize_text_field( wp_unslash( $_GET['product_cat'] ) );
			}

			if ( 'product' === $post_type ) {
				$nonce = wp_create_nonce( 'verify-Jeftinje' );
				$export_url = esc_url( plugin_dir_url( __FILE__ ) . 'export.php?export=true&wolf_attack=' . $nonce . '&category=' . urlencode( $category ) );
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery(".bulkactions").prepend('<a href="<?php echo $export_url; ?>" class="button button-jeftinje-si" style="float:left;"><?php echo esc_js( __( 'Izvozi vse produkte (Jeftinje.hr)', 'wooninja-jeftinije' ) ); ?></a>');
					});
				</script>
				<?php
			}
		}

		/**
		 * Handle the custom Bulk Action.
		 */
		function custom_bulk_action() {
			global $typenow, $pagenow;

			if ( 'edit.php' !== $pagenow ) {
				return;
			}

			$post_type = $typenow;

			if ( 'product' !== $post_type ) {
				return;
			}

			$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
			$action        = $wp_list_table->current_action();

			$allowed_actions = array( 'export' );
			if ( ! in_array( $action, $allowed_actions, true ) ) {
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
							wp_die( esc_html__( 'Error exporting products.', 'wooninja-jeftinije' ) );
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

		/**
		 * Display an admin notice on the Posts page after exporting.
		 */
		function custom_bulk_admin_notices() {
			global $post_type, $pagenow;

			if ( 'edit.php' !== $pagenow || 'product' !== $post_type || ! isset( $_REQUEST['exported'] ) || ! (int) $_REQUEST['exported'] ) {
				return;
			}

			$exported = absint( $_REQUEST['exported'] );
			$message  = sprintf(
				_n( 'Izvoz produktov uspel.', '%s produktov je bilo uspešno izvoženih.', $exported, 'wooninja-jeftinije' ),
				number_format_i18n( $exported )
			);
			echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';

			$nonce = wp_create_nonce( 'verify-Jeftinje' );
			$ids   = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( $_GET['ids'] ) ) : '';
			$url   = esc_url( plugin_dir_url( __FILE__ ) . 'export.php?export=true&wolf_attack=' . $nonce . '&alpha=true&ids=' . urlencode( $ids ) );
			?>
			<script type="text/javascript">
				window.onload = function(){
					window.open('<?php echo $url; ?>', '_blank');
				}
			</script>
			<?php
		}

		function perform_export( $post_id ) {
			return true;
		}
	}
}

add_action( 'admin_footer', 'jftn_custom_bulk_admin_footer' );
add_action( 'load-edit.php', 'jftn_custom_bulk_action' );
add_action( 'admin_notices', 'jftn_custom_bulk_admin_notices' );

/**
 * Add the custom Bulk Action to the select menus (standalone).
 */
function jftn_custom_bulk_admin_footer() {
	global $post_type, $pagenow;

	if ( 'edit.php' !== $pagenow ) {
		return;
	}

	if ( 'product' === $post_type ) {
		?>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('<option>').val('exported_jftn').text('<?php echo esc_js( __( 'Export v Jeftinje', 'wooninja-jeftinije' ) ); ?>').appendTo("select[name='action']");
				jQuery('<option>').val('exported_jftn').text('<?php echo esc_js( __( 'Export v Jeftinje', 'wooninja-jeftinije' ) ); ?>').appendTo("select[name='action2']");
			});
		</script>
		<?php
	}
}

/**
 * Handle the custom Bulk Action (standalone).
 */
function jftn_custom_bulk_action() {
	global $typenow;
	$post_type = $typenow;

	if ( 'product' !== $post_type ) {
		return;
	}

	$wp_list_table = _get_list_table( 'WP_Posts_List_Table' );
	$action        = $wp_list_table->current_action();

	$allowed_actions = array( 'exported_jftn' );
	if ( ! in_array( $action, $allowed_actions, true ) ) {
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
		case 'exported_jftn':
			$exported = 0;
			foreach ( $post_ids as $post_id ) {
				if ( ! jftn_perform_export( $post_id ) ) {
					wp_die( esc_html__( 'Error exporting orders.', 'wooninja-jeftinije' ) );
				}
				$exported++;
			}
			$sendback = add_query_arg( array( 'exported_jftn' => $exported, 'ids' => join( ',', $post_ids ) ), $sendback );
			break;

		default:
			return;
	}

	$sendback = remove_query_arg( array( 'action', 'action2', 'tags_input', 'post_author', 'comment_status', 'ping_status', '_status', 'post', 'bulk_edit', 'post_view' ), $sendback );

	wp_redirect( $sendback );
	exit();
}

/**
 * Display an admin notice after standalone bulk export.
 */
function jftn_custom_bulk_admin_notices() {
	global $post_type, $pagenow;

	if ( 'edit.php' !== $pagenow || 'product' !== $post_type || ! isset( $_REQUEST['exported_jftn'] ) || ! (int) $_REQUEST['exported_jftn'] ) {
		return;
	}

	$exported = absint( $_REQUEST['exported_jftn'] );
	$message  = sprintf(
		_n( 'Izvoz produkta uspel.', '%s produktov je bilo uspešno izvoženih.', $exported, 'wooninja-jeftinije' ),
		number_format_i18n( $exported )
	);
	echo '<div class="updated"><p>' . esc_html( $message ) . '</p></div>';

	$nonce = wp_create_nonce( 'verify-Jeftinje' );
	$ids   = isset( $_GET['ids'] ) ? sanitize_text_field( wp_unslash( $_GET['ids'] ) ) : '';
	$url   = esc_url( plugin_dir_url( __FILE__ ) . 'export.php?export=true&wolf_attack=' . $nonce . '&optininja=true&ids=' . urlencode( $ids ) );
	?>
	<script type="text/javascript">
		window.onload = function(){
			window.open('<?php echo $url; ?>', '_blank');
		}
	</script>
	<?php
}

function jftn_perform_export( $post_id ) {
	return true;
}

add_filter( 'tag_row_actions', 'jeftinje_taxonomy_export_action', 10, 2 );

/**
 * Add export action link to product category taxonomy rows.
 *
 * @param array  $actions Existing row actions.
 * @param object $tag     The term object.
 * @return array Modified row actions.
 */
function jeftinje_taxonomy_export_action( $actions, $tag ) {
	if ( 'product_cat' === $tag->taxonomy ) {
		$nonce = wp_create_nonce( 'verify-Jeftinje' );
		$actions['sm-jeftinje-view-class']   = '<a href="' . esc_url( admin_url( 'edit.php?product_cat=' . $tag->slug . '&post_type=product' ) ) . '">' . esc_html__( 'Poglej produkte', 'wooninja-jeftinije' ) . '</a>';
		$actions['sm-jeftinje-export-class'] = '<a href="' . esc_url( plugin_dir_url( __FILE__ ) . 'export.php?export=true&wolf_attack=' . $nonce . '&optininja=true&category=' . $tag->slug ) . '">' . esc_html__( 'Izvozi v Jeftinje.hr', 'wooninja-jeftinije' ) . '</a>';
	}
	return $actions;
}

new Woo_Jeftinje_Export();
