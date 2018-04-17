<?php
/**
 * The template used for displaying featured content
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php if (has_post_thumbnail()) : ?>
		<div class="entry-image">
			<a href="<?php the_permalink() ?>"><?php the_post_thumbnail( 'medium' ) ?></a>
		</div>
	<?php endif; ?>

	<div class="entry-content">
		<a href="<?php the_permalink() ?>"><h2 class="entry-title"><?php the_title(); ?></h2></a>
		<?php the_excerpt(); ?>
	</div>

</article>