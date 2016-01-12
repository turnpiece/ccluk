jQuery(document).ready(function() {




  
  jQuery('.tabs-content li:first').addClass('active');

    jQuery('.horizontalWidgetArea .homeFull .dd_causes_widget h3, .horizontalWidgetArea .homeFull .dd_news_widget h3, .horizontalWidgetArea .homeFull .dd_events_widget h3').addClass('sixteen columns');
    
    jQuery(".postTitle, .postTitleWithImage").hover(function() {
      jQuery(this).stop().animate({opacity: "0.8"}, 300);
     
    },
    function() {
       jQuery(this).stop().animate({opacity: "1"}, 300);
  
    });

                        
  	//Hide the tooglebox when page load
jQuery(".togglebox").hide();
//slide up and down when click over heading 2
jQuery(".toggle_anchor").click(function(){
// slide toggle effect set to slow you can set it to fast too.
jQuery(this).next(".togglebox").slideToggle("medium");
return true;
});

    jQuery('.toggle_anchor_active').click();
jQuery(".toggle_anchor").click(function(){
// slide toggle effect set to slow you can set it to fast too.
jQuery(this).toggleClass('anchor_active');

});
               jQuery('#s').attr('placeholder', 'Search ...');
      jQuery('#s').on('click', function() {
         
         jQuery(this).attr('placeholder', '');
         
         
      });
      
  jQuery('.searchForm a').on('click', function(e) {
         
      
       e.preventDefault();

      
      });
               
      jQuery('.searchForm').on('click', function() {
         
      
         jQuery(this).find('.icon-search-1').fadeToggle(0);
           jQuery(this).find('.icon-cancel').fadeToggle(0);
           jQuery(this).toggleClass('searchActive')
           jQuery('.topBarSearch').slideToggle(200);

      
      });
  
    
    jQuery(".causeTitleWImg, .dd_causes_post").hover(function() {
      jQuery(this).find('img').stop().animate({opacity: "0.6"}, 300);
       jQuery(this).find('ul, h2').stop().animate({bottom: "30%"}, 200);
    },
    function() {
       jQuery(this).find('img').stop().animate({opacity: "1"}, 300);
        jQuery(this).find('ul, h2').stop().animate({bottom: "25%"}, 200);
    });

function logo() {
    
    
 var offset = jQuery('.mainNav').offset().left - 10;
        jQuery('.sliderLogo').css('left', offset);

}

function prettyPhoto() {
    
    jQuery("area[rel^='prettyPhoto']").prettyPhoto();
				
				jQuery(".gallery:first a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'normal',theme:'light_square',slideshow:3000, autoplay_slideshow: false});
				jQuery(".gallery:gt(0) a[rel^='prettyPhoto']").prettyPhoto({animation_speed:'fast',slideshow:10000, hideflash: true});
		
				jQuery("#custom_content a[rel^='prettyPhoto']:first").prettyPhoto({
					custom_markup: '<div id="map_canvas" style="width:260px; height:265px"></div>',
					changepicturecallback: function(){initialize();}
				});

				jQuery("#custom_content a[rel^='prettyPhoto']:last").prettyPhoto({
					custom_markup: '<div id="bsap_1259344" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6"></div><div id="bsap_1237859" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6" style="height:260px"></div><div id="bsap_1251710" class="bsarocks bsap_d49a0984d0f377271ccbf01a33f2b6d6"></div>',
					changepicturecallback: function(){_bsap.exec();}
				});
    
}



 function mobileMenu() {
            
        // Create the dropdown base
        jQuery("<select />").appendTo("nav .container .sixteen");
        jQuery("nav select").hide();
        
        // Create default option "Go to..."
        jQuery("<option />", {
            "selected": "selected",
            "value"   : "",
            "text"    : "Go"
        }).appendTo("nav select");

        // Populate dropdown with menu items
        jQuery(".mainNav a").each(function() {
            var el = jQuery(this);
            jQuery("<option />", {
                "value"   : el.attr("href"),
                "text"    : el.text()
            }).appendTo("nav select");
        });

        jQuery("nav select").change(function() {
            window.location = jQuery(this).find("option:selected").val();
        });
            
        }
        
          
        function select() {
            
        
              // FOR EACH SELECT
    jQuery('nav select').each(function() {
        
        // LET'S PUT OUR MARKUP BEFORE IT
        jQuery(this).before('<div class="select-wrapper">');
        
        // LETS PUR OUR MARKUP AFTER IT
        jQuery(this).after('<span class="select-container"></span></div>');
        
        // UPDATES THE INITIAL SELECTED VALUE
        var initialVal = jQuery(this).children('option:selected').text();
        jQuery(this).siblings('span.select-container').text(initialVal);
        
        // HIDES SELECT BUT LET THE USER STILL CLICK IT
        jQuery(this).css({opacity: 0});  
        
        // WHEN USER CHANGES THE SELECT, WE UPDATE THE SPAN BOX
        jQuery(this).change(function() {
            
            // GETS NEW SELECTED VALUE
            var newSelVal = jQuery(this).children('option:selected').text();
            
            // UPDATES BOX
            jQuery(this).siblings('span.select-container').text(newSelVal);
            
        });
        
    }); 
            
        }
        
        function slider() {
      
            jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        itemWidth: 215,
        itemMargin: 20,
        asNavFor: '#slider'
      });
      
       
        }
        

    logo(); 
    mobileMenu();
    select();
      slider();




});

 
jQuery(window).load(function() {       
  

    jQuery('ul.sf-menu').superfish(); 
   jQuery('#myTab li:first a').click();
     jQuery('.flex-active-slide img').addClass('test');
    


});
    
jQuery(window).resize(function() {

    jQuery('.flex-caption div').css('left', 0);
    var offset = jQuery('.mainNav').offset().left;
    jQuery('.sliderLogo').css('left', offset);

   

});