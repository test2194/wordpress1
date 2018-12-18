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
class ER_Admin {

    /**
     * Constructor.
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'load_scripts' ) );
        add_filter( 'screen_settings', array( $this, 'dashboard_screen_settings' ), 10, 2 );

        if( isset( $_GET['page'] ) && sanitize_key( $_GET['page'] ) == 'reservations' ) {
            if( isset( $_GET['edit'] ) || isset( $_GET['add'] ) ) {
                add_action( 'admin_head', 'easyreservations_send_price_admin' );
                add_action( 'wp_ajax_easyreservations_send_price_admin', 'easyreservations_send_price_callback' );
            }
            else {
                add_action( 'admin_head', 'easyreservations_send_table' );
                add_action( 'admin_head', 'easyreservations_send_fav' );
            }
        }
    }

    public function load_scripts() {
        if( isset( $_GET['page'] ) ) {
            $page = sanitize_key( $_GET['page'] );

            if( $page == 'reservations' || $page == 'reservation-settings' || $page == 'reservation-statistics' || $page == 'reservation-resources' ) {
                $this->admin_scripts();
            }

            if( $page == 'reservation-resources' ) {  //  Only load Styles and Scripts on Resources Page
                $this->resource_scripts();
            }

            if( $page == 'reservation-statistics' || $page == 'reservations' ) {
                $this->statistic_scripts();
            }

            if( $page == 'reservations' ) {
                wp_enqueue_script( 'jquery-ui-datepicker' );
            }
        }
    }

    public function admin_scripts() {  //  Load Scripts and Styles
        wp_register_style( 'easy-admin-style', RESERVATIONS_URL . 'assets/css/admin.min.css', array( 'easy-ui' ) );
        wp_enqueue_style( 'easy-admin-style' );

        wp_register_script( 'easy-admin-script', RESERVATIONS_URL . 'assets/js/admin.js' );
        wp_enqueue_script( 'easy-admin-script' );
        wp_enqueue_script( 'easy-ui' );
    }

    public function statistic_scripts() {  //  Load Scripts and Styles
        wp_register_script( 'jquery-flot', RESERVATIONS_URL . 'assets/js/flot/jquery.flot.min.js' );
        wp_register_script( 'jquery-flot-stack', RESERVATIONS_URL . 'assets/js/flot/jquery.flot.stack.min.js' );
        wp_register_script( 'jquery-flot-pie', RESERVATIONS_URL . 'assets/js/flot/jquery.flot.pie.min.js' );
        wp_register_script( 'jquery-flot-crosshair', RESERVATIONS_URL . 'assets/js/flot/jquery.flot.crosshair.min.js' );
        wp_register_script( 'jquery-flot-resize', RESERVATIONS_URL . 'assets/js/flot/jquery.flot.resize.min.js' );
    }

    public function resource_scripts() {  //  Load Scripts and Styles
        wp_enqueue_script( 'jquery-ui-datepicker' );

        wp_enqueue_style( 'thickbox' );
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'thickbox' );
    }

    /*
     * Add admin menu
     */
    public function add_menu() {

        //Setup permissions
        $dashboard = 'edit_posts';
        $resources = 'edit_posts';
        $settings  = 'edit_posts';

        $reservation_main_permission = get_option( "reservations_main_permission" );
        if( $reservation_main_permission && is_array( $reservation_main_permission ) ) {
            if( isset( $reservation_main_permission['dashboard'] ) && !empty( $reservation_main_permission['dashboard'] ) ) {
                $dashboard = $reservation_main_permission['dashboard'];
            }
            if( isset( $reservation_main_permission['resources'] ) && !empty( $reservation_main_permission['resources'] ) ) {
                $resources = $reservation_main_permission['resources'];
            }
            if( isset( $reservation_main_permission['settings'] ) && !empty( $reservation_main_permission['settings'] ) ) {
                $settings = $reservation_main_permission['settings'];
            }
        }

        $pending_reservations_count = er_get_pending();
        if( $pending_reservations_count !== 0 ) {
            $pending = '<span class="update-plugins count-' . $pending_reservations_count . '"><span class="plugin-count">' . $pending_reservations_count . '</span></span>';
        }
        else {
            $pending = '';
        }

        add_menu_page( 'easyReservations', __( 'Reservations', 'easyReservations' ) . ' ' . $pending, $dashboard, 'reservations', 'easyreservations_main_page', RESERVATIONS_URL . 'assets/images/logo.png' );

        add_submenu_page( 'reservations', __( 'Dashboard', 'easyReservations' ), __( 'Dashboard', 'easyReservations' ), $dashboard, 'reservations', 'easyreservations_main_page' );
        add_submenu_page( 'reservations', __( 'Resources', 'easyReservations' ), __( 'Resources', 'easyReservations' ), $resources, 'reservation-resources', array(
            'ER_Admin_Resources',
            'output'
        ) );

        do_action( 'easy-add-submenu-page' );

        //add_submenu_page( 'reservations', __( 'Settings', 'easyReservations' ), __( 'Settings', 'easyReservations' ), $settings, 'reservation-settings', 'easyreservations_settings_page' );
        add_submenu_page( 'reservations', __( 'Settings', 'easyReservations' ), __( 'Settings', 'easyReservations' ), $settings, 'reservation-settings', array(
            'ER_Admin_Settings',
            'output'
        ) );
    }

    /*
     * Screen settings on dashboard
     */
    public function dashboard_screen_settings( $current, $screen ) {
        if( $screen->id == "toplevel_page_reservations" ) {
            if( isset( $_POST['main_settings'] ) ) {
                if( isset( $_POST['show_overview'] ) ) {
                    $show_overview = 1;
                }
                else {
                    $show_overview = 0;
                }
                if( isset( $_POST['show_table'] ) ) {
                    $show_table = 1;
                }
                else {
                    $show_table = 0;
                }
                if( isset( $_POST['show_upcoming'] ) ) {
                    $show_upcoming = 1;
                }
                else {
                    $show_upcoming = 0;
                }
                if( isset( $_POST['show_new'] ) ) {
                    $show_new = 1;
                }
                else {
                    $show_new = 0;
                }
                if( isset( $_POST['show_export'] ) ) {
                    $show_export = 1;
                }
                else {
                    $show_export = 0;
                }
                if( isset( $_POST['show_today'] ) ) {
                    $show_today = 1;
                }
                else {
                    $show_today = 0;
                }
                if( isset( $_POST['show_statistics'] ) ) {
                    $show_statistics = 1;
                }
                else {
                    $show_statistics = 0;
                }

                $showhide = array(
                    'show_overview' => $show_overview,
                    'show_table' => $show_table,
                    'show_upcoming' => $show_upcoming,
                    'show_new' => $show_new,
                    'show_export' => $show_export,
                    'show_today' => $show_today,
                    'show_statistics' => $show_statistics
                );

                if( isset( $_POST['table_color'] ) ) {
                    $table_color = 1;
                }
                else {
                    $table_color = 0;
                }
                if( isset( $_POST['table_id'] ) ) {
                    $table_id = 1;
                }
                else {
                    $table_id = 0;
                }
                if( isset( $_POST['table_name'] ) ) {
                    $table_name = 1;
                }
                else {
                    $table_name = 0;
                }
                if( isset( $_POST['table_from'] ) ) {
                    $table_from = 1;
                }
                else {
                    $table_from = 0;
                }
                if( isset( $_POST['table_email'] ) ) {
                    $table_email = 1;
                }
                else {
                    $table_email = 0;
                }
                if( isset( $_POST['table_room'] ) ) {
                    $table_room = 1;
                }
                else {
                    $table_room = 0;
                }
                if( isset( $_POST['table_exactly'] ) ) {
                    $table_exactly = 1;
                }
                else {
                    $table_exactly = 0;
                }
                if( isset( $_POST['table_reservated'] ) ) {
                    $table_reservated = 1;
                }
                else {
                    $table_reservated = 0;
                }
                if( isset( $_POST['table_persons'] ) ) {
                    $table_persons = 1;
                }
                else {
                    $table_persons = 0;
                }
                if( isset( $_POST['table_status'] ) ) {
                    $table_status = 1;
                }
                else {
                    $table_status = 0;
                }
                if( isset( $_POST['table_country'] ) ) {
                    $table_country = 1;
                }
                else {
                    $table_country = 0;
                }
                if( isset( $_POST['table_custom'] ) ) {
                    $table_custom = 1;
                }
                else {
                    $table_custom = 0;
                }
                if( isset( $_POST['table_price'] ) ) {
                    $table_price = 1;
                }
                else {
                    $table_price = 0;
                }
                if( isset( $_POST['table_filter_month'] ) ) {
                    $table_filter_month = 1;
                }
                else {
                    $table_filter_month = 0;
                }
                if( isset( $_POST['table_filter_room'] ) ) {
                    $table_filter_room = 1;
                }
                else {
                    $table_filter_room = 0;
                }
                if( isset( $_POST['table_filter_offer'] ) ) {
                    $table_filter_offer = 1;
                }
                else {
                    $table_filter_offer = 0;
                }
                if( isset( $_POST['table_filter_days'] ) ) {
                    $table_filter_days = 1;
                }
                else {
                    $table_filter_days = 0;
                }
                if( isset( $_POST['table_search'] ) ) {
                    $table_search = 1;
                }
                else {
                    $table_search = 0;
                }
                if( isset( $_POST['table_bulk'] ) ) {
                    $table_bulk = 1;
                }
                else {
                    $table_bulk = 0;
                }
                if( isset( $_POST['table_fav'] ) ) {
                    $table_fav = 1;
                }
                else {
                    $table_fav = 0;
                }
                if( isset( $_POST['table_onmouseover'] ) ) {
                    $table_onmouseover = 1;
                }
                else {
                    $table_onmouseover = 0;
                }

                $table = array(
                    'table_color' => $table_color,
                    'table_id' => $table_id,
                    'table_name' => $table_name,
                    'table_from' => $table_from,
                    'table_fav' => $table_fav,
                    'table_email' => $table_email,
                    'table_room' => $table_room,
                    'table_exactly' => $table_exactly,
                    'table_persons' => $table_persons,
                    'table_country' => $table_country,
                    'table_custom' => $table_custom,
                    'table_price' => $table_price,
                    'table_filter_month' => $table_filter_month,
                    'table_filter_room' => $table_filter_room,
                    'table_filter_offer' => $table_filter_offer,
                    'table_filter_days' => $table_filter_days,
                    'table_search' => $table_search,
                    'table_bulk' => $table_bulk,
                    'table_onmouseover' => $table_onmouseover,
                    'table_reservated' => $table_reservated,
                    'table_status' => $table_status
                );

                $overview_onmouseover  = isset( $_POST['overview_onmouseover'] ) ? 1 : 0;
                $overview_autoselect   = isset( $_POST['overview_autoselect'] ) ? 1 : 0;
                $overview_show_days    = isset( $_POST['overview_show_days'] ) ? $_POST['overview_show_days'] : 30;
                $overview_show_rooms   = isset( $_POST['overview_show_rooms'] ) ? implode( ",", $_POST['overview_show_rooms'] ) : '';
                $overview_hourly_stand = isset( $_POST['overview_hourly_stand'] ) ? 1 : 0;
                $overview_hourly_end   = isset( $_POST['overview_hourly_end'] ) ? intval( $_POST['overview_hourly_end'] ) : 23;
                $overview_hourly_start = isset( $_POST['overview_hourly_start'] ) ? intval( $_POST['overview_hourly_start'] ) : 0;

                $overview = array(
                    'overview_onmouseover' => $overview_onmouseover,
                    'overview_autoselect' => $overview_autoselect,
                    'overview_show_days' => $overview_show_days,
                    'overview_show_rooms' => $overview_show_rooms,
                    'overview_hourly_stand' => $overview_hourly_stand,
                    'overview_hourly_start' => $overview_hourly_start,
                    'overview_hourly_end' => $overview_hourly_end
                );

                update_option( 'reservations_main_options', array(
                    'show' => $showhide,
                    'table' => $table,
                    'overview' => $overview
                ) );
                if( isset( $_POST['daybutton'] ) ) {
                    update_option( "reservations_show_days", $_POST['daybutton'] );
                }

                ER()->messages()->add_success( sprintf( __( '%s settings saved', 'easyReservations' ), __( 'Reservations dashboard', 'easyReservations' ) ) );
            }

            $main_options = get_option( "reservations_main_options" );
            $show         = $main_options['show'];
            $table        = $main_options['table'];
            $overview     = $main_options['overview'];

            $current .= '<form method="post" id="er-main-settings-form" ><div style="height:144px">';
            $current .= '<input type="hidden" name="main_settings" value="1">';
            $current .= '<p style="float:left;margin-right:10px">';
            $current .= '<b><u>' . __( 'Show/Hide content', 'easyReservations' ) . '</u></b><br>';
            $current .= '<label><input type="checkbox" name="show_overview" value="1" ' . checked( $show['show_overview'], 1, false ) . '> ' . __( 'Overview', 'easyReservations' ) . '</label><br>';
            if( function_exists( 'easyreservations_statistics_mini' ) ) {
                $current .= '<label><input type="checkbox" name="show_statistics" value="1" ' . checked( $show['show_statistics'], 1, false ) . '> ' . __( 'Statistics', 'easyReservations' ) . '</label><br>';
            }
            $current .= '<label><input type="checkbox" name="show_table" value="1" ' . checked( $show['show_table'], 1, false ) . '> ' . __( 'Table', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="show_upcoming" value="1" ' . checked( $show['show_upcoming'], 1, false ) . '> ' . __( 'Upcoming reservations', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="show_new" value="1" ' . checked( $show['show_new'], 1, false ) . '> ' . __( 'New reservations', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="show_export" value="1" ' . checked( $show['show_export'], 1, false ) . '> ' . __( 'Export', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="show_today" value="1" ' . checked( $show['show_today'], 1, false ) . '> ' . __( 'What\'s happening today', 'easyReservations' ) . '</label><br>';
            $current .= '</p>';
            $current .= '<p style="float:left;margin-right:10px">';
            $current .= '<b><u>' . __( 'Table information', 'easyReservations' ) . '</u></b><br>';
            $current .= '<span style="float:left;margin-right:10px">';
            $current .= '<label><input type="checkbox" name="table_color" value="1" ' . checked( $table['table_color'], 1, false ) . '> ' . __( 'Color', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_id" value="1" ' . checked( $table['table_id'], 1, false ) . '> ' . __( 'ID', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_name" value="1" ' . checked( $table['table_name'], 1, false ) . '> ' . __( 'Name', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_from" value="1" ' . checked( $table['table_from'], 1, false ) . '> ' . __( 'Date', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_reservated" value="1" ' . checked( $table['table_reservated'], 1, false ) . '> ' . __( 'Reserved', 'easyReservations' ) . '</label><br>';
            $current .= '</span>';
            $current .= '<span style="float:left;margin-right:10px">';
            $current .= '<label><input type="checkbox" name="table_email" value="1" ' . checked( $table['table_email'], 1, false ) . '> ' . __( 'Email', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_room" value="1" ' . checked( $table['table_room'], 1, false ) . '> ' . __( 'Resource', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_exactly" value="1" ' . checked( $table['table_exactly'], 1, false ) . '> ' . __( 'Resource space', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_persons" value="1" ' . checked( $table['table_persons'], 1, false ) . '> ' . __( 'Persons', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_status" value="1" ' . checked( $table['table_status'], 1, false ) . '> ' . __( 'Status', 'easyReservations' ) . '</label><br>';
            $current .= '</span>';
            $current .= '<span style="float:left;">';
            $current .= '<label><input type="checkbox" name="table_country" value="1" ' . checked( $table['table_country'], 1, false ) . '> ' . __( 'Country', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_custom" value="1" ' . checked( $table['table_custom'], 1, false ) . '> ' . __( 'Custom fields', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_price" value="1" ' . checked( $table['table_price'], 1, false ) . '> ' . __( 'Price', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_fav" value="1" ' . checked( $table['table_fav'], 1, false ) . '> ' . __( 'Favourites', 'easyReservations' ) . '</label><br>';
            $current .= '</span>';
            $current .= '</p>';
            $current .= '<p style="float:left;margin-right:10px">';
            $current .= '<b><u>' . __( 'Table actions', 'easyReservations' ) . '</u></b><br>';
            $current .= '<label><input type="checkbox" name="table_filter_month" value="1" ' . checked( $table['table_filter_month'], 1, false ) . '> ' . sprintf( __( 'Filter by %s', 'easyReservations' ), er_date_get_interval_label( 2592000 ) ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_filter_room" value="1" ' . checked( $table['table_filter_room'], 1, false ) . '> ' . sprintf( __( 'Filter by %s', 'easyReservations' ), __( 'resource', 'easyReservations' ) ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_filter_offer" value="1" ' . checked( $table['table_filter_offer'], 1, false ) . '> ' . sprintf( __( 'Filter by %s', 'easyReservations' ), __( 'status', 'easyReservations' ) ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_filter_days" value="1" ' . checked( $table['table_filter_days'], 1, false ) . '> ' . __( 'Entries to display', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_search" value="1" ' . checked( $table['table_search'], 1, false ) . '> ' . __( 'Search', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="table_bulk" value="1" ' . checked( $table['table_bulk'], 1, false ) . '> ' . __( 'Bulk actions', 'easyReservations' ) . '</label><br>';
            $current .= '</p>';
            $current .= '<p style="float:left;margin-right:15px">';
            $current .= '<b><u>' . __( 'Show Resources', 'easyReservations' ) . ':</u></b><br>';
            $reservations_show_rooms = $overview['overview_show_rooms'];
            foreach( ER()->resources()->get() as $theNumber => $raum ) {
                $check = '';
                if( $reservations_show_rooms == '' ) {
                    $check = 'checked';
                }
                elseif( substr_count( $reservations_show_rooms, $raum->ID ) > 0 ) {
                    $check = 'checked';
                }
                $current .= '<label><input type="checkbox" name="overview_show_rooms[' . $theNumber . ']" value="' . $raum->ID . '" ' . $check . '> ' . __( stripslashes( $raum->post_title ) ) . '</label><br>';
            }
            $current .= '</p>';
            $current .= '<p style="float:left;">';
            $current .= '<b><u>' . __( 'Overview', 'easyReservations' ) . '</u></b><br>';
            $current .= '<label><input type="checkbox" name="overview_onmouseover" value="1" ' . checked( $overview['overview_onmouseover'], 1, false ) . '> ' . __( 'Overview onMouseOver Date & Select animation', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="overview_autoselect" value="1" ' . checked( $overview['overview_autoselect'], 1, false ) . '> ' . __( 'Overview autoselect with inputs on add/edit', 'easyReservations' ) . '</label><br>';
            $current .= '<label><input type="checkbox" name="overview_hourly_stand" value="1" ' . checked( $overview['overview_hourly_stand'], 1, false ) . '> ' . __( 'Hourly mode as standard', 'easyReservations' ) . '</label><br>';
            $current .= '<label>' . __( 'Only display hours between', 'easyReservations' ) . ' <select name="overview_hourly_start">' . er_form_number_options( 0, 23, $overview['overview_hourly_start'] ) . '</select> and <select name="overview_hourly_end">' . er_form_number_options( 0, 23, $overview['overview_hourly_end'] ) . '</select></label><br>';
            $current .= '<input type="text" name="overview_show_days" style="width:50px" value="' . $overview['overview_show_days'] . '"> ' . er_date_get_interval_label( 86400, 0, true );
            $current .= '</p>';
            $current .= '<input type="submit" value="Save Changes" class="button-primary" style="float:right;margin-top:120px !important">';
            $current .= '</div></form>';
        }

        return $current;
    }

}

return new ER_Admin();
