<?php
//Prevent direct access to file
if(!defined('ABSPATH'))
exit;

if(!class_exists('easyReservations')) :

final class easyReservations {
	//Current Version of easyReservations
	public $version = '5.0.8';

	//Current Database Version of easyReservations
	public $database_version = '5.0';

	//Single instance
	protected static $_instance = null;

	/**
	 * Initialize and make sure only one instance is made
	 * @return easyReservations
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'easyreservations_loaded' );
	}


	private function define_constants() {
		$reservations_settings = get_option( "reservations_settings" );

		define( 'RESERVATIONS_VERSION', '5.0.8' );
		define( 'RESERVATIONS_ABSPATH', dirname( RESERVATIONS_PLUGIN_FILE ) . '/' );
		define( 'RESERVATIONS_URL', WP_PLUGIN_URL . '/easyreservations/' );
		if ( !is_admin() && $reservations_settings['style'] == 'dark' ) {
			define( 'RESERVATIONS_STYLE', 'easy-ui easy-ui-container dark' );
		} else {
			define( 'RESERVATIONS_STYLE', 'easy-ui easy-ui-container' );
		}
		if ( ! is_array( $reservations_settings['currency'] ) ) {
			$sign = $reservations_settings['currency'];
		} else {
			$sign = $reservations_settings['currency']['sign'];
		}
		define( 'RESERVATIONS_CURRENCY', $sign );
		define( 'RESERVATIONS_DECIMAL', isset($reservations_settings['currency']['decimal']) ? $reservations_settings['currency']['decimal'] : 2);
		define( 'RESERVATIONS_DATE_FORMAT', $reservations_settings['date_format'] );
		define( 'RESERVATIONS_TIME_FORMAT', $reservations_settings['time_format'] );
		define( 'RESERVATIONS_USE_TIME', $reservations_settings['time'] );
		if ( RESERVATIONS_USE_TIME == 1 ) {
			if ( isset( $reservations_settings['time_format'] ) ) {
				$use_time = ' ' . $reservations_settings['time_format'];
			} else {
				$use_time = ' H:i';
			}
		} else {
			$use_time = '';
		}

		define( 'RESERVATIONS_DATE_FORMAT_SHOW', RESERVATIONS_DATE_FORMAT . $use_time );
	}

	private function init_hooks() {
		register_activation_hook( __FILE__, array( 'ER_Install', 'install' ) );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ), 0 );
	}

	/**
	 * What type of request is this?
	 *
	 * @param  string $type admin, ajax, cron or frontend.
	 *
	 * @return bool
	 */
	private function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin()|| defined( 'EASY_API' );
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {
		$reservations_settings = get_option( "reservations_settings" );

		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-autoloader.php' );
		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-datetime.php' );
		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-ajax.php' );

		include_once(RESERVATIONS_ABSPATH . 'lib/er-core-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/er-meta-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/er-form-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/er-date-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/er-reservation-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/er-resource-functions.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/functions/both.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/core/core.php');

		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-install.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-reservation.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-resource.php');
		include_once(RESERVATIONS_ABSPATH . 'lib/class-er-resources.php');

		include_once(RESERVATIONS_ABSPATH . 'lib/widgets/form_widget.php');

		if($this->is_request('admin')){

			include_once(RESERVATIONS_ABSPATH . 'lib/admin/class-er-admin.php');
			include_once(RESERVATIONS_ABSPATH . 'lib/admin/class-er-admin-messages.php');
			include_once(RESERVATIONS_ABSPATH . 'lib/functions/admin.php');

			if($this->is_request('ajax')){
				include_once(RESERVATIONS_ABSPATH . 'lib/functions/ajax.php');
			}

			if(isset($_GET['page'])){
				if($_GET['page'] == 'reservations') include_once(RESERVATIONS_ABSPATH . 'lib/admin/dashboard.php');
				if($_GET['page'] == 'reservation-resources') include_once(RESERVATIONS_ABSPATH . 'lib/admin/class-er-admin-resources.php');
				if($_GET['page'] == 'reservation-settings') include_once(RESERVATIONS_ABSPATH . 'lib/admin/class-er-admin-settings.php');
			}
			if(!isset($reservations_settings['tutorial']) || $reservations_settings['tutorial'] == 1) include_once(RESERVATIONS_ABSPATH . 'lib/tutorials/handle.tutorials.php');

		} else {

			include_once(RESERVATIONS_ABSPATH . 'lib/class-er-frontend.php');
			include_once(RESERVATIONS_ABSPATH . 'lib/shortcodes/form.php');
			include_once(RESERVATIONS_ABSPATH . 'lib/shortcodes/calendar.php');

			add_shortcode('easy_calendar', 'easyreservations_calendar_shortcode');
			add_shortcode('easy_form', 'easyreservations_form_shortcode');

		}

		if(file_exists(RESERVATIONS_ABSPATH.'lib/modules/premium/premium.php')){
			include_once(RESERVATIONS_ABSPATH . 'lib/modules/premium/premium.php');
		}

		$reservations_active_modules = get_option('reservations_active_modules');
		if($reservations_active_modules){
			if(easyreservations_is_module('paypal')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/paypal/paypal.php');
			if(easyreservations_is_module('useredit')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/useredit/useredit.php');
			if(easyreservations_is_module('export')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/export/export.php');
			if(easyreservations_is_module('multical')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/multical/multical.php');
			if(easyreservations_is_module('search')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/search/search.php');
			if(easyreservations_is_module('lang')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/lang/lang.php');
			if(easyreservations_is_module('styles')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/styles/styles.php');
			if(easyreservations_is_module('hourlycal')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/hourlycal/hourlycal.php');
			if(easyreservations_is_module('htmlmails')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/htmlmails/htmlmails.php');
			if(easyreservations_is_module('coupons')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/coupons/coupons.php');
			if(easyreservations_is_module('invoice')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/invoice/invoice.php');
			if(easyreservations_is_module('statistics')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/statistics/statistics.php');
			if(easyreservations_is_module('sync')) include_once(RESERVATIONS_ABSPATH . 'lib/modules/sync/sync.php');
		}
	}

	function register_scripts(){
        wp_register_style( 'easy-ui', RESERVATIONS_URL . 'assets/css/ui.min.css', array(), RESERVATIONS_VERSION ); // widget form style
        wp_register_script( 'easy-ui', RESERVATIONS_URL . 'assets/js/ui.js', array(
            'jquery-ui-slider',
            'jquery-touch-punch'
        ), RESERVATIONS_VERSION ); // widget form style
        wp_enqueue_style( 'font-awesome', RESERVATIONS_URL . 'assets/css/font-awesome/font-awesome.min.css', false );

        if( file_exists( RESERVATIONS_URL . 'assets/css/custom/datepicker.css' ) ) {
            $form1 = 'custom/datepicker.css';
        } else $form1 = 'datepicker.min.css';
        wp_register_style( 'datestyle', RESERVATIONS_URL . 'assets/css/' . $form1, array(), RESERVATIONS_VERSION );

        $lang = '';
        if( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $lang = '?lang=' . ICL_LANGUAGE_CODE;
        } elseif( function_exists( 'qtrans_getLanguage' ) ) {
            $lang = '?lang=' . qtrans_getLanguage();
        }

        $reservations_settings = get_option( "reservations_settings" );
        $reservations_currency = $reservations_settings['currency'];
        if( !is_array( $reservations_currency ) ) {
            $reservations_currency = array(
                'sign' => $reservations_currency,
                'place' => 0,
                'whitespace' => 1,
                'divider1' => '.',
                'divider2' => ',',
                'decimal' => 1
            );
        }

        wp_register_script( 'easyreservations_js_both', RESERVATIONS_URL . 'assets/js/both.js', array( "jquery-effects-slide" ), RESERVATIONS_VERSION );
        wp_localize_script( 'easyreservations_js_both', 'easy_both', array(
            'date_format' => RESERVATIONS_DATE_FORMAT,
            'time_format' => RESERVATIONS_TIME_FORMAT,
            'time' => current_time( 'timestamp' ),
            'currency' => $reservations_currency,
            'offset' => date( "Z" ),
            'style' => RESERVATIONS_STYLE,
            'resources' => ER()->resources()->get(),
            'ajaxurl' => admin_url( 'admin-ajax.php' . $lang ),
            'plugin_url' => WP_PLUGIN_URL
        ) );
        wp_enqueue_script( 'easyreservations_js_both' );
    }

	/**
	 * Init easyReservations when WordPress Initialises.
	 */
	public function init() {
		// Before init action.
		do_action( 'before_easyreservations_init' );

		if(isset($_GET['lang'])){
			global $sitepress;
			if($sitepress && is_object($sitepress)) $sitepress->switch_lang($_GET['lang']);
		}
		load_plugin_textdomain('easyReservations', false, basename( dirname( RESERVATIONS_PLUGIN_FILE )) . '/i18n/languages' );

		do_action( 'easyreservations_init' );
	}

    /**
     * Get the plugin url.
     *
     * @return string
     */
    public function plugin_url() {
        return untrailingslashit( plugins_url( '/', RESERVATIONS_PLUGIN_FILE ) );
    }

    /**
     * Get the plugin path.
     *
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( RESERVATIONS_PLUGIN_FILE ) );
    }


    /**
     * Get the template path.
     *
     * @return string
     */
    public function template_path() {
        return apply_filters( 'easyreservations_template_path', 'easyreservations/' );
    }

    /**
	 * @return ER_Resources
	 */
	public function resources(){
		return ER_Resources::instance();
	}

	/**
	 * @return ER_Admin_Messages
	 */
	public function messages(){
		return ER_Admin_Messages::instance();
	}
}

endif;