<?php

	$footer_icons = astore_option('footer_icons');
	$display_footer_icons = astore_option('display_footer_icons');
	$copyright = astore_option('copyright');
?>
<div class="footer-info-area text-center">
                    <ul class="footer-sns">
                      <?php 
						if($footer_icons && $display_footer_icons == '1'){
							foreach ($footer_icons as $item ){
								$item['icon'] = str_replace('fa-','',$item['icon']);
								$item['icon'] = str_replace('fa ','',$item['icon']);
						?>
						<li><a href="<?php echo esc_url($item['link']);?>" title="<?php echo esc_attr($item['title']);?>" target="_blank"><i class="fa fa-<?php echo esc_attr($item['icon']);?>"></i></a></li>
						<?php 
							}
						}
						?>
                    </ul>
                    <div class="site-info">
                       <span class="copyright_selective"><?php echo do_shortcode(wp_kses_post($copyright));?></span> 
	<?php printf(__('Designed by <a href="%s" target="_blank">VelaThemes</a>.','astore'),esc_url('https://velathemes.com/astore-theme/')); ?>
                    </div>
                </div>