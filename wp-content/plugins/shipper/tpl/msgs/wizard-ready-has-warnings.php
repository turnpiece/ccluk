<?php
/**
 * Shipper messages: wizard ready to sail warning notice template
 *
 * @package shipper
 */

?>
<div class="sui-notice sui-notice-warning">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p><?php esc_html_e( 'You have a few warnings, please check the sections above for more info.', 'shipper' ); ?></p>
		</div>
	</div>
</div>
<p>
	<?php esc_html_e( 'Don\'t worry!', 'shipper' ); ?>
	<?php esc_html_e( 'You can try to resolve these warnings or begin the migration right away ignoring these warnings.', 'shipper' ); ?>
	<?php esc_html_e( 'Note that Shipper overwrites any existing files or database tables on your destination website.', 'shipper' ); ?>
	<?php esc_html_e( 'Please make sure you have a backup.', 'shipper' ); ?>
</p>