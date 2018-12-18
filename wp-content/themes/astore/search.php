<?php
	get_header();
	$page_sidebar_layout = astore_option('blog_archives_sidebar_layout');
	switch($page_sidebar_layout){
		case 'left':
			$aside_class = 'left-aside';
		break;
		case 'right':
			$aside_class = 'right-aside';
		break;
		default:
			$aside_class = 'no-aside';
		break;
		
		};
?>

 <!--Main Area-->
        
        <section class="page-title-bar title-left">
            <div class="container">
              <?php astore_breadcrumbs();?>
                    
                <div class="clearfix"></div>            
            </div>
        </section>
        <div class="page-wrap">
            <div class="container">
                <div class="page-inner row <?php echo $aside_class; ?>">
                    <div class="col-main">
                        <section class="page-main" role="main" id="content">
                            <div class="page-content">
                                <!--blog list begin-->
                                <div class="blog-list-wrap">
                                
             <?php
			if ( have_posts() ) :

				while ( have_posts() ) : the_post();

					get_template_part( 'template-parts/post/content', 'search' );

				endwhile;

				the_posts_pagination( array(
					'prev_text' => '<i class="fa fa-arrow-left"></i><span class="screen-reader-text">' . __( 'Previous page', 'astore' ) . '</span>',
					'next_text' => '<span class="screen-reader-text">' . __( 'Next page', 'astore' ) . '</span><i class="fa fa-arrow-right"></i>' ,
					'before_page_number' => '<span class="meta-nav screen-reader-text">' . __( 'Page', 'astore' ) . ' </span>',
				) );

			else :

				get_template_part( 'template-parts/post/content', 'none' );

			endif;
			?>         
                                    
                                    
                                </div>
                                <!--blog list end-->
                 
                            </div>
                            <div class="post-attributes"></div>
                        </section>
                    </div>
                     <?php astore_get_sidebar($page_sidebar_layout,'archives');?>
                </div>
            </div>  
        </div>

<?php get_footer();
