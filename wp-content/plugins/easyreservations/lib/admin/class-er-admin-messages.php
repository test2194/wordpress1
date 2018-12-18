<?php
/**
 * Created by PhpStorm.
 * User: feryaz
 * Date: 03.09.2018
 * Time: 18:02
 */

//Prevent direct access to file
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists( 'ER_Admin_Messages' ) ):

	class ER_Admin_Messages {
		//Single instance
		protected static $instance = null;
		protected $messages = array();
		protected $has_error = false;

		/**
		 * Initialize and make sure only one instance is made
		 * @return ER_Admin_Messages
		 */
		public static function instance() {
			if( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Empty all messages
		 */
		public function reset() {
			$this->messages  = array();
			$this->has_error = false;
		}

		/**
		 * Has any error?
		 * @return bool
		 */
		public function has_error() {
			return $this->has_error;
		}

		/**
		 * Add error message
		 *
		 * @param string $source Element to highlight or group
		 * @param string $message Error message
		 */
		public function add_error( $message ) {
			$this->has_error = true;
			$this->add( 'error', $message );
		}

		/**
		 * Add warning message
		 *
		 * @param string $source Element to highlight or group
		 * @param string $message Warning message
		 */
		public function add_notice( $message ) {
			$this->add( 'notice', $message );
		}

		/**
		 * Add message
		 *
		 * @param string $source Element to highlight or group
		 * @param string $message Message
		 */
		public function add_success( $message ) {
			$this->add( 'updated', $message );
		}

		/**
		 * Add admin message
		 *
		 * @param string $type updated, notice, error
		 * @param string $message
		 * @param string $source
		 */
		private function add( $type, $message ) {
			$this->messages[] = array(
				'type'    => $type,
				'message' => $message
			);
		}

		/**
		 * @param string $type which type of messages to output - all, updated, notice, error
		 */
		public function output( $type = 'all' ) {
			foreach( $this->messages as $message ) {
				if( $type == 'all' || $type == $message['type'] ) {
					echo '<div class="easy-message ' . $message['type'] . '"><p>' . $message['message'] . '</p></div>';
				}
			}
		}
	}

endif;