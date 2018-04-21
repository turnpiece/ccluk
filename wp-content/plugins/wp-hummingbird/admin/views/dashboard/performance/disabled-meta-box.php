<?php
/**
 * Performance disabled meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $run_url  URL to performance module.
 */

?>
<p><?php esc_html_e( 'Hummingbird will test your website and detect what improvements you can make. We’ll give you a score out of 100 for each item, with custom recommendations on how to improve!', 'wphb' ); ?></p>

<a href="<?php echo esc_url( $run_url ); ?>" class="sui-button sui-button-primary" id="performance-scan-website">
	<?php esc_html_e( 'Run Test', 'wphb' ); ?>
</a>