<div id="pageheader" class="titleclass">
  <div class="container">
      <?php get_template_part('templates/page', 'header'); ?>
  </div><!--container-->
</div><!--titleclass-->

<?php $homeid = get_option( 'page_for_posts' );
  if(get_post_meta( $homeid, '_kad_blog_summery', true ) == 'full') {
    $summary    = 'full'; 
    $postclass  = "single-article fullpost";
  } else {
    $summary    = 'normal';
    $postclass  = 'postlist';
  } ?>

<div id="content" class="container">
  <div class="row">
    <div class="main <?php echo esc_attr(kadence_main_class()); ?>  <?php echo esc_attr($postclass) .' '. esc_attr($fullclass); ?>" role="main">

      <?php if (!have_posts()) : ?>
        <div class="alert">
          <?php _e('Sorry, no results were found.', 'virtue'); ?>
        </div>
        <?php get_search_form(); ?>
      <?php endif; ?>

       <?php if($summary == 'full'){
                      while (have_posts()) : the_post(); 
                          get_template_part('templates/content', 'fullpost'); 
                      endwhile; 
            } else {
                      while (have_posts()) : the_post(); 
                          get_template_part('templates/content', get_post_format());
                      endwhile; 
            } ?>


      <?php //Page Navigation
        if ($wp_query->max_num_pages > 1) :
          virtue_wp_pagenav();
        endif; ?>
        
    </div><!-- /.main -->