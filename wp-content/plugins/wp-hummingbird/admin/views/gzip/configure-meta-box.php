<?php
/**
 * GZip configure meta box.
 *
 * @package Hummingbird
 *
 * @var string        $deactivate_url       Deactivate URL.
 * @var string        $recheck_url          Re check status URL.
 * @var bool|WP_Error $error                Error if present.
 * @var bool          $show_enable_button   Show the enable button.
 * @var bool          $htaccess_error       True if there was an error trying to write the .htaccess file.
 * @var bool          $htaccess_writable    True if .htaccess is writable.
 * @var bool          $htaccess_written     True if .htaccess has all rules.
 * @var bool          $full_enabled         True if all types are active.
 * @var array         $pages                A list of page types.
 * @var string        $gzip_server_type     Current server type.
 * @var string        $disable_link         Disable automatic gzip link.
 * @var string        $enable_link          Enable automatic gzip link.
 */

?>
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Server type', 'wphb' ) ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Choose your server type. If you don’t know this, please contact your hosting provider.', 'wphb' ); ?>
		</span>
	</div><!-- end sui-box-settings-col-1 -->
	<div class="sui-box-settings-col-2">
		<label for="wphb-server-type" class="sui-label">
			<?php esc_html_e( 'Server type', 'wphb' ); ?>
		</label>
		<?php
		WP_Hummingbird_Utils::get_servers_dropdown( array(
			'selected' => $gzip_server_type,
		), false );
		?>
	</div>
</div>
<div class="sui-box-settings-row">
	<div class="sui-box-settings-col-1">
		<span class="sui-settings-label"><?php esc_html_e( 'Enable compression', 'wphb' ); ?></span>
		<span class="sui-description">
			<?php esc_html_e( 'Follow the instructions to activate GZip compression for this website.', 'wphb' ); ?>
		</span>
	</div><!-- end sui-box-settings-col-1 -->

	<div class="sui-box-settings-col-2">
	<?php if ( ! ( $htaccess_written && $full_enabled ) ) : ?>
		<div id="wphb-server-instructions-apache" class="wphb-server-instructions" data-server="apache" style="display: none;">
			<div class="sui-tabs">
				<div class="sui-tab">
					<label for="apache-config-auto" class="active"><?php esc_html_e( 'Automatic', 'wphb' ); ?></label>
					<input type="radio" name="apache-config-type" id="apache-config-auto" checked>
					<div class="sui-tab-content">
						<span class="sui-description">
							<?php esc_html_e( 'Hummingbird can automatically apply GZip compression for Apache servers by writing your .htaccess file. Alternately, switch to Manual to apply these rules yourself.', 'wphb' ); ?>
						</span>
						<?php if ( true === $htaccess_writable ) : ?>
							<div id="enable-cache-wrap" class="<?php echo ! in_array( $gzip_server_type, array( 'apache', 'LiteSpeed' ), true ) ? 'hidden' : ''; ?>">
								<?php if ( $show_enable_button ) : ?>
									<?php if ( true === $htaccess_written ) : ?>
										<a href="<?php echo esc_url( $disable_link ); ?>" class="sui-button sui-button-ghost"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
									<?php else : ?>
										<?php if ( $htaccess_error ) : ?>
											<div class="sui-notice sui-notice-warning htaccess-warning">
												<p><?php _e( 'We tried applying the .htaccess rules automatically but we weren’t able to. Make sure your file permissions on your .htaccess file are set to 644, or <a href="#apache-config-manual" class="switch-manual">switch to manual mode</a> and apply the rules yourself.', 'wphb' ); ?></p>
											</div>
										<?php endif; ?>
										<a href="<?php echo esc_url( $enable_link ); ?>" class="sui-button sui-button-primary sui-margin-top"><?php esc_html_e( 'Apply Rules', 'wphb' ); ?></a>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div><!-- end content -->
				</div><!-- end tab -->
				<div class="sui-tab">
					<label for="apache-config-manual"><?php esc_html_e( 'Manual', 'wphb' ); ?></label>
					<input type="radio" name="apache-config-type" id="apache-config-manual">
					<div class="sui-tab-content apache-instructions">
						<p><?php esc_html_e( 'If you are unable to get the automated method working you can copy the generated code below into your .htaccess file to activate GZip compression.', 'wphb' ); ?></p>

						<ol class="wphb-listing wphb-listing-ordered">
							<li><?php esc_html_e( 'Copy & paste the generated code below into your .htaccess file', 'wphb' ); ?></li>
							<li>
								<?php
								printf(
									/* translators: %s: Link to recheck GZip status */
								__( 'Next, <a href="%s">re-check your GZip status</a> to see if it worked. <a href="#" id="troubleshooting-link">Still having issues?</a>', 'wphb' ), esc_url( $recheck_url ) );
								?>
							</li>
						</ol>

						<pre class="sui-code-snippet"><?php echo htmlentities2( $snippets['apache'] ); ?></pre>
						<p id="troubleshooting-gzip"><strong>Troubleshooting</strong></p>
						<p><?php esc_html_e( 'If .htaccess does not work, and you have access to vhosts.conf or httpd.conf try this:', 'wphb' ); ?></p>
						<ol class="wphb-listing wphb-listing-ordered">
							<li><?php esc_html_e( 'Look for your site in the file and find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></li>
							<li><?php esc_html_e( 'Reload Apache.', 'wphb' ); ?></li>
							<li><?php esc_html_e( 'If you don\'t know where those files are, or you aren\'t able to reload Apache, you would need to consult with your hosting provider or a system administrator who has access to change the configuration of your server', 'wphb' ); ?></li>
						</ol>
						<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
					</div><!-- end apache-instructions -->
				</div><!-- end tab -->
			</div><!-- end tabs -->
		</div><!-- end wphb-server-instructions -->

		<div id="wphb-server-instructions-litespeed" class="wphb-server-instructions" data-server="LiteSpeed" style="display: none;">
			<div class="sui-tabs">
				<div class="sui-tab">
					<label for="litespeed-config-auto" class="active"><?php esc_html_e( 'Automatic', 'wphb' ); ?></label>
					<input type="radio" name="litespeed-config-type" id="litespeed-config-auto" checked>
					<div class="sui-tab-content">
						<span class="sui-description">
							<?php esc_html_e( 'Hummingbird can automatically apply browser caching for LiteSpeed servers by writing your .htaccess file. Alternately, switch to Manual to apply these rules yourself.', 'wphb' ); ?>
						</span>
						<?php if ( true === $htaccess_writable ) : ?>
							<div id="enable-cache-wrap" class="<?php echo ! in_array( $gzip_server_type, array( 'apache', 'LiteSpeed' ), true ) ? 'hidden' : ''; ?>">
								<?php if ( $show_enable_button ) : ?>
									<?php if ( true === $htaccess_written ) : ?>
										<a href="<?php echo esc_url( $disable_link ); ?>" class="sui-button sui-button-ghost sui-margin-top"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
									<?php else : ?>
										<?php if ( $htaccess_error ) : ?>
											<div class="sui-notice sui-notice-warning htaccess-warning">
												<p><?php _e( 'We tried applying the .htaccess rules automatically but we weren’t able to. Make sure your file permissions on your .htaccess file are set to 644, or <a href="#litespeed-config-manual" class="switch-manual">switch to manual mode</a> and apply the rules yourself.', 'wphb' ); ?></p>
											</div>
										<?php endif; ?>
										<a href="<?php echo esc_url( $enable_link ); ?>" class="sui-button sui-button-primary sui-margin-top"><?php esc_html_e( 'Apply Rules', 'wphb' ); ?></a>
									<?php endif; ?>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div><!-- end content -->
				</div><!-- end tab -->
				<div class="sui-tab">
					<label for="litespeed-config-manual"><?php esc_html_e( 'Manual', 'wphb' ); ?></label>
					<input type="radio" name="litespeed-config-type" id="litespeed-config-manual">
					<div class="sui-tab-content litespeed-instructions">
						<p><?php esc_html_e( 'If you are unable to get the automated method working you can copy the generated code below into your .htaccess file to activate GZip compression.', 'wphb' ); ?></p>

						<ol class="wphb-listing wphb-listing-ordered">
							<li><?php esc_html_e( 'Copy & paste the generated code below into your .htaccess file', 'wphb' ); ?></li>
							<li>
								<?php
								printf( /* translators: %s: Link to recheck GZip status */
								__( 'Next, <a href="%s">re-check your GZip status</a> to see if it worked. <a href="#" id="troubleshooting-link-litespeed">Still having issues?</a>', 'wphb' ), esc_url( $recheck_url ) );
								?>
							</li>
						</ol>

						<pre class="sui-code-snippet"><?php echo htmlentities2( $snippets['litespeed'] ); ?></pre>
						<p id="troubleshooting-gzip-litespeed"><strong>Troubleshooting</strong></p>
						<p><?php esc_html_e( 'If .htaccess does not work, and you have access to vhosts.conf or httpd.conf try this:', 'wphb' ); ?></p>
						<ol class="wphb-listing wphb-listing-ordered">
							<li><?php esc_html_e( 'Look for your site in the file and find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></li>
							<li><?php esc_html_e( 'Reload LiteSpeed.', 'wphb' ); ?></li>
							<li><?php esc_html_e( 'If you don\'t know where those files are, or you aren\'t able to reload LiteSpeed, you would need to consult with your hosting provider or a system administrator who has access to change the configuration of your server', 'wphb' ); ?></li>
						</ol>
						<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>
					</div><!-- end litespeed-instructions -->
				</div><!-- end tab -->
			</div><!-- end tabs -->
		</div><!-- end wphb-server-instructions -->

		<div id="wphb-server-instructions-nginx" class="wphb-server-instructions" data-server="nginx" style="display: none;">
			<p><?php esc_html_e( 'For NGINX servers:', 'wphb' ); ?></p>

			<ol class="wphb-listing wphb-listing-ordered">
				<li><?php esc_html_e( 'Copy the generated code into your nginx.conf usually located at /etc/nginx/nginx.conf or /usr/local/nginx/conf/nginx.conf', 'wphb' ); ?></li>
				<li><?php esc_html_e( 'Add the code above to the http or inside server section in the file.', 'wphb' ); ?></li>
				<li><?php esc_html_e( 'Reload NGINX.', 'wphb' ); ?></li>
			</ol>

			<p><?php esc_html_e( 'If you do not have access to your NGINX config files you will need to contact your hosting provider to make these changes.', 'wphb' ); ?></p>
			<p><?php WP_Hummingbird_Utils::_still_having_trouble_link(); ?></p>

            <pre class="sui-code-snippet"><?php echo htmlentities2( $snippets['nginx'] ); ?></pre>
		</div>

		<div id="wphb-server-instructions-iis" class="wphb-server-instructions" data-server="iis" style="display: none;">
			<p>
				<?php
				printf(
					/* translators: %s: Link to TechNet */
				__( 'For IIS 7 servers and above, <a href="%s" target="_blank">visit Microsoft TechNet</a>', 'wphb' ), 'https://technet.microsoft.com/en-us/library/cc771003(v=ws.10).aspx' );
				?>
			</p>
		</div>
	<?php elseif ( $htaccess_written && $full_enabled ) : ?>
		<div class="sui-notice sui-notice-info">
			<p><?php esc_html_e( 'Automatic .htaccess rules have been applied.', 'wphb' ); ?></p>
		</div>
		<a href="<?php echo esc_url( $disable_link ); ?>" class="sui-button sui-button-ghost"><?php esc_html_e( 'Deactivate', 'wphb' ); ?></a>
	<?php endif; ?>
	</div>
</div>