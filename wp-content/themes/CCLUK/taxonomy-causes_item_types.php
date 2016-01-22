<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);

?>


<div class="pageContent">
        
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
                        
                                      <?php }} ?>
                 </ul>

                 
        <?php
                            kriesi_pagination();

                  
?>                  
                 
      
        
                
            </div>
        
           
        </div>
        
    </div>


<?php get_footer(); ?>