<?php
/**
 * Shipper checks body copy templates: max exec time too low
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
				__( 'Max execution time defines how long a PHP script can run before it returns an error. Shipper will often require longer than the default setting, so we recommend increasing your Max Execution time to <b>120s or above</b> to ensure migrations have the best chance of succeeding.', 'shipper' )
			);
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-warning">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( 'Max execution time on <b>%s</b> is %d seconds.', 'shipper' ),
					$domain,
					$value
				) );
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			echo wp_kses_post(
				__('You can set the <b>max_execution_time</b> of your site to any value above 120s by using any of the following methods: ', 'shipper' ) );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '1. Go to your cPanel > Select PHP Version, and click on the Switch to PHP Options link to see the default values of your PHP options. Update the value of max_execution_time to 120s, and click on Apply and then Save.', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '2. Connect to your site via FTP, and add the following line to your .htaccess file. Make sure you backup your .htaccess file before you edit it.', 'shipper' );
		?>
	</p>
	<pre class="sui-code-snipper">php_value max_execution_time 120 </pre>
	<p>
		<?php
			esc_html_e( '3. If you have access to the php.ini file, you can increase the execution time limit by adding the following line of code or updating it (if it exists already) in your php.ini file.', 'shipper' );
		?>
	</p>
	<pre class="sui-code-snipper">max_execution_time = 120;</pre>
	<p>
		<?php
			esc_html_e( '4. An alternative to editing the php.ini file is adding the following line of code in your wp-config.php file.', 'shipper' );
		?>
	</p>
	<pre class="sui-code-snipper">set_time_limit(120);</pre>
	<p>
		<?php
			esc_html_e( '5. If none of the above works, you can ask your hosting support to increase the max execution time for you.', 'shipper' );
		?>
	</p>
</div>
<div class="sui-notice-top sui-notice-warning sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<p>
			<?php echo wp_kses_post( sprintf(
				__( 'Max execution time on %1$s is %2$s. Please fix this and check again.', 'shipper' ),
				$domain, $value
			) ); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>
