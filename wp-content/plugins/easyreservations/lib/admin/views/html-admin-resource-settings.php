<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

$tax_string = '';
if( $resource->taxes && !empty( $resource->taxes ) ) {
    foreach( $resource->taxes as $tax ) {
        $tax_string .= '<div>' . easyreservations_generate_select( 'res_tax_class[]', array(
                'both' => 'Both',
                'stay' => 'Stay',
                'prices' => 'Prices'
            ), ( isset( $tax[2] ) ) ? $tax[2] : 0 ) . ' <input type="text" name="res_tax_names[]" value="' . $tax[0] . '" style="width:150px;"> <span class="input-wrapper"><input type="text" name="res_tax_amounts[]" value="' . $tax[1] . '" style="width:50px;"><span class="input-box"><span class="fa fa-percent"></span></span></span> <a onclick="easy_add_tax(2, this);" style="font-size: 18px;vertical-align: baseline;" class="fa fa-times-circle"></a></div>';
    }
}

if( !isset( $resource->requirements['start-on'] ) ) {
    $arrival_possible_on = 0;
} else $arrival_possible_on = $resource->requirements['start-on'];
if( !isset( $resource->requirements['end-on'] ) ) {
    $departure_possible_on = 0;
} else $departure_possible_on = $resource->requirements['end-on'];
if( !isset( $resource->requirements['start-h'] ) ) {
    $starton_h = array( 0, 23 );
} else $starton_h = $resource->requirements['start-h'];
if( !isset( $resource->requirements['end-h'] ) ) {
    $endon_h = array( 0, 23 );
} else $endon_h = $resource->requirements['end-h'];

$interval_string = er_date_get_interval_label( $resource->interval, 2 );

$arrival_hours = sprintf( $hour_string, '<span class="select"><select name="start-h0">' . er_form_time_options( $starton_h[0] ) . '</select></span>', '<span class="select"><select name="start-h1">' . er_form_time_options( $starton_h[1] ) . '</select></span>' );
$departure_hours = sprintf( $hour_string, '<span class="select"><select name="end-h0">' . er_form_time_options( $endon_h[0] ) . '</select></span>', '<span class="select"><select name="end-h1">' . er_form_time_options( $endon_h[1] ) . '</select></span>' );
?>
<form id="resource_settings" name="resource_settings" method="post">
    <input type="hidden" name="easy-resource-settings"
           value="<?php echo wp_create_nonce( 'easy-resource-settings' ); ?>">
    <table id="resource_price_settings_table" class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%">
        <thead>
        <tr>
            <th colspan="2"><?php _e( 'Settings', 'easyReservations' ); ?></th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="2">
                <input type="submit" onclick="document.getElementById('resource_settings').submit(); return false;"
                       class="easy-button" value="<?php _e( 'Submit', 'easyReservations' ); ?>">
            </td>
        </tr>
        </tfoot>
        <tbody>
        <tr>
            <td colspan="2" class="easy-description">
                <?php _e( 'If your customers should only be able to select certain time ranges use slots, else use requirements to define when they can reserve.', 'easyReservations' ); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <h2><?php _e( 'Price', 'easyReservations' ); ?></h2>
            </td>
        </tr>
        <tr>
            <td class="label">
                <b id="base_price"><?php _e( 'Base price', 'easyReservations' ); ?></b>
                <?php easyreservations_help( 'The amount of money that one billing unit costs if no filter gets applied. Must be positive.' ); ?>
            </td>
            <td>
					<span class="input-wrapper">
						<input type="text" value="<?php echo $resource->base_price; ?>"
                               style="width:60px;text-align:right" name="base_price">
						<span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span>
					</span>
                <i><? _e( 'per billing unit', 'easyReservations' ); ?></i>
            </td>
        </tr>
        <tr>
            <td class="label">
                <b id="children_price"><?php _e( 'Children price', 'easyReservations' ); ?></b>
                <?php easyreservations_help( 'The amount of money that one children costs per billing unit. Must be positive, but can be percentage.' ); ?>
            </td>
            <td>
					<span class="input-wrapper">
						<input type="text" value="<?php echo $resource->children_price; ?>"
                               style="width:60px;text-align:right" name="child_price">
						<span class="input-box">&<?php echo RESERVATIONS_CURRENCY; ?>;</span>
					</span>
            </td>
        </tr>
        <tr>
            <td class="label">
                <b><?php _e( 'Billing', 'easyReservations' ); ?></b>
                <?php easyreservations_help( 'The interval by which reservations get billed. In daily mode every 24 hours get charged while the nightly mode only charges once per day regardless of arrival and departure time.' ); ?>
            </td>
            <td>
                <?php _e( 'Bill each', 'easyReservations' ); ?>
                <span class="together-wrapper">
						<?php
                        echo easyreservations_generate_select( 'billing-method', array(
                                '0' => __( 'started', 'easyReservations' ),
                                '1' => __( 'completed', 'easyReservations' )
                            ), $resource->billing_method, 'class="first"' );

                        echo easyreservations_generate_select( 'er_resource_interval', array(
                                '1800' => er_date_get_interval_label( 1800, 1 ),
                                '3600' => er_date_get_interval_label( 3600, 1 ),
                                '86400' => er_date_get_interval_label( 86400, 1 ),
                                '86401' => er_date_get_interval_label( 86401, 1 ),
                                '604800' => er_date_get_interval_label( 604800, 1 ),
                                '2592000' => er_date_get_interval_label( 2592000, 1 )
                            ), $resource->interval, 'class="last"' );
                        ?>
					</span>
            </td>
        </tr>
        <tr>
            <td class="label">
                <?php easyreservations_help( 'If enabled the base price gets multiplied by the amount of adults and the children price by the amount of children.' ); ?>
            </td>
            <td>
                <label class="wrapper">
                    <input type="checkbox" name="easy-resource-price" id="easy-resource-price"
                           value="1" <?php checked( $resource->per_person, 1 ); ?>>
                    <span class="input"></span>
                    <?php echo sprintf( __( 'Price per %s', 'easyReservations' ), __( 'person', 'easyReservations' ) ); ?>
                </label>
                <input type="hidden" id="hidden-billing-field">
            </td>
        </tr>
        <tr>
            <td class="label">
                <?php easyreservations_help( 'Affects base price as well as children price.' ); ?>
            </td>
            <td>
                <label class="wrapper">
                    <input type="checkbox" name="easy-resource-once" id="easy-resource-once"
                           value="1" <?php checked( $resource->once, 1 ); ?>>
                    <span class="input"></span>
                    <?php _e( 'Apply base price only once regardless of duration', 'easyReservations' ); ?>
                </label>
            </td>
        </tr>
        <tr>
            <td class="label">
                <b id="resource-taxes"><?php _e( 'Taxes', 'easyReservations' ); ?></b>
                <a onclick="easy_add_tax(1, this)" style="font-size: 14px;" class="fa fa-plus-circle"></a>
                <?php easyreservations_help( 'Taxes can be applied to custom field prices, to the total stay price or to both.' ); ?>
            </td>
            <td id="resource-taxes-content">
                <?php echo $tax_string; ?>
                <a class="placeholder"></a>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <h2><?php _e( 'Availability', 'easyReservations' ); ?></h2>
            </td>
        </tr>

        <tr>
            <td class="label">
                <b>
                    <?php _e( 'Frequency', 'easyReservations' ); ?>
                    <?php easyreservations_help( 'In which frequency the resource can be reserved. Defines how availability gets displayed. ' ); ?>
                </b>
            </td>
            <td>
                <span class="select">
                    <?php echo easyreservations_generate_select( 'er_resource_frequency', array(
                            '1800' => __( 'Half-hourly', 'easyReservations' ),
                            '3600' => __( 'Hourly', 'easyReservations' ),
                            '86400' => __( 'Daily', 'easyReservations' ),
                        ), $resource->frequency );
                    ?>
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">
                <b>
                    <?php _e( 'Spaces/Quantity', 'easyReservations' ); ?>
                    <?php easyreservations_help( 'How often the resource can be reserved at the same time. Below you can define a label for each of those spaces.' ); ?>
                </b>
            </td>
            <td>
					<span class="select">
						<select name="quantity">
							<?php echo er_form_number_options( 1, 250, $resource->quantity ); ?>
						</select>
					</span>
            </td>
        </tr>
        <tr>
            <td id="availability_by" class="label">
                <?php _e( 'Availability per', 'easyReservations' ); ?>
                <?php easyreservations_help( '<b>Per object</b><br>The quantity defines how often the resource can get reserved at the same time regardless of the amount of persons. Each space can have a label.<br><br><b>Per person/adult/children</b><br>The quantity defines how many persons/adults/children can reserve at the same time regardless of the amount of reservations. The resource will be summarized in one row in the overview.' ); ?>
            </td>
            <td>
                <?php
                echo easyreservations_generate_select( 'availability_by', array(
                        'unit' => __( 'Object', 'easyReservations' ),
                        'pers' => ucfirst( __( 'person', 'easyReservations' ) ),
                        'adult' => ucfirst( __( 'adult', 'easyReservations' ) ),
                        'children' => ucfirst( __( 'child', 'easyReservations' ) )
                    ), $resource->availability_by );
                ?>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <h2><?php _e( 'Requirements', 'easyReservations' ); ?></h2>
            </td>
        </tr>

        <tr>
            <td class="label" id="availability_by">
                <?php echo ucfirst( $interval_string ); ?>
                <?php easyreservations_help( 'Required and maximum amount of ' . $interval_string . ' that can be reserved. ' ); ?>
            </td>
            <td>
                <span class="input-wrapper select">
                    <span class="input-box"><?php _e( 'Min', 'easyReservations' ); ?></span><select
                        name="easy-resource-min-nights">
                        <?php echo er_form_number_options( 0, 250, $resource->requirements['nights-min'] ); ?>
                    </select>
                </span> -
                <span class="input-wrapper select">
                    <span class="input-box"><?php _e( 'Max', 'easyReservations' ); ?></span><select
                    name="easy-resource-max-nights">
                        <option
                            value="0" <?php selected( $resource->requirements['nights-max'], 0 ) ?>>&infin;</option>
                    <?php echo er_form_number_options( 1, 250, $resource->requirements['nights-max'] ); ?>
                    </select>
                </span>
            </td>
        </tr>
        <tr>
            <td id="resource_persons" class="label">
                <?php echo ucfirst( __( 'persons', 'easyReservations' ) ); ?>
                <?php easyreservations_help( 'Required and maximum amount of adults+children that can be reserved. ' ); ?>
            </td>
            <td>
                <span class="input-wrapper select">
                    <span class="input-box"><?php _e( 'Min', 'easyReservations' ); ?></span><select
                        name="easy-resource-min-pers">
                        <?php echo er_form_number_options( 1, 250, $resource->requirements['pers-min'] ); ?>
                    </select>
                </span> -
                <span class="input-wrapper select">
                    <span class="input-box"><?php _e( 'Max', 'easyReservations' ); ?></span><select
                    name="easy-resource-max-pers">
                        <option
                            value="0" <?php selected( $resource->requirements['pers-max'], 0 ) ?>>&infin;</option>
                    <?php echo er_form_number_options( 1, 250, $resource->requirements['pers-max'] ); ?>
                    </select>
                </span>
            </td>
        </tr>
        <tr>
            <td class="label">
                <?php _e( 'Arrival possible on', 'easyReservations' ); ?>
                <?php easyreservations_help( 'The earliest possible arrival time is used ' ); ?>
            </td>
            <td>
                <div class="blockquote">
                    <?php echo easyreservations_days_options( 'resource_requirements_start_on[]', $arrival_possible_on ); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label"></td>
            <td>
                <?php echo $arrival_hours ?>
            </td>
        </tr>
        <tr>
            <td class="label">
                <?php _e( 'Departure possible on', 'easyReservations' ); ?>
                <?php easyreservations_help( '' ); ?>
            </td>
            <td>
                <div class="blockquote">
                    <?php echo easyreservations_days_options( 'resource_requirements_end_on[]', $departure_possible_on ); ?>
                </div>
            </td>
        </tr>
        <tr>
            <td class="label"></td>
            <td>
                <?php echo $departure_hours; ?>
            </td>
        </tr>
        <tr>
            <td class="label">
                <?php _e( 'Required permission', 'easyReservations' ); ?>
                <?php easyreservations_help( 'Set the required permission to work with this resource. Affects reservations dashboard, resource settings, statistics and dashboard widget.' ); ?>
            </td>
            <td>
					<span class="select">
						<select name="er_resource_permission">
							<?php echo easyreservations_get_roles_options( get_post_meta( $resource->ID, 'easy-resource-permission', true ) ); ?>
						</select>
					</span>
            </td>
        </tr>
        </tbody>
    </table>
</form>