<?php
/**
 * Created by PhpStorm.
 * User: feryaz
 * Date: 03.09.2018
 * Time: 17:32
 */

//Prevent direct access to file
if( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ER_Admin class.
 */
class ER_Frontend {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'the_content', array( $this, 'clean_shortcodes' ), 99999 );
        add_action( 'wp_enqueue_scripts', array( $this, 'print_frontend_style' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
    }


    public function clean_shortcodes($content){
        $pattern_full = '(name="easyFrontendFormular">.*?</form|<form name="HourlyCalendarFormular".*?</form|<form name="easy_search_formular".*?</form|<form name="CalendarFormular.*?</form|<div id="searchbar".*?</div|<div id="easy_form_success".*?</div|id="edittable".*?</table>|<div class="easy-edit-status">.*?</div>|<div class="row">.*?</div>)s';
        preg_match_all($pattern_full, $content, $matches);
        if(!empty($matches[0])){
            foreach($matches[0] as $match){
                if(strpos($match, 'easy-edit-status') !== false || strpos($match, 'searchbar') !== false || strpos($match, 'div class="row"') !== false || strpos($match, 'easy_form_success') !== false) $thematch =  str_replace( array( '<br>', '<br />' ), '', $match );
                else $thematch = $match;
                $content = str_replace($match, str_replace( array( '<p>', '</p>' ), '', $thematch ), $content );
            }
        }
        return $content;
    }

    public function print_frontend_style(){
        $reservations_settings = get_option('reservations_settings');
        $color = '#228dff';
        if(isset($reservations_settings['primary-color'])){
            $color = $reservations_settings['primary-color'];
        }
        echo '<style type="text/css">:root { --easy-ui-primary: '.$color.';}</style>';
    }

    public function register_scripts(){
        wp_register_script('easyreservations_send_calendar', RESERVATIONS_URL.'assets/js/ajax/send_calendar.js', array( 'easy-ui' ), RESERVATIONS_VERSION);
        wp_register_script('easyreservations_send_price', RESERVATIONS_URL.'assets/js/ajax/send_price.js', array( "jquery" ), RESERVATIONS_VERSION);
        wp_register_script('easyreservations_send_validate', RESERVATIONS_URL.'assets/js/ajax/send_validate.js', array( "jquery-effects-blind" ), RESERVATIONS_VERSION);
        wp_register_script('easyreservations_send_form', RESERVATIONS_URL.'assets/js/ajax/form.js', array( "jquery-ui-slider", 'easy-ui' ), RESERVATIONS_VERSION);
        wp_register_script('easyreservations_data', RESERVATIONS_URL.'assets/js/ajax/data.js', array( "jquery" ), RESERVATIONS_VERSION);

        wp_register_style('easy-frontend', RESERVATIONS_URL.'assets/css/frontend.min.css', array('easy-ui'), RESERVATIONS_VERSION); // widget form style
        if(file_exists(RESERVATIONS_URL . 'assets/css/custom/form.css')) wp_register_style('easy-form-custom', RESERVATIONS_URL.'assets/css/custom/form.css', array('easy-ui'), RESERVATIONS_VERSION); // custom form style override
        wp_register_style('easy-form-little', RESERVATIONS_URL.'assets/css/forms/form_little.min.css', array('easy-ui'), RESERVATIONS_VERSION); // widget form style
        wp_register_style('easy-form', RESERVATIONS_URL.'assets/css/forms/form.min.css', array('easy-ui'), RESERVATIONS_VERSION); // widget form style

        if(file_exists(RESERVATIONS_URL . 'assets/css/custom/calendar.css')) wp_register_style('easy-cal-custom', RESERVATIONS_URL.'assets/css/custom/calendar.css', array(), RESERVATIONS_VERSION); // custom form style override
        wp_register_style('easy-calendar', RESERVATIONS_URL.'assets/css/calendar/calendar.min.css', array('easy-ui'), RESERVATIONS_VERSION);
        wp_register_script('easy-date-selection', RESERVATIONS_URL.'assets/js/date-selection.js', RESERVATIONS_VERSION);

        $string = explode(', beforeShow', easyreservations_build_datepicker(1, 1, true));
        $array = explode(', ', $string[0]);
        foreach($array as $key => $entry){
            $explode = explode(': ', $entry);
            $array[$explode[0]] = $explode[1];
            unset($array[$key]);
        }
        wp_localize_script('easy-date-selection', 'easy_date_selection_params', array(
            'wait' => __('Wait', 'easyReservations'),
            'select' => __('Select Date', 'easyReservations'),
            'datepicker' => $array
        ));
    }
}

return new ER_Frontend();