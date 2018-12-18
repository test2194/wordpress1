<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>
<form method="post" action="admin.php?page=reservation-resources&resource=<?php echo $resource->ID; ?>#slots"  id="slot" name="slot">
	<?php wp_nonce_field('easy-resource-slot' ); ?>
	<input type="hidden" name="slot_edit" id="slot_edit">
	<table class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:10px;width: 100%">
		<thead>
			<tr>
				<th colspan="2"><?php _e('Slot', 'easyReservations');?></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="2">
					<input type="submit" onclick="document.getElementById('slot').submit(); return false;" class="easy-button" value="<?php _e('Submit', 'easyReservations');?>">
				</td>
			</tr>
		</tfoot>
		<tbody>
			<tr>
				<td colspan="2" class="easy-description">
					Slots are predefined time ranges between which your guests can choose.
					As arrival and departure are set most requirements do not apply to slots.<br>
					The prices are for the whole duration of the slot.
					They are only selectable in the new [date] form field for now.
					<a onclick="jQuery('.paste-slot-input').toggleClass('hidden');" class="fa fa-paste easy-tooltip" style="float:right;" title="<?php echo sprintf(__('Paste %s', 'easyReservations'), __('slot', 'easyReservations')); ?>"></a>
					<input type="text" placeholder="Paste here" style="float:right" class="paste-slot-input hidden">
				</td>
			</tr>

			<tr>
				<td colspan="2" >
					<h2><?php _e('Slot', 'easyReservations');?></h2>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Label', 'easyReservations');?></b>
					<?php easyreservations_help( 'Will be displayed in the ' ); ?>
				</td>
				<td>
					<input type="text" id="slot_name" name="slot_name" style="width:296px">
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Active between', 'easyReservations');?></b>
					<?php easyreservations_help( 'When this slot is selectable for arrival' ); ?>
				</td>
				<td>
					<span class="input-wrapper">
						<input type="text" id="slot_range_from" name="slot_range_from" style="width:94px" autocomplete="off">
						<span class="input-box clickable"><span class="fa fa-calendar"></span></span>
					</span>
					and
					<span class="input-wrapper">
						<input type="text" id="slot_range_to" name="slot_range_to" style="width:94px" autocomplete="off">
						<span class="input-box clickable"><span class="fa fa-calendar"></span></span>
					</span>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Repeat', 'easyReservations');?></b>
					<?php easyreservations_help( 'Repeats the slot so you don\'t have to add it multiple times. This only works for short slots (duration = 0) and cannot reach into the next day.' ); ?>
				</td>
				<td>
					Repeat this <span id="slot_duration_display"></span> hour slot
					<span class="select"><select name="slot_repeat_amount" id="slot_repeat_amount">
						<?php echo er_form_number_options(0, 50); ?>
					</select></span>
					times with
					<span class="select"><select name="slot_repeat_break" id="slot_repeat_break"><?php echo er_form_number_options(0, 600); ?></select></span>
					minute breaks in between until <span id="slot_repeat_end"></span>
				</td>
			</tr>

			<tr>
				<td colspan="2" >
					<h2><?php _e('Arrival', 'easyReservations');?></h2>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Week days', 'easyReservations');?></b>
				</td>
				<td>
					<div class="blockquote">
						<?php echo easyreservations_days_options('slot_days[]', 9); ?>
					</div>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Time', 'easyReservations');?></b>
				</td>
				<td>
					<select name="slot-from-hour" id="slot-from-hour"><?php echo er_form_time_options(12); ?></select>
					<select name="slot-from-min" id="slot-from-min"><?php echo er_form_number_options("00", 59); ?></select>
				</td>
			</tr>

			<tr>
				<td colspan="2" >
					<h2><?php _e('Departure', 'easyReservations');?></h2>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Duration', 'easyReservations');?></b>
				</td>
				<td>
					<span class="select">
						<select name="slot_duration" id="slot_duration">
							<?php echo er_form_number_options(0, 250, 1); ?>
						</select>
					</span>
					<?php echo er_date_get_interval_label(86400); ?>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Time', 'easyReservations');?></b>
				</td>
				<td>
					<select name="slot-to-hour" id="slot-to-hour"><?php echo er_form_time_options(12); ?></select>
					<select name="slot-to-min" id="slot-to-min"><?php echo er_form_number_options("00", 59); ?></select>
				</td>
			</tr>
			<!--
			<tr>
				<td colspan="2" >
					<h2><?php // echo ucfirst(__('persons','easyReservations')); ?></h2>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b>
						<?php //_e('Adults', 'easyReservations');?>
					</b>
				</td>
				<td>
					<span class="input-wrapper select">
						<span class="input-box"><?php //_e('Min', 'easyReservations'); ?></span><select name="slot_min_adults" id="slot_min_adults">
							<?php //echo er_form_number_options(1, 250, 1); ?>
						</select>
					</span> -
					<span class="input-wrapper select">
						<span class="input-box"><?php //_e('Max', 'easyReservations'); ?></span><select name="slot_max_adults" id="slot_max_adults">
							<option value="0" >&infin;</option>
							<?php //echo er_form_number_options(1, 250); ?>
						</select>
					</span>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b>
						<?php //_e('Children', 'easyReservations');?>
					</b>
				</td>
				<td>
					<span class="input-wrapper select">
						<span class="input-box"><?php //_e('Min', 'easyReservations'); ?></span><select name="slot_min_children" id="slot_min_children">
							<?php //echo er_form_number_options(0, 250, 1); ?>
						</select>
					</span> -
					<span class="input-wrapper select">
						<span class="input-box"><?php //_e('Max', 'easyReservations'); ?></span><select name="slot_max_children" id="slot_max_children">
							<option value="0" >&infin;</option>
							<?php //echo er_form_number_options(1, 250); ?>
						</select>
					</span>
				</td>
			</tr>
			-->

			<tr>
				<td colspan="2" >
					<h2><?php _e('Price', 'easyReservations');?></h2>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Base price', 'easyReservations');?></b>
				</td>
				<td>
					<span class="input-wrapper">
						<input type="text" value="" style="width:60px;text-align:right" name="slot_base_price" id="slot_base_price">
						<span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span>
					</span>
				</td>
			</tr>
			<tr>
				<td class="label">
					<b><?php _e('Children price', 'easyReservations');?></b>
				</td>
				<td>
					<span class="input-wrapper">
						<input type="text" value="" style="width:60px;text-align:right" name="slot_children_price" id="slot_children_price">
						<span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span>
					</span>
				</td>
			</tr>

		</tbody>
	</table>
</form>
<script>
	jQuery(document).ready(function(){
		jQuery('#slot-from-hour, #slot-from-min, #slot-to-hour, #slot-to-min, #slot_duration, #slot_repeat_break, #slot_repeat_amount').bind('change', function(){
			setDuration();
		});

		function setDuration(){
			var arrival = parseFloat(jQuery('#slot-from-hour').val()) * 60 + parseFloat(jQuery('#slot-from-min').val());
			var departure = parseFloat(jQuery('#slot-to-hour').val()) * 60 + parseFloat(jQuery('#slot-to-min').val());
			var duration = departure + parseInt(jQuery('#slot_duration').val()) * 1440 - arrival;
			jQuery('#slot_duration_display').html(Math.round(duration/60*100)/100);
			if(duration > 0 && duration < 721){
				var i = 0;
				var test = arrival;
				var slot_break = parseInt(jQuery('#slot_repeat_break').val());
				while(test <= 1440){
					test += duration + slot_break;
					i++;
				}
				i = i - 2;

				var repeat_amount = jQuery('#slot_repeat_amount');
				var repeat = parseInt(repeat_amount.val());

				jQuery('#slot_repeat_amount option').attr('disabled', false);
				jQuery('#slot_repeat_amount option[value='+(i)+']').nextAll().attr('disabled', true);
				if(repeat >= i){
					repeat = i;
					repeat_amount.val( i );
				}

				var end = arrival + ( duration + slot_break ) * ( repeat + 1 );
				if(end < 1441){
					var hours = Math.floor( end / 60 );
					var minutes = end - hours * 60;
					if(minutes < 10) minutes = '0'+minutes;
					jQuery( '#slot_repeat_end' ).html( hours + ':' + minutes );
				} else {
					jQuery( '#slot_repeat_end' ).html( '' );
				}
			} else {
				jQuery('#slot_repeat_amount').val(0);
			}
		}

		setDuration();

		jQuery('.slot-edit').bind('click', function(e){
			var slot = jQuery(this).attr('data-slot');
			slot_edit(slot);
		});

		jQuery('.slot-copy').bind('click', function(e){
			var slot = slots[jQuery(this).attr('data-slot')];
			var aux = document.createElement("input");
			aux.setAttribute("value", JSON.stringify(slot));
			document.body.appendChild(aux);
			aux.select();
			document.execCommand("copy");
			document.body.removeChild(aux);
		});

		jQuery('.paste-slot-input').bind('input', function(e){
			var is_json = true;
			try {
				var json = jQuery.parseJSON(jQuery(this).val());
			} catch(err) {
				is_json = false;
			}

			if(is_json && json !== null && typeof json == 'object'){
				slot_edit(false, json);
				jQuery(this).val('').addClass('hidden');
			}
		});

		function slot_edit(i, single_filter) {
			var slot;
			if (i === false) {
				slot = single_filter;
			} else {
				slot = slots[i];
				jQuery('#slot_edit').val(parseInt(i)+1);
			}

			jQuery('#slot_name').val(slot['name']);
			jQuery('#slot_range_from').val(slot['range-from']);
			jQuery('#slot_range_to').val(slot['range-to']);
			jQuery('#slot_duration').val(slot['duration']);
			jQuery('#slot_min_adults').val(slot['adults-min']);
			jQuery('#slot_max_adults').val(slot['adults-max']);
			jQuery('#slot_min_children').val(slot['children-min']);
			jQuery('#slot_max_children').val(slot['children-max']);
			jQuery('#slot_base_price').val(slot['base-price']);
			jQuery('#slot_children_price').val(slot['children-price']);

			var hour = Math.floor(slot['from']/60);
			jQuery('#slot-from-hour').val(hour);
			jQuery('#slot-from-min').val(slot['from']-(hour*60));

			hour = Math.floor(slot['to']/60);
			jQuery('#slot-to-hour').val(hour);
			jQuery('#slot-to-min').val(slot['to']-(hour*60));

			if(slot['repeat']){
				jQuery('#slot_repeat_amount').val(slot['repeat']);
				jQuery('#slot_repeat').attr('checked', true);
			} else {
				jQuery('#slot_repeat').attr('checked', false);
			}

			var checkboxes = jQuery('input[name="slot_days[]"]');
			var count = 1;

			checkboxes.each(function(){
				var checked = false;
				if(jQuery.inArray(count, slot['days']) !== -1){
					checked = true;
				}
				jQuery(this).attr('checked', checked);
				count++;
			});

		}
	});
</script>