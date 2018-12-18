<?php

function easyreservations_main_page(){
	wp_enqueue_style( 'datestyle');
	$main_options = get_option("reservations_main_options");
	$all_custom_fields = get_option('reservations_custom_fields');
	$show = $main_options['show'];
	$overview_options = $main_options['overview'];
	global $wpdb;

	if(isset($_GET['more'])) $moreget=$_GET['more'];
	else $moreget = 0;
	if(isset($_GET['perpage'])) update_option("reservations_on_page", intval($_GET['perpage']));
	if(isset($_GET['sendmail'])) $sendmail=$_GET['sendmail'];
	if(isset($_GET['approve'])) $approve=$_GET['approve'];
	if(isset($_GET['view']))  $view=$_GET['view'];
	if(isset($_GET['delete'])) $delete=$_GET['delete'];
	if(isset($_GET['edit'])) $edit=$_GET['edit'];
	if(isset($_GET['add'])) $add=$_GET['add'];
	if(isset($_POST['resource-saver-from'])) $moreget+=round(($_POST['resource-saver-from']-strtotime(date("d.m.Y", current_time( 'timestamp' ))))/86400);
	if(!isset($edit) && !isset($view) && !isset($add) && !isset($approve) && !isset($sendmail)  && !isset($delete)) $nonepage = 0;

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + BULK ACTIONS (trash,delete,undo trash) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
	do_action('easy_dashboard_header_start');
	if(isset($_GET['bulk']) && check_admin_referer( 'easy-main-bulk' )){ // GET Bulk Actions
		if(isset($_GET['bulkArr'])) {
			$amount=0;
			$array = $_GET['bulkArr'];
			if($_GET['bulk']==1){ //  If Move to Trash
				foreach($_GET['bulkArr'] as $id){
					$amount++;
					$the_reservation = new ER_Reservation($id, array('status' => 'del', 'resource' => false));
					$the_reservation->editReservation(array('status'), false);
				}
				if(is_array($array)) $linkundo = implode("&bulkArr[]=", $array); else $linkundo = $array;

				if($amount == 1) $amount_string=__('Reservation', 'easyReservations');
				else $amount_string=$amount.' '.__('Reservations', 'easyReservations');

				ER()->messages()->add_success(sprintf(__('%s moved to trash', 'easyReservations'), $amount_string).'. <a href="'.wp_nonce_url('admin.php?page=reservations&bulkArr[]='.$linkundo.'&bulk=2', 'easy-main-bulk', '_wpnonce').'">'.__('Undo', 'easyReservations').'</a>');
			} elseif($_GET['bulk']=="2"){ //  If Undo Trashing
				if(count($array)  > "1" ) {
					foreach($array as $id){
						$amount++;
						$the_reservation = new ER_Reservation($id, array('status' => ''));
						$the_reservation->editReservation(array('status'), false);
					}
				} else {
					$amount++;
					$the_reservation = new ER_Reservation($array[0], array('status' => '', 'resource' => false));
					$the_reservation->editReservation(array('status'), false);
				}

				if($amount == 1) $amount_string = __('Reservation', 'easyReservations');
				else $amount_string = $amount.' '.__('Reservations', 'easyReservations');

				ER()->messages()->add_success(sprintf(__('%s restored from trash', 'easyReservations'), $amount_string));
			} elseif($_GET['bulk']=="3"){ //  If Delete Permanently
				if(count($array)  > "1" ) {
					foreach($array as $id){
						$amount++;
						$the_reservation = new ER_Reservation($id);
						$the_reservation->deleteReservation();
					}
				} else {
					$amount++;
					$the_reservation = new ER_Reservation($array[0]);
					$the_reservation->deleteReservation();
				}
				if($amount==1) $amount_string=__('Reservation', 'easyReservations'); else $amount_string=$amount.' '.__('Reservations', 'easyReservations');
				ER()->messages()->add_success(sprintf(__('%s deleted permanently', 'easyReservations'), $amount_string));
			}
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + DELETE CUSTOM FIELD + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_GET['delete-custom']) && isset($edit)){
		$res = new ER_Reservation($edit);
		try {
			$return = $res->delete_single_meta(sanitize_text_field($_GET['custom-type']), $_GET['delete-custom']);
			if(!$return) ER()->messages()->add_success(sprintf(__('%s deleted', 'easyReservations'), __('Custom field', 'easyReservations')));
			else ER()->messages()->add_error(__('Custom field is not existing', 'easyReservations'));
		} catch(easyException $e){
			ER()->messages()->add_error( $e->getMessage());
		}
	}

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_POST['editthereservation']) && check_admin_referer( 'easy-main-edit', 'easy-main-edit' )){
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 12*60;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int) $_POST['to-time-min'] ;else $to_hour = 12*60;

		$custom_fields = array();
		foreach($_POST as $key => $value){
			if(strpos($key, 'easy-new-custom') !== false ){
				$temp_id = str_replace('easy-new-custom-', '', $key);
				if(isset($all_custom_fields['fields'][$temp_id])){
					$custom_fields[] = array( 'id' => $temp_id, 'value' => $value );
				}
			}
		}

		$res = new ER_Reservation($edit, false, true);
		try {
			$res->name = stripslashes($_POST["name"]);
			$res->email = $_POST["email"];
			$res->set_resource(ER()->resources()->get(intval($_POST["resource"])));
			if(isset($_POST["adults"])) $res->adults = (int) $_POST["adults"];
			else $res->adults = 1;
			if(isset($_POST["children"])) $res->children = (int) $_POST["children"];
			else $res->children = 1;
			$res->country = $_POST["country"];
			$res->status = $_POST["reservation_status"];
			$res->user = (int) $_POST["edit_user"];
			if(isset($_POST["resource_space"])) $res->space = $_POST["resource_space"];
			else $res->space = 0;
			$reservation_date = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["reservation_date"].' 00:00:00');
			$arrival = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["date"].' 00:00:00', $from_hour*60);
			$departure = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["dateend"].' 00:00:00', $to_hour*60);
			if($arrival && $departure && $reservation_date instanceof DateTime){
				$res->reserved = $reservation_date->getTimestamp();
				$res->arrival = $arrival;
				$res->departure = $departure;
				$res->set_temporary_meta('custom', $custom_fields);
				$res = apply_filters('easy_reservation_edit_admin', $res);

	      		$set_price = $res->get_price();
				if(isset($_POST["priceset"]) && !empty($_POST["priceset"]) && is_numeric($_POST["priceset"])) $set_price = er_check_money( $_POST["priceset"]);
				if(isset($_POST["EDITwaspaid"]) &&  !empty($_POST["EDITwaspaid"])  && is_numeric($_POST["EDITwaspaid"])) $paid = er_check_money( $_POST["EDITwaspaid"]);
				else $paid = false;
				$res->paid = $paid ? $paid : 0;
				$res->price = $set_price ? $set_price : null;

				if(isset($_POST["sendthemail"])) $mail = 'reservations_email_to_user_admin_edited'; else $mail = '';
				if($_POST['copy'] == 'no'){
					$return = $res->editReservation(array('all'), true, $mail, $res->email);
					if(!$return){
						ER()->messages()->add_success( sprintf(__('Reservation %s', 'easyReservations'), __('updated', 'easyReservations')).'</p><p><a href="admin.php?page=reservations">&#8592; Back to Dashboard</a>');
					} else {
						foreach($return as $error){
							ER()->messages()->add_error($error);
						}
					}
				} else {
					$id = $res->id;
					$res->id = 0;
					$return = $res->addReservation();
					if(!$return){
						ER()->messages()->add_success( sprintf(__('Reservation #%1$d copied as #%2$d', 'easyReservations'), $id, $res->id ));
						?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations&edit=<?php echo $res->id; ?>"><?php
					}
				}
			}
		} catch(Exception $e){
			ER()->messages()->add_error( $e->getMessage());
		}
	} elseif(isset($_GET['recalculate'])){
		$res = new ER_Reservation(intval($_GET['edit']), false, true);
		try {

			$new_price = $res->Calculate();
			update_reservation_meta($res->id, 'history', $res->history);
			$res->price = $new_price;
			$return = $res->editReservation(array('price'), false);
			if(!$return){
				ER()->messages()->add_success( sprintf(__('Reservation %s', 'easyReservations'), __('recalculated', 'easyReservations')).'</p><p><a href="admin.php?page=reservations">&#8592; Back to Dashboard</a>');
			} else {
				foreach($return as $error){
					ER()->messages()->add_error($error);
				}
			}

		} catch(Exception $e){
			ER()->messages()->add_error( $e->getMessage());
		}
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION BY ADMIN + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

	if(isset($_POST['addreservation']) && check_admin_referer( 'easy-main-add', 'easy-main-add' )){
		if(isset($_POST['from-time-hour'])) $from_hour = ((int) $_POST['from-time-hour']*60)+(int) $_POST['from-time-min']; else $from_hour = 12*60;
		if(isset($_POST['to-time-hour']))  $to_hour = ((int) $_POST['to-time-hour']*60)+(int)$_POST['to-time-min'];else $to_hour = 12*60;

		$customs = array();
		foreach($_POST as $key => $value){
			if(strpos($key, 'easy-new-custom') !== false ){
				$temp_id = str_replace('easy-new-custom-', '', $key);
				if(isset($all_custom_fields['fields'][$temp_id])){
					$customs[] = array( 'id' => $temp_id, 'value' => $value);
				}
			}
		}

		if(isset($_POST["resource_space"])) $resresourcenumber = (int) $_POST["resource_space"];
		else $resresourcenumber = 0;
		if(isset($_POST["adults"])) $resadults = (int) $_POST["adults"];
		else $resadults = 1;
		if(isset($_POST["children"])) $reschildren = (int) $_POST["children"];
		else $reschildren = 1;

		if(empty($_POST["reservation_date"])){
			$_POST["reservation_date"] = date(RESERVATIONS_DATE_FORMAT, current_time( 'timestamp' ));
		}

		$arrival = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["date"].' 00:00:00', $from_hour*60);
		$departure = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["dateend"].' 00:00:00', $to_hour*60);
		$reservation_date = ER_DateTime::createFromFormat(RESERVATIONS_DATE_FORMAT.' H:i:s', $_POST["reservation_date"].' 00:00:00');
		if(!$reservation_date instanceof DateTime) $reservation_date = current_time( 'timestamp' );
		if($arrival && $departure){

			$res = new ER_Reservation(false, array(
				'name' => $_POST["name"],
				'email' => $_POST["email"],
				'arrival' => $arrival,
				'departure' => $departure,
				'resource' => (int) $_POST["resource"],
				'space' => $resresourcenumber,
				'country' => $_POST["country"],
				'adults' => $resadults,
				'children' => $reschildren,
				'reserved' => $reservation_date->getTimestamp(),
				'status' => $_POST["reservation_status"],
				'user' => $_POST["edit_user"]
			), true);
			$res->set_temporary_meta('custom', $customs);

			try {
				if(isset($_POST["fixReservation"]) && $_POST["fixReservation"] == "on"){
					if(!empty($_POST["priceAmount"]))$thePriceAdd = er_check_money( $_POST["priceAmount"]);
					else {
						$price = $res->Calculate();
						$thePriceAdd = er_check_money($price);
					}
					if($thePriceAdd) $res->price = $thePriceAdd;
					else ER()->messages()->add_error( __( 'Insert correct money format', 'easyReservations' ) );
				}

				if(!isset($_POST["paidAmount"]) || empty($_POST["paidAmount"]) || $_POST["paidAmount"] < 0 || $_POST["paidAmount"] === null ) $res->paid = 0;
				else $res->paid = er_check_money( $_POST["paidAmount"]);

				$return = $res->addReservation();
				if(!$return){
					ER()->messages()->add_success(sprintf(__('Reservation #%d added', 'easyReservations'), $res->id));
					do_action('easy-add-stream', 'reservation', 'add', '', $res->id);
					?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations&edit=<?php echo $res->id; ?>"><?php
				} else{
					foreach($return as $error){
						ER()->messages()->add_error($error);
					}
				}
			} catch(Exception $e){
				ER()->messages()->add_error( $e->getMessage());
			}
		} else ER()->messages()->add_error( __('Wrong date format', 'easyReservations'));
	}
/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + GET DATA IF A RESERVATION IS CALLED DIRECTLY (view,edit,approve,reject,sendmail) + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */
	$can_change_reservation = true;
	if(isset($approve)  || isset($delete) || isset($view) || isset($edit) || isset($sendmail)) { //Query of View Reject Edit Sendmail and Approve
		if(isset($edit)) $theid = $edit;
		elseif(isset($approve)) $theid = $approve;
		elseif(isset($view)) $theid = $view;
		elseif(isset($sendmail)) $theid = $sendmail;
		elseif(isset($delete)) $theid = $delete;

		$res = new ER_Reservation($theid);
		try {
			$res->checkAvailability(2);
			$res->resourcenumbername = $res->resource->get_space_name($res->space);
			$custom_fields = get_reservation_meta($res->id, 'custom');
			if(isset($approve)  || isset($delete) || isset($view)) $resource_id = $res->resource->ID; // For Overview only show date on view

			if(!empty($res->resource->permission) && !current_user_can($res->resource->permission)) die('You do not have the required permission to access this reservation');
		} catch(easyException $e){
			ER()->messages()->add_error( $e->getMessage());
		}

		$moreget+=ceil(($res->arrival-strtotime(date("d.m.Y", current_time( 'timestamp' )))-259200)/86400);
	}

	if(isset($res)){
		if($res->country == 'ICS'){
		  $can_change_reservation = false;
		}
	}

	if(isset($sendmail) && isset($_POST['thesendmail'])){
		$the_reservation = new ER_Reservation((int) $sendmail, false, true);
		try {
			$the_reservation->sendMail('reservations_email_sendmail', $the_reservation->email);
			ER()->messages()->add_success( __('Email sent successfully', 'easyReservations'));
		} catch(Exception $e){
			ER()->messages()->add_error( $e->getMessage());
		}
	} 
	
	if(isset($_POST['approve']) || isset($_POST['delete'])){
		if(isset($_POST['approve'])){
			$emailformation = 'reservations_email_to_userapp';
			$status = 'yes';
		} else {
			$emailformation = 'reservations_email_to_userdel';
			$status = 'no';
		}

		if(!isset($_POST['sendthemail'])) $emailformation = false;

		$the_reservation = new ER_Reservation($theid, false, true);
		try {
			if(isset($_POST['resource_space'])) $the_reservation->space = $_POST['resource_space'];
			$the_reservation->status = $status;

			if(isset($_POST['hasbeenpayed'])) $the_reservation->paid = $the_reservation->get_price();

			$return = $the_reservation->editReservation(array('status', 'paid', 'space'), true, $emailformation, $the_reservation->email);

			if(!$return){
				if(isset($_GET['approve'])) ER()->messages()->add_success( sprintf(__('Reservation %s', 'easyReservations'), __('approved', 'easyReservations')));
				else ER()->messages()->add_success( sprintf(__('Reservation %s', 'easyReservations'), __('rejected', 'easyReservations')));
				?><meta http-equiv="refresh" content="0; url=admin.php?page=reservations"><?php
			} else {
				if(is_array($return)) ER()->messages()->add_error( $return[0]);
				else ER()->messages()->add_error( $return);
			}
		} catch(Exception $e){
			ER()->messages()->add_error( $e->getMessage());
		}
	}
	do_action('easy_dashboard_header_end'); ?>
<h2 style="display: inline-block;">
	<?php _e('Reservations dashboard', 'easyReservations');?>&nbsp;
	<a class="badge secondary" href="admin.php?page=reservations&add"><?php _e('Add', 'easyReservations');?></a>
</h2>
<?php ER()->messages()->output(); ?>
<script>
	function get_the_select(selected, resourceId){
		var selects = new Array(); <?php
		foreach(ER()->resources()->get() as $resource){
			if($resource->availability_by === 'unit'){
				$select = '<span class="select"><select name="resource_space" id="resource_space" onchange="changer();';
				if($overview_options['overview_autoselect'] == 1){ $select .= 'dofakeClick(2);';  }
				$select .= '">';
					$select.= $resource->get_spaces_options(1);
				$select.= '<option value="0">'.addslashes(__('None', 'easyReservations')).'</option>';
				$select.= '</select></span>'; ?>
				selects[<?php echo $resource->ID; ?>] = new Array('<?php echo $select; ?>');<?php
			}
		} ?>

		if(selects[resourceId]){
			document.getElementById('resource_space_container').innerHTML = selects[resourceId];
			if(selected != 0) document.getElementById('resource_space').selectedIndex = selected-1;
			else document.getElementById('resource_space').selectedIndex = 0;
		} else document.getElementById('resource_space_container').innerHTML = '';
	}
</script>
<?php
if($show['show_overview']==1){ //Hide Overview completly
?>
<script>
	function generateXMLHttpReqObjThree(){
		var resObjektTwo = null;
		try {
			resObjektThree = new ActiveXObject("Microsoft.XMLHTTP");
		} catch(Error){
			try {
				resObjektThree = new ActiveXObject("MSXML2.XMLHTTP");
			} catch(Error){
				try {
					resObjektThree = new XMLHttpRequest();
				} catch(Error){
					alert("AJAX error");
				}
			}
		}
		return resObjektThree;
	}

	function generateAJAXObjektThree(){
		this.generateXMLHttpReqObjThree = generateXMLHttpReqObjThree;
	}

	var xxy = new generateAJAXObjektThree();
	var resObjektThree = xxy.generateXMLHttpReqObjThree();
	var save = 0;
	var countov = 0;
	var the_ov_interval = 86400;

	function easyRes_sendReq_Overview(x,y,daystoshow, interval){
		jQuery('#easyUiTooltip').remove();
		if(interval === undefined) interval = jQuery('#easy-overview-interval').val();
		the_ov_interval = interval;
		var string = '';
		if(x && x != 'no') string += 'more=' + x;
		if(y && y != 'no') string +=  '&dayPicker=' + y;
		var reservationNights = '<?php if(isset($res->times)) echo $res->times; ?>';
		if(reservationNights != '') string += '&reservationNights=' + reservationNights;
		var resource = '<?php if(isset($resource_id)) echo $resource_id; ?>';
		if(resource != '') string += '&resource=' + resource;
		var add = '<?php if(isset($add)) echo '1'; ?>';
		if(add != '') string += '&add=' + add;
		var edit = '<?php if(isset($edit)) echo $edit; ?>';
		if(edit != '') string += '&edit=' + edit;
		var app = '<?php if(isset($approve)) echo $approve; ?>';
		if(app != '') string += '&approve=' + app;
		var id = '<?php if(isset($res->id) && $res->id) echo $res->id; ?>';
		if(id != '') string += '&id=' + id;
		var res_date_from_stamp = '<?php if(isset($res->arrival)) echo $res->arrival.'-'.$res->departure; ?>';
		if(res_date_from_stamp != '') string += '&res_date_from_stamp=' + res_date_from_stamp;
		var nonepage = '<?php if(isset($nonepage)) echo $nonepage; ?>';
		if(nonepage != '') string += '&nonepage=' + nonepage;
		if(daystoshow) string += '&daysshow=' + daystoshow;
		else string += '&daysshow=' + <?php if(isset($overview_options['overview_show_days']) && !empty($overview_options['overview_show_days'])) echo $overview_options['overview_show_days']; else echo 30; ?>;

		if((y != "" || x != "") && save == 0){
			save = 1;
			resObjektThree.open('post', '<?php echo RESERVATIONS_URL; ?>lib/admin/overview.php?rand=<?php echo rand(1,999); ?>', true);
			resObjektThree.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
			resObjektThree.onreadystatechange = handleResponseValidate;
			resObjektThree.send(string + '&interval=' + interval);
			jQuery('#pickForm').html('<span class="fa fa-spinner fa-pulse" style="margin-left:10px;font-size: 20px"></span>');
		}
	}

	function handleResponseValidate(){
		var text="";
		if(resObjektThree.readyState == 4){
			document.getElementById("theOverviewDiv").innerHTML = resObjektThree.responseText;
			jQuery(document).ready(function(){
				createPickers();
			});
			Click = 0;
			var from = document.getElementById('datepicker');
			var to = document.getElementById('datepicker2');
			if(countov != 0 && window.dofakeClick && from && from.value != '<?php if(isset($res->arrival)) echo date(RESERVATIONS_DATE_FORMAT, $res->arrival); ?>' && to && to != '<?php if(isset($res->departure)) echo date(RESERVATIONS_DATE_FORMAT, $res->departure); ?>'){
				dofakeClick(2);
			}
			countov++;
			save = 0;
			jQuery.holdReady(false);
		}
	}

	function createPickers(){
		jQuery("#dayPicker").datepicker({
			changeMonth: true,
			changeYear: true,
			dateFormat: 'dd.mm.yy',
			firstDay: 1,
			defaultDate: +10,
			beforeShow: function(_, inst){
				inst.dpDiv.removeClass('ui-datepicker').addClass('easy-datepicker');
			},
			onSelect: function(){
				easyRes_sendReq_Overview('no', document.getElementById("dayPicker").value, '');
			}
		});

		jQuery.fn.column = function(i) {
			if(i) return jQuery('tr td:not(.ov-days-hours):nth-child('+(i)+'), tr td.ov-days-hours:nth-child('+(i-1)+')', this);
		};

		jQuery(function() {
			jQuery("table#overview td").hover(function() {
				var curCol = jQuery(this).attr("axis") ;
				if(curCol){
					jQuery('#overview').column(curCol).addClass("hover");
				}
			}, function() {
				var curCol = jQuery(this).attr("axis") ;
				if(curCol) jQuery('#overview').column(curCol).removeClass("hover"); 
			});
		});
		easyUiTooltip();
	}

	function formDate(str){
		if(str < 2082585600) str = parseFloat(str) * 1000;
		var date = new Date(str);
		return (( date.getDate() < 10) ? '0'+ date.getDate() : date.getDate()) + '.' +(( parseFloat(date.getMonth()+1) < 10) ? '0'+ parseFloat(date.getMonth()+1) : parseFloat(date.getMonth()+1)) + '.' + (( date.getYear() < 999) ? date.getYear() + 1900 : date.getYear());
	}

	var Click = 0;
	function clickOne(t,d,color,mode){
		deletecActiveRes();
		if(Click == 0 && t){
			var color = "black";
			if(color) color = color;
			document.getElementById("hiddenfieldclick").value = t.id;
      if(!d) d = jQuery(t).attr('date');
      if(mode == 1) jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/'+ color +'_middle.png\') repeat-x"></div>');
			else {
				var reservation = jQuery(t).children('.reservation.real');
				if(reservation.length > 0){
					var background = '';
					reservation.css('background', 'url(<?php echo RESERVATIONS_URL; ?>assets/images/black_cross.png) center top no-repeat, '+reservation.attr('data-background'));
				} else {
					jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/'+ color +'_start.png\') center top no-repeat"></div>');
				}
			}
			<?php if(isset($edit) || isset($add)){ ?>
				document.getElementById('datepicker').value=easyFormatTimestamp(d);
			<?php } elseif(isset($nonepage)){ ?>
				document.getElementById('overview_click_from').value=d;
			<?php } ?>
			if(document.getElementById('from-time-hour') && jQuery('input[name=daybutton]').val() == 'Days'){
				var theDate = easyTimestampToDate(d*1000);
				document.getElementById('from-time-hour').selectedIndex = theDate.getHours();
			}
			if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML = '<a class="fa fa-refresh" style="vertical-align:bottom;cursor:pointer;" onclick="resetSet()"></a>';
			Click = 1;
		}
	}

	function clickTwo(t,d,color,todo){
		if( Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;
			var way = 0;
			if(!t){
				way = 1;
				var last_div = document.getElementById(Last);
				if(last_div) t = last_div.parentNode.lastChild;
				else {
					resetSet();
					return;
				}
			}
			if(!color) color = "black";

			var Celle = t.id;
			var lastDiv = document.getElementById(Last);

			if(lastDiv && Last <= Celle && t.parentNode.id==lastDiv.parentNode.id){
				document.getElementById("hiddenfieldclick2").value=Celle;
				if(way == 0){
					var reservation = jQuery(t).children('.reservation.real');
					if(reservation.length > 0){
						if(Last == Celle){
							reservation.css('background', 'url(<?php echo RESERVATIONS_URL; ?>assets/images/black_cross.png) center top no-repeat, '+reservation.attr('data-background'));
						} else {
							reservation.css('background', 'url(<?php echo RESERVATIONS_URL; ?>assets/images/'+color+'_cross.png) center top no-repeat, #373737');
						}
					} else {
						if(Last == Celle){
							jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/'+ color +'_middle.png\') center top repeat"></div>');
						} else {
							jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/'+ color +'_end.png\') center top no-repeat"></div>');
						}
					}
				}
				else jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/black_middle.png\') repeat-x"></div>');

				jQuery(t).addClass('ov-no-border');
        if(!d) d = jQuery(t).attr('date');
				<?php if(isset($edit) || isset($add)){ ?>
					document.getElementById('datepicker2').value=easyFormatTimestamp(d);
				<?php } elseif(isset($nonepage)){ ?>
					jQuery('#overview_click_to').val(d);
				<?php } ?>
				if(jQuery('#to-time-hour').length > 0 && jQuery('input[name=daybutton]').val() == 'Days'){
          var theDate = easyTimestampToDate(d*1000);
					document.getElementById('to-time-hour').selectedIndex = theDate.getHours();
				}
				var theid= '';
				var work = 1;
				if( Last !== Celle ){
					while(theid != Last){
						t=t.previousSibling;
						if(jQuery(t).is('.do-not-cross')){
							resetSet();
							if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML += "<?php echo addslashes(__('Full', 'easyReservations')); ?>!";
							var field = document.getElementById('datepicker2');
							if(field && field.type == "text" ){
								jQuery('input[name="date"],input[name="dateend"],#resource,#from-time-hour,#to-time-hour,select[name="from-time-min"],select[name="to-time-min"]').css("border-color", "#F20909");
							}
							work = 0;
							break; 
						}
						theid=t.id;
						if(theid && theid != Last){
							var reservation = jQuery(t).children('.reservation.real');
							if(reservation.length > 0){
								jQuery(t).addClass('ov-no-border');
								reservation.css('background', 'url(<?php echo RESERVATIONS_URL; ?>assets/images/black_middle.png) repeat');
							} else {
								jQuery(t).addClass('ov-no-border').html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/black_middle.png\') repeat-x"></div>');
							}
						}
					}
				}
				Click = 2;
				if(work == 1){
					<?php if(isset($add) || isset($edit)) echo "easyreservations_send_price_admin();"; ?>
					if(!todo){ <?php if(isset($nonepage)){ ?>document.overview_click.submit();<?php } ?>}
				}
			}
		}
	}

	function changer(){
		var field = document.getElementById('datepicker2');
		if(field && field.type == "text" ){
			jQuery('input[name="date"],input[name="dateend"],#resource,#from-time-hour,#to-time-hour,select[name="from-time-min"],select[name="to-time-min"],#resource_space:first').css("border-color", "");
		}
		if( Click == 2 ){
			resetSet();
		}
	}

	function fakeClick(from, to, resource, exactly, color){
		var x = parseFloat(document.getElementById("timesx").value);
		var y = parseFloat(document.getElementById("timesy").value);

		if(x && from < y && to > x){
			var mode = 0;
			var days_between = Math.round((from - x) / the_ov_interval)+1;
			if(days_between < 10 && days_between >= 0) days_between = '0' + days_between;
			if(days_between <= 1){
				days_between = '01';
				mode = 1;
			}

			var daysbetween2 = Math.round((to - x) / the_ov_interval) +1;
			if(daysbetween2 < 10) daysbetween2 = '0' + daysbetween2;

			var id = resource + '-' + exactly + '-' + days_between;
			var id2 = resource + '-' + exactly + '-' + daysbetween2;

			clickOne(document.getElementById(id),from,color, mode);
			clickTwo(document.getElementById(id2),to,color);
		}
	}

	function resetSet(){
		var First = document.getElementById("hiddenfieldclick").value;
		var Last = document.getElementById("hiddenfieldclick2").value;

		if(Click == 2 || Last != '' ){
			t=document.getElementById(Last);
			if(t){
				if(jQuery(t).hasClass('do-not-delete')){
					var reservation = jQuery(t).children('.reservation.real');
					reservation.css('background', reservation.attr('abbr'));
				} else {
					jQuery(t).html('');
				}
				jQuery(t).removeClass('ov-no-border');

				var theid= '';
				if(First != Last){
					while(theid != First){
						if(t && t.id){
							theid=t.id;
							if(jQuery(t).hasClass('do-not-delete')){
								var reservation = jQuery(t).children('.reservation.real');
								reservation.css('background', reservation.attr('abbr'));
							} else {
								jQuery(t).html('');
							}
							jQuery(t).removeClass('ov-no-border');
						}
						t=t.previousSibling;
					}

					Click = 0;
					if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML='';
					jQuery("#hiddenfieldclick2,#hiddenfieldclick").val('');
				} else Click = 0;
			} else Click = 0;
		} else if(Click == 1){
			var t = document.getElementById(document.getElementById("hiddenfieldclick").value);
			if(t){
				if(document.getElementById('resetdiv')) document.getElementById('resetdiv').innerHTML='';
			}
			Click = 0;
		}
	}

	function overviewSelectDate(date){
		var table_date_field = document.getElementById("easy-table-search-date");
		if(table_date_field){
			table_date_field.value = date;
			easyreservations_send_table('all', 1);
		}
	}

	function setVals2(resourceid,resourceex){
		<?php if(isset($edit) || isset($add)){ ?>
			var x = document.getElementById("resource");
			var y = document.getElementById("resource_space");
			get_the_select(resourceex, resourceid);
			jQuery('#resource').val(resourceid);
			jQuery('#resource_space').val(resourceex);
		<?php } elseif(isset($nonepage)){ ?>
			document.getElementById("resource").value=resourceid;
			document.getElementById("resource_space").value=resourceex;
		<?php } ?>
	}

	<?php if($overview_options['overview_onmouseover'] == 1){ ?>
	function hoverEffect(t,d, color){
		if(!color) color = 'black';
    if(!d) d = easyFormatTimestamp(jQuery(t).attr('date'));
    if(d == 0) document.getElementById("ov_datefield").innerHTML = "";
    else document.getElementById("ov_datefield").innerHTML = ' (' + d + ')';

		if(Click == 1){
			var Last = document.getElementById("hiddenfieldclick").value;
			var Now = t.id;
			var Lastinfos = Last.split("-");
			var Nowinfos = Now.split("-");
			if(Nowinfos[2] >= Lastinfos[2]){
				var rightid = Lastinfos[0] + '-' + Lastinfos[1] + '-' + Nowinfos[2];
				t = document.getElementById(rightid);
				if(t){
					document.getElementById("hiddenfieldclick2").value = rightid;
					var y = t;
					if(Nowinfos[2] != Lastinfos[2] && !jQuery(t).hasClass('do-not-cross')){
						var reservation = jQuery(t).children('.reservation.real');
						if(reservation.length > 0){
							reservation.css('background', 'url(<?php echo RESERVATIONS_URL; ?>assets/images/'+color+'_cross.png) center top no-repeat, #373737');
						} else {
							jQuery(t).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/black_end.png\') center top no-repeat">');
						}
						jQuery(t).addClass('ov-no-border');
						var x=t;
						var theidx= 0;
						var theidy= 0;
						while(theidx != Last){
							x=x.previousSibling;
							theidx=x.id;

							if(theidx && theidx != Last && !jQuery(x).hasClass('er_overview_cell')  && !jQuery(x).hasClass('do-not-delete')){
								jQuery(x).addClass('ov-no-border');
								jQuery(x).html('<div class="reservation" style="background:url(\'<?php echo RESERVATIONS_URL; ?>assets/images/black_middle.png\') repeat-x">');
							}
							}
					}
					if(y !=  y.parentNode.lastChild){
						while(theidy != y.parentNode.lastChild.id){
							y=y.nextSibling;
							theidy=y.id;
							if(theidy && theidy != y.parentNode.lastChild.id){
								if(jQuery(y).hasClass('do-not-delete')){
									var reservation = jQuery(y).children('.reservation.real');
									reservation.css('background', reservation.attr('abbr'));
								} else if(jQuery(y).hasClass('do-not-cross')){
									break;
								} else {
									jQuery(y).html('');
								}
								jQuery(y).removeClass('ov-no-border');
							}
						}
						if(!jQuery(y.parentNode.lastChild).hasClass('er_overview_cell')){
							jQuery(y.parentNode.lastChild).html('');
							jQuery(y.parentNode.lastChild).removeClass('ov-no-border');
						}
					}
				}
			}
		}
	}
	<?php } ?>
	function deletecActiveRes(){
		var activres = document.getElementsByName('activeres');
		if(activres[0]){
			var ares = document.getElementById(activres[0].id);
			var firstDate = <?php if(isset($res->arrival)) echo $res->arrival; else echo 0; ?>;
			//var idbefor = ares.previousSibling;

			var splitidbefor = ares.id.split("-");

			jQuery('td[name="activeres"]')
				.html('')
				.removeClass('do-not-delete')
				.removeClass('do-not-cross')
				.removeClass('er_overview_cell')
				.attr("onclick", "changer();clickTwo(this,'"+firstDate+"'); clickOne(this,'"+firstDate+"'); setVals2('"+splitidbefor[0]+"','"+splitidbefor[1]+"');")
				.attr("name", '');
		}
	}

	function nextSave(next, i){
		if(!i) i = 0;
		i++;
		if(i < 10){
			if(next && next !== null && next.id) return next;
			else next = next.nextSibling;
		} else return false;
		nextSave(next);
	}
<?php
 if($overview_options['overview_autoselect'] == 1 && (isset($add) || isset($edit))){ ?>
	function dofakeClick(order){
		var from = document.getElementById("datepicker").value;
		var to = document.getElementById("datepicker2").value;
		var now = <?php echo strtotime(date("d.m.Y", current_time( 'timestamp' ))); ?> - (the_ov_interval*3);

		deletecActiveRes();
		var explodeFrom = from.split(".");
		var timestampFrom = parseFloat(Date.UTC(explodeFrom[2],explodeFrom[1]-1,explodeFrom[0]))/1000;
		if (document.getElementById("from-time-hour")!=null) timestampFrom = timestampFrom + parseFloat(document.getElementById("from-time-hour").value) * 3600;

		if(order == 1) easyRes_sendReq_Overview(((timestampFrom-now)/the_ov_interval)-4,'', '', the_ov_interval);

		var explodeTo = to.split(".");
		var timestampTo = parseFloat(Date.UTC(explodeTo[2],explodeTo[1]-1,explodeTo[0])) / 1000;
		if (document.getElementById("to-time-hour")!=null) timestampTo = timestampTo + parseFloat(document.getElementById("to-time-hour").value) * 3600;
		var resource = document.getElementById("resource").value;
		var resource_space = '';
		if(document.getElementById("resource_space")) resource_space = document.getElementById("resource_space").value;

		//alert("from:"+timestampFrom+" | to:"+timestampTo+" | resource:"+resource+" | resource_space:"+resource_space+" | order:"+order+" | from:"+from+" | to:"+to);

		if(from && to && resource && resource_space && from != "" && to != "" && resource != "" && resource_space != "" && (order == 2) && timestampFrom < timestampTo){
			fakeClick(timestampFrom,timestampTo,resource,resource_space,"black");
		}
	}
	<?php } else { ?> function dofakeClick(test){};
	<?php } ?>
</script>
<div id="theOverviewDiv"></div>
<script type="text/javascript">
	jQuery.holdReady(true);<?php if(isset($main_options['overview']['overview_hourly_stand']) && $main_options['overview']['overview_hourly_stand'] == 1){ ?> the_ov_interval = 3600;<?php } ?>
	jQuery(window).on("load", function() {
		<?php if(!$can_change_reservation){ ?>
			jQuery('.easy-admin-content').find('input,select,textarea').attr('disabled', true)
		<?php } ?>
		easyRes_sendReq_Overview('<?php echo $moreget; ?>','no', '',the_ov_interval);
	});
</script><?php
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
																			//START LIST//
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(!isset($approve) && !isset($delete) && !isset($view) && !isset($edit) && !isset($sendmail) && !isset($add)){
	if(!isset($show['show_statistics']) || $show['show_statistics'] == 1) do_action('easy-dashboard-between');
	if($show['show_table']==1){ ?>
	<div id="showError"></div>
	<div id="easy-table-div" class="easy-ui"></div>
	<script>
		jQuery(window).on("load", function() {
			easyreservations_send_table('', 1);
		});

		function createTablePickers(context){
			var easydateformat = '<?php echo RESERVATIONS_DATE_FORMAT; ?>';
			var dateformate = 'dd.mm.yy';
			if(easydateformat == 'Y/m/d') dateformate = 'yy/mm/dd';
			else if(easydateformat == 'm/d/Y') dateformate = 'mm/dd/yy';
			else if(easydateformat == 'Y-m-d') dateformate = 'yy-mm-dd';
			else if(easydateformat == 'd-m-Y') dateformate = 'dd-mm-yy';
			else if(easydateformat == 'd.m.Y') dateformate = 'dd.mm.yy';

			jQuery("#easy-table-search-date", context || document).datepicker({
				changeMonth: true,
				changeYear: true,
				firstDay: 1,
				dateFormat: dateformate,
				onSelect: function(dateText){
					easyreservations_send_table('all', 1);
				},
				<?php echo easyreservations_build_datepicker(0,0,true); ?>
				defaultDate: +10
			});
		}

		function resetTableValues(){
			var search = document.getElementById('easy-table-search-field');
			var date = document.getElementById('easy-table-search-date');
			var resources = document.getElementById('easy_table_resource_selector');
			var month = document.getElementById('easy_table_month_selector');
			var status = document.getElementById('easy-table-statusselector');
			var order = document.getElementById('easy_table_order');
			var orderby = document.getElementById('easy_table_order_by');
			
			if(order) order.value = '';
			if(orderby) orderby.value = '';
			if(search) search.value = '';
			if(date) date.value = '';
			if(resources) resources.selectedIndex = 0;
			if(month) month.selectedIndex = 0;
			if(status) status.selectedIndex = 0;
			easyreservations_send_table('active', 1);
		}
	</script>
	<form name="overview_click" method="post" action="admin.php?page=reservations&add">
		<input type="hidden" id="resource" name="resource">
		<input type="hidden" id="resource_space" name="resource_space">
		<input type="hidden" name="overview_click_from" id="overview_click_from">
		<input type="hidden" name="overview_click_to" id="overview_click_to">
	</form>
	<?php } do_action('easy-add-dashboard-widget');
		if( $show['show_new'] == 1 || $show['show_upcoming'] == 1 ){
			include_once(RESERVATIONS_ABSPATH . 'lib/admin/dashboard-statistics.php');
		}
		if($show['show_upcoming']==1){ ?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px; float:left;margin:0 10px 10px 0;clear:none;white-space:nowrap">
			<thead>
				<tr>
					<th>
						 <?php _e('Upcoming reservations', 'easyReservations'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0;padding:0;background:#fff">
						<div id="container" style="margin:5px 0 0 0;padding:0;background:#fff; height:300px;f"></div>
					</td>
				</tr>
			</tbody>
		</table>
		<?php } if($show['show_new']==1){ ?>
		<table  class="<?php echo RESERVATIONS_STYLE; ?>" style="width:10%; min-width:400px;min-height: 200px;float:left;margin:0 10px 10px 0;clear:none;">
			<thead>
				<tr>
					<th>
						 <?php _e('New reservations', 'easyReservations'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="margin:0; padding:0;background-color:#fff">
						<div id="container2" style="margin:5px 0 0 0;height:300px;"></div>
					</td>
				</tr>
			</tbody>
		</table><?php
		} if($show['show_export']==1){ 
			do_action('easy-add-export-widget');
		} if($show['show_today']==1){ ?>
		<?php
			$all_spaces = 0;
			foreach ( ER()->resources()->get() as $resource ) {
				$all_spaces += $resource->quantity;
			}
			$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE NOW() BETWEEN arrival AND departure AND approve='yes'"); // Search query 
		?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;clear:none;margin:0 10px 10px 0">
			<thead>
				<tr>
					<th>
						 <?php _e('What\'s happening today', 'easyReservations'); ?><span style="float:right;font-family:Georgia;font-size:16px;vertical-align:middle"><?php if($all_spaces > 0) echo round((100/$all_spaces)*count($queryDepartures)); ?><span id="idworkload" style="font-size:22px;vertical-align:middle">%<span></span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td style="background-color:#fff;padding:0">
						<table class="little_table">
							<thead>
								<tr>
									<th colspan="4"><?php _e('Arrival today', 'easyReservations'); ?></th>
								</tr>
								<?php $little_head = '<tr><th>'.__('Name', 'easyReservations').'</th>	<th>'.__('Resource', 'easyReservations').'</th><th style="text-align:center;">'.__('Persons', 'easyReservations').'</th><th style="text-align:right;">'.__('Price', 'easyReservations').'</th></tr>'; echo $little_head;?>
							</thead>
							<tbody>
							<?php
								$queryArrivalers = $wpdb->get_results("SELECT id, name, resource, adults, children FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(arrival) = DATE(NOW())"); // Search query
								$count = 0;

								foreach($queryArrivalers as $arrivler){
									$count++;
									$departure = new ER_Reservation($arrivler->id, false, true);
									if($count % 2 == 0) $class="odd";
									else $class="even";?>
									<tr class="<?php echo $class; ?>">
										<td><b><a href="admin.php?page=reservations&edit=<?php echo $departure->id; ?>"><?php echo $departure->name; ?></a></b></td>
										<td><?php echo stripslashes($departure->resource->post_title); ?></td>
										<td style="text-align:center;"><?php echo $departure->adults; ?> (<?php echo $departure->children; ?>)</td>
										<td style="text-align:right;"><?php echo $departure->formatPrice(true); ?></td>
									</tr><?php 
								}
								if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
							<thead>
								<tr>
									<th colspan="4"><?php _e('Departure today', 'easyReservations'); ?></th>
								</tr>
								<?php echo $little_head; ?>
							</thead>
							<tbody><?php 
							$queryDepartures = $wpdb->get_results("SELECT id FROM ".$wpdb->prefix ."reservations WHERE approve='yes' AND DATE(departure) = DATE(NOW()) "); // Search query
							$count = 0;
							foreach($queryDepartures as $depaturler){
								$count++;
								$departure = new ER_Reservation($depaturler->id, false, true);
								if($count % 2 == 0) $class="odd";
								else $class="even";?>
								<tr class="<?php echo $class; ?>">
									<td><b><a href="admin.php?page=reservations&edit=<?php echo $departure->id; ?>"><?php echo $departure->name; ?></a></b></td>
									<td><?php echo stripslashes($departure->resource->post_title); ?></td>
									<td style="text-align:center;"><?php echo $departure->adults; ?> (<?php echo $departure->children; ?>)</td>
									<td style="text-align:right;"><?php echo $departure->formatPrice(true); ?></td>
								</tr><?php 
							}
							if($count == 0) echo '<tr><td>'.__('None' ,'easyReservations').'</td></tr>'; ?>
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
	<?php } ?>
<?php }

if(!$can_change_reservation){
	echo '<div class="notice notice-warning is-dismissible" style="padding: 10px;margin-left: 0">'.__('This reservation is imported. It cannot be changed.', 'easyReservations').'</div>';
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + VIEW RESERVATION + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if(isset($approve) || isset($delete) || isset($view) || isset($sendmail)){
		echo '<table class="easy-admin-content" style="width:99%;margin-top:8px" cellspacing="0"><tr><td style="" valign="top">';
		?>
		<table class="<?php echo RESERVATIONS_STYLE; ?>">
			<thead>
				<tr>
					<th colspan="2">
						<?php _e('Reservation', 'easyReservations'); ?> <span class="badge secondary"><a href="admin.php?page=reservations&edit=<?php echo $res->id; ?>">#<?php echo $res->id; ?></a></span>
						<span style="float:right">
							<?php if($can_change_reservation){ ?>
								<?php if($res->status == 'del'){ ?>
                  <a class="easy-button grey" onClick="if(confirm('<?php _e('Really delete this reservation permanently?', 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=3&_wpnonce='.wp_create_nonce('easy-main-bulk'); ?>'; }"><?php echo ucfirst(__('delete', 'easyReservations'));?></a>
								<?php } else { ?>
									<a class="easy-button grey" onClick="if(confirm('<?php _e('Really move this reservation to trash?', 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=1&_wpnonce='.wp_create_nonce('easy-main-bulk'); ?>'; }"><?php echo ucfirst(__('trash', 'easyReservations'));?></a>
								<?php }
							}
							do_action('easy-view-title-right', $res); ?>
						</span>
					</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="2" style="border-bottom: 1px solid #e4e4e4;" nowrap><?php echo easyreservations_reservation_info_box($res, 'view', $res->status); ?></td>
				</tr>
				<tr>
					<td class="label" style="width:40%"><?php _e('Name', 'easyReservations');?> <span class="fa fa-user small"></span></td>
					<td style="width:60%"><?php echo $res->name;?></td>
				</tr>
				<tr class="alternate">
					<td class="label" style="width:40%"><?php _e('Date', 'easyReservations');?> <span class="fa fa-calendar small"></span></td>
					<td><?php echo date(RESERVATIONS_DATE_FORMAT_SHOW,$res->arrival); ?> - <?php echo date(RESERVATIONS_DATE_FORMAT_SHOW, $res->departure);?>
							<small>(<?php echo '<b>'.$res->times.'</b> '.er_date_get_interval_label($res->resource->interval, $res->times); ?>)</small></td>
				</tr>

				<tr>
					<td class="label"><?php _e('Email', 'easyReservations');?> <span class="fa fa-envelope small"></span></td>
					<td><?php echo $res->email;?></td>
				</tr>
				<tr class="alternate">
					<td class="label"><?php _e('Persons', 'easyReservations');?> <span class="fa fa-users small"></span></td>
					<td><?php _e('Adults', 'easyReservations');?>: <b><?php echo $res->adults;?></b> <?php _e('Children', 'easyReservations');?>: <b><?php echo $res->children;?></b></td>
				</tr>
				<tr>
					<td class="label"><?php _e('Resource', 'easyReservations');?> <span class="fa fa-home small"></span></td>
					<td><?php echo __($res->resource->post_title).'<b>'; if($res->resource->per_person && $res->resourcenumbername) echo ' - '.$res->resourcenumbername; ?></b></td>
				</tr>
				<?php if(!empty($res->country)){ ?>
					<tr class="alternate">
						<td class="label"><?php _e('Country', 'easyReservations');?>  <span class="fa fa-globe small"></span></td>
						<td><?php echo easyreservations_country_name($res->country); ?></td>
					</tr>
				<?php }
				do_action('easy-res-view-table-bottom', $res);
				if(!empty($custom_fields) && is_array($custom_fields)){
					foreach($custom_fields as $custom){
						if(isset($custom['id']) && isset($all_custom_fields['fields'][$custom['id']])){
							$field = $all_custom_fields['fields'][$custom['id']];
							echo '<tr>';
								echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" class="label">';
									echo __($field['title']).' <span class="fa fa-tag small"></span>';
								echo '</td>';

								echo '<td>';
									echo '<b>'.$res->getCustomsValue($custom).'</b> ';
									if(isset($field['price'])){
										echo er_format_money(
											$res->calculateCustom($custom['id'], $custom['value'], $custom_fields),
											1
										);
									}
								echo '</td>';
							echo '</tr>';
						}

					}
				}
				?>
			</tbody>
		</table>
		<?php echo easyreservations_detailed_price_admin($res, $res->resource); ?>
		</td>
		<td style="width:1%;"></td>
		<td valign="top" style="vertical-align:top;">
		<?php if(isset($view) && function_exists('easyreservations_generate_chat')){ ?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:350px;margin-top:0">
				<thead>
					<tr>
						<th><?php _e('User chat', 'easyReservations');?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td style="margin:0;padding:0">
							<?php echo easyreservations_generate_chat( $res, 'admin' ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		<?php }
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + EDIT RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////// EDIT RESERVATION ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($edit)){
	easyreservations_build_datepicker(1,array('datepicker','datepicker2', 'reservation_date')); ?>
<form id="editreservation" name="editreservation" method="post" action="admin.php?page=reservations&edit=<?php echo $edit; ?>">
<?php wp_nonce_field('easy-main-edit','easy-main-edit'); ?>
<input type="hidden" name="editthereservation" id="editthereservation" value="editthereservation">
<input type="hidden" name="reserved" id="reserved" value="<?php echo $res->reserved; ?>">
<input type="hidden" name="reseasdrved" id="resasderved" value="<?php echo $res->arrival; ?>">
<input type="hidden" name="copy" id="copy" value="no">
<table class="easy-admin-content" style="width:99%;margin-top:8px" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width:550px;" valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width:99%; margin-bottom:10px;">
				<thead>
					<tr>
						<th colspan="2">
							<?php echo sprintf(__('Edit %s', 'easyReservations'), __('reservation', 'easyReservations')); ?> <span class="badge secondary"><a href="admin.php?page=reservations&view=<?php echo $edit; ?>">#<?php echo $edit; ?></a></span>
							<span style="float:right">
								<?php if($can_change_reservation){ ?>
									<?php if($res->status == 'del'){ ?>
	                  <a class="easy-button grey" onClick="if(confirm('<?php _e('Really delete this reservation permanently?', 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=3&_wpnonce='.wp_create_nonce('easy-main-bulk'); ?>'; }"><?php echo ucfirst(__('delete', 'easyReservations'));?></a>
									<?php } else { ?>
										<a class="easy-button grey" onClick="if(confirm('<?php _e('Really move this reservation to trash?', 'easyReservations'); ?>')) { window.location = '<?php echo 'admin.php?page=reservations&bulkArr[]='.$res->id.'&bulk=1&_wpnonce='.wp_create_nonce('easy-main-bulk'); ?>'; }"><?php echo ucfirst(__('trash', 'easyReservations'));?></a>
									<?php }
								}
								do_action('easy-edit-title-right', $res); ?>
							</span>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<td colspan="2">
								<?php if($can_change_reservation){ ?>
									<input type="submit" onclick="document.getElementById('copy').value = 'yes';document.getElementById('editreservation').submit(); return false;" class="easy-button grey" value="<?php echo sprintf(__('Copy', 'easyReservations')); ?>">
									<input type="submit" onclick="document.getElementById('editreservation').submit(); return false;" class="easy-button" value="<?php echo sprintf(__('Edit %s', 'easyReservations'), __('reservation', 'easyReservations')); ?>">
								<?php } ?>
								<span class="showPrice" style="float:left;font-weight: normal;"><?php _e('Price', 'easyReservations'); ?>: <span id="showPrice" style="font-weight:bold;"><b><?php echo er_format_money(0,1); ?></b></span></span>
						</td>
					</tr>
				</tfoot>
				<tbody>
					<tr>
						<td colspan="2" style="border-bottom: 1px solid #e4e4e4;" nowrap><?php echo easyreservations_reservation_info_box($res, 'edit', $res->status); ?></td>
					</tr>
					<tr>
						<td nowrap class="label"><?php _e('Name', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" name="name" value="<?php echo $res->name;?>"><span class="input-box"><span class="fa fa-user small"></span></span></span></td>
					</tr>
					<tr class="alternate">
						<td nowrap class="label"><?php _e('Arrival', 'easyReservations');?></span></td>
						<td>
							<span class="input-wrapper">
								<input type="text" id="datepicker" style="width:100px" name="date" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$res->arrival); ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>">
								<span class="input-box"><span class="fa fa-calendar small"></span></span>
							</span>
							<select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo er_form_time_options(date("G",$res->arrival)); ?></select>
							<select name="from-time-min"><?php echo er_form_number_options("00",59,date("i",$res->arrival)); ?></select>
						</td>
					</tr>
					<tr>
						<td class="label"><?php _e('Departure', 'easyReservations');?></td>
						<td>
							<span class="input-wrapper">
								<input type="text" id="datepicker2" style="width:100px" name="dateend" value="<?php echo date(RESERVATIONS_DATE_FORMAT,$res->departure); ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>"><span class="input-box"><span class="fa fa-calendar small"></span></span>
							</span>
							<select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo er_form_time_options(date("G",$res->departure)); ?></select>
							<select name="to-time-min"><?php echo er_form_number_options("00",59,date("i",$res->departure)); ?></select>
						</td>
					</tr>
					<tr>
						<td nowrap class="label"><?php _e('Persons', 'easyReservations');?></td>
						<td>
							<span class="input-wrapper">
								<select name="adults" onchange="easyreservations_send_price_admin();"><?php echo er_form_number_options(1,50,$res->adults); ?></select>
								<span class="input-box"><span class="fa fa-users small"></span></span>
							</span>
							<span class="input-wrapper">
								<select name="children" onchange="easyreservations_send_price_admin();"><?php echo er_form_number_options(0,50,$res->children); ?></select>
								<span class="input-box"><span class="fa fa-child small"></span></span>
							</span>
						</td>
					</tr>
					<tr>
						<td class="label"><?php _e('Resource', 'easyReservations');?></td>
						<td>
							<span class="input-wrapper">
								<select name="resource" id="resource" style="max-width: 350px" onchange="easyreservations_send_price_admin();changer();get_the_select(1, this.value);<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>">
									<?php echo er_form_resources_options($res->resource->ID, true); ?>
								</select>
								<span class="input-box"><span class="fa fa-home small"></span></span>
							</span>

							<span id="resource_space_container"></span>
						</td>
					</tr>
					<tr class="alternate">
						<td class="label"><?php _e('Email', 'easyReservations');?></td>
						<td>
							<span class="input-wrapper">
								<input type="text" name="email" value="<?php echo $res->email;?>" style="width:250px" onchange="easyreservations_send_price_admin();">
								<span class="input-box"><span class="fa fa-envelope small"></span></span>
							</span>
						</td>
					</tr>
					<tr>
						<td class="label"><?php _e('Country', 'easyReservations');?></td>
						<td>
							<span class="input-wrapper">
								<select name="country" style="width:200px;"><option value="" <?php if($res->country=='') echo 'selected="selected"'; ?>><?php _e('Unknown', 'easyReservations');?></option><?php echo er_form_country_options($res->country); ?></select>
								<span class="input-box"><span class="fa fa-globe small"></span></span>
							</span>
						</td>
					</tr>
					<?php
					 	if(!empty($custom_fields)){
							foreach($custom_fields as $key => $custom){
								if(isset($custom['id'])){
									if(isset($all_custom_fields['fields'][$custom['id']])){
										$field = $all_custom_fields['fields'][$custom['id']];
										echo '<tr>';
										echo '<td style="vertical-align:text-bottom;text-transform: capitalize;" class="label">';
										echo __($field['title']).' <a href="admin.php?page=reservations&edit='.$edit.'&delete-custom='.$key.'&custom-type=custom" class="fa fa-times"></a></td>';
										echo '<td>'.er_generate_custom_field($custom['id'], $custom['value'], isset($field['price']) ? 'onchange="easyreservations_send_price_admin();"' : '' ).'</td></tr>';
									}
								}
							}
						}
					 ?>
				</tbody>
				<tbody id="customPrices">
				</tbody>
			</table>
			<div style="margin:10px 0">
				<?php echo easyreservations_detailed_price_admin($res, $res->resource); ?>
			</div>
			<?php if($can_change_reservation): ?>
				<input type="button" class="easy-button" onclick="window.location.href = window.location.href+'&recalculate';" value="<?php _e('Recalculate', 'easyReservations'); ?>">
			<?php endif; ?>
		</td>
		<td style="width:1%"></td>
		<td valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:10px;">
				<thead>
					<tr>
						<th colspan="2"><?php _e('Status', 'easyReservations');?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="alternate">
						<td class="label"><?php _e('Status', 'easyReservations');?></td>
						<td nowrap style="width:35%">
							<span class="select"><select name="reservation_status"><option value="" <?php if(empty($res->status)) echo 'selected'; ?>><?php _e('Pending', 'easyReservations');?></option><option value="yes" <?php if($res->status == 'yes') echo 'selected'; ?>><?php _e('Approved', 'easyReservations');?></option><option value="no" <?php if($res->status == 'no') echo 'selected'; ?>><?php _e('Rejected', 'easyReservations');?></option><option value="del" <?php if($res->status == 'del') echo 'selected'; ?>><?php _e('Trashed', 'easyReservations');?></option></select></span>
						</td>
					</tr>
					<tr>
						<td class="label"><?php _e('Reserved', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" name="reservation_date" id="reservation_date" style="width:100px" value="<?php echo date(RESERVATIONS_DATE_FORMAT, $res->reserved); ?>"><span class="input-box"><span class="fa fa-calendar small"></span></span></span></td>
					</tr>
					<tr class="alternate">
						<td class="label"><?php _e('User', 'easyReservations');?></td>
						<td nowrap><span class="select"><select name="edit_user"><option value="0"><?php _e('None', 'easyReservations');?></option>
						<?php
							echo easyreservations_get_user_options($res->user);
						?>
						</select></span></td>
					</tr>
					<tr>
						<td class="label"><?php _e('Price', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" value="<?php echo $res->price; ?>" name="priceset" style="width:60px;text-align:right;"><span class="input-box"><?php echo '&'.RESERVATIONS_CURRENCY.';';?></span></span></td>
					</tr>
					<tr class="alternate">
						<td class="label"><?php _e('Paid', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" name="EDITwaspaid" value="<?php echo $res->paid; ?>" style="width:60px;text-align:right"><span class="input-box"><?php echo '&'.RESERVATIONS_CURRENCY.';';?></span></span></td>
					</tr>
				</tbody>
			</table>
			<?php echo easyreservations_generate_admin_custom_add(); ?>
			<?php do_action('easy-dash-edit-side-middle', $res);?>
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:10px">
				<thead>
					<tr>
						<th><?php _e('Send email', 'easyReservations');?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="alternate">
						<td class="content"><label class="wrapper"><input type="checkbox" name="sendthemail" value="on"><span class="input"></span> <i><?php _e('Send email to user on edit', 'easyReservations');?></i></label></td>
					</tr>
					<?php do_action('easy_send_mail_form'); ?>
					<tr>
						<td class="content"><textarea type="text" name="approve_message" id="approve_message" value="Value" style="width:260px;height:60px" onfocus="if (this.value == 'Message') this.value = '';" onblur="if (this.value == '') this.value = 'Message';">Message</textarea></td>
					</tr>
				</tbody>
			</table>
			<?php do_action('easy-dash-edit-side-bottom', $res);?>
		</td>
</table>
</tr>
</form>
<?php do_action('easy-after-edit', $res); ?>
<script type="text/javascript">easyreservations_send_price_admin();
get_the_select('<?php echo $res->space; ?>', '<?php echo $res->resource->ID; ?>');</script>
<?php
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + ADD RESERVATION  - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($add)){
	easyreservations_build_datepicker(1,array('datepicker','datepicker2', 'reservation_date'));
?>
<form id="editreservation" name="editreservation" method="post" action="">
<?php wp_nonce_field('easy-main-add','easy-main-add'); ?>
<input type="hidden" name="addreservation" id="addreservation" value="addreservation">
<table class="easy-admin-content" style="width:99%;margin-top:8px" cellspacing="0">
	<tr>
	<td style="min-width:550px;" valign="top">
		<table class="<?php echo RESERVATIONS_STYLE; ?>" style="width: 100%">
			<thead>
				<tr>
					<th colspan="2"><?php echo sprintf(__('Add %s', 'easyReservations'), __('reservation', 'easyReservations'));?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="2"><input type="button" onclick="document.getElementById('editreservation').submit(); return false;" class="easy-button" value="<?php echo sprintf(__('Add %s', 'easyReservations'), __('reservation', 'easyReservations'));?>"><span class="showPrice" style="float:left;font-weight: normal;"><?php _e('Price', 'easyReservations'); ?>: <span id="showPrice" style="font-weight:bold;"><b><?php echo er_format_money(0,1); ?></b></span></span></td>
				</tr>
			</tfoot>
			<tbody>
				<tr>
						<td nowrap class="label"><?php _e('Name', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" name="name" value="<?php if(isset($_POST['name'])) echo $_POST['name']; ?>"><span class="input-box"><span class="fa fa-user small"></span></span></span></td>
				</tr>
				<tr class="alternate">
					<td nowrap class="label"><?php _e('Arrival', 'easyReservations');?><?
						if(isset($_POST['from-time-hour'])) $fromtimeh = $_POST['from-time-hour']; else $fromtimeh = 12;
						if(isset($_POST['from-time-min'])) $fromtimem = $_POST['from-time-min']; else $fromtimem = 0; ?>
					</td>
					<td nowrap>
						<span class="input-wrapper">
							<input type="text" id="datepicker" style="width:100px" name="date" value="<?php if(isset($_POST['date'])) echo $_POST['date']; ?>" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(1);<?php }?>">
							<span class="input-box"><span class="fa fa-calendar small"></span></span>
						</span>
						<select name="from-time-hour" id="from-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo er_form_time_options($fromtimeh); ?></select>
						<select name="from-time-min"><?php echo er_form_number_options("00",59,$fromtimem); ?></select>
					</td>
				</tr>
				<tr>
					<td class="label"><?php _e('Departure', 'easyReservations');?></td><?
						if(isset($_POST['to-time-hour'])) $totimeh = $_POST['to-time-hour']; else $totimeh = 12;
						if(isset($_POST['to-time-min'])) $totimem = $_POST['to-time-min']; else $totimem = 00; ?>
					<td nowrap>
						<span class="input-wrapper">
							<input type="text" id="datepicker2" style="width:100px" name="dateend" value="<?php if(isset($_POST['dateend'])) echo $_POST['dateend']; ?>" onchange="easyreservations_send_price_admin();changer();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>">
							<span class="input-box"><span class="fa fa-calendar small"></span></span>
						</span>
						<select name="to-time-hour" id="to-time-hour" onchange="easyreservations_send_price_admin();<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(0);<?php }?>"><?php echo er_form_time_options($totimeh); ?></select>
						<select name="to-time-min"><?php echo er_form_number_options("00",59,$totimem); ?></select>
					</td>
				</tr>
				<tr valign="top" class="alternate">
					<td nowrap class="label"><?php _e('Persons', 'easyReservations');?></td>
					<td>
						<span class="input-wrapper">
							<select name="adults" onchange="easyreservations_send_price_admin();"><?php echo er_form_number_options(1,50, isset($_POST['persons']) ? intval($_POST['persons']) : 1); ?></select>
							<span class="input-box"><span class="fa fa-users small"></span></span>
						</span>
						<span class="input-wrapper">
							<select name="children" onchange="easyreservations_send_price_admin();"><?php echo er_form_number_options(0,50, isset($_POST['children']) ? intval($_POST['children']) : 0); ?></select>
							<span class="input-box"><span class="fa fa-child small"></span></span>
						</span>
					</td>
				</tr>
				<tr valign="top">
					<td class="label"><?php _e('Resource', 'easyReservations');?></td>
					<td>
						<span class="input-wrapper">
							<select id="resource" name="resource" onchange="easyreservations_send_price_admin();changer();get_the_select(1,this.value);<?php if($overview_options['overview_autoselect'] == 1){ ?>dofakeClick(2);<?php }?>">
								<?php echo er_form_resources_options(isset($_POST['resource']) ? intval($_POST['resource']) : '', true); ?>
							</select>
							<span class="input-box"><span class="fa fa-home small"></span></span>
						</span>

						<span id="resource_space_container"></span>
					</td>
				</tr>
				<tr class="alternate">
					<td class="label"><?php _e('Email', 'easyReservations');?></td>
					<td>
						<span class="input-wrapper">
							<input type="text" name="email" value="<?php if(isset($_POST['email'])) echo $_POST['email']; ?>" onchange="easyreservations_send_price_admin();">
							<span class="input-box"><span class="fa fa-envelope small"></span></span>
						</span>
					</td>
				</tr>
				<tr>
						<td class="label"><?php _e('Country', 'easyReservations');?></td>
					<td>
						<span class="input-wrapper">
							<select name="country" style="width:200px"><option value=""><?php _e('Unknown', 'easyReservations');?></option><?php echo er_form_country_options($count); ?></select>
							<span class="input-box"><span class="fa fa-globe small"></span></span>
						</span>
					</td>
				</tr>
				<?php
					if(isset($custom_fields['fields'])){
						foreach($custom_fields['fields'] as $custom_id => $custom_field){
							if(isset($custom_field['admin'])){
								echo '<tr>';
									echo '<td style=";text-transform: capitalize;" class="label">'.__($custom_field['title']).' <a onclick="jQuery(this).parent().parent().remove()" class="fa fa-remove"></a></td>';
								echo '<td>'.er_generate_custom_field($custom_id, isset($custom_field['value']) ? $custom_field['value'] : '').'</td></tr>';
							}
						}
					}
			  ?>
			</tbody>
			<tbody id="customPrices">
			</tbody>
		</table>
		</td><td style="width:1%"></td>
		<td valign="top">
			<table class="<?php echo RESERVATIONS_STYLE; ?>" style="min-width:320px;width:320px;margin-bottom:10px;">
				<thead>
					<tr>
						<th colspan="2"><?php _e('Status', 'easyReservations');?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="alternate">
						<td class="label"><?php _e('Status', 'easyReservations');?></td>
						<td nowrap style="width:35%">
							<span class="select"><select name="reservation_status"><option value=""><?php _e('Pending', 'easyReservations');?></option><option value="yes"><?php _e('Approved', 'easyReservations');?></option><option value="no"><?php _e('Rejected', 'easyReservations');?></option><option value="del"><?php _e('Trashed', 'easyReservations');?></option></select></span>
						</td>
					</tr>
					<tr>
						<td class="label"><?php _e('Reserved', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input type="text" name="reservation_date" id="reservation_date" style="width:100px" value=""><span class="input-box"><span class="fa fa-calendar small"></span></span></span></td>
					</tr>
					<tr class="alternate">
						<td class="label"><?php _e('User', 'easyReservations');?></td>
						<td nowrap><span class="select"><select name="edit_user"><option value="0"><?php _e('None', 'easyReservations');?></option>
						<?php
							echo easyreservations_get_user_options();
						?>
						</select></span></td>
					</tr>
					<tr>
						<td class="label"><?php _e('Price', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input name="priceAmount" type="text" style="width:50px"><span class="input-box"><?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></span></span></td>
					</tr>
					<tr>
						<td class="label"><?php _e('Paid', 'easyReservations');?></td>
						<td nowrap><span class="input-wrapper"><input name="paidAmount" type="text"value="0" style="width:50px;"><span class="input-box"><?php echo '&'.RESERVATIONS_CURRENCY.';'; ?></span></span></td>
					</tr>
				</tbody>
				<tbody id="priceCell">
				</tbody>
			</table>
			<?php echo easyreservations_generate_admin_custom_add(); ?>
		</td>
	</tr>
</table>
</form>
<script>get_the_select(<?php echo isset($_POST['resource_space']) ?  intval($_POST['resource_space']) : 1; ?>, document.getElementById('resource').value);</script>
<?php if(isset($_POST['overview_click_to'])){ ?><script>jQuery(document).ready(function(){ fakeClick('<?php echo $_POST['overview_click_from']; ?>','<?php echo $_POST['overview_click_to']; ?>','<?php echo $_POST['resource']; ?>','<?php echo $_POST['resource_space']; ?>', '');document.getElementById('datepicker').value='<?php echo date(RESERVATIONS_DATE_FORMAT, $_POST['overview_click_from']); ?>';document.getElementById('datepicker2').value='<?php echo date(RESERVATIONS_DATE_FORMAT, $_POST['overview_click_to']); ?>';easyreservations_send_price_admin();});</script><?php } //Set resource and resource_space after click on Overview and redirected to add
}
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + APPROVE / REJECT - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($approve) || isset($delete)) {
	if(isset($delete)){ $delorapp=$delete; $delorapptext='reject'; } elseif(isset($approve)){ $delorapp=$approve; $delorapptext='approve'; } ?>  <!-- Content will only show on delete or approve Reservation //--> 
	<form method="post" action="admin.php?page=reservations<?php if(isset($approve)) echo "&approve=".$approve ;  if(isset($delete)) echo "&delete=".$delete ;?>"  id="reservation_approve" name="reservation_approve">
		<input type="hidden" name="action" value="reservation_approve"/>
		<input type="hidden" name="<?php if(isset($approve)) echo 'approve'; else echo 'delete' ?>" value="yes" />
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width: 600px">
			<thead>
				<tr>
					<th>
						<?php if(isset($approve)) echo '<span class="fa fa-check"></span> '.__('Approve reservation', 'easyReservations'); elseif(isset($delete)) echo '<span class="fa fa-times"></span> '.__('Reject reservation', 'easyReservations'); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td>
						<?php if($can_change_reservation){ ?>
							<input type="submit" onclick="document.getElementById('reservation_approve').submit(); return false;"  class="easy-button" value="<?php if(isset($approve)) _e('Approve', 'easyReservations'); else echo __('Reject', 'easyReservations');?>">
						<?php } ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
				<?php if(isset($approve)){ ?>
					<tr>
						<td><?php _e('Resource', 'easyReservations');?>: <?php echo __($res->resource->post_title);?> # <span id="resource_space_container"></span>
          </tr>
				<?php } do_action('easy_send_mail_form'); ?>
				<tr>
					<td class="content">
						<label class="wrapper"><input type="checkbox" name="sendthemail" checked><span class="input"></span><?php _e('Send email to guest', 'easyReservations');  ?></label><br>
						<label class="wrapper"><input type="checkbox" name="hasbeenpayed"><span class="input"></span><?php _e('Has been paid', 'easyReservations');  ?></label>
						<div>Send email with the template <strong>Mail to guest after approval/rejection</strong>.</div>
						<textarea style="height: 200px" name="approve_message" class="er-mail-textarea" width="100px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		
	</form><?php if(isset($approve)) { if($res->space < 1) $ex = 1; else $ex = $res->space;?><script>get_the_select(<?php echo $ex; ?>, <?php echo $res->resource; ?>);<?php do_action('easy-approve-script'); ?></script><?php } ?>
<?php  }
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + SEND MAIL - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + - + */

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if(isset($sendmail)) {
	?><form method="post" action=""  id="reservation_sendmail" name="reservation_sendmail">
		<input type="hidden" name="thesendmail" value="thesendmail"/>
		<table class="<?php echo RESERVATIONS_STYLE; ?>" cellspacing="0" cellpadding="0" style="width: 600px">
			<thead>
				<tr>
					<th><?php echo __('Send email to guest', 'easyReservations'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td>
						<?php if($can_change_reservation){ ?>
							<input type="submit" onclick="document.getElementById('reservation_sendmail').submit(); return false;" class="easy-button" value="<?php echo __('Send email', 'easyReservations'); ?>">
						<?php } ?>
				</tr>
			</thead>
			<tbody>
				<?php do_action('easy_send_mail_form'); ?>
				<tr>
					<td class="content">
						Send email with the template <strong>Mail to guest from admin in dashboard</strong>.
						<textarea cols="60" rows="4" name="approve_message" class="er-mail-textarea" style="height: 200px"></textarea>
					</td>
				</tr>
			</tbody>
		</table>
		<p style="float:right"></p>
	</form>
<?php }
	if(isset($approve) || isset($delete) || isset($view) || isset($sendmail)) echo '</td></tr></table>';
} ?>