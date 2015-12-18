<?php
/**
 * The template used for displaying page content in template-full.php
 *
 * @package politics
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="featured-image">
		<?php
			if ( has_post_thumbnail() ) {
					the_post_thumbnail('full-width');
			}
		?>
	</div><!-- .featured-image -->

	<header class="entry-header">
		<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
	</header><!-- .entry-header -->

	<hr>

	<div class="row">

		<?php
				// Create variable to adjust grid later
				$social_pages = '';

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

						// If 'page' is selected, we'll make the variable true
						if ( $type == 'page') {
							$social_pages = 'true';
						}
					}

					// If not disabled on individual page, display social
					if ( $social_pages == 'true' && $disabled !== 'true' ) { ?>

					<!-- Meta Column -->
					<div class="large-2 columns">
						<div class="entry-meta">

							<!-- Jetpack social sharing icons -->
			        <div class="social-share">
			          <?php echo wp_kses_post( sharing_display() ); ?>
			        </div><!-- .social-share -->
							<hr>

						</div><!-- .entry-meta -->
					</div><!-- .large-2 -->

		<?php
				} // end sharing_display function check
			} // end sharing_display
		?>

		<div class="<?php if ( $social_pages == 'true' && $disabled !== 'true' ) { echo "large-10"; } else { echo "large-12"; } ?> columns">

			<div class="entry-content">
				<?php the_content(); ?>
				<?php
					wp_link_pages( array(
						'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'politics' ),
						'after'  => '</div>',
					) );
				?>
			</div><!-- .entry-content -->

			<footer class="entry-footer-page">
				<?php edit_post_link( esc_html__( 'Edit', 'politics' ), '<span class="edit-link">', '</span>' ); ?>
			</footer><!-- .entry-footer -->

		</div><!-- .large-10 -->

	</div><!-- .row -->

</article><!-- #post-## -->
