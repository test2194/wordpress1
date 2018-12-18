<?php
include_once( ABSPATH . WPINC . '/feed.php' );

if (!class_exists('VelaTemplater')){
class VelaTemplater {

	public function __construct($atts = NULL)
		{
			add_action( 'wp_ajax_vela_import_elementor', array(&$this ,'import_elementor'));
			add_action( 'wp_ajax_nopriv_vela_import_elementor', array(&$this ,'import_elementor'));
			
		}
	
	/**
	 * templates list
	 */
	public static function templates(){

		$templates_list = array(
		
		);
		
		$theme = VELA_THEME_OPTION_NAME;
		
		// Get a SimplePie feed object from the specified feed source.
		$options     = get_option('vela_companion_options',VelaCompanion::default_options());
		$license_key = isset($options['license_key'])?$options['license_key']:'';
		
		$feed_url = 'https://velathemes.com/vela-elementor-feed/?license_key='.$license_key.'&theme='.$theme;
		
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
			$templateid = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'templateid');
			$demo = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'demo');
			$plugins = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'plugins');
			$pro = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'pro');
			$options = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'options');
			$purchase_url = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'purchase_url');
			$wxr = $item->get_item_tags(SIMPLEPIE_NAMESPACE_RSS_20, 'wxr');
			
			$required_plugins = array( 'elementor' => array( 'name' => __( 'Elementor Page Builder', 'vela-companion' ) ) );
			$plugin = '';
			$my_plugins = '';
						
			if ( isset($plugins[0]['data']) && $plugins[0]['data']!='' ){
				
			$plugins_array = json_decode(urldecode($plugins[0]['data']),true );
			
			if( $plugins_array && is_array($plugins_array)){
				
				foreach( $plugins_array as $plugin_array ){
					
					$required_plugins[$plugin_array['slug']] = $plugin_array;
					
					}
				
				}else{

				$plugin = str_replace("\r\n",'||',urldecode($plugins[0]['data']));
				
				if($plugin){
					$my_plugins = explode('||',$plugin );
			
				if($my_plugins){
				  foreach($my_plugins as $my_plugin){
					  $pices = explode('|',$my_plugin);
					  
					  if( $pices[0] !='' ){
						  
						  if(  $pices[0] == 'Elementor Page Builder' )
						  	continue;
					
						  $slug = sanitize_title(trim($pices[0]));
							
						  if(isset($pices[1]))
							$required_plugins[$slug] =  array( 'name' => $pices[0], 'slug' => $slug, 'init'=>trim($pices[1]) );
							  else
							$required_plugins[$slug] =  array( 'name' => $pices[0], 'slug' => $slug );
					  }
				  }
				  }
				}
			
			}
		}
			
			if(!($templateid)){
				$templateid = sanitize_title($title[0]['data']);
			}else{
				$templateid = sanitize_title($templateid[0]['data']);
				}
			if(isset($title[0]['data'])){
				$templates_list[$templateid] = array(
					'title'       => $title[0]['data'],
					'description' => $description[0]['data'],
					'demo_url'    => urldecode($demo[0]['data']),
					'screenshot'  => urldecode($image[0]['data']),
					'import_file' => urldecode($template[0]['data']),
					'required_plugins' => $required_plugins,
					'pro' => $pro[0]['data'],
					'options_file' => $options[0]['data'],
					'purchase_url' => $purchase_url[0]['data'],
					'wxr' => $wxr[0]['data'],
				);
				}
		
				 endforeach; 
		 endif;	


		return apply_filters( 'vela_elementor_templates_list', $templates_list );
		
		}

	/**
	 * Render the template directory admin page.
	 */
	public static function render_admin_page() {
		
		$templates_array = VelaTemplater::templates();
		include 'template-directory-tpl.php';
	}
	
	/**
	 * Check plugin state.
	 */
	public static function check_plugin_state( $slug, $file='' ) {
		if($file =='')
			$file = $slug;
		if ( file_exists( WP_CONTENT_DIR . '/plugins/' . $slug . '/' . $file . '.php' ) || file_exists( WP_CONTENT_DIR . '/plugins/' . $slug . '/index.php' ) ) {
			require_once( ABSPATH . 'wp-admin' . '/includes/plugin.php' );
			$needs = ( is_plugin_active( $slug . '/' . $file . '.php' ) ||
			           is_plugin_active( $slug . '/index.php' ) ) ?
				'deactivate' : 'activate';

			return $needs;
		} else {
			return 'install';
		}
	}
	
	/**
	 * Generate action button html.
	 *
	 */
	public static function get_button_html( $slug, $file='' ) {
		$button = '';
		if ( $file=='' )
			$file = $slug;
			
		$state  = VelaTemplater::check_plugin_state( $slug, $file );
		if ( ! empty( $slug ) ) {
			switch ( $state ) {
				case 'install':
					$nonce  = wp_nonce_url(
						add_query_arg(
							array(
								'action' => 'install-plugin',
								'from'   => 'import',
								'plugin' => $slug,
							),
							network_admin_url( 'update.php' )
						),
						'install-plugin_' . $slug
					);
					$button .= '<a data-slug="' . $slug . '" class="install-now vela-install-plugin button button-primary" href="' . esc_url( $nonce ) . '" data-name="' . $slug . '" aria-label="Install ' . $slug . '">' . __( 'Install and activate', 'vela-companion' ) . '</a>';
					break;
				case 'activate':
					$plugin_link_suffix = $slug . '/' . $file . '.php';
					$nonce              = add_query_arg(
						array(
							'action'   => 'activate',
							'plugin'   => rawurlencode( $plugin_link_suffix ),
							'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $plugin_link_suffix ),
						), network_admin_url( 'plugins.php' )
					);
					$button             .= '<a data-slug="' . $slug . '" class="activate-now button button-primary" href="' . esc_url( $nonce ) . '" aria-label="Activate ' . $slug . '">' . __( 'Activate', 'vela-companion' ) . '</a>';
					break;
			}// End switch().
		}// End if().
		return $button;
	}
	
	/**
	 * Import wxr
	 */	
	private static function import_wxr($xml_file){
		
		include(dirname(__FILE__).'/wordpress-importer.php');
				
		if ( file_exists($xml_file) && class_exists( 'Vela_Import' ) ) {
				$importer = new Vela_Import();
				
				$importer->fetch_attachments = true;
				ob_start();
				$importer->import($xml_file);
				ob_end_clean();

				flush_rewrite_rules();
		}

		}
		
	/**
	 * Utility method to call Elementor import routine.
	 */
	public function import_elementor($template='', $template_url='', $ajax = true, $set_as_home = false ) {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return 'no-elementor';
		}
		$templates_list = VelaTemplater::templates();
		require_once( ABSPATH . 'wp-admin' . '/includes/file.php' );
		require_once( ABSPATH . 'wp-admin' . '/includes/image.php' );
		
		if( isset($_POST['template_slug']) )
			$template = $_POST['template_slug'];
		
		if( isset($_POST['template_url']) )
			$template_url = $_POST['template_url'];
			
		if( isset($_POST['template_name']) )
			$template_name = $_POST['template_name'];
		else
			$template_name = $templates_list[$template]['title'];
					
		if(isset($templates_list[$template]['wxr']) ){
				$wxr = download_url( esc_url($templates_list[$template]['wxr'] ) );
				self::import_wxr($wxr);
				unlink( $wxr );
			}
		
		if ( isset($templates_list[$template]['options_file']) && $templates_list[$template]['options_file'] !='' ){
			
			$options_file = $templates_list[$template]['options_file'];
			
			}

		$template_file = download_url( esc_url( $template_url ) );
		$_FILES['file']['tmp_name'] = $template_file;
		
		$pathinfo = pathinfo($template_url);
		
		$elementor = new \Elementor\TemplateLibrary\Source_Local;

		$result = $elementor->import_template($pathinfo['basename'], $template_file);
		unlink( $template_file );

		if(isset($result->template_id)){
			$template_id = $result->template_id;
			$args = array(
			'post_type' => 'elementor_library',
			'p' => $template_id,
		);

		$query = new WP_Query( $args );
		$template_imported = $query->posts[0];
		
			}else{

		$args = array(
			'post_type'        => 'elementor_library',
			'nopaging'         => true,
			'posts_per_page'   => '1',
			'orderby'          => 'date',
			'order'            => 'DESC',
			'suppress_filters' => true,
		);

		$query = new WP_Query( $args );

		$template_imported = $query->posts[0];
		//get template id
		$template_id = $template_imported->ID;
		}
		wp_reset_query();
		wp_reset_postdata();

		//page content
		$page_content = $template_imported->post_content;
		
		//meta fields
		$elementor_data_meta      = get_post_meta( $template_id, '_elementor_data' );
		$elementor_ver_meta       = get_post_meta( $template_id, '_elementor_version' );
		$elementor_edit_mode_meta = get_post_meta( $template_id, '_elementor_edit_mode' );
		$elementor_css_meta       = get_post_meta( $template_id, '_elementor_css' );

		$elementor_metas = array(
			'_elementor_data'      => ! empty( $elementor_data_meta[0] ) ? wp_slash( $elementor_data_meta[0] ) : '',
			'_elementor_version'   => ! empty( $elementor_ver_meta[0] ) ? $elementor_ver_meta[0] : '',
			'_elementor_edit_mode' => ! empty( $elementor_edit_mode_meta[0] ) ? $elementor_edit_mode_meta[0] : '',
			'_elementor_css'       => $elementor_css_meta,
		);
		
		self::pre_process_post( $template_name );

		// Create post object
		$new_template_page = array(
			'post_type'     => 'page',
			'post_title'    => $template_name,
			'post_status'   => 'publish',
			'post_content'  => $page_content,
			'meta_input'    => $elementor_metas,
			'page_template' => apply_filters( 'template_directory_default_template', 'elementor_header_footer' )
		);

		$current_theme = wp_get_theme();


		$post_id = wp_insert_post( $new_template_page );

		
		if ( $options_file ){
			$page_options = file_get_contents( $options_file );
			$options = @json_decode($page_options,true);
			if(is_array($options)){
				foreach($options as $k=>$v){
					update_post_meta($post_id,$k,$v);
					}
				}
			
			}
		
		if($set_as_home == true){	
			update_option('show_on_front', 'page');
			update_option('page_on_front', $post_id); // Front Page
		}
		
		$redirect_url = add_query_arg( array(
			'post'   => $post_id,
			'action' => 'elementor',
		), admin_url( 'post.php' ) );
		
		
		
		if($ajax == true){
			echo json_encode( array("redirect_url"=>$redirect_url) );
			exit(0);
		}else{
			return $redirect_url;
			}
		
		//die();
	}

	/**
	 * Delete existing post by title
	 */
	function delete_post_by_title( $title ){
		
		$postid = post_exists( $title );
		if ( $postid ){
				wp_delete_post( $postid, true);
				self::delete_post_by_title( $title );
		}
	}
	
	/**
	 * Delete existing posts
	 */
	function pre_process_post( $title ){
		
		$postid = post_exists( $title );
		
		if( $postid ){
				wp_delete_post($postid, true);
		}
		
		self::delete_post_by_title( $title );

		}
	}
	new VelaTemplater;
}

