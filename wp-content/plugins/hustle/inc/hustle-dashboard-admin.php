<?php


class Hustle_Dashboard_Admin extends Opt_In
{

	private $_data;

	function __construct()
	{
		add_action( 'admin_menu', array( $this, "register_menus" ), 1 );
		add_filter("hustle_optin_vars", array( $this, "register_dashboard_vars" ));
		if( $this->_is_dashboard() )
			$this->_data = new Hustle_Dashboard_Data();
	}

	function register_menus(){

		$parent_menu_title = ( Opt_In_Utils::_is_free() )
			? __("Hustle", Opt_In::TEXT_DOMAIN)
			: __("Hustle Pro", Opt_In::TEXT_DOMAIN);

		// Parent menu
		add_menu_page( $parent_menu_title , $parent_menu_title , "manage_options", "hustle", array( $this, 'render_dashboard' ), self::$plugin_url . 'assets/img/icon.svg');

		// Dashboard
		add_submenu_page( 'hustle', __("Dashboard", Opt_In::TEXT_DOMAIN) , __("Dashboard", Opt_In::TEXT_DOMAIN) , "manage_options", 'hustle',  array( $this, "render_dashboard" )  );
	}
	/**
	 * Renders Hustle Dashboard
	 *
	 * @since 2.0
	 */
	function render_dashboard(){
		$current_user = wp_get_current_user();

		$modules_except_ss = count($this->_data->popups) + count($this->_data->slideins) + count($this->_data->embeds);

		$this->render("admin/dashboard", array(
			'user_name' => ucfirst($current_user->display_name),
			'today_total_conversions' => $this->_data->today_total_conversions,
			'most_converted_module' => $this->_data->most_converted_module,
			'top_active_modules' => $this->_data->top_active_modules,
			'active_modules' => $this->_data->active_modules,
			'popups' => $this->_data->popups,
			'slideins' => $this->_data->slideins,
			'embeds' => $this->_data->embeds,
			'social_shares' => $this->_data->social_sharings,
			'ss_share_stats_data' => $this->_data->ss_share_stats_data,
			'ss_total_share_stats' => $this->_data->ss_total_share_stats,
			'has_modules' => ( $modules_except_ss > 0 ) ? true : false,
			'is_free' => Opt_In::is_free()
		));
	}

	/**
	 * Checks if it's optin admin page
	 *
	 * @return bool
	 */
	private function _is_dashboard(){
		return isset( $_GET['page'] ) &&  ( in_array($_GET['page'], array(
			'hustle_dashboard',
			'hustle'
		   ) ) );
	}
	function register_dashboard_vars( $vars ){
		if( !$this->_is_dashboard() ) return $vars;

		$vars['top_active_modules'] = $this->_data->top_active_modules;
		$vars['graph_date_conversions'] = $this->_data->graph_date_conversions;
		$vars['graph_dates'] = $this->_data->graph_dates;
		$vars['graph_max_date'] = date("Y-m-d");
		$vars['graph_min_date'] = date( "Y-m-d", strtotime( date("Y-m-d") . " -1 month" ) );
		return $vars;
	}
}