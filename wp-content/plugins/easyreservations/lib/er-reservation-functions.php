<?php

/**
 * Get reservation data from [tag]
 * @param $tag array
 * @param $res ER_Reservation
 * @return string
 */
function er_reservation_parse_tag( $tag, $res ){
    switch($tag[0]){
        case 'res_id':
        case 'ID':
        case 'res-id':
            return zeroise( $res->id, isset($tag[1]) ? intval($tag[1]) : 0 );
            break;
        case 'name':
        case 'thename':
            return $res->name;
            break;
        case 'email':
            return $res->email;
            break;
        case 'country':
            return easyreservations_country_name($res->country);
            break;
        case 'resource':
        case 'rooms':
            return __($res->resource->post_title);
            break;
        case 'resource-space':
        case 'resource-number':
        case 'resource-nr':
        case 'resourcenumber':
        case 'roomnumber':
            return __($res->resource->get_space_name($res->space));
            break;
        case 'arrival':
        case 'arrivaldate':
        case 'date-from':
            $format = isset($field['format']) ? $field['format'] : RESERVATIONS_DATE_FORMAT_SHOW;
            return date($format, $res->arrival);
            break;
        case 'departure':
        case 'departuredate':
        case 'date-to':
            $format = isset($field['format']) ? $field['format'] : RESERVATIONS_DATE_FORMAT_SHOW;
            return date($format, $res->departure);
            break;
        case 'persons':
            return $res->adults + $res->children;
            break;
        case 'adults':
            return $res->adults;
            break;
        case 'units':
        case 'times':
        case 'nights':
        case 'days':
            return $res->times;
            break;
        case 'children':
        case 'childs':
            return $res->children;
            break;
        case 'price':
            return er_format_money($res->get_price(),1);
            break;
        case 'paid':
            return er_format_money($res->paid,1);
            break;
        case 'total':
        case 'balance':
            return er_format_money($res->get_price() - $res->paid,1);
            break;
        case 'date':
            if(isset($tag[1])) $format = $tag[1];
            else $format = RESERVATIONS_DATE_FORMAT;
            return date($format, current_time( 'timestamp' ));
            break;
        case 'custom':
            $content = '';
            $custom_fields = get_option('reservations_custom_fields');
            if(isset($tag['id'])){
                if(isset($custom_fields['fields'][$tag['id']])){
                    $custom_field = $custom_fields['fields'][$tag['id']];
                    $customs = $res->get_meta('custom');
                    foreach($customs as $custom){
                        if($custom['id'] == $tag['id']){
                            if(!isset($field['show'])){
                                $content = $res->getCustomsValue($custom);
                                if(isset($custom_field['price'])) $content .= ' ('.er_format_money($res->calculateCustom( $tag['id'], $custom['value'], $customs),1).')';
                            } elseif($field['show'] == 'title') $content = $custom_field['title'];
                            elseif($field['show'] == 'value'){
                                $content = $res->getCustomsValue($custom);
                            } elseif($field['show'] == 'amount') $content = er_format_money($res->calculateCustom( $tag['id'], $custom['value'], $customs),1);
                            break;
                        }
                    }
                    if(empty($content) && isset($custom_field['else'])){
                        $content = $custom_field['else'];
                    }
                }
            }
            return $content;
            break;
        case 'customs':
            $content = '';
            $custom_fields = get_option('reservations_custom_fields');
            $customs = $res->get_meta('custom');
            foreach($customs as $custom){
                if(isset($custom_fields['fields'][$custom['id']])){
                    $content .= $custom_fields['fields'][$custom['id']]['title'].': ';
                    $content .= $res->getCustomsValue($custom);
                    if(isset($custom_field['price'])){
                        $content .= ' ('.er_format_money($res->calculateCustom( $tag['id'], $custom['value'], $customs),1).')';
                    }
                    $content .= '<br>';
                }
            }
            return $content;
            break;
    }
    return '';
}
