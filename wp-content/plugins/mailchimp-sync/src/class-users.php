<?php

namespace MC4WP\Sync;

use WP_User;
use WP_User_Query;
use Exception;

/**
 * Class UserRepository
 *
 * @package MC4WP\Sync
 */
class Users {

	private $list_id = '';

	/**
	 * @var string
	 */
	private $meta_key = '';

	/**
	 * @var string
	 */
	private $role = '';

	/**
	 * @var array
	 */
	private $field_map = array();

	/**
	 * @var bool
	 */
	private $user_control;

	/**
	 * @var Tools
	 */
	private $tools;

	/**
	 * @param string $meta_key
	 * @param string $role
	 * @param array $field_map
	 */
	public function __construct( $list_id, $role = '', $field_map = array(), $user_control = false ) {
		$this->list_id = $list_id;
		$this->meta_key = 'mailchimp_sync_' . $list_id;
		$this->role = $role;
		$this->field_map = $field_map;
		$this->user_control = $user_control;

		$this->tools = new Tools();
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	public function get( $args = array() ) {
		if( empty( $args['role'] ) ) {
			$args['role'] = $this->role;
		}

		$user_query = new WP_User_Query( $args );
		return $user_query->get_results();
	}

	/**
	 *
	 * @return int
	 */
	public function count() {
		global $wpdb;
		
		$sql = "SELECT COUNT(u.ID) FROM $wpdb->users u";
		$params = array();
		$prefix = is_multisite() ? $wpdb->get_blog_prefix( get_current_blog_id() ) : $wpdb->prefix;
	
		if( '' !== $this->role ) {
			$sql .= " INNER JOIN $wpdb->usermeta um2 ON um2.user_id = u.ID AND um2.meta_key = %s AND um2.meta_value LIKE %s";
			$params[] = $prefix . 'capabilities';
			$params[] = '%%' . $this->role . '%%';
		} 

		if( is_multisite() ) {
			$sql .= " RIGHT JOIN {$wpdb->usermeta} um4 ON um4.user_id = u.ID AND um4.meta_key = %s";
			$params[] = $prefix . 'capabilities';
		}

		$sql .= ' WHERE 1=1';
		if( $this->user_control ) {
			$sql .= " AND NOT EXISTS( SELECT * FROM {$wpdb->usermeta} um3 WHERE um3.user_id = u.ID AND um3.meta_key = %s AND um3.meta_value = '0' )";
			$params[] = $this->get_meta_key_for_optin_status();
		}

		// now get number of users with meta key
		$query = empty( $params ) ? $sql : $wpdb->prepare( $sql, $params );
		$count = $wpdb->get_var( $query );
		return (int) $count;
	}

	/**
	 * @param string $mailchimp_id
	 *
	 * @return WP_User|null;
	 */
	public function get_user_by_mailchimp_id( $mailchimp_id ) {
		$args = array(
			'meta_key'     => $this->meta_key,
			'meta_value'   => $mailchimp_id,
		);

		return $this->get_first_user( $args );
	}

//	/**
//	 * @param string $email
//	 * @return WP_User|null
//	 */
//	public function get_user_by_email( $email ) {
//		$args = array(
//			'search' => $email,
//			'search_columns' => array( 'email' ),
//		);
//
//		return $this->get_first_user( $args );
//	}

	/**
	 * @return WP_User
	 */
	public function get_current_user() {
		return wp_get_current_user();
	}

	/**
	 * @param array $args
	 *
	 * @return null|WP_User
	 */
	public function get_first_user( array $args = array() ) {
		$args['number'] = 1;
		$users = $this->get( $args );

		if( empty( $users ) ) {
			return null;
		}

		return $users[0];
	}

	/**
	 * TODO: Run filter on result
	 *
	 * @return int
	 */
	public function count_subscribers() {
		global $wpdb;
		
		$sql = "SELECT COUNT(u.ID) FROM $wpdb->users u INNER JOIN $wpdb->usermeta um1 ON um1.user_id = u.ID AND um1.meta_key = %s";
		$params = array( $this->meta_key );
		$prefix = is_multisite() ? $wpdb->get_blog_prefix( get_current_blog_id() ) : $wpdb->prefix;
	
		if( ! empty( $this->role ) ) {
			$sql .= " INNER JOIN $wpdb->usermeta um2 ON um2.user_id = u.ID AND um2.meta_key = %s AND um2.meta_value LIKE %s";
			$params[] = $prefix . 'capabilities';
			$params[] = '%%' . $this->role . '%%';
		} 

		if( is_multisite() ) {
			$sql .= " RIGHT JOIN {$wpdb->usermeta} um4 ON um4.user_id = u.ID AND um4.meta_key = %s";
			$params[] = $prefix . 'capabilities';
		}

		$sql .= ' WHERE 1=1';
		if( $this->user_control ) {
			$sql .= " AND NOT EXISTS( SELECT * FROM {$wpdb->usermeta} um3 WHERE um3.user_id = u.ID AND um3.meta_key = %s AND um3.meta_value = '0' )";
			$params[] = $this->get_meta_key_for_optin_status();
		}

		$query = $wpdb->prepare( $sql, $params );
		$subscriber_count = $wpdb->get_var( $query );
		return (int) $subscriber_count;
	}

    /**
     * @param int|WP_User $user_id
     * @return int
     */
	public function id( $user_id ) {
        if( $user_id instanceof WP_User ) {
            $user_id = $user_id->ID;
        }

        return $user_id;
    }

	/**
	 * @param int|WP_User $user
	 * @return WP_User
	 *
	 * @throws Exception
	 */
	public function user( $user ) {

		if( ! is_object( $user ) ) {
			$user = get_user_by( 'id', $user );
		}

		if( ! $user instanceof WP_User ) {
			throw new Exception( sprintf( 'Invalid user ID: %d', $user ) );
		}

		return $user;
	}

	/**
	 * @return string
	 */
	public function get_meta_key_for_optin_status() {
		return sprintf( 'mailchimp_sync_%s_opted_in', $this->list_id );
	}

	/**
	 * @param  int|WP_User $user
	 * @return boolean
	 */
	public function get_optin_status( $user, $default = true ) {
		$user_id = $this->id( $user );
		$meta_key = $this->get_meta_key_for_optin_status();
		$opted_in = get_user_meta( $user_id, $meta_key, true );

		if( $opted_in !== null && strlen($opted_in) > 0 ) {
			return $opted_in !== "0";
		}

		return $default;
	}

	/**
	 * @param int|WP_User $user
	 * @param boolean $status
	 */
	public function set_optin_status( $user, $status ) {
		$user_id = $this->id( $user );
		$meta_key = $this->get_meta_key_for_optin_status();
		update_user_meta( $user_id, $meta_key, $status ? "1" : "0" );
	}

	/**
	 * @param WP_User $user
	 *
	 * @return bool
	 */
	public function should( WP_User $user ) {
		$sync = true;

		// if role is set, make sure user has that role or don't sync
		if( ! empty( $this->role ) && ! in_array( $this->role, $user->roles ) ) {
			$sync = false;
		}

		/**
		 * Filters whether a user should be synchronized with MailChimp or not.
		 *
		 * @param boolean $sync
		 * @param WP_User $user
		 */
		return (bool) apply_filters( 'mailchimp_sync_should_sync_user', $sync, $user );
	}

    /**
     * @param int|WP_User $user_id
     */
    public function touch( $user_id ) {
        $user_id = $this->id( $user_id );
        update_user_meta( $user_id, 'mc4wp_sync_last_updated', date( 'c' ) );
    }


    /**
     * @param int $user_id
     * @param string $email_address
     */
    public function set_mailchimp_email_address( $user_id, $email_address ) {
        $user_id = $this->id( $user_id );
        update_user_meta( $user_id, 'mc4wp_sync_remote_email_address', $email_address );
    }

    /**
     * @param int $user_id
     */
    public function delete_mailchimp_email_address( $user_id ) {
        $user_id = $this->id( $user_id );
        delete_user_meta( $user_id, 'mc4wp_sync_remote_email_address' );
    }

    /**
     * @param int $user_id
     * @return string
     */
    public function get_mailchimp_email_address( $user_id ) {
        $user_id = $this->id( $user_id );
        $email_address = get_user_meta( $user_id, 'mc4wp_sync_remote_email_address', true );
        return is_string( $email_address ) ? $email_address : '';
    }

    /**
     * @param int $user_id
     * @return bool
     */
    public function is_synced( $user_id ) {
        // check for new email meta first
        $email_address = $this->get_mailchimp_email_address( $user_id );
        if( ! empty( $email_address ) ) {
            return true;
        }

        // then check old subscriber uid
        $subscriber_uid = $this->get_subscriber_uid( $user_id );
        return ! empty( $subscriber_uid );
    }

    /**
     * @param int $user_id
     * @return string
     */
    public function get_subscriber_uid( $user_id ) {
        $user_id = $this->id( $user_id );
        $subscriber_uid = get_user_meta( $user_id, $this->meta_key, true );
        return is_string( $subscriber_uid ) ? $subscriber_uid : '';
    }

	/**
	 * @param int $user_id
	 */
	public function set_subscriber_uid( $user_id, $subscriber_uid ) {
        $user_id = $this->id( $user_id );
		update_user_meta( $user_id, $this->meta_key, $subscriber_uid );
	}

	/**
	 * @param int $user_id
	 */
	public function delete_subscriber_uid( $user_id ) {
        $user_id = $this->id( $user_id );
		delete_user_meta( $user_id, $this->meta_key );
	}

	/**
	 * @return string
	 */
	public function get_meta_key() {
		return $this->meta_key;
	}

	/**
	 * @param WP_User $user
	 *
	 * @return array
	 */
	public function get_user_merge_fields( WP_User $user ) {
		$merge_fields = array();

		if( ! empty( $user->first_name ) ) {
            $merge_fields['FNAME'] = $user->first_name;
		}

		if( ! empty( $user->last_name ) ) {
            $merge_fields['LNAME'] = $user->last_name;
		}

		if( ! empty( $user->first_name ) && ! empty( $user->last_name ) ) {
            $merge_fields['NAME'] = sprintf( '%s %s', $user->first_name, $user->last_name );
		}

		// Do we have mapping rules for user fields to mailchimp fields?
		if( ! empty( $this->field_map ) ) {

			// loop through mapping rules
			foreach( $this->field_map as $rule ) {
				// skip broken settings
				if( empty( $rule['mailchimp_field'] ) || empty( $rule['user_field'] ) ) {
					continue;
				}

				// get field value
				$value = $this->tools->get_user_field( $user, $rule['user_field'] );

				if( is_string( $value ) && ! empty( $value ) ) {
                    $merge_fields[ $rule['mailchimp_field'] ] = $value;
				}
			}
		}

        /** @ignore @deprecated */
        $merge_fields = (array) apply_filters( 'mailchimp_sync_user_data', $merge_fields, $user );

		return $merge_fields;
	}

}
