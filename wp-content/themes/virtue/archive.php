<?php global $virtue; ?>

<div id="pageheader" class="titleclass">
  <div class="container">
    <?php get_template_part('templates/page', 'header'); ?>
  </div><!--container-->
</div><!--titleclass-->

<div id="content" class="container">
  <div class="row">
        <?php if(isset($virtue['blog_archive_full']) && $virtue['blog_archive_full'] == 'full') {
            $summery    = 'full';
            $postclass  = "single-article fullpost";
          } else {
            $summery = 'normal';
            $postclass = 'postlist';
          } ?>
    <div class="main <?php echo esc_attr(kadence_main_class()); ?>  <?php echo esc_attr($postclass);?>" role="main">

    <?php if (!have_posts()) : ?>
        <div class="alert">
          <?php _e('Sorry, no results were found.', 'virtue'); ?>
        </div>
        <?php get_search_form(); ?>
    <?php endif; ?>

    <?php if($summery == 'full') {
            while (have_posts()) : the_post();
              get_template_part('templates/content', 'fullpost');
            endwhile;
          } else {
            while (have_posts()) : the_post(); 
              get_template_part('templates/content', get_post_format());
            endwhile;
          }
          //Page Navigation
          if ($wp_query->max_num_pages > 1) :
            virtue_wp_pagenav();
          endif; ?>

    </div><!-- /.main -->