<?php
if (! defined ( 'WPINC' )) {
	exit ( 'Please do not access our files directly.' );
}
function members_only_free_howto_setting() 
{
	global $wpdb, $wp_roles;
	echo "<br />";

	$setting_panel_head = 'How To Use BuddyPress Members Only:';
	members_only_free_setting_panel_head ( $setting_panel_head );

	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_1';
	$membersonly_free_how_to_bar_title = 'How to Install BuddyPress Members Only';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>How to Install BuddyPress Members Only</h2>';
	$membersonly_free_how_to_bar_content .= '#1 Download BuddyPress Members Only from <a href="https://wordpress.org/plugins/buddypress-members-only/"  target="_blank">wordpress pligin page</a>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#2 Upload the BuddyPress Members Only plugin zip file to your site via <a href="'. get_option('siteurl').'/wp-admin/plugins.php" target="_blank">' .' plugins menu</a>';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#3 Activate the plugin "BuddyPress Members only" in '.'<a href="'. get_option('siteurl').'/wp-admin/plugins.php' .'"  target="_blank">' .' plugins page</a>';
	$membersonly_free_how_to_bar_content .= '</p>';	
	$membersonly_free_how_to_bar_content .= '</div>';
	
	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );

	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_2';
	$membersonly_free_how_to_bar_title = 'What is the Function of BuddyPress Members Only?';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>What is the Function of BuddyPress Members Only</h2>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= 'Buddypress Members Only Plugin will help you build a private buddypress community.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= 'Only logged in users allowed to view your buddypress site';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= 'Non-Member Users will be redirected to your homepage or register page or your membership landing page.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= 'Also you can open your pages to guest users in back end via buddypress members only settings panel';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '</div>';

	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );

	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_3';
	$membersonly_free_how_to_bar_title = 'How BuddyPress Members Only Works?';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>How BuddyPress Members Only Works?</h2>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#1 Home page of your site is always be opened to non member users.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#2 Login page will always be opened to non member users.';
	$membersonly_free_how_to_bar_content .= '</p>';	
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#3 Register page will always be opened to non member users.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#4 Lost Password page will always be opened to non member users.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#5 User activation page will always be opened to non member users.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#6 In backend “Buddypress Members Only Setting” menu -> Option Panel, you can setting “Register Page URL”, “Opened Page URLs”, please check screenshost at https://wordpress.org/plugins/buddypress-members-only/screenshots/';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#7 “Register Page URL” will opened to non member users always.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#8 “Opened Page URLs” will opened to non member users always.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#9 When guest users try to view any other pages on your site, they can not can view content, they will be redirected to the URL which you setting in “Register Page URL”.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#10 In setting panel, you can setting redirect logged in users to buddypress profile page or buddypress members page. If you did not install buddypress, this option will be ignored.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#11 BuddyPress Members Only supported HTTPS abd HTTP, we will detect HTTPS and HTTP automatically.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#12 BuddyPress members only plugin support WordPress too, if you disable buddypress on your site, our plugin will detect it and support wordpress members only automatically';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#13 The plugin support translate and launch localized versions, .po files can be found in languages sub-folder.';
	$membersonly_free_how_to_bar_content .= '</p>';    
	$membersonly_free_how_to_bar_content .= '</div>';
	
	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );
	
	
	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_4';
	$membersonly_free_how_to_bar_title = 'BuddyPress Members Only Plugin Settings';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>BuddyPress Members Only Plugin Settings</h2>';
	$membersonly_free_how_to_bar_content .= '#1 Please log in wordpress admin panel.';
	$membersonly_free_how_to_bar_content .= '<p>';	
	$membersonly_free_how_to_bar_content .= '#2 Please click "BuddyPress Members Only" Menu, You will open  '.'<a href="'. get_option('siteurl').'/wp-admin/admin.php?page=bpmemberonly" target="_blank">'.'“Buddypress Members Only Setting”'. '</a> Panel.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#3 By default, your home page and register page will opened to guest, other pages will open to logged in users. In "Opened Page URLs" option, you can add pages URLs to open these pages to guest users, include wordpress pages and buddypress pages.';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#5 If guest users click a page which is not opened to non-member users, they will be redirect to URL which you entered in "Register Page URL" option, you can enter login page, or register page, or home page, or your membership landing page in here';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= '#6 When users logged in your site, they will be redirected to URLs you selected from "Redirect Logged in Users to" select box. If you did not install buddypress, this option will be ignored.';
	$membersonly_free_how_to_bar_content .= '</p>';	
	$membersonly_free_how_to_bar_content .= '</div>';
	
	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );
	
	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_5';
	$membersonly_free_how_to_bar_title = 'What We are Developing Now';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>What We are Developing Now</h2>';
	$membersonly_free_how_to_bar_content .= 'We are developing a new version which to allow open a few buddypress components like members, activity... and so on to guest, based on webmaster settings in back end.';
	$membersonly_free_how_to_bar_content .= '</div>';
	
	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );
	
	$tooltips_free_default_how_to_bar_id = 'tooltips_knowledge_6';
	$membersonly_free_how_to_bar_title = 'How to Get Support';
	
	$membersonly_free_how_to_bar_content = '';
	$membersonly_free_how_to_bar_content .= '<div style="padding: 30px 20px 20px 20px;">';
	$membersonly_free_how_to_bar_content .= '<h2>How to Get Support From Our Official Site: membersonly.top?</h2>';
	$membersonly_free_how_to_bar_content .= '<p>';
	$membersonly_free_how_to_bar_content .= 'Please submit ticket at: '. '<a href="https://membersonly.top/contact-us/" target="_blank">'. 'Support Ticket'.'</a>';
	$membersonly_free_how_to_bar_content .= '</p>';
	$membersonly_free_how_to_bar_content .= '</div>';
	
	members_only_free_howto_setting_panel ( $tooltips_free_default_how_to_bar_id,$membersonly_free_how_to_bar_title,$membersonly_free_how_to_bar_content );

}
function members_only_free_howto_setting_panel($membersonly_free_how_to_bar_id, $membersonly_free_how_to_bar_title = '',$membersonly_free_how_to_bar_content = '') 
{
	global $wpdb, $wp_roles;
	?>
<div class="wrap">
	<div id="dashboard-widgets-wrap">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="post-body">
				<div id="dashboard-widgets-main-content">
					<div class="postbox-container" style="width: 90%;">
						<div
							class="postbox tooltips-pro-how-to-each-bar"
							id="tooltips-pro-how-to-each-bar-id"
							data-user-role="<?php echo $membersonly_free_how_to_bar_id ?>">					
							<span id='bp-members-pro-compent-plus-<?php echo $membersonly_free_how_to_bar_id; ?>'>+</span>
							<h3 class='hndle'
								style='padding: 10px; ! important; border-bottom: 0px solid #eee !important;'>
	<?php
	echo $membersonly_free_how_to_bar_title;
	?>
									</h3>

						</div>
						<div class="inside tomas-tooltips-howto-settings postbox"
							style='padding-left: 10px; border-top: 1px solid #eee;'
							id=<?php echo $membersonly_free_how_to_bar_id ?>>
							<?php echo $membersonly_free_how_to_bar_content; ?>
							<br />
						</div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<div style="clear: both"></div>
<?php
}

function members_only_free_setting_panel_head($title)
{
	?>
		<div style='padding-top:5px; font-size:22px;'><?php echo $title; ?></div>
		<div style='clear:both'></div>
<?php 
}

members_only_free_howto_setting();


