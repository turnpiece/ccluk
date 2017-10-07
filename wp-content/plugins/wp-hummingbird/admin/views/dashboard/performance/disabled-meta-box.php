<?php
/**
 * Performance disabled meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var string $run_url  URL to performance module.
 */

?>
<div class="content">
	<p><?php esc_html_e( 'Hummingbird will test your website and detect what improvements you can make. We’ll give you a score out of 100 for each item, with custom recommendations on how to improve!', 'wphb' ); ?></p>
</div>

<div class="buttons">
	<a href="<?php echo esc_url( $run_url ); ?>" class="button" id="performance-scan-website">
		<?php esc_html_e( 'Run Test', 'wphb' ); ?>
	</a>
</div>