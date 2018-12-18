/**
 * Created by feryaz on 15.09.2018.
 */
(function( $ ) {

    $.fn.dateSelection = function(options) {
        var e = $(this);
        var calendarContainer = e.find('.datepicker');
        var now = new Date();
        var startOfDay = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        var data = false,
            lastRequest = false,
            done = false,
            slots = false,
            resourceQuantity = false,
            preservedDate = false,
            arrival = false,
            arrivalDate = false,
            arrivalTime = false,
            departure = false,
            departureTime = false;

        var settings = $.extend({
            resource: 0,
            arrivalHour: false,
            arrivalMinute: false,
            departureHour: false,
            departureMinute: false,
            minDate: null,
            init: true,
            departure: true,
            numberOfMonths: 1,
            time: false
        }, options );

        if(settings.resource == 0){
            settings.resource = $('*[name=resource]').val();
        }

        e.find('div.arrival').bind('click', function(){
            init();
        });

        $('*[name=resource]').bind('change', function(){
            settings.resource = $(this).val();
            init();
        });

        if(settings.init){
            init();
        }

        function init(){
            e.find('.departure').removeClass('active');
            e.find('.departure .text .date').removeClass('important').html('&#8212;');
            e.find('.arrival .text .date').addClass('important').html(easy_date_selection_params.wait);
            e.find('.text .time').html('');

            if(calendarContainer.hasClass('hasDatepicker')){
                destroyDatePicker(init);
            } else {
                e.find('div.time').html('');
                if(arrival){
                    preservedDate = false;
                }
                arrival = false;
                arrivalTime = false;
                departure = false;
                departureTime = false;
                done = false;
                data = false;
                slots = false;
                e.find('input[name=from]').val('');
                e.find('input[name=to]').val('');
                e.find('input[name=slot]').val(-1);

                loadData(arrival ? arrival : 0);
                generateDatepicker();
            }
        }

        function nextAction(){
            if(!done){
                if(departure){
                    if(departureTime || !settings.time){
                        destroyDatePicker(finish);
                        done = true;
                    } else {
                        generateTimepicker();
                    }
                } else if(arrival){
                    if(arrivalTime !== false || !settings.time){
                        if(settings.departure){
                            var maxDate = null;
                            if(arrival && slots) {
                                var max = 0;
                                $.each(data[arrival][arrivalTime], function (_, v) {
                                    var date = easyTimestampToDate(v[2]);

                                    if (date > max) {
                                        max = date;
                                        maxDate = easyFormatDate(date, easy_both.date_format);
                                    }
                                });
                            }
                            generateDatepicker(maxDate);
                        } else {
                            if(calendarContainer.hasClass('hasDatepicker')){
                                destroyDatePicker(nextAction);
                            } else {
                                if(slots){
                                    var date = easyTimestampToDate(data[arrival][arrivalTime][0][2]);
                                    departure = easyFormatDate(date, easy_both.date_format);
                                    departureTime = date.getHours() * 3600 + date.getMinutes() * 60;

                                    e.find('input[name=to]').val(departure);
                                    e.find('input[name=date-to-hour]').val(date.getHours());
                                    e.find('input[name=date-to-min]').val(date.getMinutes());
                                    e.find('.departure .text .date').removeClass('important').html(departure);
                                    e.find('.departure .text .time').html(easyFormatDate(date, easy_both.time_format));
                                } else {
                                    if(settings.form){
                                        var billing_units = data[arrival][2];
                                        if(billing_units < 1){
                                            billing_units = 1;
                                        }

                                        var departureStamp = arrivalDate.getTime() + (billing_units * parseInt(easy_both.resources[settings.resource]['interval'])) * 1000;
                                        if(arrivalTime !== false){
                                            departureStamp += arrivalTime * 1000;
                                        }
                                        departureStamp = new Date(departureStamp);
                                        departure = easyFormatDate(departureStamp, easy_both.date_format);
                                        if(settings.departureHour){
                                            departureStamp.setHours(settings.departureHour, settings.departureMinute, 0);
                                        }
                                        e.find('input[name=to]').val(departure);
                                        e.find('input[name=date-to-hour]').val(departureStamp.getHours());
                                        e.find('input[name=date-to-min]').val(departureStamp.getMinutes());
                                        e.find('.departure .text .date').removeClass('important').html(departure);
                                        e.find('.departure .text .time').html(easyFormatDate(departureStamp, easy_both.time_format));
                                    }
                                }
                                finish();
                            }
                            done = true;
                        }
                    } else {
                        generateTimepicker();
                    }
                }
            }
        }

        function generateTimepicker(){
            var date = $.datepicker.formatDate("DD, d M yy", calendarContainer.datepicker( "getDate" ));
            e.find('a.ui-state-active').parent().parent().after('<tr class="time-picker"><td colspan="7"><div>'+date+'<div class="insert"></div></div></td></tr>');

            if(slots){
                var time_options = '';

                if(arrivalTime !== false){
                    $.each(data[arrival][arrivalTime], function (t, v) {
                        if(v[0] > 0){
                            var bla = easyTimestampToDate(v[2]);
                            var label = easyFormatDate(bla, easy_both.time_format);
                            time_options += '<li class="easy-button" data-value="' + (bla.getHours() * 3600 + bla.getMinutes() * 60) + '" data-label="'+ label +'" data-id="'+ v[1] +'">' + label + '</li>';
                        }
                    });
                } else {
                    $.each(data[arrival], function (t, _slots) {
                        $.each(_slots, function (k, v) {
                            if(v[0] > 0){
                                var date = easyFormatDate(new Date(startOfDay.getTime() + t*1000), easy_both.time_format);
                                var label = '';
                                if(!settings.departure){
                                    label += ' -';
                                    var slotDeparture = easyFormatDate(easyTimestampToDate(v[2]), easy_both.date_format);
                                    if(arrival !== slotDeparture){
                                        label += ' ' + slotDeparture;
                                    }
                                    label += ' ' + easyFormatDate(easyTimestampToDate(v[2]), easy_both.time_format);;
                                }
                                time_options += '<li class="easy-button" data-value="' + t + '" data-label="'+ date +'" data-id="'+ v[1] +'">' + date + label + '</li>';

                                //Only display one slot with the same arrival time if we allow picking departure
                                if(settings.departure){
                                    return false;
                                }
                            }
                        });
                    });
                }
                if(time_options !== ''){
                    e.find('.time-picker .insert').html('<ul class="option-buttons">'+time_options+'</ul>');
                    e.find('.time-picker > td > div').slideDown(300);
                    e.find('ul.option-buttons li').bind('click', function(){
                        if(arrivalTime !== false) {
                            e.find('input[name=slot]').val($(this).attr('data-id'));
                            departureTime = $(this).attr('data-value');
                            var hour = Math.floor(parseInt(departureTime) / 3600);
                            e.find('input[name=date-from-hour]').val(hour);
                            e.find('input[name=date-to-min]').val((departureTime - hour * 3600)/60);
                            e.find('.departure .text .time').html($(this).attr('data-label'));
                        } else {
                            if(!settings.departure){
                                e.find('input[name=slot]').val($(this).attr('data-id'));
                            }
                            arrivalTime = $(this).attr('data-value');
                            var hour = Math.floor(parseInt(arrivalTime) / 3600);
                            e.find('input[name=date-from-hour]').val(hour);
                            e.find('input[name=date-from-min]').val((arrivalTime - hour * 3600)/60);
                            e.find('.arrival .text .time').html($(this).attr('data-label'));
                        }
                        destroyDatePicker(nextAction);
                    });
                }
            } else {
                if(data[departure ? departure : arrival][0] === parseInt(data[departure ? departure : arrival][0], 10)){
                    e.find('div.time-prototype').contents().clone(true).appendTo(e.find('.time-picker .insert'));

                    var minMax;

                    if(departure){
                        minMax = data[departure][1];
                        if(arrival == departure){
                            var hour = Math.floor(arrivalTime/3600);
                            minMax[0] = parseInt(minMax[0]) < hour ? hour : minMax[0];
                        }
                    } else {
                        minMax = data[arrival][1];
                    }

                    e.find('.time-picker .apply-time').bind('click', function () {
                        $(this).removeClass('fa-check').addClass('fa-spinner fa-spin');
                        var time = e.find('.time-picker select[name=time-hour]');
                        if(time.length > 0){
                            var minute = parseInt(e.find('.time-picker select[name=time-min]').val());
                            if(arrivalTime !== false){
                                departureTime = parseInt(time.val()) * 3600 + minute * 60;
                                e.find('input[name=date-to-hour]').val(time.val());
                                e.find('input[name=date-to-min]').val(minute);
                                e.find('.departure .text .time').html(easyFormatDate(new Date(startOfDay.getTime()+departureTime), easy_both.time_format));
                            } else {
                                arrivalTime = parseInt(time.val()) * 3600 + minute * 60;
                                e.find('input[name=date-from-hour]').val(time.val());
                                e.find('input[name=date-from-min]').val(minute);
                                e.find('.arrival .text .time').html(easyFormatDate(new Date(startOfDay.getTime()+arrivalTime), easy_both.time_format));
                                if(settings.departure){
                                    loadData(arrival);
                                }
                            }
                            destroyDatePicker(nextAction);
                        }

                    });
                    e.find('.time-picker select[name=time-hour] option').each(function(){
                        var value = parseInt($(this).val());
                        if(value < minMax[0] || value > minMax[1]){
                            $(this).attr('disabled', true).attr('selected', false).css('display', 'none');
                        } else {
                            $(this).attr('disabled', false).css('display', 'block');
                        }
                    });
                } else {
                    var time_options = '';
                    $.each(data[departure ? departure : arrival][0], function(k,v){
                        var string = k.split(' ');
                        var time = string[0].split(':');
                        var c = v < 1 ? 'bg-red' : v < resourceQuantity ? 'bg-yellow' : 'bg-green';
                        time_options += '<div class="time-option '+c+'" data-hour="'+(parseInt(time[0]) + (string[1] && (string[1] == 'PM' || string[1] == 'pm') ? 12 : 0))+'" data-minute="'+time[1]+'" data-label="'+k+'">'+k+'</div>';
                    });

                    e.find('.time-picker .insert').html('<div class="option-buttons">'+time_options+'</div>');
                    e.find('.time-picker > td > div').slideDown(300);

                    e.find('.time-picker .time-option.bg-green, .time-picker .time-option.bg-yellow').bind( 'click', function(){
                        if(arrivalTime !== false){
                            departureTime = parseInt($(this).attr('data-hour')) * 3600 + parseInt($(this).attr('data-minute')) * 60;
                            e.find('input[name=date-to-hour]').val($(this).attr('data-hour'));
                            e.find('input[name=date-to-min]').val($(this).attr('data-minute'));
                            e.find('.departure .text .time').html($(this).attr('data-label'));
                        } else {
                            arrivalTime = parseInt($(this).attr('data-hour')) * 3600 + parseInt($(this).attr('data-minute')) * 60;
                            e.find('input[name=date-from-hour]').val($(this).attr('data-hour'));
                            e.find('input[name=date-from-min]').val($(this).attr('data-minute'));
                            e.find('.arrival .text .time').html($(this).attr('data-label'));
                            if(settings.departure){
                                loadData(arrival);
                            }
                        }
                        destroyDatePicker(nextAction);
                    } );
                }


                e.find('.time-picker > td > div').slideDown(300);
            }
        }

        function generateDatepicker(maxDate){
            var date_format = 'dd.mm.yy';
            if(easy_both.date_format == 'Y/m/d') date_format = 'yy/mm/dd';
            else if(easy_both.date_format == 'm/d/Y') date_format = 'mm/dd/yy';
            else if(easy_both.date_format == 'Y-m-d') date_format = 'yy-mm-dd';
            else if(easy_both.date_format == 'd-m-Y') date_format = 'dd-mm-yy';

            calendarContainer.datepicker({
                minDate: arrival ? arrival : settings.minDate,
                maxDate: maxDate ? maxDate : null,
                dateFormat: date_format,
                firstDay: 1,
                numberOfMonths: settings.numberOfMonths,
                beforeShowDay: checkData,
                onChangeMonthYear: function(year, month, inst){
                    if(!slots || (!arrivalTime && settings.time)  || (arrival && !settings.time)){
                        loadData(date_format.replace('dd', '01').replace('mm', month).replace('yy', year));
                    }
                    e.find('div.time').slideUp(300);
                    if(arrival){
                        e.find('.departure .text .date').html(easy_date_selection_params.wait);
                    } else {
                        e.find('.arrival .text .date').html(easy_date_selection_params.wait);
                    }
                },
                onSelect: select
            }).datepicker( "setDate" , null ).slideDown('300');
            calendarContainer.find('.ui-datepicker').removeClass('ui-datepicker').addClass('easy-datepicker');
            calendarContainer.find('.ui-datepicker-today a').removeClass('ui-state-highlight').removeClass('ui-state-hover').removeClass('ui-state-active');
            $.each(easy_date_selection_params.datepicker, function(k, v){
                calendarContainer.datepicker('option', k, $.parseJSON(v))
            });
        }

        function select(dateString, instance){
            if(arrival && (arrivalTime !== false || !settings.time)){

                departure = dateString;
                e.find('input[name=to]').val(dateString);
                e.find('.departure').addClass('active');
                e.find('.departure .text .date').removeClass('important').html(dateString);
                e.find('div.departure').unbind().bind('click', function(){
                    if(departure){
                        preservedDate = departure;
                        departure = false;
                    }
                    e.find('div.departure .text .time').html('');
                    done = false;
                    nextAction();
                });
                if(settings.time){
                    setTimeout(generateTimepicker, 1);
                } else {
                    if(slots){
                        var date = false;
                        $.each(data[arrival][arrivalTime], function(_, v){
                            if(departure == easyFormatDate(easyTimestampToDate(v[2]), easy_both.date_format)){
                                date = easyTimestampToDate(v[2]);
                                return false;
                            }
                        });
                        e.find('input[name=date-to-hour]').val(date.getHours());
                        e.find('input[name=date-to-min]').val(date.getMinutes());
                        departureTime = date.getHours() * 3600 + date.getMinutes() * 60;
                    } else {
                        e.find('input[name=date-from-hour]').val(settings.departureHour);
                        e.find('input[name=date-to-min]').val(settings.departureMinute);
                        departureTime = settings.departureHour * 3600 + settings.departureMinute * 60;
                    }

                    destroyDatePicker(nextAction);
                }
            } else {
                arrivalDate = calendarContainer.datepicker('getDate');
                arrival = dateString;
                preservedDate = dateString;
                e.find('.arrival .text .date').removeClass('important').html(dateString);
                e.find('input[name=from]').val(dateString);
                if(settings.time){
                    setTimeout(generateTimepicker, 1);
                } else {
                    var hour = settings.arrivalHour ? settings.arrivalHour : data[arrival][1][1];
                    var minute = settings.arrivalHour ? settings.arrivalMinute : 0;
                    if(slots){
                        var total = parseInt(Object.keys(data[arrival]));
                        hour = Math.floor((total) / 3600);
                        minute = (total - hour * 3600)/60;
                    }
                    arrivalTime = hour * 3600 + minute * 60;
                    e.find('input[name=date-from-hour]').val(hour);
                    e.find('input[name=date-from-min]').val(minute);

                    if(!settings.departure){
                        e.find('.departure .text .date').addClass('important').html(easy_date_selection_params.wait);
                        loadData(arrival);
                    }
                    destroyDatePicker(nextAction);
                }
            }
        }

        function destroyDatePicker(callback){
            calendarContainer.slideUp(300, function(){
                $(this).datepicker( "destroy" );
                if(callback) callback();
            });
        }

        function checkData(d){
            if(data){

                var key = easyFormatDate(d, false);

                if(slots && arrival && arrivalTime !== false){
                    var iterate;
                    if(arrivalTime !== false && settings.time){
                        iterate = data[arrival][arrivalTime];
                    } else {
                        iterate = data[arrival][Object.keys(data[arrival])[0]];
                    }
                    var toReturn = [false, 'unavailable', ''];
                    $.each(iterate, function (k, v) {
                        if (easyFormatDate(easyTimestampToDate(v[2]), easy_both.date_format) == key) {
                            toReturn = [true, '', ''];
                            return true;
                        }
                    });
                    return toReturn;
                }

                if(data.hasOwnProperty(key)){
                    if (data[key][0] === parseInt(data[key][0], 10)){
                        if(data[key][0] < 1){
                            return [false, 'unavailable', '']
                        }
                        if(data[key][0] < resourceQuantity){
                            return [true, 'partially', '']
                        }
                    } else {
                        var amount_available = 0;
                        var total;
                        if(slots){
                            var hasAvailableSlot = false;
                            total = data[key][Object.keys(data[key])[0]];
                            $.each(total, function (k, v) {
                                if(v[0] > 0){
                                    hasAvailableSlot = true;
                                    amount_available++;
                                }
                            });

                        } else {
                            total = data[key][0];
                            $.each(total, function(k, v){
                                if(v > 0){
                                    hasAvailableSlot = true;
                                    amount_available++;
                                }
                            });
                        }
                        if(!hasAvailableSlot){
                            return [false, 'unavailable', '']
                        }
                        if(Object.keys(total).length > amount_available){
                            return [true, 'partially', '']
                        }
                    }
                    return [true, '', '']
                }
            }

            return [false, 'unavailable', ''];
        }

        function loadData(date){
            var now = Date.now();
            lastRequest = now;
            data = false;

            var post = {
                action: 'easyreservations_calendar',
                date: date,
                arrival: arrival && (arrivalTime !== false || !settings.time) ? arrival : 0,
                arrivalTime: arrivalTime,
                departureTime: departureTime,
                months: settings.numberOfMonths,
                adults: $('*[name=adults]').val(),
                children: $('*[name=children]').val(),
                resource: settings.resource,
                minDate: settings.minDate,
                security: e.find('input[name="easy-date-selection-nonce"]').val()
            };

            $.post(easy_both.ajaxurl, post, function(response){
                if(lastRequest == now){
                    if(arrival){
                        e.find('.departure .text .date').addClass('important').html(easy_date_selection_params.select);
                    } else {
                        e.find('.arrival .text .date').html(easy_date_selection_params.select);
                    }
                    data = response;

                    slots = easy_both.resources[settings.resource]['slots'] ? true : false;
                    resourceQuantity = easy_both.resources[settings.resource]['quantity'];
                    if(data.hasOwnProperty('max') && data['max']){
                        calendarContainer.datepicker('option', 'maxDate', data['max']);
                    } else {
                        calendarContainer.datepicker("refresh");
                    }
                    calendarContainer.find('.ui-datepicker-today a, .ui-datepicker-current-day a').removeClass('ui-state-highlight').removeClass('ui-state-hover').removeClass('ui-state-active');
                }
            });
        }

        function finish(){

            if(settings.form){
                if(typeof easyreservations_send_price === "function"){
                    easyreservations_send_price(settings.form);
                }
                easyreservations_send_validate(false, settings.form);
            }

        }
    };

})( jQuery );
