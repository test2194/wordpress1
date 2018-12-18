<?php
/**
 * Created by PhpStorm.
 * User: feryaz
 * Date: 04.09.2018
 * Time: 12:56
 */

//Prevent direct access to file
if( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ER_Admin_Resources {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$resource_id = false;
		if( isset( $_GET['resource'] ) ) {
			$resource_id = intval( $_GET['resource'] );
		}

		if( isset( $_GET['delete'] ) && check_admin_referer( 'easy-resource-delete' ) ) {
			$this->delete_resource( intval( $_GET['delete'] ) );
		} elseif( isset( $_POST['add_resource_title'] ) && check_admin_referer( 'easy-resource-add', 'easy-resource-add' ) ) {
			$this->add_resource();
		} elseif( isset( $_POST['resource_spaces'] ) && $resource_id ) {
			$this->save_resource_spaces_names( $resource_id );
		} elseif( isset( $_POST['base_price'] ) && $resource_id && check_admin_referer( 'easy-resource-settings', 'easy-resource-settings' ) ) {
			$this->save_resource_settings( $resource_id );
		} elseif( isset( $_POST['filter_form_name_field'] ) && $resource_id && check_admin_referer( 'easy-resource-filter', 'easy-resource-filter' ) ) {
			$this->save_resource_filter( $resource_id );
		} elseif( isset( $_GET['delete_filter'] ) && $resource_id && check_admin_referer( 'easy-resource-delete-filter' ) ) {
			$this->delete_resource_filter( $resource_id );
		} elseif( isset( $_POST['slot_range_from'] ) && $resource_id && check_admin_referer( 'easy-resource-slot' ) ) {
			$this->save_resource_slot( $resource_id );
		} elseif( isset( $_GET['delete_slot'] ) && $resource_id && check_admin_referer( 'easy-resource-delete-slot' ) ) {
			$this->delete_resource_slot( $resource_id );
		}
	}

	public static function output() {

		if( isset( $_GET['add_resource'] ) ) {
			//Add new resource
			include 'views/html-admin-resources-header.php';

			include 'views/html-admin-resource-add.php';

		} elseif( isset( $_GET['resource'] ) ) {
			//Edit resource
			self::output_resource_page( intval( $_GET['resource'] ) );

		} else {
			//Resources list
			include 'views/html-admin-resources-header.php';

			include 'views/html-admin-resources.php';
		}
	}

	public static function output_resource_page( $id ) {

		$resource = ER()->resources()->get( $id );
		if( ! empty( $resource->permission ) && ! current_user_can( $resource->permission ) ) {
			die( 'You do not have the required permission to access this reservation' );
		}

		wp_enqueue_style( 'datestyle' );

		$count        = 0;
		$days         = er_date_get_label();
		$hour_string  = __( 'From %1$s till %2$s', 'easyReservations' );
		$thumbnail    = get_the_post_thumbnail( $resource->ID, array( 150, 150 ) );
		$spaces_names = get_post_meta( $resource->ID, 'easy-resource-roomnames', true );
		$slots        = get_post_meta( $id, 'easy-resource-slots', true );

		$all_filter = get_post_meta( $resource->ID, 'easy_res_filter', true );
        if($all_filter && !empty($all_filter)){
            foreach( $all_filter as $key => $filter ) {
                $filter['name'] = addslashes( $filter['name'] );
                if( isset( $filter['from'] ) ) {
                    $filter['from_str'] = date( "F d, Y G:i:s", $filter['from'] );
                }
                if( isset( $filter['to'] ) ) {
                    $filter['to_str'] = date( "F d, Y G:i:s", $filter['to'] );
                }
                if( isset( $filter['date'] ) ) {
                    $filter['date_str'] = date( "F d, Y G:i:s", $filter['date'] );
                }
                $all_filter[ $key ] = $filter;
            }
        } else {
            $all_filter = array();
        }

		include 'views/html-admin-resource-index.php';
	}

	/**
	 * Delete resource
	 *
	 * @param int $id
	 *
	 * @return bool
	 */
	private function delete_resource( $id ) {
		global $wpdb;

		if( is_integer( $id ) && wp_delete_post( $id ) ) {
			$delete_reservations = $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "reservations WHERE resource=%s", $id ) );
			if( $delete_reservations ) {
				ER()->messages()->add_success( __( 'Resource and all its reservations deleted', 'easyReservations' ) );

				return true;
			} else {
				ER()->messages()->add_error( __( 'Reservations could not be deleted', 'easyReservations' ) );

				return false;
			}
		} else {
			ER()->messages()->add_error( __( 'Resource could not be deleted', 'easyReservations' ) );

			return false;
		}
	}

	/**
	 * Add resource
	 */
	private function add_resource() {
		$title    = sanitize_text_field( $_POST['add_resource_title'] );
		$content  = sanitize_text_field( $_POST['add_resource_content'] );
		$filename = esc_url_raw( $_POST['upload_image'] );

		if( isset( $_POST['dopy'] ) ) {
			$res = new ER_Resource( $_POST['dopy'] );
			try {
				$res->title   = $title;
				$res->content = $content;
				$res->add_resource();

				ER()->messages()->add_success( sprintf( __( 'Resource #%d added', 'easyReservations' ), $res->id ) );
				wp_redirect( admin_url() . 'admin.php?page=reservation-resources&resource=' . $res->id );
				exit();
			} catch( Exception $e ) {
				ER()->messages()->add_error( $e->getMessage() );
			}
		} elseif( ! empty( $title ) ) {
			$resource = array(
				'post_title'   => $title,
				'post_content' => $content,
				'post_status'  => 'private',
				'post_type'    => 'easy-rooms'
			);

			$new_id = wp_insert_post( $resource );
			add_post_meta( $new_id, 'roomcount', '1', true );
			add_post_meta( $new_id, 'reservations_groundprice', 0, true );
			add_post_meta( $new_id, 'easy-resource-interval', 86400, true );

			if( $filename != '' ) {
				$wp_filetype = wp_check_filetype( basename( $filename ), null );
				$attachment  = array(
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
					'post_content'   => '',
					'post_status'    => 'inherit'
				);
				$attach_id   = wp_insert_attachment( $attachment, $filename, $new_id );
				require_once( ABSPATH . 'wp-admin/includes/image.php' );
				$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
				wp_update_attachment_metadata( $attach_id, $attach_data );
				add_post_meta( $new_id, '_thumbnail_id', $attach_id, true );
			}

			ER()->messages()->add_success( sprintf( __( 'Resource #%d added', 'easyReservations' ), $new_id ) );
			wp_redirect( admin_url() . 'admin.php?page=reservation-resources&resource=' . $new_id );
			exit();
		} else {
			ER()->messages()->add_error( sprintf( __( 'Please enter %s', 'easyReservations' ), __( 'a title', 'easyReservations' ) ) );
		}
	}

	/**
	 * Save resource spaces names
	 */
	private function save_resource_spaces_names( $id ) {
		update_post_meta( $id, 'easy-resource-roomnames', array_map( 'sanitize_text_field', $_POST['resource_spaces'] ) );
		ER()->messages()->add_success( sprintf(__('%s saved', 'easyReservations'), __('Resource spaces names', 'easyReservations') ) );
	}

	/**
	 * Save resource settings
	 */
	private function save_resource_settings( $id ) {
		ER()->messages()->add_success( sprintf( __( '%s settings saved', 'easyReservations' ), __( 'Resource', 'easyReservations' ) ) );

		$reservations_current_req = get_post_meta( $id, 'easy-resource-req', true );

		$arrival_possible_on   = array_map( 'intval', $_POST['resource_requirements_start_on'] );
		$departure_possible_on = array_map( 'intval', $_POST['resource_requirements_end_on'] );
		if( empty( $arrival_possible_on ) ) {
			ER()->messages()->add_error( __( 'No arrival possible', 'easyReservations' ) );
		}
		if( empty( $departure_possible_on ) ) {
			ER()->messages()->add_error( __( 'No departure possible', 'easyReservations' ) );
		}
		if( count( $arrival_possible_on ) == 7 ) {
			$arrival_possible_on = 0;
		}
		if( count( $departure_possible_on ) == 7 ) {
			$departure_possible_on = 0;
		}

		$req = array(
			'nights-min' => $_POST['easy-resource-min-nights'],
			'nights-max' => $_POST['easy-resource-max-nights'],
			'pers-min'   => $_POST['easy-resource-min-pers'],
			'pers-max'   => $_POST['easy-resource-max-pers'],
			'start-on'   => $arrival_possible_on,
			'end-on'     => $departure_possible_on
		);
		if( $_POST['start-h0'] !== '0' || $_POST['start-h1'] !== '23' ) {
			$req['start-h'] = array( $_POST['start-h0'], $_POST['start-h1'] );
		}
		if( $_POST['end-h0'] !== '0' || $_POST['end-h1'] !== '23' ) {
			$req['end-h'] = array( $_POST['end-h0'], $_POST['end-h1'] );
		}
		if( $reservations_current_req != $req && ! ER()->messages()->has_error() ) {
			update_post_meta( $id, 'easy-resource-req', $req );
		}

		$base_price = er_check_money( $_POST['base_price'] );
		do_action( 'edit_resource', $id, $base_price );

		if( $base_price !== get_post_meta( $id, 'reservations_groundprice', true ) ) {
			if( is_numeric( $base_price ) && $base_price > 0 ) {
				update_post_meta( $id, 'reservations_groundprice', $base_price );
			} else {
				ER()->messages()->add_error( __( 'Insert correct money format', 'easyReservations' ) );
			}
		}

		$quantity = intval( $_POST['quantity'] );
		if( isset( $_POST['availability_by'] ) && $_POST['availability_by'] !== 'unit' ) {
			$quantity = array( $quantity, sanitize_text_field( $_POST['availability_by'] ) );
		}

		if( $quantity !== get_post_meta( $id, 'roomcount', true ) ) {
			if( is_numeric( $_POST['quantity'] ) ) {
				update_post_meta( $id, 'roomcount', $quantity );
			} else {
				ER()->messages()->add_error( sprintf(__('%s has to be numeric', 'easyReservations'), __('Resource space', 'easyReservations')) );
			}
		}

        update_post_meta( $id, 'er_resource_frequency', intval( $_POST['er_resource_frequency'] ) );

		$reservations_billing_method = intval( $_POST['billing-method'] );
		if( $reservations_billing_method !== get_post_meta( $id, 'easy-resource-billing-method', true ) ) {
			update_post_meta( $id, 'easy-resource-billing-method', $reservations_billing_method );
		}

		$reservations_current_price_set = get_post_meta( $id, 'easy-resource-price', true );
		if( isset( $_POST['easy-resource-price'] ) ) {
			$reservations_res_price_set = 1;
		} else {
			$reservations_res_price_set = 0;
		}
		if( isset( $_POST['easy-resource-once'] ) ) {
			$reservations_res_price_once = 1;
		} else {
			$reservations_res_price_once = 0;
		}

		if( ! isset( $reservations_current_price_set[0] ) || $reservations_current_price_set[0] != $reservations_res_price_set || $reservations_current_price_set[1] != $reservations_res_price_once ) {/* SET PRICE SETTINGS */
			update_post_meta( $id, 'easy-resource-price', array(
				$reservations_res_price_set,
				$reservations_res_price_once
			) );
		}

		$reservations_current_perm = get_post_meta( $id, 'easy-resource-permission', true );
		$reservations_res_perm     = $_POST['er_resource_permission'];
		if( $reservations_current_perm != $reservations_res_perm ) {/* SET PRICE SETTINGS */
			if( current_user_can( 'manage_options' ) ) {
				update_post_meta( $id, 'easy-resource-permission', $reservations_res_perm );
			} else {
				ER()->messages()->add_error( __( 'Only admins can change the permissions for', 'easyReservations' ) . ' ' . __( 'Resources', 'easyReservations' ) );
			}
		}

		$reservations_res_interval = intval( $_POST['er_resource_interval'] );
		if( $reservations_billing_method == 3 ) {
			$reservations_res_interval = 86400;
		}
		if( $reservations_res_interval !== get_post_meta( $id, 'easy-resource-interval', true ) ) {/* SET PRICE SETTINGS */
			update_post_meta( $id, 'easy-resource-interval', $reservations_res_interval );
		}

		$children_price = er_check_money( $_POST['child_price'] );
		if( $children_price !== get_post_meta( $id, 'reservations_child_price', true ) ) {
			if( $children_price !== 'error' ) {
				update_post_meta( $id, 'reservations_child_price', $children_price );
			} else {
				ER()->messages()->add_error( 'Children price has to be numeric' );
			}
		}

		if( isset( $_POST['res_tax_names'] ) && ! empty( $_POST['res_tax_names'] ) && isset( $_POST['res_tax_amounts'] ) && ! empty( $_POST['res_tax_amounts'] ) ) {
			$taxes = array();
			$sort  = array();
			foreach( $_POST['res_tax_names'] as $key => $tax ) {
				if( is_numeric( $_POST['res_tax_amounts'][ $key ] ) ) {
					$taxes[] = array(
						$_POST['res_tax_names'][ $key ],
						$_POST['res_tax_amounts'][ $key ],
						$_POST['res_tax_class'][ $key ]
					);
					if( $_POST['res_tax_class'][ $key ] == 0 ) {
						$sort[] = 2;
					} elseif( $_POST['res_tax_class'][ $key ] == 1 ) {
						$sort[] = 0;
					} elseif( $_POST['res_tax_class'][ $key ] == 2 ) {
						$sort[] = 1;
					}
				} else {
					ER()->messages()->add_error( __( 'Tax percentage has to be numeric', 'easyReservations' ) );
					$taxes[] = 'error';
					break;
				}
			}
			if( ! empty( $taxes ) && $taxes[0] !== 'error' ) {
				array_multisort( $sort, $taxes );
				update_post_meta( $id, 'easy-resource-taxes', $taxes );
			}
		} else {
			//empty taxes
			update_post_meta( $id, 'easy-resource-taxes', array() );
		}

		do_action( 'easy_resource_save', $id );
	}

	private function save_resource_filter( $id ) {
		if( ! empty( $_POST['filter_form_name_field'] ) ) {
			$type   = sanitize_text_field( $_POST['filter_type'] );
			$filter = array();

			if( $type == 'price' ) {
				$filter['type'] = 'price';
				if( isset( $_POST['filter-price-field'] ) ) {
					$filter['price'] = $_POST['filter-price-field'];
				}
				if( isset( $_POST['filter-children-price'] ) && ! empty( $_POST['filter-children-price'] ) ) {
					$filter['children-price'] = $_POST['filter-children-price'];
				}
				if( isset( $_POST['price_filter_imp'] ) ) {
					$filter['imp'] = $_POST['price_filter_imp'];
				}
				if( $_POST['filter-price-mode'] == 'baseprice' ) {
					$time_condition_id = 'cond';
					$condition_id      = 'basecond';
					$type_id           = 'condtype';
					$filter['type']    = 'price';
				} else {
					$time_condition_id = 'timecond';
					$condition_id      = 'cond';
					$type_id           = 'type';
					if( ! isset( $_POST['filter_form_condition_checkbox'] ) ) {
						if( $filter['price'] >= 0 ) {
							$filter['type'] = 'charge';
						} else {
							$filter['type'] = 'discount';
						}
					}
				}
				if( isset( $_POST['filter_form_condition_checkbox'] ) ) {
					$filter[ $type_id ]      = $_POST['filter_form_discount_type'];
					$filter[ $condition_id ] = $_POST['filter_form_discount_cond'];
					$filter['modus']         = $_POST['filter_form_discount_mode'];
				}
			} elseif( $type == 'unavail' ) {
				$time_condition_id = 'cond';
				$filter['type']    = 'unavail';
			} elseif( $type == 'req' ) {
				$time_condition_id = 'cond';
				$filter['type']    = 'req';
			}

			$filter['name'] = $_POST['filter_form_name_field'];
			if( ( $type == 'price' && isset( $_POST['filter_form_usetime_checkbox'] ) ) || $type == 'unavail' || $type == 'req' ) {
				if( isset( $_POST['price_filter_cond_range'] ) ) {
					$filter[ $time_condition_id ] = 'range';
					if( isset( $_POST['price_filter_range_from'] ) && ! empty( $_POST['price_filter_range_from'] ) ) {
						$from           = strtotime( $_POST['price_filter_range_from'] ) + ( ( (int) $_POST['price-filter-range-from-hour'] * 60 ) + (int) $_POST['price-filter-range-from-min'] ) * 60;
						$filter['from'] = $from;
					} else {
						ER()->messages()->add_error( __( 'Enter a starting date for the filter', 'easyReservations' ) );
					}
					if( isset( $_POST['price_filter_range_to'] ) && ! empty( $_POST['price_filter_range_to'] ) ) {
						$to           = strtotime( $_POST['price_filter_range_to'] ) + ( ( (int) $_POST['price-filter-range-to-hour'] * 60 ) + (int) $_POST['price-filter-range-to-min'] ) * 60;
						$filter['to'] = $to;
					} else {
						ER()->messages()->add_error( __( 'Enter an ending date for the filter', 'easyReservations' ) );
					}
					if( isset( $_POST['price_filter_range_every'] ) ) {
						$filter['every'] = 1;
					}
				}
				if( isset( $_POST['price_filter_cond_unit'] ) ) {
					$filter[ $time_condition_id ] = 'unit';
					if( isset( $_POST['price_filter_unit_year'] ) ) {
						$filter['year'] = implode( ',', $_POST['price_filter_unit_year'] );
					}
					if( isset( $_POST['price_filter_unit_quarter'] ) ) {
						$filter['quarter'] = implode( ',', $_POST['price_filter_unit_quarter'] );
					}
					if( isset( $_POST['price_filter_unit_month'] ) ) {
						$filter['month'] = implode( ',', $_POST['price_filter_unit_month'] );
					}
					if( isset( $_POST['price_filter_unit_cw'] ) ) {
						$filter['cw'] = implode( ',', $_POST['price_filter_unit_cw'] );
					}
					if( isset( $_POST['price_filter_unit_days'] ) ) {
						$filter['day'] = implode( ',', $_POST['price_filter_unit_days'] );
					}
					if( isset( $_POST['price_filter_unit_hour'] ) ) {
						$filter['hour'] = implode( ',', $_POST['price_filter_unit_hour'] );
					}
				}
				if( ! isset( $_POST['price_filter_cond_range'] ) && ! isset( $_POST['price_filter_cond_unit'] ) ) {
					ER()->messages()->add_error( sprintf(__('Select %s', 'easyReservations'), __('condition', 'easyReservations')) );
				}
			}
			if( $type == 'req' ) {
				if( ! isset( $_POST['req_filter_start_on'] ) ) {
					$_POST['req_filter_start_on'] = 8;
				} elseif( count( $_POST['req_filter_start_on'] ) == 7 ) {
					$_POST['req_filter_start_on'] = 0;
				}
				if( ! isset( $_POST['req_filter_end_on'] ) ) {
					$_POST['req_filter_end_on'] = 8;
				} elseif( count( $_POST['req_filter_end_on'] ) == 7 ) {
					$_POST['req_filter_end_on'] = 0;
				}
				$filter['req'] = array(
					'pers-min'   => $_POST['req_filter_min_pers'],
					'pers-max'   => $_POST['req_filter_max_pers'],
					'nights-min' => $_POST['req_filter_min_nights'],
					'nights-max' => $_POST['req_filter_max_nights'],
					'start-on'   => $_POST['req_filter_start_on'],
					'end-on'     => $_POST['req_filter_end_on']
				);
				if( $_POST['filter-start-h0'] !== '0' || $_POST['filter-start-h1'] !== '23' ) {
					$filter['req']['start-h'] = array( $_POST['filter-start-h0'], $_POST['filter-start-h1'] );
				}
				if( $_POST['filter-end-h0'] !== '0' || $_POST['filter-end-h1'] !== '23' ) {
					$filter['req']['end-h'] = array( $_POST['filter-end-h0'], $_POST['filter-end-h1'] );
				}
			}

			$filters = get_post_meta( $id, 'easy_res_filter', true );
			if( ! isset( $filters ) || empty( $filters ) || ! $filters ) {
				$filters = array();
			}

			if( isset( $_POST['price_filter_edit'] ) && isset( $filters[ $_POST['price_filter_edit'] ] ) ) {
				//Remove existing filter
				unset( $filters[ $_POST['price_filter_edit'] ] );
			}

			//Add new filter
			$filters[] = $filter;

			$p1filters = array();
			$pfilters = array();
			$d1filters = array();
			$d2filters = array();
			$dfilters = array();
			$ufilters = array();

			//Pre sort all filters so we can just iterate in calculation
			foreach( $filters as $key => $filter ) {
				if( $filter['type'] == 'unavail' || $filter['type'] == 'req' ) {
					$ufilters[]     = $filter;
					$ufiltersSort[] = $filter['type'];
				} else {
					if( $filter['type'] == 'price' && isset( $filter['basecond'] ) ) {
						$p1filters[]           = $filter;
						$p1sortArray[ $key ]   = $filter['imp'];
						$p1dsortArray[ $key ]  = $filter['basecond'];
						$p1dtsortArray[ $key ] = $filter['condtype'];
					} elseif( $filter['type'] == 'price' ) {
						$pfilters[]         = $filter;
						$psortArray[ $key ] = $filter['imp'];
					} elseif( isset( $filter['timecond'] ) && isset( $filter['cond'] ) ) {
						$d1filters[]          = $filter;
						$d1isortArray[ $key ] = $filter['imp'];
						$d1sortArray[ $key ]  = $filter['cond'];
						$d1tsortArray[ $key ] = $filter['type'];
					} elseif( isset( $filter['timecond'] ) ) {
						$d2filters[]          = $filter;
						$d2isortArray[ $key ] = $filter['imp'];
					} else {
						$dfilters[]          = $filter;
						$dsortArray[ $key ]  = $filter['cond'];
						$dtsortArray[ $key ] = $filter['type'];
					}
				}
			}
			if( isset( $p1sortArray ) ) {
				array_multisort( $p1sortArray, SORT_ASC, SORT_NUMERIC, $p1dtsortArray, SORT_ASC, $p1dsortArray, SORT_DESC, SORT_NUMERIC, $p1filters );
			}
			if( isset( $psortArray ) ) {
				array_multisort( $psortArray, SORT_ASC, SORT_NUMERIC, $pfilters );
			}
			if( isset( $d1tsortArray ) ) {
				array_multisort( $d1isortArray, SORT_ASC, $d1tsortArray, SORT_ASC, $d1sortArray, SORT_DESC, SORT_NUMERIC, $d1filters );
			}
			if( isset( $d2isortArray ) ) {
				array_multisort( $d2isortArray, SORT_DESC, SORT_NUMERIC, $d2filters );
			}
			if( isset( $dtsortArray ) ) {
				array_multisort( $dtsortArray, SORT_ASC, $dsortArray, SORT_DESC, SORT_NUMERIC, $dfilters );
			}
			if( isset( $ufiltersSort ) ) {
				array_multisort( $ufiltersSort, SORT_ASC, $ufilters );
			}
			$filters = array_merge( $p1filters, $pfilters, $d1filters, $d2filters, $dfilters, $ufilters );

			if( ! ER()->messages()->has_error() ) {
				update_post_meta( $id, 'easy_res_filter', $filters );
			}
		} else {
			ER()->messages()->add_error( sprintf(__('Please enter %s', 'easyReservations'), __('a name', 'easyReservations')) );
		}
	}

	/**
	 * Delete's certain filter
	 */
	private function delete_resource_filter( $id ) {
		$filters = get_post_meta( $id, 'easy_res_filter', true );
		unset( $filters[ intval( $_GET['delete_filter'] ) ] );
		update_post_meta( $id, 'easy_res_filter', $filters );
	}

	private function save_resource_slot( $id ) {
		if( empty( $_POST['slot_range_from'] ) || empty( $_POST['slot_range_to'] ) ) {
			ER()->messages()->add_error( sprintf( __( 'Please enter %s', 'easyReservations' ), __( 'arrival and departure as valid date format', 'easyReservations' ) ) );

			return;
		}
		if( ! isset( $_POST['slot_days'] ) || empty( $_POST['slot_days'] ) ) {
			ER()->messages()->add_error( sprintf( __( 'Please select %s', 'easyReservations' ), __( 'days for arrival', 'easyReservations' ) ) );

			return;
		}
		$slot = array(
			'name'           => sanitize_text_field( $_POST['slot_name'] ),
			'range-from'     => ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field( $_POST['slot_range_from'] ) . ' 00:00:00' )->getTimestamp(),
			'range-to'       => ER_DateTime::createFromFormat( RESERVATIONS_DATE_FORMAT . ' H:i:s', sanitize_text_field( $_POST['slot_range_to'] ) . ' 00:00:00' )->getTimestamp(),
			'repeat'         => intval( $_POST['slot_repeat_amount'] ),
			'repeat-break'   => intval( $_POST['slot_repeat_break'] ),
			'days'           => array_map( 'intval', $_POST['slot_days'] ),
			'from'           => intval( $_POST['slot-from-hour'] ) * 60 + intval( $_POST['slot-from-min'] ),
			'to'             => intval( $_POST['slot-to-hour'] ) * 60 + intval( $_POST['slot-to-min'] ),
			'duration'       => intval( $_POST['slot_duration'] ),
			'adults-min'     => isset($_POST['slot_min_adults']) ? intval( $_POST['slot_min_adults'] ) : 0,
			'adults-max'     => isset($_POST['slot_max_adults']) ? intval( $_POST['slot_max_adults'] ) : 0,
			'children-min'   => isset($_POST['slot_min_children']) ? intval( $_POST['slot_min_children'] ) : 0,
			'children-max'   => isset($_POST['slot_max_children']) ? intval( $_POST['slot_max_children'] ) : 0,
			'base-price'     => floatval( $_POST['slot_base_price'] ),
			'children-price' => floatval( $_POST['slot_children_price'] ),
		);

		if( empty( $slot['name'] ) ) {
			ER()->messages()->add_error( sprintf( __( 'Please enter %s', 'easyReservations' ), __( 'a name', 'easyReservations' ) ) );
		}

		if( ! ER()->messages()->has_error() ) {
			$slots = get_post_meta( $id, 'easy-resource-slots', true );
			if( ! empty( $_POST['slot_edit'] ) ) {
				unset( $slots[ intval( $_POST['slot_edit'] ) - 1 ] );
				ER()->messages()->add_success( sprintf(__('%s edited', 'easyReservations'), __('Slot', 'easyReservations')) );
			} else {
				ER()->messages()->add_success( sprintf(__('%s added', 'easyReservations'), __('Slot', 'easyReservations')) );
			}
			if( empty( $slots ) ) {
				$slots = array();
			}

			$slots[] = $slot;
			update_post_meta( $id, 'easy-resource-slots', $slots );
		}
	}

	private function delete_resource_slot( $id ) {
		$slots = get_post_meta( $id, 'easy-resource-slots', true );
		unset( $slots[ intval( $_GET['delete_slot'] ) ] );
		update_post_meta( $id, 'easy-resource-slots', $slots );
		ER()->messages()->add_success( sprintf(__('%s deleted', 'easyReservations'), __('Slot', 'easyReservations')) );
	}
}

return new ER_Admin_Resources();