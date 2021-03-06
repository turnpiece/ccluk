<?php

class CCLUK_BP_Custom {

	const DEBUGGING = false;

	const TWFY_URL = 'https://www.theyworkforyou.com';

	const POSTCODE_FIELD_ID = 2;

	const SLUG = 'ccluk';

	private $members_group_id;

	private $location = array(
		'parliamentary_constituency',
		'region',
		'european_electoral_region',
		'country'
	);

	private $mp = array(
		'full_name',
		'party',
		'url',
		'image',
		'image_height',
		'image_width'
	);

	public function __construct() {
		add_action( 'xprofile_data_after_save', array( $this, 'profile_location' ));
		add_action( 'bp_after_profile_field_content', array( $this, 'user_location_fields' ) );
		add_action( 'bp_core_activated_user', array( $this, 'join_group_on_signup' ) );
		add_action( 'bp_signup_pre_validate', array( $this, 'signup_pre_validate' ) );
		add_action( 'bp_account_details_fields', array( $this, 'password_optional_notice' ) );
		add_action( 'bp_after_registration_submit_buttons', array( $this, 'manual_signup_notice') );

		// sync BP/mailchimp user data
		add_filter( 'mc4wp_user_merge_vars', array( $this, 'mailchimp_user_sync' ), 10, 2 );
		add_filter( 'mailchimp_sync_user_data', array( $this, 'mailchimp_user_sync' ), 10, 2 );

		// add BuddyPress menu items for logged in users
		add_filter( 'wp_nav_menu_items', array( $this, 'buddypress_menu' ), 10, 2 );

		// disable data export
		add_filter( 'bp_settings_show_user_data_page', '__return_false' );

		// remove starred messages
		add_filter( 'bp_is_messages_star_active', '__return_false' );

		// reorder profile menu tabs
		add_action( 'bp_setup_nav', array( $this, 'profile_tab_order'), 999 );

		// subscribe to group on joining
		add_action( 'groups_join_group', array( $this, 'groups_join_group' ), 10, 2 );

		// groups page intro
		add_action( 'bp_before_directory_groups', array( $this, 'groups_page_intro' ) );

		// members page intro
		add_action( 'bp_before_directory_members', array( $this, 'members_page_intro' ) );

		// define the default Profile component
		define( 'BP_DEFAULT_COMPONENT', 'profile' );

		// populate and subscribe to members group
		foreach( array( 'populate', 'subscribe' ) as $fn ) {
			if (! get_option( self::SLUG.'_'.$fn.'_members_group_completed' ) ) {
				// cron job to add users to members group
				add_action( $fn.'_members_group_cron_hook', array( $this, $fn.'_members_group' ) );

				// check if this job has been scheduled
				if ( ! wp_next_scheduled( $fn.'_members_group_cron_hook' ) ) {
					wp_schedule_event( time(), 'daily', $fn.'_members_group_cron_hook' );
				}
			}
		}
	}

	public function profile_tab_order() {
		/* Reorder profile tabs */
		global $bp;
		$bp->bp_nav['profile']['position'] = 10;
		$bp->bp_nav['activity']['position'] = 20;
	}

	public function buddypress_menu( $items, $args ) {
		if( is_user_logged_in() && $args->theme_location == 'primary-menu' ) {
			$slugs = array(
				'groups' => bp_get_groups_root_slug(),
				'members' => bp_get_members_root_slug(),
				'activity' => bp_get_activity_root_slug(),
			);

			$class['groups'] = "menu-item menu-item-has-children members-area";
			$class['members'] = $class['activity'] = "menu-item";

			if (bp_is_groups_component() || bp_is_activity_component() || bp_is_members_component()) {
				$class['groups'] .= ' current-menu-ancestor';

				if (bp_is_groups_component())
					$class['groups'] .= ' current-menu-item';

				if (bp_is_activity_component())
					$class['activity'] .= ' current-menu-item';

				if (bp_is_members_component())
					$class['members'] .= ' current-menu-item';
			}

			$items .= '<li class="'.$class['groups'].'"><a href="'.site_url() . '/' . $slugs['groups'] . '/">'.__( 'Groups' ).'</a>';
			$items .= '<ul class="sub-menu">';
			foreach( array( 
				'members' => __( 'Members' ), 
				'activity' => __( 'Activity' )) as $id => $title )
				$items .= '<li class="'.$class[$id].'"><a href="'.site_url() . '/' . $slugs[$id] .'/">'.$title.'</a></li>';
				
			$items .= '</ul></li>';
		}
		return $items;
	}

	// text at bottom of registration page
	public function manual_signup_notice() { ?>
		<div class="manual-registration">
			<p><?php printf( __( "If you'd rather speak to someone before signing up, please <a href=\"%s\">get in touch</a>." ), '/contact-us/' ) ?></p>
		</div>
	<?php }

	// use name as username
	public function signup_pre_validate() {

		self::vardump( $_POST );

	    if (empty($_POST['signup_username'])) {
			// username field was empty
			self::debug( 'generating username from name' );

			$field = xprofile_get_field_id_from_name( 'name' );

			self::debug( 'using name field '.$field.' => '.$_POST['field_'.$field] );

			if (!empty($field) && !empty($_POST['field_'.$field])) {
				// create username from name
	        	$_POST['signup_username'] = $this->get_unique_username(
					sanitize_user(
						str_replace( ' ', '-', $_POST['field_'.$field] ),
						true
					 )
				);

				self::debug( 'generated username = '.$_POST['signup_username'] );
			}
	    }

		// check if they entered a password
		if (empty($_POST['signup_password'])) {
			// disable notification email
			remove_action( 'register_new_user', 'wp_send_new_user_notifications' );

			// generate random password
			$_POST['signup_password'] = wp_generate_password();
			$_POST['signup_password_confirm'] = $_POST['signup_password'];

			self::debug( 'auto-generated password' );
		}
	}

	public function password_optional_notice() { ?>
		<p class="description"><?php _e( "A password is optional, but providing one will mean you can login to this site and update any of the information you've given us. You'll also be able to keep up to date with what's going on and connect with other members.", 'onesocial' ) ?></p>
	<?php }

	// join members group on signup
	public function join_group_on_signup( $user_id ){
		$this->join_members_group( $user_id );
	}

	public function groups_page_intro() {
		echo $this->get_page_intro( __( "Join a group to connect with other members and get involved." ) );
	}

	public function members_page_intro() {
		echo $this->get_page_intro( __( "NOTE: This shows members who have joined via this website.<br />There are members who aren't on here." ) );
	}

	private function get_page_intro( $content ) {
		return '<div class="intro"><p>' . $content . '</p></div>';
	}

	public function populate_members_group() {

		self::debug( __FUNCTION__ );

		$id = self::SLUG . '_' . __FUNCTION__;

		$offset = get_option( $id, 0 );

		$users = get_users(
			array(
				'role__in' => array( 'contributor', 'author' ),
				'orderby' => 'registered',
				'order' => 'ASC',
				'fields' => array( 'ID' ),
				'number' => 100,
				'offset' => (int)$offset
			)
		);

		if (count($users)) {
			foreach ( $users as $user )
				$this->join_members_group( $user->ID );

			update_option( $id, $offset + count($users) );

		} else {
			// assume it's all been done
			add_option( $id.'_completed', time() );

			// remove the cron job
			$timestamp = wp_next_scheduled( 'populate_members_group_cron_hook' );
			wp_unschedule_event( $timestamp, 'populate_members_group_cron_hook' );
		}
	}

	// subscribe people to the members group
	public function subscribe_members_group() {

		self::debug( __FUNCTION__ );

		$id = self::SLUG . '_' . __FUNCTION__;

		$offset = get_option( $id, 0 );

		// get members group id
		if ($group_id = $this->get_members_group_id()) {

			$users = get_users(
				array(
					'role__in' => array( 'contributor', 'author' ),
					'orderby' => 'registered',
					'order' => 'ASC',
					'fields' => array( 'ID' ),
					'number' => 100,
					'offset' => (int)$offset
				)
			);

			if (count($users)) {
				foreach ( $users as $user ) {
					if (groups_is_user_member( $user->ID, $group_id )) {
						$this->subscribe_to_group( $user->ID, $group_id );
					} else {
						$this->join_members_group( $user->ID );
					}
				}	

				update_option( $id, $offset + count($users) );

			} else {
				// assume it's all been done
				add_option( $id.'_completed', time() );

				// remove the cron job
				$timestamp = wp_next_scheduled( 'subscribe_members_group_cron_hook' );
				wp_unschedule_event( $timestamp, 'subscribe_members_group_cron_hook' );
			}
		}
	}

	/**
	 * 
	 * groups join group
	 * action on joining a group
	 * 
	 * @param int $group_id
	 * @param int $user_id
	 * 
	 */
	public function groups_join_group( $group_id, $user_id ) {
		$this->subscribe_to_group( $user_id, $group_id );
	}

	private function subscribe_to_group( $user_id, $group_id ) {
		// check group email subscriptions plugin is active
		if (function_exists( 'ass_get_group_subscription_status' ) && 
			function_exists( 'ass_group_subscription' )) {

			$subs = ass_get_group_subscription_status( $user_id, $group_id );

			if (empty($subs)) {
				// subscribe them to daily digest email on joining a group
				ass_group_subscription( 'dig', $user_id, $group_id );
			}
		}
	}

	/**
	 * join members group
	 * 
	 * @param int $user_id
	 * 
	 */
	private function join_members_group( $user_id ) {
		if ($group_id = $this->get_members_group_id()) {
			if ( !groups_is_user_member( $user_id, $group_id ) ) {
				self::debug( __FUNCTION__ . ' ' . $user_id );

				// join them to the group
				groups_join_group( $group_id, $user_id );

				// delete the activity record
				bp_activity_delete(
					array(
						'type' => 'joined_group',
						'item_id' => $group_id,
						'user_id' => $user_id,
					)
				);
			}
		}
	}

	/**
	 * Add custom BuddyPress registration fields to MailChimp.
	 */
	public function mailchimp_user_sync( $data, $user ) {

	    if ($postcode = xprofile_get_field_data( self::POSTCODE_FIELD_ID , $user->id )) {
	        $data['POSTCODE'] = $postcode;

			if ($location = get_user_meta( $user->id, 'location', true )) {
				if (isset($location['parliamentary_constituency']))
					$data['PARLCONS'] = $location['parliamentary_constituency'];
			}

			if ($mp = (array) get_user_meta($user->id, 'mp', true)) {
				self::debug( print_r( $mp, true ) );
				if (isset($mp['full_name']))
					$data['MP'] = $mp['full_name'];
			}
	    }

		self::vardump( $data );

	    return $data;
	}

	/**
	 *
	 * profile location
	 *
	 * @param object $obj
	 *
	 */
	public function profile_location( $obj ) {

		//$postcode_id = $this->get_field_id( 'postcode' );
		$postcode_id = xprofile_get_field_id_from_name( 'postcode' );

		if ($obj->field_id == $postcode_id) {
			// get location data
			$this->postcode_lookup( $obj->user_id, $obj->value );

			// get MP
			$this->get_mp( $obj->user_id, $obj->value );
		}
	}

	public function user_location_fields( $user_id = false ) {
	    global $bp;

	    if( !$user_id )
	        $user_id = bp_displayed_user_id();

	    /* field will only shown on base.
	     * so if in case we are on somewhere else then skip it !
	     *
	     * It's safe enough to assume that 'base' profile group will always be there and its id will be 1,
	     * since there's no apparent way of deleting this field group.
	     */
	    if( !function_exists('bp_get_the_profile_group_id') || (bp_get_the_profile_group_id() != 1 && ! is_admin() )) {
	        return;
	    }

	    $location = (array) get_user_meta($user_id, 'location', true);

	    //Profile > View > display user's location
	    if ( 'public' == $bp->current_action ) {
	        if( $this->array_not_all_empty($location) ) {
	            ?>
	            <div class="bp-widget social">
	                <h2><?php _e('Location') ?></h2>
	                <table class="profile-fields">
	                    <tbody>

	                    <?php foreach( $location as $key => $value ) : ?>

	                        <?php

	                        if( empty( $value ) )
	                            continue;

	                        ?>
	                        <tr class="field_type_textbox field_<?php echo $key ?>">
	                            <td class="label"><?php echo ucfirst( str_replace('_', ' ', $key) ) ?></td>
	                            <td class="data"><?php echo $value; ?></td>
	                        </tr>

	                        <?php if( $key == 'parliamentary_constituency') {
	                        	$this->output_mp( $user_id );
	                        } ?>

	                    <?php endforeach; ?>
	                    </tbody>
	                </table>
	            </div>
	            <?php
	        }
	    }
	}

	/**
	 * get unique username
	 *
	 * @param string $username
	 * @return string
	 *
	 */
	private function get_unique_username( $username ) {
		$i = 0;
		while ( username_exists($username) && $i++ ) {
			$username = sprintf( '%s-%s', $username, $i );
		}
		return $username;
	}

	private function output_mp( $user_id ) {
		// get MP details

        $mp = (array) get_user_meta($user_id, 'mp', true);

        if (!empty($mp) && isset($mp['full_name'])) : ?>
        	<tr class="field_type_textbox field_mp">
        		<td class="label"><?php _e( 'MP' ) ?></td>
        		<td class="data">
        			<a href="<?php echo self::TWFY_URL . $mp['url'] ?>">
        				<img src="<?php echo self::TWFY_URL . $mp['image'] ?>" alt="<?php echo $mp['full_name']?>" />
        				<?php echo $mp['full_name'] ?>
        				<?php if (!empty($mp['party'])) : ?>
        					(<?php echo $mp['party'] ?>)
        				<?php endif; ?>
        			</a>
        		</td>
			</tr>
		<?php endif;
	}

	/**
	 *
	 * postcode lookup
	 *
	 * @param int $user_id
	 * @param string $postcode
	 *
	 */
	private function postcode_lookup( $user_id, $postcode ) {

		$API = new CCLUK_Postcode_API();
		$result = $API->lookup( $postcode );

		if (!empty($result)) {

			$location = array();

			foreach( $this->location as $field ) {
				$this->debug( 'setting user '.$user_id.' '.$field.' = '.$result->{ $field } );
				$location[ $field ] = $result->{ $field };
			}

			$this->vardump( $mp_data );

			update_user_meta( $user_id, 'location', $location );

			do_action( 'ccluk_location_saved', $location );
		}
	}

	/**
	 *
	 * get MP
	 *
	 * @param int $user_id
	 * @param string $postcode
	 * @return array
	 *
	 */
	private function get_mp( $user_id, $postcode ) {
		// get MP
		$MPAPI = new CCLUK_Parliament_API();

		$result = $MPAPI->getMP( $postcode );

		if (!empty($result)) {

			$mp = array();

			foreach( $this->mp as $field ) {
				$mp[ $field ] = $result[ $field ];
			}

		}

		update_user_meta( $user_id, 'mp', $mp );

		return $mp;
	}

	/**
	 * Check if array has any non-empty value
	 * @since 1.0
	 **/
	private function array_not_all_empty($array){
	    foreach ($array as $value) {
	        if(!empty($value)) {
	            return true;
	        }
	    }
	    return false;
	}

	protected function request($jsonurl){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, str_replace(' ', '%20', $jsonurl));
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
		  'Content-Type: application/json',
		]);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	private function get_members_group_id() {

		if (empty($this->members_group_id)) {
			global $wpdb;

			$bp = buddypress();

			self::vardump( $bp->groups );
			self::debug( __FUNCTION__ . ' ' . $bp->groups->table_name );

			$this->members_group_id = $wpdb->get_var( "SELECT `id` FROM `{$bp->groups->table_name}` WHERE `status` = 'public' AND `parent_id` = 0 AND `slug` LIKE '%all-members'" );
		}

		return $this->members_group_id;
	}

	protected static function debug( $message ) {
		if (self::DEBUGGING)
			error_log( __CLASS__ . ' :: ' . $message );
	}

	protected static function vardump( $array ) {
		self::debug( print_r( $array, true ) );
	}
}

new CCLUK_BP_Custom;


//Put together by Ryan Hart 2016
//Class to use the API provided by http://postcodes.io

class CCLUK_Postcode_API extends CCLUK_BP_Custom {

	const URL = 'https://api.postcodes.io';

	public function lookup($postcode){
		$jsonurl = self::URL.'/postcodes/'.$postcode;
		$json = $this->request($jsonurl);
		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function bulkLookup($postcodes){
		$data_string = json_encode(array('postcodes' => $postcodes));
		$ch = curl_init(self::URL.'/postcodes');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		        'Content-Type: application/json',
		        'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($result);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function nearestPostcodesFromLongLat($longitude, $latitude){
		$jsonurl = self::URL.'/postcodes?lon='.$longitude.'&lat='.$latitude;
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function bulkReverseGeocoding($geolocations){
		$data_string = json_encode(array('geolocations' => $geolocations));

		$ch = curl_init(self::URL.'/postcodes');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		        'Content-Type: application/json',
		        'Content-Length: ' . strlen($data_string))
		);

		$result = curl_exec($ch);
		curl_close($ch);
		$decoded = json_decode($result);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function random(){
		$jsonurl = self::URL.'/random/postcodes/';
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function validate($postcode){
		$jsonurl = self::URL.'/postcodes/'.$postcode.'/validate';
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			if($decoded->result == 1){
				return true;
			}
			else{
				return false;
			}
		}
		else{
			return false;
		}
		return false;
	}

	public function nearest($postcode){
		$jsonurl = self::URL.'/postcodes/'.$postcode.'/nearest';
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function partial($postcode){
		$jsonurl = self::URL.'/postcodes/'.$postcode.'/autocomplete';
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function query($postcode){
		$jsonurl = self::URL.'/postcodes?q='.$postcode;
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function lookupTerminated($postcode){
		$jsonurl = self::URL.'/terminated_postcodes/'.$postcode;
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function lookupOutwardCode($code){
		$jsonurl = self::URL.'/outcodes/'.$code;
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function nearestOutwardCode($code){
		$jsonurl = self::URL.'/outcodes/'.$code.'/nearest';
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function nearestOutwardCodeFromLongLat($longitude, $latitude){
		$jsonurl = self::URL.'/outcodes?lon='.$longitude.'&lat='.$latitude;
		$json = $this->request($jsonurl);

		$decoded = json_decode($json);
		if($decoded->status == 200){
			return $decoded->result;
		}
		else{
			return false;
		}
		return false;
	}

	public function distance($postcode1, $postcode2, $unit){
		//adapted from http://www.geodatasource.com/developers/php
		/*
			Units:
			M = Miles
			N = Nautical Miles
			K = Kilometers
		*/
		$postcode1 = $this->lookup($postcode1);
		$postcode2 = $this->lookup($postcode2);

		if($postcode1 == null || $postcode2 == null){
			return false;
		}

		$theta = $postcode1->longitude - $postcode2->longitude;
		$dist = sin(deg2rad($postcode1->latitude)) * sin(deg2rad($postcode2->latitude)) +  cos(deg2rad($postcode1->latitude)) * cos(deg2rad($postcode2->latitude)) * cos(deg2rad($theta));
		$dist = acos($dist);
		$dist = rad2deg($dist);
		$miles = $dist * 60 * 1.1515;
		$unit = strtoupper($unit);

		if ($unit == "K") {
		    return ($miles * 1.609344);
		} else if ($unit == "N") {
		    return ($miles * 0.8684);
		} else {
		    return $miles;
		}
	}
}

class CCLUK_Parliament_API extends CCLUK_BP_Custom {

	const KEY = 'EuoStzCe22iZBfnZboAf3cBM';

	public function getMP( $postcode ) {
		$url = parent::TWFY_URL.'/api/getMP?key='.self::KEY.'&postcode='.$postcode.'&output=php';
		$data = $this->request($url);

		if (!empty($data))
			return unserialize( $data );
	}
}
