<?php
/**
 * The Database updater for Social Warfare 3.0.0.
 *
 * This willl either migrate previous options to social_warfare_settings,
 * or create the new default settings.
 *
 * @since  3.0.0 | 08 MAY 2018 | Created
 * @since  3.0.6 | 14 MAY 2018 | Added local $last_migrated property.
 *
 */
class SWP_Database_Migration {


	/**
	 * This property represents the version during which we last made changes
	 * and therefore want the database migrator to have run up to this version.
	 *
	 * @var string
	 */
	public $last_migrated = '3.0.5';

    /**
     * Checks to see if we are on the most up-to-date database schema.
     *
     * If not, runs the migration and updators.
     *
     * @since 3.0.0 | 01 MAY 2018 | Created the function
     *
     */
    public function __construct() {
        if ( !$this->database_is_migrated() ) {
            $this->migrate();
        }

        if ( !$this->has_3_0_0_settings() ) {
            $this->initialize_database();
        }

        if ( !$this->post_meta_is_migrated() ) {
            $this->update_post_meta();
            $this->update_hidden_post_meta();
        }

		if ( true === _swp_is_debug('migrate_db') ) {
			$this->migrate();
		}

        if ( true === _swp_is_debug('initialize_db') ) {
            $this->initialize_db();
        }

		if ( true === _swp_is_debug('migrate_post_meta') ) {
			$this->update_post_meta();
			$this->update_hidden_post_meta();
		}

        if ( true === _swp_is_Debug('get_last_migrated') ) {
            $this->get_last_migrated( true );
        }

        if ( true === _swp_is_Debug('update_last_migrated') ) {
            $this->update_last_migrated();
        }
    }

    /**
     * Checks to see if Social Warfare < 3.0.0 options exist.
     *
     * If these options exist in the databse, we need to move them
     * from "socialWarfareOptions" to "social_warfare_settings",
     * then
     *
     * @since 3.0.0 | 01 MAY 2018 | Created the function
     * @return bool True if migrated, else false.
     */
    public function database_is_migrated() {
        $option = get_option( 'social_warfare_settings' , false);

        return false !== $option;
    }


    /**
    * Checks to see if we have 3.0.0 settings installed or not.
    *
    * @since 3.0.0 | 01 MAY 2018 | Created the function
    * @return bool True if the 3.0.0 array exists, otherwise false.
    */
    protected function has_3_0_0_settings() {
        //* Check to see if the 3.0.0 settings exist.
        $settings = get_option( 'social_warfare_settings', false );

        return is_array( $settings );
    }


    /**
    * Tries to get an option that uses the old post_meta keynames.
    *
    * @since 3.0.0 | 01 MAY 2018 | Created the function
    * @return bool True if the old option still exists; false otherwise.
    */
    public function post_meta_is_migrated() {
         //* Fetch posts with 2.3.5 metadata.
        $old_metadata = get_posts( ['meta_key' => 'swp_postLocation', 'numberposts' => 1] );

		if( $this->last_migrated !== $this->get_last_migrated() ) {
			return false;
		}

        return count( $old_metadata ) === 0;
    }

    public function update_hidden_post_meta() {
        global $wpdb;

        set_time_limit(300);

        $hidden_map = [
            '_googlePlus_shares'    => '_google_plus_shares',
            '_linkedIn_shares'      => '_linkedin_shares',
            'bitly_link_googlePlus' => '_bitly_link_google_plus',
            'bitly_link_linkedIn'   => '_bitly_link_linked_in'
        ];

        $query = "
            UPDATE " . $wpdb->prefix . "postmeta
            SET meta_key = %s
            WHERE meta_key = %s
        ";

        foreach ( $hidden_map as $old_key => $new_key ) {
            //* Make replacements for the first kind of prefix.
            $q = $wpdb->prepare( $query, $new_key, $old_key );
            $wpdb->query( $q );
        }
    }


    /**
    * Replaces 2.3.5 camelCased keys with 3.0.0 standardized snake_cased keys.
    *
    * @since 3.0.0 | 01 MAY 2018 | Created the function
    * @since 3.0.6 | 14 MAY 2018 | Added time limit to prevent very large datasets from timing out.
    */
    public function update_post_meta() {
        global $wpdb;

        set_time_limit(300);

        //* Notice there is no prefix on any of the indices.
        //* Old code has prefixed these with either "nc_" or "swp_".
        //* For simplicity's sake, we'll just check each for both.
        $metadata_map =  [
            'ogImage'                        => 'swp_og_image',
            'ogTitle'                        => 'swp_og_title',
            'pinterestImage'                 => 'swp_pinterest_image',
            'customTweet'                    => 'swp_custom_tweet',
            'postLocation'                   => 'swp_post_location',
            'floatLocation'                  => 'swp_float_location',
            'pinterestDescription'           => 'swp_pinterest_description',
            'twitterID'                      => 'swp_twitter_id',
            'ogDescription'                  => 'swp_og_description',
            'cache_timestamp'                => 'swp_cache_timestamp',
            'pin_browser_extension'          => 'swp_pin_browser_extension',
            'pin_browser_extension_location' => 'swp_pin_browser_extension_location',
            'pin_browser_extension_url'      => 'swp_pin_browser_extension_url',
            'totes'                          => 'total_shares'
        ];

        $prefix1 = "nc_";
        $prefix2 = "swp_";

        $query = "
            UPDATE " . $wpdb->prefix . "postmeta
            SET meta_key = %s
            WHERE meta_key = %s
        ";

        foreach ( $metadata_map as $old_key => $new_key ) {
            //* Make replacements for the first kind of prefix.
            $q1 = $wpdb->prepare( $query, $new_key, $prefix1 . $old_key );
            $results = $wpdb->query( $q1 );

            //* And make replacements for the second kind of prefix.
            $q2 = $wpdb->prepare( $query, $new_key, $prefix2 . $old_key );
            $results = $wpdb->query( $q2 );
        }
    }


    /**
    * Seeds the database with Social Warfare 3.0.0 default values.
    *
    * @since 3.0.0 | 01 MAY 2018 | Created the function
    * @return void
    */
    public function initialize_database() {
        $defaults = [
            'location_archive_categories'       => 'below',
            'location_home'                     => 'none',
            'location_post'                     => 'below',
            'location_page'                     => 'below',
            'float_location_post'               => 'on',
            'float_location_page'               => 'off',
            'total_shares'                      => true,
            'network_shares'                    => true,
            'twitter_id'                        => false,
            'swp_twitter_card'                  => true,
            'button_shape'                      => 'flatFresh',
            'default_colors'                    => 'full_color',
            'single_colors'                     => 'full_color',
            'hover_colors'                      => 'full_color',
            'float_default_colors'              => 'full_color',
            'float_single_colors'               => 'full_color',
            'float_hover_colors'                => 'fullColor',
            'float_style_source'                => true,
            'float_size'                        => 1,
            'float_alignment'                   => 'center',
            'button_size'                       => 1,
            'button_alignment'                  => 'fullWidth',
            'transition'                        => 'slide',
            'float_screen_width'                => 1100,
            'ctt_theme'                         => 'style1',
            'ctt_css'                           => "",
            'twitter_shares'                    => false,
            'floating_panel'                    => true,
            'float_location'                    => 'bottom',
            'float_background_color'            => '#ffffff',
            'float_button_shape'                => 'default',
            'float_vertical'                    => 'center',
            'float_button_count'                => 5,
            'custom_color'                      => '#000000',
            'custom_color_outlines'             => '#000000',
            'float_custom_color'                => '#000000',
            'float_custom_color_outlines'       => '#000000',
            'recover_shares'                    => false,
            'recovery_format'                   => 'unchanged',
            'recovery_protocol'                 => 'unchanged',
            'recovery_prefix'                   => 'unchanged',
            'decimals'                          => 0,
            'decimal_separator'                 => 'period',
            'totals_alignment'                  => 'total_sharesalt',
            'google_analytics'                  => false,
            'bitly_authentication'              => false,
            'minimum_shares'                    => 0,
            'full_content'                      => false,
            'frame_buster'                      => false,
            'analytics_medium'                  => 'social',
            'analytics_campaign'                => 'SocialWarfare',
            'swp_click_tracking'                => false,
            'order_of_icons_method'             => 'manual',
            'og_post'                           => 'article',
            'og_page'                           => 'article',
            'pinterest_image_location'          => 'hidden',
            'pin_browser_extension'             => false,
            'pinterest_fallback'                => 'all',
            'pinit_toggle'                      => false,
            'pinit_location_horizontal'         => 'center',
            'pinit_location_vertical'           => 'top',
            'pinit_min_width'                   => '200',
            'pinit_min_height'                  => '200',
            'pinit_image_source'                => 'image',
            'pinit_image_description'           => 'alt_text',
            'utm_on_pins'                       => false,
            'pin_browser_extension'             => false,
            'pin_browser_extension_location'    => 'hidden',
            'pinterest_fallback'                => 'all',
            'float_mobile'                      => 'bottom',
            'force_new_shares'                  => false,
            'cache_method'                      => 'advanced',
            'order_of_icons' =>  [
                'twitter'    => 'Twitter',
                'linkedIn'   => 'LinkedIn',
                'pinterest'  => 'Pinterest',
                'facebook'   => 'Facebook',
                'google_plus' => 'Google Plus',
            ],
        ];

        update_option( 'social_warfare_settings', $defaults );
    }


    /**
     * Map prevous key/value pairs to new keys.
     *
     * This also deletes the previous keys once the migration is done.
     * @since 3.0.0 | 01 MAY 2018 | Created the function
     * @return void
     */
    private function migrate() {
        $options = get_option( 'socialWarfareOptions', [] );

        if ( $options === [] ) :
            //* The old options do not exist.
            return;
        endif;

        $map = [
            //* Options names
            'locationSite'                      => 'location_archive_categories',
            'locationHome'                      => 'location_home',
            'totesEach'                         => 'network_shares',
            'totes'                             => 'total_shares',
            'minTotes'                          => 'minimum_shares',
            'visualTheme'                       => 'button_shape',
            'buttonSize'                        => 'button_size',
            'dColorSet'                         => 'default_colors',
            'oColorSet'                         => 'hover_colors',
            'iColorSet'                         => 'single_colors',
            'swDecimals'                        => 'decimals',
            'swp_decimal_separator'             => 'decimal_separator',
            'swTotesFormat'                     => 'totals_alignment',
            'float'                             => 'floating_panel',
            'float_background_color'            => 'float_location',
            'swp_float_scr_sz'                  => 'float_screen_width',
            'sideReveal'                        => 'transition',
            'floatStyle'                        => 'float_button_shape',
            'floatStyleSource'                  => 'float_style_source',
            'sideDColorSet'                     => 'float_default_colors',
            'sideOColorSet'                     => 'float_hover_colors',
            'sideIColorSet'                     => 'float_single_colors',
            'swp_twitter_card'                  => 'twitter_cards',
            'twitterID'                         => 'twitter_id',
            'pinterestID'                       => 'pinterest_id',
            'facebookPublisherUrl'              => 'facebook_publisher_url',
            'facebookAppID'                     => 'facebook_app_id',
            'sniplyBuster'                      => 'frame_buster',
            'linkShortening'                    => 'bitly_authentication',
            'cacheMethod'                       => 'cache_method',
            'googleAnalytics'                   => 'google_analytics',
            'analyticsMedium'                   => 'analytics_medium',
            'analyticsCampaign'                 => 'analytics_campaign',
            'advanced_pinterest_image'          => 'pin_browser_extension',
            'advanced_pinterest_image_location' => 'pinterest_image_location',
            'pin_browser_extension_location'    => 'pin_browser_extension_location',
            'advanced_pinterest_fallback'       => 'pinterest_fallback',
            'recovery_custom_format'            => 'recovery_permalink',
            'cttTheme'                          => 'ctt_theme',
            'cttCSS'                            => 'ctt_css',
            'sideCustomColor'                   => 'single_custom_color',
            'floatBgColor'                      => 'float_background_color',
            'orderOfIconsSelect'                => 'order_of_icons_method',
			'newOrderOfIcons'                   => 'order_of_icons',
        ];

        $value_map = [
            'flatFresh'     => 'flat_fresh',
            'threeDee'      => 'three_dee',
            'fullColor'     => 'full_color',
            'lightGray'     => 'light_gray',
            'mediumGray'    => 'medium_gray',
            'darkGray'      => 'dark_gray',
            'lgOutlines'    => 'light_gray_outlines',
            'mdOutlines'    => 'medium_gray_outlines',
            'dgOutlines'    => 'dark_gray_outlines',
            'colorOutlines' => 'color_outlines',
            'customColor'   => 'custom_color',
            'ccOutlines'    => 'custom_color_outlines',
            'totesAlt'      => 'totals_right',
            'totesAltLeft'  => 'totals_left',
            'buttonFloat'   => 'button_alignment',
            'post'          => 'location_post',
            'page'          => 'location_page',
            'float_vertical'=> 'float_alignment',
            'fullWidth' => 'full_width',
            'floatLeftMobile'   => 'float_mobile',
        ];



        //* We don't actually do anything with these. I left them here just as a note.
        //* They are deleted during the call to delete_option( 'socialWarfareOptions' ).
        $removals = [
            'dashboardShares',
            'rawNumbers',
            'notShowing',
            'visualEditorBug',
            'loopFix',
            'locationrevision',
            'locationattachment',
        ];


        $migrations = [];

        foreach( $options as $old => $value ) {



            //* The order of icons used to be stored in an array at 'active'.
            if ( is_array( $value) && array_key_exists( 'active', $value) ) :
                $new_value = $value;
            //* Filter out the booleans and integers.
            elseif ( is_string( $value ) ):
                $new_value = array_key_exists($value, $value_map) ? $value_map[$value] : $value;
            else :
                $new_value = $value;
            endif;

            //* Specific case: customColor mapping.
            if ( $old === 'customColor' ) :
                $migrations['custom_color'] = $new_value;
                $migrations['custom_color_outlines'] = $new_value;

                if ( $options['floatStyleSource'] == false ) :
                    $migrations['float_custom_color'] = $new_value;
                    $migrations['float_custom_color_outlines'] = $new_value;
                endif;

                continue;
            endif;

            if ( array_key_exists( $old, $map) ) :
                //* We specified an update to the key.
                $new = $map[$old];
                $migrations[$new] = $new_value;
            else :
                //* The previous key was fine, keep it.
                $migrations[$old] = $new_value;
            endif;

        }

        update_option( 'social_warfare_settings', $migrations );
        //* Play it safe for now.
        //* Leave socialWarfareOptions in the database.
        // delete_option( 'socialWarfareOptions' );
    }

    public function get_last_migrated( $echo = false ) {
        $options = get_option( 'social_warfare_settings' );

        if ( array_key_exists( 'last_migrated', $options ) ) :
            if ( true === $echo ) :
                var_dump( $options['last_migrated'] );
            endif;

            return $options['last_migrated'];
        endif;

        if ( true === $echo ) :
            echo "No previous migration version has been set.";
        endif;

        return false;

    }

    public function update_last_migrated() {
        $options = get_option( 'social_warfare_settings' );
        $options['last_migrated'] = $this->last_migrated;

        update_option( 'social_warfare_settings', $options );
    }
}
