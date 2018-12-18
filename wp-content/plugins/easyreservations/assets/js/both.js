function easyFormatTimestamp(timestamp, format){
  if(!format) format = easy_both['date_format'];
  return easyFormatDate(easyTimestampToDate(timestamp), format);
}

function easyFormatDate(date, format){
	if(!format) format = easy_both['date_format'];
	var year = date.getYear();
	if (year < 999) year += 1900;
	var month = easyAddZero(date.getMonth()+1);
	var day = easyAddZero(date.getDate());
	var hour = date.getHours();
	var minute = easyAddZero(date.getMinutes());

	format = format.replace("Y", year);
	format = format.replace("m", month);
	format = format.replace("d", day);
	format = format.replace("H", easyAddZero(hour));
	format = format.replace("h", hour % 12 ? easyAddZero(hour % 12) : 12);
	format = format.replace("a", hour >= 12 ? 'pm' : 'am');
	format = format.replace("A", hour >= 12 ? 'PM' : 'AM');
	format = format.replace("i", minute);

	return format
}

function easyDateToStamp(datestring){
  var offset = new Date().getTimezoneOffset();
  return new Date(new Date(datestring).getTime()+ parseFloat(easy_both['offset']*1000) + (offset * 60 * 1000)).getTime();
}

function easyTimestampToDate(timestamp){
	if(timestamp < 1262300400000) timestamp = timestamp*1000;
	var date = new Date(timestamp);
  	return new Date(date.getTime() + date.getTimezoneOffset() * 60 * 1000);
}

function easyStripslashes(str) {
	str = str.replace(/\\'/g, '\'');
	str = str.replace(/\\"/g, '"');
	str = str.replace(/\\0/g, '\0');
	str = str.replace(/\\\\/g, '\\');
	return str;
}

function easySelectDateTime(from, to, resource, nights, hours){
	jQuery('#easy-form-from, #easy-widget-from, #easy-search-from').val(easyFormatTimestamp(from));
	jQuery('#easy-form-to, #easy-widget-to, #easy-search-to').val(easyFormatTimestamp(to));
	var units = jQuery('#easy-form-units, #easy-widget-units, #easy-search-units');
	if(units.length > 0){
		units.each(function(){
			var interval = jQuery(this).attr('data-interval');
			if(interval === undefined || !interval || interval.length === 0){
				if(nights && nights >  0) interval = false;
				else if(resource) interval = easy_both.resources[parseInt(resource)]['interval'];
				else interval = false;
			}

			if(interval){
				var diff = (to - from)/interval;
				if(diff < 0.5) diff = 1;
				jQuery(this).val(Math.ceil(diff));

				var selected_option = jQuery(this).find('option:selected');
				if(selected_option.length == 0){
					jQuery(this).find('option:last').attr('selected', 'selected');
				}
			} else {
				//maximum select options
				if(nights > jQuery(this).get(0).options.length){
					nights = jQuery(this).get(0).options.length;
				}
				jQuery(this).prop('selectedIndex', nights-1);
			}
		});
	}
	if(hours){
		var from_date = easyTimestampToDate(from);
		var to_date = easyTimestampToDate(to);
		jQuery('#easy-form-date-from-hour, #easy-widget-date-from-hour, #easy-search-date-from-hour').val(from_date.getHours());
		jQuery('#easy-form-date-from-min, #easy-widget-date-from-min, #easy-search-date-from-min').val(from_date.getMinutes());
		jQuery('#easy-form-date-to-hour, #easy-widget-date-to-hour, #easy-search-date-to-hour').val(to_date.getHours());
		jQuery('#easy-form-date-to-min, #easy-widget-date-to-min, #easy-search-date-to-min').val(to_date.getMinutes());
	}
}

function easyAddZero(nr){
  if(nr < 10) nr = '0'+nr;
  return nr;
}
function easyInArray(array, needle){
	if(array){
		for(var i = 0; i < array.length; i++){
			if(array[i] == needle) return true;
		}
	}
	return false;
}
function changePayPalAmount(place){
	var price = easyStartPrice;
	if(place == 'perc'){
		document.getElementById('easy_radio_perc').checked = true;
		var perc = document.getElementById('easy_deposit_perc').value;
		if(perc.substr(perc.length - 1) == '%'){
			price = easyStartPrice / 100 * parseFloat(perc.substr(0,perc.length - 1));
		} else price = perc;
	} else if(place == 'own'){
		document.getElementById('easy_radio_own').checked = true;
		var price = document.getElementById('easy_deposit_own').value;
	} else if(place == 'full'){
		document.getElementById('easy_radio_full').checked = true;
		var price = easyStartPrice;
	}
	if(price > 0){
		price = Math.round(price*Math.pow(10,2))/Math.pow(10,2);
		easy_set_deposit_amount(price);
		jQuery('script[data-amount]').attr('data-amount', price*100);
	}
}
