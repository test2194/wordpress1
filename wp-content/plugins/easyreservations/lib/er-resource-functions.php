<?php

add_action( 'init', 'er_resource_register_post_type' );

function er_resource_register_post_type() {
    $labels = array(
        'name' => __( 'Resources', 'easyReservations' ),
        'singular_name' => __( 'Resource', 'easyReservations' ),
        'add_new' => sprintf( __( 'Add %s', 'easyReservations' ), __( 'resource', 'easyReservations' ) ),
        'add_new_item' => sprintf( __( 'Add %s', 'easyReservations' ), __( 'resource', 'easyReservations' ) ),
        'edit_item' => sprintf(__('Edit %s', 'easyReservations'), __('resource', 'easyReservations')),
        'new_item' => __( 'New resource', 'easyReservations' ),
        'all_items' => __( 'All resources', 'easyReservations' ),
        'view_item' => __( 'View resource', 'easyReservations' ),
    );
    $args   = array(
        'labels' => $labels,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => array( 'slug' => 'resource' ),
        'show_in_menu' => false,
        'query_var' => true,
        'rewrite' => true,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => true,
        'menu_position' => null,
        'supports' => array(
            'title',
            'editor',
            'author',
            'thumbnail',
            'excerpt',
            'comments',
            'custom-fields',
            'categorys',
            'page-attributes'
        )
    );
    register_post_type( 'easy-rooms', $args );
}

function er_resource_get_slot_matrix( $resource, $date, $check_availability, $adults = 1, $children = 0){
    if($resource->slots){
        $matrix = array();
        $date = strtotime(date('d.m.Y 00:00:00', $date));
        $day = date('N', $date);

        foreach ($resource->slots as $key => $slot){
            if($date >= $slot['range-from'] && $date <= $slot['range-to']){
                if(in_array($day, $slot['days'])){
                    $arrival = $date + $slot['from'] * 60;
                    $duration = $slot['to'] * 60 + ($slot['duration'] * 86400) - $slot['from'] * 60;

                    $avail = $resource->quantity;
                    if($check_availability){
                        $res = new ER_Reservation( false, array(
                            'arrival' => $arrival,
                            'departure' => $arrival + $duration,
                            'resource' => $resource,
                            'adults' => $adults,
                            'children' => $children
                        ), false );
                        $avail = $resource->quantity - $res->checkAvailability(0);
                    }

                    $matrix[$arrival-$date][] = array($avail, $key, $arrival + $duration);

                    if(isset($slot['repeat'])){
                        for($i = 1; $i <= $slot['repeat']; $i++){
                            $repeat_arrival = $arrival + $duration * $i;
                            if(isset($slot['repeat-break'])){
                                $repeat_arrival += intval($slot['repeat-break']) * $i;
                            }

                            $avail = $resource->quantity;
                            if($check_availability){
                                $res->arrival = $repeat_arrival;
                                $res->departure = $repeat_arrival + $duration;
                                $avail = $resource->quantity - $res->checkAvailability(0);
                            }

                            $matrix[$repeat_arrival-$date][] = array($avail, $key, $repeat_arrival + $duration);
                        }
                    }
                }
            } else {
                //break; TODO: presort so we can break here
            }
        }
        return $matrix;
    }
    return false;
}




add_filter( 'get_translatable_documents', 'er_resource_register_as_translatable', 10, 1 );

/**
 * Register resources to be translatable
 */
function er_resource_register_as_translatable( $array ) {
    $array['easy-rooms'] = get_post_type_object( 'easy-rooms' );
    return $array;
}