<?php
/**
 * Shipper checks body copy templates: server type differences
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
				__( 'Your web server is what handles HTTP requests and returns your site’s content to its visitors. When you are migrating to a different web server, some parts of your site may not work the same on the destination site due to differences in the way web servers handle configurations. Even some plugins work differently on different server types.', 'shipper' )
			);
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( '<b>%1$s</b> uses %2$s whereas <b>%3$s</b> uses %4$s.', 'shipper' ),
					$source, $local,
					$destination, $remote
				) );
			?>
		</p>
	</div>

</div>