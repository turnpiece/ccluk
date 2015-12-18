<?php
/**
 * Template part for displaying posts on the blog page in
 * masonry style.
 *
 * @package politics
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'masonry-post' ); ?>>

	<div class="card post-featured">

	  <div class="image click-effect">
			<?php if ( has_post_thumbnail() ) { ?>
					<a href='<?php echo esc_url( get_permalink() ); ?>'>
						<?php the_post_thumbnail('post-featured'); ?>
					</a>
			<?php	} ?>
	    <span class="date"><?php echo esc_html( get_the_date() ); ?></span>
	  </div>

		<h4 class="title <?php if ( has_post_thumbnail() ) { echo "has-image"; } ?>">
			<a href='<?php echo esc_url( get_permalink() ); ?>'><?php the_title(); ?></a>
		</h4>

	</div><!-- .card .post-featured -->

</article><!-- #post-## -->
