<?php
/**
 * Customizer Site options importer class.
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 */
class Vela_Site_Options_Import {

	/**
	 * Instance of Vela_Site_Options_Importer
	 */
	private static $_instance = null;

	/**
	 * Instanciate Vela_Site_Options_Importer
	 */
	public static function instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Site Options
	 *
	 */
	private static function site_options() {
		return array(
			'custom_logo',
			'nav_menu_locations',
			'show_on_front',
			'page_on_front',
			'page_for_posts',
			'header_textcolor',

			// Plugin: SiteOrigin Widgets Bundle.
			'siteorigin_widgets_active',

			// Plugin: Elementor.
			'elementor_container_width',
			'elementor_cpt_support',
			'elementor_css_print_method',
			'elementor_default_generic_fonts',
			'elementor_disable_color_schemes',
			'elementor_disable_typography_schemes',
			'elementor_editor_break_lines',
			'elementor_exclude_user_roles',
			'elementor_global_image_lightbox',
			'elementor_page_title_selector',
			'elementor_scheme_color',
			'elementor_scheme_color-picker',
			'elementor_scheme_typography',
			'elementor_space_between_widgets',
			'elementor_stretched_section_container',

			// Plugin: Beaver Builder.
			'_fl_builder_enabled_icons',
			'_fl_builder_enabled_modules',
			'_fl_builder_post_types',
			'_fl_builder_color_presets',
			'_fl_builder_services',
			'_fl_builder_settings',
			'_fl_builder_user_access',
			'_fl_builder_enabled_templates',

			// Plugin: WooCommerce.
			// Pages.
			'woocommerce_shop_page_title',
			'woocommerce_cart_page_title',
			'woocommerce_checkout_page_title',
			'woocommerce_myaccount_page_title',
			'woocommerce_edit_address_page_title',
			'woocommerce_view_order_page_title',
			'woocommerce_change_password_page_title',
			'woocommerce_logout_page_title',

			// Categories.
			'woocommerce_product_cat',
		);
	}

	/**
	 * Import site options.
	 *
	 */
	public function import_options( $options = array() ) {

		if ( ! isset( $options ) ) {
			return;
		}

		foreach ( $options as $option_name => $option_value ) {

			// Is option exist in defined array site_options()?
			if ( null !== $option_value ) {

				// Is option exist in defined array site_options()?
				if ( in_array( $option_name, self::site_options() ) ) {

					switch ( $option_name ) {

						// Set WooCommerce page ID by page Title.
						case 'woocommerce_shop_page_title':
						case 'woocommerce_cart_page_title':
						case 'woocommerce_checkout_page_title':
						case 'woocommerce_myaccount_page_title':
						case 'woocommerce_edit_address_page_title':
						case 'woocommerce_view_order_page_title':
						case 'woocommerce_change_password_page_title':
						case 'woocommerce_logout_page_title':
								$this->update_woocommerce_page_id_by_option_value( $option_name, $option_value );
							break;

						case 'page_for_posts':
						case 'page_on_front':
								$this->update_page_id_by_option_value( $option_name, $option_value );
							break;

						// nav menu locations.
						case 'nav_menu_locations':
								$this->set_nav_menu_locations( $option_value );
							break;

						// import WooCommerce category images.
						case 'woocommerce_product_cat':
								$this->set_woocommerce_product_cat( $option_value );
							break;
						case 'header_textcolor':
								set_theme_mod( 'header_textcolor', $option_value );
							break;
							

						// insert logo.
						case 'custom_logo':
								$this->insert_logo( $option_value );
							break;

						default:
									update_option( $option_name, $option_value );
							break;
					}
				}
			}
		}
	}
	
	public static function get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
	        global $wpdb;
	
	        if ( is_array( $post_type ) ) {
	                $post_type = esc_sql( $post_type );
	                $post_type_in_string = "'" . implode( "','", $post_type ) . "'";
	                $sql = $wpdb->prepare( "
	                        SELECT ID
	                        FROM $wpdb->posts
	                        WHERE post_title = %s
	                        AND post_type IN ($post_type_in_string) 
							ORDER BY ID DESC
	                ", $page_title );
	        } else {
	                $sql = $wpdb->prepare( "
	                        SELECT ID
	                        FROM $wpdb->posts
	                        WHERE post_title = %s
	                        AND post_type = %s 
							ORDER BY ID DESC
	                ", $page_title, $post_type );
	        }
	
	        $page = $wpdb->get_var( $sql );
	
	        if ( $page ) {
	                return get_post( $page, $output );
	        }
	}

	/**
	 * Update post option
	 *
	 */
	private function update_page_id_by_option_value( $option_name, $option_value ) {
		$page = self::get_page_by_title( $option_value );
		if ( is_object( $page ) ) {
			update_option( $option_name, $page->ID );
		}
	}

	/**
	 * Update WooCommerce page ids.
	 *
	 */
	private function update_woocommerce_page_id_by_option_value( $option_name, $option_value ) {
		$option_name = str_replace( '_title', '_id', $option_name );
		$this->update_page_id_by_option_value( $option_name, $option_value );
	}

	/**
	 * In WP nav menu is stored as ( 'menu_location' => 'menu_id' );
	 * In export we send 'menu_slug' like ( 'menu_location' => 'menu_slug' );
	 * In import we set 'menu_id' from menu slug like ( 'menu_location' => 'menu_id' );
	 */
	private function set_nav_menu_locations( $nav_menu_locations = array() ) {

		$menu_locations = array();

		// Update menu locations.
		if ( isset( $nav_menu_locations ) ) {

			foreach ( $nav_menu_locations as $menu => $value ) {

				$term = get_term_by( 'slug', $value, 'nav_menu' );

				if ( is_object( $term ) ) {
					$menu_locations[ $menu ] = $term->term_id;
				}
			}

			set_theme_mod( 'nav_menu_locations', $menu_locations );
		}
	}

	/**
	 * Set WooCommerce category images.
	 *
	 * @since 1.1.4
	 *
	 * @param array $cats Array of categories.
	 */
	private function set_woocommerce_product_cat( $cats = array() ) {

		$menu_locations = array();

		if ( isset( $cats ) ) {

			foreach ( $cats as $key => $cat ) {

				if ( ! empty( $cat['slug'] ) && ! empty( $cat['thumbnail_src'] ) ) {

					$image = (object) Vela_Sites_Helper::_sideload_image( $cat['thumbnail_src'] );

					if ( ! is_wp_error( $image ) ) {

						if ( isset( $image->attachment_id ) && ! empty( $image->attachment_id ) ) {

							$term = get_term_by( 'slug', $cat['slug'], 'product_cat' );

							if ( is_object( $term ) ) {
								update_term_meta( $term->term_id, 'thumbnail_id', $image->attachment_id );
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Insert Logo By URL
	 *
	 */
	private function insert_logo( $image_url = '' ) {
		
		if( !$image_url )
			set_theme_mod( 'custom_logo', '' );
			
		$data = (object) Vela_Sites_Helper::_sideload_image( $image_url );

		if ( ! is_wp_error( $data ) ) {

			if ( isset( $data->attachment_id ) && ! empty( $data->attachment_id ) ) {
				set_theme_mod( 'custom_logo', $data->attachment_id );
			}
		}
	}

}
