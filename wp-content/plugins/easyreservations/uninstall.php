<?php

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit();

function easyreservations_delete_plugin() {
	global $wpdb;
	
	$delete = get_option( 'reservations_uninstall' );
	
	if($delete != 0){
		
		// OLD OPTIONS

		delete_option( 'reservations_backgroundiffull' );
		delete_option( 'reservations_border_bottom' );
		delete_option( 'reservations_border_side' );
		delete_option( 'reservations_colorbackgroundfree' );
		delete_option( 'reservations_fontcoloriffull' );
		delete_option( 'reservations_fontcolorifempty' );
		delete_option( 'reservations_colorborder' );
		delete_option( 'reservations_overview_size' );
		delete_option( 'reservations_email_to_userapp_subj' );
		delete_option( 'reservations_email_to_userapp_msg' );
		delete_option( 'reservations_email_to_userdel_subj' );
		delete_option( 'reservations_email_to_userdel_msg' );
		delete_option( 'reservations_email_to_admin_subj' );
		delete_option( 'reservations_email_to_admin_msg' );
		delete_option( 'reservations_email_to_user_subj' );
		delete_option( 'reservations_email_to_user_msg' );
		delete_option( 'reservations_email_to_user_edited_subj' );
		delete_option( 'reservations_email_to_user_edited_msg' );
		delete_option( 'reservations_email_to_admin_edited_subj' );
		delete_option( 'reservations_email_to_admin_edited_msg' );
		delete_option( 'reservations_email_to_user_admin_edited_subj' );
		delete_option( 'reservations_email_to_user_admin_edited_msg' );
		delete_option( 'reservations_email_sendmail_subj' );
		delete_option( 'reservations_email_sendmail_msg' );
		delete_option( 'reservations_overview_size' );
		delete_option( 'reservations_currency' );
		delete_option( 'reservations_room_category' );
		delete_option( 'reservations_special_offer_cat' );

		//CURRENT OPTIONS
		
		delete_option( 'reservations_email_sendmail' );
		delete_option( 'reservations_email_to_admin' );
		delete_option( 'reservations_email_to_admin' );
		delete_option( 'reservations_email_to_user' );
		delete_option( 'reservations_email_to_userapp' );
		delete_option( 'reservations_email_to_userdel' );
		delete_option( 'reservations_email_to_user_admin_edited' );
		delete_option( 'reservations_email_to_user_edited' );
		delete_option( 'reservations_email_to_admin_paypal' );
		delete_option( 'reservations_email_to_user_paypal' );
		delete_option( 'reservations_regular_guests' );
		delete_option( 'reservations_paypal_options' );
		delete_option( 'reservations_authorize_options' );
		delete_option( 'reservations_wallet_options' );
		delete_option( 'reservations_dibs_options' );
		delete_option( 'reservations_ogone_options' );
		delete_option( 'reservations_autoapprove' );
		delete_option( 'reservations_availability_check_pending' );
		delete_option( 'reservations_main_options' );
		delete_option( 'reservations_show_days' );
		delete_option( 'reservations_price_per_persons' );
		delete_option( 'reservations_on_page' );
		delete_option( 'reservations_support_mail' );
		delete_option( 'reservations_coupons' );
		delete_option( 'reservations_uninstall' );
		delete_option( 'reservations_settings' );
		delete_option( 'reservations_form' );
		delete_option( 'reservations_db_version' );
		delete_option( 'reservations_edit_options' );
		delete_option( 'reservations_edit_url' );
		delete_option( 'reservations_main_permission' );
		delete_option( 'reservations_custom_fields' );
		delete_option( 'reservations_active_modules' );
		delete_option( 'reservations_login' );
		delete_option( 'reservations_invoice_number' );
		delete_option( 'reservations_invoice_options' );
		delete_option( 'reservations_credit_card_options' );
		delete_option( 'reservations_google_wallet_queue' );
		delete_option( 'reservations_search_attributes' );
		delete_option( 'reservations_search_bar' );
		delete_option( 'reservations_search_posttype' );
		delete_option( 'reservations_datepicker' );
		delete_option( 'reservations_ics_import' );
		delete_option( 'reservations_woocommerce' );
		delete_option( 'reservations_chat_options' );
		delete_option( 'reservations_edit_url' );
		delete_option( 'reservations_edit_options' );
		delete_option( 'reservations_woo_product_ids' );

		delete_option( 'easyreservations_successful_script' );

		$resources = get_pages( array( 'post_type' => 'easy-rooms') );

		foreach ( $resources as $resource ) {
			wp_delete_post( $resource->ID, true);
		}

		$table_name = $wpdb->prefix."reservations";
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );

		$table_name = $wpdb->prefix."reservationsmeta";
		$wpdb->query( "DROP TABLE IF EXISTS $table_name" );
	}
}

easyreservations_delete_plugin();

?>