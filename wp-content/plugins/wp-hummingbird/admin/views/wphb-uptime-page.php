<?php
/** @var WP_Hummingbird_Uptime_Page $this */
?>
<?php if ( $this->has_meta_boxes( 'summary' ) ) : ?>
	<?php $this->do_meta_boxes( 'summary' ); ?>
<?php endif; ?>
<?php if ( $this->has_meta_boxes( 'box-uptime-disabled' ) ) : ?>
	<?php $this->do_meta_boxes( 'box-uptime-disabled' ); ?>
<?php else : ?>
	<div class="sui-row-with-sidenav">
		<?php $this->show_tabs(); ?>
		<?php if ( $error ) : ?>
			<div class="sui-box">
				<div class="sui-box-header"><?php esc_html_e( 'Uptime', 'wphb' ); ?></div>
				<div class="sui-box-body">
					<div class="sui-notice sui-notice-error wphb-notice-box can-close">
						<span class="close"></span>
						<p><?php echo esc_html( $error ); ?></p>
						<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-primary">
							<?php esc_html_e( 'Try again', 'wphb' ); ?>
						</a>
					</div>
				</div>
			</div>
		<?php else : ?>
			<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>