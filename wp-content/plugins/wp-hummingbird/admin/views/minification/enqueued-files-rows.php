<?php
/**
 * Minification row.
 *
 * @package Hummingbird
 *
 * @var string      $base_name          Base name.
 * @var bool|string $compressed_size    False if no compressed size. Or size.
 * @var array       $disable_switchers  Array of disabled fields.
 * @var string      $ext                File extension. Possible values: CSS, OTHER, JS.
 * @var string      $filter             Filter string for filtering.
 * @var string      $full_src           File URL.
 * @var array       $item               File info.
 * @var bool|string $original_size      False if no original size. Or size.
 * @var bool        $is_ssl             True if site is ssl.
 * @var bool        $minified_file      True if site is file is already minified.
 * @var string      $position           File position. Possible values: '' or 'footer'.
 * @var string      $rel_src            Relative path to file.
 * @var bool|array  $row_error          False if no error, or array with error.
 * @var string      $type               Possible values: styles, scripts or other.
 */

?>
<input type="hidden" name="<?php echo esc_attr( $base_name ); ?>[handle]" value="<?php echo esc_attr( $item['handle'] ); ?>"/>
<div class="wphb-border-row
	<?php echo ( in_array( $item['handle'], $options['block'][ $type ], true ) ) ? 'disabled' : ''; ?>"
	 id="wphb-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
	 data-filter="<?php echo esc_attr( $item['handle'] . ' ' . $ext ); ?>"
	 data-filter-secondary="<?php echo esc_attr( $filter ); echo 'OTHER' === $ext ? 'other' : ''?>">

	<div class="wphb-minification-file-select">
		<label for="minification-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="screen-reader-text"><?php esc_html_e( 'Select file', 'wphb' ); ?></label>
		<input type="checkbox" data-type="<?php echo esc_attr( $ext ); ?>" data-handle="<?php echo esc_attr( $item['handle'] ); ?>" id="minification-file-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" name="minification-file[]" class="wphb-minification-file-selector">
	</div>
	<div class="wphb-minification-file-details">
		<span class="wphb-filename-extension wphb-filename-extension-<?php echo esc_attr( strtolower( $ext ) ); ?>"><?php echo esc_html( substr( $ext, 0, 3 ) ); ?></span>
		<div class="wphb-filename-info">
			<span class="wphb-filename-info-name"><?php echo esc_html( $item['handle'] ); ?></span>
			<a class="wphb-filename-info-url" target="_blank" href="<?php echo esc_url( $full_src ); ?>"><?php echo esc_html( basename( $rel_src ) ); ?></a>
		</div>
	</div>

	<div class="wphb-minification-row-details">
		<div class="wphb-minification-configuration">
			<strong><?php esc_html_e( 'Configuration', 'wphb' ); ?></strong>
			<div class="tooltip-box">
				<div class="checkbox-group">
					<?php
					$tooltip = __( 'Compress this file to reduce it’s filesize', 'wphb' );
					if ( in_array( 'minify', $disable_switchers, true ) && ! in_array( $item['handle'], $options['block'][ $type ], true ) ) {
						$tooltip = __( 'This file can’t be minified', 'wphb' );
						$dont_minify = true;
					}
					if ( $minified_file ) {
						$tooltip = __( 'This file has already been minified', 'wphb' );
					}
					?>
					<input type="checkbox" <?php disabled( in_array( 'minify', $disable_switchers, true ) || in_array( $item['handle'], $options['block'][ $type ], true ) || $minified_file ); ?>
						   id="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
						   class="toggle-checkbox toggle-minify"
						   name="<?php echo esc_attr( $base_name ); ?>[minify]" <?php checked( in_array( $item['handle'], $options['dont_minify'][ $type ], true ), false ); ?>
						   aria-label="<?php esc_attr_e( 'Minify', 'wphb' ); ?>">
					<label for="wphb-minification-minify-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
						<span class="toggle tooltip-l" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"></span>
						<i class="hb-icon-minify" aria-hidden="true"></i>
						<span aria-hidden="true"><?php esc_html_e( 'Minify', 'wphb' ); ?></span>
					</label>
					<?php
					$tooltip = __( 'Combine this file with others if possible', 'wphb' );
					if ( in_array( 'combine', $disable_switchers, true ) && ! in_array( $item['handle'], $options['block'][ $type ], true ) || $is_ssl ) {
						$tooltip = __( 'This file can’t be combined', 'wphb' );
						$dont_combine = true;
					}
					?>
					<input type="checkbox" <?php disabled( in_array( 'combine', $disable_switchers, true ) || in_array( $item['handle'], $options['block'][ $type ], true ) || $is_ssl ); ?>
						   class="toggle-checkbox toggle-combine" name="<?php echo esc_attr( $base_name ); ?>[combine]"
						   id="wphb-minification-combine-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['combine'][ $type ], true ) ); ?>
						   aria-label="<?php esc_attr_e( 'Combine', 'wphb' ); ?>">
					<label for="wphb-minification-combine-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
						<span class="toggle tooltip-l" tooltip="<?php echo esc_attr( $tooltip ); ?>" aria-hidden="true"></span>
						<i class="hb-icon-minify-combine" aria-hidden="true"></i>
						<span aria-hidden="true"><?php esc_html_e( 'Combine', 'wphb' ); ?></span>
					</label>
					<input type="checkbox" <?php disabled( in_array( 'position', $disable_switchers, true ) || in_array( $item['handle'], $options['block'][ $type ], true ) ); ?>
						   class="toggle-checkbox toggle-position-footer" name="<?php echo esc_attr( $base_name ); ?>[position]"
						   id="wphb-minification-position-footer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( $position, 'footer' ); ?> value="footer"
						   aria-label="<?php esc_attr_e( 'Footer', 'wphb' ); ?>">
					<label for="wphb-minification-position-footer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
						<span class="toggle tooltip-l" tooltip="<?php esc_attr_e( 'Load this file in the footer of the page', 'wphb' ); ?>" aria-hidden="true"></span>
						<i class="hb-icon-minify-footer" aria-hidden="true"></i>
						<span aria-hidden="true"><?php esc_html_e( 'Footer', 'wphb' ); ?></span>
					</label>
					<?php if ( 'scripts' === $type ) : ?>
						<input type="checkbox" <?php disabled( in_array( 'defer', $disable_switchers, true ) || in_array( $item['handle'], $options['block'][ $type ], true ) ); ?>
							   class="toggle-checkbox toggle-defer" name="<?php echo esc_attr( $base_name ); ?>[defer]"
							   id="wphb-minification-defer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['defer'][ $type ], true ) ); ?> value="1"
							   aria-label="<?php esc_attr_e( 'Defer', 'wphb' ); ?>">
						<label for="wphb-minification-defer-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
							<span class="toggle tooltip-l" tooltip="<?php esc_attr_e( 'Force load this file after the page has loaded', 'wphb' ); ?>" aria-hidden="true"></span>
							<i class="hb-icon-minify-defer" aria-hidden="true"></i>
							<span aria-hidden="true"><?php esc_html_e( 'Defer', 'wphb' ); ?></span>
						</label>
					<?php elseif ( 'styles' === $type ) : ?>
						<input type="checkbox" <?php disabled( in_array( 'inline', $disable_switchers, true ) || in_array( $item['handle'], $options['block'][ $type ], true ) ); ?>
							   class="toggle-checkbox toggle-inline" name="<?php echo esc_attr( $base_name ); ?>[inline]"
							   id="wphb-minification-inline-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" <?php checked( in_array( $item['handle'], $options['inline'][ $type ], true ) ); ?> value="1">
						<label for="wphb-minification-inline-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>" class="toggle-label">
							<span class="toggle tooltip-s" tooltip="<?php esc_attr_e( 'Inline style', 'wphb' ); ?>"></span>
							<i class="hb-icon-minify-inline"></i>
							<span><?php esc_html_e( 'Inline', 'wphb' ); ?></span>
						</label>
					<?php endif; ?>
				</div>
			</div>
		</div><!-- end wphb-minification-configuration -->

		<div class="wphb-minification-file-size">
			<strong><?php esc_html_e( 'File size', 'wphb' ); ?></strong>
			<?php if ( $original_size && $compressed_size ) : ?>
				<div>
					<span class=""><?php echo esc_html( $original_size ); ?>KB</span>
					<span class="dev-icon dev-icon-caret_down"></span>
					<span class=""><?php echo esc_html( $compressed_size ); ?>KB</span>
				</div>
				<div class="wphb-scan-progress">
					<div class="wphb-scan-progress-bar">
						<span style="width: 80%"></span>
					</div>
				</div>
			<?php elseif ( $minified_file ) : ?>
				<span class="tooltip tooltip-s" tooltip="<?php esc_attr_e( 'This file has already been minified', 'wphb' ); ?>"><?php esc_html_e( 'Optimized', 'wphb' ); ?></span>
			<?php elseif ( isset( $dont_minify ) && isset( $dont_combine ) && ! in_array( $item['handle'], $options['block'][ $type ], true ) ) : ?>
				<span class="tooltip tooltip-s" tooltip="<?php esc_attr_e( 'This file type cannot be minified and will be left alone', 'wphb' ); ?>"><?php esc_html_e( 'Ignored', 'wphb' ); ?></span>
			<?php elseif ( in_array( $item['handle'], $options['block'][ $type ], true ) ) : ?>
				<span class="tooltip tooltip-s" tooltip="<?php esc_attr_e( 'Excluded from processing', 'wphb' ); ?>"><?php esc_html_e( 'Excluded', 'wphb' ); ?></span>
			<?php else : ?>
				<span class="tooltip tooltip-s" tooltip="<?php esc_attr_e( 'Waiting for a visitor to visit your homepage', 'wphb' ); ?>"><?php esc_html_e( 'Pending', 'wphb' ); ?></span>
			<?php endif; ?>
		</div><!-- end wphb-minification-file-size -->
	</div><!-- end wphb-minification-row-details -->

	<div class="wphb-minification-exclude">
		<div class="tooltip-box">
			<?php $tooltip = in_array( $item['handle'], $options['block'][ $type ], true ) ? __( 'Include this file', 'wphb' ) : __( 'Exclude this file', 'wphb' ); ?>
			<span class="toggle tooltip-s tooltip-right" tooltip="<?php echo esc_attr( $tooltip ); ?>">
				<label for="wphb-minification-include-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
					   class="toggle-cross"
					   data-type="<?php echo esc_attr( $ext ); ?>"
					   data-handle="<?php echo esc_attr( $item['handle'] ); ?>">
					<input type="checkbox"
						<?php disabled( in_array( 'include', $disable_switchers, true ) ); ?>
						   id="wphb-minification-include-<?php echo esc_attr( $ext . '-' . $item['handle'] ); ?>"
						   class="toggle-checkbox toggle-include"
						   name="<?php echo esc_attr( $base_name ); ?>[include]"
						<?php checked( in_array( $item['handle'], $options['block'][ $type ], true ), false ); ?>
						   value="1">
					<i class="<?php echo ( in_array( $item['handle'], $options['block'][ $type ], true ) ) ? 'wdv-icon wdv-icon-refresh' : 'dev-icon dev-icon-cross'; ?>" aria-hidden="true"></i>
				</label>
			</span>
		</div>
	</div>
</div><!-- end wphb-border-row -->