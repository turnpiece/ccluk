(function($){

	$(document).ready(function(){
		'use strict';

		/**
		 * Foundation
		 *
		 * @link http://foundation.zurb.com/docs
		 */
		$(document).foundation();

		/**
		 * Slick Nav
		 *
		 * @link https://github.com/ComputerWolf/SlickNav
		 */
		$('#site-navigation').slicknav({
			label: '', // Text shown for mobile nav icon
		});

		/**
		 * Focus styles for menus when using keyboard navigation
		 *
		 * @link https://codeable.io/community/wordpress-accessibility-creating-accessible-dropdown-menus/
		 */
	  // Properly update the ARIA states on focus (keyboard) and mouse over events
	  $( '[role="menubar"]' ).on( 'focus.aria  mouseenter.aria', '[aria-haspopup="true"]', function ( ev ) {
	    $( ev.currentTarget ).attr( 'aria-expanded', true );
	  } );

	  // Properly update the ARIA states on blur (keyboard) and mouse out events
	  $( '[role="menubar"]' ).on( 'blur.aria  mouseleave.aria', '[aria-haspopup="true"]', function ( ev ) {
	    $( ev.currentTarget ).attr( 'aria-expanded', false );
	  } );

		// Handle Sub-Navigations so they don't overflow the window
		$("#primary-menu li ul.sub-menu").each(function() {
				var $this = $(this),
						$win = $(window);

				if ($this.offset().left + 100 > $win.width() + $win.scrollLeft() - $this.width()) {
						$this.addClass("nav-shift");
				}
		});

		/**
		 * Material Design Effect
		 * @link http://goo.gl/bvQDmw
		 */
		$(".click-effect, .rescue-button").on("click", function(e){
		    var x = e.pageX;
		    var y = e.pageY;
		    var clickY = y - $(this).offset().top;
		    var clickX = x - $(this).offset().left;
		  var box = this;

		  var setX = parseInt(clickX);
		  var setY = parseInt(clickY);
		  $(this).find("svg").remove();
		  $(this).append('<svg><circle cx="'+setX+'" cy="'+setY+'" r="'+0+'"></circle></svg>');


		  var c = $(box).find("circle");
		  c.animate(
		    {
		      "r" : $(box).outerWidth()
		    },
		    {
		      easing: "easeOutQuad",
		      duration: 400,
		        step : function(val){
		                c.attr("r", val);
		            }
		    }
		  );
		});

	});

	/**
   * Image fadein effect for home posts and blog page
	 */
	$(window).on("load",function() {

	  function fade() {
	    $('.blog .content-area img, .home .image img, .single .featured-image img').each(function() {

	      /* Check the location of each desired element */
	      var objectBottom = $(this).offset().top + $(this).outerHeight() - 200;
	      var windowBottom = $(window).scrollTop() + $(window).innerHeight();

	      /* If the object is completely visible in the window, fade it in */
	      if (objectBottom < windowBottom) {
	        if ($(this).css('opacity')==0) {$(this).fadeTo(400,1);}
	      } else {
	        if ($(this).css('opacity')==1) {$(this).fadeTo(400,0);}
	      }

	    });
	  }

	  fade(); //Fade in completely visible elements during page-load
	  $(window).scroll(function() {fade();}); //Fade in elements during scroll

	});

/* ------------------ End Document ------------------ */
})(this.jQuery);
