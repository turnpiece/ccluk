  <?php if(kadence_display_sidebar()) {
        $slide_sidebar = 848;
      } else {
        $slide_sidebar = 1140;
      }
      global $post, $virtue;
      $headcontent = get_post_meta( $post->ID, '_kad_blog_head', true );
      $height      = get_post_meta( $post->ID, '_kad_posthead_height', true );
      $swidth      = get_post_meta( $post->ID, '_kad_posthead_width', true );
      if(empty($headcontent) || $headcontent == 'default') {
          if(!empty($virtue['post_head_default'])) {
              $headcontent = $virtue['post_head_default'];
          } else {
              $headcontent = 'none';
          }
      }
      if (!empty($height)) {
        $slideheight = $height; 
      } else {
        $slideheight = 400;
      }
      if (!empty($swidth)) {
        $slidewidth = $swidth; 
      } else {
        $slidewidth = $slide_sidebar;
      } ?>
  <div id="content" class="container">
    <div class="row single-article" itemscope="" itemtype="http://schema.org/BlogPosting">
      <div class="main <?php echo esc_attr( kadence_main_class() ); ?>" role="main">
        <?php while (have_posts()) : the_post(); ?>
          <article <?php post_class(); ?>>
            <?php if ($headcontent == 'flex') { ?>
              <section class="postfeat">
                <div class="flexslider loading kt-flexslider" style="max-width:<?php echo esc_attr($slidewidth);?>px;" data-flex-speed="7000" data-flex-anim-speed="400" data-flex-animation="fade" data-flex-auto="true">
                  <ul class="slides">
                  <?php $image_gallery = get_post_meta( $post->ID, '_kad_image_gallery', true );
                          if(!empty($image_gallery)) {
                            $attachments = array_filter( explode( ',', $image_gallery ) );
                              if ($attachments) {
                              foreach ($attachments as $attachment) {
                                $attachment_url = wp_get_attachment_url($attachment , 'full');
                                $image = aq_resize($attachment_url, $slidewidth, $slideheight, true);
                                  if(empty($image)) {$image = $attachment_url;}
                                echo '<li><img src="'.esc_url($image).'" itemprop="image"/></li>';
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
                                  echo '<li><img src="'.esc_url($image).'" itemprop="image"/></li>';
                                }
                              } 
                          } ?>                            
                  </ul>
                </div> <!--Flex Slides-->
              </section>
        <?php } else if ($headcontent == 'video') { ?>
          <section class="postfeat">
            <div class="videofit">
                <?php echo get_post_meta( $post->ID, '_kad_post_video', true ); ?>
            </div>
          </section>
        <?php } else if ($headcontent == 'image') {          
                    $thumb = get_post_thumbnail_id();
                    $img_url = wp_get_attachment_url( $thumb,'full' );
                    $image = aq_resize( $img_url, $slidewidth, $slideheight, true ); //resize & crop the image
                    if(empty($image)) { $image = $img_url; }
                    if($image) : ?>
                      <div class="imghoverclass postfeat post-single-img" itemprop="image">
                        <a href="<?php echo esc_url($img_url); ?>" data-rel="lightbox" class="lightboxhover">
                          <img src="<?php echo esc_url($image); ?>" itemprop="image" alt="<?php the_title(); ?>" />
                        </a>
                      </div>
                    <?php endif; ?>
        <?php } ?>
    <?php get_template_part('templates/post', 'date'); ?> 
    <header>
      <h1 class="entry-title" itemprop="name headline"><?php the_title(); ?></h1>
      <?php get_template_part('templates/entry', 'meta-subhead'); ?>  
    </header>
    <div class="entry-content" itemprop="description articleBody">
      <?php the_content(); ?>
    </div>
    <footer class="single-footer">
      <?php $tags = get_the_tags(); if ($tags) { ?>
        <span class="posttags"><i class="icon-tag"></i> <?php the_tags('', ', ', ''); ?> </span>
      <?php } ?>
      <?php $authorbox = get_post_meta( $post->ID, '_kad_blog_author', true );
      if(empty($authorbox) || $authorbox == 'default') {
          if(isset($virtue['post_author_default']) && ($virtue['post_author_default'] == 'yes')) {
            virtue_author_box(); 
          }
      } else if($authorbox == 'yes'){ 
        virtue_author_box(); 
      }?>
      <?php $blog_carousel_recent = get_post_meta( $post->ID, '_kad_blog_carousel_similar', true ); 
      if(empty($blog_carousel_recent) || $blog_carousel_recent == 'default' ) { if(isset($virtue['post_carousel_default'])) {$blog_carousel_recent = $virtue['post_carousel_default']; } }
      if ($blog_carousel_recent == 'similar') { 
        get_template_part('templates/similarblog', 'carousel');
      } else if($blog_carousel_recent == 'recent') {
        get_template_part('templates/recentblog', 'carousel');
      } ?>
      <?php wp_link_pages(array('before' => '<nav class="page-nav"><p>' . __('Pages:', 'virtue'), 'after' => '</p></nav>')); ?>
      <?php if(isset($virtue['show_postlinks']) &&  $virtue['show_postlinks'] == 1) {get_template_part('templates/entry', 'post-links'); }?>
    </footer>
    <?php comments_template('/templates/comments.php'); ?>
  </article>
<?php endwhile; ?>
</div>

