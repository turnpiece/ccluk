<?php
/**
 * Shipper checks body copy templates: DB charsets differences
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
		echo wp_kses_post(
			__( 'Computers only understand binary numbers, and your database charset maps each character in your data to a specific number so your database server recognizes it. A charset discrepancy between the original server and the destination server is not likely to cause your migration to fail, but it may cause problems with the content on the destination site.', 'shipper' )
		);
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
							/* translators: %1$s %2$s %3$s %4$s: source, local, destination and remote site name.*/
							__( '<b>%1$s</b> uses %2$s charset whereas <b>%3$s</b> uses %4$s charset.', 'shipper' ),
							$source,
							$local,
							$destination,
							$remote
						)
					);
					?>
				</p>
			</div>
		</div>
	</div>
</div>

<div id="charset-notice-inline-dismiss" class="sui-notice sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s %2$s %3$s %4$s: source, local, destination and remote site name */
						__( '<b>%1$s</b> uses %2$s charset whereas <b>%3$s</b> uses %4$s charset.', 'shipper' ),
						$source,
						$local,
						$destination,
						$remote
					)
				);
				?>
			</p>
		</div>
	</div>

	<div class="sui-notice-actions">
		<button class="sui-button-icon" data-notice-close="charset-notice-inline-dismiss">
			<i class="sui-icon-check" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
		</button>
	</div>
</div>