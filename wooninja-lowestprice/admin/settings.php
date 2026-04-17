<?php 
 //include_once ABSPATH . '/wp-content/plugins/lowestprice/lowest-price.php';
 global $is_wc_active;
 create_table_lowestprice();
 if ( $is_wc_active == true) {
    $enteredAndValid = intval(get_option('woo_lowestPrice_license_entered_and_valid'));
    if($enteredAndValid == 1) {
        //archive_prices_function();
    }
 } else {
     // WooCommerce is NOT enabled!
     echo '<div class="notice notice-error is-dismissible"><p>' . 'Za delovanje vtičnika <b>' . EDD_LOWEST_PRICE_ITEM_NAME . '</b>je potrebno imeti aktiviran Woocommerce'.'</p></div>';
 }
?>
<h1>Najnižja cena v zadnjih 30 dneh (PID Direktiva)</h1>
<p>Vtičnik vam omogoča prikaz najnižje cene v zadnjih 30 dneh.</p>
<hr>

<?php 

$command = null;

$msg = '';
if (isset($_REQUEST["command"])) {
   $command = $_REQUEST["command"];
}

    if ($command == "shrani") {
        $tip = $_POST["Submit"];
        if ("Shrani" == $tip) {
         
            if (isset($_POST["display_lowest_price_default"]) && ($_POST["display_lowest_price_default"] == "on")) {
                update_option("display_lowest_price_default", 1);
                
            } else {
                update_option("display_lowest_price_default", 0);
            }

            if (isset($_POST["display_lowest_price_product_page"]) && ($_POST["display_lowest_price_product_page"] == "on")) {
                update_option("display_lowest_price_product_page", 1);
                update_option("display_lowest_price_default", 0);
            } else {
                update_option("display_lowest_price_product_page", 0);

            }

            // Display message if lower price is equal to current price

            if (isset($_POST["lowest_price_format_equal_show"]) && ($_POST["lowest_price_format_equal_show"] == "on")) {
                update_option("lowest_price_format_equal_show", 1);
            } else {
                update_option("lowest_price_format_equal_show", 0);
            }

            // Use shortcode
            if (isset($_POST["lowest_price_shortcode_use"]) && ($_POST["lowest_price_shortcode_use"] == "on")) {
                update_option("lowest_price_shortcode_use", 1);
                update_option("display_lowest_price_default", 0);
                update_option("display_lowest_price_product_page", 0);
            } else {
                update_option("lowest_price_shortcode_use", 0);
            }

            if (isset($_POST["lowest_price_format"])) {
                update_option( 'lowest_price_format', sanitize_text_field($_POST['lowest_price_format']) );
            }
            if (isset($_POST["lowest_price_format_equal"])) {
                update_option( 'lowest_price_format_equal', sanitize_text_field($_POST['lowest_price_format_equal']) );
            }
            if (isset($_POST["lowest_price_format_variable"])) {
                update_option( 'lowest_price_format_variable', sanitize_text_field($_POST['lowest_price_format_variable']) );
            }

            if (isset($_POST["lowest_price_format_color"])) {
                update_option("lowest_price_format_color", sanitize_hex_color($_POST['lowest_price_format_color']));
                }
        }
    }
    
?>
<div id="poststuff">
    <div id="post-body" class="metabox-holder columns-2">
        <!-- main content -->
        <div id="post-body-content">
            <div class="meta-box-sortables ui-sortable">
                <div class="postbox">
                    <div class="handlediv" title="Click to toggle"><br></div>
                            <div class="inside">
                                <form id="lowest-price-data" method="post" action="<?php echo esc_url(menu_page_url('lowestPrice-options', false));?>">
                                <input type="hidden" name="command" value="shrani">
                                    <table class="form-table">
                                        <input type="hidden" name="command" value="shrani">
                                            <tr valign="top">
                                                <th scope="row">Nastavitve</th>
                                                <td>

                                                <!-- Display default product price -->
                                                
                                                <input type="checkbox" name="display_lowest_price_default" <?php echo get_option( 'display_lowest_price_default' ) == '1'  ? 'checked' : ''; ?> >
                                                Prikaži najnižjo ceno v zadnjih <b>30 dneh.</b><br><br>
                                        
                                                <input type="checkbox" name="display_lowest_price_product_page" <?php echo get_option( 'display_lowest_price_product_page' ) == '1'  ? 'checked' : ''; ?>>
                                                Omogoča prikaz najnižje cene le na posameznemu izdelku<br>
                                                <br>
                                                <hr>

                                                <!-- Edit product price format -->
                                                <input type="text" name="lowest_price_format" value="<?php echo esc_attr(get_option('lowest_price_format')); ?>">
                                                Prikaz besedila poleg najnižje cene. <br><br>

                                                <!-- Display message for equal price-->
                                                <input type="checkbox" name="lowest_price_format_equal_show" <?php echo get_option( 'lowest_price_format_equal_show' ) == '1'  ? 'checked' : ''; ?>>
                                               
                                                <input type="text" name="lowest_price_format_equal" placeholder="Najnižja cena v zadnjih 30 dneh je enaka trenutni." value="<?php echo esc_attr(get_option('lowest_price_format_equal')); ?>">
                                                Prikaz besedila, če je najnižja cena enaka trenutni <br><br>

                                                <!-- Display message for variable products -->
                                                <input type="text" name="lowest_price_format_variable" placeholder="Najnižja cena v zadnjih 30 dneh variabilnih izdelkov" value="<?php echo esc_attr(get_option('lowest_price_format_variable')); ?>">
                                                Prikaz besedila za variabilne izdelke. <br><br>

                                                <!-- Change color -->
                                                <input type="text" name="lowest_price_format_color" value="<?php echo esc_attr(get_option('lowest_price_format_color')); ?>" placeholder="#fffffff">
                                                Barva pisave.
                                                <hr>

                                                <!-- Shortcode -->
                                                <input type="checkbox" name="lowest_price_shortcode_use" <?php echo get_option( 'lowest_price_shortcode_use' ) == '1'  ? 'checked' : ''; ?> >
                                                Shortcode 
                                                <b>[display_lowest_price]</b>

                                                </td>
                                            </tr>
                               
                                    </table>
                                        <button type="submit" name="Submit" class="button button-primary" value="Shrani">Shrani</button>
                                    </form>  
                                    <table class="form-table">
                                        <tr valign="top">
                                           <hr>
                                            <th scope="row">Shranjevanje cen: <br>
                                            POMEMBNO:
                                            <td><p>Za shranjevanje cen je pri prvi uporabi potrebno klikniti na gumb Shrani cene.</p>
                                                <button id="start-batch" class="button-secondary">Shrani cene</button>
                                                <div id="progress-info">
                                                <b><span id="progress-percentage-lowestprice">Shranjevanje izdelkov: 0%</span></b><br>
                                                <?php 
                                                $processed_count = get_option('processed_product_count', 0); 
                                                if ($processed_count <= 0): ?>
                                                    <b><span id="products-stored-count">Število izdelkov: 0</span></b>
                                                <?php else: ?>
                                                    <b>Število izdelkov: <?php echo $processed_count; ?></b>
                                                <?php endif; ?>
                                            </div>

                                            </td>
                                             
                                        </tr> 
                                    </table>
                                    <style>
                                        #start-batch {
                                            margin-top: 20px;
                                            margin-bottom: 20px;
                                        }
                                    </style>
                                    <script type="text/javascript">
                                jQuery(document).ready(function($) {
                                $('#progress-percentage-lowestprice').hide();
                                let productsProcessedSoFar = 0;  // Initializing the variable

                                $("#start-batch").click(function() {
                                    productsProcessedSoFar = 0;  // Resetting it to zero when you start the batch process
                                    processNextBatch(0);
                                    $('#progress-percentage-lowestprice').show();
                                });
                                                                                                                                
                                function processNextBatch(lastID) {
                                    $.ajax({
                                        type: 'POST',
                                        url: '/wp-admin/admin-ajax.php',
                                        data: {
                                            action: 'process_batch',
                                            last_processed_id: lastID,
                                            products_processed_so_far: productsProcessedSoFar,
                                        },
                                        dataType: 'json',  // Specify the expected data type of the response
                                        success: function(response) {
                                            // Updating the percentage display text
                                            $("#progress-percentage-lowestprice").text('Shranjevanje izdelkov: '+ response.percent.toFixed(2) + "%");

                                            // Update the products stored count
                                            $("#products-stored-count").text("Število izdelkov: " + response.products_stored);
   
                                            // Update the productsProcessedSoFar with the number processed in this batch
                                            productsProcessedSoFar += response.processed_this_batch;

                                            // If not done, process the next batch
                                            if (response.percent < 100) {
                                                processNextBatch(response.last_id);
                                            }
                                        },
                                        error: function(jqXHR, textStatus, errorThrown) {
                                            console.error("AJAX Error: " + textStatus, errorThrown);
                                        }
                                    });
                                }
                            });


                                </script>         
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
                                                'Hitra pomoč', 'lowestPrice-options'
                                        );
                                        ?></span></h2>
                                <div class="inside">
                                    <p><?php esc_attr_e('Nastavitve vam omogočajo prikazovanje in urejanje formata. Pri uporabi shortcode, je potrebno odznačiti Prikaz najnižje cene.', 'lowestPrice-options');
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