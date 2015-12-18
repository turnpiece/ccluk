<?php
/**
 * Template part for displaying single posts on the home page.
 *
 * @package politics
 */

?>

<li>
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

		<div class="card post-posts">

		  <div class="image click-effect">
				<?php if ( has_post_thumbnail() ) { ?>
						<a href='<?php echo esc_url( get_permalink() ); ?>'>
							<?php the_post_thumbnail('home-posts'); ?>
						</a>
				<?php	} ?>
		    <a class="date" href='<?php echo esc_url( get_permalink() ); ?>'><?php echo esc_html( get_the_date() ); ?></a>
		  </div><!-- .image -->

			<h4 class="title <?php if ( has_post_thumbnail() ) { echo "has-image"; } ?>">
		    <a href='<?php echo esc_url( get_permalink() ); ?>'><?php the_title(); ?></a>
			</h4>

		</div><!-- .card .post-latest -->

	</article><!-- #post-## -->
</li>
