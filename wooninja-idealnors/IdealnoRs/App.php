<?php

namespace IdealnoRs;

use IdealnoRs\ViewGenerator;
use IdealnoRs\MenuGenerator;
use IdealnoRs\Config;


if (!defined('ABSPATH')) exit;

/**
 * IdealnoRs main class 
 */

class App{

    /**
     * Create an contructor
     */
    public function __construct()
    {
        // Add menu item to the sidebar
        $menu = new MenuGenerator();

        $menu->init();

        // Check if options are loaded
        $this->checkOptions();
    }

    /**
     * Check if WooCommerce is loaded
     */
    public static function checkIfWooLoaded(){
        return in_array('woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ))) ? true : false;
    }

    /**
     * Check if options are loaded, if not set them
     */
    public function checkOptions(){

        // Get trough our plugin options
        foreach(Config::$wpoptions as $option){

            // Check if option exists
            if(get_option($option[0], false) === false){

                // Set this missing option
                add_option($option[0], $option[1]);
            }
        }
    }

}