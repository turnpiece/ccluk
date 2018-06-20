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
		<span class="sui-tag"><?php echo intval( $issues ); ?></span>
	</div>
<?php endif; ?>
<div class="sui-actions-right">
	<span class="spinner standalone"></span>
	<span class="wphb-label-notice-inline sui-hidden-xs sui-hidden-sm"><?php esc_html_e( 'Made changes?', 'wphb' ); ?></span>
	<input type="submit" class="sui-button sui-button-ghost" value="<?php esc_attr_e( 'Re-Check Expiry', 'wphb' ); ?>">
</div>