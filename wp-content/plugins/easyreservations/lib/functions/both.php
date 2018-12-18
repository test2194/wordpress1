<?php

	function easy_init_sessions() {
		if (!session_id() && !headers_sent()) {
			session_start();
		}
	}
	add_action('init', 'easy_init_sessions');

	/**
	*	Returns full name of a country
	*
	*	$country = Index of country
	*/
	function easyreservations_country_name($country){
		if(!empty($country)){
            $country_array = include RESERVATIONS_ABSPATH . 'i18n/countries.php';
			if(isset($country_array[$country])){
				return $country_array[$country];
			} else {
				return __('Unknown', 'easyReservations');
			}
		}
		return $country;
	}

	function easyreservations_get_color_schemes_options($selected){
		$color_schemes = array('mint', 'fresh', 'default', 'dull', 'dark');
		$color_modes = array('' => addslashes(__("only unavailability", "easyReservations")), ' empty' => addslashes(__("with availability", "easyReservations")), ' occupied' => addslashes(__("with partly availability", "easyReservations")), ' both' => addslashes(__("with both", "easyReservations")));
		$color_scheme_options = '';
		foreach($color_schemes as $color_scheme){
			foreach ($color_modes as $key => $color_mode){
				$color_scheme_options .= '<option value="'.$color_scheme.$key.'" '.($color_scheme.$key == $selected ? 'selected="selected"' : '').'>'.ucfirst($color_scheme).' - '.$color_mode.'</option>';
			}
		}
		return $color_scheme_options;
	}

    function easyreservations_generate_hidden_fields($array, $id = false){
        if($array){
            $return = '';
            $id_str = '';
            foreach($array as $key => $value){
                if($id) $id_str = ' id="'.$key.'" ';
                $return .= '<input type="hidden" name="'.$key.'" value="'.$value.'" '.$id_str.'>';
            }
            return $return;
        }
        return false;
    }

	function easyreservations_build_datepicker($type, $instances, $trans = false, $search = false){
		if(function_exists('mb_internal_encoding')){
			mb_internal_encoding("UTF-8");
			$function = 'mb_substr';
		} else $function = 'substr';

        $style = str_replace(array('easy-ui-container'), '', RESERVATIONS_STYLE);

        $daysnames = er_date_get_label(0,0,false,true);
		$daynames = '["'.$daysnames[6].'","'.$daysnames[0].'","'.$daysnames[1].'","'.$daysnames[2].'","'.$daysnames[3].'","'.$daysnames[4].'","'.$daysnames[5].'"]';
		$daynamesshort = '["'.$function($daysnames[6],0, 3).'","'.$function($daysnames[0],0, 3).'","'.$function($daysnames[1],0, 3).'","'.$function($daysnames[2],0, 3).'","'.$function($daysnames[3],0, 3).'","'.$function($daysnames[4],0, 3).'","'.$function($daysnames[5],0, 3).'"]';
		$daynamesmin = '["'.$function($daysnames[6],0, 2).'","'.$function($daysnames[0],0, 2).'","'.$function($daysnames[1],0, 2).'","'.$function($daysnames[2],0, 2).'","'.$function($daysnames[3],0, 2).'","'.$function($daysnames[4],0, 2).'","'.$function($daysnames[5],0, 2).'"]';
		$monthes = er_date_get_label(1,0,false,true);
		$monthnames =  '["'.$monthes[0].'","'.$monthes[1].'","'.$monthes[2].'","'.$monthes[3].'","'.$monthes[4].'","'.$monthes[5].'","'.$monthes[6].'","'.$monthes[7].'","'.$monthes[8].'","'.$monthes[9].'","'.$monthes[10].'","'.$monthes[11].'"]';
		$monthnamesshort =  '["'.$function($monthes[0],0,3).'","'.$function($monthes[1],0,3).'","'.$function($monthes[2],0,3).'","'.$function($monthes[3],0,3).'","'.$function($monthes[4],0,3).'","'.$function($monthes[5],0,3).'","'.$function($monthes[6],0,3).'","'.$function($monthes[7],0,3).'","'.$function($monthes[8],0,3).'","'.$function($monthes[9],0,3).'","'.$function($monthes[10],0,3).'","'.$function($monthes[11],0,3).'"]';
		$translations = "dayNames: $daynames, dayNamesShort: $daynamesshort, dayNamesMin: $daynamesmin, monthNames: $monthnames, monthNamesShort: $monthnamesshort, beforeShow: function(_, inst){inst.dpDiv.removeClass('ui-datepicker').addClass('easy-datepicker').addClass('$style');},";
		
		if($search) $search = 1;
		else $search = 2;

		if($trans === true) return $translations;
		elseif($trans) $format = $trans;
		else $format = RESERVATIONS_DATE_FORMAT;


		$jquery = '';
		if(isset($instances[1])) foreach($instances as $instance) $jquery .= 'input#'.$instance.',';
		else $jquery = '#'.$instances;
		$jquery = substr($jquery, 0, -1);

		if($format == 'Y/m/d') $dateformat = 'yy/mm/dd';
		elseif($format == 'm/d/Y') $dateformat = 'mm/dd/yy';
		elseif($format == 'd-m-Y') $dateformat = 'dd-mm-yy';
		elseif($format == 'Y-m-d') $dateformat = 'yy-mm-dd';
		elseif($format == 'd.m.Y') $dateformat = 'dd.mm.yy';

		if($type == 0){
			$datepicker = <<<EOF
		<script type="text/javascript">
			jQuery(document).ready(function($){
				var dates = $( "$jquery" ).datepicker({
					dateFormat: '$dateformat',
					beforeShowDay: function(date){
						var allowedDays = $(this).attr('data-days');
						if(allowedDays !== undefined){
							var day = date.getDay();
							if(day === 0) day = 7;
							if($.inArray(day+"", allowedDays.split(',')) < 0){
								return [false];
							}
						}
						if($search == 2 && window.easydisabledays ){
							return easydisabledays(date, $(this).parents("form:first").find( "[name=resource],#resource" ).val());
						} else {
							return [true];
						}
					},
					$translations
					firstDay: 1,
					showAnim: 'slideDown',
					onSelect: function( selectedDate ){
						if(this.id == '$instances[0]'){
							var option = this.id == "$instances[0]" ? "minDate" : "maxDate",
							instance = $( this ).data( "datepicker" ),
							date = $.datepicker.parseDate( instance.settings.dateFormat ||	$.datepicker._defaults.dateFormat,	selectedDate, instance.settings );
							dates.not( this ).datepicker( "option", option, date );
						}
						if(window.easyreservations_send_validate) easyreservations_send_validate(false, 'easyFrontendFormular');
						if(window.easyreservations_send_price) easyreservations_send_price('easyFrontendFormular');
					}
				});
				if($("$jquery").attr('data-min') !== undefined){
					$("$jquery" ).datepicker("change", {minDate: $("$jquery").attr('data-min')});
				}
				var maxDate = $("$jquery").attr('data-max');
				if(maxDate !== undefined){
					if(maxDate == 0) maxDate = null;
					$("$jquery" ).datepicker("change", {maxDate: maxDate});
				}
				/*
				$(document)
					.off('click', '.ui-datepicker-next')
					.off('click', '.ui-datepicker-prev');

				$(document).on('click', '.ui-datepicker-next', function(a){
            $('.ui-datepicker-header')
                .hide('slide', { direction: 'left' }, 150)
                .show('slide', { direction: 'right' }, 150);
        });

        $(document).on('click', '.ui-datepicker-prev', function(a){
            $(this).parent()
                .hide('slide', { direction: 'right' }, 150)
                .show('slide', { direction: 'left' }, 150);
        });*/
			});
		</script>
EOF;
		} else {
			$datepicker = <<<EOF
		<script type="text/javascript">
			jQuery(document).ready(function($){
				var dates = $( "$jquery" ).datepicker({
					$translations
					dateFormat: '$dateformat',
					firstDay: 1,
					showAnim: 'slideDown'
				});
			});
		</script>
EOF;
		}
		if($type == 0 && function_exists("easyreservations_header_datepicker_script")){
			easyreservations_header_datepicker_script();
		}
		echo $datepicker;
	}

?>