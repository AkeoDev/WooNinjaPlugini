<?php
/*
Plugin Name: WooNinja - UPN Nalog
Plugin URI: https://wooninja.si
Description: Olajšate delo vašim kupcem in jim omogočite še hitrejše plačilo s samodejno pripravljenim UPN obrazcem. Na plačilnem nalogu so zapisani vsi potrebni podatki za uspešno plačilo.
Version: 4.0.0
Author: Humanfrog d.o.o.
License: GPLv2 or later
Text Domain: wooninja-upnnalog
*/


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
require_once( dirname(__FILE__) . '/wooninja/functions.php' );
require_once( dirname(__FILE__) . '/wooninja/WooUPNQR.php' );
require_once( dirname(__FILE__) . '/inc/upay_api.php' );
require_once( dirname(__FILE__) . '/tfpdf/tfpdf.php' );
require_once( dirname(__FILE__) . '/wooninja/upay_sendEmail.php' );


define( 'UPN_VERSION', '4.0.0' );
define( 'UPN_PLUGIN', __FILE__ );
define( 'UPN_PLUGIN_BASENAME', plugin_basename( UPN_PLUGIN ) );
define( 'UPN_PLUGIN_NAME', trim( dirname( UPN_PLUGIN_BASENAME ), '/' ) );
define( 'UPN_PLUGIN_DIR', untrailingslashit( dirname( UPN_PLUGIN ) ) );
define( 'UPN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );


// License checking removed - plugin is now open-source.



function enqueue_scripts_unp() {

    wp_register_script('my_plugin_upn', plugins_url('/admin/upn.js' , __FILE__ ), array('jquery'));
    wp_register_style( 'my-plugin-upn-style', plugins_url('admin/upn.css', __FILE__), array(), '1.0.0' );
    wp_enqueue_script('my_plugin_upn');
    wp_enqueue_style('my-plugin-upn-style');
  }

  add_action( 'wp_enqueue_scripts', 'enqueue_scripts_unp' );

  function enqueue_scripts_unp_admin() {

    wp_register_script('admin_plugin_upn', plugins_url('/admin/upn-admin.js' , __FILE__ ), array('jquery'));
    wp_register_style( 'admin-plugin-upn-style', plugins_url('admin/upn.css', __FILE__), array(), '1.0.0' );
    wp_enqueue_script('admin_plugin_upn');
    wp_enqueue_style('admin-plugin-upn-style');
    wp_localize_script('admin_plugin_upn', 'ajax_call', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('upn_ajax_nonce')
    ));
  }

  
  add_action( 'admin_enqueue_scripts', 'enqueue_scripts_unp_admin' ); 





if ( (!is_plugin_active( 'woocommerce/woocommerce.php')) OR (!is_plugin_active_for_network( 'woocommerce/woocommerce.php') )) {

	/**
	 * Administration menu
	 */
	add_action( 'admin_menu', 'upn_administration', 9 );

	function upn_administration() {
		add_menu_page( __( 
			'UPN', 'upn-sm' ),
			__( 'UPN', 'upn-sm' ),
			'manage_options', 'upn-module',
			'upn_settings', 'dashicons-feedback' );

		$settings = add_submenu_page( 
			'upn-module',
			__( 'Nastavitve', 'upn-sm' ),
			__( 'UPN', 'upn-sm' ),
			'manage_options', 'upn-module',
			'upn_settings' );

	}

    // Set UI labels for Custom Post Type

    function register_bancni_racun_post_type() {
        $args = array(
            'public'              => true,
            'label'               => __('Bančni računi', 'textdomain'),
            'menu_icon'           => 'dashicons-bank',
            'supports'            => array('title', 'custom-fields'),
            'show_in_menu'        => 'upn-module',
            'labels' => array(
                'view_item' => 'Prikaži UPN trr',
                'add_new_item' => 'Dodaj nov bačni račun',
                'add_new' => 'Dodaj nov bančni račun',
                'update_item' => 'Posodobi bančni račun',
            )
        );
        register_post_type('bancni_racun', $args);
    }
    
    add_action('init', 'register_bancni_racun_post_type');

    function upn_add_meta_boxes() {
        add_meta_box(
            'upn_bancni_racun_meta_box',       // Unique ID
            'Podatki o podjetju',              // Box title
            'upn_bancni_racun_meta_box_html',  // Content callback, must be of type callable
            'bancni_racun'                     // Post type
        );
    }
    add_action('add_meta_boxes', 'upn_add_meta_boxes');
    
    function upn_bancni_racun_meta_box_html($post) {
        $values = get_post_custom($post->ID);
        $imePodjetja = isset($values['upnImePodjetja']) ? esc_attr($values['upnImePodjetja'][0]) : '';
        $naslovPodjetja = isset($values['upnNaslovPodjetja']) ? esc_attr($values['upnNaslovPodjetja'][0]) : '';
        $upnPostaKraj = isset($values['upnPostaKraj']) ? esc_attr($values['upnPostaKraj'][0]) : '';
        $upnBic = isset($values['upnBic']) ? esc_attr($values['upnBic'][0]) : '';
        $upnTRR = isset($values['upnTRR']) ? esc_attr($values['upnTRR'][0]) : '';

        wp_nonce_field( 'upn_save_bancni_racun', 'upn_bancni_racun_nonce' );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="upnImePodjetja">Ime podjetja</label></th>
                <td><input style="width: 50%;" type="text" name="upnImePodjetja" value="<?php echo $imePodjetja; ?>" /></td>
            </tr>
            <tr>
                <th><label for="upnNaslovPodjetja">Naslov podjetja</label></th>
                <td><input style="width: 50%;" type="text" name="upnNaslovPodjetja" value="<?php echo $naslovPodjetja; ?>" /></td>
            </tr>
            <tr>
                <th><label for="upnPostaKraj">Pošta kraj</label></th>
                <td><input style="width: 50%;" type="text" name="upnPostaKraj" value="<?php echo $upnPostaKraj; ?>" /></td>
            </tr>
            <tr>
                <th><label for="upnBic">BIC Banke</label></th>
                <td><input style="width: 50%;" type="text" name="upnBic" value="<?php echo $upnBic; ?>" /></td>
            </tr>
            <tr>
                <th><label for="upnTRR">TRR</label></th>
                <td><input style="width: 50%;" type="text" name="upnTRR" value="<?php echo $upnTRR; ?>" /></td>
            </tr>
    
        </table>
        <?php
    }
    function upn_save_bancni_racun_meta_box($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if ( ! isset( $_POST['upn_bancni_racun_nonce'] ) || ! wp_verify_nonce( $_POST['upn_bancni_racun_nonce'], 'upn_save_bancni_racun' ) ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Save each field
        if (isset($_POST['upnImePodjetja'])) {
            update_post_meta($post_id, 'upnImePodjetja', sanitize_text_field($_POST['upnImePodjetja']));
        }
        if (isset($_POST['upnNaslovPodjetja'])) {
            update_post_meta($post_id, 'upnNaslovPodjetja', sanitize_text_field($_POST['upnNaslovPodjetja']));
        }
        if (isset($_POST['upnPostaKraj'])) {
            update_post_meta($post_id, 'upnPostaKraj', sanitize_text_field($_POST['upnPostaKraj']));
        }
        if (isset($_POST['upnBic'])) {
            update_post_meta($post_id, 'upnBic', sanitize_text_field($_POST['upnBic']));
        }
        if (isset($_POST['upnTRR'])) {
            update_post_meta($post_id, 'upnTRR', sanitize_text_field($_POST['upnTRR']));
        }

    }
    add_action('save_post', 'upn_save_bancni_racun_meta_box');

    // UPN METABOX IN SINGLE PRODUCT
    add_action( 'add_meta_boxes', 'upn_trr_single_product' );
 
    function upn_trr_single_product() {    
        add_meta_box( 'choose_upn_trr', 'UPN - izberite željeni TRR', 'select_upn', 'product', 'advanced', 'high' );
    }
 
    function select_upn($post) {
        
        $selected_upn_trr = get_post_meta($post->ID, 'select_upn_trr', true);

        $upn_trr = array(
            'post_type' => 'bancni_racun',
            'post_status' => 'publish',
            'post_per_page' => 1,
        );

        $select_upn = new Wp_Query($upn_trr);
        $settings_link = '<a href="options-general.php?page=upn-module">' . __( 'Nastavitve' ) . '</a>';
        if ($select_upn->have_posts()) {
            echo '<i>*Opomba: V primeru, da je polje prazno, bodo prikazani privzeti podatki iz <a href="options-general.php?page=upn-module">' . __( 'nastavitev.' ) . '</a> </i><br>';
            echo '<i">Izbira trr-ja, se upošteva le kadar je v košarici 1 izdelek.</i>';
            echo '<select name="select_upn_trr" for="select_upn_trr" style="width:100%; margin-top: 20px;">';
            echo '<option value="">/</option>';
            while ($select_upn->have_posts()) {
                $select_upn->the_post();
                $selected = (get_the_ID() == $selected_upn_trr) ? 'selected' : '';

                echo '<option value="'.get_the_ID().'" '.$selected.'>'.get_the_title().'</option>';
            }
            echo '</select>';
            wp_reset_postdata();
        }

    wp_nonce_field('save_upn_trr_meta_box_value', 'select_upn_trr_nonce');
}


add_action('save_post_product', 'save_upn_trr_meta_box_value');

function save_upn_trr_meta_box_value($post_id) {
   
    if (!isset($_POST['select_upn_trr_nonce']) || !wp_verify_nonce($_POST['select_upn_trr_nonce'], 'save_upn_trr_meta_box_value')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Update the meta field in the database.
    if (isset($_POST['select_upn_trr'])) {
        update_post_meta($post_id, 'select_upn_trr', sanitize_text_field($_POST['select_upn_trr']));
    }

}



	function upn_settings() {
		include("admin/settings.php");
	}


    if (get_option('payment_option') == 'upay') {

        // Call uPay_frontend when a new order is created
        add_action('woocommerce_new_order', 'uPay_frontend', 10, 1);

        add_action( 'woocommerce_thankyou_bacs', 'uPay_frontend');
    
        function uPay_frontend($order_id) {
        $order = wc_get_order( $order_id );
        $order->get_meta( 'upay_qr_image_url' );
        $pm = $order->get_payment_method();
        
        if ( $pm != "bacs" ) {
            return;
        }
    
        $number = $order->get_order_number();
        $address = $order->get_address();
        $price = $order->get_total();
        $billing_first_name = $order->get_billing_first_name();
        $billing_last_name  = $order->get_billing_last_name();
        $full_name = $billing_first_name . ' ' . $billing_last_name;

  
        if (get_option('uPayType') == 'uPayTest') {
            $client_id =  get_option('upay_client_id');
            $client_secret = get_option('upay_client_secret');
            $base_url = 'https://test-api.upay.si/UPN';
        }
        if (get_option('uPayType') == 'uPayLive') {
            $client_id =  get_option('upay_client_id_live');
            $client_secret = get_option('upay_client_secret_live');
            $base_url = 'https://api.upay.si/UPN';
        }


        $upayApi = new UpayApi($client_id, $client_secret, $base_url);

        $nullableDateTime = new DateTime();
        $nullableDateTime->format('Y-m-d');

        $generateRequest = [
            //'SellPoint' => (get_option('uPayType') == 'uPayTest') ? get_option('upay_sellpoint_id') : get_option('upay_sellpoint_id_live'),
            'SellPoint' => get_option('upay_sellpoint_id'),
            'PaymentAmount' => $price,
            'PaymentDeadline' => $nullableDateTime->format('Y-m-d'),
            'PaymentPurposeCode' => 'WEBI',
            'PaymentPurpose' => 'Plačilo predračuna ' . '#'.$order->get_order_number(), 
            'RecipientReferenceCode' => 'SI00',
            'RecipientReference' => $order->get_order_number(),
            ];
        
            // Check if the order has already a UPN reference.
        
            $has_UPN_reference = $order->get_meta('has_upn_reference');
            
            if (!$has_UPN_reference) {
                // No UPN reference was found, create a new one
                $generateRequest = [
                    'SellPoint' => get_option('upay_sellpoint_id'),
                    'PaymentAmount' => $price,
                    'PaymentDeadline' => $nullableDateTime->format('Y-m-d'),
                    'PaymentPurposeCode' => 'WEBI',
                    'PaymentPurpose' => 'Plačilo predračuna ' . '#'.$order->get_order_number(), 
                    'RecipientReferenceCode' => 'SI00',
                    'RecipientReference' => $order->get_order_number(),
                ];
                
                $UPNreference = $upayApi->createNewUPNReference($generateRequest);
                $guid_id = $UPNreference->data;
                
                $upayReference_full = $UPNreference->upayReference;
            
                // Store the UPN reference in the order meta
                $order->update_meta_data('upay_referenca', $upayReference_full);

                // Mark that this order has a UPN reference
                $order->update_meta_data('has_upn_reference', true);
                $order->save();

            } else {
                    // The order already has a UPN reference, retrieve it from the post meta
                    $upayReference_full = $order->get_meta('upay_referenca');
                }
            
                $has_pdf = $order->get_meta('has_pdf');
                // Remove first part of the string (UPY)
                $upayReference = substr($upayReference_full,3);
                            
                $getQRBaseByReference = $upayApi->getQRBaseByReference($upayReference);
                
                $display_upn_base64 = $getQRBaseByReference->data;
                if (!$has_pdf) {
                    $image_data = base64_decode($display_upn_base64);
                    $image_file = tempnam(sys_get_temp_dir(), 'tfpdf') . '.png';
                    file_put_contents($image_file, $image_data);
                
                    $pdf = new tFPDF();
                    $pdf->AddPage();
                    $pdf->Image($image_file, 10, 10, 190); // 190 is the width of the image. 
                
                    $randNum = rand();
                    $pdf_file = plugin_dir_path(__FILE__) . "tfpdf/download/upay_nalog-{$randNum}.pdf";
                
                    // Output the PDF to a file on the server
                    $pdf->Output($pdf_file, 'F');
                
                    // Delete the temporary image file
                    unlink($image_file);
                
                    // Generate the URL for the PDF
                    $pdf_url = UPN_PLUGIN_URL . "tfpdf/download/upay_nalog-{$randNum}.pdf";
                
                    $order->update_meta_data('upay_pdf', $pdf_url);

                    // Mark that this order has a PDF
                    $order->update_meta_data('has_pdf', true);
                    $order->save();
                }
                
                $has_image = $order->get_meta('has_image');
                
                $getQRCodeByReference = $upayApi->getQRCodeByReference($upayReference);
                $qr_upay_base64 = $getQRCodeByReference->data;

                if (!$has_image) {
                    // Get QR code
                  
                    $image_data_qr = base64_decode($qr_upay_base64->fileContents);
                
                    // Create a temporary PNG file
                    $temp_image_file = tempnam(sys_get_temp_dir(), 'tfpdf') . '.png';
                    file_put_contents($temp_image_file, $image_data_qr);
                
                    $randNum = rand();
                    $final_image_file = plugin_dir_path(__FILE__) . "tfpdf/download/upay_qr-{$randNum}.png";
                    
                    // Move the file from the temp location to the final location
                    if (!rename($temp_image_file, $final_image_file)) {
                        echo "Failed to move the file.";
                    } 
                
                    // Generate the URL for the PNG
                    $image_url = UPN_PLUGIN_URL . "tfpdf/download/upay_qr-{$randNum}.png";
                    $order->update_meta_data('upay_qr_image_url', $image_url);

                    // Mark that this order has an image
                    $order->update_meta_data('has_image', true);
                    $order->save();
                }
                
                $upayReference = $order->get_meta('upay_referenca');

                echo "<a class='ikona_wrapper' href='" . $order->get_meta('upay_pdf') . "' target='_blank' download>" . __('Prenesi UPN nalog (pdf)', 'woo-upnnalog') . "<div id='upn-ikona'><span class='puscica_zgoraj'></span><span class='puscica_spodaj'></span><span class='base'></span></div></a><br>";

                $display_QR_upay = $order->get_meta('upay_qr_image_url');
                echo '<img src="'.$display_QR_upay.'">';
                ?>
                
                <h4>Plačilo z UPN: <b><?php echo $upayReference_full;?></b></h4>
                <img src="data:image/png;base64, <?php echo $display_upn_base64;?>" alt="Upay_UPN" />
                
                <?php
        }

       
    }
   


    // Display UPN in email
    if (get_option('email-upn-position')) {
        switch (get_option('email-upn-position')) {
            case 'wc_email_before_order_table_upn':
                add_action('woocommerce_email_before_order_table','upn_frontened_email');
                break;
            case 'wc_email_after_order_table_upn':
                add_action('woocommerce_email_after_order_table','upn_frontened_email');
                break;
            
        }
    }
    if (get_option('payment_option') == 'default_upn') {
    add_action( 'woocommerce_thankyou_bacs', 'upn_frontened', 1, 1 );
    }
    function upn_frontened_email($order) {
     
        $order_id = $order->get_id();
        
        $pm = $order->get_payment_method();

        $orderStatus = $order->get_status();
        if ( $pm != 'bacs' ) {
            return;
        }

        $hasPostMeta = $order->get_meta('upn_filename');
        
        if (!empty($hasPostMeta)) {
            $randNum = $hasPostMeta;
        } else {
            $randNum = uniqid();
            $order->add_meta_data('upn_filename', $randNum, true);
            $order->save();
        }
 
        if ( $orderStatus == "on-hold" ) {
    
          ?> 
          <br>
          <h4>Plačilo z UPN:</h4>
          <?php 
              // Get the QR code image URL from order meta
                if (get_option('payment_option') == 'default_upn') {
            
                    echo "<a class ='ikona_wrapper' href='" . UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$randNum}.pdf' target='_blank' download>" . __('Prenesi UPN nalog (pdf)', 'woo-upnnalog') . "<div id='upn-ikona'><span class='puscica_zgoraj'></span><span class='puscica_spodaj'></span><span class='base'></span></div></a><br>";

                } else {
                    
                    $display_QR_upay = $order->get_meta('upay_qr_image_url');
                    echo '<img src="'.$display_QR_upay.'">';

                } ?>
            <?php  
            }
    }
    

    if (get_option('payment_option') == 'default_upn') {
        add_action( 'woocommerce_thankyou_bacs', 'upn_frontened', 1, 1 );

 	function upn_frontened( $order_id ) {
    

  	$order = wc_get_order( $order_id );
  	$pm = $order->get_payment_method();
      if ( $pm != 'bacs' ) {
  		return;
  	}
  	$number = $order->get_order_number();
  	$address = $order->get_address();

  	$price = $order->get_total();   ?>
	<br>
		<h4><?php echo __('Plačajte preko UPN','woo-upnnalog');?></h4>
	<?php
    //Generiraj PDF
    if ($order->get_billing_company()) {
        $billing_name = $order->get_billing_company();
    } else {
        $billing_name = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
    }

    // GET NEW FORM 
    $selected_form_id = '';
    foreach ($order->get_items() as $item_id => $item) {
        $product_id = $item->get_product_id();
        if ($product_meta = get_post_meta($product_id, 'select_upn_trr', true)) {
            $selected_form_id = $product_meta;
            break; 
        }
    }
    
    if ($selected_form_id) {
        $upnImePodjetja = get_post_meta($selected_form_id, 'upnImePodjetja',true);
        $upnNaslovPodjetja = get_post_meta($selected_form_id, 'upnNaslovPodjetja',true);
        $upnPostaKraj = get_post_meta($selected_form_id, 'upnPostaKraj',true);
        $upnTRR = get_post_meta($selected_form_id, 'upnTRR',true);

    } else {
        $upnImePodjetja = get_option("upnImePodjetja");
        $upnNaslovPodjetja = get_option("upnNaslovPodjetja");
        $upnPostaKraj = get_option("upnPostaKraj");   
        $upnTRR = get_option("upnTRR");
    }

    
    $data = array(
      "tag"       =>  "UPNQR",
      "placnik_upn"   =>  "",
      "polog"       =>  "",
      "dvig"        =>  "",
      "placnik_sklic"   =>  "",
      "placnik_ime"   =>  $billing_name,
      "placnik_naslov"  =>  $order->get_billing_address_1(),
      "placnik_kraj"    =>  $order->get_billing_postcode() . " " . $order->get_billing_city(),
      "znesek"      =>  $order->get_total(),
      "datum_placila"   =>  "",
      "nujno"       =>  "",
      "koda"        =>  "WEBI",
      "namen"       =>  'Plačilo predračuna ' . $order->get_order_number(),
      "rok_placila"   =>  "",
      "trr"       =>  $upnTRR,
      "sklic"       =>  "SI00" . str_pad($order->get_order_number(), 11, '0', STR_PAD_LEFT),
      "podjetje_ime"    =>  $upnImePodjetja,
      "podjetje_naslov" =>  $upnNaslovPodjetja,
      "podjetje_kraj"   =>  $upnPostaKraj,
      "random"          =>  $order->get_meta('upn_filename'),
    );

    $qrcode = new \WooNinja\WooUPNQR();
    $qrcode->getImage($data);

    upn_generate_pdf($data);

    $randNum = $order->get_meta('upn_filename');
    // Get the absolute path to the plugin directory
    $plugin_dir_path = plugin_dir_path(__FILE__);
    //PDF
    $pdf_relative_path = "tfpdf/download/upn_nalog-{$randNum}.pdf";
    $pdf = $plugin_dir_path . $pdf_relative_path;

            $pdf_link = "<br><a class ='ikona_wrapper' href='" . UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$randNum}.pdf' target='_blank' download>" . __('Prenesi UPN nalog (pdf)', 'woo-upnnalog') . "<div id='upn-ikona'><span class='puscica_zgoraj'></span><span class='puscica_spodaj'></span><span class='base'></span></div></a>";

            $pdf_link .= "<br><br><a class ='upn_popup'>". __('Odpri UPN nalog v pojavnem oknu','woo-upnnalog')."<div id='upn-ikona'></div></a>";
            $pdf_link .= "<br><div class='upn_object'><object data='" . UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$randNum}.pdf' type='application/pdf' width='100%' height='444px'>
                                <iframe src='" . UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$randNum}.pdf' width='100%'' height='444px' style='border: none;'>
                                This browser does not support PDFs. Please download the PDF to view it: <a href='" . UPN_PLUGIN_URL . "tfpdf/download/upn_nalog-{$randNum}.pdf' download>". __('Prenesi UPN nalog (pdf)','woo-upnnalog')."</a>
                                </iframe>
                            </object></div><br>";
            $pdf_link .= "<img src='" . UPN_PLUGIN_URL . "tfpdf/download/qrcode-{$randNum}.png' /><br><br>";
          echo $pdf_link;
	} 




    }

add_action( 'woocommerce_order_status_completed', 'upn_delete', 10,1 );
add_action( 'woocommerce_cancelled_order', 'upn_delete', 10,1 );


  function save_my_radio_value() {
    check_ajax_referer('my-radio-security', 'security');

    $radio_value = isset($_POST['radio_value']) ? sanitize_text_field($_POST['radio_value']) : '';

    if (empty($radio_value)) {
        wp_send_json_error();
    }

    update_option('my_radio_option', $radio_value);
    wp_send_json_success();
}
add_action('wp_ajax_save_my_radio_value', 'save_my_radio_value');




// Metabox fiield for uPay
add_action( 'add_meta_boxes', 'upn_meta_box' );

function upn_meta_box() {
    add_meta_box(
        'uPayId',
        'uPay podatki naročila',
        'uPayContent',
        'shop_order'
    );
}
function uPayContent($order_id ) {
    // Retrieving an existing value from the database.
    $order = wc_get_order( $order_id );
    $upayReference = $order ? $order->get_meta('upay_referenca') : '';
    ?>
    <p>Referenca naročila: <b>#<?php echo $upayReference;?></b></p>
    <?php
}

add_action( 'save_post', 'save_upn_box_data' );
function save_upn_box_data( $post_id ) {
    // Check if this is an autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check if our nonce is set.
    if ( ! isset( $_POST['my-meta-box-field'] ) ) {
        return;
    }

    // Check user capabilities
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['my-meta-box-field'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, '_my_meta_value_key', $my_data );
}

if (get_option('changeStatus') == '1') {
add_filter( 'cron_schedules', 'my_custom_cron_add_every_hour' );
function my_custom_cron_add_every_hour( $schedules ) {
    $schedules['every_hour'] = array(
        'interval' => 3600, // Number of seconds, 3600 seconds in an hour.
        'display'  => __( 'Every Hour' ),
    );
    return $schedules;
}

if( !wp_next_scheduled( 'my_custom_cron_hour_event' ) ) {
    wp_schedule_event( current_time( 'timestamp' ), 'every_hour', 'my_custom_cron_hour_event' );
}


add_action( 'my_custom_cron_minute_event', 'checkStatus' );
add_action( 'woocommerce_thankyou_bacs', 'checkStatus');

function checkStatus() {

    if (get_option('uPayType') == 'uPayTest') {
        $client_id =  get_option('upay_client_id');
        $client_secret = get_option('upay_client_secret');
        $base_url = 'https://test-api.upay.si/UPN';
    }
    if (get_option('uPayType') == 'uPayLive') {
        $client_id =  get_option('upay_client_id_live');
        $client_secret = get_option('upay_client_secret_live');
        $base_url = 'https://api.upay.si/UPN';
    } 
 

    $upayApi = new UpayApi($client_id, $client_secret, $base_url);

    $args = array(
        'limit' => -1,
        'status' => 'on-hold'
    );

    $orders = wc_get_orders( $args );

    foreach ($orders as $order) {

        $upayReference = $order->get_meta('upay_referenca');
        $upayReference = substr($upayReference,3);
        $getAllData = $upayApi->getAllDataByReference($upayReference);

        if ($getAllData && isset($getAllData->data->uPayStatus->code)) {
            $uPayStatusCode = $getAllData->data->uPayStatus->code;
            // uPay status code for "Closed" -- 4
            $paid = $getAllData->data->uPayStatus->description;
       
                if (($uPayStatusCode == 4) || ($paid == 'Paid')) {
                    $order->update_status('wc-processing');

                    if (get_option('sendEmailToUpn') == '1') {
                        sendEmailStatusUpdate($order->get_id());
                    }
              }
            }
        }
        

    }
}


}


?>