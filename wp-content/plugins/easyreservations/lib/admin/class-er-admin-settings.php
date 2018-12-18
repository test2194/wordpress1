<?php
/**
 * Created by PhpStorm.
 * User: feryaz
 * Date: 11.09.2018
 * Time: 20:57
 */

//Prevent direct access to file
if( !defined( 'ABSPATH' ) ) {
    exit;
}

class ER_Admin_Settings {

    public function __construct() {
        add_action( 'admin_init', array( $this, 'init' ) );
    }

    public function init() {

        if( isset( $_GET['tutorial_history'] ) ) {

            if( !function_exists( 'wp_get_current_user' ) ) {
                include( ABSPATH . "wp-includes/pluggable.php" );
            }

            set_user_setting( 'easy_tutorial', '' );

        } elseif( isset( $_POST['easy-general-settings'] ) && check_admin_referer( 'easy-general-settings', 'easy-general-settings' ) ) {

            $this->save_general();

        } elseif(isset($_GET["delete-form"])){

            $delete = sanitize_key($_GET["delete-form"]);

            delete_option('reservations_form_'.$delete);
            ER()->messages()->add_success(sprintf(__('%s deleted', 'easyReservations'), sprintf(__('Form %s', 'easyReservations'), '<b>'.$delete.'</b>')));

        } elseif(isset($_POST['reservations_form_content'])){

            $this->edit_form();

        } elseif(isset($_POST["form_name"])){

            $this->add_form();

        } elseif(isset($_POST["custom_name"])){

            $this->save_custom();

        } elseif(isset($_GET["delete-custom"])  && check_admin_referer( 'easy-delete-custom' )){

            $custom_fields = get_option('reservations_custom_fields', array());
            unset($custom_fields['fields'][$_GET['delete-custom']]);
            update_option('reservations_custom_fields', $custom_fields);

            ER()->messages()->add_success(sprintf(__('%s deleted', 'easyReservations'), __('Custom field', 'easyReservations')));

        } elseif(isset($_POST["easy-emails"])  && check_admin_referer( 'easy-emails', 'easy-emails' )){

            $this->save_emails();

        } else {
            do_action( 'er_set_save' );
        }

        /*
        if(isset($action) && $action == "reservation_clean_database"){
            global $wpdb;
            $wpdb->query( "DELETE FROM ".$wpdb->prefix ."reservations WHERE departure < NOW() AND approve != 'yes' " );
            ER()->messages()->add_success(__('Database cleaned', 'easyReservations'));
        }*/


    }

    public static function output() {
        $current_tab = 'general';
        if( isset( $_GET['tab'] ) ) {
            $current_tab = strval( $_GET['tab'] );
        }
        include 'views/html-admin-settings-header.php';

        switch( $current_tab ) {
            case 'general':
                include 'views/html-admin-settings-general.php';
                break;
            case 'form':
                include 'views/html-admin-settings-form.php';
                break;
            case 'custom':
                include 'views/html-admin-settings-custom.php';
                break;
            case 'email':
                if(function_exists('easyreservations_generate_email_settings')){
                    easyreservations_generate_email_settings();
                } else {
                    include 'views/html-admin-settings-emails.php';
                }
                break;
            case 'about':
                include 'views/html-admin-settings-about.php';
                break;
            default:
                do_action( 'er_set_add' );
                break;
        }
    }

    public function save_general() {
        if( isset( $_POST["reservations_time"] ) ) {
            $reservations_time = 1;
        } else $reservations_time = 0;
        if( isset( $_POST["reservations_tutorial"] ) ) {
            $tutorial = 1;
        } else $tutorial = 0;
        if( isset( $_POST['merge_resources'] ) ) {
            $mergeres = $_POST['reservations_resourcemerge'];
        } else $mergeres = 0;
        if( isset( $_POST['reservations_currency_whitespace'] ) ) {
            $white = 1;
        } else $white = 0;

        $currency_locale = sanitize_text_field($_POST["reservations_currency"]);
        $currency_symbols = include RESERVATIONS_ABSPATH . 'i18n/currency-symbols.php';

        $settings_array = array(
            'style' => $_POST["reservations_style"],
            'primary-color' => $_POST["primary-color"],
            'currency' => array(
                'sign' => $currency_symbols[$currency_locale],
                'locale' => $currency_locale,
                'whitespace' => $white,
                'decimal' => $_POST["reservations_currency_decimal"],
                'divider1' => $_POST["reservations_currency_divider1"],
                'divider2' => $_POST["reservations_currency_divider2"],
                'place' => $_POST['reservations_currency_place']
            ),
            'date_format' => $_POST["reservations_date_format"],
            'time_format' => $_POST["reservations_time_format"],
            'time' => $reservations_time,
            'tutorial' => $tutorial,
            'mergeres' => array(
                'merge' => $mergeres,
                'blockbefore' => $_POST['blockbefore'],
                'blockafter' => $_POST['blockafter']
            ),
            'prices_include_tax' => $_POST['prices_include_tax']
        );
        update_option( "reservations_settings", $settings_array );
        update_option( "reservations_regular_guests", $_POST["regular_guests"] );
        update_option( "easyreservations_successful_script", $_POST["javascript"] );
        update_option( "reservations_support_mail", $_POST["reservations_support_mail"] );
        do_action( 'easy_general_settings_save' );

        $permissions = array(
            'dashboard' => $_POST["easy_permission_dashboard"],
            'resources' => $_POST["easy_permission_resources"],
            'statistics' => $_POST["easy_permission_statistics"],
            'settings' => $_POST["easy_permission_settings"]
        );
        update_option( 'reservations_main_permission', $permissions );
        update_option( 'reservations_uninstall', isset( $_POST["reservations_uninstall"] ) ? 1 : 0 );

        ER()->messages()->add_success( sprintf( __( '%s settings saved', 'easyReservations' ), __( 'General', 'easyReservations' ) ) );
    }

    public function add_form(){
        if(!empty($_POST["form_name"])){
            $string = sanitize_text_field($_POST["form_name"]);
            $form_name = 'reservations_form_'.preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', $string));

            if(get_option($form_name)=="") add_option($form_name, ' ', '', 'no' );
            elseif(get_option($form_name.'_1')=="") add_option($form_name.'_1', ' ', '', 'no');
            else add_option($form_name.'_2', ' ', '', 'no');

            ER()->messages()->add_success(sprintf(__('%s added', 'easyReservations'), sprintf(__('Form %s', 'easyReservations'), '<b>'.$string.'</b>')));
        } else ER()->messages()->add_error(sprintf(__('Please enter %s', 'easyReservations'), __('a name for the form', 'easyReservations')));

    }

    public function edit_form(){
        $test = array();
        foreach(explode("<br>\r\n", $_POST['reservations_form_content']) as $v){
            $test[] = str_replace('<br>', "<br>\r\n", $v);
        }
        $reservations_form_value = implode("<br>\r\n", $test);
        $reservations_form_value = str_replace(array('<br>', '</formtag>'), array("\n", ''), $reservations_form_value);
        $reservations_form_value = preg_replace('/<formtag.*?>/', '', $reservations_form_value);

        $reservations_form_value = html_entity_decode($reservations_form_value);
        $reservations_form_value = preg_replace('/(<(font|style)\b[^>]*>).*?(<\/\2>)/is', '', $reservations_form_value);

        $name = isset($_GET["form"]) ? sanitize_key($_GET["form"]) : '';

        if(empty($name)) update_option( 'reservations_form', $reservations_form_value);
        else update_option('reservations_form_'.$name, $reservations_form_value);

        ER()->messages()->add_success(sprintf(__('%s saved', 'easyReservations'), sprintf(__('Form %s', 'easyReservations'), '<b>'.$name.'</b>')));
    }

    public function save_custom(){
        $custom_fields = get_option('reservations_custom_fields', array());

        $custom = array();
        $custom["title"] = str_replace(array('\"', "\'"), '', $_POST['custom_name']);
        $custom["type"] = $_POST['custom_field_type'];
        $custom["unused"] = $_POST['custom_field_unused'];
        if($custom["type"] == 'text' || $custom["type"] == 'area'){
            $custom["value"] = $_POST['custom_field_value'];
        } else {
            $custom['options'] = array();
            $get_id = array();
            foreach($_POST['id'] as $nr => $id){
                $final_id = $id;
                if(is_numeric($id)){
                    $uid = uniqid($id);
                    $get_id[$id] = $uid;
                    $final_id = $uid;
                }
                $custom['options'][$final_id] = array();
                $custom['options'][$final_id]["value"] = $_POST['value'][$nr];
                if(isset($_POST['price'])) $custom['options'][$final_id]["price"] = $_POST['price'][$nr];
                if(isset($_POST['checked'][$nr]) && $_POST['checked'][$nr] == 1) $custom['options'][$final_id]['checked'] = 1;
                if(isset($_POST['min'][$nr])) $custom['options'][$final_id]['min'] = $_POST['min'][$nr];
                if(isset($_POST['max'][$nr])) $custom['options'][$final_id]['max'] = $_POST['max'][$nr];
                if(isset($_POST['step'][$nr])) $custom['options'][$final_id]['step'] = $_POST['step'][$nr];
                if(isset($_POST['label'][$nr])) $custom['options'][$final_id]['label'] = $_POST['label'][$nr];
                if(isset($_POST['number-price'][$nr]) && $_POST['number-price'][$nr] == 1) $custom['options'][$final_id]['mode'] = 1;
            }

            if(isset($_POST['if_option'])){
                foreach($_POST['if_option'] as $nr => $opt_id){
                    if(is_numeric($opt_id)) $opt_id = $get_id[$opt_id];
                    $option = array();
                    $option['type'] = $_POST['if_cond_type'][$nr];
                    $option['operator'] = $_POST['if_cond_operator'][$nr];
                    $option['cond'] = $_POST['if_cond'][$nr];
                    if($_POST['if_cond_happens'][$nr] == "price") $option['price'] = $_POST['if_cond_amount'][$nr];
                    else $option['price'] = $_POST['if_cond_happens'][$nr];
                    $option['mult'] = $_POST['if_cond_mult'][$nr];
                    $custom['options'][$opt_id]['clauses'][] = $option;
                }
            }
        }
        if(isset($_POST['custom_price_field'])) $custom['price'] = 1;
        if(isset($_POST['custom_field_required'])) $custom['required'] = 1;
        if(isset($_POST['custom_field_admin'])) $custom['admin'] = 1;
        if(isset($_POST['custom_id'])){
            $custom_id = $_POST['custom_id'];
            ER()->messages()->add_success(sprintf(__('%s saved', 'easyReservations'), __('Custom field', 'easyReservations')));
        } else {
            if(isset($custom_fields['id'])) $custom_fields['id'] = $custom_fields['id'] + 1;
            else $custom_fields['id'] = 1;
            $custom_id = $custom_fields['id'];
            ER()->messages()->add_success(sprintf(__('%s added', 'easyReservations'), __('Custom field', 'easyReservations')));
        }
        if(!isset($custom_fields['fields'])) $custom_fields['fields'] = array();
        $custom_fields['fields'][$custom_id] = $custom;
        update_option('reservations_custom_fields', $custom_fields);
    }

    public function save_emails(){
        foreach(easyreservations_get_emails()as $key => $mail){
            if(isset($_POST[$key."_msg"])){
                if(isset($_POST[$key."_check"])) $check = 1;
                else $check = 0;
                if(is_array($_POST[$key."_msg"])) $_POST[$key."_msg"] = implode($_POST[$key."_msg"]);
                update_option($key, array(
                    'msg' => stripslashes($_POST[$key."_msg"]),
                    'subj' => stripslashes($_POST[$key."_subj"]),
                    'active' => $check
                ));
            }
        }

        ER()->messages()->add_success(sprintf(__('%s settings saved', 'easyReservations'), __('Email', 'easyReservations')));
    }
}

return new ER_Admin_Settings();