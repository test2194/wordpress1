<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$amount_text = __('Amount of %s', 'easyReservations');
?>
<form method="post" id="filter_form" name="filter_form">
	<?php wp_nonce_field('easy-resource-filter','easy-resource-filter'); ?>
	<table class="<?php echo RESERVATIONS_STYLE; ?>" id="filter-table" style="margin-top:10px">
		<thead>
		<tr>
			<th>
				<?php _e('Filter', 'easyReservations'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<tr>
				<td class="easy-description">
					<?php _e('With filter you can change the price, availability and requirements by flexible conditions.', 'easyReservations'); ?>
					<a onclick="reset_filter_form()" class="fa fa-times" style="float:right;"></a>
					<a onclick="jQuery('.paste-input').toggleClass('hidden');" class="fa fa-paste easy-tooltip" style="float:right;" title="<?php echo sprintf(__('Paste %s', 'easyReservations'), __('filter', 'easyReservations')); ?>"></a>
					<input type="text" placeholder="Paste here" style="float:right" class="paste-input hidden">
				</td>
			</tr>
			<tr>
				<td>
					<ul class="easy-navigation filter-navigation">
						<li><a href="#" class="active" onclick="display_price_filter()"><?php _e('Price', 'easyReservations'); ?></a></li>
						<li><a href="#" onclick="display_availability_filter()"><?php _e('Unavailability', 'easyReservations'); ?></a></li>
						<li><a href="#" onclick="display_requirement_filter()"><?php _e('Requirements', 'easyReservations'); ?></a></li>
					</ul>
					</div>
					<input type="hidden" name="filter_type" id="filter_type">
				</td>
			</tr>
			<tr>
				<td id="filter_form_name" class="hide-it">
					<label class="in-hierarchy" style="min-width:65px;" for="filter_form_name_field"><?php _e('Label', 'easyReservations'); ?></label>
					<?php easyreservations_help( 'Can get displayed in the invoice or the receipt. ' ); ?>
					<input type="text" name="filter_form_name_field" id="filter_form_name_field">
				</td>
			</tr>
			<tr>
				<td id="filter_form_importance" class="hide-it">
					<label class="in-hierarchy" style="min-width:65px;" for="price_filter_imp"><?php _e('Priority', 'easyReservations'); ?></label>
					<?php easyreservations_help( 'The priority defines the order in which the filter get checked. They get sorted from low to high and only the first matched filter gets applied. Filter that change the base price get applied once per billing unit, whereas discount and extra charge filters only get applied once per filter condition type.' ); ?>
					<span class="select">
						<select name="price_filter_imp" id="price_filter_imp"><?php echo er_form_number_options(1,99); ?></select>
					</span>
				</td>
			</tr>
			<tr>
				<td id="filter_form_usetime" class="hide-it">
					<h2><?php _e('Condition', 'easyReservations'); ?></h2>
					<div class="easy-description">
						<?php _e('This condition must be met so the filter gets applied. Only the first matched filter for each type condition gets applied - sorted by priority from low to high.', 'easyReservations'); ?>
					</div>

					<label class="wrapper" style="margin-left:5px">
						<input type="checkbox" name="filter_form_usetime_checkbox" id="filter_form_usetime_checkbox" onclick="show_use_time();">
						<span class="input"></span> <?php echo sprintf(__('Filter by %s', 'easyReservations'), __('time', 'easyReservations')); ?>
					</label>
				</td>
			</tr>
		</tbody>
		<tbody id="filter_form_time_cond" class="hide-it">
		<tr>
			<td>
				<label class="wrapper">
					<input type="checkbox" name="price_filter_cond_range" id="price_filter_cond_range" value="range">
					<span class="input"></span> <?php _e('Date range', 'easyReservations'); ?>
				</label>
			</td>
		</tr>
		<tr>
			<td onclick="jQuery('#price_filter_cond_range').attr('checked', true);">
				<label class="in-hierarchy"><?php _e('From', 'easyReservations'); ?></label>
				<span class="input-wrapper">
					<input type="text" id="price_filter_range_from" name="price_filter_range_from" style="width:94px">
					<span class="input-box clickable"><span class="fa fa-calendar"></span></span>
				</span>
				<span class="together-wrapper input-wrapper">
					<select id="price-filter-range-from-hour" name="price-filter-range-from-hour"><?php echo er_form_time_options(12); ?></select>
					<select id="price-filter-range-from-min" name="price-filter-range-from-min"><?php echo er_form_number_options("00", 59); ?></select>
				</span><br>
				<label class="in-hierarchy"><?php _e('To', 'easyReservations'); ?></label>
				<span class="input-wrapper">
					<input type="text" id="price_filter_range_to" name="price_filter_range_to" style="width:94px">
					<span class="input-box clickable"><span class="fa fa-calendar"></span></span>
				</span>
				<span class="together-wrapper input-wrapper">
					<select id="price-filter-range-to-hour" name="price-filter-range-to-hour"><?php echo er_form_time_options(12); ?></select>
					<select id="price-filter-range-to-min" name="price-filter-range-to-min"><?php echo er_form_number_options("00", 59); ?></select>
				</span><br>
				<label class="wrapper" style="margin-left:16px">
					<input type="checkbox" name="price_filter_range_every" id="price_filter_range_every" value="1">
					<span class="input"></span> <?php _e('Apply every year', 'easyReservations'); ?>
				</label>

			</td>
		</tr>
		<tr>
			<td>
				<label class="wrapper">
					<input type="checkbox" name="price_filter_cond_unit" id="price_filter_cond_unit" value="unit">
					<span class="input"></span> <?php _e('Date unit', 'easyReservations'); ?>
				</label>
			</td>
		</tr>
		<tr>
			<td onclick="jQuery('#price_filter_cond_unit').attr('checked', true);" style="line-height: 20px">
				<span style="padding:2px 0 2px 18px;margin-top:5px;float:none"><b><u><?php _e('Hours', 'easyReservations'); ?></u></b></span><br>
				<span style="padding:2px 0 2px 18px;"><i><?php echo __('select nothing to change price/availability for entire', 'easyReservations').' '.er_date_get_interval_label(86400, 1);?></i></span><br>
				<span style="min-width:99%;display:block;float:left">
					<div style="padding:0 0 0 18px;margin:3px;width:60px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="0"><span class="input"></span>00:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="1"><span class="input"></span>01:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="2"><span class="input"></span>02:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="3"><span class="input"></span>03:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="4"><span class="input"></span>04:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="5"><span class="input"></span>05:00</label>
					</div>
					<div style="margin:3px;width:60px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="6"><span class="input"></span>06:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="7"><span class="input"></span>07:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="8"><span class="input"></span>08:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="9"><span class="input"></span>09:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="10"><span class="input"></span>10:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="11"><span class="input"></span>11:00</label>
					</div>
					<div style="margin:3px;width:60px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="12"><span class="input"></span>12:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="13"><span class="input"></span>13:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="14"><span class="input"></span>14:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="15"><span class="input"></span>15:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="16"><span class="input"></span>16:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="17"><span class="input"></span>17:00</label>
					</div>
					<div style="margin:3px;width: 60px;px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="18"><span class="input"></span>18:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="19"><span class="input"></span>19:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="20"><span class="input"></span>20:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="21"><span class="input"></span>21:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="22"><span class="input"></span>22:00</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_hour[]" value="23"><span class="input"></span>23:00</label>
					</div>
				</span>
				<span style="padding:2px 0 2px 18px;"><b><u><?php echo er_date_get_interval_label(86400, 0, true); ?></u></b></span><br>
				<span style="padding:2px 0 2px 18px;"><i><?php echo __('select nothing to change price/availability for entire', 'easyReservations').' '.__('calendar week', 'easyReservations'); ?></i></span><br>
				<span style="min-width:99%;display:block;float:left">
					<div style="padding:0 0 0 18px;margin:3px;width:98px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="1"><span class="input"></span><?php echo $days[0]; ?></label><br>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="2"><span class="input"></span><?php echo $days[1]; ?></label><br>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="3"><span class="input"></span><?php echo $days[2]; ?></label><br>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="4"><span class="input"></span><?php echo $days[3]; ?></label>
					</div>
					<div style="margin:3px;width:90px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="5"><span class="input"></span><?php echo $days[4]; ?></label><br>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="6"><span class="input"></span><?php echo $days[5]; ?></label><br>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_days[]" value="7"><span class="input"></span><?php echo $days[6]; ?></label><br>
					</div>
				</span>

				<span style="padding:2px 0 2px 18px;margin-top:5px;float:none"><b><u><?php _e('Calendar week', 'easyReservations'); ?></u></b></span><br>
				<span style="padding:2px 0 2px 18px;"><i><?php echo __('select nothing to change price/availability for entire', 'easyReservations').' '.er_date_get_interval_label(2592000, 1); ?></i></span><br>
				<span style="min-width:99%;display:block;float:left">
					<div style="padding:0 0 0 18px;margin:3px;width:40px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="1"><span class="input"></span>01</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="2"><span class="input"></span>02</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="3"><span class="input"></span>03</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="4"><span class="input"></span>04</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="5"><span class="input"></span>05</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="6"><span class="input"></span>06</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="7"><span class="input"></span>07</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="8"><span class="input"></span>08</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="9"><span class="input"></span>09</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="10"><span class="input"></span>10</label>
					</div>
					<div style="margin:3px;width:40px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="11"><span class="input"></span>11</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="12"><span class="input"></span>12</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="13"><span class="input"></span>13</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="14"><span class="input"></span>14</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="15"><span class="input"></span>15</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="16"><span class="input"></span>16</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="17"><span class="input"></span>17</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="18"><span class="input"></span>18</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="19"><span class="input"></span>19</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="20"><span class="input"></span>20</label>
					</div>
					<div style="margin:3px;width:40px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="21"><span class="input"></span>21</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="22"><span class="input"></span>22</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="23"><span class="input"></span>23</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="24"><span class="input"></span>24</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="25"><span class="input"></span>25</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="26"><span class="input"></span>26</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="27"><span class="input"></span>27</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="28"><span class="input"></span>28</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="29"><span class="input"></span>29</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="30"><span class="input"></span>30</label>
					</div>
					<div style="margin:3px;width:40px;float:left;">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="31"><span class="input"></span>31</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="32"><span class="input"></span>32</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="33"><span class="input"></span>33</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="34"><span class="input"></span>34</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="35"><span class="input"></span>35</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="36"><span class="input"></span>36</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="37"><span class="input"></span>37</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="38"><span class="input"></span>38</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="39"><span class="input"></span>39</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="40"><span class="input"></span>40</label>
					</div>
					<div style="margin:3px;width:40px;float:left">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="41"><span class="input"></span>41</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="42"><span class="input"></span>42</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="43"><span class="input"></span>43</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="44"><span class="input"></span>44</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="45"><span class="input"></span>45</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="46"><span class="input"></span>46</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="47"><span class="input"></span>47</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="48"><span class="input"></span>48</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="49"><span class="input"></span>49</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="50"><span class="input"></span>50</label>
					</div>
					<div style="margin:3px;width:40px;float:left">
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="51"><span class="input"></span>51</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="52"><span class="input"></span>52</label>
						<label class="wrapper"><input type="checkbox" name="price_filter_unit_cw[]" value="53"><span class="input"></span>53</label>
					</div>
				</span>

				<span style="padding:2px 0 2px 18px;margin-top:3px;float:none"><b><u><?php _e('Months', 'easyReservations'); ?></u></b></span><br>
				<span style="padding:2px 0 2px 18px;">
					<i><?php echo __('select nothing to change price/availability for entire', 'easyReservations').' '.__('quarter', 'easyReservations'); $months = er_date_get_label(1); ?></i>
				</span><br>
				<div style="padding:0 0 0 20px;">
					<label class="wrapper" style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="1"><span class="input"></span><?php echo $months[0]; ?></label>
					<label class="wrapper" style="width:86px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="2"><span class="input"></span><?php echo $months[1]; ?></label>
					<label class="wrapper" style="width:90px;"><input type="checkbox" name="price_filter_unit_month[]" value="3"><span class="input"></span><?php echo $months[2]; ?></label>
				</div>
				<div style="padding:0 0 0 20px;">
					<label class="wrapper" style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="4"><span class="input"></span><?php echo $months[3]; ?></label>
					<label class="wrapper" style="width:86px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="5"><span class="input"></span><?php echo $months[4]; ?></label>
					<label class="wrapper" style="width:90px;"><input type="checkbox" name="price_filter_unit_month[]" value="6"><span class="input"></span><?php echo $months[5]; ?></label>
				</div>
				<div style="padding:0 0 0 20px;">
					<label class="wrapper" style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="7"><span class="input"></span><?php echo $months[6]; ?></label>
					<label class="wrapper" style="width:86px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="8"><span class="input"></span><?php echo $months[7]; ?></label>
					<label class="wrapper" style="width:90px;"><input type="checkbox" name="price_filter_unit_month[]" value="9"><span class="input"></span><?php echo $months[8]; ?></label>
				</div>
				<div style="padding:0 0 0 20px;">
					<label class="wrapper" style="width:80px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="10"><span class="input"></span><?php echo $months[9]; ?></label>
					<label class="wrapper" style="width:86px;float:left"><input type="checkbox" name="price_filter_unit_month[]" value="11"><span class="input"></span><?php echo $months[10]; ?></label>
					<label class="wrapper" style="width:90px;"><input type="checkbox" name="price_filter_unit_month[]" value="12"><span class="input"></span><?php echo $months[11]; ?></label>
				</div>

				<span style="padding:2px 0 2px 18px;margin-top:3px"><b><u><?php echo ucfirst(__('quarter', 'easyReservations')); ?></u></b></span><br>
				<span style="padding:2px 0 2px 18px"><i><?php echo __('select nothing to change price/availability for entire', 'easyReservations').' '.__('year', 'easyReservations');  ?></i></span><br>
				<div style="padding:0 0 0 20px">
					<label class="wrapper" style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="1"><span class="input"></span>1</label>
					<label class="wrapper" style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="2"><span class="input"></span>2</label>
					<label class="wrapper" style="width:40px;float:left"><input type="checkbox" name="price_filter_unit_quarter[]" value="3"><span class="input"></span>3</label>
					<label class="wrapper" style="width:40px"><input type="checkbox" name="price_filter_unit_quarter[]" value="4"><span class="input"></span>4</label>
				</div>

				<span style="padding:2px 0 2px 18px;margin-top:3px"><b><u><?php echo ucfirst(__('year', 'easyReservations')); ?></u></b></span><br>
				<div style="padding:0 0 0 20px;">
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2015"><span class="input"></span>2015</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2016"><span class="input"></span>2016</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2017"><span class="input"></span>2017</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2018"><span class="input"></span>2018</label>
					<label class="wrapper" style="width:58px"><input type="checkbox" name="price_filter_unit_year[]" value="2019"><span class="input"></span>2019</label>
				</div>
				<div style="padding:0 0 0 20px">
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2020"><span class="input"></span>2020</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2021"><span class="input"></span>2021</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2022"><span class="input"></span>2022</label>
					<label class="wrapper" style="width:58px;float:left"><input type="checkbox" name="price_filter_unit_year[]" value="2023"><span class="input"></span>2023</label>
					<label class="wrapper" style="width:58px"><input type="checkbox" name="price_filter_unit_year[]" value="2024"><span class="input"></span>2024</label>
				</div>
			</td>
		</tr>
		</tbody>
		<tbody id="filter_form_requirements" class="hide-it">
		<tr>
			<td>
				<h2><?php _e('Requirements', 'easyReservations');?></h2>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"><?php echo ucfirst(er_date_get_interval_label($resource->interval, 2));?></label>
				<span style="display: inline-block">
					<span class="input-wrapper select">
						<span class="input-box"><?php _e('Min', 'easyReservations'); ?></span><select name="req_filter_min_nights" id="req_filter_min_nights">
							<?php echo er_form_number_options(1, 99, $resource->requirements['nights-min']); ?>
						</select>
					</span> -
					<span class="input-wrapper select">
						<span class="input-box"><?php _e('Max', 'easyReservations'); ?></span><select name="req_filter_max_nights" id="req_filter_max_nights">
							<option value="0" <?php selected($resource->requirements['nights-max'], 0)?>>&infin;</option>
							<?php echo er_form_number_options(1,99, $resource->requirements['nights-max']); ?>
						</select>
					</span>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"><?php echo ucfirst(__('persons','easyReservations'));?></label>
				<span style="display: inline-block">
					<span class="input-wrapper select">
						<span class="input-box"><?php _e('Min', 'easyReservations'); ?></span><select name="req_filter_min_pers" id="req_filter_min_pers">
							<?php echo er_form_number_options(1, 99, $resource->requirements['pers-min']); ?>
						</select>
					</span> -
					<span class="input-wrapper select">
						<span class="input-box"><?php _e('Max', 'easyReservations'); ?></span><select name="req_filter_max_pers" id="req_filter_max_pers">
							<option value="0" <?php selected($resource->requirements['pers-max'], 0)?>>&infin;</option>
							<?php echo er_form_number_options(1, 99, $resource->requirements['pers-max']); ?>
						</select>
					</span>
				</span>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"><?php _e('Arrival possible on', 'easyReservations');?></label>
				<div class="blockquote">
					<?php echo easyreservations_days_options('req_filter_start_on[]', 0); ?>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"> </label>
				<?php echo sprintf($hour_string, '<span class="select"><select name="filter-start-h0">'.er_form_time_options(0).'</select></span>', '<span class="select"><select name="filter-start-h1">'.er_form_time_options(23).'</select></span>'); ?>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"><?php _e('Departure possible on', 'easyReservations');?></label>
				<div class="blockquote">
					<?php echo easyreservations_days_options('req_filter_end_on[]', 0); ?>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				<label class="in-hierarchy" style="width: 250px"> </label>
				<?php echo sprintf($hour_string, '<span class="select"><select name="filter-end-h0">'.er_form_time_options(0).'</select></span>', '<span class="select"><select name="filter-end-h1">'.er_form_time_options(23).'</select></span>'); ?>
			</td>
		</tr>
		</tbody>
		<tbody id="filter_form_condition" class="hide-it">
		<tr>
			<td>
				<label class="wrapper" style="margin-left:5px">
					<input type="checkbox" name="filter_form_condition_checkbox" id="filter_form_condition_checkbox" onclick="show_use_condition();">
					<span class="input"></span> <?php echo sprintf(__('Filter by %s', 'easyReservations'), __('condition', 'easyReservations')); ?>
				</label>
			</td>
		</tr>
		</tbody>
		<tbody id="filter_form_discount" class="hide-it">
		<tr>
			<td>
				<label class="in-hierarchy" style="min-width: 110px"><?php _e('Type', 'easyReservations'); ?></label>
				<span class="select">
					<select name="filter_form_discount_type" id="filter_form_discount_type" onchange="setWord(this.value)">
						<option value="early"><?php echo er_date_get_interval_label($resource->interval, 0, true).' '.sprintf(__('between %1$s and %2$s', 'easyReservations'), __('Reservation', 'easyReservations'), __('Arrival', 'easyReservations')); ?></option>
						<option value="loyal"><?php _e('Recurring guests', 'easyReservations'); ?></option>
						<option value="stay"><?php echo sprintf($amount_text, er_date_get_interval_label($resource->interval)); ?></option>
						<option value="pers"><?php echo sprintf($amount_text, __('persons', 'easyReservations')); ?></option>
						<option value="adul"><?php echo sprintf($amount_text, __('adults', 'easyReservations')); ?></option>
						<option value="child"><?php echo sprintf($amount_text, __('children', 'easyReservations')); ?></option>
					</select>
				</span><br>
				<span style="display: block">
					<label class="in-hierarchy" style="min-width: 110px"><?php _e('Condition', 'easyReservations'); ?></label>
					<span class="select" ><select name="filter_form_discount_cond" id="filter_form_discount_cond"><?php echo er_form_number_options(1,250); ?></select></span> <span id="filter_form_discount_cond_verb">Days</span>
				</span>
				<span id="filter-mode-field">
					<label class="in-hierarchy" style="min-width: 110px"><?php _e('Mode', 'easyReservations'); ?></label>
          <span class="select"><select name="filter_form_discount_mode" id="filter_form_discount_mode">
            <option value="price_res"><?php echo sprintf(__('Price per %s', 'easyReservations'), lcfirst(__('Reservation', 'easyReservations'))); ?></option>
            <option value="price_halfhour"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(1800, 1)); ?></option>
            <option value="price_hour"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(3600, 1)); ?></option>
            <option value="price_realday"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(86400, 1)); ?></option>
            <option value="price_night"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(86401, 1)); ?></option>
            <option value="price_week"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(604800, 1)); ?></option>
            <option value="price_month"><?php echo sprintf(__('Price per %s', 'easyReservations'), er_date_get_interval_label(2592000, 1)); ?></option>
            <option value="price_day"><?php echo sprintf(__('Price per %s', 'easyReservations'), __('billing unit', 'easyReservations')); ?></option>
            <option value="price_pers"><?php echo sprintf(__('Price per %s', 'easyReservations'), __('person', 'easyReservations')); ?></option>
            <option value="price_adul"><?php echo sprintf(__('Price per %s', 'easyReservations'), __('adult', 'easyReservations')); ?></option>
            <option value="price_child"><?php echo sprintf(__('Price per %s', 'easyReservations'), __('child', 'easyReservations')); ?></option>
            <option value="price_both"><?php echo sprintf(__('Price per %s and %s', 'easyReservations'), er_date_get_interval_label($resource->interval, 1), __('person', 'easyReservations')); ?></option>
            <option value="price_day_adult"><?php echo sprintf(__('Price per %s and %s', 'easyReservations'), er_date_get_interval_label($resource->interval, 1), __('adult', 'easyReservations')); ?></option>
            <option value="price_day_child"><?php echo sprintf(__('Price per %s and %s', 'easyReservations'), er_date_get_interval_label($resource->interval, 1), __('child', 'easyReservations')); ?></option>
            <option value="%"><?php _e('Percent', 'easyReservations'); ?></option>
          </select></span>
        </span>
				<i style="display: block"><?php _e('Only the first condition match from high to low will be applied', 'easyReservations'); ?></i>
			</td>
		</tr>
		</tbody>
		<tbody id="filter_form_price" class="hide-it">
		<tr>
			<td>
				<h2><?php _e('Price', 'easyReservations'); ?></h2>
				<div class="easy-description">
					<?php _e('Base price filter get checked for and applied each billing unit - extra charge and discount filter only once per reservation based on arrival.', 'easyReservations'); ?>
				</div>

				<label class="in-hierarchy" for=filter-price-mode" style="min-width: 150px"><?php _e('Type', 'easyReservations'); ?></label>
				<span class="select">
					<select onchange="easy_change_amount(this);" name="filter-price-mode" id="filter-price-mode">
						<option value="charge"><?php _e('Extra charge', 'easyReservations');?></option>
						<option value="discount"><?php _e('Discount', 'easyReservations');?></option>
						<option value="baseprice"><?php _e('Change base price', 'easyReservations');?></option>
					</select>
				</span><br>
				<label class="in-hierarchy" for="filter-price-field" style="min-width: 150px"><?php _e('Price', 'easyReservations'); ?></label>
				<span class="input-wrapper"><input type="text" name="filter-price-field" id="filter-price-field" value="-100"><span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span></span>
				<div class="filter-children-price-container hidden">
					<label class="in-hierarchy" for="filter-children-price" style="min-width: 150px"><?php _e('Children price', 'easyReservations'); ?></label>
					<span class="input-wrapper"><input type="text" name="filter-children-price" id="filter-children-price" value=""><span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span></span>
				</div>
			</td>
		</tr>
		</tbody>
		<tfoot id="filter_form_button" class="hide-it">
		<tr>
			<td>
				<input class="easy-button" id="filter_form_button_input" type="button" value="<?php echo sprintf(__('Add %s', 'easyReservations'), __('filter', 'easyReservations')); ?>" onclick="beforeFiltersubmit(); return false;">
			</td>
		</tr>
		</tfoot>
	</table>
	<div id="filter_form_hidden"></div>
</form>
<script language="javascript" type="text/javascript">
	function display_price_filter(){
		show_add_price();
		document.filter_form.reset();
		document.getElementById('filter-price-field').value = 100;
	}

	function display_availability_filter() {
		show_add_avail();
		document.filter_form.reset();
	}

	function display_requirement_filter(){
		show_add_req();
		document.filter_form.reset();
	}

	jQuery('#er_resource_interval').bind('change', checkBillingUnit);
	function checkBillingUnit(){
		var interval = jQuery('#er_resource_interval');
		var billing_method = jQuery('#billing-method');
		var hidden_billing_field = jQuery('#hidden-billing-field');
		if(interval.val() == 86401){
			billing_method.attr('disabled', 'disabled');
			hidden_billing_field.attr('name', 'billing-method').val(3);
		} else {
			billing_method.attr('disabled', false);
			hidden_billing_field.attr('name', '').val('');
		}
	}

	function beforeFiltersubmit(){
		if(document.getElementById('filter_form_name_field').value == ""){
			document.getElementById('filter_form_name_field').style.border = "1px solid #f00";
			jQuery('#filter_form_name_field').focus();
			return false;
		} else document.getElementById('filter_form').submit();
	}

	function is_int(value){
		if((parseFloat(value) == parseInt(value)) && !isNaN(value)) return true;
		else return false;
	}

	jQuery('.paste-input').bind('input', function(e){
		var is_json = true;
		try {
			var json = jQuery.parseJSON(jQuery(this).val());
		} catch(err) {
			is_json = false;
		}

		if(is_json && json !== null && typeof json == 'object'){
			filter_edit(false, json);
			jQuery(this).val('').addClass('hidden');
		}
	});

	function filter_copy(i){
		var aux = document.createElement("input");
		aux.setAttribute("value", JSON.stringify(filter[i]));
		document.body.appendChild(aux);
		aux.select();
		document.execCommand("copy");
		document.body.removeChild(aux);
	}

	function filter_edit(i, single_filter){
		reset_filter_form();
		if(i === false){
			the_filter = single_filter;
		} else {
			var the_filter = filter[i];
			document.getElementById('filter_form_button_input').value = '<?php echo addslashes(sprintf(__('Edit %s', 'easyReservations'), __('filter', 'easyReservations'))); ?>';
			document.getElementById('filter_form_hidden').innerHTML = '<input type="hidden" id="price_filter_edit" name="price_filter_edit" value="'+i+'">';
		}
		var type = the_filter['type'];
		document.getElementById('filter_form_name_field').value = the_filter['name'];

		if(type == 'price' || type == 'unavail' || type == 'req' || the_filter['timecond']){
			var cond = the_filter['cond'];
			if(the_filter['timecond']) cond = the_filter['timecond'];
			if(cond == 'date' ){
				document.getElementById('price_filter_cond_range').checked = true;
				var timestamp_date = the_filter['date_str'];
				if(timestamp_date != ''){
					var date_date = new Date (timestamp_date);
					document.getElementById('price-filter-range-from-hour').selectedIndex = date_date.getHours();
					document.getElementById('price-filter-range-to-hour').selectedIndex = date_date.getHours();
					document.getElementById('price-filter-range-from-min').selectedIndex = date_date.getMinutes();
					document.getElementById('price-filter-range-to-min').selectedIndex = date_date.getMinutes();
					document.getElementById('price_filter_range_from').value = (( date_date.getDate() < 10) ? '0'+ date_date.getDate() : date_date.getDate()) + '.' +(( (date_date.getMonth()+1) < 10) ? '0'+ (date_date.getMonth()+1) : (date_date.getMonth()+1)) + '.' + (( date_date.getYear() < 999) ? date_date.getYear() + 1900 : date_date.getYear());
					document.getElementById('price_filter_range_to').value = (( date_date.getDate() < 10) ? '0'+ date_date.getDate() : date_date.getDate()) + '.' +(( (date_date.getMonth()+1) < 10) ? '0'+ (date_date.getMonth()+1) : (date_date.getMonth()+1)) + '.' + (( date_date.getYear() < 999) ? date_date.getYear() + 1900 : date_date.getYear());
				} else document.getElementById('price_filter_date').value = the_filter['date'];
			} else if(cond == 'range' || the_filter['from']){
				if(the_filter['every']){
					document.getElementById('price_filter_range_every').checked = true;
				}
				document.getElementById('price_filter_cond_range').checked = true;
				var timestamp_from = the_filter['from_str'];
				if(timestamp_from != ''){
					var date_from = new Date(timestamp_from);
					document.getElementById('price-filter-range-from-hour').selectedIndex = date_from.getHours();
					document.getElementById('price-filter-range-from-min').selectedIndex = date_from.getMinutes();
					document.getElementById('price_filter_range_from').value = (( date_from.getDate() < 10) ? '0'+ date_from.getDate() : date_from.getDate()) + '.' +(( (date_from.getMonth()+1) < 10) ? '0'+ (date_from.getMonth()+1) : (date_from.getMonth()+1)) + '.' + (( date_from.getYear() < 999) ? date_from.getYear() + 1900 : date_from.getYear());
				} else document.getElementById('price_filter_range_from').value = the_filter['from'];
				var timestamp_to = the_filter['to_str'];
				if(timestamp_to != ''){
					var date_to = new Date(timestamp_to);
					document.getElementById('price-filter-range-to-hour').selectedIndex = date_to.getHours();
					document.getElementById('price-filter-range-to-min').selectedIndex = date_to.getMinutes();
					document.getElementById('price_filter_range_to').value = (( date_to.getDate() < 10) ? '0'+ date_to.getDate() : date_to.getDate()) + '.' + (((date_to.getMonth()+1) < 10) ? '0'+ (date_to.getMonth()+1) : (date_to.getMonth()+1)) + '.' + (( date_to.getYear() < 999) ? date_to.getYear() + 1900 : date_to.getYear());
				} else document.getElementById('price_filter_range_to').value = the_filter['to'];
			}
			if((the_filter['timecond'] && the_filter['timecond'] == 'unit') || (the_filter['cond'] && the_filter['cond'] == 'unit')){
				document.getElementById('price_filter_cond_unit').checked = true;
				var hour_checkboxes = document.getElementsByName('price_filter_unit_hour[]');
				if(hour_checkboxes && the_filter['hour'] != '' && the_filter['hour']){
					var hours =  the_filter['hour'];
					var explode_hours = hours.split(",");
					for(var x = 0; x < explode_hours.length; x++){
						var nr = explode_hours[x];
						hour_checkboxes[nr].checked = true;
					}
				}
				var day_checkboxes = document.getElementsByName('price_filter_unit_days[]');
				if(day_checkboxes && the_filter['day'] != '' && the_filter['day']){
					var days =  the_filter['day'];
					var explode_days = days.split(",");
					for(var x = 0; x < explode_days.length; x++){
						var nr = explode_days[x];
						if(day_checkboxes[nr-1]) day_checkboxes[nr-1].checked = true;
					}
				}
				var cw_checkboxes = document.getElementsByName('price_filter_unit_cw[]');
				if(the_filter['cw'] != '' && the_filter['cw']){
					var cws =  the_filter['cw'];
					var explode_cws = cws.split(",");
					for(var x = 0; x < explode_cws.length; x++){
						var nr = explode_cws[x];
						if(cw_checkboxes[nr-1]) cw_checkboxes[nr-1].checked = true;
					}
				}
				var month_checkboxes = document.getElementsByName('price_filter_unit_month[]');
				if(the_filter['month'] != '' && the_filter['month']){
					var month =  the_filter['month'];
					var explode_month = month.split(",");
					for(var x = 0; x < explode_month.length; x++){
						var nr = explode_month[x];
						if(month_checkboxes[nr-1]) month_checkboxes[nr-1].checked = true;
					}
				}
				var q_checkboxes = document.getElementsByName('price_filter_unit_quarter[]');
				if(the_filter['quarter'] != '' && the_filter['quarter']){
					var quarters =  the_filter['quarter'];
					var explode_quarters = quarters.split(",");
					for(var x = 0; x < explode_quarters.length; x++){
						var nr = explode_quarters[x];
						if(q_checkboxes[nr-1]) q_checkboxes[nr-1].checked = true;
					}
				}
				var year_checkboxes = document.getElementsByName('price_filter_unit_year[]');
				if(the_filter['year'] != '' && the_filter['year']){
					var years =  the_filter['year'];
					var explode_years = years.split(",");
					for(var x = 0; x < explode_years.length; x++){
						var nr = explode_years[x] - 2014;
						if(year_checkboxes[nr-1]) year_checkboxes[nr-1].checked = true;
					}
				}
			}
		}

		if(type == 'unavail'){
			show_add_avail();
		} else if(type == 'req'){
			var reqs = the_filter['req'];
			document.getElementById('req_filter_min_pers').selectedIndex = parseFloat(reqs['pers-min'])-1;
			document.getElementById('req_filter_max_pers').selectedIndex = reqs['pers-max'];
			document.getElementById('req_filter_min_nights').selectedIndex = parseFloat(reqs['nights-min'])-1;
			document.getElementById('req_filter_max_nights').selectedIndex = reqs['nights-max'];
			var day_checkboxes = document.getElementsByName('req_filter_start_on[]');
			jQuery(day_checkboxes).prop('checked', false);
			if(day_checkboxes && reqs['start-on'] !== ''){
				if(reqs['start-on'] == 0){
					jQuery(day_checkboxes).prop('checked', true);
				}
				var explode_days = reqs['start-on'];
				for(var x = 0; x < explode_days.length; x++){
					var nr = explode_days[x];
					day_checkboxes[nr-1].checked = true;
				}
			}

			var end_checkboxes = document.getElementsByName('req_filter_end_on[]');

			jQuery(end_checkboxes).prop('checked', false);
			if(end_checkboxes && reqs['end-on'] !== ''){
				var explode_ends = reqs['end-on'];
				if(reqs['end-on'] == 0){
					jQuery(end_checkboxes).prop('checked', true);
				}

				for(var x = 0; x < explode_ends.length; x++){
					var nr = explode_ends[x];
					end_checkboxes[nr-1].checked = true;
				}
			}
			if(reqs['start-h']){
				jQuery('select[name="filter-start-h0"]').val(reqs['start-h'][0]);
				jQuery('select[name="filter-start-h1"]').val(reqs['start-h'][1]);
			}
			if(reqs['end-h']){
				jQuery('select[name="filter-end-h0"]').val(reqs['end-h'][0]);
				jQuery('select[name="filter-end-h1"]').val(reqs['end-h'][1]);
			}
			show_add_req();
		} else {
			show_add_price();
			var timecond = false;
			var condcond = false;
			var condtype = false;
			if(the_filter['imp']) document.getElementById('price_filter_imp').selectedIndex = the_filter['imp'] - 1;

			var price = the_filter['price'];
			var pricemodus = document.getElementsByName('filter-price-mode');
			document.getElementById('filter-price-field').value = price;
			if(the_filter['children-price']){
				document.getElementById('filter-children-price').value = the_filter['children-price'];
			}
			if(type == 'price'){
				jQuery('.filter-children-price-container').removeClass('hidden');
				pricemodus[0].selectedIndex = 2;
			}
			else if(price > 0) pricemodus[0].selectedIndex = 0;
			else pricemodus[0].selectedIndex = 1;
			if(type == 'price'){
				jQuery('#filter-mode-field').addClass('hidden');
				if(the_filter['cond']) timecond = 'cond';
				if(the_filter['basecond']) condcond = 'basecond';
				if(the_filter['condtype']) condtype = 'condtype';
			} else {
				if(the_filter['timecond']) timecond = 'timecond';
				if(the_filter['cond']) condcond = 'cond';
				if(the_filter['type']) condtype = 'type';
			}
			if(timecond) show_use_time(1);
			if(condcond){
				type = the_filter[condtype];
				jQuery('#filter_form_discount_type').val(type);
				setWord(type);
				document.getElementById('filter_form_discount_cond').selectedIndex = the_filter[condcond]-1;

				if(the_filter['modus']){
					jQuery('#filter_form_discount_mode').val(the_filter['modus']);
				}
				show_use_condition(1);
			}
		}
	}

	function show_add_price(){
		jQuery('#filter_form_name,#filter_form_importance,#filter_form_usetime,#filter_form_condition').removeClass('hidden').removeClass('hide-it');
		jQuery('#filter_form_time_cond,#filter_form_price,#filter_form_button,#filter_form_discount,#filter_form_requirements').addClass('hidden');

		document.getElementById('filter_type').value="price";
	}

	function show_use_time(start){
		if(start) document.getElementById('filter_form_usetime_checkbox').checked = true;
		if(document.getElementById('filter_form_usetime_checkbox').checked == true){
			show_price(1);
			jQuery('#filter_form_button, #filter_form_time_cond').removeClass();
		} else {
			jQuery('#filter_form_time_cond').addClass('hidden');
			if(document.getElementById('filter_form_condition_checkbox').checked !== true) show_price();
		}
	}

	function show_use_condition(start){
		if(start) document.getElementById('filter_form_condition_checkbox').checked = true;
		if(document.getElementById('filter_form_condition_checkbox').checked == true){
			show_price(1);
			jQuery('#filter_form_button, #filter_form_discount').removeClass();

		} else {
			jQuery('#filter_form_discount').addClass('hidden');
			if(document.getElementById('filter_form_usetime_checkbox').checked !== true) show_price();
		}
	}

	function show_price(on){
		if(on) document.getElementById('filter_form_price').className = '';
		else document.getElementById('filter_form_price').className = 'hidden';
	}

	function show_add_avail(){
		jQuery('#filter_form_name, #filter_form_time_cond, #filter_form_button').removeClass();
		jQuery('#filter_form_requirements, #filter_form_usetime, #filter_form_importance, #filter_form_price, #filter_form_discount, #filter_form_condition').addClass('hidden');

		jQuery('#filter_type').val('unavail');
	}

	function show_add_req(){
		jQuery('#filter_form_importance, #filter_form_button, #filter_form_requirements, #filter_form_time_cond, #filter_form_name').removeClass();
		jQuery('#filter_form_discount, #filter_form_price, #filter_form_usetime, #filter_form_condition').addClass('hidden');

		document.getElementById('filter_type').value="req";
	}

	function reset_filter_form(){
		jQuery('#filter_form_name, #filter_form_time_cond, #filter_form_usetime, #filter_form_requirements, #filter_form_discount, #filter_form_discount, #filter_form_price, #filter_form_importance, #filter_form_condition').addClass('hidden');
		jQuery('#filter-mode-field').removeClass('hidden');
		document.filter_form.reset();
		jQuery('#filter_type').val('');
		jQuery('#filter_form_button_input').val('<?php echo sprintf(__('Add %s', 'easyReservations'), __('filter', 'easyReservations')); ?>');
		jQuery('#filter_form_hidden').html('');
	}
	function setWord(v){
		if(v == 'early' || v=='stay') var verb = '<?php echo er_date_get_interval_label($resource->interval); ?>';
		if(v == 'loyal') var verb = '<?php echo addslashes(__('visits', 'easyReservations')); ?>';
		if(v == 'pers') var verb = '<?php echo addslashes(__('persons', 'easyReservations')); ?>';
		if(v == 'adul') var verb = '<?php echo addslashes(__('adults', 'easyReservations')); ?>';
		if(v == 'child') var verb = '<?php echo addslashes(__('children', 'easyReservations')); ?>';
		document.getElementById('filter_form_discount_cond_verb').innerHTML = verb;
	}
	jQuery(document).ready(function($) {
		jQuery('.filter-navigation').easyNavigation(false);

		checkBillingUnit();
		display_price_filter();

		$("#price_filter_date, #price_filter_range_from, #price_filter_range_to, #slot_range_from, #slot_range_to").datepicker({
			changeMonth: true,
			changeYear: true,
			<?php echo easyreservations_build_datepicker(0,0,true); ?>
			dateFormat: 'dd.mm.yy'
		});
	});
	function easy_change_amount(t){
		jQuery('#filter-mode-field').removeClass('hidden');
		jQuery('.filter-children-price-container').addClass('hidden');
		var fieldbefore = jQuery('#filter-price-field').val();
		if(t){
			var end = fieldbefore;
			if(t.value == 'discount'){
				if(fieldbefore[0] == '-') end = fieldbefore;
				else end = '-' + fieldbefore;
			} else if(t.value == 'baseprice'){
				if(fieldbefore[0] == '-') end = fieldbefore.substr(1);
				document.getElementById('filter_form_discount_mode').selectedIndex =  1;
				jQuery('#filter-mode-field').addClass('hidden');
				jQuery('.filter-children-price-container').removeClass('hidden');
			} else {
				if(fieldbefore[0] == '-') end = fieldbefore.substr(1);
			}
			jQuery('#filter-price-field').val(end);
		}
	}
	function easy_add_tax(x,y){
		if(x == 1) jQuery('.placeholder').before( '<div><?php echo easyreservations_generate_select('res_tax_class[]', array( 'both'=>'Both', 'stay'=>'Stay', 'prices'=>'Prices'), 0); ?> <input type="text" name="res_tax_names[]" value="Name" style="width:150px;"> <span class="input-wrapper"><input type="text" name="res_tax_amounts[]" value="20" style="width:50px;"><span class="input-box"><span class="fa fa-percent"></span></span></span> <a onclick="easy_add_tax(2, this);" style="font-size:18px;vertical-align: baseline;" class="fa fa-times-circle"></a></div>');
		else {
			jQuery(y.parentNode).remove();
			jQuery(y).remove();
		}
	}
</script>