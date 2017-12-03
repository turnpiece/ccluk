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
<h3><?php echo esc_html( $title ); ?></h3>
<div class="buttons">
	<a href="<?php echo esc_url( $purge_url ); ?>" class="button button-ghost tooltip tooltip-right" tooltip="<?php esc_attr_e( 'Clear all locally cached static pages', 'wphb' ); ?>"><?php esc_html_e( 'Clear cache', 'wphb' ); ?></a>
</div>