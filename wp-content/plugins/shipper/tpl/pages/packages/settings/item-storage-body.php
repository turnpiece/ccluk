<?php
/**
 * Shipper package settings templates: storage item body template
 *
 * @since v1.1
 * @package shipper
 */

$model   = new Shipper_Model_Stored_Options;
$storage = $model->get( Shipper_Model_Stored_Options::KEY_PACKAGE_LOCATION,
	Shipper_Model_Fs_Package::get_root_path() );
//only use relative to display
$relative_storage = '/' . ltrim( str_replace( ABSPATH, '', $storage ), '/' );
$exclude          = $model->get(
	Shipper_Model_Stored_Options::KEY_PACKAGE_EXCLUDE,
	true
);
?>
<div class="sui-form-field">
	<span class="sui-label">
		<?php esc_html_e( 'Location', 'shipper' ); ?>
	</span>
    <span class="sui-description">
		<?php esc_html_e( 'By default, Shipper creates a default directory to keep your package. You can change the storage directory by replacing the path below with the relative path of your chosen directory.', 'shipper' ); ?>
	</span>
    <input type="text"
           name="storage_location"
           value="<?php echo esc_attr( $relative_storage ); ?>"
           class="sui-form-control"/>
</div><!-- sui-form-field -->

<div class="sui-form-field">
	<span class="sui-label">
		<?php esc_html_e( 'Exclude from package builds', 'shipper' ); ?>
	</span>
    <span class="sui-description">
		<?php esc_html_e( 'We recommend excluding the storage directory, and all of its content and sub-folders from package builds.', 'shipper' ); ?>
	</span>

    <div class="sui-side-tabs">
        <div class="sui-tabs-menu">
            <label class="sui-tab-item <?php echo $exclude ? 'active' : ''; ?>">
                <input type="radio"
					<?php checked( true, $exclude ); ?>
                       name="exclude_storage" value="1"/>
				<?php esc_html_e( 'Exclude', 'shipper' ); ?>
            </label>

            <label class="sui-tab-item <?php echo ! $exclude ? 'active' : ''; ?>">
                <input type="radio"
					<?php checked( false, $exclude ); ?>
                       name="exclude_storage" value="0"/>
				<?php esc_html_e( 'Include', 'shipper' ); ?>
            </label>
        </div>
    </div><!-- sui-side-tabs -->

</div><!-- sui-form-field -->