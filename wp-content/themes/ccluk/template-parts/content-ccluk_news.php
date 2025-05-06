<?php

/**
 * The default template for displaying news content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<!-- Search, Blog index, archives, Profile -->
	<?php if (is_search() || is_archive() || is_home()) : ?>

		<div class="posts-stream">
			<div class="loader"><?php _e('Loading...', 'ccluk'); ?></div>
		</div>

	<?php endif; ?>

	<?php
	if (!is_single()) {
	?>

		<div class="header-area">
			<?php
			$header_class = '';

			if (has_post_thumbnail()) {
				$header_class = ' category-thumb';
			?>

				<a class="entry-post-thumbnail" href="<?php the_permalink(); ?>">
					<?php the_post_thumbnail('post-thumb'); ?>
				</a>

			<?php } ?>

			<div class="entry-meta">
				<span class="post-date">
					<time datetime="<?php echo get_the_date('c') ?>"><?php the_date() ?></time>
				</span>
			</div>

			<!-- Title -->
			<header class="entry-header<?php echo $header_class; ?>">

				<!-- Search, Blog index, archives -->
				<?php if (is_search() || is_archive() || is_home()) : ?>

					<h2 class="entry-title">
						<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr(sprintf(__('Permalink to %s', 'ccluk'), the_title_attribute('echo=0'))); ?>" rel="bookmark"><?php the_title(); ?></a>
					</h2>
					<!-- Single blog post -->
				<?php else : ?>

					<div class="table">
						<div class="table-cell">
							<h1 class="entry-title"><?php the_title(); ?></h1>
						</div>
					</div>

				<?php endif; // is_single()    
				?>

			</header><!-- .entry-header -->

		</div><!-- /.header-area -->

	<?php } ?>

	<!-- Search, Blog index, archives, Profile -->
	<?php if (is_search() || is_archive() || is_home()) : // Only display Excerpts for Search, Blog index, Profile and archives    
	?>

		<div class="entry-content entry-summary">

			<?php
			global $post;
			$post_content = $post->post_content;

			//entry-content
			the_excerpt();

			?>

		</div><!-- .entry-content -->

		<!-- all other templates -->
	<?php else : ?>
		<div class="entry-main">

			<div class="entry-content">
				<?php the_content(__('Continue reading <span class="meta-nav">&rarr;</span>', 'ccluk')); ?>
				<?php wp_link_pages(array('before' => '<div class="page-links">' . __('Pages:', 'ccluk'), 'after' => '</div>')); ?>
			</div><!-- .entry-content -->

			<footer class="entry-meta">
				<div class="row">
					<div class="entry-tags col">
						<?php
						$terms = wp_get_post_tags(get_the_ID());
						if ($terms) {
						?>
							<h3><?php _e('Tagged in', 'ccluk'); ?></h3><?php
																		foreach ($terms as $t) {
																			echo '<a href="' . get_tag_link($t->term_id) . '">' . $t->name . '<span>' . $t->count . '</span></a>';
																		}
																	}
																		?>
					</div>

					<?php if (get_post_status(get_the_ID()) == 'publish') { ?>
						<!-- /.entry-tags -->
						<div class="entry-share col">

							<ul class="helper-links">

								<?php if (function_exists('ADDTOANY_SHARE_SAVE_KIT')) { ?>
									<li>
										<?php ADDTOANY_SHARE_SAVE_KIT(array('use_current_page' => true)); ?>
									</li><?php
										}
											?>
							</ul>
						</div>
						<!-- /.entry-share -->
					<?php } ?>
				</div>

				<?php //edit_post_link( __( 'Edit', 'ccluk' ), '<span class="edit-link">', '</span>' );    
				?>

			</footer><!-- .entry-meta -->
		</div>
		<!-- /.entry-main -->

	<?php endif; ?>

</article><!-- #post -->