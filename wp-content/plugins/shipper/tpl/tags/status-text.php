<?php
/**
 * Shipper tag templates: generic status color icon, with text
 *
 * @since v1.0.3
 * @package shipper
 */

$status    = ! empty( $status ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global
	? $status
	: 'success';
$text      = ! empty( $text )
	? $text
	: 0;
$show_icon = ! empty( $hide )
	? 'style="display:none"'
	: '';
?>

<span <?php echo esc_attr( $show_icon ); ?> class="sui-tag sui-tag-<?php echo sanitize_html_class( $status ); ?>">
	<?php echo wp_kses_post( $text ); ?>
</span>