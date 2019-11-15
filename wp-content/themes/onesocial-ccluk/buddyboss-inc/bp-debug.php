<?php
/*
Plugin Name: BP Footer Debug
Description: For developers, output some useful BuddyPress global variables in the footer.
Author: r-a-y
Author URI: http://profiles.wordpress.org/r-a-y
Version: 0.1
License: GPLv2 or later
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'BP_Footer_Debug' ) ) :

class BP_Footer_Debug {

  public $version = '0.1';

	/**
	 * Init method.
	 */
	public static function init() {
		return new self();
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		// don't do anything if we're not on a BuddyPress page
		if ( ! is_buddypress() )
			return;

		// don't show anything if the logged-in user is not an admin
		if ( ! is_super_admin() )
			return;

		add_action( 'wp_head',   array( $this, 'head' ) );
		add_action( 'wp_footer', array( $this, 'footer' ) );
	}

	/**
	 * Inline CSS.
	 */
	public function head() {
	?>

	<style type="text/css">
	#bp-footer-debug {
		position: fixed;
		left: 50%;
		bottom: 0;
		z-index: 1000;
		margin-left: -100px;
		padding: 10px 15px;
		background: #595959;
		opacity: .75;
		color: #fff;
		font-size: 11px;
		text-align: left;
	}
	</style>

	<?php
	}

	/**
	 * Output our debug info in the footer.
	 */
	public function footer() {
		if ( ! self::debug_info() )
			return;
	?>

	<div id="bp-footer-debug">
		<pre><?php print_r( self::debug_info() ); ?></pre>
	</div>

	<?php
	}

	/**
	 * Helper static method to grab debug info.
	 */
	public static function debug_info() {
		$retval = array();

		if ( bp_current_component() && bp_is_user() ) {
			$retval['component'] = bp_current_component();
		}

		if ( bp_current_action() ) {
			$retval['action'] = bp_current_action();
		}

		if ( bp_action_variables() ) {
			$retval['action_variables'] = bp_action_variables();
		}

		if ( bp_is_group() ) {
			$group = groups_get_current_group();

			$retval['slug']   = $group->slug;
			$retval['status'] = $group->status;
		}

		return apply_filters( 'bp_footer_debug_info', $retval );
	}
}

add_action( 'bp_init', array( 'BP_Footer_Debug', 'init' ) );

endif;