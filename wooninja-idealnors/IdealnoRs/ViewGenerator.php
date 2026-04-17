<?php

namespace IdealnoRs;

use IdealnoRs\Config;

if (!defined('ABSPATH')) exit;

/**
 * Helptext view generator
 */
class ViewGenerator{

    public function __construct()
    {
        
    }

    /**
     * Generate success message
     */
    public static function successMessage($message){

        $html  = '<div id="message" class="updated inline">';
        $html .= '<p><strong>'. $message . '</strong></p>';
        $html .= '</div>';

        return $html;
    }


    /**
     * Generate error message
     */
    public static function errorMessage($message){

        $html = "";
        $html .= '<div class="notice notice-error is-dismissible">';
        $html .= '    <p><strong>'. Config::$name . '</strong> - ' . $message . '</p>';
        $html .= '</div>';

        return $html;
    }

}