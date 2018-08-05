<?php // phpcs:ignore
/**
 * Snapshot Utility class
 *
 * @since 2.5
 *
 * @package Snapshot
 * @subpackage Helper
 */

if ( ! class_exists( 'Snapshot_Helper_Utility' ) ) {

	class Snapshot_Helper_Utility {

		const MIN_VIABLE_EXEC_TIME = 30;

		/**
		 * Get the database name for multisites.
		 *
		 * @return string
		 */
		public static function get_db_name() {

			global $wpdb;

			$db_class = get_class( $wpdb );

			if ( "m_wpdb" === $db_class ) {

				$test_sql   = "SELECT ID FROM " . $wpdb->prefix . "posts LIMIT 1";
				$query_data = $wpdb->analyze_query( $test_sql );
				if ( isset( $query_data['dataset'] ) ) {

					global $db_servers;
					if ( isset( $db_servers[ $query_data['dataset'] ][0]['name'] ) ) {
						return $db_servers[ $query_data['dataset'] ][0]['name'];
					}
				}
			} else {
				return DB_NAME;
			}
		}


		/**
		 * Utility function to grab the array of database tables for the site. This function
		 * is multisite aware in that is only grabs tables within the site's table prefix
		 * for example if on a multisite install the table prefix is wp_2_ then all other
		 * tables 'wp_' and 'wp_x_' will be ignored.
		 *
		 * The functions builds a multi array. On node of the array [wp]  will be the
		 * core WP tables. Another node [non] will be tables within that site which
		 * are not core tables. This could be table created by other plugins.
		 *
		 * @since 1.0.0
		 * @see wp_upload_dir()
		 *
		 * @param int $blog_id
		 * @param int $site_id
		 *
		 * @return array $tables multi-dimensional array of tables.
		 */
		public static function get_database_tables( $blog_id = 0, $site_id = 1 ) {

			global $wpdb;

			if ( ( ! $blog_id ) || ( 0 === $blog_id ) ) {
				$blog_id = $wpdb->blogid;
			}

			if ( is_multisite() ) {
				$blog_tables = get_site_transient( 'snapshot-blog-tables-' . $blog_id );
				if ( $blog_tables ) {
					return $blog_tables;
				}
			}

			$tables           = array();
			$tables['global'] = array();
			$tables['wp']     = array();
			$tables['non']    = array();
			$tables['other']  = array();
			$tables['error']  = array();
			$tables_all       = array();

			$blog_prefixes         = array();
			$blog_prefixes_lengths = array();

			$old_blog_id = $wpdb->blogid;

			if ( ( $blog_id ) && ( $blog_id !== $wpdb->blogid ) ) {
				$wpdb->set_blog_id( $blog_id );
			}

			if ( is_multisite() ) {

				$db_name = self::get_db_name();
				if ( ! $db_name ) {
					$db_name = DB_NAME;
				}
				$wpdb_prefix = str_replace( '_', '\_', $wpdb->prefix );

				if ( $wpdb->prefix === $wpdb->base_prefix ) {
					// Under Multisite and when the prefix and base prefox match we assume this is the primary site.
					// For example the default base prefix is 'wp_' and for the primary site the tables all start as 'wp_???'
					// for secondary site the prefix will be something like 'wp_2_', 'wp_3_'. So on the primary site tables
					// we cannot simply look for all tables starting with 'wp_' because this will include all sites. So
					// we use some MySQL REGEX to exclude matches.
					/*
					$show_all_tables_sql = "SELECT table_name FROM information_schema.tables
											WHERE table_schema = '". $db_name ."'
											AND table_name LIKE '". $wpdb->prefix ."%'
											AND table_name NOT REGEXP '^". $wpdb_prefix ."[[:digit:]]+\_'";
					*/
					$table_placeholder = "^{$wpdb_prefix}[[:digit:]]+\_";
					$tables_all_rows = $wpdb->query( $wpdb->prepare( "SELECT table_name FROM information_schema.tables
									WHERE table_schema = %s
									AND table_name NOT REGEXP %s", $db_name, $table_placeholder ) );

				} else {
					$table_placeholder = "{$wpdb_prefix}";
					$tables_all_rows = $wpdb->query( $wpdb->prepare( "SELECT table_name FROM information_schema.tables
									WHERE table_schema = %s
									AND table_name LIKE %s", $db_name, $table_placeholder . '%' ) );
				}
			} else {
				$db_name     = DB_NAME;
				$wpdb_prefix = str_replace( '_', '\_', $wpdb->prefix );

				/*
						$show_all_tables_sql = "SELECT table_name FROM information_schema.tables
												WHERE table_schema = '". $db_name ."'
												AND table_name LIKE '". $wpdb_prefix ."%'";
				*/
				$tables_all_rows = $wpdb->query( $wpdb->prepare( "SELECT table_name FROM information_schema.tables
								WHERE table_schema = %s", $db_name ) );
			}

				if ( $tables_all_rows ) {
					foreach ( $wpdb->last_result as $table_set ) {
						foreach ( $table_set as $table_name ) {
							// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
							$table_structure = $wpdb->query( esc_sql( "DESCRIBE `{$table_name}`; " ) );
							if ( empty( $table_structure ) ) {
								continue;
							}
							$tables_all[ $table_name ] = $table_name;
						}
					}
				}

			if ( count( $tables_all ) ) {

				// Get a list of all WordPress known tables for the selected blog_id
				$tables_wp = $wpdb->tables( 'all' );
				//echo "tables_wp<pre>"; print_r($tables_wp); echo "</pre>";

				// The 'non' tables are the difference between the all and wp table sets
				$tables['non'] = array_diff( $tables_all, $tables_wp );
				$tables['wp']  = array_intersect( $tables_all, $tables_wp );

				foreach ( $tables['non'] as $_idx => $table ) {
					if ( substr( $table, 0, 3 ) !== "wp_" ) {
						$tables['other'][ $_idx ] = $table;
						unset( $tables['non'][ $_idx ] );
					}
				}

				if ( is_multisite() ) {
					if ( ! is_main_site( $blog_id ) ) {
						if ( ( isset( $wpdb->global_tables ) ) && ( count( $wpdb->global_tables ) ) ) {
							foreach ( $wpdb->global_tables as $global_table ) {
								$table_name = $wpdb->base_prefix . $global_table;
								//echo "table_name[". $table_name ."]<br />";
								$tables['global'][ $table_name ] = $table_name;
							}
						}
					}
				}
				//echo "tables<pre>"; print_r($tables); echo "</pre>";
			}

			// Now for each set set want to strip off the table prefix from the name
			// so when they are displayed they take up less room.

			if ( isset( $tables['global'] ) ) {
				if ( count( $tables['global'] ) ) {
					ksort( $tables['global'] );
				}
			}

			if ( isset( $tables['wp'] ) ) {
				if ( count( $tables['wp'] ) ) {
					ksort( $tables['wp'] );
				}
			}

			if ( isset( $tables['non'] ) ) {
				if ( count( $tables['non'] ) ) {
					ksort( $tables['non'] );
				}
			}

			if ( isset( $tables['other'] ) ) {
				if ( count( $tables['other'] ) ) {
					ksort( $tables['other'] );
				}
			}

			//echo "tables<pre>"; print_r($tables); echo "</pre>";

			if ( $old_blog_id !== $wpdb->blogid ) {
				$wpdb->set_blog_id( $old_blog_id );
			}

			if ( is_multisite() ) {
				set_transient( 'snapshot-blog-tables-' . $blog_id, $tables, 300 );
			}

			return $tables;
		}

		/**
		 * @param $table_name
		 * @param int $blog_id
		 * @param int $site_id
		 *
		 * @return mixed
		 */
		public static function get_table_meta( $table_name, $blog_id = 0, $site_id = 1 ) {
			global $wpdb;

			if ( ( $blog_id ) && ( $blog_id !== $wpdb->blogid ) ) {
				$wpdb->set_blog_id( $blog_id );
			}

			if ( is_multisite() ) {
				$db_name = self::get_db_name();
				if ( ! $db_name ) {
					$db_name = DB_NAME;
				}
				$wpdb_prefix = str_replace( '_', '\_', $wpdb->prefix );
			} else {
				$db_name     = DB_NAME;
				$wpdb_prefix = str_replace( '_', '\_', $wpdb->prefix );
			}

			$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM information_schema.TABLES WHERE table_schema = %s AND table_name = %s", $db_name, $table_name ) );

			//echo "result<pre>"; print_r($result); echo "</pre>";
			return $result;
		}

		/**
		 * Utility function to determine all blogs under a Multisite install
		 *
		 * @since 1.0.2
		 *
		 * @param bool $show_counts_only
		 *
		 * @return array|bool|int|mixed
		 */
		public static function get_blogs( $show_counts_only = false ) {

			global $wpdb;

			$archived = '0';
			$spam = 0;
			$deleted = 0;

			if ( true === $show_counts_only ) {

				$result = $wpdb->get_row( $wpdb->prepare( "SELECT count(blog_id) as blogs_count FROM $wpdb->blogs WHERE archived = %s AND spam = %d AND deleted = %d", $archived, $spam, $deleted ) );
				if ( isset( $result->blogs_count ) ) {
					return $result->blogs_count;
				} else {
					return 0;
				}
			} else {
				$blogs = wp_cache_get( 'snapshot-blogs', 'snapshot-plugin' );
				if ( $blogs ) {
					return $blogs;
				}

				if ( ( is_multisite() ) && ( is_network_admin() ) ) {

					$blog_ids = $wpdb->get_col( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE archived = %s AND spam = %d AND deleted = %d", $archived, $spam, $deleted ) );
					//echo "blog_ids<pre>"; print_r($blog_ids); echo "</pre>";
					if ( $blog_ids ) {
						$blogs = array();
						foreach ( $blog_ids as $blog_id ) {
							$blogs[ $blog_id ] = get_blog_details( $blog_id );
						}
						wp_cache_set( 'snapshot-blogs', $blogs, 'snapshot-plugin' );

						return $blogs;
					}
				}
			}
		}


		/**
		 * Utility function to generate an 8 character checksum for a filename. This is to make the filename unique.
		 *
		 * @since 1.0.2
		 *
		 * @param $file
		 *
		 * @return int|string
		 */
		public static function get_file_checksum( $file ) {

			$checksum = '';
			if ( function_exists( 'sha1_file' ) ) {
				$checksum = sha1_file( $file );
			} else {
				$checksum = rand( 8, 8 );
			}

			if ( ! $checksum ) {
				$checksum = "00000000";
			}

			if ( ( $checksum ) && ( strlen( $checksum ) > 8 ) ) {
				$checksum = substr( $checksum, 0, 8 );
			}

			return $checksum;
		}


		/**
		 * Get current blog site's theme (Works on single and multisite).
		 *
		 * Uses 'template' option for WP 3.4+ and 'current_theme' for older versions.
		 *
		 * @param int $blog_id
		 *
		 * @return mixed|void
		 */
		public static function get_current_theme( $blog_id = 0 ) {

			if ( is_multisite() ) {
				$current_theme = get_blog_option( $blog_id, 'template' );
				if ( empty ( $current_theme ) ) {
					$current_theme = get_blog_option( $blog_id, 'current_theme' );
				}
			} else {
				$current_theme = get_option( 'template' );
				if ( empty ( $current_theme ) ) {
					$current_theme = get_option( 'current_theme' );
				}
			}

			return $current_theme;
		}

		/**
		 * Utility function to get a list of allowed/active theme for the site.
		 *
		 * @since 1.0.0
		 *
		 * @param int $blog_id
		 *
		 * @return Array
		 */
		public static function get_blog_active_themes( $blog_id = 0 ) {

			// Get All themes in the system.
			$themes_all = wp_get_themes();

			/* The get_themes returns an unusable array. So we need to rework it to be able to
			   compare to the array returned from allowedthemes */
			foreach ( $themes_all as $themes_all_key => $themes_all_set ) {
				unset( $themes_all[ $themes_all_key ] );
				$themes_all[ $themes_all_set['Stylesheet'] ] = $themes_all_set['Name'];
			}

			if ( is_multisite() ) {

				//$allowed_themes = wpmu_get_blog_allowedthemes( $blog_id );
				$allowed_themes = WP_Theme::get_allowed_on_site( $blog_id );

				$themes_blog = get_blog_option( $blog_id, 'allowedthemes' );
				if ( ! $themes_blog ) {
					$themes_blog = array();
				}

				//$site_allowed_themes = get_site_allowed_themes();
				$site_allowed_themes = WP_Theme::get_allowed_on_network();
				if ( ! $site_allowed_themes ) {
					$site_allowed_themes = array();
				}

				$themes_blog = array_merge( $themes_blog, $site_allowed_themes );

				if ( ( isset( $themes_blog ) ) && ( isset( $themes_all ) ) ) {
					foreach ( $themes_all as $themes_all_key => $themes_all_name ) {
						if ( ! isset( $themes_blog[ $themes_all_key ] ) ) {
							unset( $themes_all[ $themes_all_key ] );
						}
					}
					//echo "themes_all<pre>"; print_r($themes_all); echo "</pre>";
					asort( $themes_all );

					return $themes_all;
				}

			} else {
				return $themes_all;
			}
		}

		/**
		 * Get the user's name.
		 *
		 * First try display_name, then user_nicename else user_login.
		 *
		 * @since 1.0.0
		 *
		 * @param $user_id
		 *
		 * @return string
		 */
		public static function get_user_name( $user_id ) {

			$user_name = get_the_author_meta( 'display_name', intval( $user_id ) );

			if ( ! $user_name ) {
				$user_name = get_the_author_meta( 'user_nicename', intval( $user_id ) );
			}

			if ( ! $user_name ) {
				$user_name = get_the_author_meta( 'user_login', intval( $user_id ) );
			}

			return $user_name;
		}

		/**
		 * Utility function to check if we can adjust the server PHP timeout
		 *
		 * @since 1.0.0
		 * @return bool
		 */
		public static function check_server_timeout() {
			$current_timeout = ini_get( 'max_execution_time' );
			$current_timeout = intval( $current_timeout );

			// If the max execution time is zero (means no timeout). We leave it.
			if ( 0 === $current_timeout ) {
				return true;
			}

			// Else we try to set the timeout to some other value. If success we are golden.
			$new_timeout = $current_timeout;
			@set_time_limit(0); //phpcs:ignore
			$new_timeout = ini_get( 'max_execution_time' );
			$new_timeout = intval( $new_timeout );

			if (0 === $new_timeout) {
				return true;
			}

			// Finally, if we cannot adjust the timeout and the current timeout is less than 30 seconds we throw a warning to the user.
			return $current_timeout > self::MIN_VIABLE_EXEC_TIME;
		}

		/**
		 * Utility function to build the display of a timestamp into the date time format.
		 *
		 * @since 1.0.0
		 *
		 * @param int $timestamp UNIX timestamp from time()
		 * @param string $format
		 *
		 * @return string
		 */
		public static function show_date_time( $timestamp, $format = '' ) {

			if ( ! $format ) {
				$format = get_option( 'date_format' );
				$format .= _x( ' @ ', 'date time sep', SNAPSHOT_I18N_DOMAIN );
				$format .= get_option( 'time_format' );
			}

			$gmt_offset = get_option( 'gmt_offset' ) ? get_option( 'gmt_offset' ) : 0;
			$timestamp  = $timestamp + ( $gmt_offset * 3600 );

			return date_i18n( $format, $timestamp );
		}

		/**
		 * Utility function recursively scan a folder and build an array of it's contents
		 *
		 * @since 1.0.2
		 *
		 * @param string $base Where to start.
		 *
		 * @return array
		 */
		public static function scandir( $base = '' ) {
			if ( defined('SNAPSHOT_IGNORE_SYMLINKS') && SNAPSHOT_IGNORE_SYMLINKS === true) {
				if ( is_link ( $base ) )
					return array();
			}

			if ( ( ! $base ) || ( ! strlen( $base ) ) ) {
				return array();
			}

			if ( ! file_exists( $base ) ) {
				return array();
			}

			self::check_server_timeout();

			$result = scandir($base);
			$data = !empty($result) ? array_diff( $result, array( '.', '..' ) ) : array();

			$subs = array();
			foreach ( $data as $key => $value ) :
				if ( is_dir( $base . '/' . $value ) ) :
					unset( $data[ $key ] );
					$subs[] = self::scandir( $base . '/' . $value );
				elseif ( is_file( $base . '/' . $value ) ) :
					$data[ $key ] = $base . '/' . $value;
				endif;
			endforeach;

			if ( count( $subs ) ) {
				foreach ( $subs as $sub ) {
					$data = array_merge( $data, $sub );
				}
			}

			return $data;
		}

		/**
		 * Utility function to break up a given table rows into segments based on the Settings size for Segments.
		 *
		 * Given a database table with 80,000 rows and a segment size of 1000 the returned $table_set will
		 * be an array of nodes. Each node will contain information about the stating row and number of
		 * segment (itself). Also total rows and total segments for this table.
		 *
		 * @since 1.0.2
		 *
		 * @param $table_name
		 * @param int $segmentSize
		 * @param string $where
		 *
		 * @return array
		 */
		public static function get_table_segments( $table_name, $segmentSize = 1000, $where = '' ) {

			global $wpdb;

			$table_set               = array();
			$table_set['table_name'] = $table_name;
			$table_set['rows_total'] = 0;
			$table_set['segments']   = array();

			$segment_set = array();

			// Use of esc_sql() instead of $wpdb->prepare() because of backticks in query.
			$table_data = $wpdb->get_row( esc_sql( "SELECT count(*) as total_rows FROM `{$table_name}` {$where}; " ) );
			if ( ( isset( $table_data->total_rows ) ) && intval( $table_data->total_rows ) ) {

				$last_rows                 = 0;
				$segment_set['rows_start'] = 0;
				$segment_set['rows_end']   = 0;

				$total_rows              = intval( $table_data->total_rows );
				$table_set['rows_total'] = $total_rows;

				while ( $total_rows > 0 ) {

					if ( $total_rows < $segmentSize ) {
						$segment_set['rows_start'] = intval( $last_rows );
						$segment_set['rows_end']   = intval( $total_rows );
						$table_set['segments'][]   = $segment_set;

						break;
					}

					$segment_set['rows_start'] = intval( $last_rows );
					$segment_set['rows_end']   = $segmentSize;
					$last_rows                 = $last_rows + $segmentSize;

					$table_set['segments'][] = $segment_set;

					$total_rows -= $segmentSize;
				}
			}

			return $table_set;
		}

		/**
		 * Utility function to add some custom schedule intervals to the default WordPress schedules.
		 *
		 * @since 1.0.2
		 *
		 * @param $schedules Passed in by WordPress. The current array of schedules.
		 *
		 * @return mixed $schedules And updated list containing our custom items.
		 */
		public static function add_cron_schedules( $schedules ) {

			$snapshot_schedules = array(
				'snapshot-5minutes' => array(
					'interval' => 60 * 5,
					'display'  => __( 'Every 5 Minutes', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-15minutes' => array(
					'interval' => 60 * 15,
					'display'  => __( 'Every 15 Minutes', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-30minutes' => array(
					'interval' => 60 * 30,
					'display'  => __( 'Every 30 Minutes', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-hourly' => array(
					'interval' => 60 * 60,
					'display'  => __( 'Once Hourly', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-daily' => array(
					'interval' => 1 * 24 * 60 * 60,                //	86,400
					'display'  => __( 'Daily', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-twicedaily' => array(
					'interval' => 1 * 12 * 60 * 60,                // 43,200
					'display'  => __( 'Twice Daily', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-weekly' => array(
					'interval' => 7 * 24 * 60 * 60,                // 604,800
					'display'  => __( 'Weekly', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-twiceweekly' => array(
					'interval' => 7 * 12 * 60 * 60,                // 302,400
					'display'  => __( 'Twice Weekly', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-monthly' => array(
					'interval' => 30 * 24 * 60 * 60,                // 2,592,000
					'display'  => __( 'Monthly', SNAPSHOT_I18N_DOMAIN ),
				),
				'snapshot-twicemonthly' => array(
					'interval' => 15 * 24 * 60 * 60,                // 1,296,000
					'display'  => __( 'Twice Monthly', SNAPSHOT_I18N_DOMAIN ),
				),
			);

			$schedules = array_merge( $snapshot_schedules, $schedules );
			return $schedules;
		}

		/**
		 * Utility function to get the pretty display text for a WordPress schedule interval
		 *
		 * @since 1.0.2
		 *
		 * @param $sched_key Key to item in wp_get_schedules array
		 *
		 * @return string Display text for the scheduled item. If found.
		 */
		public static function get_sched_display( $sched_key ) {

			$scheds = (array) wp_get_schedules();

			if ( isset( $scheds[ $sched_key ] ) ) {
				return $scheds[ $sched_key ]['display'];
			}

		}

		/**
		 * Utility function to get the interval for a WordPress schedule.
		 *
		 * @since 1.0.2
		 *
		 * @param $sched_key Key to item in wp_get_schedules array
		 *
		 * @return mixed
		 */
		public static function get_sched_interval( $sched_key ) {

			$scheds = (array) wp_get_schedules();

			if ( isset( $scheds[ $sched_key ] ) ) {
				return $scheds[ $sched_key ]['interval'];
			}

		}

		/**
		 * Calculate interval offset.
		 * @since 1.0.2
		 *
		 * @param string $interval
		 * @param string $interval_offset
		 *
		 * @return int|number
		 */
		public static function calculate_interval_offset_time( $interval = '', $interval_offset = '' ) {

			if ( ( empty( $interval ) ) || ( empty( $interval_offset ) ) ) {
				return 0;
			}

			$current_timestamp = time() + ( get_option( 'gmt_offset' ) * 3600 );
			$current_localtime = localtime( $current_timestamp, true );

			$diff_timestamp = $current_timestamp;

			$_offset_seconds = 0;

			if ( ( "snapshot-hourly" === $interval ) && ( isset( $interval_offset['snapshot-hourly'] ) ) ) {
				$_offset = $interval_offset['snapshot-hourly'];

			} else if ( ( ( "snapshot-daily" === $interval ) || ( "snapshot-twicedaily" === $interval ) ) && ( isset( $interval_offset['snapshot-daily'] ) ) ) {
				$_offset = $interval_offset['snapshot-daily'];
			} else if ( ( ( "snapshot-weekly" === $interval ) || ( "snapshot-twiceweekly" === $interval ) ) && ( isset( $interval_offset['snapshot-weekly'] ) ) ) {
				$_offset = $interval_offset['snapshot-weekly'];
			} else if ( ( ( "snapshot-monthly" === $interval ) || ( "snapshot-twicemonthly" === $interval ) ) && ( isset( $interval_offset['snapshot-monthly'] ) ) ) {
				$_offset = $interval_offset['snapshot-monthly'];
			} else {
				return $_offset_seconds;
			}

			//echo "offset<pre>"; print_r($_offset); echo "</pre>";

			if ( isset( $_offset['tm_min'] ) ) {

				$_tm_min = intval( $_offset['tm_min'] ) - $current_localtime['tm_min'];
				//echo "_tm_min=[". $_tm_min ."]<br />";
				if ( $_tm_min > 0 ) {
					$_offset_seconds += intval( $_tm_min ) * 60;
				} else if ( $_tm_min < 0 ) {
					$_offset_seconds -= abs( $_tm_min ) * 60;
				}
			}

			if ( isset( $_offset['tm_hour'] ) ) {

				$_tm_hour = intval( $_offset['tm_hour'] ) - $current_localtime['tm_hour'];
				//echo "_tm_hour=[". $_tm_hour ."]<br />";

				if ( $_tm_hour > 0 ) {
					$_offset_seconds += intval( $_tm_hour ) * 60 * 60;
				} else if ( $_tm_hour < 0 ) {
					$_offset_seconds -= abs( $_tm_hour ) * 60 * 60;
				}
			}

			if ( isset( $_offset['tm_wday'] ) ) {

				$_tm_wday = intval( $_offset['tm_wday'] ) - $current_localtime['tm_wday'];
				//echo "_tm_wday=[". $_tm_wday ."]<br />";

				if ( $_tm_wday > 0 ) {
					$_offset_seconds += intval( $_tm_wday ) * 24 * 60 * 60;
				} else if ( $_tm_wday < 0 ) {
					$_offset_seconds -= abs( $_tm_wday ) * 24 * 60 * 60;
				}
			}

			if ( isset( $_offset['tm_mday'] ) ) {

				$_tm_mday = intval( $_offset['tm_mday'] ) - $current_localtime['tm_mday'];
				//echo "_tm_mday=[". $_tm_mday ."]<br />";

				if ( $_tm_mday > 0 ) {
					$_offset_seconds += intval( $_tm_mday ) * 24 * 60 * 60;
				} else if ( $_tm_mday < 0 ) {
					$_offset_seconds -= abs( $_tm_mday ) * 24 * 60 * 60;
				}
			}

			if ( $_offset_seconds < 0 ) {

				$_sched_interval = self::get_sched_interval( $interval );
				$_offset_seconds += $_sched_interval;
			}

			//echo "next data: ". date('Y-m-d h:i', $current_timestamp + $_offset_seconds) ."<br />";
			//return $current_timestamp + $_offset_seconds;
			return $_offset_seconds;
		}

		/**
		 * Utility function to parse the snapshot entry backup log file into an array. The array break points
		 * are based on the string '-------------' which divides the different backup attempts.
		 *
		 * @todo: $snapshot_filename
		 *
		 * @since 1.0.2
		 *
		 * @param $backupLogFileFull
		 *
		 * @return array
		 */
		public static function get_archive_log_entries( $backupLogFileFull ) {

			if ( file_exists( $backupLogFileFull ) ) {

				$log_content = file( $backupLogFileFull );
				if ( $log_content ) {

					$log_entries     = array();
					$log_content_tmp = array();
					foreach ( $log_content as $log_content_line ) {

						$log_content_line = trim( $log_content_line );
						if ( ! strlen( $log_content_line ) ) {
							continue;
						}
						if ( strstr( $log_content_line, "----------" ) !== false ) {
							$log_entries[ $snapshot_filename ] = $log_content_tmp;
							unset( $log_content_tmp );
							$log_content_tmp = array();
							continue;
						}

						$log_content_tmp[] = $log_content_line;

						if ( strstr( $log_content_line, "finish:" ) !== false ) {
							$pos_col           = strrpos( $log_content_line, ':' );
							$snapshot_filename = substr( $log_content_line, $pos_col + 1 );
							$snapshot_filename = trim( $snapshot_filename );
						}
					}
					//echo "log_entries<pre>"; print_r($log_entries); echo "</pre>";
					if ( count( $log_entries ) ) {
						return $log_entries;
					}
				}
			}
		}

		/**
		 * Utility function to recursively remove directories.
		 *
		 * @since 1.0.3
		 * @see
		 *
		 * @param none
		 *
		 * @return none
		 */
		public static function recursive_rmdir( $dir ) {
			if ( is_dir( $dir ) ) {
				$objects = scandir( $dir );

				foreach ( $objects as $object ) {
					if ( "." !== $object && ".." !== $object ) {
						if ( filetype( $dir . "/" . $object ) === "dir" ) {
							self::recursive_rmdir( $dir . "/" . $object );
						} else {
							unlink( $dir . "/" . $object );
						}
					}
				}
				reset( $objects );
				rmdir( $dir );
			}
		}

		/**
		 * Utility function to access the latest item's data set.
		 *
		 * @since 1.0.4
		 *
		 * @param $data_items
		 *
		 * @return mixed
		 */
		public static function latest_data_item( $data_items ) {
			krsort( $data_items );
			foreach ( $data_items as $data_key => $data_item ) {
				return $data_item;
			}
		}

		/**
		 * Utility function to access the latest backup ever.
		 *
		 * @since 1.0.4
		 *
		 * @param $data_items
		 *
		 * @return mixed
		 */
		public static function latest_backup( $items ) {
			$last_files = array();

			foreach ($items as $key => $backup) {
				if( isset( $backup['data'] ) ){
					$data_backup = self::latest_data_item( $backup['data'] );
						if( isset( $data_backup["timestamp"] ) ) {
							$last_files[$data_backup["timestamp"]] = $data_backup;
						}
					}
				}

			krsort( $last_files );
			return reset( $last_files );
		}

		/**
		 * Utility function Add index.php and .htaccess files to archive folders
		 *
		 * @since 1.0.5
		 *
		 * @param string $folder Destination folder to apply security files to
		 */
		public static function secure_folder( $folder ) {

			if ( ! file_exists( trailingslashit( $folder ) . "index.php" ) ) {
				global $wp_filesystem;

				if( self::connect_fs() ) {
					$wp_filesystem->put_contents( trailingslashit( $folder ) . "index.php", "<?php // Silence is golden. ?>", FS_CHMOD_FILE );
				} else {
					return new WP_Error("filesystem_error", "Cannot initialize filesystem");
				}

			}
			if ( ! file_exists( trailingslashit( $folder ) . ".htaccess" ) ) {
				global $wp_filesystem;

				if( self::connect_fs() ) {
					$wp_filesystem->put_contents( trailingslashit( $folder ) . ".htaccess", "IndexIgnore *\r\nOptions -Indexes`\r\n", FS_CHMOD_FILE );
				} else {
					return new WP_Error("filesystem_error", "Cannot initialize filesystem");
				}
			}

			if ( ! file_exists( trailingslashit( $folder ) . "CACHEDIR.TAG" ) ) {
				global $wp_filesystem;

				if( self::connect_fs() ) {
					$wp_filesystem->put_contents( trailingslashit( $folder ) . "CACHEDIR.TAG", "", FS_CHMOD_FILE );
				} else {
					return new WP_Error("filesystem_error", "Cannot initialize filesystem");
				}
			}

		}

		/**
		 * Get the upload path of the blog.
		 *
		 * @param int $switched_blog_id
		 * @param string $key
		 *
		 * @return array|mixed
		 */
		public static function get_blog_upload_path( $switched_blog_id = 0, $key = 'basedir' ) {
			global $blog_id;
			$home_path = apply_filters( 'snapshot_home_path', get_home_path() );

			//echo "switched_blog_id[". $switched_blog_id ."] key[". $key ."]<br />";
			if ( is_multisite() ) {
				$blog_id_org = $blog_id;

				switch_to_blog( $switched_blog_id );
				$uploads = wp_upload_dir();

				if ( "basedir" === $key ) {
					if ( isset( $uploads['basedir'] ) ) {
						$upload_path = str_replace( '\\', '/', $uploads['basedir'] );
						$upload_path = str_replace( $home_path, '', $upload_path );
					}
				} else if ( "baseurl" === $key ) {

					if ( isset( $uploads['baseurl'] ) ) {
						$upload_path        = array();
						$upload_path['raw'] = str_replace( '\\', '/', $uploads['baseurl'] );

						if ( ( defined( 'UPLOADS' ) ) && ( UPLOADS !== '' ) ) {
							$UPLOADS                = str_replace( '/' . $blog_id_org . '/', '/' . $switched_blog_id . '/', untrailingslashit( UPLOADS ) );
							$upload_path['rewrite'] = str_replace( $UPLOADS, 'files', $upload_path['raw'] );
							$upload_path['rewrite'] = str_replace( get_option( 'siteurl' ) . '/', '', $upload_path['rewrite'] );
						}
						$upload_path['raw'] = str_replace( get_option( 'siteurl' ) . '/', '', $upload_path['raw'] );
					}
				}
				restore_current_blog();

			} else {
				$uploads = wp_upload_dir();
				if ( "basedir" === $key ) {
					if ( isset( $uploads['basedir'] ) ) {
						$upload_path = str_replace( '\\', '/', $uploads['basedir'] );
						$upload_path = str_replace( $home_path, '', $upload_path );
					}
				} else if ( "baseurl" === $key ) {
					if ( isset( $uploads['baseurl'] ) ) {
						$upload_path = str_replace( get_site_url(), '', $uploads['baseurl'] );
					}
				}
			}

			if ( "basedir" === $key ) {
				if ( ! $upload_path ) {
					$upload_path = trailingslashit( WP_CONTENT_DIR ) . "uploads";
					$upload_path = str_replace( '\\', '/', $upload_path );
					$upload_path = str_replace( $home_path, '', $upload_path );
				}
			}

			return $upload_path;
		}

		/**
		 * @param $item
		 *
		 * @return array
		 */
		public static function get_tables_sections_display( $item ) {

			$tables_sections_out           = array();
			$tables_sections_out['click']  = '';
			$tables_sections_out['hidden'] = '';

			//echo "item<pre>"; print_r($item); echo "</pre>";

			if ( isset( $item['tables-option'] ) ) {
				$tables_sections_out['click'] .= __( 'Tables:', SNAPSHOT_I18N_DOMAIN ) . " (" . $item['tables-option'] . ")";

				if ( isset( $item['tables-sections'] ) ) {

					foreach ( $item['tables-sections'] as $section_key => $section_tables ) {

						if ( "wp" === $section_key ) {
							$section_label = __( 'core', SNAPSHOT_I18N_DOMAIN );
						} else if ( "non" === $section_key ) {
							$section_label = __( 'non-core', SNAPSHOT_I18N_DOMAIN );
						} else if ( "other" === $section_key ) {
							$section_label = __( 'other', SNAPSHOT_I18N_DOMAIN );
						} else if ( "error" === $section_key ) {
							$section_label = __( 'error', SNAPSHOT_I18N_DOMAIN );
						} else if ( "global" === $section_key ) {
							$section_label = __( 'global', SNAPSHOT_I18N_DOMAIN );
						}

						if ( count( $section_tables ) ) {
							if ( strlen( $tables_sections_out['click'] ) ) {
								$tables_sections_out['click'] .= ", ";
							}
							$tables_sections_out['click'] .= '<a class="snapshot-list-table-' . $section_key . '-show" href="#">' . sprintf( '%d %s',
									count( $section_tables ), $section_label ) . '</a>';

							$tables_sections_out['hidden'] .= '<p style="display: none" class="snapshot-list-table-' . $section_key . '-container">' .
							                                  implode( ', ', $section_tables ) . '</p>';
						}
					}
				}
			}

			return $tables_sections_out;
		}

		/**
		 * @param $data_item
		 *
		 * @return array
		 */
		public static function get_files_sections_display( $data_item ) {

			$files_sections_out           = array();
			$files_sections_out['click']  = '';
			$files_sections_out['hidden'] = '';

			if ( isset( $data_item['files-option'] ) ) {
				$files_sections_out['click'] .= __( 'Files:', SNAPSHOT_I18N_DOMAIN ) . ' (';
				$files_sections_out['click'] .= $data_item['files-option'] . ") ";

				//if ((isset($data_item['files-count'])) && (intval($data_item['files-count']))) {
				//	$files_sections_out['click'] .= ' '. $data_item['files-count'] .' ';
				//}

				if ( isset( $data_item['files-sections'] ) ) {
					$sections_str = '';
					foreach ( $data_item['files-sections'] as $section ) {
						if ( strlen( $sections_str ) ) {
							$sections_str .= ", ";
						}
						$sections_str .= ucwords( $section );
					}
					$files_sections_out['click'] .= $sections_str;
				}
			}

			return $files_sections_out;
		}

		/**
		 * Read a file and display its content chunk by chunk.
		 *
		 * @param $filename
		 * @param bool $retbytes
		 *
		 * @todo DO WE REALLY NEED TO ECHO THE BUFFER?
		 *
		 * @return bool|int
		 */
		public static function file_output_stream_chunked( $filename, $retbytes = true ) {

			$CHUNK_SIZE = 1024 * 1024; // Size (in bytes) of tiles chunk

			$buffer = '';
			$cnt    = 0;
			$status = false;

			global $wp_filesystem;

			if( self::connect_fs() ) {
				$file = $wp_filesystem->get_contents( $filename );

				$splitFile = str_split($file, $CHUNK_SIZE);
				foreach($splitFile as $buffer) {
					echo $buffer; // phpcs:ignore
					flush();
					if ( $retbytes ) {
						$cnt += strlen( $buffer );
					}
				}
				$status = true;
			} else {
				return false;
			}

			if ( $retbytes && $status ) {
				return $cnt; // return num. bytes delivered like readfile() does.
			}

			return $status;
		}

		/**
		 * @param $someFolder
		 */
		public static function clean_folder( $someFolder ) {

			$someFolder = trailingslashit( $someFolder );

			// Cleanup any files from a previous restore attempt
			$dh = opendir( $someFolder );
			if ( $dh ) {
				$file = readdir( $dh );
				while ( false !== $file ) {
					if ( ( '.' === $file ) || ( '..' === $file ) ) {
						$file = readdir( $dh );
						continue;
					}

					self::recursive_rmdir( $someFolder . $file );
					$file = readdir( $dh );
				}
				closedir( $dh );
			}
		}

		/**
		 * @param $manifest_array
		 * @param $manifestFile
		 *
		 * @return bool
		 */
		public static function create_archive_manifest( $manifest_array, $manifestFile ) {
			if ( ! $manifest_array ) {
				return false;
			}

			global $wp_filesystem;

			if( self::connect_fs() ) {
				foreach ( $manifest_array as $token => $token_data ) {
					$wp_filesystem->put_contents($manifestFile, $wp_filesystem->get_contents( $manifestFile ) . $token . ":" . maybe_serialize( $token_data ) . "\r\n", FS_CHMOD_FILE);
				}

				return true;
			}
		}

		/**
		 *
		 * @param $manifest_file
		 *
		 * @return array
		 */
		public static function consume_archive_manifest( $manifest_file ) {
			$snapshot_manifest = array();
			$manifest_array = file( $manifest_file );

			$sessionRestoreFolder = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' ) );

			/* Return an empty array if the manifest is empty */
			if ( ! $manifest_array ) {
				return array();
			}

			/* Read the manifest file into an array, joining entries that are split across multiple lines */
			$manifest = array();
			$last_key = '';

			foreach ( $manifest_array as $line ) {
				$matches = array();
				preg_match( '/^([\w\s-]+):(.+)$/', $line, $matches );

				if ( $matches ) {

					/* If the line matches the expected format, extract the values and add them to the array */
					$label = trim( $matches[1] );
					$data = $matches[2];

					$manifest[ $label ] = $data;
					$last_key = $label;

				} elseif ( $last_key ) {
					/* Otherwise, treat this line as part of the previous one */
					$manifest[ $last_key ] .= "\n" . $line;
				}
			}

			/* Parse the raw manifest data into its proper form */
			foreach ( $manifest as $key => $value ) {

				if ( "TABLES" === $key ) {
					if ( is_serialized( $value ) ) {
						$value = maybe_unserialize( $value );
					} else {
						$table_values = explode( ',', $value );

						foreach ( $table_values as $idx => $table_name ) {
							$table_values[ $idx ] = trim( $table_name );
						}

						$value = $table_values;
					}
				} else if ( ( "TABLES-DATA" === $key ) || ( "ITEM" === $key ) || ( "FILES-DATA" === $key ) || ( 'WP_UPLOAD_URLS' === $key ) ) {
					if ( is_serialized( $value ) ) {
						$value = maybe_unserialize( $value );
					} else {
						$value = trim( $value );
					}

				} else {
					$value = trim( $value );
				}

				$snapshot_manifest[ $key ] = $value;
			}
			//echo "snapshot_manifest<pre>"; print_r($snapshot_manifest); echo "</pre>";
			//die();

			if ( ! isset( $snapshot_manifest['SNAPSHOT_VERSION'] ) ) {
				if ( isset( $snapshot_manifest['VERSION'] ) ) {
					$snapshot_manifest['SNAPSHOT_VERSION'] = $snapshot_manifest['VERSION'];
					unset( $snapshot_manifest['VERSION'] );
				}
			}

			if ( ! isset( $snapshot_manifest['WP_BLOG_ID'] ) ) {
				if ( isset( $snapshot_manifest['BLOG-ID'] ) ) {
					$snapshot_manifest['WP_BLOG_ID'] = $snapshot_manifest['BLOG-ID'];
					unset( $snapshot_manifest['BLOG-ID'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_DB_NAME'] ) ) {
				if ( isset( $snapshot_manifest['DB_NAME'] ) ) {
					$snapshot_manifest['WP_DB_NAME'] = $snapshot_manifest['DB_NAME'];
					unset( $snapshot_manifest['DB_NAME'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_DB_BASE_PREFIX'] ) ) {
				if ( isset( $snapshot_manifest['BASE_PREFIX'] ) ) {
					$snapshot_manifest['WP_DB_BASE_PREFIX'] = $snapshot_manifest['BASE_PREFIX'];
					unset( $snapshot_manifest['BASE_PREFIX'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_DB_PREFIX'] ) ) {
				if ( isset( $snapshot_manifest['PREFIX'] ) ) {
					$snapshot_manifest['WP_DB_PREFIX'] = $snapshot_manifest['PREFIX'];
					unset( $snapshot_manifest['PREFIX'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_DB_CHARSET_COLLATE'] ) ) {
				if ( isset( $snapshot_manifest['CHARSET_COLLATE'] ) ) {
					$snapshot_manifest['WP_DB_CHARSET_COLLATE'] = $snapshot_manifest['CHARSET_COLLATE'];
					unset( $snapshot_manifest['CHARSET_COLLATE'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_UPLOAD_PATH'] ) ) {
				if ( isset( $snapshot_manifest['UPLOAD_PATH'] ) ) {
					$snapshot_manifest['WP_UPLOAD_PATH'] = $snapshot_manifest['UPLOAD_PATH'];
					unset( $snapshot_manifest['UPLOAD_PATH'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_HOME'] ) ) {
				if ( isset( $snapshot_manifest['HOME'] ) ) {
					$snapshot_manifest['WP_HOME'] = $snapshot_manifest['HOME'];
					unset( $snapshot_manifest['HOME'] );
				}
			}
			if ( ! isset( $snapshot_manifest['WP_SITEURL'] ) ) {
				if ( isset( $snapshot_manifest['SITEURL'] ) ) {
					$snapshot_manifest['WP_SITEURL'] = $snapshot_manifest['SITEURL'];
					unset( $snapshot_manifest['SITEURL'] );
				}
			}

			if ( ! isset( $snapshot_manifest['WP_BLOG_NAME'] ) ) {
				$snapshot_manifest['WP_BLOG_NAME'] = '';
			}

			if ( ! isset( $snapshot_manifest['WP_BLOG_DOMAIN'] ) ) {
				if ( isset( $snapshot_manifest['WP_SITEURL'] ) ) {
					$snapshot_manifest['WP_BLOG_DOMAIN'] = wp_parse_url( $snapshot_manifest['WP_SITEURL'], PHP_URL_HOST );
				}
			}

			if ( ! isset( $snapshot_manifest['WP_BLOG_PATH'] ) ) {
				if ( isset( $snapshot_manifest['WP_SITEURL'] ) ) {
					$snapshot_manifest['WP_BLOG_PATH'] = wp_parse_url( $snapshot_manifest['WP_SITEURL'], PHP_URL_PATH );
				}
			}

			//echo "snapshot_manifest<pre>"; print_r($snapshot_manifest); echo "</pre>";

			return $snapshot_manifest;
		}

		/**
		 * @param string $archiveFilename
		 * @param string $restoreFolder
		 *
		 * @return bool|string
		 */
		public static function extract_archive_manifest( $archiveFilename = '', $restoreFolder = '' ) {

			$manifest_file = 'snapshot_manifest.txt';

			if ( ! file_exists( $archiveFilename ) ) {
				return false;
			}

			if ( ! $restoreFolder ) {
				$restoreFolder = WPMUDEVSnapshot::instance()->get_setting( 'backupRestoreFolderFull' );
			}

			// It is assumed the folder already exists!~~~
			// Clear out the restore folder.
			self::recursive_rmdir( $restoreFolder );

			$ret = wp_mkdir_p( $restoreFolder );

			//echo "zipLibrary[". WPMUDEVSnapshot::instance()->config_data['config']['zipLibrary'] ."]<br />";
			//die();

			if ( "PclZip" === WPMUDEVSnapshot::instance()->config_data['config']['zipLibrary'] ) {
				if ( ! defined( 'PCLZIP_TEMPORARY_DIR' ) ) {
					define( 'PCLZIP_TEMPORARY_DIR', $restoreFolder );
				}

				if ( ! class_exists( 'class PclZip' ) ) {
					require_once ABSPATH . '/wp-admin/includes/class-pclzip.php';
				}

				$zipArchive         = new PclZip( $archiveFilename );
				$exteact_files_list = $zipArchive->extract( PCLZIP_OPT_PATH, $restoreFolder, PCLZIP_OPT_BY_NAME, array( 'snapshot_manifest.txt' ) );

			} else {

				$zip = new ZipArchive();
				$res = $zip->open( $archiveFilename );
				if ( true === $res ) {
					$zip->extractTo( $restoreFolder, array( 'snapshot_manifest.txt' ) );
					$zip->close();
				}
			}

			$manifest_filename = trailingslashit( $restoreFolder ) . $manifest_file;

			if ( file_exists( $manifest_filename ) ) {
				return $manifest_filename;
			} else {
				return false;
			}
		}

		/**
		 * @param $restoreFile
		 * @param $restoreFolder
		 *
		 * @return array
		 */
		public static function archives_import_proc( $restoreFile, $restoreFolder ) {
			global $wpdb;

			WPMUDEVSnapshot::instance()->load_config();
			WPMUDEVSnapshot::instance()->set_backup_folder();
			WPMUDEVSnapshot::instance()->set_log_folders();

			$CONFIG_CHANGED = false;

//	echo "restoreFile=[". $restoreFile ."]<br />";
//	echo "restoreFolder=[". $restoreFolder ."]<br />";
//	echo "before items<pre>"; print_r(WPMUDEVSnapshot::instance()->config_data['items']); echo "</pre>";

			$error_status                 = array();
			$error_status['errorStatus']  = false;
			$error_status['errorText']    = "";
			$error_status['responseText'] = "";

			$snapshot_manifest = self::extract_archive_manifest( $restoreFile, $restoreFolder );

			if ( file_exists( $snapshot_manifest ) ) {
				//echo "snapshot_manifest[". $snapshot_manifest ."]<br />";
				$CONFIG_CHANGED = false;

				$manifest_data = self::consume_archive_manifest( $snapshot_manifest );
				//echo "manifest_data<pre>"; print_r($manifest_data); echo "</pre>";
				//die();

				if ( empty( $manifest_data ) ) {
					$error_status['errorStatus'] = true;
					$error_status['errorText']   = __( "Manifest data not found in archive.", SNAPSHOT_I18N_DOMAIN );

					return $error_status;
				}

				if ( ( ! isset( $manifest_data['ITEM'] ) ) || ( empty( $manifest_data['ITEM'] ) ) ) {
					$error_status['errorStatus'] = true;
					$error_status['errorText']   = __( "Manifest data does not contain ITEM section.", SNAPSHOT_I18N_DOMAIN );

					return $error_status;
				}
				$item = $manifest_data['ITEM'];

				if ( ( ! isset( $item['timestamp'] ) ) || ( empty( $item['timestamp'] ) ) ) {
					$error_status['errorStatus'] = true;
					$error_status['errorText']   = __( "Manifest ITEM does not contain 'timestamp' item.", SNAPSHOT_I18N_DOMAIN );

					return $error_status;
				}

				//$siteurl = get_option('siteurl');
				//echo "siteurl=[". $siteurl ."]<br />";

				$RESTORE['LOCAL'] = array();

				if ( is_multisite() ) {
					$blog_details = get_blog_details( $manifest_data['WP_BLOG_ID'] );
					//echo "blog_details<pre>"; print_r($blog_details); echo "</pre>";
					if ( ( isset( $blog_details->domain ) ) && ( ! empty( $blog_details->domain ) ) ) {
						$RESTORE['LOCAL']['WP_BLOG_DOMAIN'] = $blog_details->domain;
					} else {
						$RESTORE['LOCAL']['WP_BLOG_DOMAIN'] = '';
					}

					if ( ( isset( $blog_details->path ) ) && ( ! empty( $blog_details->path ) ) ) {
						$RESTORE['LOCAL']['WP_BLOG_PATH'] = $blog_details->path;
					} else {
						$RESTORE['LOCAL']['WP_BLOG_PATH'] = '';
					}
				} else {
					$siteurl                            = get_option( 'siteurl' );
					$RESTORE['LOCAL']['WP_BLOG_DOMAIN'] = wp_parse_url( $siteurl, PHP_URL_HOST );
					$RESTORE['LOCAL']['WP_BLOG_PATH']   = wp_parse_url( $siteurl, PHP_URL_PATH );
					//$RESTORE['LOCAL']['WP_BLOG_ID']		= $blog_id;
				}

				$RESTORE['IMPORT'] = array();

				if ( isset( $manifest_data['WP_BLOG_ID'] ) ) {
					$RESTORE['IMPORT']['WP_BLOG_ID'] = $manifest_data['WP_BLOG_ID'];
				} else {
					$RESTORE['IMPORT']['WP_BLOG_ID'] = '';
				}


				if ( isset( $manifest_data['WP_BLOG_NAME'] ) ) {
					$RESTORE['IMPORT']['WP_BLOG_NAME'] = $manifest_data['WP_BLOG_NAME'];
				} else {
					$RESTORE['IMPORT']['WP_BLOG_NAME'] = '';
				}


				if ( isset( $manifest_data['WP_DB_NAME'] ) ) {
					$RESTORE['IMPORT']['WP_DB_NAME'] = $manifest_data['WP_DB_NAME'];
				} else {
					$RESTORE['IMPORT']['WP_DB_NAME'] = '';
				}


				if ( isset( $manifest_data['WP_DB_BASE_PREFIX'] ) ) {
					$RESTORE['IMPORT']['WP_DB_BASE_PREFIX'] = $manifest_data['WP_DB_BASE_PREFIX'];
				} else {
					$RESTORE['IMPORT']['WP_DB_BASE_PREFIX'] = '';
				}


				if ( isset( $manifest_data['WP_DB_PREFIX'] ) ) {
					$RESTORE['IMPORT']['WP_DB_PREFIX'] = $manifest_data['WP_DB_PREFIX'];
				} else {
					$RESTORE['IMPORT']['WP_DB_PREFIX'] = '';
				}


				if ( isset( $manifest_data['WP_DB_CHARSET_COLLATE'] ) ) {
					$RESTORE['IMPORT']['WP_DB_CHARSET_COLLATE'] = $manifest_data['WP_DB_CHARSET_COLLATE'];
				} else {
					$RESTORE['IMPORT']['WP_DB_CHARSET_COLLATE'] = '';
				}


				if ( isset( $manifest_data['WP_HOME'] ) ) {
					$RESTORE['IMPORT']['WP_HOME'] = $manifest_data['WP_HOME'];
				}

				if ( isset( $manifest_data['WP_SITEURL'] ) ) {
					$RESTORE['IMPORT']['WP_SITEURL'] = $manifest_data['WP_SITEURL'];
				} else {
					$RESTORE['IMPORT']['WP_SITEURL'] = '';
				}


				if ( isset( $manifest_data['WP_UPLOAD_PATH'] ) ) {
					$RESTORE['IMPORT']['WP_UPLOAD_PATH'] = $manifest_data['WP_UPLOAD_PATH'];
				} else {
					$RESTORE['IMPORT']['WP_UPLOAD_PATH'] = '';
				}


				if ( isset( $manifest_data['WP_UPLOAD_URLS'] ) ) {
					$RESTORE['IMPORT']['WP_UPLOAD_URLS'] = $manifest_data['WP_UPLOAD_URLS'];
				} else {
					$RESTORE['IMPORT']['WP_UPLOAD_URLS'] = array();
				}


				if ( isset( $manifest_data['WP_BLOG_DOMAIN'] ) ) {
					$RESTORE['IMPORT']['WP_BLOG_DOMAIN'] = $manifest_data['WP_BLOG_DOMAIN'];
				} else if ( isset( $manifest_data['WP_SITEURL'] ) ) {
					$RESTORE['LOCAL']['WP_BLOG_DOMAIN'] = wp_parse_url( $manifest_data['WP_SITEURL'], PHP_URL_HOST );
				}

				if ( isset( $manifest_data['WP_BLOG_PATH'] ) ) {
					$RESTORE['IMPORT']['WP_BLOG_PATH'] = $manifest_data['WP_BLOG_PATH'];
				} else if ( isset( $manifest_data['WP_SITEURL'] ) ) {
					$RESTORE['IMPORT']['WP_BLOG_PATH'] = wp_parse_url( $manifest_data['WP_SITEURL'], PHP_URL_PATH );
				}

				//echo "RESTORE<pre>"; print_r($RESTORE); echo "</pre>";
				//die();

				if ( ( $RESTORE['IMPORT']['WP_BLOG_DOMAIN'] !== $RESTORE['LOCAL']['WP_BLOG_DOMAIN'] )
				     || ( $RESTORE['IMPORT']['WP_BLOG_PATH'] !== $RESTORE['LOCAL']['WP_BLOG_PATH'] )
				) {

					$item['IMPORT'] = $RESTORE['IMPORT'];

					// For Multisite we try and lookup the site based on the DOMAIN+PATH
					if ( is_multisite() ) {
						global $wpdb;

						if ( is_subdomain_install() ) {
							$sql_str = $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s LIMIT 1", $RESTORE['IMPORT']['WP_BLOG_DOMAIN'] );
							//$sql_str = $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs WHERE domain = %s LIMIT 1", $RESTORE['LOCAL']['WP_BLOG_DOMAIN']);
							$blog = $wpdb->get_row( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s LIMIT 1", $RESTORE['IMPORT']['WP_BLOG_DOMAIN'] ) );
						} else {
							$snapshot_blog_id_search_path   = trailingslashit( $RESTORE['IMPORT']['WP_BLOG_PATH'] );
							$snapshot_blog_id_search_domain = untrailingslashit( $RESTORE['IMPORT']['WP_BLOG_DOMAIN'] );
							$sql_str                        = $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s LIMIT 1",
								$snapshot_blog_id_search_domain, $snapshot_blog_id_search_path );
							$blog = $wpdb->get_row( $wpdb->prepare( "SELECT blog_id FROM $wpdb->blogs WHERE domain = %s AND path = %s LIMIT 1",
								$snapshot_blog_id_search_domain, $snapshot_blog_id_search_path ) );
						}
						if ( ( isset( $blog->blog_id ) ) && ( $blog->blog_id > 0 ) ) { // found
							//echo "blog<pre>"; print_r($blog); echo "</pre>";
							$item['blog-id'] = $blog->blog_id;
						} else {
							$item['blog-id'] = 0;
						}
					} else {
						$item['blog-id'] = 0;
					}
				}

				//echo "item<pre>"; print_r($item); echo "</pre>";
				//die();

				$item_key = $item['timestamp'];

				if ( ( ! isset( $item['data'] ) ) || ( ! count( $item['data'] ) ) ) {
					$error_status['errorStatus'] = true;
					$error_status['errorText']   = __( "Manifest ITEM does not contain 'data' section.", SNAPSHOT_I18N_DOMAIN );

					return $error_status;
				}

				// Now we check the manifest item against the config data.
				foreach ( $item['data'] as $data_item_key => $data_item ) {

					if ( ( ! isset( $data_item['filename'] ) ) || ( empty( $data_item['filename'] ) ) ) {
						$item['data'][ $data_item_key ]['filename'] = basename( $restoreFile );
					}

					if ( ( ! isset( $data_item['file_size'] ) ) || ( empty( $data_item['file_size'] ) ) ) {
						$item['data'][ $data_item_key ]['file_size'] = filesize( $restoreFile );
					}
				}

				if ( ! isset( WPMUDEVSnapshot::instance()->config_data['items'][ $item_key ] ) ) {
					WPMUDEVSnapshot::instance()->config_data['items'][ $item_key ] = $item;
					$CONFIG_CHANGED                                                = true;

					$error_status['errorStatus']  = false;
					$error_status['responseText'] = __( "Archive imported successfully.", SNAPSHOT_I18N_DOMAIN );

				} else {
					foreach ( $item['data'] as $data_item_key => $data_item ) {

						if ( ! isset( WPMUDEVSnapshot::instance()->config_data['items'][ $item_key ]['data'][ $data_item_key ] ) ) {
							WPMUDEVSnapshot::instance()->config_data['items'][ $item_key ]['data'][ $data_item_key ] = $data_item;
							$CONFIG_CHANGED                                                                          = true;

							$error_status['errorStatus']  = false;
							$error_status['responseText'] = __( "Archive imported successfully.", SNAPSHOT_I18N_DOMAIN );

						} else {
							$error_status['errorStatus']  = false;
							$error_status['responseText'] = __( "already present. not importing.", SNAPSHOT_I18N_DOMAIN );
						}
					}
				}

				if ( true === $CONFIG_CHANGED ) {
					WPMUDEVSnapshot::instance()->save_config();
				}
			} else {
				$error_status['errorStatus'] = true;
				$error_status['errorText']   = __( "Manifest data not found in archive.", SNAPSHOT_I18N_DOMAIN );
			}

			return $error_status;
		}

		/**
		 * Convert bytes to human readable format.
		 *
		 * @since 2.0.3
		 *
		 * @param int $bytes Size in bytes to convert
		 * @param int $precision
		 *
		 * @return string
		 */
		public static function size_format( $bytes = 0, $precision = 2 ) {
			return size_format($bytes, $precision);
			/*
			$kilobyte = 1000;
			$megabyte = $kilobyte * 1000;
			$gigabyte = $megabyte * 1000;
			$terabyte = $gigabyte * 1000;

			if ( ( $bytes >= 0 ) && ( $bytes < $kilobyte ) ) {
				return $bytes . 'b';

			} elseif ( ( $bytes >= $kilobyte ) && ( $bytes < $megabyte ) ) {
				return round( $bytes / $kilobyte, $precision ) . 'kb';

			} elseif ( ( $bytes >= $megabyte ) && ( $bytes < $gigabyte ) ) {
				return round( $bytes / $megabyte, $precision ) . 'M';

			} elseif ( ( $bytes >= $gigabyte ) && ( $bytes < $terabyte ) ) {
				return round( $bytes / $gigabyte, $precision ) . 'G';

			} elseif ( $bytes >= $terabyte ) {
				return round( $bytes / $terabyte, $precision ) . 'T';
			} else {
				return $bytes . 'b';
			}
			*/
		}

		/**
		 * Returns human readable file size to bytes.
		 *
		 * @param $val
		 *
		 * @return int|string
		 */
		public static function size_unformat( $val ) {
			$val  = trim( $val );
			$last = strtolower( $val[ strlen( $val ) - 1 ] );

			// Explicitly typecast the value to a numeric one
			$val = (float)$val;

			switch ( $last ) {
				// The 'G' modifier is available since PHP 5.1.0
				case 'g':
					$val *= 1024;
					// No break
				case 'm':
					$val *= 1024;
					// No break
				case 'k':
					$val *= 1024;
					// No break
				case 'b':
					$val = $val;
					// No break
			}

			return $val;
		}

		/**
		 * @param string $item_key
		 *
		 * @return array|mixed|string|void
		 */
		public static function item_get_lock_info( $item_key = '' ) {

			if ( ! $item_key ) {
				return;
			}

			$lock_info           = array();
			$lock_info['locked'] = false;
			$lock_info['file']   = trailingslashit( WPMUDEVSnapshot::instance()->get_setting( 'backupLockFolderFull' ) ) . $item_key . ".lock";
			if ( file_exists( $lock_info['file'] ) ) {
				// phpcs:ignore
				$lock_fp = fopen( $lock_info['file'], 'r' );
				if ( $lock_fp ) {

					// Try to obtain exclusive lock to prevent multiple processes.
					if ( ! flock( $lock_fp, LOCK_EX | LOCK_NB ) ) {
						$lock_info['locked'] = true;
						flock( $lock_fp, LOCK_UN );
					}
					$lock_info = fgets( $lock_fp, 4096 );
					if ( $lock_info ) {
						$lock_info = maybe_unserialize( $lock_info );
					}
					// phpcs:ignore
					fclose( $lock_fp );
				}
			}

			return $lock_info;
		}


		/**
		 * Wrapper for WP current_user_can.
		 *
		 * @todo Is this still needed?
		 *
		 * @param $cap
		 *
		 * @return bool
		 */
		public static function current_user_can( $cap ) {
			if ( is_multisite() ) {
				if ( is_network_admin() ) {
					return true;
				}

			} else {
				return current_user_can( $cap );
			}
		}

		/**
		 * @param $data_item
		 *
		 * @return int
		 */
		public static function data_item_file_processed_count( $data_item ) {
			if ( ! isset( $data_item['destination-status'] ) ) {
				return 0;
			}

			$_count = 0;
			foreach ( $data_item['destination-status'] as $_status ) {
				if ( isset( $_status['syncFilesTotal'] ) ) {
					$_count += intval( $_status['syncFilesTotal'] );
				}
			}

			return intval( $_count );
		}

		/**
		 * Will be used to replace old home URL with new home URL if the URL is changed during restore.
		 *
		 * @todo Method name is too generic
		 *
		 * @param $value
		 * @param $old_site_url
		 * @param $new_site_url
		 *
		 * @return mixed|string
		 */
		public static function replace_value( $value, $old_site_url, $new_site_url ) {
			if ( is_serialized( $value ) ) {
				$unserialized     = maybe_unserialize( $value );
				$unserialized_new = self::replace_value( $unserialized, $old_site_url, $new_site_url ); // recurse!
				return maybe_serialize( $unserialized_new );
			} elseif ( is_array( $value ) ) {
				foreach ( $value as $key => &$val ) {
					$val = self::replace_value( $val, $old_site_url, $new_site_url ); // recurse!
				}

				return $value;
			} elseif ( ( is_object( $value ) ) || ( gettype( $value ) === 'object' ) ) {
				try {
					$new_object = clone $value;
					foreach ( $value as $key => $val ) {
						$new_object->$key = self::replace_value( $val, $old_site_url, $new_site_url ); // recurse!
					}

					return $new_object;
				} catch ( Exception $e ) {
					assert(true);
				}
			} elseif ( is_string( $value ) ) {
				return str_replace( $old_site_url, $new_site_url, $value ); // no more recursion
			} else {
				assert(true); //echo "type unknown [". $val ."]<br />";
			}

			return $value;
		}

		/**
		 *
		 * @todo Look for another way.
		 *
		 * @param $remote_url
		 * @param $local_file
		 */
		public static function remote_url_to_local_file( $remote_url, $local_file ) {

			if ( ! file_exists( dirname( $local_file ) ) ) {
				mkdir( dirname( $local_file ), 0777, true );
			}

			if ( file_exists( $local_file ) ) {
				unlink( $local_file );
			}

			global $wp_filesystem;

			if( self::connect_fs() ) {
				$create_file = $wp_filesystem->put_contents( $local_file, '' );

				if ( $create_file ){
					$response = wp_remote_get( $remote_url, array(
										'sslverify' => false
									)
								);
					$wp_filesystem->put_contents( $local_file, $response['body'] );
					return self::size_format( $response['headers']['content-length'] );
				} else {
					echo wp_kses_post( "Unable to open local file [" . $local_file . "] for writing. Check parent folder permissions and reload the page." );
					die();
				}
			} else {
				echo wp_kses_post( "Cannot initialize filesystem." );
				die();
			}

		}

		/**
		 * @param $status
		 *
		 * @return string
		 */
		public static function get_zip_archive_status_string( $status ) {
			switch ( (int) $status ) {
				case ZipArchive::ER_OK           :
					return 'N No error';
				case ZipArchive::ER_MULTIDISK    :
					return 'N Multi-disk zip archives not supported';
				case ZipArchive::ER_RENAME       :
					return 'S Renaming temporary file failed';
				case ZipArchive::ER_CLOSE        :
					return 'S Closing zip archive failed';
				case ZipArchive::ER_SEEK         :
					return 'S Seek error';
				case ZipArchive::ER_READ         :
					return 'S Read error';
				case ZipArchive::ER_WRITE        :
					return 'S Write error';
				case ZipArchive::ER_CRC          :
					return 'N CRC error';
				case ZipArchive::ER_ZIPCLOSED    :
					return 'N Containing zip archive was closed';
				case ZipArchive::ER_NOENT        :
					return 'N No such file';
				case ZipArchive::ER_EXISTS       :
					return 'N File already exists';
				case ZipArchive::ER_OPEN         :
					return 'S Can\'t open file';
				case ZipArchive::ER_TMPOPEN      :
					return 'S Failure to create temporary file';
				case ZipArchive::ER_ZLIB         :
					return 'Z Zlib error';
				case ZipArchive::ER_MEMORY       :
					return 'N Malloc failure';
				case ZipArchive::ER_CHANGED      :
					return 'N Entry has been changed';
				case ZipArchive::ER_COMPNOTSUPP  :
					return 'N Compression method not supported';
				case ZipArchive::ER_EOF          :
					return 'N Premature EOF';
				case ZipArchive::ER_INVAL        :
					return 'N Invalid argument';
				case ZipArchive::ER_NOZIP        :
					return 'N Not a zip archive';
				case ZipArchive::ER_INTERNAL     :
					return 'N Internal error';
				case ZipArchive::ER_INCONS       :
					return 'N Zip archive inconsistent';
				case ZipArchive::ER_REMOVE       :
					return 'S Can\'t remove file';
				case ZipArchive::ER_DELETED      :
					return 'N Entry has been deleted';

				default:
					return sprintf( 'Unknown status %s', $status );
			}
		}

		/**
		 * Is this Snapshot Pro?
		 *
		 * @since 2.5
		 *
		 * @return bool
		 */
		public static function is_pro() {
			return true;
		}

		/**
		 * Check system requirements
		 *
		 * @since 3.1
		 *
		 * @param $requirements
		 *
		 * @return Array
		 */
		public static function check_system_requirements($requirements = array()) {
			$current_timeout = (int)ini_get( 'max_execution_time' );
			$defaults = array(
				'PhpVersion' => array(
					'test' => version_compare(PHP_VERSION, '5.2') >= 0,
					'value' => PHP_VERSION
				),
				'MaxExecTime' => array(
					'test' => 0 >= $current_timeout || $current_timeout >= 150,
					'value' => (int)ini_get('max_execution_time'),
					'warning' => true
				),
				'Mysqli' => array(
					'test' => (bool)function_exists('mysqli_connect'),
				),
				'Zip' => array(
					'test' => defined('SNAPSHOT_FORCE_ZIP_LIBRARY') && 'pclzip' === SNAPSHOT_FORCE_ZIP_LIBRARY
						? true
						: class_exists('ZipArchive')
				)
			);

			$requirements = wp_parse_args( $requirements, $defaults );

			$all_good = true;
			$warning = false;
			foreach ($requirements as $check) {
				if (!empty($check['test'])) continue;
				if (!empty($check['warning']) && ($check['warning'])){
					$warning = true;
					continue;
				}
				$all_good = false;
				break;
			}
			$results = array(
				'checks' => $requirements,
				'warning' => $warning,
				'all_good' => $all_good
				);
			return $results;
		}

		/**
		 * Connect to the filesystem
		 *
		 * @param $url
		 * @param $method
		 * @param $context
		 * @param $fields
		 *
		 * @return bool
		 */
		public static function connect_fs( $url = '', $method = '', $context = '', $fields = null ) {
			global $wp_filesystem;
			$credentials = request_filesystem_credentials($url, $method, false, $context, $fields);
			if( false === ($credentials) ) {
				return false;
			}

			//check if credentials are correct or not.
			if( ! WP_Filesystem( $credentials ) )
			{
				request_filesystem_credentials( $url, $method, true, $context );
				return false;
			}

			return true;
		}

	}
}