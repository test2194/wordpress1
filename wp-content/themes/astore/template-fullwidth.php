<?php
/**
 * Template Name: Full Width
 */
	get_header();

?>
<div class="page-wrap">
 <?php do_action('astore_before_page_wrap');?>  
  <div class="container-fullwidth">
          <article class="post-entry text-left">
            <?php do_action('astore_before_page_content');?>
            <?php
				/* Start the Loop */
				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/page/content' );

					the_posts_pagination( array(
					'prev_text' => '<i class="fa fa-arrow-left"></i><span class="screen-reader-text">' . __( 'Previous page', 'astore' ) . '</span>',
					'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'astore' ) . '</span><i class="fa fa-arrow-right"></i>' ,
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'astore' ) . ' </span>',
				) );

				endwhile; // End of the loop.
			?>
            <?php do_action('astore_after_page_content');?>               
          </article>
           <?php if ( comments_open() || get_comments_number()) :?>
          <div class="post-attributes">
         <!--Comments Area-->
            <div class="comments-area text-left">
              <?php

						comments_template();
			  ?>
            </div>            
          </div>
          <?php endif;?>
        </section>
      </div>
    </div>

<?php get_footer();