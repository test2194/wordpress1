<?php

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	/**
	 * WC_List_Grid class
	 **/
	if ( ! class_exists( 'AStore_List_Grid' ) ) {

		class AStore_List_Grid {

			public function __construct() {
				// Hooks
  				add_action( 'woocommerce_before_shop_loop' , array( $this, 'setup_gridlist' ) , 20);

			}

			// Setup
			function setup_gridlist() {
				
				if ( is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy() ) {
					
					add_action( 'woocommerce_before_shop_loop', array( $this, 'gridlist_toggle_button' ), 25);
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'gridlist_buttonwrap_open' ), 9);
					add_action( 'woocommerce_after_shop_loop_item', array( $this, 'gridlist_buttonwrap_close' ), 11);
					add_action( 'astore_after_loop_title', 'woocommerce_template_single_excerpt', 5);
					add_action( 'woocommerce_after_subcategory', array( $this, 'gridlist_cat_desc' ) );
					
				}
			}


			// Toggle button
			function gridlist_toggle_button() {

				$grid_view = '';
				$list_view = '';

				$output = sprintf( '<div class="gridlist-toggle"><a href="#" id="grid" title="%1$s"> <em>%1$s</em></a><a href="#" id="list" title="%2$s"> <em>%2$s</em></a></div>', $grid_view, $list_view );

				echo apply_filters( 'gridlist_toggle_button_output', $output, $grid_view, $list_view );
			}

			// Button wrap
			function gridlist_buttonwrap_open() {
				echo apply_filters( 'gridlist_button_wrap_start', '<div class="gridlist-buttonwrap">' );
			}
			function gridlist_buttonwrap_close() {
				echo apply_filters( 'gridlist_button_wrap_end', '</div>' );
			}

			function gridlist_cat_desc( $category ) {
				global $woocommerce;
				echo apply_filters( 'gridlist_cat_desc_wrap_start', '<div itemprop="description">' );
					echo $category->description;
				echo apply_filters( 'gridlist_cat_desc_wrap_end', '</div>' );

			}
		}

		$AStore_List_Grid = new AStore_List_Grid();
	}
}
