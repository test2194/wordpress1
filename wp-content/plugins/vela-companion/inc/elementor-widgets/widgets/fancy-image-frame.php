<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Vela_Widget_Fancy_Image_Frame extends Widget_Base {

  public function get_categories() {
		return [ 'vela-elements' ];
	}
	
   public function get_name() {
      return 'vela-fancy-image-frame';
   }

   public function get_title() {
      return __( 'Fancy Image Frame', 'vela-companion' );
   }

   public function get_icon() { 
        return 'eicon-wordpress';
   }

   protected function _register_controls() {
	   
	   $this->start_controls_section(
			'section_fancy_image_frame',
			[
				'label' => __( 'Fancy Image Frame', 'vela-companion' ),
			]
		);


	  
	   $this->add_control(
         'image',
         [
            'label' => __( 'Image', 'vela-companion' ),
            'type' => Controls_Manager::MEDIA,
            'default' => [],
            
         ]
      );
	  $this->end_controls_section();

   }

   protected function render( $instance = [] ) {

      // get our input from the widget settings.

		$settings = $this->get_settings();
		echo '<div class="cactus-e-img-frame">';
          
			$image_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'image' );

			echo $image_html;
		
         echo '</div>';

   }

   protected function content_template() {}

   public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new Vela_Widget_Fancy_Image_Frame );
