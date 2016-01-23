<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);

$pageSlider = get_post_meta(get_the_ID(), 'pageSlider', true);

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

                
               

            
                  <ul>
                    

                         <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>


                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$pageSlider = get_post_meta(get_the_ID(), 'pageSlider', true);

?>
                      
                  
                           <li <?php post_class('dd_news_post'); ?>>
                               
                                               <?php if( $pageSlider) { ?>
                    
                                <div class="postTitle clearfix">
                            
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                               
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
                               
                        <div class="postTitleWithImage clearfix">
                            
                        <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                               
                                       <?php } else { ?>
                               
                               
                                 <div class="postTitle clearfix">
                            
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                        
                                       <?php } ?>
                               
                  
                                <ul class="metaBtn clearfix">
                                    
                                    <li><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" ><span><?php _e('By', 'localization'); ?></span> <?php the_author(); ?></a></li>
                                      <li><a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><span><?php _e('On', 'localization'); ?></span> <?php echo the_time('F j, Y'); ?></a></li>
                                   
                                    
                                </ul>
                        
                               <div class="postCategories"> <span><?php _e('Posted In', 'localization'); ?></span> <?php the_category(', ');?>    </div>
                               
                               <div class="postContent"><?php the_content(); ?></div>
                        
                    </li>
                                     

    <?php }
    
        } else { ?>

            <div class="post box">
                <h3><?php _e('There is not post available.', 'localization'); ?></h3>

            </div>

<?php } ?>
                     
                 </ul>      
        
                     <?php comments_template( '', true ); ?>
                            
            </div>
        
             <?php if ($sidebar  == 'yes') { ?>
            
                        <ul class="sidebar four columns offset-by-one clearfix">
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Single News")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>

               
              
     
        </div>
        
    </div>

                  


<?php get_footer(); ?>