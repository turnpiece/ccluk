<?php

require_once( 'minify/class-uri-rewriter.php' );
require_once( 'minify/class-errors-controller.php' );
require_once( 'minify/class-minify-sources-collector.php' );
include_once( 'minify/class-minify-group.php' );
include_once( 'minify/class-minify-groups-list.php' );
include_once( 'minify/class-minify-housekeeper.php' );
include_once( 'minify/class-minify-scanner.php' );

class WP_Hummingbird_Module_Minify extends WP_Hummingbird_Module {

	/**
	 * List of groups to be processed at the end of the request
	 *
	 * @var array
	 */
	private $group_queue = array();

	/**
	 * @var WP_Hummingbird_Sources_Collector
	 */
	public $sources_collector;

	/**
	 * @var WP_Hummingbird_Minification_Errors_Controller
	 */
	public $errors_controller;

	/**
	 * @var WP_Hummingbird_Module_Minify_Housekeeper
	 */
	public $housekeeper;

	/**
	 * @var WP_Hummingbird_Module_Minify_Scanner
	 */
	public $scanner;

	/**
	 * Counter that will name scripts/styles slugs
	 *
	 * @var int
	 */
	private static $counter = 0;

	public $done = array(
		'scripts' => array(),
		'styles'  => array(),
	);

	public $to_footer = array(
		'styles'  => array(),
		'scripts' => array(),
	);

	public function __construct( $slug, $name ) {
		parent::__construct( $slug, $name );
		$this->housekeeper = new WP_Hummingbird_Module_Minify_Housekeeper();
		$this->housekeeper->init();

		$this->scanner = new WP_Hummingbird_Module_Minify_Scanner();
		self::$counter = 0;
	}

	/**
	 * Initializes Minify module
	 */
	public function init() {
		global $pagenow;

		$this->errors_controller = new WP_Hummingbird_Minification_Errors_Controller();
		$this->sources_collector = new WP_Hummingbird_Sources_Collector();

		if ( isset( $_GET['avoid-minify'] ) || 'wp-login.php' === $pagenow ) {
			add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), '__return_false' );
		}

		add_filter( 'wp_hummingbird_is_active_module_' . $this->get_slug(), array( $this, 'should_be_active' ), 20 );

		add_action( 'before_delete_post', array( $this, 'on_delete_post' ), 10 );

		// Process the queue through WP Cron
		add_action( 'wphb_minify_process_queue', array( $this, 'process_queue' ) );

		if ( ( is_multisite() && is_network_admin() ) || ! is_multisite() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_critical_css' ), 5 );
		}
	}

	/**
	 * Delete files attached to a minify group
	 *
	 * @param $post_id
	 */
	public function on_delete_post( $post_id ) {
		$group = WP_Hummingbird_Module_Minify_Group::get_instance_by_post_id( $post_id );

		if ( ( $group instanceof WP_Hummingbird_Module_Minify_Group ) && $group->file_id ) {
			if ( $group->get_file_path() && file_exists( $group->get_file_path() ) ) {
				wp_delete_file( $group->get_file_path() );
			}
			wp_cache_delete( 'wphb_minify_groups' );
		}
	}

	public function should_be_active( $is_active ) {
		if ( ! WP_Hummingbird_Utils::can_execute_php() ) {
			return false;
		}

		return $is_active;
	}

	public function run() {
		global $wp_customize;

		add_action( 'init', array( $this, 'register_cpts' ) );

		if ( is_admin() || is_customize_preview() || ( $wp_customize instanceof WP_Customize_Manager ) ) {
			return;
		}

		// Only minify on front.
		add_filter( 'print_styles_array', array( $this, 'filter_styles' ), 5 );
		add_filter( 'print_scripts_array', array( $this, 'filter_scripts' ), 5 );
		//add_action( 'wp_head', array( $this, 'print_styles' ), 900 );
		//add_action( 'wp_head', array( $this, 'print_scripts' ), 900 );
		//add_action( 'wp_print_footer_scripts', array( $this, 'print_late_resources' ), 900 );

		add_action( 'wp_footer', array( $this, 'trigger_process_queue_cron' ), 10000 );
	}

	/**
	 * Register a new CPT for Assets groups
	 */
	public static function register_cpts() {
		$labels = array(
			'name'          => 'WPHB Minify Groups',
			'singular_name' => 'WPHB Minify Group',
		);

		$args = array(
			'labels'             => $labels,
			'description'        => 'WPHB Minify Groups (internal use)',
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => false,
			'show_in_menu'       => false,
			'query_var'          => false,
			'rewrite'            => false,
			'capability_type'    => 'post',
			'has_archive'        => false,
			'hierarchical'       => false,
			'supports'           => array(),
		);
		register_post_type( 'wphb_minify_group', $args );
	}

	// Used in tests
	public function get_queue_to_process() {
		return $this->group_queue;
	}

	/**
	 * Filter styles
	 *
	 * @param array $handles list of styles slugs
	 *
	 * @return array
	 */
	function filter_styles( $handles ) {
		return $this->filter_enqueues_list( $handles, 'styles' );
	}

	/**
	 * Filter scripts
	 *
	 * @param array $handles list of scripts slugs
	 *
	 * @return array
	 */
	function filter_scripts( $handles ) {
		return $this->filter_enqueues_list( $handles, 'scripts' );
	}

	/**
	 * Filter the sources
	 *
	 * We'll collect those styles/scripts that are going to be
	 * processed by WP Hummingbird and return those that will
	 * be processed by WordPress
	 *
	 * @param array $handles list of scripts/styles slugs
	 * @param string $type scripts|styles
	 *
	 * @return array List of handles that will be processed by WordPress
	 */
	function filter_enqueues_list( $handles, $type ) {
		if ( ! $this->is_active() ) {
			// Asset optimization is not active, return the handles.
			return $handles;
		}

		if ( $this->errors_controller->is_server_error() ) {
			// There seem to be an error in our severs, do not minify.
			return $handles;
		}

		if ( 'styles' == $type ) {
			global $wp_styles;
			$wp_dependencies = $wp_styles;
		} elseif ( 'scripts' == $type ) {
			global $wp_scripts;
			$wp_dependencies = $wp_scripts;
		} else {
			// What is this?
			return $handles;
		}

		//  Nothing to do, return the handles.
		if ( empty( $handles ) ) {
			return $handles;
		}

		$return_to_wp = array();

		// Collect the handles information to use in admin later.
		foreach ( $handles as $key => $handle ) {
			// If this handle has an error, we will return it to WP without processing.
			if ( $this->errors_controller->get_handle_error( $handle, $type ) ) {
				$return_to_wp = array_merge( $return_to_wp, array( $handle ) );
				unset( $handles[ $key ] );
				continue;
			}

			if ( isset( $wp_dependencies->registered[ $handle ] ) && ! empty( $wp_dependencies->registered[ $handle ]->src ) ) {
				$this->sources_collector->add_to_collection( $wp_dependencies->registered[ $handle ], $type );
			}

			// If we aren't in footer, remove handles that need to go to footer.
			if ( ! self::is_in_footer() && isset( $wp_dependencies->registered[ $handle ]->extra['group'] ) && $wp_dependencies->registered[ $handle ]->extra['group'] ) {
				$this->to_footer[ $type ][] = $handle;
				unset( $handles[ $key ] );
			}
		}

		$handles = array_values( $handles );

		if ( self::is_in_footer() && ! empty( $this->to_footer[ $type ] ) ) {
			// Header sent us some handles to be moved to footer.
			$handles = array_unique( array_merge( $handles, $this->to_footer[ $type ] ) );
		}

		// Group dependencies by attributes like args, extra, etc
		$_groups = $this->group_dependencies_by_attributes( $handles, $wp_dependencies, $type );

		// Create a Groups list object
		$groups_list = new WP_Hummingbird_Module_Minify_Groups_List( $type );
		array_map( array( $groups_list, 'add_group' ), $_groups );

		unset( $_groups );

		// Time to split the groups if we're not combining some of them
		foreach ( $groups_list->get_groups() as $group ) {
			/** @var WP_Hummingbird_Module_Minify_Group $group */
			$dont_enqueue_list = $group->get_dont_enqueue_list();
			if ( $dont_enqueue_list ) {
				// There are one or more handles that should not be enqueued
				$group->remove_handles( $dont_enqueue_list );
				if ( 'styles' === $type ) {
					wp_dequeue_style( $dont_enqueue_list );
				} else {
					wp_dequeue_script( $dont_enqueue_list );
				}
			}

			$dont_combine_list = $group->get_dont_combine_list();
			if ( $dont_combine_list ) {
				$split_group = $this->_get_splitted_group_structure_by( 'combine', $group );
				// Split the group!
				$groups_list->split_group( $group->hash, $split_group );
			}

			if ( 'scripts' === $type && $group->get_defer_list() ) {
				$split_group = $this->_get_splitted_group_structure_by( 'defer', $group, false );
				// Split the group!
				$groups_list->split_group( $group->hash, $split_group );
			}

			if ( 'styles' === $type && $group->get_inline_list() ) {
				$split_group = $this->_get_splitted_group_structure_by( 'inline', $group, false );
				// Split the group!
				$groups_list->split_group( $group->hash, $split_group );
			}
		}

		// Set the groups handles, as we need all of them before processing
		foreach ( $groups_list->get_groups() as $group ) {
			$handles = $group->get_handles();
			if ( count( $handles ) === 1 ) {
				// Just one handle, let's keep the handle name as the group ID
				$group->group_id = $handles[0];
			} else {
				$group->group_id = 'wphb-' . ++self::$counter;
			}
			foreach ( $handles as $handle ) {
				$this->done[ $type ][] = $handle;
			}
		}

		if ( 'scripts' === $type ) {
			$this->attach_scripts_localization( $groups_list, $wp_dependencies );
		}
		$this->attach_inline_attribute( $groups_list, $wp_dependencies );

		// Parse dependencies, load files and mark groups as ready,process or only-handles
		// Watch out! Groups must not be changed after this point
		$groups_list->preprocess_groups();

		foreach ( $groups_list->get_groups() as $group ) {
			$group_status = $groups_list->get_group_status( $group->hash );
			$deps = $groups_list->get_group_dependencies( $group->hash );

			if ( 'ready' == $group_status ) {
				// The group has its file and is ready to be enqueued
				$group->enqueue( self::is_in_footer(), $deps );
				$return_to_wp = array_merge( $return_to_wp, array( $group->group_id ) );
			} else {
				// The group has not yet a file attached or it cannot be processed
				// for some reason
				foreach ( $group->get_handles() as $handle ) {
					$new_id = $group->enqueue_one_handle( $handle, self::is_in_footer(), $deps );
					$return_to_wp = array_merge( $return_to_wp, array( $new_id ) );
				}

				if ( 'process' == $group_status ) {
					// Add the group to the queue to be processed later
					if ( $group->should_process_group() ) {
						$this->group_queue[] = $group;
					}
				}
			}
		}

		return $return_to_wp;
	}

	/**
	 * Create a new group structure based on $by parameter
	 *
	 * This will allow later to split groups into new groups based on combination/deferring...
	 *
	 * @param string $by combine|defer|minify...
	 * @param WP_Hummingbird_Module_Minify_Group $group
	 * @param bool $value Value to apply if the handle should be done
	 *
	 * @return array New structure
	 */
	private function _get_splitted_group_structure_by( $by, $group, $value = true ) {
		$handles = $group->get_handles();

		// Here we'll save sources that don't need to be minified/combine/deferred...
		// Then we'll extract those handles from the group and we'll create
		// a new group for them keeping the groups order
		$group_todos = array();
		foreach ( $handles as $handle ) {
			$value = absint( $value );
			$not_value = absint( ! $value );
			$group_todos[ $handle ] = $group->should_do_handle( $handle, $by ) ? $value : $not_value;
		}

		// Now split groups if needed based on $by value
		// We need to keep always the order, ALWAYS
		// This will save the new split group structure
		$split_group = array();

		$last_status = null;
		foreach ( $group_todos as $handle => $status ) {

			// Last minify status will be the first one by default.
			if ( is_null( $last_status ) ) {
				$last_status = $status;
			}

			// Set the split groups to the last element.
			end( $split_group );
			if ( $last_status === $status && 0 !== $status ) {
				$current_key = key( $split_group );
				if ( ! $current_key ) {
					// Current key can be NULL, set to 0
					$current_key = 0;
				}

				if ( ! isset( $split_group[ $current_key ] ) || ! is_array( $split_group[ $current_key ] ) ) {
					$split_group[ $current_key ] = array();
				}

				$split_group[ $current_key ][] = $handle;
			} else {
				// Create a new group.
				$split_group[] = array( $handle );
			}

			$last_status = $status;
		}

		return $split_group;
	}

	/**
	 * Group dependencies by alt, title, rtl, conditional and args attributes
	 *
	 * @param $handles
	 * @param $wp_dependencies
	 * @param $type
	 *
	 * @return array
	 */
	private function group_dependencies_by_attributes( $handles, $wp_dependencies, $type ) {
		$groups = array();
		$prev_differentiators_hash = false;

		foreach ( $handles as $handle ) {
			$registered_dependency = isset( $wp_dependencies->registered[ $handle ] ) ? $wp_dependencies->registered[ $handle ] : false;
			if ( ! $registered_dependency ) {
				continue;
			}

			if ( ! self::is_in_footer() ) {
				/**
				 * Filter the resource (move to footer)
				 *
				 * @usedby wphb_filter_resource_to_footer()
				 *
				 * @var bool $send_resource_to_footer
				 * @var string $handle Source slug
				 * @var string $type scripts|styles
				 * @var string $source_url Source URL
				 */
				if ( apply_filters( 'wphb_send_resource_to_footer', false, $handle, $type, $wp_dependencies->registered[ $handle ]->src ) ) {
					// Move this to footer, do not take this handle in account for this iteration
					$this->to_footer[ $type ][] = $handle;
					continue;
				}
			}

			// We'll group by these extras $wp_style->extras and $wp_style->args (args is no more than a string, confusing)
			// If previous group has the same values, we'll add this dep it to that group
			// otherwise, a new group will be created
			$group_extra_differentiators = array( 'alt', 'title', 'rtl', 'conditional' );
			$group_differentiators = array( 'args' );

			// We'll create a hash for all differentiators
			$differentiators_hash = array();
			foreach ( $group_extra_differentiators as $differentiator ) {
				if ( isset( $registered_dependency->extra[ $differentiator ] ) ) {
					if ( is_bool( $registered_dependency->extra[ $differentiator ] ) && $registered_dependency->extra[ $differentiator ] ) {
						$differentiators_hash[] = 'true';
					} elseif ( is_bool( $registered_dependency->extra[ $differentiator ] ) && ! $registered_dependency->extra[ $differentiator ] ) {
						$differentiators_hash[] = 'false';
					} else {
						$differentiators_hash[] = (string) $registered_dependency->extra[ $differentiator ];
					}
				} else {
					$differentiators_hash[] = '';
				}
			}

			foreach ( $group_differentiators as $differentiator ) {
				if ( isset( $registered_dependency->$differentiator ) ) {
					if ( is_bool( $registered_dependency->$differentiator ) && $registered_dependency->$differentiator ) {
						$differentiators_hash[] = 'true';
					} elseif ( is_bool( $registered_dependency->$differentiator ) && ! $registered_dependency->$differentiator ) {
						$differentiators_hash[] = 'false';
					} else {
						$differentiators_hash[] = (string) $registered_dependency->$differentiator;
					}
				} else {
					$differentiators_hash[] = '';
				}
			}

			$differentiators_hash = implode( '-', $differentiators_hash );

			// Now compare the hash with the previous one
			// If they are the same, do not create a new group
			if ( $differentiators_hash !== $prev_differentiators_hash ) {
				$new_group = new WP_Hummingbird_Module_Minify_Group();
				$new_group->set_type( $type );
				foreach ( $registered_dependency->extra as $key => $value ) {
					$new_group->add_extra( $key, $value );
				}

				// We'll treat this later.
				$new_group->delete_extra( 'after' );
				$new_group->delete_extra( 'before' );
				$new_group->delete_extra( 'data' );

				$new_group->set_args( $registered_dependency->args );

				if ( $registered_dependency->src ) {
					$new_group->add_handle( $handle, $registered_dependency->src );

					// Add dependencies.
					$new_group->add_handle_dependency( $handle, $wp_dependencies->registered[ $handle ]->deps );
				}

				$groups[] = $new_group;
			} else {
				end( $groups );
				$last_key = key( $groups );
				$groups[ $last_key ]->add_handle( $handle, $registered_dependency->src );
				// Add dependencies.
				$groups[ $last_key ]->add_handle_dependency( $handle, $registered_dependency->deps );
				reset( $groups );
			}

			$prev_differentiators_hash = $differentiators_hash;
		} // End foreach().

		// Remove group without handles.
		$return = array();
		foreach ( $groups as $key => $group ) {
			if ( $group->get_handles() ) {
				$return[ $key ] = $group;
			}
		}

		return $return;
	}

	/**
	 * Attach inline scripts/styles to groups
	 *
	 * Extract all deps that has inline scripts/styles (added by wp_add_inline_script/style functions)
	 * then it will add those extras to the groups
	 *
	 * @param WP_Hummingbird_Module_Minify_Groups_List $groups_list
	 * @param $wp_dependencies
	 */
	private function attach_inline_attribute( &$groups_list, $wp_dependencies ) {
		$registered = $wp_dependencies->registered;
		$extras = wp_list_pluck( $registered, 'extra' );
		$after = wp_list_pluck( array_filter( $extras, array( $this, '_filter_after_after_attribute' ) ), 'after' );
		$before = wp_list_pluck( array_filter( $extras, array( $this, '_filter_after_before_attribute' ) ), 'before' );

		array_map( function( $group ) use ( $groups_list, $after, $before ) {
			/** @var WP_Hummingbird_Module_Minify_Group $group */
			array_map( function( $handle ) use ( $after, $group, $before ) {
				if ( isset( $after[ $handle ] ) ) {
					// Add!
					$group->add_after( $after[ $handle ] );
				}
				if ( isset( $before[ $handle ] ) ) {
					// Add!
					$group->add_before( $before[ $handle ] );
				}
			}, $group->get_handles() );
		}, $groups_list->get_groups() );
	}

	/**
	 * Attach localization scripts to groups
	 *
	 * @param WP_Hummingbird_Module_Minify_Groups_List $groups_list
	 * @param $wp_dependencies
	 */
	private function attach_scripts_localization( &$groups_list, $wp_dependencies ) {
		$registered = $wp_dependencies->registered;
		$extra = wp_list_pluck( $registered, 'extra' );
		$data = wp_list_pluck( array_filter( $extra, function( $a ) {
			if ( isset( $a['data'] ) ) {
				return $a['data'];
			}
			return false;
		} ), 'data' );

		array_map( function( $group ) use ( $groups_list, $data ) {
			/** @var WP_Hummingbird_Module_Minify_Group $group */
			array_map( function( $handle ) use ( $data, $group ) {
				if ( isset( $data[ $handle ] ) ) {
					// Add!
					$group->add_data( $data[ $handle ] );
				}
			}, $group->get_handles() );
		}, $groups_list->get_groups() );
	}

	/**
	 * Filter a list of dependencies returning their 'after' attribute inside 'extra' list
	 *
	 * @internal
	 *
	 * @param $a
	 *
	 * @return bool
	 */
	public function _filter_after_after_attribute( $a ) {
		if ( isset( $a['after'] ) ) {
			return $a['after'];
		}
		return false;
	}

	/**
	 * Filter a list of dependencies returning their 'before' attribute inside 'extra' list
	 *
	 * @internal
	 *
	 * @param $a
	 *
	 * @return bool
	 */
	public function _filter_after_before_attribute( $a ) {
		if ( isset( $a['before'] ) ) {
			return $a['before'];
		}
		return false;
	}

	/**
	 * Return if we are processing the footer
	 *
	 * @return bool
	 */
	public static function is_in_footer() {
		return doing_action( 'wp_footer' ) || doing_action( 'wp_print_footer_scripts' );
	}

	/**
	 * Trigger the action to process the queue
	 */
	public function trigger_process_queue_cron() {
		// Trigger que the queue hrough WP CRON so we don't waste load time
		$this->sources_collector->save_collection();

		$queue = $this->get_queue_to_process();
		$this->add_items_to_persistent_queue( $queue );
		$queue = $this->get_pending_persistent_queue();
		if ( empty( $queue ) ) {
			return;
		}

		if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) {
			$this->process_queue();
		} else {
			self::schedule_process_queue_cron();
		}
	}

	/**
	 * Process the queue: Minify and combine files
	 */
	public function process_queue() {
		// Process the queue.
		if ( get_transient( 'wphb-processing' ) ) {
			// Still processing. Try again.
			if ( ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
				self::schedule_process_queue_cron();
			}
			return;
		}

		$queue = $this->get_pending_persistent_queue();

		set_transient( 'wphb-processing', true, 60 );
		// Process 10 groups max in a request.
		$count = 0;

		$new_queue = $queue;
		foreach ( $queue as $key => $item ) {
			if ( $count >= 8 ) {
				break;
			}
			if ( ! ( $item instanceof WP_Hummingbird_Module_Minify_Group ) ) {
				continue;
			}

			/** @var WP_Hummingbird_Module_Minify_Group $item */
			if ( $item->should_generate_file() ) {
				$result = $item->process_group();
				if ( is_wp_error( $result ) ) {
					$this->errors_controller->add_server_error( $result );
				}
			}
			$this->remove_item_from_persistent_queue( $item->hash );
			unset( $new_queue[ $key ] );
			$count++;
		}

		if ( ! ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) ) {
			$new_queue = array_values( $new_queue );
			if ( ! empty( $new_queue ) ) {
				// Still needs processing
				self::schedule_process_queue_cron();
			}
		}

		delete_transient( 'wphb-processing' );
	}

	public static function schedule_process_queue_cron() {
		if ( ! wp_next_scheduled( 'wphb_minify_process_queue' ) ) {
			wp_schedule_single_event( time(), 'wphb_minify_process_queue' );
		}
	}

	/**
	 * Save a list of groups to a persistent option in database
	 *
	 * If a timeout happens during groups processing, we won't loose
	 * the data needed to process the rest of groups
	 *
	 * @param array $items
	 */
	private function add_items_to_persistent_queue( $items ) {
		if ( empty( $items ) ) {
			// Nothing to be added.
			return;
		}
		$current_queue = $this->get_pending_persistent_queue();
		if ( empty( $current_queue ) ) {
			update_option( 'wphb_process_queue', $items );
		} else {
			$updated = false;
			$current_queue_hashes = wp_list_pluck( $current_queue, 'hash' );
			foreach ( $items as $item ) {
				if ( ! in_array( $item->hash, $current_queue_hashes ) ) {
					$updated = true;
					$current_queue[] = $item;
				}
			}
			if ( $updated ) {
				update_option( 'wphb_process_queue', $current_queue );
			}
		}
	}

	/**
	 * Remove a group from the persistent queue
	 *
	 * @param string $hash
	 */
	private function remove_item_from_persistent_queue( $hash ) {
		$queue = $this->get_pending_persistent_queue();
		$items = wp_list_filter( $queue, array(
			'hash' => $hash,
		) );

		if ( ! $items ) {
			return;
		}

		$keys = array_keys( $items );
		foreach ( $keys as $key ) {
			unset( $queue[ $key ] );
		}

		$queue = array_values( $queue );

		if ( empty( $queue ) ) {
			$this->delete_pending_persistent_queue();
			return;
		}

		update_option( 'wphb_process_queue', $queue );
	}

	/**
	 * Get the list of groups that are yet pending to be processed
	 */
	public function get_pending_persistent_queue() {
		return get_option( 'wphb_process_queue', array() );
	}

	/**
	 * Deletes the persistent queue completely
	 */
	public function delete_pending_persistent_queue() {
		delete_option( 'wphb_process_queue' );
	}

	/**
	 * Implement abstract parent method for clearing cache.
	 *
	 * Clear the module cache.
	 *
	 * @param bool $reset_settings If set to true will set Asset Optimization settings to default (that includes files positions).
	 *
	 * @return mixed
	 */
	public function clear_cache( $reset_settings = true ) {
		global $wpdb;

		if ( ! WP_Hummingbird_Utils::can_execute_php() ) {
			return;
		}

		// Clear all the cached groups data.
		$option_names = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT option_name FROM $wpdb->options
				WHERE option_name LIKE %s
				OR option_name LIKE %s
				OR option_name LIKE %s
				OR option_name LIKE %s",
				'%wphb-min-scripts%',
				'%wphb-scripts%',
				'%wphb-min-styles%',
				'%wphb-styles%'
			)
		);

		foreach ( $option_names as $name ) {
			delete_option( $name );
		}

		$this->clear_files();

		if ( $reset_settings ) {
			// This one when cleared will trigger a new scan.
			WP_Hummingbird_Sources_Collector::clear_collection();

			// Reset the minification settings.
			$options = $this->get_options();
			$default_options = WP_Hummingbird_Settings::get_default_settings();
			$options['block']       = $default_options['minify']['block'];
			$options['dont_minify'] = $default_options['minify']['dont_minify'];
			$options['combine']     = $default_options['minify']['combine'];
			$options['position']    = $default_options['minify']['position'];
			$this->update_options( $options );
		}

		// Clear the pending process queue.
		self::clear_pending_process_queue();

		$this->scanner->reset_scan();

		WP_Hummingbird_Minification_Errors_Controller::clear_errors();
	}

	public function reset() {
		if ( ! WP_Hummingbird_Utils::can_execute_php() ) {
			return;
		}

		$this->clear_files();

		// Reset the minification settings.
		$options = $this->get_options();
		$default_options = WP_Hummingbird_Settings::get_default_settings();
		$options['block']       = $default_options['minify']['block'];
		$options['dont_minify'] = $default_options['minify']['dont_minify'];
		$options['combine']     = $default_options['minify']['combine'];
		$options['position']    = $default_options['minify']['position'];
		$this->update_options( $options );

		// Clear the pending process queue.
		self::clear_pending_process_queue();

		$this->scanner->reset_scan();

		WP_Hummingbird_Minification_Errors_Controller::clear_errors();
	}

	public static function clear_pending_process_queue() {
		delete_option( 'wphb_process_queue' );
		delete_transient( 'wphb-processing' );
	}

	/***************************
	 *
	 * HELPER FUNCTIONS
	 *
	 ***************************/

	/**
	 * Clear minified group files
	 */
	public function clear_files() {
		$groups = WP_Hummingbird_Module_Minify_Group::get_minify_groups();

		foreach ( $groups as $group ) {
			// This will also delete the file. See WP_Hummingbird_Module_Minify::on_delete_post().
			wp_delete_post( $group->ID );
		}

		wp_cache_delete( 'wphb_minify_groups' );
	}

	/**
	 * Get all resources collected
	 *
	 * This collection is displayed in minification admin page
	 */
	public function get_resources_collection() {
		$collection = WP_Hummingbird_Sources_Collector::get_collection();
		$posts = WP_Hummingbird_Module_Minify_Group::get_minify_groups();
		foreach ( $posts as $post ) {
			$group = WP_Hummingbird_Module_Minify_Group::get_instance_by_post_id( $post->ID );
			if ( ! $group ) {
				continue;
			}
			foreach ( $group->get_handles() as $handle ) {
				if ( isset( $collection[ $group->type ][ $handle ] ) ) {
					$collection[ $group->type ][ $handle ]['original_size'] = $group->get_handle_original_size( $handle );
					$collection[ $group->type ][ $handle ]['compressed_size'] = $group->get_handle_compressed_size( $handle );
				}
			}
		}

		return $collection;
	}

	/**
	 * Init minification scan.
	 */
	public function init_scan() {
		$this->clear_cache( false );

		// Activate minification if is not.
		$this->toggle_service( true );

		// Init scan.
		$this->scanner->init_scan();
	}

	/**
	 * Check if minification scan is running.
	 *
	 * @return bool
	 */
	public function is_scanning() {
		if ( WP_Hummingbird_Utils::can_execute_php() ) {
			return $this->scanner->is_scanning();
		}
		return false;
	}

	/**
	 * Toggle minification.
	 *
	 * @param bool $value   Value for minification. Accepts boolean value: true or false.
	 * @param bool $network Value for network. Default: false.
	 */
	public function toggle_service( $value, $network = false ) {
		$options = $this->get_options();

		if ( is_multisite() ) {
			if ( $network ) {
				// Updating for the whole network.
				$options['enabled'] = $value;
				// If deactivated for whole network, also deactivate CDN.
				if ( false === $value ) {
					$options['use_cdn'] = false;
				}
			} else {
				// Updating on subsite.
				if ( ! $options['enabled'] ) {
					// Asset optimization is turned down for the whole network, do not activate it per site.
					$options['minify_blog'] = false;
				} else {
					$options['minify_blog'] = $value;
				}
			}
		} else {
			$options['enabled'] = $value;
		}

		$this->update_options( $options );
	}

	/**
	 * Toggle CDN helper function.
	 *
	 * @param bool $value  CDN status to set.
	 */
	public function toggle_cdn( $value ) {
		$options = $this->get_options();
		$options['use_cdn'] = $value;
		$this->update_options( $options );
	}

	/**
	 * Get CDN status.
	 *
	 * @since  1.5.2
	 * @return bool
	 */
	public function get_cdn_status() {
		$options = $this->get_options();
		return $options['use_cdn'];
	}

	/**
	 * Enqueue critical CSS file (css above the fold).
	 *
	 * @since 1.8
	 */
	public function enqueue_critical_css() {
		$src = WPHB_DIR_PATH . 'admin/assets/css/critical.css';

		// If file does not exist or is empty.
		if ( ! file_exists( $src ) ) {
			return;
		}

		$content = file_get_contents( $src );
		if ( empty( $content ) ) {
			return;
		}

		wp_register_style( 'wphb-critical-css', WPHB_DIR_URL . 'admin/assets/css/critical.css' );
		wp_enqueue_style( 'wphb-critical-css' );
	}

	/**
	 * Get css file content for critical css file.
	 *
	 * @since 1.8
	 *
	 * @return string
	 */
	public static function get_css() {
		$src = WPHB_DIR_PATH. 'admin/assets/css/critical.css';

		if ( ! file_exists( $src ) ) {
			return '';
		}

		if ( ! $content = file_get_contents( $src ) ) {
			return '';
		}

		return $content;
	}

	/**
	 * Save critical css file (css above the fold).
	 *
	 * @since 1.8
	 *
	 * @param $content
	 *
	 * @return array
	 */
	public static function save_css( $content ) {
		if ( ! is_string( $content ) ) {
			return array(
				'success' => false,
				'message' => __( 'Unsupported content', 'wphb' ),
			);
		}

		$wphb_fs = WP_Hummingbird_Filesystem::instance();

		if ( is_wp_error( $wphb_fs->status ) ) {
			return array(
				'success' => false,
				'message' => __( 'Error saving file', 'wphb' ),
			);
		}

		$file = WPHB_DIR_PATH. 'admin/assets/css/critical.css';

		$status = $wphb_fs->write( $file, $content );

		if ( is_wp_error( $status ) ) {
			return array(
				'success' => false,
				'message' => $status->get_error_message(),
			);
		}

		return array(
			'success' => true,
			'message' => __( 'Settings updated', 'wphb' ),
		);
	}

}