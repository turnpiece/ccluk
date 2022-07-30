<?php
/**
 * Shipper tag templates: generic status color icon
 *
 * @since v1.0.3
 * @package shipper
 */

$status    = ! empty( $status ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global
	? $status
	: 'success';
$icon      = ! empty( $icon )
	? $icon
	: 'check-tick';
$show_icon = ! empty( $hide )
	? 'style="display:none"'
	: '';
?>

<i
	aria-hidden="true" <?php echo esc_attr( $show_icon ); ?>
	class="sui-icon-<?php echo sanitize_html_class( $icon ); ?> sui-<?php echo sanitize_html_class( $status ); ?>">
</i>