<?php
include_once( ABSPATH . WPINC . '/feed.php' );
include_once('class-download-remote-image.php' );
if (!class_exists('VelaSiter')){
class VelaSiter {

	public function __construct($atts = NULL)
		{
			add_action( 'wp_ajax_vela-sites-import-customizer-settings', array(&$this ,'import_customizer_data'));
			add_action( 'wp_ajax_nopriv_vela-sites-import-customizer-settings', array(&$this ,'import_customizer_data'));
			add_action( 'wp_ajax_vela-sites-import-wxr', array( $this, 'prepare_xml_data' ) );
			add_action( 'wp_ajax_vela-sites-import-options', array( $this, 'import_options' ) );
			add_action( 'wp_ajax_vela-sites-import-widgets', array( $this, 'import_widgets' ) );
			add_filter('wxr_importer.pre_process.post', array( $this, 'pre_process_post' ), 10, 4);
			add_action('wxr_importer.processed.post', array( $this, 'wp_import_insert_post' ), 10, 5);
			
		}
	
	/**
	 * Update _elementor_data attachment ID
	 *
	 */	
	function wp_import_insert_post( $post_id, $data, $meta, $comments, $terms ){

	$return = array();
	$_elementor_data = get_post_meta($post_id,'_elementor_data',true);
	
	if ( $_elementor_data != ''){
		
		$array = json_decode($_elementor_data, true);
		if ( is_array( $array ) ){
			foreach( $array as $k => $v ){
				
				if( is_array($v) ){
					
					$return[$k] = $this->array_loop($post_id, $v );
					
					}else{
						$return[$k] = $v;
						}
				}
				
				update_post_meta($post_id, '_elementor_data', json_encode($return));
		}
	}
	
 }
	
  /**
   * Download a remote image, insert it into the media library
   * and set it as a post's featured image.
   *
   */	
	
	function set_remote_image_as_featured_image( $post_id, $url, $attachment_data = array() ) {
		$download_remote_image = new Vela_Download_Remote_Image( $url, $attachment_data );
		$attachment_id         = $download_remote_image->download();
		if ( ! $attachment_id ) {
			return false; 
		}
		set_post_thumbnail( $post_id, $attachment_id );
		return $attachment_id;
	}
	
	/**
	 * Get attachment url by title
	 *
	 */		
	  function get_attachment_by_title( $title, $basename = '' ) {
  
	 	$attachment = get_page_by_title($title, OBJECT, 'attachment');
	    $attachment_info = array('id'=> '','url'=>'');
		if( !$attachment && $basename )
			$attachment = get_page_by_title($basename, OBJECT, 'attachment');
			
		if ( $attachment ){
		  $image_thumb = wp_get_attachment_image_src( $attachment->ID , 'full');
		  $attachment_info = array('id'=> $attachment->ID,'url'=>$image_thumb[0]);
		}
	  
		return $attachment_info;
	  }

  /**
   * Deep loop array
   *
   * @return array
   */
	
	function array_loop( $post_id, $array ){
		
		$return = array();
		foreach( $array as $k => $v ){
			
			if( is_array($v) ){
				
				if( isset($v['url']) && isset($v['id']) && !strstr($v['url'],home_url()) ){
					
					$pathinfo = pathinfo($v['url']);
					$attachment_info = $this->get_attachment_by_title( $pathinfo['filename'], $pathinfo['basename'] );
					$v['id'] = $attachment_info['id']."";
					$v['url'] = $attachment_info['url'];
				}
					$return[$k] = $this->array_loop($post_id,$v);
			
			}else{
				$return[$k] = $v;
				
				}
			
			}
		return $return;
		
	}
	
	/**
	 * Get an attachment ID given a URL.
	 *
	 */
	function get_attachment_id( $url ) {

	global $wpdb;
	$attachment_id = false;
 
	if ( '' == $attachment_url )
		return;
 
	$upload_dir_paths = wp_upload_dir();
 
	if ( false !== strpos( $attachment_url, $upload_dir_paths['baseurl'] ) ) {
 
		$attachment_url = preg_replace( '/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachment_url );
 
		$attachment_url = str_replace( $upload_dir_paths['baseurl'] . '/', '', $attachment_url );
 
		$attachment_id = $wpdb->get_var( $wpdb->prepare( "SELECT wposts.ID FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta WHERE wposts.ID = wpostmeta.post_id AND wpostmeta.meta_key = '_wp_attached_file' AND wpostmeta.meta_value = '%s' AND wposts.post_type = 'attachment'", $attachment_url ) );
 
	}
	
	return $attachment_id;
 

	}

	/**
	 * Delete existing post by title
	 */
	function delete_post_by_title( $title, $data ){
		
		$exists = post_exists( $title );
		if ( $exists ){

				wp_delete_post( $exists, true);
				$data['guid'] = $data['guid'].uniqid('copy');
				$data = self::delete_post_by_title( $title,$data );

		}
		return $data;
		
		}
	/**
	 * Delete existing posts
	 */
	function pre_process_post($data, $meta, $comments, $terms){
		
		$exists = post_exists( $data['post_title'] );
		if( $exists ){

				wp_delete_post($exists, true);
				$data['guid'] = $data['guid'].uniqid('copy');

		}
		
		$data = self::delete_post_by_title($data['post_title'], $data);

		return $data;
		}
	
	/**
	 * Import Customizer Settings
	 */
	function import_customizer_data(){

			do_action( 'vela_sites_import_customizer_settings' );

			$customizer_data = ( isset( $_POST['customizer_data'] ) ) ? (array) json_decode( urldecode( $_POST['customizer_data'] ), 1 ) : '';

			if ( !empty( $customizer_data ) ) {

				Vela_Customizer_Import::instance()->import( $customizer_data );
				wp_send_json_success( $customizer_data );

			} else {
				wp_send_json_error( __( 'Customizer data is empty!', 'vela-companion' ) );
			}

		}
	
	/**
	 * Prepare XML Data.
	 *
	 */
	function prepare_xml_data() {

		do_action( 'vela_sites_import_wxr_data' );
		
		wp_delete_nav_menu('vela-primary');

		$wxr_url = ( isset( $_REQUEST['wxr_url'] ) ) ? urldecode( $_REQUEST['wxr_url'] ) : '';

		if ( isset( $wxr_url ) ) {

			// Download XML file.
			$xml_path = Vela_Sites_Helper::download_file( $wxr_url );

			if ( $xml_path['success'] ) {

				if ( isset( $xml_path['data']['file'] ) ) {
					$data        = Vela_WXR_Importer::instance()->get_xml_data( $xml_path['data']['file'] );
					$data['xml'] = $xml_path['data'];
					wp_send_json_success( $data );
				} else {
					wp_send_json_error( __( 'There was an error downloading the XML file.', 'vela-companion' ) );
				}
			} else {
				wp_send_json_error( $xml_path['data'] );
			}
		} else {
			wp_send_json_error( __( 'Invalid site XML file!', 'vela-companion' ) );
		}

	}
	
	/**
	 * Import Options.
	 */
	function import_options() {

		do_action( 'vela_sites_import_options' );

		$options_data = ( isset( $_POST['options_data'] ) ) ? (array) json_decode( urldecode( $_POST['options_data'] ), 1 ) : '';

		if ( isset( $options_data ) ) {
			$options_importer = Vela_Site_Options_Import::instance();
			$options_importer->import_options( $options_data );
			wp_send_json_success( $options_data );
		} else {
			wp_send_json_error( __( 'Site options are empty!', 'vela-companion' ) );
		}

	}
	
	/**
	 * Import Widgets.
	 */
	function import_widgets() {

		do_action( 'vela_sites_import_widgets' );

		$widgets_data = ( isset( $_POST['widgets_data'] ) ) ? (object) json_decode( urldecode( $_POST['widgets_data'] ) ) : '';

		if ( isset( $widgets_data ) ) {
			$widgets_importer = Vela_Widget_Importer::instance();
			$status           = $widgets_importer->import_widgets_data( $widgets_data );
			wp_send_json_success( $widgets_data );
		} else {
			wp_send_json_error( __( 'Widget data is empty!', 'vela-companion' ) );
		}

	}
	
	/**
	 * sites list
	 */
	public static function sites(){
		
		$defaults_if_empty  = array(
			
		);

		$sites_list = array();
		
		$theme = VELA_THEME_OPTION_NAME;
		
		// Get a SimplePie feed object from the specified feed source.
		$options     = get_option('vela_companion_options',VelaCompanion::default_options());
		$license_key = isset($options['license_key'])?$options['license_key']:'';
		
		$feed_url = 'https://velathemes.com/api/sites-importer-api/?license_key='.$license_key.'&theme='.$theme;
		
		delete_transient('feed_' . md5($feed_url));
		delete_transient('feed_mod_' . md5($feed_url));

		$rss = fetch_feed( $feed_url );

		$maxitems = 0;
		
		if ( ! is_wp_error( $rss ) ) :
		
			$maxitems = $rss->get_item_quantity( 50 ); 
		
			$rss_items = $rss->get_items( 0, $maxitems );
		
		endif;
	
		if ( $maxitems == 0 ) :
		 
		else :
			
			foreach ( $rss_items as $item ) : 

			$template = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'template');
			$title = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'title');
			$description = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'description');
			$image = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'image');
			$siteid = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'siteid');
			$demo = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'demo');
			$plugins = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'plugins');
			$pro = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'pro');
			$options = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'options');
			$wxr = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'wxr');
			$widgets = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'widgets');
			$customizer = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'customizer');
			$purchase_url = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'purchase_url');
			
			
			$required_plugins = array('name' => __( 'Elementor Page Builder', 'vela-companion' ),'slug' => 'elementor', "init" => "elementor");


			if(isset($plugins[0]['data']) && $plugins[0]['data']!=''){
				$my_plugins = @json_decode($plugins[0]['data'], true);
				if( is_array($my_plugins) ){
					$required_plugins = $my_plugins;
				}
				
			}
			
			if(!($siteid)){
				$siteid = sanitize_title($title[0]['data']);
			}else{
				$siteid = sanitize_title($siteid[0]['data']);
				}
			if(isset($title[0]['data'])){
				$sites_list[$siteid] = array(
					'title'       => $title[0]['data'],
					'description' => $description[0]['data'],
					'demo'    => urldecode($demo[0]['data']),
					'screenshot'  => urldecode($image[0]['data']),
					'wxr' => urldecode($wxr[0]['data']),
					'required_plugins' => $required_plugins,
					'pro' => $pro[0]['data'],
					'options' => $options[0]['data'],
					'widgets' => $widgets[0]['data'],
					'customizer' => $customizer[0]['data'],
					'purchase_url' => $purchase_url[0]['data'],
					
				);
				}
		
				 endforeach; 
		 endif;	

		return apply_filters( 'vela_elementor_sites_list', $sites_list );
		
		}
	
	/**
	 * Import wxr
	 */	
	private static function import_wxr($xml_file){
		
		include(dirname(__FILE__).'/wordpress-importer.php');
		if ( file_exists($xml_file) && class_exists( 'Vela_Import' ) ) {
				$importer = new Vela_Import();
				$importer->fetch_attachments = false;
				ob_start();
				$importer->import($xml_file);
				ob_end_clean();

				flush_rewrite_rules();
		}
	}


	public static function render_sites_page() {
		
		$sites_array = VelaSiter::sites();
		include 'sites-directory-tpl.php';
	}


	}
	new VelaSiter;
}