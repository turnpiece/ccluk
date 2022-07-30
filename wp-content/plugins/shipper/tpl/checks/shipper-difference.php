<?php
/**
 * Shipper checks body copy templates: PHP versions differences
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
			__( 'As we are constantly pushing new updates to improve the API migrations, using different versions of Shipper on the source and destination can cause the migration to fail due to the underlying differences in the migration flow. ', 'shipper' )
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
							// translators: %1$s %2$s %3$s %4$s: source, local, destination and remote site name.
							__( '%1$s is using Shipper v%2$s whereas %3$s is using Shipper v%4$s', 'shipper' ),
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

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			echo wp_kses_post( __( 'We recommend using the same (and preferably the latest) version of Shipper on both the sites to avoid migration failure. So visit the plugins page on each website and update the Shipper plugin to it’s the latest available version to ensure both sites are using the same and latest plugin version.', 'shipper' ) );
		?>
	</p>
</div>