<?php get_header(); ?>


<div class="pageContent">
        
          <?php if (ot_get_option('archivebanner') != '') { ?>
    
        <div class="container">
            
                     <div class="fourteen columns offset-by-one">

                
                    <?php } else { ?>
                
                     <div class="container noBannerContent">
                         
                
                        <div class="sixteen columns">
                       
                     
                    <?php } ?>
                
                            <h1 class="pageTitle">
                     <?php single_cat_title(); ?><?php _e("'S ARCHIVE", 'localization'); ?></h1>
                
      

                    <ul class="newsPostList">
                        
                      <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>

                        
                    <li class="dd_news_post">
                    
                        
                        
                    <div class="singleMeta clearfix">
                        
                        <h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
                        <br>
                        
                      
                            
                          <ul class="metaBtn clearfix">
                                    
                                    <li><a href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" ><span><?php _e('By', 'localization'); ?></span> <?php the_author(); ?></a></li>
                                     <li><a href="<?php echo get_month_link(get_the_time('Y'), get_the_time('m')); ?>"><span><?php _e('On', 'localization'); ?></span> <?php echo the_time('F j, Y'); ?></a></li>
                                       
                                </ul>
                          
                    </div>
                    
                    <div class="excerpt">
                        
                                <?php the_excerpt(); ?>
            
                        
                           <a class="button-small-theme rounded3 " href="<?php the_permalink(); ?>"><?php _e('CONTINUE READING', 'localization'); ?></a>
                                 
                        
                    </div>
                    
                </li>

             <?php }?>

  <?php } else { ?>
                    
                                 
                 <div class="pageContent">
                    
                       <h4 style="margin-top: 0; margin-bottom: 10px;"><?php _e('THERE IS NO POST AVAILABLE', 'localization'); ?></h4>
                       
                         <a href="<?php echo home_url(); ?>"><?php _e('Go Back To Homepage', 'localization'); ?> &rarr;</a>
                </div>
                
                
                             
                           
                        
                  


<?php } ?> 
                 </ul>

                
  
            
            
                 
        <?php
                            kriesi_pagination();

?>                  
                 

                
                
            </div>
   
        </div>
        
    </div>

                         
<?php get_footer(); ?>