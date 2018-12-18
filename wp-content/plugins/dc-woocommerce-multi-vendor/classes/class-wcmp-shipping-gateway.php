<?php

if (!defined('ABSPATH'))
    exit;

/**
 * WCMp Shipping Class
 *
 * @version		3.2.2
 * @package		WCMp
 * @author 		WC Marketplace
 */
class WCMp_Shipping_Gateway {

    /**
     * Initialize shipping.
     */
    public function __construct() {
        
        add_action('woocommerce_shipping_init', array(&$this, 'load_shipping_methods'));
        add_filter('woocommerce_shipping_methods', array(&$this, 'add_shipping_methods'));
        add_filter('wcmp_split_shipping_packages', array(&$this, 'add_vendor_id_to_package'));
    }
    
    /**
     * Loads shipping zones & methods.
     * 
     */
    public function load_shipping_methods() {
        self::load_class( 'shipping-zone', 'helpers' );
        self::load_class( 'shipping-method' );
    }
    /**
     * WCMp Shipping methods register themselves by returning their main class name through the woocommerce_shipping_methods filter.
     *
     * @return array
     */
    public function add_shipping_methods($methods) {
        $methods['wcmp_vendor_shipping'] = 'WCMP_Vendor_Shipping_Method';
        return apply_filters( 'wcmp_vendor_shipping_method_init', $methods );
    }

    public function add_vendor_id_to_package($packages) {
        foreach ($packages as $key => $package) {
            $packages[$key]['vendor_id'] = $key; // $key is the vendor_id
        }
        return $packages;
    }
    
    /**
     * CLass Loader
     *
     * @access public
     * @param mixed $class_name
     * @param mixed $dir
     * @return void
     */
    public static function load_class($class_name = '', $dir = '') {
        global $WCMp;
        if ('' != $class_name && '' != $WCMp->token) {
            if($dir)
                require_once ('shipping-gateways/' . trailingslashit( $dir ) . 'class-' . esc_attr($WCMp->token) . '-' . esc_attr($class_name) . '.php');
            else
                require_once ('shipping-gateways/class-' . esc_attr($WCMp->token) . '-' . esc_attr($class_name) . '.php');
        }
    }

}
