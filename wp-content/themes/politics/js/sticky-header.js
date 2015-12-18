(function($){
  $(window).scroll(function() {
    'use strict';

    /*
  	 * Adds the 'sticky' class to header on scroll
  	 */
    if ($(this).scrollTop() > 1 ){
      $('header#masthead').addClass("stick");
    } else {
      $('header#masthead').removeClass("stick");
    }

  });

})(this.jQuery);
