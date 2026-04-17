<?php

namespace IdealnoRs;

use IdealnoRs\Config;


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
        'toplevel_page_idealnors-xml' => 'idealnors-xml',
        'idealno-rs-xml_page_idealnors-settings' => 'idealnors-settings',
        'idealno-rs-xml_page_idealnors-licenca' => 'ceneje-licenca'
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
        add_action('admin_menu', array( $this, 'addMenuRs'));
        add_action('admin_menu', array( $this, 'addSettingsSubMenuRs'));
        add_action('admin_menu', array( $this, 'addSubMenuRs'));
    }

    /**
     * Generate WP menu
     */
    public function addMenuRs(){

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
    public function addSettingsSubMenuRs(){

        add_submenu_page(
            'idealnors-xml',
            'Settings',
            'Settings',
            'manage_options',
            'idealnors-settings',
            array($this, 'loadView')
        );
    }


    /**
     * Generate WP submenu
     */
    public function addSubMenuRs(){

        add_submenu_page(
            'idealnors-xml',
            'License',
            'License',
            'manage_options',
            'idealnors-licenca',
            array($this, 'loadView')
        );
    }

    /**
     * Load view from views
     */
    function loadView(){

        // current_filter() also returns the current action
        $current_views = $this->views[current_filter()];

        // Include name from views
        include(dirname(__FILE__).'/views/'.$current_views.'.php');

    }
}