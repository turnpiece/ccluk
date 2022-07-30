<?php
/**
 * Shipper checks body copy templates: AWS SDK
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'AWS SDK is the library Shipper uses to migrate files between your source and destination websites. This library must be installed and loaded for Shipper to run.', 'shipper' );
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-error">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: website name */
							__( 'Shipper is unable to load AWS SDK on <b>%s</b>.', 'shipper' ),
							$domain
						)
					);
					?>
				</p>
			</div>
		</div>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
		echo wp_kses_post(
			__( 'This issue usually occurs because a plugin is running a different version of AWS SDK. Disable plugins which use AWS SDK, and run Shipper again. If you are not sure which plugin is causing the issue, disable plugins one by one until you locate the culprit. ', 'shipper' )
		);
		?>
	</p>
</div>

<div id="aws-notice-inline-dismiss" class="sui-notice sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %s: website name */
						__( 'Unable to load AWS SDK on %1$s. Please fix this and check again.', 'shipper' ),
						$domain
					)
				);
				?>
			</p>
		</div>
	</div>

	<div class="sui-notice-actions">
		<button class="sui-button-icon" data-notice-close="aws-notice-inline-dismiss">
			<i class="sui-icon-check" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
		</button>
	</div>
</div>