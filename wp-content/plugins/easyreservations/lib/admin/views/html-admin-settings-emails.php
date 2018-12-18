<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}
$emails = easyreservations_get_emails();
?>

<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="margin-bottom:5px;width:30%;float:right">
    <thead>
        <tr><th><?php _e('Available tags', 'easyReservations');?></th></tr>
    </thead>
    <tbody>
        <tr><td><code class="codecolor">&lt;br&gt;</code> <i><?php _e('Wordwrap', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[adminmessage]</code> <i><?php _e('Message from admin', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[ID]</code> <i><?php _e('ID', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[name]</code> <i><?php _e('Name', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[email]</code> <i><?php _e('Email', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[arrival]</code> <i><?php _e('Arrival date', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[departure]</code> <i><?php _e('Departure date', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[units]</code> <i><?php echo sprintf(__('Amount of %s', 'easyReservations'), __('billing units', 'easyReservations')); ?></i></td></tr>
        <tr><td><code class="codecolor">[persons]</code> <i><?php echo sprintf(__('Amount of %s', 'easyReservations'), __('persons', 'easyReservations')); ?></i></td></tr>
        <tr><td><code class="codecolor">[adults]</code> <i><?php echo sprintf(__('Amount of %s', 'easyReservations'), __('adults', 'easyReservations'));?></i></td></tr>
        <tr><td><code class="codecolor">[children]</code> <i><?php echo sprintf(__('Amount of %s', 'easyReservations'), __('children', 'easyReservations'));?></i></td></tr>
        <tr><td><code class="codecolor">[country]</code> <i><?php _e('Country', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[resource]</code> <i><?php _e('Resource', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[resource-space]</code> <i><?php _e('Resource space', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[price]</code> <i><?php _e('Price', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[paid]</code> <i><?php _e('Paid', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor" id="idtagcustom">[custom id="*"]</code> <i><?php _e('Custom field', 'easyReservations');?></i></td></tr>
        <tr><td><code class="codecolor">[editlink]</code> <i><?php _e('Link to user control panel', 'easyReservations');?></i></td></tr>
        <?php do_action('easy_email_settings_tags_list'); ?>
    </tbody>
</table>
<form method="post" action="admin.php?page=reservation-settings&tab=email"  id="reservations_email_settings" name="reservations_email_settings">
    <?php wp_nonce_field( 'easy-emails', 'easy-emails' ); ?>
    <?php foreach($emails as $key => $email) :?>
    <table class="<?php echo RESERVATIONS_STYLE; ?> table" style="margin-bottom:5px;width:70%">
        <thead>
        <tr>
            <th>
                <?php echo $email['name']; ?>
                <span style="float:right; font-size:13px">
                    <label class="wrapper">
                        <input type="checkbox" value="1" name="<?php echo $key; ?>_check" <?php checked(1, $email['option']['active'] ); ?>>
                        <span class="input"></span> <?php _e('Active', 'easyReservations'); ?>
                    </label>
                </span>
                </th>
            </tr>
        </thead>
        <tfoot>
            <tr>
                <td>
                    <input type="button" onclick="document.getElementById('reservations_email_settings').submit(); return false;" class="easy-button" value="<?php _e('Submit', 'easyReservations'); ?>">
                </td>
            </tr>
        </tfoot>
        <tbody>
            <tr valign="top">
                <td>
                    <input type="text" name="<?php echo $key; ?>_subj" style="width:60%;" value="<?php echo stripslashes($email['option']['subj']);?>">
                    <?php _e('Subject', 'easyReservations'); ?>
                    <input type="button" onclick="setDefault('<?php echo $key; ?>');" style="float:right" class="easy-button grey" value="<?php _e('Default', 'easyReservations'); ?>">
                </td>
            </tr>
            <tr valign="top">
                <td>
                    <textarea id="<?php echo $key; ?>_msg" name="<?php echo $key; ?>_msg" style="width:99%;height:200px;"><?php echo stripslashes($email['option']['msg']); ?></textarea>
                </td>
            </tr>
        </tbody>
    </table>
    <?php endforeach; ?>
</form>
<script>
    var emails = <?php echo json_encode($emails); ?>;
    function setDefault(email){
        jQuery('#'+email+'_msg').val(emails[email]['default'])
    }
</script>