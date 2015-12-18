<?php
/**
 * Template part for displaying single posts.
 *
 * @package politics
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="featured-image">
		<?php
			if ( has_post_thumbnail() ) {
					the_post_thumbnail('pages-posts');
			}
		?>
	</div><!-- .featured-image -->

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<hr>

	<div class="row">

		<div class="large-3 columns">

			<div class="entry-meta">

				<div class="posted-date">
					<h3><?php _e('Posted','politics'); ?></h3>
					<p><?php echo esc_html( get_the_date() ); ?></p>
				</div><!-- .posted-date -->

			<hr>

				<div class="post-author">
					<h3><?php _e('Written By','politics'); ?></h3>
					<p><?php echo esc_html( get_the_author() ); ?></p>
				</div>

			<hr>

<?php
		// Create variable to adjust grid later
		$social_pages = NULL;

		// Check if Jetpack Social Sharing is activated
		if ( function_exists( 'sharing_display' ) ) {

			// Get the global settings
			$options = get_option( 'sharing-options' );
			// Get the page settings
			$disabled = get_post_meta( get_the_ID(), 'sharing_disabled' );

			// Assign variable to pages disable setting
			if ( array_key_exists( 0, $disabled ) ) {
				$disabled = 'true';
			}

			// Load up the global post types that are selected
			$types = $options['global']['show'];

			// Loop through the post types
			foreach ( $types as $type ) {

				// If 'post' is selected, we'll make the variable true
				if ( $type == 'post') {
					$social_pages = 'true';
				}

			}

			// If not disabled on individual page, display social
			if ( $social_pages == 'true' && $disabled !== 'true' ) { ?>

				<!-- Jetpack social sharing icons -->
        <div class="social-share">
          <?php echo wp_kses_post( sharing_display() ); ?>
        </div><!-- .social-share -->

			<hr>

<?php
		} // end sharing_display function check
	} // end sharing_display
?>

			</div><!-- .entry-meta -->

		</div><!-- .large-3 -->

		<div class="large-9 columns">

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'politics' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->

			<footer class="entry-footer">
				<h2><?php esc_html_e( 'You might also be interested in&#58;', 'politics' ); ?></h2>
				<?php the_post_navigation(); ?>
			</footer><!-- .entry-footer -->

		</div><!-- .large-9 -->

	</div><!-- .row -->

</article><!-- #post-## -->
