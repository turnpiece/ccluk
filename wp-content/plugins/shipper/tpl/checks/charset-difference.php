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
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( '<b>%1$s</b> uses %2$s charset whereas <b>%3$s</b> uses %4$s charset.', 'shipper' ),
					$source, $local,
					$destination, $remote
				) );
			?>
		</p>
	</div>

</div>
<div class="sui-notice-top sui-notice-warning sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<p>
			<?php echo wp_kses_post( sprintf(
				__( '<b>%1$s</b> uses %2$s charset whereas <b>%3$s</b> uses %4$s charset.', 'shipper' ),
				$source, $local,
				$destination, $remote
			) ); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>