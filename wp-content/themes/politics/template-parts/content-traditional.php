<?php
/**
 * Template part for displaying posts on the blog page in
 * traditional list style with sidebar.
 *
 * @package politics
 */

?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<div class="featured-image click-effect">
    <?php if ( has_post_thumbnail() ) { ?>
        <a href='<?php echo esc_url( get_permalink() ); ?>'>
          <?php the_post_thumbnail('pages-posts'); ?>
        </a>
    <?php	} ?>
	</div><!-- .featured-image -->

	<header class="entry-header">
    <a href='<?php echo esc_url( get_permalink() ); ?>'>
		    <?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
    </a>
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

		</div><!-- .large-2 -->

		<div class="large-9 columns">

			<div class="entry-content">
				<?php the_content(); ?>
			</div><!-- .entry-content -->

		</div><!-- .large-9 -->

	</div><!-- .row -->

</article><!-- #post-## -->
