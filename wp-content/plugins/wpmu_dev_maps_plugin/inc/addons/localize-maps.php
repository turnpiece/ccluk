<?php
/*
Plugin Name: Forced maps localization
Description: By default, your maps will be shown according to preferred browser locale for your visitors. Enabling this add-on will show your maps in the language you select in plugin settings.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_Locale_AdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Locale_AdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);
	}

	public function register_settings() {
		add_settings_section(
			'agm_google_maps_forced_l10n',
			__( 'Localization', AGM_LANG ),
			'__return_false',
			'agm_google_maps_options_page'
		);
		add_settings_field(
			'agm_google_maps_l10n_languages',
			__( 'Languages', AGM_LANG ),
			array( $this, 'create_languages_box' ),
			'agm_google_maps_options_page',
			'agm_google_maps_forced_l10n'
		);
	}

	public function create_languages_box() {
		$language = $this->_get_options( 'language' );
		?>
		<label for="agm-locale-select_language">
			<?php _e( 'Select your language', AGM_LANG ); ?>:
		</label>
		<select id="agm-locale-select_language" name="agm_google_maps[locale-language]">
			<option value=""><?php _e( 'Browser detect (default)', AGM_LANG ); ?></option>
			<?php foreach ( Agm_Locale_PublicPages::get_supported_languages() as $key => $lang ) : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $language ); ?>>
				<?php echo esc_html( $lang ); ?>
			</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	private function _get_options( $key = 'language' ) {
		$opts = apply_filters(
			'agm_google_maps-options-locale',
			get_option( 'agm_google_maps' )
		);
		return @$opts['locale-' . $key];
	}
}

class Agm_Locale_PublicPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_Locale_PublicPages();
		$me->_add_hooks();
	}

	public static function get_supported_languages() {
		return array(
			'ar' => __( 'Arabic', AGM_LANG ),
			'eu' => __( 'Basque', AGM_LANG ),
			'bg' => __( 'Bulgarian', AGM_LANG ),
			'bn' => __( 'Bengali', AGM_LANG ),
			'ca' => __( 'Catalan', AGM_LANG ),
			'cs' => __( 'Czech', AGM_LANG ),
			'da' => __( 'Danish', AGM_LANG ),
			'de' => __( 'German', AGM_LANG ),
			'el' => __( 'Greek', AGM_LANG ),
			'en' => __( 'English', AGM_LANG ),
			'en-AU' => __( 'English (Australian)', AGM_LANG ),
			'en-GB' => __( 'English (Great Britain)', AGM_LANG ),
			'es' => __( 'Spanish', AGM_LANG ),
			'eu' => __( 'Basque', AGM_LANG ),
			'fa' => __( 'Farsi', AGM_LANG ),
			'fi' => __( 'Finnish', AGM_LANG ),
			'fil' => __( 'Filipino', AGM_LANG ),
			'fr' => __( 'French', AGM_LANG ),
			'gl' => __( 'Galician', AGM_LANG ),
			'gu' => __( 'Gujarati', AGM_LANG ),
			'hi' => __( 'Hindi', AGM_LANG ),
			'hr' => __( 'Croatian', AGM_LANG ),
			'hu' => __( 'Hungarian', AGM_LANG ),
			'id' => __( 'Indonesian', AGM_LANG ),
			'it' => __( 'Italian', AGM_LANG ),
			'iw' => __( 'Hebrew', AGM_LANG ),
			'ja' => __( 'Japanese', AGM_LANG ),
			'kn' => __( 'Kannada', AGM_LANG ),
			'ko' => __( 'Korean', AGM_LANG ),
			'lt' => __( 'Lithuanian', AGM_LANG ),
			'lv' => __( 'Latvian', AGM_LANG ),
			'ml' => __( 'Malayalam', AGM_LANG ),
			'mr' => __( 'Marathi', AGM_LANG ),
			'nl' => __( 'Dutch', AGM_LANG ),
			'no' => __( 'Norwegian', AGM_LANG ),
			'pl' => __( 'Polish', AGM_LANG ),
			'pt' => __( 'Portuguese', AGM_LANG ),
			'pt-BR' => __( 'Portuguese (Brazil)', AGM_LANG ),
			'pt-PT' => __( 'Portuguese (Portugal)', AGM_LANG ),
			'ro' => __( 'Romanian', AGM_LANG ),
			'ru' => __( 'Russian', AGM_LANG ),
			'sk' => __( 'Slovak', AGM_LANG ),
			'sl' => __( 'Slovenian', AGM_LANG ),
			'sr' => __( 'Serbian', AGM_LANG ),
			'sv' => __( 'Swedish', AGM_LANG ),
			'tl' => __( 'Tagalog', AGM_LANG ),
			'ta' => __( 'Tamil', AGM_LANG ),
			'te' => __( 'Telugu', AGM_LANG ),
			'th' => __( 'Thai', AGM_LANG ),
			'tr' => __( 'Turkish', AGM_LANG ),
			'uk' => __( 'Ukrainian', AGM_LANG ),
			'vi' => __( 'Vietnamese', AGM_LANG ),
			'zh-CN' => __( 'Chinese (simplified)', AGM_LANG ),
			'zh-TW' => __( 'Chinese (traditional)', AGM_LANG ),
		);
	}

	private function _get_options( $key = 'language' ) {
		$opts = apply_filters(
			'agm_google_maps-options-locale',
			get_option( 'agm_google_maps' )
		);
		return @$opts['locale-' . $key];
	}

	private function _add_hooks() {
		add_action( 'agm_google_maps-add_javascript_data', array($this, 'add_language_data' ) );
	}

	public function add_language_data() {
		$language = $this->_get_options( 'language' );
		$all_languages = array_keys( self::get_supported_languages() );
		if ( ! in_array( $language, $all_languages ) ) { return false; }
		printf(
			'<script type="text/javascript">if (typeof(_agmLanguage) == "undefined") _agmLanguage="%s";</script>',
			$language
		);
	}
}

if ( is_admin() ) {
	Agm_Locale_AdminPages::serve();
} else {
	Agm_Locale_PublicPages::serve();
}