<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 * @package WordPress
 * @subpackage OneSocial Theme
 */
?>
</div><!-- #main .wrapper -->
</div><!-- #page -->
</div> <!-- #inner-wrap -->

<!-- Don't delete this -->
<div class="bb-overlay"></div>

</div><!-- #main-wrap (Wrap For Mobile) -->

<?php do_action( 'buddyboss_before_footer' ); ?>

<?php
global $bp, $post;

// don't remove this filter, marketplace plugin uses it
$show_footer = apply_filters( 'onesocial_show_footer', true );

if ( $show_footer ) : ?>

	<footer id="colophon" class="<?php echo $style; ?>">

		<?php get_template_part( 'template-parts/footer', 'widgets' ); ?>

		<div class="footer-inner-bottom">
			<div class="footer-inner">

				<div id="footer-links">

					<div class="footer-credits <?php if ( !has_nav_menu( 'secondary-menu' ) ) : ?>footer-credits-single<?php endif; ?>">
						&copy; 2022 - <?php echo get_bloginfo( 'name' ) ?>
					</div>

					<?php if ( has_nav_menu( 'secondary-menu' ) ) : ?>
						<ul class="footer-menu">
							<?php wp_nav_menu( array( 'container' => false, 'menu_id' => 'nav', 'theme_location' => 'secondary-menu', 'items_wrap' => '%3$s', 'depth' => 1, ) ); ?>
						</ul>
					<?php endif; ?>

				</div>

				<?php get_template_part( 'template-parts/footer-social-links' ); ?>

			</div><!-- .footer-inner -->

		</div><!-- .footer-inner-bottom -->

		<!-- Don't delete this -->
		<div class="bb-overlay"></div>

	</footer>

<?php endif; ?>

<?php do_action( 'bp_footer' ) ?>

<?php wp_footer(); ?>

</body>
</html>