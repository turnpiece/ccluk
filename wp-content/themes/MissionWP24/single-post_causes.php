<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);
$donatetext = get_post_meta(get_the_ID(), 'donatetext', true);
$donateurl = get_post_meta(get_the_ID(), 'donateurl', true);
$pageSlider = get_post_meta(get_the_ID(), 'pageSlider', true);

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
                     
                          <?php if( $pageSlider) { ?> <?php } else { ?>
                             
                               <a class="donateBtn button-small" href="<?php echo $donateurl; ?>"><i class="icon-plus-circled"></i><?php echo $donatetext; ?></a>

                        
                                 <?php } } ?>
                 
                 </h1>
                 
                    <?php } ?>
                
                    <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>


                    <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$donatetext = get_post_meta(get_the_ID(), 'donatetext', true);
$donateurl = get_post_meta(get_the_ID(), 'donateurl', true);


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
                
                 <?php if( $donatetext) { ?>
                     
   <?php if( $sidebar) { ?><?php } else { ?>
                             
                               <a class="donateBtn button-small" style="margin-top:0;" href="<?php echo $donateurl; ?>"><i class="icon-plus-circled"></i><?php echo $donatetext; ?></a>

                        
                                 <?php } } ?>
                
                                <?php } else if( $bigimg) { ?>
                                
                <div class="causeThumb"> <img src="<?php echo $bigimg; ?>" alt="" /></div>
                        
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
                            
                               <?php if( $donatetext) { ?>
                            
                              <li class="widget">
                    
                        <div class="widget_btn">
                            
                                <a class="donateBtn button-small" href="<?php echo $donateurl; ?>"><i class="icon-plus-circled"></i><?php echo $donatetext; ?></a>
                 
                            
                        </div>
                    
                </li>
                             
                         <?php } ?>
                
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Single Causes")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>

               



<?php get_footer(); ?>