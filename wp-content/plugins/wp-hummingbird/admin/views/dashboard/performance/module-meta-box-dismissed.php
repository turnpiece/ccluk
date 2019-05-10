<?php
/**
 * Performance report dismissed meta box on dashboard page.
 *
 * @package Hummingbird
 *
 * @var bool   $notifications     Performance cron reports status.
 */

?>

<p>
	<?php esc_html_e( 'Run a Google PageSpeed test and get itemised insight (with fixes) on where you can improve your website’s performance.', 'wphb' ); ?>
</p>

<div class="sui-notice sui-notice-grey-info">
	<p><?php esc_html_e( 'You chose to ignore your last performance test. Run a new test to see new recommendations.', 'wphb' ); ?></p>
</div>
