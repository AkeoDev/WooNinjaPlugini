<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $woocommerce;
global $plugin_page;

$command = null;
if ( isset( $_REQUEST['command'] ) ) {
	$command = sanitize_text_field( wp_unslash( $_REQUEST['command'] ) );
}

if ( $command == 'shrani-vrste-posiljk' ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'espremnica_settings_' . $command ) ) {
		wp_die( esc_html__( 'Varnostna preveritev ni uspela.', 'wooninja-espremnica' ) );
	}
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
	if ( 'Shrani' == $tip ) {
		$enabled_vrste_posiljk = isset( $_POST['espremnica_csv_dodatne_storitve'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['espremnica_csv_dodatne_storitve'] ) ) : array();
		update_option( 'espremnica_csv_vrste_posiljk', $enabled_vrste_posiljk, true );

		$privzete_vrste_posiljk        = get_option( 'espremnica_csv_default_vrste_posiljk' );
		$privzete_storitve_values      = array_values( $privzete_vrste_posiljk );
		$enabled_vrste_posiljk_values  = array_values( $enabled_vrste_posiljk );
		$privzete_vrste_posiljk_new    = $privzete_vrste_posiljk;

		foreach ( $privzete_vrste_posiljk_new as $index => $value ) {
			if ( ! in_array( $value, $enabled_vrste_posiljk_values, true ) ) {
				unset( $privzete_vrste_posiljk_new[ $index ] );
			}
		}

		update_option( 'espremnica_csv_default_vrste_posiljk', $privzete_vrste_posiljk_new, true );
	}
}

if ( $command == 'shrani-privzete-vrste-posiljk' ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'espremnica_settings_' . $command ) ) {
		wp_die( esc_html__( 'Varnostna preveritev ni uspela.', 'wooninja-espremnica' ) );
	}
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
	if ( 'Shrani' == $tip ) {
		$storitve = isset( $_POST['espremnica_csv_dodatne_storitve'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['espremnica_csv_dodatne_storitve'] ) ) : array();
		update_option( 'espremnica_csv_default_vrste_posiljk', $storitve, true );
	}
}

if ( $command == 'shrani-dodatne-storitve' ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'espremnica_settings_' . $command ) ) {
		wp_die( esc_html__( 'Varnostna preveritev ni uspela.', 'wooninja-espremnica' ) );
	}
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
	if ( 'Shrani' == $tip ) {
		$enabled_dodatne_storitve = isset( $_POST['espremnica_csv_dodatne_storitve'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['espremnica_csv_dodatne_storitve'] ) ) : array();
		update_option( 'espremnica_csv_dodatne_storitve', $enabled_dodatne_storitve, true );

		$privzete_storitve              = get_option( 'espremnica_csv_default_dodatne_storitve' );
		$privzete_storitve_values       = array_values( $privzete_storitve );
		$enabled_dodatne_storitve_values = array_values( $enabled_dodatne_storitve );
		$privzete_storitve_new          = $privzete_storitve;

		foreach ( $privzete_storitve as $index => $value ) {
			if ( ! in_array( $value, $enabled_dodatne_storitve_values, true ) ) {
				unset( $privzete_storitve_new[ $index ] );
			}
		}

		update_option( 'espremnica_csv_default_dodatne_storitve', $privzete_storitve_new, true );
	}
}

if ( $command == 'shrani-privzete-dodatne-storitve' ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'espremnica_settings_' . $command ) ) {
		wp_die( esc_html__( 'Varnostna preveritev ni uspela.', 'wooninja-espremnica' ) );
	}
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
	if ( 'Shrani' == $tip ) {
		$storitve = isset( $_POST['espremnica_csv_dodatne_storitve'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['espremnica_csv_dodatne_storitve'] ) ) : array();
		update_option( 'espremnica_csv_default_dodatne_storitve', $storitve, true );
	}
}

if ( $command == 'shrani-parametre' ) {
	if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'espremnica_settings_' . $command ) ) {
		wp_die( esc_html__( 'Varnostna preveritev ni uspela.', 'wooninja-espremnica' ) );
	}
	$tip = isset( $_POST['Submit'] ) ? sanitize_text_field( wp_unslash( $_POST['Submit'] ) ) : '';
	if ( 'Shrani' == $tip ) {
		update_option( 'oznakaZaVrstoPosiljke', sanitize_text_field( wp_unslash( $_POST['oznakaZaVrstoPosiljke'] ) ) );
		update_option( 'sprejemnaStevilkaMin', sanitize_text_field( wp_unslash( $_POST['sprejemnaStevilkaMin'] ) ) );
		update_option( 'sprejemnaStevilkaMax', sanitize_text_field( wp_unslash( $_POST['sprejemnaStevilkaMax'] ) ) );
		update_option( 'sprejemnaStevilkaStart', sanitize_text_field( wp_unslash( $_POST['sprejemnaStevilkaStart'] ) ) );
		update_option( 'firmaIme', sanitize_text_field( wp_unslash( $_POST['firmaIme'] ) ) );
		update_option( 'firmaNaslov', sanitize_text_field( wp_unslash( $_POST['firmaNaslov'] ) ) );
		update_option( 'firmaPosta', sanitize_text_field( wp_unslash( $_POST['firmaPosta'] ) ) );
		update_option( 'firmaKontaktTelefon', sanitize_text_field( wp_unslash( $_POST['firmaKontaktTelefon'] ) ) );
		update_option( 'KomitentId', sanitize_text_field( wp_unslash( $_POST['KomitentId'] ) ) );
		update_option( 'PogodbaId', sanitize_text_field( wp_unslash( $_POST['PogodbaId'] ) ) );
		update_option( 'PodruznicaId', sanitize_text_field( wp_unslash( $_POST['PodruznicaId'] ) ) );
		update_option( 'PostaID', sanitize_text_field( wp_unslash( $_POST['PostaID'] ) ) );
		update_option( 'PostaIme', sanitize_text_field( wp_unslash( $_POST['PostaIme'] ) ) );
		! empty( $_POST['TestMode'] ) ? update_option( 'TestMode', absint( $_POST['TestMode'] ) ) : update_option( 'TestMode', false );
	}
}

?>

<style>

	/**
	 * Help Tip
	 */
	.woocommerce-help-tip {
		color: #666;
		display: inline-block;
		font-size: 10px;
		font-style: normal;
		line-height: 12px;
		position: relative;
		vertical-align: middle;
		width: 12px;
		height: 12px;
		background: #666;
		color: #FFF;
		border-radius: 50%;
		text-align: center;
	}

	.woocommerce-help-tip::after {
		cursor: help;
		content: "?";
	}

	/**
	 * Tooltips
	 */
	.tips {
		cursor: help;
		text-decoration: none;
	}

	img.tips {
		padding: 5px 0 0;
	}

	#tiptip_holder {
		display: none;
		z-index: 8675309;
		position: absolute;
		top: 0;
		/*rtl:ignore*/
		left: 0;
	}

	#tiptip_holder.tip_top {
		padding-bottom: 5px;
	}

	#tiptip_holder.tip_top #tiptip_arrow_inner {
		margin-top: -7px;
		margin-left: -6px;
		border-top-color: #333;
	}

	#tiptip_holder.tip_bottom {
		padding-top: 5px;
	}

	#tiptip_holder.tip_bottom #tiptip_arrow_inner {
		margin-top: -5px;
		margin-left: -6px;
		border-bottom-color: #333;
	}

	#tiptip_holder.tip_right {
		padding-left: 5px;
	}

	#tiptip_holder.tip_right #tiptip_arrow_inner {
		margin-top: -6px;
		margin-left: -5px;
		border-right-color: #333;
	}

	#tiptip_holder.tip_left {
		padding-right: 5px;
	}

	#tiptip_holder.tip_left #tiptip_arrow_inner {
		margin-top: -6px;
		margin-left: -7px;
		border-left-color: #333;
	}

	#tiptip_content,
	.chart-tooltip,
	.wc_error_tip {
		color: #fff;
		font-size: 0.8em;
		max-width: 150px;
		background: #333;
		text-align: center;
		border-radius: 3px;
		padding: 0.618em 1em;
		box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
	}

	#tiptip_content code,
	.chart-tooltip code,
	.wc_error_tip code {
		padding: 1px;
		background: #888;
	}

	#tiptip_arrow,
	#tiptip_arrow_inner {
		position: absolute;
		border-color: transparent;
		border-style: solid;
		border-width: 6px;
		height: 0;
		width: 0;
	}

</style>

<script>
	jQuery(document).ready(function ($) {
		var tiptip_args = {
			'attribute': 'data-tip',
			'fadeIn': 50,
			'fadeOut': 50,
			'delay': 200
		};
		$('.woocommerce-help-tip').tipTip(tiptip_args);
	});
</script>

<div class="wrap">
	<h2><?php esc_html_e( 'Nastavitve Woocommerce -> eSpremnica CSV', 'wooninja-espremnica' ); ?></h2>

	<hr>

	<style>
		.form-table td {
			padding: 0;
		}

		th {
			vertical-align: top;
			padding: 0;
			margin-bottom: 5px;
		}
	</style>

	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div class="postbox-container smdpd" style="width: 40%;">
				<div id="normal-sortables" class="ui-sortable meta-box-sortable">
					<!-- BOXES -->
					<div class="postbox" style="padding: 25px;">

						<!-- NASTAVITVE POSTE -->

						<h1><?php esc_html_e( 'Nastavitve parametrov', 'wooninja-espremnica' ); ?></h1>

						<?php
						$oznaka_vrste_posiljke   = get_option( 'oznakaZaVrstoPosiljke' );
						$min_sprejemna_stevilka  = get_option( 'sprejemnaStevilkaMin' );
						$max_sprejemna_stevilka  = get_option( 'sprejemnaStevilkaMax' );
						$start_sprejemna_stevilka = get_option( 'sprejemnaStevilkaStart' );
						$imeFirme                = get_option( 'firmaIme' );
						$naslovFirme             = get_option( 'firmaNaslov' );
						$postaFirme              = get_option( 'firmaPosta' );
						$telKontaktFirme         = get_option( 'firmaKontaktTelefon' );
						$komitentId              = get_option( 'KomitentId' );
						$pogodbaId               = get_option( 'PogodbaId' );
						$podruznicaId            = get_option( 'PodruznicaId' );
						$postaId                 = get_option( 'PostaID' );
						$postaIme                = get_option( 'PostaIme' );
						?>
						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_spremnica_csv', false ) ); ?>">
							<?php wp_nonce_field( 'espremnica_settings_shrani-parametre' ); ?>
							<input type="hidden" style="width:100%;" name="command" value="shrani-parametre">
							<input type="text" style="width:100%;" name="firmaIme" value="<?php echo esc_attr( $imeFirme ); ?>" />
							<label><?php esc_html_e( 'Ime podjetja, npr. NAVIDEZNO PODJETJE D.O.O.', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="firmaNaslov" value="<?php echo esc_attr( $naslovFirme ); ?>" />
							<label><?php esc_html_e( 'Naslov podjetja, npr KRANJSKA ULICA 1', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="firmaPosta" value="<?php echo esc_attr( $postaFirme ); ?>" />
							<label><?php esc_html_e( 'Poštna številka in kraj, npr. 4000 KRANJ', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="firmaKontaktTelefon" value="<?php echo esc_attr( $telKontaktFirme ); ?>" />
							<label><?php esc_html_e( 'Telefonski kontakt odgovorne osebe v podjetju za pošiljke, npr. 040123456', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="oznakaZaVrstoPosiljke" value="<?php echo esc_attr( $oznaka_vrste_posiljke ); ?>" />
							<label><?php esc_html_e( 'CF, CA oziroma nekaj, kar določi Pošta Slovenije', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="sprejemnaStevilkaMin" value="<?php echo esc_attr( $min_sprejemna_stevilka ); ?>" />
							<label><?php esc_html_e( 'MIN sprejemna številka', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="sprejemnaStevilkaMax" value="<?php echo esc_attr( $max_sprejemna_stevilka ); ?>" />
							<label><?php esc_html_e( 'MAX sprejemna številka', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="sprejemnaStevilkaStart" value="<?php echo esc_attr( $start_sprejemna_stevilka ); ?>" />
							<label><?php esc_html_e( 'START sprejemna številka', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="KomitentId" value="<?php echo esc_attr( $komitentId ); ?>" />
							<label>KomitentId</label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="PogodbaId" value="<?php echo esc_attr( $pogodbaId ); ?>" />
							<label>PogodbaId</label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="PodruznicaId" value="<?php echo esc_attr( $podruznicaId ); ?>" />
							<label>PodruznicaId</label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="PostaID" value="<?php echo esc_attr( $postaId ); ?>" />
							<label>PostaID</label>
							<br>
							<br>
							<input type="text" style="width:100%;" name="PostaIme" value="<?php echo esc_attr( $postaIme ); ?>" />
							<label>PostaIME</label>
							<br>
							<br>
							<input type="checkbox" name="TestMode" value="1" <?php checked( get_option( 'TestMode' ) ); ?> />
							<label><?php esc_html_e( 'Vklopi testni način', 'wooninja-espremnica' ); ?></label>
							<br>
							<br>
							<button type="submit" style="display:block;" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani podatke', 'wooninja-espremnica' ); ?></button>
						</form>
						<!-- VRSTA POSILJKE -->

						<h1><?php esc_html_e( 'Omogočene vrste pošiljk', 'wooninja-espremnica' ); ?></h1>

						<?php
						$omogocene_vrste_posiljk = get_option( 'espremnica_csv_vrste_posiljk' );
						if ( ! is_array( $omogocene_vrste_posiljk ) ) {
							$omogocene_vrste_posiljk = array();
						}

						$vrste_posiljk = get_espremnica_csv_vrste_posiljk();
						?>

						<style>
							.disabled {
								opacity: 0.6;
							}
						</style>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_espremnica_csv', false ) ); ?>">
							<?php wp_nonce_field( 'espremnica_settings_shrani-vrste-posiljk' ); ?>
							<input type="hidden" name="command" value="shrani-vrste-posiljk">
							<?php
							foreach ( $vrste_posiljk as $vrsta ) {
								$click = '';
								if ( ! $vrsta['enable'] ) {
									$click = 'onclick="alert(\'' . esc_js( 'Storitev je trenutno v izdelavi.' ) . '\');return false;"';
									?>
									<div class="disabled">
									<?php
								}
								?>

								<input <?php echo $click; ?> type="checkbox" name="espremnica_csv_dodatne_storitve[]"
																 value="<?php echo esc_attr( $vrsta['id'] ); ?>" <?php if ( in_array( $vrsta['id'], $omogocene_vrste_posiljk, true ) ) { echo 'checked'; } ?>> <?php echo esc_html( $vrsta['title'] ); ?><?php if ( $vrsta['description'] != '' ) { echo wc_help_tip( $vrsta['description'] ); } ?>
								<br>

								<?php
								$click = '';
								if ( ! $vrsta['enable'] ) {
									?>
									</div>
									<?php
								}
							}
							?>

							<br>
							<br>
							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani', 'wooninja-espremnica' ); ?></button>

						</form>

						<h1><?php esc_html_e( 'Privzeta vrsta pošiljke', 'wooninja-espremnica' ); ?></h1>

						<?php
						$privzete_vrste_posiljk = get_option( 'espremnica_csv_default_vrste_posiljk' );

						if ( ! is_array( $privzete_vrste_posiljk ) ) {
							$privzete_vrste_posiljk = array();
						}
						?>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_espremnica_csv', false ) ); ?>">
							<?php wp_nonce_field( 'espremnica_settings_shrani-privzete-vrste-posiljk' ); ?>
							<input type="hidden" name="command" value="shrani-privzete-vrste-posiljk">

							<select name="espremnica_csv_dodatne_storitve[]">
								<option value="-1"><?php esc_html_e( '-- Izberi privzeti način vrste pošiljke --', 'wooninja-espremnica' ); ?></option>
							<?php

							foreach ( $vrste_posiljk as $vrsta ) {
								if ( in_array( $vrsta['id'], $omogocene_vrste_posiljk, true ) ) {
									$click = '';
									if ( ! $vrsta['enable'] ) {
										$click = 'onclick="alert(\'' . esc_js( 'Storitev je trenutno v izdelavi.' ) . '\');return false;"';
										?>
										<div class="disabled">
										<?php
									}
									?>

									<option <?php echo $click; ?> value="<?php echo esc_attr( $vrsta['id'] ); ?>" <?php if ( in_array( $vrsta['id'], $privzete_vrste_posiljk, true ) ) { echo "selected='selected'"; } ?>> <?php echo esc_html( $vrsta['title'] ); ?></option>
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
								</select>

							<br>
							<br>
							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani', 'wooninja-espremnica' ); ?></button>
						</form>

						<!-- DODATNE STORITVE -->

						<h1><?php esc_html_e( 'Omogočene dodatne storitve pošiljk', 'wooninja-espremnica' ); ?></h1>

						<?php
						$omogocene_storitve = get_option( 'espremnica_csv_dodatne_storitve' );
						if ( ! is_array( $omogocene_storitve ) ) {
							$omogocene_storitve = array();
						}

						$dodatne_storitve = get_espremnica_csv_dodatne_storitve();
						?>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_espremnica_csv', false ) ); ?>">
							<?php wp_nonce_field( 'espremnica_settings_shrani-dodatne-storitve' ); ?>
							<input type="hidden" name="command" value="shrani-dodatne-storitve">
							<?php
							foreach ( $dodatne_storitve as $str ) {
								$click = '';
								if ( ! $str['enable'] ) {
									$click = 'onclick="alert(\'' . esc_js( 'Storitev je trenutno v izdelavi.' ) . '\');return false;"';
									?>
									<div class="disabled">
									<?php
								}
								?>

								<input <?php echo $click; ?> type="checkbox" name="espremnica_csv_dodatne_storitve[]"
																 value="<?php echo esc_attr( $str['id'] ); ?>" <?php if ( in_array( $str['id'], $omogocene_storitve, true ) ) { echo 'checked'; } ?>> <?php echo esc_html( $str['title'] ); ?><?php if ( $str['description'] != '' ) { echo wc_help_tip( $str['description'] ); } ?>
								<br>

								<?php
								$click = '';
								if ( ! $str['enable'] ) {
									?>
									</div>
									<?php
								}
							}
							?>

							<br>
							<br>
							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani', 'wooninja-espremnica' ); ?></button>

						</form>

						<h1><?php esc_html_e( 'Privzete dodatne storitve pošiljk', 'wooninja-espremnica' ); ?></h1>

						<?php
						$privzete_storitve = get_option( 'espremnica_csv_default_dodatne_storitve' );
						if ( ! is_array( $privzete_storitve ) ) {
							$privzete_storitve = array();
						}
						?>

						<form method="post" action="<?php echo esc_url( menu_page_url( 'sm_espremnica_csv', false ) ); ?>">
							<?php wp_nonce_field( 'espremnica_settings_shrani-privzete-dodatne-storitve' ); ?>
							<input type="hidden" name="command" value="shrani-privzete-dodatne-storitve">

							<?php
							foreach ( $dodatne_storitve as $str ) {
								if ( in_array( $str['id'], $omogocene_storitve, true ) ) {
									$click = '';
									if ( ! $str['enable'] ) {
										$click = 'onclick="alert(\'' . esc_js( 'Storitev je trenutno v izdelavi.' ) . '\');return false;"';
										?>
										<div class="disabled">
										<?php
									}
									?>

									<input <?php echo $click; ?> type="checkbox" name="espremnica_csv_dodatne_storitve[]"
																	 value="<?php echo esc_attr( $str['id'] ); ?>" <?php if ( in_array( $str['id'], $privzete_storitve, true ) ) { echo 'checked'; } ?>> <?php echo esc_html( $str['title'] ); ?><?php if ( $str['description'] != '' ) { echo wc_help_tip( $str['description'] ); } ?>
									<br>

									<?php
									$click = '';
									if ( ! $str['enable'] ) {
										?>
										</div>
										<?php
									}
								}
							}
							?>

							<br>
							<br>
							<button type="submit" name="Submit" class="button button-primary" value="Shrani"><?php esc_html_e( 'Shrani', 'wooninja-espremnica' ); ?></button>
						</form>

					</div>
				</div>
			</div>

		</div>
	</div>

</div>
