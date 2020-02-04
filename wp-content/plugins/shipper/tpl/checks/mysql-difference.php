<?php
/**
 * Shipper checks body copy templates: MySQL versions differences
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
				__( 'WordPress uses MySQL database to store your data, including posts, pages, users, and plugins data. Migrating from one server to another with a significant difference in the MySQL versions can cause issues in your destination site.', 'shipper' )
			);
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( '<b>%1$s</b> is on MySQL v%2$s whereas <b>%3$s</b> is on MySQL v%4$s.', 'shipper' ),
					$source, $local,
					$destination, $remote
				) );
			?>
		</p>
	</div>

</div>