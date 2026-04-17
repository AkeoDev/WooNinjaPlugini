<?php

use IdealnoBa\Config;
use IdealnoBa\License;
use IdealnoBa\ViewGenerator;

if (!defined('ABSPATH')) exit;

// Check user auth level
if (!current_user_can('manage_options')) return;


// Try to allocate lang
$currentlang = get_option('woo_idealnoba_defaultlang', 'ba');

// Try to get a license key
$currentlicense = get_option('woo_idealnoba_key', '');

// Get license status
$status = get_option('woo_idealnoba_activated', 'false');

// Customer info
$customerinfo = unserialize(get_option('woo_idealnoba_buyerinfo'));

// Check license submit
if(isset($_POST['woo_idealnoba_action_license'])){

    // Get key
    $key = trim($_POST['woo_idealnoba_key']);

    // Try to deactivate old license
    $deactivate = License::deactivateLicense();

    // Check if deactivation was successfull
    if($deactivate['success'] === true){

        // Try to activate current license
        $activate = License::activateLicense($key);

        // Check if activation was successfull
        if($activate['success'] === true){

            // Set trial expired
            update_option('woo_idealnoba_trial', 'false');

            // Print success message
            echo ViewGenerator::successMessage(Config::$translates[17][$currentlang]);

        }else{

            // Include errors
            include_once('license-errors.php');

            // Print returned error or system error
            echo ViewGenerator::errorMessage($errors[$activate['message']][$currentlang]);
        }
        
    }else{

        // Print error 
        echo ViewGenerator::errorMessage($deactivate['message']);
    }

}

?>



<div class="wrap">

    <h2><?php echo esc_html(Config::$name); ?> - License</h2>

    <form action="" method="post">
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row">
                    <label for="default_role">KEY</label>
                </th>
                <td>
                    <input class="regular-text code" type="text" value="<?php echo esc_attr($currentlicense); ?>" name="woo_idealnoba_key">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row" valign="top">
                    Status 
                </th>
                <td>
                    <?php if ($status !== false && $status == 'true') { ?>
                        <span style="color:green; font-weight:800">Active</span>
                        <?php
                    } else {
                        ?>
                        <span style="color:red; font-weight:800">Inactive</span>
                    <?php } ?>
                </td>
            </tr>
        </table>
        <input type="submit" class="button-primary woocommerce-save-button" name="woo_idealnoba_action_license" value="<?php echo esc_attr(Config::$translates[9][get_option('woo_idealnoba_defaultlang', 'ba')]); ?>"/>
    </form>

    <?php

        // Check if status is available
        if($status === 'true'):

    ?>
        <table class="wp-list-table widefat fixed striped table-view-list posts" style="width: 50%; margin-top: 30px;">
            <thead>
                <tr>
                    <th><?php echo esc_html(Config::$translates[18][$currentlang]); ?></th>
                    <th>Email</th>
                    <th><?php echo esc_html(Config::$translates[19][$currentlang]); ?></th>
                    <th><?php echo esc_html(Config::$translates[20][$currentlang]); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td scope="row">
                        <?php echo esc_html($customerinfo['username']); ?>
                    </td>
                    <td scope="row">
                        <?php echo esc_html($customerinfo['email']); ?>
                    </td>
                    <td scope="row">
                        <?php echo esc_html($customerinfo['expiration']); ?>
                    </td>
                    <td scope="row">
                        <?php echo esc_html($customerinfo['payment_id']); ?>
                    </td>
                </tr>
            </tbody>        
        </table>

    <?php

        endif;

    ?>

</div>