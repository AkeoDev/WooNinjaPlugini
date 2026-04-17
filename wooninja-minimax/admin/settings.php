<?php 
global $woocommerce;
global $plugin_page;
global $current_user;

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!defined('SMMiniMax_DEMO')) {
    define('MINIMAXAPI_URL', 'https://minimmax.si');
} else {
    define('MINIMAXAPI_URL', SMMINIMAX_DEMO);
}


define('MINIMAX_PLUGIN_VERSION', '2.2');
define('MINIMAX_PLUGIN', __FILE__);
define('MINIMAX_PLUGIN_BASENAME', plugin_basename(MINIMAX_PLUGIN));
define('MINIMAX_PLUGIN_NAME', trim(dirname(MINIMAX_PLUGIN_BASENAME), '/'));
define('MINIMAX_PLUGIN_DIR', untrailingslashit(dirname(MINIMAX_PLUGIN)));
define('MINIMAX_PLUGIN_URL', plugin_dir_url(__FILE__));
 include_once ABSPATH . '/wp-content/plugins/minimax/api/api.php';

 $api = new minimaxAPI(
    get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
    get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
    get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
    get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID)
);  
$command = null;

?>

      <?php 

 if (isset($_REQUEST["command"])) {
    $command = $_REQUEST["command"];
 }

    if ($command == 'shrani-podatke') {
    $tip = $_POST["Submit"];

    if ("Shrani" == $tip) {

        update_user_meta($current_user->ID, "MINIMAXclientContextUserId", sanitize_text_field($_POST["clientContextUserId"]));
        update_user_meta($current_user->ID, "MINIMAXclientContextUserSecret", sanitize_text_field($_POST["clientContextUserSecret"]));
        update_user_meta($current_user->ID, "MINIMAXclientContextUsername", sanitize_text_field($_POST["clientContextUsername"]));
        update_user_meta($current_user->ID, "MINIMAXclientContextUserPass", sanitize_text_field($_POST["clientContextUserPass"]));        

        $api = new minimaxAPI(
            get_the_author_meta("MINIMAXclientContextUserId", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserSecret", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID),
            get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID),
            true
        ); 

        if($api->authead == false) {
            echo '<h3 style="color: red;">Napaka</h3>';
        }
   
        $stevilcenje = $api->getStevilcenje();
     
        if (isset($stevilcenje)) {
            update_option("MiniMaxStevilcenje", $stevilcenje);
        }

        $nacinPlacila = $api->getNacinPlacila();

        if (isset($nacinPlacila)) {
            update_option("SMMiniMaxNacinPlacila", $nacinPlacila);
        }
 
    }
}

 if ($command == 'shrani-eposta') {
    $tip = $_POST["Submit"];
    if ("Shrani" == $tip) {
        update_option("minimax_email_skp", sanitize_email($_POST["minimax_email_skp"]));
        update_option("minimax_email_racun_zadeva", sanitize_text_field($_POST["minimax_email_racun_zadeva"]));
        //update_option("minimax_email_racun_sporocilo", $_POST["minimax_email_racun_sporocilo"]);
        update_option( 'minimax_email_racun_sporocilo', wp_kses_post( $_POST['minimax_email_racun_sporocilo'] ) );
        update_option('logo_image_email', esc_url_raw($_POST['logo_image_email']));
        update_option('email_color_border', sanitize_hex_color($_POST['email_color_border']));
        update_option('email_color_bg', sanitize_hex_color($_POST['email_color_bg']));
        update_option('email_cololor_button_link', sanitize_hex_color($_POST['email_cololor_button_link']));
        update_option('email_color_typography', sanitize_hex_color($_POST['email_color_typography']));
        if (isset($_POST['minimax_display_button'])) {
            update_option('minimax_display_button', true);
        } else {
            update_option('minimax_display_button', false);
        }

    }
}

if ($command == "shrani-nastavitve") {
    $tip = $_POST["Submit"];
    if ("Shrani" == $tip) {
        if (isset($_POST["MiniMax_invoice_stevilcenje"])) {
            update_option("MiniMax_invoice_stevilcenje", sanitize_text_field($_POST["MiniMax_invoice_stevilcenje"]));

        }

        if (isset($_POST["MiniMax_invoice_organizacija"])) {
            update_option("MiniMax_invoice_organizacija", sanitize_text_field($_POST["MiniMax_invoice_organizacija"]));

        }

        if (isset($_POST["MiniMax_analitika"])) {
        update_option("MiniMax_analitika", sanitize_text_field($_POST['MiniMax_analitika']));
        }
        if (isset($_POST["complited_status_invoice"])) {
            update_option("complited_status_invoice", sanitize_text_field($_POST['complited_status_invoice']));
            }
        if (isset($_POST["minimax-stock"])) {
            update_option("minimax-stock", sanitize_text_field($_POST['minimax-stock']));
            }
        if (isset($_POST["MiniMax_skladisce"])) {
            update_option("MiniMax_skladisce", sanitize_text_field($_POST['MiniMax_skladisce']));
            }
        if (isset($_POST["MiniMax_employer"])) {
            update_option("MiniMax_employer", sanitize_text_field($_POST['MiniMax_employer']));
            }
        if (isset($_POST["MiniMax_invoice_template"])) {
            update_option("MiniMax_invoice_template", sanitize_text_field($_POST['MiniMax_invoice_template']));
            }

        if (isset($_POST["MiniMax_invoice_template_do"])) {
            update_option("MiniMax_invoice_template_do", sanitize_text_field($_POST['MiniMax_invoice_template_do']));
            }
        if (isset($_POST["MiniMax_product_type"])) {
            update_option("MiniMax_product_type", sanitize_text_field($_POST["MiniMax_product_type"]));
        }
        if (isset($_POST["Minimax_order_status"])) {
            update_option("Minimax_order_status", sanitize_text_field($_POST["Minimax_order_status"]));
        }
        if (isset($_POST['check_minimax_automatic_invoice'])) {
            update_option('check_minimax_automatic_invoice', true);
        } else {
            update_option('check_minimax_automatic_invoice', false);
        }

        if (isset($_POST["minimax_delivery_text"])) {
            update_option("minimax_delivery_text", sanitize_text_field($_POST['minimax_delivery_text']));
            }
        if (isset($_POST["minimax_measurement"])) {
            update_option("minimax_measurement", sanitize_text_field($_POST['minimax_measurement']));
        }
        if (isset($_POST['minimax_item_code_sku'])) {
            update_option('minimax_item_code_sku', true);
        } else {
            update_option('minimax_item_code_sku', false);
        }

        if (isset($_POST['minimax_manual_vat_rate'])) {
            update_option('minimax_manual_vat_rate', true);
        } else {
            update_option('minimax_manual_vat_rate', false);
        }


        // PAYMENT METHOD
         if (isset($_POST['total_payment_option'])) {
            update_option('total_payment_option', intval($_POST['total_payment_option']));
        }

        $total_number_of_payment_methods = get_option('total_payment_option', 0);

        $getPaymentMethod = $api->getNacinPlacila();
        // Loop through each payment method to save the selected MiniMax ID and corresponding name
        for ($i = 0; $i < $total_number_of_payment_methods; $i++) {
            if (isset($_POST['MiniMax_payment_method_' . $i], $_POST['woo_payment_method_title_' . $i])) {
                // Save MiniMax payment method ID
                $minimax_payment_method_id = sanitize_text_field($_POST['MiniMax_payment_method_' . $i]);
                update_option('MiniMax_payment_method_' . $i, $minimax_payment_method_id);

                // Retrieve MiniMax payment method name based on the selected ID
                $minimax_payment_method_name = '';
                foreach ($getPaymentMethod->Rows as $minimax_payment_method) {
                    if ($minimax_payment_method->PaymentMethodId == $minimax_payment_method_id) {
                        $minimax_payment_method_name = $minimax_payment_method->Name;
                        break;
                    }
                }

                  // Save the MiniMax payment method ID
            update_option('MiniMax_payment_method_' . $i, $minimax_payment_method_id);
            
            // Save the MiniMax payment method name
            update_option('MiniMax_payment_method_name_' . $i, $minimax_payment_method_name);

            // Save the WooCommerce payment method title for reference
            update_option('WooCommerce_payment_method_for_MiniMax_' . $i, sanitize_text_field($_POST['woo_payment_method_title_' . $i]));
            } 
        }
 
        
    }
}

 if ($command == "shrani-nastavitve-status") {
    $tip = $_POST["Submit"];
    if ("Shrani" == $tip) {
        if (isset($_POST["complited_status_invoice"])) {
            update_option("complited_status_invoice", sanitize_text_field($_POST['complited_status_invoice']));
            }
    }
} 
 


////////////////////////////////////////////////////////////////////
$stevilcenje = get_option("MiniMaxStevilcenje", array());
$nacinPlacila = get_option("SMMiniMaxNacinPlacila", array());
$nacinPlacilaMapirano = get_option("SMMiniMaxNacinPlacilaMapirano", array());

$last_update = get_option("last_update",array());
////////////////////////////////////////////////////////////////////


?>
<h1>Minimax</h1>
<?php 
if (isset($api->params['client_secret'])) { 
    ?>
    <h2>Pozdravljeni, <?= $api->params['username'];?></h2>
<?php } ?>



<div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <!-- main content -->
            <div id="post-body-content">
                <div class="meta-box-sortables ui-sortable">
                    <div class="postbox">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->
                        <h2 class="hndle"><span><?php esc_attr_e('Dostop do vašega MiniMax računa', 'SMMiniMax');?></span>
                        </h2>
                        <div class="inside">
                            <form id="login" method="post" action="<?php echo esc_url(menu_page_url('mm-MiniMax', false)); ?>" autocomplete="off">
                            <!-- VALIDATION -->    
                            <div id="validation"></div>
                            <!-- BEFORE AJAX SEND -->
                            <div id='loader' style='display: none;'>
                                    <p>Preverjanje podatkov.</p>
                                    <div class="loadingspinner"></div>
                            </div>
                                
                                <div class="alert alert-danger display-error" style=""></div>
                                <table class="form-table">
                                     <input type="hidden" name="command" value="shrani-podatke"> 
                                    <tr valign="top">
                                        <th scope="row">Uporabniško ime</th>
                                        <td><input type="text" autocomplete=off style="width: 100%;" id="clientContextUsername" name="clientContextUsername" value="<?php echo esc_attr(get_the_author_meta("MINIMAXclientContextUsername", $current_user->ID));?>" /></td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">Uporabniško geslo</th>
                                        <td><input type="password" autocomplete=off style="width: 100%;" id="clientContextUserPass" name="clientContextUserPass" value="<?php echo esc_attr(get_the_author_meta("MINIMAXclientContextUserPass", $current_user->ID));?>" /></td>
                                    </tr>
                                </table>
                                <button type="submit" name="submit" class="button button-primary" value="Shrani">Shrani spremembe in pridobi podatke</button>
                                </p>
                            </form>
                            <!-- AJAX VALIDATION -->
                            <script type="text/javascript" >
                                jQuery(document).ready(function ($) {
                                    jQuery("#login").on('submit', function (e) {
                                        e.preventDefault();
                                        var formData = {
                                            'action' : 'login',
                                            clientContextUserId: $("#clientContextUserId").val(),
                                            clientContextUserSecret: $("#clientContextUserSecret").val(),
                                            clientContextUsername: $("#clientContextUsername").val(),
                                            clientContextUserPass: $("#clientContextUserPass").val(),
                                        };
                                        
                                        $.ajax({
                                            type: 'POST',
                                            url: '/wp-admin/admin-ajax.php',
                                            dataType: "json",
                                            data: formData,
                                            beforeSend: function(){
                                                // Show image container
                                                $("#loader").show();
                                            },
                                            success : function(data){
                                                if (data.code == 404) {
                                                jQuery("#validation").html(data.msg);
                                                } else {
                                              //  jQuery("#validation").html(data.msg);
                                              location.reload();
                                                jQuery("#validation").html('<h3 style="color: green;">Uspešna prijava</h3>');
                                            //    jQuery("#settings-block").load("#minimax-data")
                                                jQuery("#settings-block").show();
                                                }
                                            },
                                            error: function(data) {
                                                jQuery("#validation").html(data.msg);
                                           /*      if ($('clientContextUsername').val() != '') {
                                                    $('clientContextUsername').
                                                } */
                                                jQuery("#validation").html('<h3 style="color: red;">Pri povezavi z MiniMaxom je prišlo do napake. Preverite ali so vnešeni podatki pravilni.</h3>');
                                                
                                                jQuery("#settings-block").hide();
                                                
                                            },
                                            complete:function(data){
                                                // Hide image container
                                                jQuery("#validation").html(data);
                                                $("#loader").hide();
                                            }
                                         })
                                      
                                    });
                                });
                            </script>
                            <style>
                                #loader {
                                    display: flex;
                                    gap: 20px;
                                }
                                .loadingspinner {
                                    pointer-events: none;
                                    width: 2.5em;
                                    height: 2.5em;
                                    border: 0.4em solid transparent;
                                    border-color: #eee;
                                    border-top-color: #79A85D;
                                    border-radius: 50%;
                                    animation: loadingspin 1s linear infinite;
                                }
                                .hide_settings {
                                    display: none;
                                }

                                @keyframes loadingspin {
                                    100% {
                                            transform: rotate(360deg)
                                    }
                                }
                            </style>
                        </div>
                        <!-- .inside -->
                    </div>
                    <!-- .postbox -->
                </div>
                <!-- .meta-box-sortables .ui-sortable -->
            </div>
            <!-- post-body-content -->
            <!-- sidebar -->
            <div id="postbox-container-1" class="postbox-container">
                <div class="meta-box-sortables">
                    <div class="postbox" style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">
                        <div class="handlediv" title="Click to toggle"><br></div>
                        <!-- Toggle -->
                        <h2 class="hndle"><span><?php
                                esc_attr_e(
                                        'Hitra pomoč', 'SMMiniMax',
                                );
                                ?></span></h2>
                        <div class="inside">
                            <p><?php esc_attr_e('Za pravilno delovanje, je potrebno izpolniti prijavne podatke (Uporabniško ime in geslo). Pred začetkom, je potrebno v SAOP uporabniškem računu kreirati novo aplikacijo.', 'SMMiniMax');?></p>
                            <a href="https://login.saop.si/Profile" target="_BLANK">SAOP uporabniški račun</a>
                        </div>
                        <!-- .inside -->
                    </div>
                    <!-- .postbox -->
                </div>
                <!-- .meta-box-sortables -->
            </div>
            <!-- #postbox-container-1 .postbox-container -->
        </div>
        <!-- #post-body .metabox-holder .columns-2 -->
        <br class="clear">
    </div>
    <!-- #poststuff -->

	<!-- NASTAVITVE -->
    <?php

    if(!empty($api->params['client_id'])) {
        global $hide_block;
  
    ?>
    <div id="settings-block" class=<?php echo $hide_block;?> style="display: none;">
	<div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- main content -->
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php esc_attr_e('Privzete vrednosti', 'SMMiniMax');?></span>
                                </h2>
                                <div class="inside">
                                    <form id="minimax-data" method="post" action="<?php echo esc_url(menu_page_url('mm-MiniMax', false));?>">
                                        <table class="form-table">
                                            <input type="hidden" name="command" value="shrani-nastavitve">
                                            <tr valign="top">
                                                <th scope="row">Izberite organizacijo</th>
                                                    <td>
                                                    <?php 
                                                    $org_ids = $api->get_data;
                                                    ?>
                                                    <select name="MiniMax_invoice_organizacija"  style="width: 100%;">
                                                            <option value="/">/</option>
                                                            <?php
                                                            if ($org_ids) {
                                                                foreach ($org_ids->Rows as $org_id) {
                                                               
                                                                    $get_org_id = $org_id->Organisation->ID;
                                                                    $get_org_name = $org_id->Organisation->Name;

                                                                    if ($get_org_id) {
                                                                       ?>
                                                                         <option <?php
                                                                        if ($get_org_id == get_option("MiniMax_invoice_organizacija", 0)) {
                                                                            echo "selected='selected' ";
                                                                        };
                                                                        ?>value="<?php echo $get_org_id;
                                                                        ?>"><?php echo $get_org_name;
                                                                        ?></option>
                                                                        <?php 
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                        </td>
                                                    <?php 
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                            
                                                <th scope="row">Številčenje za račun</th>
                                                <td>
                                                    <?php 
                                                    $stevilcenje = $api->getStevilcenje();
                                                    ?>
                                                    <select name="MiniMax_invoice_stevilcenje"  style="width: 100%;">
                                                        <option value="/">/</option>
                                                        <?php
                                                       foreach ($stevilcenje->Rows as $value) {
                                                   
                                                            if ($value->Document == 'IR') {
                                                                ?>
                                                                <option <?php
                                                                if ($value->DocumentNumberingId == get_option("MiniMax_invoice_stevilcenje", 0)) {
                                                                    echo "selected='selected' ";
                                                                };
                                                                ?>value="<?php echo $value->DocumentNumberingId;
                                                                ?>"><?php echo $value->Name;
                                                            
                                                                ?></option>
                                                                <?php
                                                            }
                                                        } 
                                                        
                                                        ?>
                                                    </select>
                                                </td>
                                            </tr>
                                            <?php 
                                            $analytics = $api->analytics();
                                       
                                           ?>

                                           <tr valign="top">
                                           <th scope="row">Analitika</th>
                                           <td>
                                
                                               <select name="MiniMax_analitika"  style="width: 100%;">
                                                   <option value="/">/</option>
                                                   <?php
                                                foreach ($analytics->Rows as $value) {
                                                
                                                           ?>
                                                           <option <?php
                                                           if ($value->AnalyticId == get_option("MiniMax_analitika", 0)) {
                                                               echo "selected='selected' ";
                                                           }
                                                           ;
                                                           ?>value="<?php echo $value->AnalyticId;
                                                           ?>"><?php echo $value->Name;
                                                           ?></option>
                                                           <?php
                                             
                                                   } 
                                                   ?>
                                               </select>
                                           </td>
                                       </tr>
                                       <tr valign="top">
                                                    <th scope="row">Skladišče</th>
                                                    <td>
                                                    <?php 
                                                    $skladisca = $api->getSkladisca();
                                                    ?>
                                                        <select name="MiniMax_skladisce"  style="width: 100%;">
                                                            <option value="/">/</option>
                                                            <?php
                                                                foreach ($skladisca->Rows as $value) {
                                                                ?>
                                                                <option <?php
                                                                if ($value->WarehouseId == get_option("MiniMax_skladisce", 0)) {
                                                                    echo "selected='selected' ";
                                                                }
                                                                ;
                                                                ?>value="<?php echo $value->WarehouseId;
                                                                ?>"><?php echo $value->Name;
                                                                ?></option>
                                                                <?php
                                                            } 
                                                            ?>
                                                        </select>
                                                    </td>
                                               
                                                </tr>
                                                <tr valign="top">
                                                    <th scope="row">
                                                        Blagajnik (opcijsko)
                                                    </th>
                                                    <td>
                                                        <?php 
                                                        $employers = $api->GetEmployees();
                      
                                                        ?>
                                                        <select name="MiniMax_employer" style="width:100%;" id="">
                                                            <option value="/">/</option>
                                                            <?php 
                                                            foreach ($employers->Rows as $employer) {
                                                                ?>
                                                                <option <?php 
                                                                if ($employer->EmployeeId == get_option("MiniMax_employer",0)) {
                                                                    echo "selected='selected'";
                                                                };
                                                                ?>value="<?php echo $employer->EmployeeId;?>"><?php echo $employer->FirstName;?></option>
                                                                <?php 
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <hr>
                                                <tr valign="top" style="border-top:1px solid #dcdcde;">
                                                    <td scope="row" style="padding-left:0px;">
                                                        <p>Za izdajo računov je izpis izdanih računov in dobavnic obvezno.</p>
                                                    </td>
                                                </tr>
                                                <tr valign="top">
                                                    
                                                    <th scope="row">Izpis - izdani račun</th>
                                                        <td>
                                                            <?php 
                                                            $IssuedInvoiceReport = $api->get_report_templates();

                                                            ?>
                                                            <select name="MiniMax_invoice_template" style="width:100%;">
                                                                <option value="/">/</option>
                                                                <?php 
                                                                foreach ($IssuedInvoiceReport->Rows as $issuedInvoice_ir) {

                                                                    if($issuedInvoice_ir->DisplayType == 'IR') {
                                                                    ?>
                                                                    <option <?php 
                                                                    if ($issuedInvoice_ir->ReportTemplateId == get_option("MiniMax_invoice_template",0)) {
                                                                        echo "selected='selected'";
                                                                    };?>value="<?php echo $issuedInvoice_ir->ReportTemplateId;?>"><?php echo $issuedInvoice_ir->Name;?></option>
                                                                    <?php 
                                                                } 
                                                            }?>
                                                            </select>
                                                        </td>
                                                </tr>
                                                <tr valign="top">
                                                    <th scope="row">Tip računa</th>
                                                        <td>
                                                            <select name="MiniMax_invoice_template_do" style="width:100%;">
                                                                <option value="/">/</option>
                                                                <?php 
                                                                foreach ($IssuedInvoiceReport->Rows as $issuedInvoice_do) {
                                                                    if ($issuedInvoice_do->DisplayType == 'DO') {
                                                                        ?>
                                                                        <option <?php
                                                                        if($issuedInvoice_do->ReportTemplateId == get_option("MiniMax_invoice_template_do",0)) {
                                                                            echo "selected='selected'";
                                                                        };?> value="<?php echo $issuedInvoice_do->ReportTemplateId;?>"><?php echo $issuedInvoice_do->Name;?>
                                                                        </option>
                                                                        <?php 
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </td>
                                                </tr>
                                                <tr valign="top" style="border-top:1px solid #dcdcde">
                                                    <th scope="row">Tip artikla, obvezen podatek. Izbiramo lahko med
                                                        <p>Nastavitev je generalna za vse artikle.</p>
                                                    </th>
                                                        <td>
                                                            <select name="MiniMax_product_type" style="width:100%;">
                                                                <option value="I">Izdelek</option>
                                                                <option value="B">Blago</option>
                                                                <option value="M">Material</option>
                                                                <option value="P">Polizdelek</option>
                                                                <option value="S">Storitve</option>
                                                                <option value="A">Predplacila</option>
                                                                <option value="AS">Predplačila za storitve</option>
                                                            </select>
                                                        </td>
                                                </tr>
                                                <tr valign="top" style="border-top:1px solid #dcdcde">
                                                    <th scope="row">Avtomatsko izdaj račun glede na status naročila</th>
                                                        <td style="width: 200px">
                                                            <span style="color:red";>Pred uporabo samodejnega načina, svetujemo, da skrbo uredite nastavitve, ter sprva naredite izdajo računa ročno. </span></br>
                                                            <?php
                                                            $minimax_set_automatic_invoice = get_option('check_minimax_automatic_invoice', false);
                                                            $checked_automatic_invoice = ($minimax_set_automatic_invoice == true) ? 'checked' : '';
                                                            ?>
                                                            <label>Uporabi avtomatski izvoz</label>
                                                            <input type="checkbox" id="check_minimax_automatic_invoice" name="check_minimax_automatic_invoice" value="1" <?= $checked_automatic_invoice ;?>>
                                                        </td>
                                                        <td style="width: 30%;">
                                                            <?php
                                                            $order_statuses = wc_get_order_statuses();
                                                            
                                                            if($order_statuses){
                                                                ?>
                                                                <select name="Minimax_order_status" style="width:100%;">
                                                                    <?php
                                                                    foreach ($order_statuses as $single_status => $label) {
                                                                    
                                                                        ?>
                                                                        <option <?php if ($single_status == get_option("Minimax_order_status", 0)) {
                                                                            echo "selected='selected' ";
                                                                        };;?> name="" value="<?= $single_status;?>"><?= $label;?></option>
                                                                        <?php
                                                                    }
                                                                    ?>
                                                                </select>
                                                                <?php
                                                            }
                                                            ?>
                                                        </td>
                                                </tr>

                                                <tr valign="top" style="border-top:1px solid #dcdcde">
                                                    <th scope="row">Ime dostave
                                                    <p>(Prikaz dostave na računu)</p>
                                                    </th>
                                                    
                                                    <td>
                                                        <input type="text" name="minimax_delivery_text" value="<?php echo esc_attr(get_option('minimax_delivery_text',"")); ?>" placeholder="Dostava" style="width: 100%;">
                                                    </td>

                                                </tr>
                                                <tr valign="top" style="border-top:1px solid #dcdcde">
                                                    <th scope="row">Merska enota
                                                    <p>(Prikaz merske enote ob artiklu. Privzet zapis - 'Kom')</p>
                                                    </th>
                                                    
                                                    <td>
                                                        <input type="text" name="minimax_measurement" value="<?php echo esc_attr(get_option('minimax_measurement',"")); ?>" placeholder="Merska enota" style="width: 100%;">
                                                    </td>

                                                </tr>
                                                <tr valign="top">
                                                <th scope="row">Preverjanje artiklov preko <b>SKU</b></th>
                                                <td>
                                                    <p>Za preverjanje artiklov med Woocommercom in Minimaxom preko SKU izdelka.</p><br>
                                                    <i>V primeru, da imate artikle v Minimaxu že vnešene, lahko v izogib podvajanju artiklov, vnesete SKU izdelka pri Šifri artikla v Minimaxu. </i><br>
                                                    <i>V primeru, da SKU ne uporabljate, lahko pustite odkljukano.</i><br>
                                                    <?php 
                                                    $minimax_option_sku = get_option('minimax_item_code_sku', false);
                                                    $checked_sku = ($minimax_option_sku == true) ? 'checked' : '';

                                                    echo '<input type="checkbox" id="minimax_item_code_sku" name="minimax_item_code_sku" value="1" ' . $checked_sku . ' />';
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row">Ročni vnos stopenj DDV</th>
                                                <td>
                                                    <p>Nastavitev urejate v nastavitvah organizacije v vašem Minimax računu</p>
                                                    <i>V primeru, da imate v Minimaxu ročni vnos stopenj DDV izbran, je potrebno obkljukati izbiro</i><br>

                                                    <?php 
                                                    $minimax_option_manual_vat_rate = get_option('minimax_manual_vat_rate', false);
                                                    $minimax_manual_vat_rate = ($minimax_option_manual_vat_rate == true) ? 'checked' : '';

                                                    echo '<input type="checkbox" id="minimax_manual_vat_rate" name="minimax_manual_vat_rate" value="1" ' . $minimax_manual_vat_rate . ' />';
                                                    ?>
                                                </td>
                                            </tr>
                                            <tr valign="top" style="border-top:1px solid #dcdcde">
                                                    <th scope="row">Plačilne metode
                                                    <p>Za pravilni prikaz izpisa plačilne metode na računu, je potrebno povezati plačilne metode, ki so na voljo v Minimaxu z plačilnimi metodami v Woocommercu.</p>
                                                    </th>
                                                    <td>
                                                    <?php 
                                                    $gateways = WC()->payment_gateways->get_available_payment_gateways();
                                                    $enabled_gateways = [];
                                                    if ($gateways) {
                                                        foreach ($gateways as $gateway) {

                                                            if ($gateway->enabled == 'yes') {
                                                                $enabled_gateways[] = [
                                                                    'payment_method_id' => $gateway->id,
                                                                    'payment_method_title' => $gateway->title,
                                                                ];
                                                              
                                                  
                                                            }
                                                        }
                                                    }
                                                    $getPaymentMethod = $api->getNacinPlacila();
                                                    if ($enabled_gateways) {
                                                        $single_payment_row = 0;
                                                        foreach ($enabled_gateways as $single_payment) {
                                                            ?>
                                                            <p>WooCommerce Payment Method: <b><?= esc_html($single_payment['payment_method_title']); ?></b></p>
                                                            <p>Minimax Payment Method</p>
                                                    
                                                            <!-- MiniMax payment method select dropdown -->
                                                            <select name="MiniMax_payment_method_<?php echo esc_attr($single_payment_row); ?>" style="width:100%;">
                                                                <option value="/">/</option>
                                                                <?php
                                                                foreach ($getPaymentMethod->Rows as $minimax_payment_method) {
                                                                    $minimax_payment_method_id = $minimax_payment_method->PaymentMethodId;
                                                                    $minimax_payment_method_name = $minimax_payment_method->Name;
                                                                    $selected = ($minimax_payment_method_id == get_option('MiniMax_payment_method_' . $single_payment_row, '/')) ? "selected='selected'" : "";
                                                                    ?>
                                                                    <option <?= $selected; ?> value="<?= esc_attr($minimax_payment_method_id); ?>">
                                                                        <?= esc_html($minimax_payment_method_name); ?>
                                                                    </option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </select>
                                                            
                                                            <!-- Hidden input for MiniMax method name (filled on save) -->
                                                            <input type="hidden" name="woo_payment_method_title_<?php echo esc_attr($single_payment_row); ?>" value="<?php echo esc_attr($single_payment['payment_method_title']); ?>">
                                                            
                                                            <?php 
                                                            $single_payment_row++;
                                                        }
                                                        ?>
                                                        <input type="hidden" name="total_payment_option" value="<?php echo esc_attr($single_payment_row); ?>">
                                                        <?php 
                                                    }
                                                    ?>
                        
                                                    <?php 
                                                    
                                                    ?>
                                                    </td>
                                                </tr>
                                            <!-- NUMBERING ID -->
                                        </table>
                                        <button type="submit" name="Submit" class="button button-primary" value="Shrani">Shrani</button>
                                    </form>            
                                </div>
                                <!-- .inside -->
                                
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->
                    </div>
                    <!-- post-body-content -->
                    <!-- sidebar -->
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="postbox" style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php
                                        esc_attr_e(
                                                'Hitra pomoč', 'SMMiniMax'
                                        );
                                        ?></span></h2>
                                <div class="inside">
                                    <p><?php esc_attr_e('Iz spustnih seznamov lahko izberete nastavitve, ki jih imate nastavljene v vašem MiniMax računu.', 'SMMiniMax');
                                        ?></p>
                                </div>
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables -->
                    </div>
                    <!-- #postbox-container-1 .postbox-container -->
                </div>
                <!-- #post-body .metabox-holder .columns-2 -->
                <br class="clear">
            </div>
                <!-- SINHRONIZACIJA -->
                <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- main content -->
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php esc_attr_e('Sinhronizacija', 'SMMiniMax');?></span>
                                </h2>
                                <hr>
                                <div class="inside">
                                        <table class="form-table">
                                            <input type="hidden" name="command" value="shrani-eposta">
                                            <tr valign="top">
                                                <th scope="row">Posodobi izdelke</th>
                                                <td>
                                                    <?php 
                                                    
                                                    $product_list = array(
                                                        'post_type' => 'product',
                                                        'posts_per_page' => 20,
                                                        'post_status' => 'publish',
                                                
                                                    );
                                                
                                                    $product_display = new Wp_Query($product_list);
                                                
                                                    $update_items = [];
                                                    if ($product_display->have_posts()) {
                                                
                                                        while ($product_display->have_posts()) {
                                                
                                                        $product_display->the_post();
                                                        $product = wc_get_product($product_display->post->ID);
                                                        $product_id = $product_display->post->ID;

                                                        $stockItem = $api->stock();
                                                        }

                                                    }
                                                    ?>
                                                <p>Možnost posodobitve izdelkov. V minimaxu se posodobi: <b>Ime izdelka in Cena</b></p>
                                                <form id="sync" action="" method="POST" >
                                                <?php global $date; global $date_show;?>
                                                <?php 
                                                if (!empty(get_option('last_update'))) {
                                                echo '<p>Izdelki so bili na zadnje posodobljeni dne: <b>'. get_option('last_update',$date_show).'</b></p>';
                                                } else {
                                                    echo '<p>Izdelki še niso bili posodobljeni.</p>';
                                                }
                                                ?>
                                               
                                                <input id="last_update" type="hidden" name="last_update" value="<?= esc_attr(get_option('last_update',$date_show)); ?>">
                                                    <button type="submit" id="update_item_all" name="minimax-update-item" value="item_update" class="button button-secondary" style="margin-top: 10px;">Posodobi izdelek</button>
                                                  
                                                </form> 
                                                <?php 
        
                                                ?>
                                                <script type="text/javascript" >
                                                        jQuery(document).ready(function ($) {
                                                        jQuery("#update_item_all").on('click', function (e) {
                                                            var update_date = jQuery("#last_update").val()
                                                            swal2({
                                                                title: 'Posodabljam izdelke!',
                                                                text: 'Prosim počakajte...',
                                                                allowOutsideClick: false,
                                                                allowEscapeKey: false,
                                                                onOpen: function () {
                                                                    swal2.showLoading();
                                                                    $.ajax({
                                                                        url: '/wp-admin/admin-ajax.php',
                                                                        data: {
                                                                        'action': 'update_items_all',
                                                                        'last_update' : update_date,
                                                                        },
        
                                                                    type: 'GET',
                                                                    success: function(data) {
                                                    
                                                                        if (data == "<p class='notice notice-error is-dismissible'>Izdelki nimajo sprememb<p>") {
                                                                            
                                                                            var error = '<p style="color: red;">Prišlo je do napake! Za izdelke ni novih posodobitev.</p>';
                                                                            $('#result').html(error); 
                                                                        } else {
                                                                            $('#result').html(data);
                                                                        }

                                                                    $('#result').html(data);
                                                                    //$('#last_update').val('juhu');
                                                                    swal2.close();
                                                                    },
                                                                error: function(data) {
                                                                alert(data.toString());
                                                            },
                                                        }).then(function(data) {
                                                            if (data == '<p class="notice notice-error is-dismissible">Izdelki nimajo sprememb<p>') {
                                                        swal(
                                                        'Napaka!',
                                                        'Za izdelke ni novih posodobitev.',
                                                        'error'
                                                        );
                                                    } else {
                                                        swal(
                                                        'Uspešno!',
                                                        'Izdelki so bili posodobljeni',
                                                        'success'
                                                        )
                                                    }
                                                        })
                                                    }
                                                });
                                                e.preventDefault();
                                                });
                                            }); 
                                        </script>
                                        <div id ="result"></div> 
                                                </td>
                                            </tr>
                                        </table>
                                     
                                </div>
                                <!-- SYNC STOCK -->
                                <div class="inside">
                                        <table class="form-table">
                                            <input type="hidden" name="command" value="shrani-eposta">
                                            <tr valign="top">
                                                <th scope="row">Posodobi zalogo iz minimaxa v woocommerce</th>
                                                <td>
                                                <p>Za usklajevanje zaloge je potrebno v MiniMaxu imeti začetno stanje zaloge.</p>
                                                <form id="sync" action="" method="POST" >
                                                    <input type="hidden" name="product_id" value="">
                                                    <button type="submit" id="update_item_stock" name="minimax-update-item_stock" value="item_update_stock" class="button button-secondary" style="margin-top: 10px;">Uskladi zalogo</button>
                                                </form> 
                                                <?php 
        
                                                ?>
                                                <script type="text/javascript" >
                    
                                                        jQuery(document).ready(function ($) {
                                                            jQuery("#update_item_stock").on('click', function (e) {
                                                                
                                                                swal2({
                                                                    title: 'Usklajujem zalogo!',
                                                                    text: 'Prosim počakajte...',
                                                                    allowOutsideClick: false,
                                                                    allowEscapeKey: false,
                                                                    onOpen: function () {
                                                                        swal2.showLoading();
                                                                        $.ajax({
                                                                            url: '/wp-admin/admin-ajax.php',
                                                                            data: {
                                                                            'action': 'sync_stock_from_minimax',
                                                                            },
                                                                        type: 'GET',
                                                                        
                                                                        success: function(data) {
                                                                            
                                                                        if (data == "error_sync") {
                                                                            
                                                                            var error = '<p style="color: red;">Prišlo je do napake! Preverite nastavitve in vodenje zaloge v MiniMaxu.</p>';
                                                                            $('#result_stock').html(error); 
                                                                        } else {
                                                                            $('#result_stock').html(data);
                                                                        }

                                                                        swal2.close();
                                                                        },
                                                                    error: function(data) {
                                                        
                                                                    alert(data.toString());
                                                                        
                                                                },
                                                              
                                                        }).then(function(data) {
                                                            if (data == 'error_sync') {
                                                                swal(
                                                                        'Napaka!',
                                                                        'Zaloge ni bilo mogoče prenesti.',
                                                                        'error'
                                                                        );   
                                                            } else {
                                                                swal(
                                                                    'Uspešno!',
                                                                    'Zaloga je bila posodobljena.',
                                                                    'success'
                                                                    ); 
                                                                }
                                                                
                                                            })
                                                        }
                                                });
                                                e.preventDefault();
                                                });
                                            }); 
                                        </script>
                                        <div id ="result_stock"></div> 
                                                </td>
                                            </tr>
                      
                                        </table>
                                     
                                </div>
             
                           
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->
                    </div>
                    <!-- post-body-content -->
                    <!-- sidebar -->
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="postbox" style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php
                                        esc_attr_e(
                                                'Hitra pomoč', 'SMQuibi'
                                        );
                                        ?></span></h2>
                                <div class="inside">
                                    <p>Sinhronizacija vam omogoča posodobitev vseh izdelkov v Minimax. Posodobitev je na voljo le za izdelke, ki so že že izdali v Minimax poleg računa</p>
                                </div>
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables -->
                    </div>
                    <!-- #postbox-container-1 .postbox-container -->
                </div>
                <!-- #post-body .metabox-holder .columns-2 -->
                <br class="clear">
            </div>
            <!-- MAIL -->

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <!-- main content -->
                    <div id="post-body-content">
                        <div class="meta-box-sortables ui-sortable">
                            <div class="postbox">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php esc_attr_e('Elektronska pošta', 'SMMiniMax');?></span>
                                </h2>
                                <div class="inside">
                                    <form method="post" action="<?php echo esc_url(menu_page_url('mm-MiniMax', false));
                                        ?>">
                                        <h4>Splošne nastavitve</h4>
                                        <table class="form-table">
                                            <input type="hidden" name="command" value="shrani-eposta">
                                            <tr valign="top">
                                                <th scope="row">Barvna shema</th>
                                                    <td>
                                                        <label for="">Obroba</label>
                                
                                                        <input type="text" class="email_color_border" id="email_color_border" name="email_color_border" value="<?php echo esc_attr(get_option( 'email_color_border' )); ?>" />
                                                    </td>
                                                </tr>
                                            <tr valign="top">
                                                <th></th>
                                                    <td>
                                                        <label for="">Povezava</label>
                                                      
                                                        <input type="text" class="email_cololor_button_link" id="email_cololor_button_link" name="email_cololor_button_link" value="<?php echo esc_attr(get_option( 'email_cololor_button_link' )); ?>" />
                                                    </td>
                                                </tr>
                                            <tr valign="top">
                                                <th></th>
                                                    <td>
                                                        <label for="">Ozadje</label>
                                                 
                                                        <input type="text" class="email_color_bg" id="email_color_bg" name="email_color_bg" value="<?php echo esc_attr(get_option( 'email_color_bg' )); ?>" />
                                                    </td>
                                                </tr>
                                            <tr valign="top">
                                                <th></th>
                                                    <td>
                                                        <label for="">Pisava</label>
                                                
                                                       <input type="text" class="email_color_typography" id="email_color_typography" name="email_color_typography" value="<?php echo esc_attr(get_option( 'email_color_typography' )); ?>" />
                                                    </td>
                                                </tr>
                                            <tr valign="top">
                                            <hr>
                                                <th scope="row">Skrita kopija</th>
                                                <td>
                                                    <input type="text" name="minimax_email_skp" value="<?php echo esc_attr(get_option("minimax_email_skp", "")); ?>" style="width: 100%;">
                                                </td>
                                            </tr>
                                        </table>
                                        <hr>
                                        <h4>Besedilo računa</h4>
                                        
                                        <table class="form-table">
                                            <tr valign="top">
                                                <th scope="row">Zadeva</th>
                                                <td>
                                                    <input type="text" name="minimax_email_racun_zadeva" value="<?php echo esc_attr(get_option("minimax_email_racun_zadeva", "")); ?>" style="width: 100%;">
                                                </td>
                                            </tr>
                                            <tr valign="top">
                                                <th scope="row"> Logotip</th>
                                                <td>
                                                    <input id="logo_image_email" type="text" name="logo_image_email" value="<?php echo esc_attr(get_option('logo_image_email')); ?>" />
                                                    <input id="upload_image_button" type="button" class="button-primary" value="Naloži logotip" />
                                                </td>
                                            </tr>
                                                <th scope="row">Sporočilo</th>
                                                <td>
                                                    <?php 
                                                      // Get the current content of the custom field
                                                 // Get the current content of the custom field
                                                    $content = get_option( 'minimax_email_racun_sporocilo' );

                                                    // Output the WYSIWYG editor
                                                    wp_editor( $content, 'minimax_email_racun_sporocilo', array(
                                                        'textarea_name' => 'minimax_email_racun_sporocilo',
                                                        'media_buttons' => false,
                                                        'textarea_rows' => 5,
                                                    ) );

                                                    ?>
                                            
                                                </td>
                                            </tr>
                                            <th scope="row"> Vklop / izklop povezave do spletne trgovine</th>
                                                <td>
                                                    <?php 
                                                    $minimax_option = get_option('minimax_display_button', false);
                                                    $checked = ($minimax_option == true) ? 'checked' : '';

                                                    echo '<input type="checkbox" id="minimax_display_button" name="minimax_display_button" value="1" ' . $checked . ' />';
                                                    ?>
                                                </td>
                                        </table>
                                        <button type="submit" name="Submit" class="button button-primary" value="Shrani">Shrani</button>
                                    </form>
                                </div>
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables .ui-sortable -->
                    </div>
                    <!-- post-body-content -->
                    <!-- sidebar -->
                    <div id="postbox-container-1" class="postbox-container">
                        <div class="meta-box-sortables">
                            <div class="postbox" style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%)">
                                <div class="handlediv" title="Click to toggle"><br></div>
                                <!-- Toggle -->
                                <h2 class="hndle"><span><?php
                                        esc_attr_e(
                                                'Hitra pomoč', 'SMQuibi'
                                        );
                                        ?></span></h2>
                                <div class="inside">
                                    <p>V besedilu lahko uporabimo sledeče značke, ki se bodo samodejno zamenjale z besedilom:<ul><li>[orderid] - številka naročila</li><li>[name] - ime</li><li>[surname] - priimek</li></ul><br>
                                    Emailu lahko prilagodite barvno shemo in mu s tem lahko zamenjate barvo ozadja, pisave, obrobe in barvo povezave.
                                </p>
                                </div>
                                <!-- .inside -->
                            </div>
                            <!-- .postbox -->
                        </div>
                        <!-- .meta-box-sortables -->
                    </div>
                    <!-- #postbox-container-1 .postbox-container -->
                </div>
                <!-- #post-body .metabox-holder .columns-2 -->
                <br class="clear">
            </div>
            <?php 
    } 
//}?>
</div>
            <div class="minimax-footer" style="background: linear-gradient(116.97deg, #D2DE26 0%, #79A85D 90.13%); padding: 10px 20px 10px 20px;">
            <img  src="https://wooninja.si/wp-content/uploads/2022/01/WooNinjaFB_logo.png" style="width:100%;max-width:150px;height:auto;display:block; margin-left: auto; margin-right: auto;" width="150">
                <h3 style="text-align: center">Wooninja.si</h3>
            </div> 

