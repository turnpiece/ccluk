<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);
$donatetext = get_post_meta(get_the_ID(), 'donatetext', true);
$donateurl = get_post_meta(get_the_ID(), 'donateurl', true);

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
                
                               <?php if ($sidebar  == 'yes') { ?>
                            
                <h1 class="pageTitle"><?php the_title(); ?></h1>
                
                
                    <?php } else { ?>
                
                 <h1 class="pageTitle full"><?php the_title(); ?>
                 
                  <?php if( $donatetext) { ?>
                             
                               <a class="donateBtn button-small" href="<?php echo $donateurl; ?>"><i class="icon-plus-circled"></i><?php echo $donatetext; ?></a>

                        
                                 <?php } ?>
                 
                 </h1>
                 
                    <?php } ?>
                
                    <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>


                    <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$facebooktext = get_post_meta(get_the_ID(), 'facebooktext', true);
$facebookurl = get_post_meta(get_the_ID(), 'facebookurl', true);
$eventsmonth = get_post_meta(get_the_ID(), 'eventsmonth', true);
$eventsday = get_post_meta(get_the_ID(), 'eventsday', true);
$eventModule = get_post_meta(get_the_ID(), 'eventModule', true);
$info1 = get_post_meta(get_the_ID(), 'info1', true);
$info2 = get_post_meta(get_the_ID(), 'info2', true);
$info3 = get_post_meta(get_the_ID(), 'info3', true);
$info4 = get_post_meta(get_the_ID(), 'info4', true);
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
                    
                                <?php } else if( $bigimg) { ?>
                                
                <div class="causeThumb"> <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a></div>
                        
                                        <?php } ?>
                                
                  <?php if ($eventModule  == 'yes') { ?>
                         
                        <?php if ($sidebar  == 'yes') { ?>
                
                <div class="eventMeta clearfix">
                    
                                    <?php } else { ?>
                    
                               <div class="eventMeta eventMetaFull clearfix">
                                   
                                    <?php } ?>
                    
                     <div class="dateContainer">

                                <span class="month"><?php echo( $eventsmonth )  ?></span>
                                     <span class="day"><?php echo( $eventsday )  ?></span>
                                     
                        </div>
                    
                    <div class="info">
                        
                        <?php if( $facebooktext) { ?>
                            
                         <?php if ($sidebar  == 'yes') { ?>
                        
                                            <?php } else { ?>
                        
                        <div class="facebookBtnWidget">
                            
                                <a class="facebookBtn button-small" href="<?php echo $facebookurl; ?>"><i class="icon-facebook"></i><?php echo $facebooktext; ?></a>
                            
                        </div> <?php } ?>
                                
                                      <?php } ?>
                                
                                    <?php if( $info1) { ?>
                                
                                <span><?php echo $info1; ?></span>
                                
                                      <?php } ?>
                                
                                    <?php if( $info2) { ?>
                                
                               <span><?php echo $info2; ?></span>

                         <?php } ?>
                               
                               <?php if( $info3) { ?>
                                
                               <span><?php echo $info3; ?></span>

                         <?php } ?>
                               
                               <?php if( $info4) { ?>
                                
                               <span><?php echo $info4; ?></span>

                         <?php } ?>
                        
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
                            
                               <?php if( $facebooktext) { ?>
                            
                              <li class="widget">
                    
                        <div class="widget_btn">
                            
                                <a class="facebookBtn button-small" href="<?php echo $facebookurl; ?>"><i class="icon-facebook"></i><?php echo $facebooktext; ?></a>
                 
                            
                        </div>
                    
                </li>
                             
                         <?php } ?>
                
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Single Events")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>




<?php get_footer(); ?>