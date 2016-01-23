<?php get_template_part('templates/head'); ?>
  <body <?php body_class(); ?>>
    <div id="wrapper" class="container">
    <?php do_action('get_header');
        get_template_part('templates/header');
    ?>
      <div class="wrap contentclass" role="document">

      <?php do_action('kt_afterheader');

          include kadence_template_path(); ?>
            
          <?php if (kadence_display_sidebar()) : ?>
            <aside class="<?php echo esc_attr(kadence_sidebar_class()); ?> kad-sidebar" role="complementary">
              <div class="sidebar">
                <?php include kadence_sidebar_path(); ?>
              </div><!-- /.sidebar -->
            </aside><!-- /aside -->
          <?php endif; ?>
          </div><!-- /.row-->
        </div><!-- /.content -->
      </div><!-- /.wrap -->
      <?php do_action('get_footer');
      get_template_part('templates/footer'); ?>
    </div><!--Wrapper-->
  </body>
</html>
