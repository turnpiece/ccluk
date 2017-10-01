<?php
/*
 *
 * Template Name: Write to your MP
 *
 */
 get_header();
?>
<div id="primary" class="site-content default-page">

	<div id="content" role="main">

		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/content', 'page' ); ?>
		<?php endwhile; // end of the loop.  ?>

		<div class="bluebox" id="findYourMP">
			<form method="get" action="https://www.writetothem.com/who">
				<label for="pc"><?php _e( 'Post code', 'onesocial' ) ?></label>
				<input type="text" name="pc" id="pc" />
				<input type="submit" name="submit" value="Go" />
			</form>
		</div>
	</div>
</div>

<?php

get_sidebar();

get_footer();