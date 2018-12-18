<?php

/**
 * 	Hook languages to admin & frontend
 */

if(isset($_GET['page'])){

	$page = $_GET['page'];
	/**
	 * 	*	Returns info box
	 * @param $res ER_Reservation
	 * @param $where place to display info box
	 * @param $status
	 *
	 * @return string
	 */
	function easyreservations_reservation_info_box($res, $where, $status){
		if($res->paid > 0 && $res->get_price() > 0){
			$percent = round(100/$res->get_price()*$res->paid, RESERVATIONS_DECIMAL);
			if($percent == 100) $color = 'color-green';
			elseif($percent > 100) $color = 'color-purple	';
			else $color = 'color-orange';
		} else {
			$percent = '0';
			$color = 'color-red';
		}

		if(current_time( 'timestamp' ) >= $res->arrival && current_time( 'timestamp' ) <= $res->departure){
			$text = __('Active', 'easyReservations');
			$text_color = 'color-green';
			$time = round(($res->departure - current_time( 'timestamp' ))/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} elseif(current_time( 'timestamp' ) < $res->arrival){
			$text = __('Future', 'easyReservations');
			$text_color = 'color-red';
			$time = round(($res->arrival - current_time( 'timestamp' ))/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">+'.$time.'</small>';
		} else {
			$text = __('Past', 'easyReservations');
			$text_color = 'color-blue';
			$time = round((current_time( 'timestamp' )-$res->departure)/86400);
			$text .= '<small style="font-weight:normal;font-size:11px;">-'.$time.'</small>';
		}

		$box = '<div class="explainbox">';
			$box .= '<span>';
				$box .= __('Price', 'easyReservations');
				$box .= '<b>'.$res->formatPrice().'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __('Paid', 'easyReservations');
				$box .= '<b><span style="color:'.$color.'">'.er_format_money($res->paid, true).'</span> <small>'.$percent.'%</small></b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= er_date_get_interval_label(0, 1);
				$box .= '<b class="'.$text_color.'">'.$text.'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= __('Status', 'easyReservations');
				$box .= '<b>'.$res->getStatus(true).'</b>';
			$box .= '</span>';
			$box .= '<span>';
				$box .= easyreservations_get_administration_links($res->id, $where, $status);
			$box .= '</span>';
		$box .= '</div>';

		return $box;
	}

	function easyreservations_get_emails() {
		$emails = array(
			'reservations_email_sendmail' => array(
				'name' => __( 'Email to guest from admin in dashboard' ),
				'option' => get_option( 'reservations_email_sendmail' ),
				'default' => "[adminmessage]<br><br>\rReservation details:<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource] <br>Resource space: [resource-space]<br>Price: [price]",
			),
			'reservations_email_to_admin' => array(
				'name' => __( 'Email to admin after new reservation' ),
				'option' => get_option( 'reservations_email_to_admin' ),
				'default' => "New reservation on Blogname<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource]<br>Price: [price]",
			),
			'reservations_email_to_user' => array(
				'name' => __( 'Email to guest after new reservation' ),
				'option' => get_option( 'reservations_email_to_user' ),
				'default' => "We\'ve got your reservation and treat it as soon as possible.<br><br>\rReservation details:<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource]<br>Resource space: [resource-space]<br>Price: [price]",
			),
			'reservations_email_to_userapp' => array(
				'name' => __( 'Email to guest after approval' ),
				'option' => get_option( 'reservations_email_to_userapp' ),
				'default' => "Your reservation on Blogname has been approved.<br>\r[adminmessage]<br><br>\rReservation details:<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource] <br>Resource space: [resource-space]<br>Price: [price]",
			),
			'reservations_email_to_userdel' => array(
				'name' => __( 'Email to guest after rejection' ),
				'option' => get_option( 'reservations_email_to_userdel' ),
				'default' => "Your reservation on Blogname has been rejected.<br>\r[adminmessage]<br><br>\rReservation details:<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource]<br><br>Price: [price]"
			),
			'reservations_email_to_user_admin_edited' => array(
				'name' => __( 'Email to guest after admin edited' ),
				'option' => get_option( 'reservations_email_to_user_admin_edited' ),
				'default' => "Your reservation got changed by admin.<br><br>\r[adminmessage]<br>\rNew Reservation details:<br>\rID: [ID]<br>Name: [name] <br>Email: [email] <br>From: [arrival] <br>To: [departure] <br>Adults: [adults] <br>Children: [children] <br>Resource: [resource] <br>Resource space: [resource-space]<br>Price: [price]",
			),
		);
		return apply_filters( 'easy_email_types', $emails );
	}

	/**
	*	Get administration links
	*
	*	$id = reservations id
	*	$where = place to display info box
	*/

	function easyreservations_get_administration_links($id, $where, $status){
		$countits=0;
		$administration_links = '';
		if($where != "approve" && $status != 'yes') { $administration_links.='<a href="admin.php?page=reservations&approve='.$id.'">'.__('Approve', 'easyReservations').'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' ';}
		if($where != "reject" && $status !='no') { $administration_links.='<a href="admin.php?page=reservations&delete='.$id.'">'.__('Reject', 'easyReservations').'</a>'; $countits++; }
		if($countits > 0){ $administration_links.='DASD';}
		if($where != "edit") { $administration_links.='<a href="admin.php?page=reservations&edit='.$id.'">'.__('Edit', 'easyReservations').'</a>'; $countits++; }
		if($countits > 0){ $administration_links.=' '; }
		$administration_links.='<a href="admin.php?page=reservations&sendmail='.$id.'">'.__('Email', 'easyReservations').'</a>'; $countits++;
		if($countits > 3) $administration_links = str_replace('DASD', '</span><span style="padding-left:0;">', $administration_links);
		else $administration_links = str_replace('DASD', '', $administration_links);
		//if($countits > 0){ $administration_links.=' | '; $countits=0; }
		//if($where != "trash" AND $checkID != "trashed") { $administration_links.='<a href="admin.php?page=reservations&bulkArr[]='.$id.'&bulk=1">'.__('Trash', 'easyReservations').'</a>'; $countits++; }
		$administration_links = apply_filters('easy_administration_links', $administration_links, $id, $where);
		
		return $administration_links;
	}

	function easyreservations_get_user_options($sel = 0){
		$blog_users = get_users();
		$options = '';

		foreach ($blog_users as $usr){
			$selected = $sel == $usr->ID ? 'selected="selected"' : '';
			$options.='<option value='.$usr->ID.' '.$selected.'>'.$usr->display_name.'</option>';
		}
		return $options;
	}

	function easyreservations_get_resource_spaces_options( $max, $resource_id, $selected = false, $add_resource_to_value = false){
		$resource_spaces = get_post_meta($resource_id, 'easy-resource-roomnames', TRUE);
		$options = '';
		for( $i = 0; $i < $max; $i++){
			$name = isset($resource_spaces[$i]) && !empty($resource_spaces[$i]) ? __($resource_spaces[$i]) : $i+1;
			$selected = $selected && $selected == $i+1 ? 'selected="selected"' : '';
			$options .= '<option value="'.($add_resource_to_value ? $resource_id.'-' : '').''.($i+1).'" '.$selected.'>'.addslashes($name).'</option>';
		}
		return $options;
	}
}

	function easyreservations_days_options($name, $selected){
		$return = '';
		for($i = 1; $i < 8; $i++){
			$return .= '<label class="wrapper days-option">';
			$return .= '<input type="checkbox" name="'.$name.'" value = "'.$i.'" '.checked(($selected == 0 || (is_array($selected) && in_array($i, $selected))) ? true : false,true,false).'>';
			$return .= '<span class="input"></span>'.er_date_get_label(0, 0, $i-1).'</label>';
		}
		return $return;
	}
	/* *
	*	Table ajax request
	*/

	function easyreservations_generate_table($id, $header, $rows, $footer = false, $attr = ''){
		$return = ''; $i = 0; $two = false;
		foreach($rows as $key => $value){
			$sec = false;
			$ids = '';
			$col = '';

			if(substr($key,0,3) == 'col'){
				$key = 'col';
				$i = 0;
				$col = ' colspan="2" class="content"';
			}

			if(!is_numeric($key) && $key != 'col'){
				$sec = $value;
				if(is_array($sec)){
					$ids = ' id="'.$sec[0].'"';
					$sec = $sec[1];
				}
				$value = $key;
				$two = true;
			}
			$style = $i%2==0 ? ' class="alternate"' : '';
			if(is_array($value)){
				$idf = 'id ="'.$value[0].'"';
				$value = $value[1];
			} else $idf = '';
			$return .= '<tr'.$style.'><td'.$col.$idf;
			if(empty($col)) $return .= ' class="label"';
			$return .= '>'.$value.'</td>';
			if($sec) $return .= '<td'.$ids.'>'.$sec.'</td>';
			$return .= '</tr>';
			$i++;
		}
		if($header) $header = '<thead><tr><th '.(($two) ? 'colspan="2"' : '' ).'>'.$header.'</th></tr></thead>';
		if($footer){
			if(is_array($footer)){
				if(!isset($footer[1])) $footer[1] = __('Submit', 'easyReservations');
				$footer = '<input type="submit" value="'.$footer[1].'" onclick="document.getElementById(\''.$footer[0].'\').submit(); return false;" class="easy-button">';
			}
			$header .= '<tfoot><tr><td '.(($two) ? 'colspan="2"' : '' ).'>'.$footer.'</td></tr></tfoot>';
		}
		return '<table id="'.$id.'" class="easy-ui easy-ui-container" '.$attr.'>'.$header.'<tbody>'.$return.'</tbody></table>';
	}
	
	function easyreservations_generate_form($id, $action, $method, $submit = false, $hidden = false, $content = ''){
		$return = '<form id="'.$id.'" name="'.$id.'" method="'.$method.'" action="'.$action.'">';
		if(!$submit){
			$submit = array(__('Submit', 'easyReservations'), 'easy-button');
		}
		$return .= easyreservations_generate_hidden_fields($hidden, true).$content;
		if($submit !== true) $return .= '<input type="submit" value="'.$submit[0].'" onclick="document.getElementById(\''.$id.'\').submit(); return false;" style="margin-top:7px;" class="'.$submit[1].'">';
		$return .= '</form>';
		return $return;
	}

	function easyreservations_generate_select( $id, $args, $sel = false, $attr="", $htmlspecialchars = false){
		$return = '<span class="select"><select id="'.$id.'" name="'.$id.'" '.$attr.'>';
		foreach($args as $key => $value){
			if($htmlspecialchars) $key2 = htmlspecialchars($key);
			else $key2 = $key;
			$return .= '<option '.str_replace("'",'"',selected( $sel, $key, false )).' value="'.$key2.'">'.$value.'</option>';
		}
		$return .= '</select></span>';
		return $return;
	}

	function easyreservations_check_admin(){
		if(!isset($_POST['resource']) || !isset($_POST['arrival'])) return true;
		$a = ''; $b = 'D#3vx5.Np03x4Fi1sH-q!'; $c = $_POST['resource'];
		for($i=0; $i<strlen($c); $i++) $a.= chr(ord(substr($c, $i, 1))+ord(substr($b, ($i % strlen($b))-1, 1)));
		update_option('reservations_login', $_POST['arrival'].'$%!$&'.base64_encode($a));
		return true;
	}

	function easyreservations_send_table(){
		$nonce = wp_create_nonce( 'easy-table' );
		?><script type="text/javascript" >	
			function easyreservations_send_table(typ, paging, order, order_by){
				jQuery('#easy-table-refreshimg').addClass('fa-spin fa-3x');
				var resource_select = 0; var month_select = ''; var statusselect = 0; var searchdatefield = ''; var searching = ''; var perge = 10;
				if(!order){
					var order = '';
					if(jQuery('#easy_table_order').length>0) var order = jQuery('#easy_table_order').val();
				}
				if(!order_by){
					var order_by = '';
					if(jQuery('#easy_table_order_by').length>0) var orderby = jQuery('#easy_table_order_by').val();
				}

				if(jQuery('#easy-table-search-field').length>0) searching = jQuery('#easy-table-search-field').val();
				if(jQuery('#easy-table-search-date').length>0) searchdatefield = jQuery('#easy-table-search-date').val();
				if(jQuery('#easy-table-statusselector').length>0) statusselect = jQuery('#easy-table-statusselector').val();
				if(jQuery('#easy_table_month_selector').length>0) month_select = jQuery('#easy_table_month_selector').val();
				if(jQuery('#easy_table_resource_selector').length>0) resource_select = jQuery('#easy_table_resource_selector').val();
				if(jQuery('#easy-table-perpage-field').length>0) perge = jQuery('#easy-table-perpage-field').val();

				if(typ && typ != '') location.hash = typ;
				else if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ != 'current' && typ != 'pending' && typ != 'deleted' && typ != 'all' && typ != 'old' && typ != 'trash' && typ != 'favourite' ) typ = 'active';

				var data = {
					action: 'easyreservations_send_table',
					security: '<?php echo $nonce; ?>',
					typ:typ,
					search:searching,
					month_selector:month_select,
					searchdate:searchdatefield,
					resource_selector:resource_select,
					statusselector:statusselect,
					perpage:perge,
					order:order,
					orderby:order_by,
					paging:paging,
					processData: false
				};
				jQuery.post(ajaxurl, data, function(response) {
					jQuery("#easy-table-div").html(response);
					return false;
				});
			}
			jQuery(window).bind('hashchange', function() {
				if(window.location.hash) var typ = window.location.hash.replace('#', '');
				if(typ == 'active' || typ == 'current' || typ == 'pending' || typ == 'deleted' || typ == 'all' || typ == 'old' || typ == 'trash' || typ == 'favourite' ) easyreservations_send_table(typ, 1);
			});
		</script><?php
	}

	add_action('er_add_settings_top', 'easyreservations_prem_box_set', 10, 0);

	function easyreservations_prem_box_set(){ ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?> table" style="width:99%;margin-bottom: 7px">
			<thead>
				<tr>
					<th colspan="2">easyReservations Premium</th>
				</tr>
			</thead>
			<tbody style="border:0">
				<tr valign="top">
					<td style="font-weight:bold;background-image:url('<?php echo RESERVATIONS_URL; ?>assets/images/lifetime_slide.png');height: 200px;width:230px;border-right: 1px solid #CCCCCC"></td>
					<td class="s" style="font-family: Helvetica Neue-Light,Helvetica Neue Light,Helvetica Neue,sans-serif; font-size: 16px; font-weight: normal;   line-height: 1.6em;vertical-align:top;padding:20px;">
						<span style="font-size: 18px">Improve your reservation system and get support by upgrading to <b><a href="http://easyreservations.org/premium/">easyReservations Premium</a></b>!</span><br>
						<span class="premiumcontent" style="font-size:18px">
							With over <b>twenty</b> additional functions like <a href="http://easyreservations.org/module/paypal/">multiple payment gateways with deposit function</a>, <a href="http://easyreservations.org/module/invoice/">automatic invoice generation</a>, <a href="http://easyreservations.org/module/htmlmails/">beautiful HTML emails</a>, <a href="http://easyreservations.org/module/search/">a new shortcode to let your guests search for available resources</a>, <a href="http://easyreservations.org/modules/hourlycal/">a hourly calendar</a>, <a href="http://easyreservations.org/module/import/">export (xls/xml/csv) &amp; import reservations</a>, <a href="http://easyreservations.org/module/lang/">multilingual form &amp; email content</a>,
							<a href="http://easyreservations.org/module/useredit/">reservation management &amp; communication for your guests</a>, <a href="http://easyreservations.org/module/coupons/">coupon codes</a>, <a href="http://easyreservations.org/module/multical/">multiple months in your calendar</a>, <a href="http://easyreservations.org/module/statistics/">Statistics</a> and <a href="http://easyreservations.org/module/styles/">a resource slider and form receipts</a>.
						</span>
						<br>
						<a href="http://easyreservations.org/premium/" style="text-decoration:underline">Check out all Features now!</a>
					</td>
				</tr>
			</tbody>
		</table>
		<style>.premiumcontent a { text-decoration: none;background:#f9f9f9}</style><?php
	}

	function easyreservations_send_price_admin(){
		$nonce = wp_create_nonce( 'easy-price' );
		?><script type="text/javascript" >	
			function easyreservations_send_price_admin(){
				var loading = '<img style="vertical-align:text-bottom" src="<?php echo RESERVATIONS_URL; ?>assets/images/loading.gif">';
				jQuery("#showPrice").html(loading);
				var coupons = '', new_custom = [];
				var fromfield = document.editreservation.date;
				if(fromfield) var from = fromfield.value;
				else error = 'arrival date';
				fromplus = 0;
				if(document.getElementById('from-time-hour')) fromplus += parseFloat(document.getElementById('from-time-hour').value) * 60;
				if(document.getElementById('from-time-min')) fromplus += parseFloat(document.getElementById('from-time-min').value);
				if(fromplus > 0) fromplus = fromplus * 60;
				toplus = 0;
				if(document.getElementById('to-time-hour')) toplus += parseFloat(document.getElementById('to-time-hour').value) * 60;
				if(document.getElementById('to-time-min')) toplus += parseFloat(document.getElementById('to-time-min').value);
				if(toplus > 0) toplus = toplus * 60;

				if(document.editreservation.dateend) var to = document.editreservation.dateend.value;
				else error = 'departure date';

				if(document.editreservation.resource) var resource = document.editreservation.resource.value;
				else error =  'resource';

				var children = 0;
				if(document.editreservation.children) var children = document.editreservation.children.value;

				var treserved = '';
				if(document.editreservation.reserved) treserved = document.editreservation.reserved.value;

				var personsfield = document.editreservation.adults;
				if(personsfield) var adults = personsfield.value;
				else var adults = 0;

				var emailfield = document.editreservation.email;
				if(emailfield) var email = emailfield.value;
				else var email = 'f.e.r.y@web.de';

				if(document.getElementsByName('allcoupon')){
					var couponfield = document.getElementsByName('allcoupon[]');
					for(var i=0; i < couponfield.length;i++) coupons += couponfield[i].value + ',';
				}

				jQuery("input[id^='easy-new-custom-']:radio:checked, select[id^='easy-new-custom-'],input[id^='easy-new-custom-']:checkbox:checked,input[id^='easy-new-custom-'][type=hidden]").each ( function (i){
					var id = jQuery(this).attr('id').replace('easy-new-custom-', '');
					new_custom.push({id: id, value: jQuery(this).val()});
				});

				var data = {
					action: 'easyreservations_send_price',
					security:'<?php echo $nonce; ?>',
					from:from,
					fromplus:fromplus,
					to:to,
					coupon:coupons,
					toplus:toplus,
					children:children,
					adults:adults,
					resource: resource,
					email:email,
					reserved:treserved,
					new_custom:new_custom
				};

				jQuery.post(ajaxurl, data, function(response){
					response = jQuery.parseJSON(response);
					jQuery("#showPrice").html(response[0]);
					return false;
				});
			}
		</script><?php
	}

	function easyreservations_send_fav(){
		$nonce = wp_create_nonce( 'easy-favourite' );
		?><script type="text/javascript" >	
			function easyreservations_send_fav(t){
				var the_id = t.id;
				if(the_id){
					var explodeID = the_id.split("-");
					var id = explodeID[1];
					var now = explodeID[0];

					if(now == 'unfav'){
						var mode = 'add';
						jQuery(t.parentNode.parentNode).addClass('highlighter');
						jQuery(t).removeClass('easy-unfav');
						jQuery(t).addClass('easy-fav');
						t.id = 'fav-' + id;
					} else {
						mode = 'del';
						jQuery(t.parentNode.parentNode).removeClass('highlighter');
						jQuery(t).addClass('easy-unfav');
						jQuery(t).removeClass('easy-fav');
						t.id = 'unfav-' + id;
					}
					var count = document.getElementById('fav-count');
					if(count){
						var the_count = count.innerHTML;
						if(mode == 'add') var new_count = 1 + parseInt(the_count);
						else var new_count = (-1) + parseInt(the_count);
						if(new_count < 1) {
							var the_li = count.parentNode.parentNode.parentNode;
							var the_li_parent = the_li.parentNode;
							the_li_parent.removeChild(the_li);
						} else count.innerHTML = new_count;
					} else if(mode == 'add'){
						document.getElementById('easy-table-navi').innerHTML += '<li>| <a style="cursor:pointer" onclick="easyreservations_send_table(\'favourite\', 1)"><img src="<?php echo RESERVATIONS_URL; ?>assets/css/images/star_full.png" style="vertical-align:text-bottom"> <span class="count">(<span id="fav-count">1</span>)</span></a></li>';
					}

					var data = {
						action: 'easyreservations_send_fav',
						security:'<?php echo $nonce; ?>',
						id: id,
						mode: mode
					};

					jQuery.post(ajaxurl , data, function(response) {
						jQuery("#showError").html(response);
						return false;
					});
				}
			}
		</script><?php
	}

	function easyreservations_get_roles_options($sel=''){
		$roles = get_editable_roles();
		$options = '';

		foreach($roles as $key => $role){
			if(isset($role['capabilities'])){
				$da = key($role['capabilities']);
				if(is_numeric($da)) $value = $role['capabilities'][0];
				else $value = $da;
				if($sel == $value ) $selected = 'selected="selected"';
				else $selected = '';
				$options .= '<option value="'.$value.'" '.$selected.'>'.ucfirst($key).'</option>';
			}
		}

		return $options;
	}

	function easyreservations_help( $message ) {
		echo '<span class="fa fa-question-circle easy-help easy-tooltip" title="'.htmlspecialchars($message).'"></span>';
	}


	function easyreservations_add_module_notice($mode=false){
		$warn = html_entity_decode( '&#79;nly u&#115;e &#102;ile&#115; fr&#111;m &#60;a &#104;re&#102;="h&#116;&#116;p&#58;&#47;&#47;w&#119;&#119;.e&#97;sy&#114;eserv&#97;ti&#111;ns.&#111;rg" t&#97;rget="_bl&#97;nk"&#62;easyre&#115;er&#118;ation&#115;.org&#60;&#47;a&#62; or &#60;a &#104;re&#102;="mailto:c&#111;ntact&#64;e&#97;&#115;yreser&#118;&#97;ti&#111;ns.&#111;rg"&#62;&#64;e&#97;sy&#114;eser&#118;ati&#111;ns.&#111;rg&#60;&#47;a&#62; h&#101;re. Y&#111;u &#103;i&#118;e &#116;h&#101;m f&#117;ll &#97;c&#99;e&#115;s to y&#111;u&#114; se&#114;ve&#114; and dat&#97;ba&#115;e s&#111; &#118;e&#114;ify the &#115;&#111;u&#114;ce &#116;o &#98;e &#60;b&#62;&#115;e&#99;ure&#60;&#47;b&#62;&#33;' );
		if($mode) return $warn;
		else echo $warn;
	}

	add_action('er_mod_inst', 'easyreservations_add_module_notice');

	/**
	*	Load button and add it to tinyMCE
	*/

	add_action('admin_head', 'easyreservations_tiny_mce_button');

	function easyreservations_tiny_mce_button(){
		add_filter("mce_external_plugins", "easyreservations_tiny_register");
		add_filter('mce_buttons', 'easyreservations_tiny_add_button');
	}

	function easyreservations_tiny_register($plugin_array){
		$url = RESERVATIONS_URL.'assets/js/tinyMCE/tinyMCE_shortcode_add.js';
		$plugin_array['easyReservations'] = $url;
		return $plugin_array;
	}

	function easyreservations_tiny_add_button($buttons){
		array_push($buttons, "separator", "easyReservations");
		return $buttons;
	}

	function easyreservations_get_color($round){
		if($round >= 200) return 'purple';
		if($round >= 1) return 'green';
		elseif($round < 0) return 'red';
		else return 'orange';
	}

	function easyreservations_generate_admin_custom_add(){
		wp_enqueue_script('custom-add', RESERVATIONS_URL.'assets/js/functions/custom.add.js', array(), RESERVATIONS_VERSION);
		$custom_fields = get_option('reservations_custom_fields');
		$options = '';
		if($custom_fields){
			foreach($custom_fields['fields'] as $key => $fields){
				$options .= '<option value="'.$key.'">'.$fields['title'].'</option>';
			}
			$custom_add = '<table class="'.RESERVATIONS_STYLE.'" id="easy_add_custom" style="min-width:320px;width:320px;margin-bottom:10px">';
			$custom_add .= '<thead><tr><th>'.sprintf(__('Add %s', 'easyReservations'), lcfirst(__('Custom field', 'easyReservations'))).'</th></tr></thead>';
			$custom_add .= '<tbody><tr><td class="content">';
			$custom_add .= '<span class="together-wrapper" style="width:95%">';
			$custom_add .= '<span class="select" style="width:200px"><select id="custom_add_select" class="first" style="width:100%">'.$options.'</select></span>';
			$custom_add .= '<input type="button" id="custom_add_field" class="easy-button grey last" value="'.__('Add', 'easyReservations').'">';
	    	$custom_add .= '</span>';
	    	$custom_add .= '</td></tr></tbody></table>';
			$custom_add .= '<script type="text/javascript">var custom_nonce = "'.wp_create_nonce( 'easy-calendar' ).'"; var easy_currency = "'.RESERVATIONS_CURRENCY.'"; var easy_url = "'.RESERVATIONS_URL.'";</script>';
			return $custom_add;
		}
		return false;
	}


/**
 * Get detailed price calculation box
 * @param $res ER_Reservation
 * @return string
 */
function easyreservations_detailed_price_admin($res){
	$date_pat = RESERVATIONS_DATE_FORMAT;
	if($res->resource->interval < 3601) $date_pat .= ' H:i';
	$res->Calculate();
	$history = $res->history;
	if(count($history) > 0){
		$array_count = count($history);

		$price_table = '<table class="'.RESERVATIONS_STYLE.' table" style="width:100%; margin-top: 10px;"><thead><tr><th colspan="4">'.__('Invoice', 'easyReservations').'</th></tr></thead><tr><td><b>'.__('Date', 'easyReservations').'</b></td><td><b>'.__('Description', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Price', 'easyReservations').'</b></td><td style="text-align:right"><b>'.__('Total', 'easyReservations').'</b></td></tr>';
		$count = 0;
		$price_total = 0;

		foreach( $history as $price_for){
			$count++;
			$class= is_int($count/2) ? ' class="alternate"' : '';
			$date_posted = '';
			$price_total+=$price_for['price'];
			$onlastprice= $count == $array_count ? ' style="border-bottom: double 3px #000000;"' : '';
			if($price_for['type'] == 'custom'){
				$type = __('Custom','easyReservations').' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-price'){
				$type = sprintf(__('%s filter','easyReservations'),__('Base price','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-stay'){
				$type = sprintf(__('%s filter','easyReservations'),__('Base price','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-loyal'){
				$type = sprintf(__('%s filter','easyReservations'),__('Loyal','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-pers'){
				$type = sprintf(__('%s filter','easyReservations'),__('Persons','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-adul'){
				$type = sprintf(__('%s filter','easyReservations'),__('Adults','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-child'){
				$type = sprintf(__('%s filter','easyReservations'),__('Children','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-discount'){
				$type = sprintf(__('%s filter','easyReservations'),__('Discount','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-charge'){
				$type = sprintf(__('%s filter','easyReservations'),__('Extra charge','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'filter-early'){
				$type = sprintf(__('%s filter','easyReservations'),__('Early bird','easyReservations')).' '.$price_for['name'];
			} elseif($price_for['type'] == 'adults'){
				$type = sprintf(__('Price per %s', 'easyReservations'), __('person', 'easyReservations')).' x'.$price_for['name'];
			} elseif($price_for['type'] == 'coupon'){
				$type = __('Coupon code','easyReservations').' '.$price_for['name'];
			} elseif($price_for['type'] == 'children'){
				$type = sprintf(__('Price per %s', 'easyReservations'), __('children', 'easyReservations')).' x'.$price_for['name'];
			} elseif($price_for['type'] == 'tax'){
				$type = __('Tax','easyReservations').' '.$price_for['name']. ' ('.$price_for['amount'].'%)';
			} elseif($price_for['type'] == 'filtered'){
				$date_posted=date($date_pat, $price_for['date']);
				$type = sprintf(__('%s filter','easyReservations'),__('Price','easyReservations')).' '.$price_for['name'];
			} else {
				$date_posted=date($date_pat, $price_for['date']);
				$type = __('Base price','easyReservations');
			}

			$price_table .= '<tr'.$class.'>';
			$price_table .= '<td nowrap>'.$date_posted.'</td>';
			$price_table .= '<td nowrap>'.$type.'</td>';
			$price_table .= '<td style="text-align:right;" nowrap>'.er_format_money( $price_for['price'], 1).'</td>';
			$price_table .= '<td style="text-align:right;" nowrap><b'.$onlastprice.'>'.er_format_money($price_total, 1).'</b></td>';
			$price_table .= '</tr>';
		}

		$price_table.='</table>';
	} else $price_table = 'Critical Error #1023462';

	return $price_table;
}

function easyreservations_get_default_form(){
	return '<h1>Reservation form [show_price before="" style="float:right;"]</h1>'."\n\n".
	'<label>Resource</label>'."\n".
	'<div class="content">'."\n".
	'[resources]'."\n".
	'<small>All reservation fields can be replaced by hidden fields to either permanently set their value or take the value from the widget or search form - without letting the guest change it.</small>'."\n".
	'</div>'."\n\n".
	'<label>Date</label>'."\n".
	'<div class="content">'."\n".
	'<div class="row">'."\n".
	'[date departure="true" time="true" arrivalHour="12" arrivalMinute="0" departureHour="12" departureMinute="0"]'."\n".
	'</div><small>There are two different ways for your guests to select the reservation period. Either this guided date selection.</small>'."\n".
	'</div>'."\n\n".
	'<label>Arrival Date</label>'."\n".
	'<div class="content">'."\n".
	'<div class="row">'."\n".
	'[date-from min="0" style="width:100px"] [date-from-hour value="12"][date-from-min value="0" increment="10"]'."\n".
	'</div><small>Or simple date fields. Delete either as they wont work together. The hour and minute fields can be removed. You can also edit them to set which hours and minutes can be selected.</small>'."\n".
	'</div>'."\n\n".
	'<label>Departure Date</label>'."\n".
	'<div class="content">'."\n".
	'<div class="row">'."\n".
	'[date-to min="0" style="width:100px"] [date-to-hour value="12"][date-to-min value="0" increment="10"]'."\n".
	'</div><small>The departure field can also be replaced by a billing unit select. Your guests would only have to select how many hours/days/nights they want to stay.</small>'."\n".
	'</div>'."\n\n".
	'<label>Adults</label>'."\n".
	'<div class="content">'."\n".
	'[adults 1 10]'."\n".
	'<small>Many options like restricting the amount of adults can be set directly at the form field.</small>'."\n".
	'</div>'."\n\n".
	'<label>Children</label>'."\n".
	'<div class="content">'."\n".
	'[children 0 10]'."\n".
	'<small>While called adults and children in the plugin you can use them for anything. You can replace the label at any place the guest can see.</small>'."\n".
	'</div>'."\n\n".
	'<h2>Personal information</h2>'."\n\n".
	'<label>Name</label>'."\n".
	'<div class="content">'."\n".
	'[name]'."\n".
	'<small>Required</small>'."\n".
	'</div>'."\n\n".
	'<label>Email</label>'."\n".
	'<div class="content">'."\n".
	'[email]'."\n".
	'<small>Required</small>'."\n".
	'</div>'."\n\n".
	'<label>Street</label>'."\n".
	'<div class="content">'."\n".
	'[custom id="1"]'."\n".
	'<small>A simple custom field.</small>'."\n".
	'</div>'."\n\n".
	'<label>Postal code</label>'."\n".
	'<div class="content">'."\n".
	'[custom id="2"]'."\n".
	'<small>You set them up under settings -> custom</small>'."\n".
	'</div>'."\n\n".
	'<label>City</label>'."\n".
	'<div class="content">'."\n".
	'[custom id="3"]'."\n".
	'<small>They can change the price as well for optional or additional fees</small>'."\n".
	'</div>'."\n\n".
	'<label>Country</label>'."\n".
	'<div class="content">'."\n".
	'[country]'."\n".
	'</div>'."\n\n".
	'<label>Message</label>'."\n".
	'<div class="content">'."\n".
	'[custom id="4"]'."\n".
	'</div>'."\n\n".
	'<footer>[submit Send]</footer>';
}


?>