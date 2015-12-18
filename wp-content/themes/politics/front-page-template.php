<?php
/**
 * Template Name: Front Page
 *
 * @package politics
 */
get_header(); ?>

<!-- Home Featured Posts -->
	<div class="featured-posts content-area">

		<div class="home_posts_titles">
			<div class="row">

				<div class="large-12 columns">

					<?php
						$home_posts_section_title = get_theme_mod( 'home_posts_section_title', customizer_library_get_default( 'home_posts_section_title' ) );
						if ( $home_posts_section_title ) { ?>

						<h2><?php echo esc_attr( $home_posts_section_title ); ?></h2>

					<?php } // end home_posts_title

						$home_posts_section_subtitle = get_theme_mod( 'home_posts_section_subtitle', customizer_library_get_default( 'home_posts_section_subtitle' ) );
						if ( $home_posts_section_subtitle ) { ?>

						<p><?php echo esc_attr( $home_posts_section_subtitle ); ?></p>

					<?php } // end home_posts_section_subtitle ?>

				</div><!-- .large-12 -->

			</div><!-- .row -->
		</div><!-- .home_posts_titles -->

		<div class="featured-posts-content">

			<div class="row">

				<div class="large-6 columns">

				<?php
					/**
					 * The first featured post
					 */
					// Get chosen category
					$home_posts_cat = esc_attr( get_theme_mod( 'home_posts_cat' ) );

					// WP_Query arguments
					$args = array (
						'post_type'         => 'post',
						'cat'         	    => $home_posts_cat,
						'posts_per_page'    => '1',
						'post__not_in'	    => get_option('sticky_posts'),
					);

					// The Query
					$query = new WP_Query( $args );

					// The Loop
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();

							get_template_part( 'template-parts/content', 'homelatest' );
						}

					} else {

						get_template_part( 'template-parts/content', 'none' );

					}
					// Restore original Post Data
					wp_reset_postdata();

				?>

			</div><!-- .large-6 -->

			<div class="large-6 columns ">

				<ul class="large-block-grid-2 medium-block-grid-2 small-block-grid-1">

				<?php
					/**
					 * The next four posts
					 */
					$args = array (
						'post_type'         => 'post',
						'cat'         	    => $home_posts_cat,
						'posts_per_page'    => '4',
						'post__not_in'	    => get_option('sticky_posts'),
						'offset'						=> '1'
					);

					// The Query
					$query = new WP_Query( $args );

					// The Loop
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();

							get_template_part( 'template-parts/content', 'home' );
						}

					} else {

						get_template_part( 'template-parts/content', 'none' );

					}
					// Restore original Post Data
					wp_reset_postdata();

				?>

				</ul><!-- .large-block-grid-2 -->

			</div><!-- .large-6 -->

			</div><!-- .row -->

		</div><!-- .featured-posts-content -->

	</div><!-- .featured-posts -->

<!-- Home Parallax Section -->
	<div class="home_paralax content-area clearfix">
		<div class="home_paralax_content">

			<div class="row">

				<div class="large-12 columns">

					<div class="home_paralax_inner_content">

				<?php
					/**
					 * Display content from selected page
					 */
					$home_paralax_content_page = esc_attr( get_theme_mod( 'home_paralax_content_page' ) );

					$args = array (
						'post_type' => 'page',
						'page_id'  => $home_paralax_content_page
					);

					// The Query
					$query = new WP_Query( $args );

					// The Loop
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();

							the_content();
						}

					}

					// Restore original Post Data
					wp_reset_postdata();

				?>

			</div><!-- .home_paralax_inner_content -->

				</div><!-- .large-12 -->

			</div><!-- .row -->

		</div><!-- .home_paralax_content -->
	</div><!-- .color-overlay -->

<!-- Secondary Content Area -->
	<div class="content-area clearfix">

		<div class="home_secondary_content_header">

			<div class="row">

				<div class="large-12 columns">

					<?php
						$home_secondary_content_title = get_theme_mod( 'home_secondary_content_title', customizer_library_get_default( 'home_secondary_content_title' ) );
						if ( $home_secondary_content_title ) { ?>

						<h2><?php echo esc_attr( $home_secondary_content_title ); ?></h2>

					<?php } // end home_posts_title

						$home_secondary_content_subtitle = get_theme_mod( 'home_secondary_content_subtitle', customizer_library_get_default( 'home_secondary_content_subtitle' ) );
						if ( $home_secondary_content_subtitle ) { ?>

						<p><?php echo esc_attr( $home_secondary_content_subtitle ); ?></p>

					<?php } // end home_posts_section_subtitle ?>

				</div><!-- .large-12 -->

			</div><!-- .row -->

		</div><!-- .home_secondary_content -->

		<div class="home-secondary-content">

			<div class="row">

				<div class="large-12 columns">

				<?php
					/**
					 * Display content from selected page
					 */
					$home_secondary_content_page = esc_attr( get_theme_mod( 'home_secondary_content_page' ) );
					$args = array (
						'post_type' => 'page',
						'page_id'  => $home_secondary_content_page
					);

					// The Query
					$query = new WP_Query( $args );

					// The Loop
					if ( $query->have_posts() ) {
						while ( $query->have_posts() ) {
							$query->the_post();

							the_content();
						}

					}

					// Restore original Post Data
					wp_reset_postdata();

				?>

				</div><!-- .large-12 -->

			</div><!-- .row -->

		</div><!-- .home-secondary-content -->

	</div><!-- .content-area -->

<?php get_footer(); ?>
