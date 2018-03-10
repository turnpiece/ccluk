<?php
/**
 * Advanced tools database cleanup settings meta box.
 *
 * @package Hummingbird
 * @since 1.8
 *
 * @var array $fields     Array of tables used to build checkboxes.
 * @var int   $frequency  Cleanup frequency.
 * @var bool  $schedule   If schedule is enabled or disabled.
 */
?>

<form id="advanced-db-settings">
	<div class="row box-content settings-form">
		<?php if ( ! WP_Hummingbird_Utils::is_member() ) : ?>
			<div class="wphb-disabled-overlay"></div>
		<?php endif; ?>

		<div class="col-third">
			<strong><?php esc_html_e( 'Schedule Cleanups', 'wphb' ) ?></strong>
			<span class="sub">
				<?php esc_html_e( 'Schedule Hummingbird to automatically clean your database daily, weekly or monthly.', 'wphb' ); ?>
			</span>
		</div><!-- end col-third -->
		<div class="col-two-third">
			<span class="toggle tooltip-right" tooltip="<?php esc_attr_e( 'Enabled scheduled cleanups', 'wphb' ); ?>">
				<input type="checkbox" class="toggle-checkbox" name="scheduled_cleanup" id="scheduled_cleanup" <?php checked( $schedule ); disabled( ! WP_Hummingbird_Utils::is_member() ); ?>>
				<label for="scheduled_cleanup" class="toggle-label small"></label>
			</span>
			<label for="scheduled_cleanup"><?php esc_html_e( 'Enabled scheduled cleanups', 'wphb' ); ?></label>

			<div class="wphb-border-frame with-padding schedule-box <?php echo $schedule ? '' : 'hidden' ?>">
				<label for="cleanup_frequency"><?php esc_html_e( 'Frequency', 'wphb' ); ?></label>
				<select name="cleanup_frequency" id="cleanup_frequency">
					<option <?php selected( 1, $frequency ) ?> value="1">
						<?php esc_html_e( 'Daily', 'wphb' ) ?>
					</option>
					<option <?php selected( 7, $frequency ) ?> value="7">
						<?php esc_html_e( 'Weekly', 'wphb' ) ?>
					</option>
					<option <?php selected( 30, $frequency ) ?> value="30">
						<?php esc_html_e( 'Monthly', 'wphb' ) ?>
					</option>
				</select>

				<label for="included-tables"><?php esc_html_e( 'Included Tables', 'wphb' ); ?></label>
				<div id="included-tables" class="included-tables">
					<?php foreach ( $fields as $type => $field ) : ?>
						<label for="<?php echo esc_attr( $type ); ?>">
							<input type="checkbox" name="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $type ); ?>" <?php checked( $field['checked'] ); ?>>
							<?php echo esc_html( $field['title'] ); ?>
						</label>
					<?php endforeach; ?>
				</div>

			</div>
		</div>
	</div>
</form>