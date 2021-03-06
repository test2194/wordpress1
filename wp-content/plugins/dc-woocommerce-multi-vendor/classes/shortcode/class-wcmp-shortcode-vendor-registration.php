<?php

/**
 * WCMp Vendor Registration Shortcode Class
 *
 * @version		2.4.3
 * @package		WCMp/shortcode
 * @author 		WC Marketplace
 */
class WCMp_Vendor_Registration_Shortcode {

    public function __construct() {
        
    }

    /**
     * Output the vendor Registration shortcode.
     *
     * @access public
     * @param array $atts
     * @return void
     */
    public static function output($attr) {
        global $WCMp;
        if (!apply_filters('enable_users_can_register_for_wcmp_vendor_registration', get_option('users_can_register')) || apply_filters('is_woocommerce_enable_myaccount_registration_for_wcmp_vendor_registration', get_option( 'woocommerce_enable_myaccount_registration' )) != 'yes') {
            echo ' ' . __('Signup has been disabled.', 'dc-woocommerce-multi-vendor');
            return;
        }
        $frontend_style_path = $WCMp->plugin_url . 'assets/frontend/css/';
        $frontend_style_path = str_replace(array('http:', 'https:'), '', $frontend_style_path);
        $suffix = defined('WCMP_SCRIPT_DEBUG') && WCMP_SCRIPT_DEBUG ? '' : '.min';
        if (( 'no' === get_option('woocommerce_registration_generate_password') && !is_user_logged_in())) {
            wp_enqueue_script('wc-password-strength-meter');
        }
        wp_enqueue_script( 'wc-country-select' );
        wp_enqueue_script( 'wcmp_country_state_js' );
        wp_enqueue_style('wcmp_vandor_registration_css', $frontend_style_path . 'vendor-registration' . $suffix . '.css', array(), $WCMp->version);
        $WCMp->template->get_template('shortcode/vendor_registration.php');
    }

}
