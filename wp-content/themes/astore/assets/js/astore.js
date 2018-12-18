jQuery(document).ready(function($) {

    $(".site-nav-toggle").click(function() {
        $(".astore-main-nav").toggle();
    });
	
	 
	$(".cactus-menu-toggle").click(function() {
        $(".cactus-mobile-drawer-header").toggle();
    });
	
	$(document).on("click",".menu-expand", function() {
        $(this).siblings(".sub-menu").toggle();
    });
	
	
	/*$(".cactus-cate-menu-toggle").click(function(){
        $(".cactus-cate-menu").toggle();
    });*/
	
	$(".cactus-cate-menu-wrap").mouseenter(function(){
		$(".cactus-cate-menu").show();
	}).mouseleave(function(){
		$(".cactus-cate-menu").hide();
	})
	
	$(".cactus-main-nav li,.cactus-cate-menu li").mouseenter(function(){
  		$(this).addClass("menu-item-hovered");
	});
	$(".cactus-main-nav li,.cactus-cate-menu li").mouseleave(function(){
  		$(this).removeClass("menu-item-hovered");
	});
	

    var stickyTop = function() {

        var stickyTop;
        if ($("body.admin-bar").length) {

            if ($(window).width() < 765) {
                stickyTop = 46;
            } else {
                stickyTop = 32;
            }
        } else {
            stickyTop = 0;
        }
		

        return stickyTop;
    }

    $('.page_item_has_children').addClass('menu-item-has-children');
	
	var page_min_height = $(window).height() - $('.site-footer').outerHeight()- stickyTop();
	
	if($('.header-wrap').length)
		page_min_height = page_min_height - $('.header-wrap').outerHeight();
		
	if($('.page-title-bar').length)
		page_min_height = page_min_height - $('.page-title-bar').outerHeight();
		
	$('.page-wrap').css({'min-height':page_min_height});
	
	function onScroll(event){
    var scrollPos = $(document).scrollTop()+$(".astore-header").height();
	
	$('.astore-nav-main a[href^="#"]').each(function () {
        var currLink = $(this);
		var refElement = $(currLink.attr("href"));
		if(refElement.length){
        if (refElement.position().top <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
            $('.astore-nav-main li').removeClass("active");
            currLink.parent('li').addClass("active");
        }else{
            currLink.parent('li').removeClass("active");
        }
		}
    });
	
	$('.astore-nav-left a[href^="#"]').each(function () {
        var currLink = $(this);
		var refElement = $(currLink.attr("href"));
		if(refElement.length){
        if (refElement.position().top <= scrollPos && refElement.position().top + refElement.height() > scrollPos) {
            $('.astore-nav-left li').removeClass("active");
            currLink.parent('li').addClass("active");
        }else{
            currLink.parent('li').removeClass("active");
        }
		}
    });
	}

    function astoreFxdHeader() {
        var stickyHeight = stickyTop();

        var headerPosition = $(document).scrollTop();
		
		if(headerPosition>400)
			$('#back-to-top, .back-to-top').fadeIn();
		else
			$('#back-to-top, .back-to-top').fadeOut();

        var headerHeight = $(".cactus-header").height();
		
		if ($(".header-image").length) {
			headerHeight += $(".header-image").outerHeight()-69;
		}
		
        if (headerPosition < headerHeight) $(".cactus-fixed-header-wrap").hide();
        else $(".cactus-fixed-header-wrap").show().css({ 'top': stickyHeight });
    }
    $(window).scroll(function() {
        astoreFxdHeader();
		onScroll();
    })
	
	// hide animation items
	  if($().waypoint && $(window).width() > 919 ) {
		  jQuery('.astore-animation').each(function(){
			  if($(this).data('imageanimation')==="yes"){
				  $(this).find("img,i.fa").css("visibility","hidden");
			  }else{
				  $(this).css("visibility","hidden");
			  }
		  });
	  }
	  
	// home page animation
	var astore_animation = function (e){
	
		e.css({'visibility':'visible'});
		e.find("img,i.fa").css({'visibility':'visible'});
	
		// this code is executed for each appeared element
		var animation_type       = e.data('animationtype');
		var animation_duration   = e.data('animationduration');
		var image_animation      = e.data('imageanimation');
		if(image_animation === "yes"){
							 
		e.find("img,i.fa").addClass("animated "+animation_type);
	
		if(animation_duration) {
			e.find("img,i.fa").css('-moz-animation-duration', animation_duration+'s');
			e.find("img,i.fa").css('-webkit-animation-duration', animation_duration+'s');
			e.find("img,i.fa").css('-ms-animation-duration', animation_duration+'s');
			e.find("img,i.fa").css('-o-animation-duration', animation_duration+'s');
			e.find("img,i.fa").css('animation-duration', animation_duration+'s');
			}
	
		}else{
			e.addClass("animated "+animation_type);
	
			if(animation_duration) {
				e.css('-moz-animation-duration', animation_duration+'s');
				e.css('-webkit-animation-duration', animation_duration+'s');
				e.css('-ms-animation-duration', animation_duration+'s');
				e.css('-o-animation-duration', animation_duration+'s');
				e.css('animation-duration', animation_duration+'s');
				}
			}
		}
		
	$('.astore-animation').each(function(index, element) {
		var el = $(this);
		el.waypoint(function() {astore_animation(el);},{ triggerOnce: true, offset: '90%' });
	});

    /* smooth scroll*/
    $(document).on('click', "a.scroll,.site-nav a[href^='#'],.astore-main-nav a[href^='#']", function(e) {

        var selectorHeight = 0;
        if (!$('.fxd-header').length)
            selectorHeight = $('.astore-main-header').outerHeight();
        else
            selectorHeight = $('.fxd-header').outerHeight();

        e.preventDefault();
        var id = $(this).attr('href');

        if (typeof $(id).offset() !== 'undefined') {
            var goTo = $(id).offset().top - selectorHeight - stickyTop() + 1;
            $("html, body").animate({ scrollTop: goTo }, 500);
        }
    });

    $('.comment-form #submit').addClass('btn-normal');
    $('.comment-reply-link').addClass('pull-right btn-reply');

    $('#back-to-top, .back-to-top').click(function() {
        $('html, body').animate({ scrollTop: 0 }, '800');
        return false;
    });

    if ($(window).width() < 920) {
        $('li.menu-item-has-children').prepend('<div class="menu-expand"></div>');
    } else {
        $('#top-menu .menu-expand').remove();
    }

    $(window).resize(function() {
		$('.sub-menu').hide();
		
        if ($(window).width() < 920) {
			$('.cactus-mobile-main-nav').show();
            $('li.menu-item-has-children').prepend('<div class="menu-expand"></div>');
        } else {
			$('.cactus-mobile-main-nav').hide();
            $('#top-menu .menu-expand').remove();
        }

    });

    

	if(astore_params.page_preloader === '1' ){
		if(astore_params.preloader_image!=='')
			$.LoadingOverlaySetup({background : astore_params.preloader_background,image : astore_params.preloader_image});
		else
			$.LoadingOverlaySetup({background : astore_params.preloader_background});
			
		$.LoadingOverlay("show");
		$(window).load(function() {
			$.LoadingOverlay("hide");
		});
	}

    function adjustStretchHeader() {
        var slideHeight = [];
        var sliderHeight = 0;
        $('.banner_selective .astore-slider-item').each(function(index, element) {
            if ($(this).height() > 0)
                slideHeight[index] = $(this).height();

        });
        if (slideHeight.length > 0)
            sliderHeight = Math.min.apply(null, slideHeight);
        if (sliderHeight > 0)
            $('.banner_selective .astore-slider-item').css({ 'height': sliderHeight });
    }

    $('button.single_add_to_cart_button').prepend('<i class="fa fa-shopping-cart"></i> ');
    $('body.admin-bar').addClass('astore-adminbar');

 /* woocommerce */

	$('#grid').click(function() {
		$(this).addClass('active');
		$('#list').removeClass('active');
		$('.archive .post-wrap ul.products').fadeOut(300, function() {
			$(this).addClass('grid').removeClass('list').fadeIn(300);
		});
		return false;
	});

	$('#list').click(function() {
		$(this).addClass('active');
		$('#grid').removeClass('active');
		$('.archive .post-wrap ul.products').fadeOut(300, function() {
			$(this).removeClass('grid').addClass('list').fadeIn(300);
		});
		return false;
	});

	$('#gridlist-toggle a').click(function(event) {
	    event.preventDefault();
	});

 /* woocommerce quantity button */
	$('<div class="quantity-nav"><div class="quantity-button quantity-up">+</div><div class="quantity-button quantity-down">-</div></div>').insertAfter('.quantity input');

	$('.quantity').each(function() {

		var spinner = $(this),
		input   = spinner.find('input[type="number"]'),
		btnUp   = spinner.find('.quantity-up'),
		btnDown = spinner.find('.quantity-down'),
		
		min = input.attr('min'),
		max = input.attr('max');
		
		btnUp.click(function() {
			var oldValue = parseFloat(input.val());
			if ( max !='' && oldValue >= max) {
				var newVal = oldValue;
			} else {
				var newVal = oldValue + 1;
			}
			
			spinner.find("input").val(newVal);
			spinner.find("input").trigger("change");
		
		});
		
		btnDown.click(function() {
		
			var oldValue = parseFloat(input.val());
			
			if (oldValue <= min) {
				var newVal = oldValue;
			} else {
				var newVal = oldValue - 1;
			}
			
			spinner.find("input").val(newVal);
			spinner.find("input").trigger("change");
		
		});

	});

});