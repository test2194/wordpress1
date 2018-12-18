<?php
/*
	Plugin Name: Vela Companion
	Description: Vela theme options.
	Author: VelaThemes
	Author URI: https://velathemes.com/
	Version: 1.1.2
	Text Domain: vela-companion
	Domain Path: /languages
	License: GPL v2 or later
*/

defined('ABSPATH') or die("No script kiddies please!");

define( 'VELA_COMPANION_DIR',  plugin_dir_path( __FILE__ ) );
define( 'VELA_COMPANION_VER',  '1.1.2' );

require_once 'inc/widget-recent-posts.php';
require_once 'inc/pageMetabox/options.php';
require_once 'inc/Vela_Taxonomy_Images.php';
require_once 'inc/templates-importer/templates-importer.php';

require_once 'inc/templates-importer/class-sites-helper.php';
require_once 'inc/templates-importer/class-customizer-import.php';
require_once 'inc/templates-importer/wxr-importer/class-vela-wxr-importer.php';
require_once 'inc/templates-importer/class-site-options-import.php';
require_once 'inc/templates-importer/class-widgets-importer.php';
require_once 'inc/templates-importer/sites-importer.php';

require_once 'inc/elementor-widgets/elementor-widgets.php';

if (!class_exists('VelaCompanion')){

	class VelaCompanion{	
		public $slider = array();
		public function __construct($atts = NULL)
		{
			
			$theme = wp_get_theme();
			$prefix = 'vela_';
			
			$option_name = $theme->get( 'Template' );
			if( $option_name == '' )
				$option_name = $theme->get( 'TextDomain' );
			
			define( 'VELA_THEME_OPTION_NAME', sanitize_title($option_name) );
						
			if( VELA_THEME_OPTION_NAME == 'cactus' || strstr(VELA_THEME_OPTION_NAME,'cactus') ){
				$prefix = 'cactus_';
			}
			if( VELA_THEME_OPTION_NAME == 'astore' || strstr(VELA_THEME_OPTION_NAME,'astore') ){
				$prefix = 'astore_';
			
			}

			register_activation_hook( __FILE__, array(&$this ,'plugin_activate') );
			add_action( 'plugins_loaded', array(&$this, 'init' ) );
			add_action( 'admin_menu', array(&$this ,'plugin_menu') );
			add_action( 'switch_theme', array(&$this ,'plugin_activate') );
			add_action( 'wp_enqueue_scripts',  array(&$this , 'enqueue_scripts' ));
			add_action( 'admin_enqueue_scripts',  array(&$this , 'enqueue_admin_scripts' ));
			add_action( 'wp_footer', array( $this, 'gridlist_set_default_view' ) );
			
			add_action( $prefix.'before_page_wrap', array(&$this ,'page_slider') );
			add_filter( $prefix.'page_title_bar', array(&$this ,'page_title_bar'), 20, 2 );
			
			add_filter( $prefix.'page_sidebar_layout', array(&$this ,'page_sidebar_layout'), 20,1 );
			add_action( $prefix.'before_sidebar', array( $this, 'before_sidebar' ) );
			add_action( $prefix.'after_sidebar', array( $this, 'after_sidebar' ) );
			add_shortcode( 'cactus_map', array(&$this ,'map_shortcode') );
			
			add_action( 'edit_category', array( $this, 'updated_category_fields' ), 10, 2 );
			add_action( 'create_category', array( $this, 'save_category_fields' ), 10, 2 );
			add_action( 'category_edit_form_fields', array( $this, 'category_custom_fields' ) );
			add_action('category_add_form_fields', array( $this, 'category_custom_fields' ) );
			
			add_action( 'cactus-contact-form',  array(&$this , 'contact_form' ));
			add_action( 'wp_ajax_cactus_contact', array(&$this ,'send_email'));
			add_action( 'wp_ajax_nopriv_cactus_contact', array(&$this ,'send_email'));
			
			add_filter( 'vela_custom_menu', array( $this, 'custom_menu' ) );
			
			//add_action( 'customize_controls_init', array( &$this,'customize_controls_enqueue') );

		}
		
	
	/**
	 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
	 */
	function customize_controls_enqueue(){
		//wp_enqueue_script( 'vela_companion_customizer_controls',  plugins_url('/assets/js/customizer.js', __FILE__), '', '1.0.0', true );
			
	}
	
	/**
	 * Custom menu
	 *
	 */
	public function custom_menu( $menu ) {
		
		global $post;
		if( !isset( $post->ID ) )
			return '';
			
		if ( $meta = get_post_meta( $post->ID, '_vela_custom_menu', true ) ) {
			$menu = $meta;
		}
		
			return $menu;


	}

	function plugin_activate( $network_wide ) {
			
			 if ( is_plugin_active('cactus-companion/cactus-companion.php') ) {
    			deactivate_plugins('cactus-companion/cactus-companion.php');    
    		}
			
			 if ( is_plugin_active('astore-companion/astore-companion.php') ) {
    			deactivate_plugins('astore-companion/astore-companion.php');    
    		}
			
			if ( is_plugin_active('capeone-companion/capeone-companion.php') ) {
    			deactivate_plugins('capeone-companion/capeone-companion.php');    
    		}
			
	}
		
	public static function init() {
		
		load_plugin_textdomain( 'vela-companion', false,  basename( dirname( __FILE__ ) ) . '/languages' );
	}
	
	
	/**
	 * Contact form
	*/
	function contact_form(){
		?>
		<form action="" class="cactus-contact-form">
		
		<ul class="cactus-list-md-3">
		  <li>
			<div class="form-group">
			  <label for="name" class="control-label sr-only"> <?php _e( 'Name', 'vela-companion' );?></label>
			  <input type="text" class="form-control" id="name" placeholder="<?php echo apply_filters('cc_label_name',__( 'Name', 'vela-companion' ));?> *">
			</div>
		  </li>
		  <li>
			<div class="form-group">
			  <label for="email" class="control-label sr-only"><?php _e( 'Email', 'vela-companion' );?></label>
			  <input type="email" class="form-control" id="email" placeholder="<?php echo apply_filters('cc_label_email',__( 'Email', 'vela-companion' ));?> *">
			</div>
		  </li>
		  <li>
			<div class="form-group">
			  <label for="subject" class="control-label sr-only"><?php _e( 'Subject', 'vela-companion' );?></label>
			  <input type="text" class="form-control" id="subject" placeholder="<?php echo apply_filters('cc_label_subject',__( 'Subject', 'vela-companion' ));?> *">
			</div>
		  </li>
		</ul>
		<div class="form-group">
		  <label class="control-label sr-only" for="message"><?php echo apply_filters('cc_label_message',__( 'Message', 'vela-companion' ));?></label>
		  <textarea name="message" id="message" required="required" aria-required="true" rows="4" placeholder="<?php echo apply_filters('cc_label_message',__( 'Message', 'vela-companion' ));?> *" class="form-control"></textarea>
		</div>
		<div class="form-group">
		  <label class="control-label sr-only" for="submit"><?php echo apply_filters('cc_label_submit',__( 'Submit', 'vela-companion' ));?></label>
		  <input type="submit" value="<?php echo apply_filters('cc_button_text',__( 'SEND YOUR MESSAGE', 'vela-companion' ));?>" id="submit">&nbsp;&nbsp;&nbsp;&nbsp;<span class="noticefailed"></span>
		</div>
		
	   
	  </form>

		<?php
		
		}
		
	/*	
	*	Send email
	*
	*/
	
	function send_email(){
		if(trim($_POST['name']) === '') {
			$Error = __('Please enter your name.','vela-companion');
			$hasError = true;
		} else {
			$name = trim($_POST['name']);
		}
	
		if(trim($_POST['email']) === '')  {
			$Error = __('Please enter your email address.','vela-companion');
			$hasError = true;
		} else if (!preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($_POST['email']))) {
			$Error = __('You entered an invalid email address.','onetone');
			$hasError = true;
		} else {
			$email = trim($_POST['email']);
		}
		
		if(trim($_POST['subject']) === '') {
			$Error = __('Please enter subject.','vela-companion');
			$hasError = true;
		} else {
			$subject = trim($_POST['subject']);
		}
	
		if(trim($_POST['message']) === '') {
			$Error =  __('Please enter a message.','vela-companion');
			$hasError = true;
		} else {
			if(function_exists('stripslashes')) {
				$message = stripslashes(trim($_POST['message']));
			} else {
				$message = trim($_POST['message']);
			}
		}
	
		if(!isset($hasError)) {
			
			$options = get_option('cactus_companion_options');
			
		   if (isset($options['cactus_contact_form_email']) && preg_match("/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i", trim($options['cactus_contact_form_email']))) {
			 $emailTo = $options['cactus_contact_form_email']; ;
		   }
		   
		   $emailTo = apply_filters( 'vela_contact_form_email', $emailTo );
		   
		   if( $emailTo == '' ){
			 $emailTo = get_option('admin_email');
			}	
			
		   if($emailTo !=""){
			   
				$body = "Name: $name \n\nEmail: $email \n\nMessage: $message";
				$headers = 'From: '.$name.' <'.$emailTo.'>' . "\r\n" . 'Reply-To: ' . $email;
	
				wp_mail($emailTo, $subject, $body, $headers);
				$emailSent = true;
			}
			
			echo json_encode(array("msg"=>__("Your message has been successfully sent!","vela-companion"),"error"=>0));
			
		}
		else
		{
			echo json_encode(array("msg"=>$Error,"error"=>1));
		}
		die() ;
	}
	
	/**
	 * Displays the map
	*/
	function map_shortcode( $atts ) {
	
		$atts = shortcode_atts(
			array(
				'address'           => false,
				'width'             => '100%',
				'height'            => '650px',
				'enablescrollwheel' => 'true',
				'zoom'              => 15,
				'disablecontrols'   => 'false',
				'key'               => '',
				'button_text'       => ''
			),
			$atts
		);
	
		$address = $atts['address'];
	
		wp_enqueue_script( 'google-maps-api', '//maps.google.com/maps/api/js?key=' . sanitize_text_field( $atts['key'] ) );
	
		if( $address  ) :
	
			wp_print_scripts( 'google-maps-api' );
	
			$coordinates = VelaCompanion::map_get_coordinates( $address, false, sanitize_text_field( $atts['key'] ) );
	
			if( ! is_array( $coordinates ) ) {
				echo $coordinates;		
				return;
			}
	
			$map_id = uniqid( 'vela_map_' ); // generate a unique ID for this map
	
			ob_start(); ?>
			<div class="cactus_map_canvas" id="<?php echo esc_attr( $map_id ); ?>" style="height: <?php echo esc_attr( $atts['height'] ); ?>; width: <?php echo esc_attr( $atts['width'] ); ?>"></div>
			<script type="text/javascript">
				var map_<?php echo $map_id; ?>;
				function cactus_run_map_<?php echo $map_id ; ?>(){
					var location = new google.maps.LatLng("<?php echo $coordinates['lat']; ?>", "<?php echo $coordinates['lng']; ?>");
					var map_options = {
						zoom: <?php echo $atts['zoom']; ?>,
						center: location,
						scrollwheel: <?php echo 'true' === strtolower( $atts['enablescrollwheel'] ) ? '1' : '0'; ?>,
						disableDefaultUI: <?php echo 'true' === strtolower( $atts['disablecontrols'] ) ? '1' : '0'; ?>,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					}
					map_<?php echo $map_id ; ?> = new google.maps.Map(document.getElementById("<?php echo $map_id ; ?>"), map_options);
					var marker = new google.maps.Marker({
					position: location,
					map: map_<?php echo $map_id ; ?>
					});
				}
				cactus_run_map_<?php echo $map_id ; ?>();
			</script>
			<?php
			return ob_get_clean();
		else :
			return __( 'This Google Map cannot be loaded because the maps API does not appear to be loaded', 'vela-companion' );
		endif;
	}
	
		/**
	 * Retrieve coordinates for an address
	 *
	*/
	public static function map_get_coordinates( $address, $force_refresh = false,$api_key='' ) {
	
		$address_hash = md5( $address );
	
		$coordinates = get_transient( $address_hash );
	
		if ( $force_refresh || $coordinates === false ) {
	
			$args       = apply_filters( 'vela_map_query_args', array( 'key' => $api_key, 'address' => urlencode( $address ), 'sensor' => 'false' ) );
			$url        = add_query_arg( $args, 'https://maps.googleapis.com/maps/api/geocode/json' );
			$response 	= wp_remote_get( $url );
	
			if( is_wp_error( $response ) ) {
				return;
			}
	
			$data = wp_remote_retrieve_body( $response );
	
			if( is_wp_error( $data ) ) {
				return;
			}
	
			if ( $response['response']['code'] == 200 ) {
	
				$data = json_decode( $data );
	
				if ( $data->status === 'OK' ) {
	
					$coordinates = $data->results[0]->geometry->location;
	
					$cache_value['lat'] 	= $coordinates->lat;
					$cache_value['lng'] 	= $coordinates->lng;
					$cache_value['address'] = (string) $data->results[0]->formatted_address;
	
					// cache coordinates for 3 months
					set_transient($address_hash, $cache_value, 3600*24*30*3);
					$data = $cache_value;
	
				} elseif ( $data->status === 'ZERO_RESULTS' ) {
					return __( 'No location found for the entered address.', 'vela-companion' );
				} elseif( $data->status === 'INVALID_REQUEST' ) {
					return __( 'Invalid request. Did you enter an address?', 'vela-companion' );
				} elseif( $data->status === 'REQUEST_DENIED' ) {
					return $data->error_message;
				}else {
					return __( 'Something went wrong while retrieving your map, please ensure you have entered the short code correctly.', 'vela-companion' );
				}
	
			} else {
				return __( 'Unable to contact Google API service.', 'vela-companion' );
			}
	
		} else {
		   // return cached results
		   $data = $coordinates;
		}
	
		return $data;
	}
	
	/**
    * Save the form field
    */
   public function save_category_fields( $term_id, $tt_id ) {
     if( isset( $_POST['cactus_category_meta'] ) && '' !== $_POST['cactus_category_meta'] ){
       add_term_meta( $term_id, 'cactus_category_meta', $_POST['cactus_category_meta'], true );
     }
   }
   
    /**
    * Update the form field value
    */
  public function updated_category_fields( $term_id, $tt_id='' ) {
     if( isset( $_POST['cactus_category_meta'] ) && '' !== $_POST['cactus_category_meta'] ){
       update_term_meta( $term_id, 'cactus_category_meta', $_POST['cactus_category_meta'] );
     } else {
       update_term_meta( $term_id, 'cactus_category_meta', '' );
     }
   }
   
	/**
    * Category options
    */

  function category_custom_fields( $tag ) {
		
			//$category_meta = get_option( 'cactus_category_meta' );
			$term_id = isset($tag->term_id)?$tag->term_id:'';
			$category_meta = get_term_meta($term_id);
			$category_meta = isset($category_meta['cactus_category_meta'])?unserialize($category_meta['cactus_category_meta'][0]):null;

			?>
            
			<table class="form-table cmb_metabox">
  <tbody>
    <tr class="cmb-type-checkbox cmb_id__ccmb_hide_page_title_bar">
      <th class="row"><label for="_ccmb_hide_page_title_bar"><?php _e("Hide Page Title Bar", 'vela-companion'); ?></label></th>
      <td><input type="checkbox" class="cmb_option cmb_list" name="cactus_category_meta[<?php echo $term_id ?>][_ccmb_hide_page_title_bar]" id="_ccmb_hide_page_title_bar" value="1" <?php if ( isset( $category_meta[ $term_id ]['_ccmb_hide_page_title_bar'] ) ) checked( $category_meta[ $term_id ]['_ccmb_hide_page_title_bar'], '1', true ); ?>>
        <label for="_ccmb_hide_page_title_bar"> <span class="cmb_metabox_description"></span> </label></td>
    </tr>
    <tr class="cmb-type-colorpicker cmb_id__ccmb_bg_color">
      <th class="row"><label for="_ccmb_bg_color"><?php _e("Background Color", 'vela-companion'); ?></label></th>
      <td>
            <input type="text" class="cmb_colorpicker cmb_text_small wp-color-picker" name="cactus_category_meta[<?php echo $tag->term_id ?>][_ccmb_bg_color]" id="_ccmb_bg_color" value="<?php if ( isset( $category_meta[ $term_id ]['_ccmb_bg_color'] ) ) esc_attr_e( $category_meta[ $term_id ]['_ccmb_bg_color'] ); ?>">
          
        <p class="cmb_metabox_description"></p></td>
    </tr>
    
    <tr class="form-field hide-if-no-js">
               <th class="row" scope="row" valign="top"><label for="taxonomy-image"><?php _e("Background Image", 'vela-companion'); ?></label></th>
               <td>
                  <div class="form-field term-group">
                   
       <input type="hidden" id="cactus-taxonomy-image-id" name="cactus_category_meta[<?php echo $term_id ?>][bg_img]" class="custom_media_url" value="<?php if ( isset( $category_meta[ $term_id ]['bg_img'] ) ) esc_attr_e( $category_meta[ $term_id ]['bg_img'] ); ?>">
       <div id="category-image-wrapper">
            <?php if ( isset( $category_meta[ $term_id ]['bg_img'] ) ) { ?>
              <?php echo wp_get_attachment_image( $category_meta[ $term_id ]['bg_img'], 'thumbnail' ); ?>
            <?php } ?>
          </div>
       <p>
         <input type="button" class="button button-secondary showcase_tax_media_button" id="showcase_tax_media_button" name="showcase_tax_media_button" value="<?php _e( 'Add Image', 'vela-companion' ); ?>" />
         <input type="button" class="button button-secondary showcase_tax_media_remove" id="showcase_tax_media_remove" name="showcase_tax_media_remove" value="<?php _e( 'Remove Image', 'vela-companion' ); ?>" />
       </p>
     </div>
     </td>
           </tr>
           
    <tr class="cmb-type-radio cmb_id__ccmb_sidebar">
      <th class="row"><label for="_ccmb_sidebar"><?php _e("Sidebar", 'vela-companion'); ?></label></th>
      <td><ul class="cmb_radio_list cmb_list">
          <li>
            <input type="radio" class="cmb_option" name="cactus_category_meta[<?php echo $term_id ?>][_ccmb_sidebar]" id="_ccmb_sidebar1" value="" <?php if ( isset( $category_meta[ $term_id ]['_ccmb_sidebar'] ) ) checked( $category_meta[ $term_id ]['_ccmb_sidebar'], '', true ); else echo 'checked="checked"'; ?>>
            <label for="_ccmb_sidebar1"><?php _e("Default", 'vela-companion'); ?></label>
          </li>
          <li>
            <input type="radio" class="cmb_option" name="cactus_category_meta[<?php echo $term_id ?>][_ccmb_sidebar]" id="_ccmb_sidebar2" value="left" <?php if ( isset( $category_meta[ $term_id ]['_ccmb_sidebar'] ) ) checked( $category_meta[ $term_id ]['_ccmb_sidebar'], 'left', true ); ?>>
            <label for="_ccmb_sidebar2"><?php _e("Left Sidebar", 'vela-companion'); ?></label>
          </li>
          <li>
            <input type="radio" class="cmb_option" name="cactus_category_meta[<?php echo $term_id ?>][_ccmb_sidebar]" id="_ccmb_sidebar3" value="right" <?php if ( isset( $category_meta[ $term_id ]['_ccmb_sidebar'] ) ) checked( $category_meta[ $term_id ]['_ccmb_sidebar'], 'right', true ); ?>>
            <label for="_ccmb_sidebar3"><?php _e("Right Sidebar", 'vela-companion'); ?></label>
          </li>
          <li>
            <input type="radio" class="cmb_option" name="cactus_category_meta[<?php echo $term_id ?>][_ccmb_sidebar]" id="_ccmb_sidebar4" value="no" <?php if ( isset( $category_meta[ $term_id ]['_ccmb_sidebar'] ) ) checked( $category_meta[ $term_id ]['_ccmb_sidebar'], 'no', true ); ?>>
            <label for="_ccmb_sidebar4"><?php _e("No Sidebar", 'vela-companion'); ?></label>
          </li>
        </ul>
        <p class="cmb_metabox_description"></p></td>
    </tr>
  </tbody>
</table>

<?php
		}
		
  function save_category_custom_fields() {
	if ( isset( $_POST['cactus_category_meta'] ) && !update_option('cactus_category_meta', $_POST['cactus_category_meta']) )
		add_option('cactus_category_meta', $_POST['cactus_category_meta']);
}
	/**
	 * Enqueue admin scripts
	*/
	function enqueue_admin_scripts()
	{
		wp_enqueue_style( 'wp-color-picker' );
		
		$theme = VELA_THEME_OPTION_NAME;
		
		if(isset($_GET['page']) && $_GET['page']=='vela-template'){
			wp_enqueue_script( 'plugin-install' );
			wp_enqueue_script( 'updates' );
		}
		
		wp_enqueue_style( 'vela-companion-admin', plugins_url('assets/css/admin.css', __FILE__));
		wp_enqueue_script( 'vela-companion-admin', plugins_url('assets/js/admin.js', __FILE__),array('jquery', 'wp-util', 'updates','wp-color-picker' ),VELA_COMPANION_VER,true);
	
		if(isset($_GET['page']) && $_GET['page']=='vela-sites'){
			wp_enqueue_script( 'vela-site-importer', plugins_url('assets/js/site-importer.js', __FILE__),array('jquery', 'wp-util', 'updates','wp-color-picker' ),'',true);
			wp_localize_script( 'vela-site-importer', 'velaSiteImporter',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'i18n' =>array(
						's0' => __( "Executing Demo Import will make your site similar as preview. Please bear in mind -\n\n1. It is recommended to run import on a fresh WordPress installation.\n\n2. Importing site does not delete any pages or posts. However, it can overwrite your existing content.\n\n", 'vela-companion' ),					
						's1'=> __( 'Importing Customizer...', 'vela-companion' ),
						's2'=> __( 'Import Customizer Failed', 'vela-companion' ),
						's3'=> __( 'Customizer Imported', 'vela-companion' ),
						's4'=> __( 'Preparing WXR Data...', 'vela-companion' ),
						's5'=> __( 'Import WXR Failed', 'vela-companion' ),
						's6'=> __( 'Importing WXR...', 'vela-companion' ),
						's6_1'=> __( 'Importing Media, Pages, Posts...', 'vela-companion' ),
						's7'=> __( 'WXR Successfully imported!', 'vela-companion' ),
						's8'=> __( 'Importing Theme Options...', 'vela-companion' ),
						's9'=> __( 'Importing Options Failed', 'vela-companion' ),
						's10'=> __( 'Theme Options Successfully imported!', 'vela-companion' ),
						's11'=> __( 'Importing Widgets...', 'vela-companion' ),
						's12'=> __( 'Import Widgets Failed', 'vela-companion' ),
						's13'=> __( 'Widgets Successfully imported!', 'vela-companion' ),
						's14'=> __( 'Site import complete!', 'vela-companion' ),
						's14_1'=> sprintf(__( 'Site import complete! <a href="%s" target="_blank">Visit your website</a>', 'vela-companion' ), esc_url( home_url( '/' ) )),
						  ),
				) );
		}
		
		wp_localize_script( 'vela-companion-admin', 'vela_companion_admin',
				array(
					'ajaxurl' => admin_url('admin-ajax.php'),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'i18n' =>array('t1'=> __( 'Install and Import', 'vela-companion' ),'t2'=> __( 'Import', 'vela-companion' ),'t3'=> __( 'Install and Import Site', 'vela-companion' ),'t4'=> __( 'Import Site', 'vela-companion' ) ),
				) );

	if( strstr($theme,'-pro') ){
		$custom_css = '.vela-free, .'.$theme.'-free{ display:none;}';
		wp_add_inline_style( 'vela-companion-admin', wp_filter_nohtml_kses($custom_css) );
	}
	
	}
	
	/**
	 * Get page sidebar
	*/	

	function page_slider(){
	
		$html = '';
			
		$slider = $this->slider;
			
		if(!empty($slider) && is_array($slider)){
			$this->has_slider = true;
			$html .= '<div class="banner_slider cactus-slider owl-carousel">';
			foreach($slider as $slide){
					
					$default = array(
									'image' => '',
									'title' => '',
									'subtitle' => '',
									'btn_text' => '',
									'btn_link' => '',
								);
					$slide = array_merge($default, $slide);
					$html .= '<div class="cactus-slider-item">';
					 if($slide['image'] !=''):
					if (is_numeric($slide['image'])) {
							$image_attributes = wp_get_attachment_image_src($slide['image'], 'full');
							$slide['image']    = $image_attributes[0];
						  }
					
					$html .= '<img src="'.esc_url($slide['image']).'" alt="'.esc_attr($slide['title']).'">';
					 endif;
					 
					$html .= '<div class="cactus-slider-caption-wrap">';
					$html .= '<div class="cactus-slider-caption">';
					$html .= '<div class="cactus-slider-caption-inner">';
					$html .= '<h2 class="cactus-slider-title">'.wp_kses_post( $slide['title'] ).'</h2>';
					$html .= '<p class="cactus-slider-desc">'.wp_kses_post( $slide['subtitle'] ).'</p>';
					
					if($slide['btn_text']!=''):
						$html .= '<div class="cactus-action"> <a href="'.esc_url($slide['btn_link']).'"><span class="cactus-btn primary">'.esc_attr($slide['btn_text']).'</span></a> </div>';
					endif;
					
					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';
					$html .= '</div>';
					}
				$html .= '</div>';
				
				}
	
		echo $html;
	
	}
	
	/**
	 * Get sidebar status
	*/	
	function page_title_bar($content){
			
			global $post;
			$theme = VELA_THEME_OPTION_NAME;
			$prefix = '_vela_';
			$prefix2 = 'vela_';
			if( $theme == 'cactus' || strstr($theme,'cactus') ){
				$prefix = '_ccmb_';
				$prefix2 = 'cactus_';
			}
			if( $theme == 'astore' || strstr($theme,'astore') ){
				$prefix = '_acmb_';
				$prefix2 = 'astore_';
				
			}
				
			$postid = isset( $post->ID )?$post->ID:0;
			if(is_home()){
				$postid = get_option( 'page_for_posts' );
			}
			if((is_singular() || is_home()) && $postid>0){
				$hide_page_title_bar = get_post_meta($postid, $prefix.'hide_page_title_bar', true);
				if($hide_page_title_bar=='1' || $hide_page_title_bar=='on')
					return '';
			}
			if (is_category()) {
			  $category = get_category(get_query_var('cat'));
			  $cat_id = $category->cat_ID;
			  if($cat_id>0){
					$category_meta = get_term_meta($cat_id);
					$category_meta = isset($category_meta[$prefix2.'category_meta'])?unserialize($category_meta[$prefix2.'category_meta'][0]):null;
					
					if(isset($category_meta[$cat_id][$prefix.'hide_page_title_bar'])){
						$hide_page_title_bar = $category_meta[$cat_id][$prefix.'hide_page_title_bar'];
						if($hide_page_title_bar=='1' || $hide_page_title_bar=='on')
							return '';
						}
				}
			}
			
				return $content;
			}
	
	/**
	 * Enqueue front scripts
	*/
	
	function enqueue_scripts()
	{
	
		global $post;
		$custom_css = '';
		$postid = isset($post->ID)?$post->ID:0;
		if(is_home()){
			$postid = get_option( 'page_for_posts' );
			}
		
		$theme = VELA_THEME_OPTION_NAME;
		$prefix = '_vela_';
		$prefix2 = 'vela_';
		
		if( $theme == 'cactus' || strstr($theme,'cactus') ){
			$prefix = '_ccmb_';
			$prefix2 = 'cactus_';
		}
		if( $theme == 'astore' || strstr($theme,'astore') ){
			$prefix = '_acmb_';
			$prefix2 = 'astore_';
		}
			
		if($postid>0){
			$this->slider = get_post_meta($postid, $prefix.'slideshow', true);
			$bg_color = get_post_meta($postid, $prefix.'bg_color', true);
			$bg_image = get_post_meta($postid, $prefix.'bg_image', true);
			
			if($bg_color!=''){
				$custom_css .= '.page-id-'.$postid.' .page-wrap,.postid-'.$postid.' .page-wrap{background-color:'.$bg_color.';}';
				if( !is_page_template('template-sections.php') )
					$custom_css .= '.page-id-'.$postid.' .page-inner, .postid-'.$postid.' .page-inner{padding-top: 30px;}';
				}
			if($bg_image!=''){
				$custom_css .= '.page-id-'.$postid.' .page-wrap, .postid-'.$postid.' .page-wrap{background-image:url('.$bg_image.');}';
				if( !is_page_template('template-sections.php') )
					$custom_css .= '.page-id-'.$postid.' .page-inner, .postid-'.$postid.' .page-inner{padding-top: 30px;}';
				
				}
				
		}
		if(!empty($this->slider) && is_array($this->slider)){
			$custom_css .= '.page-id-'.$postid.' .page-wrap, .blog .page-wrap{padding-top: 0;}.page-id-'.$postid.' .page-inner, .blog .page-inner{padding-top:30px;}';
		}
		
		if (is_category()) {
			  $category = get_category(get_query_var('cat'));
			  $cat_id = $category->cat_ID;
			  if($cat_id>0){
					$category_meta = get_term_meta($cat_id);
					$category_meta = isset($category_meta[$prefix2.'category_meta'])?unserialize($category_meta[$prefix2.'category_meta'][0]):null;
					
					if(isset($category_meta[$cat_id][$prefix.'bg_color'])){
						$custom_css .= ".category-".$cat_id." .page-wrap{background-color:".$category_meta[$cat_id][$prefix.'bg_color'].";}";
						$custom_css .= ".category-".$cat_id." .page-inner, .category-".$cat_id." .page-inner{ padding-top: 30px; }";
						}
					if(isset($category_meta[$cat_id]['bg_img'])){
						$image = wp_get_attachment_image_url( $category_meta[ $cat_id ]['bg_img'], 'full');
						
						$custom_css .= ".category-".$cat_id." .page-wrap{background-image:url(".$image.");}";
						$custom_css .= ".category-".$cat_id." .page-inner, .category-".$cat_id." .page-inner{padding-top: 30px;}";
						}
						
				  }
		  }

		$i18n = array();
		
		wp_enqueue_script( 'owl-carousel', plugins_url('assets/vendor/owl-carousel/js/owl.carousel.min.js', __FILE__), array( 'jquery' ), null, false);
		wp_enqueue_script( 'jquery-cookie', plugins_url('assets/vendor/jquery.cookie.min.js', __FILE__), array( 'jquery' ), null, false);
		wp_enqueue_script( 'mixitup', plugins_url('assets/vendor/mixitup/mixitup.min.js', __FILE__), array( 'jquery' ), null, false);
		
		wp_enqueue_script( 'vela-companion-front', plugins_url('assets/js/front.js', __FILE__),array('jquery'),VELA_COMPANION_VER,false);
		
		wp_enqueue_style( 'owl-carousel', plugins_url('assets/vendor/owl-carousel/css/owl.carousel.css', __FILE__));
		wp_enqueue_style( 'vela-companion-front', plugins_url('assets/css/front.css', __FILE__));
		wp_enqueue_style( 'vela-companion-element', plugins_url('assets/css/element.css', __FILE__));
		if( $theme == 'cactus' || strstr($theme,'cactus') ){
			wp_enqueue_style( 'cactus-companion-front', plugins_url('assets/css/cactus-frontpage.css', __FILE__));
		}
		
		$i18n = array(
			'i1'=> __('Please fill out all required fields.','cactus-companion' ),
			'i2'=> __('Please enter your name.','cactus-companion' ),
			'i3'=> __('Please enter valid email.','cactus-companion' ),
			'i4'=> __('Please enter subject.','cactus-companion' ),
			'i5'=> __('Message is required.','cactus-companion' ),
			);
		
		wp_localize_script( 'vela-companion-front', 'vela_params', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'i18n' => $i18n,
		'plugins_url' => plugins_url('', __FILE__)
	)  );	
		
		$scheme_value = get_option( 'elementor_scheme_color' );
		
		if( is_array($scheme_value) && isset($scheme_value[4]) && $scheme_value[4]!='' ){
		
		$custom_css .= ".cactus-e-img-frame:before {
			border-color: ".$scheme_value[4].";
		}
		.cactus-e-testimonial-item .cactus-e-person-avatar:before {
			border-color: ".$scheme_value[4].";
		}
		.cactus-e-testimonial-carousel .owl-dot.active {
			background-color: ".$scheme_value[4].";
		}
		.cactus-e-port-category {
			color: #00dfb8;
		}
		.cactus-e-port-filter li.active a {
			background-color: ".$scheme_value[4].";
		}
		.cactus-e-post-action a {
			color: ".$scheme_value[4].";
		}
		.cactus-e-post-mon {
			color: ".$scheme_value[4].";
		}";
				}

		
		if($custom_css!='')
			wp_add_inline_style( 'vela-companion-element', wp_filter_nohtml_kses($custom_css) );

	}
	
	
	public static function replaceStar($str, $start, $length = 0){
	  $i = 0;
	  $star = '';
	  if($start >= 0) {
	   if($length > 0) {
		$str_len = strlen($str);
		$count = $length;
		if($start >= $str_len) {
		 $count = 0;
		}
	   }elseif($length < 0){
		$str_len = strlen($str);
		$count = abs($length);
		if($start >= $str_len) {
		 $start = $str_len - 1;
		}
		$offset = $start - $count + 1;
		$count = $offset >= 0 ? abs($length) : ($start + 1);
		$start = $offset >= 0 ? $offset : 0;
	   }else {
		$str_len = strlen($str);
		$count = $str_len - $start;
	   }
	  }else {
	   if($length > 0) {
		$offset = abs($start);
		$count = $offset >= $length ? $length : $offset;
	   }elseif($length < 0){
		$str_len = strlen($str);
		$end = $str_len + $start;
		$offset = abs($start + $length) - 1;
		$start = $str_len - $offset;
		$start = $start >= 0 ? $start : 0;
		$count = $end - $start + 1;
	   }else {
		$str_len = strlen($str);
		$count = $str_len + $start + 1;
		$start = 0;
	   }
	  }
	 
	  while ($i < $count) {
	   $star .= '*';
	   $i++;
	  }
	 
	  return substr_replace($str, $star, $start, $count);
	}
	/**
	 * Admin menu
	*/
	function plugin_menu() {
	//	add_menu_page( 'Vela Companion', 'Vela Companion', 'manage_options', 'vela-companion', array( 'VelaTemplater', 'render_admin_page' ) );
		
		if( VELA_THEME_OPTION_NAME != 'capeone' && !strstr(VELA_THEME_OPTION_NAME,'capeone') ){
		add_theme_page( __( 'Vela Templates Directory', 'vela-companion' ), __( 'Vela Templates', 'vela-companion' ), 'manage_options', 'vela-templates',
			  array( 'VelaTemplater', 'render_admin_page' )
		  );
		}
		  
		  add_theme_page( __( 'Vela Sites Directory', 'vela-companion' ), __( 'Vela Sites', 'vela-companion' ), 'manage_options', 'vela-sites',
			  array( 'VelaSiter', 'render_sites_page' )
		  );
		
		  add_theme_page( __( 'Vela Theme License', 'vela-companion' ), __( 'Vela License', 'vela-companion' ), 'manage_options', 'vela-license',
				array( 'VelaCompanion', 'license' )
			);

		add_action( 'admin_init', array(&$this,'register_mysettings') );
	}
	
	/**
	 * Register settings
	*/
	function register_mysettings() {
		register_setting( 'vela-settings-group', 'vela_companion_options', array(&$this,'text_validate') );
	}
	
	static function license(){
		
		if ( !current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.', 'vela-companion' ) );
		}
		?>
		
        <form method="post" class="vela-license-box" action="<?php echo admin_url('options.php');?>">

		<?php
			settings_fields( 'vela-settings-group' );
			$options     = get_option('vela_companion_options',VelaCompanion::default_options());
			$vela_companion_options = wp_parse_args($options,VelaCompanion::default_options());
			
			
		?>
		<div class="wrap">

          <div class="license">
          <p><?php _e( 'Activate to unlock Pro site demos and templates.', 'vela-companion' );?></p>
          <?php if($vela_companion_options['license_key'] == '' ):?>
		<p><?php _e( 'Vela License Key', 'vela-companion' );?>: <input size="50" name="vela_companion_options[license_key]" value="<?php echo $vela_companion_options['license_key'];?>" type="text" /></p>
		<p></p>
        <?php
		
		else:
		$vela_companion_options['license_key'] = VelaCompanion::replaceStar($vela_companion_options['license_key'],10,8);
		?>
        <p><?php _e( 'Vela License Key', 'vela-companion' );?>: <input size="50" disabled="disabled" name="vela_companion_options[license_key_hide]" value="<?php echo $vela_companion_options['license_key'];?>" type="text" /><input size="50" type="hidden" name="vela_companion_options[license_key]" value="" type="text" /></p>
		<p></p>
        
        <?php endif;?>
		 
		   </div>
			<p class="submit">
            <?php if($vela_companion_options['license_key'] == '' ):?>
			<input type="submit" class="button-primary" value="<?php _e('Active','vela-companion');?>" />
            <?php	else:?>
            <input type="submit" class="button-primary" value="<?php _e('Deactivate','vela-companion');?>" />
		 <?php endif;?>
			</p>
		</div>
        </form>
		
	<?php	}
	
	
	function gridlist_set_default_view() {
				
				$default = apply_filters( 'vela_glt_default','grid' );
				
				?>
					<script>
					jQuery(document).ready(function($) {
						if ($.cookie( 'gridcookie' ) == null) {
					    	$( '.archive .post-wrap ul.products' ).addClass( '<?php echo $default; ?>' );
					    	$( '.gridlist-toggle #<?php echo $default; ?>' ).addClass( 'active' );
					    }
					});
					</script>
				<?php
			}
			
	
	function before_sidebar(){
		global $post;
		
		$theme = VELA_THEME_OPTION_NAME;
		$prefix = '_vela_';
		if( $theme == 'cactus' || strstr($theme,'cactus') )
			$prefix = '_ccmb_';
		if( $theme == 'astore' || strstr($theme,'astore') )
			$prefix = '_acmb_';
		
		$postid = isset($post->ID)?$post->ID:0;
		if( is_singular() ){
				
				$before_sidebar = get_post_meta($postid , $prefix.'before_sidebar', true);
				if( $before_sidebar != '' ){
					echo '<div class="vela-before-sidebar">';
					echo wp_kses_post($before_sidebar);
					echo '</div>';
				}
				
		}
		
	}
	
	function after_sidebar(){
		global $post;
		
		$theme = VELA_THEME_OPTION_NAME;
		$prefix = '_vela_';
		if( $theme == 'cactus' || strstr($theme,'cactus') )
			$prefix = '_ccmb_';
		if( $theme == 'astore' || strstr($theme,'astore') )
			$prefix = '_acmb_';
		
		$postid = isset($post->ID)?$post->ID:0;
		if( is_singular() ){
				
				$after_sidebar = get_post_meta($postid , $prefix.'after_sidebar', true);
				if( $after_sidebar != '' ){
					echo '<div class="vela-after-sidebar">';
					echo wp_kses_post($after_sidebar);
					echo '</div>';
				}
				
		}
		
	}
	
	/**
	 * Get sidebar layout
	*/

	function page_sidebar_layout( $content ){
		
			global $post;
			
		$theme = VELA_THEME_OPTION_NAME;
		$prefix = '_vela_';
		if( $theme == 'cactus' || strstr($theme,'cactus') )
			$prefix = '_ccmb_';
		if( $theme == 'astore' || strstr($theme,'astore') )
			$prefix = '_acmb_';
			
			$postid = isset($post->ID)?$post->ID:0;
			if(is_home()){
				$postid = get_option( 'page_for_posts' );
			}
			
			if((is_singular()||is_home()) && $postid>0){
				
				$sidebar_layout = get_post_meta($postid , $prefix.'sidebar', true);
				
				if( $sidebar_layout != '' )
					return $sidebar_layout;
				}
			
			if (is_category()) {
			  $category = get_category(get_query_var('cat'));
			  $cat_id = $category->cat_ID;
			  if($cat_id>0){
					$category_meta = get_term_meta($cat_id);
					$category_meta = isset($category_meta[$prefix.'category_meta'])?unserialize($category_meta[$prefix.'category_meta'][0]):null;
					
					if(isset($category_meta[$cat_id][ $prefix.'sidebar'])){
						$sidebar_layout = $category_meta[$cat_id][ $prefix.'sidebar'];
						if( $sidebar_layout != '' )
							return $sidebar_layout;
						}
				}
			}
				
				return $content;
			
			}
	
	public static function get_query_args( $control_id, $settings ) {
		$defaults = array(
			$control_id . '_post_type' => 'post',
			$control_id . '_posts_ids' => array(),
			'orderby' => 'date',
			'order' => 'desc',
			'posts_per_page' => 3,
			'offset' => 0,
		);

		$settings = wp_parse_args( $settings, $defaults );

		$post_type = $settings[ $control_id . '_post_type' ];

		if ( 'current_query' === $post_type ) {
			$current_query_vars = $GLOBALS['wp_query']->query_vars;

			/**
			 * Current query variables.
			 *
			 * Filters the query variables for the current query.
			 *
			 * @param array $current_query_vars Current query variables.
			 */
			$current_query_vars = apply_filters( 'elementor_pro/query_control/get_query_args/current_query', $current_query_vars );

			return $current_query_vars;
		}

		$query_args = array(
			'orderby' => $settings['orderby'],
			'order' => $settings['order'],
			'ignore_sticky_posts' => 1,
			'post_status' => 'publish', // Hide drafts/private posts for admins
		);

		if ( 'by_id' === $post_type ) {
			$query_args['post_type'] = 'any';
			$query_args['posts_per_page'] = -1;

			$query_args['post__in']  = $settings[ $control_id . '_posts_ids' ];

			if ( empty( $query_args['post__in'] ) ) {
				// If no selection - return an empty query
				$query_args['post__in'] = array('0');
			}
		} else {
			$query_args['post_type'] = $post_type;
			$query_args['posts_per_page'] = $settings['posts_per_page'];
			$query_args['tax_query'] = array();

			if ( 0 < $settings['offset'] ) {
				/**
				 * Due to a WordPress bug, the offset will be set later, in $this->fix_query_offset()
				 * @see https://codex.wordpress.org/Making_Custom_Queries_using_Offset_and_Pagination
				 */
				$query_args['offset_to_fix'] = $settings['offset'];
			}

			$taxonomies = get_object_taxonomies( $post_type, 'objects' );

			foreach ( $taxonomies as $object ) {
				$setting_key = $control_id . '_' . $object->name . '_ids';

				if ( ! empty( $settings[ $setting_key ] ) ) {
					$query_args['tax_query'][] = array(
						'taxonomy' => $object->name,
						'field' => 'term_id',
						'terms' => $settings[ $setting_key ],
					);
				}
			}
		}

		if ( ! empty( $settings[ $control_id . '_authors' ] ) ) {
			$query_args['author__in'] = $settings[ $control_id . '_authors' ];
		}

		if ( ! empty( $settings['exclude'] ) ) {
			$post__not_in = array();
			if ( in_array( 'current_post', $settings['exclude'] ) ) {
				if ( Utils::is_ajax() && ! empty( $_REQUEST['post_id'] ) ) {
					$post__not_in[] = $_REQUEST['post_id'];
				} elseif ( is_singular() ) {
					$post__not_in[] = get_queried_object_id();
				}
			}

			if ( in_array( 'manual_selection', $settings['exclude'] ) && ! empty( $settings['exclude_ids'] ) ) {
				$post__not_in = array_merge( $post__not_in, $settings['exclude_ids'] );
			}

			$query_args['post__not_in'] = $post__not_in;
		}

		return $query_args;
	}
	/**
	 * Set default options
	*/
	
	public static function default_options(){

		$return = array(
			'license_key' => '',

		);
		
		return $return;
		
		}

		
		}
	
	new VelaCompanion;
}