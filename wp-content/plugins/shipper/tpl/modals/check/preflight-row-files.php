<?php
/**
 * Shipper templates: preflight check invalid file lists partial
 *
 * @package shipper
 */

$exclusions = new Shipper_Model_Stored_Exclusions();
$paths      = array_keys( $exclusions->get_data() );

$exclude_msg = __( 'Exclude file from migration', 'shipper' );
$include_msg = __( 'Reinclude file', 'shipper' );
?>

<table class="sui-table shipper-filelist">
	<thead>
		<tr>
			<th class="shipper-filelist-filename">
				<label class="sui-checkbox sui-checkbox-sm">
					<input type="checkbox" name="shipper-bulk-all" />
					<span></span>
				</label>
				<?php esc_html_e( 'Filename', 'shipper' ); ?>
			</th>
			<th class="shipper-filelist-filesize" width="15%">
				<?php esc_html_e( 'Size', 'shipper' ); ?></th>
			<th class="shipper-filelist-filetype" width="10%">
				<?php esc_html_e( 'Type', 'shipper' ); ?></th>
			<th class="shipper-filelist-actions" width="20%">
				<?php esc_html_e( 'Include/Exclude', 'shipper' ); ?></th>
		</tr>
	</thead>
	<tbody>

		<?php
		foreach ( $files as $file ) {
			if ( ! isset( $file['path'] ) ) {
				continue;
			}


			$file['path'] = wp_normalize_path( realpath( $file['path'] ) );
			$is_excluded  = in_array( $file['path'], $paths, true );

			$cls = array( 'shipper-paginated' );
			if ( $is_excluded ) {
				$cls[] = 'shipper-file-excluded';
			}

			$extension = pathinfo( $file['path'], PATHINFO_EXTENSION );
			?>

		<tr
			class="<?php echo esc_attr( join( ' ', $cls ) ); ?>"
			data-size="<?php echo esc_attr( $file['size'] ); ?>"
			data-type="<?php echo esc_attr( $extension ); ?>"
			data-path="<?php echo esc_attr( $file['path'] ); ?>"
		>
				<td class="shipper-filelist-filename">
					<label class="sui-checkbox sui-checkbox-sm">
						<input type="checkbox"
							name="shipper-bulk" value="<?php echo esc_attr( $file['path'] ); ?> " />
						<span></span>
					</label>
					<b
						title="<?php echo esc_attr( $file['path'] ); ?>"
						data-tooltip="<?php echo esc_attr( $file['path'] ); ?>"
						style="--tooltip-width: 370px;"
						class="sui-tooltip">
							<span><?php echo esc_html( basename( $file['path'] ) ); ?></span></b>
				</td>

				<td class="shipper-filelist-filesize">
					<?php echo esc_html( size_format( $file['size'] ) ); ?>
				</td>

				<td class="shipper-filelist-filetype">
					<?php echo esc_html( $extension ); ?>
				</td>

				<td class="shipper-filelist-actions">
					<a data-tooltip="<?php echo esc_attr( $exclude_msg ); ?>" title="<?php echo esc_attr( $exclude_msg ); ?>" data-path="<?php echo esc_attr( $file['path'] ); ?>" href="#exclude" data-wpnonce="{{shipper-nonce-placeholder}}" class="sui-tooltip">
						<i class="sui-icon-close" aria-hidden="true"></i>
						<span><?php esc_attr_e( 'Exclude', 'shipper' ); ?></span>
					</a>
					<a data-tooltip="<?php echo esc_attr( $include_msg ); ?>" title="<?php echo esc_attr( $include_msg ); ?>" data-path="<?php echo esc_attr( $file['path'] ); ?>" href="#include" data-wpnonce="{{shipper-nonce-placeholder}}" class="sui-tooltip">
						<i class="sui-icon-update" aria-hidden="true"></i>
						<span><?php esc_attr_e( 'Include', 'shipper' ); ?></span>
					</a>
				</td>
			</tr>
	<?php } ?>
	</tbody>
</table>

<p class="shipper-note">
	<?php esc_html_e( 'Note: Excluding files won\'t delete them, they just won\'t be migrated.', 'shipper' ); ?>
</p>