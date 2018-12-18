var submit_state = false;
function easyreservations_send_validate(y, form) {
    if (y !== 'send' || (!submit_state && y == 'send')) {
        submit_state = false;
        var errornr = 1, custom = '', ids = false, theid = '';
        if (jQuery('#' + form + ' input[name="editID"]').length > 0) theid = jQuery('#' + form + ' input[name="editID"]').val();

        var data = easy_get_data(form);
        var the_error_field = 'easy-form-units';

        if (y) var mode = y;
        else mode = 'normal';
        data['action'] = 'easyreservations_send_validate';
        data['mode'] = mode;
        data['id'] = theid;
        data['ids'] = ids;
        if(data['from'] == '') return;
        if(data['to'] !== '') the_error_field = 'easy-form-to';

        if (y == "send") {
            submit_state = true;
            easyOverlayDimm(0);
        }

        var last;
        jQuery("[id^='easy-new-custom-']").each ( function (i){
            var id = jQuery(this).attr('id').replace('easy-new-custom-', '');
            if(last == id){
                return true;
            } else {
                last = id;
            }
            if(this.type == 'checkbox' && this.checked == false) data['new_custom'].push({id: id, value: ''});
            else if(this.type == 'radio' && this.checked == false && !jQuery("input[name='"+this.name+"']:checked").val()) data['new_custom'].push({id: id, value: ''});
        });

        jQuery("#easy-show-error-div").addClass('hide');
        jQuery("[id^='easy-form-'],[id^='easy-custom-'],[id^='easy-new-custom-']").removeClass('form-error');
        jQuery(".error-wrapper + .ui-effects-wrapper, .error-wrapper, .ui-effects-wrapper").remove();
        jQuery('#' + form + ' small.hide').removeClass('hide');
        jQuery('#' + form + ' #easy-show-error').html();

        if (easyReservationIDs) ids = easyReservationIDs;


        if (errornr == 1) {
            jQuery.post(easy_both.ajaxurl, data, function (response) {
                jQuery('input,select,textarea').removeClass('form-error');
                jQuery(".error-wrapper + .ui-effects-wrapper, .error-wrapper, .ui-effects-wrapper").remove();
                if (y == "send") jQuery('#easyFrontendFormular *[temp="disabled"]').attr('disabled', 'disabled').attr('temp', false);
                if (y !== "send" && submit_state) return;
                var errornr = 0;
                var warning = '';

                if (response != '' && response != null && response != 1) {
                    errornr++;
                    if (mode == 'send' && response.length > 0) jQuery("#easy-show-error-div").removeClass('hide');
                    var warningli = '';
                    for (var i = 0; i < response.length; i++) {
                        var field = response[i];
                        i++;
                        var error = easyStripslashes(response[i]);
                        if (field == 'date') {
                            var error_field = jQuery('#' + the_error_field);
                            var from_field = jQuery('#easy-form-from');
                            from_field.addClass('form-error');
                            error_field.addClass('form-error');
                            warning = '<span class="error-wrapper"><label class="easy-show-error" id="easy-error-field-' + field + '" for="' + the_error_field + '">' + error + '</label></span>';
                            easy_add_form_error(the_error_field, warning, from_field);
                            if (mode == 'send' && document.getElementById('easy-show-error') !== null) {
                                document.getElementById('easy-show-error').innerHTML += '<li><label for="' + the_error_field + '">' + error + '</label></li>';
                            }
                        } else {
                            jQuery('#' + field + ':last').addClass('form-error');
                            warning = '<span class="error-wrapper"><label for="' + field + '" class="easy-show-error" id="easy-error-field-' + field + '">' + error + '</label></span>';
                            if (mode == 'send' && document.getElementById('easy-show-error') !== null) {
                                document.getElementById('easy-show-error').innerHTML += '<li><label for="' + field + '">' + error + '</label></li>';
                            }
                            if (field == 'easy-form-to') field = the_error_field;
                            if (field == 'easy-form-captcha') field = 'easy-form-captcha-img';
                            easy_add_form_error(field, warning);
                        }
                    }

                    if (y == 'send') {
                        jQuery('html, body').animate({
                            scrollTop: (jQuery('.form-error:first').offset().top - 200)
                        }, 500);
                    }
                }
                if(errornr == 0 && mode == 'send'){
                    if (easyReservationAtts['multiple'] == 0) {
                        jQuery(window).unbind('beforeunload');
                        document.getElementById('easyFrontendFormular').submit();
                        return true;
                    } else {
                        if (easyReservationEdit) easyFormSubmit();
                        else {
                            easyInnerlay(1);
                        }
                    }
                } else {
                    easyOverlayDimm(1);
                }
            });
        }
    }
}

function easy_add_form_error(field_id, warning, from_field) {
    var elem = false;
    var field = jQuery('#' + field_id);
    if (from_field !== undefined) {
        if (field.length > 0) {
            elem = field.parents('div.content');
        } else {
            elem = from_field.parents('div.content');
        }
    } else {
        elem = field.parents('div.content');
    }

    if (elem.length > 0) {
        var small = elem.find('small');
        if (small.length > 0) {
            warning = jQuery(warning);
            small.addClass('hide').after(warning);
        } else {
            warning = jQuery(warning).css('display', 'none');
            elem.append(warning);
        }
    } else {
        warning = jQuery(warning).css('display', 'none');
        var span = field.parents('span.row');
        if (span.length > 0) span.after(warning);
        else field.after(warning);
    }

    warning.show("blind", null, 200);
}