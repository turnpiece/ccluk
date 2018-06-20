<?php
/**
 * Asset optimization row (basic view).
 *
 * @package Hummingbird
 *
 * @var string      $base_name          Base name.
 * @var bool|string $compressed_size    False if no compressed size. Or size.
 * @var bool        $disabled           Enabled or disabled state.
 * @var array       $disable_switchers  Array of disabled fields.
 * @var string      $ext                File extension. Possible values: CSS, OTHER, JS.
 * @var string      $filter             Filter string for filtering.
 * @var string      $full_src           File URL.
 * @var array       $item               File info.
 * @var bool|string $original_size      False if no original size. Or size.
 * @var bool        $is_ssl             True if site is ssl.
 * @var bool        $minified_file      True if site is file is already minified (extension *.min.*).
 * @var bool        $processed          True file has been processed (compressed).
 * @var bool        $compressed         True if processed file is smaller than original file.
 * @var string      $position           File position. Possible values: '' or 'footer'.
 * @var string      $rel_src            Relative path to file.
 * @var bool|array  $row_error          False if no error, or array with error.
 * @var string      $type               Possible values: styles, scripts or other.
 */
?>
<input type="hidden" name="<?php echo esc_attr( $base_name ); ?>[handle]" value="<?php echo esc_attr( $item['handle'] ); ?>">
<input type="hidden" name="<?php echo esc_attr( $base_name ); ?>[include]" value="1">
<div class="wphb-border-row<?php echo ( $disabled ) ? ' disabled' : ''; ?>"
	 id="wphb-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
	 data-filter="<?php echo esc_attr( $item['handle'] . ' ' . $ext ); ?>"
	 data-filter-secondary="<?php echo esc_attr( $filter ); echo 'OTHER' === $ext ? 'other' : ''?>">
	<?php if ( ! $compressed ) : ?>
		<span class="wphb-row-status wphb-row-status-already-compressed sui-tooltip sui-tooltip-top-right sui-tooltip-constrained"
			  data-tooltip="<?php esc_attr_e( 'This file has already been compressed – we recommend you turn off compression for this file to avoid issues', 'wphb' ); ?>"><i
				class="sui-icon-warning-alert" aria-hidden="true"></i></span>
	<?php elseif ( 'OTHER' === $ext ) : ?>
		<span class="wphb-row-status wphb-row-status-other sui-tooltip sui-tooltip-top-right sui-tooltip-constrained"
			  data-tooltip="<?php esc_attr_e( 'This file has no linked URL, it will not be combined/minified', 'wphb' ); ?>"><i
				class="sui-icon-info" aria-hidden="true"></i></span>
	<?php endif; ?>
	<span class="wphb-row-status wphb-row-status-changed sui-tooltip sui-tooltip-top-right sui-hidden"
		  data-tooltip="<?php esc_attr_e( 'You need to publish your changes for your new settings to take effect', 'wphb' ); ?>"><i
			class="sui-icon-update" aria-hidden="true"></i></span>
	<div class="fileinfo-group">
		<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( strtolower( $ext ) ); ?>">
			<?php echo esc_html( substr( $ext, 0, 3 ) ); ?>
		</span>

		<div class="wphb-minification-file-info">
			<span><?php echo esc_html( $item['handle'] ); ?></span>

			<?php if ( ( in_array( 'minify', $disable_switchers, true ) && ! $disabled ) || ! $original_size ) : ?>
				<span><?php esc_html_e( 'Filesize Unknown', 'wphb' ); ?> &mdash;</span>
			<?php elseif ( $minified_file || $original_size === $compressed_size ) : ?>
				<span class="original-size"><?php echo esc_html( $original_size ); ?>KB &mdash;</span>
			<?php elseif ( $processed ) : ?>
				<?php if ( $compressed ) : ?>
					<?php
					$size_diff = $original_size - $compressed_size;
					?>
					<span class="sui-tooltip sui-tooltip-constrained" data-tooltip="<?php echo esc_html( 'This assets file size has been reduced by ' ) . esc_attr( $size_diff ); ?>KB">
						<span class="original-size crossed-out"><?php echo esc_html( $original_size ); ?>KB</span>
						<i class="sui-icon-chevron-down" aria-hidden="true"></i>
						<span class="compressed-size"><?php echo esc_html( $compressed_size ); ?>KB</span>
					</span>
					<span> &mdash;</span>
				<?php else : ?>
					<span class="original-size"><?php echo esc_html( $original_size ); ?>KB &mdash;</span>
				<?php endif; ?>
			<?php elseif ( in_array( $item['handle'], $options['dont_minify'][ $type ], true ) ) : ?>
				<span class="original-size"><?php echo esc_html( $original_size ); ?>KB &mdash;</span>
			<?php else : ?>
				<span class="wphb-row-status wphb-row-status-queued sui-tooltip sui-tooltip-top-right sui-tooltip-constrained"
					  data-tooltip="<?php esc_attr_e( 'This file is queued for compression. It will get optimized when someone visits a page that requires it.', 'wphb' ); ?>"><i
						class="sui-icon-loader sui-loading" aria-hidden="true"></i></span>
				<span class="original-size"><?php echo esc_html( $original_size ); ?>KB &mdash;</span>
			<?php endif; ?>

			<a href="<?php echo esc_url( $full_src ); ?>" target="_blank">
				<?php echo esc_html( urldecode( basename( $rel_src ) ) ); ?>
			</a>
		</div>
	</div><!-- end fileinfo-group -->

	<div class="checkbox-group">
		<?php
		if ( in_array( 'minify', $disable_switchers, true ) && ! $disabled ) {
			$tooltip = __( 'This file type cannot be compressed and will be left alone', 'wphb' );
		} elseif ( $minified_file ) {
			$tooltip = __( 'This file is already compressed', 'wphb' );
		} else {
			$tooltip = __( 'Compression is on for this file, which aims to reduce its size', 'wphb' );
			if ( in_array( $item['handle'], $options['dont_minify'][ $type ], true ) ) {
				$tooltip = __( 'Compression is off for this file. Turn it on to reduce its size', 'wphb' );
			}
		}
		?>

		<input type="checkbox" <?php disabled( in_array( 'minify', $disable_switchers, true ) || $disabled || $minified_file ); ?>
			   id="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
			   class="toggle-checkbox toggle-minify"
			   name="<?php echo esc_attr( $base_name ); ?>[minify]" <?php checked( in_array( $item['handle'], $options['dont_minify'][ $type ], true ), false ); ?>
			   aria-label="<?php esc_attr_e( 'Compress', 'wphb' ); ?>">
		<label for="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
			   class="toggle-label sui-tooltip sui-tooltip-top-left sui-tooltip-constrained"
			   data-tooltip="<?php echo esc_attr( $tooltip ); ?>">
			<span class="hb-icon-minify" aria-hidden="true"></span>
		</label>
	</div><!-- end checkbox-group -->

</div><!-- end wphb-border-row -->