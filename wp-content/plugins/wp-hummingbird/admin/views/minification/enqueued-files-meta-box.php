<?php
/**
 * Minification table.
 *
 * @package Hummingbird
 *
 * @var int    $error_time_left  Time left before next scan is possible.
 * @var bool   $is_server_error  Server error status.
 * @var bool   $is_ssl           Is server running ssl.
 * @var string $scripts_rows     Table rows for minified scripts.
 * @var array  $selector_filter  List of items to filter by.
 * @var array  $server_errors    List of server errors.
 * @var string $styles_rows      Table rows for minified stykes.
 */

?>
<?php if ( $is_server_error ) : ?>
	<div class="wphb-notice wphb-notice-error wphb-notice-box can-close">
		<span class="close"></span>
		<p>
			<?php printf(
				/* translators: %d: Time left before another retry. */
				__( 'It seems that we are having problems in our servers. Minification will be turned off for %d minutes', 'wphb' ),
			$error_time_left ); ?>
		</p>
		<p><?php echo $server_errors[0]->get_error_message(); ?></p>
	</div>
<?php endif; ?>

<p><?php esc_html_e( 'Choose what files to minify, combine and where to position them in the page.', 'wphb' ); ?></p>

<?php if ( $is_ssl ) {
	$this->admin_notices->show( 'http2-info', __( "We've disabled the Combine option because your server has HTTP/2 activated. HTTP/2 automatically optimizes the delivery of assets for you", 'wphb' ), 'blue-info', false, true );
} ?>

<div class="alignleft">
	<a href="#bulk-update-modal" class="button button-notice disabled" id="bulk-update" rel="dialog">
		<?php esc_html_e( 'Bulk Update', 'wphb' ); ?>
	</a>
</div>
<div class="buttons alignright">
	<a href="#" class="button button-ghost" id="wphb-minification-filter-button">
		<?php esc_html_e( 'Filter', 'wphb' ); ?>
	</a>
</div>
<div class="clear"></div>

<div id="wphb-minification-filter" class="wphb-border-frame hidden">
	<div class="wphb-minification-filter-block" id="wphb-minification-filter-block-search">
		<h3 class="wphb-block-title"><?php esc_html_e( 'Filter', 'wphb' ); ?></h3>

		<div class="wphb-filters-data">

			<div class="wphb-minification-filter-field wphb-minification-filter-field-select">
				<label for="wphb-secondary-filter" class="screen-reader-text"><?php esc_html_e( 'Filter plugin or theme.', 'wphb' ); ?></label>
				<select name="wphb-secondary-filter" id="wphb-secondary-filter">
					<option value=""><?php esc_html_e( 'Choose Plugin or Theme', 'wphb' ); ?></option>
					<option value="other"><?php esc_html_e( 'Others', 'wphb' ); ?></option>
					<?php foreach ( $selector_filter as $secondary_filter ) : ?>
						<option value="<?php echo esc_attr( $secondary_filter ); ?>"><?php echo esc_html( $secondary_filter ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

			<div class="wphb-minification-filter-field wphb-minification-filter-field-search">
				<label for="wphb-s" class="screen-reader-text"><?php esc_html_e( 'Search by name or extension', 'wphb' ); ?></label>
				<input type="text" id="wphb-s" name="s" placeholder="<?php esc_attr_e( 'Search by name or extension', 'wphb' ); ?>" autocomplete="off">
			</div>

		</div>

	</div>
</div>

<div class="wphb-enqueued-files">
	<div class="wphb-border-row-header">
		<div class="wphb-minification-file-select">
			<label for="minification-bulk-file" class="screen-reader-text"><?php esc_html_e( 'Select all files', 'wphb' ); ?></label>
			<input type="checkbox" id="minification-bulk-file" name="minification-bulk-files" class="wphb-minification-bulk-file-selector">
		</div>
		<div class="wphb-minification-file-details"><?php esc_html_e( 'File Details', 'wphb' ); ?></div>
		<div class="wphb-minification-exclude">&nbsp;</div>
		<div class="wphb-minification-row-details">
			<span><?php esc_html_e( 'Configuration', 'wphb' ); ?></span>
			<span><?php esc_html_e( 'Filesize', 'wphb' ); ?></span>
		</div>
	</div>
	<?php echo $styles_rows; ?>
	<?php echo $scripts_rows; ?>
	<div class="buttons alignright">
		<button type="submit" class="button button-grey button-large wphb-discard hidden"><?php esc_html_e( 'Discard Changes', 'wphb' ); ?></button>
		<input type="submit" class="button button-large" name="submit" value="<?php esc_attr_e( 'Save Changes', 'wphb' ); ?>"/>
	</div>
	<div class="clear"></div>
</div>

<?php wp_nonce_field( 'wphb-enqueued-files' ); ?>
<?php wphb_bulk_update_modal(); ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery( trackChanges );

		// Track changes made to the form.
		function trackChanges(){
			jQuery('.wphb-discard').hide();

			jQuery(':input').on('change', function( objEvent ) {

				jQuery( this ).toggleClass('changed');
				var $changed = jQuery('.wphb-enqueued-files').find('input.changed');
				var buldUpdateButton = jQuery('#bulk-update');
				if ( $changed.length === 0 ) {
					jQuery('.wphb-discard').hide();
					buldUpdateButton.removeClass('button-grey');
					buldUpdateButton.addClass('button-notice disabled');
				} else {
					jQuery('.wphb-discard').show();
					buldUpdateButton.removeClass('button-notice disabled');
					buldUpdateButton.addClass('button-grey');
				}
			});
		}

		// Toggle checkboxes.
		jQuery('#minification-bulk-file').on('click', function() {
			var checkBoxes = jQuery('input[class=wphb-minification-file-selector]');
			checkBoxes.prop('checked', !checkBoxes.prop('checked'));
		});
	})
</script>