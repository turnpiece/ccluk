<?php
/**
 * Asset optimization table (basic view).
 *
 * @package Hummingbird
 *
 * @since 1.7.1
 *
 * @var int    $error_time_left  Time left before next scan is possible.
 * @var bool   $is_http2         Does server support HTTP/2.
 * @var bool   $is_server_error  Server error status.
 * @var string $scripts_rows     Table rows for minified scripts.
 * @var array  $selector_filter  List of items to filter by.
 * @var array  $server_errors    List of server errors.
 * @var string $styles_rows      Table rows for minified styles.
 * @var string $others_rows      Table rows for files not hosted locally.
 * @var string $type             Asset optimization view. Accepts: 'advanced', 'basic'.
 */
?>
<div class="wphb-minification-files">
	<div class="wphb-minification-files-header">
		<p>
			<?php esc_html_e( 'Choose which files you wish to compress and then publish your changes.', 'wphb' ); ?>
		</p>
	</div>

	<?php
	if ( $is_server_error ) {
		$message = sprintf(
			/* translators: %d: Time left before another retry. */
			__( 'It seems that we are having problems in our servers. Asset optimization will be turned off for %d minutes', 'wphb' ),
			$error_time_left
		) . '<br>' . $server_errors[0]->get_error_message();
		$this->admin_notices->show( 'minification-server-error', $message, 'error' );
	}

	if ( $is_http2 ) {
		$this->admin_notices->show( 'http2-info', __( "We've disabled the Combine option because your server has HTTP/2 activated. HTTP/2 automatically optimizes the delivery of assets for you", 'wphb' ), 'blue-info', true );
	}
	?>

	<?php if ( 'advanced' === $type ) : ?>
		<div class="wphb-minification-filter-buttons">
			<div class="sui-actions-left">
				<a class="sui-button button-notice disabled" data-a11y-dialog-show="bulk-update-modal" id="bulk-update" >
					<?php esc_html_e( 'Bulk Update', 'wphb' ); ?>
				</a>
			</div>
			<div class="sui-actions-right">
				<a href="#" class="sui-button sui-button-icon sui-button-ghost" id="wphb-minification-filter-button">
					<i class="sui-icon-filter sui-md sui-fw" aria-hidden="true"></i>
				</a>
			</div>

			<div class="clear"></div>


		</div>
		<div class="wphb-minification-filter sui-border-frame sui-hidden">
			<div class="wphb-minification-filter-block" id="wphb-minification-filter-block-search">
				<h3 class="wphb-block-title"><?php esc_html_e( 'Filter', 'wphb' ); ?></h3>

				<label for="wphb-secondary-filter" class="screen-reader-text"><?php esc_html_e( 'Filter plugin or theme', 'wphb' ); ?></label>
				<select name="wphb-secondary-filter" id="wphb-secondary-filter">
					<option value=""><?php esc_html_e( 'Choose Plugin or Theme', 'wphb' ); ?></option>
					<option value="other"><?php esc_html_e( 'Others', 'wphb' ); ?></option>
					<?php foreach ( $selector_filter as $secondary_filter ) : ?>
						<option value="<?php echo esc_attr( $secondary_filter ); ?>"><?php echo esc_html( $secondary_filter ); ?></option>
					<?php endforeach; ?>
				</select>

				<label for="wphb-s" class="screen-reader-text"><?php esc_html_e( 'Search by name or extension', 'wphb' ); ?></label>
				<input type="text" id="wphb-s" class="sui-form-control" name="s" placeholder="<?php esc_attr_e( 'Search by name or extension', 'wphb' ); ?>" autocomplete="off">
			</div>
		</div>
	<?php endif; ?>

	<?php if ( 'advanced' === $type ) : ?>
		<div class="wphb-minification-files-select">
			<label for="minification-bulk-file" class="screen-reader-text"><?php esc_html_e( 'Select all CSS files', 'wphb' ); ?></label>
			<label class="sui-checkbox">
				<input type="checkbox" id="minification-bulk-file" name="minification-bulk-files" class="wphb-minification-bulk-file-selector" data-type="CSS">
				<span aria-hidden="true"></span>
			</label>
			<h3><?php esc_html_e( 'CSS', 'wphb' ); ?></h3>
		</div>
	<?php else : ?>
		<h3><?php esc_html_e( 'CSS', 'wphb' ); ?></h3>
	<?php endif; ?>

	<div class="wphb-minification-files-table wphb-minification-files-<?php echo esc_attr( $type ); ?>">
		<?php echo $styles_rows; ?>
	</div><!-- end wphb-minification-files-table -->

	<?php if ( 'advanced' === $type ) : ?>
		<div class="wphb-minification-files-select">
			<label for="minification-bulk-file" class="screen-reader-text"><?php esc_html_e( 'Select all JavaScript files', 'wphb' ); ?></label>
			<label class="sui-checkbox">
				<input type="checkbox" id="minification-bulk-file" name="minification-bulk-files" class="wphb-minification-bulk-file-selector" data-type="JS">
				<span aria-hidden="true"></span>
			</label>
			<h3><?php esc_html_e( 'JavaScript', 'wphb' ); ?></h3>
		</div>
	<?php else : ?>
		<h3><?php esc_html_e( 'JavaScript', 'wphb' ); ?></h3>
	<?php endif; ?>

	<div class="wphb-minification-files-table wphb-minification-files-<?php echo esc_attr( $type ); ?>">
		<?php echo $scripts_rows; ?>
	</div><!-- end wphb-minification-files-table -->

	<?php if ( '' !== $others_rows ) : ?>
		<?php if ( 'advanced' === $type ) : ?>
			<div class="wphb-minification-files-select">
				<label for="minification-bulk-file" class="screen-reader-text"><?php esc_html_e( 'Select all Other files', 'wphb' ); ?></label>
				<label class="sui-checkbox">
					<input type="checkbox" id="minification-bulk-file" name="minification-bulk-files" class="wphb-minification-bulk-file-selector" data-type="OTHER">
					<span aria-hidden="true"></span>
				</label>
				<h3><?php esc_html_e( 'Other', 'wphb' ); ?></h3>
			</div>
		<?php else : ?>
			<h3><?php esc_html_e( 'Other', 'wphb' ); ?></h3>
		<?php endif; ?>

		<div class="wphb-minification-files-table wphb-minification-files-<?php echo esc_attr( $type ); ?>">
			<?php echo $others_rows; ?>
		</div><!-- end wphb-minification-files-table -->
	<?php endif; ?>
</div><!-- end wphb-minification-files -->

<?php wp_nonce_field( 'wphb-enqueued-files' ); ?>
<?php WP_Hummingbird_Utils::get_modal( 'bulk-update' ); ?>