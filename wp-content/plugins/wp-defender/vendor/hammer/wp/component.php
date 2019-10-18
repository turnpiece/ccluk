<?php
/**
 * Author: Hoang Ngo
 */

namespace Hammer\WP;

use Hammer\Helper\WP_Helper;

class Component extends \Hammer\Base\Controller {
	/**
	 * Returns the callback array for the specified method
	 *
	 * @param string $tag The tag that is addressed by the callback.
	 * @param string|array $method The callback method.
	 *
	 * @return array A working callback.
	 * @since  1.0.0
	 *
	 */
	private function get_callback( $tag, $method ) {
		if ( is_array( $method ) ) {
			$callback = $method;
		} else {
			$callback = array( $this, ! empty( $method ) ? $method : $tag );
		}

		return $callback;
	}

	/**
	 * Registers an action hook.
	 *
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param int $priority optional. Used to specify the order in which the
	 *         functions associated with a particular action are executed
	 *         (default: 10). Lower numbers correspond with earlier execution,
	 *         and functions with the same priority are executed in the order in
	 *         which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function
	 *         accept (default 1).
	 *
	 * @return Component The Object.
	 * @uses add_action() To register action hook.
	 *
	 * @since  1.0.0
	 *
	 */
	protected function addAction( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		add_action(
			$tag,
			$this->get_callback( $tag, $method ),
			$priority,
			$accepted_args
		);

		return $this;
	}

	/**
	 * Executes the callback function instantly if the specified action was
	 * already fired. If the action was not fired yet then the action handler
	 * is registered via add_action().
	 *
	 * Important note:
	 * If the callback is executed instantly, then the functionr receives NO
	 * parameters!
	 *
	 * @param string $tag
	 * @param string $method
	 * @param int $priority
	 * @param int $accepted_args
	 *
	 * @return Component
	 * @uses add_action() To register action hook.
	 *
	 * @since  1.0.0
	 *
	 */
	protected function run_action( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		$callback = $this->get_callback( $tag, $method );

		if ( did_action( $tag ) ) {
			// Note: No argument is passed to the callback!
			call_user_func( $callback );
		} else {
			add_action(
				$tag,
				$callback,
				$priority,
				$accepted_args
			);
		}

		return $this;
	}

	/**
	 * Removes an action hook.
	 *
	 * @param string $tag The name of the action to which the $method is hooked.
	 * @param string $method The name of the method to be called.
	 * @param int $priority optional. Used to specify the order in which the
	 *         functions associated with a particular action are executed
	 *         (default: 10). Lower numbers correspond with earlier execution,
	 *         and functions with the same priority are executed in the order in
	 *         which they were added to the action.
	 *
	 * @return Component
	 * @since  1.0.0
	 * @uses remove_action() To remove action hook.
	 *
	 */
	protected function remove_action( $tag, $method = null, $priority = 10 ) {
		if ( null === $method ) {
			remove_all_actions( $tag );
		} else {
			remove_action(
				$tag,
				$this->get_callback( $tag, $method ),
				$priority
			);
		}

		return $this;
	}

	/**
	 * Registers AJAX action hook.
	 *
	 * @param string $tag The name of the AJAX action to which the $method is
	 *         hooked.
	 * @param string $method Optional. The name of the method to be called.
	 *         If the name of the method is not provided, tag name will be used
	 *         as method name.
	 * @param boolean $private Optional. Determines if we should register hook
	 *         for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook
	 *         for not logged in users.
	 *
	 * @return Component
	 * @since  1.0.0
	 *
	 */
	protected function addAjaxAction( $tag, $method = '', $private = true, $public = false ) {
		if ( $private ) {
			$this->run_action( 'wp_ajax_' . $tag, $method );
		}

		if ( $public ) {
			$this->run_action( 'wp_ajax_nopriv_' . $tag, $method );
		}

		return $this;
	}

	/**
	 * This will register all the endpoints as ajaxpriv
	 * Maybe upgrade to wp restful but it later call
	 *
	 * @param $endpoints
	 * @param $module
	 */
	protected function registerEndpoints( $endpoints, $module ) {
		$cached = array();
		foreach ( $endpoints as $endpoint => $callback ) {
			$this->addAjaxAction( $endpoint, $callback );
			$cached[ $callback ] = $endpoint;
		}

		$list = (array) WP_Helper::getArrayCache()->get( 'endpoints' );
		if ( ! isset( $list[ $module ] ) ) {
			$list[ $module ] = array();
		}
		$cached          = array_merge( $list[ $module ], $cached );
		$list[ $module ] = $cached;
		WP_Helper::getArrayCache()->set( 'endpoints', $list );
	}

	/**
	 * Removes AJAX action hook.
	 *
	 * @param string $tag The name of the AJAX action to which the $method is
	 *         hooked.
	 * @param string $method Optional. The name of the method to be called. If
	 *         the name of the method is not provided, tag name will be used as
	 *         method name.
	 * @param boolean $private Optional. Determines if we should register hook
	 *         for logged in users.
	 * @param boolean $public Optional. Determines if we should register hook
	 *         for not logged in users.
	 *
	 * @return Component
	 * @since  1.0.0
	 *
	 */
	protected function remove_ajax_action( $tag, $method = null, $private = true, $public = false ) {
		if ( $private ) {
			$this->remove_action( 'wp_ajax_' . $tag, $method );
		}

		if ( $public ) {
			$this->remove_action( 'wp_ajax_nopriv_' . $tag, $method );
		}

		return $this;
	}

	/**
	 * Registers a filter hook.
	 *
	 * @param string $tag The name of the filter to hook the $method to.
	 * @param string $method The name of the method to be called when the
	 *         filter is applied.
	 * @param int $priority optional. Used to specify the order in which the
	 *         functions associated with a particular action are executed
	 *         (default: 10). Lower numbers correspond with earlier execution,
	 *         and functions with the same priority are executed in the order in
	 *         which they were added to the action.
	 * @param int $accepted_args optional. The number of arguments the function
	 *         accept (default 1).
	 *
	 * @return Component
	 * @uses add_filter() To register filter hook.
	 *
	 * @since  1.0.0
	 *
	 */
	protected function addFilter( $tag, $method = '', $priority = 10, $accepted_args = 1 ) {
		$args = func_get_args();

		add_filter(
			$tag,
			$this->get_callback( $tag, $method ),
			$priority,
			$accepted_args
		);

		return $this;
	}

	/**
	 * Removes a filter hook.
	 *
	 * @param string $tag The name of the filter to remove the $method to.
	 * @param string $method The name of the method to remove.
	 * @param int $priority optional. The priority of the function (default: 10).
	 *
	 * @return Component
	 * @since  1.0.0
	 *
	 * @uses remove_filter() To remove filter hook.
	 *
	 */
	protected function remove_filter( $tag, $method = null, $priority = 10 ) {
		if ( null === $method ) {
			remove_all_filters( $tag );
		} else {
			remove_filter(
				$tag,
				$this->get_callback( $tag, $method ),
				$priority
			);
		}

		return $this;
	}

	/**
	 * Unbinds all hooks previously registered for actions and/or filters.
	 *
	 * @param boolean $actions Optional. TRUE to unbind all actions hooks.
	 * @param boolean $filters Optional. TRUE to unbind all filters hooks.
	 *
	 * @since  1.0.0
	 *
	 */
	public function unbind( $actions = true, $filters = true ) {
		$types = array();

		if ( $actions ) {
			$types['actions'] = 'remove_action';
		}

		if ( $filters ) {
			$types['filters'] = 'remove_filter';
		}

		foreach ( $types as $hooks => $method ) {
			foreach ( $this->$hooks as $hook ) {
				call_user_func_array( $method, $hook );
			}
		}
	}

	/**
	 * Register a shortcode
	 *
	 * @param $tag
	 * @param $method
	 */
	public function add_shortcode( $tag, $method ) {
		add_shortcode( $tag, $this->get_callback( $tag, $method ) );
	}

	/**
	 * Shorthand to check if current request is ajax
	 * @return bool
	 */
	public function is_ajax() {
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if this plugin activate for network wide
	 *
	 * @param $slug
	 *
	 * @return bool
	 */
	public function isNetworkActivate( $slug ) {
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}

		return is_plugin_active_for_network( $slug );
	}
}