<?php

namespace IdealnoRs;

if (!defined('ABSPATH')) exit;

/**
 * Hooks class
 * 
 * Used for plugin to listen for specific hooks which affects plugin globaly (activation, deinstall, etc..)
 */

class Hooks{

    /**
     * Lang choosen holder
     *
     * @var string
     */
    public $lang = 'sl';

    /**
     * $post_fields
     *
     * Array which contains checkbox custom fields
     * 
     * @var array
     */
    public $checkbox_fields = [
        [
            'name' => [
                'sl' => 'Izvozi izdelk',
                'en' => 'Export product',
                'ba' => 'Izvozi proizvod',
                'hr' => 'Izvozi proizvod',
                'rs' => 'Izvozi proizvod',
            ],
            'help' => [
                'sl' => 'Če je izbrano, bo ta izdelek izvožen pri določeni vrsti izvoza. To ne vpliva na celoten izvoz seznama.',
                'en' => 'If selected, this product will be exported on a specific type of export. This doesnt affect the whole list export.',
                'ba' => 'Ako je oznaceno, ovaj proizvod ce biti exportan pri opciji exporta specificnih proizvoda. Export citave liste ne utice na ovaj odabir.',
                'hr' => 'Ako je oznaceno, ovaj proizvod ce biti exportan pri opciji exporta specificnih proizvoda. Export citave liste ne utice na ovaj odabir.',
                'rs' => 'Ako je oznaceno, ovaj proizvod ce biti exportan pri opciji exporta specificnih proizvoda. Export citave liste ne utice na ovaj odabir.',
            ],
            'slug' => 'woo_idealnors_field_exportproduct',
            'available' => true,
            'type' => 'checkbox'
        ],
        [
            'name' => [
                'sl' => 'Ignoriraj izdelk',
                'en' => 'Ignore product',
                'ba' => 'Ignoriraj  proizvod',
                'hr' => 'Ignoriraj  proizvod',
                'rs' => 'Ignoriraj  proizvod',
            ],
            'help' => [
                'sl' => 'Če izberete to možnost, bo izdelek prezrt pri vsem izvozu. To bo preglasilo poseben izvoz.',
                'en' => 'If this is chosen, the product will be ignored on all exports. This will override specific export.',
                'ba' => 'Ako je ova opcija odabrana, ovaj proizvod nece biti exportan nigdje. Ova opcija ignorira specificni export opciju.',
                'hr' => 'Ako je ova opcija odabrana, ovaj proizvod nece biti exportan nigdje. Ova opcija ignorira specificni export opciju.',
                'rs' => 'Ako je ova opcija odabrana, ovaj proizvod nece biti exportan nigdje. Ova opcija ignorira specificni export opciju.',
            ],
            'slug' => 'woo_idealnors_field_ignoreproduct',
            'available' => true,
            'type' => 'checkbox'
        ]
    ]; 

    /**
     * $text_fields
     * 
     * Text type inputs
     *
     * @var array
     */
    public $text_fields = [
        [
            'name' => [
                'sl' => 'EAN',
                'en' => 'EAN',
                'ba' => 'EAN',
                'hr' => 'EAN',
                'rs' => 'EAN',
            ],
            'help' => [
                'sl' => 'Koda EAN je edinstvena koda izdelka',
                'en' => 'EAN code is Idealno.rs unique product code.',
                'ba' => 'EAN kod je unikatni kod proizvoda.',
                'hr' => 'CUEANIN kod je unikatni kod proizvoda.',
                'rs' => 'EAN kod je unikatni kod proizvoda.',
            ],
            'slug' => 'woo_idealnors_field_ean',
            'available' => true,
            'type' => 'text',
            'required' => true,
        ],
        [
            'name' => [
                'sl' => 'CUIN',
                'en' => 'CUIN',
                'ba' => 'CUIN',
                'hr' => 'CUIN',
                'rs' => 'CUIN',
            ],
            'help' => [
                'sl' => 'Koda CUIN je edinstvena koda izdelka',
                'en' => 'CUIN code is Idealno.rs unique product code.',
                'ba' => 'CUIN kod je unikatni kod proizvoda.',
                'hr' => 'CUIN kod je unikatni kod proizvoda.',
                'rs' => 'CUIN kod je unikatni kod proizvoda.',
            ],
            'slug' => 'woo_idealnors_field_cuin',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Ime proizvajalca',
                'en' => 'Manufacturer name',
                'ba' => 'Ime proizvodjaca',
                'hr' => 'Ime proizvodjaca',
                'rs' => 'Ime proizvodjaca',
            ],
            'help' => [
                'sl' => 'Vnesite ime proizvajalca.',
                'en' => 'Insert the name of the manufacturer.',
                'ba' => 'Unesite ime proizvodjaca ovog proizvoda.',
                'hr' => 'Unesite ime proizvodjaca ovog proizvoda.',
                'rs' => 'Unesite ime proizvodjaca ovog proizvoda.',
            ],
            'slug' => 'woo_idealnors_field_brand',
            'available' => true,
            'type' => 'text', 
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Garancija',
                'en' => 'Warranty',
                'ba' => 'Garancija',
                'hr' => 'Garancija',
                'rs' => 'Garancija',
            ],
            'help' => [
                'sl' => 'Neka vrsta garancije za ta izdelek. Opomba: največ 50 znakov.',
                'en' => 'Some type of warranty of this product. Note: 50 chars maximal.',
                'ba' => 'Garancija na proizvod, pisana vrijednost. Napomena: 50 znakova maximalno.',
                'hr' => 'Garancija na proizvod, pisana vrijednost. Napomena: 50 znakova maximalno.',
                'rs' => 'Garancija na proizvod, pisana vrednost. Napomena: 50 znakova maximalno.',
            ],
            'slug' => 'woo_idealnors_field_warranty',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Video (YouTube, Vimeo...)',
                'en' => 'Video (YouTube, Vimeo...)',
                'ba' => 'Video (YouTube, Vimeo...)',
                'hr' => 'Video (YouTube, Vimeo...)',
                'rs' => 'Video (YouTube, Vimeo...)',
            ],
            'help' => [
                'sl' => 'Videoposnetek prikazuje nekaj informacij o izdelku.',
                'en' => 'Video showing some information about the product.',
                'ba' => 'Video koji pokazuje dodatne informacije o proizvodu.',
                'hr' => 'Video koji pokazuje dodatne informacije o proizvodu.',
                'rs' => 'Video koji pokazuje dodatne informacije o proizvodu.',
            ],
            'slug' => 'woo_idealnors_field_video',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Barva',
                'en' => 'Color',
                'ba' => 'Boja',
                'hr' => 'Boja',
                'rs' => 'Boja',
                ],
            'help' => [
                'sl' => 'Barva izdelka.',
                'en' => 'Color of the product. No special characters, input normal color description (black, white...)',
                'ba' => 'Boja proizvoda. Bez dodatnih znakova, normalni opis boje (bijela, crna...)',
                'hr' => 'Boja proizvoda. Bez dodatnih znakova, normalni opis boje (bijela, crna...)',
                'rs' => 'Boja proizvoda. Bez dodatnih znakova, normalni opis boje (bela, crna...)',
            ],
            'slug' => 'woo_idealnors_field_color',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Velikost',
                'en' => 'Size',
                'ba' => 'Veličina',
                'hr' => 'Veličina',
                'rs' => 'Veličina',
                ],
            'help' => [
                'sl' => 'Velikost izdelka. Za izdelke uporabite posebne velikosti. Primer: XL, M, S, EU 42 ...',
                'en' => 'Size of the product. Use specific sizes for products. Example: XL, M, S, EU 42...',
                'ba' => 'Veličina proizvoda. Koristite specifične veličine. Na primjer: XL, M, S, EU 42...',
                'hr' => 'Veličina proizvoda. Koristite specifične veličine. Na primjer: XL, M, S, EU 42...',
                'rs' => 'Veličina proizvoda. Koristite specifične veličine. Na primjer: XL, M, S, EU 42...',
            ],
            'slug' => 'woo_idealnors_field_size',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
        [
            'name' => [
                'sl' => 'Darilo z izdelkom',
                'en' => 'Gift with product',
                'ba' => 'Poklon sa proizvodom',
                'hr' => 'Poklon sa proizvodom',
                'rs' => 'Poklon sa proizvodom',
                ],
            'help' => [
                'sl' => 'Darilo z besedami, ki jih želite podariti z nakupom tega izdelka. Opomba: največ 150 znakov.',
                'en' => 'Gift in words which you want to give with buying this product. Note: max 150 chars.',
                'ba' => 'Dar koji zelite pokloniti sa kupovinom ovog proizvoda. Napomena: maximalno 150 znakova texta.',
                'hr' => 'Dar koji zelite pokloniti sa kupovinom ovog proizvoda. Napomena: maximalno 150 znakova texta.',
                'rs' => 'Dar koji zelite pokloniti sa kupovinom ovog proizvoda. Napomena: maximalno 150 znakova texta.',
            ],
            'slug' => 'woo_idealnors_field_gift',
            'available' => true,
            'type' => 'text',
            'required' => false,
        ],
    ];


    public $select_fields = [
        [
            'name' => [
                'sl' => 'Stanje izdelka',
                'en' => 'Condition of the product',
                'ba' => 'Stanje proizvoda',
                'hr' => 'Stanje proizvoda',
                'rs' => 'Stanje proizvoda',
            ],
            'help' => [
                'sl' => 'Izberite stanje izdelka. Privzeto je novo, če je pogoj drugačen, izberite s seznama.',
                'en' => 'Choose the condition of the product. Default is new if the condition is different choose from the list.',
                'ba' => 'Odaberi stanje proizvoda. Po defaultu je stanje novo, ako je stanje drugacije odaberite sa liste.',
                'hr' => 'Odaberi stanje proizvoda. Po defaultu je stanje novo, ako je stanje drugacije odaberite sa liste.',
                'rs' => 'Odaberi stanje proizvoda. Po defaultu je stanje novo, ako je stanje drugacije odaberite sa spiska.',
            ],
            'fields' =>[
                'new' => 'New',
                'renew' => 'Renewed',
                'refurbished' => 'Refurbished',
                'reboxed' => 'Reboxed',
                'used' => 'Used'
            ],
            'slug' => 'woo_idealnors_field_condition',
            'available' => true,
            'default' => 'new',
            'emptyFirst' => false,
            'type' => 'select'
        ],
        [
            'name' => [
                'sl' => 'Spol',
                'en' => 'Gender',
                'ba' => 'Spol',
                'hr' => 'Spol',
                'rs' => 'Spol',
            ],
            'help' => [
                'sl' => 'Izberite spol. Pustite prazno, če je ni mogoče dodeliti.',
                'en' => 'Choose sex. Leave blank if it is non-assignable.',
                'ba' => 'Odaberite pol. Ostavite prazno ako nije moguce dodijeliti.',
                'hr' => 'Odaberite pol. Ostavite prazno ako nije moguce dodijeliti.',
                'rs' => 'Odaberite pol. Ostavite prazno ako nije moguce dodijeliti.',
            ],
            'fields' =>[
                'male' => 'Male',
                'unisex' => 'Unisex',
                'female' => 'Female',
            ],
            'slug' => 'woo_idealnors_field_gender',
            'available' => true,
            'default' => '',
            'emptyFirst' => true,
            'type' => 'select'
        ],
        [
            'name' => [
                'sl' => 'Starostna skupina',
                'en' => 'Age group',
                'ba' => 'Starosna grupa',
                'hr' => 'Starosna grupa',
                'rs' => 'Starosna grupa',
            ],
            'help' => [
                'sl' => 'Izberite starostno skupino. Pustite prazno, če izdelek ni določen glede na starostno skupino.',
                'en' => 'Choose age group. Leave empty if the product is not defined by age group.',
                'ba' => 'Izaberite starosnu skupinu. Ostavite  prazno ako nije moguce dodijeliti.',
                'hr' => 'Izaberite starosnu skupinu. Ostavite  prazno ako nije moguce dodijeliti.',
                'rs' => 'Izaberite starosnu skupinu. Ostavite  prazno ako nije moguce dodijeliti.',
            ],
            'fields' =>[
                'adult' => 'Adult',
                'kids' => 'Kids',
                'toddler' => 'Toddler',
                'infant ' => 'Infant',
                'newborn' => 'Newborn'
            ],
            'slug' => 'woo_idealnors_field_agegroup',
            'available' => true,
            'default' => '',
            'emptyFirst' => true,
            'type' => 'select'
        ],
    ];

    /**
     * Append listeners
     */
    public function __construct()
    {

        $this->lang = get_option('woo_idealnors_defaultlang', 'rs');

        // Hook js scripts
        add_action( 'admin_enqueue_scripts', [$this, 'includeJqueryPlugins']);
            
        // Create metabox
        add_action('add_meta_boxes', [$this, 'createBox']);

        // Hook product save action
        add_action('save_post', [$this, 'handleProductPost']);

        // Hook script enquoe
        //add_action( 'plugins_loaded', [$this, 'registerExportEndpoint']);

        // Check save type
        if(get_option('woo_idealnors_save_type', 'update') === 'update'){

            // Add action on product save
            add_action( 'woocommerce_process_product_meta', function(){

                // Call XML class
                $export = new ExportXML();
                $export->generateXML();
            }, 15);
        }

    }

    /**
     * createPanel
     * 
     * Create panel in woo data product page
     *
     * @return void
     */
    public function createBox(){

        // Call wp function
        add_meta_box(
            Config::$hooks[0],            // Unique ID
            Config::$name,                // Box title
            [$this, 'cenejeBoxRs'],   // Content callback, must be of type callable
            'product',                    // Post type
            'side'
        );
      
    }

    /**
     * cenejeBoxRs
     *
     * Returns cenejeRs html box
     * 
     * @return string
     */
    public function cenejeBoxRs($post){

        ?>

		<style>
			.inside .woocommerce-help-tip {
				margin-left: 0;
			}
		</style>

        <strong><?php echo esc_html(Config::$translates[15][$this->lang]); ?></strong>

        <br>
        <br>

        <?php wp_nonce_field('woo_idealnors_save_product', 'woo_idealnors_product_nonce'); ?>

        <div class="js-gls-form">

            <input hidden value="<?php echo esc_attr(count($this->checkbox_fields)); ?>" />

            <?php

                // Parse all checkboxes fields
                foreach ($this->checkbox_fields as $field):
            
                    $postdata = get_post_meta( $post->ID,  $field['slug'], true );
                    
                    ?>

                        <input type="checkbox" class="service-item" name="<?php echo esc_attr($field["slug"]); ?>" <?php echo $postdata !== 'false' && $postdata !== '' ? 'checked' : ''; ?>>
                        <?php echo esc_html($field['name'][$this->lang]); ?>
                        <?php echo wc_help_tip($field['help'][$this->lang]); ?>

                        <br>

                    <?php
                endforeach;

                ?> 
                
                <hr>

                <?php

                // Parse all text fields
                foreach ($this->text_fields as $field):
                    

                    ?>

                        <span style="margin-top: 15px"><?php echo esc_html($field['name'][$this->lang]); ?></span> <?php echo wc_help_tip($field['help'][$this->lang]); ?> <br>
                        <input type="text" style="width: 100%;" class="service-item" name="<?php echo esc_attr($field["slug"]); ?>" value="<?php echo esc_attr(get_post_meta( $post->ID,  $field['slug'], true )); ?>">
                
                        <br>
                    <?php

            
                endforeach;

                ?> 
            
                <hr>
                <?php

                // Parse all text fields
                foreach ($this->select_fields as $field):
                    
                    // Get already selected option if set
                    $selected = get_post_meta($post->ID, $field['slug'], true);

                    ?>

                        <span style="margin-top: 15px"><?php echo esc_html($field['name'][$this->lang]); ?></span> <?php echo wc_help_tip($field['help'][$this->lang]); ?> <br>

                        <select id="<?php echo esc_attr($field["slug"]); ?>" name="<?php echo esc_attr($field["slug"]); ?>" style="width: 100%;">
                           
                           <?php
                            
                            if($field['emptyFirst']){
                               ?>
                                <option value="">Brez opcije</option>
                               <?php
                            }

                            // Map trough categories
                            foreach($field['fields'] as $key => $fieldtext):  
                                
                                ?>
                                    <option <?php selected($key, $selected); ?> value="<?php echo esc_attr($key); ?>" ><?php echo esc_html($fieldtext); ?></option>
                                <?php

                            endforeach;

                            ?>
                        </select>
                        <br>
                    <?php


                endforeach;

                ?> 
            
                <?php

                // Get ceneje categories from csv files language dependant
                $categories = self::getCenejeCategories($this->lang);

                // Get already selected option if set
                $selected = get_post_meta($post->ID, 'woo_idealnors_field_categoryid', true);

                ?>

                    <span style="margin-top: 15px">Kategorija</span> <br>
                    <select id="woo_idealnors_field_categoryid" name="woo_idealnors_field_categoryid" style="width: 100%;">
                        <option value="">Brez opcije</option>

                        <?php

                            // Map trough categories
                            foreach($categories as $key => $category):  

                                // Skip first element
                                if($key === 0) continue;

                                ?>
                                    <option <?php selected($category[3], $selected); ?> value="<?php echo esc_attr($category[3]); ?>" ><?php echo esc_html(str_replace('- -> ', '', $category[5])); ?></option>
                                <?php

                            endforeach;
                        ?>

                    </select>
            
            <br>
            <hr> 

        </div>

        <script>

            jQuery(document).ready(function($) {
                $('#woo_idealnors_field_categoryid').select2({
                    containerCssClass : "category-select"
                });
            });

        </script>


        <style>
			.category-select{
				max-width: 100%;
			}
		</style>

        <?php     
    }

    /**
     * handleProductPost
     *
     * Handle fields which are submited on product update
     * 
     * @param [object] $body
     * @return void
     */
    public function handleProductPost($id){

        // Verify nonce before processing
        if (!isset($_POST['woo_idealnors_product_nonce']) || !wp_verify_nonce($_POST['woo_idealnors_product_nonce'], 'woo_idealnors_save_product')) {
            return;
        }

        // Parse trough our text fields
        foreach($this->text_fields as $field){

            if(isset($_POST[$field['slug']])){
                update_post_meta($id, $field['slug'], sanitize_text_field($_POST[$field['slug']]));
            }else{
                update_post_meta($id, $field['slug'], false);
            }
        }

        // Parse trough our checkbox fields
        foreach($this->checkbox_fields as $field){

            if(isset($_POST[$field['slug']])){
                update_post_meta($id, $field['slug'], sanitize_text_field($_POST[$field['slug']]));
            }else{
                update_post_meta($id, $field['slug'], false);
            }
        }

        // Check select fields
        foreach($this->select_fields as $field){

            if(isset($_POST[$field['slug']])){
                update_post_meta($id, $field['slug'], sanitize_text_field($_POST[$field['slug']]));
            }
        }

        // Check if category is set
        if(isset($_POST['woo_idealnors_field_categoryid'])){
            update_post_meta($id, 'woo_idealnors_field_categoryid', sanitize_text_field($_POST['woo_idealnors_field_categoryid']));
        }
    }

    /**
     * includeJqueryPlugins
     *
     * Include custom jquery plugins
     * 
     * @return void
     */
    public function includeJqueryPlugins(){

        // Include select2 jquery plugin and register it
        wp_register_style( 'select2css', 'https://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
        wp_register_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
        wp_enqueue_style( 'select2css' );
        wp_enqueue_script( 'select2' );

    }

    /**
     * getCenejeCategories
     * 
     * Get all official categories from ceneje websites
     *
     * @return array
     */
    public static function getCenejeCategories($lang){

        switch ($lang) {

            case 'sl':

                return array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'static/ceneje.si-kategorije.csv'));
            
            case 'ba':
                
                return array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'static/idealno.ba-kategorije.csv'));

            case 'rs':

                return array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'static/idealno.rs-kategorije.csv'));

            case 'hr':

                return array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'static/jeftinije.hr-kategorije.csv'));
            
            default:

                // Default return slovenian
                return array_map('str_getcsv', file(plugin_dir_path(__FILE__) . 'static/ceneje.si-kategorije.csv'));
        }
    }



}