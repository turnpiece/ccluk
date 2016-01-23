<?php  get_header(); ?>
	<?php  if('page' == sanitize_text_field(get_option('show_on_front'))){ get_template_part('index');} else{ ?>
	<?php cosmica_demo_slider(); ?>
	<?php get_template_part('index','services'); ?>
	<?php get_template_part('index','callout'); ?>
	<?php get_template_part('index','clients'); }  ?>
<?php  get_footer(); ?>