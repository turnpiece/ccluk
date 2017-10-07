<?php if ( ! wphb_is_member() ) : ?>
	<div class="content-box-two-cols-image-left">
		<div class="wphb-block-entry-image wphb-block-entry-image-bottom">
			<img class="wphb-image"
			     src="<?php echo wphb_plugin_url() . 'admin/assets/image/hummingbird-upsell-minify.png'; ?>"
			     srcset="<?php echo wphb_plugin_url() . 'admin/assets/image/hummingbird-upsell-minify@2x.png'; ?> 2x"
			     alt="<?php esc_attr_e( 'Try Pro for FREE today!', 'wphb' ); ?>">
		</div>
		<div class="wphb-block-entry-content wphb-upsell-free-message">
			<?php printf(
				__( '<p>Schedule automated performance tests and receive email reports direct to your inbox. You\'ll get Hummingbird Pro plus 100+ WPMU DEV plugins, themes & 24/7 WP support. <a href="%s" rel="dialog">Try Pro for FREE today!</a></p>', 'wphb' ),
				'#wphb-upgrade-membership-modal'
			); ?>
		</div>
	</div>
    <?php wphb_membership_modal(); ?>
<?php endif; ?>