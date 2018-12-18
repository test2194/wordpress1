<?php
/*
Plugin Name: easyReservations
Plugin URI: http://www.easyreservations.org
Description: This powerful property and reservation management plugin allows you to receive, schedule and handle your bookings easily!
Version: 5.0.8
Author: Feryaz Beer
Author URI: http://www.feryaz.de
License:GPL2
*/

//Prevent direct access to file
if(!defined('ABSPATH'))
	exit;

// Define WC_PLUGIN_FILE.
if ( ! defined( 'RESERVATIONS_PLUGIN_FILE' ) ) {
	define( 'RESERVATIONS_PLUGIN_FILE', __FILE__ );
}

if ( ! class_exists( 'easyReservations' ) ) {
	include_once dirname( __FILE__ ) . '/lib/class-easyreservations.php';
}

/**
 * Main instance of easyReservations.
 *
 * Returns the main instance of ER to prevent the need to use globals.
 *
 * @return easyReservations
 */
function ER() {
	return easyReservations::instance();
}

ER();

/*
function easyreservations_api_rewrite_rule(){
	add_rewrite_tag('%api%','(^*)');
	add_rewrite_rule('^api/([^/]*)/{0,1}([^/]*)/{0,1}([^/]*)/{0,1}?','wp-content/plugins/easyreservations/lib/api/index.php?controller=$1&action=$2&information=$3','top');
}
add_action('init', 'easyreservations_api_rewrite_rule' );
*/