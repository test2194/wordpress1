<?php
/**
 * Autoloader to reduce memory usage
 */

if(!defined('ABSPATH'))
	exit;

if(!class_exists('ER_Autoloader')):

	class ER_Autoloader {

		/**
		 * Path to the includes directory.
		 *
		 * @var string
		 */
		private $include_path = '';

		public function __construct() {
			//Check if an autoload function is already loaded
			if( function_exists( "__autoload" )){
				spl_autoload_register( "__autoload" );
			}

			//Register autoload function
			spl_autoload_register( array( $this, 'autoload' ) );

			$this->include_path = untrailingslashit( plugin_dir_path( RESERVATIONS_PLUGIN_FILE ) ) . '/lib/';
		}

		public function autoload($class){
			//Check if class of easyReservations is requested
			$class = strtolower($class);
			$class_parts = explode('_', $class);

			$path = false;

			if($class_parts[0] == 'er'){
				$path = $this->include_path;
			} else {
				$path = apply_filters('easyreservations_autoload_path_'.$class_parts['0'], false);
			}

			//Convert name to file
			$file = sanitize_file_name('class-'.str_replace('_', '-', $class).'.php');

			//Add subfolder to path if necessary
			if(strpos($class, 'admin') === 0){
				$path .= 'admin/';
			} elseif (strpos($class, 'meta_box') === 0){
				$path .= 'admin/meta-boxes/';
			}

			//Check if readable and include once
			if (is_readable($path.$file)){
				include_once($path.$file);
				return true;
			}

			return false;
		}
	}

	return new ER_Autoloader();

endif;
