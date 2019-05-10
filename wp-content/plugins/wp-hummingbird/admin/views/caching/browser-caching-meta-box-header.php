<?php
/**
 * Browser caching meta box header.
 *
 * @package Hummingbird
 *
 * @var string $title   Title of the module.
 * @var int    $issues  Number of caching issues.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="sui-actions-left">
		<span class="sui-tag sui-tag-warning"><?php echo intval( $issues ); ?></span>
	</div>
<?php endif; ?>
<div class="sui-actions-right">
	<span class="spinner"></span>
	<span class="wphb-label-notice-inline sui-hidden-xs sui-hidden-sm"><?php esc_html_e( 'Made changes?', 'wphb' ); ?></span>
	<a href="#" class="sui-button sui-button-ghost sui-button-icon-left">
		<i class="sui-icon-update" aria-hidden="true"></i>
		<?php esc_attr_e( 'Re-Check Status', 'wphb' ); ?>
	</a>
</div>
