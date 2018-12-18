var easyCalendars = [];
jQuery("body").on({
	click: function(){
		var split = this.id.split("-");
		if(easyCalendars[split[2]]){
			var cal = easyCalendars[split[2]];
			cal.click(this,jQuery(this).attr('date'),split[4]);
		}
	}
}, "td[date]");

function change_resource(resource_id){
	var x = 0;
	for(var i in easyCalendars){
		if(x > 5) return;
		if(easyCalendars[i].resource !== resource_id)
			easyCalendars[i].change('resource', resource_id);
		x++;
	}
}

jQuery('select[id$="easy-form-resource"]').bind("change", function(){
	change_resource(jQuery(this).val());
});

function easyCalendar(nonce, atts, type){
	this.id = atts['id'];
	this.nonce = nonce;
	this.resource = atts['resource'];
	this.date = 0;
	this.last_date = 0;
	this.type = type;
	this.atts = atts;

	this.clicknr = 0;
	this.cellnr = 0;
	this.calm = 0;

	this.change = change;
	this.send = send;
	this.click = click;

	easyCalendars[this.id] = this;

	function change(key, value){
		this[key] = value;
		this.send(key, value);
	}
	function send(key, value){
		var data = {
			action: 'easyreservations_send_calendar',
			security: this.nonce,
			resource: this.resource,
			date: this.date,
			where: this.type,
			atts: this.atts
		};


		if(this.adults) data.adults = this.adults;
		if(this.children) data.children = this.children;
		if(this.reserved) data.reserved = this.reserved;

		var id = this.id,
				last_date = this.last_date,
				slide_out_direction = 'left',
				slide_in_direction = 'right',
        calendar_form = jQuery('#CalendarFormular-'+id),
				width = undefined,
				height = 0;

		var resource_box = false;
		var next_ele = calendar_form.next();
		if(next_ele.is('.calendar-resource-box:not(.float-right,.float-left,.float-full-width)')){
			resource_box = next_ele;
		} else {
			var prev_ele = calendar_form.prev();
			if(prev_ele.is('.calendar-resource-box:not(.float-right,.float-left,.float-full-width)')){
				resource_box = prev_ele;
			}
		}

		//prevent spamming of prev next button
		if(key == 'date' && this.last_date == this.date){
			return true;
		}
		calendar_form.addClass('loading');

		if(last_date !== 0 || this.date !== 0){
			//width = calendar_form.find('.easy-calendar').width();
		}
		jQuery.post(easy_both.ajaxurl, data, function(response){
			var ele = jQuery("#CalendarFormular-"+id+" .calendar-table .let-me-fly");

			if(ele.length > 0 && key == 'date'){
				var new_calendar = jQuery(response);

				new_calendar.find('.let-me-fly').css('display', 'none');
				if(key == 'date' && parseInt(value) < last_date){
					slide_out_direction = 'right';
					slide_in_direction = 'left';
				}

				ele.hide('slide', { direction: slide_out_direction, complete: function(){
					//jQuery("#CalendarFormular-"+id+" .easy-calendar").html(new_calendar).width(width);
					calendar_form.html(new_calendar)

					var bla = jQuery("#CalendarFormular-"+id+" .let-me-fly");
					bla.css('display', 'none').show('slide', { direction: slide_in_direction }, 200);
					if(resource_box){
						resource_box.css('height', jQuery("#CalendarFormular-"+id+" .calendar-table").height()+3);
					}
					calendar_form.removeClass('loading');
				}}, 200);
			} else {
				//height = jQuery("#CalendarFormular-"+id+" .easy-calendar").html(response).width(width).height();
				height = calendar_form.html(response);
				if(resource_box){
					resource_box.css('height', jQuery("#CalendarFormular-"+id+" .calendar-table").height()+3);
				}
				calendar_form.removeClass('loading');
			}
			if(jQuery.fn.simpleSlider) {
				var slider = jQuery(".easy-slide-show").data("simpleslider");
				slider.resizeSlider();
			}
		});
		if(key == 'date'){
			this.last_date = parseInt(value);
		}
	}

	function click(cell, date, m){
		jQuery("#CalendarFormular-"+this.id+' .reqdisabled').removeClass('reqdisabled');
		if(this.clicknr == 2 || (atts['select'] == 1 && this.clicknr == 1)){
			jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
			this.clicknr = 0;
		}

		if(this.clicknr == 1){
			jQuery("#CalendarFormular-"+this.id+' .reqstartdisabled').addClass('reqdisabled');
			if(jQuery(cell).hasClass('reqenddisabled')) return false;
			this.cellnr = parseFloat(this.cellnr);
			this.calm = parseFloat(this.calm);
			var axis = parseFloat(cell.axis);
			if(this.calm != m) axis = 31;
			if(!document.getElementById('easy-cal-' + this.id + '-'+ this.cellnr + '-' + this.calm)) this.cellnr = 1;
			if(this.cellnr <= axis && parseFloat(m) >= this.calm){
				for(var i = this.cellnr; i<=axis; i++){
					var element = '#easy-cal-' + this.id + '-'+ i + '-' + this.calm;
					if(i != axis && jQuery(element).hasClass('calendar-cell-full') && !jQuery(cell).hasClass('calendar-cell-halfend')){
						jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
						jQuery('#easy-form-to, #easy-search-to').val('');
						jQuery(cell.parentNode.parentNode.parentNode.parentNode).addClass("calendar-full");
						break;
					}
					jQuery(element).addClass("calendar-cell-selected");
					if(i == 31 && this.calm != m){
						i = 0;
						this.calm = this.calm + 1;
						if(this.calm == m) axis = parseFloat(cell.axis);
					}
				}
				jQuery('#easy-form-to,#easy-widget-to,#easy-search-to').val(date).trigger('change');
				if(document.getElementById('easy-form-units') && document.getElementById('easy-form-from')){
					var instance = jQuery( 'input#easy-form-from' ).data( "datepicker" );
					if(instance){
						var dateanf = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, document.getElementById('easy-form-from').value, instance.settings );
						var dateend = jQuery.datepicker.parseDate(instance.settings.dateFormat || jQuery.datepicker._defaults.dateFormat, date, instance.settings );
						var diff = Math.abs(dateanf - dateend)/1000;
						var interval = 86400;
						jQuery('#easy-form-units').val(Math.ceil(diff/interval)).trigger('change');
					}
				}
				this.clicknr = 2;
			} else {
				this.clicknr = 2;
				this.calm = 0;
				jQuery("#CalendarFormular-"+this.id+" .calendar-cell-selected").removeClass("calendar-cell-selected");
			}
		}
		if(this.clicknr == 0){
			if(jQuery(cell).hasClass('reqstartdisabled')) return false;
			if(jQuery(cell).hasClass('calendar-cell-full') && !jQuery(cell).hasClass('calendar-cell-halfend')) return false;
			jQuery("#CalendarFormular-"+this.id+' .reqenddisabled').addClass('reqdisabled');
			jQuery(cell.parentNode.parentNode.parentNode.parentNode).removeClass("calendar-full");
			jQuery(cell).addClass("calendar-cell-selected");
			jQuery('#easy-form-from,#easy-widget-from, #easy-search-from,#easy-form-to,#easy-widget-to, #easy-search-to').val(date).trigger('change');
			this.calm = m;
			this.cellnr = cell.axis;
			this.clicknr = 1;
		}
	}
	this.send();
}