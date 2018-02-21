<?php
/** @var WP_Hummingbird_Uptime_Page $this */
?>
<?php if ( $this->has_meta_boxes( 'summary' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'summary' ); ?>
	</div>
<?php endif; ?>
<?php if ( $this->has_meta_boxes( 'box-uptime-disabled' ) ) : ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'box-uptime-disabled' ); ?>
	</div>
<?php else : ?>
	<div class="row">
		<div class="col-fifth">
			<?php $this->show_tabs(); ?>
		</div><!-- end col-sixth -->
		<div class="col-four-fifths">
			<?php if ( $error ) : ?>
				<div class="wphb-notice-box wphb-notice-box-error can-close">
					<span class="close"></span>
					<span class="wphb-icon wphb-icon-left"><i class="wdv-icon wdv-icon-fw wdv-icon-warning-sign"></i></span>
					<?php $support_link = '#'; ?>
					<p><?php echo esc_html( $error ); ?></p>
					<a href="<?php echo esc_url( $retry_url ); ?>" class="button button-notice-box button-notice-box-error"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
				</div>
			<?php else : ?>
				<?php $this->do_meta_boxes( $this->get_current_tab() ); ?>
			<?php endif; ?>
		</div>
	</div>
<?php endif; ?>