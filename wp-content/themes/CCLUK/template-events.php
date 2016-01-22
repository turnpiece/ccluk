<?php /*
  Template Name: Events Template
 */ ?>


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
                
               

            
                  <ul>
                    
                       <?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$arguments = array(
    'post_type' => 'post_events',
    'post_status' => 'publish',
    'paged' => $paged
);

$events_query = new WP_Query($arguments);

dd_set_query($events_query);

?>
                     
                       <?php if ($events_query->have_posts()) : while ($events_query->have_posts()) : $events_query->the_post(); ?>
                      
                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$facebooktext = get_post_meta(get_the_ID(), 'facebooktext', true);
$facebookurl = get_post_meta(get_the_ID(), 'facebookurl', true);
$eventsmonth = get_post_meta(get_the_ID(), 'eventsmonth', true);
$eventsday = get_post_meta(get_the_ID(), 'eventsday', true);

?>
                      
                        <li <?php post_class('dd_events_post'); ?>>
                            
                        
                                    <div class="dd_events_thumb">
                                        
                                            <?php if( $bigimg) { ?>
                                
                                <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                        
                                        <?php } ?>                                        
                                        
                                    </div>
                                    
                        <div class="dd_events_top clearfix">
                            
                                   <?php if( $eventsmonth) { ?>
                            
                            <div class="dateContainer">

                                <span class="month"><?php echo( $eventsmonth )  ?></span>
                                     <span class="day"><?php echo( $eventsday )  ?></span>
                                     
                        </div>
                            
                              <div class="postTitle">
                            
                            <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                            
                                   <?php } else { ?>
                    
                        <div class="postTitle">
                            
                            <h1 style="width: 100% !important;"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                            
                        </div>
                            
                                    <?php }  ?>
                            
                        </div>
                        
                                    <?php the_excerpt(); ?>
                            
                        <div class="dd_events_btn">
                            
                                    <?php if( $facebooktext) { ?>
                            
                                 <a class="facebookBtn button-small" href="<?php echo $facebookurl; ?>"><i class="icon-facebook"></i><?php echo $facebooktext; ?></a>
                 
                               
                           <span class="or"><?php _e('OR', 'localization'); ?></span>
                        
                                    <?php } ?>
                        
                      <a class="continue button-small-theme" href="<?php the_permalink(); ?>"><?php _e('MORE INFO', 'localization'); ?></a>
                            
                        </div>
                        
                    </li>
                            
                  
           
      <?php endwhile; ?>
                    
                

<?php endif; ?>
                     
                 </ul>

                 
        <?php
                            kriesi_pagination();

                            dd_restore_query();
?>                  
                 
      
        
                
            </div>
        
             <?php if ($sidebar  == 'yes') { ?>
            
                        <ul class="sidebar four columns offset-by-one clearfix">
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Events")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>

                



<?php get_footer(); ?>