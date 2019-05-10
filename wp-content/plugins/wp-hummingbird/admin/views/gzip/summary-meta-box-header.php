<?php
/**
 * Gzip status meta box header.
 *
 * @package Hummingbird
 *
 * @var bool|int $issues       Number of issues. False if all ok.
 * @var string   $recheck_url  Re-check status link.
 * @var string   $title
 */

?>

<h3  class="sui-box-title"><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="sui-actions-left">
		<span class="sui-tag sui-tag-warning"><?php echo intval( $issues ); ?></span>
	</div>
<?php endif; ?>

<div class="sui-actions-right">
	<span class="wphb-label-notice-inline sui-hidden-xs sui-hidden-sm"><?php esc_html_e( 'Made changes?', 'wphb' ); ?></span>
	<a href="<?php echo esc_url( $recheck_url ); ?>" class="sui-button sui-button-ghost sui-button-icon-left" name="submit">
		<i class="sui-icon-update" aria-hidden="true"></i>
		<?php esc_html_e( 'Re-Check Status', 'wphb' ); ?>
	</a>
</div>
