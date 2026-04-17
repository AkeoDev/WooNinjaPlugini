​<?php

use IdealnoRs\Config;
use IdealnoRs\Pages;

if (!defined('ABSPATH')) exit;

// Check user auth level
if (!current_user_can('manage_options')) return;

// Set default tab null if we are on home page of plugin
$default_tab = array_key_first(Config::$tabpages);

// If we have tab query param, use custom
$tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;

// Check do we have this page set in config
if(!array_key_exists($tab, Config::$tabpages)) return;

// Get lang used
$default_lang = get_option('woo_idealnors_defaultlang', 'rs');

// Get trial status
$trial = get_option('woo_idealnors_trial', 'true');

// Init pages class
$pages = new Pages();


?>

<div class="wrap">

    <h2><?php echo esc_html(Config::$name); ?></h2>

    <nav class="nav-tab-wrapper wp-clearfix" aria-label="Ceneje menu">
        <?php
            foreach(Config::$tabpages as $key => $page){
                ?>
                    <a href="admin.php?page=<?php echo esc_attr(Config::$plugin_prefix); ?>&tab=<?php echo esc_attr($key); ?>" class="nav-tab <?php echo $tab === $key ? ' nav-tab-active' : ''; ?>"><?php echo esc_html($page[$default_lang]); ?></a>
                <?php
            }
        ?> 
    </nav>

    <?php

        switch ($tab) {
            case 'homepage':

                // Homepage is also default case
                echo $pages->CenejeDashboard();
                      
            break;
            
            case 'settings':

                if($trial !== 'true'){

                    // Echo settings tab
                    echo $pages->SettingsTab();

                }else{
                    echo "<h3>". Config::$translates[21][$default_lang] . "</h3>";
                }

            break;

            case 'products':

                if($trial !== 'true'){

                    // Echo products tab
                    echo $pages->ProductsTab();

                }else{
                    echo "<h3>". Config::$translates[21][$default_lang] . "</h3>";
                }

            break;

            case 'categories':

                if($trial !== 'true' ){

                    // Echo categories tab
                    echo $pages->CategoriesTab();

                }else{
                    echo "<h3>". Config::$translates[21][$default_lang] . "</h3>";
                }

            break;

            case 'export':

                if($trial !== 'true'){

                    // Echo export tab
                    echo $pages->ExportTab();

                }else{
                    echo "<h3>". Config::$translates[21][$default_lang] . "</h3>";
                }

            break;
            
            default:

                echo $pages->CenejeDashboard();
                break;
        }


    ?>      
</div>