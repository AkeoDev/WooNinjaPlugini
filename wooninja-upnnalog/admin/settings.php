<?php

	

	global $plugin_page;

	$command = null;
	if ( isset( $_REQUEST["command"] ) ) {
		$command = $_REQUEST["command"];
	}

	//delete_option('payment_option');
	//delete_option('payment_option');
	$use_short_code = false;
	if ( $command == "shrani-podatke" ) {
		// Check nonce for security
		if ( ! isset( $_POST['upn_settings_nonce'] ) || ! wp_verify_nonce( $_POST['upn_settings_nonce'], 'upn_save_settings' ) ) {
			wp_die( __( 'Security check failed', 'woo-upnnalog' ) );
		}

		$tip = $_POST["Submit"];
		if ( "Shrani" == $tip ) {
			update_option("upnImePodjetja",  sanitize_text_field($_POST["upnImePodjetja"]));
			update_option("upnNaslovPodjetja",  sanitize_text_field($_POST["upnNaslovPodjetja"]));
			update_option("upnPostaKraj",  sanitize_text_field($_POST["upnPostaKraj"]));
			update_option("upnBic",  sanitize_text_field($_POST["upnBic"]));
			update_option("upnTRR",  sanitize_text_field($_POST["upnTRR"]));

			if (isset($_POST['payment_option'])) {
				update_option('payment_option', sanitize_text_field($_POST['payment_option']));
			}
			}
		}

		if ( $command == "Shrani-nastavitve-generalne" ) {
			// Check nonce for security
			if ( ! isset( $_POST['upn_general_settings_nonce'] ) || ! wp_verify_nonce( $_POST['upn_general_settings_nonce'], 'upn_save_general_settings' ) ) {
				wp_die( __( 'Security check failed', 'woo-upnnalog' ) );
			}

			$tip = $_POST["Submit-settings"];
			if ( "Shrani-nastavitve" == $tip ) {

				if (isset($_POST['upnBrisanje'])) {
					update_option("upnBrisanje", sanitize_text_field($_POST["upnBrisanje"]));
				}
				if (isset($_POST['upnBrisanjeCancel'])) {
					update_option("upnBrisanjeCancel", sanitize_text_field($_POST["upnBrisanjeCancel"]));
				}

				if (isset($_POST['changeStatus'])) {
					update_option("changeStatus", sanitize_text_field($_POST["changeStatus"]));
				}
				if (isset($_POST['sendEmailToUpn'])) {
					update_option('sendEmailToUpn', sanitize_text_field($_POST['sendEmailToUpn']));
				}
				if (isset($_POST['emailRecipient_upn'])) {
					update_option('emailRecipient_upn', sanitize_email($_POST['emailRecipient_upn']));
				}
				if (isset($_POST['email-upn-position'])) {
					update_option('email-upn-position', sanitize_text_field($_POST['email-upn-position']));
				}

			}
		}


		if ($command == 'shrani-metodo') {
			$tip = $_POST["Submit-metoda"];

			if ("Izbira-metode" == $tip) {
				
				if (isset($_POST['payment_option'])) {
					update_option('payment_option', sanitize_text_field($_POST['payment_option']));



				}
			}
		}

		if ($command == 'shrani-podatke-upay') {
			// Check nonce for security
			if ( ! isset( $_POST['upn_upay_test_nonce'] ) || ! wp_verify_nonce( $_POST['upn_upay_test_nonce'], 'upn_save_upay_test' ) ) {
				wp_die( __( 'Security check failed', 'woo-upnnalog' ) );
			}

			$tip = $_POST["Submit-upay"];

			if ("Shrani-upay" == $tip) {

					update_option("upay_client_id",  sanitize_text_field($_POST["upay_client_id"]));
					update_option("upay_client_secret",  sanitize_text_field($_POST["upay_client_secret"]));
					update_option("upay_sellpoint_id",  sanitize_text_field($_POST["upay_sellpoint_id"]));

			}
		}

		if ($command == 'shrani-podatke-upay-live') {
			// Check nonce for security
			if ( ! isset( $_POST['upn_upay_live_nonce'] ) || ! wp_verify_nonce( $_POST['upn_upay_live_nonce'], 'upn_save_upay_live' ) ) {
				wp_die( __( 'Security check failed', 'woo-upnnalog' ) );
			}

			$tip = $_POST["Submit-upay-live"];

			if ("Shrani-upay-live" == $tip) {

					update_option("upay_client_id_live",  sanitize_text_field($_POST["upay_client_id_live"]));
					update_option("upay_client_secret_live",  sanitize_text_field($_POST["upay_client_secret_live"]));
					update_option("upay_sellpoint_id_live",  sanitize_text_field($_POST["upay_sellpoint_id_live"]));

			}
		}

		if (get_option('payment_option') == 'upay') {
			$upn_class = 'upay_active';
		}
		if (get_option('payment_option') == 'default_upay') {
			$upn_class = 'upn_active';
		}
		
		if (!get_option('payment_option') == 'upay') {
			update_option('payment_option', 'default_upn');
		} 
		
		if((isset($_POST['shortcode'])) && !empty($_POST['shortcode']))
		{
    	$use_short_code= $_POST['shortcode']; //note i used $_POST since you have a post form **method='post'**
		}

?>

<div class="wrap">
	<h2>Nastavitve prikaza UPN naloga</h2>
	<br><hr>
	<h3>Modul omogoča klasični UPN obrazec ali uPay UPN obrazec.</h3>
	<br>
	<div id="dashboard-widgets-wrap">
	    <div id="dashboard-widgets" class="metabox-holder">
			<form id="paymentForm" method="" action="" style="padding-bottom: 20px;">
				<input type="hidden" name="command" value="shrani-metodo">
				<div style="display: flex; gap:70px;">
					<div>
						<label class="switch postbox"style="padding: 25px; width: 285px;">
					
								<div style="display: flex; justify-content: space-between; align-items: center;"> 
								<img src="<?php echo plugin_dir_url(__FILE__) . '/WOO-NINJA.png';?>" alt="wooninja" style="width: 75px; height: 50px;">
									<h2 style="text-align: center;">UPN podatki</h2><br />
									<input type="radio" id="default_upn" name="payment_option" value="default_upn" <?php checked('default_upn', get_option('payment_option') ? get_option('payment_option') : 'default_upn'); ?>>
									<span class="slider round"></span>
							 	</div> 
	
						</label>
					</div>
					<div>
						<label class="switch postbox" style="padding: 25px; width: 285px;">

							<div style="display: flex; justify-content: space-between; align-items: center;"> 
								<img src="<?php echo plugin_dir_url(__FILE__) . '/logo-upay.svg';?>" alt="upay-logo" style="width: 75px; height: 50px;">
									<h2 style="text-align: center;">uPay podatki</h2><br />
									<input type="radio" id="upay" name="payment_option" value="upay" <?php checked('upay', get_option('payment_option')); ?>>
									<span class="slider round"></span>
								</div>

						</label>
					</div>
				</div>
			<!-- 	<button type="submit" name="Submit-metoda" class="button button-secondaty" value="Izbira-metode">Potrdi izbiro </button> -->
			
			</form>	
	        <form method="post" action="<?php echo esc_url( menu_page_url( 'upn-module', false ) ); ?>">
	        <!-- BOXES -->
	            <div class="postbox" style="padding: 25px; width: 700px;">

					<?php
				//	if (get_option('payment_option') == 'default_upn') {?>
					<table id="form-table_upn" class="form-table default_upn">
					<h4 class="title-upn" style="margin-left: 0px;">Podatki o vašem podjetju:</h4>

						<input type="hidden" name="command" value="shrani-podatke">
						<?php wp_nonce_field( 'upn_save_settings', 'upn_settings_nonce' ); ?>	
							<tr valign="top">
							<th scope="row">Ime podjetja</th>
							<td><input type="text"  style="width: 100%;" name="upnImePodjetja" value="<?php echo esc_attr( get_option('upnImePodjetja') ); ?>" /></td>
							</tr>
							
															
							<tr valign="top">
							<th scope="row">Naslov podjetja</th>
							<td><input type="text"  style="width: 100%;" name="upnNaslovPodjetja" value="<?php echo esc_attr( get_option('upnNaslovPodjetja') ); ?>" /></td>
							</tr>

							<tr valign="top">
							<th scope="row">Pošta Kraj</th>
							<td><input type="text"  style="width: 100%;" name="upnPostaKraj" value="<?php echo esc_attr( get_option('upnPostaKraj') ); ?>" /></td>
							</tr>
							
															
							<tr valign="top">
							<th scope="row">BIC Banke</th>
							<td><input type="text"  style="width: 100%;" name="upnBic" value="<?php echo esc_attr( get_option('upnBic') ); ?>" /></td>
							</tr>
							
															
							<tr valign="top">
							<th scope="row">TRR</th>
							<td><input type="text"  style="width: 100%;" name="upnTRR" value="<?php echo esc_attr( get_option('upnTRR') ); ?>" /></td>
							</tr>
					
					</table>
					<button type="submit" name="Submit" class="button button-primary upn-button" value="Shrani">Shrani podatke</button>
					<div id='loader' style='display: none;'>
                        <div class="loadingspinner"></div>
                	</div>
				<!-- 	</p> -->
	            
	        </form>
				<?php //} 
				//if (get_option('payment_option') == 'upay') { ?>
				<div id="wrap-upay-data">
				<div id="dashboard-widgets-wrap">
	    			<div id="dashboard-widgets" class="metabox-holder">
					<form id="uPayFormType" method="" action="" style="padding-bottom: 20px; position: relative;">
						<input type="hidden" name="command" value="shrani-api-podatki">
						<h2>Vnesite testne ali live podatke za povezovanje z uPay</h2>
						<h4 class="title-upay" style="margin-left: 0px;">Podatke za povezovanje prejmete s strani uPay.</h4>
							<div class="radio-buttons">
    							<div class="radio-option">
        							<input type="radio" id="uPayLive" name="uPayType" value="uPayLive" class="radio-input"  <?php checked('uPayLive', get_option('uPayType')); ?>>
        							<label for="uPayLive" class="radio-label">Live podatki</label>
    							</div>
    							<div class="radio-option">
        							<input type="radio" id="uPayTest" name="uPayType" value="uPayTest" class="radio-input" <?php checked('uPayTest', get_option('uPayType') ? get_option('uPayType') : 'uPayTest'); ?>>
        							<label for="uPayTest" class="radio-label">Testni podatki</label>
    							</div>
							</div>
						</form>
					</div>
				</div>
				<!-- UPAY TEST CONNECTION -->
				<form id="uPayTestConnection" method="post" action="<?php echo esc_url( menu_page_url( 'upn-module', false ) ); ?>">
					<table id="form-table-upay" class="form-table upay">

						<input type="hidden" name="command" value="shrani-podatke-upay">
						<?php wp_nonce_field( 'upn_save_upay_test', 'upn_upay_test_nonce' ); ?>
						<tr valign="top">
						<th scope="row">Client ID (test)</th>
						<td><input type="text"  style="width: 100%;" name="upay_client_id" value="<?php echo esc_attr( get_option('upay_client_id') ); ?>" /></td>
						</tr>									
						<tr valign="top">
						<th scope="row">Client secret (test)</th>
						<td><input type="text"  style="width: 100%;" name="upay_client_secret" value="<?php echo esc_attr( get_option('upay_client_secret') ); ?>" /></td>
						</tr>
						<tr valign="top">
						<th scope="row">SellPoint ID (test)</th>
						<td><input type="text"  style="width: 100%;" name="upay_sellpoint_id" value="<?php echo esc_attr( get_option('upay_sellpoint_id') ); ?>" /></td>
						</tr>
					</table>
					<button type="submit" name="Submit-upay" class="button button-primary upay-button" value="Shrani-upay">Shrani podatke</button>
					<div id='loader' style='display: none;'>
                        <div class="loadingspinner"></div>
                	</div>
				</form>
				<!-- UPAY PROD CONNECTION -->
				<form id="uPayLiveConnection" method="post" action="<?php echo esc_url( menu_page_url( 'upn-module', false ) ); ?>">
					<table id="form-table-upay" class="form-table upay">
					<h4 class="title-upay" style="margin-left: 0px;">uPay dostopni podatki</h4>

						<input type="hidden" name="command" value="shrani-podatke-upay-live">
						<?php wp_nonce_field( 'upn_save_upay_live', 'upn_upay_live_nonce' ); ?>
						<tr valign="top">
						<th scope="row">Client ID</th>
						<td><input type="text"  style="width: 100%;" name="upay_client_id_live" value="<?php echo esc_attr( get_option('upay_client_id_live') ); ?>" /></td>
						</tr>									
						<tr valign="top">
						<th scope="row">Client secret</th>
						<td><input type="text"  style="width: 100%;" name="upay_client_secret_live" value="<?php echo esc_attr( get_option('upay_client_secret_live') ); ?>" /></td>
						</tr>
						<tr valign="top">
						<th scope="row">SellPoint ID</th>
						<td><input type="text"  style="width: 100%;" name="upay_sellpoint_id_live" value="<?php echo esc_attr( get_option('upay_sellpoint_id_live') ); ?>" /></td>
						</tr>
					</table>
					<button type="submit" name="Submit-upay-live" class="button button-primary upay-button" value="Shrani-upay-live">Shrani podatke</button>
					<div id='loader' style='display: none;'>
                        <div class="loadingspinner"></div>
                	</div>
				</form>
				</div>
				</div>
				<?php // } ?>
				<form method="post" action="<?php echo esc_url( menu_page_url( 'upn-module', false ) ); ?>">
					<input type="hidden" name="command" value="Shrani-nastavitve-generalne">
					<?php wp_nonce_field( 'upn_save_general_settings', 'upn_general_settings_nonce' ); ?>
				<div id="form-table_upn" class="postbox" style="padding: 25px;">
					<h4 style="margin-left: 0px;">Nastavitve plugina:</h4>
					<?php 
					if (get_option('payment_option') == 'upay') { ?>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								Zaključevanje statusov naročil<br/>
								<small>Avtomatsko spreminjanje status naročil iz on-hold v Proccesing  </small>
							</th>
							<td>
								<select name="changeStatus" id="changeStatus">
									<option value="0"
										<?php 

										if (get_option('changeStatus') == '0'){ print "selected";}
										?>
									>
										Ne
									</option>
									<option value="1"
										<?php 

										if (get_option('changeStatus') == '1'){ print "selected";}
										?>
									>
										Da
									</option>

								</select>
							</td>
						</tr>
					</table>
					<table class="form-table">
						<tr valign="top">
							<th scope="row">
								Obveščanje po elektronski pošti
								<small>V polje vnesite elektronski naslov na katerega želite prejemati sporočila.</small>
								<p>Sporočilo se pošlje ob spremembi uPay statusa v zaključeno.</p>
							</th>
							<td>
							<select name="sendEmailToUpn" id="sendEmailToUpn">
									<option value="0"
										<?php 

										if (get_option('sendEmailToUpn') == '0'){ print "selected";}
										?>
									>
										Ne
									</option>
									<option value="1"
										<?php 

										if (get_option('sendEmailToUpn') == '1'){ print "selected";}
										?>
									>
										Da
									</option>

								</select>
								<input type="email" name="emailRecipient_upn" placeholder="info@wooninja.si" value="<?= get_option('emailRecipient_upn');?>">
							</td>
						</tr>
					</table>
					<?php } ?>
					<table class="form-table">					
						<tr valign="top">
							<th scope="row">
								Brisanje UPN nalogov ob statusu Zaključeno<br />
								<small>Brisanje PDF in XML generiranih ob naročilu po zaključku naročila.</small>
							</th>
							<td>
							<select name="upnBrisanje">
								<option value="0" 
									<?php 
										if(get_option('upnBrisanje') == "0") { print "selected"; } 
									?>
								>
									Ne
								</option>
								<option value="1" 
									<?php 
										if(get_option('upnBrisanje') == "1") { print "selected"; } 
									?>
								>
									Da
								</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							Brisanje UPN nalogov ob statusu Prekinjeno<br />
							<small>Brisanje PDF in XML generiranih ob naročilu po prekinitvi naročila.</small>
						</th>
						<td>
							<select name="upnBrisanjeCancel">
								<option value="0" 
									<?php 
										if(get_option('upnBrisanjeCancel') == "0") { print "selected"; } 
									?>
								>
									Ne
								</option>
								<option value="1" 
									<?php 
										if(get_option('upnBrisanjeCancel') == "1") { print "selected"; } 
									?>
								>
									Da
								</option>
							</select>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							Prikaz UPN-ja v Email predlogi
							<small>Iz spustnega menija izberite željeno pozijo za prikaz UPN-ja</small>
						</th>
						<td>
							<select name="email-upn-position">
								<option value="wc_email_before_order_table_upn" 
									<?php 
										if(get_option('email-upn-position') == "wc_email_before_order_table_upn") { print "selected"; } 
									?>
									>
									Nad tabelo naročila
								</option>
								<option value="wc_email_after_order_table_upn" 
									<?php 
										if(get_option('email-upn-position') == "wc_email_after_order_table_upn") { print "selected"; } 
									?>
									>
									Pod tabelo naročila
								</option>
					<!-- 			<option value="wc_email_footer_table_upn" 
									<?php 
										/* if(get_option('email-upn-position') == "wc_email_footer_table_upn") { print "selected"; }  */
									?>
									>
									Pod podatki stranke
								</option> -->
							</select>
						<!--<input type="checkbox" name="shortcode" value="shortcode" <?php if(get_option('shortcode') == true) { print "checked";}?>>-->
						</td>
					</tr>
					</table>
					<button type="submit" name="Submit-settings" class="button button-secondary" value="Shrani-nastavitve">Shrani podatke</button>
					</p>
				</div>
				</form>
			<style>

			</style>

			<?php if (get_option('payment_option') == 'default_upn') { ?>
	        <div class="postbox-container " style="width: 100%;">
	            <div id="normal-sortables" class="ui-sortable meta-box-sortable">
	                <!-- BOXES -->
	                <div class="postbox" style="padding: 25px;">
						
						<div class="div" style="border: 1px solid red; font-family: 'Arial'; font-size: 11px; font-weight: bold;position:relative; background: url('<?php echo UPN_PLUGIN_URL;?>upn.jpg'); height: 400px; background-repeat:no-repeat;">
							
							<div style="position: absolute; left: 20px; top: 35px">Ime priimek</div>

							<!-- namen -->
							<div style="position: absolute; left: 20px; top: 77px">Plačilo računa #</div>

							<!-- znesek -->
							<div style="position: absolute; left: 130px; top: 115px;">* * <?=number_format(15,2,',','.')?></div>

							<!-- IBAN prejemnika -->
							<div style="position: absolute; left: 20px; top: 147px">SI56 1010 1111 2222 333</div>
							<!-- ... in BIC banke -->
							<div style="position: absolute; left: 20px; top: 160px">BAKOSI2X</div>

							<!-- referenca prejemnika -->
							<div style="position: absolute; left: 20px; top: 200px">SI00 000</div>

							<!-- ime prejemnika -->
							<div style="font-size: 10px; position: absolute; left: 20px; top: 230px" class="ime-podjetja">Mizar Jože s.p.</div>
							<div style="font-size: 10px; position: absolute; left: 20px; top: 240px" class="naslov-podjetja">Cesta v Jagodje 2, 6310 Izola</div>


							<!-- PLAÄŒNIK -->

							<!-- ime in naslov -->
							<div style="position: absolute; left: 220px; top: 73px">Ime priimek</div>
							<div style="position: absolute; left: 220px; top: 84px">Naslov, posta</div>

							<!-- namen -->
							<div style="position: absolute; left: 220px; top: 115px">OTHR</div>
							<div style="position: absolute; left: 290px; top: 115px">Plačilo računa #</div>


							<!-- PREJEMNIK -->
							<!-- znesek -->
							<div style="position: absolute; left: 330px; top: 143px">* * <?=number_format(15,2,',','.')?></div>

							<!-- BIC banke -->
							<div style="position: absolute; left: 580px; top: 143px" class="bic-podjetja">BAKOSI2X</div>

							<!-- IBAN prejemnika -->
							<div style="position: absolute; left: 220px; top: 170px" class="trr-podjetja">SI56 1010 1111 2222 333</div>

							<!-- referenca -->
							<div style="position: absolute; left: 220px; top: 200px">SI00 &nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 0000</div>

							<!-- ime in naslov -->
							<div style="position: absolute; left: 220px; top: 229px" class="ime-podjetja">Mizar Jože s.p.</div>
							<div style="position: absolute; left: 220px; top: 239px" class="naslov-podjetja">Cesta v Jagodje 2, 6310 Izola</div>

						</div>

	                </div>
	            </div>
	        </div>
			<?php } ?>
	    </div>
	</div>
</div>
