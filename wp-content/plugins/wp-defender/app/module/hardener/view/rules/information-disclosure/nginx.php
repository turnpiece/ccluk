<p>
	<?php _e( "We can’t automatically action this fix, but follow the instructions below to patch this up.", wp_defender()->domain ) ?>
</p>
<strong><?php _e( "Instructions", wp_defender()->domain ) ?></strong>
<p>
	<?php _e( "1. Copy the generated code into your site specific .conf file usually located in a subdirectory under /etc/nginx/... or /usr/local/nginx/conf/...", wp_defender()->domain ) ?>
</p>
<p>
	<?php _e( "2. Add the code above inside the server section in the file, right before the php location block. Looks something like:", wp_defender()->domain ) ?>
    <code>location ~ \.php$ {</code>
</p>
<p>
	<?php _e( "3. Reload NGINX.", wp_defender()->domain ) ?>
</p>
<strong><?php _e( "Code", wp_defender()->domain ) ?></strong>
<?php
if ( DIRECTORY_SEPARATOR == '\\' ) {
	//Windows
	$wp_includes = str_replace( ABSPATH, '', WPINC );
	$wp_content  = str_replace( ABSPATH, '', WP_CONTENT_DIR );
} else {
	$wp_includes = str_replace( $_SERVER['DOCUMENT_ROOT'], '', ABSPATH . WPINC );
	$wp_content  = str_replace( $_SERVER['DOCUMENT_ROOT'], '', WP_CONTENT_DIR );
}

$rules = "# Turn off directory indexing
autoindex off;

# Deny access to htaccess and other hidden files
location ~ /\. {
  deny  all;
}

# Deny access to wp-config.php file
location = /wp-config.php {
  deny all;
}

# Deny access to revealing or potentially dangerous files in the /wp-content/ directory (including sub-folders)
location ~* ^$wp_content/.*\.(txt|md|exe|sh|bak|inc|pot|po|mo|log|sql)$ {
  deny all;
}
";
?>
<p><code>## WP Defender - Prevent information disclosure ##<?php echo esc_html( $rules ); ?>## WP Defender - End ##</code></p>
<div class="sui-notice">
    <p><?php echo sprintf( __( "Still having trouble? <a target='_blank' href=\"%s\">Open a support ticket</a>.", wp_defender()->domain ), 'https://premium.wpmudev.org/forums/forum/support#question' ) ?></p>
</div>