​<?php

use IdealnoRs\Config;
use IdealnoRs\ViewGenerator;

if (!defined('ABSPATH')) exit;

// Check user auth level
if (!current_user_can('manage_options')) return;

// Try to allocate lang
$currentlang = get_option('woo_idealnors_defaultlang', 'rs');

// Check if we have posted to the settings
if(isset($_POST['ceneje_settings'])){

    // Get lang from POST request
    $lang = $_POST['woo_idealnors_defaultlang'];

    // Check do we have this page set in config
    if(!array_key_exists($lang, Config::$languages)){

        // Add admin notice
        add_action('admin_notices', function(){

            // Print error message with limits
            echo ViewGenerator::errorMessage(Config::$translates[6][Config::$default_lang]);
        });


    }else{

        update_option('woo_idealnors_defaultlang', $lang);

        // Add admin notice
        add_action('admin_notices', function(){

            global $lang;

            // Print error message with limits
            //echo ViewGenerator::successMessage(Config::$translates[7][Config::$default_lang]); Config::$languages[$lang];
        });
    }

    // Refresh page
    wp_redirect($_SERVER['HTTP_REFERER']);

}

?>

<div class="wrap">

    <h2><?php echo esc_html(Config::$name); ?> - Settings</h2>

    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="default_role">Plugin language</label>
                </th>
                <td>
                    <select name="woo_idealnors_defaultlang">
                        <?php

                            foreach(Config::$languages as $key => $lang):

                                ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php echo $currentlang === $key ? 'selected="selected"' : ''; ?>><?php echo esc_html($lang); ?></option>
                                <?php
                                
                            endforeach;
                        ?>
                    </select>
                </td>
            </tr>
        </table>
        <input type="submit" class="button-primary woocommerce-save-button" name="ceneje_settings" value="<?php echo esc_attr(Config::$translates[9][get_option('woo_idealnors_defaultlang', 'rs')]); ?>"/>
    </form>
</div>