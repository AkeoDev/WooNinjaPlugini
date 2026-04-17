<?php

namespace IdealnoRs;

if (!defined('ABSPATH')) exit;

/**
 * Generate config for the plugin
 */

class Config{

    // Main info
    public static $name = "Idealno.rs - XML";
    public static $version = '0.0.8';
    public static $default_lang = "rs";
    public static $plugin_prefix = 'idealnors-xml';
    public static $filename = 'idealnors-export.xml';
    public static $interval = 604800; // One week for license check
    public static $producturl = 'https://wooninja.si/downloads/idealno-rs-xml-za-woocommerce/';

    /**
     * License info
     */
    public static $license = [
        'EDD_IDEALNORS_STORE_URL'     => 'https://wooninja.si',
        'EDD_IDEALNORS_ITEM_ID'       =>  620253896,
        'EDD_WOO_IDEALNORS_ITEM_NAME' => 'Idealno.rs XML za Woocommerce',
        'WOONINJA_IDEALNORS_VERSION'  => '0.0.8',
    ];



    /**
     * WP Plugin options
     * Dont change its values or order, it can lead to plugin malfuction
     * 
     * [key, default value]
     */
    public static $wpoptions = [
        ['woo_idealnors_key',          ''],    // string/null - key store
        ['woo_idealnors_expired',      'false'], // BOOL - check if expired
        ['woo_idealnors_activated',    'false'],
        ['woo_idealnors_lastcheck',    ''],    // Last call on licensing system 
        ['woo_idealnors_buyerinfo',    'a:0:{}'],
        ['woo_idealnors_trial',        'true'],    // Is it trial?
        ['woo_idealnors_defaultlang',  'rs'], 
        ['woo_idealnors_export_type',  'all'], // default all, specific, none
        ['woo_idealnors_save_type',    'update'], // default update, cron (on every product update)
        ['woo_idealnors_ignore_products', 'false'],    // default false, true - used to ignore specific products on export 
        ['woo_idealnors_ignore_products_value', ''], // default emtpy, used for storing ignore data only
        ['woo_idealnors_ignore_categories', 'false'],    // default false, true - used to ignore specific products on export 
        ['woo_idealnors_ignore_categories_value', ''], // default emtpy, used for storing ignore data only
        ['woo_idealnors_taxrate', 0], // default emtpy, used for storing ignore data only
        ['woo_idealnors_clubprice', 0], // default emtpy, used for storing ignore data only
        ['woo_idealnors_delivery_price', 0], // default 0, used for marking delivery price
        ['woo_idealnors_delivery_min', 0], // default 0, minimal days for delivery
        ['woo_idealnors_delivery_max', 0], // default 0, max days for delivery
        ['woo_idealnors_export_utm', 'false'], // default 0, if set products will be export with custom crafted UTM params
        ['woo_idealnors_utm_source', ''], // default '', utm source param
        ['woo_idealnors_utm_medium', ''], // default '', utm source param
        ['woo_idealnors_utm_campaign', ''], // default '', utm campaign param    
    ];


    /**
     * Hook names list
     *
     * @var array
     */
    public static $hooks = [
        'woo_idealnors_box'
    ];


    public static $languages = [
        'sl' => 'Slovenčina',
        'en' => 'English',
        'ba' => 'Bosanski',
        'rs' => 'Srbski',
        'hr' => 'Hrvatski'
    ];

    /**
     * Plugin tab nav
     * 
     * First item of list is default page
     */
    public static $tabpages = [
        'homepage' =>[
            'sl' => 'Glavno',
            'en' => 'Main',
            'ba' => 'Glavno',
            'hr' => 'Glavno',
            'rs' => 'Glavno',
        ],
        'settings' =>[
            'sl' => 'Postavke',
            'en' => 'Settings',
            'ba' => 'Postavke',
            'hr' => 'Postavke',
            'rs' => 'Postavke',
        ],
        'products' => [
            'sl' => 'Izdelki',
            'en' => 'Products',
            'ba' => 'Proizvodi',
            'hr' => 'Proizvodi',
            'rs' => 'Proizvodi',
        ],
        'categories' => [
            'sl' => 'Kategorije',
            'en' => 'Categories',
            'ba' => 'Kategorije',
            'hr' => 'Kategorije',
            'rs' => 'Kategorije',
        ],
        'export' => [
            'sl' => 'Izvoz',
            'en' => 'Export',
            'ba' => 'Izvoz',
            'hr' => 'Izvoz',
            'rs' => 'Izvoz',
        ]
    ];



    /**
     * Translates for plugin
     */
    public static $translates = [
        0 =>[
            'sl' => 'WooCommerce ni mogoče najti. Za delovanje teh vtičnikov je potreben WooCommerece.',
            'en' => 'WooCommerce is not found. This plugins requires WooCommerece to work.',
            'ba' => 'WooCommerce nije pronadjen. Ovaj plugin zahtijeva WooCommerce za rad.',
            'hr' => 'WooCommerce nije pronadjen. Ovaj plugin zahtijeva WooCommerce za rad.',
            'rs' => 'WooCommerce nije pronadjen. Ovaj plugin zahtijeva WooCommerce za rad.'
        ],
        1 => [
            'sl' => 'Uporabljate poskusno različico tega vtičnika. Razmislite o nakupu licence za polne funkcije in podporo.',
            'en' => 'You are using a trial version of this plugin. This is an open-source plugin.',
            'ba' => 'Koristite probnu verziju ovog plugina. Kupite licencu za plugin da bi koristili sve opcije i podršku.',
            'hr' => 'Koristite probnu verziju ovog plugina. Kupite licencu za plugin da bi koristili sve opcije i podršku.',
            'rs' => 'Koristite probnu verziju ovog plugina. Kupite licencu za plugin da bi koristili sve opcije i podršku.'
        ],
        2 => [
            'sl' => '',
            'en' => 'has expired license. If you want to still use this plugin, renew your license please. <a href="https://wooninja.si/checkout/?edd_license_key=%s&download_id=%d">We are offering 50%% discount on renew.</a>',
            'ba' => 'ima isteklu licencu. Ako zelite da koristite plugin i dalje, molimo Vas da obnovite licencu. <a href="https://wooninja.si/checkout/?edd_license_key=%s&download_id=%d">Nudimo 50%% popusta na sljedecoj obnovi licence.</a>',
            'hr' => 'ima isteklu licencu. Ako zelite da koristite plugin i dalje, molimo Vas da obnovite licencu. <a href="https://wooninja.si/checkout/?edd_license_key=%s&download_id=%d">Nudimo 50%% popusta na sljedecoj obnovi licence.</a>',
            'rs' => 'ima isteklu licencu. Ako zelite da koristite plugin i dalje, molimo Vas da obnovite licencu. <a href="https://wooninja.si/checkout/?edd_license_key=%s&download_id=%d">Nudimo 50%% popusta na sljedecoj obnovi licence.</a>',
        ],
        3 => [
            'sl' => 'uporabljate preskusno različico s preskusno omejitvijo <strong>%s/%s</strong>. Za popolno podporo vtičnikov kupite licenco na <a href="https://woo.ninja/"> Woo.Ninja </a>',
            'en' => 'you are using trial version with trial limit <strong>%s/%s</strong>. This is an open-source plugin provided without support.',
            'ba' => 'koristite testnu verziju sa <strong>%s/%s</strong> ostalih pokusaja. Za punu podrsku plugina, molimo Vas kupite plugin na <a href="https://woo.ninja/">Woo.Ninja</a>',
            'hr' => 'koristite testnu verziju sa <strong>%s/%s</strong> ostalih pokusaja. Za punu podrsku plugina, molimo Vas kupite plugin na <a href="https://woo.ninja/">Woo.Ninja</a>',
            'rs' => 'koristite testnu verziju sa <strong>%s/%s</strong> ostalih pokusaja. Za punu podrsku plugina, molimo Vas kupite plugin na <a href="https://woo.ninja/">Woo.Ninja</a>'
        ],
        4 =>[
            'sl' => 'Vse nastavitve v zvezi z vtičnikom',
            'en' => 'All settings regarding plugin work.',
            'ba' => 'Sve postavke vezane za rad plugina.',
            'hr' => 'Sve postavke vezane za rad plugina.',
            'rs' => 'Sve postavke vezane za rad plugina.'
        ],
        5 =>[
            'sl' => 'Izberite jezik',
            'en' => 'Choose language',
            'ba' => 'Izaberite jezik',
            'hr' => 'Izaberite jezik',
            'rs' => 'Izaberite jezik'
        ],
        6 =>[
            'sl' => 'Jezik ne obstaja.',
            'en' => 'Language doesnt exists.',
            'ba' => 'Ne postoji odabrani jezik.',
            'hr' => 'Ne postoji odabrani jezik.',
            'rs' => 'Ne postoji odabrani jezik.'
        ],
        7 =>[
            'sl' => 'Jezik vtičnikov je zdaj: %s',
            'en' => 'Plugin language is now: %s',
            'ba' => 'Odabrani jezik plugina je: %s',
            'hr' => 'Odabrani jezik plugina je: %s',
            'rs' => 'Odabrani jezik plugina je: %s'
        ],
        8 =>[
            'sl' => 'Uspešno ste posodobili nastavitve.',
            'en' => 'You have updated settings successfully.',
            'ba' => 'Uspješno ste promijenili postavke.',
            'hr' => 'Uspješno ste promijenili postavke.',
            'rs' => 'Uspješno ste promenili postavke.'
        ],
        9 =>[
            'sl' => 'Shrani',
            'en' => 'Save',
            'ba' => 'Spremite',
            'hr' => 'Spremite',
            'rs' => 'Spremite'
        ],
        10 => [
            'sl' => 'Preskok izdelka: %s Problem: %s',
            'en' => 'Skipping product: %s Problem: %s',
            'ba' => 'Preskacemo proizvod: %s Problem: %s',
            'hr' => 'Preskacemo proizvod: %s Problem: %s',
            'rs' => 'Preskacemo proizvod: %s Problem: %s'
        ],
        11 => [
            'sl' => 'Na zalogi',
            'en' => 'On stock',
            'ba' => 'U trgovini',
            'hr' => 'U trgovini',
            'rs' => 'U trgovini'
        ],
        12 => [
            'sl' => 'U prihodu',
            'en' => 'Incomming',
            'ba' => 'Uskoro dostupan',
            'hr' => 'Uskoro dostupan',
            'rs' => 'Uskoro dostupan'
        ],
        13 => [
            'sl' => 'Preverite dobavljivost',
            'en' => 'Check availability',
            'ba' => 'Provjerite dobavljivost',
            'hr' => 'Provjerite dobavljivost',
            'rs' => 'Provjerite dobavljivost'
        ],
        14 =>[
            'sl' => 'Preverite dobavljivost',
            'en' => 'Check availability',
            'ba' => 'Provjerite dobavljivost',
            'hr' => 'Provjerite dobavljivost',
            'rs' => 'Provjerite dobavljivost'
        ],
        15 =>[
            'sl' => 'Nastavitve izdelka',
            'en' => 'Product settings',
            'ba' => 'Postavke proizvoda',
            'hr' => 'Postavke proizvoda',
            'rs' => 'Postavke proizvoda'
        ],
        16 =>[
            'sl' => 'Prišlo je do napake pri deaktivaciji stare licence: %s. Prosim, poizkusite ponovno!',
            'en' => 'Deactivating license %s went wrong. Please, try again.',
            'ba' => 'Deaktiviranje licence %s je pošlo po zlu. Molimo Vas da pokušate ponovo.',
            'hr' => 'Deaktiviranje licence %s je pošlo po zlu. Molimo Vas da pokušate ponovo.',
            'rs' => 'Deaktiviranje licence %s je pošlo po zlu. Molimo Vas da pokušate ponovo.'
        ],
        17 =>[
            'sl' => 'Uspešno ste registrirali licenco.',
            'en' => 'You have successfully registered a license.',
            'ba' => 'Uspješno ste registrirali licencu.',
            'hr' => 'Uspješno ste registrirali licencu.',
            'rs' => 'Uspešno ste registrirali licencu.'
        ],
        18 =>[
            'sl' => 'Uporabnik računa',
            'en' => 'Account user',
            'ba' => 'Korisnik računa',
            'hr' => 'Korisnik računa',
            'rs' => 'Korisnik računa'
        ],
        19 =>[
            'sl' => 'Datum poteka',
            'en' => 'Expiration date',
            'ba' => 'Datum isteka',
            'hr' => 'Datum isteka',
            'rs' => 'Datum isteka'
        ],
        20 =>[
            'sl' => 'ID plačila',
            'en' => 'Payment ID',
            'ba' => 'ID uplatnice',
            'hr' => 'ID uplatnice',
            'rs' => 'ID uplatnice'
        ],
        21 => [
            'sl' => 'Pred uporabo teh funkcij vnesite licenco!',
            'en' => 'Please enter a license before using these features!',
            'ba' => 'Molimo Vas, unesite licencu prije korištenja ovih opcija!',
            'hr' => 'Molimo Vas, unesite licencu prije korištenja ovih opcija!',
            'rs' => 'Molimo Vas, unesite licencu prije korištenja ovih opcija!'
        ]
    ];

}

