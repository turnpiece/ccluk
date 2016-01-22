<div id="pageheader" class="titleclass">
  <div class="container">
    <?php get_template_part('templates/page', 'header'); ?>
  </div><!--container-->
</div><!--titleclass-->
  
<div id="content" class="container">
  <div class="row">
    <div class="main <?php echo esc_attr(kadence_main_class()); ?>  postlist" role="main">

      <?php if (!have_posts()) : ?>
        <div class="alert">
          <?php _e('Sorry, no results were found.', 'virtue'); ?>
        </div>
        <?php get_search_form(); ?>
      <?php endif; ?>

      <?php while (have_posts()) : the_post(); ?>
        <?php get_template_part('templates/content', get_post_format()); ?>
      <?php endwhile; ?>

      <?php //Page Navigation
        if ($wp_query->max_num_pages > 1) :
          virtue_wp_pagenav();
        endif; ?>

    </div><!-- /.main -->