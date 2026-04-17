<?php

namespace IdealnoBa;

use IdealnoBa\Config;


if (!defined('ABSPATH')) exit;



/**
 * Generate WP menu
 */
class MenuGenerator{

    /**
     * Views array list
     * 
     * Map filter to view
     */
    protected $views = array(
        'toplevel_page_idealnoba-xml' => 'idealnoba-xml',
        'idealno-ba-xml_page_idealnoba-settings' => 'idealnoba-settings',
        'idealno-ba-xml_page_idealnoba-licenca' => 'idealnoba-licenca'
    );

    /**
     * Construct WP menu for this plugin
     */
    public function __construct()
    {
    }

    /**
     * Generate admin menu 
     */
    public function init(){

        // Hook action
        add_action('admin_menu', array( $this, 'addMenuBa'));
        add_action('admin_menu', array( $this, 'addSettingsSubMenuBa'));
        add_action('admin_menu', array( $this, 'addSubMenuBa'));
    }

    /**
     * Generate WP menu
     */
    public function addMenuBa(){

        add_menu_page(
            __(Config::$name), 
            __(Config::$name), 
            'manage_options', 
            Config::$plugin_prefix,
            array($this, 'loadView'),
            'https://wooninja.si/wp-content/uploads/2021/10/WooNina_icon_16.png'
        );

    }


    /**
     * Generate WP submenu
     */
    public function addSettingsSubMenuBa(){

        add_submenu_page(
            'idealnoba-xml',
            'Settings',
            'Settings',
            'manage_options',
            'idealnoba-settings',
            array($this, 'loadView')
        );
    }


    /**
     * Generate WP submenu
     */
    public function addSubMenuBa(){

        add_submenu_page(
            'idealnoba-xml',
            'License',
            'License',
            'manage_options',
            'idealnoba-licenca',
            array($this, 'loadView')
        );
    }

    /**
     * Load view from views
     */
    function loadView(){

        // current_filter() also returns the current action
        $current_views = $this->views[current_filter()];

        //die(current_filter());

        // Include name from views
        include(dirname(__FILE__).'/views/'.$current_views.'.php');

    }
}