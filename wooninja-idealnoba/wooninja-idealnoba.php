<?php
/**
* 
* Plugin Name: WooNinja - Idealno.ba XML
* Plugin URI: https://wooninja.si
* Description: Jednostavan izvoz proizvoda u Idealno.ba trgovinu
* Version: 1.0.0
* Author: Humanfrog d.o.o.
* Author URI: https://wooninja.si
* License: GPLv2 or later
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: wooninja-idealnoba
*/


use IdealnoBa\App;
use IdealnoBa\ViewGenerator;
use IdealnoBa\Config;
use IdealnoBa\Hooks;




/**
 * Prevent direct access to the plugin directory
 */
if (!defined('ABSPATH')){
    die;
}


/**
 * Autoload classes
 */
include_once( plugin_dir_path( __FILE__ ) . '/IdealnoBa/autoload.php');


// Init class
$idealnoba = new App();


/**
 * Check if WooCommerce is loaded
 */
if(!$idealnoba->checkIfWooLoaded()){

    // Set action
    add_action('admin_notices', function(){
        echo ViewGenerator::errorMessage(Config::$translates[0][get_option('woo_idealnoba_defaultlang', 'ba')]);
    });
}


// Register hooks
$hooks = new Hooks();