(function($){
	
	var VelaSSEImport = {
		complete: {
			posts: 0,
			media: 0,
			users: 0,
			comments: 0,
			terms: 0,
		},

		updateDelta: function (type, delta) {
			this.complete[ type ] += delta;

			var self = this;
			requestAnimationFrame(function () {
				self.render();
			});
		},
		updateProgress: function ( type, complete, total ) {
			var text = complete + '/' + total;

			if( 'undefined' !== type && 'undefined' !== text ) {
				total = parseInt( total, 10 );
				if ( 0 === total || isNaN( total ) ) {
					total = 1;
				}
				var percent = parseInt( complete, 10 ) / total;
				var progress     = Math.round( percent * 100 ) + '%';
				var progress_bar = percent * 100;
			}
		},
		render: function () {
			var types = Object.keys( this.complete );
			var complete = 0;
			var total = 0;

			for (var i = types.length - 1; i >= 0; i--) {
				var type = types[i];
				this.updateProgress( type, this.complete[ type ], this.data.count[ type ] );

				complete += this.complete[ type ];
				total += this.data.count[ type ];
			}

			this.updateProgress( 'total', complete, total );
		}
	};
	var VelaImporter = {
	
		customizer_data : '',
		wxr_url         : '',
		options_data    : '',
		widgets_data    : '',

		init: function()
		{
			this._bind();
		},
		_bind:function(){
			$( document ).on('click' , '.vela-import-site', VelaImporter._importDemo);
			$( document ).on('click' , '.vela-import-wxr', VelaImporter._importPrepareXML);
			$( document ).on('click' , '.vela-import-options', VelaImporter._importSiteOptions);
			$( document ).on('click' , '.vela-import-widgets', VelaImporter._importWidgets);
			$( document ).on('click' , '.vela-sites-import-done', VelaImporter._importEnd);
			
			},
		_importDemo:function(){
			
			if( ! confirm(  velaSiteImporter.i18n.s0 ) ) {
				return;
			}
			var wrap = $('.vela-theme-info.active');
			VelaImporter.site = wrap.data('site-slug');
			VelaImporter.wxr_url = wrap.data('site-wxr');
			VelaImporter.options_data = wrap.data('site-options');
			VelaImporter.customizer_data = wrap.data('site-customizer');
			VelaImporter.widgets_data = wrap.data('site-widgets');
			if ( $( '.active .vela-installable' ).length || $( '.active .vela-activate' ).length ) {

				VelaImporter.checkAndInstallPlugins();
			} else {
				VelaImporter._importCustomizerSettings();
			}
			
			},
		/**
		 * 1. Import Customizer Options.
		 */
		_importCustomizerSettings: function( event ) {

			$.ajax({
				url  : vela_companion_admin.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					action          : 'vela-sites-import-customizer-settings',
					customizer_data : VelaImporter.customizer_data,
				},
				beforeSend: function() {
					$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s1+'</div>');
					$('.vela-import-site').text( velaSiteImporter.i18n.s1 );
				},
			})
			.fail(function( jqXHR ){
				$('.vela-import-site').text( velaSiteImporter.i18n.s2 );
		    })
			.done(function ( customizer_data ) {

				// 1. Fail - Import Customizer Options.
				if( false === customizer_data.success ) {
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+customizer_data.data+'</div>');
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+velaSiteImporter.i18n.s2+'</div>');
					$('.vela-import-site').text( velaSiteImporter.i18n.s2 );
				} else {
					
					// 1. Pass - Import Customizer Options.
					$('.vela-import-site').text( velaSiteImporter.i18n.s3 );
					$('.vela-theme-info').append('<div class="import-return-info notice-success">'+velaSiteImporter.i18n.s3+'</div>');
					
					$('.vela-import-site').removeClass( 'vela-import-site' ).addClass('vela-import-wxr vela-sites-import-customizer-settings-done');

					$(document).trigger( 'vela-sites-import-customizer-settings-done' );
					$( ".vela-import-wxr" ).trigger( "click" );
				}
			});
		},
		
		/**
		 * 2. Prepare XML Data.
		 */
		_importPrepareXML: function( event ) {

			$.ajax({
				url  : vela_companion_admin.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					action  : 'vela-sites-import-wxr',
					wxr_url : VelaImporter.wxr_url,
				},
				beforeSend: function() {
					$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s4+'</div>');
					$('.vela-import-wxr').text( velaSiteImporter.i18n.s4 );
				},
			})
			.fail(function( jqXHR ){
				
				$('.vela-theme-info').append('<div class="import-return-info notice-error">'+jqXHR.status + ' ' + jqXHR.responseText+'</div>');
		    })
			.done(function ( xml_data ) {

				// 2. Fail - Prepare XML Data.
				if( false === xml_data.success ) {
					
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+velaSiteImporter.i18n.s5+'</div>');
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+xml_data.data+'</div>');
					
					
				} 
					
					// 2. Pass - Prepare XML Data.
					// Import XML though Event Source.
					VelaSSEImport.data = xml_data.data;
					VelaSSEImport.render();
					
					$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s6_1+'</div>');
					$('.vela-import-wxr').text( velaSiteImporter.i18n.s6 );
										
					var evtSource = new EventSource( VelaSSEImport.data.url );
					evtSource.onmessage = function ( message ) {
						var data = JSON.parse( message.data );
						switch ( data.action ) {
							case 'updateDelta':
									VelaSSEImport.updateDelta( data.type, data.delta );
								break;

							case 'complete':
								evtSource.close();

								// 2. Pass - Import XML though "Source Event".
								$('.vela-import-wxr').text( velaSiteImporter.i18n.s7 );
								$('.vela-theme-info').append('<div class="import-return-info notice-success">'+velaSiteImporter.i18n.s7+'</div>');
								
								$('.vela-import-wxr').removeClass( 'vela-import-wxr' ).addClass('vela-import-options vela-sites-import-xml-done');
								
								$(document).trigger( 'vela-sites-import-xml-done' );
								
								$( ".vela-import-options" ).trigger( "click" );
								
								

								break;
						}
					};
					evtSource.addEventListener( 'log', function ( message ) {
						var data = JSON.parse( message.data );
						if( data.level !== 'warning' ){
							$('.vela-theme-info').append( "<p class='import-return-info'>" + data.level + ': ' + data.message + "</p>" );
						}
					});	
					
			});
		},
		
		/**
		 * 3. Import Site Options.
		 */
		_importSiteOptions: function( event ) {

			$.ajax({
				url  : vela_companion_admin.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					action       : 'vela-sites-import-options',
					options_data : VelaImporter.options_data,
				},
				beforeSend: function() {
					$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s8+'</div>');
					$('.vela-import-options').text( velaSiteImporter.i18n.s8 );
				},
			})
			.fail(function( jqXHR ){
				$('.vela-theme-info').append('<div class="import-return-info notice-error">'+jqXHR.status + ' ' + jqXHR.responseText+'</div>');
				$('.vela-import-options').text( velaSiteImporter.i18n.s9 );
		    })
			.done(function ( options_data ) {

				// 3. Fail - Import Site Options.
				if( false === options_data.success ) {
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+velaSiteImporter.i18n.s9+'</div>');
					$('.vela-import-options').text( velaSiteImporter.i18n.s9 );

				} else {

					// 3. Pass - Import Site Options.
					$('.vela-theme-info').append('<div class="import-return-info notice-success">'+ velaSiteImporter.i18n.s10 +'</div>');
					$('.vela-import-options').text( velaSiteImporter.i18n.s10 );
					
					$('.vela-import-options').removeClass( 'vela-import-options' ).addClass('vela-import-widgets vela-sites-import-options-done');
					$(document).trigger( 'vela-sites-import-options-done' );
					$( ".vela-import-widgets" ).trigger( "click" );
				}
			});
		},
		
		/**
		 * 4. Import Widgets.
		 */
		_importWidgets: function( event ) {

			$.ajax({
				url  : vela_companion_admin.ajaxurl,
				type : 'POST',
				dataType: 'json',
				data : {
					action       : 'vela-sites-import-widgets',
					widgets_data : VelaImporter.widgets_data,
				},
				beforeSend: function() {
					$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s11+'</div>');
					$('.vela-import-widgets').text( velaSiteImporter.i18n.s11 );
				},
			})
			.fail(function( jqXHR ){
				//$('.vela-theme-info').append('<div class="import-return-info">'+velaSiteImporter.i18n.s11+'</div>');
				$('.vela-theme-info').append('<div class="import-return-info notice-error">'+jqXHR.status + ' ' + jqXHR.responseText+'</div>');
				$('.vela-import-widgets').text( velaSiteImporter.i18n.s12 );

		    })
			.done(function ( widgets_data ) {

				// 4. Fail - Import Widgets.
				if( false === widgets_data.success ) {
					$('.vela-import-widgets').text( velaSiteImporter.i18n.s12 );
					$('.vela-theme-info').append('<div class="import-return-info notice-error">'+widgets_data.data+'</div>');

				} else {
					
					// 4. Pass - Import Widgets.
					$('.vela-theme-info').append('<div class="import-return-info notice-success">'+velaSiteImporter.i18n.s13+'</div>');
					$('.vela-import-widgets').removeClass( 'vela-import-widgets' ).addClass('vela-sites-import-done vela-sites-import-widgets-done');
					$(document).trigger( 'vela-sites-import-widgets-done' );	
					$( ".vela-sites-import-done" ).trigger( "click" );				
				}
			});
		},
		
		_importEnd: function( event ) {

			$('.vela-sites-import-done').text( velaSiteImporter.i18n.s14 );
			$('.vela-theme-info').append('<div class="import-return-info notice-success">'+velaSiteImporter.i18n.s14_1+'</div>');
			$('.vela-import-button').removeClass( 'vela-sites-import-done' );
		},
		checkAndInstallPlugins:function () {
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
								VelaImporter.activatePlugin( response.activateUrl, plugin );
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
						VelaImporter.activatePlugin( activateUrl, plugin );
					}
				}
			);
		}
	},

	activatePlugin: function ( activationUrl, plugin ) {
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
						$( '.vela-import-site' ).trigger( 'click' );
					}
				}
			}
		);
	}

	}
	
	$(function(){
		VelaImporter.init();
	});
	
})(jQuery);