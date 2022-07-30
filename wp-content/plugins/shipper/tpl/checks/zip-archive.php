<?php
/**
 * Shipper checks body copy templates: zip archive not found
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Shipper uses PHP\'s built-in ZipArchive class to zip your files on your source website and unzip them on your destination. You need to have this module available on both sites for the migration to run.', 'shipper' );
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
							// translators: %s: website name.
							__( 'PHP ZipArchive class not found on <b>%s</b>.', 'shipper' ),
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
			esc_html_e( 'You need to make sure the ZipArchive PHP extension is installed and available to use. You can use any of the following methods to install the extension:', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '1. Most hosts have the ZipArchive extension installed and available by default, but it may not be active. Open your cPanel, and under the Software section, click on select the PHP version option. You\'ll see your current PHP version, extensions available, and active PHP extensions. Check the zip option, and click on save to activate it. Note that if the zip option is not available in this list, please contact your hosting support, and ask them to install zip extension for you.', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( '2. If you have your own VPS, you must install the zip extension, and restart your server. You can ask your sysadmin to install the zip extension. Following are examples of how you can install the zip extension on a couple of popular servers:', 'shipper' );
		?>
	</p>

	<div class="sui-side-tabs sui-tabs">
		<div data-tabs>
			<div class="active"><?php esc_html_e( 'Ubuntu', 'shipper' ); ?></div>
			<div><?php esc_html_e( 'centOS', 'shipper' ); ?></div>
		</div>
		<div data-panes>
			<div class="sui-tab-boxed active">
				<p>
					<?php esc_html_e( 'Run the following commands in your terminal to install the zip extension on your server.', 'shipper' ); ?>
				</p>
				<pre class="sui-code-snipper">
	#Install zip entension
	sudo apt-get install php-zip

	#Restart Apache server
	sudo service apache2 restart

	#Restart NGINX server
	sudo service nginx restart</pre>
			</div>
			<div class="sui-tab-boxed">
				<p>
					<?php esc_html_e( 'Run the following commands in your terminal to install the zip extension on your server.', 'shipper' ); ?>
				</p>
				<pre class="sui-code-snipper">
	#Install zip entension
	sudo yum install php-zip

	#Restart server
	sudo service httpd restart</pre>
			</div>
		</div>
	</div>

	<p>
		<?php
			esc_html_e( '3. If none of the above works, you can ask your hosting support or your system admin to install the zip extension on your server.', 'shipper' );
		?>
	</p>
</div>
<div class="sui-notice sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<div class="sui-notice-message">
			<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
			<p>
				<?php
				echo wp_kses_post(
					sprintf(
						// translators: %s: website name.
						__( 'Zip support is not found on %1$s. Please fix this and check again.', 'shipper' ),
						$domain
					)
				);
				?>
			</p>
		</div>
	</div>

	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>