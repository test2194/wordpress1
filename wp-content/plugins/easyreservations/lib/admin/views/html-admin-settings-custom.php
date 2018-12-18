<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script('custom-fields', RESERVATIONS_URL.'assets/js/functions/custom.settings.js');
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style( 'datestyle' );

$resources = array();
foreach(ER()->resources()->get() as $key => $resource){
    $resources[$key] = stripslashes($resource->post_title);
}

$custom_fields = get_option('reservations_custom_fields', array());

?>
<table id="custom_fields_table" class="<?php echo RESERVATIONS_STYLE; ?> table" style="width:100%">
    <thead>
        <tr>
            <th><?php _e('ID', 'easyReservations'); ?></th>
            <th><?php _e('Title', 'easyReservations'); ?></th>
            <th><?php _e('Type', 'easyReservations'); ?></th>
            <th><?php _e('Value', 'easyReservations'); ?></th>
            <th colspan="2"><?php _e('Unused', 'easyReservations'); ?></th>
        </tr>
    </thead>
    <tbody>
    <?php if($custom_fields && !empty($custom_fields)): ?>
        <?php foreach($custom_fields['fields'] as $key => $custom_field): ?>
            <tr>
                <td><?php echo $key; ?></td>
                <td><?php echo $custom_field['title']; ?></td>
                <td><?php echo ucfirst($custom_field['type']); ?></td>
                <td>
                    <?php if($custom_field['type'] == 'select' || $custom_field['type'] == 'radio' ): ?>
                        <ul class="options">
                            <?php foreach($custom_field['options'] as $opt_id => $option): ?>
                                <li class="<?php if($opt_id == $custom_field['value']) echo 'selectedoption'; ?>">
                                    <?php echo $option['value']; ?>
                                    <?php if(isset($option['price'])) echo er_format_money( $option['price'], true); ?>
                                    <?php if(isset($option['clauses'])): ?>
                                        (<?php echo count($option['clauses']). _n('Condition', 'Conditions', count($option['clauses']), 'easyReservations'); ?>)
                                    <?php endif; ?>

                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php
                        elseif(isset($custom_field['value'])):
                            echo $custom_field['value'];
                        endif;
                    ?>
                </td>
                <td>
                    <?php echo $custom_field['unused']; ?>
                </td>
                <td style="width:60px">
                    <a href="javascript:custom_edit(<?php echo $key; ?>);" class="fa fa-pencil"></a>
                    <a href="<?php echo wp_nonce_url('admin.php?page=reservation-settings&tab=custom&delete-custom='.$key, 'easy-delete-custom'); ?>" class="fa fa-trash"></a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="5"><?php _e('No custom fields defined', 'easyReservations'); ?></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>
<form name="custom_creator" id="custom_creator" method="post" style="margin-top: 10px">
    <table id="custom_field_add" class="<?php echo RESERVATIONS_STYLE; ?>" style="width:100%">
        <thead>
            <tr>
                <th>
                    <?php _e('Custom field', 'easyReservations'); ?>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td>
                    <input type="submit" value="<?php _e('Submit', 'easyReservations'); ?>" class="easy-button">
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr>
                <td class="content">
                    <?php _e('With custom fields you can add your own fields to the form. The data gathered can be used through the whole system in emails, invoices and when the guest edits his reservation.', 'easyReservations'); ?>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="in-hierarchy"><?php _e('Title', 'easyReservations'); ?></label>
                    <input type="text" name="custom_name" id="custom_name">
                </td>
            </tr>
            <tr>
                <td>
                    <label class="in-hierarchy"><?php _e('Price', 'easyReservations'); ?></label>
                    <label class="wrapper"><input id="custom_price_field" name="custom_price_field" type="checkbox"><span class="input"></span>
                        <?php _e('Field has influence on price', 'easyReservations'); ?></label>
                </td>
            </tr>
            <tr id="custom_type_tr">
                <td>
                    <label class="in-hierarchy"><?php _e('Type', 'easyReservations'); ?></label>
                    <span class="select"><select name="custom_field_type" id="custom_field_type">
                            </select></span>
                </td>
            </tr>
            <tr>
                <td colspan="2" id="custom_field_extras">
                </td>
            </tr>
        </tbody>
    </table>
</form>
<script>
    var plugin_url = "<?php echo WP_PLUGIN_URL; ?>";
    var currency = "<?php echo RESERVATIONS_CURRENCY; ?>";
    var custom_nonce = "<?php echo wp_create_nonce('easy-custom'); ?>";
    var all_custom_fields = <?php echo json_encode(isset($custom_fields['fields']) ? $custom_fields['fields'] : array()); ?>;
    var resources = <?php echo json_encode($resources); ?>;
</script>

