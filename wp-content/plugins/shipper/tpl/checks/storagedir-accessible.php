<?php
/**
 * Shipper checks body copy templates: storage directory can be accessed externally
 *
 * @since v1.0.3
 * @package shipper
 */

?>
<div>
	<h4><?php esc_html_e( 'Overview', 'shipper' ); ?></h4>
	<p>
		<?php
			esc_html_e( 'Your storage directory is where Shipper stores cache files during the migration. These files contain sensitive information such as migration info, API response cache, tables, and files lists. Even though Shipper obfuscates these files, they can still be penetrated. Therefore, your storage directory should not be visible and accessible on the web.', 'shipper' );
		?>
	</p>

	<h4><?php esc_html_e( 'Status', 'shipper' ); ?></h4>
	<div class="sui-notice sui-notice-error">
		<p>
			<?php
				echo wp_kses_post( sprintf(
					__( 'Storage directory on <b>%s</b> is visible and accessible on the web.', 'shipper' ),
					$domain
				) );
			?>
		</p>
	</div>

	<h4><?php esc_html_e( 'How To Fix', 'shipper' ); ?></h4>
	<p>
		<?php
			echo wp_kses_post( sprintf( __( 'Shipper automatically tries to protect your storage directory ( <code>%s</code> ) by adding a protective .htaccess file to your working directory, which is one level above. We add the following .htaccess rule to protect your storage directory:'  , 'shipper' ), $value ) );
		?>
	</p>
	<pre class="sui-code-snippet">Order deny,allow
Deny from all
Options -Indexes</pre>
	<p>
		<?php
			esc_html_e( 'Verify that this .htaccess file is present in your working directory, along with the rule above. If not, we recommend you manually add a .htaccess file with the above code, and run Shipper again.', 'shipper' );
		?>
	</p>
	<p>
		<?php
			esc_html_e( 'However, if this rule already exists in your working directory, chances are your server is configured to disallow any .htaccess overrides or is unable to parse the .htaccess file (your server doesn\'t support .htaccess, e.g. NGINX). In this case, you can contact your hosting provider, and ask them to ensure your storage directory is not visible and accessible on the web. ', 'shipper' );
		?>
	</p>
</div>
<div class="sui-notice-top sui-notice-error sui-can-dismiss shipper-recheck-unsuccessful" style="display:none">
	<div class="sui-notice-content">
		<p>
			<?php echo wp_kses_post( sprintf(
				__( 'Storage directory web visible on %1$s. Please fix this and check again.', 'shipper' ),
				$domain
			) ); ?>
		</p>
	</div>
	<span class="sui-notice-dismiss">
		<a role="button" href="#" aria-label="Dismiss" class="sui-icon-check"></a>
	</span>
</div>