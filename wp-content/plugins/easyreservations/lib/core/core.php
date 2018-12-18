<?php
/*
Plugin Name: Module Core
Plugin URI: http://www.easyreservations.oef/module/
Description: The core contains the modules overview, the module installation and the module update notifier.
Version: 1.3
Author: Feryaz Beer
License:GPL2
*/

	if(is_admin()){

		add_action('easy_settings_navigation', 'easyreservations_core_add_settings_tab');

		function easyreservations_core_add_settings_tab(){
			$current = isset($_GET['tab']) && $_GET['tab'] == "plugins" ? 'active' : '';
			$tab = '<li ><a href="admin.php?page=reservation-settings&tab=plugins" class="'.$current.'"><span class="fa fa-diamond"></span> '. __('Premium', 'easyReservations').'</a></li>';

			echo $tab;
		}
		
		add_action('er_set_save', 'easyreservations_core_save_settings');

		function easyreservations_core_save_settings(){
			if(isset($_GET['tab']) && $_GET['tab'] == "plugins" && current_user_can('activate_plugins')){
				if(isset($_GET['check'])){
					$update = easyreservations_latest_modules_versions(0,false,true);
					if(is_string($update)) ER()->messages()->add_error($update);
					else ER()->messages()->add_notice(__('Checked for updates', 'easyReservations'));
				} elseif(isset($_POST['prem_login'])){
					$login = easyreservations_latest_modules_versions(0,false, true);
					if(is_array($login)) ER()->messages()->add_success(__('Logged in', 'easyReservations'));
					elseif(is_string($login) && $login != 'creds') ER()->messages()->add_error($login);
				} elseif(isset($_GET['activate'])){
					ER()->messages()->add_success(sprintf(__('%s activated', 'easyReservations'), sprintf(__('Module %s', 'easyReservations'), '<b>'.$_GET['activate'].'</b>')));
				} elseif(isset($_GET['logout'])){
					delete_option('reservations_login');
					ER()->messages()->add_notice(__('Logged out', 'easyReservations'));
				} elseif(isset($_GET['activate_all'])){
					ER()->messages()->add_notice(__('All installed modules activated', 'easyReservations'));
				} elseif(isset($_GET['deactivate_all'])){
					ER()->messages()->add_notice(__('All installed modules deactivated', 'easyReservations'));
				} elseif(isset($_GET['changelog'])){
					echo '<div id="the_modules_changelog">'.easyreservations_latest_modules_versions(0,false,true,false,$_GET['changelog']).'</div>';
					exit;
				} elseif(isset($_GET['update']) || isset($_GET['install'])){
					if(isset($_GET['update'])) $update_string = __('updated', 'easyReservations');
					else {
						$_GET['update'] = $_GET['install'];
						$update_string = __('installed', 'easyReservations');
					}
					$update = easyreservations_latest_modules_versions(86400, false, true, $_GET['update']);
					if($_GET['update'] == 'all') $link = 'http://easyreservations.org/premium/';
					else $link = 'http://easyreservations.org/module/'.$_GET['update'];
					if($update === false) ER()->messages()->add_error(sprintf(__('Failure at updating module %1$s - %2$s', 'easyReservations'), '<b>'.$_GET['update'].'</b>', '<a href="'.$link.'">update manually</a>'));
					elseif(is_object($update)) ER()->messages()->add_error(sprintf(__('Failure at extracting module %1$s - %2$s', 'easyReservations'), '<b>'.$_GET['update'].'</b>', '<a href="'.$link,'">update manually</a>'));
					elseif(is_string($update) && $update != 'creds') ER()->messages()->add_error($update);
					elseif($update != 'creds') ER()->messages()->add_success(sprintf(__('Module %s', 'easyReservations'), '<b>'.$_GET['update'].'</b>'.' '.$update_string));
				} elseif(isset($_GET['deactivate'])){
					ER()->messages()->add_notice(sprintf(__('%s deactivated', 'easyReservations'), sprintf(__('Module %s', 'easyReservations'), '<b>'.$_GET['deactivate'].'</b>')));
				} elseif(isset($_GET['delete'])){
					$url = 'admin.php?page=reservation-settings&tab=plugins&delete='.$_GET['delete'];
					if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false) ) ) {

					} elseif ( ! WP_Filesystem($creds) ) {
						request_filesystem_credentials($url, 'ftp', true, false);
					} else {
						$dir = RESERVATIONS_ABSPATH.'lib/modules/'.$_GET['delete'].'/';
						foreach (scandir($dir) as $item) {
							if ($item == '.' || $item == '..') continue;
							unlink($dir.DIRECTORY_SEPARATOR.$item);
						}
						rmdir($dir);
						ER()->messages()->add_notice(sprintf(__('%s deleted', 'easyReservations'), sprintf(__('Module %s', 'easyReservations'), '<b>'.$_GET['delete'].'</b>')));
					}
				}

				if(isset($_FILES['reservation_core_upload_file']) || isset($_GET['file_name'])){
					if(isset($_FILES['reservation_core_upload_file'])) $file_name = $_FILES['reservation_core_upload_file']['name']; else $file_name = $_GET['file_name'];
					$file_tmp_name = $_FILES['reservation_core_upload_file']['tmp_name'];
					if(isset($_FILES['reservation_core_upload_file'])) $file_type = $_FILES['reservation_core_upload_file']['type']; else $file_type = 'application/x-zip' ;
					$uploads = wp_upload_dir();
					$saved_file_location = $uploads['basedir'].'/'. $file_name;

					if(preg_match("/(easyreservations|module|premium)/i", $file_name) && ($file_type == 'application/zip'  || $file_type == 'application/x-zip' || $file_type == 'application/x-zip-compressed' || $file_type == 'text/html' || $file_type == 'application/octet-stream' || isset($_GET['file_name']))){
						if(move_uploaded_file($file_tmp_name, $saved_file_location) || isset($_GET['file_name'])) {
							$url = 'admin.php?page=reservation-settings&tab=plugins&file_name='.$file_name;
							if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false) ) ) {
								$error = 1;
							} elseif ( ! WP_Filesystem($creds) ) {
								request_filesystem_credentials($url, 'ftp', true, false);
							} else {
								global $wp_filesystem;
									if(class_exists('ZipArchive')){
									$zip = new ZipArchive();  
									$x = $zip->open($saved_file_location);  
									if($x === true){
										$zip->extractTo(RESERVATIONS_ABSPATH);
										$zip->close();                
									} else {
										unzip_file($saved_file_location, RESERVATIONS_ABSPATH);
									}
								}
								unlink($saved_file_location);
								ER()->messages()->add_success(sprintf(__('Module %s', 'easyReservations'), '<b>'.str_replace('.zip', '', str_replace( '-', ' ', $file_name)).'</b>').' '.__('installed', 'easyReservations'));
							}
						} else ER()->messages()->add_error(__('Upload failed', 'easyReservations'));
					} else ER()->messages()->add_error(__('Wrong file', 'easyReservations'));
				}
			}
		}

		add_action('er_set_add', 'easyreservations_core_add_settings');
		
		function easyreservations_load_modules_array(){
			return array(
				'invoice' => array(
					'slug' => 'invoice',
					'title' => 'Invoice',
					'content' => 'Generate totally customizable Invoices automatically from predefined templates. Including an editor for admins, invoices as email attachments and correct A4 Letter formats.',
					'function' => 'easyreservations_load_invoice_template',
					'least' => '1.0.20',
					'vers' => '1.0.20',
					'image' => 'book',
				),
				'htmlmails' => array(
					'slug' => 'htmlmails',
					'title' => 'htmlMails',
					'content' => 'Style your emails with HTML to increase the appearance of your hospitality.',
					'function' => 'easyreservations_send_multipart_mail',
					'least' => '1.1.14',
					'vers' => '1.1.14',
					'image' => 'envelope',
				),
				'paypal' => array(
					'slug' => 'paypal',
					'title' => 'Payment',
					'content' => 'Integration of multiple payment gateways like PayPal, Authorize.net, 2checkout, DIBSpayment or Ogone. Further implementation of the stripe.com credit card gateway and a function to store credit cards for manual treatment. Automatically approve reservations after submit or payment.',
					'function' => 'easyreservations_validate_payment',
					'least' => '1.7.10',
					'vers' => '1.7.10',
					'image' => 'paypal',
				),
				'search' => array(
					'slug' => 'search',
					'title' => 'Search',
					'content' => 'New shortcode to let your guests search for available resources. No page reload for searching, compatible to calendar, shows price, can show unavailable resources and links to form with automatic selection. For each resource it can display a small one-column calendar to show its availability. The results can be shown as list or table.',
					'function' => 'easyreservations_search_add_tinymce',
					'least' => '2.0.3',
					'vers' => '2.0.4',
					'image' => 'search',
					'addon' => array(
						array(
							'slug' => 'relatedpost',
							'file' => 'searchtypes.php',
							'title' => 'Releated posts add-on',
							'content' => 'Link posts or pages with resources for the search results',
							'function' => 'easyreservations_search_change_values',
							'link' => 'forums/topic/releated-posts-add-on/',
							'vers' => '1.0.8',
							'least' => '1.0.8',
							'beta' => 1
						),
						array(
							'slug' => 'attributes',
							'file' => 'attributes.php',
							'title' => 'Resource attributes add-on',
							'content' => 'Give resources different attributes to let your guests search for them.',
							'function' => 'easyreservations_search_attributes_check',
							'link' => 'forums/topic/resource-attributes-add-on/',
							'vers' => '1.0.2',
							'least' => '1.0.2',
							'beta' => 1
						)
					)
				),
				'sync' => array(
					'slug' => 'sync',
					'title' => 'Synchronization',
					'content' => 'Synchronize your resources availability with unlimited portals and calendars through iCalendar feeds. Includes function to add reservations to the shopping card of WooCommerce.',
					'function' => 'easyreservations_sync_add_rewrite_rule',
					'least' => '2.0.3',
					'vers' => '2.0.3',
					'image' => 'refresh',
				),
				'hourlycal' => array(
					'slug' => 'hourlycal',
					'title' => 'Hourly calendar',
					'content' => 'Show your guests the availability of the resources on an hourly basis.',
					'function' => 'easyreservations_send_hourlycal_callback',
					'least' => '1.2.1',
					'vers' => '1.2.1',
					'image' => 'clock-o',
				),
				'export' => array(
					'slug' => 'export',
					'title' => 'Export',
					'content' => 'Export selectable reservation information by time, selection or all as .xls or .csv.',
					'function' => 'easyreservations_export_widget',
					'least' => '1.2.15',
					'vers' => '1.2.15',
					'image' => 'table',
				),
				'lang' => array(
					'slug' => 'lang',
					'title' => 'Multilingual',
					'content' => 'Makes texts in forms, emails, search bar and invoices translatable.',
					'function' => 'easyreservations_translate_content',
					'least' => '1.2.7',
					'vers' => '1.2.7',
					'image' => 'globe',
				),
				'useredit' => array(
					'slug' => 'useredit',
					'title' => 'User control panel',
					'content' => 'Lets your guests login to edit their reservations. They can switch between their reservations in a table. In addition it provides a chat-like feature. New messages in table, dummy message at start, admin notices, avatars and fully AJAX driven. The guest can see his invoice and cancel his reservation.',
					'function' => 'easyreservations_generate_chat',
					'least' => '1.3.12',
					'vers' => '1.3.12',
					'image' => 'user',
				),
				'statistics' => array(
					'slug' => 'statistics',
					'title' => 'Statistics',
					'content' => 'Detailed statistics, charts, resource usages and a dashboard widget.',
					'function' => 'easyreservations_add_statistics_submenu',
					'least' => '1.2.6',
					'vers' => '1.2.6',
					'image' => 'pie-chart',
				),
				'styles' => array(
					'slug' => 'styles',
					'title' => 'Appearance', 'easyReservations',
					'content' => 'Two new shortcodes. Receipt displays the price calculation in your forms and resource box displays your resources as slider or list to improve the selection. Includes a new datepicker style.',
					'function' => 'easyreservations_register_datepicker_style',
					'least' => '2.0.4',
					'vers' => '2.0.4',
					'image' => 'certificate',
				),
				'coupons' => array(
					'slug' => 'coupons',
					'title' => 'Coupon',
					'content' => 'Let your guests enter coupon codes for discounts.',
					'least' => '1.0.19',
					'function' => 'easyreservations_calculate_coupon',
					'vers' => '1.0.19',
					'image' => 'ticket',
				),
				'multical' => array(
					'slug' => 'multical',
					'title' => 'Extended calendar',
					'content' => 'Extend the calendar shortcode to show multiple months in a flexible grid (x*y). Includes a new boxed calendar style.',
					'least' => '1.1.10',
					'function' => 'easyreservations_generate_multical',
					'vers' => '1.1.10',
					'image' => 'th',
				)
			);
		}

		function easyreservations_core_add_settings(){
			$core_data = get_plugin_data(RESERVATIONS_ABSPATH.'lib/core/core.php', false);
			if(isset($_GET['tab']) && $_GET['tab'] == "plugins"){
				$login = false;
				$the_modules = easyreservations_load_modules_array();
				if($data = get_option('reservations_login')) $xml = easyreservations_latest_modules_versions(86400,$the_modules, true);
				else $login = true;
				if(isset($xml) && $xml && is_array($xml)) $the_modules = $xml;
				elseif(isset($xml) && $xml && is_string($xml) && $xml != 'creds') $login_error = $xml; ?>
					<input type="hidden" name="action" value="reservation_core_settings">
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table table" style="width:99%;">
						<thead>
							<tr>
								<th style="width:10px"></th>
								<th><?php _e('Name', 'easyReservations');?></th>
								<th style="width:50%"><?php _e('Description', 'easyReservations');?></th>
								<th style="text-align:center"><?php echo ucfirst(__('installed', 'easyReservations'));?></th>
								<th style="text-align:center"><?php _e('Actual', 'easyReservations');?></th>
								<th style="text-align:right"></th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td style="text-align:center"><span class="fa fa-diamond"></span> </td>
								<td><b><?php echo $core_data['Name']; ?></b></td>
								<td><?php echo $core_data['Description']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:center"><?php echo $core_data['Version']; ?></td>
								<td style="font-weight:bold;text-align:right"></td>
							</tr>
							<?php
								$action = '';
								foreach($the_modules as $module){
									$status = 0;
									$new_update = false;
									$deprecated = false;
									if(function_exists($module['function'])) $status = 2;
									elseif(file_exists(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$module['slug'].'.php')) $status = 1;
									$actual_version = $module['vers'];
									if($status > 0){
										$data = get_plugin_data(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$module['slug'].'.php', false);
										$installed_version = $data['Version'];
										if(version_compare($installed_version, $actual_version) == -1) $color = 'color:#FF3B38';
										else $color = '';
										if( version_compare($data['Description'], RESERVATIONS_VERSION) == +1) $deprecated = array(true,$data['Description']);
										elseif(version_compare($data['Version'], $module['least']) == -1) $deprecated = array(false, $module['least']);

										if($status == 1){
											$action = '<form action="'.RESERVATIONS_URL.'lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="activate" value="'.$module['slug'].'"><a onclick="this.parentNode.submit();" href="#">'.__('Activate', 'easyReservations').'</a></form>';
											$action .= ' <a href="admin.php?page=reservation-settings&tab=plugins&delete='.$module['slug'].'" style="color:#ff5954">'.ucfirst(__('delete', 'easyReservations')).'</a>';
										} else $action = '<form action="'.RESERVATIONS_URL.'lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="deactivate" value="'.$module['slug'].'"><a onclick="javascript:this.parentNode.submit()" href="#">'.__('Deactivate', 'easyReservations').'</a></form>';
										if($login === false){
											$action .= ' <a href="javascript:" onclick="get_changelog(\''.$module['slug'].'\');return true;" style="color:#1fa856">'.__('Changelog', 'easyReservations').'</a>';
											if(isset($module['updated'])) $new_update = true;
										}
									}
									if(!isset($module['beta']) || $status > 0){ ?>
									<tr class="<?php if($status != 2) echo 'inactive '; echo 'module_row_'.$module['slug']; ?>">
										<td style="text-align:center"><span class="fa fa-<?php echo$module['image']; ?>"></span></td>
										<td><b><a href="http://easyreservations.org/module/<?php echo $module['slug']; ?>/" target="_blank" style="font-size: 12px;font-weight: bold;"><?php echo $module['title'];?></a></b><br><?php echo $action; ?></td>
										<td><?php 
											if($deprecated){
												if($deprecated[0]) $message = sprintf( __('Incompatibility - Update easyReservations to at least %s','easyReservations'), $deprecated[1]);
												else $message = sprintf( __('Incompatibility - This version of easyReservations needs at least version %s of this module','easyReservations'), $deprecated[1]);
												echo '<b style="color:#FF3B38">'.$message.'</b>';
											} else echo $module['content']; ?></td>
										<td style="font-weight:bold;text-align:center"><?php if($status > 0) echo '<a style="color:#118D18">'.$installed_version.'</a>'; else echo '<a style="color:#FF3B38">'.__('None', 'easyReservations').'</a>'; ?></td>
										<td style="text-align:center;font-weight:bold;<?php echo $color; ?>"><?php echo $actual_version; ?></td>
										<td style="font-weight:bold;text-align:right"><?php
											if($login === false){
												if($new_update) echo '<a class="easy-button green" href="admin.php?page=reservation-settings&tab=plugins&update='.$module['slug'].'">'.__('Update', 'easyReservations').'</a>';
												elseif($status !== 0) echo '<a class="easy-button green" href="admin.php?page=reservation-settings&tab=plugins&install='.$module['slug'].'">'.__('Reinstall', 'easyReservations').'</a>';
												else echo '<a class="easy-button green" href="admin.php?page=reservation-settings&tab=plugins&install='.$module['slug'].'">'. __('Install', 'easyReservations').'</a>';
											} else{
												if($status > 0) echo '<a class="easy-button" href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__('Download', 'easyReservations').'</a>';
												else echo '<a class="easy-button" href="http://easyreservations.org/module/'.$module['slug'].'/" target="_blank">'.__('Download', 'easyReservations').'</a>';
											} ?>
										</td>
									</tr><?php
										if($status == 2 && isset($module['addon']) && is_array($module['addon'])){
											foreach($module['addon'] as $addon){
												if(!isset($addon['beta']) || file_exists(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$addon['file'])){
													$status = 1;
													$deprecated = false;
													$data = get_plugin_data(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$addon['file'], false);
													$installed_version = $data['Version'];
													if(version_compare($installed_version, $addon['vers']) == -1) $color = 'color:#FF3B38';
													else $color = '';
													if( version_compare($data['Description'], RESERVATIONS_VERSION) == +1) $deprecated = array(true,$data['Description']);
													elseif(version_compare($data['Version'], $addon['least']) == -1) $deprecated = array(false, $addon['least']);
													if(function_exists($addon['function'])) $status = 2;
													$class = '';
													if($status != 2) $class .=  'inactive '; $class .= 'module_row_'.$addon['slug'];

													if($status == 1) $action = '<form action="'.RESERVATIONS_URL.'lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="activate" value="'.$addon['slug'].'"><a onclick="this.parentNode.submit();" href="#">'.__('Activate', 'easyReservations').'</a></form>';
													else $action = '<form action="'.RESERVATIONS_URL.'lib/core/activate.php" method="post" style="display:inline-block;"><input type="hidden" name="deactivate" value="'.$addon['slug'].'"><a onclick="javascript:this.parentNode.submit()" href="#">'.__('Deactivate', 'easyReservations').'</a></form>';

													echo '<tr class="'.$class.'"><td></td><td>'.$addon['title'].'<br>'.$action.'</td><td>';
													if($deprecated){
														if($deprecated[0]) $message = sprintf( __('Incompatibility - Update easyReservations to at least %s','easyReservations'), $deprecated[1]);
														else $message = sprintf( __('Incompatibility - This version of easyReservations needs at least version %s of this module','easyReservations'), $deprecated[1]);
														echo '<b style="color:#FF3B38">'.$message.'</b>';
													} else echo  $addon['content'];
													echo '</td><td style="font-weight:bold;text-align:center">';
													echo '<a style="color:#118D18">'.$installed_version.'</a></td><td style="text-align:center;font-weight:bold;'.$color.'">'.$addon['vers'].'</td><td style="font-weight:bold;text-align:right">';
													echo '<a class="easy-button" href="http://easyreservations.org/'.$addon['link'].'/" target="_blank">'.__('Download', 'easyReservations').'</a></td>';
													echo '</tr>';
												}
											}
										}
									}
								}
								echo '</table>'; ?>
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="min-width:300px;width:300px;margin-top:5px;margin-right:5px;float:left;text-align:left;">
						<thead>
							<tr>
								<th><?php if($login) _e('Premium Login', 'easyReservations'); else _e('Premium Features', 'easyReservations'); ?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td><?php if($login){?><input type="button" value="<?php _e('Login', 'easyReservations');?>" onclick="document.getElementById('reservation_prem_login').submit(); return false;" style="" class="easy-button" ><?php } ?></td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td style="text-align:center">
									<?php
										if(isset($login_error)) echo '<b style="color:#FF3B38">'.$login_error.'</b>';
										if($login){ ?>
										<form enctype="multipart/form-data"  action="admin.php?page=reservation-settings&tab=plugins" name="reservation_prem_login" id="reservation_prem_login" method="post" style="text-align:left">
											<p>
												<label for="prem_login"><?php _e('Username', 'easyReservations');?></label>
												<input type="text" name="arrival" id="prem_login" style="float:right;margin-bottom:4px;">
											</p>
											<p>
												<br><label for="prem_pw" style="float:left"><?php _e('Password', 'easyReservations');?></label>
												<input type="password" name="resource" id="prem_pw" style="float:right;padding:6px;width:168px">
											</p>
											<input type="hidden" name="prem_login" value="1">
										</form>
										<span style="display:inline-block;text-align:left;margin-top:10px;line-height: 22px">
											Login with your premium account to easily install and update your modules.<br>
										</span>
									<?php } else { ?>
										<p><a href="admin.php?page=reservation-settings&tab=plugins&install=all" class="easy-button green"><?php _e('Install all modules', 'easyReservations'); ?></a></p>
										<p>
											<form action="<?php echo RESERVATIONS_URL; ?>lib/core/activate.php" method="post" style="display:inline-block;">
												<input type="hidden" name="activate_all" value="bla">
												<a href="#" onclick="this.parentNode.submit();" class="easy-button"><?php _e('Activate all', 'easyReservations'); ?></a>
											</form>
											<form action="<?php echo RESERVATIONS_URL; ?>lib/core/activate.php" method="post" style="display:inline-block;">
												<input type="hidden" name="deactivate_all" value="bla">
												<a href="#" onclick="this.parentNode.submit();" class="easy-button grey"><?php _e('Deactivate all', 'easyReservations'); ?></a>
											</form>
										</p>
										<p><a href="admin.php?page=reservation-settings&tab=plugins&check" class="easy-button grey"><?php _e('Check for updates', 'easyReservations'); ?></a></p>
										<p><a href="admin.php?page=reservation-settings&tab=plugins&logout" class="easy-button grey"><?php _e('Logout', 'easyReservations'); ?></a></p>
										<p><?php _e('Last checked at', 'easyReservations');?>: <?php echo date(RESERVATIONS_DATE_FORMAT." H:i", (int) get_option( 'easyreservations-notifier-last-updated')); ?></p>
									<?php } ?>
								</td>
							</tr>
						</tbody>
					</table>
					<table class="<?php echo RESERVATIONS_STYLE; ?> easy-modules-table" style="width:200px;margin-top:5px">
						<thead>
							<tr>
								<th><?php _e('Install or update module manually', 'easyReservations');?></th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<td>
									<input type="button" value="<?php _e('Install', 'easyReservations');?>" onclick="document.getElementById('reservation_core_upload').submit(); return false;" class="easy-button">
								</td>
							</tr>
						</tfoot>
						<tbody>
							<tr>
								<td>
									<form enctype="multipart/form-data"  action="admin.php?page=reservation-settings&tab=plugins" name="reservation_core_upload" id="reservation_core_upload" method="post">
										<p>
											<?php do_action('er_mod_inst'); ?>
										</p>
										<input type="hidden" name="action" value="reservation_core_upload_plugin">
										<input type="hidden" name="max_file_size" value="1000000000">
										<input name="reservation_core_upload_file" type="file" size="50" maxlength="100000" accept="text/*"><br>
									</form>
								</td>
							</tr>
						</tbody>
					</table>
					<style>
						#changlog_td { border-top:0;}
						#changlog_td ul {
							line-height: 1.2em;
							list-style: disc !important;
							padding-left: 30px;
						} #changlog_td li {
							list-style-type: circle;
						}
					</style>
				<?php if(!$login){ ?>
						<script>
							is_changelog = false;

							function get_changelog(module){
								if(is_changelog){
									jQuery('#changlog_tr').remove();
									if(module == is_changelog){
										jQuery('.module_row_'+is_changelog+' td').css('border-bottom', '0');
										is_changelog = false;
										return true;
									}
									is_changelog = false;
								}
								is_changelog = module;
								jQuery('.module_row_'+module+' td').css('border-bottom', '0');
								jQuery('<tr id="changlog_tr"><td></td><td colspan="5" id="changlog_td"><img src="<?php echo RESERVATIONS_URL; ?>assets/images/loading.gif"></td></tr>').insertAfter('.module_row_'+module);
								var req = jQuery.ajax({
									url: 'admin.php?page=reservation-settings&tab=plugins&changelog='+module,
									success: function(data){
										changelog = jQuery(data).find('#the_modules_changelog').html();
										is_changelog = module;
										jQuery('#changlog_td').html(changelog);
									}
								});
								req.error(function(error,textStatus, errorThrown) {
									jQuery('.module_row_'+module+' td').css('border-bottom', '1px');
									jQuery('#changlog_tr').remove();
									is_changelog = false;
									alert(errorThrown);
								});
							}
						</script>
				<?php
				}
			}
		}

		function easyreservations_latest_modules_versions($interval, $modules = false, $onload = false, $update = false, $changelog = false){
			if(!$modules) $modules = easyreservations_load_modules_array();
			$login = get_option('reservations_login');
			$error = '';
			if($login !== false && !empty($login)){
				$notifier_file_url = 'http://easyreservations.org/req/modules/';
				$db_cache_field = 'easyreservations-notifier-cache';
				$db_cache_field_last_updated = 'easyreservations-notifier-last-updated';
				$last = get_option( $db_cache_field_last_updated );
				if($update || !$last || (( current_time( 'timestamp' ) - $last ) > $interval) || $interval == 0){
					$explode= explode('$%!$&', $login);
					if( function_exists('curl_init')){ // if cURL is available, use it...
						if($update){
							$notifier_file_url =  'http://easyreservations.org/req/down/'.$update;
							$url = 'admin.php?page=reservation-settings&tab=plugins&update='.$update;
							if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false))){
								return 'creds';
							} elseif ( ! WP_Filesystem($creds) ) {
								request_filesystem_credentials($url, 'ftp', true, false);
								return 'creds';
							}
						} elseif($changelog){
							$notifier_file_url =  'http://easyreservations.org/req/change/'.$changelog;
						}
						$ch = curl_init($notifier_file_url);
						curl_setopt($ch, CURLOPT_URL, $notifier_file_url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Accept: application/json', 'Content-Type: json'));
						curl_setopt($ch, CURLOPT_TIMEOUT, 10);
						curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ); //CURLAUTH_DIGEST
						curl_setopt($ch, CURLOPT_USERPWD, $explode[0]. ':' .$explode[1]);
						$cache = curl_exec($ch);
						$responseInfo	= curl_getinfo($ch);
						if($responseInfo['http_code'] == "401"){
							update_option('reservations_login', '');
							return __('Wrong login data', 'easyReservations');
						} elseif($responseInfo['http_code'] == "403") return sprintf(__('No premium account - %s','easyReservations'), '<a target="_blank" href="http://easyreservations.org/premium/">order here</a>');
					} else {
						return __('cURL isnt installed on your server, please contact your host', 'easyReservations');
					}
					if($update && empty($error)){
						$newfile = RESERVATIONS_ABSPATH.'tmp_file_dasd.zip';
						$url = 'admin.php?page=reservation-settings&tab=plugins&update='.$update;
						if (false === ($creds = request_filesystem_credentials($url, 'ftp', false, false))){
							return 'creds';
						} elseif ( ! WP_Filesystem($creds) ) {
							request_filesystem_credentials($url, 'ftp', true, false);
							return 'creds';
						} else {
							if(!$das = file_put_contents($newfile, $cache)) return sprintf(__('Failure at copying module %1$s - %2$s', 'easyReservations'), '<b>'.$_GET['update'].'</b>', '<a href="http://easyreservations.org/module/'.$_GET['update'].'">update manually</a>');
							if(class_exists('ZipArchive')){
								$zip = new ZipArchive();  
								$x = $zip->open($newfile);
								if($x === true){
									$copy = $zip->extractTo(RESERVATIONS_ABSPATH);
									$zip->close();
									unlink($newfile);
									return $copy;
								} else {
									$zip = unzip_file($newfile, RESERVATIONS_ABSPATH);
									unlink($newfile);
									return $zip;
								}
							} else {
								$zip = unzip_file($newfile, RESERVATIONS_ABSPATH);
								unlink($newfile);
								return $zip;
							}
							return false;
						}
					} elseif($changelog){
						return $cache;
					} elseif ($cache){
						update_option( $db_cache_field, $cache );
						update_option( $db_cache_field_last_updated, current_time( 'timestamp' ));
					}
			}
			if($onload && !empty($error)) return $error;
			elseif(!$onload && !empty($error)) return false;
			$notifier_data = get_option( $db_cache_field );

			if($notifier_data && !empty($notifier_data)){
				$xml = json_decode($notifier_data);
				$changes = array();
				if($xml && !empty($xml) && $xml != null && is_array($xml)){
					foreach($xml as $module){
						if($module->name == 'chat') $module->name = 'useredit';
						if(file_exists(RESERVATIONS_ABSPATH.'lib/modules/'.$module->name.'/'.$module->name.'.php')) {
							$modules[$module->name]['vers'] = $module->version;
							$modules[$module->name]['update'] = $module->update;
							$data = get_plugin_data(RESERVATIONS_ABSPATH.'lib/modules/'.$module->name.'/'.$module->name.'.php', false);
							if(version_compare($data['Version'], $module->version) == -1){
								$modules[$module->name]['updated'] = $module->update;
								$changes[$module->name] = array($module->name, $module->version);
							}
						}
					}
				}

				if($onload){
					return $modules;
				} else {
					if(!empty($changes)){
						return $changes;
					} else return false;
				}
			} else return false;
		} elseif($onload) return $modules;
		return false;
	}
}
	function easyreservations_is_module($module = false){
		$reservations_active_modules = get_option('reservations_active_modules');
		if(file_exists(RESERVATIONS_ABSPATH."lib/modules/$module/$module.php") && is_array($reservations_active_modules) && in_array($module, $reservations_active_modules)) return true;
		else return false;
	}

	function easyreservations_activate_module($module){
		$active = get_option('reservations_active_modules');
		$active[] = $module;
		update_option("reservations_active_modules",$active);
	}

	function easyreservations_deactivate_module($module){
		$active = get_option('reservations_active_modules');
		if(!empty($active)){
			foreach($active as $key => $mod){
				if($mod == $module){
					unset($active[$key]);
					break;
				}
			}
		}
		update_option("reservations_active_modules", $active);
	}

	function easyreservations_modules_check_incompatibility(){
		$changes = easyreservations_latest_modules_versions(86400,false,true);
		$deprecated_plugin = ''; $deprecated_modules = '';
		if($changes && !empty($changes) && is_array($changes)){
		  foreach($changes as $module){
			if(file_exists(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$module['slug'].'.php')){
			  $deprecated = false;
			  $data = get_plugin_data(RESERVATIONS_ABSPATH.'lib/modules/'.$module['slug'].'/'.$module['slug'].'.php', false);
			  if( version_compare($data['Description'], RESERVATIONS_VERSION) == +1) $deprecated = array(true,$data['Description']);
			  elseif(version_compare($data['Version'], $module['least']) == -1) $deprecated = array(false, $module['least']);
			  if($deprecated){
					if($deprecated[0]){
					  $deprecated_plugin .=  '<li>'.sprintf( __('Module %1$s is incompatible to easyReservations %2$s - update to version %3$s','easyReservations'), $module['title'], RESERVATIONS_VERSION, $deprecated[1]) . '</li>';
					} else {
					  $deprecated_modules .=  '<li>easyReservations '.sprintf( __('%1$s is incompatible to %2$s %3$s - update at least to version %4$s','easyReservations'), RESERVATIONS_VERSION, $module['title'], $data['Version'], $deprecated[1]) . '</li>';
					}
			  }
			}
		  }
		  if(!empty($deprecated_plugin)){
				$file = 'easyreservations/easyReservations.php';
				$deprecated_plugin = '<h2>Update easyReservations</h2><ul>'.$deprecated_plugin.'</ul><strong><a href="'.wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file).'">Update easyReservations now</a></strong>';
		  }
		  if(!empty($deprecated_modules)){
				$deprecated_modules = '<h2>Incompatible Modules</h2><ul style="list-style:disc;padding-left:30px">'.$deprecated_modules.'</ul><strong><a href="admin.php?page=reservation-settings&tab=plugins&update=all">Update modules now</a></strong>';
		  }
		  if(!empty($deprecated_plugin) || !empty($deprecated_modules)) ER()->messages()->add_error($deprecated_plugin.$deprecated_modules);
		}
	}

	add_action('easy-header', 'easyreservations_modules_check_incompatibility' );
?>