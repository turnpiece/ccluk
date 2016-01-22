<?php global $post; 
    $headcontent = get_post_meta( $post->ID, '_kad_blog_head', true );
    $height      = get_post_meta( $post->ID, '_kad_posthead_height', true ); 
    $swidth      = get_post_meta( $post->ID, '_kad_posthead_width', true );
    if (!empty($height)) {
      $slideheight = $height; 
    } else {
      $slideheight = 400;
    }
    if (!empty($swidth)) {
      $slidewidth = $swidth; 
    } else {
      $slidewidth = 848;
    } ?>
        <article <?php post_class(); ?> itemscope="" itemtype="http://schema.org/BlogPosting">
           <?php if ($headcontent == 'flex') { ?>
               <section class="postfeat">
                <div class="flexslider kt-flexslider" style="max-width:<?php echo esc_attr($slidewidth);?>px;" data-flex-speed="7000" data-flex-anim-speed="400" data-flex-animation="fade" data-flex-auto="true">
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
        <?php } else if ($headcontent == 'video') { ?>
        <section class="postfeat">
          <div class="videofit">
              <?php global $post; echo get_post_meta( $post->ID, '_kad_post_video', true ); ?>
          </div>
        </section>
        <?php } else if ($headcontent == 'image') {           
                    $thumb = get_post_thumbnail_id();
                    $img_url = wp_get_attachment_url( $thumb,'full' ); //get full URL to image (use "large" or "medium" if the images too big)
                    $image = aq_resize( $img_url, $slidewidth, $slideheight, true ); //resize & crop the image
                     if(empty($image)) { $image = $img_url; } 
                    ?>
                    <?php if($image) : ?>
                      <div class="imghoverclass">
                        <a href="<?php echo esc_url($img_url); ?>" data-rel="lightbox" class="lightboxhover">
                          <img src="<?php echo esc_url($image); ?>" alt="<?php the_title(); ?>" />
                        </a>
                      </div>
                    <?php endif; ?>
        <?php } ?>
    <?php get_template_part('templates/post', 'date'); ?>             
    <header>
      <a href="<?php the_permalink() ?>"><h1 class="entry-title" itemprop="name headline"><?php the_title(); ?></h1></a>
      <?php get_template_part('templates/entry', 'meta-subhead'); ?>
    </header>
    <div class="entry-content" itemprop="articleBody">
      <?php global $more; $more = 0; ?>
      <?php $readmore =  __('Continued', 'virtue');
      the_content($readmore); ?>
    </div>
    <footer class="single-footer clearfix">
      <?php $tags = get_the_tags(); if ($tags) { ?> <span class="posttags"><i class="icon-tag"></i> <?php the_tags('', ', ', ''); ?> </span><?php } ?>
      
      <?php wp_link_pages(array('before' => '<nav class="page-nav"><p>' . __('Pages:', 'virtue'), 'after' => '</p></nav>')); ?>
        <?php
  if ( comments_open() ) :
    echo '<p class="kad_comments_link">';
      comments_popup_link( 
        __( 'Leave a Reply', 'virtue' ), 
        __( '1 Comment', 'virtue' ), 
        __( '% Comments', 'virtue' ),
        'comments-link',
        __( 'Comments are Closed', 'virtue' )
    );
    echo '</p>';
  endif;
  ?>
    </footer>
  </article>

