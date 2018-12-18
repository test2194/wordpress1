<?php

function easyreservations_tutorial_get_current_screen(){
    if(isset($_GET['page']) && $_GET['page'] == 'reservation-settings'){
        if(isset($_GET['tab']) && $_GET['tab'] == "invoice"){
            if(function_exists('easyreservations_generate_invoice')){
                include_once(dirname(__FILE__)."/invoices.tutorials.php");
                return 'invoice';
            }
        } elseif(isset($_GET['tab']) && $_GET['tab'] == "coupons"){
            if(function_exists('easyreservations_admin_add_coupon_to_res')){
                include_once(dirname(__FILE__)."/coupons.tutorials.php");
                return 'coupons';
            }
        } elseif(isset($_GET['tab']) && $_GET['tab'] == "pay"){
            if(function_exists('easyreservations_generate_paypal_button')){
                include_once(dirname(__FILE__)."/paypal.tutorials.php");
                return 'paypal';
            }
        } elseif(isset($_GET['tab']) && $_GET['tab'] == "form"){
            include_once(dirname(__FILE__)."/form.tutorials.php");
            return 'form';
        } elseif(isset($_GET['tab']) && $_GET['tab'] == "custom"){
            include_once(dirname(__FILE__)."/custom.tutorials.php");
            return 'custom';
        } elseif(isset($_GET['tab']) && $_GET['tab'] == "email"){
            if(function_exists('easyreservations_send_multipart_mail')){
                include_once(dirname(__FILE__)."/emailshtml.tutorials.php");
                return 'emailshtml';
            } else {
                include_once(dirname(__FILE__)."/emails.tutorials.php");
                return 'emails';
            }
        }
    } elseif(isset($_GET['page']) && $_GET['page'] == 'reservation-resources'){
        if(isset($_GET['resource'])){
        } elseif(isset($_GET['addresource'])){
        } else {
            include_once(dirname(__FILE__)."/resources.tutorials.php");
            return 'resources';
        }
    } elseif(isset($_GET['page']) && $_GET['page'] == 'reservations'){
        if(isset($_GET['edit'])){

        } elseif(isset($_GET['add'])){

        } elseif(isset($_GET['delete'])){

        } elseif(isset($_GET['approve'])){

        } elseif(isset($_GET['sendmail'])){

        } else {
            include_once(dirname(__FILE__)."/dashboard.tutorials.php");
            return 'dashboard';
        }
    } elseif(strpos($_SERVER['SCRIPT_NAME'], 'post-new.php') !== false || (isset($_GET['action']) && strpos($_SERVER['SCRIPT_NAME'], 'post.php') !== false && $_GET['action'] == 'edit')) {
        include_once(dirname(__FILE__)."/post.tutorials.php");
        return 'post';
    } elseif(strpos($_SERVER['SCRIPT_NAME'], 'tinyMCE_shortcode_add.php') !== false) {
        include_once(dirname(__FILE__)."/tinymce.tutorials.php");
        return 'tinymce';
    } elseif(strpos($_SERVER['SCRIPT_NAME'], 'widgets.php') !== false) {
        include_once(dirname(__FILE__)."/widgets.tutorials.php");
        return 'widgets';
    }

    return '';
}

function easyreservations_execute_pointer($nr, $handler, $content, $at, $execute = false, $custom = array()){
		$script = easyreservations_tutorial_get_current_screen();
		$return = '<script type="text/javascript">';
		$option = get_user_setting( 'easy_tutorial');
		if($option && !empty($option)) $option.= 'X';
		$save = "setUserSetting( 'easy_tutorial', '$option$script' );";

		for($i = 0; $i < $nr; $i++){
			$send = '';
			if($i == $nr-1) $send = $save;
			if(isset($execute[$i])) $send.= $execute[$i];
			if(!isset($custom[$i])) $custom[$i] = '';
			$return .= 'function easypointer'.$i.'(){ ';
			$return .= easyreservations_generate_pointer( $send, $i,
				$handler[$i],
				$content[$i],
				$at[$i],
				$custom[$i]
			);
			$return .= '}';
		}
		$return.=<<<EOF
	
	jQuery(document).keyup(function(e) {
		if(e.keyCode == 27) { jQuery('.wp-pointer-buttons > a.close').click(); }   // esc
	});
	jQuery(window).on("load", function() {
		easypointer0();
	});</script>
EOF;
		return $return;
	}

	function easyreservations_generate_pointer($send, $nr, $handler, $content, $at, $custom){
		$nr++;
		$nr2 = $nr+1;
		$nr3 = $nr2+1;
		$return = <<<EOF
if(jQuery('$handler').length>0){
	jQuery('$handler').pointer({
		content: '$content',
		$custom
		buttons: function( event, t ) {
			button = jQuery('<a class="close" href="#">Continue [ESC]</a>');
			return button.bind( 'click.pointer', function(e) {
				e.preventDefault();
				t.element.pointer('close');
			});
		},
		position: {
			my: 'left top',
			at: 'left bottom',
			edge: '$at'
		},
		close: function() {
			$send
			if(window.easypointer$nr) easypointer$nr();
			else if(window.easypointer$nr2) easypointer$nr2();
		}
	}).pointer('open');
} else if(window.easypointer$nr) easypointer$nr();
EOF;
		return $return;
	}

	function easyreservations_load_pointer() {
        $script = easyreservations_tutorial_get_current_screen();
		if(!empty($script)){
			$admin_bar = get_user_setting( 'easy_tutorial');
			if(!empty($admin_bar)){
				$explode = explode('X', $admin_bar);
				if(!empty($explode) && in_array($script, $explode)) return false;
			}
			add_action( 'admin_print_footer_scripts', 'easyreservations_'.$script.'_tutorial', 19 );
			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script( 'utils' ); // for user settings
		}
		return true;
	}
	add_action( 'admin_enqueue_scripts', 'easyreservations_load_pointer' );
