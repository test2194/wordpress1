<?php

function astore_sanitize_woo_categories( $input )
{
    $valid = astore_get_woo_categories();
    foreach ($input as $value) {
        if ( !array_key_exists( $value, $valid ) ) {
            return array();
        }
    }

    return $input;
}
function astore_get_pages(){
  $pages = get_pages(); 
  $return = array();
  foreach ( $pages as $page ) {
	$return [$page->ID] = $page->post_title;
  }
  return $return;
}
/**
 * Defines customizer options
 */

function astore_customizer_library_options() {
	
	global $astore_customizer_options, $astore_customizer_options, $wp_version;

	// Stores all the controls that will be added
	$options = array();

	// Stores all the sections to be added
	$sections = array();

	// Stores all the panels to be added
	$panels = array();

	// Adds the sections to the $options array
	$options['sections'] = $sections;
	
	$imagepath = get_template_directory_uri().'/assets/images/';
	
	$target = array(
		'_blank' => __( 'Blank', 'astore' ),
		'_self'  => __( 'Self', 'astore' )
	);
	
	$transport = '';
	if ( version_compare( $wp_version, '4.9' ) >= 0 ){
		$transport = 'postMessage';
	}
	


	// Panel Header
	
	$panel = 'panel-header';
	
	$panels[] = array(
		'id' => $panel,
		'title' => __( 'AStore: Header Options', 'astore' ),
		'priority' => '5'
	);
	
	$section = 'section-header-general-options';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'General Options', 'astore' ),
		'priority' => '1',
		'panel' => $panel
	);
	
	$options['header_full_width'] = array(
		'id' => 'header_full_width',
		'label'   => __( 'Full-width Header', 'astore' ),
		'section' => $section,
		'type'    => 'checkbox',
		'transport' => $transport,
		'default' => '',
	);
	
	
	$options['sticky_logo'] = array(
		'id' => 'sticky_logo',
		'label'   => __( 'Sticky Header Logo', 'astore' ),
		'section' => 'title_tagline',
		'type'    => 'image',
		'default' => '',
		//'transport' => 'postMessage',
	);
	
	$section = 'section-header-topbar';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Top Bar', 'astore' ),
		'priority' => '2',
		'panel' => $panel
	);
	
	$options['display_topbar'] = array(
		'id' => 'display_topbar',
		'label'   => __( 'Display Top Bar', 'astore' ),
		'section' => $section,
		'type'    => 'checkbox',
		'transport' => $transport,
		'default' => '',
	);
	
	$options['topbar_left'] = array(
		'id' => 'topbar_left',
		'label'   => __( 'Top Bar Left', 'astore' ),
		'section' => $section,
		'type'    => 'repeater',
		'transport' => $transport,
		'row_label' => array(
					'type' => 'field',
					'value' => esc_attr__('Item', 'astore' ),
					'field' => 'text',),
		'fields' => array(
			'icon'=>array('type'=>'iconpicker','default'=>'','label'=> __( 'Font-awesome Icon', 'astore' )),
			'text'=>array('type'=>'text','default'=>'','label'=> __( 'Text', 'astore' )),
			'link'=>array('type'=>'text','default'=>'','label'=> __( 'Link', 'astore' )),
			'target'=>array('type'=>'select','default'=>'', 'choices'=> $target, 'label'=> __( 'Target', 'astore' )),
		),
		'default' =>  array(
			array(
				"icon" => 'fa-envelope',
				"text" => "admin@domain.com",
				"link" => "",
				"target" => "_self",
				),
			array(
				"icon" => 'fa-phone',
				"text" => "011 322 44 56",
				"link" => "",
				"target" => "_self",
				),
			array(
				"icon" => 'fa-clock-o',
				"text" => "Monday - Friday 10 AM - 8 PM",
				"link" => "",
				"target" => "_self",
				),
			)
		);
		
	$options['topbar_icons'] = array(
		'id' => 'topbar_icons',
		'label'   => __( 'Top Bar Icons', 'astore' ),
		'section' => $section,
		'type'    => 'repeater',
		'transport' => $transport,
		'row_label' => array(
					'type' => 'field',
					'value' => esc_attr__('Icon', 'astore' ),
					'field' => 'icon',),
		'fields' => array(
			'icon'=>array('type'=>'iconpicker','default'=>'','label'=> __( 'Font-awesome Icon', 'astore' )),
			'link'=>array('type'=>'text','default'=>'','label'=> __( 'Link', 'astore' )),
			'target'=>array('type'=>'select','default'=>'', 'choices'=> $target, 'label'=> __( 'Target', 'astore' )),
		),
		'default' =>  array(
			array(
				"icon" => 'fa-twitter',
				"link" => "#",
				"target" => "_self",
				),
			array(
				"icon" => 'fa-facebook',
				"link" => "#",
				"target" => "_self",
				),
			array(
				"icon" => 'fa-weibo',
				"link" => "#",
				"target" => "_self",
				),
			)
		);
	
	$options['topbar_right'] = array(
		'id' => 'topbar_right',
		'label'   => __( 'Top Bar Right', 'astore' ),
		'section' => $section,
		'type'    => 'repeater',
		'transport' => $transport,
		'row_label' => array(
					'type' => 'field',
					'value' => esc_attr__('Item', 'astore' ),
					'field' => 'text',),
		'fields' => array(
			'icon'=>array('type'=>'iconpicker','default'=>'','label'=> __( 'Font-awesome Icon', 'astore' )),
			'text'=>array('type'=>'text','default'=>'','label'=> __( 'Text', 'astore' )),
			'link'=>array('type'=>'text','default'=>'','label'=> __( 'Link', 'astore' )),
			'target'=>array('type'=>'select','default'=>'', 'choices'=> $target, 'label'=> __( 'Target', 'astore' )),
		),
		'default' =>  array(
			array(
				"icon" => "",
				"text" => "Home",
				"link" => "#",
				"target" => "_self",
				),
			array(
				"icon" => "",
				"text" => "Blog",
				"link" => "#",
				"target" => "_self",
				),
			array(
				"icon" => "",
				"text" => "Contact",
				"link" => "#",
				"target" => "_self",
				),
			)
		);		
	
	$section = 'section-navigation-bar';

	$sections[] = array(
		'id' => $section,
		'title' => __( 'Navigation Bar', 'astore' ),
		'priority' => '2',
		'panel' => $panel
	);
	$options['display_shopping_cart_icon'] = array(
			'id' => 'display_shopping_cart_icon',
			'label'   => __( 'Display Shopping Cart Icon', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'default' => '1',
			//'transport' => $transport,
		);
	
	$options['sticky_header'] = array(
			'id' => 'sticky_header',
			'label'   => __( 'Sticky Navigation Bar', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'default' => '1',
			'transport' => $transport,
		);
	$options['sticky_header_background_color'] = array(
			'id' => 'sticky_header_background_color',
			'label'   => __( 'Sticky Navigation Bar background Color', 'astore' ),
			'section' => $section,
			'type'    => 'color',
			'default' => '#ffffff',
			'transport' => $transport,
		);
		
	$options['sticky_header_background_opacity'] = array(
			'id' => 'sticky_header_background_opacity',
			'label'   => __( 'Sticky Navigation Bar background Opacity', 'astore' ),
			'section' => $section,
			'type'    => 'range-value',
			'default' => '1',
			'transport' => $transport,
			'input_attrs' => array(
			'min'    => 0,
			'max'    => 1,
			'step'   => 0.1,
			'suffix' => '',
  	),
	);
	
	$options['categories_menu_toggle'] = array(
			'id' => 'categories_menu_toggle',
			'label'   => __( 'Display Categories Menu Toggle', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'default' => '1',
			//'transport' => $transport,
		);
	
	$options['categories_menu_toggle_text'] = array(
			'id' => 'categories_menu_toggle_text',
			'label'   => __( 'Categories Menu Toggle Text', 'astore' ),
			'section' => $section,
			'type'    => 'text',
			'default' => 'Browse Categories',
			//'transport' => $transport,
		);
	
		
	// Panel Footer
	$section = 'panel-footer';

	$sections[] = array(
			'id' => $section,
			'title' => __( 'AStore: Footer Options', 'astore' ),
			'priority' => '9'
		);
	
	$options['display_footer_widgets'] = array(
			'id' => 'display_footer_widgets',
			'label'   => __( 'Display Footer Widgets', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'transport' => 'postMessage', 
			'default' => '',
		);
	
	$options['display_footer_icons'] = array(
			'id' => 'display_footer_icons',
			'label'   => __( 'Display Footer Icons', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'transport' => 'postMessage', 
			'default' => '1',
		);
	
	$options['footer_icons'] = array(
				'id' => 'footer_icons',
				'label'   => __( 'Footer Icons', 'astore' ),
				'section' => $section,
				'type'    => 'repeater',
				'transport' => $transport,
				'row_label' => array(
							'type' => 'field',
							'value' => esc_attr__('Icon', 'astore' ),
							'field' => 'title',),
				'fields' => array(
					'icon'=>array('type'=>'iconpicker','default'=>'','label'=> __( 'Font-awesome Icon', 'astore' )),
					'title'=>array('type'=>'text','default'=>'','label'=> __( 'Title', 'astore' )),
					'link'=>array('type'=>'text','default'=> '','label'=> __( 'Link', 'astore' )),
				
				),
				'default' =>  array(
					array(
						"icon" => 'fa-twitter',
						"title" => "",
						"link" => "",
						),
					array(
						"icon" => 'fa-facebook',
						"title" => "",
						"link" => "",
						),
					array(
						"icon" => 'fa-instagram',
						"title" => "",
						"link" => "",
						),
					array(
						"icon" => 'fa-google-plus',
						"title" => "",
						"link" => "",
						),
					array(
						"icon" => 'fa-youtube',
						"title" => "",
						"link" => "",
						),
					)
				);
	
	$options['copyright'] = array(
		'id' => 'copyright',
		'label'   => __( 'Copyright', 'astore' ),
		'section' => $section,
		'type'    => 'astore_editor',
		'transport' => 'postMessage',
		'default' => ''
		);
			
	$options['section_order'] = array(
		'id' => 'section_order',
		'label'   => __( 'Section Order', 'astore' ),
		'section' => $section,
		'type'    => 'text',
		'default' => '',
		);

	// Panel Typography
	$panel = 'panel-typography';
	
	$panels[] = array(
		'id' => $panel,
		'title' => __( 'AStore: Typography', 'astore' ),
		'priority' => '11'
	);
	
	$section = 'section-font-family';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Font Family', 'astore' ),
		'priority' => '10',
		'panel' => $panel
	);
	
	$options['headings_font_family'] = array(
		'id' => 'headings_font_family',
		'label'   => __( 'Headings font family', 'astore' ),
		'section' => $section,
		'type'    => 'select',
		'choices' => customizer_library_get_font_choices(),
		'default' => 'Oswald'
	);
	
	$options['body_font_family'] = array(
		'id' => 'body_font_family',
		'label'   => __( 'Body font family', 'astore' ),
		'section' => $section,
		'type'    => 'select',
		'choices' => customizer_library_get_font_choices(),
		'default' => 'Open Sans'
	);
	
	$section = 'section-font-size';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'General Font Size', 'astore' ),
		'priority' => '11',
		'panel' => $panel
	);
	
	$options['body_font_size'] = array(
		'id' => 'body_font_size',
		'label'   => __( 'Body Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '13',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	
	$options['h1_font_size'] = array(
		'id' => 'h1_font_size',
		'label'   => __( 'H1 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '36',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['h2_font_size'] = array(
		'id' => 'h2_font_size',
		'label'   => __( 'H2 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '30',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['h3_font_size'] = array(
		'id' => 'h3_font_size',
		'label'   => __( 'H3 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '24',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['h4_font_size'] = array(
		'id' => 'h4_font_size',
		'label'   => __( 'H4 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '20',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['h5_font_size'] = array(
		'id' => 'h5_font_size',
		'label'   => __( 'H5 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '18',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['h6_font_size'] = array(
		'id' => 'h6_font_size',
		'label'   => __( 'H6 Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '16',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	
	$section = 'section-font-size-section';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Section Font Size', 'astore' ),
		'priority' => '11',
		'panel' => $panel
	);
	
	$options['section_title_font_size'] = array(
		'id' => 'section_title_font_size',
		'label'   => __( 'Section Title Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '40',
		'transport' => 'postMessage',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	$options['section_subtitle_font_size'] = array(
		'id' => 'section_subtitle_font_size',
		'label'   => __( 'Section Subtitle Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '14',
		'transport' => 'postMessage',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	
	$options['section_item_title_font_size'] = array(
		'id' => 'section_item_title_font_size',
		'label'   => __( 'Section Item Ttile Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '20',
		'transport' => 'postMessage',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	
	$options['section_content_font_size'] = array(
		'id' => 'section_content_font_size',
		'label'   => __( 'Section Content Font Size', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '14',
		'transport' => 'postMessage',
		'input_attrs' => array(
			'min'    => 1,
			'max'    => 60,
			'step'   => 1,
			'suffix' => 'px',
  	),
	);
	
	$section = 'section-font-color';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Font Color', 'astore' ),
		'priority' => '12',
		'panel' => $panel
	);
	$options['section_title_color'] = array(
		'id' => 'section_title_color',
		'label'   => __( 'Section Title Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#333'
	);
	
	$options['section_subtitle_color'] = array(
		'id' => 'section_subtitle_color',
		'label'   => __( 'Section Subtitle Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#555'
	);
	
	$options['section_item_title_color'] = array(
		'id' => 'section_item_title_color',
		'label'   => __( 'Section Item Title Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#bc2944'
	);
	
	$options['section_content_color'] = array(
		'id' => 'section_content_color',
		'label'   => __( 'Section Content Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#555'
	);
	
	$options['section_link_color'] = array(
		'id' => 'section_link_color',
		'label'   => __( 'Section Link Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#a0a0a0'
	);
	
	$options['section_link_hover_color'] = array(
		'id' => 'section_link_hover_color',
		'label'   => __( 'Section Link Hover Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#bc2944'
	);
	
	$options['section_icon_color'] = array(
		'id' => 'section_icon_color',
		'label'   => __( 'Section Icon Color', 'astore' ),
		'section' => $section,
		'type'    => 'color',
		'default' => '#555'
	);

	
	// Panel Pages & Posts Options
	$panel = 'panel-pages-posts-options';
	$panels[] = array(
		'id' => $panel,
		'title' => __( 'AStore: Pages & Posts Options', 'astore' ),
		'priority' => '14'
	);
	
	$section = 'section-title-bar';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Title Bar', 'astore' ),
		'priority' => '9',
		'panel' => $panel
	);
	
	$options['display_titlebar'] = array(
			'id' => 'display_titlebar',
			'label'   => __( 'Display Title Bar', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'default' => '1',
			//'transport' => $transport,
		);
	
	
	$section = 'section-posts-archive';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Posts archive', 'astore' ),
		'priority' => '10',
		'panel' => $panel
	);
	
	$options['blog_list_style'] = array(
		'id' => 'blog_list_style',
		'label'   => __( 'Posts Archive Layout', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		//'transport' => $transport,
		'choices' => array(
				'1'=> array('label'=>__( 'Full Width Image', 'astore' ),'url'=> $imagepath.'customize/blog-style1.png'),
				'2'=> array('label'=>__( 'Side Image', 'astore' ),'url'=> $imagepath.'customize/blog-style2.png'),
				'3'=> array('label'=>__( 'Grid', 'astore' ),'url'=> $imagepath.'customize/blog-style3.png'),
				),
		'default' => '1'
	);
	
	//Display: Full Post/Excerpt
	//Display Feature Image/Display Category/Display Author/Dispaly Date
	$options['excerpt_style'] = array(
		'id' => 'excerpt_style',
		'label'   => __( 'Excerpt', 'astore' ),
		'section' => $section,
		'type'    => 'radio',
		'choices' => array(
				'0'=> __( 'Excerpt', 'astore' ),
				'1'=> __( 'Full Post', 'astore' ),
				),
		'default' => '0'
	);
	
	$options['excerpt_display_feature_image'] = array(
			'id' => 'excerpt_display_feature_image',
			'label'   => __( 'Display Feature Image', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
		
	$options['excerpt_display_category'] = array(
			'id' => 'excerpt_display_category',
			'label'   => __( 'Display Category', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	$options['excerpt_display_author'] = array(
			'id' => 'excerpt_display_author',
			'label'   => __( 'Display Author', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	$options['excerpt_display_date'] = array(
			'id' => 'excerpt_display_date',
			'label'   => __( 'Display Date', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	
	
	$section = 'section-posts-single';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Single Post', 'astore' ),
		'priority' => '10',
		'panel' => $panel
	);
	
	
	//Display Feature Image/Display Category/Display Author/Dispaly Date
	
	$options['display_feature_image'] = array(
			'id' => 'display_feature_image',
			'label'   => __( 'Display Feature Image', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
		
	$options['display_category'] = array(
			'id' => 'display_category',
			'label'   => __( 'Display Category', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	$options['display_author'] = array(
			'id' => 'display_author',
			'label'   => __( 'Display Author', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	$options['display_date'] = array(
			'id' => 'display_date',
			'label'   => __( 'Display Date', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	
	$section = 'section-sidebar-options';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'Sidebar Settings', 'astore' ),
		'priority' => '11',
		'panel' => $panel
	);
	// Sidebar
	
	$options['page_sidebar_layout'] = array(
		'id' => 'page_sidebar_layout',
		'label'   => __( 'Sidebar: Pages', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		'default' => 'no',
		//'choices' => array('no' =>__( 'No Sidebar', 'astore' ),'left'=>__( 'Left Sidebar', 'astore' ),'right'=>__( 'Right Sidebar', 'astore' )),
		'choices' => array(
				'no'=> array('label'=>__( 'No Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-none.png'),
				'left'=> array('label'=>__( 'Left Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-left.png'),
				'right'=> array('label'=>__( 'Right Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-right.png'),
				),
		);
	
	$options['blog_sidebar_layout'] = array(
		'id' => 'blog_sidebar_layout',
		'label'   => __( 'Sidebar: Single Post', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		'default' => 'no',
		//'choices' => array('no' =>__( 'No Sidebar', 'astore' ),'left'=>__( 'Left Sidebar', 'astore' ),'right'=>__( 'Right Sidebar', 'astore' )),
		'choices' => array(
				'no'=> array('label'=>__( 'No Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-none.png'),
				'left'=> array('label'=>__( 'Left Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-left.png'),
				'right'=> array('label'=>__( 'Right Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-right.png'),
				),
		);
	
	$options['blog_archives_sidebar_layout'] = array(
		'id' => 'blog_archives_sidebar_layout',
		'label'   => __( 'Sidebar: Posts Archive', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		'default' => 'no',
		'choices' => array('no' =>__( 'No Sidebar', 'astore' ),'left'=>__( 'Left Sidebar', 'astore' ),'right'=>__( 'Right Sidebar', 'astore' )),
		'choices' => array(
				'no'=> array('label'=>__( 'No Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-none.png'),
				'left'=> array('label'=>__( 'Left Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-left.png'),
				'right'=> array('label'=>__( 'Right Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-right.png'),
				),
		);
		
	$options['woo_single_sidebar_layout'] = array(
		'id' => 'woo_single_sidebar_layout',
		'label'   => __( 'Sidebar: WooCommerce Single Product', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		'default' => 'no',
		'choices' => array('no' =>__( 'No Sidebar', 'astore' ),'left'=>__( 'Left Sidebar', 'astore' ),'right'=>__( 'Right Sidebar', 'astore' )),
		'choices' => array(
				'no'=> array('label'=>__( 'No Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-none.png'),
				'left'=> array('label'=>__( 'Left Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-left.png'),
				'right'=> array('label'=>__( 'Right Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-right.png'),
				),
		);
		
	$options['woo_archives_sidebar_layout'] = array(
		'id' => 'woo_archives_sidebar_layout',
		'label'   => __( 'Sidebar: WooCommerce Archive', 'astore' ),
		'section' => $section,
		'type'    => 'radio-image',
		'default' => 'no',
		'choices' => array('no' =>__( 'No Sidebar', 'astore' ),'left'=>__( 'Left Sidebar', 'astore' ),'right'=>__( 'Right Sidebar', 'astore' )),
		'choices' => array(
				'no'=> array('label'=>__( 'No Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-none.png'),
				'left'=> array('label'=>__( 'Left Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-left.png'),
				'right'=> array('label'=>__( 'Right Sidebar', 'astore' ),'url'=> $imagepath.'customize/sidebar-right.png'),
				),
		);
		
		
	
	// General Options
/*	$panel = 'general-options';
	$panels[] = array(
		'id' => $panel,
		'title' => __( 'General Options', 'astore' ),
		'priority' => '12'
	);*/
	$section = 'astore-general-options';
	$sections[] = array(
		'id' => $section,
		'title' => __( 'AStore: General Options', 'astore' ),
		'priority' => '15',
		'panel' => ''
	);
	
	$options['scheme'] = array(
			'id' => 'scheme',
			'label'   => __( 'Scheme', 'astore' ),
			'section' => $section,
			'type'    => 'radio',
			//'transport' => 'postMessage', 
			'default' => 'light',
			'choices' => array(
				'light'    => __( 'Light', 'astore' ),
				'dark'    => __( 'Dark', 'astore' ),
  			),
			
		);
	
	$options['page_preloader'] = array(
			'id' => 'page_preloader',
			'label'   => __( 'Display Page Pre-loader', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '',
		);
		
	$options['preloader_background'] = array(
			'id' => 'preloader_background',
			'label'   => __( 'Pre-loader Background Color', 'astore' ),
			'section' => $section,
			'type'    => 'color',
			//'transport' => 'postMessage', 
			'default' => '#999999',
		);
	
	$options['preloader_opacity'] = array(
		'id' => 'preloader_opacity',
		'label'   => __( 'Pre-loader Background Opacity', 'astore' ),
		'section' => $section,
		'type'    => 'range-value',
		'default' => '0.8',
		'input_attrs' => array(
			'min'    => 0,
			'max'    => 1,
			'step'   => 0.1,
			'suffix' => '',
  	),
	);
	
	$options['preloader_image'] = array(
			'id' => 'preloader_image',
			'label'   => __( 'Pre-loader Image', 'astore' ),
			'section' => $section,
			'type'    => 'image',
			'transport' => 'postMessage', 
			'default' =>  $imagepath.'preloader.gif',
		);
		
	$options['display_scroll_to_top'] = array(
			'id' => 'display_scroll_to_top',
			'label'   => __( 'Enable Scroll to Top Button', 'astore' ),
			'section' => $section,
			'type'    => 'checkbox',
			'transport' => 'postMessage', 
			'default' => '1',
		);
	
		
	$options['homepage_animation'] = array(
			'id' => 'homepage_animation',
			'label'   => __( 'Homepage Animation', 'astore' ),
			'section' => 'static_front_page',
			'type'    => 'checkbox',
			//'transport' => 'postMessage', 
			'default' => '1',
		);
	

	$astore_customizer_options = $options;
	
	$new_options = array();
	foreach( $options as $option ){
		if( isset($option['default']) ){
			$key = ASTORE_TEXTDOMAIN.'['.$option['id'].']';
			$option['id'] = $key;
			$new_options[$key] = $option;
			}

		}
	// Adds the sections to the $options array
	$new_options['sections'] = $sections;

	// Adds the panels to the $options array
	$new_options['panels'] = $panels;
	
	$customizer_library = Customizer_Library::Instance();
	$customizer_library->add_options( $new_options );
	return $options;

}
add_action( 'init', 'astore_customizer_library_options' );