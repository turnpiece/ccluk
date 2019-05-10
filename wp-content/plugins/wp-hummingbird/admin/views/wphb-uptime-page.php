<?php
/**
 * Uptime page.
 *
 * @package Hummingbird
 * @var WP_Hummingbird_Uptime_Page $this
 */

if ( $this->has_meta_boxes( 'summary' ) ) {
	$this->do_meta_boxes( 'summary' );
}

if ( $this->has_meta_boxes( 'box-uptime-disabled' ) ) {
	$this->do_meta_boxes( 'box-uptime-disabled' );
} else {
	?>
	<div class="sui-row-with-sidenav">
		<?php $this->show_tabs(); ?>
		<?php if ( $error ) : ?>
			<div class="sui-box">
				<div class="sui-box-header"><?php esc_html_e( 'Uptime', 'wphb' ); ?></div>
				<div class="sui-box-body">
					<div class="sui-notice sui-notice-error can-close">
						<span class="close"></span>
						<p><?php echo esc_html( $error ); ?></p>
						<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-blue">
							<?php esc_html_e( 'Try again', 'wphb' ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php else : ?>
			<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
		<?php endif; ?>
	</div>
	<?php
}
?>

<?php WP_Hummingbird_Utils::get_modal( 'add-recipient' ); ?>

<script>
	jQuery(document).ready( function() {
		if ( window.WPHB_Admin ) {
			window.WPHB_Admin.getModule( 'uptime' );
		}
	});
</script>
