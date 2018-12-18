<?php

define('ASTORE_VERSION','1.0.8');
define('ASTORE_TEXTDOMAIN','astore');

if( !function_exists('is_plugin_active') ) {
	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
}		
$template_directory = trailingslashit( get_template_directory() );

require_once($template_directory . '/inc/plugin-install/class-plugin-install-helper.php');
// Helper library for the theme customizer.
require_once($template_directory . '/inc/customizer-library/customizer-library.php');
// Define options for the theme customizer.
require_once($template_directory . '/inc/customizer-options.php');
require_once($template_directory . 'inc/customizer-library/custom-controls/editor/editor-page.php');
require_once($template_directory . 'inc/wc-list-grid.php');

function astore_setup() {

	load_theme_textdomain( 'astore' );

	add_theme_support( 'automatic-feed-links' );

	add_theme_support( 'title-tag' );

	add_theme_support( 'post-thumbnails' );

	add_image_size( 'astore-featured-image', 960, 720, true );
	
	add_image_size( 'astore-widget-post-image', 480, 360, true );

	// Set the default content width.
	$GLOBALS['content_width'] = 850;

	register_nav_menus( array(
		'top'    => __( 'Top Menu', 'astore' ),
		'browse-categories'    => __( 'Top Left Menu (Browse Categories)', 'astore' ),
	) );

	/*
	 * Switch default core markup for search form, comment form, and comments
	 * to output valid HTML5.
	 */
	add_theme_support( 'html5', array(
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
	) );

	// Add theme support for Custom Logo.
	add_theme_support( 'custom-logo', array(
		'height'      => 100,
		'flex-width'  => true,
		'flex-height' => true,
	) );
	
	// Setup the WordPress core custom header feature.
	add_theme_support( 'custom-header', array(
		'default-image'          => '',
		'random-default'         => false,
		'width'                  => '1920',
		'height'                 => '70',
		'flex-height'            => true,
		'flex-width'             => true,
		'default-text-color'     => '#333333',
		'header-text'            => true,
		'uploads'                => true,
		'wp-head-callback'       => '',
		'admin-head-callback'    => '',
		'admin-preview-callback' => ''
	)); 

	// Setup the WordPress core custom background feature.
	add_theme_support( 'custom-background',  array(
		'default-color' => 'ffffff',
		'default-image' => '',
	) );

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );
	
	// Woocommerce Support
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
	
	// Add Works Image size
	add_image_size( 'astore-works', 480, 360, true );

	/*
	 * This theme styles the visual editor to resemble the theme style,
	 * specifically font, colors, and column width.
 	 */
	add_editor_style( array( 'assets/css/editor-style.css' ) );
	
}
add_action( 'after_setup_theme', 'astore_setup' );

/**
 * After switch theme
 */
function astore_after_switch_theme(){
	delete_option('astore_welcome_notice');
}
add_action('after_switch_theme', 'astore_after_switch_theme');

/**
 * Enqueue scripts and styles.
 */
function astore_scripts() {
	
	global $astore_options;
	
	$astore_options = get_option(ASTORE_TEXTDOMAIN);
	
	$headings_font_family = esc_attr(astore_option( 'headings_font_family'));
	$body_font_family     = esc_attr(astore_option( 'body_font_family'));
	$page_preloader       = absint(astore_option( 'page_preloader'));
	$fonts[] = $headings_font_family;
	$fonts[] = $body_font_family;
	
	$theme_info = wp_get_theme();
	
	wp_enqueue_style( 'astore-google-fonts', customizer_library_get_google_font_uri($fonts), false, '', false );
	wp_enqueue_style( 'simple-line-icons',  get_template_directory_uri() .'/assets/plugins/simple-line-icons/css/simple-line-icons.css', false, '', false );
	wp_enqueue_style( 'bootstrap',  get_template_directory_uri() .'/assets/plugins/bootstrap/css/bootstrap.css', false, '', false );
	wp_enqueue_style( 'font-awesome',  get_template_directory_uri() .'/assets/plugins/font-awesome/css/font-awesome.min.css', false, '', false );
	wp_enqueue_style( 'owl-carousel',  get_template_directory_uri() .'/assets/plugins/owl-carousel/css/owl.carousel.css', false, '', false );
	
	wp_enqueue_style( 'animate', get_template_directory_uri() .'/assets/plugins/animate.css' );
	// Theme stylesheet.
	wp_enqueue_style( 'astore-style', get_stylesheet_uri(), array(), $theme_info->get( 'Version' ) );
	
	if ( is_rtl() )
		wp_enqueue_style( 'astore-rtl', get_template_directory_uri() .'/rtl.css' );
	
	wp_enqueue_script( 'bootstrap', get_template_directory_uri() . '/assets/plugins/bootstrap/js/bootstrap.min.js' , array( 'jquery' ), null, true);
	
	wp_enqueue_script( 'respond', get_template_directory_uri() . '/assets/plugins/respond.min.js' , array( 'jquery' ), null, true);
	wp_enqueue_script( 'owl-carousel', get_template_directory_uri() . '/assets/plugins/owl-carousel/js/owl.carousel.min.js' , array( 'jquery' ), null, true);
	
	
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
	
	$preloader_background = esc_attr(astore_option('preloader_background'));
	$preloader_opacity = esc_attr(astore_option('preloader_opacity'));
	$preloader_image = esc_attr(astore_option('preloader_image'));
	if (is_numeric($preloader_image)) {
		$image_attributes = wp_get_attachment_image_src($preloader_image, 'full');
		$preloader_image   = $image_attributes[0];
	}
	
	$preloader_bg = '';
	if($preloader_background!=''){
		$rgb = astore_hex2rgb( $preloader_background );
		$preloader_bg = "rgba(".$rgb[0].",".$rgb[1].",".$rgb[2].",".$preloader_opacity.")";
	}
	
	wp_enqueue_script( 'astore-main', get_template_directory_uri() . '/assets/js/astore.js' , array( 'jquery' ), null, true);
	wp_localize_script( 'astore-main', 'astore_params', array(
		'ajaxurl'  => esc_url(admin_url('admin-ajax.php')),
		'themeurl' => get_template_directory_uri(),
		'page_preloader' => $page_preloader,
		'preloader_background' => $preloader_bg,
		'preloader_image' => esc_url($preloader_image),
	)  );
	
	$custom_css = '';
	$header_text_color = get_header_textcolor();

	if ( 'blank' != $header_text_color ) :

	  $custom_css .= ".site-name,
		  .site-tagline {
		  color: ".esc_attr( $header_text_color )." !important;
	  }.site-tagline {
			  display: none;
		  }\r\n";
	else:

	  $custom_css .= ".site-name,
		  .site-tagline {
			  display: none;
		  }\r\n";
		
	endif;
	
	// Font family
	$custom_css .=  "h1,h2,h3,h4,h5,h6{font-family:".$headings_font_family.";}";
	$custom_css .=  "body,button,input,select,textarea{font-family:".$body_font_family.";}";
	
	// Font size
	$body_font_size = absint(astore_option('body_font_size'));
	if($body_font_size>0)
		$custom_css .=  "html, body, div{font-size:".$body_font_size."px;}";
		
	for($i=1;$i<=6;$i++){
		$heading_font_size = absint(astore_option('h'.$i.'_font_size'));
		$custom_css .=  "h".$i."{font-size:".$heading_font_size."px;}";
	}
		
	// Section Font size
	$section_title_font_size = absint(astore_option('section_title_font_size'));
	$custom_css .=  ".astore-section .astore-section-title{font-size:".$section_title_font_size."px;}";
	
	$section_subtitle_font_size = absint(astore_option('section_subtitle_font_size'));
	$custom_css .=  ".astore-section .astore-section-desc{font-size:".$section_subtitle_font_size."px;}";
	
	$section_item_title_font_size = absint(astore_option('section_item_title_font_size'));
	$custom_css .=  ".astore-section h4{font-size:".$section_item_title_font_size."px;}";
	
	$section_content_font_size = absint(astore_option('section_content_font_size'));
	$custom_css .=  ".astore-section, .astore-section p, .astore-section div{font-size:".$section_content_font_size."px;}";
	
	// Font color
	$section_title_color = esc_attr(astore_option( 'section_title_color'));
	$section_subtitle_color = esc_attr(astore_option( 'section_subtitle_color'));
	$section_item_title_color = esc_attr(astore_option( 'section_item_title_color'));
	$section_content_color = esc_attr(astore_option( 'section_content_color'));
	$section_link_color = esc_attr(astore_option( 'section_link_color'));
	$section_link_hover_color = esc_attr(astore_option( 'section_link_hover_color'));
	$section_icon_color = esc_attr(astore_option( 'section_icon_color'));
	
	$custom_css .=  ".astore-section-title {
		  color: ".$section_title_color.";
	  }";
	  
	$custom_css .=  ".astore-section-desc {
		  color: ".$section_subtitle_color.";
	  }";
	$custom_css .=  ".astore-feature-caption h4,
	  .astore-team-vcard h4,
	  .astore-counter-title,
	  .astore-section .astore-testimonial-name,
	  .astore-section .entry-title,
	  .astore-section .woocommerce ul.products li.product h3 {
		  color: ".$section_item_title_color.";
	  }";
	$custom_css .=  ".astore-section-content {
		  color: ".$section_content_color.";
	  }";
	$custom_css .=  ".astore-section-content a {
		  color: ".$section_link_color.";
	  }";
	$custom_css .=  ".astore-section-content a:hover {
		  color: ".$section_link_hover_color.";
	  }";
	$custom_css .=  ".astore-feature-item i,
	  .astore-counter-item i,
	  .astore-feature-item i {
		  color: ".$section_icon_color.";
	  }";

	$sticky_header_background_color   = astore_option('sticky_header_background_color');
	$sticky_header_background_opacity = astore_option('sticky_header_background_opacity');
	
	if( $sticky_header_background_color!='' ){
		$rgb = customizer_library_hex_to_rgb($sticky_header_background_color);
		$custom_css .=  "header .astore-fixed-header-wrap,header .astore-fixed-header-wrap .astore-header{background-color:rgba(".$rgb['r'].",".$rgb['g'].",".$rgb['b'].",".$sticky_header_background_opacity.");}";
	}

	$custom_css = apply_filters( 'astore_additional_css', $custom_css );
	wp_add_inline_style( 'astore-style', wp_filter_nohtml_kses($custom_css) );
}
add_action( 'wp_enqueue_scripts', 'astore_scripts' );

function astore_admin_scripts(){
	global $pagenow;
	wp_enqueue_script( 'astore-admin', get_template_directory_uri().'/assets/js/admin.js', array( 'jquery' ), '', true );
	if( $pagenow == "themes.php" && isset($_GET['page']) && $_GET['page'] == "astore-welcome" ):
		wp_enqueue_style( 'astore-admin', get_template_directory_uri() . '/assets/css/admin.css', '', '', false );
	endif;
	
	wp_localize_script( 'astore-admin', 'astore_admin', array(
			'ajaxurl' => esc_url( admin_url('admin-ajax.php') ),
		)  );
	}
add_action( 'admin_enqueue_scripts', 'astore_admin_scripts' );

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function astore_customize_controls_enqueue(){
	wp_enqueue_style( 'astore_library_customizer', get_template_directory_uri() . '/assets/css/customizer.css', '', '1.0.0', false );
	wp_enqueue_script( 'astore_library_customizer_controls', get_template_directory_uri() . '/assets/js/customizer.js', array( 'customize-preview'), '1.0.0', true );
	
	$loading         = esc_attr__('Updating','astore');
	$complete        = esc_attr__('Complete','astore');
	$error           = esc_attr__('Error','astore');
	$confirm         = esc_attr__( 'Click OK to reset. Any AStore options will be rastored!', 'astore' );

	wp_localize_script( 'astore_library_customizer_controls', 'astore_customize_params', array(
			'ajaxurl'        => admin_url('admin-ajax.php'),
			'themeurl' => get_template_directory_uri(),
			'loading' => $loading,
			'complete' => $complete,
			'error' => $error,
			'confirm' =>$confirm,

		)  );
		
}
add_action( 'customize_controls_init', 'astore_customize_controls_enqueue' );

function astore_customize_preview_enqueue(){
	wp_enqueue_script( 'astore_library_customizer_preview', get_template_directory_uri() . '/assets/js/customizer-preview.js', array( 'customize-preview' ), '1.0.0', true );
	}
add_action( 'customize_preview_init', 'astore_customize_preview_enqueue' );

/**
 * Prints HTML with meta information for the current post-date/time and author.
 */
function astore_posted_on() {

	// Get the author name; wrap it in a link.
	$byline = sprintf(
		/* translators: %s: post author */
		esc_attr__( 'by %s', 'astore' ),
		'<span class="entry-author"> <a href="' . esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ) . '">' . get_the_author() . '</a></span>'
	);
	
	// Finally, let's write all of this to the page.
	echo '<span class="entry-date">' . astore_time_link() . '</span> | ' . $byline . '';
}
 
/**
 * Gets a nicely formatted string for the published date.
 */
function astore_time_link() {
	$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
	$time_string = sprintf( $time_string,
		get_the_date( DATE_W3C ),
		get_the_date(),
		get_the_modified_date( DATE_W3C ),
		get_the_modified_date()
	);

	// Wrap the time string in a link, and preface it with 'Posted on'.
	return sprintf(
		/* translators: %s: post date */
		esc_attr__( '<span class="screen-reader-text">Posted on</span> %s ', 'astore' ),
		'<a href="' . esc_url( get_permalink() ) . '" rel="bookmark">' . $time_string . '</a>'
	);
}

/**
 * Returns an accessibility-friendly link to edit a post or page.
 */
function astore_edit_link() {

	$link = edit_post_link(
		sprintf(
			/* translators: %s: Name of current post */
			esc_attr__( 'Edit<span class="screen-reader-text"> "%s"</span>', 'astore' ),
			get_the_title()
		),
		'<span class="edit-link">',
		'</span>'
	);

	return $link;
}

/**
 * Register widget area.
 *
 */
function astore_widgets_init() {
	register_sidebar( array(
		'name'          => esc_attr__( 'Sidebar', 'astore' ),
		'id'            => 'sidebar-1',
		'description'   => esc_attr__( 'Add widgets here to appear in your sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Page Sidebar', 'astore' ),
		'id'            => 'sidebar-page',
		'description'   => esc_attr__( 'Add widgets here to appear in your pages sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Blog Sidebar', 'astore' ),
		'id'            => 'sidebar-blog',
		'description'   => esc_attr__( 'Add widgets here to appear in your posts sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Archives', 'astore' ),
		'id'            => 'sidebar-archives',
		'description'   => esc_attr__( 'Add widgets here to appear in your posts list sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	
	register_sidebar( array(
		'name'          => esc_attr__( 'WooCommerce Single Product', 'astore' ),
		'id'            => 'sidebar-woo-single',
		'description'   => esc_attr__( 'Add widgets here to appear in your products sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'WooCommerce Archives', 'astore' ),
		'id'            => 'sidebar-woo-archives',
		'description'   => esc_attr__( 'Add widgets here to appear in your products list sidebar.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Footer 1', 'astore' ),
		'id'            => 'footer-1',
		'description'   => esc_attr__( 'Add widgets here to appear in your footer.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );

	register_sidebar( array(
		'name'          => esc_attr__( 'Footer 2', 'astore' ),
		'id'            => 'footer-2',
		'description'   => esc_attr__( 'Add widgets here to appear in your footer.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Footer 3', 'astore' ),
		'id'            => 'footer-3',
		'description'   => esc_attr__( 'Add widgets here to appear in your footer.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
	
	register_sidebar( array(
		'name'          => esc_attr__( 'Footer 4', 'astore' ),
		'id'            => 'footer-4',
		'description'   => esc_attr__( 'Add widgets here to appear in your footer.', 'astore' ),
		'before_widget' => '<section id="%1$s" class="widget-box %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	) );
}
add_action( 'widgets_init', 'astore_widgets_init' );

/**
 *  Custom comments list
 */	
function astore_comment($comment, $args, $depth) {

?>
   
<li <?php comment_class("comment media-comment"); ?> id="comment-<?php comment_ID() ;?>">
	<div class="media-avatar media-left">
	<?php echo get_avatar($comment,'70','' ); ?>
  </div>
  <div class="media-body">
      <div class="media-inner">
          <h4 class="media-heading clearfix">
             <?php echo get_comment_author_link();?> - <?php comment_date(); ?> <?php edit_comment_link(__('(Edit)','astore'),'  ','') ;?>
             <?php comment_reply_link(array_merge( $args, array('depth' => $depth, 'max_depth' => $args['max_depth']))) ;?>
          </h4>
          
          <?php if ($comment->comment_approved == '0') : ?>
                   <em><?php esc_attr_e('Your comment is awaiting moderation.','astore') ;?></em>
                   <br />
                <?php endif; ?>
                
          <div class="comment-content"><?php comment_text() ;?></div>
      </div>
  </div>
</li>
                                            
<?php
	}
	
/**
 * Returns breadcrumbs.
 */
function astore_breadcrumbs() {
	$delimiter = '/'; 
	$before = '<span class="current">';
	$after = '</span>';
	if ( !is_home() && !is_front_page() || is_paged() ) {
		echo '<div itemscope itemtype="http://schema.org/WebPage" id="crumbs"><i class="fa fa-home"></i>';
		global $post;
		$homeLink = esc_url(home_url());
		echo ' <a itemprop="breadcrumb" href="' . $homeLink . '">' . __( 'Home' , 'astore' ) . '</a> ' . $delimiter . ' ';
		if ( is_category() ) {
			global $wp_query;
			$cat_obj = $wp_query->get_queried_object();
			$thisCat = $cat_obj->term_id;
			$thisCat = get_category($thisCat);
			$parentCat = get_category($thisCat->parent);
			if ($thisCat->parent != 0){
				$cat_code = get_category_parents($parentCat, TRUE, ' ' . $delimiter . ' ');
				echo $cat_code = str_replace ('<a','<a itemprop="breadcrumb"', $cat_code );
			}
			echo $before . '' . single_cat_title('', false) . '' . $after;
		} elseif ( is_day() ) {
			echo '<a itemprop="breadcrumb" href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo '<a itemprop="breadcrumb"  href="' . esc_url(get_month_link(get_the_time('Y'),get_the_time('m'))) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('d') . $after;
		} elseif ( is_month() ) {
			echo '<a itemprop="breadcrumb" href="' . esc_url(get_year_link(get_the_time('Y'))) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
			echo $before . get_the_time('F') . $after;
		} elseif ( is_year() ) {
			echo $before . get_the_time('Y') . $after;
		} elseif ( is_single() && !is_attachment() ) {
			if ( get_post_type() != 'post' ) {
				$post_type = get_post_type_object(get_post_type());
				$slug = $post_type->rewrite;
				echo '<a itemprop="breadcrumb" href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a> ' . $delimiter . ' ';
				echo $before . get_the_title() . $after;
			} else {
				$cat = get_the_category(); $cat = $cat[0];
				$cat_code = get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
				echo $cat_code = str_replace ('<a','<a itemprop="breadcrumb"', $cat_code );
				echo $before . get_the_title() . $after;
			}
		} elseif ( !is_single() && !is_page() && get_post_type() != 'post' ) {
			$post_type = get_post_type_object(get_post_type());
			if ($post_type)
			echo $before . $post_type->labels->singular_name . $after;
		} elseif ( is_attachment() ) {
			$parent = get_post($post->post_parent);
			$cat = get_the_category($parent->ID); $cat = isset($cat[0])?$cat[0]:'';
			echo '<a itemprop="breadcrumb" href="' . esc_url(get_permalink($parent)) . '">' . $parent->post_title . '</a> ' . $delimiter . ' ';
			echo $before . get_the_title() . $after;
		} elseif ( is_page() && !$post->post_parent ) {
			echo $before . get_the_title() . $after;
		} elseif ( is_page() && $post->post_parent ) {
			$parent_id  = $post->post_parent;
			$breadcrumbs = array();
			while ($parent_id) {
				$page = get_page($parent_id);
				$breadcrumbs[] = '<a itemprop="breadcrumb" href="' .esc_url( get_permalink($page->ID)) . '">' . get_the_title($page->ID) . '</a>';
				$parent_id  = $page->post_parent;
			}
			$breadcrumbs = array_reverse($breadcrumbs);
			foreach ($breadcrumbs as $crumb) echo $crumb . ' ' . $delimiter . ' ';
			echo $before . get_the_title() . $after;
		} elseif ( is_search() ) {
			echo $before ;
			printf( esc_attr__( 'Search Results for: %s', 'astore' ),  get_search_query() );
			echo  $after;
		} elseif ( is_tag() ) {
			echo $before ;
			printf( esc_attr__( 'Tag Archives: %s', 'astore' ), single_tag_title( '', false ) );
			echo  $after;
		} elseif ( is_author() ) {
			global $author;
			$userdata = get_userdata($author);
			echo $before ;
			printf( esc_attr__( 'Author Archives: %s', 'astore' ),  $userdata->display_name );
			echo  $after;
		} elseif ( is_404() ) {
			echo $before;
			esc_attr_e( 'Not Found', 'astore' );
			echo  $after;
		}
		if ( get_query_var('paged') ) {
			if ( is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author() )
				echo sprintf( esc_attr__( '( Page %s )', 'astore' ), get_query_var('paged') );
		}
		echo '</div>';
	}
}

/**
 * Get option
 */
function astore_option($name){
	
	global $astore_options,$astore_customizer_options;
	
	if(!$astore_customizer_options){
		$astore_customizer_options = astore_customizer_library_options();
		}
	if( isset($astore_options[$name]) )
		return $astore_options[$name];
	elseif(isset($astore_customizer_options[$name]['default'])){
		
		return $astore_customizer_options[$name]['default'];
		
	}
	else
		return '';
	}

function astore_option_saved($name){
	
	$astore_options = get_option(ASTORE_TEXTDOMAIN);
	
	if( isset($astore_options[$name]) )
		return $astore_options[$name];
	else
		return '';
	}

/**
 * Get sidebar
 */
function astore_get_sidebar($layout,$type){
	if($layout=='' || $layout == 'none' || $layout == 'no' )
		return '';
	?>
	<div class="col-aside-<?php echo $layout; ?>">
    <?php do_action('astore_before_sidebar');?>
      <aside class="blog-side left text-left">
          <div class="widget-area">
             <?php get_sidebar($type);?>
          </div>
        </aside>
        <?php do_action('astore_after_sidebar');?>
      </div>
<?php
	}

/**
 * Selective Refresh
 */
function astore_register_partials( WP_Customize_Manager $wp_customize ) {
	
	  global $astore_customizer_options;

	// Abort if selective refresh is not available.
		if ( ! isset( $wp_customize->selective_refresh ) ) {
			return;
		}

		// Bail early if we don't have any options.
		if ( empty( $astore_customizer_options ) ) {
			return;
		}

	
	$wp_customize->selective_refresh->add_partial( 'copyright_selective', array(
		'selector' => '.copyright_selective',
		'settings' => array( 'astore[copyright]' ),
		'render_callback' => 'astore_copyright',
	) );
	
	
	$wp_customize->selective_refresh->add_partial( 'header_site_title', array(
		'selector' => '.site-name',
		'settings' => array( 'blogname' ),
		'render_callback' => 'astore_header_site_title',
		
	) );
	
	$wp_customize->selective_refresh->add_partial( 'header_site_description', array(
		'selector' => '.site-tagline',
		'settings' => array( 'blogdescription' ),
		'render_callback' => 'astore_header_site_descriptione',
		
	) );
	
	
	$wp_customize->get_section ('title_tagline')->panel = 'panel-header';
	//$wp_customize->get_section ('colors')->panel = 'panel-header';
	$wp_customize->get_section ('header_image')->panel = 'panel-header';
	
}
add_action( 'customize_register', 'astore_register_partials' );

/* footer */
function astore_copyright(){
	
	$astore_options = get_option(ASTORE_TEXTDOMAIN);
	if( isset($astore_options['copyright']) )
		return $astore_options['copyright'];
	}

//button_text_call_to_action

function astore_header_site_title(){
	return get_bloginfo( 'name' );
	}

function astore_header_site_descriptione(){
	return get_bloginfo( 'description' );
	}

/**
 * Get WooCommerce products categories.
 *
 */
function astore_get_woo_categories() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return array();
	}
	$astore_categories_array = array();
	$astore_prod_categories = get_categories(
		array(
			'taxonomy' => 'product_cat',
			'hide_empty' => 1,
			'title_li' => '',
		)
	);
	
	if ( ! empty( $astore_prod_categories ) ) {
		foreach ( $astore_prod_categories as $astore_prod_cat ) {
			if ( ! empty( $astore_prod_cat->term_id ) && ! empty( $astore_prod_cat->name ) ) {
				$astore_categories_array[ $astore_prod_cat->term_id ] = $astore_prod_cat->name;
			}
		}
	}

	return $astore_categories_array;
}

/*
 * Notifications in customize
 */
require get_template_directory() . '/inc/customizer-notify/class-astore-customizer-notify.php';

$config_customizer = array(
	'recommended_plugins'       => array(
		
		'woocommerce' => array(
			'recommended' => true,
			'description' => sprintf( esc_html__( 'Install and activate %s plugin to build your front page.', 'astore' ), 'WooCommerce' ),
		),
		
		'elementor' => array(
			'recommended' => true,
			'description' => sprintf( esc_html__( 'Install and activate %s plugin to build your front page.', 'astore' ), 'Elementor Page Builder' ),
		),
		'vela-companion' => array(
			'recommended' => true,
			'description' => sprintf( esc_html__( 'Install and activate %s plugin to take full advantage of AStore theme. More options could be found at Customize and page meta options.', 'astore' ), sprintf( '<strong>%s</strong>', 'Vela Companion' ) ),
		),
		
	),
	'recommended_actions'       => array(),
	'recommended_actions_title' => esc_html__( 'Recommended Actions', 'astore' ),
	'recommended_plugins_title' => esc_html__( 'Recommended Plugins', 'astore' ),
	'install_button_label'      => esc_html__( 'Install and Activate', 'astore' ),
	'activate_button_label'     => esc_html__( 'Activate', 'astore' ),
	'deactivate_button_label'   => esc_html__( 'Deactivate', 'astore' ),
);
//AStore_Customizer_Notify::init( apply_filters( 'astore_customizer_notify_array', $config_customizer ) );

/**
 * Welcome notice.
 *
 */
function astore_welcome_notice() {
	global $pagenow;
	if(function_exists('is_plugin_active') && is_plugin_active('vela-companion/vela-companion.php')){
		return '';
		}
	$theme = wp_get_theme();
	if ( is_child_theme() ) {
		$theme_name = $theme->parent()->get( 'Name' );
	} else {
		$theme_name = $theme->get( 'Name' );
	}
	$theme_version = $theme->get( 'Version' );
	$theme_slug    = $theme->get_template();
			
	$astore_welcome_notice = get_option('astore_welcome_notice');
	if($astore_welcome_notice == '1')
		return '';
	if( $pagenow == "themes.php" && isset($_GET['page']) && $_GET['page'] == "astore-welcome" ):
		return '';
	endif;
    ?>
    <div class="updated notice is-dismissible astore-welcome-notice">   
    <p> 
   <?php
	printf(__('Welcome! Thanks for choosing AStore! To gain full demo contents of the theme you need to go to Customize to install & activate %1$s plugin.', 'astore' ),'<a href="https://wordpress.org/plugins/vela-companion/" target="_blank">Vela Companion</a>');
	?></p>
    <p> 
    <?php
	echo '<a href="' . esc_url( admin_url( 'customize.php') ) . '" class="button button-primary" style="text-decoration: none;">' .  esc_attr__('Go to Customize', 'astore') . '</a> ';
	?>

</p>
    </div>
    <?php
}
//add_action( 'admin_notices', 'astore_welcome_notice' );

/**
 * Dismiss welcome notice.
 *
 */
 
function astore_dismiss_notice(){
	update_option('astore_welcome_notice',1);
}
add_action('wp_ajax_astore_dismiss_notice', 'astore_dismiss_notice');
add_action('wp_ajax_nopriv_astore_dismiss_notice', 'astore_dismiss_notice');

/**
 * Admin page
 *
 */
add_action('admin_menu', 'astore_about_theme');

function astore_about_theme() {
    add_theme_page(
        esc_attr__('About AStore', 'astore' ),
        esc_attr__('About AStore', 'astore' ),
        'manage_options',
        'astore-welcome',
        'astore_about_theme_callback' );
}
 
function astore_about_theme_callback() {
	
	$theme_info = wp_get_theme();
	
    echo '<div class="wrap"><div id="icon-tools" class="icon32"></div>';
        echo '<h2>'.esc_attr__('About AStore', 'astore' ).'</h2>';
	
	echo '<div class="astore-info-wrap">
	<h1>'.sprintf(esc_attr__('Welcome to %1$s Version %2$s', 'astore'),$theme_info->get( 'Name' ), $theme_info->get( 'Version' ) ).'</h1>
	<p>'.sprintf(esc_attr__('%1$s is the best choice for building online store since it\'s fully compatible with WooCommerce, the most popular ecommerce plugin. Using Elementor page builder plugin, you could simply edit your site using just drag & drop.', 'astore'),$theme_info->get( 'Name' ) ).'</p>
	<p>'.esc_attr__('Documentation', 'astore' ).': <a href="https://velathemes.com/astore-documentation/" target="_brank">https://velathemes.com/astore-documentation/</a></p>
	</div>';
		
    echo '</div>';

}

/**
 * Add script to the footer
 *
 */

function astore_footer_script(){ 
	$display_scroll_to_top = astore_option('display_scroll_to_top');
	if($display_scroll_to_top=='1' || is_customize_preview() ){
		$css_class = 'back-to-top';
		if( $display_scroll_to_top !=1 && is_customize_preview() )
			$css_class  .= ' hide';
			
		echo '<div class="'.$css_class.'"></div>';
		}

 } 
add_action('wp_footer','astore_footer_script');

/**
 * Convert Hex Code to RGB
 * @param  string $hex Color Hex Code
 * @return array       RGB values
 */
 
function astore_hex2rgb( $hex ) {
	if ( strpos( $hex,'rgb' ) !== FALSE ) {

		$rgb_part = strstr( $hex, '(' );
		$rgb_part = trim($rgb_part, '(' );
		$rgb_part = rtrim($rgb_part, ')' );
		$rgb_part = explode( ',', $rgb_part );

		$rgb = array($rgb_part[0], $rgb_part[1], $rgb_part[2], $rgb_part[3]);

	} elseif( $hex == 'transparent' ) {
		$rgb = array( '255', '255', '255', '0' );
	} else {

		$hex = str_replace( '#', '', $hex );
		
		
		if( strlen( $hex ) == 3 ) {
			$r = hexdec( substr( $hex, 0, 1 ) . substr( $hex, 0, 1 ) );
			$g = hexdec( substr( $hex, 1, 1 ) . substr( $hex, 1, 1 ) );
			$b = hexdec( substr( $hex, 2, 1 ) . substr( $hex, 2, 1 ) );
		} else {
			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );
		}
		$rgb = array( $r, $g, $b );
	}

	return $rgb;
}


/**
 * Mini cart
 */

add_filter('astore_shopping_cart','astore_add_cart_single_ajax', 10, 2);

function astore_add_cart_single_ajax() {
	
	$html = '';
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
		ob_start();
		the_widget( 'WC_Widget_Cart' );
		$html = ob_get_clean();
	}
	return $html;
}

/**
 * Hide header & footer
 */
function astore_hide_header(){
	if(isset($_GET['hide-header'])){
		return 1;
		}
	}
function astore_hide_footer(){
	if(isset($_GET['hide-footer'])){
		return 1;
		}
	}

add_filter('astore_hide_header','astore_hide_header');
add_filter('astore_hide_footer','astore_hide_header');

/**
 * Filter woocommerce_subcategory_count_html
 */

function astore_subcategory_count_html( $string ){
	
	$num = str_replace('<mark class="count">(','',$string);
	$num = str_replace(')</mark>','',$num);
	if($num == 1){
		$string = '<mark class="count">'.$num.' '.__( 'Product', 'astore' ).'</mark>';
		}else{
		$string = '<mark class="count">'.$num.' '.__( 'Products', 'astore' ).'</mark>';
	}
	return $string;
	}
add_filter('woocommerce_subcategory_count_html','astore_subcategory_count_html');


/**
 * Include the TGM_Plugin_Activation class.
 */
if ( !class_exists( 'TGM_Plugin_Activation' ) ) 
	load_template( trailingslashit( get_template_directory() ) . 'inc/class-tgm-plugin-activation.php' );

add_action( 'tgmpa_register', 'astore_theme_register_required_plugins' );

/**
 * Register the required plugins for this theme.
 *
 */
function astore_theme_register_required_plugins() {
	
	$options     = get_option('astore_companion_options',true);
	$license_key = isset($options['license_key'])?$options['license_key']:'';

    $plugins = array(
		
		array(
			'name'     				=> __('Vela Companion','astore'), // The plugin name
			'slug'     				=> 'vela-companion', // The plugin slug (typically the folder name)
			'source'   				=> '', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '1.0.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> '', // If set, overrides default API URL and points to an external URL
		),
		
		array(
			'name'     				=> __('Elementor','astore'), // The plugin name
			'slug'     				=> 'elementor', // The plugin slug (typically the folder name)
			'source'   				=> '', // The plugin source
			'required' 				=> false, // If false, the plugin is only 'recommended' instead of required
			'version' 				=> '1.0.0', // E.g. 1.0.0. If set, the active plugin must be this version or higher, otherwise a notice is presented
			'force_activation' 		=> false, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
			'force_deactivation' 	=> false, // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins
			'external_url' 			=> '', // If set, overrides default API URL and points to an external URL
		),
	);

    /**
     * Array of configuration settings. Amend each line as needed.
     */
    $config = array(
        'id'           => 'vela-companion',                 // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',                      // Default absolute path to pre-packaged plugins.
        'menu'         => 'tgmpa-install-plugins', // Menu slug.
        'has_notices'  => true,                    // Show admin notices or not.
        'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
        'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => false,                   // Automatically activate plugins after installation or not.
        'message'      => '',                      // Message to output right before the plugins table.
    );

    tgmpa( $plugins, $config );

}
/**
 * Fontawsome icons
 */
function astore_fontawsome_icons(){
	
	$icons = array( "glass","music","search","envelope-o","heart","star","star-o","user","film","th-large","th","th-list","check","remove","close","times","search-plus","search-minus","power-off","signal","gear","cog","trash-o","home","file-o","clock-o","road","download","arrow-circle-o-down","arrow-circle-o-up","inbox","play-circle-o","rotate-right","repeat","refresh","list-alt","lock","flag","headphones","volume-off","volume-down","volume-up","qrcode","barcode","tag","tags","book","bookmark","print","camera","font","bold","italic","text-height","text-width","align-left","align-center","align-right","align-justify","list","dedent","outdent","indent","video-camera","photo","image","picture-o","pencil","map-marker","adjust","tint","edit","pencil-square-o","share-square-o","check-square-o","arrows","step-backward","fast-backward","backward","play","pause","stop","forward","fast-forward","step-forward","eject","chevron-left","chevron-right","plus-circle","minus-circle","times-circle","check-circle","question-circle","info-circle","crosshairs","times-circle-o","check-circle-o","ban","arrow-left","arrow-right","arrow-up","arrow-down","mail-forward","share","expand","compress","plus","minus","asterisk","exclamation-circle","gift","leaf","fire","eye","eye-slash","warning","exclamation-triangle","plane","calendar","random","comment","magnet","chevron-up","chevron-down","retweet","shopping-cart","folder","folder-open","arrows-v","arrows-h","bar-chart-o","bar-chart","twitter-square","facebook-square","camera-retro","key","gears","cogs","comments","thumbs-o-up","thumbs-o-down","star-half","heart-o","sign-out","linkedin-square","thumb-tack","external-link","sign-in","trophy","github-square","upload","lemon-o","phone","square-o","bookmark-o","phone-square","twitter","facebook-f","facebook","github","unlock","credit-card","feed","rss","hdd-o","bullhorn","bell","certificate","hand-o-right","hand-o-left","hand-o-up","hand-o-down","arrow-circle-left","arrow-circle-right","arrow-circle-up","arrow-circle-down","globe","wrench","tasks","filter","briefcase","arrows-alt","group","users","chain","link","cloud","flask","cut","scissors","copy","files-o","paperclip","save","floppy-o","square","navicon","reorder","bars","list-ul","list-ol","strikethrough","underline","table","magic","truck","pinterest","pinterest-square","google-plus-square","google-plus","money","caret-down","caret-up","caret-left","caret-right","columns","unsorted","sort","sort-down","sort-desc","sort-up","sort-asc","envelope","linkedin","rotate-left","undo","legal","gavel","dashboard","tachometer","comment-o","comments-o","flash","bolt","sitemap","umbrella","paste","clipboard","lightbulb-o","exchange","cloud-download","cloud-upload","user-md","stethoscope","suitcase","bell-o","coffee","cutlery","file-text-o","building-o","hospital-o","ambulance","medkit","fighter-jet","beer","h-square","plus-square","angle-double-left","angle-double-right","angle-double-up","angle-double-down","angle-left","angle-right","angle-up","angle-down","desktop","laptop","tablet","mobile-phone","mobile","circle-o","quote-left","quote-right","spinner","circle","mail-reply","reply","github-alt","folder-o","folder-open-o","smile-o","frown-o","meh-o","gamepad","keyboard-o","flag-o","flag-checkered","terminal","code","mail-reply-all","reply-all","star-half-empty","star-half-full","star-half-o","location-arrow","crop","code-fork","unlink","chain-broken","question","info","exclamation","superscript","subscript","eraser","puzzle-piece","microphone","microphone-slash","shield","calendar-o","fire-extinguisher","rocket","maxcdn","chevron-circle-left","chevron-circle-right","chevron-circle-up","chevron-circle-down","html5","css3","anchor","unlock-alt","bullseye","ellipsis-h","ellipsis-v","rss-square","play-circle","ticket","minus-square","minus-square-o","level-up","level-down","check-square","pencil-square","external-link-square","share-square","compass","toggle-down","caret-square-o-down","toggle-up","caret-square-o-up","toggle-right","caret-square-o-right","euro","eur","gbp","dollar","usd","rupee","inr","cny","rmb","yen","jpy","ruble","rouble","rub","won","krw","bitcoin","btc","file","file-text","sort-alpha-asc","sort-alpha-desc","sort-amount-asc","sort-amount-desc","sort-numeric-asc","sort-numeric-desc","thumbs-up","thumbs-down","youtube-square","youtube","xing","xing-square","youtube-play","dropbox","stack-overflow","instagram","flickr","adn","bitbucket","bitbucket-square","tumblr","tumblr-square","long-arrow-down","long-arrow-up","long-arrow-left","long-arrow-right","apple","windows","android","linux","dribbble","skype","foursquare","trello","female","male","gittip","gratipay","sun-o","moon-o","archive","bug","vk","weibo","renren","pagelines","stack-exchange","arrow-circle-o-right","arrow-circle-o-left","toggle-left","caret-square-o-left","dot-circle-o","wheelchair","vimeo-square","turkish-lira","try","plus-square-o","space-shuttle","slack","envelope-square","wordpress","openid","institution","bank","university","mortar-board","graduation-cap","yahoo","google","reddit","reddit-square","stumbleupon-circle","stumbleupon","delicious","digg","pied-piper-pp","pied-piper-alt","drupal","joomla","language","fax","building","child","paw","spoon","cube","cubes","behance","behance-square","steam","steam-square","recycle","automobile","car","cab","taxi","tree","spotify","deviantart","soundcloud","database","file-pdf-o","file-word-o","file-excel-o","file-powerpoint-o","file-photo-o","file-picture-o","file-image-o","file-zip-o","file-archive-o","file-sound-o","file-audio-o","file-movie-o","file-video-o","file-code-o","vine","codepen","jsfiddle","life-bouy","life-buoy","life-saver","support","life-ring","circle-o-notch","ra","resistance","rebel","ge","empire","git-square","git","y-combinator-square","yc-square","hacker-news","tencent-weibo","qq","wechat","weixin","send","paper-plane","send-o","paper-plane-o","history","circle-thin","header","paragraph","sliders","share-alt","share-alt-square","bomb","soccer-ball-o","futbol-o","tty","binoculars","plug","slideshare","twitch","yelp","newspaper-o","wifi","calculator","paypal","google-wallet","cc-visa","cc-mastercard","cc-discover","cc-amex","cc-paypal","cc-stripe","bell-slash","bell-slash-o","trash","copyright","at","eyedropper","paint-brush","birthday-cake","area-chart","pie-chart","line-chart","lastfm","lastfm-square","toggle-off","toggle-on","bicycle","bus","ioxhost","angellist","cc","shekel","sheqel","ils","meanpath","buysellads","connectdevelop","dashcube","forumbee","leanpub","sellsy","shirtsinbulk","simplybuilt","skyatlas","cart-plus","cart-arrow-down","diamond","ship","user-secret","motorcycle","street-view","heartbeat","venus","mars","mercury","intersex","transgender","transgender-alt","venus-double","mars-double","venus-mars","mars-stroke","mars-stroke-v","mars-stroke-h","neuter","genderless","facebook-official","pinterest-p","whatsapp","server","user-plus","user-times","hotel","bed","viacoin","train","subway","medium","yc","y-combinator","optin-monster","opencart","expeditedssl","battery-4","battery","battery-full","battery-3","battery-three-quarters","battery-2","battery-half","battery-1","battery-quarter","battery-0","battery-empty","mouse-pointer","i-cursor","object-group","object-ungroup","sticky-note","sticky-note-o","cc-jcb","cc-diners-club","clone","balance-scale","hourglass-o","hourglass-1","hourglass-start","hourglass-2","hourglass-half","hourglass-3","hourglass-end","hourglass","hand-grab-o","hand-rock-o","hand-stop-o","hand-paper-o","hand-scissors-o","hand-lizard-o","hand-spock-o","hand-pointer-o","hand-peace-o","trademark","registered","creative-commons","gg","gg-circle","tripadvisor","odnoklassniki","odnoklassniki-square","get-pocket","wikipedia-w","safari","chrome","firefox","opera","internet-explorer","tv","television","contao","amazon","calendar-plus-o","calendar-minus-o","calendar-times-o","calendar-check-o","industry","map-pin","map-signs","map-o","map","commenting","commenting-o","houzz","vimeo","black-tie","fonticons","reddit-alien","edge","credit-card-alt","codiepie","modx","fort-awesome","usb","product-hunt","mixcloud","scribd","pause-circle","pause-circle-o","stop-circle","stop-circle-o","shopping-bag","shopping-basket","hashtag","bluetooth","bluetooth-b","percent","gitlab","wpbeginner","wpforms","envira","universal-access","wheelchair-alt","question-circle-o","blind","audio-description","volume-control-phone","braille","assistive-listening-systems","asl-interpreting","american-sign-language-interpreting","deafness","hard-of-hearing","deaf","glide","glide-g","signing","sign-language","low-vision","viadeo","viadeo-square","snapchat","snapchat-ghost","snapchat-square","pied-piper","first-order","yoast","themeisle","google-plus-circle","google-plus-official","handshake-o","envelope-open","envelope-open-o","linode","address-book","address-book-o","vcard","address-card","vcard-o","address-card-o","user-circle","user-circle-o","user-o","id-badge","drivers-license","id-card","drivers-license-o","id-card-o","quora","free-code-camp","telegram","thermometer-4","thermometer","thermometer-full","thermometer-3","thermometer-three-quarters","thermometer-2","thermometer-half","thermometer-1","thermometer-quarter","thermometer-0","thermometer-empty","shower","bathtub","s15","bath","podcast","window-maximize","window-minimize","window-restore","times-rectangle","window-close","times-rectangle-o","window-close-o","bandcamp","grav","etsy","imdb","ravelry","eercast","microchip","snowflake-o","superpowers","wpexplorer","meetup");
	
	return $icons;
	
	}