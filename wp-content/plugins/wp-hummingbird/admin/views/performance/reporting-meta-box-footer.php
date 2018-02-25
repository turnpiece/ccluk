	<?php if ( ! wphb_is_member() ) : ?>
		<div class="content-box-two-cols-image-left">
			<div class="wphb-block-entry-content wphb-upsell-free-message">
				<?php printf(
					__( '<p>Schedule automated performance tests and receive email reports direct to your inbox. You\'ll get Hummingbird Pro plus 100+ WPMU DEV plugins, themes & 24/7 WP support. <a href="%s" rel="dialog">Try Pro for FREE today!</a></p>', 'wphb' ),
					'#wphb-upgrade-membership-modal'
				); ?>
			</div>
		</div>
		<?php wphb_membership_modal(); ?>
	<?php else : ?>
		<div class="buttons alignright">
			<button class="button button-large">
				<?php esc_html_e( 'Update Settings', 'wphb' ) ?>
			</button>
		</div>
		<div class="clear"></div>
	<?php endif; ?>
</form>