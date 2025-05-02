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

</div><!-- #main-wrap (Wrap For Mobile) -->

<?php
global $post;

$post_infinite			 = true;

// don't remove this filter, marketplace plugin uses it
$show_footer = apply_filters('onesocial_show_footer', !((is_archive() || is_home()) && $post_infinite));

if ($show_footer) : ?>

	<footer id="colophon">

		<?php get_template_part('template-parts/footer', 'widgets'); ?>

		<div class="footer-inner-bottom">
			<div class="footer-inner">
				<div id="footer-links">
					<?php
					$show_copyright	 = true;
					$copyright		 = "&copy; 2025 - " . get_bloginfo('name');

					if ($show_copyright && $copyright) {
					?>

						<div class="footer-credits <?php if (!has_nav_menu('secondary-menu')) : ?>footer-credits-single<?php endif; ?>">
							<?php echo $copyright; ?>
						</div>

					<?php } ?>

					<?php if (has_nav_menu('secondary-menu')) : ?>
						<ul class="footer-menu">
							<?php wp_nav_menu(array('container' => false, 'menu_id' => 'nav', 'theme_location' => 'secondary-menu', 'items_wrap' => '%3$s', 'depth' => 1,)); ?>
						</ul>
					<?php endif; ?>

				</div>

				<?php get_template_part('template-parts/footer-social-links'); ?>

			</div><!-- .footer-inner -->

		</div><!-- .footer-inner-bottom -->

		<!-- Don't delete this -->
		<div class="bb-overlay"></div>

	</footer>

<?php endif; ?>

<?php

// Lost Password
get_template_part('template-parts/site-lost-password');
?>

<?php wp_footer(); ?>

</body>

</html>