<?php
/**
 * Shipper templates: shared footer template
 *
 * @package shipper
 */

?>
<footer class="shipper-footer">
	<div class="sui-footer">
		<?php echo wp_kses_post( Shipper_Helper_Assets::get_footer_text() ); ?>
	</div>

<?php if ( ! Shipper_Helper_Assets::has_custom_footer() ) { ?>
	<ul class="sui-footer-nav">
		<li><a href="https://wpmudev.com/hub2/" target="_blank">The Hub</a></li>
		<li><a href="https://wpmudev.com/projects/category/plugins/" target="_blank">Plugins</a></li>
		<li><a href="https://wpmudev.com/roadmap/" target="_blank">Roadmap</a></li>
		<li><a href="https://wpmudev.com/hub2/support/" target="_blank">Support</a></li>
		<li><a href="https://wpmudev.com/docs/" target="_blank">Docs</a></li>
		<li><a href="https://wpmudev.com/hub2/community/" target="_blank">Community</a></li>
		<li><a href="https://wpmudev.com/terms-of-service/" target="_blank">Terms of Service</a></li>
		<li><a href="https://incsub.com/privacy-policy/" target="_blank">Privacy Policy</a></li>
	</ul>

	<ul class="sui-footer-social">
		<li><a href="https://www.facebook.com/wpmudev" target="_blank">
			<i class="sui-icon-social-facebook" aria-hidden="true"></i>
			<span class="sui-screen-reader-text">Facebook</span>
		</a></li>
		<li><a href="https://twitter.com/wpmudev" target="_blank">
			<i class="sui-icon-social-twitter" aria-hidden="true"></i></a>
			<span class="sui-screen-reader-text">Twitter</span>
		</li>
		<li><a href="https://www.instagram.com/wpmu_dev/" target="_blank">
			<i class="sui-icon-instagram" aria-hidden="true"></i>
			<span class="sui-screen-reader-text">Instagram</span>
		</a></li>
	</ul>
<?php } ?>
</footer>