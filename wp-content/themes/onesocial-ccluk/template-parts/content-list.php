<?php
/**
 * The template used for displaying page content in page.php
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 * @since OneSocial Theme 1.0.0
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<header class="entry-header">
		<?php if (has_post_thumbnail()) : ?>
			<a href="<?php the_permalink() ?>">
				<?php the_post_thumbnail( 'medium' ) ?>
			</a>
		<?php endif; ?>
		
		<a href="<?php the_permalink() ?>">
			<h2 class="entry-title"><?php the_title(); ?></h2>
		</a>
	</header>

	<div class="entry-content">
		<?php the_excerpt() ?>
	</div>

</article>