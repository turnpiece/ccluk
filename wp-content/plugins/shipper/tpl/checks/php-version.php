<?php
/**
 * Shipper checks body copy templates: PHP version issue
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'PHP is the scripting language that powers WordPress under the hood. New versions are released over time that brings both speed and security improvements. It’s important to use the latest and greatest tools, so older PHP versions eventually cease to be supported. Shipper is using AWS PHP SDK which requires PHP 5.5 or higher to work properly. ', 'shipper' );
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
							/* translators: %1$s %2$s: website name and PHP version.*/
							__( '<b>%1$s</b> is using PHP %2$s, and Shipper needs PHP 5.5 or above to work.', 'shipper' ),
							$domain,
							$value
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
			__( 'You need to upgrade your PHP version to the latest stable release. You can either contact your hosting provider and ask them to update your PHP version, or do it yourself following an official WordPress tutorial on <a href="https://wordpress.org/support/update-php/" target="_blank">updating your PHP version</a>.', 'shipper' )
		);
		?>
	</p>
	<p>
		<?php
		echo wp_kses_post(
			__( '<b>Note</b>: Make sure you run a full backup of your website before updating your PHP version. <a href="https://wpmudev.com/project/snapshot/" target="_blank">Snapshot Pro</a> can help you with this!', 'shipper' )
		);
		?>
	</p>
</div>
<div class="sui-notice sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: website name. */
						__( 'PHP version too low on %1$s. Please fix this and check again.', 'shipper' ),
						$domain
					)
				);
				?>
			</p>
		</div>
	</div>

	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>