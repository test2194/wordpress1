function generateOptions(options, sel){
	var value = '';
	if(typeof options == "string"){
		var split = options.split('-');
		for(var k = split[0]; k <= split[1]; k++){
			var selected = '';
			if(sel && sel == k) selected = 'selected="selected"';
			value += '<option value="'+k+'" '+selected+'>'+k+'</option>';
		}
	} else {
		jQuery.each(options, function(ok,ov){
			var selected = '';
			if(sel && sel == ok) selected = ' selected="selected"';
			value += '<option value="'+ok+'"'+selected+'>'+ov+'</option>';
		});
	}
	return value;
}

function isFunction(functionToCheck) {
	return functionToCheck && {}.toString.call(functionToCheck) === '[object Function]';
}