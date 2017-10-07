<div class="wphb-content">

	<div id="wphb-server-instructions-apache" class="wphb-server-instructions hidden" data-server="apache">
		<?php if ( $htaccess_writable && ! $gzip_already_active ): ?>
			<p><?php _e( 'Hummingbird will try to write into your <strong>.htaccess</strong> file for you', 'wphb' ); ?></p>
			<p><a href="#" id="toggle-apache-instructions"><?php esc_html_e( 'Want to do it manually?', 'wphb' ); ?></a></p>
		<?php endif; ?>

		<div class="apache-instructions <?php echo $htaccess_writable && ! $gzip_already_active ? 'hidden' : ''; ?>">

			<p><?php esc_html_e( 'For Apache servers:', 'wphb'); ?></p>

			<p><?php _e( 'Copy the generated code into your <strong>.htaccess</strong> file', 'wphb'); ?></p>

			<p><?php _e( 'If .htaccess does not work, and you have access to <strong>vhosts.conf</strong> or <strong>httpd.conf</strong> try this:', 'wphb' ); ?></p>
			<ol class="wphb-listing wphb-listing-ordered">
				<li><?php esc_html_e( 'Look for your site in the file and find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></li>
				<li><?php _e( 'Reload Apache.', 'wphb' ); ?></li>
			</ol>

			<p><?php _e( 'If you don\'t know where those files are, or you aren\'t able to reload Apache, you would need to <strong>consult with your hosting provider or a system administrator who has access</strong> to change the configuration of your server', 'wphb' ); ?></p>

			<?php _wphb_still_having_trouble_link(); ?>

			<div id="wphb-code-snippet">
				<div id="wphb-code-snippet-apache" class="wphb-code-snippet">
					<div class="wphb-block-content">
						<pre><?php echo htmlentities2( $snippets['apache'] ); ?></pre>
					</div>

				</div>
			</div>

		</div>
	</div>

	<div id="wphb-server-instructions-litespeed" class="wphb-server-instructions hidden" data-server="LiteSpeed">
		<?php if ( $htaccess_writable && ! $gzip_already_active ): ?>
			<p><?php _e( 'Hummingbird will try to write into your <strong>.htaccess</strong> file for you', 'wphb' ); ?></p>
			<p><a href="#" id="toggle-litespeed-instructions"><?php esc_html_e( 'Want to do it manually?', 'wphb' ); ?></a></p>
		<?php endif; ?>

		<div class="litespeed-instructions <?php echo $htaccess_writable && ! $gzip_already_active ? 'hidden' : ''; ?>">

			<p><?php esc_html_e( 'For LiteSpeed servers:', 'wphb'); ?></p>

			<p><?php _e( 'Copy the generated code into your <strong>.htaccess</strong> file', 'wphb'); ?></p>

			<p><?php _e( 'If .htaccess does not work, and you have access to <strong>vhosts.conf</strong> or <strong>httpd.conf</strong> try this:', 'wphb' ); ?></p>
			<ol class="wphb-listing wphb-listing-ordered">
				<li><?php esc_html_e( 'Look for your site in the file and find the line that starts with <Directory> - add the code above into that section and save the file.', 'wphb' ); ?></li>
				<li><?php _e( 'Reload LiteSpeed.', 'wphb' ); ?></li>
			</ol>

			<p><?php _e( 'If you don\'t know where those files are, or you aren\'t able to reload Apache, you would need to <strong>consult with your hosting provider or a system administrator who has access</strong> to change the configuration of your server', 'wphb' ); ?></p>

			<?php _wphb_still_having_trouble_link(); ?>

			<div id="wphb-code-snippet">
				<div id="wphb-code-snippet-litespeed" class="wphb-code-snippet">
					<div class="wphb-block-content">
						<pre><?php echo htmlentities2( $snippets['litespeed'] ); ?></pre>
					</div>

				</div>
			</div>

		</div>
	</div>

	<div id="wphb-server-instructions-nginx" class="wphb-server-instructions hidden" data-server="nginx">
		<p><?php esc_html_e( 'For NGINX servers:', 'wphb'); ?></p>

		<ol class="wphb-listing wphb-listing-ordered">
			<li><?php _e( 'Copy the generated code into your <strong>nginx.conf</strong> usually located at <strong>/etc/nginx/nginx.conf</strong> or <strong>/usr/local/nginx/conf/nginx.conf</strong>', 'wphb' ); ?></li>
			<li><?php _e( 'Add the code below to the <strong>http</strong> section in the file.', 'wphb' ); ?></li>
			<li><?php _e( 'Reload NGINX.', 'wphb' ); ?></li>
		</ol>

		<p><?php _e( 'If you do not have access to your NGINX config files you will need to contact your hosting provider to make these changes.', 'wphb' ); ?></p>
		<?php _wphb_still_having_trouble_link(); ?>

		<div id="wphb-code-snippet">
			<div id="wphb-code-snippet-apache" class="wphb-code-snippet">
				<div class="wphb-block-content">
					<pre><?php echo htmlentities2( $snippets['nginx'] ); ?></pre>
				</div>
			</div>
		</div>
	</div>

	<div id="wphb-server-instructions-iis" class="wphb-server-instructions hidden" data-server="iis">
		<p><?php printf( __( 'For IIS servers, <a href="%s">visit Microsoft TechNet</a>', 'wphb'), 'https://www.microsoft.com/technet/prodtechnol/WindowsServer2003/Library/IIS/25d2170b-09c0-45fd-8da4-898cf9a7d568.mspx?mfr=true' ); ?></p>
	</div>

	<div id="wphb-server-instructions-iis-7" class="wphb-server-instructions hidden" data-server="iis-7">
		<p><?php printf( __( 'For IIS 7 servers, <a href="%s">visit Microsoft TechNet</a>', 'wphb'), 'https://technet.microsoft.com/en-us/library/cc771003(v=ws.10).aspx' ); ?></p>
	</div>

</div>