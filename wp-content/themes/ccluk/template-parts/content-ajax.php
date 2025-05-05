<?php

/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="posts-stream">
		<div class="loader"><?php _e('Loading...', 'ccluk'); ?></div>
	</div>

	<div class="header-area">
		<?php
		$header_class = '';

		if (has_post_thumbnail()) {
			$thumb_class	 = '';
			$header_class	 = ' has-image';
			$size			 = 'full';

			if (!is_single()) {
				$thumb_class = ' category-thumb';
				$size		 = 'post-thumb';
			}

			$image_id	 = get_post_thumbnail_id();
			$full_image	 = wp_get_attachment_image_src($image_id, 'post-thumb');
		?>

			<a class="entry-post-thumbnail<?php echo $thumb_class; ?>" href="<?php the_permalink(); ?>">
				<img class="<?php $thumb_class ?>" src="<?php echo $full_image[0]; ?>" />
			</a>

		<?php } ?>

		<div class="entry-meta">
			<span class="post-date">
				<time datetime="<?php echo get_the_date('c') ?>"><?php the_date() ?></time>
			</span>
		</div>

		<!-- Title -->
		<header class="entry-header<?php echo $header_class; ?>">
			<h2 class="entry-title">
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(__('Permalink to %s', 'ccluk'), the_title_attribute('echo=0'))); ?>" rel="bookmark"><?php the_title(); ?></a>
			</h2>
		</header><!-- .entry-header -->

	</div><!-- /.header-area -->

	<div class="entry-content entry-summary">

		<?php
		global $post;
		$post_content = $post->post_content;
		?>

		<?php the_excerpt(); ?>

		<footer class="entry-meta">
			<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(__('Permalink to %s', 'ccluk'), the_title_attribute('echo=0'))); ?>" class="read-more"><?php _e('Continue reading', 'ccluk'); ?></a>
			<span class="sep"><?php _e('.', 'ccluk') ?></span>
			<span><?php echo ccluk_estimated_reading_time($post_content); ?></span>
			<a href="#" class="to-top bb-icon-arrow-top-f"></a>
		</footer><!-- .entry-meta -->

	</div><!-- .entry-content -->

</article><!-- #post -->