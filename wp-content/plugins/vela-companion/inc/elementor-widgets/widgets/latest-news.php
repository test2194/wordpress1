<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Vela_Widget_Latest_News extends Widget_Base {

  public function get_categories() {
		return [ 'vela-elements' ];
	}
	
   public function get_name() {
      return 'vela-latest-news-1';
   }

   public function get_title() {
      return __( 'Latest News', 'vela-companion' );
   }

   public function get_icon() { 
        return 'eicon-wordpress';
   }

   protected function _register_controls() {

      $this->add_control(
         'section_blog_posts',
         [
            'label' => __( 'Blog Posts', 'vela-companion' ),
            'type' => Controls_Manager::SECTION,
         ]
      );

      $this->add_control(
         'posts_per_page',
         [
            'label' => __( 'Number of Posts', 'vela-companion' ),
            'type' => Controls_Manager::SELECT,
            'default' => 6,
            'section' => 'section_blog_posts',
            'options' => [
               2 => __( '2', 'vela-companion' ),
               3 => __( '3', 'vela-companion' ),
               4 => __( '4', 'vela-companion' ),
               5 => __( '5', 'vela-companion' ),
			   6 => __( '6', 'vela-companion' ),
			   7 => __( '7', 'vela-companion' ),
			   8 => __( '8', 'vela-companion' ),
			   9 => __( '9', 'vela-companion' ),
			   10 => __( '10', 'vela-companion' ),
			   11 => __( '11', 'vela-companion' ),
			   12 => __( '12', 'vela-companion' ),
            ]
         ]
      );
	  
	   $this->add_control(
         'columns',
         [
            'label' => __( 'Columns', 'vela-companion' ),
            'type' => Controls_Manager::SELECT,
            'default' => 3,
            'section' => 'section_blog_posts',
            'options' => [
               2 => __( '2', 'vela-companion' ),
               3 => __( '3', 'vela-companion' ),
               4 => __( '4', 'vela-companion' ),
              
            ]
         ]
      );
	  
	  
	  $this->start_controls_section(
			'section_design_layout',
			[
				'label' => __( 'Color', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'highlight_color',
			[
				'label' => __( 'Color', 'vela-companion' ),
				'separator' => 'before',
				'type' => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} a .elementor-portfolio-item__title' => 'color: {{VALUE}};',
				],
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);
	$this->end_controls_section();

   }

   protected function render( $instance = [] ) {

      // get our input from the widget settings.

		$settings = $this->get_settings();
		$post_count = ! empty( $settings['posts_per_page'] ) ? (int)$settings['posts_per_page'] : 6;
		$columns  = ! empty( $settings['columns'] ) ? $settings['columns'] : '3';


      ?>


<div class="cactus-e-post-list cactus-e-col-lg-<?php echo $columns ;?>">
             
<?php 
// the query
$args = array(
  'post_type'=>'post',
  'posts_per_page' => $post_count,
  'ignore_sticky_posts' => 1,
  'post_status' => array( 'publish' ),
);
$the_query = new \WP_Query( $args ); ?>

<?php if ( $the_query->have_posts() ) : ?>

	<?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
    
    
    
    <article class="cactus-e-post-item">
      <div class="cactus-e-post-inner">
          <div class="cactus-e-post-figure">
             <?php
						  if( has_post_thumbnail() ){
							  $featured_img_url = get_the_post_thumbnail_url(get_the_ID(),'full'); 
							  ?>
               <a href="<?php the_permalink();?>"><img src="<?php echo $featured_img_url;?>" alt="<?php the_title(); ?>" ></a>
                              
              <div class="cactus-e-post-date">
                  <span class="cactus-e-post-day"><?php echo get_the_time('d');?></span>
                  <span class="cactus-e-post-mon"><?php echo get_the_time('M');?></span>
              </div>
              <?php
						  }
						  ?>
              
          </div>
          <div class="cactus-e-post-content">
              <h3 class="cactus-e-post-title"><a href="<?php the_permalink();?>"><?php the_title(); ?></a></h3>
              <div class="cactus-e-post-summary">
                 <?php the_excerpt(); ?>
              </div>
              <div class="cactus-e-post-action">
                  <a href="<?php the_permalink();?>"><?php _e( 'Read More', 'vela-companion' );?></a>
              </div>
          </div>    
      </div>                                                
  </article>

        
	<?php endwhile; ?>

	<?php wp_reset_postdata(); ?>

<?php endif; ?>
                  
              </div>

<?php

   }

   protected function content_template() {}

   public function render_plain_content( $instance = [] ) {}

}
Plugin::instance()->widgets_manager->register_widget_type( new Vela_Widget_Latest_News );
