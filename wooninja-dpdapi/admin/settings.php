<?php

global $woocommerce;
global $plugin_page;

$command = null;
if ( isset( $_REQUEST['command'] ) ) {
	$command = sanitize_text_field( $_REQUEST['command'] );
}

if ( $command == 'shrani-podatke' ) {
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( $_POST['Submit'] ) : '';
	if ( 'Shrani' == $tip ) {
		update_option( 'apiUserName', sanitize_text_field( $_POST['apiUserName'] ) );
		update_option( 'apiPassword', sanitize_text_field( $_POST['apiPassword'] ) );
		update_option( 'dpd_api_token', sanitize_text_field( $_POST['dpd_api_token'] ) );
	}
}

?>

<div class="wrap">
	<h2><?php esc_html_e( 'Nastavitve WooCommerce -> DPD WebLabel', 'wooninja-dpdapi' ); ?></h2>

	<hr>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">

			<div class="postbox-container smdpd">
				<div id="normal-sortables" class="ui-sortable meta-box-sortable">
					<!-- BOXES -->
					<div class="postbox" style="padding: 25px;">

						<h4><?php esc_html_e( 'Dostop do vašega WebLabel računa:', 'wooninja-dpdapi' ); ?></h4>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm-dpd', false ) ); ?>">
							<table class="form-table">
								<input type="hidden" name="command" value="shrani-podatke">

								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Uporabniško ime', 'wooninja-dpdapi' ); ?></th>
								<td><input type="text" style="width: 100%;" name="apiUserName" value="<?php echo esc_attr( get_option( 'apiUserName' ) ); ?>" /></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'Geslo', 'wooninja-dpdapi' ); ?></th>
								<td><input type="password" style="width: 100%;" name="apiPassword" value="<?php echo esc_attr( get_option( 'apiPassword' ) ); ?>" /></td>
								</tr>
								<tr valign="top">
								<th scope="row"><?php esc_html_e( 'API Token', 'wooninja-dpdapi' ); ?></th>
								<td><input type="text" style="width: 100%;" name="dpd_api_token" value="<?php echo esc_attr( get_option( 'dpd_api_token' ) ); ?>" /></td>
								</tr>

							</table>

							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani spremembe', 'wooninja-dpdapi' ); ?></button>

						</form>

					</div>
				</div>
			</div>

		</div>
	</div>

</div>
