(function($){
	$(document).ready(function(){
		'use strict';

		$('.masonry-wrap').masonry({
      columnWidth: '.masonry-post',
      itemSelector: '.masonry-post',
      //gutter: '.gutter-sizer'
    });

    var infiniteCount = 1;
    $( document.body ).on( 'post-load', function () {
        var elements = $('.infinite-wrap.infinite-view-' + infiniteCount + ' article');
        $('.masonry-wrap').masonry( 'appended', elements );
        infiniteCount++;
    });

  /* ------------------ End Document ------------------ */
  });


  })(this.jQuery);
