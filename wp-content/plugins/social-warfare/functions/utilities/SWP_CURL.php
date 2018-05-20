<?php

/**
 * SWP_CURL: A class process API share count requests via cURL
 *
 * @package   SocialWarfare\Functions
 * @copyright Copyright (c) 2018, Warfare Plugins, LLC
 * @license   GPL-3.0+
 * @since     1.0.0
 * @since     3.0.0 | 22 FEB 2018 | Refactored into a class-based system.
 *
 */
class SWP_CURL {

	public static function fetch_shares_via_curl_multi( $data, $options = array() ) {

		// array of curl handles
		$curly = array();
		// data to be returned
		$result = array();

		// multi handle
		$mh = curl_multi_init();

		// loop through $data and create curl handles
		// then add them to the multi-handle
		if( is_array( $data ) ):
			foreach ( $data as $id => $d ) :

				if ( $d !== 0 || $id == 'google_plus' ) :

					$curly[ $id ] = curl_init();

					if ( $id == 'google_plus' ) :

						curl_setopt( $curly[ $id ], CURLOPT_URL, 'https://clients6.google.com/rpc' );
						curl_setopt( $curly[ $id ], CURLOPT_POST, true );
						curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $curly[ $id ], CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"' . rawurldecode( $d ) . '","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
						curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER, true );
						curl_setopt( $curly[ $id ], CURLOPT_HTTPHEADER, array( 'Content-type: application/json' ) );

					else :

						$url = (is_array( $d ) && ! empty( $d['url'] )) ? $d['url'] : $d;
						curl_setopt( $curly[ $id ], CURLOPT_URL,            $url );
						curl_setopt( $curly[ $id ], CURLOPT_HEADER,         0 );
						curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER, 1 );
						curl_setopt( $curly[ $id ], CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
						curl_setopt( $curly[ $id ], CURLOPT_FAILONERROR, 0 );
						curl_setopt( $curly[ $id ], CURLOPT_FOLLOWLOCATION, 0 );
						curl_setopt( $curly[ $id ], CURLOPT_RETURNTRANSFER,1 );
						curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYPEER, false );
						curl_setopt( $curly[ $id ], CURLOPT_SSL_VERIFYHOST, false );
						curl_setopt( $curly[ $id ], CURLOPT_TIMEOUT, 5 );
						curl_setopt( $curly[ $id ], CURLOPT_CONNECTTIMEOUT, 5 );
						curl_setopt( $curly[ $id ], CURLOPT_NOSIGNAL, 1 );
						curl_setopt( $curly[ $id ], CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
						// curl_setopt($curly[$id], CURLOPT_SSLVERSION, CURL_SSLVERSION_SSLv3);
					endif;

					// extra options?
					if ( ! empty( $options ) ) {
						curl_setopt_array( $curly[ $id ], $options );
					}

					curl_multi_add_handle( $mh, $curly[ $id ] );

				endif;
			endforeach;
		endif;

		  // execute the handles
		  $running = null;
		do {
			curl_multi_exec( $mh, $running );
		} while ($running > 0);

		  // get content and remove handles
		foreach ( $curly as $id => $c ) {
			$result[ $id ] = curl_multi_getcontent( $c );
			curl_multi_remove_handle( $mh, $c );
		}

		  // all done
		  curl_multi_close( $mh );

          if( true == _swp_is_debug('show_share_data') ) :
              echo "<pre>";
              var_dump($result);
              echo "</pre>";
          endif;
          
		  return $result;
	}

	public function file_get_contents_curl( $url ) {
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
		curl_setopt( $ch, CURLOPT_FAILONERROR, 0 );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1 );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 5 );
		curl_setopt( $ch, CURLOPT_NOSIGNAL, 1 );
		curl_setopt( $ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
		$cont = @curl_exec( $ch );
		$curl_errno = curl_errno( $ch );
		curl_close( $ch );
		if ( $curl_errno > 0 ) {
			return false;
		}
		return $cont;
	}
}
