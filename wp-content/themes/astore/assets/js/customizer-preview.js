var astroe_customizer_options = function ( $ ) {
    'use strict';
    $( function () {
        var customize = wp.customize;
		customize.preview.bind( 'sticky_header_background_color', function( data ) {
			 $( '.cactus-fixed-header-wrap .cactus-header' ).css('background-color',data);
		} );
	} );
}
astroe_customizer_options( jQuery );