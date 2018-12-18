(function( $ ) {
	
	// Add Color Picker to all inputs that have 'color-field' class
    $(function() {
        $('.wp-color-picker').wpColorPicker();
    });
	
	// Handle sidebar collapse in preview.
	$( '.vela-template-preview' ).on(
		'click', '.collapse-sidebar', function () {
			event.preventDefault();
			var overlay = $( '.vela-template-preview' );
			if ( overlay.hasClass( 'expanded' ) ) {
				overlay.removeClass( 'expanded' );
				overlay.addClass( 'collapsed' );
				return false;
			}

			if ( overlay.hasClass( 'collapsed' ) ) {
				overlay.removeClass( 'collapsed' );
				overlay.addClass( 'expanded' );
				return false;
			}
		}
	);

	// Handle responsive buttons.
	$( '.vela-responsive-preview' ).on(
		'click', 'button', function () {
			$( '.vela-template-preview' ).removeClass( 'preview-mobile preview-tablet preview-desktop' );
			var deviceClass = 'preview-' + $( this ).data( 'device' );
			$( '.vela-responsive-preview button' ).each(
				function () {
					$( this ).attr( 'aria-pressed', 'false' );
					$( this ).removeClass( 'active' );
				}
			);

			$( '.vela-responsive-preview' ).removeClass( $( this ).attr( 'class' ).split( ' ' ).pop() );
			$( '.vela-template-preview' ).addClass( deviceClass );
			$( this ).addClass( 'active' );
		}
	);

	// Hide preview.
	$( '.close-full-overlay' ).on(
		'click', function () {
			$( '.vela-template-preview .vela-theme-info.active' ).removeClass( 'active' );
			$( '.vela-template-preview' ).hide();
			$( '.vela-template-frame' ).attr( 'src', '' );
			$('body.vela-companion_page_vela-template').css({'overflow-y':'auto'});
		}
	);
			
	
	// Open preview routine.
	$( '.vela-preview-template' ).on(
		'click', function () {
			$('.import-return-info').remove();
			var templateSlug = $( this ).data( 'template-slug' );
			var previewUrl = $( this ).data( 'demo-url' );
			$( '.vela-template-frame' ).attr( 'src', previewUrl );
			$( '.vela-theme-info.' + templateSlug ).addClass( 'active' );
			setupImportButton();
			$( '.vela-template-preview' ).fadeIn();
			$('body.vela-companion_page_vela-template').css({'overflow-y':'hidden'});
		}
	);
	
	$(document).on('click', '.vela-preview-site',
		 function () {
			$('.import-return-info').remove();
			var siteSlug = $( this ).data( 'site-slug' );
			var previewUrl = $( this ).data( 'demo-url' );
			$( '.vela-template-frame' ).attr( 'src', previewUrl );
			$( '.vela-theme-info.' + siteSlug ).addClass( 'active' );
			setupImportSiteButton();
			$( '.vela-template-preview' ).fadeIn();
			$('body.vela-companion_page_vela-template').css({'overflow-y':'hidden'});
		}
	);
	
	
	$( '.vela-next-prev .next-theme' ).on(
				'click', function () {
					var active = $( '.vela-theme-info.active' ).removeClass( 'active' );
					if ( active.next() && active.next().length ) {
						active.next().addClass( 'active' );
					} else {
						active.siblings( ':first' ).addClass( 'active' );
					}
					changePreviewSource();
					setupImportButton();
				}
			);
			$( '.vela-next-prev .previous-theme' ).on(
				'click', function () {
					var active = $( '.vela-theme-info.active' ).removeClass( 'active' );
					if ( active.prev() && active.prev().length ) {
						active.prev().addClass( 'active' );
					} else {
						active.siblings( ':last' ).addClass( 'active' );
					}
					changePreviewSource();
					setupImportButton();
				}
			);

			// Change preview source.
			function changePreviewSource() {
				var previewUrl = $( '.vela-theme-info.active' ).data( 'demo-url' );
				$( '.vela-template-frame' ).attr( 'src', previewUrl );
			}
	
	function setupImportButton() {
		var installable = $( '.active .vela-installable' );
		if ( installable.length > 0 ) {
			$( '.wp-full-overlay-header .vela-import-template' ).text( vela_companion_admin.i18n.t1 );
		} else {
			$( '.wp-full-overlay-header .vela-import-template' ).text( vela_companion_admin.i18n.t2 );
		}
		var activeTheme = $( '.vela-theme-info.active' );
		var button = $( '.wp-full-overlay-header .vela-import-template' );
		$( button ).attr( 'data-template-file', $( activeTheme ).data( 'template-file' ) );
		$( button ).attr( 'data-template-title', $( activeTheme ).data( 'template-title' ) );
		$( button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
		
		if($( activeTheme ).data( 'template-file' ) == '' ){
				$('.vela-buy-now').show();
				$('.vela-import-template').hide();
				if($( activeTheme ).data( 'purchase-url' ) != '' )
					$('.vela-buy-now').attr('href', $( activeTheme ).data( 'purchase-url' ) );
			}else{
				$('.vela-buy-now').hide();
				$('.vela-import-template').show();
				}
	}
	
	function setupImportSiteButton() {
		var installable = $( '.active .vela-installable' );
		
		$('.vela-import-button').addClass('vela-import-site');
		if ( installable.length > 0 ) {
			$( '.wp-full-overlay-header .vela-import-site' ).text( vela_companion_admin.i18n.t3 );
		} else {
			$( '.wp-full-overlay-header .vela-import-site' ).text( vela_companion_admin.i18n.t4 );
		}
		var activeTheme = $( '.vela-theme-info.active' );
		var button = $( '.wp-full-overlay-header .vela-import-site' );
		$( button ).attr( 'data-demo-url', $( activeTheme ).data( 'demo-url' ) );
		$( button ).attr( 'data-site-wxr', $( activeTheme ).data( 'site-wxr' ) );
		$( button ).attr( 'data-site-title', $( activeTheme ).data( 'site-title' ) );
		$( button ).attr( 'data-site-slug', $( activeTheme ).data( 'site-slug' ) );
		
		$( button ).attr( 'data-template-slug', $( activeTheme ).data( 'template-slug' ) );
		$( button ).attr( 'data-site-options', $( activeTheme ).data( 'site-options' ) );
		$( button ).attr( 'data-site-widgets', $( activeTheme ).data( 'site-widgets' ) );
		$( button ).attr( 'data-site-customizer', $( activeTheme ).data( 'site-customizer' ) );
							 
		
		if($( activeTheme ).data( 'site-wxr' ) == '' ){
				$('.vela-buy-now').show();
				$('.vela-import-site').hide();
				if($( activeTheme ).data( 'purchase-url' ) != '' )
					$('.vela-buy-now').attr('href', $( activeTheme ).data( 'purchase-url' ) );
					
			}else{
				$('.vela-buy-now').hide();
				$('.vela-import-site').show();
				$( activeTheme ).find('.hide-in-pro').hide();
				}
	}
	
	
	// Handle import click.
	$( '.wp-full-overlay-header' ).on(
		'click', '.vela-import-template', function () {
			$( this ).addClass( 'vela-import-queue updating-message vela-updating' ).html( '' );
			$( '.vela-template-preview .close-full-overlay, .vela-next-prev' ).remove();
			var template_url = $( this ).data( 'template-file' );
			var template_name = $( this ).data( 'template-title' );
			var template_slug = $( this ).data( 'template-slug' );
			
			if ( $( '.active .vela-installable' ).length || $( '.active .vela-activate' ).length ) {

				checkAndInstallPlugins();
			} else {
				$.ajax(
					{
						url: vela_companion_admin.ajaxurl,
						beforeSend: function ( xhr ) {
							$( '.vela-import-queue' ).addClass( 'vela-updating' ).html( '' );
							xhr.setRequestHeader( 'X-WP-Nonce', vela_companion_admin.nonce );
						},
						// async: false,
						data: {
							template_url: template_url,
							template_name: template_name,
							template_slug: template_slug,
							action: 'vela_import_elementor'
						},
					//	dataType:"json",
						type: 'POST',
						success: function ( data ) {
							console.log( 'success' );
							console.log( data );
							$( '.vela-updating' ).replaceWith( '<span class="vela-done-import"><i class="dashicons-yes dashicons"></i></span>' );
							var obj = $.parseJSON( data );
							
							location.href = obj.redirect_url;
						},
						error: function ( error ) {
							console.log( 'error' );
							console.log( error );
						},
						complete: function() {
							$( '.vela-updating' ).replaceWith( '<span class="vela-done-import"><i class="dashicons-yes dashicons"></i></span>' );
						}
					}, 'json'
				);
			}
		}
	);

	function checkAndInstallPlugins() {
		var installable = $( '.active .vela-installable' );
		var toActivate = $( '.active .vela-activate' );
		if ( installable.length || toActivate.length ) {

			$( installable ).each(
				function () {
					var plugin = $( this );
					$( plugin ).removeClass( 'vela-installable' ).addClass( 'vela-installing' );
					$( plugin ).find( 'span.dashicons' ).replaceWith( '<span class="dashicons dashicons-update" style="-webkit-animation: rotation 2s infinite linear; animation: rotation 2s infinite linear; color: #ffb227 "></span>' );
					var slug = $( this ).find( '.vela-install-plugin' ).attr( 'data-slug' );
					
					if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
						  wp.updates.requestFilesystemCredentials( event );
		  
						  $document.on( 'credential-modal-cancel', function() {
							  var $message = $( '.install-now.vela-installing' );
		  
							  $message
								  .removeClass( 'vela-installing' )
								  .text( wp.updates.l10n.installNow );
		  
							  wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
						  } );
					  }
					  
					wp.updates.installPlugin(
						{
							slug: slug,
							success: function ( response ) {
								activatePlugin( response.activateUrl, plugin );
							}
						}
					);
				}
			);

			$( toActivate ).each(
				function () {
						var plugin = $( this );
						var activateUrl = $( plugin ).find( '.activate-now' ).attr( 'href' );
					if (typeof activateUrl !== 'undefined') {
						activatePlugin( activateUrl, plugin );
					}
				}
			);
		}
	}

	function activatePlugin( activationUrl, plugin ) {
		$.ajax(
			{
				type: 'GET',
				url: activationUrl,
				beforeSend: function() {
					$( plugin ).removeClass( 'vela-activate' ).addClass( 'vela-installing' );
					$( plugin ).find( 'span.dashicons' ).replaceWith( '<span class="dashicons dashicons-update" style="-webkit-animation: rotation 2s infinite linear; animation: rotation 2s infinite linear; color: #ffb227 "></span>' );
					$( plugin ).find( '.activate-now' ).removeClass('activate-now  button-primary').addClass('button-activatting button-secondary').text('Activating').attr('href','#');
				},
				success: function () {
					$( plugin ).find( '.dashicons' ).replaceWith( '<span class="dashicons dashicons-yes" style="color: #34a85e"></span>' );
					$( plugin ).find( '.button-activatting' ).text('Activated');
					$( plugin ).removeClass( 'vela-installing' );
				},
				complete: function() {
					if ( $( '.active .vela-installing' ).length === 0 ) {
						$( '.vela-import-queue' ).trigger( 'click' );
					}
				}
			}
		);
	}
	
	
     
})( jQuery );