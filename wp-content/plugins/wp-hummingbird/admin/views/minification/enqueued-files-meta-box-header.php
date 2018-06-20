<?php
/**
 * Enqueued files meta box header.
 *
 * @var string $title
 * @var string $type
 */
?>

<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>

<div class="sui-actions-right">
	<span class="wphb-label-notice-inline sui-hidden-xs sui-hidden-sm">
		<?php _e( 'Not seeing all your files in this list?', 'wphb' ); ?>
	</span>

	<div class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Added/removed plugins or themes? Update your file list to include new files, and remove old ones', 'wphb' ); ?>">
		<input type="submit" class="sui-button sui-button-ghost" name="recheck-files" value="<?php esc_attr_e( 'Re-Check Files', 'wphb' ); ?>">
	</div>

	<div class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php esc_attr_e( 'Clears all local or hosted assets and recompresses files that need it', 'wphb' ); ?>">
		<input type="submit" class="sui-button" name="clear-cache" value="<?php esc_attr_e( 'Clear cache', 'wphb' ); ?>">
	</div>
</div>

<?php
if ( 'advanced' === $type ) {
	$tooltip = __( 'Switch to basic mode', 'wphb' );
} else {
	$tooltip = __( 'Switch to advanced mode for more control', 'wphb' );
}
?>

<span class="wphb-heading-divider"></span>
<a href="#" class="wphb-switch-button sui-tooltip sui-tooltip-top-left" data-tooltip="<?php echo esc_attr( $tooltip ); ?>">
	<i class="sui-icon-settings-slider-control" aria-hidden="true"></i>
</a>