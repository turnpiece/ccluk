<?php
/*
Plugin Name: KML Uploader
Description: Allows you to upload your own KML files.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0.1
Requires:    KML Overlay
Author:      Ve Bailovity (Incsub )
*/

class Agm_Kml_UploaderAdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Kml_UploaderAdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// UI
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);
		add_action(
			'wp_ajax_agm_list_kml_uploads',
			array( $this, 'json_list_kml_uploads' )
		);

		// Uploads
		add_filter(
			'agm_google_maps-settings_form_options',
			array( $this, 'settings_form_options' )
		);
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/kml-uploads.min.js' );
	}

	public function json_list_kml_uploads() {
		$files = $this->_list_kml_files();
		$files = $files ? $files : array();

		$result = array();
		foreach ( $files as $key => $val ) {
			$file = basename( $val );
			$url = esc_url( $this->_get_kml_url( $file ) );
			if ( ! $file || ! $url ) {
				continue;
			}
			$result[$file] = $url;
		}
		header( 'Content-type: application/json' );
		echo json_encode( $result );
		exit();
	}

	public function settings_form_options( $opts ) {
		return $opts . ' enctype="multipart/form-data"';
	}

	public function register_settings() {
		if (isset( $_FILES['kml'] ) ) $this->_upload_kml_file();

		add_settings_section(
			'agm_google_maps_kml',
			__( 'KML files', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_list_kmls',
			__( 'Existing KML files', AGM_LANG ),
			array( $this, 'create_kml_list_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_kml'
		);
		add_settings_field(
			'agm_google_maps_upload_kml',
			__( 'Upload a KML file', AGM_LANG ),
			array( $this, 'create_kml_uploads_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_kml'
		);
	}

	public function create_kml_list_box() {
		$files = $this->_list_kml_files();
		if ( ! $files ) {
			_e( '<em>No KML files.</em>', AGM_LANG );
			return false;
		}
		echo '<ul>';
		foreach ( $files as $file ) {
			$file = basename( $file );
			$url = esc_url( $this->_get_kml_url( $file ) );
			$file = esc_html( $file );
			if ( ! $file || ! $url ) {
				continue;
			}
			echo '<li>';
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $file ) . '</a>';
			echo '</li>';
		}
		echo '</ul>';
	}

	public function create_kml_uploads_box() {
		echo '<input type="file" name="kml" />';
		echo '<div><small>' . __( 'Only files with .kml and .kmz extension are allowed.', AGM_LANG ) . '</small></div>';
		echo '<p><input type="submit" value="' . __( 'Upload', AGM_LANG ) . '" /></p>';
	}

	private function _list_kml_files() {
		$dir = $this->_get_uploads_dir();
		if ( ! $dir ) {
			return false;
		}

		return glob( "{$dir}/*.{kml,kmz}", GLOB_BRACE );
	}

	private function _upload_kml_file() {
		$name = preg_replace( '~[^-_.a-z0-9]~i', '-', $_FILES['kml']['name'] );
		$name = strtolower( basename( $name ) );
		$ext = pathinfo( $name, PATHINFO_EXTENSION );
		if ( ! in_array( $ext, array( 'kml', 'kmz' ) ) ) {
			return false;
		}

		// Get upload dir info.
		$dir = $this->_get_uploads_dir();
		if ( ! $dir ) {
			return false;
		}

		if ( ! move_uploaded_file( $_FILES['kml']['tmp_name'], "{$dir}/{$name}" ) ) {
			return false;
		}

		return true;
	}

	private function _get_uploads_dir() {
		$uploads = wp_upload_dir();
		$path = $uploads['basedir'] . '/agm-kmls';
		if ( ! is_dir( $path ) ) {
			wp_mkdir_p( $path );
		}
		if ( ! is_dir( $path ) ) {
			return false;
		}

		return $path;
	}

	private function _get_kml_url( $file ) {
		if ( ! $file ) {
			return false;
		}

		$file = basename( $file );
		$uploads = wp_upload_dir();
		$path = $uploads['basedir'] . '/agm-kmls';
		$url = $uploads['baseurl'] . '/agm-kmls';

		if ( ! is_dir( $path ) ) {
			return false;
		}
		if ( ! file_exists( "{$path}/{$file}" ) ) {
			return false;
		}

		return "{$url}/{$file}";
	}
}

if ( is_admin() && class_exists( 'Agm_Kml_AdminPages' ) ) {
	Agm_Kml_UploaderAdminPages::serve();
}