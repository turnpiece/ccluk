<?php
/**
 * Shipper codec: domain replacer
 *
 * @package shipper
 */

/**
 * Domain replacer class
 */
class Shipper_Helper_Codec_Domain extends Shipper_Helper_Codec {

	/**
	 * Options instance holder
	 *
	 * @var Shipper_Model_Stored_Options instance.
	 */
	private $options;

	/**
	 * Shipper_Helper_Codec_Domain constructor.
	 *
	 * @since 1.2.7
	 */
	public function __construct() {
		$this->options = new Shipper_Model_Stored_Options();
	}

	/**
	 * Gets a list of replacement pairs
	 *
	 * A single replacement pair list, with current domain as key and
	 * replacement macro as value.
	 *
	 * @return array
	 */
	public function get_replacements_list() {
		$destinations = new Shipper_Model_Stored_Destinations();
		$current      = $destinations->get_current();
		$rpl          = array();

		/**
		 * Allows for domain-with-scheme macro opting out
		 *
		 * @param bool $include_scheme_macro Whether to include the scheme macro.
		 *
		 * @return bool
		 */
		if ( apply_filters( 'shipper_codec_domain_include_scheme', true ) ) {
			// This has to come first in the list.
			// That is so we expand URLs with scheme first.
			// Should fix the 840279109797148 issue.
			$rpl[ get_bloginfo( 'wpurl' ) ] = '{{SHIPPER_WPURL_WITH_SCHEME}}';
			$rpl[ get_bloginfo( 'url' ) ]   = '{{SHIPPER_URL_WITH_SCHEME}}';
		}

		/**
		 * If the source site is a sub-domain multisite not sub-directory (sub-site.domain.com - not domain.com/sub-site)
		 * Then we'll update the domain and home url to the actual sub site.
		 *
		 * @see https://incsub.atlassian.net/browse/SHI-157
		 *
		 * @since 1.2.0
		 */
		$model = new Shipper_Model_Stored_Migration();
		$meta  = new Shipper_Model_Stored_MigrationMeta();

		if ( $model->is_package_migration() ) {
			$meta = new Shipper_Model_Stored_PackageMeta();
		}

		if ( function_exists( 'get_site' ) && $meta->is_extract_mode() && $meta->get_site_id() > 1 ) {
			$site = get_site( $meta->get_site_id() );

			if ( ! empty( $site->domain ) && strlen( $site->path ) === 1 ) {
				// we're are sure that it's a sub-site on sub-domain.
				$current['domain']   = $site->domain;
				$current['home_url'] = $site->domain;
			}
		}

		$rpl[ $current['domain'] ] = '{{SHIPPER_DOMAIN}}';

		if ( $current['domain'] !== $current['home_url'] ) {
			$rpl[ $current['home_url'] ] = '{{SHIPPER_HOME_URL}}';
		}

		$has_domain_in_admin = preg_match(
			'/' . preg_quote( $current['domain'], '/' ) . '/',
			$current['admin_url']
		);
		$has_home_in_admin   = preg_match(
			'/' . preg_quote( $current['home_url'], '/' ) . '/',
			$current['admin_url']
		);
		if ( ! $has_domain_in_admin && ! $has_home_in_admin ) {
			$rpl[ $current['admin_url'] ] = '{{SHIPPER_ADMIN_URL}}';
		}

		return $rpl;
	}

	/**
	 * Gets a regex expression matcher string
	 *
	 * Will match only the domain expression.
	 *
	 * @param string $string Original domain.
	 * @param string $value Macro on import, empty on export.
	 *
	 * @return string
	 */
	public function get_matcher( $string, $value = '' ) {
		if ( ! empty( $value ) ) {
			// While decoding sql strings.
			return preg_quote( $value, '/' );
		}

		// While encoding sql strings.
		if ( $this->options->get( Shipper_Model_Stored_Options::KEY_SKIPEMAILS ) ) {
			/**
			 * If self::get_replacement_list returns `example@domain.com`
			 * Lets skip line. So `example@domain.com` wont replaced with `example@{{SHIPPER_DOMAIN}}`
			 *
			 * Regex uses: negative lookbehind
			 *
			 * @since 1.2.7
			 */
			return '\b(?<!@)' . preg_quote( $string, '/' ) . '(\b|$)';
		}

		return '\b' . preg_quote( $string, '/' ) . '(\b|$)';
	}

	/**
	 * Gets expansion replacement string
	 *
	 * @param string $name Original domain.
	 * @param string $value Process-dependent domain representation.
	 *                      (macro on export, original on import).
	 *
	 * @return string
	 */
	public function get_replacement( $name, $value ) {
		return $value;
	}
}