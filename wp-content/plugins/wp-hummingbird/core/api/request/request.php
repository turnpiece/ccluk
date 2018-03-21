<?php
/**
 * Class WP_Hummingbird_API_Request
 */

abstract class WP_Hummingbird_API_Request {

	/**
	 * API Key
	 *
	 * @var string
	 */
	private $api_key = '';

	/**
	 * Module action path
	 *
	 * @var string
	 */
	private $path = '';

	/**
	 * @var null|WP_Hummingbird_API_Service
	 */
	private $service = null;

	/**
	 * Request Method
	 *
	 * @var string
	 */
	private $method = 'POST';

	/**
	 * Request max timeout
	 *
	 * @var int
	 */
	private $timeout = 15;

	/**
	 * Header arguments
	 *
	 * @var array
	 */
	private $headers = array();

	/**
	 * POST arguments
	 *
	 * @var array
	 */
	private $post_args = array();

	/**
	 * GET arguments
	 *
	 * @var array
	 */
	private $get_args = array();

	private $logger;

	/**
	 * WP_Hummingbird_API_Request constructor.
	 *
	 * @param WP_Hummingbird_API_Service $service
	 *
	 * @throws WP_Hummingbird_API_Exception
	 */
	public function __construct( $service ) {
		$this->logger = new WP_Hummingbird_Logger( 'api' );

		if ( ! $service instanceof WP_Hummingbird_API_Service ) {
			throw new WP_Hummingbird_API_Exception( __( 'Wrong Service. $service must be an instance of WP_Hummingbird_API_Service', 'wphb' ), 404 );
		}

		$this->service = $service;
	}

	public function get_service() {
		return $this->service;
	}

	/**
	 * Set the Request API Key
	 *
	 * @param string $api_key
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	public function set_timeout( $timeout ) {
		$this->timeout = $timeout;
	}

	/**
	 * Add a new request argument for POST requests
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function add_post_argument( $name, $value ) {
		$this->post_args[ $name ] = $value;
	}

	/**
	 * Add a new request argument for GET requests
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function add_get_argument( $name, $value ) {
		$this->get_args[ $name ] = $value;
	}

	/**
	 * Add a new request argument for GET requests
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function add_header_argument( $name, $value ) {
		$this->headers[ $name ] = $value;
	}

	/**
	 * Get the Request URL
	 *
	 * @param string $path Endpoint route
	 * @return mixed
	 */
	abstract public function get_api_url( $path = '' );

	/**
	 * Make a GET API Call
	 *
	 * @param string $path Endpoint route
	 * @param array() $data
	 *
	 * @return mixed
	 */
	public function get( $path, $data = array() ) {
		try {
			$result = $this->request( $path, $data, 'get' );
			return $result;
		} catch ( WP_Hummingbird_API_Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Make a GET API Call
	 *
	 * @param string $path Endpoint route
	 * @param array() $data
	 *
	 * @return mixed
	 */
	public function post( $path, $data = array() ) {
		try {
			$result = $this->request( $path, $data, 'post' );
			return $result;
		} catch ( WP_Hummingbird_API_Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	public function patch( $path, $data = array() ) {
		try {
			$result = $this->request( $path, $data, 'patch' );
			return $result;
		} catch ( WP_Hummingbird_API_Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Make a GET API Call
	 *
	 * @param string $path Endpoint route
	 * @param array() $data
	 *
	 * @return mixed
	 */
	public function head( $path, $data = array() ) {
		try {
			$result = $this->request( $path, $data, 'head' );
			return $result;
		} catch ( WP_Hummingbird_API_Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}

	}

	/**
	 * Make a GET API Call
	 *
	 * @param string $path Endpoint route
	 * @param array() $data
	 *
	 * @return mixed
	 */
	public function delete( $path, $data = array() ) {
		try {
			$result = $this->request( $path, $data, 'delete' );
			return $result;
		} catch ( WP_Hummingbird_API_Exception $e ) {
			return new WP_Error( $e->getCode(), $e->getMessage() );
		}
	}

	/**
	 * Make an API Request
	 *
	 * @since 1.8.1 Timeout for non-blocking changed from 0.1 to 2 seconds.
	 *
	 * @param $path
	 * @param array $data
	 * @param string $method
	 *
	 * @return array|mixed|object
	 * @throws WP_Hummingbird_API_Exception
	 */
	public function request( $path, $data = array(), $method = 'post' ) {
		$url = $this->get_api_url( $path );

		$this->sign_request();

		$url = add_query_arg( $this->get_args, $url );
		if ( 'post' != $method && 'patch' != $method && 'delete' != $method ) {
			$url = add_query_arg( $data, $url );
		}

		$args = array(
			'headers'   => $this->headers,
			'sslverify' => false,
			'method'    => strtoupper( $method ),
			'timeout'   => $this->timeout,
		);

		if ( ! $args['timeout'] || 2 === $args['timeout'] ) {
			$args['blocking'] = false;
		}

		$this->logger->log( "WPHB API: Sending request to {$url}" );
		$this->logger->log( 'WPHB API: Arguments:' );
		$this->logger->log( $args );

		switch ( strtolower( $method ) ) {
			case 'patch':
			case 'delete':
			case 'post':
				if ( is_array( $data ) ) {
					$args['body'] = array_merge( $data, $this->post_args );
				} else {
					$args['body'] = $data;
				}

				$response = wp_remote_post( $url, $args );
				break;
			case 'head':
				$response = wp_remote_head( $url, $args );
				break;
			case 'get':
				$response = wp_remote_get( $url, $args );
				break;
			default:
				$response = wp_remote_request( $url, $args );
				break;
		}

		$this->logger->log( 'WPHB API: Response:' );
		$this->logger->log( $response );

		return $response;
	}

	protected function sign_request() {}

}