<?php

class CCLUK_BP_Custom {

	const DEBUGGING = false;

	private $fields = array(
		'parliamentary_constituency',
		'region',
		'european_electoral_region',
		'country'
	);

	function __construct() {
		add_action('xprofile_data_after_save', array( $this, 'profile_location' ));
		add_action('bp_after_profile_field_content', array( $this, 'user_location_fields' ) );
	}

	function user_location_fields( $user_id = false ) {
	    global $bp;

	    if( !$user_id )
	        $user_id = bp_displayed_user_id();
	    
	    /* field will only shown on base. 
	     * so if in case we are on somewhere else then skip it ! 
	     * 
	     * It's safe enough to assume that 'base' profile group will always be there and its id will be 1,
	     * since there's no apparent way of deleting this field group.
	     */
	    if( !function_exists('bp_get_the_profile_group_id') || (function_exists('bp_get_the_profile_group_id') && bp_get_the_profile_group_id() != 1 && ! is_admin() )) {
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

	                    <?php endforeach; ?>
	                    </tbody>
	                </table>
	            </div>
	            <?php
	        }
	    }
	}

	/**
	 *
	 * profile location
	 *
	 * @param object $obj
	 *
	 */
	function profile_location( $obj ) {

		//$postcode_id = $this->get_field_id( 'postcode' );
		$postcode_id = xprofile_get_field_id_from_name( 'postcode' );

		if ($obj->field_id == $postcode_id) {
			$this->postcode_lookup( $obj->user_id, $obj->value );
		}
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

			foreach( $this->fields as $field ) {
				$this->debug( 'setting user '.$user_id.' '.$field.' = '.$result->{ $field } );
				$location[ $field ] = $result->{ $field };
			}

			update_user_meta( $user_id, 'location', $location );

			do_action( 'ccluk_location_saved', $location );
		}
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

/*
	private function get_field_id( $name ) {
		global $wpdb;
		$bp = buddypress();
		$name = strtolower($name);
		$table = $bp->table_prefix . 'bp_xprofile_fields';
		return $wpdb->get_var( "SELECT `id` FROM `$table` WHERE LOWER(`name`) = '$name'" );
	}
*/
	private function debug( $message ) {
		if (self::DEBUGGING)
			error_log( __CLASS__ . ' :: ' . $message );
	}
}

new CCLUK_BP_Custom;


//Put together by Ryan Hart 2016
//Class to use the API provided by http://postcodes.io

class CCLUK_Postcode_API{

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

	public function request($jsonurl){
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
}
