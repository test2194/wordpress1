<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-color-picker' );
wp_enqueue_style( 'wp-color-picker' );

$settings = get_option( "reservations_settings" );
$currency = $settings['currency'];

if( !is_array( $currency ) ) {
    $currency = array(
        'sign' => $currency,
        'place' => 0,
        'whitespace' => 1,
        'divider1' => '.',
        'divider2' => ',',
        'decimal' => 2
    );
}

$date_format        = $settings['date_format'];
$permission_options = get_option( "reservations_main_permission" );
$permission_string  = 'Required user role to access the ';
if( isset( $settings['time_format'] ) ) {
    $time_format = $settings['time_format'];
} else $time_format = 'H:i';

if( isset( $settings['prices_include_tax'] ) ) {
    $prices_include_tax = $settings['prices_include_tax'];
} else $prices_include_tax = 0;


if( isset( $settings['mergeres'] ) && is_array( $settings['mergeres'] ) ) {
    $block_before = $settings['mergeres']['blockbefore'];
    $block_after  = $settings['mergeres']['blockafter'];
    $thenum       = $settings['mergeres']['merge'];
} else {
    $block_before = 0;
    $block_after  = 0;
    $thenum       = 0;
}

if( !isset( $settings['tutorial'] ) ) {
    $settings['tutorial'] = 1;
}

$divider      = array( '.' => '.', ',' => ',', ' ' => 'Whitespace', '' => __( 'None', 'easyReservations' ) );
$styles       = array( 'light' => __( 'Light', 'easyReservations' ), 'dark' => __( 'Dark', 'easyReservations' ) );
$date_formats = array(
    'Y/m/d' => date( 'Y/m/d' ),
    'Y-m-d' => date( 'Y-m-d' ),
    'm/d/Y' => date( 'm/d/Y' ),
    'd-m-Y' => date( 'd-m-Y' ),
    'd.m.Y' => date( 'd.m.Y' )
);
$time_formats = array( 'H:i' => date( 'H:i', current_time( 'timestamp' ) ), 'h:i a' => date( 'h:i a' ), 'h:i A' => date( 'h:i A' ) );

if( isset( $settings['primary-color'] ) ) {
    $primary_color = $settings['primary-color'];
} else $primary_color = '#228dff';

$hours      = er_date_get_interval_label( 3600, 2 );
$days       = er_date_get_interval_label( 86400, 2 );
$minutes    = __( 'minutes', 'easyReservations' );
$time_array = array(
    0 => '0 ' . $minutes,
    5 => '5 ' . $minutes,
    10 => '10 ' . $minutes,
    15 => '15 ' . $minutes,
    30 => '30 ' . $minutes,
    45 => '45 ' . $minutes,
    60 => '1 ' . er_date_get_interval_label( 3600, 1 ),
    90 => '1.5 ' . $hours,
    120 => '2 ' . $hours,
    150 => '2.5 ' . $hours,
    180 => '3 ' . $hours,
    240 => '4 ' . $hours,
    300 => '5 ' . $hours,
    360 => '6 ' . $hours,
    600 => '10 ' . $hours,
    720 => '12 ' . $hours,
    1080 => '18 ' . $hours,
    1440 => '1 ' . er_date_get_interval_label( 86400, 1 ),
    2160 => '1.5 ' . $days,
    2880 => '2 ' . $days,
    4320 => '3 ' . $days,
    5760 => '4 ' . $days,
    7200 => '5 ' . $days,
    8640 => '6 ' . $days,
    10080 => '7 ' . $days,
    20160 => '14 ' . $days,
    40320 => '1 ' . er_date_get_interval_label( 2592000, 1 )
);

?>
<form id="general_settings" action="admin.php?page=reservation-settings" method="post" >
    <?php wp_nonce_field('easy-general-settings', 'easy-general-settings'); ?>
    <table class="easy-ui easy-ui-container" style="width:100%;margin-top: 10px">
        <thead>
            <tr>
                <th colspan="2"><?php echo sprintf(__('%s settings', 'easyReservations'),__('General', 'easyReservations')); ?></th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td colspan="2">
                    <input type="submit" onclick="document.getElementById('general_settings').submit(); return false;" class="easy-button" value="<?php _e('Submit', 'easyReservations');?>">
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td colspan="2" class="content">
                    <h2><?php _e('Format', 'easyReservations');?></h2>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Support email', 'easyReservations');?>
                    <?php easyreservations_help( 'Required. The email you set here will be the sender of emails to guests and receive emails to admin' ); ?>
                </td>
                <td>
                    <input type="text" name="reservations_support_mail" value="<?php echo get_option( "reservations_support_mail" ); ?>" style="width:50%">
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Money format', 'easyReservations');?>
                    <?php easyreservations_help( 'Define how to display prices. This is only a visual setting and does not change how the data is stored.' ); ?>
                </td>
                <td id="currency_settings">
                    <span class="together-wrapper">
                        <select id="reservations_currency" name="reservations_currency" class="first">
                            <?php
                                $currency_symbols = include RESERVATIONS_ABSPATH . 'i18n/currency-symbols.php';
                                $currency_locals = include RESERVATIONS_ABSPATH . 'i18n/currency-locals.php';
                                foreach($currency_symbols as $key => $currency_symbol){
                                    $class = '';
                                    if(isset($currency['locale']) && $currency['locale'] == $key) $class = ' selected="selected"';
                                    echo '<option value="'.$key.'" data-symbol="'.htmlentities($currency_symbol).'"'.$class.'>'.$currency_locals[$key].' (&'.$currency_symbol.';)</option>';
                                }
                            ?>
                        </select>
                        <?php echo easyreservations_generate_select('reservations_currency_place', array( __('after Price', 'easyReservations'), __('before Price', 'easyReservations')), $currency['place'], 'class="last"'); ?>
                    </span><br>

                    <label class="wrapper">
                        <input type="checkbox" name="reservations_currency_whitespace" <?php checked( $currency['whitespace'],1); ?>>
                        <span class="input"></span>
                        <?php _e('Whitespace between price and currency sign', 'easyReservations'); ?>
                    </label><br>

                    <?php echo easyreservations_generate_select('reservations_currency_divider1', $divider, $currency['divider1']); ?>
                    <?php _e('Thousand separator', 'easyReservations'); ?><br>

                    <?php echo easyreservations_generate_select('reservations_currency_divider2', $divider, $currency['divider2']); ?>
                    <?php _e('Decimal separator', 'easyReservations'); ?><br>

                    <input name="reservations_currency_decimal" id="reservations_currency_decimal" type="number" value="<?php echo $currency['decimal']; ?>" min="0" style="width:112px">
                    <?php _e('Number of decimals', 'easyReservations'); ?><br>

                    <?php _e('Example', 'easyReservations'); ?>
                    <code><span id="reservations_currency_example"></span></code>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Taxes', 'easyReservations');?>
                    <?php easyreservations_help( 'Changing this setting wont update existing reservations price calculation.' ); ?>
                </td>
                <td>
                    <?php echo easyreservations_generate_select('prices_include_tax', array(
                        0 => __('Enter prices exclusive of tax', 'easyReservations'),
                        1 => __('Enter prices inclusive of tax', 'easyReservations')
                    ), $prices_include_tax); ?>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Date format', 'easyReservations');?>
                    <?php easyreservations_help( 'How to display dates.' ); ?>
                </td>
                <td>
                    <?php echo easyreservations_generate_select('reservations_date_format', $date_formats, $date_format); ?>
                    <?php echo easyreservations_generate_select('reservations_time_format', $time_formats, $time_format); ?>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Time', 'easyReservations');?>
                    <?php easyreservations_help( 'Used throughout the whole system when displaying dates.' ); ?>
                </td>
                <td>
                    <label class="wrapper">
                        <input type="checkbox" name="reservations_time" id="reservations_time" <?php checked( $settings['time'], 1); ?>>
                        <span class="input"></span>
                        <?php _e('Enable display of time', 'easyReservations'); ?>
                    </label>
                </td>
            </tr>
            <?php do_action('easy_general_settings_format'); ?>

            <tr>
                <td colspan="2" class="content">
                    <h2><?php _e('Appearance', 'easyReservations');?></h2>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Primary color', 'easyReservations');?>
                    <?php easyreservations_help( 'Used to highlight elements in frontend.' ); ?>
                </td>
                <td>
                    <span class="input-wrapper">
                        <input name="primary-color" id="primary-color" type="text" value="<?php echo $primary_color; ?>">
                        <span class="input-box"><span id="color-me" class="fa fa-paint-brush clickable" style="color:<?php echo $primary_color; ?>"></span></span>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Frontend style', 'easyReservations');?>
                    <?php easyreservations_help( 'Switch between light or dark mode for form, calendar and search.' ); ?>
                </td>
                <td>
                    <?php echo easyreservations_generate_select('reservations_style', $styles, $settings['style']); ?>
                </td>
            </tr>
            <?php do_action('easy_general_settings_appearance'); ?>

            <tr>
                <td colspan="2" class="content">
                    <h2><?php _e('Availability', 'easyReservations');?></h2>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Merge resources', 'easyReservations');?>
                    <?php easyreservations_help( 'If enabled this overrides the resources quantity and only allows X reservations in all resource together.' ); ?>
                </td>
                <td>
                    <label class="wrapper">
                        <input type="checkbox" id="checkbox_merge" name="merge_resources" value="1" <?php echo $thenum > 0 ? 'checked="checked"' : ''; ?>>
                        <span class="input"></span>
                        <?php echo sprintf(
                            __('Only allow %s reservations at the same time across all resources', 'easyReservations'),
                            '<span class="select"><select name="reservations_resourcemerge" onclick="document.getElementById(\'checkbox_merge\').checked = true;">'.er_form_number_options(1,99,$thenum).'</select></span>'
                        ); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Block time', 'easyReservations');?>
                    <?php easyreservations_help( 'Block time before or after reservations for preparation or clean-up.' ); ?>
                </td>
                <td>
                    <?php echo sprintf(
                        __('Block %s before and %s after each reservation', 'easyReservations'),
                        easyreservations_generate_select('blockbefore', $time_array, $block_before),
                        easyreservations_generate_select('blockafter', $time_array, $block_after)
                    ); ?>
                </td>
            </tr>
            <?php do_action('easy_general_settings_availability'); ?>

            <tr>
                <td colspan="2" class="content">
                    <h2><?php _e('Permissions', 'easyReservations');?></h2>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Dashboard', 'easyReservations');?>
                    <?php easyreservations_help( $permission_string . 'dashboard. Additonally each resource has its own permission setting to define who can admin it.' ); ?>
                </td>
                <td>
                    <span class="select">
                        <select name="easy_permission_dashboard">
                            <?php echo easyreservations_get_roles_options($permission_options['dashboard']); ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Resources', 'easyReservations');?>
                    <?php easyreservations_help( $permission_string . 'resources' ); ?>
                </td>
                <td>
                    <span class="select">
                        <select name="easy_permission_resources">
                            <?php echo easyreservations_get_roles_options($permission_options['resources']); ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Statistics', 'easyReservations');?>
                    <?php easyreservations_help( $permission_string . 'statistics' ); ?>
                </td>
                <td>
                    <span class="select">
                        <select name="easy_permission_statistics">
                            <?php echo easyreservations_get_roles_options($permission_options['statistics']); ?>
                        </select>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Settings', 'easyReservations');?>
                    <?php easyreservations_help( $permission_string . 'settings' ); ?>
                </td>
                <td>
                    <span class="select">
                        <select name="easy_permission_settings">
                            <?php echo easyreservations_get_roles_options($permission_options['settings']); ?>
                        </select>
                    </span>
                </td>
            </tr>
            <?php do_action('easy_general_settings_permission'); ?>

            <tr>
                <td colspan="2" class="content">
                    <h2><?php _e('Miscellaneous', 'easyReservations');?></h2>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Tutorial', 'easyReservations');?>
                    <?php easyreservations_help( 'Whether to display the popup tutorial notes.' ); ?>
                </td>
                <td>
                    <label class="wrapper">
                        <input type="checkbox" name="reservations_tutorial" id="reservations_tutorial" value="1" <?php checked( $settings['tutorial'], 1 ); ?>>
                        <span class="input"></span>
                        <?php _e('Enable tutorial mode', 'easyReservations'); ?>
                    </label>
                    <a class="easy-button grey" href="admin.php?page=reservation-settings&tutorial_history=0"><?php _e('Reset', 'easyReservations'); ?></a>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Uninstall', 'easyReservations');?>
                    <?php easyreservations_help( 'Whether to keep data on uninstall.' ); ?>
                </td>
                <td>
                    <label class="wrapper">
                        <input type="checkbox" name="reservations_uninstall" id="reservations_uninstall" value="1" <?php checked(get_option( "reservations_uninstall" ), 1 ); ?>>
                        <span class="input"></span>
                        <?php _e('Delete settings, reservations and resources', 'easyReservations'); ?>
                    </label>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Important guests', 'easyReservations');?>
                    <?php easyreservations_help( 'Enter emails of important guests separated by comma. Reservations from these emails will be highlighted.' ); ?>
                </td>
                <td style="padding-top:5px">
                    <textarea name="regular_guests" style="width:90%;height:100px;margin-top:5px;"><?php echo get_option( 'reservations_regular_guests' ); ?></textarea>
                </td>
            </tr>
            <tr>
                <td class="label">
                    <?php _e('Execute script', 'easyReservations');?>
                    <?php easyreservations_help( 'Execute scripts after successful reservation. Has to be valid javascript - if you don\'t know what that is leave this empty.' ); ?>
                </td>
                <td style="padding-top:5px">
                    <textarea name="javascript" style="width:90%;height:100px;margin-top:5px;"><?php echo stripslashes(get_option( 'easyreservations_successful_script' )); ?></textarea>
                </td>
            </tr>
            <?php do_action('easy_general_settings_misc'); ?>

        </tbody>
    </table>
</form>
<script type="text/javascript">
    function easyreservations_currency_example(){
        var divider1 = jQuery('select[name=reservations_currency_divider1]').val();
        var divider2 = jQuery('select[name=reservations_currency_divider2]').val();
        var decimal = parseInt(jQuery('#reservations_currency_decimal').val());
        var place = jQuery('select[name=reservations_currency_place]').val();
        var sign = jQuery('#reservations_currency option:selected').attr('data-symbol');

        var price = 54 + divider1;
        if(decimal > 0) {
            price += 847 + divider2;
            for(var i = 0; i < decimal; i++){
                price += 9;
            }
        }
        else price += 848;
        if(place == 0){
            if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) price += ' ';
            price += '&'+sign+';';
        } else {
            var white = '';
            if(jQuery('input[name=reservations_currency_whitespace]').is(":checked")) white = ' ';
            price = '&' + sign + ';' + white + price;
        }
        jQuery('#reservations_currency_example').html(price);
    }
    jQuery(document).ready(function(){
        jQuery('#primary-color').iris(
            {
                hide:true,
                change: function(event, ui){
                    jQuery('#color-me').css( 'color', ui.color.toString());
                }
            }
            ).click(function (event) {
            jQuery(this).iris('show');
            return false;
        });

        jQuery(document).click(function (e) {
            if (!jQuery(e.target).is(".colour-picker, .iris-picker, .iris-picker-inner, #primary-color")) {
                jQuery('#primary-color').iris('hide');
            }
        });

            /*.ColorPicker({
            color: '#228dff',
            onChange: function (hsb, hex, rgb) {
                document.getElementById('primary-color').value = '#' + hex;
                jQuery('#color-me').css('color', '#' + hex);
            },
            onSubmit: function (hsb, hex, rgb) {
                document.getElementById('primary-color').value = '#' + hex;
            }
        });*/
    });
    jQuery('#currency_settings input,#currency_settings select').bind('change',function(){
        easyreservations_currency_example();
    });
    easyreservations_currency_example();
    easyUiTooltip();
</script>