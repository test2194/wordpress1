window.easyLastPrice = 0;
function easyreservations_send_price(form){
	if(!document.easyFrontendFormular) return false;
    var error = 0;
	var data = easy_get_data(form);
	if(data['from'] == "") return false;

    jQuery("#showPrice").html('<img style="vertical-align:text-bottom" src="' + easy_both.plugin_url + '/easyreservations/assets/images/loading.gif">');
    jQuery('.receipt .overlay').css('display', 'block');

    if(jQuery('#'+form+' input[name^="coupon"]').length > 0) data['coupon'] = jQuery('#'+form+' input[name^="coupon"]').val();
    data['action'] = 'easyreservations_send_price';

    var receipt = jQuery('.receipt');
    if(receipt.length > 0){
     data['receipt-atts'] = receipt.data();
    }
	if(error == 0){
        jQuery.post(easy_both.ajaxurl , data, function(response) {
            response = jQuery.parseJSON(response);
            jQuery(".receipt-container.hidden, .receipt.hidden").removeClass('hidden');
            jQuery("#showPrice").html(response[0]);
            receipt.html(easyStripslashes(response[2]));
            jQuery('.receipt .overlay').css('display', 'none');
            window.easyLastPrice = parseFloat(response[1]);
            return false;
        });
	}
}