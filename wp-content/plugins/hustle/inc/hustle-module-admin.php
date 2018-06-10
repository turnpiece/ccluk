<?php
if( !class_exists("Hustle_Module_Admin") ):

/**
 * Class Hustle_Module_Admin
 */
class Hustle_Module_Admin {

	const ADMIN_PAGE = 'hustle';
	const DASHBOARD_PAGE = 'hustle_dashboard';
	const POPUP_LISTING_PAGE = 'hustle_popup_listing';
	const POPUP_WIZARD_PAGE = 'hustle_popup';
	const SLIDEIN_LISTING_PAGE = 'hustle_slidein_listing';
	const SLIDEIN_WIZARD_PAGE = 'hustle_slidein';
	const EMBEDDED_LISTING_PAGE = 'hustle_embedded_listing';
	const EMBEDDED_WIZARD_PAGE = 'hustle_embedded';
	const SOCIAL_SHARING_LISTING_PAGE = 'hustle_sshare_listing';
	const SOCIAL_SHARING_WIZARD_PAGE = 'hustle_sshare';
	const SETTINGS_PAGE = 'hustle_settings';
	const UPGRADE_PAGE = 'hustle_upgrade';

	private $_hustle;

	function __construct( Opt_In $hustle ){

		$this->_hustle = $hustle;

		add_action( 'admin_init', array( $this, "init" ) );
		add_action("current_screen", array( $this, "set_proper_current_screen" ) );

		if( $this->_is_admin_module() ) {
			add_action( 'admin_enqueue_scripts', array( $this, "register_scripts" ), 99 );
			add_action( 'admin_print_styles', array( $this, "register_styles" ) );
			// add_action("admin_footer", array($this, "add_layout_templates"));
			add_filter( 'admin_body_class', array( $this, 'admin_body_class' ), 99 );
			add_filter("user_can_richedit", '__return_true'); // allow rich editor in
			add_filter( 'tiny_mce_before_init', array( $this, 'set_tinymce_settings' ) );
			add_filter("wp_default_editor", array( $this, 'set_editor_to_tinymce' ));
			add_filter("tiny_mce_plugins", array( $this, 'remove_despised_editor_plugins' ));

		}

		add_filter( 'w3tc_save_options', array( $this, 'filter_w3tc_save_options' ), 10, 1 );
		add_filter('plugin_action_links', array( $this, 'add_plugin_action_links' ), 10, 5 );

	}

	// force reject minify for hustle js and css
	function filter_w3tc_save_options( $config ) {

		// reject js
		$defined_rejected_js = $config['new_config']->get("minify.reject.files.js");
		$reject_js = array(
			$this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/admin.min.js',
			$this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/ad.js',
			$this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/front.min.js'
		);
		foreach( $reject_js as $r_js ) {
			if ( !in_array( $r_js, $defined_rejected_js ) ) {
				array_push($defined_rejected_js, $r_js);
			}
		}
		$config['new_config']->set("minify.reject.files.js", $defined_rejected_js);

		// reject css
		$defined_rejected_css = $config['new_config']->get("minify.reject.files.css");
		$reject_css = array(
			$this->_hustle->get_static_var( "plugin_url" ) . 'assets/css/front.min.css',
			$this->_hustle->get_static_var( "plugin_url" ) . 'assets/css/admin.min.css',
		);
		foreach( $reject_css as $r_css ) {
			if ( !in_array( $r_css, $defined_rejected_css ) ) {
				array_push($defined_rejected_css, $r_css);
			}
		}
		$config['new_config']->set("minify.reject.files.css", $defined_rejected_css);

		return $config;
	}

	/**
	 * Removes unnecessary editor plugins
	 *
	 * @param $plugins
	 * @return mixed
	 */
	function remove_despised_editor_plugins( $plugins ){

		if( ( $k = array_search( "fullscreen", $plugins) ) !== false ){
			unset( $plugins[ $k ] );
		}
		$plugins[] = "paste";
		return $plugins;
	}

	/**
	 * Sets default editor to tinymce for opt-in admin
	 *
	 * @param $editor_type
	 * @return string
	 */
	function set_editor_to_tinymce( $editor_type ){
		return "tinymce";
	}

	/**
	 * Inits admin
	 *
	 * @since 3.0
	 */
	function init(){


		return;
	}

	/**
	 * Register scripts for the admin page
	 *
	 * @since 1.0
	 */
	function register_scripts(){

		/**
		 * Register popup requirements
		 */
		lib3()->ui->add( TheLib_Ui::MODULE_CORE );
		lib3()->ui->add( TheLib_Ui::MODULE_SELECT );
		lib3()->ui->add( TheLib_Ui::MODULE_ANIMATION );

		wp_enqueue_script('thickbox');
		wp_enqueue_media();
		wp_enqueue_script('media-upload');
		wp_enqueue_script('jquery-ui-sortable');

		wp_register_script( 'optin_admin_ace', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/vendor/ace/ace.js', array(), $this->_hustle->get_const_var( "VERSION" ), true );
		wp_register_script( 'optin_admin_fitie', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/vendor/fitie/fitie.js', array(), $this->_hustle->get_const_var( "VERSION" ), true );
		wp_register_script( 'hustle_google_chart', 'https://www.gstatic.com/charts/loader.js', array(), $this->_hustle->get_const_var( "VERSION" ), true );

		wp_enqueue_script(  'optin_admin_ace' );
		wp_enqueue_script(  'hustle_google_chart' );
		wp_enqueue_script(  'optin_admin_popup' );
		wp_enqueue_script(  'optin_admin_select2' );

		wp_enqueue_script(  'optin_admin_fitie' );
		add_filter( 'script_loader_tag', array($this, "handle_specific_script"), 10, 2 );
		add_filter( 'style_loader_tag', array($this, "handle_specific_style"), 10, 2 );

		$tags = array_map(array($this, "terms_to_select2_data"), get_categories(array(
			"hide_empty" =>false,
			'taxonomy' => 'post_tag'
		)));

		$cats = array_map(array($this, "terms_to_select2_data"), get_categories(array(
			"hide_empty" =>false,
		)));


		$posts = array_map(array($this, "posts_to_select2_data"), get_posts(array(
				'numberposts' => -1
		 )));
		/**
		 * Add all posts
		 */
		$allPosts = new stdClass();
		$allPosts->id = "all";
		$allPosts->text = __("ALL POSTS", Opt_In::TEXT_DOMAIN);
		array_unshift($posts, $allPosts);

		$pages = array_map(array($this, "posts_to_select2_data"), get_posts(array(
			'numberposts' => -1,
			'post_type' => 'page'
		)));

		/**
		 * Add all pages
		 */
		$allPages = new stdClass();
		$allPages->id = "all";
		$allPages->text = __("ALL PAGES", Opt_In::TEXT_DOMAIN);
		array_unshift($pages, $allPages);

		/**
		 * Add all custom post types
		 */
		$post_types = array();
		$cpts = get_post_types( array(
			'public'   => true,
		   '_builtin' => false
		), 'objects' );
		foreach( $cpts as $cpt ) {

			// skip ms_invoice
			if ( $cpt->name === 'ms_invoice' ) {
				continue;
			}

			$cpt_array['name'] = $cpt->name;
			$cpt_array['label'] = $cpt->label;
			$cpt_array['data'] = array_map(array($this, "posts_to_select2_data"), get_posts(array(
				'numberposts' => -1,
				'post_type' => $cpt->name
			)));
			// all posts under this custom post type
			$allCPTPosts = new stdClass();
			$allCPTPosts->id = "all";
			$allCPTPosts->text = __("ALL ", Opt_In::TEXT_DOMAIN) . $cpt->label;
			array_unshift($cpt_array['data'], $allCPTPosts);

			$post_types[$cpt->name] = $cpt_array;
		}

		$optin_vars = array(
			'messages' => array(
			  'dont_navigate_away' => __("Changes are not saved, are you sure you want to navigate away?", Opt_In::TEXT_DOMAIN),
			  'undefined_name_service_provider' => __("Please define proper Opt-in name and service provider", Opt_In::TEXT_DOMAIN),
			  'undefined_name' => __("Please define proper Opt-in name", Opt_In::TEXT_DOMAIN),
			  'unselected_provider' => __("Please select service provider", Opt_In::TEXT_DOMAIN),
			  'error' => __("Error", Opt_In::TEXT_DOMAIN),
			  'ok' => __("Ok", Opt_In::TEXT_DOMAIN),
			  'sure_to_delete' => __("Are you sure you want to delete this optin?", Opt_In::TEXT_DOMAIN ),
			  'something_went_wrong' => '<label class="wpmudev-label--notice"><span>' . __("Something went wrong. Please try again.", Opt_In::TEXT_DOMAIN ) . '</span></label>',
			  'positions' => array(
				  'top_left' => __("Top Left", Opt_In::TEXT_DOMAIN ),
				  'top_center' => __("Top Center", Opt_In::TEXT_DOMAIN ),
				  'top_right' => __("Top Right", Opt_In::TEXT_DOMAIN ),
				  'center_left' => __("Center Left", Opt_In::TEXT_DOMAIN ),
				  'center_right' => __("Center Right", Opt_In::TEXT_DOMAIN ),
				  'bottom_left' => __("Bottom Left", Opt_In::TEXT_DOMAIN ),
				  'bottom_center' => __("Bottom Center", Opt_In::TEXT_DOMAIN ),
				  'bottom_right' => __("Bottom Right", Opt_In::TEXT_DOMAIN ),
				),
				'settings' => array(
					'popup' => __("Pop-up", Opt_In::TEXT_DOMAIN ),
					'slide_in' => __("Slide-in", Opt_In::TEXT_DOMAIN ),
					'magic_bar' => __("Magic Bar", Opt_In::TEXT_DOMAIN ),
					'after_content' => __("After Content", Opt_In::TEXT_DOMAIN ),
					'floating_social' => __("Floating Social", Opt_In::TEXT_DOMAIN ),
				),
				'conditions' => array(
					'only_on_not_found' => __("404 page", Opt_In::TEXT_DOMAIN ),
					'visitor_logged_in' => __("Visitor is logged in", Opt_In::TEXT_DOMAIN ),
					'visitor_not_logged_in' => __("Visitor not logged in", Opt_In::TEXT_DOMAIN ),
					'shown_less_than' => __("{type_name} shown less than", Opt_In::TEXT_DOMAIN ),
					'only_on_mobile' => __("Only on mobile devices", Opt_In::TEXT_DOMAIN ),
					'not_on_mobile' => __("Not on mobile devices", Opt_In::TEXT_DOMAIN ),
					'from_specific_ref' => __("From a specific referrer", Opt_In::TEXT_DOMAIN ),
					'not_from_specific_ref' => __("Not from a specific referrer", Opt_In::TEXT_DOMAIN ),
					'not_from_internal_link' => __("Not from an internal link", Opt_In::TEXT_DOMAIN ),
					'from_search_engine' => __("From a search engine", Opt_In::TEXT_DOMAIN ),
					'on_specific_url' => __("On specific URL", Opt_In::TEXT_DOMAIN ),
					'not_on_specific_url' => __("Not on specific URL", Opt_In::TEXT_DOMAIN ),
					'visitor_has_commented' => __("Visitor has commented before", Opt_In::TEXT_DOMAIN ),
					'visitor_has_never_commented' => __("Visitor has never commented", Opt_In::TEXT_DOMAIN ),
					'in_a_country' => __("In a specific Country", Opt_In::TEXT_DOMAIN ),
					'not_in_a_country' => __("Not in a specific Country", Opt_In::TEXT_DOMAIN ),
					'posts' => __("Posts", Opt_In::TEXT_DOMAIN ),
					'pages' => __("Pages", Opt_In::TEXT_DOMAIN ),
					'categories' => __("Categories", Opt_In::TEXT_DOMAIN ),
					'tags' => __("Tags", Opt_In::TEXT_DOMAIN ),
				),
				'condition_labels' => array(
					'only_on_not_found' => __("Only on 404 page", Opt_In::TEXT_DOMAIN ),
					'visitor_logged_in' => __("Only when visitor has logged in", Opt_In::TEXT_DOMAIN ),
					'visitor_not_logged_in' => __("Only when visitor has not logged in", Opt_In::TEXT_DOMAIN ),
					'shown_less_than' => __("{type_name} shown less than a certain times", Opt_In::TEXT_DOMAIN ),
					'only_on_mobile' => __("Only on mobile devices", Opt_In::TEXT_DOMAIN ),
					'not_on_mobile' => __("Not on mobile devices", Opt_In::TEXT_DOMAIN ),
					'from_specific_ref' => __("From a specific referrer", Opt_In::TEXT_DOMAIN ),
					'not_from_specific_ref' => __("Not from a specific referrer", Opt_In::TEXT_DOMAIN ),
					'not_from_internal_link' => __("Not from an internal link", Opt_In::TEXT_DOMAIN ),
					'from_search_engine' => __("From a search engine", Opt_In::TEXT_DOMAIN ),
					'on_specific_url' => __("On specific URLs", Opt_In::TEXT_DOMAIN ),
					'not_on_specific_url' => __("Not on specific URLs", Opt_In::TEXT_DOMAIN ),
					'visitor_has_commented' => __("Visitor has commented before", Opt_In::TEXT_DOMAIN ),
					'visitor_has_never_commented' => __("Visitor has never commented", Opt_In::TEXT_DOMAIN ),
					'in_a_country' => __("In specific countries", Opt_In::TEXT_DOMAIN ),
					'not_in_a_country' => __("Not in specific countries", Opt_In::TEXT_DOMAIN ),
					'posts' => __("On certain posts", Opt_In::TEXT_DOMAIN ),
					'all_posts' => __("All posts", Opt_In::TEXT_DOMAIN ),
					'all' => __("All", Opt_In::TEXT_DOMAIN ),
					'no' => __("No", Opt_In::TEXT_DOMAIN ),
					'no_posts' => __("No posts", Opt_In::TEXT_DOMAIN ),
					'only_on_these_posts' => __("Only {number} posts", Opt_In::TEXT_DOMAIN ),
					'number_posts' => __("{number} posts", Opt_In::TEXT_DOMAIN ),
					'except_these_posts' => __("All posts except {number}", Opt_In::TEXT_DOMAIN ),
					'pages' => __("On certain pages", Opt_In::TEXT_DOMAIN ),
					'all_pages' => __("All pages", Opt_In::TEXT_DOMAIN ),
					'no_pages' => __("No pages", Opt_In::TEXT_DOMAIN ),
					'only_on_these_pages' => __("Only {number} pages", Opt_In::TEXT_DOMAIN ),
					'number_pages' => __("{number} pages", Opt_In::TEXT_DOMAIN ),
					'except_these_pages' => __("All pages except {number}", Opt_In::TEXT_DOMAIN ),
					'categories' => __("On certain categories", Opt_In::TEXT_DOMAIN ),
					'all_categories' => __("All categories", Opt_In::TEXT_DOMAIN ),
					'no_categories' => __("No categories", Opt_In::TEXT_DOMAIN ),
					'only_on_these_categories' => __("Only {number} categories", Opt_In::TEXT_DOMAIN ),
					'number_categories' => __("{number} categories", Opt_In::TEXT_DOMAIN ),
					'except_these_categories' => __("All categories except {number}", Opt_In::TEXT_DOMAIN ),
					'tags' => __("On certain tags", Opt_In::TEXT_DOMAIN ),
					'all_tags' => __("All tags", Opt_In::TEXT_DOMAIN ),
					'no_tags' => __("No tags", Opt_In::TEXT_DOMAIN ),
					'only_on_these_tags' => __("Only {number} tags", Opt_In::TEXT_DOMAIN ),
					'number_tags' => __("{number} tags", Opt_In::TEXT_DOMAIN ),
					'except_these_tags' => __("All tags except {number}", Opt_In::TEXT_DOMAIN ),
					"everywhere" => __("Show everywhere", Opt_In::TEXT_DOMAIN)
				),
				'conditions_body' => array(
					'only_on_not_found' => __('Shows the {type_name} on the 404 page.', Opt_In::TEXT_DOMAIN),
					'visitor_has_commented' => __('Shows the {type_name} if the user has already left a comment. You may want to combine this condition with either "Visitor is logged in" or "Visitor is not logged in".', Opt_In::TEXT_DOMAIN),
					'visitor_has_never_commented' => __('Shows the {type_name} if the user has never left a comment. You may want to combine this condition with either "Visitor is logged in" or "Visitor is not logged in".', Opt_In::TEXT_DOMAIN),
					'from_search_engine' => __('Shows the {type_name} if the user arrived via a search engine.', Opt_In::TEXT_DOMAIN),
					'not_from_internal_link' => __('Shows the {type_name} if the user did not arrive on this page via another page on your site.', Opt_In::TEXT_DOMAIN),
					'not_on_mobile' => __('Shows the {type_name} to visitors that are using a normal computer or laptop (i.e. not a Phone or Tablet).', Opt_In::TEXT_DOMAIN),
					'only_on_mobile' => __('<label class="wph-label--alt">Shows the {type_name} to visitors that are using a mobile device (Phone or Tablet).</label>', Opt_In::TEXT_DOMAIN),
					'visitor_not_logged_in' => __('<label class="wph-label--alt">Shows the {type_name} if the user is not logged in to your site.</label>', Opt_In::TEXT_DOMAIN),
					'visitor_logged_in' => __('<label class="wph-label--alt">Shows the {type_name} if the user is logged in to your site.</label>', Opt_In::TEXT_DOMAIN),
				),
				'model' => array(
					"defaults" => array(
						"module_name" => '',
						"module_title" => __("e.g. Get 50% Early-bird Special", Opt_In::TEXT_DOMAIN),
						"module_message" => __("Please fill in the form and submit to subscribe", Opt_In::TEXT_DOMAIN),
						"success_message" => __("Congratulations! You have been subscribed to {name}", Opt_In::TEXT_DOMAIN),
						"cta_button" => __("Sign Up", Opt_In::TEXT_DOMAIN)
					),
					"errors" => array(
						'name' => __('Please fill "name" field.', Opt_In::TEXT_DOMAIN),
						'provider' => __('Please choose a valid provider.', Opt_In::TEXT_DOMAIN),
						'api_key' => __('Please provide api key.', Opt_In::TEXT_DOMAIN),
						'mail_list' => __('Please select a mail list.', Opt_In::TEXT_DOMAIN)
					)
				),
				'custom_content' => array(
					'errors' => array(
						'cta_url' => __('Please provide a valid url (http://example.net).', Opt_In::TEXT_DOMAIN)
					),
					'no_name' => __( 'Please provide name.', Opt_In::TEXT_DOMAIN ),
				),
				'form_fields' => array(
					'errors' => array(
						'custom_field_not_supported' => __('Custom fields are not supported by the active provider', Opt_In::TEXT_DOMAIN)
					),
				),
				'providers' => array(
					'select_list' => __('Selected list (campaign), Press the Fetch Lists button to update value.', Opt_In::TEXT_DOMAIN ),
					'no_fetch_list'	=> __('Selected list (campaign).', Opt_In::TEXT_DOMAIN ),
				),
				"sendy" => array(
					"enter_url" => __("Please enter installation URL", Opt_In::TEXT_DOMAIN)
				),
				"mad_mimi" => array(
					"username" => __("Please enter username or email address", Opt_In::TEXT_DOMAIN)
				),
				"infusionsoft" => array(
					"enter_account_name" => __("Please enter your account name", Opt_In::TEXT_DOMAIN)
				),
				"media_uploader" => array(
					"select_or_upload" => __("Select or Upload Image", Opt_In::TEXT_DOMAIN),
					"use_this_image" => __("Use this image", Opt_In::TEXT_DOMAIN)
				),
				"dashboard" => array(
					"not_enough_data" => __("There is no enough data yet, please try again later.", Opt_In::TEXT_DOMAIN)
				),
				"activecampaign" => array(
					"enter_url" => __("Please enter your ActiveCampaign URL", Opt_In::TEXT_DOMAIN)
				),
				"convertkit" => array(
					"enter_api_secret" => __("Please enter your API Secret key from ConvertKit", Opt_In::TEXT_DOMAIN)
				),
				'module_fields' => array(
					'no_label' => __( 'Please enter field label', Opt_In::TEXT_DOMAIN ),
					'no_name' => __( 'Please enter field name', Opt_In::TEXT_DOMAIN ),
					'custom_field_already_exists' => __( 'Custom field "{name}" already exists.', Opt_In::TEXT_DOMAIN ),
					'custom_field_not_exist' => __( 'Custom field doesn\'t exist! Please check your provider.', Opt_In::TEXT_DOMAIN ),
					'cannot_create_custom_field' => __( 'Unable to create new custom field. Please check your provider.', Opt_In::TEXT_DOMAIN ),
				),
				'mautic' => array(
					'enter_url' => __( 'Please enter installation URL', Opt_In::TEXT_DOMAIN ),
					'invalid_url' => __( 'Please enter valid installation URL', Opt_In::TEXT_DOMAIN ),
					'username' => __( 'Please enter username', Opt_In::TEXT_DOMAIN ),
					'password' => __( 'Please enter password', Opt_In::TEXT_DOMAIN ),
				),
			),
			'url' => get_home_url(),
			'includes_url' => includes_url(),
			'palettes' => $this->_hustle->get_palettes(),
			'preview_image' => "",
			'cats' => $cats,
			'tags' => $tags,
			'posts' => $posts,
			'post_types' => $post_types,
			'pages' => $pages,
			'is_edit' => Hustle_Module_Admin::is_edit(),
			'current' => array(),
			'is_admin' => (int) is_admin(),
			// 'module_fields' => Opt_In_Meta_Design::default_fields(),
			// 'get_module_field_nonce' => wp_create_nonce( 'optin_add_module_field' ),
			'error_log_nonce' => wp_create_nonce( 'hustle_get_error_logs' ),
			'clear_log_nonce' => wp_create_nonce( 'optin_clear_logs' ),
	        // 'hubspot_nonce' => wp_create_nonce( 'hustle_hubspot_referrer' ),
		);

		$ap_vars = array(
			'url' => get_home_url(),
			'includes_url' => includes_url()
		);

		$optin_vars['countries'] = $this->_hustle->get_countries();
		$optin_vars['animations'] = $this->_hustle->get_animations();
		// $optin_vars['services'] = $this->_email_services->get_all();
		$optin_vars['providers'] = $this->_hustle->get_providers();

		$optin_vars = apply_filters("hustle_optin_vars", $optin_vars);

		$optin_vars['is_free'] = (int) Opt_In::is_free();

		// $total_optins = count(Opt_In_Collection::instance()->get_all_optins( null ));
		// $optin_vars['is_limited'] = (int) ( Opt_In_Utils::_is_free( 'opt-ins' ) && ! $this->_is_edit() && $total_optins >= 1 );

		if( isset($_GET['page'] ) && 'hustle' == $_GET['page'] ) {
			wp_enqueue_script( 'jquery-sortable' );
		}
		if(isset( $_GET['page'] ) && $_GET['page'] != 'hustle') wp_enqueue_script( 'wp-color-picker-alpha', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/vendor/wp-color-picker-alpha.min.js', array( 'wp-color-picker' ), '1.2.2', true );
		wp_register_script( 'optin_admin_scripts', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/admin.min.js', array( 'jquery', 'backbone', 'jquery-effects-core' ), $this->_hustle->get_const_var( "VERSION" ), true );
		wp_localize_script( 'optin_admin_scripts', 'optin_vars', $optin_vars );
		wp_localize_script( 'optin_admin_scripts', 'hustle_vars', $optin_vars );
		wp_enqueue_script( 'optin_admin_scripts' );

	}

	/**
	 * Is the admin page being viewed in edit mode
	 *
	 * @since 1.0.0.
	 *
	 * @return mixed
	 */
	public static function is_edit(){
		return  (bool) filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
	}

	/**
	 * Determine what admin section for Pop-up module
	 *
	 * @since 3.0.0.
	 *
	 * @return mixed, string or boolean
	 */
	public static function get_current_section(){
		$section = filter_input(INPUT_GET, "section", FILTER_SANITIZE_STRING);
		return ( is_null($section) || empty($section) )
			? false
			: $section;
	}

	/**
	 * Handling specific scripts for each scenario
	 *
	 */
	function handle_specific_script( $tag, $handle ) {
		if ( $handle === 'optin_admin_fitie' ) {
			$tag = "<!--[if IE]>$tag<![endif]-->";
		}
		return $tag;
	}

	/**
	 * Handling specific style for each scenario
	 *
	 */
	function handle_specific_style( $tag, $handle ) {
		if ( $handle == 'hustle_admin_ie' ) {
			$tag = "<!--[if IE]>". $tag ."<![endif]-->";
		}
		return $tag;
	}

	function set_proper_current_screen( $current ){
		global $current_screen;
		if ( !Opt_In_Utils::_is_free() ) {
			$current_screen->id = Opt_In_Utils::clean_current_screen($current_screen->id);
		}
	}

	/**
	 * Registers styles for the admin
	 *
	 *
	 */
	function register_styles(){
		wp_enqueue_style('thickbox');

		wp_register_style( 'optin_admin_select2', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/js/vendor/select2/css/select2.min.css', array(), $this->_hustle->get_const_var( "VERSION" ));
		wp_register_style( 'wpoi_admin', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/css/admin.min.css', array(), $this->_hustle->get_const_var( "VERSION" ));
		wp_register_style( 'hustle_admin_ie', $this->_hustle->get_static_var( "plugin_url" ) . 'assets/css/ie-admin.min.css', array(), $this->_hustle->get_const_var( "VERSION" ));
		wp_register_style( 'hstl-roboto', 'https://fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:300,300i,400,400i,500,500i,700,700i', $this->_hustle->get_const_var( "VERSION" ) );
		wp_register_style( 'hstl-opensans', 'https://fonts.googleapis.com/css?family=Open+Sans:400,400i,700,700i', $this->_hustle->get_const_var( "VERSION" ) );
		wp_register_style( 'hstl-source', 'https://fonts.googleapis.com/css?family=Source+Code+Pro', $this->_hustle->get_const_var( "VERSION" ) );

		wp_enqueue_style( 'optin_admin_select2' );
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_style( 'wdev_ui' );
		wp_enqueue_style( 'wdev_notice' );
		wp_enqueue_style( 'wpoi_admin' );
		wp_enqueue_style( 'hustle_admin_ie' );
		wp_enqueue_style( 'hstl-roboto' );
		wp_enqueue_style( 'hstl-opensans' );
		wp_enqueue_style( 'hstl-source' );

	}

	/**
	 * Converts term object to usable object for select2
	 * @param $term Term
	 * @return stdClass
	 */
	function terms_to_select2_data( $term ){
		$obj = new stdClass();
		$obj->id = $term->term_id;
		$obj->text = $term->name;
		return $obj;
	}

	/**
	 * Converts post object to usable object for select2
	 *
	 * @param $post WP_Post
	 * @return stdClass
	 */
	function posts_to_select2_data($post){
		$obj = new stdClass();
		$obj->id = $post->ID;
		$obj->text = $post->post_title;
		return $obj;
	}


	/**
	 * Checks if it's module admin page
	 *
	 * @return bool
	 */
	private function _is_admin_module() {
		return isset( $_GET['page'] ) &&  ( in_array($_GET['page'], array(
		Hustle_Module_Admin::ADMIN_PAGE,
		Hustle_Module_Admin::DASHBOARD_PAGE,
		Hustle_Module_Admin::POPUP_LISTING_PAGE,
		Hustle_Module_Admin::POPUP_WIZARD_PAGE,
		Hustle_Module_Admin::SLIDEIN_LISTING_PAGE,
		Hustle_Module_Admin::SLIDEIN_WIZARD_PAGE,
		Hustle_Module_Admin::EMBEDDED_LISTING_PAGE,
		Hustle_Module_Admin::EMBEDDED_WIZARD_PAGE,
		Hustle_Module_Admin::SOCIAL_SHARING_LISTING_PAGE,
		Hustle_Module_Admin::SOCIAL_SHARING_WIZARD_PAGE,
		Hustle_Module_Admin::SETTINGS_PAGE,
		Hustle_Module_Admin::UPGRADE_PAGE) ) );

	}


	/**
	 * Modify admin body class to our own advantage!
	 *
	 * @param $classes
	 * @return mixed
	 */
	function admin_body_class( $classes ){
		return str_replace(array("wpmud ", "wpmud"), "", $classes);
	}

	/**
	 * Modify tinymce editor settings
	 *
	 * @param $settings
	 */
	function set_tinymce_settings( $settings ) {
		$settings['paste_as_text'] = 'true';
		return $settings;
	}

	/**
	 * Adds custom links on plugin page
	 *
	 */
	function add_plugin_action_links( $actions, $plugin_file ) {
		static $plugin;

		if (!isset($plugin))
			$plugin = Opt_In::$plugin_base_file;

		if ($plugin == $plugin_file) {
			$dashboard_url = 'admin.php?page=hustle';
			$settings = array('settings' => '<a href="'. $dashboard_url .'">' . __('Settings', Opt_In::TEXT_DOMAIN) . '</a>');
			$actions = array_merge($settings, $actions);
		}

		return $actions;
	}
}

endif;