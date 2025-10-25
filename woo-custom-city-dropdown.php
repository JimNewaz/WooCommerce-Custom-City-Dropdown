<?php
/**
 * Plugin Name: WooCommerce Custom City Dropdown
 * Plugin URI: https://yoursite.com
 * Description: Replaces the default city field with a dropdown of specific Virginia cities organized by region
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * Text Domain: woo-custom-city
 * Requires at least: 5.0
 * Requires PHP: 7.0
 * WC requires at least: 3.0
 * WC tested up to: 8.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class WooCommerce_Custom_City_Dropdown {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Change city field to select dropdown
        add_filter('woocommerce_checkout_fields', array($this, 'customize_city_field'));
        add_filter('woocommerce_default_address_fields', array($this, 'customize_default_city_field'));
        
        // Validate the city field
        add_action('woocommerce_after_checkout_validation', array($this, 'validate_city_field'), 10, 2);
        
        // Add custom CSS for better styling
        add_action('wp_head', array($this, 'add_custom_styles'));
    }
    
    /**
     * Get list of allowed cities organized by region
     */
    private function get_cities_list() {
        return array(
            'Independent Cities' => array(
                'Alexandria (Independent City)',
                'Fairfax (Independent City)',
                'Falls Church (Independent City)',
                'Manassas (Independent City)',
                'Manassas Park'
            ),
            'Arlington County' => array(
                'Arlington'
            ),
            'Fairfax County' => array(
                'Alexandria (Fairfax County)',
                'Annandale',
                'Burke',
                'Centreville',
                'Chantilly',
                'Clifton',
                'Dunn Loring',
                'Fairfax (Fairfax County)',
                'Fairfax Station',
                'Falls Church (Fairfax County)',
                'Fort Belvoir',
                'Great Falls',
                'Herndon',
                'Lorton',
                'McLean',
                'Merrifield',
                'Mount Vernon',
                'Oakton',
                'Reston',
                'Springfield',
                'Tysons',
                'Vienna'
            ),
            'Loudoun County' => array(
                'Aldie',
                'Ashburn',
                'Bluemont',
                'Brambleton',
                'Hamilton',
                'Leesburg',
                'Lovettsville',
                'Middleburg',
                'Purcellville',
                'Round Hill',
                'South Riding',
                'Sterling'
            ),
            'Prince William County' => array(
                'Bristow',
                'Catharpin',
                'Dale City',
                'Dumfries',
                'Gainesville',
                'Haymarket',
                'Lake Ridge',
                'Manassas (Prince William County)',
                'Montclair',
                'Nokesville',
                'Occoquan',
                'Quantico',
                'Triangle',
                'Woodbridge'
            )
        );
    }
    
    /**
     * Format cities for select dropdown with optgroups
     */
    private function format_cities_for_select() {
        $cities = $this->get_cities_list();
        $formatted = array('' => __('Select a city *', 'woo-custom-city'));
        
        foreach ($cities as $region => $city_list) {
            foreach ($city_list as $city) {
                $formatted[$city] = $city;
            }
        }
        
        return $formatted;
    }
    
    /**
     * Customize checkout city field
     */
    public function customize_city_field($fields) {
        $cities = $this->format_cities_for_select();
        
        // Modify billing city field
        if (isset($fields['billing']['billing_city'])) {
            $fields['billing']['billing_city']['type'] = 'select';
            $fields['billing']['billing_city']['options'] = $cities;
            $fields['billing']['billing_city']['class'] = array('form-row-wide', 'address-field', 'update_totals_on_change');
            $fields['billing']['billing_city']['custom_attributes'] = array(
                'data-placeholder' => __('Select a city', 'woo-custom-city')
            );
        }
        
        // Modify shipping city field
        if (isset($fields['shipping']['shipping_city'])) {
            $fields['shipping']['shipping_city']['type'] = 'select';
            $fields['shipping']['shipping_city']['options'] = $cities;
            $fields['shipping']['shipping_city']['class'] = array('form-row-wide', 'address-field', 'update_totals_on_change');
            $fields['shipping']['shipping_city']['custom_attributes'] = array(
                'data-placeholder' => __('Select a city', 'woo-custom-city')
            );
        }
        
        return $fields;
    }
    
    /**
     * Customize default address fields (for My Account, etc.)
     */
    public function customize_default_city_field($fields) {
        if (isset($fields['city'])) {
            $cities = $this->format_cities_for_select();
            $fields['city']['type'] = 'select';
            $fields['city']['options'] = $cities;
            $fields['city']['class'] = array('form-row-wide', 'address-field');
        }
        
        return $fields;
    }
    
    /**
     * Validate city field to ensure only allowed cities are selected
     */
    public function validate_city_field($data, $errors) {
        $allowed_cities = $this->format_cities_for_select();
        unset($allowed_cities['']); // Remove the placeholder option
        
        if (!empty($data['billing_city']) && !array_key_exists($data['billing_city'], $allowed_cities)) {
            $errors->add('billing_city', __('Please select a valid city from the dropdown.', 'woo-custom-city'));
        }
        
        if (!empty($data['shipping_city']) && !array_key_exists($data['shipping_city'], $allowed_cities)) {
            $errors->add('shipping_city', __('Please select a valid shipping city from the dropdown.', 'woo-custom-city'));
        }
    }
    
    /**
     * Add custom CSS for better styling
     */
    public function add_custom_styles() {
        if (is_checkout() || is_account_page()) {
            ?>
            <style type="text/css">
                .woocommerce-checkout #billing_city_field select,
                .woocommerce-checkout #shipping_city_field select,
                .woocommerce-address-fields #billing_city_field select,
                .woocommerce-address-fields #shipping_city_field select {
                    width: 100%;
                    padding: 10px;
                    border: 1px solid #ddd;
                    border-radius: 4px;
                    font-size: 14px;
                }
                
                .woocommerce-checkout #billing_city_field select:focus,
                .woocommerce-checkout #shipping_city_field select:focus {
                    border-color: #007cba;
                    outline: none;
                    box-shadow: 0 0 0 1px #007cba;
                }
            </style>
            <?php
        }
    }
}

// Initialize the plugin
function woo_custom_city_dropdown_init() {
    new WooCommerce_Custom_City_Dropdown();
}
add_action('plugins_loaded', 'woo_custom_city_dropdown_init');