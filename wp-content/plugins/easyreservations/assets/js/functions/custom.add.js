var custom_temp_id = 1000, price_temp_id = 1000;

jQuery('#custom_add_field').bind('click', easy_generate_custom);

function easy_generate_custom(){
	var id = jQuery('#custom_add_select').val();
	var data = {
		action: 'easyreservations_get_custom',
		security:custom_nonce,
		id: id
	};
	jQuery.post(ajaxurl, data, function(response){
		response = jQuery.parseJSON(response);
		if(response && response[0]){
			var field = '<tr><td class="label">';
			field += response[1]['title'];
			field += '<a style="vertical-align:middle;" onclick="this.parentNode.parentNode.parentNode.removeChild(this.parentNode.parentNode)" class="fa fa-times"></a></td>';
			field += '<td>'+response[0]+'</td></tr>';
			document.getElementById("customPrices").innerHTML += field;
			easyUiSlider()
		}
	});
}

