<?php

class Opt_In_Condition_From_Search_Engine extends Opt_In_Condition_Abstract implements Opt_In_Condition_Interface
{
	function is_allowed(Hustle_Model $optin){
		return $this->is_from_searchengine_ref();
	}

	/**
	 * Tests if the current referrer is a search engine.
	 * Current referrer has to be specified in the URL param "thereferer".
	 *
	 * @return bool
	 */
	public function is_from_searchengine_ref() {
		$response = false;
		$referrer = $this->utils()->get_referrer();

		$patterns = array(
			'/search?',
			'.google.',
			'web.info.com',
			'search.',
			'del.icio.us/search',
			'delicious.com/search',
			'soso.com',
			'/search/',
			'.yahoo.',
			'.bing.',
		);

		foreach ( $patterns as $url ) {
			if ( false !== stripos( $referrer, $url ) ) {
				if ( $url == '.google.' ) {
					if ( $this->is_googlesearch( $referrer ) ) {
						$response = true;
					} else {
						$response = false;
					}
				} else {
					$response = true;
				}
				break;
			}
		}
		return $response;
	}

	/**
	 * Checks if the referrer is a google web-source.
	 *
	 * courtesy Philipp Stracker
	 *
	 * @param  string $referrer
	 * @return bool
	 */
	public function is_googlesearch( $referrer = '' ) {
		$response = true;

		// Get the query strings and check its a web source.
		$qs = parse_url( $referrer, PHP_URL_QUERY );
		$qget = array();

		foreach ( explode( '&', $qs ) as $keyval ) {
			$kv = explode( '=', $keyval );
			if ( 2 == count( $kv ) ) {
				$qget[ trim( $kv[0] ) ] = trim( $kv[1] );
			}
		}

		if ( isset( $qget['source'] ) ) {
			$response = $qget['source'] == 'web';
		}

		return $response;
	}
	function label(){
		return __("Only from search engine", Opt_In::TEXT_DOMAIN);
	}
}