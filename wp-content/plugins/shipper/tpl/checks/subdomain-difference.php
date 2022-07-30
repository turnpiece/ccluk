<?php
/**
 * Shipper checks body copy templates: install subdomain/directory differences
 *
 * @since v1.0.3
 * @package shipper
 */

$source_type = ! empty( $local )
	// translators: %s: site name.
	? sprintf( __( '<b>%s</b> is a subdomain installation', 'shipper' ), $source )
	// translators: %s: site name.
	: sprintf( __( '<b>%s</b> is a subdirectory installation', 'shipper' ), $source );
$dest_type = ! empty( $remote )
	// translators: %s: site name.
	? sprintf( __( '<b>%s</b> is a subdomain installation', 'shipper' ), $destination )
	// translators: %s: site name.
	: sprintf( __( '<b>%s</b> is a subdirectory installation', 'shipper' ), $destination );
?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Multisite networks support two different URL schemes for subsite addresses, i.e., Subdomain (For example: site1.example.com, site2.example.com) and Subdirectory (For example: example.com/site1, example.com/site2). You can define your preferred address type for your network at the time of installation. Since both of these address types are very hard to switch later, because of the way data is stored in the database, Shipper can only migrate from your network if both the installations have the same address type.', 'shipper' );
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
							// translators: %s: source and destination type.
							__( '%1$s but %2$s.', 'shipper' ),
							$source_type,
							$dest_type
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
			esc_html_e( 'Unfortunately, Shipper cannot yet migrate your network to another network if the address types are different. Meanwhile, you can use another plugin which supports this type of migration.', 'shipper' );
		?>
	</p>

</div>