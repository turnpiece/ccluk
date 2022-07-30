<?php
/**
 * Shipper package migration modals: package-specific settings template, files section
 *
 * @since v1.1
 * @package shipper
 */

$themes_path  = Shipper_Helper_Fs_Path::get_relpath(
	get_theme_root()
);
$plugins_path = trailingslashit(
	Shipper_Helper_Fs_Path::get_relpath( WP_PLUGIN_DIR )
);
$media_path   = Shipper_Helper_Fs_Path::get_relpath(
	trailingslashit( WP_CONTENT_DIR ) . 'uploads/'
);
?>
<div class="sui-form-field shipper-file-exclusions">
	<label class="sui-label">
		<?php esc_html_e( 'File Exclusion Filter', 'shipper' ); ?>
		<div class="shipper-quicklinks">
			<a href="#themes" data-path="<?php echo esc_attr( $themes_path ); ?>">
				[<?php esc_html_e( 'Themes', 'shipper' ); ?>]
			</a>,
			<a href="#plugins" data-path="<?php echo esc_attr( $plugins_path ); ?>">
				[<?php esc_html_e( 'Plugins', 'shipper' ); ?>]
			</a>,
			<a href="#media" data-path="<?php echo esc_attr( $media_path ); ?>">
				[<?php esc_html_e( 'Media', 'shipper' ); ?>]
			</a>
		</div>
	</label>
	<textarea class="sui-form-control" placeholder=".zip&#10;/entire-folder/&#10;/folder/file.zip&#10;/folder/*.txt&#10;/folder/sub-folder/*.*&#10;*/folder/*.*"></textarea>
</div>
<span class="shipper-description">
	<?php
	$description = __( 'Enter one exclusion rule per line. We use pattern matching to exclude files based on your exclusion rules.', 'shipper' );

	if ( Shipper_Helper_Assets::has_docs_links() ) {
		$description .= sprintf(
			/* translators: %s: shipper doc url. */
			__( ' Not sure how to use them? Refer to our <a href="%s" target="_blank">file exclusion docs</a>.', 'shipper' ),
			'https://wpmudev.com/docs/wpmu-dev-plugins/shipper/#Migration-Filters'
		);
	}

	echo wp_kses_post( $description );
	?>
</span>