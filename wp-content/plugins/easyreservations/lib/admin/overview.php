<?php
require('../../../../../wp-load.php');
$settings = get_option( "reservations_settings" );

function easyreservations_calculate_out_summertime($timestamp, $begin){
	$diff = 0;
	if(version_compare(PHP_VERSION, '5.3.0') >= 0 && is_numeric($timestamp)){
		$timezone = new DateTimeZone(date_default_timezone_get ());
		$transitions = $timezone->getTransitions($begin, $timestamp);
		if(isset($transitions[1]) && $transitions[0]['offset'] != $transitions[1]['offset']){
			$diff = $transitions[1]['offset'] - $transitions[0]['offset'];
			//if($transitions[0]['offset'] < $transitions[1]['offset']) $diff = $transitions[0]['offset'] - $transitions[1]['offset'];
			//else $diff = $transitions[1]['offset'] - $transitions[0]['offset'];
		}
	}
	return ($timestamp-$diff);
}



function easyreservations_overview_cell($cell_id, $class_td, $date, $axis, $style_td, $onmouseenter, $onclick, $add_td = false, $name_to_display = false, $class_res = false, $title = false, $background_color = false, $background = false){
	$cell = '<td id="'.$cell_id.'" ';
	if($title){
		$cell .= 'title="'.$title.'" ';
		$class_td .= ' easy-tooltip';
	}
	$cell .= 'class="'.$class_td.'" ';
	$cell .= 'date="'.$date.'" ';
	$cell .= 'axis="'.$axis.'" ';
	$cell .= 'style="'.$style_td.'" ';
	$cell .= 'onmouseenter="'.$onmouseenter.'" ';
	$cell .= 'onclick="'.$onclick.'" ';
	$cell .= $add_td ? $add_td : '';
	$cell .= '>';

	if($name_to_display){
		$cell .= '<span class="overview_reservations_name">'.$name_to_display.'</span>';
	}

	if($name_to_display || $title){
		$cell .= '<div class="reservation real '.$class_res.'" ';
		$cell .= 'data-background="'.$background_color.'" ';
		$cell .= 'abbr="'.$background.'" ';
		$cell .= 'style="background:'.$background.'"></div>';
	}
	$cell .= '</td>';
	return $cell;
}

if(isset($_POST['more'])) $days_after_present = $_POST['more'];
$main_options = get_option("reservations_main_options");
$overview_options = $main_options['overview'];
if(isset($_POST['interval'])) $interval = $_POST['interval'];
else $interval = 86400;
if($interval == 3600)	$date_pat = "d.m.Y H";
else $date_pat = "d.m.Y";

if(isset($_POST['dayPicker'])){
	$dayPicker=$_POST['dayPicker'];
	$daysbetween=(strtotime($dayPicker)-strtotime(date("d.m.Y", current_time( 'timestamp' ))))/$interval;
	$days_after_present=round($daysbetween/$interval*$interval)+2;
}

$months = er_date_get_label(1);
$days = er_date_get_label(0,2);

if(isset($_POST['resource']) && !empty($_POST['resource'])) $resource_id = intval( $_POST['resource']);
if(isset($_POST['approve'])) $approve = $_POST['approve'];
if(isset($_POST['add']) && !empty($_POST['add'])) $add = intval($_POST['add']);
if(isset($_POST['edit']) && !empty($_POST['edit'])) $edit = $_POST['edit'];
if(isset($_POST['nonepage']) && $_POST['nonepage'] == 0) $nonepage = $_POST['nonepage'];
if(isset($_POST['id']) && !empty($_POST['id'])) $id = $_POST['id'];
if(isset($_POST['res_date_from_stamp'])){
	$explode_time = explode("-", $_POST['res_date_from_stamp']);
	$reservation_arrival_stamp = $explode_time[0];
	$reservation_departure_stamp = $explode_time[1];
}
$date_style = isset($nonepage) ? 'ov-days-hover' : '';

if(isset($_POST['daysshow'])) $days_to_show = $_POST['daysshow'];
else $days_to_show = $overview_options['overview_show_days']; //How many Days to Show
$days_to_skip = $days_to_show;
$reservations_show_rooms = $overview_options['overview_show_rooms'];

if(!isset($reservations_show_rooms) || empty($reservations_show_rooms)) $show_rooms = ER()->resources()->get_accessible();
else {
	global $wpdb;
	$show_rooms = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE ID in($reservations_show_rooms) ORDER BY menu_order");
}

/* - - - - - - - - - - - - - - - - *\
|
|	Calculate Overview
|
/* - - - - - - - - - - - - - - - - */
if(!isset($days_after_present)) $days_after_present=0;
$timevariable = strtotime(date("d.m.Y 00:00:00", current_time( 'timestamp' )))-($interval*3); //Timestamp of first Second of today

if($interval == 3600){
	$timevariable = strtotime(date("d.m.Y H:00:00", current_time( 'timestamp' )))-(3600*3); //Timestamp of first Second of today
}
$timesx = easyreservations_calculate_out_summertime($timevariable+$interval*intval($days_after_present), $timevariable); // Timestamp of Startdate of Overview
$timesy = $timesx+$interval*$days_to_show; // Timestamp of Enddate of Overview
$skipped_cells = 0;
$hours_per_day = 23;
if($interval == 3600){
	if(isset($overview_options['overview_hourly_end'])){
		$hours_per_day = $hours_per_day - 23 + $overview_options['overview_hourly_end'];
		if(date('H', $timesx) > $overview_options['overview_hourly_start']){
			$timesx += (23 - $overview_options['overview_hourly_start'] - date('H', $timesx)) * 3600;
		}
	}
	$timesy = $timesx+$interval*$days_to_show; // Timestamp of Enddate of Overview

	if(isset($overview_options['overview_hourly_start'])){
		$hours_per_day -= $overview_options['overview_hourly_start'];
		if(date('H', $timesx) < $overview_options['overview_hourly_start']){
			$timesx += ($overview_options['overview_hourly_start'] - date('H', $timesx)) * 3600;
		}
	}

	$days_to_skip = $days_to_skip * (23 / $hours_per_day);

	$skipped_cells = (23 - $hours_per_day) * ($days_to_show/$hours_per_day);
	$timesy += $skipped_cells * 3600;
}

$dateshow=date("d. ", $timesx).$months[date("n", $timesx)-1].date(" Y", $timesx).' - '.date("d. ", $timesy-$interval).$months[date("n", $timesy-$interval)-1].date(" Y", $timesy-$interval);
$start_date=date("Y-m-d H:i", $timesx); // Formated Startdate
$end_date=date("Y-m-d H:i", $timesy-$interval); // Formated Enddate
if(!isset($daysbetween)) $daysbetween = ($timesx/$interval)-(strtotime(date("d.m.Y", current_time( 'timestamp' )))/$interval);

if(isset($reservation_arrival_stamp)){
	$numberhighstart = ceil(($reservation_arrival_stamp-$timesx)/$interval);
	$numberlaststart = ceil(($reservation_departure_stamp-$timesx)/$interval);
	if($numberlaststart<10) $numberlaststart='0'.$numberlaststart;
	if($numberhighstart<10) $numberhighstart='0'.$numberhighstart;
}

$days_string = er_date_get_interval_label(86400, 0, true);
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + OVERVIEW + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?><input type="hidden" id="hiddenfieldclick" name="hiddenfieldclick"><input type="hidden" id="timesx" name="timesx" value="<?php echo $timesx; ?>">
<input type="hidden" id="hiddenfieldclick2" name="hiddenfieldclick2"><input type="hidden" id="timesy" name="timesy" value="<?php echo $timesy; ?>">
<input type="hidden" id="getmore" name="getmore" value="<?php echo $days_after_present; ?>">
<table class="easy-ui overview" cellspacing="0" cellpadding="0" id="overview" style="width:99%;" onmouseout="document.getElementById('ov_datefield').innerHTML = '';">
<thead>
<tr>
  <th colspan="<?php echo $days_to_show+1; ?>"  class="overviewHeadline"><?php echo '<input type="hidden" id="easy-overview-interval" value="'.$interval.'">'; ?>
    <span id="pickForm"><a onclick="jQuery('#dayPicker').datepicker('show');" class="fa fa-calendar" style="font-size: 18px; margin-left:10px"></a><input name="dayPicker" id="dayPicker" type="text" value="<?php if(isset($dayPicker)) echo $dayPicker; ?>" style="visibility: hidden; width:0;padding:0"></span> &nbsp;<b class="overviewDate"><?php echo $dateshow; ?></b><span id="ov_datefield" style="padding-left:6px;width:300px;display:inline-block"> </span>
			<span style="float:right">
				<?php if($interval == 3600){ ?>
          <input name="daybutton" class="easy-button grey" value="Days" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present/24; ?>','no','<?php echo $days_to_show; ?>',86400);resetSet();">
				<?php } else { ?>
          <input name="daybutton" class="easy-button grey" value="Hours" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present*24; ?>','no','<?php echo $days_to_show; ?>',3600);resetSet();">
				<?php } ?>
          <input name="settimes" class="easy-button grey" value="15" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',15);resetSet();">
				<input name="settimes" class="easy-button grey" value="30" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',30);resetSet();">
				<input name="settimes" class="easy-button grey" value="45" type="button" onclick="easyRes_sendReq_Overview('<?php echo $days_after_present; ?>','',45);resetSet();">
			</span>
  </th>
</tr>
<tr id="overviewTheadTr">
    <td class="h1overview" rowspan="2">
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-($days_to_skip);?>','no', '<?php echo $days_to_show; ?>');" title="-<?php echo ($days_to_skip).' '.$days_string; ?>" class="fa fa-fast-backward easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-round($days_to_skip/2);?>','no', '<?php echo $days_to_show; ?>');" title="-<?php echo round($days_to_skip/2).' '.$days_string; ?>" class="fa fa-backward easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present-round($days_to_skip/3);?>','no', '<?php echo $days_to_show; ?>');" title="-<?php echo round($days_to_skip/3).' '.$days_string; ?>" class="fa fa-step-backward easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('0','no', '<?php echo $days_to_show; ?>');" title="<?php _e('Present', 'easyReservations'); ?>" class="fa fa-pause easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+round($days_to_skip/3);?>','no', '<?php echo $days_to_show; ?>');" title="+<?php echo round($days_to_skip/3).' '.$days_string; ?>" class="fa fa-step-forward easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+round($days_to_skip/2);?>','no', '<?php echo $days_to_show; ?>');" title="+<?php echo round($days_to_skip/2).' '.$days_string; ?>" class="fa fa-forward easy-tooltip" style="font-size: 18px"></a>
        <a onclick="easyRes_sendReq_Overview('<?php echo $days_after_present+($days_to_skip);?>','no', '<?php echo $days_to_show; ?>');" title="+<?php echo ($days_to_skip).' '.$days_string; ?>" class="fa fa-fast-forward easy-tooltip" style="font-size: 18px"></a>
    </td>
	<?php
	$co=0;
	$add_to_date = 0;
	$last_date = 0;
	$last_month = '';
	$month_display = '';
	while($co < $days_to_show){
		$current_date = easyreservations_calculate_out_summertime($timesx+($interval*$co)+$add_to_date, $timesx);
		if($interval == 3600){
			if((isset($overview_options['overview_hourly_start']) && date("H", $current_date) < $overview_options['overview_hourly_start']) ||
				 (isset($overview_options['overview_hourly_end']) && date("H", $current_date) > $overview_options['overview_hourly_end'])){
				$add_to_date += $interval;
				continue;
			}
		}
		$background_highlight = '';
		if($interval == 86400 || date("d.m.Y", $last_date) !== date("d.m.Y", $current_date)){
			$class = '';
			if($interval == 3600){
				$class = " first-of-month";
				$tomorrow = easyreservations_calculate_out_summertime(strtotime(date("d.m.Y", $current_date))+86400, strtotime(date("d.m.Y", $current_date)));
				$diff = round(($tomorrow - $current_date)/$interval);
				if(isset($overview_options['overview_hourly_end'])){
					$diff -= 23-$overview_options['overview_hourly_end'];
				}
			} else {
				if(date("d", $current_date) ==  "1") $class = " first-of-month";
				$diff = 1;
				if(isset($reservation_arrival_stamp) && $current_date >= strtotime(date('d.m.Y 00:00', $reservation_arrival_stamp)) && $current_date <= strtotime(date('d.m.Y 23:59', $reservation_departure_stamp))) $background_highlight='backgroundhighlight';
			}
			if(date($date_pat, $current_date) ==  date($date_pat, current_time( 'timestamp' ))) $background_highlight .= ' today';
			if(date("N", $current_date) ==  6 || date("N", $current_date) ==  7) $background_highlight .= ' weekend';
			?>
        <td onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$current_date); ?>');easyRes_sendReq_Overview('<?php echo $days_after_present+$co;?>','no', '<?php echo $days_to_show; ?>')" colspan="<?php echo $diff; ?>" class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style.$class; ?>" style="vertical-align:middle;padding:1px 0;<?php if($interval == 86400) echo 'min-width:20px;'; ?>">
					<a href="javascript:void(0)">
					<?php if($interval == 86400){
							echo date("j",$current_date).'<br><small>'.$days[date("N",$current_date)-1];
						}  else {
							echo '<small style="overflow:hidden;display:inline-block;">'.date(RESERVATIONS_DATE_FORMAT, $current_date);
						}
						echo '</small>';

					?>
					</a>
        </td><?php
		}
		if($interval > 3600){
			$current_month = date('m', $current_date);
			if($current_month !== $last_month){
				$year = date('Y', $current_date);
				$d = cal_days_in_month(CAL_GREGORIAN, $current_month, $year) - intval(date('d', $current_date)) + 1;
				$month_display .= '<td colspan="'.$d.'">'.er_date_get_label(1,0,$current_month-1).' '.$year.'</td>';
				$last_month = $current_month;
			}
		}

		$co++;
		$last_date = $current_date;
	} ?>
</tr>
<?php if($interval > 3600){ ?>
	<tr class="month_display">
		<?php echo $month_display; ?>
	</tr>
<?php } ?>

<?php if($interval == 3600){ ?><tr><?php
	$co=0;
	$add_to_date = 0;
	$last_date = 0;
	while($co < $days_to_show){
		$current_date = easyreservations_calculate_out_summertime($timesx+($interval*$co)+$add_to_date, $timesx);

		$class = '';
		if(date("d.m.Y", $current_date) !==  date("d.m.Y", $last_date)) $class = " first-of-month";

		if((isset($overview_options['overview_hourly_start']) && date("H", $current_date) < $overview_options['overview_hourly_start']) ||
			(isset($overview_options['overview_hourly_end']) && date("H", $current_date) > $overview_options['overview_hourly_end'])){
			$add_to_date += $interval;
			continue;
		}
		$background_highlight = 'ov-days-hours';
		if(isset($reservation_arrival_stamp) && strtotime(date('d.m.Y H:00', $reservation_arrival_stamp)) <= $current_date &&
		   strtotime(date('d.m.Y H:59', $reservation_departure_stamp)) >= $current_date){
				$background_highlight .= ' backgroundhighlight';
		} else {
			if(date("H", $current_date) == 00) $background_highlight.=' first';
			if(date("d.m.y H", $current_date) == date("d.m.y H", current_time( 'timestamp' ))) $background_highlight.=' today';
		}?>
      <td  class="<?php echo  $background_highlight; ?> ov-days <?php echo $date_style.$class; ?>" style="vertical-align:middle;min-width:23px" onclick="overviewSelectDate('<?php echo date(RESERVATIONS_DATE_FORMAT,$current_date); ?>');">
				<?php echo '<small>'.date("H", $current_date).'</small>'; ?>
      </td><?php
		$last_date = $current_date;
		$co++;
	} ?>
</tr>
	<?php } ?>
</thead>
<tfoot>
<tr>
    <th colspan="<?php echo $days_to_show+1; ?>" class="overviewFooter">
        <span style="vertical-align:middle;" id="resetdiv"></span>
			<span style="float:right;">
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'assets/images/blue_dot.png'; ?>">&nbsp;<small><?php _e('Past', 'easyReservations'); ?></small>
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'assets/images/green_dot.png'; ?>">&nbsp;<small><?php _e('Present', 'easyReservations'); ?></small>
				<img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'assets/images/red_dot.png'; ?>">&nbsp;<small><?php _e('Future', 'easyReservations'); ?></small>
				<?php if(isset($id)){ ?> <img style="vertical-align:text-bottom;" src="<?php echo RESERVATIONS_URL.'assets/images/yellow_dot.png'; ?>">&nbsp;<small><?php _e('Active', 'easyReservations'); ?></small><?php } ?>
			</span>
    </th>
</tr>
</tfoot>
<tbody>
<?php
if(isset($resource_id)) $all_resources = $wpdb->get_results("SELECT ID, post_title FROM ".$wpdb->prefix."posts WHERE ID='$resource_id'");
else $all_resources = $show_rooms;

foreach( $all_resources as $key => $resource){ /* - + - FOREACH ROOM - + - */
	$res = new ER_Reservation(false, array('arrival' => $timesx, 'departure' => $timesx + $interval, 'resource' => (int) $resource->ID ));
	$res->resource->interval = $interval;
	if(isset($resource_number)) unset($resource_number);
	if(isset( $settings['mergeres'])){
		if(is_array( $settings['mergeres']) && isset( $settings['mergeres']['merge']) && $settings['mergeres']['merge'] > 0) $resource_number = $settings['mergeres']['merge'];
		elseif(is_numeric( $settings['mergeres']) && $settings['mergeres'] > 0) $resource_number = $settings['mergeres'];
	}
	$roomcount = get_post_meta($resource->ID, 'roomcount', true);
	$persons_query = 'adults+children as persons';
	if(is_array($roomcount)){
		if(!isset($resource_number)) $resource_number = $roomcount[0];
		$avail_by_person = $roomcount[1];
		if($avail_by_person == 'adult'){
			$persons_query = 'adults as persons';
		} elseif($avail_by_person == 'children'){
			$persons_query = 'children as persons';
		}
	} else {
		$avail_by_person = false;
		if(!isset($resource_number)) $resource_number = $roomcount;
	}
	$resource_names = get_post_meta($resource->ID, 'easy-resource-roomnames', TRUE);
	$row_count=0;
	$query = $wpdb->prepare("SELECT id, name, departure, arrival, space, $persons_query FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND resource=$resource->ID AND (arrival BETWEEN %s AND '$end_date' OR departure BETWEEN %s AND '$end_date' OR %s BETWEEN arrival AND departure) ORDER BY space ASC, arrival ASC", $start_date, $start_date, $start_date);
	$resource_sql = $wpdb->get_results($query);
	if(isset($reservations)) unset($reservations);
	foreach($resource_sql as $resourc){
		if($avail_by_person){
			$reservations[1][] = $resourc;
		} elseif(!empty($resourc->space)){
			$reservations[$resourc->space][] = $resourc;
			$co=0;
		}
	} ?>
<tr class="ov_resource_row">
    <td nowrap>
	    <a href="admin.php?page=reservation-resources&resource=<?php echo $resource->ID; ?>"><?php echo substr(__(stripslashes($resource->post_title)),0,20); ?></a>
    </td>
	<?php
	$co=0;
	$last_date = 0;
	$add_to_date = 0;
	while($co < $days_to_show){
		$current_date = easyreservations_calculate_out_summertime($timesx+($co*$interval)+$add_to_date, $timesx);
		if($interval == 3600){
			if((isset($overview_options['overview_hourly_start']) && date("H", $current_date) < $overview_options['overview_hourly_start']) ||
				(isset($overview_options['overview_hourly_end']) && date("H", $current_date) > $overview_options['overview_hourly_end'])){
				$add_to_date += $interval;
				continue;
			}
		}
		$text_color='#118D18';

		$res->arrival = $current_date;
		$res->departure = $res->arrival+$interval;
		$res->times = 1;
		$roomDayPersons = round($resource_number-$res->checkAvailability(3),1);
		if($roomDayPersons <= 0) $text_color='#FF3B38';

		$class = '';
		if(($interval == 3600 && date("d.m.Y", $current_date) !== date("d.m.Y", $last_date)) || ($interval == 86400 && date("d", $current_date) ==  "1")) $class = "first-of-month";
		if(date($date_pat, $current_date) == date($date_pat, current_time( 'timestamp' ))) $class .= " today";
		$last_date = $current_date;
		?><td axis="<?php echo $co+2;?>" class="<?php echo $class; ?>" style="color:<?php echo $text_color; ?>" >
			<?php echo '<small>'.$roomDayPersons.'</small>'; ?>
		</td><?php
		$co++;
	} ?>
</tr><?php
	while($resource_number > $row_count){  /* - + - FOREACH EXACTLY ROOM - + - */
		if($avail_by_person){
			if($row_count > 0) break;
			$name = __( stripslashes($resource->post_title));
		} else {
			if(isset($resource_names[$row_count]) && !empty($resource_names[$row_count])) $name = __($resource_names[$row_count]);
			else $name = '#'.($row_count+1);
		}
		$name = substr($name,0,20);
		$row_count++;

		if($timesx < current_time( 'timestamp' )) $bg_color_last='#63a7fb';
		else $bg_color_last='#fa6e5a';

		if($row_count == $resource_number) $borderbottom=0;
		else $borderbottom=1; ?>
			<tr id="resource<?php echo $row_count.'-'.$resource->ID; ?>">
				<td class="resource_header" style="color:#8C8C8C;" onclick="<?php if(isset($edit)){ ?>document.getElementById('datepicker').value='<?php echo date("d.m.Y",$reservation_arrival_stamp); ?>';document.getElementById('datepicker2').value='<?php echo date("d.m.Y",$reservation_departure_stamp); ?>';setVals2(<?php echo $resource->ID; ?>,<?php echo $row_count; ?>);<?php } if(isset($edit) || isset($approve)){ ?>changer();clickOne(document.getElementById('<?php echo $resource->ID.'-'.$row_count.'-'.$numberhighstart; ?>'),'<?php echo $reservation_arrival_stamp; ?>');clickTwo(document.getElementById('<?php echo $resource->ID.'-'.$row_count.'-'.$numberlaststart; ?>'),'<?php echo $reservation_departure_stamp; ?>');<?php } if(isset($approve)){ ?>document.reservation_approve.resource_space.selectedIndex=<?php echo $row_count-1; ?>;<?php } ?>"  nowrap><?php echo $name; ?></td><?php
		$count_res_nights_2=0; $count_res_nights_3=0; $count_number_add=0; $wasFull=0; $countdifferenz=0; $itIS=0; $cell_count=0; $datesHalfOccupied = array(); $personsOccupied = array();$numberOccupied = array();
		if(isset($reservations[$row_count])){
			foreach($reservations[$row_count] as $reservation){
				$res_id=$reservation->id;
				$res_name=$reservation->name;
				$res_arrival = strtotime($reservation->arrival);
				$res_departure= strtotime($reservation->departure);
				if(date($date_pat, $res_departure) == date($date_pat, $res_arrival)){
					$res_nights = 0;
					if($interval == 3600) $temp_date_pat = $date_pat.':i';
					else $temp_date_pat = $date_pat;

					$date_arrival = strtotime(date($temp_date_pat,$res_arrival));


					$add_to_date = 0;
					if($interval == 3600 && isset($overview_options['overview_hourly_start'])){
						if(date("d.m.Y", $timesx) !== date("d.m.Y", $date_arrival)){
							$add_to_date += 23 - $overview_options['overview_hourly_end'];

							$date1 = new DateTime(date($temp_date_pat,$timesx));
							$date2 = new DateTime(date($temp_date_pat,$date_arrival));
							$diff = intval($date2->diff($date1)->format("%a")-1);
							if($diff >  0){
								$add_to_date = $add_to_date + ($diff * (23-$hours_per_day));
							}

							$add_to_date += $overview_options['overview_hourly_start'];
						}
						$round = round(($date_arrival-$timesx)/$interval)-$add_to_date+1;
					} else {
						$round = round(($date_arrival+$interval-$timesx)/$interval);
					}

					if($avail_by_person){
						if(isset($datesHalfOccupied[$round]['i'])) $datesHalfOccupied[$round]['i'] += $reservation->persons;
						else $datesHalfOccupied[$round]['i'] = $reservation->persons;
					} else {
						if(isset($datesHalfOccupied[$round]['i'])) $datesHalfOccupied[$round]['i'] += 1;
						else $datesHalfOccupied[$round]['i'] = 1;
					}
					if(isset($datesHalfOccupied[$round]['v'])) $datesHalfOccupied[$round]['v'] .= date('d.m H:i', $res_arrival).' - '.date('d.m H:i', $res_departure).' <b>'.$res_name.'</b> (#'.$res_id.')<br>';
					else $datesHalfOccupied[$round]['v'] = date('d.m H:i', $res_arrival).' - '.date('d.m H:i', $res_departure).' <b>'.$res_name.'</b> (#'.$res_id.')<br>';
					$datesHalfOccupied[$round]['id'][] = $res_id;
					if(isset($personsOccupied[date($date_pat, $round+$interval)])) $personsOccupied[date($date_pat, $round+$interval)] += $reservation->persons;
					else $personsOccupied[date($date_pat, $round+$interval)] = $reservation->persons;
				} else {
					$date_pattern = $date_pat;
					if($interval == 3600) $date_pattern.=":00";
					$res_nights = round((strtotime(date($date_pattern,$res_departure)) - strtotime(date($date_pattern,$res_arrival))) / $interval);
					for($i=0; $i <= $res_nights; $i++){
						if($timesx <= $res_arrival+($i*$interval) && $res_nights >= 1){
							$date = easyreservations_calculate_out_summertime($res_arrival+($i*$interval)+$interval, $res_arrival);
							$daysOccupied[] = date($date_pat, $date);
							$numberOccupied[] = $countdifferenz;
							if($avail_by_person){
								if(isset($personsOccupied[date($date_pat, $date)])) $personsOccupied[date($date_pat, $date)] += $reservation->persons;
								else $personsOccupied[date($date_pat, $date)] = $reservation->persons;
							}
						}
					}
				}
				$reservation_array[]=array( 'name' =>$res_name, 'ID' =>$res_id, 'departure' => $res_departure, 'arDate' => $res_arrival, 'nights' => $res_nights );
				$countdifferenz++;
			}
		}

		$showdatenumber_start= 0;
		$add_to_date = 0;
		$last_date = 0;
		$lastDay = 0;

		$onClick = 0;
		while($showdatenumber_start < $days_to_show){
			$cell_count++;
			$showdatenumber_start++;
			$dateToday = easyreservations_calculate_out_summertime($timesx+($interval*$showdatenumber_start)+$add_to_date, $timesx);
			if($interval == 3600){
				if((isset($overview_options['overview_hourly_start']) && date("H", $dateToday-$interval) < $overview_options['overview_hourly_start']) ||
					 (isset($overview_options['overview_hourly_end']) && date("H", $dateToday-$interval) > $overview_options['overview_hourly_end'])){
					$add_to_date += $interval;
					$cell_count--;
					$showdatenumber_start--;
					continue;
				}
			}

			$wasFullTwo = 0;
			$border_side = 1;
			$onClick = 0;
			$table_click = '';
			if($cell_count < 10) $cell_count_prepared='0'.$cell_count;
			else $cell_count_prepared=$cell_count;
			if($dateToday < current_time( 'timestamp' )) $bg_pattern = "url(".RESERVATIONS_URL ."assets/images/patbg.png?cond=".current_time( 'timestamp' ).") center center repeat";
			else $bg_pattern = '';
			$res->arrival = $dateToday-$interval;
			$res->times = 1;
			$avail = $res->filter_availability($resource_number, 0);

			if(date($date_pat, $dateToday-$interval)==date($date_pat, current_time( 'timestamp' ))) $bg_color_free = '#f7f8f9';
			elseif($avail > 0) $bg_color_free = '#ffeeee';
			elseif(date("N", $dateToday-$interval)==6 OR date("N", $dateToday-$interval)==7) $bg_color_free = '#ffffda';
			else $bg_color_free = '#FFFFFF';

			$cell_class = '';
			if(($interval == 3600 && date("d.m.Y", $dateToday-$interval) !== date("d.m.Y", $last_date)) || ($interval == 86400 && date("d", $dateToday-$interval+1) ==  "1")){
				$cell_class = ' first-of-month';
			} elseif(date($date_pat, $dateToday-$interval) == date($date_pat, current_time( 'timestamp' ))){
				$cell_class = ' today';
			}
			$last_date = $dateToday-$interval;

			if($avail_by_person){
				$res_day_count = 0;
				if(isset($datesHalfOccupied[$cell_count])) $res_day_count += $datesHalfOccupied[$cell_count]['i'];
				if(isset($personsOccupied[date($date_pat, $dateToday)])) $res_day_count += $personsOccupied[date($date_pat, $dateToday)];
				if($res_day_count > 0){
					$table_click = 'jQuery(\'#easy_table_resource_selector\').val('.$resource->ID.');document.getElementById(\'easy-table-search-date\').value = \''.date(RESERVATIONS_DATE_FORMAT, $dateToday-$interval).'\';easyreservations_send_table(\'all\', 1);';

					$percent = 100/$resource_number*$res_day_count;
					if($percent > 95) $bg_color_back = '#fa6e5a';
					elseif($percent > 70) $bg_color_back = '#faa55a';
					elseif($percent > 35) $bg_color_back = '#89bd5f';
					else $bg_color_back = '#aade7f';
					$background = $bg_color_back;

					$onclick = '';
					if(isset($nonepage) && !empty($table_click)){
						$onclick = $table_click;
					} elseif((isset($edit) || isset($add)) && $onClick==0) {
						$onclick = "changer();clickTwo(this, false);clickOne(this);setVals2($resource->ID,$row_count);";
					}
					echo easyreservations_overview_cell(
						$resource->ID.'-'.$row_count.'-'.$cell_count_prepared,
						'do-not-delete person '.$cell_class,
						$dateToday-$interval,
						$cell_count+1,
						'background:'.$bg_color_back.'; cursor:pointer;',
						$overview_options['overview_onmouseover'] == 1 ? 'hoverEffect(this, false);' : '',
						$onclick,
						false,
						$res_day_count,
						'',
						isset($datesHalfOccupied[$cell_count]) ? $datesHalfOccupied[$cell_count]['v'] : '',
						$bg_color_back,
						$background
					);
				} else {
					echo easyreservations_overview_cell($resource->ID.'-'.$row_count.'-'.$cell_count_prepared, $cell_class, $dateToday-$interval, $cell_count+1, 'background:'.$bg_pattern.' '.$bg_color_free, $overview_options['overview_onmouseover'] == 1 ? 'hoverEffect(this);' : '', isset($edit) || isset($add) || isset($nonepage) ? 'changer();clickTwo(this);clickOne(this);setVals2('.$resource->ID.','.$row_count.');' : '');
				}
				continue;
			}

			$create_cell = false;
			if(isset($daysOccupied)){
				if(in_array(date($date_pat, $dateToday), $daysOccupied)){
					$class = '';
					$onClick = '';
					if($numberOccupied[$count_res_nights_3] !== $count_number_add && $cell_count !== 1){
						$count_number_add++;
					}

					//if(isset($reservation_array[$CountNumberOfAdd]['nights']) && $reservation_array[$CountNumberOfAdd]['nights'] < 1) while($reservation_array[$CountNumberOfAdd]['nights'] < 1) $CountNumberOfAdd++;
					$arrival = $reservation_array[$count_number_add]['arDate'];
					$departure = $reservation_array[$count_number_add]['departure'];
					$nights = $reservation_array[$count_number_add]['nights'];

					if(isset($daysOccupied[$count_res_nights_3+1]) && isset($numberOccupied[$count_res_nights_3-1]) && $numberOccupied[$count_res_nights_3-1] !== $daysOccupied[$count_res_nights_3] && $numberOccupied[$count_res_nights_3-1] !== $numberOccupied[$count_res_nights_3])$wasFullTwo=1;

					if(($count_res_nights_2 == 0 && $cell_count !== 1) || ($wasFullTwo == 1 && $cell_count !== 1) || $dateToday - $arrival <= $interval){
						$bg_color_occ="url(".RESERVATIONS_URL ."assets/images/1REPLACE_start.png) center top no-repeat";
						$cell_class .= ' cross';
						$itIS=0;
					} elseif($count_res_nights_2 !== 0 || $cell_count == 1 || (isset($daysOccupied[$count_res_nights_3]) && $lastDay==$daysOccupied[$count_res_nights_3])){
						$bg_color_occ="url(".RESERVATIONS_URL ."assets/images/1REPLACE_middle.png) top repeat-x";
						if($cell_count != 1) $border_side=0;
						$itIS++;
					}

					if(isset($daysOccupied[$count_res_nights_3+1]) && $daysOccupied[$count_res_nights_3] !== $daysOccupied[$count_res_nights_3+1] && $numberOccupied[$count_res_nights_3] !== $numberOccupied[$count_res_nights_3+1]){
						$bg_color_occ="url(".RESERVATIONS_URL ."assets/images/1REPLACE_end.png) center top no-repeat";
						$itIS=$nights;
						$cell_class .= ' noname';
					}
					if(isset($daysOccupied[$count_res_nights_3+1]) && $daysOccupied[$count_res_nights_3] === $daysOccupied[$count_res_nights_3+1] && array_key_exists($count_res_nights_3+1, $daysOccupied)){
						$bg_color_occ='url('.RESERVATIONS_URL .'assets/images/1REPLACE_cross.png) center top no-repeat, 2REPLACE';
						$cell_class .= ' cross';
						$count_res_nights_2=0;
						$count_res_nights_3++;
						$count_number_add++;
						$arrival = $reservation_array[$count_number_add]['arDate'];
						$departure = $reservation_array[$count_number_add]['departure'];
						$nights = $reservation_array[$count_number_add]['nights'];
						$itIS = 0;
						$onClick = 1;
					}

					if(!in_array(date($date_pat, $dateToday+$interval), $daysOccupied) || isset($datesHalfOccupied[$dateToday-$interval])){
						$bg_color_occ="url(".RESERVATIONS_URL ."assets/images/1REPLACE_end.png) center top no-repeat";
						$itIS=$nights;
						if(isset($datesHalfOccupied[$dateToday-$interval])){
							$exp = explode(",", $bg_color_occ);
							$bg_color_occ = $exp[0];
							$cell_class .= ' cross';
							$bg_color_occ.=", url(".RESERVATIONS_URL ."assets/images/1REPLACE_start.png) center top no-repeat";
						} else {
							$cell_class .= ' noname';
						}
					}

					$count_res_nights_2++;
					$count_res_nights_3++;
					$add_name = '';
					$lastDay = $daysOccupied[$count_res_nights_3-1];
					$bg_color_string = false;
					if(isset($id) && $reservation_array[$count_number_add]['ID'] == $id){
						$bg_color_occ=str_replace("1REPLACE", "yellow", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#ffbb3f';
						$add_name = ' name="activeres"';
					} elseif($arrival < current_time( 'timestamp' ) && $departure > current_time( 'timestamp' )){
						$bg_color_occ=str_replace("1REPLACE", "green", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_string = "green";

						$bg_color_back='#89bd5f';
					} elseif($arrival > current_time( 'timestamp' )){
						$bg_color_occ=str_replace("1REPLACE", "red", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_string = "red";

						$bg_color_back='#fa6e5a';
					} else {
						$bg_color_occ=str_replace("1REPLACE", "blue", $bg_color_occ);
						$bg_color_occ=str_replace("2REPLACE", $bg_color_last, $bg_color_occ);
						$bg_color_back='#63a7fb';
						$bg_color_string = "blue";
					}

					$minus_days = 0;
					$nightsproof = $nights;
					if($arrival < $timesx){
						$daybetween = ($timesx-$arrival)/$interval;
						$minus_days = floor($daybetween);
						$nightsproof = $nights-$minus_days;
					}
					if($departure > $timesy) {
						$daybetween = ($timesy/$interval)-(($arrival/$interval)+$nights);
						$minus_days += intval(substr(round($daybetween), 1, 10));
						$nightsproof = $nights-$minus_days;
					}

					$title_one = 	date('d.m H:i', $arrival).' - '.date('d.m H:i', $departure).' <b>'.$reservation_array[$count_number_add]['name'].'</b> (#'.$reservation_array[$count_number_add]['ID'].')<br>';
					$value = ''; $title = '';
					if($border_side == 0 ){
						$cell_class .= ' er_overview_cell';
						$title .= $title_one;
					}
					if(isset($datesHalfOccupied[$cell_count])){
						$value = $datesHalfOccupied[$cell_count]['i'];
						$title .= $datesHalfOccupied[$cell_count]['v'];
						$table_click = 'document.getElementById(\'easy-table-search-field\').value = \''.$reservation_array[$count_number_add]['ID'].','.implode(',', $datesHalfOccupied[$cell_count]['id']).'\';easyreservations_send_table(\'all\', 1);';
						//$bg_color_occ = substr($bg_color_occ, 0, -7).$bg_color_free;
					}
					$name_to_display = substr($reservation_array[$count_number_add]['name'], 0, max(($nights - $minus_days) * 7,2));

					$onclick = '';
					if(($itIS > 0 && $itIS < $nightsproof) || ($itIS == 0 && $nightsproof == 1)){
						$onclick = ' location.href = \'admin.php?page=reservations&edit='.$reservation_array[$count_number_add]['ID'].'\';';
						$cell_class .= ' do-not-cross';
					} elseif(isset($nonepage) && !empty($table_click)){
						$onclick .= $table_click;
					} elseif((isset($edit) || isset($add) || isset($nonepage)) && $onClick==0) {
						$onclick = 'changer();clickTwo(this, false, \''.$bg_color_string.'\');clickOne(this);setVals2('.$resource->ID.','.$row_count.');';
					}

					echo easyreservations_overview_cell(
						$resource->ID.'-'.$row_count.'-'.$cell_count_prepared,
						'do-not-delete '.$cell_class,
						$dateToday-$interval,
						$cell_count+1,
						'background:'.$bg_pattern.' '.$bg_color_free.';cursor: pointer;',
						$overview_options['overview_onmouseover'] == 1 ? 'hoverEffect(this, false, \''.$bg_color_string.'\');' : '',
						$onclick,
						$add_name,
						$itIS === 0 || $cell_count_prepared == 1 ? $name_to_display : false,
						'',
						$title,
						$bg_color_back,
						$bg_color_occ
					);

					$bg_color_last=$bg_color_back;
					$wasFull=1;
				} else {
					$count_res_nights_2 = 0;
					$create_cell = true;
					$wasFull=0;
				}
			} else {
				$create_cell = true;
			}
			if($create_cell){
				$value = false; $title = false; $bg_color_string = false; $bg_color_back = $bg_pattern.' '.$bg_color_free; $background = '';
				if(isset($datesHalfOccupied[$cell_count])){
					$count_number_add++;
					$value = $datesHalfOccupied[$cell_count]['i'];
					$title = $datesHalfOccupied[$cell_count]['v'];
					if($border_side == 0){
						$cell_class .= 'er_overview_cell';
					}
					$table_click = 'document.getElementById(\'easy-table-search-field\').value = \''.implode('|', $datesHalfOccupied[$cell_count]['id']).'\';easyreservations_send_table(\'all\', 1);';
					$cell_class .= ' do-not-delete';

					if(isset($id) && in_array($id, $datesHalfOccupied[$cell_count]['id'])){
						$bg_color_string = "yellow";
						$bg_color_back = '#ffbb3f';
						$add_name = ' name="activeres"';
					} elseif(date("d.m.Y", $dateToday-$interval) == date("d.m.Y", current_time( 'timestamp' ))){
						$bg_color_string = "green";
						$bg_color_back='#89bd5f';
					} elseif($dateToday-$interval > current_time( 'timestamp' )){
						$bg_color_string = "red";
						$bg_color_back='#fa6e5a';
					} else {
						$bg_color_back='#63a7fb';
						$bg_color_string = "blue";
					}
					$background = $bg_color_back;
				}

				$onclick = '';
				if(isset($nonepage) && !empty($table_click)){
					$onclick = $table_click;
				} elseif((isset($edit) || isset($add) || isset($nonepage)) && $onClick==0) {
					$onclick = 'changer();clickTwo(this, false, \''.$bg_color_string.'\');clickOne(this);';
					$onclick .= 'setVals2('.$resource->ID.','.$row_count.');';
				}
				echo easyreservations_overview_cell(
					$resource->ID.'-'.$row_count.'-'.$cell_count_prepared,
					$cell_class.' half',
					$dateToday-$interval,
					$cell_count+1,
					'background:'.$bg_color_back.'; cursor:pointer;',
					$overview_options['overview_onmouseover'] == 1 ? 'hoverEffect(this, false, \''.$bg_color_string.'\');' : '',
					$onclick,
					false,
					$value,
					'',
					$title,
					$bg_color_back,
					$background
				);
			}
		}
		unset($daysOccupied,$datesHalfOccupied,$numberOccupied,$reservation_array);
		echo '</tr>';
	}
} ?></tbody>
</table>