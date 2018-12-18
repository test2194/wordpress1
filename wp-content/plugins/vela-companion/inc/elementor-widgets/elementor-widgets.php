<?php
use Elementor\Controls_Manager;
use Elementor\Group_Control_Base;
if ( ! defined( 'ABSPATH' ) ) exit;


// This file is pretty much a boilerplate WordPress plugin.
// It does very little except including wp-widget.php
if(!class_exists('AstoreElementorCustomElement')){
class AstoreElementorCustomElement {

   private static $instance = null;

   public static function get_instance() {
      if ( ! self::$instance )
         self::$instance = new self;
      return self::$instance;
   }

   public function init(){
      add_action( 'elementor/widgets/widgets_registered', array( $this, 'widgets_registered' ) );
   }

   public function widgets_registered() {

      // We check if the Elementor plugin has been installed / activated.
      if(defined('ELEMENTOR_PATH') && class_exists('Elementor\Widget_Base')){
		  
		 

         // We look for any theme overrides for this custom Elementor element.
         // If no theme overrides are found we use the default one in this plugin.

			foreach (glob(dirname(__FILE__)."/widgets/*.php") as $filename) {

				$template_file = $filename;
				
				 if ( $template_file && is_readable( $template_file ) ) {
            		require_once $template_file;
         		}
			}
        
      }
   }
}
}
AstoreElementorCustomElement::get_instance()->init();


function vela_add_elementor_widget_categories( $elements_manager ) {

	$elements_manager->add_category(
		'vela-elements',
		[
			'title' => __( 'Vela Elements', 'vela-companion' ),
			'icon' => 'fa fa-plug',
		]
	);
	

}
add_action( 'elementor/elements/categories_registered', 'vela_add_elementor_widget_categories' );


