<?php
/**
 * Page caching header meta box.
 *
 * @package Hummingbird
 *
 * @var string $title      Module title.
 * @var string $purge_url  Purge page cache url.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<div class="sui-actions-right">
	<a href="<?php echo esc_url( $purge_url ); ?>" class="sui-button sui-button-ghost sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_attr_e( 'Clear all locally cached static pages', 'wphb' ); ?>"><?php esc_html_e( 'Clear cache', 'wphb' ); ?></a>
</div>