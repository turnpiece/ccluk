<?php

namespace WP_Defender\Module\Audit\Model;

use Hammer\Helper\WP_Helper;
use WP_Defender\Behavior\Utils;
use WP_Defender\Module\Audit\Component\Audit_API;
use function foo\func;

class Events extends \Hammer\WP\Settings {
	private static $_instance;
	/**
	 * The active data
	 * @var array
	 */
	public $data = [];
	/**
	 * Last data, use for backup
	 * @var array
	 */
	public $old_data = [];
	/**
	 * This is pending to on cloud
	 * @var array
	 */
	public $eventsPending = [];
	/**
	 * Timestamp last sync
	 * @var int
	 */
	public $lastSync;
	/**
	 * Date from
	 * @var
	 */
	public $lastSyncFrom;

	/**
	 * @return Events
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			$class           = new Events( 'wd_audit_cached', WP_Helper::is_network_activate( wp_defender()->plugin_slug ) );
			self::$_instance = $class;
		}

		return self::$_instance;
	}

	public function __construct( $id, $isMulti ) {
		parent::__construct( $id, $isMulti );
	}

	/**
	 * @param array $params
	 *
	 * @return int
	 */
	public function hasData( $params = [] ) {
		if ( ! is_countable( $this->data ) ) {
			//fail safe
			return false;
		}
		/**
		 * if params empty means we query for summary, which mostly cached
		 */
		if ( empty( $params ) && is_countable( $this->data ) ) {
			return count( $this->data );
		}
		/**
		 * because we only cached for 2 months, so if user pick older there wont be data
		 * return from API instead
		 */
		if ( is_countable( $this->data ) ) {

			//if the timestamp smaller date from value, means we will have to pull the stuff from API
			if ( strtotime( $params['date_from'] ) > strtotime( $this->lastSyncFrom ) ) {
				return true;
			}
		}

		//fail safe
		return false;
	}

	/**
	 *  This will fetch data from cloud and store on local
	 */
	public function fetch() {
		//we will fetch the data of recent 3 months, the cached will be pulled daily
		//if user fetch things that we don't have in local cache, fetch and merge
		$date_format = 'Y-m-d H:i:s';
		$args        = [
			'date_from' => date( $date_format, strtotime( 'midnight', strtotime( '-30 days', current_time( 'timestamp' ) ) ) ),
			'date_to'   => date( $date_format, strtotime( 'tomorrow', current_time( 'timestamp' ) ) ),
		];
		Utils::instance()->log( sprintf( 'Fetching data from %s to %s to local', $args['date_from'], $args['date_to'] ) );
		$data = Audit_API::pullLogs( $args, 'timestamp', 'desc', true );

		if ( is_wp_error( $data ) ) {
			Utils::instance()->log( sprintf( 'Fetch error: %s', $data->get_error_message() ) );

			return;
		}
		if ( is_array( $data ) && $data['status'] == 'success' ) {
			//backup all the old time
			$this->old_data     = array_merge( $this->data, $this->eventsPending );
			$this->data         = $data['data'];
			$this->lastSync     = time();
			$this->lastSyncFrom = $args['date_from'];
			Utils::instance()->log( sprintf( 'Fetched done. %s records', count( $data['data'] ) ) );
		}
		$this->save();
	}

	/**
	 *
	 *
	 * @param array $filter
	 * @param string $order_by
	 * @param string $order
	 * @param bool $nopaging
	 *
	 * @return array
	 */
	public function getData( $filter = array(), $order_by = 'timestamp', $order = 'desc', $nopaging = false ) {
		$data = $this->filterData( array_merge( $this->data, $this->eventsPending ), $filter );
		usort( $data, function ( $a, $b ) use ( $order, $order_by ) {

			if ( $order == 'desc' ) {
				return intval( $b[ $order_by ] ) > intval( $a[ $order_by ] );
			} else {
				return intval( $a[ $order_by ] ) > intval( $b[ $order_by ] );
			}
		} );

		$per_page = 40;

		return [
			'data'        => $data,
			'total_items' => count( $data ),
			'total_pages' => ceil( count( $data ) / $per_page ),
			'per_page'    => $per_page
		];
	}

	/**
	 * Submit all the pending to cloud
	 */
	public function sendToApi() {
		if ( empty( $this->eventsPending ) ) {
			return;
		}
		//upload data to cloud first
		Utils::instance()->log( sprintf( 'Preparing submit %d events to cloud', count( $this->eventsPending ) ) );
		Audit_API::openSocket();
		if ( Audit_API::socketToAPI( $this->eventsPending ) == false ) {
			Audit_API::curlToAPI( $this->eventsPending );
		}
		Utils::instance()->log( sprintf( 'Submitted %d events to cloud', count( $this->eventsPending ) ) );
		//flushed
		$this->eventsPending = [];
		$this->save();
	}

	public function checksumData() {
		if ( count( $this->data ) == count( $this->old_data ) ) {
			Utils::instance()->log( 'Checksum verified!' );

			return true;
		}
		//log it
	}

	/**
	 * @param $data
	 * @param array $filter
	 *
	 * @return array
	 */
	private function filterData( $data, $filter = [] ) {
		/**
		 * data can be filter from
		 * date range
		 * user_id
		 * event type
		 * ip
		 * context
		 * action type
		 */
		$date_from = strtotime( $filter['date_from'] );
		$date_to   = strtotime( $filter['date_to'] );
		foreach ( $data as $i => $item ) {
			if ( $item['timestamp'] < $date_from || $item['timestamp'] > $date_to ) {
				unset( $data[ $i ] );
				continue;
			}

			if ( ! empty( $filter['event_type'] ) && ! in_array( $item['event_type'], $filter['event_type'] ) ) {
				unset( $data[ $i ] );
				continue;
			}

			if ( ! empty( $filter['user_id'] ) && $item['user_id'] != $filter['user_id'] ) {
				unset( $data[ $i ] );
				continue;
			}

			if ( ! empty( $filter['ip'] ) && ! stristr( $item['ip'], $filter['ip'] ) ) {
				unset( $data[ $i ] );
				continue;
			}

		}

		return $data;
	}

	/**
	 * @param $events
	 */
	public function append( $events ) {
		Utils::instance()->log( var_export( $events, true ), 'settings' );
		$this->eventsPending = array_merge( $this->eventsPending, $events );
		$this->save();
	}
}