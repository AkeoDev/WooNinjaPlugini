<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Woo_eSpremnica_CSV_API_Export' ) ) {

	class AuthenticationPS {
		public $KomitentId;
		public $PogodbaId;
		public $PostaId;
		public $PodruznicaId;
	}

	class Woo_eSpremnica_CSV_API_Export {

		private $wsdlAddress;

		public function __construct() {
			if ( is_admin() ) {
				add_action( 'admin_enqueue_scripts', array( $this, 'load_script' ) );
				add_action( 'wp_ajax_espremnica_csv_api_generate', array( $this, 'espremnica_csv_api_generate' ) );
				add_action( 'wp_ajax_espemnica_delete_entry', array( $this, 'espemnica_delete_entry' ) );
				add_action( 'wp_ajax_espremnica_send_to_api', array( $this, 'espremnica_send_to_api' ) );
				add_action( 'wp_ajax_espremnica_generate_list', array( $this, 'espremnica_generate_list' ) );
			}
			$this->wsdlAddress = $this->setWsdlAddress();
		}

		public static function load_script() {
			echo "<script type='text/javascript'>
				var downloadTranslation = '" . esc_js( __( 'Download PDF', 'wooninja-espremnica' ) ) . "';
				var closeTranslation = '" . esc_js( __( 'Close', 'wooninja-espremnica' ) ) . "';
			</script>";

			wp_enqueue_script(
				'espremnica-csv-api',
				plugins_url( 'js/espremnica-csv-api.js', __FILE__ ),
				array( 'jquery', 'common' ),
				false,
				true
			);
			wp_localize_script(
				'espremnica-csv-api',
				'espremnica_csv_api',
				array(
					'nonce'    => wp_create_nonce( 'espremnica-csv-api' ),
					'ajax_url' => admin_url( 'admin-ajax.php' ),
				)
			);
		}

		function get_tracking_number() {
			$min_sprejemna_stevilka   = get_option( 'sprejemnaStevilkaMin' );
			$max_sprejemna_stevilka   = get_option( 'sprejemnaStevilkaMax' );
			$start_sprejemna_stevilka = get_option( 'sprejemnaStevilkaStart' );

			$orders = wc_get_orders( array(
				'status'     => 'any',
				'limit'      => 1,
				'meta_key'   => 'sprejemna_stevilka',
				'orderby'    => 'meta_value_num',
				'order'      => 'DESC',
				'date_query' => array(
					array(
						'year' => date( 'Y' ),
					),
				),
			) );
			if ( ! empty( $orders ) ) {
				$last_order = $orders[0];
				$last_used_sprejemna_stevilka = $last_order->get_meta( 'sprejemna_stevilka' );
				$next_sprejemna_stevilka      = (int) $last_used_sprejemna_stevilka + 1;
				if ( $next_sprejemna_stevilka > (int) $max_sprejemna_stevilka || $next_sprejemna_stevilka < (int) $min_sprejemna_stevilka ) {
					$sprejemna_stevilka = (int) $start_sprejemna_stevilka;
				} else {
					$sprejemna_stevilka = $next_sprejemna_stevilka;
				}
			} else {
				$sprejemna_stevilka = $start_sprejemna_stevilka;
			}
			return $sprejemna_stevilka;
		}

		public function generate_tracking_code( $tracking_number, $smerna_koda = false, $spaceless = false ) {
			$oznakaPosiljke = get_option( 'oznakaZaVrstoPosiljke' );
			if ( $smerna_koda ) {
				$oznakaPosiljke = 'AD';
			}
			$tracking_number    = str_pad( $tracking_number, 8, '0', STR_PAD_LEFT );
			$alfanumericNumber  = $oznakaPosiljke . ' ' . wordwrap( $tracking_number, 4, ' ', true ) . ' ' . $this->getControlBit( str_replace( ' ', '', $tracking_number ) ) . ' SI';

			if ( $spaceless ) {
				$alfanumericNumber = str_replace( ' ', '', $alfanumericNumber );
			}
			return $alfanumericNumber;
		}

		public function getControlBit( $tracking_number ) {
			$utezniFaktorji    = array( '8', '6', '4', '2', '3', '5', '9', '7' );
			$sprejemnaStevilka = str_split( $tracking_number );

			$sumTotal = 0;
			foreach ( $sprejemnaStevilka as $key => $number ) {
				$sumTotal += $number * $utezniFaktorji[ $key ];
			}

			$controlBit = fmod( $sumTotal, 11 );
			$controlBit = 11 - (int) $controlBit;
			if ( $controlBit >= 1 && $controlBit <= 9 ) {
				return $controlBit;
			} elseif ( $controlBit == 10 ) {
				return 0;
			} elseif ( $controlBit == 11 ) {
				return 5;
			}
		}

		private function psAuthentication() {
			$psAuth = array(
				'komitentId'   => get_option( 'KomitentId' ),
				'pogodbaId'    => get_option( 'PogodbaId' ),
				'podruznicaId' => get_option( 'PodruznicaId' ),
				'postaId'      => get_option( 'PostaID' ),
			);
			return $psAuth;
		}

		private function setWsdlAddress() {
			if ( get_option( 'TestMode' ) !== false && get_option( 'TestMode' ) ) {
				return 'https://tsteportal.posta.si/Services/eSpremnica.Provider/WCF/eOddaja.svc?wsdl';
			} else {
				return 'https://eportal.posta.si/Services/eSpremnica.Provider/WCF/eOddaja.svc?wsdl';
			}
		}

		public function fetchPSWebService() {
			$options = array(
				'soap_version'   => SOAP_1_1,
				'exceptions'     => false,
				'stream_context' => stream_context_create(
					array(
						'ssl' => array(
							'verify_peer'      => false,
							'verify_peer_name' => false,
						),
					)
				),
			);

			$webServiceClient = new SoapClient( $this->wsdlAddress, $options );

			return $webServiceClient;
		}

		public function resultCodeValid( $response ) {
			$resultCode = $response->aResult->ResultCode;
			if ( $resultCode == 'Normal' ) {
				return true;
			}
			return false;
		}

		function espemnica_delete_entry() {
			check_ajax_referer( 'espremnica-csv-api', 'nonce' );

			if ( isset( $_POST['espremnica_sprejemna_stevilka'] ) ) {
				$post_id            = absint( $_POST['order_id'] );
				$sprejemna_stevilka = sanitize_text_field( wp_unslash( $_POST['espremnica_sprejemna_stevilka'] ) );

				$order       = wc_get_order( $post_id );
				$pdfLocation = __DIR__ . '/download/eSpremnica-' . md5( $sprejemna_stevilka ) . '.pdf';

				if ( file_exists( $pdfLocation ) ) {
					unlink( $pdfLocation );
				}

				$order->delete_meta_data( 'sprejemna_stevilka' );
				$order->update_meta_data( 'order_posta', 'NE' );
				$order->save();
			}

			wp_die();
		}

		function espremnica_csv_api_generate() {
			check_ajax_referer( 'espremnica-csv-api', 'nonce' );

			$post_id = absint( $_POST['order_id'] );

			if ( isset( $_POST['espremnica_csv_form'] ) ) {
				$form_data = array();
				parse_str( sanitize_text_field( wp_unslash( $_POST['espremnica_csv_form'] ) ), $form_data );

				$order    = wc_get_order( $post_id );

				if ( isset( $form_data['espremnica_csv_dodatne_storitve'] ) ) {
					$order->update_meta_data(
						'espremnica_csv_dodatne_storitve',
						array_map( 'sanitize_text_field', $form_data['espremnica_csv_dodatne_storitve'] )
					);
				} else {
					$order->update_meta_data( 'espremnica_csv_dodatne_storitve', array() );
				}

				if ( isset( $form_data['espremnica_csv_vrste_posiljk'] ) ) {
					$order->update_meta_data(
						'espremnica_csv_vrste_posiljk',
						sanitize_text_field( $form_data['espremnica_csv_vrste_posiljk'] )
					);
				} else {
					$order->update_meta_data( 'espremnica_csv_vrste_posiljk', array() );
				}

				if ( isset( $form_data['opomba_espremnica_csv'] ) ) {
					$order->update_meta_data(
						'opomba_espremnica_csv',
						sanitize_textarea_field( $form_data['opomba_espremnica_csv'] )
					);

					$order->update_meta_data( 'espremnica_csv_saved_before', 'yes' );
				}
				$order->save();
			} else {
				$order    = wc_get_order( $post_id );
			}
			$order_id = $order->get_id();

			$currency = $order->get_currency();

			if ( $order->get_shipping_first_name() != '' ) {
				$orderData = 'shipping';
				$ime       = $order->get_shipping_first_name();
				$priimek   = $order->get_shipping_last_name();
				$naslov    = $order->get_shipping_address_1();
				$posta     = $order->get_shipping_postcode();
				$mesto     = $order->get_shipping_city();
				$drzava    = $order->get_shipping_country();
				if ( $order->get_billing_phone() != null && $order->get_billing_phone() != false ) {
					$telefon = $order->get_billing_phone();
				}
			} else {
				$orderData = 'billing';
				$ime       = $order->get_billing_first_name();
				$priimek   = $order->get_billing_last_name();
				$naslov    = $order->get_billing_address_1();
				$posta     = $order->get_billing_postcode();
				$mesto     = $order->get_billing_city();
				$drzava    = $order->get_billing_country();
				$telefon   = $order->get_billing_phone();
			}

			$drzave = get_espremnica_csv_koda_drzave();
			if ( $drzava == '' ) {
				$drzava = '705';
			} else {
				$drzava = vrniKodoDrzave( $drzava, $drzave );
			}

			$billing_phone = $order->get_billing_phone();
			$telefon       = '' . preg_replace( '/^0/', '00386', $billing_phone );

			$email = $order->get_billing_email();

			$opomba    = $order->get_meta( 'opomba_espremnica_csv' );
			$cena      = GetPostaTotal( $order->get_total() + 0 );
			$odkupnina = '';

			$dodatne_storitve = $order->get_meta( 'espremnica_csv_dodatne_storitve' );

			if ( ! is_array( $dodatne_storitve ) ) {
				$dodatne_storitve = get_option( 'espremnica_csv_default_dodatne_storitve', array() );
				if ( in_array( 'ODK', $dodatne_storitve, true ) ) {
					$odkupnina = $cena;
				}
				$dodatne_storitve             = implode( ',', $dodatne_storitve );
				$dodatneStoritvePdfNalepka    = $dodatne_storitve;
			} else {
				if ( in_array( 'ODK', $dodatne_storitve, true ) ) {
					$odkupnina = $cena;
				}
				$dodatneStoritvePdfNalepka = $dodatne_storitve;
				$dodatne_storitve          = implode( ',', $dodatne_storitve );
			}

			$vrste_posiljke = $order->get_meta( 'espremnica_csv_vrste_posiljk' );

			$result = array();

			$tracking_number = $this->get_tracking_number();

			require_once __DIR__ . '/code128/code128.php';

			$imeFirme          = get_option( 'firmaIme' );
			$naslovFirme       = get_option( 'firmaNaslov' );
			$postaFirme        = get_option( 'firmaPosta' );
			$telKontaktFirme   = get_option( 'firmaKontaktTelefon' );

			$pdf = new PDF_Code128( 'P', 'pt', 'A4' );
			$pdf->SetMargins( 0, 0, 0 );
			$pdf->AddPage();
			$pdf->AddFont( 'DejaVu', '', 'DejaVuSansCondensed.ttf', true );
			$pdf->SetFont( 'DejaVu', '', 12 );

			$pdf->Line( 12, 12, 286, 12 );
			$pdf->Line( 286, 12, 286, 408 );
			$pdf->Line( 286, 408, 12, 408 );
			$pdf->Line( 12, 408, 12, 12 );
			$pdf->Line( 12, 72, 286, 72 );
			$pdf->Line( 12, 141.6, 286, 141.6 );
			$pdf->Line( 12, 206.1, 286, 206.1 );
			$pdf->Line( 12, 270.6, 286, 270.6 );
			$pdf->Line( 12, 352.6, 286, 352.6 );
			$pdf->Line( 19, 158, 279, 203 );
			$pdf->Line( 19, 203, 279, 158 );
			$pdf->Image( __DIR__ . '/img/ps.png', 17.15, 76.32, 87, 13.2 );
			$pdf->Image( __DIR__ . '/img/telefon.png', 275.2, 144, 7.4, 10.4 );
			$pdf->Image( __DIR__ . '/img/telefon.png', 275.2, 207.8, 7.4, 10.4 );

			$paymentMethod = $order->get_payment_method();

			$pdf->SetFont( 'DejaVu', '', 12 );
			$label = 'Storitve';
			$pdf->SetXY( 16.7, 274.7 );
			$pdf->Write( 12, $label );

			if ( $opomba !== '' ) {
				$pdf->SetFont( 'DejaVu', '', 7 );
				$label = 'Opomba: ' . $opomba;
				$pdf->SetXY( 16.7, 340 );
				$pdf->Write( 7, $label );
				$pdf->SetFont( 'DejaVu', '', 12 );
			}

			if ( is_array( $dodatneStoritvePdfNalepka ) ) {
				$xKoordinataStoritveSingle = 16.7;
				$yKoordinataMultiplier     = 1;
				$xKoordinataMultiplier     = 1;
				foreach ( $dodatneStoritvePdfNalepka as $dodatnaStoritev ) {
					if ( $yKoordinataMultiplier == 1 && $xKoordinataMultiplier == 1 ) {
						$pdf->SetXY( 16.7, 289 );
						$pdf->Write( 10, $dodatnaStoritev );
					} else {
						$pdf->SetXY( 16.7 + ( 79 * ( $xKoordinataMultiplier - 1 ) ), 289 + ( 11 * ( $yKoordinataMultiplier - 1 ) ) );
						$pdf->Write( 10, $dodatnaStoritev );
					}
					if ( $yKoordinataMultiplier % 3 == 0 ) {
						$xKoordinataMultiplier++;
						$yKoordinataMultiplier = 0;
					}
					$yKoordinataMultiplier++;
				}
			} else {
				$label = $dodatneStoritvePdfNalepka;
				$pdf->SetXY( 16.7, 289 );
				$pdf->Write( 10, $label );
			}

			$pdf->SetFont( 'DejaVu', '', 10 );
			$label = get_option( 'PostaID' ) . ' ' . get_option( 'PostaIme' );
			$pdf->SetXY( 16.7, 92.6 );
			$pdf->Write( 10, 'Sprejemna pošta: ' . $label );

			if ( is_array( $vrste_posiljke ) ) {
				if ( in_array( '109', $vrste_posiljke, true ) ) {
					$pdf->SetFont( 'DejaVu', '', 16 );
					$label = 'Mali PPKT';
					$pdf->SetFillColor( 0, 0, 0 );
					$pdf->Rect( 148, 72, 134, 17, 'F' );
					$pdf->SetXY( 148, 72 );
					$pdf->Write( 16, $label );
				}
			} else {
				if ( $vrste_posiljke == '109' ) {
					$pdf->SetFont( 'DejaVu', '', 16 );
					$label = 'Mali PPKT';
					$w     = $pdf->GetStringWidth( $label ) + 67;
					$pdf->SetFillColor( 0, 0, 0 );
					$pdf->SetTextColor( 255, 255, 255 );
					$pdf->SetXY( 148, 72 );
					$pdf->Cell( $w, 17, $label, 1, 1, 'C', true );
					$pdf->SetTextColor( 0, 0, 0 );
				}
			}

			$pdf->SetFont( 'DejaVu', '', 10 );
			$tracking_code = $this->generate_tracking_code( $tracking_number, false, true );
			$label         = $this->generate_tracking_code( $tracking_number, false, false );
			$pdf->Code128( 57.39, 22.75, $tracking_code, 183, 35.4 );
			$pdf->SetXY( 107.55, 60.3 );
			$pdf->Write( 10, $label );

			$pdf->SetFont( 'DejaVu', '', 10 );
			$label = 'Datum sprejema: ' . date( 'd.m.Y' );
			$pdf->SetXY( 16.7, 105.7 );
			$pdf->Write( 10, $label );
			$label = 'Masa:';
			$pdf->SetXY( 16.7, 118.4 );
			$pdf->Write( 10, $label );
			$label = '(g)';
			$pdf->SetXY( 88, 118.4 );
			$pdf->Write( 10, $label );
			$label = 'Poštnina: Pogodba';
			$pdf->SetXY( 16.7, 130.4 );
			$pdf->Write( 10, $label );

			if ( in_array( 'V', $dodatneStoritvePdfNalepka, true ) ) {
				$label = 'Vrednost: ' . $cena . ' EUR';
				$pdf->SetXY( 166, 105.7 );
				$pdf->Write( 10, $label );
			}

			if ( in_array( 'ODK', $dodatneStoritvePdfNalepka, true ) || in_array( 'ODKBN', $dodatneStoritvePdfNalepka, true ) ) {
				$label = 'Odkupnina: ' . $cena . ' EUR';
				$pdf->SetXY( 166, 118.4 );
				$pdf->Write( 10, $label );
			}

			$label = 'Pošiljatelj';
			$pdf->SetXY( 16.7, 145.5 );
			$pdf->Write( 10, $label );
			$label = strtoupper( $imeFirme );
			$pdf->SetXY( 16.7, 158.65 );
			$pdf->Write( 10, $label );
			$label = strtoupper( $naslovFirme );
			$pdf->SetXY( 16.7, 168.5 );
			$pdf->Write( 10, $label );
			$label = strtoupper( $postaFirme );
			$pdf->SetXY( 16.7, 178.7 );
			$pdf->Write( 10, $label );
			$label = $telKontaktFirme;
			$pdf->SetXY( 200.6, 144.4 );
			$pdf->Write( 10, $label );

			$label     = 'Naslovnik';
			$pdf->SetXY( 16.7, 209.3 );
			$pdf->Write( 10, $label );
			$post_code = preg_replace( '/[^0-9]/', '', $posta );
			$pdf->SetFont( 'DejaVu', '', 10 );
			$label = preg_replace( '/[^0-9]/', '', $telefon );
			$pdf->SetXY( 200.6, 208.5 );
			$pdf->Write( 10, $label );
			$pdf->SetFont( 'DejaVu', '', 12 );
			$label = strtoupper( $ime ) . ' ' . strtoupper( $priimek );
			$pdf->SetXY( 16.7, 222.5 );
			$pdf->Write( 12, $label );
			$label = strtoupper( $naslov );
			$pdf->SetXY( 16.7, 232.9 );
			$pdf->Write( 12, $label );
			$label = $post_code . ' ' . preg_replace( '/[^A-Ža-ž0-9 ]/', '', strtoupper( $mesto ) );
			$pdf->SetXY( 16.7, 257.4 );
			$pdf->Write( 12, $label );

			$pdf->SetFont( 'DejaVu', '', 10 );
			$barcodeLabel = $this->generate_tracking_code( str_pad( $post_code, 8, '0', STR_PAD_RIGHT ), true, false );
			$barcode      = $this->generate_tracking_code( str_pad( $post_code, 8, '0', STR_PAD_RIGHT ), true, true );
			$pdf->Code128( 57.39, 358.7, $barcode, 183, 35.4 );
			$pdf->SetXY( 107.55, 396.4 );
			$pdf->Write( 10, $barcodeLabel );

			$pdfLocation = __DIR__ . '/download/eSpremnica-' . md5( $tracking_number ) . '.pdf';
			$pdfUrl      = plugin_dir_url( __FILE__ ) . '/download/eSpremnica-' . md5( $tracking_number ) . '.pdf';
			$pdf->Output( $pdfLocation, 'F' );

			$csv['VrstaPosiljke']    = "$vrste_posiljke";
			$csv['CrtnaKoda']        = $this->generate_tracking_code( $tracking_number, false, true );
			$csv['Naziv']            = $ime . ' ' . $priimek;
			$csv['DodatenNaziv']     = '';
			$csv['Naslov']           = "$naslov";
			$csv['PostnaSt']         = "$posta";
			$csv['NazivPoste']       = "$mesto";
			$csv['Drzava']           = "$drzava";
			$csv['TelSt']            = "$telefon";
			$csv['EMail']            = "$email";
			$csv['IdNaslovnika']     = '';
			$csv['Opomba']           = "$opomba";
			$csv['Masa']             = '';
			$csv['DodatneStoritve']  = "$dodatne_storitve";
			$csv['Odkupnina']        = "$odkupnina";
			$csv['Vrednost']         = '';
			$csv['VrstaVplDok']      = '';
			$csv['RefX']             = '';
			$csv['Model']            = '';
			$csv['Sklic']            = "$order_id";
			$csv['Namen']            = '';
			$csv['OdkupninaVValuti'] = '';
			$csv['Valuta']           = '';
			$csv['Navodilo']         = '';

			$wp_url  = get_home_url();
			$wp_path = get_home_path();

			$result['csv']                        = $csv;
			$result['data']['wp_url']             = $wp_url;
			$result['data']['pdf_url']            = $pdfUrl;
			$result['data']['wp_path']            = $wp_path;
			$result['data']['sprejemna_stevilka'] = (string) $tracking_number;

			$list = array(
				array_keys( $csv ),
				array_values( $csv ),
			);

			$result['list'] = $list;

			$csvString = implode( ';', array_keys( $csv ) ) . PHP_EOL . implode( ';', $csv );

			$authPs               = new AuthenticationPS();
			$authPs->KomitentId   = get_option( 'KomitentId' );
			$authPs->PogodbaId    = get_option( 'PogodbaId' );
			$authPs->PostaId      = get_option( 'PostaID' );
			$authPs->PodruznicaId = get_option( 'PodruznicaId' );

			$requestData = array(
				'aAuthentication' => $authPs,
			);

			$order->update_meta_data( 'sprejemna_stevilka', $tracking_number );
			$order->update_meta_data( 'order_posta', 'DA' );
			$order->update_meta_data( 'crtna_koda', $csv['CrtnaKoda'] );
			$order->save();

			unset( $result['csv'] );
			unset( $result['list'] );
			echo wp_json_encode( $result );
			wp_die();
		}

		function espremnica_send_to_api() {
			check_ajax_referer( 'espremnica-csv-api', 'nonce' );

			$all_orders = wc_get_orders( array(
				'status'     => 'any',
				'limit'      => -1,
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key'     => 'sprejemna_stevilka',
						'compare' => 'EXISTS',
					),
					array(
						'key'     => 'order_posta',
						'value'   => 'DA',
						'compare' => '=',
					),
					array(
						'key'     => 'espremnica_odposlano',
						'compare' => 'NOT EXISTS',
					),
				),
				'orderby'  => 'meta_value_num',
				'meta_key' => 'sprejemna_stevilka',
				'order'    => 'ASC',
			) );

			if ( ! empty( $all_orders ) ) {
				$order_ids              = array();
				$order_tracking_numbers = '';
				$csvString              = '';

				$fp = fopen( plugin_dir_path( __FILE__ ) . 'download/espremnica.csv', 'w' );

				foreach ( $all_orders as $order ) {
					$postaObj = new Woo_eSpremnica_CSV_API_Export();
					$order_id = $order->get_id();
					$currency = $order->get_currency();

					if ( $order->get_shipping_first_name() != '' ) {
						$orderData = 'shipping';
						$ime       = $order->get_shipping_first_name();
						$priimek   = $order->get_shipping_last_name();
						$naslov    = $order->get_shipping_address_1();
						$posta     = $order->get_shipping_postcode();
						$mesto     = $order->get_shipping_city();
						$drzava    = $order->get_shipping_country();
						if ( $order->get_billing_phone() != null && $order->get_billing_phone() != false ) {
							$telefon = $order->get_billing_phone();
						}
					} else {
						$orderData = 'billing';
						$ime       = $order->get_billing_first_name();
						$priimek   = $order->get_billing_last_name();
						$naslov    = $order->get_billing_address_1();
						$posta     = $order->get_billing_postcode();
						$mesto     = $order->get_billing_city();
						$drzava    = $order->get_billing_country();
						$telefon   = $order->get_billing_phone();
					}

					$drzave = get_espremnica_csv_koda_drzave();
					if ( $drzava == '' ) {
						$drzava = '705';
					} else {
						$drzava = vrniKodoDrzave( $drzava, $drzave );
					}

					$billing_phone = $order->get_billing_phone();
					$telefon       = '' . preg_replace( '/^0/', '00386', $billing_phone );
					$email         = $order->get_billing_email();

					$opomba           = $order->get_meta( 'opomba_espremnica_csv' );
					$cena             = GetPostaTotal( $order->get_total() + 0 );
					$odkupnina        = '';
					$vrednost_paketa  = '';
					$dodatne_storitve = $order->get_meta( 'espremnica_csv_dodatne_storitve' );

					if ( ! is_array( $dodatne_storitve ) ) {
						$dodatne_storitve = get_option( 'espremnica_csv_default_dodatne_storitve', array() );
						if ( in_array( 'ODK', $dodatne_storitve, true ) || in_array( 'ODKBN', $dodatne_storitve, true ) ) {
							$odkupnina = $cena;
						}
						$dodatne_storitve          = implode( ',', $dodatne_storitve );
						$dodatneStoritvePdfNalepka = $dodatne_storitve;
					} else {
						if ( in_array( 'ODK', $dodatne_storitve, true ) || in_array( 'ODKBN', $dodatne_storitve, true ) ) {
							$odkupnina = $cena;
						}
						$dodatneStoritvePdfNalepka = $dodatne_storitve;
						$dodatne_storitve          = implode( ',', $dodatne_storitve );
					}

					if ( is_array( $dodatne_storitve ) && count( $dodatne_storitve ) > 0 ) {
						if ( in_array( 'V', $dodatne_storitve, true ) ) {
							$vrednost_paketa = $cena;
						}
					}

					$vrste_posiljke = $order->get_meta( 'espremnica_csv_vrste_posiljk' );
					if ( is_array( $vrste_posiljke ) ) {
						$vrste_posiljke = implode( ',', $vrste_posiljke );
					}

					$result          = array();
					$tracking_number = $order->get_meta( 'sprejemna_stevilka' );
					$crtna_koda      = $order->get_meta( 'crtna_koda' );

					$crtna_koda_split = substr( $crtna_koda, 0, 2 ) . ' ' . substr( $crtna_koda, 2, 4 ) . ' ' . substr( $crtna_koda, 6, 4 ) . ' ' . substr( $crtna_koda, 10, 1 ) . ' ' . substr( $crtna_koda, 11, 2 );
					$countryCode      = $order->get_billing_country();

					$csv['VrstaPosiljke']    = "{$vrste_posiljke}";
					$csv['CrtnaKoda']        = "{$crtna_koda}";
					$csv['Naziv']            = "{$ime} {$priimek}";
					$csv['DodatenNaziv']     = '';
					$csv['Naslov']           = "{$naslov}";
					$csv['PostnaSt']         = "{$posta}";
					$csv['NazivPoste']       = "{$mesto}";
					$csv['Drzava']           = "{$drzava}";
					$csv['TelSt']            = "{$telefon}";
					$csv['EMail']            = "{$email}";
					$csv['IdNaslovnika']     = '';
					$csv['Opomba']           = "{$opomba}";
					$csv['Masa']             = '';
					$csv['DodatneStoritve']  = "{$dodatne_storitve}";
					$csv['Odkupnina']        = "{$odkupnina}";
					$csv['Vrednost']         = "{$vrednost_paketa}";
					$csv['VrstaVplDok']      = '';
					$csv['RefX']             = '';
					$csv['Model']            = '';
					$csv['Sklic']            = "{$order_id}";
					$csv['Namen']            = '';
					$csv['OdkupninaVValuti'] = '';
					$csv['Valuta']           = '';
					$csv['Navodilo']         = '';

					$csvPrint['VrstaPosiljke']    = "{$vrste_posiljke}";
					$csvPrint['CrtnaKoda']        = "{$crtna_koda}";
					$csvPrint['Naziv']            = "{$ime} {$priimek}";
					$csvPrint['DodatenNaziv']     = '';
					$csvPrint['Naslov']           = "{$naslov}";
					$csvPrint['PostnaSt']         = "{$countryCode}-{$posta}";
					$csvPrint['NazivPoste']       = "{$mesto}";
					$csvPrint['Drzava']           = "{$drzava}";
					$csvPrint['TelSt']            = "{$telefon}";
					$csvPrint['EMail']            = "{$email}";
					$csvPrint['IdNaslovnika']     = '';
					$csvPrint['Opomba']           = "{$opomba}";
					$csvPrint['Masa']             = '';
					$csvPrint['DodatneStoritve']  = "{$dodatne_storitve}";
					$csvPrint['Odkupnina']        = "{$odkupnina}";
					$csvPrint['Vrednost']         = "{$vrednost_paketa}";
					$csvPrint['VrstaVplDok']      = '';
					$csvPrint['RefX']             = '';
					$csvPrint['Model']            = '';
					$csvPrint['Sklic']            = "{$order_id}";
					$csvPrint['Namen']            = '';
					$csvPrint['OdkupninaVValuti'] = '';
					$csvPrint['Valuta']           = '';
					$csvPrint['Navodilo']         = '';
					$csvPrint['DrzavaKoda']       = $countryCode;

					$wp_url  = get_home_url();
					$wp_path = ABSPATH;

					$result['csv']                        = $csv;
					$result['data']['wp_url']             = $wp_url;
					$result['data']['wp_path']            = $wp_path;
					$result['data']['sprejemna_stevilka'] = (string) $tracking_number;

					$list = array(
						array_keys( $csv ),
						array_values( $csv ),
					);

					$result['list'] = $list;

					if ( $csvString == '' ) {
						$listPrint = array(
							array_keys( $csvPrint ),
							array_values( $csvPrint ),
						);
					} else {
						$listPrint = array(
							array_values( $csvPrint ),
						);
					}

					foreach ( $listPrint as $fields ) {
						fputcsv( $fp, $fields, ';', '"' );
					}

					$csvString .= implode( ';', $csv );

					$csvString .= PHP_EOL;

					$order_ids[]            = $order_id;
					$order_tracking_numbers .= $tracking_number . ', ';
				}

				// Remove trailing newline
				$csvString = rtrim( $csvString, PHP_EOL );

				fclose( $fp );
				$result['data']['sprejemne_stevilke_odposlano'] = $order_tracking_numbers;
			}

			$authPs               = new AuthenticationPS();
			$authPs->KomitentId   = get_option( 'KomitentId' );
			$authPs->PogodbaId    = get_option( 'PogodbaId' );
			$authPs->PostaId      = get_option( 'PostaID' );
			$authPs->PodruznicaId = get_option( 'PodruznicaId' );

			$requestData = array(
				'aAuthentication' => $authPs,
			);

			$response = $postaObj->fetchPSWebService()->GetGuid( $requestData );

			if ( $postaObj->resultCodeValid( $response ) === true ) {
				$aGuid       = $response->GetGuidResult;
				$requestData = array(
					'aAuthentication' => $authPs,
					'aGuid'           => $aGuid,
					'aData'           => $csvString,
				);

				$response = $postaObj->fetchPSWebService()->PostData( $requestData );

				if ( $postaObj->resultCodeValid( $response ) === true ) {
					$requestData = array(
						'aAuthentication' => $authPs,
						'aGuid'           => $aGuid,
					);
					$response = $postaObj->fetchPSWebService()->GetStatus( $requestData );

					if ( $postaObj->resultCodeValid( $response ) === true ) {
						$last_order_obj = wc_get_order( $order_id );
						if ( $last_order_obj ) {
							$last_order_obj->update_meta_data( 'guid', $aGuid );
							$last_order_obj->save();
						}
						if ( get_option( 'stevilka_oddajnega_popisa' ) ) {
							$currentNum = get_option( 'stevilka_oddajnega_popisa' );
							update_option( 'stevilka_oddajnega_popisa', $currentNum + 1 );
						} else {
							update_option( 'stevilka_oddajnega_popisa', 1, true );
						}

						unset( $result['csv'] );
						unset( $result['list'] );
						echo wp_json_encode( $result );
						foreach ( $order_ids as $single_id_order ) {
							$single_order_obj = wc_get_order( $single_id_order );
							if ( $single_order_obj ) {
								$single_order_obj->update_meta_data( 'espremnica_odposlano', 'DA' );
								$single_order_obj->update_meta_data( 'sticker_generated', date( 'Y-m-d' ) );
								$single_order_obj->update_meta_data( 'espremnica_st_oddajnega_popisa', get_option( 'stevilka_oddajnega_popisa' ) );
								$single_order_obj->save();
							}
						}
						exit;
					} else {
						echo wp_json_encode( $response );
					}
				} else {
					echo wp_json_encode( $response );
				}
			} else {
				echo wp_json_encode( $response );
			}
			wp_die();
		}

		function espremnica_generate_list() {
			check_ajax_referer( 'espremnica-csv-api', 'nonce' );

			require_once __DIR__ . '/code128/eSpremnicaPDF.php';

			$pdf = new PDF_Code128_eSpremnica( 'L', 'pt', 'A4' );
			$pdf->AddFont( 'DejaVu', '', 'DejaVuSansCondensed.ttf', true );
			$pdf->AddPage();
			$pdf->SetFont( 'DejaVu', '', 10 );

			$fp      = fopen( plugin_dir_path( __FILE__ ) . 'download/espremnica.csv', 'r' );
			$flag    = true;
			$csvKeys = '';
			$stevec  = 1;

			while ( ! feof( $fp ) ) {
				if ( $flag == true ) {
					$csvFirstLineValues = explode( ';', fgetcsv( $fp )[0] );
					$csvKeys            = array_combine( $csvFirstLineValues, $csvFirstLineValues );
					$flag               = false;
					continue;
				}

				$csvValues = explode( ';', fgetcsv( $fp, 0, '#' )[0] );
				if ( count( $csvValues ) > 1 ) {
					$combinedValues    = array_combine( $csvFirstLineValues, $csvValues );
					$crtna_koda        = $combinedValues['CrtnaKoda'];
					$sprejemna_stevilka = substr( $crtna_koda, 0, 2 ) . ' ' . substr( $crtna_koda, 2, 4 ) . ' ' . substr( $crtna_koda, 6, 4 ) . ' ' . substr( $crtna_koda, 10, 1 ) . ' ' . substr( $crtna_koda, 11, 2 );
					$naslovnaPosta     = "{$combinedValues['PostnaSt']} " . strtoupper( $combinedValues['NazivPoste'] );
					$imePriimek        = strtoupper( str_replace( '"', '', $combinedValues['Naziv'] ) );
					$naslov            = strtoupper( str_replace( '"', '', $combinedValues['Naslov'] ) );

					$pdf->Cell( 32, 30, $stevec, 1, 0, 'L' );
					$pdf->Cell( 102, 30, $sprejemna_stevilka, 1, 0, 'L' );
					$pdf->Cell( 116, 15, $imePriimek, 'TLR', 2, 'L' );
					$pdf->Cell( 116, 15, $naslov, 'BLR', 0, 'L' );
					$pdf->SetXY( $pdf->GetX(), $pdf->GetY() - 15 );
					$pdf->Cell( 116, 30, $naslovnaPosta, 1, 0, 'L' );
					$pdf->Cell( 65, 30, '', 1, 0, 'L' );
					$pdf->Cell( 54, 30, $combinedValues['Vrednost'], 1, 0, 'L' );
					$pdf->Cell( 54, 30, $combinedValues['Odkupnina'], 1, 0, 'L' );
					$pdf->Cell( 91, 30, $combinedValues['DodatneStoritve'], 1, 0, 'L' );
					if ( strlen( $combinedValues['Opomba'] ) > 28 ) {
						$pdf->Cell( 0, 15, substr( $combinedValues['Opomba'], 0, 28 ), 'TLR', 2, 'L' );
						$pdf->Cell( 0, 15, substr( $combinedValues['Opomba'], 28 ), 'BLR', 1, 'L' );
					} else {
						$pdf->Cell( 0, 30, $combinedValues['Opomba'], 1, 1, 'L' );
					}

					$stevec++;
				}
			}
			fclose( $fp );

			$stOddajnegaPopisa = get_option( 'stevilka_oddajnega_popisa' );
			$pdfLocation       = __DIR__ . '/download/eSpremnica-oddano-' . $stOddajnegaPopisa . '.pdf';
			$pdfUrl            = plugin_dir_url( __FILE__ ) . '/download/eSpremnica-oddano-' . $stOddajnegaPopisa . '.pdf';
			$pdf->Output( $pdfLocation, 'F' );

			$result['data']['pdf_url'] = $pdfUrl;
			echo wp_json_encode( $result );
			wp_die();
		}
	}
}
