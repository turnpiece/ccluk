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
            
            
                
                     <div class="fourteen columns offset-by-one">
                
           
                
                    <?php } else { ?>
                
                     <div class="container noBannerContent">
                         
                                
                
                        <div class="sixteen columns">
                            
                 
                     
                    <?php } ?>
                
                <h1 class="pageTitle"><?php single_cat_title(); ?></h1>
                
               

            
                  <ul>
                    
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
                            
                                 <a class="facebookBtn button-small" href="<?php echo $facebookurl; ?>"><i class="icon-facebook-squared"></i><?php echo $facebooktext; ?></a>
                 
                               
                           <span class="or"><?php _e('OR', 'localization'); ?></span>
                        
                                    <?php } ?>
                        
                      <a class="continue button-small-theme" href="<?php the_permalink(); ?>"><?php _e('MORE INFO', 'localization'); ?></a>
                            
                        </div>
                        
                    </li>
                            
                  
           
                            <?php }} ?>
                     
                 </ul>

                 
        <?php
                            kriesi_pagination();

       
?>                  
                 
      
        
                
            </div>
                      
        </div>
        
    </div>


<?php get_footer(); ?>