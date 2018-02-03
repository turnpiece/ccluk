<?php
/**
 * Enqueued files meta box header.
 *
 * @var string $title
 * @var string $type
 */
?>

<h3><?php echo esc_html( $title ); ?></h3>

<div class="buttons">
	<p class="wphb-label-notice-inline hide-to-mobile">
		<?php _e( 'Not seeing all your files in this list?', 'wphb' ); ?>
	</p>

	<div class="tooltip" tooltip="<?php esc_attr_e( 'Looks for newly enqueued files and preserves current settings', 'wphb' ); ?>">
		<input type="submit" class="button button-ghost" name="recheck-files" value="<?php esc_attr_e( 'Re-Check Files', 'wphb' ); ?>">
	</div>

	<div class="tooltip" tooltip="<?php esc_attr_e( 'Clears all local or hosted assets and recompresses files that need it', 'wphb' ); ?>">
		<input type="submit" class="button button-grey" name="clear-cache" value="<?php esc_attr_e( 'Clear cache', 'wphb' ); ?>">
	</div>
</div>

<?php
if ( 'advanced' === $type ) {
	$tooltip = __( 'Switch to basic mode', 'wphb' );
} else {
	$tooltip = __( 'Switch to advanced mode for more control', 'wphb' );
}
?>

<a href="#" class="wphb-switch-button tooltip tooltip-right" tooltip="<?php echo esc_attr( $tooltip ); ?>">
	<i class="hb-fi-settings-slider-control"></i>
</a>