<?php

/**
 * Get other templates.
 *
 * @access public
 * @param string $template_name Template name.
 * @param array $args Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 */
function er_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    if( !empty( $args ) && is_array( $args ) ) {
        extract( $args );
    }

    $located = er_locate_template( $template_name, $template_path, $default_path );

    if( !file_exists( $located ) ) {
        //wc_doing_it_wrong( __FUNCTION__, sprintf( __( '%s does not exist.', 'woocommerce' ), '<code>' . $located . '</code>' ), '2.1' );
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters( 'wc_get_template', $located, $template_name, $args, $template_path, $default_path );

    do_action( 'easyreservations_before_template_part', $template_name, $template_path, $located, $args );

    include $located;

    do_action( 'easyreservations_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Like er_get_template, but returns the HTML instead of outputting.
 *
 * @see er_get_template
 * @param string $template_name Template name.
 * @param array $args Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 *
 * @return string
 */
function er_get_template_html( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
    ob_start();
    er_get_template( $template_name, $args, $template_path, $default_path );
    return ob_get_clean();
}

/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @access public
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path Default path. (default: '').
 * @return string
 */
function er_locate_template( $template_name, $template_path = '', $default_path = '' ) {
    if( !$template_path ) {
        $template_path = ER()->template_path();
    }

    if( !$default_path ) {
        $default_path = ER()->plugin_path() . '/templates/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template( array(
            trailingslashit( $template_path ) . $template_name,
            $template_name,
        ) );

    // Get default template/.
    if( !$template ) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters( 'easyreservations_locate_template', $template, $template_name, $template_path );
}

/**
 * Queue some JavaScript code to be output in the footer.
 *
 * @param string $code Code.
 */
function er_enqueue_js( $code ) {
    global $er_queued_js;

    if( empty( $er_queued_js ) ) {
        $er_queued_js = '';
    }

    $er_queued_js .= "\n" . $code . "\n";
}

add_action( 'wp_print_footer_scripts', 'er_print_js', 25 );
add_action( 'admin_print_footer_scripts', 'er_print_js', 25 );

/**
 * Output any queued javascript code in the footer.
 */
function er_print_js() {
    global $er_queued_js;

    if( !empty( $er_queued_js ) ) {
        // Sanitize.
        $er_queued_js = wp_check_invalid_utf8( $er_queued_js );
        $er_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $er_queued_js );
        $er_queued_js = str_replace( "\r", '', $er_queued_js );

        $js = "<!-- easyReservations JavaScript -->\n<script type=\"text/javascript\">\n $er_queued_js \n</script>\n";

        /**
         * Queued jsfilter.
         *
         * @since 2.6.0
         * @param string $js JavaScript code.
         */
        echo apply_filters( 'easyreservations_queued_js', $js ); // WPCS: XSS ok.

        unset( $er_queued_js );
    }
}

/**
 *    Repair incorrect input and checks if string can be a price
 *
 * @param string $price a string to check
 * @return string or bool if not correct
 */
function er_check_money( $price ) {
    $newPrice = str_replace( ",", ".", $price );
    return ( preg_match( "/^[\-]{0,1}[0-9]+[\.]?[0-9]*$/", $newPrice ) ) ? $newPrice : false;
}

/**
 * Format price into currency string
 *
 * @since 1.3
 *
 * @param float $amount amount of money to format
 * @param int $mode 1 = currency sign | 0 = without
 * @return string
 */
function er_format_money( $amount, $mode = 0 ) {
    if( $amount == '' )
        $amount = 0;
    $reservations_settings = get_option( "reservations_settings" );
    $currency_settings     = $reservations_settings['currency'];
    if( !is_array( $currency_settings ) )
        $currency_settings = array(
            'sign' => $currency_settings,
            'place' => 0,
            'whitespace' => 1,
            'divider1' => '.',
            'divider2' => ',',
            'decimal' => 2
        );

    if( $amount < 0 || substr( $amount, 0, 1 ) == '-' ) {
        $amount = substr( $amount, 1 );
        $add    = '-';
    } else $add = '';

    $money = $add . number_format( $amount, $currency_settings['decimal'], $currency_settings['divider2'], $currency_settings['divider1'] );

    if( $mode == 1 ) {
        $white = $currency_settings['whitespace'] == 1 ? ' ' : '';

        if( $currency_settings['place'] == 0 ) {
            $money = $money . $white . '&' . $currency_settings['sign'] . ';';
        } else $money = '&' . $currency_settings['sign'] . ';' . $white . $money;
    }
    return $money;
}

add_action( 'admin_bar_menu', 'er_register_admin_bar', 999 );

function er_register_admin_bar() {
    if( current_user_can( 'edit_posts' ) ) {
        global $wp_admin_bar;

        $pending_reservations_cnt = er_get_pending();
        $pending                  = $pending_reservations_cnt != 0 ? '<span class="ab-label">' . $pending_reservations_cnt . '</span>' : '';

        $wp_admin_bar->add_node( array(
            'id' => 'reservations',
            'title' => '<span class="er-adminbar-icon"></span>' . $pending,
            'href' => admin_url( 'admin.php?page=reservations#pending' ),
            'meta' => array( 'class' => 'er-adminbar-item' )
        ) );
        $wp_admin_bar->add_node( array(
            'parent' => 'reservations',
            'id' => 'reservations-new',
            'title' => 'New',
            'href' => admin_url( 'admin.php?page=reservations&add' ),
        ) );
        $wp_admin_bar->add_node( array(
            'parent' => 'reservations',
            'id' => 'reservations-pending',
            'title' => 'Pending',
            'href' => admin_url( 'admin.php?page=reservations#pending' ),
        ) );
        $wp_admin_bar->add_node( array(
            'parent' => 'reservations',
            'id' => 'reservations-nurrent',
            'title' => 'Current',
            'href' => admin_url( 'admin.php?page=reservations#current' ),
        ) );
    }
}

/**
 * @return int amount of pending reservations
 */
function er_get_pending() {
    global $wpdb;
    $count = $wpdb->get_var( "SELECT COUNT(*) as Num FROM " . $wpdb->prefix . "reservations WHERE approve='' AND arrival > NOW()" );
    return intval( $count );
}

/**
 * @return array emails of imports guests
 */
function er_get_important_guests(){
    return explode(",", str_replace(array(' ', PHP_EOL), array('', ','), get_option("reservations_regular_guests")));
}

/**
 * Nonce without time restraint to somewhat secure email links
 * @param $nonce
 * @param int $action
 * @return bool|int
 */
function er_verify_nonce( $nonce, $action = -1 ) {
    $i = wp_nonce_tick();
    // Nonce generated 0-12 hours ago
    if ( hash_equals(substr(wp_hash($i .'|'.$action . '|0', 'nonce'), -12, 10), $nonce) )
        return 1;
    // Nonce generated 12-24 hours ago
    if ( hash_equals( substr(wp_hash(($i - 1) .'|'.$action . '|0', 'nonce'), -12, 10) , $nonce ) )
        return 2;
    // Invalid nonce
    return false;
}