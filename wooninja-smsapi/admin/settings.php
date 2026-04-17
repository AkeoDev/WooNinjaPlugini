<?php
/**
 * WooNinja SMS API - admin nastavitve.
 *
 * @package WooNinja_SMSAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
global $plugin_page;

function field( $name ) {
	return str_replace( ' ', '-', strtolower( $name ) );
}

$fields = array(
	array(
		'field_title' => 'API uporabniško ime',
		'field_help'  => '',
	),
	array(
		'field_title' => 'API Geslo',
		'field_help'  => '',
	),
	array(
		'field_title' => 'Telefon pošiljatelja',
		'field_help'  => 'Brez +00386',
	),
	array(
		'field_title' => 'ID pošiljatelja',
		'field_help'  => '',
	),
);

if ( isset( $_REQUEST['command'] ) && 'shrani-nastavitve' === sanitize_text_field( $_REQUEST['command'] ) ) {
	if ( isset( $_POST['smsapi_settings_nonce'] ) && wp_verify_nonce( $_POST['smsapi_settings_nonce'], 'smsapi_save_settings' ) ) {
		if ( isset( $_POST['Submit'] ) && 'Shrani' === $_POST['Submit'] ) {
			foreach ( $fields as $value ) {
				$field_key = field( $value['field_title'] );
				if ( isset( $_POST[ $field_key ] ) ) {
					update_option( 'smsapi_' . $field_key, sanitize_text_field( $_POST[ $field_key ] ) );
				}
			}
			if ( isset( $_POST['smsapi_default'] ) ) {
				update_option( 'smsapi_default', sanitize_text_field( $_POST['smsapi_default'] ) );
			}
		}
	}
}

$smsapi = new SMSApi();
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Nastavitve SMSApi', 'wooninja-smsapi' ); ?></h2>
	<hr>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div class="postbox-container smdpd" style="width: 40%;">
				<div id="normal-sortables" class="ui-sortable meta-box-sortable">
					<div class="postbox" style="padding: 25px;">

						<form method="post" action="<?php echo esc_url( menu_page_url( 'smsapi', false ) ); ?>">
							<?php wp_nonce_field( 'smsapi_save_settings', 'smsapi_settings_nonce' ); ?>
							<input type="hidden" name="command" value="shrani-nastavitve">
							<table class="form-table">
								<tr>
									<td colspan="2">
										<br>
										<h1><?php esc_html_e( 'Nastavitve pošiljatelja', 'wooninja-smsapi' ); ?></h1>
									</td>
								</tr>

								<tr>
									<th scope="row"><?php esc_html_e( 'Stanje kreditov', 'wooninja-smsapi' ); ?></th>
									<td>
										<span style="display:inline-block; padding: 8px; border-radius: 5px; background: #2cddd7; color: #fff;">
											<?php echo esc_html( round( $smsapi->getCredits(), 2 ) ); ?>
										</span>
									</td>
								</tr>

								<?php foreach ( $fields as $value ) : ?>
									<tr>
										<th scope="row"><?php echo esc_html( $value['field_title'] ); ?></th>
										<td>
											<input type="text" style="width: 100%;" name="<?php echo esc_attr( field( $value['field_title'] ) ); ?>" value="<?php echo esc_attr( get_option( 'smsapi_' . field( $value['field_title'] ) ) ); ?>" />
											<?php if ( ! empty( $value['field_help'] ) ) : ?>
												<small><?php echo esc_html( $value['field_help'] ); ?></small>
											<?php endif; ?>
										</td>
									</tr>
								<?php endforeach; ?>

								<tr>
									<th scope="row"><?php esc_html_e( 'Privzeto pošiljaj kot', 'wooninja-smsapi' ); ?></th>
									<td>
										<?php $default = get_option( 'smsapi_default', 'phone' ); ?>
										<select name="smsapi_default">
											<option value="phone" <?php selected( $default, 'phone' ); ?>><?php esc_html_e( 'Telefon pošiljatelja', 'wooninja-smsapi' ); ?></option>
											<option value="id" <?php selected( $default, 'id' ); ?>><?php esc_html_e( 'ID pošiljatelja', 'wooninja-smsapi' ); ?></option>
										</select>
									</td>
								</tr>

								<tr>
									<td colspan="2">
										<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani', 'wooninja-smsapi' ); ?></button>
									</td>
								</tr>
							</table>
						</form>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
