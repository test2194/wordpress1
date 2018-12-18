<?php

$preview_url = add_query_arg( 'vela_sites', '', home_url() );

$html = '';

if ( is_array( $sites_array ) ) {
	$html .= '<div class="vela-template-dir wrap">';
	$html .= '<h1 class="wp-heading-inline">' . __( 'Vela Sites Directory', 'vela-companion' ) . '</h1>';
	$html .= '<div class="vela-template-browser">';

	foreach ( $sites_array as $site => $properties ) {
		$html .= '<div class="vela-template">';
		$html .= '<div class="more-details vela-preview-site" data-demo-url="' . esc_url( $properties['demo'] ) . '" data-site-slug="' . esc_attr( $site ) . '" data-template-slug="' . esc_attr( $site ) . '" ><span>' . __( 'More Details', 'vela-companion' ) . '</span></div>';
		$html .= '<div class="vela-template-screenshot">';
		$html .= '<img src="' . esc_url( $properties['screenshot'] ) . '" alt="' . esc_html( $properties['title'] ) . '" >';
		$html .= '</div>'; // .vela-template-screenshot
		$html .= '<h2 class="template-name template-header">' . esc_html( $properties['title'] ) . (isset($properties['pro'] )&&$properties['pro']=='1'? apply_filters('vela_after_template_title','<span class="pro-template">Pro</span>'):'').'</h2>';
		$html .= '<div class="vela-template-actions">';

		if ( ! empty( $properties['demo'] ) ) {
			$html .= '<a class="button vela-preview-template" data-demo-url="' . esc_url( $properties['demo'] ) . '" data-site-slug="' . esc_attr( $site ) . '" >' . __( 'Preview', 'vela-companion' ) . '</a>';
		}
		$html .= '</div>'; // .vela-template-actions
		$html .= '</div>'; // .vela-template
	}
	$html .= '</div>'; // .vela-template-browser
	$html .= '</div>'; // .vela-template-dir
	$html .= '<div class="wp-clearfix clearfix"></div>';
}// End if().

echo $html;
?>

<div class="vela-template-preview theme-install-overlay wp-full-overlay expanded" style="display: none;">
	<div class="wp-full-overlay-sidebar">
		<div class="wp-full-overlay-header">
			<button class="close-full-overlay"><span class="screen-reader-text"><?php _e( 'Close', 'vela-companion' );?></span></button>
			<div class="vela-next-prev">
				<button class="previous-theme"><span class="screen-reader-text"><?php _e( 'Previous', 'vela-companion' );?></span></button>
				<button class="next-theme"><span class="screen-reader-text"><?php _e( 'Next', 'vela-companion' );?></span></button>
			</div>
            
			<span class="vela-import-button vela-import-site button button-primary"><?php _e( 'Import Site', 'vela-companion' );?></span>
  
           
            <a target="_blank" class="vela-buy-now" href="<?php echo esc_url('https://velathemes.com/cactus-pro-theme/');?>"><span class="button orange"><?php _e( 'Buy Now', 'vela-companion' );?></span></a>
            
		</div>
		<div class="wp-full-overlay-sidebar-content">
			<?php
			foreach ( $sites_array as $site => $properties ) {
			?>
				<div class="install-theme-info vela-theme-info <?php echo esc_attr( $site ); ?>"
					 data-demo-url="<?php echo esc_url( $properties['demo'] ); ?>"
					 data-site-wxr="<?php echo esc_url( $properties['wxr'] ); ?>"
					 data-site-title="<?php echo esc_attr( $properties['title'] ); ?>" 
                     data-site-slug="<?php echo esc_attr( $site ); ?>" 
                     data-template-slug="<?php echo esc_attr( $site ); ?>" 
                     data-site-options="<?php echo esc_html( $properties['options'] ); ?>" 
                     data-site-widgets="<?php echo esc_html( $properties['widgets'] ); ?>" 
                     data-site-customizer="<?php echo esc_html( $properties['customizer'] ); ?>" 
                     data-purchase-url="<?php echo isset($properties['purchase_url'])?esc_url( $properties['purchase_url'] ):''; ?>" 
                     >
					<h3 class="theme-name"><?php echo esc_attr( $properties['title'] ); ?></h3>
					<img class="theme-screenshot" src="<?php echo esc_url( $properties['screenshot'] ); ?>" alt="<?php echo esc_attr( $properties['title'] ); ?>">
					<div class="theme-details">
						<?php
						 	echo wp_kses_post( $properties['description'] );
						 ?>
					</div>
					<?php
					if ( ! empty( $properties['required_plugins'] ) && is_array( $properties['required_plugins'] ) ) {
					?>
					<div class="vela-required-plugins">
						<p><?php _e( 'Required Plugins', 'vela-companion' );?></p>
						<?php
						foreach ( $properties['required_plugins'] as $details ) {
							$file_name = isset($details['init'])?$details['init']:'';
							
							if ( VelaTemplater::check_plugin_state( $details['slug'],$file_name ) === 'install' ) {
								echo '<div class="vela-installable plugin-card-' . esc_attr( $details['slug'] ) . '">';
								echo '<span class="dashicons dashicons-no-alt"></span>';
								echo $details['name'];
								echo VelaTemplater::get_button_html( $details['slug'],$file_name );
								echo '</div>';
							} elseif ( VelaTemplater::check_plugin_state( $details['slug'],$file_name ) === 'activate' ) {
								echo '<div class="vela-activate plugin-card-' . esc_attr( $details['slug'] ) . '">';
								echo '<span class="dashicons dashicons-admin-plugins" style="color: #ffb227;"></span>';
								echo $details['name'];
								echo VelaTemplater::get_button_html( $details['slug'],$file_name );
								echo '</div>';
							} else {
								echo '<div class="vela-installed plugin-card-' . esc_attr( $details['slug'] ) . '">';
								echo '<span class="dashicons dashicons-yes" style="color: #34a85e"></span>';
								echo $details['name'];
								echo '</div>';
							}
						}
						?>
					</div>
					<?php
					}
					?>
				</div><!-- /.install-theme-info -->
			<?php } ?>
		</div>

		<div class="wp-full-overlay-footer">
			<button type="button" class="collapse-sidebar button" aria-expanded="true" aria-label="Collapse Sidebar">
				<span class="collapse-sidebar-arrow"></span>
				<span class="collapse-sidebar-label"><?php _e( 'Collapse', 'vela-companion' ); ?></span>
			</button>
			<div class="devices-wrapper">
				<div class="devices vela-responsive-preview">
					<button type="button" class="preview-desktop active" aria-pressed="true" data-device="desktop">
						<span class="screen-reader-text"><?php _e( 'Enter desktop preview mode', 'vela-companion' ); ?></span>
					</button>
					<button type="button" class="preview-tablet" aria-pressed="false" data-device="tablet">
						<span class="screen-reader-text"><?php _e( 'Enter tablet preview mode', 'vela-companion' ); ?></span>
					</button>
					<button type="button" class="preview-mobile" aria-pressed="false" data-device="mobile">
						<span class="screen-reader-text"><?php _e( 'Enter mobile preview mode', 'vela-companion' ); ?></span>
					</button>
				</div>
			</div>

		</div>
	</div>
	<div class="wp-full-overlay-main vela-main-preview">
		<iframe src="" title="Preview" class="vela-template-frame"></iframe>
	</div>
</div>
