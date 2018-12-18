<?php
namespace Elementor;

use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Scheme_Color;
use Elementor\Scheme_Typography;

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Class Portfolio
 */
class Vela_Portfolio extends Widget_Base {

	/**
	 * @var \WP_Query
	 */
	private $_query = null;

	protected $_has_template_content = false;
	
	public function get_categories() {
		return [ 'vela-elements' ];
	}

	public function get_name() {
		return 'vela-portfolio';
	}

	public function get_title() {
		return __( 'Portfolio', 'vela-companion' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_script_depends() {
		return [ 'imagesloaded' ];
	}

	public function on_import( $element ) {
		if ( ! get_post_type_object( $element['settings']['posts_post_type'] ) ) {
			$element['settings']['posts_post_type'] = 'post';
		}

		return $element;
	}



	public function get_query() {
		return $this->_query;
	}

	protected function _register_controls() {
		$this->register_query_section_controls();
	}

	private function register_query_section_controls() {
		$this->start_controls_section(
			'section_layout',
			[
				'label' => __( 'Layout', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_responsive_control(
			'columns',
			[
				'label' => __( 'Columns', 'vela-companion' ),
				'type' => Controls_Manager::SELECT,
				'default' => '3',
				'tablet_default' => '2',
				'mobile_default' => '1',
				'options' => [
					'1' => '1',
					'2' => '2',
					'3' => '3',
					'4' => '4',
					'5' => '5',
					'6' => '6',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'posts_per_page',
			[
				'label' => __( 'Posts Per Page', 'vela-companion' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 6,
			]
		);

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name' => 'thumbnail_size',
				'exclude' => [ 'custom' ],
				'default' => 'medium',
				'prefix_class' => 'elementor-portfolio--thumbnail-size-',
			]
		);

		$this->add_control(
			'masonry',
			[
				'label' => __( 'Masonry', 'vela-companion' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Off', 'vela-companion' ),
				'label_on' => __( 'On', 'vela-companion' ),
				'condition' => [
					'columns!' => '1',
				],
				'render_type' => 'ui',
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'item_ratio',
			[
				'label' => __( 'Item Ratio', 'vela-companion' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 0.66,
				],
				'range' => [
					'px' => [
						'min' => 0.1,
						'max' => 2,
						'step' => 0.01,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-post__thumbnail__link' => 'padding-bottom: calc( {{SIZE}} * 100% )',
					'{{WRAPPER}}:after' => 'content: "{{SIZE}}"; position: absolute; color: transparent;',
				],
				'condition' => [
					'masonry' => '',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'show_title',
			[
				'label' => __( 'Show Title', 'vela-companion' ),
				'type' => Controls_Manager::SWITCHER,
				'default' => 'yes',
				'label_off' => __( 'Off', 'vela-companion' ),
				'label_on' => __( 'On', 'vela-companion' ),
			]
		);

		$this->add_control(
			'title_tag',
			[
				'label' => __( 'Title HTML Tag', 'vela-companion' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'h1' => 'H1',
					'h2' => 'H2',
					'h3' => 'H3',
					'h4' => 'H4',
					'h5' => 'H5',
					'h6' => 'H6',
					'div' => 'div',
					'span' => 'span',
					'p' => 'p',
				],
				'default' => 'h3',
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_query',
			[
				'label' => __( 'Query', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);
		


		$post_type_args = [
			'show_in_nav_menus' => true,
		];

	

		$_post_types = get_post_types( $post_type_args , 'objects' );

		$post_types  = [];

		foreach ( $_post_types as $post_type => $object ) {
			$post_types[ $post_type ] = $object->label;
		}
		

		
		$this->add_control(
			'posts_post_type',
			[
				'label' => __( 'Source', 'vela-companion' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'post',
				'options' => $post_types,
			]
		);
		
	

		$this->add_control(
			'advanced',
			[
				'label' => __( 'Advanced', 'vela-companion' ),
				'type' => Controls_Manager::HEADING,
			]
		);

		$this->add_control(
			'orderby',
			[
				'label' => __( 'Order By', 'vela-companion' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'post_date',
				'options' => [
					'post_date' => __( 'Date', 'vela-companion' ),
					'post_title' => __( 'Title', 'vela-companion' ),
					'menu_order' => __( 'Menu Order', 'vela-companion' ),
					'rand' => __( 'Random', 'vela-companion' ),
				],
			]
		);

		$this->add_control(
			'order',
			[
				'label' => __( 'Order', 'vela-companion' ),
				'type' => Controls_Manager::SELECT,
				'default' => 'desc',
				'options' => [
					'asc' => __( 'ASC', 'vela-companion' ),
					'desc' => __( 'DESC', 'vela-companion' ),
				],
			]
		);

		$this->add_control(
			'offset',
			[
				'label' => __( 'Offset', 'vela-companion' ),
				'type' => Controls_Manager::NUMBER,
				'default' => 0,
				'condition' => [
					'posts_post_type!' => 'by_id',
				],
			]
		);

		//Module::add_exclude_controls( $this );

		$this->end_controls_section();

		$this->start_controls_section(
			'filter_bar',
			[
				'label' => __( 'Filter Bar', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'show_filter_bar',
			[
				'label' => __( 'Show', 'vela-companion' ),
				'type' => Controls_Manager::SWITCHER,
				'label_off' => __( 'Off', 'vela-companion' ),
				'label_on' => __( 'On', 'vela-companion' ),
			]
		);

		$this->add_control(
			'taxonomy',
			[
				'label' => __( 'Taxonomy', 'vela-companion' ),
				'type' => Controls_Manager::SELECT2,
				'label_block' => true,
				'default' => [],
				'options' => $this->get_taxonomies(),
				'condition' => [
					'show_filter_bar' => 'yes',
					'posts_post_type!' => 'by_id',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_design_layout',
			[
				'label' => __( 'Items', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'item_gap',
			[
				'label' => __( 'Item Gap', 'vela-companion' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio' => 'margin: 0 -{{SIZE}}px',
					'(desktop){{WRAPPER}} .elementor-portfolio-item' => 'width: calc( 100% / {{columns.SIZE}} ); border: {{SIZE}}px solid transparent',
					'(tablet){{WRAPPER}} .elementor-portfolio-item' => 'width: calc( 100% / {{columns_tablet.SIZE}} ); border: {{SIZE}}px solid transparent',
					'(mobile){{WRAPPER}} .elementor-portfolio-item' => 'width: calc( 100% / {{columns_mobile.SIZE}} ); border: {{SIZE}}px solid transparent',
				],
				'frontend_available' => true,
			]
		);

		$this->add_control(
			'border_radius',
			[
				'label' => __( 'Border Radius', 'vela-companion' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio-item__img, {{WRAPPER}} .elementor-portfolio-item__overlay' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_design_overlay',
			[
				'label' => __( 'Item Overlay', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'color_background',
			[
				'label' => __( 'Background Color', 'vela-companion' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_4,
				],
				'selectors' => [
					'{{WRAPPER}} a .elementor-portfolio-item__overlay' => 'background-color: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'color_title',
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

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography_title',
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-portfolio-item__title',
				'condition' => [
					'show_title' => 'yes',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_design_filter',
			[
				'label' => __( 'Filter Bar', 'vela-companion' ),
				'tab' => Controls_Manager::TAB_STYLE,
				'condition' => [
					'show_filter_bar' => 'yes',
				],
			]
		);

		$this->add_control(
			'color_filter',
			[
				'label' => __( 'Color', 'vela-companion' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_3,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio__filter' => 'color: {{VALUE}}',
				],
			]
		);

		$this->add_control(
			'color_filter_active',
			[
				'label' => __( 'Active Color', 'vela-companion' ),
				'type' => Controls_Manager::COLOR,
				'scheme' => [
					'type' => Scheme_Color::get_type(),
					'value' => Scheme_Color::COLOR_1,
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio__filter.elementor-active' => 'color: {{VALUE}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography_filter',
				'scheme' => Scheme_Typography::TYPOGRAPHY_1,
				'selector' => '{{WRAPPER}} .elementor-portfolio__filter',
			]
		);

		$this->add_control(
			'filter_item_spacing',
			[
				'label' => __( 'Space Between', 'vela-companion' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio__filter:not(:last-child)' => 'margin-right: calc({{SIZE}}{{UNIT}}/2)',
					'{{WRAPPER}} .elementor-portfolio__filter:not(:first-child)' => 'margin-left: calc({{SIZE}}{{UNIT}}/2)',
				],
			]
		);

		$this->add_control(
			'filter_spacing',
			[
				'label' => __( 'Spacing', 'vela-companion' ),
				'type' => Controls_Manager::SLIDER,
				'default' => [
					'size' => 10,
				],
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .elementor-portfolio__filters' => 'margin-bottom: {{SIZE}}{{UNIT}}',
				],
			]
		);

		$this->end_controls_section();
	}

	protected function get_taxonomies() {
		$taxonomies = get_taxonomies( [ 'show_in_nav_menus' => true ], 'objects' );

		$options = [ '' => '' ];

		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = $taxonomy->label;
		}

		return $options;
	}

	protected function get_posts_tags() {
		
		$taxonomy = $this->get_settings( 'taxonomy' );

		foreach ( $this->_query->posts as $post ) {
			if ( ! $taxonomy ) {
				$post->tags = [];

				continue;
			}

			$tags = wp_get_post_terms( $post->ID, $taxonomy );

			$tags_slugs = [];

			foreach ( $tags as $tag ) {
				if(isset($tag->term_id))
					$tags_slugs[ $tag->term_id ] = $tag;
			}

			$post->tags = $tags_slugs;
		}
	}

	public function query_posts() {
		$query_args = \VelaCompanion::get_query_args( 'posts', $this->get_settings() );

		$query_args['posts_per_page'] = $this->get_settings( 'posts_per_page' );

		$this->_query = new \WP_Query( $query_args );
	}

	public function render() {
		$this->query_posts();

		$wp_query = $this->get_query();

		if ( ! $wp_query->found_posts ) {
			return;
		}
		
		echo '<div class="cactus-e-port-wrap">';

		$this->get_posts_tags();

		$this->render_loop_header();

		while ( $wp_query->have_posts() ) {
			$wp_query->the_post();

			$this->render_post();
		}

		$this->render_loop_footer();

		wp_reset_postdata();
		
		echo "<script>
jQuery(window).load(function(e) {
	
	var containerEl = document.querySelector('.cactus-e-port-list');
    var mixer = mixitup(containerEl);
	jQuery('.cactus-e-port-filter').on( 'click', 'a', function() {
		jQuery(this).parents('.cactus-e-port-filter').find('li').removeClass('active');
		jQuery(this).parent('li').addClass('active');
	});

});

</script> ";
		echo '</div>';
	}

	protected function render_thumbnail() {
		$settings = $this->get_settings();

		$settings['thumbnail_size'] = [
			'id' => get_post_thumbnail_id(),
		];

		$thumbnail_html = Group_Control_Image_Size::get_attachment_image_html( $settings, 'thumbnail_size' );
		?>
		
			<?php echo $thumbnail_html ?>

		<?php
	}

	protected function render_filter_menu() {
		$taxonomy = $this->get_settings( 'taxonomy' );

		if ( ! $taxonomy ) {
			return;
		}

		$terms = [];

		foreach ( $this->_query->posts as $post ) {
			$terms += $post->tags;
		}

		if ( empty( $terms ) ) {
			return;
		}

		usort( $terms, function( $a, $b ) {
			return strcmp( $a->name, $b->name );
		} );

		?>
        <nav class="cactus-e-port-filter">
		<ul>
			<li class="active"><a href="javascript:;" class="control" data-filter="*"><?php echo __( 'All', 'vela-companion' ); ?></a></li>
			<?php foreach ( $terms as $term ) { ?>
				<li><a href="javascript:;" class="control" data-filter=".filter-<?php echo esc_attr( $term->term_id ); ?>"><?php echo $term->name; ?></a></li>
			<?php } ?>
		</ul>
        </nav>
		<?php
	}

	protected function render_title() {
		if ( ! $this->get_settings( 'show_title' ) ) {
			return;
		}

		$tag = $this->get_settings( 'title_tag' );
		?>
		<<?php echo $tag ?> class="cactus-e-port-title">
		<?php the_title() ?>
		</<?php echo $tag ?>>
		<?php
	}

	protected function render_categories_names() {
		global $post;

		if ( ! $post->tags ) {
			return;
		}

		$separator = '<span class="elementor-portfolio-item__tags__separator"></span>';

		$tags_array = [];

		foreach ( $post->tags as $tag ) {
			$tags_array[] = '<span class="elementor-portfolio-item__tags__tag">' . $tag->name . '</span>';
		}

		?>
		<div class="cactus-e-port-category">
			<?php echo implode( $separator, $tags_array ); ?>
		</div>
		<?php
	}

	protected function render_post_header() {
		global $post;

		$tags_classes = array_map( function( $tag ) {
			return 'filter-' . $tag->term_id;
		}, $post->tags );

		$classes = [
			'cactus-e-port-item',
			'mix',
			'element-item',
			'egrid-item',
			implode( ' ', $tags_classes ),
		];

		?>
		<article <?php post_class( $classes ); ?>>
        <div class="cactus-e-port-figure">
			
		<?php
	}

	protected function render_post_footer() {
		?>
		
        </div>
		</article>
		<?php
	}

	protected function render_overlay_header() {
		?>
		<div class="cactus-e-port-overlay">
        <a class="cactus-e-port-link" href="<?php echo get_permalink() ?>">
        <div class="cactus-e-port-caption">
		<?php
	}

	protected function render_overlay_footer() {
		?>
        </div>
        </a>
        <a href="<?php echo get_permalink() ?>#zoomout" class="cactus-e-port-zoom"></a>
		</div>
		<?php
	}

	protected function render_loop_header() {
		if ( $this->get_settings( 'show_filter_bar' ) ) {
			$this->render_filter_menu();
		}
		
		$columns = $this->get_settings( 'columns' );
		if ( ! $this->get_settings( 'columns' ) ) {
			$columns = 4;
		}
		
		?>
		 <div class="cactus-e-port-list cactus-e-col-lg-<?php echo $columns; ?> full">
		<?php
	}

	protected function render_loop_footer() {
		?>
		</div>
		<?php
	}

	protected function render_post() {
		$this->render_post_header();
		$this->render_thumbnail();
		$this->render_overlay_header();
		$this->render_categories_names();
		$this->render_title();
		// $this->render_categories_names();
		$this->render_overlay_footer();
		$this->render_post_footer();
	}

	public function render_plain_content() {}
}
Plugin::instance()->widgets_manager->register_widget_type( new Vela_Portfolio );