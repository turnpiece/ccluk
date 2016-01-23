<?php get_header(); ?>

    <div class="sliderWrapper">

        <div id="slider" class="flexslider">
                       
        <ul class="slides">
            
                   <?php if ( function_exists( 'ot_get_option' ) ) {
  
  /* get the slider array */
  $slides = ot_get_option( 'my_slider', array() );
  
  if ( ! empty( $slides ) ) {
    foreach( $slides as $slide ) {
        
     	echo '<li><a href="'.$slide['btnurl'].'"><img src="'.$slide['image'].'" alt="'.$slide['title'].'" /></a><div class="flex-holder"><div class="flex-caption"><div>';
                            
                                     if($slide['title']) {
                                         
                      echo '<a href="'.$slide['btnurl'].'"><h1 style="color: '.$slide['textcolor'].'; background: '.$slide['backgroundcolor'].';">'.$slide['title'].'</h1><br>';
                                   
                      }
                      
                       if($slide['title2']) {
                                         
                      echo '<h1 style="color: '.$slide['textcolor'].'; background: '.$slide['backgroundcolor'].';">'.$slide['title2'].'</h1>';
                                   
                      }
                      
                       echo '</a>';
                       
                         if($slide['description']) {
                                         
                      echo '<span class="flex-caption-decription">';
                      
                       echo $slide['description'];
                      
                      echo '</span>';
                                   
                      }
                                     
                        if($slide['btntext']) { 
                       
                       echo '   <ul class="caption-btn"><li><a href="'.$slide['btnurl'].'">'.$slide['btntext'].' &rarr;</a></li></ul>';
                            
                        }
                        
                       echo ' </div></div></div></li>';

  }
  
} 

                    }
                    
                    ?>
                            
             
        </ul>
                   
                      <script type="text/javascript">
                       
                       jQuery(window).load(function() {
 
 jQuery('#carousel').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: false,
        slideshow: false,
        itemWidth: 215,
        itemMargin: 20,
        asNavFor: '#slider'
      });
      
      jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: true,
      
           <?php if (ot_get_option('autoslide') == 'yes') { ?>
                       
      slideshow: true,                //Boolean: Animate slider automatically
        slideshowSpeed: <?php echo ot_get_option('delay') ?>, 
      
      <?php } else { ?>
       slideshow: false,  
       <?php }  ?>
           
      sync: "#carousel",
        start: function(slider){
          jQuery('body').removeClass('loading');
     
             
        }
      });
 
});
                       
                             
      
         
           
           </script>
        
    </div>

         

               
   
        
         <div class="carouselWrapper">
    
        <div class="container">
            
            <div class="sixteen columns">
                
   <div id="carousel" class="flexslider">

            <ul class="slides">
 
            
                   <?php if ( function_exists( 'ot_get_option' ) ) {
  
  /* get the slider array */
  $slides = ot_get_option( 'my_slider', array() );
  
  if ( ! empty( $slides ) ) {
    foreach( $slides as $slide ) {
        
     	echo '<li class="four columns">';
                            
            if($slide['thumbimage']) { 
        
        	echo '<img src="'.$slide['thumbimage'].'" alt="'.$slide['title'].'" />';
                
            }
  
                       echo ' </li>';

  }
  
} 

                    }
                    
                    ?>
 
        </ul>
        
    </div>
        
    </div>
            
               </div>
        
    </div>

    </div>


<div class="homePageContent container">

    <ul class="horizontalWidgetArea clearfix">
        
         <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Home Full")) ; ?>
         <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Home One Half")) ; ?>
         <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Home One Third")) ; ?>
         <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Home Two Thirds")) ; ?>
         <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Home One Fourth")) ; ?>
        
    </ul>
    
    <ul class="leftWidgetArea six columns clearfix">
        
        
        
    </ul>
    
    <ul class="rightWidgetArea ten columns clearfix">
        
        
        
    </ul>
    
</div>

<?php get_footer(); ?>