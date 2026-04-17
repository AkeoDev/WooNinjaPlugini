<?php
/**
* Plugin Name: WooNinja - Minimax
* Plugin URI: https://wooninja.si
* Description: MiniMax modul omogoča izdajanja in pošiljanje računov.
* Version: 3.0.0
* Author: Humanfrog d.o.o.
* Author URI: https://wooninja.si
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wooninja-minimax
**/


function plugin_add_settings_link( $links ) {
    $settings_link = '<a href="options-general.php?page=minimax-options">' . __( 'Nastavitve' ) . '</a>';
    array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );

function my_custom_plugin_admin_enqueue_scripts() {
    wp_enqueue_script( 'tiny_mce' );
    wp_enqueue_script( 'quicktags' );
    wp_enqueue_script( 'wp-tinymce' );
    wp_enqueue_style( 'wp-admin' );
  }
  
  add_action( 'admin_enqueue_scripts', 'my_custom_plugin_admin_enqueue_scripts' );
  

  
function email_template_color() {
    
    // Email border template
    add_settings_field( 'email_color_border', __( 'Color 1', 'email_color' ), 'email_color_field_callback_1', 'email_color', 'email_color_section' );

    // Email color button link
    add_settings_field( 'email_cololor_button_link', __( 'Color 2', 'email_color' ), 'email_color_field_callback_2', 'email_color', 'email_color_section' );


    // Background color for email
    add_settings_field( 'email_color_bg', __( 'Color 3', 'email_color' ), 'email_color_field_callback_3', 'email_color', 'email_color_section' );

    // Email color
    add_settings_field( 'email_color_typography', __( 'Color 4', 'email_color' ), 'email_color_field_callback_3', 'email_color', 'email_color_section' );
}
add_action( 'admin_init', 'email_template_color' );



if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

include_once plugin_dir_path( __FILE__ ) . 'api/api.php';
include_once plugin_dir_path( __FILE__ ) . 'order-table-filter.php';

require_once(ABSPATH . "wp-admin" . '/includes/image.php');
require_once(ABSPATH . "wp-admin" . '/includes/file.php');
require_once(ABSPATH . "wp-admin" . '/includes/media.php');

/**
 * Small plugin static settings
 */
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


global $current_user;

function minimax_settings_dokumentacija() {
    include("admin/documentation.php");
}

$settings = [
    'currency_code' => 7,
    'country_code' => 192
];

if (is_admin()) {
    add_action('wp_ajax_send_customer', 'send_customer');
    add_action('wp_ajax_minimax_create_invoice', 'minimax_create_invoice');
    add_action('wp_ajax_send_pdf_automatic', 'send_pdf_automatic');
    add_action('wp_ajax_update_items', 'update_items');
    add_action('wp_ajax_update_items_all', 'update_items_all');
    add_action('wp_ajax_sync_stock_from_minimax', 'sync_stock_from_minimax');
    add_action('wp_ajax_login', 'login');
}

function minimax_options_panel() {

    add_menu_page('MiniMax nastavitve', 'MiniMax', 'manage_options', 'minimax-options', 'minimax_settings', 'https://wooninja.si/wp-content/uploads/2021/10/WooNina_icon_16.png');

    add_submenu_page('minimax-options', 'Dokumentacija', 'Dokumentacija', 'manage_options', 'minimax-options-dokumentacija', 'minimax_settings_dokumentacija');
}

add_action('admin_menu', 'minimax_options_panel');
function minimax_settings() {
    include("admin/settings.php");
}
add_action('admin_enqueue_scripts', 'smMiniMax_scripts');

function smMiniMax_scripts($hook) {

    wp_enqueue_script('sweetalert2woominimax', plugins_url('/js/sweetalert2.all.min.js', __FILE__), '');
    wp_enqueue_script('sw2hackwoominimax', plugins_url('/js/sw2hack.js', __FILE__), '');

}


$dir = plugin_dir_path( __FILE__ );

// METABOX
$high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
if ($high_performance_order_storage == 'yes') {
    add_action('admin_init', 'minimax_check_order_edit_page');

    function minimax_check_order_edit_page() {
        global $pagenow;
        global $order_post_id;
        if ($pagenow == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'wc-orders') {
            if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {

                $order_post_id = intval($_GET['id']);
                add_action('admin_notices', 'minimax_meta_box');
            }
        }
    }

    function minimax_meta_box() {
        add_meta_box('MiniMax-box', __('Minimax', 'minimax-sm'), 'MiniMax_box_content', null, 'side', 'high');
    }
} else {

    add_action('admin_init', 'minimax_load_admin_hooks');

    function minimax_load_admin_hooks() {
        add_action('add_meta_boxes_shop_order', 'minimax_meta_box');
    }

    function minimax_meta_box() {
        global $current_user;
        add_meta_box('MiniMax-box', __('Minimax', 'minimax-sm'), 'MiniMax_box_content', 'shop_order', 'side', 'high');
    }
}

function sm_MiniMax_refresh_meta_box_ajax() {
    MiniMax_return_box_content($_POST['postid']);

    wp_die();
}

global $order_id; global $pdf;


function get_order_from_multisite($order_post_id) {
    global $current_site_id;
    // Switch to the site where the order was created
    switch_to_blog($current_site_id);
    // Retrieve the order
    $order = wc_get_order($order_post_id);
    // Restore to the original site
    switch_to_blog($current_site_id);
    restore_current_blog();
    return $order;
}


 function get_order_data($order_post_id) {
    global $current_user;
  
  

    $high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
    $is_multisite = is_multisite();

    if ($is_multisite) {
        $high_performance_order_storage = get_site_option('woocommerce_custom_orders_table_enabled', $high_performance_order_storage);
    }

    if (is_admin() && !wp_doing_ajax()) {
        // **Admin Panel Logic (Manual Trigger)**
        if ($high_performance_order_storage == 'yes') {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $order_post_id = intval($_GET['id']);
            }
        } else {
            $order_post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        }
    } else {
        // **Thank You Page Logic (WooCommerce hook)**
        if (!is_numeric($order_post_id) || $order_post_id <= 0) {
            error_log("Error: Invalid order ID received on Thank You page.");
            return;
        }
    }



    if ($is_multisite) {
        $order = get_order_from_multisite($order_post_id);
    } else {
        $order = wc_get_order($order_post_id);
    }

    if (!$order_post_id) {
      
        return null;
    }


    if (!$order) {
    
        return null;
    }


    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    );

    $countryCode = $order->get_billing_country();
    $getCountryByCode = $api->getCountryByCode($countryCode);
    $getCountryId = $getCountryByCode->CountryId;

    $GetCurrencies = $api->GetCurrencies($order->get_currency());

    $addressBilling = $order->get_address("billing");



    $order_data = array(
        'billing_address' => $addressBilling['address_1'],
        'billing_company' => $addressBilling['company'],
        'billing_first_name' => $addressBilling['first_name'],
        'billing_last_name' => $addressBilling['last_name'],
        'billing_country' => $countryCode,
        'billing_post_code' => $addressBilling['postcode'],
        'billing_city' => $addressBilling['city'],
        'billing_email' => $addressBilling['email'],
        'AddresseeName' => $addressBilling['first_name'] . ' ' . $addressBilling['last_name'],
        'AddresseeAddress' => $addressBilling['address_1'] . ' ' . $addressBilling['address_2'],
        'order_status' => $order->get_status(),
        'order_payment_method' => $order->get_payment_method(),
        'AddresseeCountry' => $getCountryId,
        'Currency' => $GetCurrencies->CurrencyId,
        'discount_total' => $order->get_total_discount(),
    );



    return $order_data;
} 




function MiniMax_return_box_content($order_post_id) {
    global $pdf; global $woocommerce; global $post;

    $high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
    $is_multisite = is_multisite();

    if ($is_multisite) {
        $high_performance_order_storage = get_site_option('woocommerce_custom_orders_table_enabled', $high_performance_order_storage);
    }
    global $order_post_id;
    if ($high_performance_order_storage == 'yes') {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $order_post_id = intval($_GET['id']);
        }
    } else {
        $order_post_id = $post->ID;
    }
	
    $order_obj = wc_get_order( $order_post_id );
    $result = $order_obj ? $order_obj->get_meta('minimax_delivery_order_id') : '';

    $status_invoice = 'enabled';
    $status = 'disabled';

    $pdf_url = '';
    if (!empty($result)) {
        $status_invoice = 'enabled';
        $status = 'enabled';
        $pdf_url = $order_obj->get_meta('_minimax_pdf');
        $upload_dir = plugin_dir_path( __FILE__ );
        global $save; global $pdf;
    }

    $log_invoice_data = $order_obj ? $order_obj->get_meta('log_invoice_data') : '';

    $status_btn = "disabled";
    if ($log_invoice_data) {
        $status_btn = 'enabled';
    } 
    ?>

    <form action="" method="POST" >
        <?php 
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $order_post_id = intval($_GET['id']);
            }
        ?>
        <p>Izdaj račun</p>
        <input type="hidden" name="minimax-orderId"  id="minimax-orderId" value="<?= $order_post_id;?>">
        <button id="create_invoice" type="submit" name="minimax-action" value="racun" class="button button-primary" <?= $status_invoice; ?>>Račun</button>
        <button id="update_customer" type="submit" name="minimax-update-customer" value="racun-update" class="button" <?= $status; ?>>Posodobi podatke</button>
        <p>PDF</p>
        <a href="<?= $pdf_url;?>" name="minimax-action-pdf" value="pdf" class="button" target="_blank" <?= $status; ?>>Odpri PDF</a>
        <button type="submit" name="minimax-action-pdf-send" value="pdf-send" class="button button-primary" <?= $status; ?>>Pošlji PDF</button>

        <hr>
        <p>Log podatkov (JSON)</p>
        <input type="hidden" name="invoice-response" id="invoice-response" value="<?php //echo $invoice_data; ?>">
        <button type="submit" name="minimax-log-invoice" value="<?= $order_post_id;?>" class="button <?= $status_btn;?>" target="_blank">Log podatkov</button>
        </hr>
    </form>
    <?php 
}


global $order_post_id;
// Check if this POST request is racun type
if (isset($_POST['minimax-action']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'minimax_meta_box_action')) {
    add_action( 'wp_loaded', function(){
        global $order_post_id; global $settings; global $send; global $pdf_url;
        global $api; global $pdf_name; global $attachments; global $save;
        $order_post_id = intval($_POST["minimax-orderId"]);
        minimax_create_invoice($order_post_id, 'racun');
    }, 10);
}

// PDF
if (isset($_POST['minimax-action-pdf-send']) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'minimax_meta_box_action')) {
    add_action( 'wp_loaded', function(){
        global $order_post_id; global $settings; global $send; global $pdf_url;
        global $api; global $pdf_name; global $attachments; global $save;
        $order_post_id = intval($_POST["minimax-orderId"]);
        send_pdf_manually($order_post_id, $order_post_id, $send, $api, $pdf_url, $pdf_name, $attachments);
    }, 10);
}

if (isset($_POST["minimax-log-invoice"]) && isset($_POST['_wpnonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_wpnonce'])), 'minimax_meta_box_action')) {
    add_action("wp_loaded", function(){
        global $order_post_id;
        display_log_invoice($order_post_id);
    }, 10);
}
 function send_customer($order_post_id) {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }

    global $order_post_id;
    global $settings;  global $current_user;  global $wpdb;
    global $woocommerce; global $product; global $order_id;
    global $post;
    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    );  

    $order_id = $_POST['post_id'];

    $get_order_data = get_order_data($order_id);

    // GET CUSTOMER DATA
        $order_for_meta = wc_get_order($order_id);

        $customer_id = $get_order_data['billing_email'];
        $customer_hash = md5($customer_id);

        $customerCode = $order_for_meta->get_meta('minimax_invoice_customer_hash');
        $check_customer = $api->customer_by_code($customerCode); 
        if ($check_customer) {

            $changed = false;
            $minimax_customer_id = $check_customer->CustomerId;
            
            if ($get_order_data['AddresseeName'] != $check_customer->Name) {
                $changed = true;
                $check_customer->Name = $get_order_data['AddresseeName'];
      
            }
            if ($get_order_data['billing_post_code'] != $check_customer->PostalCode) {
                $changed = true;
                $check_customer->PostalCode = $get_order_data['billing_post_code'];
            }
            if ($get_order_data['billing_city'] != $check_customer->City) {
                $changed = true;
                $check_customer->City = $get_order_data['billing_city'];
            }
            if ($get_order_data['billing_address'] != $check_customer->Address) {
                $changed = true;
                $check_customer->Address = $get_order_data['billing_address'];
            }
            if ($get_order_data['billing_country'] != $check_customer->Country) {
                $changed = true;
                $check_customer->Country = $get_order_data['billing_country'];
            }
             if ($changed) {
                 
                $update_customer = json_encode($check_customer,JSON_PRETTY_PRINT);

                $update = $api->update_customer($minimax_customer_id,$update_customer);

             }
            }

        $store_update_data[] = array(
            $get_order_data['AddresseeName'],
            $get_order_data['billing_post_code'],
            $get_order_data['billing_city'],
        ); 

        echo '<table id="response" class="widefat">
        <thead>
            <tr>
                <th class="row-title">Ime in priimek</th>
                <th class="row-title">Naslov</th>
                <th class="row-title">Poštna številka</th>
                <th class="row-title">Kraj</th>
            </tr>
        </thead>
        <tbody>'; 

         foreach ($store_update_data as $key => $value) {
            echo '<tr style="' . ($value[3] < $value[2] ? 'background-color: #FFD2D2' : '') . '">';
            echo "<td>{$value[0]}</td>";
            echo "<td>{$value[1]}</td>";
            echo "<td>{$value[2]}</td>";
            echo "<td>{$value[3]}</td>";
            echo "</tr>";
        }      
        echo '</tbody>'
        . '</table>';  
    die();
    }


// AJAX FOR UPDATE CUSTOMER
add_action('admin_footer', 'sync_customer_minimax_javascript');

function sync_customer_minimax_javascript() {


    $is_multisite = is_multisite();

    if ($is_multisite) {
        $high_performance_order_storage = get_site_option('woocommerce_custom_orders_table_enabled');
    } else {
        $high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
    }
    global $order_post_id;  global $woocommerce; global $post;
    if ($high_performance_order_storage == 'yes') {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $order_post_id = intval($_GET['id']);
        }
    } else {
        //$order_post_id = $post->ID;
        if (isset($_POST['post_id'])) {
            $order_post_id = $_POST['post_id'];
        }
    }

    ?>
    <script type="text/javascript" >
        jQuery(document).ready(function ($) {
      
            // UPDATE CUSTOMER
            jQuery("#update_customer").on('click', function (e) {
                var order_id = $('#minimax-orderId').val();
                swal2({
                    title: 'Posodabljam stranko!',
                    text: 'Prosim počakajte...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    onOpen: function () {
                        swal2.showLoading();
                        $.ajax(
                            {
                                url: '/wp-admin/admin-ajax.php',
                                data: {
                                    'action': 'send_customer',
                                    'post_id': <?= $order_post_id;?>
                                },        
                                type: 'POST',
                                success: function(data) {
                                $('#result').html(data);
                                swal2.close();
                                },
                                error: function(data) {
                                    alert(data.toString());
                                },
                                
                                
                            }
                        ).then(function() {
                        swal(
                            'Uspešno!',
                            'Stranka je bila posodobljena.',
                            'success'
                        );
                        })
                     }
                });
                e.preventDefault();
            });
       

        // CREATE INVOICE
        jQuery("#create_invoice").on('click', function(e) {
            e.preventDefault();
            var order_id = $('#minimax-orderId').val();
            var current_site = $('#current_site_id').val();

            swal2({
                title: 'Izdani račun!',
                text: 'Prosim počakajte...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                onOpen: function() {
                    swal2.showLoading();
                    jQuery.ajax({
                        url: '/wp-admin/admin-ajax.php',
                        type: 'POST',
                        data: {
                            'action': 'minimax_create_invoice',
                            'post_id': <?= $order_post_id;?>,
                            'current_site': current_site,
                        },
                        success: function(data) {
                        swal2.close(); 

                        console.log('PRIKAŽI DADATA:',data);
                        if (!data.success) {
                            var msg = data.messages;
                            console.log('PRIKAZI SPOROCILO:',msg);
                            let errorMessages = Array.isArray(data.messages) 
                            ? data.messages.map(msg => `<li>${msg}</li>`).join("") 
                            : `<li>${data.messages}</li>`;


                            console.log("Error:", errorMessages);
                            swal2({
                                type: 'error',
                                title: 'Error',
                                html: `<ul style="text-align: left; padding-left: 20px;"><li>${errorMessages}</li></ul>`,
                            }).then(function() {
                                location.reload();
                            });

                        } else {
                            var successMessage = Array.isArray(data.messages) 
                            ? data.messages.join("\n") 
                            : "Račun je bil uspešno izdan!";

                            console.log("Success:", successMessage);
                            //swal2('Račun je bil uspešno izdan!', '', 'success');
                           sendInvoiceEmail(<?= $order_post_id; ?>);

                           swal2({
                                type: 'success',
                                title: "Racun je bil uspešno izdan",
                                text: successMessage,
                            }).then(function() {
                                location.reload();
                            });
                         
                        }
                    },

                        error: function(xhr) {
                
                            swal2({
                                type: 'error',
                                title: 'Oops...',
                                text: 'Prišlo je do napake:: ' + xhr.statusText,
                            });
                        },
                    });
                }
            });
        });

    
        function sendInvoiceEmail(order_post_id) {
        swal2.fire({
        title: 'Pošiljanje sporočila',
        text: 'Prosimo počakajte...',
        onOpen: () => {
            Swal.showLoading();
            jQuery.ajax({
                url: '/wp-admin/admin-ajax.php',
                type: 'POST',

                data: {
                    'action': 'send_pdf_automatic', 
                    'post_id': <?= $order_post_id;?>
                },
                success: function(response) {
                console.log(' PRIKAZI PODATKE:', response);
                    if (response.success) {
                        swal2.fire('Sporočilo je bilo uspešno poslano', '', 'success');
                    } else {
                        swal2.fire('Error', 'Prišlo je do napake: ' + response.message, 'error');
                    }
                },
                error: function(xhr) {
                    swal2.fire('Error', 'Prišlo je do napake: ' + xhr.statusText, 'error');
                },
            });
        },
    });
} 


        });
    </script> 

    <?php
} 


// MAIN FUNCTION FOR CREAT INVOICE
   
function minimax_create_invoice($order_post_id) {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }
    global $woocommerce, $post;
    global $order_post_id;


    $status = 'enabled';
    if (isset($_POST["minimax-action"])) {
        $status = 'disabled';
        
    }
    if (isset($_POST["current_site"])) {
       global $current_site_id; $current_site_id = $_POST['current_site'];
    }

    if (is_admin()) {
        $order_post_id = $_POST['post_id'];
    } else {
        $order_post_id =  wc_get_order_id_by_order_key( $_GET[ 'key' ] );
    }
 

    if (!is_numeric($order_post_id) || $order_post_id <= 0) {
        return;
    }


    $is_multisite = is_multisite();

    if ($is_multisite) {
        $order = get_order_from_multisite($order_post_id);
    } else {
        $order = wc_get_order($order_post_id);
    }

    if (isset($_POST["minimax-action"])) {

        $date_clicked = date('Y-m-d H:i:s');;
        $message_invoce_note = 'Račun je bil izdan v minimax dne ' . $date_clicked ;
        $order->add_order_note( $message_invoce_note );

    }

    global $settings;  global $current_user;  global $wpdb; 
    global $woocommerce; global $product; 

    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    );  

    $get_order_data = get_order_data($order_post_id);

    $high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
    $is_multisite = is_multisite();

    if ($is_multisite) {
        $high_performance_order_storage = get_site_option('woocommerce_custom_orders_table_enabled', $high_performance_order_storage);
    }

    $order_id  = $order_post_id; 

    /************************** */
    /* CUSTOMER 
    /****************************/

    // CHECK CUSTOMER BY CODE
  

    //switch_to_blog($current_blog_id);


    $customer_id = $order->get_billing_email();

    $customerCode_hash = md5($customer_id);

    $customerCode = $order->get_meta('minimax_invoice_customer_hash');
    
    $check_customer = $api->customer_by_code($customerCode_hash);

    // CUSTOMER DATA

    if (!empty($get_order_data['billing_company'])) {
        $RecipientName = $get_order_data['billing_company'];
    } else {
        $RecipientName = $get_order_data["AddresseeName"];
    }
    
    $billing_country_shipping = WC()->countries->countries[ $get_order_data['billing_country']];

    if ($billing_country_shipping == 'Slovenia' || $billing_country_shipping == 'Slovenija') {
        $display_country_name = null;

    }  else {
        $display_country_name = WC()->countries->countries[ $get_order_data['billing_country']];
    }
 
    if ($check_customer) {
        $minimax_customer_id = $check_customer->CustomerId;

    } else {
            
        $customer_data = array();
        $customer_data["CustomerId"] = null;
        $customer_data["Code"] = $customerCode_hash;
        $customer_data["Name"] = $RecipientName;
        $customer_data["Address"] = $get_order_data['billing_address'];
        $customer_data["PostalCode"] = $get_order_data['billing_post_code'];
        $customer_data["City"] = $get_order_data['billing_city'];
        $customer_data["Country"]["ID"] = $get_order_data['AddresseeCountry'];
        $customer_data["Country"]["Name"] = $get_order_data['billing_company'];
        $customer_data["Country"]["ResourceUrl"] = null;
        $customer_data["CountryName"] = $display_country_name;
        $customer_data["TaxNumber"] = null;
        $customer_data["RegistrationNumber"] = null;
        $customer_data["VATIdentificationNumber"] = null;
        $customer_data["SubjectToVAT"] = "N";
        $customer_data["Currency"]["ID"] = $get_order_data['Currency'];
        $customer_data["Currency"]["Name"] = null;
        $customer_data["Currency"]["ResourceUrl"] = null;
        $customer_data["ExpirationDays"] = 0;
        $customer_data["RebatePercent"] = '0.0';
        $customer_data["WebSiteURL"] = null;
        $customer_data["EInvoiceIssuing"] = "SeNePripravlja";
        $customer_data["InternalCustomerNumber"] = null;
        $customer_data["Usage"] = 'D';
        $customer_data["RecordDtModified"] = "0001-01-01T00:00:00";
        $customer_data["RowVersion"] = null;

        // Encode to JSON
        $podatki = json_encode($customer_data, JSON_PRETTY_PRINT);
	
        // Fetch user data after create
        $customer_data = $api->create_customer($podatki);


        // Get customer
        $minimax_customer_id = $customer_data->CustomerId;

        $order->add_meta_data('minimax_invoice_customer_id', $minimax_customer_id, true);
        $order->add_meta_data('minimax_invoice_customer_hash', $customer_data->Code, true);
        $order->save();
    }

    // Call invoice method after this 
    create_post_after_order_customer($order_post_id, $minimax_customer_id, $api);


}

function create_post_after_order_customer( $order_post_id, $customer_id, $api) {
    
    global $woocommerce; global $product;
    global $current_user;  global $wpdb;
    global $settings;   global $post;

     global $current_site_id;
    $customerCode = bin2hex(openssl_random_pseudo_bytes(16));

    $high_performance_order_storage = get_option('woocommerce_custom_orders_table_enabled');
    $is_multisite = is_multisite();

    if ($is_multisite) {
        $high_performance_order_storage = get_site_option('woocommerce_custom_orders_table_enabled', $high_performance_order_storage);
    }
    global $order_post_id; global $post;
    if (is_admin()) {
        // **Admin Panel Logic (Manual Trigger)**
        if ($high_performance_order_storage == 'yes') {
            if (isset($_GET['id']) && !empty($_GET['id'])) {
                $order_post_id = intval($_GET['id']);
            }
           
        } else {
            $order_post_id = $_POST['post_id'];
        
        }
    } else {
        $order_post_id =  wc_get_order_id_by_order_key( $_GET[ 'key' ] );
    }
    $is_multisite = is_multisite();


    if ($is_multisite) {
        $order = get_order_from_multisite($order_post_id);
    } else {
        $order = wc_get_order($order_post_id);
    }


    $get_order_data = get_order_data($order_post_id);


    $address = $get_order_data['AddresseeName'];

    $addressBilling = $order->get_address("billing");
  
    $order_data = $order->get_data(); 

    if (!empty($get_order_data['billing_company'])) {
        $RecipientName = $get_order_data['billing_company'];
    } else {
        $RecipientName = $get_order_data["AddresseeName"];
    }

 
    // PAYMENT METHOD
    $paymentMethodID = null;
 
	$currentDate = new DateTime(); 
    $formattedDate = $currentDate->format(DateTime::ATOM); 


    $datePaid = new DateTime($formattedDate);

    $time = $datePaid->format(DateTime::ATOM);
	
    $items = $order->get_items();

    // BILLING COUNTRY 
    $billing_country_shipping = WC()->countries->countries[$get_order_data['billing_country']];

    if ($billing_country_shipping == 'Slovenia' || $billing_country_shipping == 'Slovenija') {
        $billing_country_shipping = '';

        $percent = 22;
        $VatRatePercentage = '';
    }

    $billing_country_address_shipping = null;
    if ($get_order_data['AddresseeCountry'] != 192) {
        $billing_country_address_shipping = WC()->countries->countries[$addressBilling['country']];
        $billing_country_shipping = WC()->countries->countries[$addressBilling['country']];

        $billing_country = $get_order_data['billing_country'];
    } else {
        $billing_country = null;
    }

    // Get order status

    switch ($order->get_status()) {
        case 'pending':
            $status = 'O';
            $payment_status = 'NeplacanNezapadel';
            break;
        case 'processing':
            $status = 'O';
            $payment_status = 'Osnutek';
            break;
        case 'on-hold':
            $status = 'I';
            $payment_status = 'Osnutek';
            break;
        case 'completed':
            $status = 'I';
            $payment_status = 'Placan';
            break;
        case 'cancelled':
            $status = 'Z';
            break;
        case 'refunded':
            $status = 'R';
            break;
        default:
            $status = 'R';
            $payment_status = 'Osnutek';
            break;
    }

    
    $get_employees = $api->GetEmployees();
   $getPaymentMethod = $api->getNacinPlacila();

   $total_number_of_payment_methods = get_option('total_payment_option', 0);
   $paymentMethodID = null;
   $paymentMethodName = null;
   $order_payment_method = $order->get_payment_method_title();
   
   for ($i = 0; $i < $total_number_of_payment_methods; $i++) {
       // Retrieve the stored MiniMax payment method ID and name
       $minimax_payment_method = get_option('MiniMax_payment_method_' . $i);
       $minimax_payment_method_name = get_option('MiniMax_payment_method_name_' . $i); // Retrieve the saved name
       $woocommerce_payment_method_title = get_option('WooCommerce_payment_method_for_MiniMax_' . $i);
     
       // Check if the order payment method matches the WooCommerce payment method title
       if ($order_payment_method == $woocommerce_payment_method_title) {
           $paymentMethodID = $minimax_payment_method;
           $paymentMethodName = $minimax_payment_method_name;
           break; // Exit loop once a match is found
       }
   }
      
    /************************/
    /* CREATE INVOICE JSON
    /*************************/

    $GetCurrencies = $api->GetCurrencies($order->get_currency());
    $stevilcenje = $api->getStevilcenje();
    foreach ($stevilcenje->Rows as $value) {
        if ($value->DocumentNumberingId == get_option("MiniMax_invoice_stevilcenje", 0)) {
        $document_name_display = $value->Name;
        }
    }

    $GetItemsSettings = $api->GetItemsSettings();
     
    if ($GetItemsSettings->PricesIncludeVAT == 'D') {
        $PricesOnInvoice = 'D';

    } else {
        $PricesOnInvoice   = 'N';
    }


    $billing_country_shipping = WC()->countries->countries[ $get_order_data['billing_country']];

    if ($billing_country_shipping == 'Slovenia' || $billing_country_shipping == 'Slovenija') {
        $display_country_name = null;

    }  else {
        $display_country_name = WC()->countries->countries[ $get_order_data['billing_country']];
    }

    $get_analytic_by_id = $api->analyticsByID(get_option('MiniMax_analitika'));
    $analytic_data = [
        'ID' => $get_analytic_by_id->AnalyticId,
        'Name' => $get_analytic_by_id->Name,
    ];


   $invoice_json = [
        'IssuedInvoiceId'              => null,
        'InvoiceNumber'                => null,
        'DocumentNumbering'            => [
            'ID' => get_option('MiniMax_invoice_stevilcenje'),
            'Name' => $document_name_display,
        ],
        'Customer'                     => [
            'ID' => $customer_id
        ],
        'DateIssued'                   => $time,
        'DateTransaction'              => $time,//"2023-08-05T08:36:50+00:00"
        'DateTransactionFrom'          => $time,
        'DateDue'                      => $time,
        'AddresseeName'                => $RecipientName,//$get_order_data['AddresseeName'],
        'AddresseeAddress'             => $get_order_data['AddresseeAddress'],
        'AddresseePostalCode'          => $get_order_data['billing_post_code'],
        'AddresseeCity'                => $get_order_data['billing_city'],
        'AddresseeCountryName'         => $display_country_name,//$get_order_data['billing_country'],//$billing_country_shipping,//$billing_country,
        'AddresseeCountry'             => [
            'ID' => $get_order_data['AddresseeCountry'],
        ],
        //Prejemnik
        'RecipientName'                => $RecipientName,
        'RecipientAddress'             => $get_order_data['AddresseeAddress'],
        'RecipientPostalCode'          => $get_order_data['billing_post_code'],
        'RecipientCity'                => $addressBilling['city'],
        'RecipientCountryName'         => $display_country_name,//$get_order_data['billing_country'],//$billing_country_shipping,//$billing_country,
        'RecipientCountry'             => [
            'ID' => $get_order_data['AddresseeCountry'],
        ],
        'PaymentReference'             => null,
        'Currency'                     => [
            'ID' => $get_order_data['Currency'],
        ],
        'Analytic'                     => $analytic_data,//$get_analytic_by_id,//get_option('MiniMax_analitika'),
        'Document'                     => null,
        'IssuedInvoiceReportTemplate'  => [
            'ID' => get_option('MiniMax_invoice_template'),
        ],
        'DeliveryNoteReportTemplate'   => [
            'ID' => get_option('MiniMax_invoice_template_do'),
        ],
        
        'Status'                       => 'O',//$status,
        'DescriptionAbove'             => null,
        'DescriptionBelow'             => null,
        'DeliveryNoteDescriptionAbove' => null,
        'DeliveryNoteDescriptionBelow' => null,
        'Notes'                        => null,
        'Employee'                     => [
            'ID' => empty(get_option('MiniMax_employer')) ? null : get_option('MiniMax_employer'),
        ],
        'PricesOnInvoice'              => $PricesOnInvoice,
        'RecurringInvoice'             => 'N',
        'InvoiceAttachment'            => null,
        'EInvoiceAttachment'           => null,
        'InvoiceType'                  => 'R',
        'OriginalDocumentType'         => null,
        'OriginalDocumentDate'         => null,
        'PurposeCode'                  => null,
        'VatAccountingType'            => null,
        'PaymentStatus'                => $payment_status,
        'IssuedInvoiceRows'            => [],
        'IssuedInvoicePaymentMethods' => [],

    ];
    $index = 1;

    $show_all_items = $api->get_all_items();

    $display = $show_all_items->Rows[0]->Code;


    $is_multisite = is_multisite();

 

    foreach ($order->get_items() as $item_id => $item ){


        $itemid = $item->get_product_id(); 



        $hash = md5($itemid);

        $hash = substr($hash, 0, -2);

        $product_id = $item->get_product_id();
        $product = wc_get_product($product_id);

        // Check if the product is a variable product
        if ($product->is_type('variable')) {
           
            $variation_id = $item->get_variation_id();
            
            $variation = wc_get_product($variation_id);

            // Use the SKU of the variation
            if (get_option('minimax_item_code_sku') && $variation && $variation->get_sku()) {
                $item_code = $variation->get_sku();
            } else {
               
                $hash = md5($variation_id);
                $item_code = substr($hash, 0, -2);
            }
            } else {

                // For simple products, use the SKU or part of the hash as before
                $hash = md5($product_id);
                if (get_option('minimax_item_code_sku') && $product->get_sku()) {
                    $item_code = $product->get_sku();
            } else {
                $item_code = substr($hash, 0, -2);
            }
        }

        $itemCode = get_post_meta($itemid,'minimax_item',true);

        $singlePrice = (float)($item->get_total() / $item->get_quantity());
        $singlePriceVAT = (float)($item->get_total_tax() / $item->get_quantity());

        $item_response = $api->check_code($item_code);
   
        $product_type = get_option("MiniMax_product_type");

        if ($item_response) {

            $minimax_id = $item_response->ItemId;
            
        } else { 

        $items_data = [];
        $items_data['Name'] = htmlspecialchars($item->get_name(), ENT_QUOTES, 'UTF-8');
        $items_data['Code'] = $item_code;
        $items_data['ItemType'] = $product_type ?? "I";
        $items_data['StocksManagedOnlyByQuantity'] = 'D';
        $items_data['ReliefByCompositeFromWarehouse'] = 'N';
        $items_data['VatRate'] = [
            'ID' => 36
        ];
        $items_data['Price'] = (float)$singlePrice; 
        $items_data['Currency'] = [
            
            'ID' => $get_order_data['Currency'],
        ];

        $items_data = json_encode($items_data, JSON_PRETTY_PRINT);

        $minimax_item = $api->posttItems($items_data);

        $minimax_id = $minimax_item->ItemId;

        add_post_meta($itemid, 'minimax_item',$minimax_item->Code);
   

        }

        // FIX
        $singlePrice = (float)($item->get_total() / $item->get_quantity());
        $singlePriceVAT = (float)($item->get_total_tax() / $item->get_quantity());

        $singlePriceSubtotal = (float)($item->get_subtotal() / $item->get_quantity());
        $singlePriceSubtotal = round($singlePriceSubtotal,2);
        $singlePriceSubtotalVAT = (float)($item->get_subtotal_tax() / $item->get_quantity());


        $singlePriceTotal = (float)($item->get_total() / $item->get_quantity());
        $singlePriceTotal = round($singlePriceTotal,2);
        $singlePriceTotalVAT = (float)($item->get_total_tax() / $item->get_quantity());

        if($singlePriceSubtotal != $singlePriceTotal){
            $singleDiscount = number_format(round((($singlePriceSubtotal - $singlePriceTotal) * 100) / $singlePriceSubtotal,2),2);
        } else {
            $singleDiscount = null;
        }
        
        // PRIDOBI TAX
        $customer_country_code = $order->get_billing_country();

        // VAT RATE CODE - MINIMAX. NA PODLAGI TEGA DOBI VatRateId
        $get_country_by_code = $api->getCountryByCode($customer_country_code);
        $get_country_id = $get_country_by_code->CountryId;

        $date = date('Y-m-d');

        $product_id = $item->get_product_id(); // ID of your product
        $product = wc_get_product($product_id);
        $product_tax_class = $product->get_tax_class();
  
        if ($product_tax_class == '') {
            // Standard rate
            $code = "S";
        } else if ($product_tax_class == 'reduced-rate' || $product_tax_class == 'znizana-stopnja') {
            // Reduced rate
            $code = "Z";
   
        } else if ($product_tax_class == 'zero-rate' || $product_tax_class == 'stopnja-nic') {
            // Zero rate
            $code = "N";
        } 

        $getVat = $api->getVatRate($get_order_data['billing_country']);
        
        $getCountryByCode = $api->getCountryByCode($get_order_data['billing_country']);
  
        $billing_country_code = $get_order_data['billing_country'];
        $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );
 
        if ($billing_country_code == $getCountryByCode->Code || empty($billing_country_code)) {

            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            $tax_rates = WC_Tax::get_rates( $product->get_tax_class() );

            $product_tax_class = $product->get_tax_class();

            $tax_rates = WC_Tax::get_rates($product_tax_class);

            $billing_country_code = $get_order_data['billing_country'];

            
            $product_id = $item->get_product_id();
            $product = wc_get_product($product_id);

            $product_tax_class = $product->get_tax_class();

            $tax_rates = WC_Tax::find_rates(array(
                'country'   => $billing_country_code,
                'tax_class' => $product_tax_class,
            ));

            $percent = 0;

            if (!empty($tax_rates)) {
                // Billing country tax rate found, so use it
                $tax_rate = reset($tax_rates);
                $percent = $tax_rate['rate'];

            } else {
                // No billing country tax rate found, so try to get the default '*' rate
                $fallback_tax_rates = WC_Tax::find_rates(array(
                    'country'   => '*',
                    'tax_class' => $product_tax_class,
                ));

                if (!empty($fallback_tax_rates)) {
                    $fallback_tax_rate = reset($fallback_tax_rates);
                    $percent = $fallback_tax_rate['rate'];            
                }
            }

            } 



        $get_country_id = $getCountryByCode->CountryId;

        $getVatRate = $api->getGeneralVatRate($get_country_id,$date,$code);

        $VatRatePercentageNew = $getVatRate->VatRatePercentage->ID;
        $getVatRateIDNew = $getVatRate->VatRateId;
        $getVatRatePercent = $getVatRate->Percent;

        // VAT RATE FOR SHIPPING
        $getVatRatePay = $api->getGeneralVatRate($get_country_id,$date,'S');
        $getVatRatePayId = $getVatRatePay->VatRateId;
        $getVateRatePayPercent = $getVatRatePay->Percent;
        $getVateratePayPercentage = $getVatRatePay->VatRatePercentage->ID;

        $billing_country_shipping = WC()->countries->countries[$get_order_data['billing_country']];
        if ($billing_country_shipping == 'Slovenia' || $billing_country_shipping == 'Slovenija'  || $billing_country_shipping == '') {
            $VatRatePercentageNew = null;
            $getVateratePayPercentage = null;
        }
        
        // Get the item name
        $product = $item->get_product();
        $itemName = htmlspecialchars($item->get_name(), ENT_QUOTES, 'UTF-8');

        // Get the product variation attributes
        $formatted_meta_data = $item->get_formatted_meta_data();

       // If it's a variation, append its attributes to its name
       if ( ! empty( $formatted_meta_data ) ) {
           foreach ( $formatted_meta_data as $meta_id => $meta ) {
               $attribute_name = $meta->key;
               $taxonomy = $attribute_name; // As the key already seems to be the full attribute taxonomy
               
               // Get the attribute term
               $term = get_term_by('slug', $meta->value, $taxonomy);

               if ( ! $term ) {
                continue;
                }
               // If the term exists, use its name, otherwise use the slug

               $attribute_value = $term ? $term->name : $meta->value;
   
               // Append the attribute name and value to the product name
               $itemName .= ' - ' . $attribute_value;
                
           }
       }

       $date = date("Y-m-d"); 
       $getDDV = $api->getDDV($date,$get_order_data['AddresseeCountry']);

       foreach ($getDDV->Rows as $single_ddv) {
           if ($percent == $single_ddv->Percent) {
               $VatRateId = $single_ddv->VatRateId;
               if ($billing_country_shipping == 'Slovenia' || $billing_country_shipping == 'Slovenija'  || $billing_country_shipping == '') {
                $VatRatePercentage = null;
               } else {
                $VatRatePercentage = $single_ddv->VatRatePercentage;
               }  
           } 
   
       }


        $invoice_json['IssuedInvoiceRows'][] = [
            'IssuedInvoiceRowId' => 1,
            'IssuedInvoice'      => 1,
            'Item'               => [
                'ID' => $minimax_id
            ],
            'ItemName'         => $itemName,//$item->get_name(),
            'RowNumber'          => $index++,
            'ItemCode'           => $item_code,
            'ItemType'            => $product_type ?? "I",
            'Description'        => null,
            'Quantity'           => $item->get_quantity(),
            'DiscountPercent'    => number_format($singleDiscount,2),
            'UnitOfMeasurement'  => empty(get_option('minimax_measurement')) ? 'Kom' : get_option('minimax_measurement'),
            'Price'              => number_format($singlePriceSubtotal,2),
            'PriceWithVAT'       => number_format($singlePriceSubtotal + $singlePriceSubtotalVAT, 2),
            'VATPercent' => get_option('minimax_manual_vat_rate') == 1 ? '' : $percent,
            'VATRate'            => [
                'ID' => $VatRateId,//$getVatRate->VatRateId,
            ],
            'VatRatePercentage' => [
                'ID' =>  $VatRatePercentage,//$VatRatePercentage,
          
            ], 
            'Discount'           => null,
            'Value'              => null,
            'Warehouse'          => [
                'ID' => get_option('MiniMax_skladisce'),
            ],
            'RecordDtModified'   => null,
            'RowVersion'         => null,
        ];
    

    }

    // FEES
     $fees = $order->get_fees();

    if ($fees) {
        foreach ($fees as $fee) {
    
            $fee_total = $fee->get_total();

            $fee_name= $fee->get_name();
            $fee_code = str_replace(' ', '_', $fee_name);

            $item_response = $api->check_code($fee_code);
             if ($item_response) {
                $minimax_id_fee = $item_response->ItemId;
            }   else {
                $static_products_fee = [
                    'Name' => 'Provizija -' . $fee_name ,
                    'Code' => $fee_code,
                    'ItemType' => 'S',
                    'StocksManagedOnlyByQuantity' => 'N',
                    'CalculationOfConsumptionTax' => 'N',
                    'VatRate' => [
                        'ID' => 36,
                    ],
                    'Price' => ((float)$fee_total),
                    'Currency' => [
                        'ID' => $get_order_data['Currency'],
                    ]
    
                ];
    
                $items_data_fee = json_encode($static_products_fee, JSON_PRETTY_PRINT);
    
                $minimax_item_fee = $api->posttItems($items_data_fee);
    
                $minimax_id_fee = $minimax_item_fee->ItemId; 
            } 
            
           $invoice_json['IssuedInvoiceRows'][] = [
                'OrderRowId'       => null,
                'IssuedInvoiceRowId' => 1,
                'IssuedInvoice'      => 1,
                'Item'               => [
                    'ID' => $minimax_id_fee
                ],
                'ItemName'         => 'Provizija - '. $fee_name,
                'RowNumber'          => $index++,
                'ItemCode'           => null,
                'Description'        => null,
                'ItemType'            => 'S',
                'Quantity'           => 1,
                'DiscountPercent'    => null,
                'UnitOfMeasurement'  => empty(get_option('minimax_measurement')) ? 'Kom' : get_option('minimax_measurement'),
                'Price'            => ((float)$fee_total),
                'PriceWithVAT'       => ((float)$fee_total),
                'VATPercent'         =>  get_option('minimax_manual_vat_rate') == 1 ? '' : $getVateRatePayPercent,
                'VATRate'            => [
                    'ID' => $getVatRatePayId,//$getVatRate->VatRateId,
                ],
                'VatRatePercentage' => [
                    'ID' => $getVateratePayPercentage,//$getVateratePayPercentage,//$VatRatePercentage,
                ],
                'Discount'           => 0,
                'DiscountPercent'    => 0,
                'Value'              => null,
              'Warehouse'          => null,
                'RecordDtModified'   => null,
                'RowVersion'         => null,
            ];   
    
        }
    } 

    $shipping_total = 0;
    $shipping_tax_total = 0;
    $shipping_methods = $order->get_shipping_methods();
    foreach ($shipping_methods as $shipping_method) {
        $shipping_total += $shipping_method->get_total();
        $taxes = $shipping_method->get_taxes(); // Get the taxes array
        $shipping_tax_total += array_sum($taxes['total']); // Sum all tax values
    }

      if ($shipping_total > 0) {
        $item_code_delivery = 'Dostava';
        $item_response = $api->check_code($item_code_delivery);
        

    
        if ($item_response) {
            $minimax_id_special = $item_response->ItemId;
        } else {

            $static_products = [
                'Name' => 'Dostava',
                'Code' => 'Dostava',
                'ItemType' => 'S',
                'StocksManagedOnlyByQuantity' => 'N',
                'CalculationOfConsumptionTax' => 'N',
                'VatRate' => [
                    'ID' => 36,
                ],
                'Price' => (float)$order->calculate_shipping(),
                'Currency' => [
                    'ID' => $get_order_data['Currency'],
                ]

            ];

            $items_data_delivery = json_encode($static_products, JSON_PRETTY_PRINT);

            $minimax_item_delivery = $api->posttItems($items_data_delivery);

            $minimax_id_special = $minimax_item_delivery->ItemId;
        }

        $shipping_total_with_vat = $shipping_total + $shipping_tax_total;

        $invoice_json['IssuedInvoiceRows'][] = [

            'OrderRowId'       => null,
            'IssuedInvoiceRowId' => 1,
            'IssuedInvoice'      => 1,
            'Item'               => [
                'ID' => $minimax_id_special,
            ],
            'ItemName'           => empty(get_option('minimax_delivery_text')) ? 'Dostava' : get_option('minimax_delivery_text'),
            'RowNumber'          => $index++,
            'ItemCode'           => null,
            'Description'        => null,
            'ItemType'            => 'S',
            'StocksManagedOnlyByQuantity' => 'N',
            'CalculationOfConsumptionTax' => 'N',
            'Quantity'           => 1,
            'DiscountPercent'    => null,
            'UnitOfMeasurement'  => empty(get_option('minimax_measurement')) ? 'Kom' : get_option('minimax_measurement'),
            'Price'              => (float)$shipping_total,
            'PriceWithVAT'       => number_format($shipping_total_with_vat, 2),//(float)$shipping_total,
            'VATPercent' => get_option('minimax_manual_vat_rate') == 1 ? '' : $getVateRatePayPercent,
            'VATRate'            => [
                'ID' => $getVatRatePayId,//$getVatRate->VatRateId,
            ],
            'VatRatePercentage' => [
                'ID' => $getVateratePayPercentage,//$getVateratePayPercentage,
            ], 
            'Discount'           => 0,
            'DiscountPercent'    => 0,
            'Value'              => null,
            'Warehouse'          => null,
            'RecordDtModified'   => null,
            'RowVersion'         => null,
            'StocksAccount' => null,
            'ReliefByCompositeFromWarehouse' => 'N',
            'ReliefByCompositeFromIssuedInvoice' => 'N',
        ];  

    }  

   $invoice_json['IssuedInvoicePaymentMethods'][] = [
        'PaymentMethod' => [
            'ID' => $paymentMethodID,
        ],
        'Amount'      => number_format($order->get_total(),2),
        'AlreadyPaid' => 'D',
     ];
     


     $invoice_data = json_encode($invoice_json, JSON_PRETTY_PRINT);
  
     $order->update_meta_data("log_invoice_data", $invoice_data);
     $order->save();

     $send = $api->get_IssuedInvoice($invoice_data);
     $messages = [];
     
     if (!property_exists($send, 'IssuedInvoiceId')) {
        $responseObj = json_decode($send);
     if (isset($responseObj->success) && $responseObj->success === false) {

         if (isset($responseObj->response) && is_array($responseObj->response)) {
    
             foreach ($responseObj->response as $errorDetail) {
         
                 if (isset($errorDetail->Message)) {
                   $splitMessages = preg_split('/(?<=\.)\s*/', strip_tags($errorDetail->Message));
                    
                   $messages = array_merge($messages, $splitMessages);
                }
             }
         }
     
         if (empty($messages)) {
             $messages[] = 'Prišlo je do napake.';
         }
         $response = [
            'success' => false,
            'messages' => $messages,
        ];

        }
     } else {

        
        $issuedInvoiceId = $send->IssuedInvoiceId;
     
        $rowVersion = $send->RowVersion;

        $pdf_invoice = $api->invoice_pdf($issuedInvoiceId,$rowVersion);


  		
        $message = [];
        // IZDAJ RAČUN
        $izdani_racun = $api->CustomActionIssuedInvoice($issuedInvoiceId,$rowVersion);
        
        
        $success = true; // Assume success, but override if needed

        $responseArray = json_decode($izdani_racun, true);

        if ($responseArray && isset($responseArray['success']) && !$responseArray['success']) {
            if (isset($responseArray['response']['ValidationMessages'])) {
                $errorMessage = $responseArray['response']['ValidationMessages'];
                foreach ($errorMessage as $single_message) {
                    $splitMessages = preg_split('/(?<=\.)\s*/', strip_tags($single_message["Message"]));
                   $message = array_merge($message, $splitMessages);
                }
            } 
        } 

        if ($pdf_invoice->ValidationMessages) {
            foreach($pdf_invoice->ValidationMessages as $single_message) {
                $splitMessages = preg_split('/(?<=\.)\s*/', strip_tags($single_message->Message));
                    
                $message = array_merge($message, $splitMessages);
            }

        }
        if ($pdf_invoice->Data == null) {
            $success = false;
        }
        // If no validation messages, set default success message
        if (empty($message)) {
            $message[] = "Račun je bil uspešno izdan.";
        }
        
        // Send proper success/failure response
        $response = [
            'success' => $success,
            'messages' => $message
        ];

        
        $dir = plugin_dir_path( __FILE__ );
        global $save; global $pdf;
        $save = $dir.'Racuni/'.$pdf_invoice->Data->AttachmentFileName;
        $pdf_name = $pdf_invoice->Data->AttachmentFileName;
        $pdf = base64_decode($pdf_invoice->Data->AttachmentData);
        file_put_contents($save,$pdf);

        $pdf_url = get_site_url() . "/" . substr($save, strpos($save, "/wp-content") + 1); 
        $file = $dir.'Racuni/'.$pdf_invoice->Data->AttachmentFileName;

        // CALL FUNCTION FOR SENDING PDF
        $order->update_meta_data('minimax_delivery_order_id', true);
        $order->update_meta_data('_pdf', $pdf_invoice->Data->AttachmentData);
        $order->update_meta_data('_minimax_pdf_mail', $save);
        $order->update_meta_data('_minimax_pdf', $pdf_url);
        $order->save();
        $pdf_url = $order->get_meta('_minimax_pdf');

        // send_pdf_manually($order_post_id, $send ,$api,$pdf_url,$pdf_name,$file);
     }
     

     if (is_admin()) {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit; // Correct way to end an AJAX request
    }
}

function send_pdf_manually( $order_post_id, $send ,$api,$pdf_url,$pdf_name,$attachments) {


    global $send; global $api; global $pdf_url; global $pdf_name; global $file;
    
   /*  global $woocommerce; global $product;
    global $current_user;  global $wpdb;
    global $settings; */global $save;
    //$order = new WC_Order($order_post_id);
    $is_multisite = is_multisite();
    if ($is_multisite) {
        $order = get_order_from_multisite($order_post_id);
    } else {
        $order = wc_get_order($order_post_id);
    }

    $DatePaid = $order->get_meta('_paid_date');
    $datePaid = new DateTime($DatePaid);
    $time     = $datePaid->format(DateTime::ATOM);
  

    $content_type = function() { return 'text/html'; };
    add_filter( 'wp_mail_content_type', $content_type ); 

    if (get_option("minimax_email_skp","")) {
    $skp = get_option("minimax_email_skp","");
    }
    if (get_option(__("minimax_email_racun_zadeva","minimax"))) {
    $subject = get_option(__("minimax_email_racun_zadeva","minimax"));
    }
    if (get_option(__("minimax_email_racun_sporocilo","minimax"))) {
    $body_email = get_option(__("minimax_email_racun_sporocilo","minimax"));
    }
    $test = $order->add_order_note("Račun poslan na dan: " . $time . " na email " .  $order->get_billing_email() . "." );
    $order->update_meta_data('racun', "Poslano na dan:" . $time . " na email " . $order->get_billing_email() . ".");
    $order->save();
    $headers_email = array();
    $woocommerce_email_from_name = get_option('woocommerce_email_from_name');
    $from_email = get_option('woocommerce_email_from_address');
    if (!empty($skp)) {
        $headers_email[] = "BCC: {$skp}";
        
    }
    $headers_email[] = "From: {$woocommerce_email_from_name} <$from_email>";

    $placeholders = array('[orderid]', '[name]', '[surname]');
    $values = array($order->get_order_number(), $order->get_billing_first_name(), $order->get_billing_last_name());
    $subject = str_replace($placeholders, $values, $subject);
    
    $email_color = get_option('email_color_border');
    if (empty($email_color)) {
        $email_color = "#00558f";
    }
    $email_background = get_option('email_color_bg');
    if (empty($email_background)) {
        $email_background = "#f9f9f9";
    }

    $email_button_color = get_option('email_cololor_button_link');
    if (empty($email_button_color)) {
        $email_button_color = "#00558f";
    }
    $email_color_typo = get_option('email_color_typography');
    if (empty($email_color_typo)) {
        $email_color_typo = "#1a1a1a";
    }

    $dir = plugin_dir_path( __FILE__ );
    $message = "";
    $message .= '<html><body>';
    $message .= '<div style="background: #eee;">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" style="table-layout:fixed;background-color:#e5e5e5" id="bodyTable">';
    $message .= '<tbody><tr>';
    $message .= '<td style="padding-right:10px;padding-left:10px;" align="center" valign="top" id="bodyCell">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapperBody" style="max-width:600px">';
    $message .= '<tbody><tr>';
    $message .= '<td align="center" valign="top">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableCard" style="background-color:'.$email_background.';border-color:'.$email_background.';border-style:solid;border-width:0 1px 1px 1px;">';
    $message .= '<tbody><tr>';
    $message .= '<td style="background-color:'.$email_color.';font-size:1px;line-height:3px" class="topBorder" height="3">&nbsp;</td>';
    $message .= '</tr><tr>';
    $message .= '<td style="padding-top: 60px; padding-bottom: 20px;" align="center" valign="middle" class="emailLogo">';
    $message .= '<a href="#" style="text-decoration:none" target="_blank">';
    $message .= '<img border="0" src="' . get_option('logo_image_email') . '" style="width:100%;max-width:150px;height:auto;display:block" width="150">';
    $message .= '</a></td></tr>';
    $message .= '<tr><td style="padding-bottom: 5px; padding-left: 20px; padding-right: 20px;" align="center" valign="top" class="mainTitle">';
    $message .= '</td></tr>';
    $message .= '<tr><td style="padding-bottom: 30px; padding-left: 20px; padding-right: 20px;" align="center" valign="top" class="subTitle">';
    $message .= '</td></tr>';
    $message .= '<tr><td style="padding-left:20px;padding-right:20px" align="center" valign="top" class="containtTable ui-sortable">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableDescription" style="">';
    $message .= '<tbody><tr><td style="padding-bottom: 20px;" valign="top" class="description">';
    $message .=  str_replace($placeholders, $values, $body_email, $pdf_url);
    $message .= '</td></tr></tbody></table>';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="tableButton" style="">';
    $message .= '<tbody><tr><td style="padding-top:20px;padding-bottom:20px" align="center" valign="top">';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" align="center">';
    if (get_option('minimax_display_button')) {
    $message .= '<tbody><tr><td style="background-color:'.$email_button_color.'; padding: 12px 35px; border-radius: 50px;" align="center" class="ctaButton"> <a href=" '. site_url() .'" style="color:#fff;font-family:Poppins,Helvetica,Arial,sans-serif;font-size:13px;font-weight:600;font-style:normal;letter-spacing:1px;line-height:20px;text-transform:uppercase;text-decoration:none;display:block" target="_blank" class="text">Spletna trgovina</a>';
    }
    $message .= '</td></tr></tbody></table></td></tr></tbody></table></td></tr>';
    $message .= '<tr><td style="font-size:1px;line-height:1px" height="20">&nbsp;</td></tr>';
    $message .= '<table border="0" cellpadding="0" cellspacing="0" width="100%" class="space"><tbody><tr><td style="font-size:1px;line-height:1px" height="30">&nbsp;</td></tr></tbody></table>';
    $message .= '</tbody></table></td></tr></tbody></table></td></tr></tbody></table>';
    $message .= '</div>';
    $message .= '</html></body>';

    $save = $order->get_meta('_minimax_pdf');
    $file = $order->get_meta('_minimax_pdf_mail');

    $attachments = array($file);


   $email_sent =  wp_mail($order->get_billing_email(),$subject,$message,$headers_email,$attachments, $values);

     if ($email_sent) {
        return true; 
    } else {
        return false; 
    } 

?>
<?php 
    
}

 function send_pdf_automatic() {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }

    global $order_post_id;  global $settings; global $send; global $pdf_url;
    global $api; global $pdf_name; global $attachments;global $save;


    $order_post_id = sanitize_text_field($_POST['post_id']);
    $order_for_pdf = wc_get_order($order_post_id);

    $save = $order_for_pdf->get_meta('_minimax_pdf');
    $file = $order_for_pdf->get_meta('_minimax_pdf_mail');

    $attachments = $file;

    $result = send_pdf_manually($order_post_id, $send ,$api,$pdf_url,$pdf_name,$attachments);

    if ($result) {
        wp_send_json_success(['message' => 'Sporočilo je bilo uspešno poslano.']);
    } else {
        wp_send_json_error(['message' => 'Pri pošiljanju je prišlo do napake.']);
    }
}  


function MiniMax_box_content() {
    global $post_id;
    MiniMax_return_box_content($post_id);
} 

function media_uploader_enqueue() {
    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_register_script('media-uploader', plugins_url('media-uploader.js' , __FILE__ ), array('jquery'));
    wp_enqueue_script( 'my-script-handle', plugins_url('media-uploader.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
    wp_enqueue_script('media-uploader');
    wp_enqueue_script( 'tiny_mce' );
    wp_enqueue_script( 'wp-tinymce' );
    wp_enqueue_style( 'wp-admin' );
    $translation_array = array(
        'select_color_text' => __( 'New Text Here', 'minimax' ),
    );
    wp_localize_script( 'my-script-handle', 'myplugin_vars', $translation_array );

}

add_action('admin_enqueue_scripts', 'media_uploader_enqueue');
add_action( 'woocommerce_product_options_general_product_data', 'misha_option_group' );
 
function misha_option_group() {
    global $post;
    if ( isset( $_POST['minimax-dobavitelj'] ) ) {
        update_post_meta($post->ID, 'minimax-dobavitelj', sanitize_text_field( $_POST['minimax-dobavitelj'] ) );
    }
    get_post_meta($post->ID,'minimax-dobavitelj',true);
    ?>
    <div class="option_group">
        <h2>Minimax - Izdelek</h2>
        <p>Če imate spremembe na izdelku, lahko izdelek s klikom na spodnji gumb posodobite v MiniMaxu.</p>
            <form action="" method="POST" >
            <input type="hidden" name="minimax-orderId" value="<?= get_the_id();?>">
            <button type="submit" id="update_item" name="minimax-update-item" value="item_update" class="button button-primary" style="margin: 10px;">Posodobi izdelek</button>
        </form>
    </div>
<?php }

function update_items() {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }
    global $woocommerce; global $product; global $post; global $product_id;
    global $order_post_id;
    global $settings;  global $current_user;  global $wpdb;
    global $woocommerce; global $product; global $order_id;
    $order_post_id = $_POST["minimax-orderId"];

    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    );  
    $product = wc_get_product($order_post_id);
    $product_id = $product->get_id();
   
    $hash = md5($product_id);
    
    // Check if the product is a variable product
    if ($product->is_type('variable')) {
    
        $variation_id = $item->get_variation_id();
    
        $variation = wc_get_product($variation_id);

        // Use the SKU of the variation
        if (get_option('minimax_item_code_sku') && $variation && $variation->get_sku()) {
            $item_code = $variation->get_sku();
        } else {
        
            $hash = md5($variation_id);
            $item_code = substr($hash, 0, -2);
        }
        } else {
            $hash = md5($product_id);
            if (get_option('minimax_item_code_sku') && $product->get_sku()) {
                $item_code = $product->get_sku();
            } else {
                $item_code = substr($hash, 0, -2);
            }
        }

    $minimax_check_item = $api->check_code($item_code);
   
    if ($minimax_check_item) {
        $minimax_item_id = $minimax_check_item->ItemId;

        $changed = false;
        if ($product->get_name() !== $minimax_check_item->Name) {
            $changed = true;
            $minimax_check_item->Name = $product->get_name();

        }
        if ($product->get_price() !== $minimax_check_item->Price) {
            $changed = true;
            $minimax_check_item->Price = $product->get_price();

        }
        if ($changed) {

            $update_item = json_encode($minimax_check_item, JSON_PRETTY_PRINT);
            $update_item_api = $api->update_items($minimax_item_id,$update_item);     
        }
        
    }

    die();
}

add_action('admin_footer', 'update_item_minimax_javascript');

function update_item_minimax_javascript() {
    $test = 'Uspešno prenešeno';
    ?>
    <script type="text/javascript" >
        jQuery(document).ready(function ($) {
  
            jQuery("#update_item").on('click', function (e) {
                swal2({
                    title: 'Posodabljam izdelek!',
                    text: 'Prosim počakajte...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    onOpen: function () {
                        swal2.showLoading();
                        $.ajax(
                            {
                                url: '/wp-admin/admin-ajax.php',
                                data: {
                                    'action': 'update_items',
                                    'minimax-orderId': <?= get_the_id(); ?>,
                                },
                                type: 'POST',
                                success: function(data) {
                                swal2.close();
                                },
                                error: function(data) {
                                    alert(data.toString());
                                },
                            }
                        ).then(function() {
                        swal(
                            'Uspešno!',
                            'Izdelek je bil posodobljen',
                            'success'
                        );
                        })
                     }
                });
                e.preventDefault();
            });
        });
    </script> <?php
} 

// SYNC UPDATE UTEMS IN MINIMAX

function update_items_all($order_post_id) {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }

    global $woocommerce; global $product; global $post; global $product_id;
    global $order_post_id;
    global $settings;  global $current_user;  global $wpdb; 
    global $woocommerce; global $product; global $order_id;
    

    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    );  

    $product_list = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'post_status' => 'publish',

    );

    $product_display = new Wp_Query($product_list);

    $update_items = [];
    if ($product_display->have_posts()) {

        while ($product_display->have_posts()) {

        $product_display->the_post();
        $product = wc_get_product($product_display->post->ID);
        $hash = md5($product_display->post->ID);

        if (get_option('minimax_item_code_sku')) {
            if ($product->get_sku()) {
                $item_code = $product->get_sku();
            } else {
                $item_code = substr($hash, 0, -2);
            }
        } else {
            $item_code = substr($hash, 0, -2);
        }
        $minimax_check_item = $api->check_code($item_code);
        
       if ($minimax_check_item) {
            $minimax_item_id = $minimax_check_item->ItemId;

            $changed = false;

            if ($product->get_name() !== $minimax_check_item->Name || $product->get_price() != $minimax_check_item->Price) {
                $changed = true;
                $update_items[] = array(
                    'product_name' => $product->get_name(),
                    'product_price' => $product->get_price(),
                ); 
                $minimax_check_item->Name = $product->get_name();
                $minimax_check_item->Price = $product->get_price();
          
            } 
         
             if ($changed) {
                $update_item = json_encode($minimax_check_item, JSON_PRETTY_PRINT);
                $update_item_api = $api->update_items($minimax_item_id,$update_item);     
            }  
            
        }  
        }
    }


    wp_reset_postdata();
    
    if (!empty($update_items)) {
        
        echo '<table id="response" class="widefat">
        
        <thead>
            <tr>
                <th style="padding-left: 10px;" class="row-title">Zp.št.</th>
                <th class="row-title">Ime izdelka</th>
                <th class="row-title">Cena</th>
            </tr>
        </thead>
        <tbody>'; 
        $rows = 0;

         foreach ($update_items as $value) {
            $rows++;
      
            if ($rows%2 == 0) {
            echo '<tr style="background-color: #E0E0E0">';
            } else {
            echo '<tr style="background-color: #017eb4">'; 
            }
            echo "<td style='color: white; padding-left: 10px;'> ".$rows ."</td>";
            echo "<td style='color: white;'>{$value['product_name']}</td>";
            echo "<td style='color: white;'>{$value['product_price']}</td>";
            echo "</tr>";
 
        }
        
        echo '</tbody>';
    
    
        echo '<div class="notice notice-success is-dismissible" style="margin-left: 0px;">';
        echo '<p>Število posodobljenih izdelkov: <b>' . $rows . '</b>.</p>';
        echo '</div>';
    
        $date_show = date('Y-m-d'). ' ob ' . date('h:i:s') . ' uri';
        update_option('last_update', $date_show);
        echo '</table>';  
    } else {
               
        echo '<p class="notice notice-error is-dismissible">Izdelki nimajo sprememb<p>';
        exit();
        
        echo '</div>';
    }

    die();

    if (isset($_POST['minimax-update-item'])) {
        $date_update = date('Y-m-d H:i:s');
        echo "Zadnja posodobitev:" . $date_update; 
    }
}

    // SYNC STOCK FOR ITEM
    function sync_stock_from_minimax($order_post_id) {
    check_ajax_referer( 'minimax_action_nonce', 'security' );
    if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }

    global $woocommerce; global $product; global $post; global $product_id;
    global $order_post_id;
    global $settings;  global $current_user;  global $wpdb; 
    global $woocommerce; global $product; global $order_id;
    $api = new minimaxAPI(
        get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
        get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
    ); 

    $allSkus = new WP_Query(array(
        'post_type' => array('product','product_variation'), 
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ));

    // POST STOCK ENTRY TO MINIMAX
    $stock = array();

    //$order = new WC_Order($order_post_id);
    $store_data = array();
    $stocks = $api->getStockForItem_all();
 
    if ($allSkus->have_posts()) {
        $value_stock[] = array();
        foreach ($stocks->Rows as $single_stock) {
            $value_stock[] = [
             'product_stock_name' => $single_stock->ItemName,
             'product_stock_hash' => $single_stock->ItemCode,
             'product_stock_quantity' => $single_stock->Quantity,
         ];

        } 

        while ($allSkus->have_posts()) {

        $allSkus->the_post();
        $product = wc_get_product($allSkus->post->ID);
        $product_id = $product->get_id();
        $stock_quantity = $product->get_stock_quantity();
        $store_data[] = $product;
    
        $hash = md5($allSkus->post->ID);
        
        if (get_option('minimax_item_code_sku')) {
            if ($product->get_sku()) {
                $item_code = $product->get_sku();
            } else {
                $item_code = substr($hash, 0, -2);
            }
        } else {
            $item_code = substr($hash, 0, -2);
        }

        $item_response = $api->check_code($item_code);

        $error = false;
        if ($stocks == NULL || '') {
            $error = true;
        }
    
        $key = array_search($product->get_name(), array_column($value_stock, 'product_stock_name'));
        $data = false;
        foreach($value_stock as $value){
               
			if ($value['product_stock_hash'] == $product->get_sku()) {
                if (array_key_exists('product_stock_quantity',$value)) {

                   $product->set_stock_quantity($value['product_stock_quantity']);
                   $product->save();
                 }
             }
        }

        }
    }

        if ($error) {
            echo 'error_sync';
            exit();
        } else {
    
        if (count($value_stock) != 0) {
        
        echo '<table id="response_stock" class="widefat">
        <thead>
            <tr>
                <th class="row-title" style="width: 100px; padding-left: 10px;">Zp.št</th>
                <th class="row-title">Ime izdelka</th>
                <th class="row-title">Zaloga</th>
            </tr>
        </thead>
        <tbody>'; 

    $rows = 0;
     foreach ($value_stock as $key => $value) {

        if (empty($value)) {
            continue;
        }
        $rows++;
        if($rows%2 == 0) {
  
        if ($rows <= 1) {
        $str = ' izdelek';
        } else if ($rows == 2) {
        $str = ' izdelka';
        } else {
        $str = ' izdelkov';
        } 
    
        echo '<tr style="background-color: #E0E0E0">';
        } else {
            echo '<tr style="background-color: #017eb4">'; 
        }
        echo "<td style='color: white; padding-left: 10px;'> ".$rows ."</td>";
        echo "<td style='color: white;'>{$value['product_stock_name']}</td>";
        echo "<td style='color: white;'>{$value['product_stock_quantity']}</td>";
        echo "</tr>";
    }      
    echo '</tbody>';
    echo '<p style="width:100%; color: green; padding-bottom: 20px; padding-top: 20px;" class="widefat">Zaloga je bila posodobljena za <b>' . $rows . '</b>'.$str.'.</p>';
    echo '</table>';  
    }
      }
    die();

    }

    // LOGIN FORM VALIDATION
    function login() {
        check_ajax_referer( 'minimax_action_nonce', 'security' );
        if ( ! current_user_can( 'manage_woocommerce' ) ) { wp_die( -1, 403 ); }
        global $woocommerce; global $product; global $post; global $product_id;
        global $order_post_id;
        global $settings;  global $current_user;  global $wpdb;
        global $woocommerce; global $product; global $order_id;

        update_user_meta($current_user->ID, "MINIMAXclientContextUserId", $_POST["clientContextUserId"]);
        update_user_meta($current_user->ID, "MINIMAXclientContextUserSecret", $_POST["clientContextUserSecret"]);
        update_user_meta($current_user->ID, "MINIMAXclientContextUsername", $_POST["clientContextUsername"]);
        update_user_meta($current_user->ID, "MINIMAXclientContextUserPass", $_POST["clientContextUserPass"]);
         
        $api = new minimaxAPI(
            get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID),
            
        );  

        if (isset($_POST["MINIMAXclientContextUsername"])) {
            update_user_meta($current_user->ID, "MINIMAXclientContextUsername", $_POST["clientContextUsername"]);;
        }


    $errorMSG = "";

    $empty = false;
    if (empty($_POST["clientContextUsername"])) {

       $errorMSG .= "<li style='color: red;'>Uporabniško ime je obvezno</li>";
    } else {

        $clientContextUsername = $_POST["clientContextUsername"];
    }

    /* clientContextUserPass */
    if (empty($_POST["clientContextUserPass"])) {
       $errorMSG .= "<li style='color: red;'>Geslo je obvezno</li>";
    } else {
        $clientContextUserPass = $_POST["clientContextUserPass"];
    }

    if($errorMSG){

        echo json_encode(['code'=>404, 'msg'=>$errorMSG]);
   
        exit;
    }

    } 


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Add a custom section in the WooCommerce admin order page
function get_current_site_order( $order ) {
    if ( is_multisite() ) {
        $current_site_id = get_current_blog_id();
    } else {
        $current_site_id = 1; 
    }

    ?>
    <input type="hidden" name="current_site_id" id="current_site_id" value="<?= $current_site_id;?>">
    <?php 
}
add_action( 'woocommerce_admin_order_data_after_order_details', 'get_current_site_order' );


function display_log_invoice($order_post_id) {
    global $order_post_id;
    $order_post_id = $_POST["minimax-log-invoice"];
    
    $order_log = wc_get_order($order_post_id);
    $log_invoice_data = $order_log ? $order_log->get_meta('log_invoice_data') : '';
    echo "<pre>";

    echo "</pre>";

    // Za debugg se lahko zakomentira delete_post_meta, da ni ptorebno vsakic klikniti na izdaj racun.
    if ($order_log) {
        $order_log->delete_meta_data("log_invoice_data");
        $order_log->save();
    }
    die();

}

add_action('woocommerce_thankyou', 'auto_trigger_create_post_after_order', 10, 1);

function auto_trigger_create_post_after_order($order_id) {

    if (!get_option("check_minimax_automatic_invoice")) {
        return;
    }
    if (!$order_id) {
        return;
    }
    
    // Fetch order safely
    $order = wc_get_order($order_id);

    $order_id = $order->get_id();

    if (!$order) {
       
        return;
    }

    global $settings;  global $current_user;  global $wpdb; 
    global $woocommerce; global $product; 

    $customer_id = $order->get_customer_id();
    $order_status = $order->get_status();

    // From plugin settitngs get select value for order status. 
    $select_order_status = trim(get_option('Minimax_order_status'),"wc-");

    if ($order_status == $select_order_status) {

        $api = new minimaxAPI(
            get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
        );  

        // Trigger invoice creation
        minimax_create_invoice($order_id);
    }
}


?>