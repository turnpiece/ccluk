<section class="pagefeat container">
    <?php global $post;
    $height = get_post_meta( $post->ID, '_kad_posthead_height', true );
    $swidth = get_post_meta( $post->ID, '_kad_posthead_width', true );
    if (!empty($height)){
      $slideheight = $height; 
    } else {
      $slideheight = 400;
    }
    if (!empty($swidth)) {
      $slidewidth = $swidth; 
    } else {
      $slidewidth = 1140;
    } ?>
      <div class="flexslider loading kt-flexslider" style="max-width:<?php echo esc_attr($slidewidth);?>px;" data-flex-speed="7000" data-flex-anim-speed="400" data-flex-animation="fade" data-flex-auto="true">
          <ul class="slides">
            <?php global $post;
                $image_gallery = get_post_meta( $post->ID, '_kad_image_gallery', true );
                          if(!empty($image_gallery)) {
                            $attachments = array_filter( explode( ',', $image_gallery ) );
                              if ($attachments) {
                              foreach ($attachments as $attachment) {
                                $attachment_url = wp_get_attachment_url($attachment , 'full');
                                $image = aq_resize($attachment_url, $slidewidth, $slideheight, true);
                                  if(empty($image)) {$image = $attachment_url;}
                                echo '<li><img src="'.esc_url($image).'"/></li>';
                              }
                            }
                          } else {
                            $attach_args = array('order'=> 'ASC','post_type'=> 'attachment','post_parent'=> $post->ID,'post_mime_type' => 'image','post_status'=> null,'orderby'=> 'menu_order','numberposts'=> -1);
                            $attachments = get_posts($attach_args);
                              if ($attachments) {
                                foreach ($attachments as $attachment) {
                                  $attachment_url = wp_get_attachment_url($attachment->ID , 'full');
                                  $image = aq_resize($attachment_url, $slidewidth, $slideheight, true);
                                    if(empty($image)) {$image = $attachment_url;}
                                  echo '<li><img src="'.esc_url($image).'"/></li>';
                                }
                              } 
                          } ?>                  
            </ul>
          </div> <!--Flex Slides-->
</section>