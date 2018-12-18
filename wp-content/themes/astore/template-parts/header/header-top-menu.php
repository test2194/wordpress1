<?php
   wp_nav_menu( array(
	'theme_location' => 'top',
	'menu_id'        => 'top-menu',
	'menu_class' => 'cactus-main-nav',
	'container' =>'',
	'fallback_cb' => false,
	'link_before' => '<span>',
	'link_after' => '</span>',
) );
