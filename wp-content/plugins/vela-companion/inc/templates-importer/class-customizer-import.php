<?php
/**
 * Customizer Data importer class.
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Data importer class.
 *
 */
class Vela_Customizer_Import {

	/**
	 * Instance of Vela_Customizer_Import
	 */
	private static $_instance = null;

	/**
	 * Instantiate Vela_Customizer_Import
	 */
	public static function instance() {

		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self;
		}

		return self::$_instance;
	}

	/**
	 * Import customizer options.
	 */
	public function import( $options ) {

		// Update Vela Theme customizer settings.
		if ( is_array($options) ) {
			
			self::_import_settings( $options );
		}

	}

	/**
	 * Import Vela Setting's

	 */
	static public function _import_settings( $options = array() ) {
		foreach ( $options as $key => $val ) {

			if ( Vela_Sites_Helper::_is_image_url( $val ) ) {

				$data = Vela_Sites_Helper::_sideload_image( $val );

				if ( ! is_wp_error( $data ) ) {
					$options[ $key ] = $data->url;
				}
			}
		}

		// Updated settings.
		update_option( VELA_THEME_OPTION_NAME, $options );
	}
}
