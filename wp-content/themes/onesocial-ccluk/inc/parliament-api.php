<?php

class CCLUK_Parliament_API {

	const TWFY_API_BASE_URL = 'https://www.theyworkforyou.com/api/'; 
	const TWFY_API_KEY = 'EuoStzCe22iZBfnZboAf3cBM';

	const COUNTRIES_URL = 'https://cdn.rawgit.com/everypolitician/everypolitician-data/master/countries.json';
	
	function __construct() {

		add_action( 'wp_ajax_getMP', array( $this, 'getMP' ), 10, 1 );
		add_action( 'wp_ajax_nopriv_getMP', array( $this, 'getMP' ), 10, 1 );
	}

	/**
	 *
	 * get data
	 *
	 * @param string $query
	 * @return array
	 *
	 */
	private function getData( $url ) {

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => ,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		return decode_json( $response );
	}

	private function 

	/**
	 *
	 * get MP by postcode
	 *
	 * @param string $postcode
	 * @return array
	 *
	 */
	public function getMP( $postcode ) {

		$url= self::TWFY_API_BASE_URL . 'getMP?postcode=' . urlencode( $postcode ) . '&output=js&key=' . self::TWFY_API_KEY ;

		$data = $this->getData( $url );



	}

	private function getCountry() {
		// get contact details
		$countries = $this->getData( self::COUNTRIES_URL );

		foreach ( $countries as $country )
			 if ($country['slug'] == 'UK')
			 	return $country;
			 
	}

	public function displayMP() {

	}

	/**
	 * Custom validation callback to validate UK postcodes.
	 *
	 * It also tries to format provided postcode in correct format.
	 *
	 * Note: It's only usable for "postcode" fields.
	 */
	public function checkPostcode($original_postcode)
	{
		// Set callback's custom error message (CI specific)
		// $this->set_message('check_postcode_uk', 'Invalid UK postcode format.');

		// Permitted letters depend upon their position in the postcode.
		// Character 1
		$alpha1 = "[abcdefghijklmnoprstuwyz]";
		// Character 2
		$alpha2 = "[abcdefghklmnopqrstuvwxy]";
		// Character 3
		$alpha3 = "[abcdefghjkpmnrstuvwxy]";
		// Character 4
		$alpha4 = "[abehmnprvwxy]";
		// Character 5
		$alpha5 = "[abdefghjlnpqrstuwxyz]";

		// Expression for postcodes: AN NAA, ANN NAA, AAN NAA, and AANN NAA with a space
		$pcexp[0] = '/^('.$alpha1.'{1}'.$alpha2.'{0,1}[0-9]{1,2})([[:space:]]{0,})([0-9]{1}'.$alpha5.'{2})$/';

		// Expression for postcodes: ANA NAA
		$pcexp[1] = '/^('.$alpha1.'{1}[0-9]{1}'.$alpha3.'{1})([[:space:]]{0,})([0-9]{1}'.$alpha5.'{2})$/';

		// Expression for postcodes: AANA NAA
		$pcexp[2] = '/^('.$alpha1.'{1}'.$alpha2.'{1}[0-9]{1}'.$alpha4.')([[:space:]]{0,})([0-9]{1}'.$alpha5.'{2})$/';

		// Exception for the special postcode GIR 0AA
		$pcexp[3] = '/^(gir)([[:space:]]{0,})(0aa)$/';

		// Standard BFPO numbers
		$pcexp[4] = '/^(bfpo)([[:space:]]{0,})([0-9]{1,4})$/';

		// c/o BFPO numbers
		$pcexp[5] = '/^(bfpo)([[:space:]]{0,})(c\/o([[:space:]]{0,})[0-9]{1,3})$/';

		// Overseas Territories
		$pcexp[6] = '/^([a-z]{4})([[:space:]]{0,})(1zz)$/';

		// Anquilla
		$pcexp[7] = '/^ai-2640$/';

		// Load up the string to check, converting into lowercase
		$postcode = strtolower($original_postcode);

		// Assume we are not going to find a valid postcode
		$valid = FALSE;

		// Check the string against the six types of postcodes
		foreach ($pcexp as $regexp)
		{
			if (preg_match($regexp, $postcode, $matches))
			{
				// Load new postcode back into the form element
				$postcode = strtoupper ($matches[1] . ' ' . $matches [3]);

				// Take account of the special BFPO c/o format
				$postcode = preg_replace ('/C\/O([[:space:]]{0,})/', 'c/o ', $postcode);

				// Take acount of special Anquilla postcode format (a pain, but that's the way it is)
				preg_match($pcexp[7], strtolower($original_postcode), $matches) AND $postcode = 'AI-2640';

				// Remember that we have found that the code is valid and break from loop
				$valid = TRUE;
				break;
			}
		}

		// Return with the reformatted valid postcode in uppercase if the postcode was
		return $valid ? $postcode : FALSE;
	}
}
