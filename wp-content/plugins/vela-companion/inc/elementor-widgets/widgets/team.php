<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Vela_Widget_Team extends Widget_Base {
	
	public function get_categories() {
		return [ 'vela-elements' ];
	}
	
   public function get_name() {
      return 'vela-team';
   }

   public function get_title() {
      return __( 'Team', 'vela-companion' );
   }

   public function get_icon() { 
        return 'eicon-wordpress';
   }

   protected function _register_controls() {
		$this->start_controls_section(
			'section_vela_team',
			[
				'label' => __( 'Items', 'vela-companion' ),
			]
		);
		
		$this->add_control(
			'avatar',
			[
				'label' => __( 'Avatar', 'vela-companion' ),
				'type' => Controls_Manager::MEDIA,
				'label_block' => true,
				'default' => [],
			]
		);
		
		$this->add_control(
			'name',
			[
				'label' => __( 'Name', 'vela-companion' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
			]
		);
		
		$this->add_control(
			'byline',
			[
				'label' => __( 'Byline', 'vela-companion' ),
				'type' => Controls_Manager::TEXT,
				'label_block' => true,
				'default' => '',
			]
		);
		
		$this->add_control(
			'description',
			[
				'label' => __( 'Description', 'vela-companion' ),
				'type' => Controls_Manager::TEXTAREA,
				'label_block' => true,
				'default' => '',
			]
		);

		$this->add_control(
			'team_icons',
			[
				'label' => __( 'Icon', 'vela-companion' ),
				'type' => Controls_Manager::REPEATER,
				'default' => [],
				'fields' => [
					
					[
						'name' => 'icon',
						'label' => __( 'Icon', 'vela-companion' ),
						'type' => Controls_Manager::ICON,
						'label_block' => true,
						'placeholder' => '',
					],
					[
						'name' => 'icon_link',
						'label' => __( 'Icon Link', 'vela-companion' ),
						'type' => Controls_Manager::TEXT,
						'label_block' => true,
						'placeholder' => '',
					],
					
				],
				//'title_field' => '<i class="{{ icon }}" aria-hidden="true"></i> {{{ text }}}',
			]
		);
		
		$this->end_controls_section();

	}

   protected function render( $instance = [] ) {

      // get our input from the widget settings.

		$settings = $this->get_settings();

      ?>


<div class="cactus-e-person">
    <div class="cactus-e-person-avatar">
        <?php
			$image_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'avatar' );

			echo $image_html;
		?>
    </div>
    <div class="cactus-e-person-content">
        <h3 class="cactus-e-person-name"><?php echo esc_attr($settings['name']);?></h3>
        <h4 class="cactus-e-person-jobtitle"><?php echo esc_attr($settings['byline']);?></h4>
        <div class="cactus-e-person-desc">
           <?php echo wp_kses_post($settings['description']);?>
        </div>
        <div class="cactus-e-person-social">
        <?php foreach ( $settings['team_icons'] as $index => $item ) : ?>
      
            <a target="_blank" href="<?php echo esc_url($item['icon_link']);?>"><i class="<?php echo esc_attr($item['icon']);?>"></i></a>
            <?php endforeach; ?>
 
        </div>
    </div>                                                    
</div>


<?php

   }

   protected function content_template() {}

   public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new Vela_Widget_Team );
