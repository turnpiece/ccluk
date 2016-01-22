<?php  get_header(); ?>

	<div class="container">
		<div id="content-area" class="clearfix">
			<main class="main-content col-md-8 col-md-12">
			<?php	get_template_part( 'includes/loop', 'single' ); ?>

				<div class="clearfix"></div>
			<?php // If comments are open or we have at least one comment, load up the comment template.
			if ( comments_open() || get_comments_number() ) :
				comments_template();
			endif; ?>
			</main>
			<?php get_sidebar(); ?>
			<div class="clearfix"></div>
						
		</div> <!-- #content-area -->
	</div> <!-- .container --> 
<?php  get_footer();?>