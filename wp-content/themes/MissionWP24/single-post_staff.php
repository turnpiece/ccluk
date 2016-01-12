<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);

?>

 <?php if ($sidebar  == 'yes') { ?>

<div class="pageContent">
    
        <?php } else { ?>

    <div class="pageContent full">
        
        <?php } ?>

        
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
                
               

            
                  <ul class="clearfix">
                    
          <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>

                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$info1 = get_post_meta(get_the_ID(), 'info1', true);
$info2 = get_post_meta(get_the_ID(), 'info2', true);
$info3 = get_post_meta(get_the_ID(), 'info3', true);
$info4 = get_post_meta(get_the_ID(), 'info4', true);
$pageSlider = get_post_meta(get_the_ID(), 'pageSlider', true);

?>
                      

                        <li <?php post_class('dd_board_post clearfix'); ?>>
                                       
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
                            
							 <?php if( $bigimg) { ?>
                                
                                   <div class="dd_board_post_thumb">
                              
                               <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                              
                          </div>
                        
                                        <?php } ?>   
										
                                <div class="dd_board_post_details">
                              
                              <h4><a class="dd_board_post_title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                              <div class="postContent"><?php the_content(); ?></div>
                                <?php if( $info1) { ?>
                                
                                <h4><?php echo $info1; ?></h4>
                                
                                      <?php } ?>
                                
                                    <?php if( $info2) { ?>
                                
                               <h4><?php echo $info2; ?></h4>

                         <?php } ?>
                               
                               <?php if( $info3) { ?>
                                
                               <h4><?php echo $info3; ?></h4>

                         <?php } ?>
                               
                               <?php if( $info4) { ?>
                                
                               <h4><?php echo $info4; ?></h4>

                         <?php } ?>
                              
                          </div>
                            
                        </li>

        
                        
          
 <?php }
        } else { ?>

            <div class="post box">
                <h3><?php _e('There is not post available.', 'localization'); ?></h3>

            </div>

<?php } ?>
                     
                 </ul>
   
            </div>
        
             <?php if ($sidebar  == 'yes') { ?>
            
                        <ul class="sidebar four columns offset-by-one clearfix">
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Single Staff")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>

                

    <div style="display: none;">
        <?php the_tags();  posts_nav_link(); ?>
    </div>

<?php get_footer(); ?>