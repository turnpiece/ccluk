<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the #content div and all content after
 *
 * @package politics
 */

?>
	</div><!-- #content -->

</div><!-- #page -->

<div class="footer-wrap" role="contentinfo">

	<footer id="colophon" class="site-footer">

		<div class="row footer-full-wrap">

			<div class="large-12 columns">
				<?php if ( is_active_sidebar( 'footer-full' ) ) { ?>

					<?php dynamic_sidebar( 'footer-full' ); ?>

				<?php } ?>
			</div>

		</div><!-- .row -->

		<div class="row footer-quarters-wrap">

			<div class="large-3 columns">
				<?php if ( is_active_sidebar( 'footer-first' ) ) { ?>

					<?php dynamic_sidebar( 'footer-first' ); ?>

				<?php } ?>
			</div>

			<div class="large-3 columns">
				<?php if ( is_active_sidebar( 'footer-second' ) ) { ?>

					<?php dynamic_sidebar( 'footer-second' ); ?>

				<?php } ?>
			</div>

			<div class="large-3 columns">
				<?php if ( is_active_sidebar( 'footer-third' ) ) { ?>

					<?php dynamic_sidebar( 'footer-third' ); ?>

				<?php } ?>
			</div>

			<div class="large-3 columns">
				<?php if ( is_active_sidebar( 'footer-fourth' ) ) { ?>

					<?php dynamic_sidebar( 'footer-fourth' ); ?>

				<?php } ?>
			</div>

		</div><!-- .row -->

		<div class="row">

			<div class="large-12 columns">

				<hr>

				<div class="copyright-info">

					<?php
						$footer_copyright = get_theme_mod( 'footer_copyright', customizer_library_get_default( 'footer_copyright' ));

						echo wp_kses( $footer_copyright,
							array(
								'strong' => array(),
						    'a' => array(
						      'href' => array(),
						      'title' => array()
						    ),
								)
							);

					?>

				</div><!-- .copyright-info -->

			</div><!-- .large-12 -->

		</div><!-- .row -->

	</footer><!-- #colophon -->

</div><!-- .footer-wrap -->

<?php wp_footer(); ?>

</body>
</html>
