<?php
/**
 * Plugin Name: User check-ins
 * Description: Allows your users to check-in their current location. Locations of the users can be displayed on a map using a map-shortcode.
 * Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Example:     [agm_add_checkin], [agm_show_checkins id="1" last_hour="48"]
 * Version:     1.0.2
 * Author:      Philipp Stracker (Incsub)
 */




// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
//                               SHARED FUNCTIONS
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------




/**
 * This class contains functions that are used on Admin and on Frontpage.
 */
class Agm_UCI_Shared {
	/**
	 * Instance of an AgmMapModel object.
	 *
	 * @since  1.0.0
	 * @var    AgmMapModel
	 */
	protected $_model = null;

	/**
	 * Define default options that are used when the addon is activated for the
	 * first time.
	 *
	 * @since 1.0.1
	 * @var array
	 */
	private $_default_options = array(
		'link_pages' => false, // Not yet used
		'link_comments' => false, // Not yet used
		'max_checkins' => '10',
		'max_guest_checkins' => '50',
		'merge_distance' => '3',
		'edit_details' => false,
		'allow_guest_checkin' => true,
		'show_empty_map' => true,
		'automatic_checkin' => 'manual',
	);

	private $_default_user_options = array(
		'checkin' => 'ask',
		'share' => 'member',
	);

	/**
	 * Constructor. Adds the shared filter hooks.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->_model = new AgmMapModel();

		/*
		 * Filter to validate checkin data after.
		 * Callback takes 2 params: checkin_data, user_id, default_values.
		 */
		add_filter(
			'agm_google_maps_checkins-prepare-checkin',
			array( $this, 'validate_checkin' ), 10, 3
		);

		/*
		 * Filter to validate the checkin-collection before writing it to the
		 * database or before returning it for usage by the addon.
		 * Callback takes 1 param: checkin_collection.
		 */
		add_filter(
			'agm_google_maps_checkins-sanitzie-checkins',
			array( $this, 'sanitize_checkins' ), 10, 2
		);

		/*
		 * This filter is only called when there are more checkins than allowed.
		 * The handler should take care to remove some checkins.
		 * Callback takes 2 params: checkin_collection, max_count, current_id
		 */
		add_filter(
			'agm_google_maps_checkins-limit-checkin-count',
			array( $this, 'limit_checkin_count' ), 10, 3
		);
	}

	/**
	 * Appends a single location to the user metadata.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guest checkins.
	 * @param  object $location Location details.
	 * @return int|false On success the new/updated checkin-ID is returned.
	 */
	protected function _add_checkin( $user_id, $location ) {
		$user_id = absint( $user_id );
		$is_guest = ( 0 == $user_id );

		// Get currently saved checkin details.
		$data = $this->_get_checkin_data( $user_id );

		// Every checkin gets a unique ID so we can link to the checkin easily.
		$cid = absint( $data->last_id ) + 1;

		// Since we create a new checkin let's first set the create/modify stamps.
		$location['created'] = time();
		$location['modified'] = 0;

		/*
		 * Test if the new location is within the merge-distance of an existing
		 * location. If this is the case then only increase the counter of the
		 * existing location but add no new data.
		 */
		$margin = $this->_get_option( 'merge_distance', 'int' );
		foreach ( $data->checkins as $id => $checkin ) {
			$distance = $this->get_distance(
				$location['lat'], $location['lng'],
				$checkin['lat'], $checkin['lng']
			);

			if ( $distance < $margin ) {
				/*
				 * Location already exists:
				 * Increase the counter and set modified date.
				 */
				$location = $checkin;
				$cid = $id;
				$location['count'] = absint( $location['count'] ) + 1;
				$location['modified'] = time();
				$data->checkins[ $id ] = $location;

				break;
			}
		}

		/*
		 * If the current checkin-ID is higher than the last-ID then
		 * update the last-ID counter.
		 * When the current check-ID is not higher, it means that an existing
		 * checkin is updated
		 */
		if ( $cid > absint( $data->last_id ) ) {
			$data->last_id = $cid;
		}

		// This will either add a new item to the collection or replace one.
		$data->checkins[ $cid ] = $location;

		/*
		 * Apply the max-checkin limitation:
		 * When there are too many checkins then remove the oldest one.
		 * Age is determined by the modify/create stamps, and not by the ID.
		 */
		if ( $is_guest ) {
			$limit = $this->_get_option( 'max_guest_checkins', 'int' );
		} else {
			$limit = $this->_get_option( 'max_checkins', 'int' );
		}

		if ( count( $data->checkins ) > $limit ) {
			$data->checkins = apply_filters(
				'agm_google_maps_checkins-limit-checkin-count',
				$data->checkins, $limit, $cid
			);
		}

		if ( $this->_replace_checkin_data( $user_id, $data ) ) {
			return $cid;
		} else {
			return false;
		}
	}

	/**
	 * Replaces a single location in the user metadata.
	 * This is called when the user edits a location.
	 * Main difference to _add_checkin: Locations are not merged or appended.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guest checkins.
	 * @param  int $cid The checkin-id
	 * @param  array $edit_data An array with location details.
	 *                Only fields that are specified in the array are updated.
	 * @return bool
	 */
	protected function _update_checkin( $user_id, $cid, $edit_data ) {
		$user_id = absint( $user_id );

		$data = $this->_get_checkin_data( $user_id );
		if ( isset( $data->checkins[ $cid ] ) ) {
			$location = $data->checkins[ $cid ];

			/*
			 * Update the fields from $edit_data in the $location array.
			 * @see $this->validate_checkin
			 */
			$location = apply_filters(
				'agm_google_maps_checkins-prepare-checkin',
				$edit_data, $user_id, $location
			);

			$data->checkins[ $cid ] = $location;
			return $this->_replace_checkin_data( $user_id, $data );
		}
		return false;
	}

	/**
	 * Removes a single location from the user metadata.
	 * This is called when the user edits a location.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guest checkins.
	 * @param  int $cid The checkin-id
	 * @return bool
	 */
	protected function _remove_checkin( $user_id, $cid ) {
		$user_id = absint( $user_id );

		$data = $this->_get_checkin_data( $user_id );
		if ( isset( $data->checkins[ $cid ] ) ) {
			unset( $data->checkins[ $cid ] );
			return $this->_replace_checkin_data( $user_id, $data );
		}
		return false;
	}

	/**
	 * Returns an array with checkins of the given user.
	 *
	 * lowest level GETTER function.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guest checkins.
	 * @return array List of all current checkins.
	 */
	protected function _get_checkin_data( $user_id ) {
		$user_id = absint( $user_id );
		$is_guest = (0 == $user_id);
		$cache_key = 'agm-checkin-' . $user_id;

		// Get the value from cache if it already exists.
		$data = wp_cache_get( $cache_key );

		// When there was no valid checkin object in cache then get it from DB.
		if ( ! is_object( $data ) ) {
			if ( $is_guest ) {
				// Guest-checkins are stored in the options table.
				$data = get_option( 'agm_google_maps_guest_checkins' );
			} else {
				// User-checkins are stored in the user-meta table.
				$data = get_user_meta( $user_id, 'agm_google_maps_checkins', true );
			}

			// Sanitize the checkin collection.
			$data = apply_filters( 'agm_google_maps_checkins-sanitzie-checkins', $data, $user_id );

			// Allow other plugins or addons to play with us.
			$data = apply_filters( 'agm_google_maps_checkins-uci-get-checkins', $data, $user_id );

			// Update the value in WP cache to speed up the next Get request.
			wp_cache_set( $cache_key, $data );
		}

		return $data;
	}

	/**
	 * Returns a single checkin object of the specified user.
	 *
	 * @since  1.0.0
	 * @param  int $user_id User ID.
	 * @param  int $cid Checkin ID.
	 * @return array|false The checkin details.
	 */
	protected function _get_checkin_by_id( $user_id, $cid ) {
		$checkin = false;
		$user_id = absint( $user_id );
		$cid = absint( $cid );

		if ( $cid > 0 && $user_id > 0 ) {
			$data = $this->_get_checkin_data( $user_id );

			if ( isset( $data->checkins[ $cid ] ) ) {
				$checkin = $data->checkins[ $cid ];
			}
		}

		return $checkin;
	}

	/**
	 * Saves the given checkin list back to database, overwriting any existing
	 * checkins of the specified user.
	 *
	 * lowest level SETTER function.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guests.
	 * @param  array $data List of checkins.
	 * @return bool
	 */
	protected function _replace_checkin_data( $user_id, $data ) {
		$user_id = absint( $user_id );
		$is_guest = (0 == $user_id);
		$cache_key = 'agm-checkin-' . $user_id;

		// Sanitize the checkin collection.
		$data = apply_filters( 'agm_google_maps_checkins-sanitzie-checkins', $data, $user_id );

		// Allow other plugins or addons to play with us.
		$data = apply_filters( 'agm_google_maps_checkins-uci-save-checkins', $data, $user_id );

		// Update the value in WP cache to speed up the next Get request.
		wp_cache_set( $cache_key, $data );

		if ( $is_guest ) {
			if ( $this->_get_option( 'allow_guest_checkin' ) ) {
				// Guest-checkins are stored in the options table.
				return update_option( 'agm_google_maps_guest_checkins', $data );
			}
		} else {
			// User-checkins are stored in the user-meta table.
			return update_user_meta( $user_id, 'agm_google_maps_checkins', $data );
		}
		return false;
	}

	/**
	 * Parses the $_POST array to extract location details used for an
	 * user-checkin.
	 *
	 * @since  1.0.0
	 * @param  array $data Data collection containing the Location details.
	 * @param  int $user_id Creator of the checkin.
	 * @param  string $mode Either 'add' or 'update'. When adding an item then
	 *                lat/lng are mandatory, otherwise they are optional.
	 * @return array|false An array describing the check-in as detailled as possible.
	 *                When mandatory location details are missing the function returns false.
	 */
	protected function _get_ajax_location_data( $data, $user_id, $mode ) {
		$user_id = absint( $user_id );
		$checkin = array();
		$checkin['lat'] = @$data['lat'];
		$checkin['lng'] = @$data['lng'];

		// Take the users default "sharing" setting for new checkins.
		$checkin['share'] = @$data['share'];
		$checkin['title'] = @$data['title'];
		$checkin['description'] = @$data['description'];

		if ( 'update' == $mode ||
			(is_numeric( $checkin['lat'] ) && is_numeric( $checkin['lng'] ) )
		) {
			/*
			 * Validate the location details before they are returned.
			 * @see $this->validate_checkin
			 */
			$checkin = apply_filters(
				'agm_google_maps_checkins-prepare-checkin',
				$checkin, $user_id
			);
			return $checkin;
		} else {
			return false;
		}
	}

	/**
	 * Filter, that takes the whole checkin-collection of a user and makes sure
	 * it is properly formatted.
	 *
	 * @since  1.0.0
	 * @param  any $collection The checkin collection-object.
	 * @param  int $user_id Owner of the collection.
	 * @return object The sanitized checkin collection-object.
	 */
	public function sanitize_checkins( $collection, $user_id ) {
		if ( ! is_object( $collection ) ) {
			$collection = (object) array();
		}

		// last_id is used to generate a unique-ID for the next checkin of the user.
		if ( ! isset( $collection->last_id ) ) {
			$collection->last_id = 0;
		}

		// This is the list of all checkins.
		if ( ! isset( $collection->checkins ) ) {
			$collection->checkins = array();
		}

		return $collection;
	}

	/**
	 * Filter that is responsible to remove excess checkins.
	 * It is only called when the addon detects that the collection contains more
	 * checkins that current plugin-configuration allows.
	 *
	 * @since  1.0.0
	 * @param  array $data Checkin list (not the checkin-collection object!)
	 * @param  int $max_checkins Max number of checkins.
	 * @param  int $current_id If an item was added this will be the new checkin-ID.
	 *                We always should not dump this checkin item...
	 * @return array The cleaned checkin list.
	 */
	public function limit_checkin_count( $data, $max_checkins, $current_id ) {
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// Maybe another filter-hook already cleaned the list...
		if ( count( $data ) > $max_checkins ) {
			/*
			 * Get a array that lists [timestamp] => [checkin-id]
			 * Then we will sort the array by key (=by timestamp) and only keep
			 * the top <n> entries. This way we will dump the oldest checkins.
			 */
			$ages = array();
			foreach ( $data as $id => $checkin ) {
				if ( $id != $current_id ) {
					// Either use the modify or create stamp, whichever is more current.
					$tstamp = max(
						absint( $checkin['modified'] ),
						absint( $checkin['created'] )
					);

					while ( isset( $ages[ $tstamp ] ) ) {
						$tstamp += 1;
					}
					$ages[ $tstamp ] = $id;
				}
			}

			// Sort the checkins by age.
			krsort( $ages, SORT_NUMERIC );

			// Only keep the <max> most recently modified checkins.
			$pool = $data;
			$data = array();
			$counter = 0;

			// It is possible that $current_id is 0 or invalid.
			if ( isset( $pool[ $current_id ] ) ) {
				// Always keep the current checkin.
				$data[ $current_id ] = $pool[ $current_id ];
				$counter = 1; // Start with 1 since the $current_id item is already added.
			}

			// foreach() will always iterate the items in the sort sequence.
			foreach ( $ages as $id ) {
				$data[ $id ] = $pool[ $id ];
				$counter += 1;

				if ( $counter >= $max_checkins ) {
					break;
				}
			}
		}

		return $data;
	}

	/**
	 * Filter, that takes a single checkin-object (= array) as input and makes
	 * sure that the array contains all required fields. Also the field values
	 * are sanitized if required.
	 *
	 * @since  1.0.0
	 * @param  array $checkin The checkin-object to validate.
	 * @param  int $user_id Owner of the checkin.
	 * @param  array $defaults Optional. A checkin-array that contains default
	 *                values. This is used to update a checkin.
	 * @return array Validated checkin-object.
	 */
	public function validate_checkin( $checkin, $user_id, $defaults = array() ) {
		$user_id = absint( $user_id );
		$is_guest = (0 == $user_id);
		$is_update = (isset( $defaults['created'] ) && absint( $defaults['created'] ) > 0);
		$is_changed = false;

		// Set the default values for a new checkin.
		if ( ! isset( $defaults['created'] ) ) { $defaults['created'] = time(); }
		if ( ! isset( $defaults['modified'] ) ) { $defaults['modified'] = 0; }
		if ( ! isset( $defaults['count'] ) ) { $defaults['count'] = 1; }

		/*
		 * Now add the values from the checkin-array.
		 * When a value is not set, the default value will be used.
		 * Exception: Lat/Lng are always mandatory!
		 */
		if ( isset( $checkin['lat'] ) && 0 != $checkin['lat'] ) {
			$val = floatval( @$checkin['lat'] );
			if ( $defaults['lat'] != $val ) { $is_changed = true; }
			$defaults['lat'] = $val;
		}
		if ( isset( $checkin['lng'] ) && 0 != $checkin['lng'] ) {
			$val = floatval( @$checkin['lng'] );
			if ( $defaults['lng'] != $val ) { $is_changed = true; }
			$defaults['lng'] = $val;
		}

		if ( isset( $checkin['count'] ) ) {
			// Count can only grow, so take the highest value.
			$val = max( 1, absint( @$checkin['count'] ), absint( @$defaults['count'] ) );
			if ( $defaults['count'] != $val ) { $is_changed = true; }
			$defaults['count'] = $val;
		}

		if ( $is_guest ) {
			// Guest checkins have less details and are always public.
			$defaults['share'] = 'pub';
			$defaults['title'] = '';
			$defaults['description'] = '';
		} else {
			if ( isset( $checkin['title'] ) ) {
				$val = @$checkin['title'];
				if ( $defaults['title'] != $val ) { $is_changed = true; }
				$defaults['title'] = $val;
			}
			if ( isset( $checkin['description'] ) ) {
				$val = @$checkin['description'];
				if ( $defaults['description'] != $val ) { $is_changed = true; }
				$defaults['description'] = $val;
			}
			if ( isset( $checkin['share'] ) ) {
				$val = @$checkin['share'];
				if ( $defaults['share'] != $val ) { $is_changed = true; }
				$defaults['share'] = $val;
			}

			// Make sure the title/descriptions are valid strings (not NULL).
			if ( ! is_string( $defaults['title'] ) ) { $defaults['title'] = ''; }
			if ( ! is_string( $defaults['description'] ) ) { $defaults['description'] = ''; }

			// When no/invalid sharing is detected then use the value defined in user-settings.
			if ( ! in_array( $defaults['share'], array( 'pub', 'member', 'priv' ) ) ) {
				$defaults['share'] = $this->_get_user_option( $user_id, 'share' );
			}
		}

		/*
		 * Prepare the marker element, so we don't need to create it on every
		 * page load.
		 */
		$marker = $this->_model->_position_to_marker( $defaults['lat'], $defaults['lng'] );

		if ( ! empty( $defaults['title'] ) ) {
			$marker['title'] = $defaults['title'];
		} else {
			$defaults['title'] = @$marker['title'];
		}

		if ( ! empty( $defaults['description'] ) ) {
			$marker['body'] = $defaults['description'] . '<br /><br />';
		}

		// Add a short note on author/create time to the marker.
		$marker['body'] .= $this->_display_user_details(
			$user_id,
			max( $defaults['created'], $defaults['modified'] )
		);

		// Try to display the user avatar as icon.
		$marker['icon'] = $this->_get_user_icon( $user_id );

		// Update the modify stamp only if the item is not new and some values were updated
		if ( $is_changed && ! $is_new ) {
			$defaults['modified'] = time();
		}

		$defaults['marker'] = $marker;
		return $defaults;
	}

	/**
	 * Return a single option value for the User-Checkin addon.
	 *
	 * @since  1.0.0
	 * @param  string $key
	 * @param  string $format Optional. How to format the response.
	 *           Possible values: "raw" (default), "bool", "int".
	 * @return mixed Setting value.
	 */
	protected function _get_option( $key, $format = 'raw' ) {
		// We will cache the option values locally.
		static $Opts = null;

		if ( null === $Opts ) {
			// Only do "get_options" and "apply_filter" once per request.
			$Opts = apply_filters(
				'agm_google_maps-options-uci',
				get_option( 'agm_google_maps' )
			);
			if ( ! is_array( $Opts ) ) {
				$Opts = array();
			}
		}

		if ( ! isset( $Opts[ 'uci-' . $key ] ) ) {
			$Opts[ 'uci-' . $key ] = @$this->_default_options[ $key ];
		}
		$val = @$Opts[ 'uci-' . $key ];

		switch ( $format ) {
			case 'bool':
				return $this->_is_true( $val );

			case 'int':
				return intval( $val );

			default:
				return $val;
		}
	}

	/**
	 * Returns the value of a user-specific setting
	 *
	 * @since  1.0.0
	 * @param  int $user_id
	 * @param  string $key
	 * @return mixed Setting value.
	 */
	protected function _get_user_option( $user_id, $key ) {
		// User options are stored in the checkin-collection object in user meta.
		$data = $this->_get_checkin_data( $user_id );

		if ( isset( $data->{ $key } ) ) {
			return $data->{ $key };
		} else {
			return @$this->_default_user_options[ $key ];
		}
	}

	/**
	 * Sets the value of a user-specific setting
	 *
	 * @since  1.0.0
	 * @param  int $user_id
	 * @param  string $key
	 * @param  any $value
	 * @return mixed Setting value.
	 */
	protected function _set_user_option( $user_id, $key, $value ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}
		if ( in_array( $key, array( 'last_id', 'checkins' ) ) ) {
			// reserved keywords.
			return false;
		}

		// User options are stored in the checkin-collection object in user meta.
		$data = $this->_get_checkin_data( $user_id );
		$data->{ $key } = $value;
		$this->_replace_checkin_data( $user_id, $data );

		return true;
	}

	/**
	 * Returns the distance between two coordinates in meters.
	 * Based on the following stackoverflow answer:
	 * {@link http://stackoverflow.com/a/9046008/313501}
	 *
	 * TODO: Move this to class_gm_map_model.php (confirm with Ve).
	 *
	 * @since  1.0.0
	 * @param  float $lat1 Point-A
	 * @param  float $lng1 Point-A
	 * @param  float $lat2 Point-B
	 * @param  float $lng2 Point-B
	 * @return float Distance in meters.
	 */
	public function get_distance( $lat1, $lng1, $lat2, $lng2 ) {
		$earth_radius = 3958.75;
		$d_lat = deg2rad( $lat2 - $lat1 );
		$d_lng = deg2rad( $lng2 - $lng1 );
		$val_a = sin( $d_lat / 2 ) * sin( $d_lat / 2 ) +
			cos( deg2rad( $lat1 ) ) * cos( deg2rad( $lat2 ) ) *
			sin( $d_lng / 2 ) * sin( $d_lng / 2 );
		$val_c = 2 * atan2( sqrt( $val_a ), sqrt( 1 - $val_a ) );
		$dist = $earth_radius * $val_c;

		// Convert from miles to meters:
		$meter_conversion = 1609;
		$meter_dist = $dist * $meter_conversion;

		return $meter_dist;
	}

	/**
	 * Tries to interpret the value as a boolean expression.
	 *
	 * @since  1.0.0
	 * @param  mixed $val Value to interpret.
	 * @return bool
	 */
	protected function _is_true( $val ) {
		$true = array( true, 1, '1', 'on', 'yes', 'true' );
		return in_array( $val, $true );
	}

	/**
	 * Returns the avatar of the defined user. This function checks if
	 * BuddyPress is active and uses the BuddyPress avatar, if possible.
	 *
	 * @since  1.0.0
	 * @param  int $user_id
	 * @param  int $size Optional. Width/height of the avatar. Default is 32px.
	 * @return URL to the avatar image.
	 */
	protected function _get_user_icon( $user_id, $size = 32 ) {
		$avatar = '';

		// For guests we display the default marker.
		// TODO: Add option so user can choose a guest-icon?
		if ( 0 == $user_id ) {
			return AGM_PLUGIN_URL . 'img/system/marker.png';
		}

		if ( function_exists( 'bp_core_fetch_avatar' ) ) {
			// If possible use the BuddyPress avatar.
			$avatar = bp_core_fetch_avatar(
				array(
					'object' => 'user',
					'item_id' => $user_id,
					'width' => $size,
					'height' => $size,
					'html' => false,
				)
			);
		} else {
			// When BuddyPress is not available then take default WordPress avatar.
			$img = get_avatar( $user_id, $size );

			// We now have a full <img> tag and need to extract the src URL...
			$array = array();
			preg_match( '/src=\'([^\']*)/i', $img, $array );
			if ( empty( $array ) ) {
				preg_match( '/src="([^"]*)/i', $img, $array );
			}
			$avatar = $array[1];
		}

		return $avatar;
	}

	/**
	 * Returns a short text that displays the user-name and formatted timestamp.
	 *
	 * @since  1.0.0
	 * @param  int $user_id
	 * @param  int $timestamp Timestamp value (seconds since epoch).
	 * @return string Meta-information for the marker ("by <user> on <time>")
	 */
	protected function _display_user_details( $user_id, $timestamp ) {
		static $Usernames = array();

		// Find the user nicename if we did not search it yet.
		if ( ! isset( $Usernames[ $user_id ] ) ) {
			$user = get_userdata( $user_id );
			if ( $user ) {
				$Usernames[ $user_id ] = $user->display_name;
			} else {
				$Usernames[ $user_id ] = __( 'Guest', AGM_LANG );
			}
		}

		// Build the meta string.
		$meta = __( 'By %1$s, on %2$s', AGM_LANG );
		$name = $Usernames[ $user_id ];
		$time = date( __( 'j F, Y (G:i)', AGM_LANG ), $timestamp );
		$meta = sprintf( $meta, $name, $time );

		return $meta;
	}

	/**
	 * Maybe we want to add an edit-link to the checkin-marker so the user can
	 * edit his checkin details.
	 *
	 * @since  1.0.0
	 * @param  object $checkin Checkin object.
	 * @return object The (maybe) modified checkin object.
	 */
	protected function _maybe_add_edit_link( $checkin ) {
		if ( is_user_logged_in() ) {
			if ( get_current_user_id() == $checkin['user_id'] ) {
				$checkin['marker']['body'] .= ' <a href="#" class="edit-marker" data-id="' . $checkin['id'] . '" data-identifier="' . $checkin['marker']['identifier'] . '">' .
					__( 'Edit', AGM_LANG ) .
					'</a>';
			}
		}
		return $checkin;
	}

	/**
	 * Makes sure the marker icon is an URL and not an img tag
	 *
	 * @since  1.0.2
	 * @param  object $checkin Checkin object.
	 * @return object The (maybe) modified checkin object.
	 */
	protected function _maybe_change_icon( $checkin ) {
		$icon = @$checkin['marker']['icon'];
		if ( empty( $icon ) ) {
			$icon = AGM_PLUGIN_URL . 'img/system/marker.png';
		} else if ( strpos( $icon, '<img' ) !== false ) {
			$array = array();
			preg_match( '/src=\'([^\']*)/i', $icon, $array );
			if ( empty( $array ) ) {
				preg_match( '/src="([^"]*)/i', $icon, $array );
			}
			$icon = $array[1];
		}
		$checkin['marker']['icon'] = $icon;

		return $checkin;
	}

};




// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
//                             USER PROFILE (ADMIN)
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------




/**
 * This class provides functions that any logged-in user can use to customize
 * his checkin-experience:
 *     - Enable auto-checkin
 *     - Change privacy options
 *     - List and modify checkins
 */
class Agm_UCI_UserProfile extends Agm_UCI_Shared {

	/**
	 * Initialize the user-profile functions
	 *
	 * @since  1.0.0
	 * @return Agm_UCI_UserProfile singleton instance.
	 */
	public static function serve() {
		static $inst = null;
		if ( null === $inst ) {
			$inst = new Agm_UCI_UserProfile();
		}
		return $inst;
	}

	/**
	 * Constructor.
	 * Hooks up the addon for WordPress Admin sections.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// Hook is only executed when viewing OWN profile page.
		add_action(
			'show_user_profile',
			array( $this, 'show_profile_options' )
		);

		// Hook is called when user updates his OWN profile.
		add_action(
			'personal_options_update',
			array( $this, 'save_profile_options' )
		);

		// Add the admin-js in the user profile.
		lib3()->ui->add( AGM_PLUGIN_URL . 'css/user-check-in-admin.min.css', 'profile.php' );
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/user-check-in.min.js', 'profile.php' );
	}

	/**
	 * Action hook. Displays the plugin option fields in the WordPress
	 * user-profile.
	 *
	 * @since  1.0.0
	 * @param  WP_User $user
	 */
	public function show_profile_options( $user ) {
		$edit_details = $this->_get_option( 'edit_details' );

		$auto_checkin = $this->_get_user_option( $user->ID, 'checkin' );
		$def_share = $this->_get_user_option( $user->ID, 'share' );
		$checkins = $this->_get_checkin_data( $user->ID );

		$sel_never = ($auto_checkin == 'off' ? 'selected="selected"' : '');
		$sel_ask = ($auto_checkin == 'ask' ? 'selected="selected"' : '');
		$sel_always = ($auto_checkin == 'on' ? 'selected="selected"' : '');

		$sel_priv = ($def_share == 'priv' ? 'selected="selected"' : '');
		$sel_member = ($def_share == 'member' ? 'selected="selected"' : '');
		$sel_pub = ($def_share == 'pub' ? 'selected="selected"' : '');

		$alternate = '';
		?>
		<h3><?php _e( 'Google Maps: Checkins', AGM_LANG ); ?></h3>

		<table class="form-table">
			<tr>
				<th><label for="">
					<?php _e( 'Check in my location', AGM_LANG ); ?>
				</label></th>
				<td>
					<select name="agm_google_maps[checkin]">
						<option value="off" <?php echo esc_html( $sel_never ); ?>><?php _e( 'Never submit my location', AGM_LANG ); ?></option>
						<option value="ask" <?php echo esc_html( $sel_ask ); ?>><?php _e( 'Always ask me before submitting my location', AGM_LANG ); ?></option>
						<option value="on" <?php echo esc_html( $sel_always ); ?>><?php _e( 'Submit my location and don´t ask me', AGM_LANG ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'Default privacy setting', AGM_LANG ); ?></th>
				<td>
					<select name="agm_google_maps[share]">
						<option value="priv" <?php echo esc_html( $sel_priv ); ?>><?php _e( 'Only I can see my location', AGM_LANG ); ?></option>
						<option value="member" <?php echo esc_html( $sel_member ); ?>><?php _e( 'Only logged in users can see my location', AGM_LANG ); ?></option>
						<option value="pub" <?php echo esc_html( $sel_pub ); ?>><?php _e( 'Everybody can see my location', AGM_LANG ); ?></option>
					</select>
				</td>
			</tr>
			<tr>
				<th><?php _e( 'My locations', AGM_LANG ); ?></th>
				<td>
		<?php if ( count( $checkins->checkins ) ) : ?>
			<ul class="lst-checkins">
			<?php foreach ( $checkins->checkins as $id => $checkin ) : ?>
				<?php
				$alternate = ('alternate' == $alternate ? '' : 'alternate');

				$sel_check_priv = ($checkin['share'] == 'priv' ? 'selected="selected"' : '');
				$sel_check_member = ($checkin['share'] == 'member' ? 'selected="selected"' : '');
				$sel_check_pub = ($checkin['share'] == 'pub' ? 'selected="selected"' : '');

				/*
				 * We use dashicons to display the sharing state.
				 * {@link http://melchoyce.github.io/dashicons/}
				 */
				$share_icon = 'dashicons-visibility pub';
				if ( 'member' == $checkin['share'] ) {
					$share_icon = 'dashicons-admin-users member';
				}
				if ( 'priv' == $checkin['share'] ) {
					$share_icon = 'dashicons-lock priv';
				}

				$is_modified = $checkin['modified'] > $checkin['created'];
				$created = date_i18n( 'd F Y (H:i:s)', $checkin['created'] );
				$modified = date_i18n( 'd F Y (H:i:s)', max( $checkin['modified'], $checkin['created'] ) );

				/*
				 * The fields are parsed using the function:
				 * @see $this->_get_ajax_location_data()
				 * For available parameters see the details in that function.
				 */
				?>
				<li class="item <?php echo esc_attr( $alternate ); ?>">
					<div class="read-only">
						<span class="the-preview the-preview-s"></span>
						<div class="infos">
							<span class="share-icon"><i class="dashicons <?php echo esc_attr( $share_icon ); ?>"></i></span>
							<?php if ( $edit_details ) : ?>
								<span class="title"><?php echo esc_attr( $checkin['title'] ); ?></span>
								<small>
							<?php endif; ?>
							<i class="dashicons dashicons-location"></i>
							<span class="lat"><?php echo esc_attr( $checkin['lat'] ); ?></span> / <span class="lng"><?php echo esc_attr( $checkin['lng'] ); ?></span>
							<?php if ( $checkin['count'] > 1 ) : ?>
								(<?php _e( 'Count', AGM_LANG ); ?>:
								<?php echo esc_html( $checkin['count'] ); ?>)
							<?php endif; ?>
							<?php if ( $edit_details ) : ?>
								</small>
							<?php endif; ?>
						</div>
						<span class="item-actions">
							<select class="share" name="agm_google_maps[checkins][<?php echo esc_attr( $id ); ?>][share]">
								<option value="priv" <?php echo esc_html( $sel_check_priv ); ?>><?php _e( 'Only me', AGM_LANG ); ?></option>
								<option value="member" <?php echo esc_html( $sel_check_member ); ?>><?php _e( 'Logged in users', AGM_LANG ); ?></option>
								<option value="pub" <?php echo esc_html( $sel_check_pub ); ?>><?php _e( 'Everybody', AGM_LANG ); ?></option>
							</select>
							<input type="hidden" class="trash-flag" name="agm_google_maps[checkins][<?php echo esc_attr( $id ); ?>][trash]" value="0" />
							<?php if ( $edit_details ) : ?>
								<a class="edit button button-small" href="#"><?php _e( 'Edit', AGM_LANG ); ?></a>
							<?php endif; ?>
							<a class="small trash" href="#"><?php _e( 'Delete', AGM_LANG ); ?></a>
							<a class="small restore" href="#" style="display:none"><?php _e( 'Don´t delete', AGM_LANG ); ?></a>
						</span>
					</div>
					<?php if ( $edit_details ) : ?>
						<div class="form" style="display:none">
							<span class="the-preview the-preview-l"></span>
							<div class="row">
								<label for="lat-<?php echo esc_attr( $id ); ?>"><?php _e( 'Lat', AGM_LANG ); ?>:</label>
								<span class="lat"><?php echo esc_attr( $checkin['lat'] ); ?></span>
							</div>
							<div class="row">
								<label for="lng-<?php echo esc_attr( $id ); ?>"><?php _e( 'Long', AGM_LANG ); ?>:</label>
								<span class="lng"><?php echo esc_attr( $checkin['lng'] ); ?></span>
							</div>
							<div class="row">
								<label for="title-<?php echo esc_attr( $id ); ?>"><?php _e( 'Title', AGM_LANG ); ?>:</label>
								<input type="text" id="title-<?php echo esc_attr( $id ); ?>" data-readonly=".title" name="agm_google_maps[checkins][<?php echo esc_attr( $id ); ?>][title]" value="<?php echo esc_attr( $checkin['title'] ); ?>" />
							</div>
							<div class="row">
								<label for="desc-<?php echo esc_attr( $id ); ?>"><?php _e( 'Description', AGM_LANG ); ?>:</label>
								<input type="text" id="desc-<?php echo esc_attr( $id ); ?>" name="agm_google_maps[checkins][<?php echo esc_attr( $id ); ?>][description]" value="<?php echo esc_attr( $checkin['description'] ); ?>" />
							</div>
							<div class="row">
								<label><?php _e( 'Last modified', AGM_LANG ); ?>:</label>
								<span>
									<?php echo esc_attr( $modified ); ?> -
									<?php if ( $is_modified ) : ?>
										<?php _e( 'created on', AGM_LANG ); ?> <?php echo esc_attr( $created ); ?> -
									<?php endif; ?>
									<?php _e( 'Count', AGM_LANG ); ?> <?php echo esc_html( $checkin['count'] ); ?>
								</span>
							</div>

							<div class="form-actions">
								<a class="save button button-small" href="#"><?php _e( 'OK', AGM_LANG ); ?></a>
								<a class="small cancel" href="#"><?php _e( 'Cancel', AGM_LANG ); ?></a>
							</div>
						</div>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php else : /* if: count( $checkins->checkins ) */ ?>
			-
		<?php endif; ?>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * Action hook. When user saves his profile we can store our plugin settings.
	 *
	 * @since  1.0.0
	 * @param  int $user_id
	 */
	public function save_profile_options( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$data = @$_POST['agm_google_maps'];
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		// Optin: Automatic checkin.
		if ( isset( $data['checkin'] ) ) {
			$auto_checkin = $data['checkin'];
			$this->_set_user_option( $user_id, 'checkin', $auto_checkin );
		}

		// Option: Default privacy.
		if ( isset( $data['share'] ) ) {
			$def_share = $data['share'];
			$this->_set_user_option( $user_id, 'share', $def_share );
		}

		// If checkin details are provided then update the checkins in DB.
		if ( isset( $data['checkins'] ) && is_array( $data['checkins'] ) ) {
			$checkins = $data['checkins'];
			/*
			 * The checkin-details are provided as array:
			 * each checkin-item is one array item
			 */
			foreach ( $checkins as $cid => $post_data ) {
				if ( 1 == $post_data['trash'] ) {
					/*
					 * DELETE-Flow is very straight forward:
					 *
					 * _remove_checkin()
					 *     _get_checkin_data()
					 *     remove checkin item in checkin-array
					 *     _replace_checkin_data()
					 */
					if ( is_numeric( $user_id ) ) {
						$this->_remove_checkin( $user_id, $cid );
					}
				} else {
					/*
					 * UPDATE-Flow:
					 *
					 * _get_ajax_location_data()
					 *     (this extracts details from the edit form)
					 * _update_checkin()
					 *     _get_checkin_data()
					 *     apply validation filter
					 *         (this preserves details that are not submitted via edit form)
					 *     replace checkin item in checkin-array
					 *     _replace_checkin_data()
					 */

					// Update the checkin
					$checkin = $this->_get_ajax_location_data( $post_data, $user_id, 'update' );

					if ( is_numeric( $user_id ) && is_array( $checkin ) ) {
						/*
						 * We use the "replace" function to update details
						 * instead of adding/merging the location
						 */
						$this->_update_checkin( $user_id, $cid, $checkin );
					}
				}
			}
		} // end if: isset( $data['checkins'] )

	}
}




// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
//                            PLUGIN OPTIONS (ADMIN)
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------




/**
 * The Admin class for the check-ins plugin.
 * Handles the option screen.
 */
class Agm_UCI_AdminPages extends Agm_UCI_Shared {

	/**
	 * Initialize the admin side
	 *
	 * @since  1.0.0
	 * @return Agm_UCI_AdminPages singleton instance.
	 */
	public static function serve() {
		static $inst = null;
		if ( null === $inst ) {
			$inst = new Agm_UCI_AdminPages();
		}
		return $inst;
	}

	/**
	 * Constructor.
	 * Hooks up the addon for WordPress Admin sections.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// Call the parent constructor.
		parent::__construct();

		// Add our own options to the plugin config page.
		add_action(
			'agm_google_maps-options-plugins_options',
			array( $this, 'register_settings' )
		);

		// Load the admin javascripts
		add_action(
			'agm-admin-scripts',
			array( $this, 'enqueue_scripts' )
		);

		// -- Ajax handlers

		// Collect checkin data from user and store it.
		add_action(
			'wp_ajax_agm_uci_checkin',
			array( $this, 'ajax_checkin' )
		);

		/*
		 * We might even allow guests to contribute check-in locations.
		 * @see ajax_guest_checkin() has more notes on this feature.
		 */
		if ( $this->_get_option( 'allow_guest_checkin' ) ) {
			add_action(
				'wp_ajax_nopriv_agm_uci_checkin',
				array( $this, 'ajax_guest_checkin' )
			);
		}

		// Return HTML code for an edit-form to the user.
		add_action(
			'wp_ajax_agm_uci_checkin_editor',
			array( $this, 'ajax_checkin_editor' )
		);

		// Save the checkin changes the user made on frontend (via ajax_checkin_editor).
		add_action(
			'wp_ajax_agm_uci_checkin_save',
			array( $this, 'ajax_checkin_save' )
		);

		// Delete a single checkin from the frontend (via ajax_checkin_editor).
		add_action(
			'wp_ajax_agm_uci_checkin_remove',
			array( $this, 'ajax_checkin_delete' )
		);

		// Available for registered users to change their auto-commit status.
		add_action(
			'wp_ajax_agm_uci_permission',
			array( $this, 'ajax_set_permission' )
		);
	}

	/**
	 * Extend the plugin config page and add our own AddOn options.
	 *
	 * @since  1.0.0
	 */
	public function register_settings() {
		add_settings_section(
			'agm_google_maps_uci',
			__( 'User check-ins', AGM_LANG ),
			create_function( '', '' ),
			'agm_google_maps_options_page'
		);

		add_settings_field(
			'agm_google_maps_uci_usage',
			__( 'Overview', AGM_LANG ),
			array( $this, 'render_settings_box_usage' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);

		add_settings_field(
			'agm_google_maps_uci_allowed_checkin',
			__( 'Allow check-ins', AGM_LANG ),
			array( $this, 'render_settings_box_allowed_data' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);

		add_settings_field(
			'agm_google_maps_uci_edit_details',
			__( 'Checkin details', AGM_LANG ),
			array( $this, 'render_settings_box_details' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);

		/*
		add_settings_field(
			'agm_google_maps_uci_linking',
			__( 'Connect checkins to content', AGM_LANG ),
			array( $this, 'render_settings_box_linking' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);
		*/

		add_settings_field(
			'agm_google_maps_uci_limits',
			__( 'Checkin limits', AGM_LANG ),
			array( $this, 'render_settings_box_limits' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);

		add_settings_field(
			'agm_google_maps_uci_map',
			__( 'Map defaults', AGM_LANG ),
			array( $this, 'render_settings_box_map' ),
			'agm_google_maps_options_page',
			'agm_google_maps_uci'
		);
	}

	/**
	 * Display the usage instructions for the add-on.
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_usage() {
		$shortcode_tag = 'agm_map' == AgmMapModel::get_config( 'shortcode_map' ) ? 'agm_map' : 'map';
		?>
		<p>
			<?php _e( 'Shortcode', AGM_LANG ); ?> <code>[agm_add_checkin]</code><br />
			<?php _e( 'Try to collect the user location on the page without displaying a map. No options available.', AGM_LANG ); ?>
		<p>
		</p>
			<?php _e( 'Shortcode', AGM_LANG ); ?> <code>[agm_show_checkins]</code><br />
			<?php printf(
				__(
					'Display a google map with the specified locations. ' .
					'The shortcode supports all options of the <code>[%s]</code> ' .
					'shortcode. Additionally these options are available:', AGM_LANG
				),
				$shortcode_tag
			); ?>
			<ul style="padding-left: 20px; margin-top: 5px; list-style: disc">
			<li><?php _e( '<code>user="1,2"</code>: (Filter) Show locations of these users by user-ID.', AGM_LANG ); ?><br />
			<li><?php _e( '<code>role="admin,editor"</code>: (Filter) Show locations of users in these roles.', AGM_LANG ); ?></li>
			<li><?php _e( '<code>group="group1,group2"</code>: (Filter, BuddyPress) Show locations of users in these groups.', AGM_LANG ); ?></li>
			<li><?php _e( '<code>guest="true"</code>: (Filter) True additionally shows locations of guests. False will only show locations of registred users.', AGM_LANG ); ?></li>
			<li><?php _e( '<code>last_hour="24"</code>: (Filter, time) Show locations that were added or updated within the last X hours.', AGM_LANG ); ?></li>
			<li><?php _e( '<code>automatic="true"</code>: Try to collect the user location on page load (same as <code>[agm_add_checkin]</code>.', AGM_LANG ); ?></li>
			<li><?php _e( '<code>show_button="true"</code>: Add a button below the map that allows the user to manually submit his location.', AGM_LANG ); ?></li>
			</ul>
			<?php _e( 'If no filter is defined then all locations will be displayed on the map.', AGM_LANG ); ?>
		</p>
		<?php
	}

	/**
	 * Display the plugin options for "allowed check-ins".
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_allowed_data() {
		$automatic_checkin = $this->_get_option( 'automatic_checkin' );
		$allow_guest_checkin = $this->_get_option( 'allow_guest_checkin' );

		if ( ! in_array( $automatic_checkin, array( 'never', 'once', 'always', 'manual' ) ) ) {
			$automatic_checkin = 'never';
		}

		$sel_never = ('never' == $automatic_checkin ? 'selected="selected"' : '');
		$sel_once = ('once' == $automatic_checkin ? 'selected="selected"' : '');
		$sel_always = ('always' == $automatic_checkin ? 'selected="selected"' : '');
		$sel_manual = ('manual' == $automatic_checkin ? 'selected="selected"' : '');

		$check_guest_on = ($allow_guest_checkin ? '' : 'checked="checked"');
		$check_guest_off = ($allow_guest_checkin ? 'checked="checked"' : '');

		?>
		<p>
			<label for="agm-uci-automatic_checkin">
			<?php _e( 'Collect location data in the background', AGM_LANG ); ?>:
			</label>
			<select id="agm-uci-automatic_checkin" name="agm_google_maps[uci-automatic_checkin]">
				<option value="never" <?php echo esc_html( $sel_never ); ?>><?php _e( 'Never (disable this feature)', AGM_LANG ); ?></option>
				<option value="once" <?php echo esc_html( $sel_once ); ?>><?php _e( 'One checkin per user', AGM_LANG ); ?></option>
				<option value="always" <?php echo esc_html( $sel_always ); ?>><?php _e( 'On every page', AGM_LANG ); ?></option>
				<option value="manual" <?php echo esc_html( $sel_manual ); ?>><?php _e( 'Manual via shortcode [agm_add_checkin]', AGM_LANG ); ?></option>
			</select><br /><em>
			<?php _e( 'Location data is only collected with user agreement.<br />Guests will be asked to submit the location on each visit. Logged-in users can additionally choose to always or never submit their location.', AGM_LANG ) ?>
			</em>
		</p>

		<p>
			<label for="agm-uci-allow_guest_checkin-off">
			<input type="radio" id="agm-uci-allow_guest_checkin-off" name="agm_google_maps[uci-allow_guest_checkin]" value="0" <?php echo esc_html( $check_guest_on ); ?> />
			<?php _e( 'Only logged in users can check-in their location', AGM_LANG ); ?>
			</label>
		</p>

		<p>
			<label for="agm-uci-allow_guest_checkin-on">
			<input type="radio" id="agm-uci-allow_guest_checkin-on" name="agm_google_maps[uci-allow_guest_checkin]" value="1" <?php echo esc_html( $check_guest_off ); ?> />
			<?php _e( 'Guests can check-in their location', AGM_LANG ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Display the plugin options for "Checkin details".
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_details() {
		$edit_details = $this->_get_option( 'edit_details' );
		$check_edit_off = ($edit_details ? '' : 'checked="checked"');
		$check_edit_on = ($edit_details ? 'checked="checked"' : '');

		?>
		<p>
			<label for="agm-uci-edit_details-off">
			<input type="radio" id="agm-uci-edit_details-off" name="agm_google_maps[uci-edit_details]" value="0"  <?php echo esc_html( $check_edit_off ); ?> />
			<?php _e( 'Only save the location', AGM_LANG ); ?>
			</label>
		</p>

		<p>
			<label for="agm-uci-edit_details-on">
			<input type="radio" id="agm-uci-edit_details-on" name="agm_google_maps[uci-edit_details]" value="1" <?php echo esc_html( $check_edit_on ); ?> />
			<?php _e( 'Users can edit locations and add additional information', AGM_LANG ); ?>
			</label><br /><em>
			<?php _e( 'Logged in users can optionaly enter a title and description for their check-ins and edit the location details later.', AGM_LANG ); ?>
			</em>
		</p>
		<?php
	}

	/**
	 * Display the plugin options for "Linking".
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_linking() {
		$link_pages = $this->_get_option( 'link_pages' );
		$link_comments = $this->_get_option( 'link_comments' );

		$check_link_pages = ($link_pages ? 'checked="checked"' : '');
		$check_link_comments = ($link_comments ? 'checked="checked"' : '');

		?>
		<p>
			<label for="agm-uci-link_pages">
			<input type="checkbox" id="agm-uci-link_pages" name="agm_google_maps[uci-link_pages]" value="1" <?php echo esc_html( $check_link_pages ); ?> />
			<?php _e( 'Checkins are linked to the current post/page', AGM_LANG ); ?>
			</label><br /><em>
			<?php _e( 'When activated all checkins are linked to the current post or page.<br />Only works on single pages, not on special pages like search results, archive or front page.', AGM_LANG ); ?>
			</em>
		</p>

		<p>
			<label for="agm-uci-link_comments">
			<input type="checkbox" id="agm-uci-link_comments" name="agm_google_maps[uci-link_comments]" value="1" <?php echo esc_html( $check_link_comments ); ?> />
			<?php _e( 'Enable geotaging of comments', AGM_LANG ); ?>
			</label><br /><em>
			<?php _e( 'When activated there will be a new checkbox to <code>Geotag this comment</code> in the comment form.', AGM_LANG ); ?>
			</em>
		</p>
		<?php
	}

	/**
	 * Display the plugin options for "Limits".
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_limits() {
		$max_checkins = $this->_get_option( 'max_checkins', 'int' );
		$max_guest_checkins = $this->_get_option( 'max_guest_checkins', 'int' );
		$merge_distance = $this->_get_option( 'merge_distance', 'int' );

		?>
		<p>
			<label for="agm-uci-max_checkins">
				<?php _e( 'Max number of checkins to keep for a single user', AGM_LANG ); ?>
			</label>
			<input type="number" min="1" max="100" maxlength="3" id="agm-uci-max_checkins" name="agm_google_maps[uci-max_checkins]" value="<?php echo esc_attr( $max_checkins ); ?>" />
			<br />
			<em>
			<?php _e( 'When a new location is checked-in and the limit is reached an older location will be removed from the users collection.<br />A high value can result in a longer loading time of maps. Value between 1-100', AGM_LANG ); ?>
			</em>
		</p>

		<p>
			<label for="agm-uci-max_guest_checkins">
				<?php _e( 'Max anonyoumous guest checkins to keep', AGM_LANG ); ?>
			</label>
			<input type="number" min="1" max="1000" maxlength="4" id="agm-uci-max_guest_checkins" name="agm_google_maps[uci-max_guest_checkins]" value="<?php echo esc_attr( $max_guest_checkins ); ?>" />
			<br />
			<em>
			<?php _e( 'When a new location is checked-in and the limit is reached the oldest location will be removed from the guest collection.<br />A high value can result in a longer loading time of maps. Value between 1-1000', AGM_LANG ); ?>
			</em>
		</p>

		<p>
			<label for="agm-uci-merge_distance">
				<?php _e( 'Catch double check-ins that are closer than', AGM_LANG ); ?>
			</label>
			<input type="number" min="0" max="100" maxlength="3" id="agm-uci-merge_distance" name="agm_google_maps[uci-merge_distance]" value="<?php echo esc_attr( $merge_distance ); ?>" /> meters
			<br />
			<em>
			<?php _e( 'When a user checks-in a new location, the plugin will not save the location when it is closer than this distance to an already checked-in location.<br />Only the locations of the current user are checked. Value between 1-100', AGM_LANG ); ?>
			</em>
		</p>
		<?php
	}

	/**
	 * Display the plugin options for "Map details".
	 *
	 * @since  1.0.0
	 */
	public function render_settings_box_map() {
		$show_empty_map = $this->_get_option( 'show_empty_map', 'bool' );
		$check_show_empty_map = ($show_empty_map ? 'checked="checked"' : '');
		?>
		<p>
			<label for="agm-uci-show_empty_map">
			<input type="checkbox" id="agm-uci-show_empty_map" name="agm_google_maps[uci-show_empty_map]" value="1" <?php echo esc_html( $check_show_empty_map ); ?> />
			<?php _e( 'Show maps without markers', AGM_LANG ); ?>
			</label><br /><em>
			<?php _e( 'Choose if a map that contains no location markers should be displayed or not.<br />If an empty map is displayed the map center is undefined by default, so you should provide a default position (e.g. via the "<code>Where am I?</code>" or "<code>Center map on location</code>" AddOn).', AGM_LANG ); ?>
			</em>
		</p>
		<?php
	}

	/**
	 * Adds the admin-javascript to the page. Provides the user-check-in options
	 * in the map editor.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/user-check-in.min.js' );
	}

	/**
	 * AJAX HANDLER. We get location details from a user to check-in.
	 *
	 * @since  1.0.0
	 */
	public function ajax_checkin() {
		$user_id = get_current_user_id();
		$this->_ajax_handle_checkin( $user_id, $_POST );
	}

	/**
	 * AJAX HANDLER. We get location details from a guest to check-in.
	 * Guest check-ins are all collected in a single list. Later it is not
	 * possible anymore to differentiate which guest did checkin which locations.
	 * Hence on the map there will be no difference between all guest locations.
	 *
	 * @since  1.0.0
	 */
	public function ajax_guest_checkin() {
		if ( $this->_get_option( 'allow_guest_checkin' ) ) {
			$this->_ajax_handle_checkin( 0, $_POST );
		} else {
			die();
		}
	}

	/**
	 * AJAX HANDLER. Return a form for the user to edit his checkin.
	 * We do this via ajax to make sure the user will only see his own checkin
	 * details and that the login-check is done.
	 *
	 * @since  1.0.0
	 */
	public function ajax_checkin_editor() {
		$user_id = get_current_user_id();
		$cid = @$_POST['id'];
		$checkin = $this->_get_checkin_by_id( $user_id, $cid );

		if ( ! empty( $checkin ) ) {
			$edit_details = $this->_get_option( 'edit_details' );
			$this->_checkin_editor( $user_id, $cid, $checkin, $edit_details );
		}
		die();
	}

	/**
	 * AJAX HANDLER. Receives the changes submitted via the ajax_checkin_editor
	 * form and updates the database.
	 *
	 * @since  1.0.0
	 */
	public function ajax_checkin_save() {
		$user_id = get_current_user_id();
		$cid = @$_POST['id'];

		$data = $this->_get_ajax_location_data( $_POST, $user_id, 'update' );

		// Default response is "ERR".
		$response = array(
			'status' => 'ERR',
		);

		// After saving we set response to "OK" and include the checkin details.
		if ( $this->_update_checkin( $user_id, $cid, $data ) ) {
			$checkin = $this->_get_checkin_by_id( $user_id, $cid );

			// Important: Multiple occurances of this code, search for #ReturnCheckin!

			// These fields are not saved in DB:
			$checkin['user_id'] = $user_id;
			$checkin['id'] = $cid;
			$checkin['marker']['identifier'] = 'uci-' . $user_id . '-' . $cid;

			$checkin = $this->_maybe_add_edit_link( $checkin );
			$checkin = $this->_maybe_change_icon( $checkin );
			$response['status'] = 'OK';
			$response['data'] = $checkin;
		}
		echo json_encode( $response );

		die();
	}

	/**
	 * AJAX HANDLER. Deletes a checkin via the ajax_checkin_editor form.
	 *
	 * @since  1.0.0
	 */
	public function ajax_checkin_delete() {
		$user_id = get_current_user_id();
		$cid = @$_POST['id'];

		// Default response is "ERR".
		$response = array(
			'status' => 'ERR',
		);

		// After saving we set response to "OK" and include the checkin details.
		if ( $this->_remove_checkin( $user_id, $cid ) ) {
			$checkin = array(
				'marker' => array(),
			);

			// Important: Multiple occurances of this code, search for #ReturnCheckin!

			// These fields are not saved in DB:
			$checkin['user_id'] = -1;
			$checkin['id'] = -1;
			$checkin['marker']['identifier'] = 'uci-' . $user_id . '-' . $cid;

			$response['status'] = 'DEL';
			$response['data'] = $checkin;
		}
		echo json_encode( $response );

		die();
	}

	/**
	 * AJAX HANDLER. Change the auto-checkin option of the current user.
	 * This handler is only available for logged in users.
	 *
	 * @since  1.0.0
	 */
	public function ajax_set_permission() {
		if ( is_user_logged_in() ) {
			$state = @$_POST['state'];
			$user_id = get_current_user_id();

			if ( in_array( $state, array( 'on', 'off', 'ask' ) ) ) {
				$this->_set_user_option( $user_id, 'checkin', $state );
			}
		}

		die();
	}

	/**
	 * Helper for ajax handlers ajax_checkin() and ajax_guest_checkin()
	 *
	 * @since  1.0.0
	 * @param  int $user_id Can be 0 for guests.
	 * @param  array $data Data collection containing the location details (e.g. $_POST).
	 */
	protected function _ajax_handle_checkin( $user_id, $data ) {
		$success = true;
		$cid = 0;
		$checkin = $this->_get_ajax_location_data( $data, $user_id, 'add' );

		if ( is_numeric( $user_id ) && is_array( $checkin ) ) {
			$cid = $this->_add_checkin( $user_id, $checkin );
			$success = ($cid !== false);
		} else {
			$success = false;
		}

		$response = array(
			'status' => 'ERR',
		);

		if ( $success ) {
			$checkin = $this->_get_checkin_by_id( $user_id, $cid );

			if ( ! empty( $checkin ) ) {
				// Important: Multiple occurances of this code, search for #ReturnCheckin!

				// These fields are not saved in DB:
				$checkin['user_id'] = $user_id;
				$checkin['id'] = $cid;
				$checkin['marker']['identifier'] = 'uci-' . $user_id . '-' . $cid;

				$checkin = $this->_maybe_add_edit_link( $checkin );
				$checkin = $this->_maybe_change_icon( $checkin );
				$response['status'] = 'OK';
				$response['data'] = $checkin;
			}
		}
		echo json_encode( $response );

		die();
	}

	/**
	 * Builds the HTML form to edit the specified checkin item.
	 *
	 * @since  1.0.0
	 * @param  int $user_id Owner of the checkin.
	 * @param  int $cid Checkin ID.
	 * @param  array $checkin Check details.
	 * @param  bool $edit_details False: Only sharing can be changed.
	 */
	protected function _checkin_editor( $user_id, $cid, $checkin, $edit_details ) {
		$sel_check_priv = ($checkin['share'] == 'priv' ? 'selected="selected"' : '');
		$sel_check_member = ($checkin['share'] == 'member' ? 'selected="selected"' : '');
		$sel_check_pub = ($checkin['share'] == 'pub' ? 'selected="selected"' : '');

		?>
		<div class="agm-form" data-id="<?php echo esc_attr( $cid ); ?>">
			<div class="inner-modal"></div>
			<span class="agm-form-close">×</span>
			<div class="agm-form-title">Edit location</div>

			<?php if ( $edit_details ) : ?>
				<div class="row">
					<label for="title-<?php echo esc_attr( $cid ); ?>"><?php _e( 'Title', AGM_LANG ); ?>:</label>
					<input type="text" id="title-<?php echo esc_attr( $cid ); ?>" class="title" value="<?php echo esc_attr( $checkin['title'] ); ?>" />
				</div>
				<div class="row">
					<label for="desc-<?php echo esc_attr( $cid ); ?>"><?php _e( 'Description', AGM_LANG ); ?>:</label>
					<input type="text" id="desc-<?php echo esc_attr( $cid ); ?>" class="desc" value="<?php echo esc_attr( $checkin['description'] ); ?>" />
				</div>
			<?php endif; ?>

			<div class="row">
				<label for="share-<?php echo esc_attr( $cid ); ?>"><?php _e( 'Visible for', AGM_LANG ); ?>:</label>
				<select class="share" id="share-<?php echo esc_attr( $cid ); ?>">
					<option value="priv" <?php echo esc_html( $sel_check_priv ); ?>><?php _e( 'Only me', AGM_LANG ); ?></option>
					<option value="member" <?php echo esc_html( $sel_check_member ); ?>><?php _e( 'Logged in users', AGM_LANG ); ?></option>
					<option value="pub" <?php echo esc_html( $sel_check_pub ); ?>><?php _e( 'Everybody', AGM_LANG ); ?></option>
				</select>
			</div>

			<div class="agm-form-buttons">
				<button type="button" class="agm-form-delete pull-left"><?php _e( 'Delete', AGM_LANG ); ?></button>
				<button type="button" class="agm-form-cancel"><?php _e( 'Cancel', AGM_LANG ); ?></button>
				<button type="button" class="agm-form-save"><?php _e( 'Save', AGM_LANG ); ?></button>
			</div>
		</div>
		<?php
	}
}




// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
//                                   FRONTEND
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------




/**
 * User-side implementation will do two things:
 *  - Collect the check-in data.
 *  - Display the checked-in places on a map.
 */
class Agm_UCI_UserPages extends Agm_UCI_Shared {

	/**
	 * Instance of an AgmMapReplacer object.
	 *
	 * @since  1.0.2
	 * @var    AgmMapReplacer
	 */
	protected $_replacer = null;

	/**
	 * Flag that is calculated once on each request to decide if the addon
	 * should try to collect the checkin location in background.
	 * @see init_userside() [= the wp_init hook]
	 *
	 * @since  1.0.0
	 * @var    bool
	 */
	private $_checkin_now = false;

	/**
	 * Additional information on reason when _checkin_now is true.
	 * Values: 'always', 'once', 'manual', 'never', 'map', 'user'
	 *
	 * @since  1.0.0
	 * @var    string
	 */
	private $_checkin_reason = '';

	/**
	 * Initialize the user-side.
	 *
	 * @since  1.0.0
	 * @return Agm_UCI_UserPages singleton instance.
	 */
	public static function serve() {
		static $inst = null;
		if ( null === $inst ) {
			$inst = new Agm_UCI_UserPages();
		}
		return $inst;
	}

	/**
	 * Constructor.
	 * Add WordPress hooks to get the addon working...
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		// Call the parent constructor.
		parent::__construct();

		$this->_replacer = new AgmMarkerReplacer();

		// Some conditional statements do not work right now, so add the wp_init hook.
		add_action(
			'init',
			array( $this, 'init_userside' )
		);

		/*
		 * This shortcode triggers a manual background-checkin on a specific page.
		 * Also if this shortcode is positioned before the agm_show_checkins
		 * shortcode then an eventual confirmation message for the user will be
		 * displayed at that position.
		 */
		add_shortcode( 'agm_add_checkin', array( $this, 'do_checkin' ) );

		// This will display a map with checked-in locations.
		add_shortcode( 'agm_show_checkins', array( $this, 'show_checkins' ) );
	}

	/**
	 * Init-Hook.
	 * We hook up some functions here since the is_user_logged_in() funciton is
	 * not working earlier.
	 *
	 * @since  1.0.0
	 */
	public function init_userside() {
		$automatic_checkin = $this->_get_option( 'automatic_checkin' );
		$allow_guest_checkin = $this->_get_option( 'allow_guest_checkin' );

		// By default we assume that we don't want to collect the checkin details.
		$this->_checkin_now = false;
		$this->_checkin_reason = $automatic_checkin;

		switch ( $automatic_checkin ) {
			case 'always':
				$this->_checkin_now = ( is_user_logged_in() || $allow_guest_checkin );
				break;

			case 'once':
				if ( is_user_logged_in() ) {
					$data = $this->_get_checkin_data( get_current_user_id() );
					$this->_checkin_now = ( 0 == count( $data->checkins ) );
				}
				break;

			case 'manual':
				// Don't auto checkin.
				break;

			case 'never':
				// Don't auto checkin.
				break;
		}

		// AddOn decided to try a background checkin. Load the script.
		if ( $this->_checkin_now ) {
			add_action(
				'wp_enqueue_scripts',
				array( $this, 'enqueue_scripts' ), 10, 1
			);
		}
	}

	/**
	 * Adds the javascript components to the page. For one part the .js file
	 * "user-check-in.js" is loaded and also a global variable "_agmUci" is added
	 * which contains all the addon options required by the js file.
	 *
	 * @since  1.0.0
	 */
	public function enqueue_scripts() {
		static $Done = false;

		// Make sure to only localize and enqueue the scrips once.
		if ( ! $Done ) {
			$allow_guest_checkin = $this->_get_option( 'allow_guest_checkin' );

			if ( is_user_logged_in() || $allow_guest_checkin ) {
				$user_id = get_current_user_id();
				if ( 0 == $user_id ) {
					// Always asks guests for confirmation
					$allow_checkin = 'ask';
				} else {
					// Logged in users can turn checkin on/off
					$allow_checkin = $this->_get_user_option( $user_id, 'checkin' );
				}

				// See if user does not want to submit data.
				if ( 'off' == $allow_checkin && $this->_checkin_now ) {
					$this->_checkin_now = false;
					$this->_checkin_reason = 'user';
				}

				// This icon is used to dynamically display a new checkin on he current page.
				$icon_url = $this->_get_user_icon( $user_id );

				// Add translations that are used in javascript.
				$lang = array(
					'title' => __( 'Submit location?', AGM_LANG ),
					'ask_checkin' => __( 'The webpage wants to know where you are. Send your location?', AGM_LANG ),
					'yes' => __( 'Yes', AGM_LANG ),
					'no' => __( 'No', AGM_LANG ),
					'remember' => __( 'Don´t ask me again.', AGM_LANG ),
					'status_sending' => __( 'Sending your location...', AGM_LANG ),
					'status_saved' => __( 'Location saved!', AGM_LANG ),
					'status_submit' => __( 'Submit my current location', AGM_LANG ),
				);

				/*
				 * Tipp: These settings are sitewide/user-specific.
				 * NO map-specific settings are included here.
				 */
				$data = array(
					/*
					 * We need the ajax_url here, since empty maps might not be
					 * displayed and so maybe the _agm object is missing.
					 */
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'icon_url' => $icon_url,

					// Tell javascript if the visitor is a guest or logged in.
					'guest' => ( ! is_user_logged_in() ),

					// @see init_userside() and do_checkin()
					'do_checkin' => $this->_checkin_now,
					'checkin_info' => $this->_checkin_reason,

					// Setting from user profile.
					'allow_checkin' => $allow_checkin,

					// Translations.
					'lang' => $lang,
				);

				lib3()->ui->data( '_agmUci', $data, 'front' );
				lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/user-check-in.min.js', 'front' );
				lib3()->ui->add( AGM_PLUGIN_URL . 'css/user-check-in.min.css', 'front' );
			}

			// Prevent double enqueueing of these scripts.
			$Done = true;
		}
	}

	/**
	 * Creates a map from a list of markers.
	 *
	 * @since  1.0.0
	 * @param  array $markers Map markers to display.
	 * @param  int|false $id ID-Attribute of the map.
	 * @param  array $overrides Display options (i.e. shortcode attributes)
	 * @return string HTML Code to display the map.
	 */
	private function _create_map( $markers, $id = false, $overrides = array() ) {
		AgmDependencies::ensure_presence();

		if ( empty ( $markers ) && ! $this->_get_option( 'show_empty_map' ) ) {
			return false;
		}
		$id = $id ? $id : md5( time() . rand() );

		$map = $this->_model->get_map_defaults();
		$map['defaults'] = $map;
		$map['id'] = $id;
		$map['show_map'] = 1;
		$map['show_markers'] = 0;
		$map['markers'] = $markers;

		return $this->_replacer->create_tag( $map, $overrides );
	}

	/**
	 * Shortcode. Will trigger a background checkin, if the user has not disabled it.
	 *
	 * @since  1.0.0
	 * @param  array $attributes Shortcode attributes.
	 * @return string HTML Code that triggers the checkin process.
	 */
	public function do_checkin( $attributes ) {
		$automatic_checkin = $this->_get_option( 'automatic_checkin' );

		if ( $automatic_checkin == 'never' ) {
			// Don't do anything.
			return '';
		}

		/*
		 * When $automatic_checkin is NOT 'never' then do the checkin, since
		 * this is inside the "agm_add_checkin" shortcode which is interpreted
		 * as a manual command for background checkin.
		 */

		// Admin decided to try a background checkin on this page.
		$this->_checkin_now = true;
		$this->_checkin_reason = 'manual';
		$this->enqueue_scripts();

		// Return an invisible hook to position the JS-confirmation message.
		return $this->show_confirmation_hook();
	}

	/**
	 * Shortcode. Show a map with specified checkin markers.
	 *
	 * @since  1.0.0
	 * @param  array $attributes Shortcode attributes.
	 * @return string HTML Code to output the map.
	 */
	public function show_checkins( $attributes ) {
		/*
		 * Accept the attributes in singular or plural form.
		 * In conflict, the plural form will define the actual value.
		 */
		$defaults = array(
			// Limit checkins by user-ID
			'user' => '',
			'users' => null,
			// Limit checkins by role name
			'role' => '',
			'roles' => null,
			// Limit checkins by BuddyPress group slug
			'group' => '',
			'groups' => null,
			// Limit checkins by time (past X hours)
			'last_hours' => 0,
			// Limit checkins by page-link
			# 'page' => '',
			# 'pages' => null,
			// Limit checkins by comment-link
			# 'comment' => '',
			# 'comments' => null,
			// Show or hide anonymous checkins
			'guest' => true,
			'guests' => null,
			// Enable/disable automatic checkin on page load
			'automatic' => false,
			// Show a button to allow manual checkin
			'show_button' => false,
		);
		$defaults = $this->_replacer->default_values( $defaults );

		// If we have a map-ID then load the map attributes from DB.
		if ( isset( $attributes['id'] ) ) {
			$map = $this->_model->get_map( $attributes['id'] );
			$attributes = array_merge( $attributes, $map );
		}

		$attributes = shortcode_atts( $defaults, $attributes, 'agm_show_checkins' );
		$attributes = apply_filters(
			'agm-shortcode-overrides',
			$attributes, $attributes
		);

		$markers = $this->get_markers( $attributes );

		// Parse boolean attributes.
		$attributes['automatic'] = agm_positive_values( $attributes['automatic'], 1, 0 );
		$attributes['show_button'] = agm_positive_values( $attributes['show_button'], 1, 0 );

		// Enqueue the front-end javascript.
		$this->_checkin_reason = 'map';
		$this->enqueue_scripts();

		// Create the map with all markers.
		$map = $this->_create_map( $markers, false, $attributes );

		$out = '';

		// Add an invisible hook to position the JS-confirmation message above the map.
		$out .= $this->show_confirmation_hook();

		// Add the actual map to the page.
		$out .= '<div id="agm-map-checkins">' . $map . '</div>';

		return $out;
	}

	/**
	 * Returns HTML code that is used to position the javascript confirmation message.
	 *
	 * @since  1.0.0
	 * @return string An HTML element that is recognized by the JS function.
	 */
	public function show_confirmation_hook() {
		static $Done = false;

		if ( ! $Done ) {
			$Done = true;
			return '<div class="agm-map-uci-confirm" style="display:none"></div>';
		}

		return '';
	}

	/**
	 * Returns an array of map markers containing the user checkins.
	 *
	 * @since  1.0.0
	 * @param  array $attributes Shortcode attributes that define which checkins to display.
	 * @return array List of map markers.
	 */
	protected function get_markers( $attributes ) {
		$loggedin = is_user_logged_in();
		extract( $attributes );
		$markers = array();

		// If the plural version was specified then use it instead of singular value.
		if ( null !== $users ) { $user = $users; }
		if ( null !== $roles ) { $role = $roles; }
		if ( null !== $groups ) { $group = $groups; }
		# if ( null !== $pages ) { $page = $pages; }
		# if ( null !== $comments ) { $comment = $comments; }
		if ( null !== $guests ) { $guest = $guests; }

		// Get a list of all user-IDs.
		$users = $this->_get_user_ids( $user, $role, $group, $guest );

		// Get a list of all checkin objects of these users, already filtered by time.
		$checkins = $this->_get_checkins_by_user( $users, $last_hours );

		// Now we have all checkin items, so make them AGM-compatible!
		foreach ( $checkins as $checkin ) {
			$checkin = $this->_maybe_add_edit_link( $checkin );
			$checkin = $this->_maybe_change_icon( $checkin );
			$markers[] = $checkin['marker'];
		}

		return $markers;
	}

	/**
	 * Returns an array of user-IDs based on the specified filters.
	 * This will be a collection of all users that are identified by one of the
	 * filters. E.g. all users that have the defined ID or the defined role, ...
	 *
	 * @since  1.0.0
	 * @param  string $user A single user-ID or a comma separated list.
	 * @param  string $role A single role name or a comma separated list.
	 * @param  string $group A single Buddypress group-slug or comma separated list.
	 * @param  bool $include_guest
	 * @return array List of all user-IDs.
	 */
	protected function _get_user_ids( $user, $role, $group, $include_guest ) {
		$users = array();

		if ( strlen( $user ) == 0 && strlen( $role ) == 0 && strlen( $group ) == 0 ) {
			// Get all users!
			$args = array(
				'fields' => 'ID',
			);
			$users = get_users( $args );
		} else {
			// Initialize user list with the specified list (if available).
			if ( ! empty( $user ) ) {
				$users = explode( ',', $user );
			}

			// Append all users of the specified roles (if available).
			if ( ! empty( $role ) ) {
				$roles = explode( ',', $role );
				foreach ( $roles as $role_name ) {
					// Get all users of the defined role.
					$args = array(
						'role' => $role_name,
						'fields' => 'ID',
					);
					$role_users = get_users( $args );

					// Append the users to the user list.
					$users = array_merge( $users, $role_users );
				}
			}

			// Append all users based on BuddyPress group.
			//TODO: This function is untested.
			if ( defined( 'BP_VERSION' ) && class_exists( 'BP_Groups_Member' ) ) {
				if ( ! empty ( $group ) ) {
					$groups = explode( ',', $group );
					foreach ( $groups as $group_slug ) {
						// Get the user_ids of the group.
						$group_id = groups_get_id( $group_slug );
						$group_users = BP_Groups_Member::get_group_member_ids( $group_id );

						// Append group users to the user list.
						$users = array_merge( $users, $group_users );
					}
				}
			}
		}

		// Maybe include guest checkins.
		if ( true == $include_guest ) {
			$users[] = 0;
		}

		// Remove duplicate users.
		$users = array_unique( $users );
		return $users;
	}

	/**
	 * Returns a list of all checkin objects of the specified users that are
	 * shared with the current user (i.e. "pub" or "member") and that are within
	 * the defined time frame.
	 *
	 * @since  1.0.0
	 * @param  array $users List of user-IDs.
	 * @param  int $last_hours Only checkins that were created/updated within
	 *                that amount of time are included (1 = past 1 hour).
	 * @return array List of checkin objects.
	 */
	protected function _get_checkins_by_user( $users, $last_hours ) {
		$last_hours = absint( $last_hours );
		$checkins = array();

		// Define which checkins are visible for the current user.
		$can_see = array( 'pub' );
		if ( is_user_logged_in() ) {
			$can_see[] = 'member';
		}

		// Calculate the time margin
		$time_limit = (0 == $last_hours ? 0 : time() - ($last_hours * 3600));

		// Loop through all users.
		foreach ( $users as $user_id ) {
			$user_id = intval( $user_id );
			$data = $this->_get_checkin_data( $user_id );

			// Loop through all checkins of the user.
			foreach ( $data->checkins as $cid => $checkin ) {
				if ( ! in_array( $checkin['share'], $can_see ) ) {
					continue;
				}
				if ( $checkin['created'] < $time_limit && $checkin['modified'] < $time_limit ) {
					continue;
				}

				// Important: Multiple occurances of this code, search for #ReturnCheckin!

				// These fields are not saved in DB:
				$checkin['user_id'] = $user_id;
				$checkin['id'] = $cid;
				$checkin['marker']['identifier'] = 'uci-' . $user_id . '-' . $cid;

				$checkins[] = $checkin;
			}
		}

		return $checkins;
	}
}




// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------
//                                 GLOBAL / INIT
// ----------------------------------------------------------------------------
// ----------------------------------------------------------------------------




if ( is_admin() ) {
	// Hint: All ajax handlers are stored in the AdminPages instance.
	Agm_UCI_AdminPages::serve();

	// The user profile extends the WordPress user-section and adds ajax handlers.
	Agm_UCI_UserProfile::serve();
} else {
	Agm_UCI_UserPages::serve();
}