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
				__( 'PHP is the scripting language that powers WordPress under the hood. Your plugins and themes make use of PHP functions, and if you migrate from one server to another with a significant difference in the PHP version, some of your plugins and themes may not work as expected.', 'shipper' )
			);
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( '<b>%1$s</b> is on PHP v%2$s.x.x whereas <b>%3$s</b> is on PHP v%4$s.x.x.', 'shipper' ),
					$source, $local,
					$destination, $remote
				) );
			?>
		</p>
	</div>

</div>
