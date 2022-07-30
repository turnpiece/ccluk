<?php
/**
 * Shipper checks body copy templates: open_basedir in effect
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
			__( 'PHP has a security measure called <b>open_basedir</b> which limits which files can be accessed by a PHP script. Usually, it’s set to your root directory or a couple of specific directories. Shipper needs to be able to write in the working directory, storage directory, temp directory, and log directory to work properly. Having open_basedir active is likely to cause migration failures.', 'shipper' )
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
							/* translators: %s: website name.*/
							__( 'Open_basedir restriction is in effect on <b>%s</b>.', 'shipper' ),
							$domain
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
			echo wp_kses_post( __( 'We recommend disabling the <b>open_basedir</b> restriction during migrations to ensure things go smoothly.', 'shipper' ) );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '1. Go to your cPanel > Select PHP Version, and click on Switch to PHP Options link to see the default values of PHP options. Update the value of open_basedir to an empty value, and click on Apply and then Save.', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '2. Open your php.ini file, comment the open_basedir rule, and restart your server. To comment a line of code in the php.ini file, you need to suffixit with a semicolon. So, to comment the open_basedir rule, add a semicolon in the beginning as in: “; open_basedir true”. Commenting is preferred to deleting the rule, in case you want to reactivate it after the migration.', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '3. You can also edit the Apache configuration file to disable the PHP open_basedir restriction. Open the httpd.conf file. Add the following line of code at the end, and restart your web server.', 'shipper' );
		?>
	</p>
	<pre class="sui-code-snipper">php_admin_value open_basedir none</pre>
	<p>
		<?php
			esc_html_e( '4. If none of the above works, you can ask your hosting support to turn off the open_basedir restriction for you.', 'shipper' );
		?>
	</p>
</div>

<div id="basedir-notice-inline-dismiss" class="sui-notice sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						/* translators: %1$s: website name. */
						__( 'Open_basedir restriction in effect on %1$s. Please fix this and check again.', 'shipper' ),
						$domain
					)
				);
				?>
			</p>
		</div>
	</div>

	<div class="sui-notice-actions">
		<button class="sui-button-icon" data-notice-close="basedir-notice-inline-dismiss">
			<i class="sui-icon-check" aria-hidden="true"></i>
			<span class="sui-screen-reader-text"><?php esc_attr_e( 'Close this notice', 'shipper' ); ?></span>
		</button>
	</div>
</div>