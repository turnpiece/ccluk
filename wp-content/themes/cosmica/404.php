<?php
get_header(); ?>
	 <div class="heading-name bg-source" >
		<div class="wrap-grid">
			<h2><?php _e('404','cosmica'); ?></h2>
			<h3 class="page-title"><?php _e( 'Not found', 'cosmica' ); ?></h3>
		</div>
	</div>
	<div class="blog-container wrap-grid">
		<section class="blog-content">
			<div class="the-blog-item post">
				<h3 class="the-blog-item-text"><?php _e( 'This is somewhat embarrassing, isn\'t it?', 'cosmica' ); ?></h3>
				<p><?php _e( 'It looks like nothing was found at this location. Maybe try a search?', 'cosmica' ); ?></p>
				<div class="search-form-cont"> 	<?php get_search_form(); ?> 	</div>
			</div>
		</section>
	</div>
<?php get_footer(); ?>