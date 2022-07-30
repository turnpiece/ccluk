<?php
/**
 * Shipper tag templates: singular domain tag
 *
 * @since v1.0.3
 * @package shipper
 */

$domain = ! empty( $domain ) // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- this is not WordPress global
	? $domain
	: home_url();

$out = preg_replace( '/^https?:\/\//', '', esc_url( $domain ) );
?>

<span class="sui-tag">
	<?php echo esc_html( $out ); ?>
</span>