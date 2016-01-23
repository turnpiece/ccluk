<?php get_header(); ?>


 <?php 

$headerimg = get_post_meta(get_the_ID(), 'headerimg', true);
$sidebar = get_post_meta(get_the_ID(), 'sidebar', true);

?>



    <div class="pageContent full">
        

        
          <?php if( $headerimg) { ?>
    
        <div class="container">
            
              
                
                     <div class="fourteen columns offset-by-one">
                
                 
                
                    <?php } else { ?>
                
                     <div class="container noBannerContent">
                         
                
                        <div class="sixteen columns">
                            
                       
                     
                    <?php } ?>
                
                <h1 class="pageTitle"><?php single_cat_title(); ?></h1>
                
               

            
                  <ul class="clearfix">
                    
      
                      <?php
        if (have_posts ()) {

            while (have_posts ()) {

                (the_post());
        ?>
                      
                      
                             <?php 

$bigimg = get_post_meta(get_the_ID(), 'bigimg', true);
$info1 = get_post_meta(get_the_ID(), 'info1', true);
$info2 = get_post_meta(get_the_ID(), 'info2', true);
$info3 = get_post_meta(get_the_ID(), 'info3', true);
$info4 = get_post_meta(get_the_ID(), 'info4', true);

?>
                      

                        <li <?php post_class('dd_board_post clearfix'); ?>>
                                       
                                            <?php if( $bigimg) { ?>
                                
                                   <div class="dd_board_post_thumb">
                              
                               <a href="<?php the_permalink(); ?>"><img src="<?php echo $bigimg; ?>" alt="" /></a>
                              
                          </div>
                        
                                        <?php } ?>   
                            
                                <div class="dd_board_post_details">
                              
                              <h4><a class="dd_board_post_title" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                              <?php the_excerpt(); ?>
                                <?php if( $info1) { ?>
                                
                                <h4><?php echo $info1; ?></h4>
                                
                                      <?php } ?>
                                
                                    <?php if( $info2) { ?>
                                
                               <h4><?php echo $info2; ?></h4>

                         <?php } ?>
                               
                               <?php if( $info3) { ?>
                                
                               <h4><?php echo $info3; ?></h4>

                         <?php } ?>
                               
                               <?php if( $info4) { ?>
                                
                               <h4><?php echo $info4; ?></h4>

                         <?php } ?>
                              
                          </div>
                            
                        </li>

        
                        
          
                    
<?php } } ?> 
                 

    </ul>
            
             
         <?php
                            kriesi_pagination();

?>                  
            
            
        </div>
      
        
    </div>
      
        
                
            </div>
        
         
                
        </div>
        
    </div>



<?php get_footer(); ?>