<?php
/**
 * WooNinja Jeftinije - admin nastavitve.
 *
 * @package WooNinja_Jeftinije
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
global $plugin_page;

if ( isset( $_REQUEST['command'] ) && 'shrani-podatke' === sanitize_text_field( $_REQUEST['command'] ) ) {
	if ( isset( $_POST['jeftinije_settings_nonce'] ) && wp_verify_nonce( $_POST['jeftinije_settings_nonce'], 'jeftinije_save_settings' ) ) {
		if ( isset( $_POST['Submit'] ) && 'Shrani' === $_POST['Submit'] ) {
			update_option( 'jeftinje_default_opis', sanitize_text_field( $_POST['jeftinje_default_opis'] ) );
			update_option( 'jeftinje_attributi', sanitize_text_field( $_POST['jeftinje_attributi'] ) );
			update_option( 'jeftinje_moreimages', sanitize_text_field( $_POST['ceneje_moreimages'] ) );
		}
	}
}

$all_categories = get_categories( array(
	'taxonomy'     => 'product_cat',
	'orderby'      => 'name',
	'show_count'   => 0,
	'pad_counts'   => 0,
	'hierarchical' => 1,
	'title_li'     => '',
	'hide_empty'   => 0,
) );
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Nastavitve Woocommerce -> Jeftinje.hr', 'wooninja-jeftinije' ); ?></h2>

	<hr>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div class="postbox-container smdpd">
				<div id="normal-sortables" class="ui-sortable meta-box-sortable">
					<div class="postbox" style="padding: 25px;">

						<h4><?php esc_html_e( 'Izvozi posamezne kategorije', 'wooninja-jeftinije' ); ?></h4>

						<table class="table">
							<?php foreach ( $all_categories as $category ) : ?>
								<tr>
									<td>
										<input class="category-export" type="checkbox" value="<?php echo esc_attr( $category->term_id ); ?>">
										<strong><?php echo esc_html( $category->name ); ?></strong> (<?php echo esc_html( $category->count ); ?>)
									</td>
								</tr>
							<?php endforeach; ?>
						</table>
						<br>
						<a href="#" class="button button-primary js-export-categories">
							<?php esc_html_e( 'Izvozi', 'wooninja-jeftinije' ); ?>
						</a>

						<hr>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_jeftinjesi', false ) ); ?>">
							<?php wp_nonce_field( 'jeftinije_save_settings', 'jeftinije_settings_nonce' ); ?>
							<input type="hidden" name="command" value="shrani-podatke">
							<table class="form-table">
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Opis produkta za Jeftinje.hr:', 'wooninja-jeftinije' ); ?></th>
									<td>
										<select style="width: 100%;" name="jeftinje_default_opis">
											<option value="woocommerce" <?php selected( 'woocommerce', get_option( 'jeftinje_default_opis' ) ); ?>>Woocommerce opis produkta</option>
											<option value="short" <?php selected( 'short', get_option( 'jeftinje_default_opis' ) ); ?>>Woocommerce kratek opis produkta</option>
											<option value="custom" <?php selected( 'custom', get_option( 'jeftinje_default_opis' ) ); ?>>Posebno polje za opis izdelka za Jeftinje.hr</option>
										</select>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Izvozi attribute produktov:', 'wooninja-jeftinije' ); ?></th>
									<td>
										<select style="width: 100%;" name="jeftinje_attributi">
											<option value="da" <?php selected( 'da', get_option( 'jeftinje_attributi' ) ); ?>>DA</option>
											<option value="ne" <?php selected( 'ne', get_option( 'jeftinje_attributi' ) ); ?>>NE</option>
										</select>
										<br>
										<small>
											<?php esc_html_e( 'Jeftinje.hr v nekaterih primerih ne podpira možnosti objave oblačil/obutve po velikostih.', 'wooninja-jeftinije' ); ?>
										</small>
									</td>
								</tr>
								<tr valign="top">
									<th scope="row"><?php esc_html_e( 'Obvezne dodatne slike produktov:', 'wooninja-jeftinije' ); ?></th>
									<td>
										<select style="width: 100%;" name="ceneje_moreimages">
											<option value="da" <?php selected( 'da', get_option( 'jeftinje_moreimages' ) ); ?>>DA</option>
											<option value="ne" <?php selected( 'ne', get_option( 'jeftinje_moreimages' ) ); ?>>NE</option>
										</select>
									</td>
								</tr>
							</table>

							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani spremembe', 'wooninja-jeftinije' ); ?></button>
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
jQuery( document ).ready( function( $ ) {
	$( '.js-export-categories' ).click( function( e ) {
		e.preventDefault();
		var categories = '';

		$( '.category-export:checked' ).each( function() {
			categories += $( this ).attr( 'value' ) + '$';
		});

		<?php $nonce = wp_create_nonce( 'verify-Jeftinje' ); ?>
		var link = <?php echo wp_json_encode( esc_url( plugin_dir_url( __FILE__ ) . '../export.php?export=true&wolf_attack=' . $nonce ) ); ?> + '&category=' + encodeURIComponent( categories );
		window.location = link;
	});
});
</script>
