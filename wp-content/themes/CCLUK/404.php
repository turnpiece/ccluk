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
                
        
                    <h4><?php _e('PAGE NOT FOUND', 'localization'); ?></h4>
                  <p><?php _e("Looks like something went completely wrong! But don't worry - it can happen to the best of us, - and it just happened to you.", 'localization'); ?></p>
                     <a href="<?php echo home_url(); ?>"><?php _e('Go Back To Homepage', 'localization'); ?> &rarr;</a>
                     
                
                
            </div>
        
             <?php if ($sidebar  == 'yes') { ?>
            
                        <ul class="sidebar four columns offset-by-one clearfix">
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Pages")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>




<?php get_footer(); ?>