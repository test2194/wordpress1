<?php
if( !defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

if(isset($_GET["form"])){
    $current_form_name = sanitize_key($_GET['form']);
    $reservations_form = get_option("reservations_form_".$current_form_name);
} else {
    $current_form_name = '';
    $reservations_form = get_option("reservations_form");
}

$custom_fields = get_option('reservations_custom_fields');
$custom_fields_array = array();
if($custom_fields){
    foreach($custom_fields['fields'] as $id => $custom){
        $custom_fields_array[$id] = $custom['title'];
    }
}

$new_form = '';
if(!empty($reservations_form)){
    foreach(explode("\r\n", ($reservations_form)) as $v){
        $new_form .= nl2br(htmlspecialchars($v, ENT_COMPAT));
    }
    $tags = er_form_template_parser($new_form, true);
    foreach($tags as &$v){
        $explode = explode(' ', $v);
        $new_form = str_replace('['.$v.']', '<formtag attr="'.$explode[0].'">['.$v.']</formtag>', $new_form);
    }
}
wp_enqueue_script('jquery-ui-accordion');
wp_enqueue_script('form-editor', RESERVATIONS_URL.'assets/js/functions/form.editor.js');

?><div style="width:99%;line-height: 22px">
    <ul class="navtabs">
        <li class="<?php if(empty($current_form_name)) echo 'curr'; ?>"><a href="admin.php?page=reservation-settings&tab=form"><?php _e('Standard', 'easyReservations');?></a></li>
        <?php
            $forms = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT option_name FROM ".$wpdb->prefix ."options WHERE option_name like %s ",
                    $wpdb->esc_like("reservations_form_") . '%'
                )
            );

            foreach($forms as $form_option){
                $form_option_name = str_replace('reservations_form_', '', $form_option->option_name);
                if(!empty($form_option_name)){
                    $link = 'admin.php?page=reservation-settings&tab=form';
                    echo '<li class="'.($form_option_name == $current_form_name ? 'curr' : '').'">';
                    echo '<a href="'.$link.'&form='.$form_option_name.'">'.ucfirst(str_replace('-', ' ', $form_option_name)).'</a>';
                    if($form_option_name !== 'default-widget' && $form_option_name !== 'default-search-bar'){
                        echo ' <a href="'.$link.'&delete-form='.$form_option_name.'" style="font-size:16px;color:#e45235;cursor:pointer" class="fa fa-times"></a>';
                    }
                    echo '</li>';
                }
            }
        ?>
    </ul>
    <div style="float:right" class="easy-ui">
        <form method="post" action="admin.php?page=reservation-settings&tab=form" id="reservations_form_add">
	        <span class="together-wrapper">
				<input name="form_name" type="text" style="width:200px;">
		        <input type="button" onclick="document.getElementById('reservations_form_add').submit(); return false;" class="easy-button last" value="<?php _e('Add', 'easyReservations');?>">
            </span>
        </form>
    </div>
</div>
<div id="form_container" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false" contenteditable="true">
   <?php echo stripslashes($new_form); ?>
</div>
<div id="accordion_container" class="easy-ui">
    <div id="accordion" style="width:100%">
        <h3><?php _e('Date fields','easyReservations'); ?></h3>
        <div class="table">
            <table class="formtable">
                <thead>
                <tr>
                    <th></th>
                    <th><?php _e('Type','easyReservations'); ?></th>
                    <th><?php _e('Default','easyReservations'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr attr="date">
                    <td style="background-image:url(<?php echo RESERVATIONS_URL; ?>assets/images/units.png);"></td>
                    <td><strong><?php _e('Date selection','easyReservations'); ?></strong><br><i><?php _e('For arrival and departure','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="date-from">
                    <td style="background-image:url(<?php echo RESERVATIONS_URL; ?>assets/images/day.png);"></td>
                    <td><strong><?php _e('Arrival date','easyReservations'); ?></strong><br><i><?php _e('Text field with datepicker','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="date-from-hour">
                    <td style="text-align:center;"><span class="fa fa-clock-o" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Arrival hour','easyReservations'); ?></strong><br><i><?php _e('Select field as of the time pattern selection','easyReservations'); ?></i></td>
                    <td>00</td>
                </tr>
                <tr attr="date-from-min">
                    <td style="text-align:center;"><span class="fa fa-hourglass" style="font-size: 16px"></span></td>
                    <td><strong><?php _e('Arrival minute','easyReservations'); ?></strong><br><i><?php _e('Select field','easyReservations'); ?> 00-59</i></td>
                    <td>12</td>
                </tr>
                <tr attr="date-to">
                    <td style="background-image:url(<?php echo RESERVATIONS_URL; ?>assets/images/day.png);"></td>
                    <td><strong><?php _e('Departure date','easyReservations'); ?></strong><br><i><?php _e('Text field with datepicker','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="date-to-hour">
                    <td style="text-align:center;"><span class="fa fa-clock-o" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Departure hour','easyReservations'); ?></strong><br><i><?php _e('Select field as of the time pattern selection','easyReservations'); ?></i></td>
                    <td>12</td>
                </tr>
                <tr attr="date-to-min">
                    <td style="text-align:center;"><span class="fa fa-hourglass" style="font-size: 16px"></span></td>
                    <td><strong><?php _e('Departure minute','easyReservations'); ?></strong><br><i><?php _e('Select field','easyReservations'); ?> 00-59</i></td>
                    <td>00</td>
                </tr>
                <tr attr="units">
                    <td style="background-image:url(<?php echo RESERVATIONS_URL; ?>assets/images/units.png);"></td>
                    <td><strong><?php echo ucfirst(__('billing units', 'easyReservations')); ?></strong><br><i><?php _e('Select field to choose length of stay','easyReservations'); ?></i></td>
                    <td>1</td>
                </tr>
                </tbody>
            </table>
        </div>
        <h3><?php _e('Information fields','easyReservations'); ?></h3>
        <div class="table">
            <table class="formtable">
                <thead>
                <tr>
                    <th></th>
                    <th><?php _e('Type','easyReservations'); ?></th>
                    <th><?php _e('Default','easyReservations'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr attr="resources">
                    <td style="text-align:center;"><span class="fa fa-home" style="font-size: 18px"><for/span></td>
                    <td><strong><?php _e('Resources','easyReservations'); ?></strong><br><i><?php _e('Select for resource','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="adults">
                    <td style="text-align:center;"><span class="fa fa-male" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Adults','easyReservations'); ?></strong><br><i><?php _e('Select field for adults','easyReservations'); ?></i></td>
                    <td>1</td>
                </tr>
                <tr attr="children">
                    <td style="text-align:center;"><span class="fa fa-child" style="font-size: 20px"></span></td>
                    <td><strong><?php _e('Children','easyReservations'); ?></strong><br><i><?php _e('Select field for children','easyReservations'); ?></i></td>
                    <td>0</td>
                </tr>
                <tr attr="name">
                    <td style="text-align:center;"><span class="fa fa-user" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Name','easyReservations'); ?><br><i></strong><?php _e('Text field for name','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="email">
                    <td style="text-align:center;"><span class="fa fa-envelope" style="font-size: 16px"></span></td>
                    <td><strong><?php _e('Email','easyReservations'); ?></strong><br><i><?php _e('Text field for email','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                <tr attr="country">
                    <td style="text-align:center;"><span class="fa fa-globe" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Country','easyReservations'); ?></strong><br><i><?php _e('Select field for country','easyReservations'); ?></i></td>
                    <td>&#10008;</td>
                </tr>
                </tbody>
            </table>
        </div>
        <h3><?php _e('Special fields','easyReservations'); ?></h3>
        <div class="table">
            <table class="formtable">
                <thead>
                <tr>
                    <th></th>
                    <th><?php _e('Type','easyReservations'); ?></th>
                </tr>
                </thead>
                <tbody>
                <tr attr="hidden">
                    <td style="text-align:center;"><span class="fa fa-lock" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Hidden','easyReservations'); ?></strong><br><i><?php _e('Dictate information and/or hide it from guest','easyReservations'); ?></i></td>
                </tr>
                <tr attr="custom">
                    <td style="text-align:center;"><span class="fa fa-tag" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Custom','easyReservations'); ?></strong><br><i><?php _e('Custom form elements to get more information','easyReservations'); ?></i></td>
                </tr>
                <?php do_action('easy_form_settings_list'); ?>
                <tr attr="captcha">
                    <td style="text-align:center;"><span class="fa fa-id-card" style="font-size: 16px"></span></td>
                    <td><strong><?php _e('Captcha','easyReservations'); ?></strong><br><i><?php _e('To be secure against spam reservations','easyReservations'); ?></i></td>
                </tr>
                <tr attr="show_price">
                    <td style="text-align:center;"><span class="fa fa-money" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Show price','easyReservations'); ?></strong><br><i><?php _e('Display price live','easyReservations'); ?></i></td>
                </tr>
                <tr attr="error">
                    <td style="text-align:center;"><span class="fa fa-times" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Error','easyReservations'); ?></strong><br><i><?php _e('Displays errors','easyReservations'); ?></i></td>
                </tr>
                <tr attr="submit">
                    <td style="text-align:center;"><span class="fa fa-bolt" style="font-size: 18px"></span></td>
                    <td><strong><?php _e('Submit button','easyReservations'); ?></strong><br><i><?php _e('Button to submit the form','easyReservations'); ?></i></td>
                </tr>
                </tbody>
            </table>
        </div>
        <h3><?php _e('Format','easyReservations'); ?></h3>
        <div class="table">
            <table class="formtable">
                <tbody>
                <tr bttr="label">
                    <td><strong><?php _e('Label','easyReservations'); ?> <tag>&lt;label&gt;</tag></strong><br><i><?php _e('Used for description of tags. Should be before the content wrapper.','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="div">
                    <td><strong><?php _e('Content wrapper','easyReservations'); ?> <tag>&lt;div class="content"&gt;</tag><br><i></strong><?php _e('Wrapper around content. Should be around fields and text.','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="row">
                    <td><strong><?php _e('Row','easyReservations'); ?> <tag>&lt;div class="row"&gt;</tag><br><i></strong><?php _e('Wrapper for multiple elements in one row. Should be inside the content wrapper and around any form elements and/or text. It may be necessary to define their width\'s.','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="small">
                    <td><strong><?php _e('Small','easyReservations'); ?> <tag>&lt;small&gt;</tag><br><i></strong><?php _e('A caption for the form element. Should be in the content wrapper.','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="b">
                    <td><strong><?php _e('Bold','easyReservations'); ?> <tag>&lt;strong&gt;</tag></strong><br><i><?php _e('Bold text','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="i">
                    <td><strong><?php _e('Italic','easyReservations'); ?> <tag>&lt;i&gt;</tag></strong><br><i><?php _e('Italic text','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="h1">
                    <td><strong><?php _e('Headline','easyReservations'); ?> <tag>&lt;h1&gt;</tag></strong><br><i><?php _e('Big headline','easyReservations'); ?></i></td>
                </tr>
                <tr bttr="h2">
                    <td><strong><?php _e('Sub-headline','easyReservations'); ?> <tag>&lt;h2&gt;</tag></strong><br><i><?php _e('Smaller headline to divide the form.','easyReservations'); ?></i></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="easy-ui" style="line-height: 44px">
    <?php if($current_form_name == 'default-widget' || $current_form_name == 'default-search-bar'): ?>
        <i style="margin:5px;"><?php _e('Default widget and search bar template cannot be edited so you always have them as reference', 'easyReservations'); ?>.</i>
    <?php else: ?>
        <a href="javascript:submitForm();" class="easy-button" style="margin:5px;"><?php _e('Submit', 'easyReservations'); ?></a>
        <a href="javascript:resetToDefault();" class="easy-button grey" style="margin:5px 5px 5px 0;"><?php _e('Default', 'easyReservations'); ?></a>
    <?php endif; ?>
</div>
<form id="easy_form" method="post">
    <input type="hidden" name="action" value="reservations_form_settings">
    <input type="hidden" name="reservations_form_content" id="reservations_form_content" value="">
</form>

<script type="text/javascript">
    function submitForm(){
        jQuery('#reservations_form_content').val(jQuery('#form_container').html());
        jQuery('#easy_form').submit();
    }

    function resetToDefault(){
        var Default = '<?php echo str_replace( array( "\n", "\r" ), array( "\\n", "\\r" ), easyreservations_get_default_form()); ?>';

        jQuery('#form_container').html(htmlForTextWithEmbeddedNewlines(Default));
    }

    function generateHiddenOptions(tag){
        var value = '<h4><?php echo addslashes(__('Type', 'easyReservations'));?></h4><p><select id="hiddentype" name="1" onchange="changeHiddenOption()">';
        jQuery.each({
            xxx: "<?php echo addslashes(__('Type', 'easyReservations'));?>",
            resource: "<?php echo addslashes(__('Resource', 'easyReservations'));?>",
            from: "<?php echo addslashes(__('Arrival date', 'easyReservations'));?>",
            "date-from-hour": "<?php echo addslashes(__('Arrival hour', 'easyReservations'));?>",
            "date-from-min": "<?php echo addslashes(__('Arrival minute', 'easyReservations'));?>",
            to: "<?php echo addslashes(__('Departure date', 'easyReservations'));?>",
            "date-to-hour": "<?php echo addslashes(__('Departure hour', 'easyReservations'));?>",
            "date-to-min": "<?php echo addslashes(__('Departure minute', 'easyReservations'));?>",
            units: "<?php echo addslashes(ucfirst(__('billing units', 'easyReservations')));?>",
            adults: "<?php echo addslashes(__('Adults', 'easyReservations'));?>",
            children: "<?php echo addslashes(__('Children', 'easyReservations'));?>"
        }, function(ok,ov){
            var selected = '';
            if(tag && tag[1] == ok) selected = 'selected="selected"';
            value += '<option value="'+ok+'" '+selected+'>'+ov+'</option>';
        });
        value += '</select></p><span id="the_hidden_value">';
        if(tag) value += changeHiddenOption(tag, tag[1]);
        value += '</span><label class="wrapper"><input type="checkbox" name="display"><span class="input"></span> <?php echo addslashes(__('Display value', 'easyReservations'));?></label>';
        return value;
    }

    function changeHiddenOption(tag,typ){
        var type = jQuery('#hiddentype').val();
        if(typ) type = typ;
        var field = false;
        if(type == 'resource'){
            if(!tag || !tag[2]) tag = {2:''};
            field = generateResourceSelect(tag[2],'2');
        } else if(type == "from" || type == "to"){
            if(!tag || !tag[2]) tag = {2:'<?php echo RESERVATIONS_DATE_FORMAT; ?>'};
            field = '<input type="text" name="2" value="'+tag[2]+'">'
        } else if(type == "date-from-hour" || type == "date-to-hour"){
            if(!tag || !tag[2]) tag = {2:12};
            field = '<select name="2">'+generateOptions('0-23',tag[2])+'</select>'
        } else if(type == "date-from-min" || type == "date-to-min"){
            if(!tag || !tag[2]) tag = {2:30};
            field = '<select name="2">'+generateOptions('0-59',tag[2])+'</select>'
        } else if(type == "adults" || type == "units"){
            if(!tag || !tag[2]) tag = {2:2};
            field = '<select name="2">'+generateOptions('1-100',tag[2])+'</select>'
        } else if(type == "children"){
            if(!tag || !tag[2]) tag = {2:1};
            field = '<select name="2">'+generateOptions('0-100',tag[2])+'</select>'
        }
        if(field){
            field = '<h4><?php echo addslashes(__('Value', 'easyReservations'));?></h4><p>'+field+'</p>'
            if(typ) return field;
            else jQuery('#the_hidden_value').html(field);
        }
    }

    function resourceSelect(tag){
        if(!tag) tag = {value:''};
        else if(!tag['value']) tag['value'] = '';
        return generateResourceSelect(tag['value'],'value');
    }

    function generateResourceSelect(sel,name){
        var resources = <?php echo json_encode(ER()->resources()->get()); ?>;
        var value = '<select name="'+name+'">';
        jQuery.each(resources, function(k,v){
            var selected = '';
            if(sel && sel == k) selected = 'selected="selected"';
            value += '<option value="'+k+'" '+selected+'>'+v['post_title']+'</option>';
        });
        return value+'</select>';
    }

    function daysCheckboxes(sel){
        if(!sel) sel = {value:''};
        var days = <?php echo json_encode(er_date_get_label(0, 3)); ?>;
        var value = '';
        var selected_days = false;
        if(sel['days']){
            selected_days = sel['days'].split(',');
        }

        jQuery.each(days, function(k,v){
            var selected = '';
            if((selected_days && jQuery.inArray(""+(k+1), selected_days) > -1) || !selected_days) selected = 'checked="checked"';
            value += '<label class="wrapper"><input type="checkbox" class="not" value="'+(k+1)+'" name="day-'+(k+1)+'" '+selected+'><span class="input"></span>'+v+'</label> ';
        });
        return value;
    }

    function generateDaysCheckboxes(){
        var tag = '';
        jQuery.each([1,2,3,4,5,6,7], function(k,v){
            if(jQuery('input[name="day-'+v+'"]').is(':checked')){
                tag += v+',';
            }
        });
        if(tag  === ''){
            return 'days="" ';
        }
        tag = tag.substr(0,tag.length-1);
        if(tag == '1,2,3,4,5,6,7'){
            tag = '';
        }
        if(tag !== ''){
            tag = 'days="'+tag+'" ';
        }

        return tag;
    }

    function customRequired(tag){
        var sel = '', checked = '';
        if(tag && tag[Object.keys(tag)[Object.keys(tag).length - 1]]) sel = tag[Object.keys(tag)[Object.keys(tag).length - 1]];
        if(sel == '*') checked = ' checked="checked"';
        return '<input type="checkbox" name="*" value="*"'+checked+'> <?php echo addslashes(__('Required', 'easyReservations')); ?><br>';
    }

    function generateTimeSelection(tag){
        var value = '<a href="javascript:" onclick="generateTimeOptions()">Add time range</a>';
        if(tag && tag['range']){
            var times = tag['range'].split(';');
            jQuery.each(times, function(k,v){
                if(v && v != ''){
                    var fromto = v.split('-');
                    value += generateTimeOptions(fromto[0],fromto[1],true);
                }
            });
        } else value += '<span class="timerange"></span>';
        return value;
    }

    function generateTimeOptions(sel,val,doreturn){
        var value = '<p style="padding:0;margin:0;" class="timerange"><select class="not" name="range-from[]">';
        jQuery.each(<?php echo json_encode(array('00','01','02','03','04','05','06','07','08','09',10,11,12,13,14,15,16,17,18,19,20,21,22,23)); ?>, function(k,v){
            var selected = '';
            if(sel && sel == k) selected = 'selected="selected"';
            value += '<option value="'+k+'" '+selected+'>'+v+'</option>';
        });

        if(!val) val = '';
        value += '</select> - <select class="not" name="range-to[]">';
        jQuery.each(<?php echo json_encode(array('00','01','02','03','04','05','06','07','08','09',10,11,12,13,14,15,16,17,18,19,20,21,22,23)); ?>, function(k,v){
            var selected = '';
            if(val && val == k) selected = 'selected="selected"';
            value += '<option value="'+k+'" '+selected+'>'+v+'</option>';
        });

        value += '</select><a href="#" onclick="this.parentNode.parentNode.removeChild(this.parentNode);"  class="fa fa-times"></a></p>';

        if(doreturn) return value;
        else jQuery('.timerange:last').after(value);
    }

    function addTimeRangeToTag(){
        var codefields = document.getElementsByName('range-from[]');
        var transfields = document.getElementsByName('range-to[]');
        var tag = '';

        if(codefields.length >= 1){
            for(var i = 0; i < codefields.length; i++){
                tag += codefields[i].value+'-'+transfields[i].value+';';
            }
        }
        if(tag != '') tag = 'range="'+tag+'""';
        return tag;
    }

    var style = {
            title: '<?php echo addslashes(__('Style', 'easyReservations'));?>',
            input: 'text'
        },
        title = {
            title: '<?php echo addslashes(__('Title', 'easyReservations'));?>',
            input: 'text'
        },
        timerange = {
            title: '<?php echo addslashes(__('Time range', 'easyReservations'));?>',
            input: generateTimeSelection
        },
        disabled = {
            title: '<?php echo addslashes(__('Disabled', 'easyReservations'));?>',
            input: 'check',
            default: 'disabled'
        },
        fields = {
            error: {
                name: '<?php echo addslashes(__('Errors', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Shows the warning messages in form. Is required for the multiple reservations form function.', 'easyReservations'));?>',
                options: {
                    error_title: {
                        title: '<?php echo addslashes(__('Title', 'easyReservations'));?>',
                        input: 'text',
                        default: 'Errors found in the form'
                    },
                    error_message: {
                        title: '<?php echo addslashes(__('Message', 'easyReservations'));?>',
                        input: 'textarea',
                        default: 'There is a problem with the form, please check and correct the following:'
                    },
                    style: style,
                    title: title
                }
            },
            "date": {
                name: '<?php echo addslashes(__('Date', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('For resources with slots and resources that get reserved on a daily basis.', 'easyReservations'));?>',
                options: {
                    departure: {
                        title: '<?php echo addslashes(__('Departure is selectable', 'easyReservations'));?>',
                        input: 'check',
                        checked: 'true',
                        default: 'true'
                    },
                    time: {
                        title: '<?php echo addslashes(__('Time is selectable', 'easyReservations'));?>',
                        input: 'check',
                        checked: 'true',
                        default: 'true'
                    },
                    resource: {
                        title: '<?php echo addslashes(__('Default resource', 'easyReservations'));?>',
                        input: resourceSelect
                    },
                    arrivalHour : {
                        title: '<?php echo addslashes(__('Default arrival hour', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-23',
                        default: '12'
                    },
                    arrivalMinute: {
                        title: '<?php echo addslashes(__('Default arrival minute', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-59',
                        default: '0'
                    },
                    departureHour : {
                        title: '<?php echo addslashes(__('Default departure hour', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-23',
                        default: '12'
                    },
                    departureMinute: {
                        title: '<?php echo addslashes(__('Default departure minute', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-59',
                        default: '0'
                    },
                }
            },
            "date-from": {
                name: '<?php echo addslashes(__('Arrival date', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Is required in any form.', 'easyReservations'));?>',
                generate: generateDaysCheckboxes,
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Value', 'easyReservations'));?>',
                        input: 'text',
                        default: '+14'
                    },
                    days: {
                        title: '<?php echo addslashes(__('Selectable days', 'easyReservations'));?>',
                        input:daysCheckboxes
                    },
                    min: {
                        title: '<?php echo addslashes(__('Earliest selectable date in days (0=now)', 'easyReservations'));?>',
                        input: 'amount',
                        default: '0'
                    },
                    max: {
                        title: '<?php echo addslashes(__('Latest selectable date in days (0=endless)', 'easyReservations'));?>',
                        input: 'amount',
                        default: '0'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            "date-to": {
                name: '<?php echo addslashes(__('Departure date', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Field with datepicker for the departure date. Can be replaced by billing units selection or deleted so that every reservation lasts one billing unit.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Value', 'easyReservations'));?>',
                        input: 'text',
                        default: '+21'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            "date-from-hour": {
                name: '<?php echo addslashes(__('Arrival hour', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select for arrival hour. Can be replaced by a hidden field and defaults to 12:00 if not in form.', 'easyReservations'));?>',
                generate: addTimeRangeToTag,
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-23',
                        default: '12'
                    },
                    range: timerange,
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            "date-to-hour": {
                name: '<?php echo addslashes(__('Departure hour', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select for departure hour. Can be replaced by a hidden field and defaults to 12:00 if not in form.', 'easyReservations'));?>',
                generate: addTimeRangeToTag,
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-23',
                        default: '12'
                    },
                    range: timerange,
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            "date-from-min": {
                name: '<?php echo addslashes(__('Arrival minute', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select for arrival minute.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-59',
                        default: '0'
                    },
                    increment: {
                        title: '<?php echo addslashes(__('Increment', 'easyReservations'));?>',
                        input: 'select',
                        options: {1:'1', 5:'5', 10:'10', 15:'15', 30:'30'},
                        default: '1'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            "date-to-min": {
                name: '<?php echo addslashes(__('Departure minute', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select for departure minute.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-59',
                        default: '0'
                    },
                    increment: {
                        title: '<?php echo addslashes(__('Increment', 'easyReservations'));?>',
                        input: 'select',
                        options: {1:'1', 5:'5', 10:'10', 15:'15', 30:'30'},
                        default: '1'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            units: {
                name: '<?php echo addslashes(ucfirst(__('billing units', 'easyReservations')));?>',
                desc: '<?php echo addslashes(__('Select of billing units to define the duration of stay. Can be replaced by departure date field or defaults to one billing unit if not in form.', 'easyReservations'));?>',
                options: {
                    1: {
                        title: '<?php echo addslashes(__('Min', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '1'
                    },
                    2: {
                        title: '<?php echo addslashes(__('Max', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '10'
                    },
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '7'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            resources: {
                name: '<?php echo addslashes(__('Resources', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select of resources. Is required and can only be replaced by hidden field. You can exclude or include resources by entering comma separated IDs.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: resourceSelect
                    },
                    exclude: {
                        title: '<?php echo addslashes(__('Exclude', 'easyReservations'));?>',
                        input: 'text',
                        default: ''
                    },
                    include: {
                        title: '<?php echo addslashes(__('Include', 'easyReservations'));?>',
                        input: 'text',
                        default: ''
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            adults: {
                name: '<?php echo addslashes(__('Adults', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select of adults. Is required and can only be replaced by hidden field.', 'easyReservations'));?>',
                options: {
                    1: {
                        title: '<?php echo addslashes(__('Min', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '1'
                    },
                    2: {
                        title: '<?php echo addslashes(__('Max', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '10'
                    },
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '3'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            children: {
                name: '<?php echo addslashes(__('Children', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select of children. Can be replaced by hidden field or deleted.', 'easyReservations'));?>',
                options: {
                    1: {
                        title: '<?php echo addslashes(__('Min', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-100',
                        default: '0'
                    },
                    2: {
                        title: '<?php echo addslashes(__('Max', 'easyReservations'));?>',
                        input: 'select',
                        options: '1-100',
                        default: '10'
                    },
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: '0-100',
                        default: '0'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            name: {
                name: '<?php echo addslashes(__('Name', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Is required in any form.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Value', 'easyReservations'));?>',
                        input: 'text',
                        default: ''
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            email: {
                name: '<?php echo addslashes(__('Email', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Is required in any form.', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Value', 'easyReservations'));?>',
                        input: 'text',
                        default: ''
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            country: {
                name: '<?php echo addslashes(__('Country', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Select field for country', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Selected', 'easyReservations'));?>',
                        input: 'select',
                        options: <?php echo str_replace('\\"', '"', addslashes(json_encode(include RESERVATIONS_ABSPATH . 'i18n/countries.php'))); ?>,
                        default: 'US'
                    },
                    style: style,
                    title: title,
                    disabled:disabled
                }
            },
            hidden: {
                name: '<?php echo addslashes(__('Hidden', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Date and information fields can be replaced by hidden fields to force the selection without the guest choosing or seeing it. They are helpful for special offers or forms for just one resource.', 'easyReservations'));?>',
                options: generateHiddenOptions
            },
            captcha: {
                name: '<?php echo addslashes(__('Captcha', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('To be secure against spam reservations', 'easyReservations'));?>',
                options: {
                    color: {
                        title: '<?php echo addslashes(__('Color of code', 'easyReservations'));?>',
                        input: 'select',
                        options: {black: "<?php echo addslashes(__('Black', 'easyReservations'));?>", white: "<?php echo addslashes(__('White', 'easyReservations'));?>"},
                        default: 'black'
                    },
                    style: style,
                    title: title
                }
            },
            "show_price": {
                name: '<?php echo addslashes(__('Display price live', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Display price as of current selection.', 'easyReservations'));?>',
                options: {
                    before: {
                        title: '<?php echo addslashes(__('Text before price', 'easyReservations'));?>',
                        input: 'text',
                        default: 'Price:'
                    },
                    style: style,
                    title: title
                }
            },
            submit: {
                name: '<?php echo addslashes(__('Submit', 'easyReservations'));?>',
                desc: '<?php echo addslashes(__('Button to submit the form', 'easyReservations'));?>',
                options: {
                    value: {
                        title: '<?php echo addslashes(__('Value', 'easyReservations'));?>',
                        input: 'text',
                        default: 'Submit'
                    },
                    style: style,
                    title: title
                }
            },
            custom: {
                name: '<?php echo addslashes(__('Custom', 'easyReservations'));?>',
                desc: '<?php echo addslashes(sprintf( __('Can be any form element, can have an impact on the price and are used to get more information. Define them %s first', 'easyReservations'), '<a href="admin.php?page=reservation-settings&tab=custom">here</a>'));?>',
                options: {
                    id: {
                        title: '<?php echo addslashes(__('Select field', 'easyReservations'));?>',
                        input: 'select',
                        options: <?php if(!isset($custom_fields_array)) $custom_fields_array = array(); echo json_encode($custom_fields_array);?>
                    },
                    style: style,
                    title: title
                }
            }
        };
    <?php do_action('easy_form_settings_js'); ?>
</script>