<?php if ( $this->has_meta_boxes( 'box-uptime-disabled' ) ) : ?>
    <div class="row">
		<?php $this->do_meta_boxes( 'box-uptime-disabled' ); ?>
    </div>
<?php endif; ?>

<?php if ( $this->has_meta_boxes( 'summary' ) ) : ?>
    <div class="row">
        <?php $this->do_meta_boxes( 'summary' ); ?>
    </div>
<?php endif; ?>

<?php if ( $error ): ?>
	<div class="row">
		<div class="wphb-notice-box wphb-notice-box-error can-close">
			<span class="close"></span>
			<span class="wphb-icon wphb-icon-left"><i class="wdv-icon wdv-icon-fw wdv-icon-warning-sign"></i></span>
			<?php $support_link = '#'; ?>
			<p><?php echo $error; ?></p>
			<a href="<?php echo esc_url( $retry_url ); ?>" class="button button-notice-box button-notice-box-error"><?php _e( 'Try again', 'wphb' ); ?></a>
		</div>
	</div>
<?php else: ?>
	<div class="row">
		<?php $this->do_meta_boxes( 'main' ); ?>
	</div>
<?php endif; ?>