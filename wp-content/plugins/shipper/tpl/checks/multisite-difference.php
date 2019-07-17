<?php
/**
 * Shipper checks body copy templates: install multisite differences
 *
 * @since v1.0.3
 * @package shipper
 */

$source_type = empty( $local )
	? sprintf( __( '<b>%s</b> is a single site', 'shipper' ), $source )
	: sprintf( __( '<b>%s</b> is a multisite', 'shipper' ), $source );
$dest_type = empty( $remote )
	? sprintf( __( '<b>%s</b> is a single site', 'shipper' ), $destination )
	: sprintf( __( '<b>%s</b> is a multisite', 'shipper' ), $destination );
?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Shipper can only migrate between the same WordPress installation types, i.e. from one single site to another single site or from one multisite network to another multisite network. It is necessary for your source and destination sites to have the same Wordpress installation.', 'shipper' );
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-error">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( '%1$s but %2$s installation.', 'shipper' ),
					$source_type, $dest_type
				) );
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Unfortunately, Shipper cannot yet migrate between sites with different WordPress installation type. We\'re working on adding features which will allow you to extract a single site from a network and move it to a standalone installation, or migrate a single site into a network as a subsite. Meanwhile, you can use another plugin which supports this type of migration.', 'shipper' );
		?>
	</p>

</div>
