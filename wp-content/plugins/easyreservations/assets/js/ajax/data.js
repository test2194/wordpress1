function easy_get_data(form){
    var tnights = 0, nights = '', nights_interval = 0, slot = -1, to = '', toplus = 0, fromplus = 0, children = 0, adults = 1, captcha = 'x!', captcha_prefix = '', tom = 0, toh = '', fromm = 0, fromh = '', theid = '';

    if(jQuery('#'+form+' input[name="from"]').length > 0){
        var from = jQuery('#'+form+' input[name="from"]').val();
        if(jQuery('#'+form+' *[name="date-from-hour"]').length > 0) fromh = parseInt(jQuery('#'+form+' *[name="date-from-hour"]').val()) * 60;
        if(jQuery('#'+form+' *[name="date-from-min"]').length > 0) fromm = parseInt(jQuery('#'+form+' *[name="date-from-min"]').val());
        if(fromh !== '') fromplus = (fromh + fromm)*60;
    } else alert('no arrival field - correct that');

    var nights_field = jQuery('#'+form+' *[name="nights"]');
    if(jQuery('#'+form+' input[name="to"]').length > 0){
        to = jQuery('#'+form+' input[name="to"]').val()
    } else if(nights_field.length > 0){
        nights = nights_field.val();
        if(nights_field.attr('data-interval')){
            nights_interval = nights_field.attr('data-interval');
        }
        tnights = nights;
    }

    if(jQuery('#'+form+' *[name="date-to-hour"]').length > 0) toh = parseInt(jQuery('#'+form+' *[name="date-to-hour"]').val()) * 60;
    if(jQuery('#'+form+' *[name="date-to-min"]').length > 0) tom = parseInt(jQuery('#'+form+' *[name="date-to-min"]').val());
    if(toh !== '') toplus = (toh + tom)*60;
    if(jQuery('#'+form+' *[name="slot"]').length > 0) slot = parseInt(jQuery('#'+form+' *[name="slot"]').val());

    if(jQuery('#'+form+' *[name="resource"]').length > 0) var resource = jQuery('#'+form+' *[name="resource"]').val();
    else alert('no resource field - correct that');

    var instance = jQuery('#'+form+' input[name="from"]').data( "datepicker" );
    if(instance && to != ''){
        var dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, from, instance.settings );
        var dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, to, instance.settings );
        var difference_ms = Math.abs(dateanf - dateend);
        var diff = difference_ms/1000;
        diff += toplus;
        diff -= fromplus;
        var interval = easy_both.resources[resource]['interval'];
        tnights = Math.ceil(diff/interval);
    }
    if(jQuery('#'+form+' *[name="children"]').length > 0) children = parseFloat(jQuery('#'+form+' *[name="children"]').val());
    if(jQuery('#'+form+' *[name="adults"]').length > 0) adults = parseFloat(jQuery('#'+form+' *[name="adults"]').val());
    if(jQuery('#'+form+' input[name="email"]').length > 0) var email = jQuery('#'+form+' input[name="email"]').val();
    else alert('no email field - correct that');
    if(jQuery('#'+form+' *[name="captcha_value"]').length > 0) captcha = jQuery('#'+form+' *[name="captcha_value"]').val();
    if(jQuery('#'+form+' *[name="captcha_prefix"]').length > 0) captcha_prefix = jQuery('#'+form+' *[name="captcha_prefix"]').val();
    if(jQuery('#'+form+' input[name="reservation-name"]').length > 0) var name = jQuery('#'+form+' input[name="reservation-name"]').val();
    else alert('no name field - correct that');

    var new_custom = [];
    jQuery("input[id^='easy-new-custom-']:radio:checked, select[id^='easy-new-custom-'],input[id^='easy-new-custom-']:checkbox:checked,input[id^='easy-new-custom-'][type=number],input[id^='easy-new-custom-'][type=text],input[id^='easy-new-custom-'][type=hidden]").each ( function (i){
        var id = jQuery(this).attr('id').replace('easy-new-custom-', '');
        new_custom.push({id: id, value: jQuery(this).val()});
    });

    return {
        security:jQuery('#'+form+' input[name="pricenonce"]').val(),
        captcha:captcha,
        captcha_prefix:captcha_prefix,
        slot:slot,
        from:from,
        fromplus:fromplus,
        to:to,
        toplus:toplus,
        name:name,
        new_custom: new_custom,
        nights_interval: nights_interval,
        nights: nights,
        tnights: tnights,
        children: children,
        adults: adults,
        resource: resource,
        email:email
    };
}
