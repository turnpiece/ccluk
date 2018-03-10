<?php
/**
 * Asset optimization row (advanced view).
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
<div class="wphb-border-row<?php echo ( $disabled ) ? ' disabled' : ''; ?>"
	 id="wphb-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
	 data-filter="<?php echo esc_attr( $item['handle'] . ' ' . $ext ); ?>"
	 data-filter-secondary="<?php echo esc_attr( $filter ); echo 'OTHER' === $ext ? 'other' : ''?>">

	<div class="fileinfo-group">
		<div class="wphb-minification-file-select">
			<label for="minification-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="screen-reader-text"><?php esc_html_e( 'Select file', 'wphb' ); ?></label>
			<input type="checkbox" data-type="<?php echo esc_attr( $ext ); ?>" data-handle="<?php echo esc_attr( $item['handle'] ); ?>" id="minification-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" name="minification-file[]" class="wphb-minification-file-selector">
		</div>

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

	<div class="wphb-minification-row-details">
		<div class="checkbox-group wphb-minification-advanced-group">
			<?php
			$tooltip = __( 'Compress', 'wphb' );
			if ( in_array( 'minify', $disable_switchers, true ) && ! $disabled ) {
				$tooltip = __( 'This file can’t be compressed', 'wphb' );
				$dont_minify = true;
			}
			if ( $minified_file ) {
				$tooltip = __( 'This file has already been compressed', 'wphb' );
			}
			?>
			<input type="checkbox" <?php disabled( in_array( 'minify', $disable_switchers, true ) || $disabled || $minified_file ); ?>
				   id="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
				   class="toggle-checkbox toggle-minify"
				   name="<?php echo esc_attr( $base_name ); ?>[minify]" <?php checked( in_array( $item['handle'], $options['dont_minify'][ $type ], true ), false ); ?>
				   aria-label="<?php esc_attr_e( 'Compress', 'wphb' ); ?>">
			<label for="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify" aria-hidden="true"></span>
			</label>
			<?php
			$tooltip = __( 'Combine', 'wphb' );
			if ( in_array( 'combine', $disable_switchers, true ) && ! $disabled || $is_ssl ) {
				$tooltip = __( 'This file can’t be combined', 'wphb' );
				$dont_combine = true;
			}
			?>
			<input type="checkbox" <?php disabled( in_array( 'combine', $disable_switchers, true ) || $disabled || $is_ssl ); ?>
				   class="toggle-checkbox toggle-combine" name="<?php echo esc_attr( $base_name ); ?>[combine]"
				   id="wphb-minification-combine-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['combine'][ $type ], true ) ); ?>
				   aria-label="<?php esc_attr_e( 'Combine', 'wphb' ); ?>">
			<label for="wphb-minification-combine-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-combine" aria-hidden="true"></span>
			</label>
			<input type="checkbox" <?php disabled( in_array( 'position', $disable_switchers, true ) || $disabled ); ?>
				   class="toggle-checkbox toggle-position-footer" name="<?php echo esc_attr( $base_name ); ?>[position]"
				   id="wphb-minification-position-footer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( $position, 'footer' ); ?> value="footer"
				   aria-label="<?php esc_attr_e( 'Footer', 'wphb' ); ?>">
			<label for="wphb-minification-position-footer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
				<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Move to Footer', 'wphb' ); ?>" aria-hidden="true"></span>
				<span class="hb-icon-minify-footer" aria-hidden="true"></span>
			</label>
			<?php if ( 'scripts' === $type ) : ?>
				<input type="checkbox" <?php disabled( in_array( 'defer', $disable_switchers, true ) || $disabled ); ?>
					   class="toggle-checkbox toggle-defer" name="<?php echo esc_attr( $base_name ); ?>[defer]"
					   id="wphb-minification-defer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['defer'][ $type ], true ) ); ?> value="1"
					   aria-label="<?php esc_attr_e( 'Defer', 'wphb' ); ?>">
				<label for="wphb-minification-defer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
					<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Force load this file after the page has loaded', 'wphb' ); ?>" aria-hidden="true"></span>
					<span class="hb-icon-minify-defer" aria-hidden="true"></span>
				</label>
			<?php elseif ( 'styles' === $type ) : ?>
				<input type="checkbox" <?php disabled( in_array( 'inline', $disable_switchers, true ) || $disabled ); ?>
					   class="toggle-checkbox toggle-inline" name="<?php echo esc_attr( $base_name ); ?>[inline]"
					   id="wphb-minification-inline-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['inline'][ $type ], true ) ); ?> value="1">
				<label for="wphb-minification-inline-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
					<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Inline CSS', 'wphb' ); ?>"></span>
					<span class="hb-icon-minify-inline" aria-hidden="true"></span>
				</label>
			<?php endif; ?>
		</div><!-- end checkbox-group -->

		<div class="wphb-minification-exclude">
			<span class="toggle tooltip-s tooltip-right" tooltip="<?php $disabled ? esc_attr_e( 'Include file', 'wphb' ) : esc_attr_e( 'Exclude file', 'wphb' ); ?>">
				<input type="checkbox"
					<?php disabled( in_array( 'include', $disable_switchers, true ) ); ?>
					   id="wphb-minification-include-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
					   class="toggle-checkbox toggle-include"
					   name="<?php echo esc_attr( $base_name ); ?>[include]"
					<?php checked( $disabled, false ); ?>
					   value="1">
				<label for="wphb-minification-include-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label small"></label>
			</span>
		</div><!-- end wphb-minification-exclude -->
	</div><!-- end wphb-minification-row-details -->

</div><!-- end wphb-border-row -->