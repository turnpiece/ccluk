<?php
/**
 * Page caching meta box.
 *
 * @package Hummingbird
 *
 * @var string        $deactivate_url  Deactivate URL.
 * @var bool|WP_Error $error           Error if present.
 */

?>
<div class="row settings-form with-bottom-border">
	<p><?php esc_html_e( 'Hummingbird stores static HTML copies of your pages and posts to decrease page load time.', 'wphb' ); ?></p>

	<?php if ( is_wp_error( $error ) ) : ?>
		<div class="wphb-caching-error wphb-notice wphb-notice-error">
			<p><?php echo $error->get_error_message(); ?></p>
		</div>
	<?php else : ?>
		<div class="wphb-caching-success wphb-notice wphb-notice-success">
			<p><?php esc_html_e( 'Page caching is currently active.', 'wphb' ); ?></p>
		</div>
	<?php endif; ?>
</div><!-- end row -->

	<div class="row settings-form">
		<div class="col-third">
			<strong><?php esc_html_e( 'Deactivate', 'wphb' ); ?></strong>
			<span class="sub">
				<?php esc_html_e( 'You can deactivate page caching at any time. Remember this may result in slower page loads unless you have another caching plugin activate.', 'wphb' ); ?>
			</span>
		</div>
		<div class="col-two-third">
			<a href="<?php echo esc_url( $deactivate_url ); ?>" class="button button-ghost">
				<?php esc_html_e( 'Deactivate', 'wphb' ); ?>
			</a>
			<span class="desc">
				<?php esc_html_e( 'Note: Deactivating won’t lose any of your website data, only the cached pages will be removed and won’t be served to your visitors any longer.', 'wphb' ); ?>
			</span>
		</div>
	</div>