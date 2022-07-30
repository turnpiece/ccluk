<?php
/**
 * Shipper checks body copy templates: server OS differences
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
			__( 'Every server runs on an operating system, and each operating system has a different directory structure and supports different system libraries. When migrating to a server with a different operating system, if your source site has any hard-coded links or some of your plugins depend on system libraries, they may not work as expected in your destination site.', 'shipper' )
		);
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-warning">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %1$s %2$s %3$s %4$s: source local, destination and remote site url */
							__( '<b>%1$s</b> is on %2$s server whereas <b>%3$s</b> is on %4$s server.', 'shipper' ),
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