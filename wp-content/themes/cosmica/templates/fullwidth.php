<?php
/**
Template Name: Full Width
*/
?>
<?php  get_header(); ?>
	<div class="container">
		<div id="content-area" class="clearfix">
			<main class="main-content col-md-12">
				<?php get_template_part( 'includes/loop', 'page' ); ?>
				<div class="clearfix"></div>
				<div id="pagination">
					<?php echo paginate_links(array('prev_next' => true, 'prev_text' => __('&#171; Previous Page','cosmica'), 'next_text' => __('Next Page &#187;','cosmica'))); ?>
				</div>
				<div class="clearfix"></div>
			</main>
			<div class="clearfix"></div>
		</div> <!-- #content-area -->
	</div> <!-- .container -->
<?php  get_footer();?>

