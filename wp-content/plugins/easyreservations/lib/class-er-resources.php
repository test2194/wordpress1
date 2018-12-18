<?php
/**
 * Loads resources once
 */


//Prevent direct access to file
if(!defined('ABSPATH'))
	exit;


class ER_Resources {

    /**
	 * The single instance of the class.
	 *
	 * @var ER_Resources|null
	 */
	protected static $instance = null;
	protected $resources = null;
	protected $did_query = false;

	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Get resources
	 * @param bool $id (optional)
	 *
	 * @return array|ER_Resource|bool
	 */
	public function get($id = false){
		if ( is_null( $this->resources ) ) {
			$this->resources = $this->prepare();
		}
		if($id){
			if(isset($this->resources[$id])){
				return $this->resources[$id];
			}
			return false;
		}
		return $this->resources;
	}

	public function get_accessible(){
		$return = array();
		foreach($this->get() as $id => $resource){
			$get_role = get_post_meta($resource->ID, 'easy-resource-permission', true);
			if(empty($get_role) || current_user_can($get_role)){
				$return[$id]= $resource;
			}
		}
		return $return;
	}

	protected function prepare(){
		return $this->transform($this->query());
	}

	protected function transform($resources_post_data){
		$return = array();
		foreach($resources_post_data as $post_data){
			$return[$post_data->ID] = new ER_Resource($post_data);
		}
		return $return;
	}

	protected function query(){
		global $wpdb;

		$resources = $wpdb->get_results(
			"SELECT ID, post_title, menu_order, post_name, post_content, post_excerpt 
			FROM {$wpdb->prefix}posts 
			WHERE post_type = 'easy-rooms' AND post_status != 'auto-draft' 
			ORDER BY menu_order ASC"
		);

		if(function_exists('icl_object_id')){
			if(defined('POLYLANG_VERSION')){
				if(is_admin()){
					$blog_current_lang = !empty($_GET['lang']) && !is_numeric($_GET['lang']) ? $_GET['lang'] :
						(($lg = get_user_meta(get_current_user_id(), 'pll_filter_content', true)) ? $lg : 'all');
				} else {
					$blog_current_lang = pll_current_language();
				}
				$default_lang = pll_default_language();
			} else {
				$wpml_options = get_option( 'icl_sitepress_settings' );
				$default_lang = $wpml_options['default_language'];

				if(defined('ICL_LANGUAGE_CODE')) $blog_current_lang = ICL_LANGUAGE_CODE;
				else {
					$blog_lang = get_option('WPLANG');
					if(!$blog_lang && defined('WPLANG') && WPLANG != '') $blog_lang = WPLANG;
					if(!$blog_lang) $blog_lang = 'en';

					$lang_locales = array( 'en_US' => 'en', 'af' => 'af', 'ar' => 'ar', 'bn_BD' => 'bn', 'eu' => 'eu', 'be_BY' => 'be', 'bg_BG' => 'bg', 'ca' => 'ca', 'zh_CN' => 'zh-hans', 'zh_TW' => 'zh-hant', 'hr' => 'hr', 'cs_CZ' => 'cs', 'da_DK' => 'da', 'nl_NL' => 'nl', 'eo' => 'eo', 'et' => 'et', 'fo' => 'fo', 'fi_FI' => 'fi', 'fr_FR' => 'fr', 'gl_ES' => 'gl', 'ge_GE' => 'ka', 'de_DE' => 'de', 'el' => 'el', 'he_IL' => 'he', 'hu_HU' => 'hu', 'is_IS' => 'is', 'id_ID' => 'resource', 'it_IT' => 'it', 'ja' => 'ja', 'km_KH' => 'km', 'ko_KR' => 'ko', 'ku' => 'ku', 'lv' => 'lv', 'lt' => 'lt', 'mk_MK'  => 'mk', 'mg_MG' => 'mg', 'ms_MY' => 'ms', 'ni_ID' => 'ni', 'nb_NO' => 'nb', 'fa_IR' => 'fa', 'pl_PL' => 'pl', 'pt_PT' => 'pt-pt', 'pt_BR' => 'pt-br', 'ro_RO' => 'ro', 'ru_RU' => 'ru', 'sr_RS' => 'sr', 'si_LK' => 'si', 'sk_SK' => 'sk', 'sl_SI' => 'sl', 'es_ES' => 'es', 'su_ID' => 'su', 'sv_SE' => 'sv', 'tg' => 'tg', 'th' => 'th', 'tr' => 'tr', 'uk_UA' => 'uk', 'ug' => 'ug', 'uz_UZ' => 'uz', 'vi' => 'vi', 'cy' => 'cy' );
					if(isset($lang_locales[$blog_lang])) $blog_current_lang = $lang_locales[$blog_lang];
					else {
						$exp = explode('_',$blog_lang);
						$blog_current_lang = $exp[0];
					}
				}
			}

			foreach ($resources as $key => $resource){
				$current_lang_id = icl_object_id($resource->ID,'easy-rooms', false, $blog_current_lang);
				$default_lang_id = icl_object_id($resource->ID,'easy-rooms', $resource->ID, $default_lang);

				if($default_lang_id == $resource->ID && !is_null($current_lang_id)){
					$real_translation = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT post_title, post_content, post_excerpt 
							FROM {$wpdb->prefix}posts 
							WHERE ID='%s' AND post_type = 'easy-rooms' AND post_status != 'auto-draft' 
							ORDER BY menu_order ASC",
							$current_lang_id
						)
					);
					$resources[$key]->post_excerpt = $real_translation[0]->post_excerpt;
					$resources[$key]->post_content = $real_translation[0]->post_content;
					$resources[$key]->post_title = $real_translation[0]->post_title;
				} elseif($default_lang_id !== $resource->ID && !is_null($current_lang_id)){
					unset($resources[$key]);
					continue;
				}
			}
		}

		return $resources;
	}
}
