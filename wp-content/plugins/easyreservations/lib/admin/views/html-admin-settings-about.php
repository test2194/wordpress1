<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}?>
<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="width:100%" cellspacing="0" cellpadding="0">
    <thead>
    <tr>
        <th colspan="2"> <?php _e('Status', 'easyReservations');?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="font-weight:bold;padding:10px;text-align:center"><span style="width:30%;display: inline-block">Version: <?php echo RESERVATIONS_VERSION; ?></span><span style="width:30%;display: inline-block">Last update: 16.11.2018</span><span style="width:30%;display: inline-block">written by Feryaz Beer</span></td>
    </tr>
    <tr class="alternate">
        <td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/knowledgebase/" target="_blank" id="iddocumentation"><?php _e('Documentation', 'easyReservations');?></a></td>
    </tr>
    <tr>
        <td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/forums/forum/bug-reports/" target="_blank" id="idbugreport"><?php _e('Report bug', 'easyReservations');?></a></td>
    </tr>
    <tr class="alternate">
        <td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://easyreservations.org/premium/" target="_blank" id="idpremium"><?php _e('Premium', 'easyReservations');?></a></td>
    </tr>
    <tr>
        <td style="font-size:14px;text-align:center;font-weight:bold;padding:10px"><a href="http://wordpress.org/extend/plugins/easyreservations/" target="_blank" id="idrate"><?php _e('Rate the Plugin, please!', 'easyReservations'); ?></a></td>
    </tr>
    </tbody>
</table>

<table id="changelog" class="<?php echo RESERVATIONS_STYLE; ?>" style="margin-top:10px;width:100%">
    <thead>
    <tr>
        <th> <?php _e('Changelog', 'easyReservations');?></th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td style="width:100%;line-height: 22px" align="left">
            <?php include(RESERVATIONS_ABSPATH.'changelog.html');?>
        </td>
    </tr>
    </tbody>
</table>
