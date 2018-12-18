<?php 
    $entry_class = 'entry-box-wrap';
    if ( '' !== get_the_post_thumbnail() )
    	$entry_class .= ' has-post-thumbnail';
	
	$excerpt_style = astore_option('excerpt_style');
	
	$display_feature_image = astore_option('excerpt_display_feature_image');
	$display_category = astore_option('excerpt_display_category');
	$display_author = astore_option('excerpt_display_author');
	$display_date = astore_option('excerpt_display_date');
	
	if (is_single()){
		$display_feature_image = astore_option('display_feature_image');
		$display_category = astore_option('display_category');
		$display_author = astore_option('display_author');
		$display_date = astore_option('display_date');
	}
	
?>
<div id="post-<?php the_ID(); ?>" <?php post_class($entry_class); ?>>
      <article class="entry-box">
      <?php 
    	if ( '' !== get_the_post_thumbnail() && $display_feature_image == '1' ) : 
    ?>
          <div class="entry-image">
              <div class="img-box figcaption-middle text-center fade-in">
              <?php if (is_single()):?>
              <?php the_post_thumbnail( 'cactus-featured-image' ); ?>
              <?php else:?>
                  <a href="<?php the_permalink(); ?>">
                    <?php the_post_thumbnail( 'cactus-featured-image' ); ?>
                      <div class="img-overlay">
                          <div class="img-overlay-container">
                              <div class="img-overlay-content">
                                  <i class="fa fa-link"></i>
                              </div>
                          </div>
                      </div>
                  </a>
                  <?php endif ;?>
              </div>
          </div>
          <?php endif; ?>
          <div class="entry-main">
              <div class="entry-header">
              <?php if($display_category == '1' ):?>
                  <div class="entry-category"><?php the_category(', '); ?></div>
                  <?php endif; ?>
                  <a href="<?php the_permalink(); ?>"><h1 class="entry-title"><?php the_title(); ?></h1></a>
                  <div class="entry-meta">
                   <?php if($display_date == '1' ):?>
                      <span class="entry-date updated"><a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m'));?>"><?php echo get_the_date("M d, Y");?></a></span> 
                      <?php endif; ?>
                      <?php if($display_author == '1' ):?>
                      | <span class="entry-author author vcard"><?php esc_attr_e('By' ,'astore');?> <span class="fn"><?php echo get_the_author_link();?></span></span>
                      <?php endif; ?>
                  </div>
              </div>
              <div class="entry-summary">
            
   <?php 
            if ( is_single() || $excerpt_style == '1' ) {
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
         <?php if ( !is_single() ) : ?>
              <div class="entry-footer clearfix">
                  <div class="pull-left">
                      <div class="entry-more"><a href="<?php the_permalink(); ?>"><?php esc_attr_e('Continue Reading...', 'astore');?></a></div>
                  </div>
                  <div class="pull-right">
                      <div class="entry-comments"> <?php
              if ( comments_open() ) :
                
                comments_popup_link( esc_attr__( 'No comments yet', 'astore' ), esc_attr__( '1 comment', 'astore' ), esc_attr__( '% comments', 'astore' ), 'comments-link', '');
                
              endif;
              ?></div>
                  </div>
              </div>
              <?php endif; ?>
          </div>                                            
      </article>
  </div>