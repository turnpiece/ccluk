<?php

if ( !class_exists( 'onesocial_Redux_Framework_config' ) ) {

	class onesocial_Redux_Framework_config {

		public $args	 = array();
		public $sections = array();
		public $theme;
		public $ReduxFramework;

		public function __construct() {

			if ( !class_exists( 'ReduxFramework' ) ) {
				return;
			}

			// This is needed. Bah WordPress bugs.  ;)
			if ( true == Redux_Helpers::isTheme( __FILE__ ) ) {
				$this->initSettings();
			} else {
				add_action( 'plugins_loaded', array( $this, 'initSettings' ), 10 );
			}
		}

		public function initSettings() {

			// Just for demo purposes. Not needed per say.
			$this->theme = wp_get_theme();

			// Set the default arguments
			$this->setArguments();

			// Create the sections and fields
			$this->setSections();

			if ( !isset( $this->args[ 'opt_name' ] ) ) { // No errors please
				return;
			}

			// If Redux is running as a plugin, this will remove the demo notice and links
			add_action( 'redux/loaded', array( $this, 'remove_demo' ) );

			$this->ReduxFramework = new ReduxFramework( $this->sections, $this->args );
		}

		// Remove the demo link and the notice of integrated demo from the redux-framework plugin
		function remove_demo() {

			// Used to hide the demo mode link from the plugin page. Only used when Redux is a plugin.
			if ( class_exists( 'ReduxFrameworkPlugin' ) ) {
				remove_filter( 'plugin_row_meta', array( ReduxFrameworkPlugin::instance(), 'plugin_metalinks' ), null, 2 );

				// Used to hide the activation notice informing users of the demo panel. Only used when Redux is a plugin.
				remove_action( 'admin_notices', array( ReduxFrameworkPlugin::instance(), 'admin_notices' ) );
			}
		}

		public function setSections() {

			$customize_url	 = add_query_arg( 'return', urlencode( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) ), 'customize.php' );
			$admin_url		 = admin_url( $customize_url );

			// Logo Settings
			$this->sections[] = array(
				'title'		 => __( 'Logo', 'onesocial' ),
				'icon'		 => 'el-icon-adjust',
				'priority'	 => 20,
				'fields'	 => array(
					array(
						'id'		 => 'logo_switch',
						'type'		 => 'switch',
						'title'		 => __( 'Desktop Logo', 'onesocial' ),
						'subtitle'	 => __( 'Upload your custom site logo for desktop layout (280px by 80px).', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_logo',
						'type'		 => 'media',
						'url'		 => false,
						'required'	 => array( 'logo_switch', 'equals', '1' ),
					),
					array(
						'id'		 => 'mobile_logo_switch',
						'type'		 => 'switch',
						'title'		 => __( 'Mobile Logo', 'onesocial' ),
						'subtitle'	 => __( 'Upload your custom site logo for mobile layout (280px by 80px).', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_mobile_logo',
						'type'		 => 'media',
						'url'		 => false,
						'required'	 => array( 'mobile_logo_switch', 'equals', '1' ),
					),
					array(
						'id'		 => 'boss_favicon',
						'type'		 => 'none',
						'url'		 => false,
						'title'		 => __( 'Favicon', 'onesocial' ),
						'subtitle'	 => sprintf( __( 'Upload your custom site favicon and Apple device icons at <a href="%s">Appearance &gt; Customize</a> in the Site Identity section.', 'onesocial' ), $admin_url ),
					),
				)
			);

			$bookmarks_button	 = array();
			$write_post_button	 = array();

			if ( function_exists( 'buddyboss_sap' ) ) {
				$bookmarks_button = array(
					'id'		 => 'bookmarks_button',
					'type'		 => 'switch',
					'title'		 => __( 'Bookmarks', 'onesocial' ),
					'subtitle'	 => __( 'Show/hide Bookmarks button in titlebar.', 'onesocial' ),
					'on'		 => __( 'Show', 'onesocial' ),
					'off'		 => __( 'Hide', 'onesocial' ),
					'default'	 => '1',
				);

				$write_post_button = array(
					'id'		 => 'write_post_button',
					'type'		 => 'switch',
					'title'		 => __( 'Write a Story', 'onesocial' ),
					'subtitle'	 => __( 'Show/hide Write a Story button in titlebar.', 'onesocial' ),
					'on'		 => __( 'Show', 'onesocial' ),
					'off'		 => __( 'Hide', 'onesocial' ),
					'default'	 => '1',
				);
			}

			// Header Settings
			$this->sections[] = array(
				'title'		 => __( 'Header', 'onesocial' ),
				'id'		 => 'header_layout',
				'customizer' => false,
				'icon'		 => 'el-icon-credit-card',
				'fields'	 => array(
					array(
						'id'		 => 'boss_header',
						'title'		 => __( 'Header Style', 'onesocial' ),
						'subtitle'	 => __( 'Select the header layout.', 'onesocial' ),
						'type'		 => 'image_select',
						'customizer' => false,
						'default'	 => 'header-style-1',
						'options'	 => array(
							'header-style-1' => array(
								'alt'	 => 'Header style 1',
								'img'	 => get_template_directory_uri() . '/buddyboss-inc/buddyboss-framework/assets/images/headers/style1.png'
							),
							'header-style-2' => array(
								'alt'	 => 'Header style 2',
								'img'	 => get_template_directory_uri() . '/buddyboss-inc/buddyboss-framework/assets/images/headers/style2.png'
							),
						)
					),
					array(
						'id'	 => 'sticky_header_info',
						'type'	 => 'info',
						'desc'	 => __( 'Sticky Header', 'onesocial' )
					),
					array(
						'id'		 => 'sticky_header',
						'type'		 => 'switch',
						'title'		 => __( 'Sticky Header', 'onesocial' ),
						'subtitle'	 => __( 'Enable sticky header, so titlebar sticks to top of page at all times.', 'onesocial' ),
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
						'default'	 => '0',
					),
					array(
						'id'	 => 'header_buttons_info',
						'type'	 => 'info',
						'desc'	 => __( 'Header Buttons', 'onesocial' )
					),
					array(
						'id'		 => 'messages_button',
						'type'		 => 'switch',
						'title'		 => __( 'Messages', 'onesocial' ),
						'subtitle'	 => __( 'Show/hide Messages button in titlebar.', 'onesocial' ),
						'on'		 => __( 'Show', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '1',
					),
					array(
						'id'		 => 'notifications_button',
						'type'		 => 'switch',
						'title'		 => __( 'Notifications', 'onesocial' ),
						'subtitle'	 => __( 'Show/hide Notifications button in titlebar.', 'onesocial' ),
						'on'		 => __( 'Show', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '1',
					),
					$bookmarks_button,
					array(
						'id'		 => 'profile_setting_button',
						'type'		 => 'switch',
						'title'		 => __( 'Settings', 'onesocial' ),
						'subtitle'	 => __( 'Show/hide Settings button in titlebar.', 'onesocial' ),
						'on'		 => __( 'Show', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '1',
					),
					array(
						'id'		 => 'header_search',
						'type'		 => 'switch',
						'title'		 => __( 'Search', 'onesocial' ),
						'subtitle'	 => __( 'Show/hide Search button in titlebar.', 'onesocial' ),
						'on'		 => __( 'Show', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '1',
					),
					$write_post_button
				)
			);

			$add_posts_title	 = array();
			$add_posts_switch	 = array();

			if ( function_exists( 'buddyboss_sap' ) ) {
				$add_posts_title = array(
					'id'	 => 'boss_add_posts',
					'type'	 => 'info',
					'desc'	 => __( 'Posts Editor', 'onesocial' )
				);

				$add_posts_switch = array(
					'id'		 => 'onesocial_adding_posts',
					'type'		 => 'switch',
					'title'		 => __( 'Blog Index Post Editor', 'onesocial' ),
					'subtitle'	 => __( 'Allow members to publish from the Blog index.', 'onesocial' ),
					'default'	 => '1',
					'on'		 => __( 'On', 'onesocial' ),
					'off'		 => __( 'Off', 'onesocial' ),
				);
			}

			// Layout Settings
			$this->sections[] = array(
				'title'		 => __( 'Layout', 'onesocial' ),
				'id'		 => 'device_layout',
				'customizer' => false,
				'icon'		 => 'el-icon-website',
				'fields'	 => array(
					array(
						'id'		 => 'boss_adminbar',
						'type'		 => 'switch',
						'title'		 => __( 'WordPress Adminbar', 'onesocial' ),
						'subtitle'	 => __( 'Display the adminbar for logged in admin users.', 'onesocial' ),
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
						'default'	 => '0',
					),
					array(
						'id'	 => 'boss_homepage_sidebar_info',
						'type'	 => 'info',
						'desc'	 => __( 'Front Page Sidebar', 'onesocial' )
					),
					array(
						'id'		 => 'boss_homepage_sidebar_switch',
						'type'		 => 'switch',
						'title'		 => __( 'Front Page Sidebar', 'onesocial' ),
						'subtitle'	 => __( 'If Front page is set to display "Your latest posts" at <em>Settings > Reading</em>, you can set the sidebar default to be opened or closed.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'Opened', 'onesocial' ),
						'off'		 => __( 'Closed', 'onesocial' ),
					),
					$add_posts_title,
					$add_posts_switch,
					array(
						'id'	 => 'boss_activity_infinite_info',
						'type'	 => 'info',
						'desc'	 => __( 'Infinite Scrolling', 'onesocial' )
					),
					array(
						'id'		 => 'boss_activity_infinite',
						'type'		 => 'switch',
						'title'		 => __( 'Activity Infinite Scrolling', 'onesocial' ),
						'subtitle'	 => __( 'Allow content in all Activity Streams to automatically load more as you scroll down the page.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'post_infinite',
						'type'		 => 'switch',
						'title'		 => __( 'Posts Infinite Scrolling', 'onesocial' ),
						'subtitle'	 => __( 'Allow Posts to automatically load more as you scroll down the page.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'	 => 'responsive_layout_info',
						'type'	 => 'info',
						'desc'	 => __( 'Responsive: We use device detection to determine the correct layout, with media queries as a fallback.', 'onesocial' )
					),
					array(
						'id'		 => 'boss_layout_desktop',
						'type'		 => 'button_set',
						'title'		 => __( 'Desktop', 'onesocial' ),
						'subtitle'	 => __( 'Select the default desktop layout.', 'onesocial' ),
						'options'	 => array(
							'desktop'	 => 'Desktop',
							'mobile'	 => 'Mobile'
						),
						'default'	 => 'desktop'
					),
					array(
						'id'		 => 'boss_layout_tablet',
						'type'		 => 'button_set',
						'title'		 => __( 'Tablet', 'onesocial' ),
						'subtitle'	 => __( 'Select the default tablet layout.', 'onesocial' ),
						'options'	 => array(
							'desktop'	 => 'Desktop',
							'mobile'	 => 'Mobile'
						),
						'default'	 => 'mobile'
					),
					array(
						'id'		 => 'boss_layout_phone',
						'type'		 => 'button_set',
						'title'		 => __( 'Phone', 'onesocial' ),
						'subtitle'	 => __( 'Phones can only display mobile layout.', 'onesocial' ),
						'options'	 => array(
							'mobile' => 'Mobile'
						),
						'default'	 => 'mobile'
					),
					array(
						'id'		 => 'boss_layout_switcher',
						'type'		 => 'switch',
						'title'		 => __( 'View Desktop/Mobile button', 'onesocial' ),
						'subtitle'	 => __( 'Display or hide the layout switch button in your site footer.', 'onesocial' ),
						'on'		 => __( 'Display', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '1',
					),
					array(
						'id'	 => 'mobile_layout_info',
						'type'	 => 'info',
						'desc'	 => __( 'Mobile Options', 'onesocial' )
					),
					array(
						'id'		 => 'onesocial_search_instead',
						'type'		 => 'switch',
						'title'		 => __( 'Search Input', 'onesocial' ),
						'subtitle'	 => __( 'The mobile titlebar can optionally display a search input in place of your site logo/title.', 'onesocial' ),
						'on'		 => __( 'Display', 'onesocial' ),
						'off'		 => __( 'Hide', 'onesocial' ),
						'default'	 => '0',
					),
				)
			);

			$page_sidebar_array = array(
				'id'		 => 'page_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Page/Post Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the page and blog post sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'right'
			);

			$home_sidebar_array = array(
				'id'		 => 'home_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Homepage Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the homepage sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'right'
			);

			$profile_sidebar_array = array(
				'id'		 => 'profile_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Member Profile Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the member profile sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'left'
			);

			$single_group_sidebar_array = array(
				'id'		 => 'single_group_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Single Group Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the single group sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'left'
			);

			$activity_sidebar_array = array(
				'id'		 => 'activity_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Activity Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the activity sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'left'
			);

			$forums_sidebar_array = array(
				'id'		 => 'forums_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Forums Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the forums sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'left'
			);

			$blogs_sidebar_array = array(
				'id'		 => 'blogs_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Blogs &rarr; Directory (multisite) Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the forums sidebar alignment.', 'onesocial' ),
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				),
				'default'	 => 'left'
			);

			$search_sidebar_array = array(
				'id'		 => 'search_sidebar',
				'type'		 => 'button_set',
				'title'		 => __( 'Search Results Sidebar', 'onesocial' ),
				'subtitle'	 => __( 'Select the search results page sidebar alignment.', 'onesocial' ),
				'default'	 => 'right',
				'options'	 => array(
					'left'	 => 'Left',
					'right'	 => 'Right'
				)
			);

			// Sidebar Settings
			$this->sections[] = array(
				'title'		 => __( 'Sidebars', 'onesocial' ),
				'icon'		 => 'el el-lines',
				'customizer' => false,
				'fields'	 => array(
					$page_sidebar_array,
					$home_sidebar_array,
					$profile_sidebar_array,
					$single_group_sidebar_array,
					$activity_sidebar_array,
					$forums_sidebar_array,
					$blogs_sidebar_array,
					$search_sidebar_array,
				)
			);

			$group_cover_sizes = apply_filters( 'boss_group_cover_sizes', array( '322' => 'Big', '200' => 'Small' ) );

			// Cover Images
			$this->sections[] = array(
				'title'		 => __( 'Cover Images', 'onesocial' ),
				'id'		 => 'cover_photos',
				'customizer' => false,
				'icon'		 => 'el-icon-picture',
				'fields'	 => array(
					array(
						'id'	 => 'buddypress_group_info',
						'type'	 => 'info',
						'desc'	 => __( 'BuddyPress Groups &gt; Cover Images', 'onesocial' )
					),
					array(
						'id'		 => 'boss_cover_group',
						'type'		 => 'switch',
						'title'		 => __( 'Enable Group Cover Images', 'onesocial' ),
						'subtitle'	 => __( 'Make sure to also enable "Group Cover Image Uploads" at <em>Settings &gt; BuddyPress &gt; Settings.</em>', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_cover_group_size',
						'type'		 => 'select',
						'title'		 => __( 'Cover Image Size', 'onesocial' ),
						'subtitle'	 => __( 'Adjust the height of group cover images.', 'onesocial' ),
						'required'	 => array( 'boss_cover_group', 'equals', '1' ),
						'options'	 => $group_cover_sizes,
						'default'	 => '322',
					),
					array(
						'id'		 => 'boss_group_cover_default',
						'type'		 => 'media',
						'title'		 => __( 'Default Photo', 'onesocial' ),
						'subtitle'	 => __( 'We display a photo at random from our included library. You can optionally upload your own image to always use a default cover photo. Ideal size is 1050px by 320px.', 'onesocial' ),
						'url'		 => false,
						'required'	 => array( 'boss_cover_group', 'equals', '1' ),
					),
				)
			);

			// Profile Settings
			$this->sections[] = array(
				'title'		 => __( 'Profiles', 'onesocial' ),
				'icon'		 => 'el-icon-torso',
				'customizer' => false,
				'fields'	 => array(
					array(
						'id'	 => 'user_menus_info',
						'type'	 => 'info',
						'desc'	 => __( 'User Menus', 'onesocial' )
					),
					array(
						'id'		 => 'boss_dashboard',
						'type'		 => 'switch',
						'title'		 => __( 'Dashboard Links', 'onesocial' ),
						'subtitle'	 => __( 'For admin users, display links to the WordPress dashboard in their profile dropdown menu.', 'onesocial' ),
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
						'default'	 => '1',
					),
					array(
						'id'		 => 'boss_profile_adminbar',
						'type'		 => 'switch',
						'title'		 => __( '"My Profile" Menu', 'onesocial' ),
						'subtitle'	 => __( 'Display the WordPress menu titled "My Profile" in the user profile dropdown menu.', 'onesocial' ),
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
						'default'	 => '1',
					),
					array(
						'id'	 => 'header_fields_info',
						'type'	 => 'info',
						'desc'	 => __( 'Profile Header Field', 'onesocial' )
					),
					array(
						'id'	 => 'social_media_links_info',
						'type'	 => 'info',
						'desc'	 => __( 'Social Media Links', 'onesocial' )
					),
					array(
						'id'		 => 'profile_social_media_links_switch',
						'type'		 => 'switch',
						'title'		 => __( 'Social Media Links', 'onesocial' ),
						'subtitle'	 => __( 'Allow users to display their social media links in their profiles.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'profile_social_media_links',
						'type'		 => 'checkbox',
						'title'		 => __( 'Sites to Allow', 'onesocial' ),
						'options'	 => array(
							'facebook'		 => 'Facebook',
							'twitter'		 => 'Twitter',
							'linkedin'		 => 'Linkedin',
							'google-plus'	 => 'Google+',
							'youtube'		 => 'Youtube',
							'instagram'		 => 'Instagram',
							'pinterest'		 => 'Pinterest',
						),
						'default'	 => array(
							'facebook'		 => '1',
							'twitter'		 => '1',
							'linkedin'		 => '1',
							'google-plus'	 => '1',
							'youtube'		 => '1',
							'instagram'		 => '1',
							'pinterest'		 => '1',
						),
						'required'	 => array( 'profile_social_media_links_switch', 'equals', '1' ),
					)
				)
			);

			// Array of social options
			$social_options = array(
				'facebook'		 => '',
				'twitter'		 => '',
				'linkedin'		 => '',
				'google-plus'	 => '',
				'youtube'		 => '',
				'instagram'		 => '',
				'pinterest'		 => '',
				'email'			 => '',
				'dribbble'		 => '',
				'vk'			 => '',
				'tumblr'		 => '',
				'github'		 => '',
				'flickr'		 => '',
				'skype'			 => '',
				'vimeo'			 => '',
				'xing'			 => '',
				'rss'			 => '',
			);

			$social_options = apply_filters( 'boss_social_options', $social_options );

			// Footer Settings
			$this->sections[] = array(
				'title'		 => __( 'Footer', 'onesocial' ),
				'icon'		 => 'el-icon-bookmark',
				'customizer' => false,
				'fields'	 => array(
					array(
						'id'		 => 'onesocial_footer',
						'type'		 => 'image_select',
						'title'		 => __( 'Footer Style', 'onesocial' ),
						'customizer' => false,
						'default'	 => 'footer-style-1',
						'options'	 => array(
							'footer-style-1' => array(
								'alt'	 => 'Footer style 1',
								'img'	 => get_template_directory_uri() . '/buddyboss-inc/buddyboss-framework/assets/images/footers/style1.png'
							),
							'footer-style-2' => array(
								'alt'	 => 'Footer style 2',
								'img'	 => get_template_directory_uri() . '/buddyboss-inc/buddyboss-framework/assets/images/footers/style2.png'
							),
						)
					),
					array(
						'id'		 => 'footer_copyright_content',
						'type'		 => 'switch',
						'title'		 => __( 'Copyright Text', 'onesocial' ),
						'subtitle'	 => __( 'Enter your custom copyright text.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_copyright',
						'type'		 => 'editor',
						'default'	 => '&copy; ' . date( 'Y' ) . ' - OneSocial <span class="boss-credit">&middot; Powered by <a href="https://www.buddyboss.com" title="BuddyPress themes" target="_blank">BuddyBoss</a></span>',
						'args'		 => array(
							'teeny'			 => true,
							'media_buttons'	 => false,
							'textarea_rows'	 => 6
						),
						'required'	 => array( 'footer_copyright_content', 'equals', '1' ),
					),
					array(
						'id'		 => 'footer_social_links',
						'type'		 => 'switch',
						'title'		 => __( 'Social Links', 'onesocial' ),
						'subtitle'	 => __( 'Define and reorder your social icons in the footer. Keep the input field empty for any social icon you do not wish to display.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_footer_social_links',
						'type'		 => 'sortable',
						'label'		 => true,
						'required'	 => array( 'footer_social_links', 'equals', '1' ),
						'options'	 => $social_options,
					),
				)
			);

			$login_description = sprintf( __( 'Sign into %s', 'onesocial' ), get_bloginfo( 'name' ) );

			if ( get_option( 'users_can_register' ) ) {
				$login_description .= __( ' or create an account', 'onesocial' );
			}
/*
			// Login/Register
			$this->sections[] = array(
				'title'		 => __( 'Register/Login', 'onesocial' ),
				'icon'		 => 'el-icon-pencil',
				'customizer' => false,
				'fields'	 => array(
					array(
						'id'		 => 'user_login_option',
						'type'		 => 'switch',
						'title'		 => __( 'Register/Login Overlays', 'onesocial' ),
						'subtitle'	 => __( 'Toggle the custom register/login overlays on or off.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'login_info',
						'type'		 => 'info',
						'desc'		 => __( 'Login Overlay', 'onesocial' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'login_form_title',
						'type'		 => 'text',
						'title'		 => __( 'Login Title', 'onesocial' ),
						'default'	 => __( 'Welcome back!', 'onesocial' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'login_form_description',
						'type'		 => 'textarea',
						'title'		 => __( 'Login Description', 'onesocial' ),
						'default'	 => $login_description,
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'boss_login_message',
						'title'		 => __( 'Social Login Message', 'onesocial' ),
						'type'		 => 'editor',
						'default'	 => '',
						'args'		 => array(
							'teeny'			 => true,
							'media_buttons'	 => false,
							'textarea_rows'	 => 6
						),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'register_info',
						'type'		 => 'info',
						'desc'		 => __( 'Register Overlay', 'onesocial' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'register_form_title',
						'type'		 => 'text',
						'title'		 => __( 'Register Title', 'onesocial' ),
						'default'	 => __( 'Register', 'onesocial' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'register_form_description',
						'type'		 => 'textarea',
						'title'		 => __( 'Register Description', 'onesocial' ),
						'default'	 => sprintf( __( 'Join %s', 'onesocial' ), get_bloginfo( 'name' ) ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'reset_password_info',
						'type'		 => 'info',
						'desc'		 => __( 'Reset Password Overlay', 'onesocial' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'reset_password_title',
						'type'		 => 'text',
						'title'		 => __( 'Reset Password Title', 'onesocial' ),
						'default'	 => get_bloginfo( 'name' ),
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
					array(
						'id'		 => 'reset_password_description',
						'type'		 => 'textarea',
						'title'		 => __( 'Reset Password Description', 'onesocial' ),
						'default'	 => $login_description,
						'required'	 => array( 'user_login_option', 'equals', '1' ),
					),
				)
			);

			// WordPress Login
			$this->sections[] = array(
				'title'		 => __( 'WordPress Login', 'onesocial' ),
				'id'		 => 'admin_login',
				'customizer' => false,
				'icon'		 => 'el-icon-lock',
				'fields'	 => array(
					array(
						'id'		 => 'boss_custom_login',
						'type'		 => 'switch',
						'title'		 => __( 'Custom Login Screen', 'onesocial' ),
						'subtitle'	 => __( 'Toggle the custom WordPress login screen design on or off.', 'onesocial' ),
						'default'	 => '1',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'admin_login_info',
						'type'		 => 'info',
						'desc'		 => __( 'WordPress Login Screen', 'onesocial' ),
						'required'	 => array( 'boss_custom_login', 'equals', '1' ),
					),
					array(
						'id'		 => 'admin_logo_option',
						'type'		 => 'select',
						'title'		 => __( 'Title/Logo', 'onesocial' ),
						'subtitle'	 => __( 'Display the site title or upload a logo.', 'onesocial' ),
						'required'	 => array( 'boss_custom_login', 'equals', '1' ),
						'default'	 => 'title',
						'options'	 => array(
							'title'	 => __( 'Site Title', 'onesocial' ),
							'image'	 => __( 'Logo', 'onesocial' ),
						),
					),
					array(
						'id'			 => 'admin_site_title',
						'type'			 => 'typography',
						'title'			 => __( 'Site Title', 'onesocial' ),
						'subtitle'		 => __( 'Specify the site title properties.', 'onesocial' ),
						'google'		 => true,
						'line-height'	 => false,
						'text-align'	 => false,
						'subsets'		 => true,
						'color'			 => false,
						'required'		 => array( 'admin_logo_option', 'equals', 'title' ),
						'default'		 => array(
							'font-size'		 => '28px',
							'google'		 => 'true',
							'font-family'	 => 'Open Sans',
							'font-weight'	 => '900',
						)
					),
					array(
						'id'		 => 'boss_admin_login_logo',
						'type'		 => 'media',
						'url'		 => false,
						'required'	 => array( 'boss_custom_login', 'equals', '1' ),
						'title'		 => __( 'Custom Logo', 'onesocial' ),
						'subtitle'	 => __( 'We display a custom logo in place of the default WordPress logo.', 'onesocial' ),
						'required'	 => array( 'admin_logo_option', 'equals', 'image' ),
					),
					array(
						'id'		 => 'admin_custom_colors',
						'type'		 => 'color',
						'required'	 => array( 'boss_custom_login', 'equals', '1' ),
						'title'		 => __( 'Custom Colors', 'onesocial' ),
						'subtitle'	 => __( 'Edit the admin login screen colors in the <a href="javascript:void(0);" class="redux-group-tab-link-a" data-key="8" data-rel="8">Styling section</a>, under the "Admin Screen".', 'onesocial' ),
					),
				)
			);
*/
			// Codes Settings
			$this->sections[] = array(
				'title'		 => __( 'Custom Codes', 'onesocial' ),
				'icon'		 => 'el-icon-edit',
				'customizer' => false,
				'fields'	 => array(
					array(
						'id'		 => 'tracking',
						'type'		 => 'switch',
						'title'		 => __( 'Tracking Code', 'onesocial' ),
						'subtitle'	 => __( 'Paste your Google Analytics (or other) tracking code here. This will be added before the closing of body tag.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_tracking_code',
						'type'		 => 'ace_editor',
						'mode'		 => 'plain_text',
						'theme'		 => 'chrome',
						'required'	 => array( 'tracking', 'equals', '1' ),
					),
					array(
						'id'		 => 'custom_css',
						'type'		 => 'switch',
						'title'		 => __( 'CSS', 'onesocial' ),
						'subtitle'	 => __( 'Quickly add some CSS here to make design adjustments. It is a much better solution then manually editing the theme. You may also consider using a child theme.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_custom_css',
						'type'		 => 'ace_editor',
						'mode'		 => 'css',
						'validate'	 => 'css',
						'theme'		 => 'chrome',
						'default'	 => ".your-class {\n    color: blue;\n}",
						'required'	 => array( 'custom_css', 'equals', '1' ),
					),
					array(
						'id'		 => 'custom_js',
						'type'		 => 'switch',
						'title'		 => __( 'JavaScript', 'onesocial' ),
						'subtitle'	 => __( 'Quickly add some JavaScript code here. It is a much better solution then manually editing the theme. You may also consider using a child theme.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_custom_js',
						'type'		 => 'ace_editor',
						'mode'		 => 'javascript',
						'validate'	 => 'plain_text',
						'theme'		 => 'chrome',
						'default'	 => "jQuery( document ).ready( function(){\n    //Your codes strat from here\n});",
						'required'	 => array( 'custom_js', 'equals', '1' ),
					)
				)
			);

			// Optimizations
			$this->sections[] = array(
				'title'		 => __( 'Optimizations', 'onesocial' ),
				'id'		 => 'optimizations',
				'customizer' => false,
				'icon'		 => 'el-icon-tasks',
				'fields'	 => array(
					array(
						'id'		 => 'boss_minified_css',
						'type'		 => 'switch',
						'title'		 => __( 'Minify CSS', 'onesocial' ),
						'subtitle'	 => __( 'By default the theme loads stylesheets that are not minified. You can enable this setting to instead load minified and combined stylesheets.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_minified_js',
						'type'		 => 'switch',
						'title'		 => __( 'Minify JavaScript', 'onesocial' ),
						'subtitle'	 => __( 'By default the theme loads scripts that are not minified. You can enable this setting to instead load minified and combined JS files.', 'onesocial' ),
						'default'	 => '0',
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
					),
					array(
						'id'		 => 'boss_inputs',
						'type'		 => 'switch',
						'title'		 => __( 'Form Inputs JavaScript', 'onesocial' ),
						'subtitle'	 => __( 'To improve compatibility with certain setups, turn the JavaScript off related to dropdowns, checkboxes, and radios.', 'onesocial' ),
						'on'		 => __( 'On', 'onesocial' ),
						'off'		 => __( 'Off', 'onesocial' ),
						'default'	 => '1',
					),
				)
			);

			//Plugins
			$this->sections[] = array(
				'title'		 => __( 'Plugins', 'onesocial' ),
				'icon'		 => 'el-icon-wrench',
				'customizer' => false,
				'fields'	 => array(
					array(
						'id'		 => 'boss_plugin_support',
						'type'		 => 'raw',
						'full_width' => true,
						'callback'	 => 'boss_plugins_submenu_page_callback',
					),
				)
			);

			// Import / Export
			$this->sections[] = array(
				'title'	 => __( 'Import / Export', 'onesocial' ),
				//'desc'	 => __( 'Import and Export your Boss theme settings from file, text or URL.', 'onesocial' ),
				'icon'	 => 'el-icon-refresh',
				'fields' => array(
					array(
						'id'		 => 'opt-import-export',
						'type'		 => 'import_export',
						//'title'		 => 'Import Export',
						//'subtitle'	 => 'Save and restore your Boss options',
						'full_width' => true,
					),
				),
			);

			//Miscellaneous settings
			$entry_content		 = apply_filters( 'onesocial_entry_content', array( 'content' => 'Post Content', 'excerpt' => 'Post Excerpt' ) );
			$this->sections[] = array(
				'title'		 => __( 'Miscellaneous', 'onesocial' ),
				'icon'		 => 'el-icon-th',
				'fields'	 => array(
					array(
						'id'		 => 'onesocial_entry_content',
						'type'		 => 'select',
						'title'		 => __( 'Entry Content', 'onesocial' ),
						'options'	 => $entry_content,
						'default'	 => 'excerpt',
					)
				)
			);
		}

		/**
		 * Returns xprofile fields list
		 */
		public function boss_customizer_xprofile_field_choices() {
			$options = array();
			if ( function_exists( 'bp_is_active' ) && bp_is_active( 'xprofile' ) ) {
				global $wpdb, $bp;
				$field_groups = array();

				$dbfields = $wpdb->get_results(
				"SELECT g.id as 'group_id', g.name as 'group_name', f.id, f.name "
				. " FROM {$bp->profile->table_name_fields} f JOIN {$bp->profile->table_name_groups} g ON f.group_id=g.id "
				. " WHERE f.parent_id=0 "
				. " ORDER BY f.name ASC "
				);

				if ( !empty( $dbfields ) ) {
					foreach ( $dbfields as $dbfield ) {
						if ( !isset( $field_groups[ $dbfield->group_id ] ) ) {
							$field_groups[ $dbfield->group_id ] = array(
								'name'	 => $dbfield->group_name,
								'fields' => array(),
							);
						}

						$field_groups[ $dbfield->group_id ][ 'fields' ][ $dbfield->id ] = $dbfield->name;
					}

					$show_opt_group = count( $field_groups ) > 1 ? true : false;
					foreach ( $field_groups as $group_id => $group ) {
						if ( $show_opt_group ) {
							//optgroup > options
							$options[ $group[ 'name' ] ] = $group[ 'fields' ];
						} else {
							foreach ( $group[ 'fields' ] as $id => $name ) {
								//direct options
								$options[ $id ] = $name;
							}
						}
					}
				}
			}

			return $options;
		}

		/**
		 * All the possible arguments for Boss.
		 * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
		 * */
		public function setArguments() {

			$theme = wp_get_theme(); // For use with some settings. Not necessary.

			$this->args = array(
				// TYPICAL -> Change these values as you need/desire
				'opt_name'			 => 'onesocial_options', // This is where your data is stored in the database and also becomes your global variable name.
				'display_name'		 => $theme->get( 'Name' ), // Name that appears at the top of your panel
				'display_version'	 => $theme->get( 'Version' ), // Version that appears at the top of your panel
				'menu_type'			 => 'submenu', //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
				'allow_sub_menu'	 => true, // Show the sections below the admin menu item or not
				'menu_title'		 => __( 'OneSocial Theme', 'onesocial' ),
				'page_title'		 => __( 'OneSocial Theme', 'onesocial' ),
				// You will need to generate a Google API key to use this feature.
				// Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
				'google_api_key'	 => 'AIzaSyARjtGd3aZFBZ_8kJty6BwgRsCurPFvFeg', // https://console.developers.google.com/project/ Must be defined to add google fonts to the typography module
				'async_typography'	 => false, // Use a asynchronous font on the front end or font string
				'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
				'admin_bar'			 => false, // Show the panel pages on the admin bar
				'global_variable'	 => '', // Set a different name for your global variable other than the opt_name
				'dev_mode'			 => false, // Show the time the page took to load, etc
				'customizer'		 => true, // Enable basic customizer support
				//'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
				//'disable_save_warn' => true,                    // Disable the save warning when a user changes a field
				// OPTIONAL -> Give you extra features
				'page_priority'		 => null, // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
				'page_parent'		 => 'buddyboss-settings', // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
				'page_permissions'	 => 'manage_options', // Permissions needed to access the options panel.
				'menu_icon'			 => '', // Specify a custom URL to an icon
				'last_tab'			 => '', // Force your panel to always open to a specific tab (by id)
				'page_icon'			 => 'icon-themes', // Icon displayed in the admin panel next to your menu_title
				'page_slug'			 => 'onesocial_options', // Page slug used to denote the panel
				'save_defaults'		 => true, // On load save the defaults to DB before user clicks save or not
				'default_show'		 => false, // If true, shows the default value next to each field that is not the default value.
				'default_mark'		 => '', // What to print by the field's title if the value shown is default. Suggested: *
				'show_import_export' => true, // Shows the Import/Export panel when not used as a field.
				// CAREFUL -> These options are for advanced use only
				'transient_time'	 => 60 * MINUTE_IN_SECONDS,
				'output'			 => true, // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
				'output_tag'		 => true, // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
				'footer_credit'		 => ' ', // Disable the footer credit of Redux. Please leave if you can help it.
				// FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
				'database'			 => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
				'system_info'		 => false, // REMOVE
				'disable_tracking'	 => true,
				// HINTS
				'hints'				 => array(
					'icon'			 => 'icon-question-sign',
					'icon_position'	 => 'right',
					'icon_color'	 => 'lightgray',
					'icon_size'		 => 'normal',
					'tip_style'		 => array(
						'color'		 => 'light',
						'shadow'	 => true,
						'rounded'	 => false,
						'style'		 => '',
					),
					'tip_position'	 => array(
						'my' => 'top left',
						'at' => 'bottom right',
					),
					'tip_effect'	 => array(
						'show'	 => array(
							'effect'	 => 'slide',
							'duration'	 => '500',
							'event'		 => 'mouseover',
						),
						'hide'	 => array(
							'effect'	 => 'slide',
							'duration'	 => '500',
							'event'		 => 'click mouseleave',
						),
					),
				)
			);

			// SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
			$this->args[ 'share_icons' ][]	 = array(
				'url'	 => 'https://www.facebook.com/BuddyBossWP',
				'title'	 => 'Like us on Facebook',
				'icon'	 => 'el-icon-facebook'
			);
			$this->args[ 'share_icons' ][]	 = array(
				'url'	 => 'https://twitter.com/buddybosswp',
				'title'	 => 'Follow us on Twitter',
				'icon'	 => 'el-icon-twitter'
			);
			$this->args[ 'share_icons' ][]	 = array(
				'url'	 => 'https://www.linkedin.com/company/buddyboss',
				'title'	 => 'Find us on LinkedIn',
				'icon'	 => 'el-icon-linkedin'
			);

			// Panel Intro text -> before the form
			if ( !isset( $this->args[ 'global_variable' ] ) || $this->args[ 'global_variable' ] !== false ) {
				if ( !empty( $this->args[ 'global_variable' ] ) ) {
					$v = $this->args[ 'global_variable' ];
				} else {
					$v = str_replace( '-', '_', $this->args[ 'opt_name' ] );
				}
				$this->args[ 'intro_text' ] = sprintf( __( '<p>To access any of your saved options from within your code you can use your global variable: <strong>$%1$s</strong></p>', 'onesocial' ), $v );
			} else {
				$this->args[ 'intro_text' ] = __( '<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'onesocial' );
			}

			// Add content after the form.
			//$this->args[ 'footer_text' ] = __( '<p>This text is displayed below the options panel. It isn\'t required, but more info is always better! The footer_text field accepts all HTML.</p>', 'onesocial' );
		}

	}

	global $reduxConfig;
	$reduxConfig = new onesocial_Redux_Framework_config();
}
