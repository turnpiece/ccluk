<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);


?>


<div class="pageContent">
        
          <?php if( $headerimg) { ?>
    
        <div class="container">
            
                 <?php if ($sidebar  == 'yes') { ?>
            
            <div class="nine columns offset-by-one">
                
                        <?php } else { ?>
                
                     <div class="fourteen columns offset-by-one">
                
                        <?php } ?>
                
                    <?php } else { ?>
                
                     <div class="container noBannerContent">
                         
                                <?php if ($sidebar  == 'yes') { ?>
                         
                 <div class="eleven columns">
                     
                             <?php } else { ?>
                
                        <div class="sixteen columns">
                            
                             <?php } ?>
                     
                    <?php } ?>
                
                <h1 class="pageTitle"><?php the_title(); ?></h1>
                
                    <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>

                 <?php 

$pageSlider = get_post_meta(get_the_ID(), 'pageSlider', true);

?>
                

                  <?php if( $pageSlider) { ?>
                    
                        <div class="sliderWrapper">

        <div id="slider" class="flexslider">
      
        <ul class="slides">
            
                   <?php
               

                        /* get the slider array */
                   $list = get_post_meta($post->ID, 'pageSlider', TRUE) ;

                        if (!empty($list)) {
                            foreach ($list as $slide) {

                                echo '<li><img src="' . $slide['pageSliderImg'] . '" alt="' . $slide['title'] . '" /></a></li>';
                                
                            }
                        }
                    
                    ?>
                            
              <?php

   ?>
                         
             
        </ul>
            
             <script type="text/javascript">
                       
                       jQuery(window).load(function() {
 
      
      jQuery('#slider').flexslider({
        animation: "slide",
        controlNav: false,
        animationLoop: true,
       slideshow: false,  

           
      sync: "#carousel",
        start: function(slider){
          jQuery('body').removeClass('loading');
     
             
        }
      });
 
});

           </script>
        
    </div>

    </div>
                    
                                <?php } ?>
        
                    
                            <div class="postContent"><?php the_content(); ?></div>
                 
      
                
                
<?php }
        } else { ?>

            <div class="post box">
                <h3><?php _e('There is not post available.', 'localization'); ?></h3>

            </div>

<?php } ?>
                
                
            </div>
        
             <?php if ($sidebar  == 'yes') { ?>
            
                        <ul class="sidebar four columns offset-by-one clearfix">
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Pages")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>




<?php get_footer(); ?>