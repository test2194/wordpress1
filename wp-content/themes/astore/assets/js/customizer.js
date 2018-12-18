jQuery(document).ready(function($) {
    'use strict';

    var customize = wp.customize;

	/*icon type switch*/
    function icon_type_switch(icon_type,row) {
		if(!row.length)
			return false;
        if (icon_type === 'icon') {
			row.find('.repeater-field-image').hide();
			row.find('.repeater-field-iconpicker').show();
        } else {
			row.find('.repeater-field-image').show();
			row.find('.repeater-field-iconpicker').hide();

        }
    }
	$('select[data-field="icon_type"]').each(function(index, element) {
        var icon_type = $(this).val();
		var row = $(this).parents('li.repeater-row');
		icon_type_switch(icon_type,row);
    });
	$(document).on('change', 'select[data-field="icon_type"]', function(){
		var icon_type = $(this).val();
		var row = $(this).parents('li.repeater-row');
		icon_type_switch(icon_type,row);
    });
	
	$(document).on('click', 'button.repeater-add', function(){
		var newRow = $(this).parent('.customize-control-repeater').find('li.repeater-row:last'); 
		setTimeout(function(){
			var icon_type = newRow.find('select[data-field="icon_type"]').val();
			if(icon_type !== 'undefined'){
				icon_type_switch(icon_type,newRow);
			}
		 }, 100);
			
	 });
	
    /*Top Bar Left tpl*/
    function astore_topbar_left_tpl() {

        var html = '';
        $('#customize-control-astore-topbar_left .repeater-fields > li').each(function(index, element) {

            var icon = $(this).find('input[data-field="icon"]').val();
            var text = $(this).find('input[data-field="text"]').val();
            var link = $(this).find('input[data-field="link"]').val();
            var target = $(this).find('input[data-field="target"]').val();
            html += '<span class="astore-microwidget"><a href="' + link + '" target="' + target + '"><i class="fa ' + icon + '"></i>&nbsp;&nbsp;' + text + '</a></span>';

        });
        customize.previewer.send('topbar_left', html);

    }

    wp.customize('astore[topbar_left]', function(value) {
        value.bind(function(to) {
            astore_topbar_left_tpl();
        });
    });

    $(document).on('click', '#customize-control-astore-topbar_left .repeater-row-remove', function() {
        $(this).parents('li.repeater-row').remove();
        astore_topbar_left_tpl();
    });

    /*Top Bar Icons tpl*/
    function astore_topbar_icons_tpl() {

        var html = '';
        $('#customize-control-astore-topbar_icons .repeater-fields > li').each(function(index, element) {

            var icon = $(this).find('input[data-field="icon"]').val();
            var link = $(this).find('input[data-field="link"]').val();
            var target = $(this).find('input[data-field="target"]').val();
            html += '<a href="' + link + '" target="' + target + '"><i class="fa ' + icon + '"></i></a>';

        });
        customize.previewer.send('topbar_icons', html);

    }

    wp.customize('astore[topbar_icons]', function(value) {
        value.bind(function(to) {
            astore_topbar_icons_tpl();
        });
    });

    $(document).on('click', '#customize-control-astore-topbar_icons .repeater-row-remove', function() {
        $(this).parents('li.repeater-row').remove();
        astore_topbar_icons_tpl();
    });

    /*Top Bar Right tpl*/
    function astore_topbar_right_tpl() {

        var html = '';
        $('#customize-control-astore-topbar_right .repeater-fields > li').each(function(index, element) {

            var icon = $(this).find('input[data-field="icon"]').val();
            var text = $(this).find('input[data-field="text"]').val();
            var link = $(this).find('input[data-field="link"]').val();
            var target = $(this).find('input[data-field="target"]').val();
            html += '<span class="astore-microwidget"><a href="' + link + '" target="' + target + '"><i class="fa ' + icon + '"></i>&nbsp;&nbsp;' + text + '</a></span>';

        });
        customize.previewer.send('topbar_right', html);

    }

    wp.customize('astore[topbar_right]', function(value) {
        value.bind(function(to) {
            astore_topbar_right_tpl();
        });
    });

    $(document).on('click', '#customize-control-astore-topbar_right .repeater-row-remove', function() {
        $(this).parents('li.repeater-row').remove();
        astore_topbar_right_tpl();
    });

	wp.customize( 'astore[sticky_header_background_color]', function( value ) {
		value.bind( function( to ) {
				var patt = /^#([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
				var matches = patt.exec(to);
				var opacity = $('#customize-control-astore-sticky_header_background_opacity input').val();
				var rgba = "rgba("+parseInt(matches[1], 16)+","+parseInt(matches[2], 16)+","+parseInt(matches[3], 16)+","+opacity+")";
				customize.previewer.send('sticky_header_background_color', rgba);
				console.log(rgba);
		} );
	} );
	
	wp.customize( 'astore[sticky_header_background_opacity]', function( value ) {
		value.bind( function( to ) {
				var color = $('#customize-control-astore-sticky_header_background_color input').val();
				var patt = /^#([\da-fA-F]{2})([\da-fA-F]{2})([\da-fA-F]{2})$/;
				var matches = patt.exec(color);
				var rgba = "rgba("+parseInt(matches[1], 16)+","+parseInt(matches[2], 16)+","+parseInt(matches[3], 16)+","+to+")";
				console.log(rgba);
				customize.previewer.send('sticky_header_background_color', rgba);
		} );
	} );

    /*footer icons tpl*/
    function astore_footer_icons_tpl() {

        var html = '';
        $('#customize-control-astore-footer_icons .repeater-fields > li').each(function(index, element) {

            var title = $(this).find('input[data-field="title"]').val();
            var icon = $(this).find('input[data-field="icon"]').val();
            var link = $(this).find('input[data-field="link"]').val();

            if (icon !== '') {
                icon = icon.replace('fa ', '');
                icon = icon.replace('fa-', '');
            }
            if (icon != '')
                html += '<li><a href="' + link + '" title="' + title + '" target="_blank"><i class="fa fa-' + icon + '"></i></a></li>';
        });
        customize.previewer.send('footer_icons_selective', html);

    }
    wp.customize('astore[footer_icons]', function(value) {
        value.bind(function(to) {
            astore_footer_icons_tpl();
        });
    });

    $(document).on('click', '#customize-control-astore-footer_icons .repeater-row-remove', function() {
        $(this).parents('li.repeater-row').remove();
        astore_footer_icons_tpl();
    });

	
	/* === Checkbox Multiple Control === */

    $( '.customize-control-multiple-checkbox input[type="checkbox"]' ).on(
        'change',
        function() {
			var checkbox_values = [];
            checkbox_values = jQuery( this ).parents( '.customize-control' ).find( 'input[type="checkbox"]:checked' ).map(
                function() {
                    return this.value;
                }
            ).get().join( ',' );
			
            $( this ).parents( '.customize-control' ).find( 'input[type="hidden"]' ).val( checkbox_values ).trigger( 'change' );
        }
    );



});