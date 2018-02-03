<?php
/**
 * Minification row (basic view).
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

	<div class="fileinfo-group">
		<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( strtolower( $ext ) ); ?>">
			<?php echo esc_html( substr( $ext, 0, 3 ) ); ?>
		</span>

		<div class="wphb-minification-file-info">
			<span><?php echo esc_html( $item['handle'] ); ?></span>

			<?php if ( ( in_array( 'minify', $disable_switchers, true ) && ! $disabled ) || ! $original_size ) : ?>
				<span><?php esc_html_e( 'Size N/A', 'wphb' ); ?> &mdash;</span>
			<?php elseif ( $minified_file || $original_size === $compressed_size ) : ?>
				<span class="original-size"><?php echo esc_html( $original_size ); ?>KB &mdash;</span>
			<?php elseif ( $original_size && $compressed_size ) : ?>
				<span class="original-size crossed-out"><?php echo esc_html( $original_size ); ?>KB</span>
				<span class="dev-icon dev-icon-caret_down"></span>
				<span class="compressed-size"><?php echo esc_html( $compressed_size ); ?>KB &mdash;</span>
			<?php else : ?>
				<span><?php esc_html_e( 'Compressing...', 'wphb' ); ?></span>
			<?php endif; ?>

			<a href="<?php echo esc_url( $full_src ); ?>" target="_blank">
				<?php echo esc_html( urldecode( basename( $rel_src ) ) ); ?>
			</a>
		</div>
	</div><!-- end fileinfo-group -->

	<div class="checkbox-group">
		<?php if ( in_array( 'minify', $disable_switchers, true ) && ! $disabled ) : ?>
			<span class="tooltip tooltip-right" tooltip="<?php esc_attr_e( 'This file type cannot be compressed and will be left alone', 'wphb' ); ?>">
				<?php esc_html_e( "Can't be compressed", 'wphb' ); ?>
			</span>
		<?php elseif ( $minified_file ) : ?>
			<span class="tooltip tooltip-right" tooltip="<?php esc_attr_e( 'This file has already been compressed', 'wphb' ); ?>">
				<?php esc_html_e( 'Already compressed', 'wphb' ); ?>
			</span>
		<?php else : ?>
			<input type="checkbox" <?php disabled( in_array( 'minify', $disable_switchers, true ) || $disabled || $minified_file ); ?>
				   id="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
				   class="toggle-checkbox toggle-minify"
				   name="<?php echo esc_attr( $base_name ); ?>[minify]" <?php checked( in_array( $item['handle'], $options['dont_minify'][ $type ], true ), false ); ?>
				   aria-label="<?php esc_attr_e( 'Compress', 'wphb' ); ?>">
			<label for="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
				<span class="hb-icon-minify" aria-hidden="true">
					<span><?php esc_html_e( 'Compress', 'wphb' ); ?></span>
				</span>
			</label>
		<?php endif; ?>
	</div><!-- end checkbox-group -->

</div><!-- end wphb-border-row -->