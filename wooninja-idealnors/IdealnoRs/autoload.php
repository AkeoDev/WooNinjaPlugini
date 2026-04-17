<?php

if (!defined('ABSPATH')) exit;

/**
 * Note: manual autoloading of classes, any missuage will lead to error
 * Main namespace is IdealnoRs, so dont extend this since it will throw fatals
 */

spl_autoload_register( function($class){

    try{
        
        $parts = explode("\\", $class);

        // Check if file exists
        if($parts[0] === 'IdealnoRs'){

            // Hacky load of namespace
            include($parts[1]. ".php");
        }

    } catch (\Exception $th) {

        // Echo message
       throw $th->getMessage();
    }
    
});

