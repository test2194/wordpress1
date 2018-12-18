<div id="post-<?php the_ID(); ?>" <?php post_class('entry-box-wrap'); ?>>
  <article class="entry-box">
    <div class="entry-header no-img">
      <div class="entry-category"><?php echo get_the_category_list(', ');?></div>
      <?php 
		  if ( is_single() ) {
				the_title( '<h1 class="entry-title">', '</h1>' );
			} else {
				the_title( '<h1 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h1>' );
			}
			
			if ( 'post' === get_post_type() ) :
				echo '<div class="entry-meta">';
					if ( is_single() ) :
						astore_posted_on();
					else :
						echo astore_time_link();
						astore_edit_link();
					endif;
				echo '</div><!-- .entry-meta -->';
			endif;

		  ?>
    </div>
    <div class="entry-main">
      <div class="entry-summary">
        <?php 
			if ( is_single() ) {
			the_content( );
		} else {
			the_excerpt();
		}
			?>
        <?php
		  
	$args  = array(
		'before'           => '<p>' . esc_attr__( 'Pages:', 'astore' ),
		'after'            => '</p>',
		'link_before'      => '',
		'link_after'       => '',
		'next_or_number'   => 'number',
		'separator'        => ' ',
		'nextpagelink'     => esc_attr__( 'Next page', 'astore' ),
		'previouspagelink' => esc_attr__( 'Previous page', 'astore' ),
		'pagelink'         => '%',
		'echo'             => 1
	);
 
	wp_link_pages( $args  );
		
	?>
      </div>
    </div>
    <?php if ( !is_single() ) { ?>
    <div class="entry-footer clearfix">
      <div class="pull-left">
        <div class="entry-more"><a href="<?php the_permalink(); ?>">
          <?php esc_attr_e('Continue Reading...', 'astore');?>
          </a></div>
      </div>
      <div class="pull-right">
        <div class="entry-comments">
          <?php
		  if ( comments_open() ) :
			
			 comments_popup_link( esc_attr__( 'No comments yet', 'astore' ), esc_attr__( '1 comment', 'astore' ), esc_attr__( '% comments', 'astore' ), 'comments-link', '');
			
		  endif;
		  ?>
        </div>
      </div>
    </div>
    <?php } ?>
  </article>
</div>

<!-- #post-## --> 
