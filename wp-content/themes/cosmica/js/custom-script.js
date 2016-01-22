jQuery(document).ready(function($) {
  jQuery(".search-toggle").click(function(e) {
    jQuery(".search-form-container").toggle();
  });
  jQuery(".back-to-top").click(function(e) {
    var body = jQuery("html, body");
    body.stop().animate({scrollTop:0}, "500", "swing", function() {
    });
  });

  if(cosmica_object.is_admin_bar_showing){
    jQuery("#header").addClass("with-admin-bar");
  }
  jQuery(document).scroll(function(e) {
    var scrollHeight = jQuery(document).scrollTop();

    if (scrollHeight >= 350 && !jQuery("#header").hasClass("header-post-scroll")) {
      jQuery("#header").hide();
      jQuery("#header").addClass("header-post-scroll");
      jQuery("#header").slideDown();
    }
    if (scrollHeight <= 10 && jQuery("#header").hasClass("header-post-scroll")) {
      jQuery("#header").removeClass("header-post-scroll");
    }

    if (scrollHeight >= 400) {
      jQuery(".back-to-top").show();
    }
    if (scrollHeight <= 100) {
      jQuery(".back-to-top").hide();
    }
    
  });

  jQuery(".cart-button").click(function() {
    var $cart = jQuery(this).siblings(".cart-data-container").children(".cart-item-container");
    if (!$cart.hasClass("bounceInLeft")) {
      $cart.removeClass("bounceOutLeft");
      $cart.addClass("bounceInLeft");
      $cart.show();
    } else {
      $cart.removeClass("bounceInLeft");
      $cart.addClass("bounceOutLeft");
      setTimeout(function() {
        $cart.hide();
      }, 1E3);
    }
  });
  jQuery(".search-button").click(function() {
    var $search = jQuery(this).siblings(".bottom-search-form-container").children(".search-form-incont");
    if (!$search.hasClass("lightSpeedIn")) {
      jQuery(this).removeClass("fa-search");
      jQuery(this).addClass("fa-times");
      $search.removeClass("lightSpeedOut");
      $search.addClass("lightSpeedIn");
      $search.show();
    } else {
      $search.removeClass("lightSpeedIn");
      $search.addClass("lightSpeedOut");
      jQuery(this).removeClass("fa-times");
      jQuery(this).addClass("fa-search");
      setTimeout(function() {
        $search.hide();
      }, 1E3);
    }
  });
  jQuery("#primary-menu").slicknav({label:"", prependTo:"#primary-nav-container"});
  jQuery(".flexslider").flexslider({namespace:"slider-", selector:".slides > li", animation:"slide", easing:"swing", direction:"horizontal", reverse:false, animationLoop:true, smoothHeight:false, startAt:0, slideshow:true, slideshowSpeed:7E3, animationSpeed:600, initDelay:0, randomize:false, pauseOnAction:true, pauseOnHover:true, useCSS:true, touch:true, video:false, controlNav:true, directionNav:true, prevText:"", nextText:"", keyboard:false, multipleKeyboard:false, mousewheel:false, pausePlay:false, 
  pauseText:"Pause", playText:"Play", controlsContainer:"", manualControls:"", sync:"", asNavFor:"", itemWidth:0, itemMargin:0, minItems:0, maxItems:0, move:0, before:function(slider) {
    
    var sl_button_1 = jQuery(slider).find(".slider-active-slide").find(".button-success");
    var sl_button_2 = jQuery(slider).find(".slider-active-slide").find(".button-warning");
    var sl_title = jQuery(slider).find(".slider-active-slide").find(".slide-text-title");
    var sl_description = jQuery(slider).find(".slider-active-slide").find(".slide-text-desc");

    sl_button_1.removeClass("animated fadeInLeftBig");
    sl_button_2.removeClass("animated fadeInRightBig");
    sl_title.removeClass("animated bounceInDown");
    sl_description.removeClass("animated rotateIn");
    
  }, after:function(slider) {
    
    var sl_button_1 = jQuery(slider).find(".slider-active-slide").find(".button-success");
    var sl_button_2 = jQuery(slider).find(".slider-active-slide").find(".button-warning");
    var sl_title = jQuery(slider).find(".slider-active-slide").find(".slide-text-title");
    var sl_description = jQuery(slider).find(".slider-active-slide").find(".slide-text-desc");
    
    sl_button_1.addClass("animated fadeInLeftBig");
    sl_button_2.addClass("animated fadeInRightBig");
    sl_title.addClass("animated bounceInDown");
    sl_description.addClass("animated rotateIn");
    
  }});
  jQuery(".cdns-testimonials-wrapper").flexslider({namespace:"testimonial-", selector:".cdns-testimonials > li", animation:"slide", controlNav:true, directionNav:false, slideshow:false, smoothHeight:true, start:function() {
    jQuery(".cdns-testimonials").children("li").css({"opacity":1, "position":"relative"});
  }});
  jQuery(".client-carousel-con").flexslider({namespace:"client-", selector:".home-carousel-ul > li", animation:"slide", animationLoop:false, controlNav:false, slideshow:true, prevText:"", nextText:"", itemWidth:210, itemMargin:5, start:function() {
  }});
  jQuery('#primary-nav').lavaLamp({container: 'li', target:'#primary-menu > li', fx: 'easeOutQuad', autoResize:true, speed: 500, startItem:0, });  
});
