

<footer>
        
        <ul class="container clearfix">
            
              
            
        </ul>
    
        
    </footer>
    
      
   

    <footer>
        
        <ul class="container">
            
             <?php if (!function_exists('dynamic_sidebar') || !dynamic_sidebar("Footer")) ; ?>
            
        </ul>
        
    </footer>
    
    <div class="smallFooter">
        
        <div class="container clearfix">
      
            <div class="sixteen columns">
                
                                   
         <ul class="smallFooterLeft">
                    
                    
                        <?php if (ot_get_option('donatelink') != '') { ?>
                        
                        <li class="donate"><a href="<?php echo ot_get_option('donatelink') ?>"><i class="icon-plus-circled"></i><?php echo ot_get_option('donatebtntext') ?></a></li>
                        
                            <?php } ?>
                        
                         <?php if (ot_get_option('contactlink') != '') { ?>
                        <li class="mail"><a href="<?php echo ot_get_option('contactlink') ?>"><i class="icon-mail"></i></a></li>
                              <?php } ?>
                        
                        <?php if (ot_get_option('twitterlink') != '') { ?>
                        <li class="twitter"><a href="<?php echo ot_get_option('twitterlink') ?>"><i class="icon-twitter"></i></a></li>
                              <?php } ?>
                        
                          <?php if (ot_get_option('youtubelink') != '') { ?>
                        <li class="youtube"><a href="<?php echo ot_get_option('youtubelink') ?>"><i class="icon-youtube"></i></a></li>
                              <?php } ?>
                        
                         <?php if (ot_get_option('facebooklink') != '') { ?>
                        <li class="facebook"><a href="<?php echo ot_get_option('facebooklink') ?>"><i class="icon-facebook"></i></a></li>
                              <?php } ?>
                        
                         
                        
                         <?php if (ot_get_option('vimeolink') != '') { ?>
                        <li class="vimeo"><a href="<?php echo ot_get_option('vimeolink') ?>"><i class="icon-vimeo"></i></a></li>
                              <?php } ?>
                        
                         <?php if (ot_get_option('googlelink') != '') { ?>
                        <li class="google"><a href="<?php echo ot_get_option('googlelink') ?>"><i class="icon-gplus"></i></a></li>
                              <?php } ?>
                        
                   
                        
                         <?php if (ot_get_option('flickrlink') != '') { ?>
                        <li class="flickr"><a href="<?php echo ot_get_option('flickrlink') ?>"><i class="icon-dot-2"></i></a></li>
                              <?php } ?>
                        
                  
                        
                              <?php if (ot_get_option('pinterestlink') != '') { ?>
                        <li class="pinterest"><a href="<?php echo ot_get_option('pinterestlink') ?>"><i class="icon-pinterest"></i></a></li>
                              <?php } ?>
                               <?php if (ot_get_option('linkedinlink') != '') { ?>
                        <li class="linkedin"><a href="<?php echo ot_get_option('linkedinlink') ?>"><i class="icon-linkedin"></i></a></li>
                              <?php } ?>
                          <?php if (ot_get_option('dribbblelink') != '') { ?>
                        <li class="dribbble"><a href="<?php echo ot_get_option('dribbblelink') ?>"><i class="icon-dribbble"></i></a></li>
                              <?php } ?>
                        
                        <?php if (ot_get_option('instagramlink') != '') { ?>
                        <li class="instagram"><a href="<?php echo ot_get_option('instagramlink') ?>"><i class="icon-instagram"></i></a></li>
                              <?php } ?>
                        
                         <?php if (ot_get_option('behancelink') != '') { ?>
                        <li class="behance"><a href="<?php echo ot_get_option('behancelink') ?>"><i class="icon-behance"></i></a></li>
                              <?php } ?>
                        
                                          

                </ul>
        
        <div class="smallFooterRight">
            
            <span><?php echo ot_get_option('smallfooterrightcontent') ?></span>
            
        </div>
                
            </div>
            
        </div>
        
    </div>
    

      <?php wp_footer(); ?> 


  </body>
  
</html>