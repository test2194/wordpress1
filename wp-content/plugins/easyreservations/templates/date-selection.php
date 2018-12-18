<?php
defined( 'ABSPATH' ) || exit;

$arrival_time = isset($_POST['date-from-hour']) ? intval($_POST['date-from-hour']) * 3600 + (isset($_POST['date-from-min']) ? intval($_POST['date-from-min']) * 60 : 0) : false;
$departure_time = isset($_POST['date-to-hour']) ? intval($_POST['date-to-hour']) * 3600 + (isset($_POST['date-to-min']) ? intval($_POST['date-from-min']) * 60 : 0) : false;
?>
<div class="easy-date-selection easy-ui" id="easy_selection_<?php echo $uid; ?>">
    <?php wp_nonce_field( 'easy-date-selection', 'easy-date-selection-nonce' ); ?>
    <input type="hidden" name="slot" value="-1">
    <div class="header">
        <div class="departure">
            <?php _e('Departure', 'easyReservations'); ?>
            <span class="text">
                <span class="date"><?php if(isset($_POST['to'])) echo $_POST['to']; else echo '&#8212;';?></span>
                <span class="time"><?php if($departure_time) echo date(RESERVATIONS_TIME_FORMAT, $departure_time); ?></span>
            </span>
            <input type="hidden" name="to">
            <input type="hidden" name="date-to-hour">
            <input type="hidden" name="date-to-min">
        </div>
        <div class="arrival">
            <?php _e('Arrival', 'easyReservations'); ?>
            <span class="text">
                <span class="date"><?php if(isset($_POST['from'])) echo $_POST['from']; else _e('Select Date', 'easyReservations');;?></span>
                <span class="time"><?php if($arrival_time) echo date(RESERVATIONS_TIME_FORMAT, $arrival_time); ?></span>
            </span>
            <input type="hidden" name="from">
            <input type="hidden" name="date-from-hour">
            <input type="hidden" name="date-from-min">
        </div>
    </div>
    <div class="calendar">
        <div class="datepicker">

        </div>
        <input type="hidden" value="" name="datepicker-alt-field" id="datepicker-alt-field" />
        <div class="time-prototype" style="display:none;">
            <?php if($time_selection == 'time'): ?>
                <select name="time">
                    <?php echo er_form_time_options(false, $increment, $range); ?>
                </select>
            <?php else: ?>
                <select name="time-hour"><?php echo er_form_time_options(false); ?></select>
                <select name="time-min"><?php echo er_form_number_options("00",59); ?></select>
            <?php endif; ?>
            <span class="fa fa-check apply-time"></span>
        </div>
    </div>
</div>
<div id="easy-form-from"></div>
<div id="easy-form-to"></div>