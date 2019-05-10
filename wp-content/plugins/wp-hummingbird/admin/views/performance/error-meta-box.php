<?php
/**
 * Performance error meta box.
 *
 * @since 2.0.0  Isolated from other meta boxes.
 * @package Hummingbird
 *
 * @var string   $error_text     Error text.
 * @var string   $error_details  Error details.
 * @var string   $retry_url      Url to start a new performance scan.
 */

?>

<div class="sui-notice sui-notice-error">
	<p><?php echo $error_text; ?></p>
	<div id="wphb-error-details">
		<p><code><?php echo $error_details; ?></code></p>
	</div>
	<div class="sui-notice-buttons">
		<a href="<?php echo esc_url( $retry_url ); ?>" class="sui-button sui-button-blue button-notice"><?php esc_html_e( 'Try again', 'wphb' ); ?></a>
		<a target="_blank" href="<?php echo esc_url( WP_Hummingbird_Utils::get_link( 'support' ) ); ?>" class="sui-button sui-button-blue button-notice"><?php esc_html_e( 'Support', 'wphb' ); ?></a>
	</div>
</div>
