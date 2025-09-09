<?php

/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage CCL UK Theme
 * @since CCL UK Theme 1.0.0
 */
?>

<aside id="post-<?php the_ID(); ?>-author" class="post-author">

	<div class="author-details">
		<div class="author-top">

			<div class="author vcard">

				<span class="post-date">
					<a href="<?php echo get_permalink(); ?>" title="<?php echo get_the_time(); ?>" rel="bookmark" class="entry-date">
						<time datetime="<?php echo get_the_date('c'); ?>"><?php the_date(); ?></time>
					</a>
				</span>

				<div class="load-more-posts">
					<a href="<?php echo get_the_author_meta('ID'); ?>" data-sort="recommended" data-target="target-<?php the_ID(); ?>" data-sequence="500"><?php _e('Most recommended stories', 'ccluk'); ?></a>
					<a href="<?php echo get_the_author_meta('ID'); ?>" data-sort="latests" class="show-latest" data-target="target-<?php the_ID(); ?>" data-sequence="500"><?php _e('Latest stories', 'ccluk'); ?></a>
				</div>
			</div>
		</div>

	</div>

</aside>