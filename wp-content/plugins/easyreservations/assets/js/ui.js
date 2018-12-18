function easyUiSlider(){
  jQuery('.easy-ui .easy-slider-input:not(.generated)').each(function(){
    var form_field = jQuery(this);
    form_field.addClass('generated')
    var slider = jQuery('<div id="slider" class="easy-slider"><div id="custom-handle" class="ui-slider-handle"><label><span class="fa fa-chevron-left"></span><span class="text"></span><span class="fa fa-chevron-right"></label></div></div>');
    jQuery(this).after(slider);
    var handle = slider.find( "span.text" );
    var min = parseFloat(form_field.attr('data-min'));
    var max = parseFloat(form_field.attr('data-max'));
    var step = parseFloat(form_field.attr('data-step'));
    var label = form_field.attr('data-label');
    var value = form_field.val();
    if(min === undefined) min = 1;
    if(max === undefined) max = 100;
    if(step === undefined) step = 1;
    if(label === undefined) label = '';
    slider.slider({
      range: "min",
      min: min,
      max: max,
      step: step,
      value: form_field.val(),
      create: function() {
        handle.text( jQuery( this ).slider( "value" ) + '' + label );
        form_field.val( jQuery( this ).slider( "value" ) );
      },
      slide: function( event, ui ) {
        handle.text( ui.value + '' + label );
        form_field.val(ui.value);
      },
      stop: function( event, ui ) {
        form_field.val(ui.value).trigger('change');
      }
    });
  });
}

jQuery(document).ready(function(){
  jQuery('.sbHolder').remove();

  var target = '.easy-ui .together',
    invert = ':not(' + target + ')',
    breakpoints = jQuery('.easy-ui > *'+invert+',.easy-ui > div.content > *'+invert);

  breakpoints.each(function(){
    jQuery(this).nextUntil(invert).wrapAll( '<span class="together-wrapper">' );
  });

  breakpoints.first().prevUntil(invert).wrapAll( '<span class="together-wrapper">' );

  jQuery('select[name$="-min"]').each(function(k,v){
    jQuery('<span class="input-box"><span class="fa fa-clock-o"></span></span>').insertAfter(this);
    jQuery(this).add(jQuery(this).prev()).add(jQuery(this).next()).wrapAll('<span class="input-wrapper">');
  });

  var isIE11 = !!window.MSInputMethodContext && !!document.documentMode;
  if(window.CSS && window.CSS.supports && window.CSS.supports('--a', 0) && (isIE11 === undefined || !isIE11)){
    jQuery('.input-wrapper select[name$="-hour"]').each(function(k,v){
      var twelve_hours = false;

      var hideHoursInSelect = function(ele ,test){
        var select = jQuery(this);
        if(test){
          select = ele;
        }
        select.find('option').each(function(k,t){
          if(!twelve_hours && (t.text.indexOf("AM") >= 0 || t.text.indexOf("am") >= 0 || t.text.indexOf("PM") >= 0 || t.text.indexOf("pm") >= 0)){
            twelve_hours = true;
          }
          var explode = t.text.split(":");
          jQuery(t).attr('data-text', t.text);
          t.label = explode[0];
          t.text = explode[0];
        });
        if(!test && twelve_hours){
          var label = 'PM';
          if(select.find('option:selected').data('text').indexOf("AM") >= 0){
            label = 'AM';
          }
          while(!select.hasClass('input-box')){
            select = select.next();
            if(select.hasClass('input-box')){
              select.children('span').removeClass('fa-clock-o').removeClass('fa').addClass('').html(label)
            }
            if(select.length < 1){
              break;
            }
          }
        }
      };

      hideHoursInSelect(jQuery(this), 1);

      jQuery(this).bind('focusin click', function(){
        jQuery(this).find('option').each(function(k,t){
          var orig = jQuery(t).attr('data-text');
          t.label = orig;
          t.text = orig;
        });
      }).bind('blur change', hideHoursInSelect);
    });
  }


  jQuery('.input-box.clickable'). bind('click', function(t){
    if(jQuery(this).next().length > 0){
      jQuery(this).next().focus()
    } else {
      jQuery(this).prev().focus()
    }
  });

  easyUiSlider();

  jQuery.fn.easyNavigation = function( options ) {
    var all_links = jQuery(this).find('li a');
    var current_target = options['value'];


    all_links.bind('click', function(e){
      e.preventDefault();

      if(!jQuery(this).hasClass('active')){
        all_links.removeClass('active');
        jQuery(this).addClass('active');
        jQuery('#'+current_target).addClass('hidden');
        var target = jQuery(this).attr('target');
        if(options.hash){
          window.location.hash = target;
        }

        if(target){
          current_target = target;
          jQuery('#'+target).removeClass('hidden');
        }
      }
    });

    if(options.hash && window.location.hash != ''){
      jQuery('a[target="'+window.location.hash.substring(1)+'"]').click();
    } else {
      jQuery('a[target="'+current_target+'"]').click();
    }
  };
});

function easyUiTooltip(){
  jQuery('#easyUiTooltip').remove();
  var easyUiTooltip = jQuery('<div id="easyUiTooltip"></div>');
  jQuery('body').append(easyUiTooltip);
  jQuery('.easy-tooltip[title][title!=""]').hover(function(e) {
    var ae = jQuery(this);
    var title = ae.attr('title');
    ae.attr('title', '');
    ae.data('titleText', title);
    easyUiTooltip
      .html(title)
      .css({
        'top':ae.position().top + 70,
        'left':ae.position().left + parseInt(ae.css('marginLeft'), 10) + 180 - easyUiTooltip.width() / 2 + ae.width() / 2
      })
      .show(0);
  }, function() {
    var ae = jQuery(this);
    easyUiTooltip.hide(0);
    var title = ae.data('titleText');
    ae.attr('title', title);
  }).mousemove(function(e) {
    var ae = jQuery(this)
    easyUiTooltip.css({
      'top':ae.position().top + 70,
      'left':ae.position().left + parseInt(ae.css('marginLeft'), 10) + 180 - easyUiTooltip.width() / 2 + ae.width() / 2
    });
  });
}
