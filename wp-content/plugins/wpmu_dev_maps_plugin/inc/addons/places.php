<?php
/*
Plugin Name: Google Places support
Description: Allows you to show nearby places - new options will be available in the map options dialog.
Plugin URI:  http://premium.wpmudev.org/project/wordpress-google-maps-plugin
Version:     1.0
Author:      Ve Bailovity (Incsub)
*/

class Agm_PlacesAdminPages {

	private function __construct() {}

	public static function serve() {
		$me = new Agm_PlacesAdminPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// UI
		add_action(
			'agm-admin-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-save-options',
			array( $this, 'prepare_for_save' ),
			10, 2
		);
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);

		// Adding in map defaults
		add_action(
			'agm_google_maps-options',
			array( $this, 'inject_default_location_types' )
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/admin/places.min.js' );
	}

	public function prepare_for_save( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}

	public function prepare_for_load( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}

	public function inject_default_location_types( $options ) {
		$options['place_types'] = array(
			'accounting' => __( 'Accounting', AGM_LANG ),
			'airport' => __( 'Airport', AGM_LANG ),
			'amusement_park' => __( 'Amusement park', AGM_LANG ),
			'aquarium' => __( 'Aquarium', AGM_LANG ),
			'art_gallery' => __( 'Art gallery', AGM_LANG ),
			'atm' => __( 'ATM', AGM_LANG ),
			'bakery' => __( 'Bakery', AGM_LANG ),
			'bank' => __( 'Bank', AGM_LANG ),
			'bar' => __( 'Bar', AGM_LANG ),
			'beauty_salon' => __( 'Beauty salon', AGM_LANG ),
			'bicycle_store' => __( 'Bicycle store', AGM_LANG ),
			'book_store' => __( 'Book store', AGM_LANG ),
			'bowling_alley' => __( 'Bowling alley', AGM_LANG ),
			'bus_station' => __( 'Bus station', AGM_LANG ),
			'cafe' => __( 'Cafe', AGM_LANG ),
			'campground' => __( 'Campground', AGM_LANG ),
			'car_dealer' => __( 'Car dealer', AGM_LANG ),
			'car_rental' => __( 'Car rental', AGM_LANG ),
			'car_repair' => __( 'Car repair', AGM_LANG ),
			'car_wash' => __( 'Car wash', AGM_LANG ),
			'casino' => __( 'Casino', AGM_LANG ),
			'cemetery' => __( 'Cemetery', AGM_LANG ),
			'church' => __( 'Church', AGM_LANG ),
			'city_hall' => __( 'City hall', AGM_LANG ),
			'clothing_store' => __( 'Clothing store', AGM_LANG ),
			'convenience_store' => __( 'Convenience store', AGM_LANG ),
			'courthouse' => __( 'Courthouse', AGM_LANG ),
			'dentist' => __( 'Dentist', AGM_LANG ),
			'department_store' => __( 'Department store', AGM_LANG ),
			'doctor' => __( 'Doctor', AGM_LANG ),
			'electrician' => __( 'Electrician', AGM_LANG ),
			'electronics_store' => __( 'Electronics store', AGM_LANG ),
			'embassy' => __( 'Embassy', AGM_LANG ),
			'establishment' => __( 'Establishment', AGM_LANG ),
			'finance' => __( 'Finance', AGM_LANG ),
			'fire_station' => __( 'Fire station', AGM_LANG ),
			'florist' => __( 'Florist', AGM_LANG ),
			'food' => __( 'Food', AGM_LANG ),
			'funeral_home' => __( 'Funeral home', AGM_LANG ),
			'furniture_store' => __( 'Furniture store', AGM_LANG ),
			'gas_station' => __( 'Gas station', AGM_LANG ),
			'general_contractor' => __( 'General contractor', AGM_LANG ),
			'grocery_or_supermarket' => __( 'Grocery or supermarket', AGM_LANG ),
			'gym' => __( 'Gym', AGM_LANG ),
			'hair_care' => __( 'Hair care', AGM_LANG ),
			'hardware_store' => __( 'Hardware store', AGM_LANG ),
			'health' => __( 'Health', AGM_LANG ),
			'hindu_temple' => __( 'Hindu temple', AGM_LANG ),
			'home_goods_store' => __( 'Home goods store', AGM_LANG ),
			'hospital' => __( 'Hospital', AGM_LANG ),
			'insurance_agency' => __( 'Insurance agency', AGM_LANG ),
			'jewelry_store' => __( 'Jewelry store', AGM_LANG ),
			'laundry' => __( 'Laundry', AGM_LANG ),
			'lawyer' => __( 'Lawyer', AGM_LANG ),
			'library' => __( 'Library', AGM_LANG ),
			'liquor_store' => __( 'Liquor store', AGM_LANG ),
			'local_government_office' => __( 'Local government office', AGM_LANG ),
			'locksmith' => __( 'Locksmith', AGM_LANG ),
			'lodging' => __( 'Lodging', AGM_LANG ),
			'meal_delivery' => __( 'Meal delivery', AGM_LANG ),
			'meal_takeaway' => __( 'Meal takeaway', AGM_LANG ),
			'mosque' => __( 'Mosque', AGM_LANG ),
			'movie_rental' => __( 'Movie rental', AGM_LANG ),
			'movie_theater' => __( 'Movie theater', AGM_LANG ),
			'moving_company' => __( 'Moving company', AGM_LANG ),
			'museum' => __( 'Museum', AGM_LANG ),
			'night_club' => __( 'Night club', AGM_LANG ),
			'painter' => __( 'Painter', AGM_LANG ),
			'park' => __( 'Park', AGM_LANG ),
			'parking' => __( 'Parking', AGM_LANG ),
			'pet_store' => __( 'Pet store', AGM_LANG ),
			'pharmacy' => __( 'Pharmacy', AGM_LANG ),
			'physiotherapist' => __( 'Physiotherapist', AGM_LANG ),
			'place_of_worship' => __( 'Place of worship', AGM_LANG ),
			'plumber' => __( 'Plumber', AGM_LANG ),
			'police' => __( 'Police', AGM_LANG ),
			'post_office' => __( 'Post office', AGM_LANG ),
			'real_estate_agency' => __( 'Real estate agency', AGM_LANG ),
			'restaurant' => __( 'Restaurant', AGM_LANG ),
			'roofing_contractor' => __( 'Roofing contractor', AGM_LANG ),
			'rv_park' => __( 'RV park', AGM_LANG ),
			'school' => __( 'School', AGM_LANG ),
			'shoe_store' => __( 'Shoe store', AGM_LANG ),
			'shopping_mall' => __( 'Shopping mall', AGM_LANG ),
			'spa' => __( 'Spa', AGM_LANG ),
			'stadium' => __( 'Stadium', AGM_LANG ),
			'storage' => __( 'Storage', AGM_LANG ),
			'store' => __( 'Store', AGM_LANG ),
			'subway_station' => __( 'Subway station', AGM_LANG ),
			'synagogue' => __( 'Synagogue', AGM_LANG ),
			'taxi_stand' => __( 'Taxi stand', AGM_LANG ),
			'train_station' => __( 'Train station', AGM_LANG ),
			'travel_agency' => __( 'Travel agency', AGM_LANG ),
			'university' => __( 'University', AGM_LANG ),
			'veterinary_care' => __( 'Veterinary care', AGM_LANG ),
			'zoo' => __( 'Zoo', AGM_LANG ),
		);
		return $options;
	}
}


class Agm_PlacesUserPages {
	private function __construct() {}

	public static function serve() {
		$me = new Agm_PlacesUserPages();
		$me->_add_hooks();
	}

	private function _add_hooks() {
		// UI
		add_action(
			'agm-user-scripts',
			array( $this, 'load_scripts' )
		);
		add_filter(
			'agm-load-options',
			array( $this, 'prepare_for_load' ),
			10, 2
		);
	}

	public function load_scripts() {
		lib3()->ui->add( AGM_PLUGIN_URL . 'js/user/places.min.js', 'front' );
	}

	public function prepare_for_load( $options, $raw ) {
		$options['show_places'] = isset( $raw['show_places']) ? $raw['show_places'] : 0;
		$options['places_radius'] = isset( $raw['places_radius']) ? $raw['places_radius'] : 1000;
		$options['place_types'] = isset( $raw['place_types']) ? $raw['place_types'] : array();
		return $options;
	}
}

function _agm_places_add_library_support( $data ) {
	$data['libraries'] = $data['libraries'] ? $data['libraries'] : array();
	$data['libraries'][] = 'places';
	return $data;
}
add_filter( 'agm_google_maps-javascript-data_object', '_agm_places_add_library_support' );

if ( is_admin() ) {
	Agm_PlacesAdminPages::serve();
} else {
	Agm_PlacesUserPages::serve();
}