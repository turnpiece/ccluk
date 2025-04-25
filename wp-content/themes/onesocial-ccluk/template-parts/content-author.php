<?php

/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
?>

<aside id="post-<?php the_ID(); ?>-author" class="post-author">
	<div class="author-details">
		<div class="author-top">
			<div class="author vcard">

				<a class="url fn n" href="<?php ccluk_the_user_link(get_the_author_meta('ID')) ?>" title="<?php echo get_the_author(); ?>" rel="author">
					<?php echo get_avatar(get_the_author_meta('ID'), 200, '', get_the_author()); ?>
					<span class="name"><?php echo get_the_author(); ?></span>
				</a>

				<span class="post-date">
					<a href="<?php echo get_permalink(); ?>" title="<?php echo get_the_time(); ?>" rel="bookmark" class="entry-date">
						<time datetime="<?php echo get_the_date('c'); ?>"><?php the_date(); ?></time>
					</a>
				</span>
			</div>
		</div>
	</div>
</aside>