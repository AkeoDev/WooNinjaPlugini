<?php
/**
* 
* Plugin Name: WooNinja - Idealno.rs XML
* Plugin URI: https://wooninja.si
* Description: Jednostavan nacin izvoza proizvoda u Idealno.rs
* Version: 1.0.0
* Author: Humanfrog d.o.o.
* Author URI: https://wooninja.si
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wooninja-idealnors
*/


use IdealnoRs\App;
use IdealnoRs\ViewGenerator;
use IdealnoRs\Config;
use IdealnoRs\Hooks;




/**
 * Prevent direct access to the plugin directory
 */
if (!defined('ABSPATH')){
    die;
}


/**
 * Autoload classes
 */
include_once( plugin_dir_path( __FILE__ ) . '/IdealnoRs/autoload.php');


// Init class
$idealnors = new App();


/**
 * Check if WooCommerce is loaded
 */
if(!$idealnors->checkIfWooLoaded()){

    // Set action
    add_action('admin_notices', function(){
        echo ViewGenerator::errorMessage(Config::$translates[0][get_option('woo_idealnors_defaultlang', 'rs')]);
    });
}


// Register hooks
$hooks = new Hooks();