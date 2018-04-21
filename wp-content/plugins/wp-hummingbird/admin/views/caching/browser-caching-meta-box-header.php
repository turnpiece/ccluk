<?php
/**
 * Browser caching meta box header.
 *
 * @package Hummingbird
 *
 * @var string $title       Title of the module.
 * @var int    $issues      Number of caching issues.
 * @var string $url         Url to recheck status.
 */

?>
<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="sui-actions-left">
		<span class="sui-tag"><?php echo intval( $issues ); ?></span>
	</div>
<?php endif; ?>
<div class="sui-actions-right">
	<span class="wphb-label-notice-inline hide-to-mobile"><?php esc_html_e( 'Made changes?', 'wphb' ); ?></span>
	<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost" id="recheck-expiry" name="submit">
		<?php esc_html_e( 'Re-Check Expiry', 'wphb' ); ?>
	</a>
</div>