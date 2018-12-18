<?php
	$categories_menu_toggle_text = astore_option('categories_menu_toggle_text');
?>
	<div class="cactus-cate-menu-wrap">
		<div class="cactus-cate-menu-toggle"><i class="fa fa-list-ul"></i> 
		<?php echo esc_attr($categories_menu_toggle_text);?>
		</div>
		<?php
			global $post;

			$postid = isset($post->ID)?$post->ID:0;
			if(is_home()){
				$postid = get_option( 'page_for_posts' );
			}

			$expand_menu = get_post_meta($postid , 'astore_expand_menu', true);
			
			
			$expand_menu = apply_filters( 'astore_expand_menu', $expand_menu );

			$menu_class = 'cactus-cate-menu';
			if ( $expand_menu == '1' || $expand_menu == 'on' )
				$menu_class .= ' show';
		?>
		<div class="<?php echo $menu_class;?>" style="display:none;">
		
		 <?php

		   wp_nav_menu( array(
			'theme_location' => 'browse-categories',
			'menu_id'        => 'browse-categories',
			'menu_class' => 'cactus-cate-nav',
			'fallback_cb'    => false,
			'container' =>'',
			'link_before' => '<span>',
			'link_after' => '</span>',
		) );

	?>
		</div>
	</div>