<?php
/*
	Plugin Name: Volumetric Shipping
	Plugin URI: 
	Description: "Volumteric Shipping" extension allows merchants to calculate shipping charges according to the volumetric weight rather than actual weight. .
	Version: 1.0
	Author: SunArc
	Author URI: https://sunarctechnologies.com/
	Text Domain: volumetric-shipping
	License: GPL2

*/

if ( ! defined( 'WPINC' ) ) {
 
    die;
 
}
 
/*
 * Check if WooCommerce is active
 */
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		add_action('admin_menu', 'vs_sunarc_add_pages');
		define('vs_sunarc_plugin_dir', dirname(__FILE__));
		function vs_sunarc_add_pages() {
			add_submenu_page('woocommerce', 'Table Rate', 'Table Rate', 'manage_options', 'table-rate', 'vs_table_rate_setting_page');
		}

		function vs_table_rate_setting_page() {
			include plugin_dir_path(__FILE__) . 'volumetric-shipping-tables.php';
		}
		function vs_wpdocs_selectively_enqueue_admin_script( $hook ) {

		wp_enqueue_style('related-pages-admin-styles2',plugins_url('/volumetric-shipping/') . '/assets/css/dataTables.bootstrap.min.css');
		wp_enqueue_style('related-pages-admin-styles3',plugins_url('/volumetric-shipping/') . '/assets/css/buttons.bootstrap.min.css');
		wp_enqueue_script('my_custom_script_custom1', plugins_url('/volumetric-shipping/') . '/assets/js/jquery.dataTables.min.js', array(), '1.0' );
		wp_enqueue_script( 'my_custom_script_custom2', plugins_url('/volumetric-shipping/') . '/assets/js/dataTables.bootstrap.min.js', array(), '1.0' );
		wp_enqueue_script( 'my_custom_script_custom3', plugins_url('/volumetric-shipping/') . '/assets/js/dataTables.buttons.min.js', array(), '1.0' );
		wp_enqueue_script( 'my_custom_script_jquery', plugins_url('/volumetric-shipping/') . '/assets/js/buttons.html5.min.js', array(), '1.0' );
		wp_enqueue_script( 'custom', plugins_url('/volumetric-shipping/') . '/assets/js/custom.js', array(), '1.0' );
		}
	add_action( 'admin_enqueue_scripts', 'vs_wpdocs_selectively_enqueue_admin_script' );
 
	
    function vs_sunarc_shipping_method() {
        if ( ! class_exists( 'vs_sunarc_shipping_method' ) ) {
            class vs_sunarc_shipping_method extends WC_Shipping_Method {
                 public function __construct() {
                    $this->id                 = 'sunarc'; 
                    $this->method_title       = __( 'Sunarc Shipping', 'sunarc' );  
                    $this->method_description = __( 'Custom Shipping Method for sunarc', 'sunarc' ); 
 
                    // Availability & Countries
                    
					$sunarc_country_state = get_option( 'sunarc_country_state' );
					$datas = unserialize($sunarc_country_state);
					$region =array();
					foreach($datas as $data)
					{
						$region[] = $data[1];
					}
			
					if (!in_array("*", $region)){
					$this->availability = 'including';
                    $this->countries = $region; 
					}
					
                    $this->init();
 
                    $this->enabled = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
                    $this->title = isset( $this->settings['title'] ) ? $this->settings['title'] : __( 'sunarc Shipping', 'sunarc' );
                }
                function init() {
                    $this->init_form_fields(); 
                    $this->init_settings(); 
                    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
                }
 
               
                function init_form_fields() { 
 
                    $this->form_fields = array(
 
                     'enabled' => array(
                          'title' => __( 'Enable', 'sunarc' ),
                          'type' => 'checkbox',
                          'description' => __( 'Enable this shipping.', 'sunarc' ),
                          'default' => 'yes'
                          ),
 
                     'title' => array(
                        'title' => __( 'Title', 'sunarc' ),
                          'type' => 'text',
                          'description' => __( 'Title to be display on site', 'sunarc' ),
                          'default' => __( 'sunarc Shipping', 'sunarc' )
                          ),
 
                     'weight' => array(
                        'title' => __( 'Weight (kg)', 'sunarc' ),
                          'type' => 'number',
                          'description' => __( 'Maximum allowed weight <br><a href="admin.php?page=table-rate">Add Table Rate</a>	', 'sunarc' ),
                          'default' => 100
                          ),
						  
					   
						 
						
                     );
 
                }
                public function calculate_shipping( $package = array() ) {
                    
                    $weight = 0;
                    $cost = 0;
                    $country = $package["destination"]["country"];
				   $vs_sunarc_shipping_method = new vs_sunarc_shipping_method();
                    $volumetricweight = (int) $vs_sunarc_shipping_method->settings['weight'];
			
                    foreach ( $package['contents'] as $item_id => $values ) 
                    { 
                     $_product = $values['data']; 
                     $product_id = $values['product_id'];
					 $weight = get_post_meta($product_id ,'_weight',true);
					 $width =  get_post_meta($product_id ,'_width',true);
					 $height = get_post_meta($product_id ,'_height',true);
					 $length = get_post_meta($product_id ,'_length',true);
					 $volumetric_weight =($length*$height*$width)/$volumetricweight;
					 $weightsum =$weightsum+$weight;
					 $volumetricsum= $volumetricsum+$volumetric_weight;
                     //$weight = $weight + $_product->get_weight() * $values['quantity']; 
                    }
                    $weight = wc_get_weight( $volumetricsum, 'kg' );
					$sunarc_country = get_option( 'sunarc_country_state' );
					$datass = unserialize($sunarc_country);
					$results = array();
					$nextweight= array();
					foreach($datass as $datas)
					{
					$results[][$datas[3]] = $datas[4];
					$nextweight[] =$datas[3];
					}
				
		     		 $k=1;
					 $lastElement = end($results);
					$lastprice = current(array_slice($lastElement, -1));
					
					foreach($results as $keyweight=> $shipping)
					{ 
						if($weight > $weightsum) {
						if($weight >=$keyweight && $weight < $nextweight[$k]   ) {
                        $cost = $shipping;
						break;
						} 
						else
						{
						$cost = $lastprice;
						}
					$k++;
						}
					else
						{
						if($weightsum >=$keyweight && $weightsum < $nextweight[$k]   ) {
                        $cost = $shipping;
						break;
						} 
						else
						{
						$cost = $lastprice;
						}
					$k++;
						}
					}
				
					 
					 
                   
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => $cost
                    );
 
                    $this->add_rate( $rate );
                    
                }
            }
        }
    }
 
    add_action( 'woocommerce_shipping_init', 'vs_sunarc_shipping_method' );
 
    function vs_add_sunarc_shipping_method( $methods ) {
        $methods[] = 'vs_sunarc_shipping_method';
        return $methods;
    }
 
    add_filter( 'woocommerce_shipping_methods', 'vs_add_sunarc_shipping_method' );
 
    
}
