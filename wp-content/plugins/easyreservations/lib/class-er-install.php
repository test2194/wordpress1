<?php
/**
 * Install and Update easyReservations.
 * User: feryaz
 * Date: 01.09.2018
 * Time: 18:26
 */

//Prevent direct access to file
if( !defined( 'ABSPATH' ) )
    exit;

if( !class_exists( 'ER_Install' ) ) :

    class ER_Install {

        private static $db_updates = array(
            '1.4' => 'updates/easyreservations-update-1.4.php',
            '1.5' => 'updates/easyreservations-update-1.5.php',
            '1.6' => 'updates/easyreservations-update-1.6.php',
            '1.7' => 'updates/easyreservations-update-1.7.php',
            '3.2' => 'updates/easyreservations-update-3.2.php',
            '4.0' => 'updates/easyreservations-update-4.0.php',
            '5.0' => 'updates/easyreservations-update-5.0.php',
        );

        public static function init() {
            add_action( 'admin_init', array( __CLASS__, 'check_for_update' ), 5 );
        }

        /**
         * Check if update has to be made
         *
         * @return void
         */
        public static function check_for_update() {
            if( !defined( 'IFRAME_REQUEST' ) ) {
                $plugins = apply_filters( 'easyreservations_plugin_version',
                    array( 'reservations' => ER()->database_version )
                );
                foreach( $plugins as $name => $version ) {
                    if( self::needs_update( $name, $version ) ) {
                        do_action( 'easyreservations_before_update' );
                        self::install( $name );
                        do_action( 'easyreservations_after_update' );
                    }
                }
            }
        }

        private static function needs_update( $plugin, $version ) {
            if( get_option( $plugin . '_db_version' ) !== $version ) {
                return true;
            }
            return false;
        }

        private static function install( $plugin ) {
            if( !defined( 'ER_INSTALLING' ) ) {
                define( 'ER_INSTALLING', true );
            }

            $current_plugin_db_version = get_option( $plugin . '_db_version', null );

            //New install
            if( is_null( $current_plugin_db_version ) ) {
                if( $plugin == 'reservations' ) {
                    include( 'updates/install.php' );
                }
                else {
                    do_action( 'easyreservations_' . $plugin . '_install' );
                }
            }

            //Update
            if( !is_null( $current_plugin_db_version ) ) {
                self::update( $plugin );
            }
        }

        private static function update( $plugin = 'reservations' ) {
            $db_version = get_option( $plugin . '_db_version' );

            if( $plugin !== 'reservations' ) {
                $db_updates = apply_filters( 'easyreservations_' . $plugin . '_db_updates', false );
            } else {
                $db_updates = self::$db_updates;
            }

            foreach( $db_updates as $version => $update_script ) {
                if( version_compare( $db_version, $version, '<' ) ) {
                    include( $update_script );
                    update_option( $plugin . '_db_version', $version );
                }
            }

            update_option( 'reservations_db_version', ER()->database_version );
            do_action( 'easyreservations_updated' );
        }
    }

    ER_Install::init();

endif;