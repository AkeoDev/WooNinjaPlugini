<?php

namespace IdealnoRs;

use IdealnoRs\Config;
use IdealnoRs\Hooks;
use IdealnoRs\ExportXML;
use Exception;

if (!defined('ABSPATH')) exit;



/**
 * Pages class 
 */

class Pages{  

    // Set lang holder
    public $lang = '';

    public static $texts = [
        0 =>[
            'sl' => 'Glavne nastavitve',
            'en' => 'Main settings',
            'ba' => 'Glavne postavke',
            'hr' => 'Glavne postavke',
            'rs' => 'Glavne postavke',
        ],
        1 =>[
            'sl' => 'Izberite način izvoza',
            'en' => 'Choose export type',
            'ba' => 'Odaberite način izvoza',
            'hr' => 'Odaberite način izvoza',
            'rs' => 'Odaberite način izvoza',
        ],
        2 =>[
            'sl' => 'Avtomatsko izvozi vse izdelke',
            'en' => 'Automaticly export all products',
            'ba' => 'Automatski izvezi sve proizvode',
            'hr' => 'Automatski izvezi sve proizvode',
            'rs' => 'Automatski izvezi sve proizvode',
        ],
        3 =>[
            'sl' => 'Avtomatsko izvozi izbrane izdelke',
            'en' => 'Automaticly export specific products',
            'ba' => 'Automatski izvozi sve odabrane proizvode',
            'hr' => 'Automatski izvozi sve odabrane proizvode',
            'rs' => 'Automatski izvozi sve odabrane proizvode',
        ],
        4 =>[
            'sl' => 'Ne želim, da se izdelki avtomatsko izvozijo',
            'en' => 'Stop exporting products',
            'ba' => 'Prestani izvoziti proizvode',
            'hr' => 'Prestani izvoziti proizvode',
            'rs' => 'Prestani izvoziti proizvode',
        ],
        5 =>[
            'sl' => 'Način shranjevanja',
            'en' => 'Save method',
            'ba' => 'Nacin spremanja export fajla',
            'hr' => 'Nacin spremanja export fajla',
            'rs' => 'Nacin spremanja export fajla',
        ],
        6 =>[
            'sl' => 'Obnovite XML pri vsaki posodobitvi izdelka',
            'en' => 'Save XML on every product update',
            'ba' => 'Spremi XML na svakoj izmjeni proizvoda',
            'hr' => 'Spremi XML na svakoj izmjeni proizvoda',
            'rs' => 'Spremi XML na svakoj izmeni proizvoda',
        ],
        7 =>[
            'sl' => 'Uporabi razporejevalnik opravil (opravilo CRON)',
            'en' => 'Use task scheduler (CRON job)',
            'ba' => 'Upotrijebi upravitelj zadataka (CRON job)',
            'hr' => 'Upotrijebi upravitelj zadataka (CRON job)',
            'rs' => 'Upotrijebi upravitelj zadataka (CRON job)',
        ],
        8 =>[
            'sl' => 'Če shranite vsak izdelek, bo vtičnik trajal nekaj časa za obnovo XML.',
            'en' => 'If you use update on every product, it will rebuild XML and that will take some time.',
            'ba' => 'Ako koristite spremanje na svakoj izmjeni, trajat ce neko vrijeme spremanje XML-a',
            'hr' => 'Ako koristite spremanje na svakoj izmjeni, trajat ce neko vrijeme spremanje XML-a',
            'rs' => 'Ako koristite spremanje na svakoj izmeni, trajat ce neko vreme spremanje XML-a',
        ],
        9 =>[
            'sl' => 'Z uporabo opravila CRON lahko izberete časovni interval, na primer posodobite XML vsakih 10 minut.',
            'en' => 'With use of CRON, you can easily set save interval, for example save on every 10 minutes.',
            'ba' => 'Sa koristenjem CRON-a, lako namjestite da sprema svako 10 minuta na primjer',
            'hr' => 'Sa koristenjem CRON-a, lako namjestite da sprema svako 10 minuta na primjer',
            'rs' => 'Sa koristenjem CRON-a, lako namestite da sprema svako 10 minuta na primer',
        ],
        10 =>[
            'sl' => 'CRON vreme',
            'en' => 'CRON interval',
            'ba' => 'CRON interval',
            'hr' => 'CRON interval',
            'rs' => 'CRON interval',
        ],
        11 =>[
            'sl' => 'Vnesite število minut, ki jih bo WP spremljal za posodobitev.',
            'en' => 'Insert time inteval, which will be used for save interval.',
            'ba' => 'Unesite vremenski interval, koji ce biti koristen za spremanje podataka u XML.',
            'hr' => 'Unesite vremenski interval, koji ce biti koristen za spremanje podataka u XML.',
            'rs' => 'Unesite vremenski interval, koji ce biti koristen za spremanje podataka u XML.',
        ],
        12 =>[
            'sl' => 'To bo uporabljeno samo, če je izbrano opravilo CRON.',
            'en' => 'This will be used only if CRON is selected.',
            'ba' => 'Ova vrijednost ce biti koristena samo ako je CRON opcija odabrana.',
            'hr' => 'Ova vrijednost ce biti koristena samo ako je CRON opcija odabrana.',
            'rs' => 'Ova vrednost ce biti koristena samo ako je CRON opcija odabrana.',
        ],
        13 =>[
            'sl' => 'Davčna stopnja',
            'en' => 'Tax rate',
            'ba' => 'Iznos taxe',
            'hr' => 'Iznos taxe',
            'rs' => 'Iznos taxe',
        ],
        14 =>[
            'sl' => 'Davčna stopnja, ki se bo uporabljala pri izvozu izdelkov.',
            'en' => 'Tax rate, which will be used only on exporting products.',
            'ba' => 'Iznos taxe koja ce biti samo koristena za proizvode koji se izvoze.',
            'hr' => 'Iznos taxe koja ce biti samo koristena za proizvode koji se izvoze.',
            'rs' => 'Iznos taxe koja ce biti samo koristena za proizvode koji se izvoze.',
        ],
        15 =>[
            'sl' => 'Cena za člane kluba',
            'en' => 'Price for members of club',
            'ba' => 'Cijena za clanove kluba',
            'hr' => 'Cijena za clanove kluba',
            'rs' => 'Cena za clanove kluba',
        ],
        16 =>[
            'sl' => 'Napišite odstotek za člane kluba. Cena se bo znižala za % odstotkov. Tega ne obravnava WooCommerce ali ta vtičnik. Če želite to uporabiti, morate zagotoviti članstvo v klubu.',
            'en' => 'Write percentage of discount for club members. This price is not handled by WooCommerce or this plugin. If you want to use this, you will need to manual club membership.',
            'ba' => 'Napišite procent popusta za članove kluba. Napomena: ovo ne obrađuje WooCommerce ili ovaj plugin. Ako koristite ovu opciju, ručno morate srediti vođenje kluba.',
            'hr' => 'Napišite procent popusta za članove kluba. Napomena: ovo ne obrađuje WooCommerce ili ovaj plugin. Ako koristite ovu opciju, ručno morate srediti vođenje kluba.',
            'rs' => 'Napišite procent popusta za članove kluba. Napomena: ovo ne obrađuje WooCommerce ili ovaj plugin. Ako koristite ovu opciju, ručno morate srediti vođenje kluba.',
        ],
        17 =>[
            'sl' => 'Cena dostave',
            'en' => 'Price for delivery',
            'ba' => 'Cijena dostave',
            'hr' => 'Cijena dostave',
            'rs' => 'Cena dostave',
        ],
        18 =>[
            'sl' => 'Koliko stane dostava uporabniku?',
            'en' => 'How much delivery costs?',
            'ba' => 'Koliko košta dostava do klijenta?',
            'hr' => 'Koliko košta dostava do klijenta?',
            'rs' => 'Koliko košta dostava do klijenta?',
        ],
        19 =>[
            'sl' => 'Minimalni čas dostave',
            'en' => 'Minimal delivery time required',
            'ba' => 'Minimalno vrijeme potrebno za dostavu',
            'hr' => 'Minimalno vrijeme potrebno za dostavu',
            'rs' => 'Minimalno vreme potrebno za dostavu',
        ],
        20 =>[
            'sl' => 'Kako hitro lahko dostavimo stranki?',
            'en' => 'How fast can we deliver to the customer?',
            'ba' => 'Koliko brzo dostavimo proizvod stranki?',
            'hr' => 'Koliko brzo dostavimo proizvod stranki?',
            'rs' => 'Koliko brzo dostavimo proizvod stranki?',
        ],
        21 =>[
            'sl' => 'Maximalni čas dostave',
            'en' => 'Maximal delivery time',
            'ba' => 'Maximalmo vrijeme za dostavu',
            'hr' => 'Maximalmo vrijeme za dostavu',
            'rs' => 'Maximalmo vreme za dostavu',
        ],
        22 =>[
            'sl' => 'Najdaljši dan, potreben za dostavo izdelka?',
            'en' => 'Maximal day required to deliver the product?',
            'ba' => 'Najduži period potreban za dostavu?',
            'hr' => 'Najduži period potreban za dostavu?',
            'rs' => 'Najduži period potreban za dostavu?',
        ],
        23 =>[
            'sl' => 'Nastavitve za izvoz',
            'en' => 'Export settings',
            'ba' => 'Postavke za izvoz',
            'hr' => 'Postavke za izvoz',
            'rs' => 'Postavke za izvoz',
        ],
        24 =>[
            'sl' => 'Ignorirajte določene izdelke',
            'en' => 'Ignore specific products',
            'ba' => 'Ignoriraj odabrane proizvode',
            'hr' => 'Ignoriraj odabrane proizvode',
            'rs' => 'Ignoriraj odabrane proizvode',
        ],
        25 =>[
            'sl' => 'Določeni izdelki',
            'en' => 'Specific products',
            'ba' => 'Odabrani proizvodi',
            'hr' => 'Odabrani proizvodi',
            'rs' => 'Odabrani proizvodi',
        ],
        26 =>[
            'sl' => 'Vrednosti ločite s presledkom.',
            'en' => 'Seperate items by space.',
            'ba' => 'Razdvojite proizvode praznim mjestom',
            'hr' => 'Razdvojite proizvode praznim mjestom',
            'rs' => 'Razdvojite proizvode praznim mestom',
        ],
        27 =>[
            'sl' => 'Ignorirajte določene kategorije',
            'en' => 'Ignore specific categories',
            'ba' => 'Ignoriraj odabrane kategorije',
            'hr' => 'Ignoriraj odabrane kategorije',
            'rs' => 'Ignoriraj odabrane kategorije',
        ],
        28 =>[
            'sl' => 'Določene kategorije',
            'en' => 'Specific categories',
            'ba' => 'Odabrane kategorije',
            'hr' => 'Odabrane kategorije',
            'rs' => 'Odabrane kategorije',
        ],
        29 =>[
            'sl' => 'Kategorije',
            'en' => 'Categories',
            'ba' => 'Kategorije',
            'hr' => 'Kategorije',
            'rs' => 'Kategorije',
        ],
        30 =>[
            'sl' => 'Export UTM parametri (na povezavah do izdelkov)',
            'en' => 'Export UTM parameters (on links to the product)',
            'ba' => 'Export UTM parametri (na linku od proizvoda)',
            'hr' => 'Export UTM parametri (na linku od proizvoda)',
            'rs' => 'Export UTM parametri (na linku od proizvoda)',
        ],
        31 =>[
            'sl' => 'Izvozi UTM parametre',
            'en' => 'Export UTM parameters',
            'ba' => 'Izvozi UTM parametre',
            'hr' => 'Izvozi UTM parametre',
            'rs' => 'Izvozi UTM parametre',
        ],
        32 =>[
            'sl' => 'Besede bodo združljive z URL -jem (URL encode)',
            'en' => 'Words will be URL compatible (URL encode)',
            'ba' => 'Riječi će biti URL kompatibilne (URL encode)',
            'hr' => 'Riječi će biti URL kompatibilne (URL encode)',
            'rs' => 'Reči će biti URL kompatibilne (URL encode)',
        ],
        33 =>[
            'sl' => 'Najprej ustvarite XML.',
            'en' => 'Please, generate XML first.',
            'ba' => 'Molimo Vas, pokrenite generisanje XML-a',
            'hr' => 'Molimo Vas, pokrenite generisanje XML-a',
            'rs' => 'Molimo Vas, pokrenite generisanje XML-a',
        ],
        34 =>[
            'sl' => 'Izdelki (export)',
            'en' => 'Products (export)',
            'ba' => 'Proizvodi (export)',
            'hr' => 'Proizvodi (export)',
            'rs' => 'Proizvodi (export)',
        ],
        35 =>[
            'sl' => 'Vaš trenutni način je izvoz vseh, zato ne bomo prikazali vseh izdelkov.',
            'en' => 'Your current mode is export all so we wont show all products.',
            'ba' => 'Vaš trenutni tip exporta je sve, zbog toga svi proizvodi neće biti prikazani ovdje.',
            'hr' => 'Vaš trenutni tip exporta je sve, zbog toga svi proizvodi neće biti prikazani ovdje.',
            'rs' => 'Vaš trenutni tip exporta je sve, zbog toga svi proizvodi neće biti prikazani ovdje.',
        ],
        36 =>[
            'sl' => 'Zanemarjeni izdelki (%s)',
            'en' => 'Ignored products (%s)',
            'ba' => 'Ignorirani proizvodi (%s)',
            'hr' => 'Ignorirani proizvodi (%s)',
            'rs' => 'Ignorirani proizvodi (%s)',
        ],
        37 =>[
            'sl' => 'Naziv izdelka',
            'en' => 'Name of the product',
            'ba' => 'Ime proizvoda',
            'hr' => 'Ime proizvoda',
            'rs' => 'Ime proizvoda',
        ],
        38 =>[
            'sl' => 'To možnost ste onemogočili.',
            'en' => 'You have disabled this option.',
            'ba' => 'Ova opcija je isključena.',
            'hr' => 'Ova opcija je isključena.',
            'rs' => 'Ova opcija je isključena.',
        ],
        39 =>[
            'sl' => 'O vtičniku',
            'en' => 'About plugin',
            'ba' => 'O pluginu',
            'hr' => 'O pluginu',
            'rs' => 'O pluginu',
        ],
        40 =>[
            'sl' => 'Opombe ob izdaji',
            'en' => 'Release notes',
            'ba' => 'Poslednje nadogradnje',
            'hr' => 'Poslednje nadogradnje',
            'rs' => 'Posledne nadogradnje',
        ],
        41 =>[
            'sl' => 'Opombe ob izdaji',
            'en' => 'Release notes',
            'ba' => 'Poslednje nadogradnje',
            'hr' => 'Poslednje nadogradnje',
            'rs' => 'Posledne nadogradnje',
        ],
        // This is language specific
        42 =>[
            'sl' => 'Izvažajte svoje izdelke s spletnega mesta WooCommerce neposredno na trg Idealno.rs in uživajte u vseh prednostnih ogleševalnih izdelkov.<br>
                    Ta vtičnik vam nudi popolno podporo pri prilaganju in prilagodljivost pri izvozu na Idealno.rs',
            'ba' => 'Izvozite vaše proizvode iz WooCommerce trgovine direktno u trgovinu Idealno.ba i uživajte u svim prednostima oglašavanja proizvoda.<br>
                    Ovaj plugin je u potpunosti kompatibilan, dinamičan i prilaogođen trgovini Idealno.ba.',
            'hr' => 'Izvozite vaše proizvode iz WooCommerce trgovine direktno u trgovinu Jeftinije.hr i uživajte u svim prednostima oglašavanja proizvoda.<br>
                    Ovaj plugin je u potpunosti kompatibilan, dinamičan i prilaogođen trgovini Jeftinije.hr.',
            'rs' => 'Izvozite vaše proizvode iz WooCommerce trgovine direktno u trgovinu Idealno.rs i uživajte u svim prednostima oglašavanja proizvoda.<br>
                    Ovaj plugin je u potpunosti kompatibilan, dinamičan i prilaogođen trgovini Idealno.rs.',
        ], 
    ];
    
    
    
    public function __construct()
    {
        $this->lang = get_option('woo_idealnors_defaultlang', 'rs');
    }


    /**
     * Dashboard of tab menu
     */
    public function CenejeDashboard(){

        include(plugin_dir_path(__FILE__) . 'views/release-notes.php');

        $logo = base64_encode(file_get_contents(plugin_dir_path(__FILE__) . 'static/woo_' . Config::$default_lang . '.png'));

        $activated = get_option('woo_idealnors_activated', 'false');
        
        ?>
      
            <h2><?php echo esc_html(self::$texts[39][$this->lang]); ?></h2>

            <div style="margin-top:40px; display:flex; flex-direction:row; justify-content: flex-start; gap: 15px; max-width: 800px; border: 1px solid gray; background-color: white; padding: 20px">

                <div style="width: 20%">  
                    <img style="padding-left: 25px" src="data:image/png;base64, <?php echo esc_attr($logo); ?>" alt="all">
                </div>

                <div style="width: 80%; padding: 10px; border-left: 2px solid #777;">
                    <div style="font-weight: 500">
                        <?php echo esc_html(self::$texts[42][Config::$default_lang]); ?>
                    </div>
                    <br>
                    <?php
                        
                        if($activated !== 'true'){

                            ?>

                                <a href="<?php echo esc_url(Config::$producturl); ?>">
                                    <button>Buy</button>
                                </a>
                                
                            <?php

                        }else{

                            ?>

                                <a href="<?php echo esc_url(Config::$producturl); ?>">
                                    <button>Documentation</button>
                                </a>

                            <?php

                        }

                    ?>


                    
                </div>
            </div>

            <h2 style="padding-top: 40px;"><?php echo esc_html(self::$texts[40][$this->lang]); ?></h2>

            <?php

            foreach(array_reverse($updates[$this->lang]) as $key => $update){

                ?>

                <div style="display: flex; align-items: baseline;">
                    <h3><?php echo esc_html($key); ?> </h3><h4 style="margin-left: 10px;"><?php echo esc_html($update[0]); ?></h4>
                </div>
                    
                <?php

                // Map trough messages
                foreach($update as $key => $line){

                    // Skip first element since its date
                    if($key === 0) continue;

                    ?>
                        <span><?php echo esc_html($line); ?></span><br>
                    <?php
                }
            }

    }



    /**
     * Settings of the tab menu
     */
    public function SettingsTab(){

        // Get export method
        $export_method = get_option('woo_idealnors_export_type', 'all');
        $save_method = get_option('woo_idealnors_save_type', 'update');
        $ddv_value = get_option('woo_idealnors_taxrate', 0);
        $club_value = get_option('woo_idealnors_clubprice', 0);
        $delivery_value = get_option('woo_idealnors_delivery_price', 0);
        $deliverymin_value = get_option('woo_idealnors_delivery_min', 0);
        $deliverymax_value = get_option('woo_idealnors_delivery_max', 0);

        // Ignore products values
        $ignore_products = get_option('woo_idealnors_ignore_products', false);
        $ignored_products = get_option('woo_idealnors_ignore_products_value', '');

        // Ignore categories values
        $ignore_categories = get_option('woo_idealnors_ignore_categories', false);
        $ignored_categories = get_option('woo_idealnors_ignore_categories_value', '');

        // Handle POST request from this tab
        if(isset($_POST['ceneje_settings_tab'])){

            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'idealnors_settings_tab')) {
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $export = sanitize_text_field(trim($_POST['woo_idealnors_export_type']));
            $save = sanitize_text_field(trim($_POST['woo_idealnors_save_type']));
            $ddv = sanitize_text_field(trim($_POST['woo_idealnors_taxrate']));
            $club = sanitize_text_field(trim($_POST['woo_idealnors_clubprice']));
            $delivery = sanitize_text_field(trim($_POST['woo_idealnors_delivery_price']));
            $deliverymin = sanitize_text_field(trim($_POST['woo_idealnors_delivery_min']));
            $deliverymax = sanitize_text_field(trim($_POST['woo_idealnors_delivery_max']));

            // Redirect back, bad data
            if(empty($export) || empty($save)){
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $updated = false;

            if($export !== $export_method){
                update_option('woo_idealnors_export_type', $export);
                $updated = true;
            }

            if($save !== $save_method){
                update_option('woo_idealnors_save_type', $save);
                $updated = true;
            }

            if($ddv !== $ddv_value){
                update_option('woo_idealnors_taxrate', $ddv);
                $updated = true;
            }

            if($club !== $club_value){
                update_option('woo_idealnors_clubprice', $club);
                $updated = true;
            }

            if($delivery !== $delivery_value){
                update_option('woo_idealnors_delivery_price', $delivery);
                $updated = true;
            }

            if($deliverymin !== $deliverymin_value){
                update_option('woo_idealnors_delivery_min', $deliverymin);
                $updated = true;
            }

            if($deliverymax !== $deliverymax_value){
                update_option('woo_idealnors_delivery_max', $deliverymax);
                $updated = true;
            }

            if($updated){
                wp_safe_redirect(wp_get_referer());
                exit;
            }
        }


        /**
         * Check if settings tab have posted some export rules
         */
        if(isset($_POST['ceneje_settings_export_tab'])){

            // Verify nonce
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'idealnors_settings_export_tab')) {
                wp_safe_redirect(wp_get_referer());
                exit;
            }

            $updated = false;

            if(isset($_POST['woo_idealnors_ignore_products'])){
                update_option('woo_idealnors_ignore_products', 'true');
                $updated = true;
            }else{
                update_option('woo_idealnors_ignore_products', 'false');
                $updated = true;
            }

            if(isset($_POST['woo_idealnors_ignore_categories'])){
                update_option('woo_idealnors_ignore_categories', 'true');
                $updated = true;
            }else{
                update_option('woo_idealnors_ignore_categories', 'false');
                $updated = true;
            }

            $ignored_products_form = sanitize_textarea_field($_POST['woo_idealnors_ignore_products_value']);
            $ignored_categories_form = sanitize_textarea_field($_POST['woo_idealnors_ignore_categories_value']);

            if($ignored_products !== $ignored_products_form){
                update_option('woo_idealnors_ignore_products_value', $ignored_products_form);
                $updated = true;
            }

            if($ignored_categories !== $ignored_categories_form){
                update_option('woo_idealnors_ignore_categories_value', $ignored_categories_form);
                $updated = true;
            }

            if($updated){
                wp_safe_redirect(wp_get_referer());
                exit;
            }

        }


        $html = '';

    
        $html = '
            <h2>'. self::$texts[0][$this->lang]. '</h2>
            <form method="POST" action="#">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            '. self::$texts[1][$this->lang] . '
                        </th>
                        <td>
                            <input style="margin: 5px;" type="radio" name="woo_idealnors_export_type" value="all" '. ($export_method === 'all' ? "checked" : "") .'>'. self::$texts[2][$this->lang] .'<br />
                            <input style="margin: 5px;" type="radio" name="woo_idealnors_export_type" value="specific" '. ($export_method === 'specific' ? "checked" : "") .'>'. self::$texts[3][$this->lang] .'<br/>
                            <input style="margin: 5px;" type="radio" name="woo_idealnors_export_type" value="none" '. ($export_method === 'none' ? "checked" : "") .'>'. self::$texts[4][$this->lang] .'<br/>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            '. self::$texts[5][$this->lang] . '
                        </th>
                        <td>
                            <input style="margin: 5px;" type="radio" name="woo_idealnors_save_type" value="update" '. ($save_method === 'update' ? "checked" : "") .'>'. self::$texts[6][$this->lang] .'<br />
                            <input disabled style="margin: 5px;" type="radio" name="woo_idealnors_save_type" value="cron" '. ($save_method === 'cron' ? "checked" : "") .'>'. self::$texts[7][$this->lang] .'<br/>
                            <p class="description">'. self::$texts[8][$this->lang] .'</p>
                            <p class="description">'. self::$texts[9][$this->lang] .'</p>
                        </td>
                    </tr>
                  
                    <tr>
                        <th scope="row">
                        '. self::$texts[10][$this->lang] .'
                        </th>
                        <td>
                            <a href="' . esc_url(admin_url('tools.php?page=action-scheduler')) . '" target="_blank">
                                <input disabled type="button" class="button-primary woocommerce-save-button" value="Settings" />
                            </a>
                            <p class="description">'. self::$texts[11][$this->lang] .'</p>
                            <p class="description">'. self::$texts[12][$this->lang] .'</p>
                        </td>
                    </tr>
                
                        
                    <tr>
                        <th scope="row">
                            '. self::$texts[13][$this->lang] .'
                        </th>
                        <td>
                            <input name="woo_idealnors_taxrate" class="regular-text code" value="'. $ddv_value .'" /> %
                            <p class="description">'. self::$texts[14][$this->lang] .'</p>
                        </td>
                    </tr
                    <tr>
                        <th scope="row">
                            '. self::$texts[15][$this->lang] .'
                        </th>
                        <td>
                            <input name="woo_idealnors_clubprice" class="regular-text code" value="'. $club_value .'" /> %
                            <p class="description">'. self::$texts[16][$this->lang] .'</p>
                        </td>
                    </tr>
                    <hr />
                    <tr>
                        <th scope="row">
                            '. self::$texts[17][$this->lang] .'
                        </th>
                        <td>
                            <input name="woo_idealnors_delivery_price" class="regular-text code" value="'. $delivery_value .'" /> 
                            <p class="description">'. self::$texts[18][$this->lang] .'</p>
                        </td>
                    </tr>
                        <tr>
                        <th scope="row">
                            '. self::$texts[19][$this->lang] .'
                        </th>
                        <td>
                            <input name="woo_idealnors_delivery_min" class="regular-text code" value="'. $deliverymin_value .'" /> 
                            <p class="description">'. self::$texts[20][$this->lang] .'</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            '. self::$texts[21][$this->lang] .'
                        </th>
                        <td>
                            <input name="woo_idealnors_delivery_max" class="regular-text code" value="'. $deliverymax_value .'" /> 
                            <p class="description">'. self::$texts[22][$this->lang] .'</p>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button-primary woocommerce-save-button" name="ceneje_settings_tab" value="'. Config::$translates[9][$this->lang] . '"/>
            </form>

            <h2 style="margin-top: 60px">'. self::$texts[23][$this->lang] .'</h2>
            <form method="POST" action="#">
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            '. self::$texts[24][$this->lang] .'
                        </th>
                        <td>
                            <input style="margin: 5px;" type="checkbox" name="woo_idealnors_ignore_products" '. ($ignore_products === 'true' ? "checked" : "") .' />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                           '. self::$texts[25][$this->lang] .'
                        </th>
                        <td>
                            <textarea style="'. ($ignore_products !== 'true' ? "pointer-events:none;background:#f0f0f1;" : "") .'" cols="60"  rows="5" style="margin: 5px;" name="woo_idealnors_ignore_products_value" id="woo_idealnors_ignore_products_value">'. $ignored_products . '</textarea>
                            <p class="description">'. self::$texts[26][$this->lang] .'</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            '. self::$texts[27][$this->lang] .'
                        </th>
                        <td>
                            <input style="margin: 5px;" type="checkbox" name="woo_idealnors_ignore_categories"  '. ($ignore_categories === 'true' ? "checked" : "") .' />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            '. self::$texts[28][$this->lang] .'
                        </th>
                        <td>
                            <textarea style="'. ($ignore_categories !== 'true' ? "pointer-events:none;background:#f0f0f1;" : "") .'" cols="60"  rows="5" style="margin: 5px;" name="woo_idealnors_ignore_categories_value" id="woo_idealnors_ignore_categories_value">'. $ignored_categories . '</textarea>
                            <p class="description">'. self::$texts[26][$this->lang] .'</p>
                        </td>
                    </tr>
                </table>
                <input type="submit" class="button-primary woocommerce-save-button" name="ceneje_settings_export_tab" value="'. Config::$translates[9][$this->lang] . '"/>
            </form>
            ';
            
        return $html;
    }


    /**
     * Products of the tab menu
     */
    public function ProductsTab(){

        $exporttype = get_option('woo_idealnors_export_type', 'all');

        ?>

            <div style="display: flex; flex-direction: row; justify-content: flex-start; align-items: stretch; gap: 15px">

                <div style="width: 50%;">

                    <h2><?php echo esc_html(self::$texts[34][$this->lang]); ?></h2>

                    <?php

                        // Check type export 
                        if($exporttype === 'all'){

                            // Echo friendly message
                            echo("<h4>" . self::$texts[35][$this->lang] . "</h4>");
                        
                        }elseif($exporttype === 'specific'){

                            ?>

                            <table class="wp-list-table widefat fixed striped table-view-list posts">
                                <thead>
                                    <tr>
                                        <th width="10%">ID</th>
                                        <th><?php echo esc_html(self::$texts[37][$this->lang]); ?></th>
                                        <th width="20%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                
                            <?php

                            // Fetch all exported products 
                            $products = ExportXML::getSpecificProducts();

                            if($products !== null){

                                // Check do we have posts at all
                                if ($products->have_posts()){
                    
                                    // Map trought all products and fetch product info (referenced to $product)
                                    while($products->have_posts()){ 
                                        
                                        // Get product data
                                        $products->the_post();

                                        global $product;

                                        ?>

                                        <tr>
                                            <td><?php echo esc_html($product->get_id()); ?></td>
                                            <td><?php echo esc_html($product->get_name()); ?></td>
                                            <td>
                                                <a style="top: 0;" href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>" class="page-title-action">Edit</a>
                                                <a style="top: 0;" href="<?php echo esc_url($product->get_permalink()); ?>" class="page-title-action">View</a>
                                            </td>
                                        </tr>
                        
                                        <?php
                                    }
                                }
                            }

                            ?>

                                </tbody>
                            </table>

                            <?php
                        }
                    ?>
                </div>

                <div style="width: 50%;">   

                    <h2><?php echo esc_html(sprintf(self::$texts[36][$this->lang], 'specific')); ?></h2>

                    <table class="wp-list-table widefat fixed striped table-view-list posts">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th><?php echo esc_html(self::$texts[37][$this->lang]); ?></th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php

                            // Fetch all exported products 
                            $products = ExportXML::getAllProducts();

                            if($products !== null){

                                // Check do we have posts at all
                                if ($products->have_posts()){
                    
                                    // Map trought all products and fetch product info (referenced to $product)
                                    while($products->have_posts()){ 
                                        
                                        // Get product data
                                        $products->the_post();

                                        global $product;

                                        // Get post meta info
                                        $meta = get_post_meta($product->get_id());

                                        // Check do we have ignore this product enabled
                                        if(isset($meta['woo_idealnors_field_ignoreproduct']) && current($meta['woo_idealnors_field_ignoreproduct']) === 'on'){

                                            ?>

                                            <tr>
                                                <td><?php echo esc_html($product->get_id()); ?></td>
                                                <td><?php echo esc_html($product->get_name()); ?></td>
                                                <td>
                                                    <a style="top: 0;" href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>" class="page-title-action">Edit</a>
                                                    <a style="top: 0;" href="<?php echo esc_url($product->get_permalink()); ?>" class="page-title-action">View</a>
                                                </td>
                                            </tr>
                            
                                            <?php
                                        }
                                    }
                                }
                            }
                        
                        ?>

                        </tbody>
                    </table>

                    <br>

                    <h2><?php echo esc_html(sprintf(self::$texts[36][$this->lang], 'macro specific')); ?></h2>

                    <table class="wp-list-table widefat fixed striped table-view-list posts">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th><?php echo esc_html(self::$texts[37][$this->lang]); ?></th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php

                            // Fetch all exported products 
                            $products = ExportXML::getAllProducts();


                            // Check have we macro setting for products
                            if(get_option('woo_idealnors_ignore_products', 'false') === 'true'){

                                // Get ignored products string
                                $macroproducts = preg_split('/\r\n|[\r\n]/', get_option('woo_idealnors_ignore_products_value', ''));

                                if($products !== null){

                                    // Check do we have posts at all
                                    if ($products->have_posts()){
                        
                                        // Map trought all products and fetch product info (referenced to $product)
                                        while($products->have_posts()){ 
                                            
                                            // Get product data
                                            $products->the_post();

                                            global $product;

                                            // Get post meta info
                                            $meta = get_post_meta($product->get_id());


                                            // Check do we have macro skip enabled for products
                                            if(!is_null($macroproducts)){

                                                // Check if macro skip is set in field
                                                if(in_array((string)$product->get_id(), $macroproducts)){

                                                    ?>

                                                    <tr>
                                                        <td><?php echo esc_html($product->get_id()); ?></td>
                                                        <td><?php echo esc_html($product->get_name()); ?></td>
                                                        <td>
                                                            <a style="top: 0;" href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>" class="page-title-action">Edit</a>
                                                            <a style="top: 0;" href="<?php echo esc_url($product->get_permalink()); ?>" class="page-title-action">View</a>
                                                        </td>
                                                    </tr>
                                    
                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                ?>

                                <tr>
                                    <td colspan="3"><?php echo esc_html(self::$texts[38][$this->lang]); ?></td>
                                </tr>

                                <?php
                            }
                        
                        ?>

                        </tbody>
                    </table>
                    
                    <br>

                    <h2><?php echo esc_html(sprintf(self::$texts[36][$this->lang], 'macro categories')); ?></h2>

                    <table class="wp-list-table widefat fixed striped table-view-list posts">
                        <thead>
                            <tr>
                                <th width="10%">ID</th>
                                <th><?php echo esc_html(self::$texts[37][$this->lang]); ?></th>
                                <th width="20%">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        <?php

                            // Fetch all exported products 
                            $products = ExportXML::getAllProducts();


                            // Check have we macro setting for products
                            if(get_option('woo_idealnors_ignore_categories', 'false') === 'true'){

                                // Get ignored products string
                                $macrocategories = preg_split('/\r\n|[\r\n]/', get_option('woo_idealnors_ignore_categories_value', ''));

                                if($products !== null){

                                    // Check do we have posts at all
                                    if ($products->have_posts()){
                        
                                        // Map trought all products and fetch product info (referenced to $product)
                                        while($products->have_posts()){ 
                                            
                                            // Get product data
                                            $products->the_post();

                                            global $product;

                                            // Get post meta info
                                            $meta = get_post_meta($product->get_id());


                                            // Check do we have macro skip enabled for products
                                            if(!is_null($macrocategories)){

                                                // Check if macro skip is set in field
                                                if(in_array((string)$product->get_id(), $macrocategories)){

                                                    ?>

                                                    <tr>
                                                        <td><?php echo esc_html($product->get_id()); ?></td>
                                                        <td><?php echo esc_html($product->get_name()); ?></td>
                                                        <td>
                                                            <a style="top: 0;" href="<?php echo esc_url(admin_url('post.php?post=' . $product->get_id() . '&action=edit')); ?>" class="page-title-action">Edit</a>
                                                            <a style="top: 0;" href="<?php echo esc_url($product->get_permalink()); ?>" class="page-title-action">View</a>
                                                        </td>
                                                    </tr>
                                    
                                                    <?php
                                                }
                                            }
                                        }
                                    }
                                }
                            }else{
                                ?>

                                <tr>
                                    <td colspan="3"><?php echo esc_html(self::$texts[38][$this->lang]); ?></td>
                                </tr>

                                <?php
                            }
                        
                        ?>

                        </tbody>
                    </table>
                    
                </div>
            </div>

        <?php
    }

    /**
     * CategoriesTab
     * 
     * Return table with ceneje categories
     *
     * @return string
     */
    public function CategoriesTab(){

        $categories = Hooks::getCenejeCategories($this->lang);

        ?>

            <h2><?php echo esc_html(self::$texts[29][$this->lang]); ?></h2>

            <table class="wp-list-table widefat fixed striped table-view-list posts">
                <thead>
                    <tr>
                        <th style="width: 5%;">ID</th>
                        <th style="width: 15%;">L1</th>
                        <th style="width: 15%;">L2</th>
                        <th style="width: 15%;">L3</th>
                        <th style="width: 50%;">L1 -> L2 -> L3</th>
                    </tr>
                </thead>
                <tbody>

                    <?php

                        if($this->lang !== 'en'){

                            foreach($categories as $key => $category){

                                // Skip first line
                                if($key === 0) continue;
                                
                                ?>

                                <tr>
                                    <td><?php echo esc_html($category[3]); ?></td>
                                    <td><?php echo esc_html($category[1]); ?></td>
                                    <td><?php echo esc_html($category[2]); ?></td>
                                    <td><?php echo esc_html($category[4]); ?></td>
                                    <td><?php echo esc_html(str_replace('- -> ', '', $category[5])); ?></td>  
                                </tr>
                                
                                <?php
                            }
                        }else{

                            ?>
                                <tr>
                                    <td colspan="5">Sorry, but this content is not available in the English.</td>
                                </tr>
                            <?php
                        }
                    ?>

                </tbody>
            </table>
        <?php

    }




    /**
     * Export of the tab menu
     */
    public function ExportTab(){

        /**
         * Get values from the db
         */
        $exportutmdb = get_option('woo_idealnors_export_utm', 'false');
        $sourcedb = get_option('woo_idealnors_utm_source', '');
        $mediumdb = get_option('woo_idealnors_utm_medium', '');
        $campaigndb = get_option('woo_idealnors_utm_campaign', '');
        //$termdb = get_option('woo_idealnors_utm_term', '');
        //$contentdb = get_option('woo_idealnors_utm_content', '');


        // Check if form is submited
        if(isset($_POST['ceneje_export_utm']) && !empty($_POST['ceneje_export_utm'])){

            // UTM form params
            $exportutm = $_POST['woo_idealnors_export_utm'];
            $source = $_POST['woo_idealnors_utm_source'];
            $medium = $_POST['woo_idealnors_utm_medium'];
            $campaign = $_POST['woo_idealnors_utm_campaign'];
            //$term = $_POST['woo_idealnors_utm_term'];
            //$content = $_POST['woo_idealnors_utm_content'];

            // Set state holder
            $updated = false;

            // Check if export checkbox is enabled
            if(isset($exportutm)){

                // Set export to true is set
                update_option('woo_idealnors_export_utm', 'true');

            }else{

                // Set export to false if missing
                update_option('woo_idealnors_export_utm', 'false');

            }


            // Check if values are not equal 
            if($source !== $sourcedb){

                // Update UTM param
                update_option('woo_idealnors_utm_source', $source);

                $updated = true;
            }


            // Check if values are not equal 
            if($medium !== $mediumdb){

                // Update UTM param
                update_option('woo_idealnors_utm_medium', $medium);

                $updated = true;
            }


            // Check if values are not equal 
            if($campaign !== $campaigndb){

                // Update UTM param
                update_option('woo_idealnors_utm_campaign', $campaign);

                $updated = true;
            }

            /*
            // Check if values are not equal 
            if($term !== $termdb){

                // Update UTM param
                update_option('woo_idealnors_utm_term', urlencode($term));

                $updated = true;
            }


            // Check if values are not equal 
            if($content !== $contentdb){

                // Update UTM param
                update_option('woo_idealnors_utm_content', $content);

                $updated = true;
            }
            */


            // Check if db was updated
            if($updated){

                // Refresh page
                wp_redirect($_SERVER['HTTP_REFERER']);
            }

        }

        /**
         * Check if XML file exists
         */

        // Craft path to XML
        $path = get_home_path() .'/wp-content/uploads/' . Config::$filename;

        // Check if file exists
        $exists = file_exists($path) ? true : false;


        /**
         * Check if user submitted to delete XML file
         */
        if(isset($_POST['woo_idealnors_deletexml']) && !empty($_POST['woo_idealnors_deletexml'])){

            // Try
            try{

                if($exists){

                    // Use WP function to delete file
                    wp_delete_file($path);

                    // Refresh page
                    wp_redirect($_SERVER['HTTP_REFERER']);
                }

                // Refresh page
                wp_redirect($_SERVER['HTTP_REFERER']);

            }catch(\Throwable $th){
                throw new Exception('XML file doesnt exists. Check with the developers.');
            }
        }

        ?>

        <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row">
                            Export URL
                        </th>
                        <td>
                            <input disabled class="regular-text code" value="<?php echo esc_attr(wp_upload_dir()['baseurl'] . '/' . Config::$filename); ?>" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            Download
                        </th>
                        <td>
                            <a href="<?php echo esc_url(wp_upload_dir()['baseurl'] . '/' . Config::$filename); ?>" target="_blank">
                                <input <?php echo $exists ? '' : 'disabled'; ?> type="submit" class="button-primary woocommerce-save-button" value="Download"/>
                            </a>
                            <?php if(!$exists){ ?> <p class="description"><?php echo esc_html(self::$texts[33][$this->lang]); ?></p> <?php } ?>
                        </td>                       
                    </tr>
                    <tr>
                        <th scope="row">
                            Delete file
                        </th>
                        <td>
                            <form method="POST" action="#">
                                <input name="woo_idealnors_deletexml" <?php echo $exists ? '' : 'disabled'; ?> type="submit" class="button-primary" value="Delete" style="background-color: red; border: none;"/>
                            </form>
                
                            <?php if(!$exists){ ?> <p class="description"><?php echo esc_html(self::$texts[33][$this->lang]); ?></p> <?php } ?>
                        </td>
                    </tr>
                </table>
                <h2><?php echo esc_html(self::$texts[30][$this->lang]); ?></h2>
                <form method="POST" action="#">
                    <table class="form-table" role="presentation">
                        <tr>
                            <th scope="row">
                                <?php echo esc_html(self::$texts[31][$this->lang]); ?>
                            </th>
                            <td>
                                <input style="margin: 5px;" type="checkbox" name="woo_idealnors_export_utm"  <?php echo $exportutmdb === 'true' ? 'checked' : ''; ?> />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                UTM Source
                            </th>
                            <td>
                                <input name="woo_idealnors_utm_source" class="regular-text code" value="<?php echo esc_attr($sourcedb); ?>" />                     
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                UTM Medium
                            </th>
                            <td>
                                <input name="woo_idealnors_utm_medium" class="regular-text code" value="<?php echo esc_attr($mediumdb); ?>" />                    
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                UTM Campaign
                            </th>
                            <td>
                                <input name="woo_idealnors_utm_campaign" class="regular-text code" value="<?php echo esc_attr($campaigndb); ?>" />                     
                            </td>
                        </tr>
                    </table>
                    <input type="submit" class="button-primary woocommerce-save-button" name="ceneje_export_utm" value="<?php echo esc_attr(Config::$translates[9][$this->lang]); ?>"/>
                </form>
                
        <?php
    }

    /**
     * customCronFilter
     * 
     * Updates CRON description and time limit
     *
     * @param [array] $schedules
     * @return array
     */
    public function customCronFilter($schedules){ 
        $schedules['woo_idealnors_interval'] = array(
            'interval' => 1800,
            'display'  => esc_html__( 'Run Woo Ceneje Plugin every x time. Default 10 minutes' ), );
        return $schedules;
    }



    /**
     * Throw on undefined class function
     */
    public function __call($name, $arguments){
        throw new Exception('Ceneje XML Plugin: calling undefined page ' . $name);
    } 

    /**
     * Throw on undefined class function (static)
     */
    public static function __callStatic($name, $arguments)
    {
        throw new Exception('Ceneje XML Plugin: calling undefined page (static)' . $name);
    }
}