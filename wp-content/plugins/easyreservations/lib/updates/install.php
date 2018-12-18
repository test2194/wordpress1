<?php

	$emailstandart0="[adminmessage]<br>
	Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br>edit your reservation on [editlink]";
	$emailstandart1="New Reservation on Blogname from<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]";
	$emailstandart2="Your Reservation on Blogname has been approved.<br>
	[adminmessage]<br><br>
	Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br>edit your reservation on [editlink]";
	$emailstandart3="Your Reservation on Blogname has been rejected.<br>
	[adminmessage]<br> <br>
	Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br>edit your reservation on [editlink]";
	$emailstandart4="We've got your reservaion and treat it as soon as possible.<br><br>
	Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]";
	$emailstandart5="Your reservation got edited from you. If this wasnt you, please contact us through this email address.<br><br>
	New Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";
	$emailstandart6="Reservation got edited by Guest.<br><br>
	New Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br><br>[changelog]";
	$emailstandart7="Your reservation got edited by admin.<br><br>
	[adminmessage]<br>
	New Reservation Details:<br>
	ID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Persons: [adults] <br>Children: [children] <br>Resource: [resource] <br>Price: [price]<br><br>edit your reservation on [editlink]<br><br>[changelog]";

	$default_form = easyreservations_get_default_form();

	$permission = array('dashboard' => 'edit_posts', 'statistics' => 'edit_posts', 'resources' => 'edit_posts', 'settings' => 'edit_posts');
	add_option('reservations_main_permission', $permission, '', 'yes' );
	add_option( 'reservations_email_to_user', array('msg' => $emailstandart4, 'subj' =>  'Your Reservation on '.get_option('blogname'), 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_userapp', array('msg' => $emailstandart2, 'subj' => 'Your Reservation on '.get_option('blogname').' has been approved', 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_userdel', array('msg' => $emailstandart3, 'subj' =>  'Your Reservation on '.get_option('blogname').' has been rejected', 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_admin', array('msg' => $emailstandart1, 'subj' =>  'New Reservation at '.get_option('blogname'), 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_user_edited', array('msg' => $emailstandart5, 'subj' =>  'Your Reservation on '.get_option('blogname').' got edited', 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_admin_edited', array('msg' =>  $emailstandart6, 'subj' => 'Reservation on '.get_option('blogname').' got edited by user', 'active' => 1), '', 'no');
	add_option( 'reservations_email_to_user_admin_edited', array('msg' => $emailstandart7, 'subj' =>  'Reservation on '.get_option('blogname').' got edited by admin', 'active' => 1), '', 'no');
	add_option( 'reservations_email_sendmail', array('msg' => $emailstandart0, 'subj' => 'Message from '.get_option('blogname'), 'active' => 1), '', 'no');

	add_option( 'reservations_uninstall', '1', '', 'no' );
	add_option( 'reservations_form', $default_form, '', 'no' );
	add_option( 'reservations_regular_guests', '', '', 'no' );
	add_option( 'reservations_edit_url', '', '', 'yes' );
	add_option( 'reservations_price_per_persons', '1', '', 'yes' );
	add_option( 'reservations_on_page', '10', '', 'no' );
	add_option( 'reservations_support_mail', '', '', 'yes' );
	add_option('reservations_db_version', ER()->database_version, '', 'yes' );
	$showhide = array( 'show_overview' => 1, 'show_table' => 1, 'show_upcoming' => 1, 'show_new' => 1, 'show_export' => 1, 'show_today' => 1 );
	$table = array( 'table_color' => 1, 'table_id' => 0, 'table_name' => 1, 'table_from' => 1, 'table_to' => 1, 'table_nights' => 1, 'table_email' => 1, 'table_fav' => 1, 'table_room' => 1, 'table_exactly' => 1, 'table_offer' => 1, 'table_persons' => 1, 'table_childs' => 1, 'table_country' => 1, 'table_message' => 0, 'table_custom' => 0, 'table_paid' => 0, 'table_price' => 1, 'table_filter_month' => 1, 'table_filter_room' => 1, 'table_filter_offer' => 1, 'table_filter_days' => 1, 'table_search' => 1, 'table_bulk' => 1, 'table_onmouseover' => 1, 'table_reservated' => 0, 'table_status' => 1, 'table_fav' => 1 );
	$overview = array( 'overview_onmouseover' => 1, 'overview_autoselect' => 1, 'overview_show_days' => 30, 'overview_show_rooms' => '' );
	add_option('reservations_main_options', array('show' => $showhide, 'table' => $table, 'overview' => $overview ), '', 'no');
	$edit_options = array( 'login_text' => '', 'edit_text' => '', 'submit_text' => 'Reservation successfully edited',  'table_infos' => array('date', 'status', 'price', 'room'), 'table_status' => array('','yes','no'), 'table_time' => array('past','current','future'), 'table_style' => 1, 'table_more' => 1 );
	add_option('reservations_edit_options', $edit_options, '', 'no');
	add_option('reservations_settings', array(  'currency' => '#36', 'date_format' => 'd.m.Y', 'time' => 1, 'tutorial' => 1 ), '', 'yes');

	$default = '<label>Arrival:</label>[date-from style="width:95px"] [date-from-hour][date-from-min]'."\n".'<label>Departure:</label>[date-to style="width:95px"] [date-to-hour][date-to-min]'."\n".'<label>Resource:</label> [resources]'."\n".'<label>Name:</label> [name]'."\n".'<label>Email:</label> [email]'."\n".'<label>Country:</label> [country]';
	add_option('reservations_form_default-widget', $default, false, 'no');

	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	$max_index_length = 191;

	$sql = "CREATE TABLE {$wpdb->prefix}reservations (
				id int(10) NOT NULL AUTO_INCREMENT,
				arrival DATETIME NOT NULL,
				departure DATETIME NOT NULL,
				user int(10) NOT NULL,
				name varchar(35) NOT NULL,
				email varchar(50) NOT NULL,
				country varchar(4) NOT NULL,
				approve varchar(3) NOT NULL,
				resource int(10) NOT NULL,
				space int(10) NOT NULL,
				adults int(10) NOT NULL,
				children int(10) NOT NULL,
				price DECIMAL(13,4),
				paid DECIMAL(13, 4) NOT NULL default '0',
				reserved DATETIME NOT NULL,
			  PRIMARY KEY (id)
			) $charset_collate;";

	$sql .= "CREATE TABLE {$wpdb->prefix}reservationmeta (
			  meta_id bigint(20) unsigned NOT NULL auto_increment,
			  reservation_id bigint(20) unsigned NOT NULL default '0',
			  meta_key varchar(255) default NULL,
			  meta_value longtext,
			  PRIMARY KEY  (meta_id),
			  KEY reservation_id (reservation_id),
			  KEY meta_key (meta_key($max_index_length))
			) $charset_collate;";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

	$room_args = array( 'post_status' => 'publish|private', 'post_type' => 'easy-rooms', 'orderby' => 'post_title', 'order' => 'ASC', 'numberposts' => 1);
	$roomcategories = get_posts( $room_args );
	if(!$roomcategories){
		$roomOne = array(
			'post_title' => 'Sample Resource One',
			'post_content' => 'This is a Sample Resource.',
			'post_status' => 'private',
			'post_author' => 1,
			'post_type' => 'easy-rooms'
		);

		$roomOne_id = wp_insert_post( $roomOne );
		add_post_meta($roomOne_id, 'roomcount', 4);
		add_post_meta($roomOne_id, 'reservations_groundprice', 120);
		add_post_meta($roomOne_id, 'reservations_child_price', 10);

		$roomTwo = array(
			'post_title' => 'Sample Resource Two',
			'post_content' => 'This is a Sample Resource.',
			'post_status' => 'private',
			'post_author' => 1,
			'post_type' => 'easy-rooms'
		);

		$roomTwo_id = wp_insert_post( $roomTwo );
		add_post_meta($roomTwo_id, 'roomcount', 7);
		add_post_meta($roomTwo_id, 'reservations_groundprice', 250.57);
		add_post_meta($roomTwo_id, 'reservations_child_price', 20);
	}

    add_option('reservations_custom_fields', array(
        'id' => 4,
        'fields' => array(
            1 => array( 'title'  => 'Street', 'type'   => 'text',  'value'  => '', 'unused' => ''),
            2 => array( 'title'  => 'Postcode', 'type'   => 'text',  'value'  => '', 'unused' => ''),
            3 => array( 'title'  => 'City', 'type'   => 'text',  'value'  => '', 'unused' => ''),
            4 => array( 'title'  => 'Message', 'type'   => 'area',  'value'  => '', 'unused' => ''),
        )
    ));
