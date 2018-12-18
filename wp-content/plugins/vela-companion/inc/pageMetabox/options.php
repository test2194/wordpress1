<?php

add_filter( 'cmb_meta_boxes', 'vela_page_metaboxes' );
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */
function vela_page_metaboxes( array $meta_boxes ) {
	
	$theme = VELA_THEME_OPTION_NAME;
	$prefix = '_vela_';
	if( $theme == 'cactus' || strstr($theme,'cactus') )
		$prefix = '_ccmb_';
	if( $theme == 'astore' || strstr($theme,'astore') )
		$prefix = '_acmb_';

	if( $theme == 'cactus' || strstr($theme,'cactus') ){
	/**
	 * Metabox Slider
	 */
	$meta_boxes['cc_slideshow'] = array(
		'id'         => 'cc_slideshow',
		'title'      => __( 'Slider', 'vela-companion' ),
		'pages'      => array( 'page', 'post' ),
		'fields'     => array(
			array(
				'id'          => $prefix . 'slideshow',
				'type'        => 'group',
				'description' => '',
				'options'     => array(
					'group_title'   => __( 'Slide {#}', 'vela-companion' ), // {#} gets replaced by row number
					'add_button'    => __( 'Add Another Slide', 'vela-companion' ),
					'remove_button' => __( 'Remove Slide', 'vela-companion' ),
					'sortable'      => true, // beta
				),
				// Fields array works the same, except id's only need to be unique for this group. Prefix is not needed.
				'fields'      => array(
					array(
						'name' => __( 'Big Title', 'vela-companion' ),
						'id'   => 'title',
						'type' => 'text',
					),
					array(
						'name' => __( 'Sub-Title', 'vela-companion' ),
						'id'   => 'subtitle',
						'type' => 'text',
					),
					array(
						'name' => __( 'Image', 'vela-companion' ),
						'id'   => 'image',
						'type' => 'file',
					),
					array(
						'name' => __( 'Button Text', 'vela-companion' ),
						'id'   => 'btn_text',
						'type' => 'text',
					),
					array(
						'name' => __( 'Button Link', 'vela-companion' ),
						'id'   => 'btn_link',
						'type' => 'text',
					),
				),
			),
		),
	);
	}
	
	/**
	 * Page options
	 */
	if( $theme == 'cactus' || strstr($theme,'cactus') ){
			$meta_boxes['theme_options_metabox'] = array(
			'id'         => 'theme_options_metabox',
			'title'      => __( 'Page Options', 'vela-companion' ),
			'pages'      => array( 'page', 'post' ), // Post type
			'context'    => 'normal',
			'priority'   => 'high',
			'fields'     => array(
				array(
					'name'    => __( 'Hide Page Title Bar', 'vela-companion' ),
					'desc'    => '',
					'id'      => $prefix . 'hide_page_title_bar',
					'type'    => 'checkbox',
					'default' => ''
				),
				array(
					'name'    => __( 'Background Color', 'vela-companion' ),
					'desc'    => '',
					'id'      => $prefix . 'bg_color',
					'type'    => 'colorpicker',
					'default' => '#ffffff'
				),
				array(
					'name' => __( 'Background Image', 'vela-companion' ),
					'desc' => __( 'Upload an image or enter a URL.', 'vela-companion' ),
					'id'   => $prefix . 'bg_image',
					'type' => 'file',
				),
				array(
					'name'    => __( 'Sidebar', 'vela-companion' ),
					'desc'    => '',
					'id'      => $prefix . 'sidebar',
					'type'    => 'radio',
					'default' => '',
					'options' => array(
						'' => __( 'Default', 'vela-companion' ),
						'left' => __( 'Left Sidebar', 'vela-companion' ),
						'right' => __( 'Right Sidebar', 'vela-companion' ),
						'no' => __( 'No Sidebar', 'vela-companion' ),
					),
				),
				array(
					'name'    => __( 'Content Before Sidebar', 'vela-companion' ),
					'desc'    => '',
					'id'      => $prefix . 'before_sidebar',
					'type'    => 'textarea',
					'default' => ''
				),
				
				array(
					'name'    => __( 'Content After Sidebar', 'vela-companion' ),
					'desc'    => '',
					'id'      => $prefix . 'after_sidebar',
					'type'    => 'textarea',
					'default' => ''
				),
				
			)
		);
		}elseif( $theme == 'capeone' || strstr($theme,'capeone') ){
			
			
		  $menus = array( esc_html__( 'Default', 'vela-companion' ) );
		  $get_menus 	= get_terms( 'nav_menu', array( 'hide_empty' => true ) );
		  foreach ( $get_menus as $menu) {
			  $menus[$menu->term_id] = $menu->name;
		  }
		  
		  $meta_boxes['theme_options_metabox'] = array(
			  'id'         => 'theme_options_metabox',
			  'title'      => __( 'Page Options', 'vela-companion' ),
			  'pages'      => array( 'page', 'post' ), // Post type
			  'context'    => 'normal',
			  'priority'   => 'high',
			  'fields'     => array(
				  
				  array(
					  'name'    => __( 'Custom Menu', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'custom_menu',
					  'type'    => 'select',
					  'default' => '',
					  'options' => $menus,
				  ),
				  
				  array(
					  'name'    => __( 'Header Transparent', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'transparent_header',
					  'type'    => 'checkbox',
					  'default' => '',
					  'options' => '',
				  ),
				  
				   array(
					  'name'    => __( 'Display Titlebar', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'display_titlebar',
					  'type'    => 'select',
					  'default' => 'default',
					  'options' => array( 'default' => __( 'Default', 'vela-companion' ), '1' => __( 'Yes', 'vela-companion' ),'no' => __( 'No', 'vela-companion' ) ),
				  ),
				  array(
					  'name'    => __( 'Display Breadcrumb', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'display_breadcrumb',
					  'type'    => 'select',
					  'default' => 'default',
					  'options' => array( 'default' => __( 'Default', 'vela-companion' ), '1' => __( 'Yes', 'vela-companion' ),'no' => __( 'No', 'vela-companion' ) ),
				  ),
				  
				  array(
					  'name'    => __( 'Titlebar Background Color', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'titlebar_bg_color',
					  'type'    => 'colorpicker',
					  'default' => ''
				  ),
				  array(
					  'name' => __( 'Titlebar Background Image', 'vela-companion' ),
					  'desc' => __( 'Upload an image or enter a URL.', 'vela-companion' ),
					  'id'   => $prefix . 'titlebar_bg_image',
					  'type' => 'file',
				  ),
				  				  
				  array(
					  'name'    => __( 'Page Background Color', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'bg_color',
					  'type'    => 'colorpicker',
					  'default' => '#ffffff'
				  ),
				  array(
					  'name' => __( 'Page Background Image', 'vela-companion' ),
					  'desc' => __( 'Upload an image or enter a URL.', 'vela-companion' ),
					  'id'   => $prefix . 'bg_image',
					  'type' => 'file',
				  ),
				  array(
					  'name'    => __( 'Sidebar', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'sidebar',
					  'type'    => 'radio',
					  'default' => '',
					  'options' => array(
						  '' => __( 'Default', 'vela-companion' ),
						  'left' => __( 'Left Sidebar', 'vela-companion' ),
						  'right' => __( 'Right Sidebar', 'vela-companion' ),
						  'no' => __( 'No Sidebar', 'vela-companion' ),
					  ),
				  ),
				  array(
					  'name'    => __( 'Content Before Sidebar', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'before_sidebar',
					  'type'    => 'textarea',
					  'default' => ''
				  ),
				  
				  array(
					  'name'    => __( 'Content After Sidebar', 'vela-companion' ),
					  'desc'    => '',
					  'id'      => $prefix . 'after_sidebar',
					  'type'    => 'textarea',
					  'default' => ''
				  ),
				  
			  )
		  );
			
			
			}else{
	
	
	$meta_boxes['theme_options_metabox'] = array(
		'id'         => 'theme_options_metabox',
		'title'      => __( 'Page Options', 'vela-companion' ),
		'pages'      => array( 'page', 'post' ), // Post type
		'context'    => 'normal',
		'priority'   => 'high',
		'fields'     => array(
			
			
			
			array(
				'name'    => __( 'Background Color', 'vela-companion' ),
				'desc'    => '',
				'id'      => $prefix . 'bg_color',
				'type'    => 'colorpicker',
				'default' => '#ffffff'
			),
			array(
				'name' => __( 'Background Image', 'vela-companion' ),
				'desc' => __( 'Upload an image or enter a URL.', 'vela-companion' ),
				'id'   => $prefix . 'bg_image',
				'type' => 'file',
			),
			array(
				'name'    => __( 'Sidebar', 'vela-companion' ),
				'desc'    => '',
				'id'      => $prefix . 'sidebar',
				'type'    => 'radio',
				'default' => '',
				'options' => array(
					'' => __( 'Default', 'vela-companion' ),
					'left' => __( 'Left Sidebar', 'vela-companion' ),
					'right' => __( 'Right Sidebar', 'vela-companion' ),
					'no' => __( 'No Sidebar', 'vela-companion' ),
				),
			),
			array(
				'name'    => __( 'Content Before Sidebar', 'vela-companion' ),
				'desc'    => '',
				'id'      => $prefix . 'before_sidebar',
				'type'    => 'textarea',
				'default' => ''
			),
			
			array(
				'name'    => __( 'Content After Sidebar', 'vela-companion' ),
				'desc'    => '',
				'id'      => $prefix . 'after_sidebar',
				'type'    => 'textarea',
				'default' => ''
			),
			
		)
	);
	
		}

	return $meta_boxes;
}

add_action( 'init', 'vela_initialize_cmb_meta_boxes', 9999 );
/**
 * Initialize the metabox class.
 */
function vela_initialize_cmb_meta_boxes() {

	if ( ! class_exists( 'vela_Meta_Box' ) )
		require_once 'init.php';

}