<?php
/**
 * Gravatar caching header meta box.
 *
 * @package Hummingbird
 *
 * @var string $title  Module title.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<div class="sui-actions-right">
	<span class="spinner"></span>
	<a href="#" class="sui-button sui-button-ghost sui-tooltip sui-tooltip-top-left" data-tooltip="<?php esc_attr_e( 'Clear all locally cached Gravatars', 'wphb' ); ?>">
		<?php esc_html_e( 'Clear cache', 'wphb' ); ?>
	</a>
</div>