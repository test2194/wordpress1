<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Vela_Widget_Testimonials extends Widget_Base {
	
	public function get_categories() {
		return [ 'vela-elements' ];
	}
	
   public function get_name() {
      return 'vela-testimonials';
   }

   public function get_title() {
      return __( 'Testimonials Carousel', 'vela-companion' );
   }

   public function get_icon() { 
        return 'eicon-wordpress';
   }

   protected function _register_controls() {
		$this->start_controls_section(
			'section_vela_testimonials',
			[
				'label' => __( 'Testimonial Items', 'vela-companion' ),
			]
		);

		$this->add_control(
			'vela_testimonials',
			[
				'label' => '',
				'type' => Controls_Manager::REPEATER,
				'default' => [
					
				],
				'fields' => [
					[
						'name' => 'description',
						'label' => __( 'Description', 'vela-companion' ),
						'type' => Controls_Manager::TEXTAREA,
						'label_block' => true,
						'placeholder' => __( 'Description', 'vela-companion' ),
						'default' => __( 'Description', 'vela-companion' ),
					],
					[
						'name' => 'avatar',
						'label' => __( 'Avatar', 'vela-companion' ),
						'type' => Controls_Manager::MEDIA,
						'label_block' => true,
						'default' => '',
					],
					[
						'name' => 'name',
						'label' => __( 'Name', 'vela-companion' ),
						'type' => Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => '',
					],
					[
						'name' => 'byline',
						'label' => __( 'Byline', 'vela-companion' ),
						'type' => Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => '',
					],
				],
				//'title_field' => '<i class="{{ icon }}" aria-hidden="true"></i> {{{ text }}}',
			]
		);
		
		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_testimonials',
			[
				'label' => __( 'Style', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'testimonial_color',
			[
				'label' => __( 'Color', 'vela-companion' ),
				'type' => Controls_Manager::COLOR,
				'default' => '#666666',
				'selectors' => [
					'{{WRAPPER}} .cactus-e-testimonial-content,{{WRAPPER}} .cactus-e-person-name,{{WRAPPER}} .cactus-e-person-jobtitle' => 'color: {{VALUE}};',
					'{{WRAPPER}} .cactus-e-testimonial-carousel .owl-dot:not(.active)' => 'background-color: {{VALUE}};',
				],
				
			]
		);


	}

   protected function render( $instance = [] ) {

      // get our input from the widget settings.

		$settings = $this->get_settings();

      ?>


 <div class="cactus-e-testimonial-carousel owl-carousel">
             
<?php foreach ( $settings['vela_testimonials'] as $index => $item ) : ?>
 
    <div class="cactus-e-testimonial-item">
      <div class="cactus-e-testimonial-content" style=";">
         <?php echo wp_kses_post($item['description']);?>
      </div>
      <div class="cactus-e-person-vcard">
          <div class="cactus-e-person-avatar">
              <img src="<?php echo esc_url($item['avatar']['url']);?>" alt="">
          </div>                                                    
          <h3 class="cactus-e-person-name" style=""><?php echo esc_attr($item['name']);?></h3>
          <h4 class="cactus-e-person-jobtitle" style=""><?php echo esc_attr($item['byline']);?></h4>
      </div>
  </div>


	<?php endforeach; ?>
                  
              </div>

<?php

   }

   protected function content_template() {}

   public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new Vela_Widget_Testimonials );
