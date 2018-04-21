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
			<div class="wphb-notice-box wphb-notice-box-error can-close">
				<span class="close"></span>
				<span class="wphb-icon wphb-icon-left"><i class="wdv-icon wdv-icon-fw wdv-icon-warning-sign"></i></span>
				<?php $support_link = '#'; ?>
				<p><?php echo esc_html( $error ); ?></p>
				<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-primary button-notice-box button-notice-box-error"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
			</div>
		<?php else : ?>
			<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
		<?php endif; ?>
	</div>
<?php endif; ?>