<?php


function upn_generate_pdf($data){
    // we add tfpdf library for generating PDF and set up corrdinates
    require_once( dirname(__FILE__) . "/../tfpdf/tfpdf.php");
    $upnCoordinates = array(
      //Desni del
      array('id' => 'A1', 'x' => 4, 'y' => -93, 'order' => 16, 'x2' => 57, 'y2' => 84.25, 'value' => $data["placnik_ime"]),
      array('id' => 'A2', 'x' => 3.9, 'y' => -88, 'order' => 21, 'x2' => 57, 'y2' => 81.1, 'value' => $data['placnik_naslov'] . " " . $data['placnik_kraj']),

      array('id' => 'A3', 'x' => 4, 'y' => -75, 'order' => 23, 'x2' => 57, 'y2' => 71.75, 'value' => $data['namen']),

      array('id' => 'A4', 'x' => 17, 'y' => -65, 'order' => 32, 'x2' => 57, 'y2' => 63.25, 'value' => '***' . number_format($data['znesek'],2,',','.')),
      array('id' => 'A5', 'x' => 4, 'y' => -55, 'order' => 44, 'x2' => 57, 'y2' => 46.25, 'value' => $data['trr']),
      array('id' => 'A6', 'x' => 4, 'y' => -50, 'order' => 58, 'x2' => 57, 'y2' => 37.75, 'value' => $data['sklic']),

      array('id' => 'A7', 'x' => 4, 'y' => -39, 'order' => 64, 'x2' => 57, 'y2' => 25.25, 'value' => $data['podjetje_ime']),
      array('id' => 'A8', 'x' => 4, 'y' => -35, 'order' => 64, 'x2' => 57, 'y2' => 25.25, 'value' => $data['podjetje_naslov']),
      array('id' => 'A9', 'x' => 4, 'y' => -31, 'order' => 64, 'x2' => 57, 'y2' => 25.25, 'value' => $data['podjetje_kraj']),


      //Levi del
      array('id' => 'A10', 'x' => 65, 'y' => -25, 'order' => 64, 'x2' => 65, 'y2' => 50, 'value' => $data['podjetje_ime']),
      array('id' => 'A11', 'x' => 65, 'y' => -20, 'order' => 64, 'x2' => 65, 'y2' => 50, 'value' => $data['podjetje_naslov']),
      array('id' => 'A12', 'x' => 65, 'y' => -15, 'order' => 64, 'x2' => 65, 'y2' => 50, 'value' => $data['podjetje_kraj']),


      array('id' => 'A13', 'x' => 65, 'y' => -41, 'order' => 64, 'x2' => 65, 'y2' => 50, 'value' => $data['trr']),
      array('id' => 'A14', 'x' => 65, 'y' => -33, 'order' => 58, 'x2' => 65, 'y2' => 37.75, 'value' => $data['sklic']),

      array('id' => 'A15', 'x' => 65, 'y' => -50, 'order' => 58, 'x2' => 65, 'y2' => 37.75, 'value' => $data['koda']),
      array('id' => 'A16', 'x' => 80, 'y' => -50, 'order' => 58, 'x2' => 80, 'y2' => 37.75, 'value' => $data['namen']),

      array('id' => 'A17', 'x' => 114, 'y' => -59, 'order' => 58, 'x2' => 114, 'y2' => 37.75, 'value' => '***' . number_format($data['znesek'],2,',','.')),

    );
    $coorOrder = array_column($upnCoordinates, 'order');
    array_multisort($coorOrder, SORT_ASC, $upnCoordinates);

    // we instantiate new pdf and populate it with data
    $pdf = new tFPDF('L','mm',array(210,101.6));
    $pdf->SetMargins(0,0,0);
    $pdf->SetAutoPageBreak(false,0);
    $pdf->AddPage();
    $pdf->AddFont('DejaVu','','DejaVuSansCondensed-Bold.ttf',true);
    $pdf->Image( dirname(__FILE__) . '/../upnqr.png', 0, 0, 210, 101.6);
    $pdf->SetFont('DejaVu', '', 9);
    foreach ($upnCoordinates as $fieldCoordinates) {
      if (isset($fieldCoordinates['value'])) {
        $x1 = $fieldCoordinates['x'];
        $x2 = $fieldCoordinates['x2'];
        $xCalc = $x2 - $x1;
        $y1 = $fieldCoordinates['y'];
        $y2 = $fieldCoordinates['y2'];
        $yCalc = abs($y1 + $y2);
        $pdf->SetXY($fieldCoordinates['x'],$fieldCoordinates['y']);
        $pdf->MultiCell($xCalc,4,$fieldCoordinates['value']);
      }
    }

    //DOdaj QR
    $pdf->Image(dirname(__FILE__) . '/../tfpdf/download/qrcode-' . $data['random'] . '.png',63,7,40,40);
    // we generate pdf and print it out after checkout

    $pdf->Output( dirname(__FILE__) . '/../tfpdf/download/upn_nalog-' . $data['random'] . '.pdf', 'F');

}


// License functions removed - plugin is now open-source.


/* function upn_check_settings()
{
    // Check if license key has been entered and is valid
    $enteredAndValid = intval(get_option('woo_upn_license_entered_and_valid'));
    $license = (!empty($_POST["woo_upn_license_key"]) ? trim($_POST["woo_upn_license_key"]) : "");

    // If license key has changed or is newly entered and valid, or if a week has passed since the last check
    if (($license !== "" && $enteredAndValid == 0) || (get_option('woo_upn_license_key') !== $license && !empty($license))) {
        // Deactivate old license
        if (get_option('woo_upn_license_key') !== "") {
            // data to send in our API request
            $api_params = array(
                'edd_action' => 'deactivate_license',
                'license' => $license,
                'item_name' => urlencode(EDD_WOO_UPN_ITEM_NAME), // the name of our product in EDD
                'url' => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post(EDD_UPN_STORE_URL, array('timeout' => 15, 'sslverify' => true, 'body' => $api_params));

            // make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('Prišlo je do napake pri deaktivaciji stare licence: ' . get_option('woo_upn_license_key') . '. Prosim, poizkusite ponovno!');
                }

                $base_url = admin_url('admin.php?page=upn-options-licenca');
                $responseMessage["Error1"] = $message;
            }
        }

        // Activate new license
        $api_params = array(
            'edd_action' => 'activate_license',
            'license' => $license,
            'item_name' => urlencode(EDD_WOO_UPN_ITEM_NAME), // the name of our product in EDD
            'url' => home_url()
        );

        // Call the custom API.
        $response = wp_remote_post(EDD_UPN_STORE_URL, array('timeout' => 15, 'sslverify' => true, 'body' => $api_params));

        // make sure the response came back okay
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            if (is_wp_error($response)) {
                $message = $response->get_error_message();
            } else {
                $message = __('Prišlo je do napake pri aktiviranju nove licence: ' . $license . '. Prosim, poizkusite ponovno!');
            }
            $responseMessage["Error2"] = $message;
        } else {
            // decode the license data
            $license_data = json_decode(wp_remote_retrieve_body($response));
            if ($license_data->success === true) {
                update_option('woo_upn_license_entered_and_valid', "1");
                update_option('woo_upn_license_status', $license_data->license);
                update_option('woo_upn_lastcheck', time()); // Set current timestamp as the last check time
            } else {
                update_option('woo_upn_lastcheck', time());
            }
        }
        update_option('woo_upn_license_key', $license);
    } else {
        // Check if a week has passed since the last check
        $lastCheck = intval(get_option('woo_upn_lastcheck'));
        $currentTimestamp = time();
        $weekInSeconds = 7 * 24 * 60 * 60;
        //$weekInSeconds = 2 * 60;
        $isWeekPassed = ($currentTimestamp - $lastCheck) >= $weekInSeconds;

        if ($isWeekPassed) {
            // Get the current license from the database
            $license = get_option('woo_upn_license_key');
            // Data to send in our API request
            $api_params = array(
                'edd_action' => 'activate_license',
                'license' => $license,
                'item_name' => urlencode(EDD_WOO_UPN_ITEM_NAME), // the name of our product in EDD
                'url' => home_url()
            );

            // Call the custom API.
            $response = wp_remote_post(EDD_UPN_STORE_URL, array('timeout' => 15, 'sslverify' => true, 'body' => $api_params));
            $license_data = json_decode(wp_remote_retrieve_body($response));

            // Make sure the response came back okay
            if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
                if (is_wp_error($response)) {
                    $message = $response->get_error_message();
                } else {
                    $message = __('Prišlo je do napake, prosim, poizkusite ponovno!');
                }
                $responseMessage["Error2"] = $message;
            } else {
                $license_data = json_decode(wp_remote_retrieve_body($response));
                if (false === $license_data->success) {
                    update_option('woo_upn_license_status', $license_data->error);
                    switch ($license_data->error) {
                        case 'expired':
                            $message = sprintf(__('Vaša licenca je potekla dne %s.'), date_i18n(get_option('date_format'), strtotime($license_data->expires, current_time('timestamp'))));
                            break;
                        case 'revoked':
                            $message = __('Vaša licenca je bila deaktivirana.');
                            break;
                        case 'missing':
                            $message = __('Vpisane licence ni bilo mogoče najti pod nakupi na Wooninja.si.');
                            break;
                        case 'invalid':
                        case 'site_inactive':
                            $message = __('Vaša licenca ni veljavna za obstoječo spletno stran.');
                            break;
                        case 'item_name_mismatch':
                            $message = sprintf(__('Ta licenčni ključ ni veljaven za modul %s.'), EDD_WOO_UPN_ITEM_NAME);
                            break;
                        case 'no_activations_left':
                            $message = __('Presegli ste limit aktivnih licenc.');
                            break;
                        default:
                            $message = __('Prišlo je do napake, prosim, poizkusite ponovno!');
                            break;
                    }
                }
            }
            if (!empty($message)) {
                $responseMessage["Error3"] = $message;
            }

            // Update the license status
            update_option('woo_upn_license_status', $license_data->license);
            update_option('woo_upn_lastcheck', $currentTimestamp); // Update the last check time
        }
    }

    $messages = array();
    if (!empty($responseMessage)) {
        foreach ($responseMessage as $messageKey => $message) {
            $messages[] = $message;
        }
    }

    return $messages;
}
 */

/*
Funckija za izbris PDF / QR po zaključku naročila
*/


function upn_delete($order_id){

    $order = wc_get_order($order_id);
    $randNum  = $order->get_meta( 'upn_filename' );
    $upay_pdf = $order->get_meta( 'upay_pdf' );
    $upay_qr  = $order->get_meta( 'upay_qr_image_url' );


    preg_match("/upay_nalog-(\d+)\.pdf/", $upay_pdf, $matches_upay_pdf);
    $number_upay_pdf = $matches_upay_pdf[1];

    preg_match("/upay_qr-(\d+)\.png/", $upay_qr, $matches_upay_qr);
    $number_upay_qr = $matches_upay_qr[1];

    //Check order status
 
    if($order->get_status() == 'completed'){
        $delete = get_option('upnBrisanje');

        if($delete == 1){  
            upn_delete_files($randNum);
            upn_delete_files($number_upay_pdf);
            upn_delete_files($number_upay_qr);

        } 
    }

    //Check order status
    if($order->get_status() == 'cancelled'){
        $deleteCancel = get_option('upnBrisanjeCancel');

        if($deleteCancel == 1){
            upn_delete_files($randNum);
            upn_delete_files($upay_pdf);
            upn_delete_files($upay_qr);
        }
    }
}


function upn_delete_files($random){

    $pdf = UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$random}.pdf";
    $upay_pdf = UPN_PLUGIN_URL . "tfpdf/download/upay_nalog-{$random}.pdf";

    $pdf_upn = "tfpdf/download/upn_nalog-{$random}.pdf";
    $pdf_upay = "tfpdf/download/upay_nalog-{$random}.pdf";
    $qr_png = "tfpdf/download/upay_qr-{$random}.png";

    // Generate the URL to the pdf file
    $pdf_url_upn = UPN_PLUGIN_URL . $pdf_upn;
    $pdf_url_upay = UPN_PLUGIN_URL . $pdf_upay;
    $qr_url_upay = UPN_PLUGIN_URL . $qr_png;

    // Convert the URL to a server path
    $pdf_path_upn = str_replace(home_url(), ABSPATH, $pdf_url_upn);
    $pdf_path_upay = str_replace(home_url(), ABSPATH, $pdf_url_upay);
    $qr_path_upay = str_replace(home_url(), ABSPATH, $qr_url_upay);


    if(is_writable($pdf_path_upn)){
        //Delete the file
        $deleted = unlink($pdf_path_upn);
    }
    if(is_writable($pdf_path_upay)) {
        $deleted = unlink($pdf_path_upay);
    }
    //QR
    $qrcode = UPN_PLUGIN_URL . "fpdf/download/qrcode-{$random}.png";
    if(is_writable($qrcode)){
        //Delete the file
        $deleted = unlink($qrcode);
    }
    if(is_writable($qr_path_upay)){
        //Delete the file
        $deleted = unlink($qr_path_upay);
    }
}


add_action('wp_ajax_save_payment_option', 'save_payment_option');
add_action('wp_ajax_nopriv_save_payment_option', 'save_payment_option');

function save_payment_option() {
    // Check nonce for security
    check_ajax_referer('upn_ajax_nonce', 'security');

    // Make sure there's a payment_option parameter
    if(isset($_POST['payment_option'])) {
        // Update the option in the database
        update_option('payment_option', sanitize_text_field($_POST['payment_option']));
        echo 'Option updated successfully.';
    } else {
        echo 'Failed to update option.';
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}

add_action('wp_ajax_upay_connection', 'upay_connection');
add_action('wp_ajax_nopriv_upay_connection', 'upay_connection');

function upay_connection() {
    // Check nonce for security
    check_ajax_referer('upn_ajax_nonce', 'security');

    // Make sure there's a payment_option parameter
    if(isset($_POST['uPayType'])) {
        // Update the option in the database
        update_option('uPayType', sanitize_text_field($_POST['uPayType']));
        echo 'Option updated successfully.';
    } else {
        echo 'Failed to update option.';
    }
    wp_die(); // this is required to terminate immediately and return a proper response
}



function upn_check_for_update() {

    $plugin_path = UPN_PLUGIN_DIR . '/woo-upnnalog.php';;

    // Get plugin data
    $plugin_data = get_plugin_data($plugin_path);

    // Extract the version
    $plugin_version = $plugin_data['Version'];

    $latest_version = UPN_VERSION;
    $current_version = $plugin_version;


    if (version_compare($latest_version, $current_version, '>') && version_compare($current_version, '2.3', '<')) {
        add_action( 'admin_notices', 'upn_update_notice' );
    }
    if (version_compare($latest_version, $current_version, '>') && version_compare($current_version, '2.3', '<')) {
        add_action( 'admin_notices', 'upn_update_notice_new' );
    }
  }
  add_action( 'admin_init', 'upn_check_for_update' );
  
  function upn_update_notice() {

    ?>
    <div class="notice notice-warning is-dismissible">
        <p><?php _e( 'Na voljo je nova posodobitev vtičnika za <b>' . EDD_WOO_UPN_ITEM_NAME. '</b>. Vtičnik omogoča plačevanje preko <b>uPay UPN obrazca</b>', 'upn' ); ?></p>
    </div>
    <?php
  }
  
  function upn_update_notice_new() {

    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Vtičniku <b>' . EDD_WOO_UPN_ITEM_NAME. '</b> smo dodali novo funkcionalnost uPay - možnost pospeševanja in takojšnega potrjevanja plačil preko UPN nalogov', 'upn' ); ?></p>
        <p><?php _e('Več informacij o uPay si lahko pogledate ' . '<a href="https://upay.si/wooninja" target="_BLANK">tukaj.</a>','upn');?></p>
    </div>
    <?php
  }
  
