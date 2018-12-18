<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}?>
<h2 style="display: inline-block">
    <?php _e('Settings', 'easyReservations');?>
</h2>

<?php do_action( 'er_add_settings_top' ); ?>

<ul class="easy-ui easy-ui-container easy-navigation">
    <li>
        <a href="admin.php?page=reservation-settings" class="<?php if($current_tab == 'general') echo 'active'; ?>">
            <span class="fa fa-cog"></span> <?php _e('General', 'easyReservations'); ?>
        </a>
    </li>
    <li>
        <a href="admin.php?page=reservation-settings&tab=form" class="<?php if($current_tab == 'form') echo 'active'; ?>">
            <span class="fa fa-check-square-o"></span> <?php _e('Form', 'easyReservations'); ?>
        </a>
    </li>
    <li>
        <a href="admin.php?page=reservation-settings&tab=custom" class="<?php if($current_tab == 'custom') echo 'active'; ?>">
            <span class="fa fa-tag"></span> <?php _e('Custom', 'easyReservations'); ?>
        </a>
    </li>
    <li>
        <a href="admin.php?page=reservation-settings&tab=email" class="<?php if($current_tab == 'email') echo 'active'; ?>">
            <span class="fa fa-envelope-o"></span> <?php _e('Email', 'easyReservations'); ?>
        </a>
    </li>
    <?php do_action( 'easy_settings_navigation' ); ?>
    <li>
        <a href="admin.php?page=reservation-settings&tab=about" class="<?php if($current_tab == 'about') echo 'active'; ?>">
            <span class="fa fa-info"></span> <?php _e('About', 'easyReservations'); ?>
        </a>
    </li>
</ul>

<?php ER()->messages()->output(); ?>