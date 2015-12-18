<?php
/**
 * The template for displaying 404 pages (not found).
 *
 * @package politics
 */

get_header(); ?>

<div class="row">

  <div class="large-12 columns">

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<section class="error-404 not-found">
				<header class="page-header">
					<h1 class="page-title"><?php esc_html_e( 'Oops! That page can&rsquo;t be found.', 'politics' ); ?></h1>
				</header><!-- .page-header -->

        <div class="row">
          <div class="large-5 large-centered columns">

				<div class="page-content">
          <i class="fa fa-object-ungroup"></i>
          <p><?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'politics' ); ?></p>
          <?php get_search_form(); ?>
				</div><!-- .page-content -->

            </div><!-- .large-5 -->
        </div><!-- .row -->

			</section><!-- .error-404 -->

		</main><!-- #main -->
	</div><!-- #primary -->

	</div><!-- .large-12 -->

</div><!-- .row -->

<?php get_footer(); ?>
