<?php /*
  Template Name: News Template
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
                global $paged;


                $arguments = array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'paged' => $paged
                );

                $blog_query = new WP_Query($arguments);

                dd_set_query($blog_query);
            ?>
                    
         <?php if ($blog_query->have_posts()) : while ($blog_query->have_posts()) : $blog_query->the_post(); ?>


                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);

?>
                      
                  
                           <li <?php post_class('dd_news_post'); ?>>
                               
                                     <?php if( $bigimg) { ?>
                               
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
                               
                                       <?php the_excerpt(); ?>
                        
                               <a class="continue" href="<?php the_permalink(); ?>"><?php _e('CONTINUE READING', 'localization'); ?> &rarr;</a>
                        
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
                             
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("News")) ; ?>
                             
                             </ul>
                
                        <?php }  ?>
                
        </div>
        
    </div>

               



<?php get_footer(); ?>