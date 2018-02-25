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
<h3><?php echo esc_html( $title ); ?></h3>
<?php if ( $issues ) : ?>
	<div class="wphb-pills"><?php echo intval( $issues ); ?></div>
<?php endif; ?>
<div class="buttons">
	<p class="wphb-label-notice-inline hide-to-mobile"><?php esc_html_e( 'Made changes?', 'wphb' ); ?></p>
	<a href="<?php echo esc_url( $url ); ?>" class="button button-ghost" id="recheck-expiry" name="submit">
		<?php esc_html_e( 'Re-Check Expiry', 'wphb' ); ?>
	</a>
</div>