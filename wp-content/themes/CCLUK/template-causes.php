<?php /*
  Template Name: Causes Template
 */ ?>


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
                
               

            
                  <ul>
                    
                       <?php
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$arguments = array(
    'post_type' => 'post_causes',
    'post_status' => 'publish',
    'paged' => $paged
);

$causes_query = new WP_Query($arguments);

dd_set_query($causes_query);

?>
                     
                       <?php if ($causes_query->have_posts()) : while ($causes_query->have_posts()) : $causes_query->the_post(); ?>
                      
                        <li <?php post_class('dd_causes_post'); ?>>
                            
                            
                            <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$donatetext = get_post_meta(get_the_ID(), 'donatetext', true);
$donateurl = get_post_meta(get_the_ID(), 'donateurl', true);

?>
                        
                            
                               <?php if( $bigimg) { ?>
                            
                            <div class="causeTitleWImg">

                                <div class="widgetWrapper">
                                    
                                       <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>

                         <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                    
                                </div>

                            </div>
                            
                                        <?php } else { ?>
                            
                            <div class="causeTitle">


                         <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

                            </div>
                            
                                    <?php } ?>
                        
                     <?php the_excerpt(); ?>
                            
                         <div class="widget_btn">
                            
                             <?php if( $donatetext) { ?>
                             
                               <a class="donateBtn button-small" href="<?php echo $donateurl; ?>"><i class="icon-plus-circled"></i><?php echo $donatetext; ?></a>
                               
                      
                               
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
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Causes")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>


<?php get_footer(); ?>