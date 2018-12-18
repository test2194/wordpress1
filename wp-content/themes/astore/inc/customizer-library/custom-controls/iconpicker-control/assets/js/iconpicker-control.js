( function( $ ) {

	$( function() {
		$( '.icp' ).iconpicker({icons:astore_iconpicker.icons}).on( 'iconpickerUpdated', function() {
			$( this ).trigger( 'change' );
		} );
	} );

} )( jQuery );