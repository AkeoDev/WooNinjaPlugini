<?php
/**
 * WooNinja SMS API - pošiljanje SMS sporočil.
 *
 * @package WooNinja_SMSAPI
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
global $plugin_page;

$smsapi = new SMSApi();

if ( isset( $_REQUEST['command'] ) && 'pošlji-sms' === sanitize_text_field( $_REQUEST['command'] ) ) {
	if ( isset( $_POST['smsapi_send_nonce'] ) && wp_verify_nonce( $_POST['smsapi_send_nonce'], 'smsapi_send_sms' ) ) {
		$phone    = sanitize_text_field( $_POST['phone'] );
		$message  = sanitize_textarea_field( $_POST['message'] );
		$response = $smsapi->sendSms( $phone, $message );

		if ( isset( $response[0] ) && '-1' === (string) $response[0] ) {
			?>
			<div class="error notice">
				<p><?php printf( esc_html__( 'Prišlo je do napake pri pošiljanju sporočila! (Št. napake: %s)', 'wooninja-smsapi' ), esc_html( $response[2] ) ); ?></p>
			</div>
			<?php
		} else {
			?>
			<div class="updated notice">
				<p><?php esc_html_e( 'Sporočilo je bilo poslano!', 'wooninja-smsapi' ); ?></p>
			</div>
			<?php
		}
	}
}
?>

<div class="wrap">
	<h2><?php esc_html_e( 'Pošiljanje SMS sporočila', 'wooninja-smsapi' ); ?></h2>
	<hr>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div class="postbox-container smdpd" style="width: 40%;">
				<div id="normal-sortables" class="ui-sortable meta-box-sortable">
					<div class="postbox" style="padding: 25px;">

						<form method="post" action="<?php echo esc_url( menu_page_url( 'smsapi_send', false ) ); ?>">
							<?php wp_nonce_field( 'smsapi_send_sms', 'smsapi_send_nonce' ); ?>
							<input type="hidden" name="command" value="pošlji-sms">
							<table class="form-table">
								<tr>
									<th scope="row"><?php esc_html_e( 'Stanje kreditov', 'wooninja-smsapi' ); ?></th>
									<td>
										<span style="display:inline-block; padding: 8px; border-radius: 5px; background: #2cddd7; color: #fff;">
											<?php echo esc_html( round( $smsapi->getCredits(), 2 ) ); ?>
										</span>
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Telefonska številka prejemnika', 'wooninja-smsapi' ); ?></th>
									<td>
										<input type="text" name="phone" style="width: 100%;">
									</td>
								</tr>
								<tr>
									<th scope="row"><?php esc_html_e( 'Vsebina sporočila', 'wooninja-smsapi' ); ?></th>
									<td>
										<textarea name="message" style="width: 100%; height: 200px;"></textarea>
									</td>
								</tr>
								<tr>
									<td colspan="2">
										<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Pošlji', 'wooninja-smsapi' ); ?></button>
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
