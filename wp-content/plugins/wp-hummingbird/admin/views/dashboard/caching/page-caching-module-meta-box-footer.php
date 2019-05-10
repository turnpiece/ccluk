<?php
/**
 * Page caching meta box footer on dashboard page.
 *
 * @package Hummingbird
 *
 * @since 1.7.0
 * @var string $url        Url to module.
 */

?>
<a href="<?php echo esc_url( $url ); ?>" class="sui-button sui-button-ghost" name="submit">
	<i class="sui-icon-wrench-tool" aria-hidden="true"></i>
	<?php esc_html_e( 'Configure', 'wphb' ); ?>
</a>
