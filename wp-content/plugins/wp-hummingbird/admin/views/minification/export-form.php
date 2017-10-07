<?php

$settings = wphb_get_settings();

$minification_options = array(
	'blocked' => $settings['block'],
	'dont_minify' => $settings['dont_minify'],
	'combine' => $settings['combine'],
	'position' => $settings['position'],
	'plugins' => get_option( 'active_plugins' ),
	'network_plugins' => get_site_option( 'active_sitewide_plugins' ),
	'theme' => get_stylesheet()
);
?>

<p>Copy this and paste into your development site</p>
<pre style="width:70%">
	<?php echo wp_json_encode( $minification_options ); ?>
</pre>

<?php if ( defined( 'WPHB_IMPORT_MINIFICATION' ) && WPHB_IMPORT_MINIFICATION ): ?>
	<?php
	if ( isset( $_POST['action'] ) && $_POST['action'] === 'import-minification' ) {
		check_admin_referer( 'import-minification' );
		$json = json_decode( $_POST['json'] );
		if ( is_array( $json ) ) {

		}
	}
	?>
	<form action="" method="post">
		<p>Paste your JSON here</p>
		<?php wp_nonce_field( 'check-minification' ); ?>
		<input type="hidden" name="action" value="check-minification">
		<textarea name="json" id="" cols="30" rows="10">

		</textarea>
		<?php submit_button( 'Submit', 'primary', 'submit-minification-import' ); ?>
	</form>
<?php else: ?>
	<p>Want to import settings? Use <code>define( 'WPHB_IMPORT_MINIFICATION', true );</code> in your wp-config.php file</p>
<?php endif; ?>