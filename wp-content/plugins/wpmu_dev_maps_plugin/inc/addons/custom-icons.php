<?php
/*
Plugin Name: Custom Icons
Description: Enable this add-on to add your own map marker icons! The custom icons can be selected when you edit a marker inside the Map Editor.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Philipp Stracker (Incsub)
*/

class Agm_Icons_AdminPages {

	private function __construct() {}

	/**
	 * Initialize the Admin interface.
	 *
	 * @since 1.0
	 */
	public static function serve() {
		$me = new Agm_Icons_AdminPages();
		$me->_add_hooks();
	}

	/**
	 * Attach the hooks for the admin-page
	 *
	 * @since 1.0
	 */
	private function _add_hooks() {
		// Load the javascript that provides new map options.
		$data = array(
			'lang' => array(),
		);

		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/custom-icons.min.js', 'settings_page_agm_google_maps' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'css/icons-admin.min.css', 'settings_page_agm_google_maps' );
		lib3()->ui->add( 'media', 'settings_page_agm_google_maps' );
		lib3()->ui->data( '_agmIcons', $data, 'settings_page_agm_google_maps' );

		// Add our own options to the plugin config page.
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	/**
	 * Add new section to the plugin config page
	 *
	 * @since  1.0
	 */
	public function register_settings() {
		add_settings_section(
			'agm_google_maps_icons',
			__( 'Custom icons', AGM_LANG ),
			create_function( '', '' ),
			'agm_google_maps_options_page'
		);

		add_settings_field(
			'agm_google_maps_icon_add',
			__( 'Add new icons', AGM_LANG ),
			array( $this, 'render_settings_box_add' ),
			'agm_google_maps_options_page',
			'agm_google_maps_icons'
		);

		add_settings_field(
			'agm_google_maps_icon_list',
			__( 'Custom icons', AGM_LANG ),
			array( $this, 'render_settings_box_icons' ),
			'agm_google_maps_options_page',
			'agm_google_maps_icons'
		);
	}

	/**
	 * Renders the settings where user can preview and delete custom icons.
	 *
	 * @since  1.0
	 */
	public function render_settings_box_icons() {
		$options = get_option( 'agm_google_maps' );
		$iconlist = @$options['custom_icons'];
		?>
		<table class="icons widefat" cellspacing="0" cellpadding="0">
			<?php foreach ( array( 'thead', 'tfoot' ) as $tag ) : ?>
			<<?php echo esc_attr( $tag ); ?>>
				<tr>
					<th style="width:5%"><?php _e( 'Icon', AGM_LANG ); ?></th>
					<th style="width:75%"><?php _e( 'URL', AGM_LANG ); ?></th>
					<th style="width:15%"><?php _e( 'Width x Height', AGM_LANG ); ?></th>
					<th style="width:5%">&nbsp;</th>
				</tr>
			</<?php echo esc_attr( $tag ); ?>>
			<?php endforeach; ?>
			<tbody>
			</tbody>
		</table>
		<input type="hidden" name="agm_google_maps[custom_icons]" class="custom-icon-list" value="<?php echo esc_attr( $iconlist ); ?>" />
		<?php
	}

	/**
	 * Renders the settings where user can add new custom icons.
	 *
	 * @since  1.0
	 */
	public function render_settings_box_add() {
		?>
		<div>
			<label for="custom-icon"><?php _e( 'Icon URL', AGM_LANG ); ?>:</label>
			<input type="url" class="custom-icon-url" id="custom-icon" value="" style="display:block;width:100%" placeholder="http://..." maxlength="1024" />
		</div>
		<div>
			<span style="float: left"><img src="" class="custom-icon-preview marker-icon-32" /></span>
			<button type="button" class="add-custom-icon button disabled" disabled="disabled"
				data-enabled="<?php _e( 'Add this icon', AGM_LANG ); ?>"
				data-disabled="<?php _e( 'Enter a valid image URL', AGM_LANG ); ?>"
				>
			</button>
		</div>
		<br />
		<div style="clear: both">
			<button type="button" class="add-media-image button"><?php _e( 'Add icon from media library', AGM_LANG ); ?></button>
		</div>
		<br />
		<div>
			<em><?php _e( 'Note: All icons will be displayed in full-size on the map. In the editor and this list the icon-preview is displayed with 32 x 32 pixels.', AGM_LANG ); ?></em>
		</div>
		<?php
	}

}



class Agm_Icons_Shared {

	private function __construct() {}

	/**
	 * Initialize the traffic overlay on frontend of the website.
	 *
	 * @since 1.0
	 */
	public static function serve() {
		$me = new Agm_Icons_Shared();
		$me->_add_hooks();
	}

	/**
	 * Setup all the WordPress hooks to get the overlay working.
	 *
	 * @since 1.0
	 */
	private function _add_hooks() {
		add_filter(
			'agm_google_maps-custom_icons',
			array( $this, 'load_icons' )
		);
	}

	/**
	 * Modifies the icons list: Add our custom icons!
	 *
	 * @since  1.0
	 */
	public function load_icons( $icons ) {
		$custom = $this->_get_custom_icons();
		return array_merge( $icons, $custom );
	}

	/**
	 * Returns an array of custom icons
	 *
	 * @since 1.0
	 */
	private function _get_custom_icons() {
		static $Icons = null;

		if ( null === $Icons ) {
			$options = get_option( 'agm_google_maps' );
			$iconlist = @$options['custom_icons'];
			$Icons = json_decode( $iconlist );

			if ( ! is_array( $Icons ) ) {
				$Icons = array();
			}
		}

		return $Icons;
	}

}


if ( is_admin() ) {
	Agm_Icons_AdminPages::serve();
}
Agm_Icons_Shared::serve();