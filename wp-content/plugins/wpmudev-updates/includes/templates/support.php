
<h3 class="nav-tab-wrapper">
<?php
//$disabled = false;
$tab = ( !empty($_GET['tab']) ) ? $_GET['tab'] : 'usage';

$tabs = array(
	'usage'    	=> __('Instructions', 'wpmudev'),
	'support' 	=> __('Support Requests', 'wpmudev'),
	'system'    => __('System Info', 'wpmudev')
);

//add support access tab if allowed
if ( !defined('WPMUDEV_DISABLE_REMOTE_ACCESS') && current_user_can('edit_users') && $this->allowed_user() )
	$tabs['access'] = __('Support Access', 'wpmudev');

$tabhtml = array();

// If someone wants to remove or add a tab
$tabs = apply_filters( 'wpmudev_support_tabs', $tabs );

foreach ( $tabs as $stub => $title ) {
	$class = ( $stub == $tab ) ? ' nav-tab-active' : '';
	$tabhtml[] = '	<a href="' . $this->support_url . '&tab=' . $stub . '" class="nav-tab'.$class.'">'.$title.'</a>';
}

echo implode( "\n", $tabhtml );
?>
</h3>
<div class="clear"></div>

<?php
switch( $tab ) {

	//---------------------------------------------------//
	case "usage": ?>
	<section class="support-usage grid_container">
		<h2 class="section-header"><i class="wdvicon-info-sign"></i><?php _e('Plugin/Theme Usage Instructions', 'wpmudev') ?></h2>
		<?php
		$allow_auto = true;
		if (!$this->allowed_user()) {
			$allow_auto = false;
		}
		if ( $this->get_apikey() && $this->allowed_user() && ($data['membership'] == 'full' || is_numeric($data['membership'])) && isset($data['downloads']) && $data['downloads'] != 'enabled' ) {
			$allow_auto = false;
		}

		$projects = array();
		if ( is_array( $data ) ) {
			$remote_projects = isset($data['projects']) ? $data['projects'] : array();
			$local_projects = $this->get_local_projects();
			if ( is_array( $local_projects ) ) {
				foreach ( $local_projects as $local_id => $local_project ) {
					//skip if not in remote results
					if (!isset($remote_projects[$local_id]))
						continue;

					$type = $remote_projects[$local_id]['type'];

					$projects[$type][$local_id]['thumbnail'] = $remote_projects[$local_id]['thumbnail'];
					$projects[$type][$local_id]['name'] = $remote_projects[$local_id]['name'];
					$projects[$type][$local_id]['description'] = $remote_projects[$local_id]['short_description'];
					$projects[$type][$local_id]['url'] = $remote_projects[$local_id]['url'];
					$projects[$type][$local_id]['wp_config_url'] = $remote_projects[$local_id]['wp_config_url'];
					$projects[$type][$local_id]['ms_config_url'] = $remote_projects[$local_id]['ms_config_url'];
					$projects[$type][$local_id]['instructions_url'] = $remote_projects[$local_id]['instructions_url'];
					$projects[$type][$local_id]['support_url'] = $remote_projects[$local_id]['support_url'];
					$projects[$type][$local_id]['autoupdate'] = (($local_project['type'] == 'plugin' || $local_project['type'] == 'theme') && $this->get_apikey() && $allow_auto) ? $remote_projects[$local_id]['autoupdate'] : 0;

					//handle wp autoupgrades
					if ($projects[$type][$local_id]['autoupdate'] == '2') {
						if ($local_project['type'] == 'plugin') {
							$update_plugins = get_site_transient('update_plugins');
							if (isset($update_plugins->response[$local_project['filename']]->new_version))
								$projects[$type][$local_id]['remote_version'] = $update_plugins->response[$local_project['filename']]->new_version;
							else
								$projects[$type][$local_id]['remote_version'] = $local_project['version'];
						} else if ($local_project['type'] == 'theme') {
							$update_themes = get_site_transient('update_themes');
							if (isset($update_themes->response[$local_project['filename']]['new_version']))
								$projects[$type][$local_id]['remote_version'] = $update_themes->response[$local_project['filename']]['new_version'];
							else
								$projects[$type][$local_id]['remote_version'] = $local_project['version'];
						} else {
							$projects[$type][$local_id]['remote_version'] = $remote_projects[$local_id]['version'];
						}
					} else {
						$projects[$type][$local_id]['remote_version'] = $remote_projects[$local_id]['version'];
					}

					$projects[$type][$local_id]['local_version'] = $local_project['version'];
					$projects[$type][$local_id]['filename'] = $local_project['filename'];
					$projects[$type][$local_id]['type'] = $local_project['type'];
				}
			}
		}
		?>
		<p><?php _e('Here you can find a quick links to installation and usage instructions for the WPMU DEV plugins and themes installed on this server.', 'wpmudev') ?></p>

		<h3><?php _e('Installed WPMU DEV Plugins', 'wpmudev') ?></h3>
		<?php
		echo "
			<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
			<thead><tr>
			<th scope='col'>".__('Name', 'wpmudev')."</th>
			<th scope='col'>".__('Links', 'wpmudev')."</th>
			<th scope='col'>".__('Installed Version', 'wpmudev')."</th>
			<th scope='col'>".__('Latest Version', 'wpmudev')."</th>
			<th scope='col'>".__('Actions', 'wpmudev')."</th>
			</tr></thead>
			<tbody id='the-list'>
			";

		if (isset($projects['plugin']) && is_array($projects['plugin']) && count($projects['plugin']) > 0) {
			$class = (isset($class) && 'alternate' == $class) ? '' : 'alternate';
			foreach ($projects['plugin'] as $project_id => $project) {
				$local_version = $project['local_version'];
				$remote_version = $project['remote_version'];

				$check = (version_compare($remote_version, $local_version, '>')) ? "style='background-color:#EFF7FF;'" : '';

				if ( $project['autoupdate'] && $project['type'] == 'plugin' && $this->user_can_install($project_id) ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url('update.php?action=upgrade-plugin&plugin=') . $project['filename'], 'upgrade-plugin_' . $project['filename']) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> ".__('Auto Update', 'wpmudev')."</a>";
				} else if ( $project['autoupdate'] && $project['type'] == 'theme' && $this->user_can_install($project_id) ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url('update.php?action=upgrade-theme&theme=') . $project['filename'], 'upgrade-theme_' . $project['filename']) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> ".__('Auto Update', 'wpmudev')."</a>";
				} else if ($this->user_can_install($project_id)) {
					$upgrade_button_code = "<a href='" . esc_url($project['url']) . "' class='button-secondary' target='_blank'><i class='wdvicon-download-alt'></i> ".__('Download Update', 'wpmudev')."</a>";
				} else if (!$this->get_apikey()) { //no api key yet
					$upgrade_button_code = "<a href='" . $this->dashboard_url . "' title='" . __('Setup your WPMU DEV account to update', 'wpmudev') . "' class='button-secondary'><i class='wdvicon-pencil'></i> ".__('Configure to Update', 'wpmudev')."</a>";
				} else {
					$upgrade_button_code = "<a href='" . apply_filters('wpmudev_project_upgrade_url', esc_url('https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode($project['url']) . '#signup'), $project_id) . "' class='button-secondary' target='_blank'><i class='wdvicon-arrow-up'></i> ".__('Upgrade to Update', 'wpmudev')."</a>";
				}

				$upgrade_button = (version_compare($remote_version, $local_version, '>')) ? $upgrade_button_code : '';

				//get configure link
				$config_url = $active = false;
				if (is_multisite() && is_network_admin())
					$active = is_plugin_active_for_network($local_projects[$project_id]['filename']);
				else
					$active = is_plugin_active($local_projects[$project_id]['filename']);

				if ($active) {
					if (is_multisite() && is_network_admin())
						$config_url = empty($project['ms_config_url']) ? false : network_admin_url($project['ms_config_url']);
					else
						$config_url = empty($project['wp_config_url']) ? false : admin_url($project['wp_config_url']);
				}
				if ($config_url) $config_url = '<br /><a href="' . esc_url($config_url) . '"><i class="wdvicon-cog"></i> ' . __('Configure', 'wpmudev') . '</a>';

				$screenshot = $project['thumbnail'];

				//=========================================================//
				echo "<tr class='" . $class . "' " . $check . " >";
				echo "<td style='vertical-align:middle'><img src='$screenshot' width='70' height='45' style='float:left; padding: 5px' /></a><strong><a href='{$this->server_url}?action=description&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('%s Details', 'wpmudev'), $project['name'] ) . "'>{$project['name']}</a></strong><br />{$project['description']}</td>";	 	 	 	 	 		    	
				echo "<td style='vertical-align:middle;width:250px;'><a href='{$this->server_url}?action=help&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('%s Installation & Use Instructions', 'wpmudev'), $project['name'] ) . "'><i class='wdvicon-info-sign'></i> " . __('Installation & Use Instructions', 'wpmudev') . "</a>$config_url</td>";
				echo "<td style='vertical-align:middle'><strong>" . $local_version . "</strong></td>";
				echo "<td style='vertical-align:middle'><strong><a href='{$this->server_url}?action=details&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('View version %s details', 'wpmudev'), $remote_version ) . "'>{$remote_version}</a></strong></td>";
				echo "<td style='vertical-align:middle'>" . $upgrade_button . "</td>";
				echo "</tr>";
				$class = ('alternate' == $class) ? '' : 'alternate';
				//=========================================================//
			}
		} else {
			?><tr><td colspan="5"><?php _e('No installed WPMU DEV plugins', 'wpmudev') ?></td></tr><?php
		}
		?>
		</tbody></table>

		<h3><?php _e('Installed WPMU DEV Themes', 'wpmudev') ?></h3>
		<?php
		echo "
			<table cellpadding='3' cellspacing='3' width='100%' class='widefat'>
			<thead><tr>
			<th scope='col'>".__('Name', 'wpmudev')."</th>
			<th scope='col'>".__('Instructions', 'wpmudev')."</th>
			<th scope='col'>".__('Installed Version', 'wpmudev')."</th>
			<th scope='col'>".__('Latest Version', 'wpmudev')."</th>
			<th scope='col'>".__('Actions', 'wpmudev')."</th>
			</tr></thead>
			<tbody id='the-list'>
			";

		if (isset($projects['theme']) && is_array($projects['theme']) && count($projects['theme']) > 0) {
			$class = (isset($class) && 'alternate' == $class) ? '' : 'alternate';
			foreach ($projects['theme'] as $project_id => $project) {
				$local_version = $project['local_version'];
				$remote_version = $project['remote_version'];

				$check = (version_compare($remote_version, $local_version, '>')) ? "style='background-color:#EFF7FF;'" : '';

				if ( $project['autoupdate'] && $project['type'] == 'plugin' && $this->user_can_install($project_id) ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url('update.php?action=upgrade-plugin&plugin=') . $project['filename'], 'upgrade-plugin_' . $project['filename']) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> ".__('Auto Update', 'wpmudev')."</a>";
				} else if ( $project['autoupdate'] && $project['type'] == 'theme' && $this->user_can_install($project_id) ) {
					$upgrade_button_code = "<a href='" . wp_nonce_url( $this->self_admin_url('update.php?action=upgrade-theme&theme=') . $project['filename'], 'upgrade-theme_' . $project['filename']) . "' class='button-secondary'><i class='wdvicon-upload-alt'></i> ".__('Auto Update', 'wpmudev')."</a>";
				} else if ($this->user_can_install($project_id)) {
					$upgrade_button_code = "<a href='" . esc_url($project['url']) . "' class='button-secondary' target='_blank'><i class='wdvicon-download-alt'></i> ".__('Download Update', 'wpmudev')."</a>";
				} else if (!$this->get_apikey()) { //no api key yet
					$upgrade_button_code = "<a href='" . $this->dashboard_url . "' title='" . __('Setup your WPMU DEV account to update', 'wpmudev') . "' class='button-secondary'><i class='wdvicon-pencil'></i> ".__('Configure to Update', 'wpmudev')."</a>";
				} else {
					$upgrade_button_code = "<a href='" . apply_filters('wpmudev_project_upgrade_url', esc_url('https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode($project['url']) . '#signup'), $project_id) . "' class='button-secondary' target='_blank'><i class='wdvicon-arrow-up'></i> ".__('Upgrade to Update', 'wpmudev')."</a>";
				}

				$upgrade_button = (version_compare($remote_version, $local_version, '>')) ? $upgrade_button_code : '';

				$screenshot = $project['thumbnail'];

				//=========================================================//
				echo "<tr class='" . $class . "' " . $check . " >";
				echo "<td style='vertical-align:middle'><img src='$screenshot' width='70' height='45' style='float:left; padding: 5px' /></a><strong><a href='{$this->server_url}?action=description&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('%s Details', 'wpmudev'), $project['name'] ) . "'>{$project['name']}</a></strong><br />{$project['description']}</td>";
				echo "<td style='vertical-align:middle;width:250px;'><a href='{$this->server_url}?action=help&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('%s Installation & Use Instructions', 'wpmudev'), $project['name'] ) . "'><i class='wdvicon-info-sign'></i> " . __('Installation & Use Instructions', 'wpmudev') . "</a></td>";
				echo "<td style='vertical-align:middle'><strong>" . $local_version . "</strong></td>";
				echo "<td style='vertical-align:middle'><strong><a href='{$this->server_url}?action=details&id={$project_id}&TB_iframe=true&width=640&height=800' class='thickbox' title='" . sprintf( __('View version %s details', 'wpmudev'), $remote_version ) . "'>{$remote_version}</a></strong></td>";
				echo "<td style='vertical-align:middle'>" . $upgrade_button . "</td>";
				echo "</tr>";
				$class = ('alternate' == $class) ? '' : 'alternate';
				//=========================================================//
			}
		} else {
			?><tr><td colspan="5"><?php _e('No installed WPMU DEV themes', 'wpmudev') ?></td></tr><?php
		}
		?>
		</tbody></table>
	</section>
	<?php
	break;


	//---------------------------------------------------//
	case "support": ?>

		<section class="lightbox overlay">
			<section class="before-you-post lightbox" >
				<h3><?php _e("Here are some ways you can solve your problem right now!", 'wpmudev'); ?></h3>
				<ol>
					<li><?php _e("Make sure you're running the latest version of WordPress.", 'wpmudev'); ?></li>
					<li><?php _e("<a href='admin.php?page=wpmudev-updates'>Make sure</a> you are using the latest version of our product.", 'wpmudev'); ?></li>
					<li><?php _e("Have you read the <a href='admin.php?page=wpmudev-updates&tab=installed'>'Usage' instructions</a>? It's like a mini manual.", 'wpmudev'); ?></li>
					<li><?php _e("Have you tried searching? Use the field in the top right to search our support.", 'wpmudev'); ?></li>
				</ol>
				<h3><?php _e("And if you're feeling a bit more technical:", 'wpmudev'); ?></h3>
				<ol>
					<li><?php _e("Disable and re-activate the plugin or theme.", 'wpmudev'); ?></li>
					<li><?php _e("Check for a plugin conflict - try disabling other plugins and see if that fixes it... if it does, notify us &amp; we'll find a fix.", 'wpmudev'); ?></li>
					<li><?php _e("Check for a theme conflict - try another theme (like Twenty Eleven) and see if it fixes it... if it does, notify us &amp; we'll find a fix.", 'wpmudev'); ?></li>
				</ol>
			</section>
		</section>

	<?php if ( !$this->get_apikey() || ($data['membership'] != 'full' && !is_numeric($data['membership'])) ) { ?>

		<section id="support-disabled">
			<section class="contents clearfix">
				<section class="layer" id="support-layer">
					<section class="promotional">
						<span class="tag-upgrade"></span>
						<h3 class="support-msg"><span class="wpmudev-logo-small"></span>&nbsp; <?php _e('members get unlimited, comprehensive support for <br/>all our products and any <br />WordPress related Queries', 'wpmudev') ?></h3>
						<a class="btn" href="<?php echo apply_filters('wpmudev_join_url', 'http://premium.wpmudev.org/join/'); ?>">
							<button class="wpmu-button"><?php _e('Find out more &raquo;', 'wpmudev') ?></button>
						</a>
						<?php if (!$this->get_apikey()) { ?>
						<p class="support-already-member"><a href="admin.php?page=wpmudev&clear_key=1"><?php _e('Already a member?', 'wpmudev') ?></a></p>
						<?php } ?>
					</section>
				</section>
			</section>
		</section>

	<?php } ?>

	<section class="support-wrap wpmudev-dash">
		<div class="grid_container">
			<h1 class="section-header">
				<i class="wdvicon-question-sign"></i><?php _e('Support', 'wpmudev') ?>
			</h1>
			<div class="listing-form-elements">
				<table cellpadding="0" cellspacing="0" border="0">
					<tbody>
						<tr>
							<td width="48%" align="center">&nbsp;</td>
							<td width="4.8%">&nbsp;</td>
							<td width="47%"><input type="text" id="search_projects" placeholder="<?php _e('Search support', 'wpmudev') ?>" /><a id="forum-search-go" href="#" class="search-btn"><i class="wdvicon-search"></i></a></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="support-container grid_container">
			<div class="ask-question-container">

				<div id="success_ajax" style="display:none;">
					<h1><i class="wdvicon-ok"></i> <?php _e("Success!", 'wpmudev') ?></h1>
					<p><?php _e("Thanks for contacting Support, we'll get back to you as soon as possible.", 'wpmudev'); ?>
						<a href="#" target="_blank"><?php _e('You can view or add to your support request here &raquo;', 'wpmudev'); ?></a>
					</p>
					<?php
					$access = get_site_option('wdp_un_remote_access');
					if ( ! defined('WPMUDEV_DISABLE_REMOTE_ACCESS') && current_user_can('edit_users') && $this->allowed_user() && !$access ) { //verify permissions
					?>
					<form action="<?php echo $this->support_url; ?>&tab=access" method="post">
						<?php wp_nonce_field( 'wdpun_access' ); ?>
						<p><strong><?php _e('In order to give you the fastest support possible, we highly recommend granting our support team temporary access to this site so we can quickly debug and fix your issue.', 'wpmudev') ?></strong>
						<?php _e('This is completely secure, optional, and fully controlled by you.', 'wpmudev') ?>
						<small><a href="<?php echo $this->support_url; ?>&tab=access"><?php _e('More info', 'wpmudev'); ?> &raquo;</a></small></p>
						<input type="submit" class="wpmu-button" name="grant-access" value="<?php _e('Grant Access', 'wpmudev') ?>">
					</form>
					<?php
					}
					?>
				</div>

				<form id="qa-form" method="post" enctype="multipart/form-data" action="">
					<fieldset>
						<legend>
							<?php _e("Question? Bug? Feature request? <br />Let's see how we can help.", 'wpmudev') ?><br />
							<small><?php _e('Before you post, please read', 'wpmudev'); ?> <a href="#" id="tips"><?php _e('these tips', 'wpmudev'); ?></a>.</small>
						</legend>

						<ol>
							<?php if ( $this->get_apikey() && ($data['membership'] == 'full' || is_numeric($data['membership'])) && isset($data['downloads']) && $data['downloads'] != 'enabled' ) { ?>
								<div class="error fade"><p><?php _e('This site is not enabled for direct dashboard support. You may <a href="http://premium.wpmudev.org/wp-admin/profile.php?page=wdpun">change which sites are enabled or upgrade to a higher membership level here &raquo;</a>', 'wpmudev'); ?></p></div>
							<?php } else if (!$this->allowed_user()) {
								$user_info = get_userdata( get_site_option('wdp_un_limit_to_user') );
							?>
								<div class="error fade"><p><?php printf(__('Only the admin user "%s" has access to WPMU DEV support.', 'wpmudev'), $user_info->display_name); ?></p></div>
							<?php } ?>

							<div id="error_topic" style="display:none;" class="error fade">
								<p><i class="wdvicon-warning-sign wdvicon-large"></i> <?php _e('Please enter your question title.', 'wpmudev'); ?></p>
							</div>
							<div id="error_ajax" style="display:none;" class="error fade">
								<p><i class="wdvicon-warning-sign wdvicon-large"></i> <?php _e('There was a problem posting your support question:', 'wpmudev'); ?></p>
							</div>
							<div id="error-short_title" style="display:none" class="error fade">
								<p>
								<i class="wdvicon-warning-sign"></i>
								<span><?php _e('Sorry, please add a bit more detail to your question or subject - we require this so that we can offer the best possible support and member experience.', 'wpmudev'); ?></span>
								</p>
							</div>
							<li>
								<div class="wrap"><label for="topic"><?php _e('Ask a question - the more detail the better', 'wpmudev') ?></label></div>
								<input type="text" name="topic" id="topic" maxlength="100"<?php echo $disabled; ?> />
							</li>
							<div id="error_project" style="display:none;" class="error fade"><p><i class="wdvicon-warning-sign wdvicon-large"></i> <?php _e('Please select what you need support for.', 'wpmudev'); ?></p></div>
							<li class="select">
								<select id="q-and-a" name="project_id">
									<option value=""><?php _e('Select an Installed Product:', 'wpmudev') ?></option>
									<?php
									$projects = $this->get_local_projects();
									$data = $this->get_updates();
									$forum = isset( $_GET['forum'] ) ? (int)$_GET['forum'] : false;
									$plugins = $themes = array();
									foreach ($projects as $pid => $project) {
										if (isset($data['projects'][$pid])) {
											if ($data['projects'][$pid]['type'] == 'plugin')
												$plugins[ trim($data['projects'][$pid]['name']) ] = '<option value="'.$pid.'"'.$disabled.'>'.esc_attr($data['projects'][$pid]['name'])."</option>";
											else if ($data['projects'][$pid]['type'] == 'theme')
												$themes[ trim($data['projects'][$pid]['name']) ] = '<option value="'.$pid.'"'.$disabled.'>'.esc_attr($data['projects'][$pid]['name'])."</option>";
										}
									}
									if ( count($plugins) ) {
										ksort($plugins); //sort alphabetically
										echo '<optgroup forum_id="1" label="'.__('Plugins:', 'wpmudev').'">' . implode("\n", $plugins) . '</optgroup>';
									}
									if ( count($themes) ) {
										ksort($themes); //sort alphabetically
										echo '<optgroup forum_id="2" label="'.__('Themes:', 'wpmudev').'">' . implode("\n", $themes) . '</optgroup>';
									}
									?>
									<optgroup label="<?php _e('General Topic:', 'wpmudev'); ?>">
										<option forum_id="11" value=""<?php echo $disabled; selected($forum, 11); ?>><?php _e('General', 'wpmudev'); ?></option>
										<option forum_id="10" value=""<?php echo $disabled; selected($forum, 10); ?>><?php _e('BuddyPress', 'wpmudev'); ?></option>
										<option forum_id="8" value=""<?php echo $disabled; selected($forum, 8); ?>><?php _e('Beginners WordPress Discussion', 'wpmudev'); ?></option>
										<option forum_id="7" value=""<?php echo $disabled; selected($forum, 7); ?>><?php _e('Advanced WordPress Discussion', 'wpmudev'); ?></option>
										<option forum_id="5" value=""<?php echo $disabled; selected($forum, 5); ?>><?php _e('Feature Suggestions &amp; Feedback', 'wpmudev'); ?></option>
									</optgroup>
								</select>
							</li>

							<div id="error_content" style="display:none;" class="error fade"><p><i class="wdvicon-warning-sign wdvicon-large"></i> <?php _e('Please enter your support question.', 'wpmudev'); ?></p></div>
							<li>
								<div class="wrap"><label for="post_content"><?php _e('Ok, go for it...', 'wpmudev') ?></label></div>
								<textarea rows="20" id="post_content" name="post_content"<?php echo $disabled; ?>></textarea>
							</li>
							<li>
								<p class="caution-note"><i class="wdvicon-info-sign"></i> <?php _e("Please don't share any private information (passwords, API keys, etc.) here, support staff will ask for these via email if they are required.", 'wpmudev') ?></p>
							</li>
							<li>
								<div class="wrap"><label for="notify-me"><?php _e("Notify me of responses via email", 'wpmudev') ?></label></div>
								<input type="checkbox" id="notify-me" checked="checked" value="1" name="stt_checkbox"<?php echo $disabled; ?> />

								<?php if ($disabled) { ?>
									<a class="wpmu-button icon"><i class="wdvicon-play-circle wdvicon-large"></i><?php _e("Post your question", 'wpmudev') ?></a>
								<?php } else { ?>
									<a id="qa-submit" class="wpmu-button icon"><i class="wdvicon-play-circle wdvicon-large"></i><?php _e("Post your question", 'wpmudev') ?></a>
								<?php } ?>
									<span id="qa-posting" class="wpmu-button icon" style="display:none;"><img src="<?php echo $spinner; ?>" /> <?php _e("Posting question...", 'wpmudev') ?></span>
							</li>
						</ol>
					</fieldset>

				<input type="hidden" value="1" id="forum_id" name="forum_id">
				</form>
				<img src="<?php echo $spinner; ?>" width="1" height="1" /><!-- preload -->
			</div>
			<?php if (!$disabled) { ?>
			<div class="your-latest-q-and-a" >
				<section class="recent-activity-widget" id="recent-qa-activity"<?php echo (isset($profile['forum']['support_threads']) && count($profile['forum']['support_threads'])) ? '' : ' style="display:none;"'; // hide Q&A activity if there is none ?>>
					<ul>
						<li class="accordion-title">
							<p><?php _e('YOUR LATEST Q&A ACTIVITY:', 'wpmudev'); ?> <a href="#" class="ui-hide-link"><span><?php _e('HIDE', 'wpmudev'); ?></span><span class="ui-hide-triangle"></span></a></p>
							<ul>
							<?php if (isset($profile['forum']['support_threads'])) foreach ($profile['forum']['support_threads'] as $thread) { ?>
								<li>
									<?php if ($thread['status'] == 'resolved') { ?>
									<i class="wdvicon-ok-sign wdvicon-large resolved" title="<?php _e('Resolved', 'wpmudev'); ?>"></i>
									<?php } else { ?>

									<?php } ?>
									<a href="<?php echo $thread['link'];?>" target="_blank"><?php echo $thread['title'];?></a>
								</li>
							<?php } else { ?>
								<li class="no-activity"><?php _e('No support activity yet.', 'wpmudev'); ?></li>
							<?php } ?>
							</ul>
						</li>
					</ul>
				</section>
			</div>
			<?php } ?>
		</div>
	</section>
	<?php
	break;


	//---------------------------------------------------//
	case "system": ?>

		<section class="support-debug grid_container">
		<h2 class="section-header"><i class="wdvicon-list"></i><?php _e('System Information', 'wpmudev') ?></h2>
		<p><?php _e('Here you can find useful system information about this server and WordPress installation that can help support staff with debugging.', 'wpmudev') ?></p>
		<?php
		$wpmudev_debug = new WPMUDEV_Debug();
		$wpmudev_debug->output_html();
		?>
		</section>
	<?php
	break;


	//---------------------------------------------------//
	case "access":
		//check for permissions before displaying
		if ( defined('WPMUDEV_DISABLE_REMOTE_ACCESS') || !current_user_can('edit_users') || !$this->allowed_user() ) break;
		?>
		<section class="lightbox overlay">
			<section class="before-you-post lightbox" >
				<h3><?php _e("How Support Access is Secure", 'wpmudev'); ?></h3>
				<p><?php _e('When you click the "Grant Access" button a random 64 character access token is generated that is only good for 72 hours and saved in your db. This token is sent to the WPMU DEV API over an SSL encrypted connection to prevent eavsdropping, and stored on our secure servers. This access token is in no way related to your password, and can only be used from our closed WPMU DEV API system for temporary access to this site.', 'wpmudev') ?></p>
				<p><?php _e('Only current WPMU DEV support staff can use this token to login as your user account by submitting a special form that only they have access to. This will give them 1 hour of admin access to this site before their login cookie expires. Every support staff login during the 72 hour period is logged locally and you can view the details on this page.', 'wpmudev'); ?></p>
				<p><?php _e('You may at any time revoke this access which invalidates the token and it will no longer be usable. If you have special security concerns and you would like to disable the support access tab and functionality completely and permanently for whatever reason, you may do so by adding this line to your wp-config.php file:', 'wpmudev') ?><br />define('WPMUDEV_DISABLE_REMOTE_ACCESS', true);</p>
			</section>
		</section>

		<section class="support-access grid_container">
		<h2 class="section-header"><i class="wdvicon-lock"></i><?php _e('Support Staff Access', 'wpmudev') ?></h2>
		<?php
		//handle posts
		if (isset($_POST['grant-access'])) {
			check_admin_referer( 'wdpun_access' );
			if ($this->enable_remote_access()) {
				?><div class="updated fade"><p><?php _e('Support staff access successfully enabled!', 'wpmudev'); ?></p></div><?php
			} else {
				?><div class="error fade"><p><?php _e('There was a problem enabling support staff access. Please check that you have Admin permissions and that this site is enabled in the API.', 'wpmudev'); ?></p></div><?php
			}
		} else if (isset($_POST['revoke-access'])) {
			check_admin_referer( 'wdpun_access' );
			if ($this->revoke_remote_access()) {
				?><div class="updated fade"><p><?php _e('Support staff access revoked!', 'wpmudev'); ?></p></div><?php
			} else {
				?><div class="error fade"><p><?php _e('There was a problem revoking support staff access. Please check that you have Admin permissions.', 'wpmudev'); ?></p></div><?php
			}
		}
		//show enabled error message if needed
		if ( $this->get_apikey() && ($data['membership'] == 'full' || is_numeric($data['membership'])) && isset($data['downloads']) && $data['downloads'] != 'enabled' ) {
			?><div class="info_error"><p><i class="wdvicon-info-sign"></i>&nbsp;<?php _e('This site is not enabled for support. You may <a href="http://premium.wpmudev.org/wp-admin/profile.php?page=wdpun">change which sites are enabled or upgrade to a higher membership level here &raquo;</a>', 'wpmudev'); ?></p></div><?php
		}
		?>
		<p><?php _e('Here you can grant WPMU DEV support staff temporary admin access to this site, review staff access history, and revoke access. This is completely secure, optional, and fully controlled by you.', 'wpmudev') ?>
		<small><a href="#" id="tips"><?php _e('More info', 'wpmudev'); ?> &raquo;</a></small></p>
		<form action="" method="post">
			<?php wp_nonce_field( 'wdpun_access' ); ?>

		<?php
		$access = get_site_option('wdp_un_remote_access');
		if ($access) {
			if ($access['expire'] >= time()) {
				echo '<h3>' . sprintf(__('Support access is %s until %s', 'wpmudev'), '<strong class="active">'.__('ACTIVE', 'wpmudev').'</strong>', get_date_from_gmt(date('Y-m-d H:i:s', $access['expire']), get_option('date_format') . ' ' . get_option('time_format'))) . '</h3>';
				echo '<p>' . __('You may revoke or extend access for another 72 hours.', 'wpmudev') . '</p>';
				?><input type="submit" class="wpmu-button" id="revoke-access" name="revoke-access" value="<?php _e('Revoke', 'wpmudev') ?>">
				<input type="submit" class="wpmu-button" id="grant-access" name="grant-access" value="<?php _e('Extend', 'wpmudev') ?>"<?php echo $disabled; ?>><?php
			} else {
				echo '<h3>' . sprintf(__('Support access is %s', 'wpmudev'), '<strong class="inactive">'.__('INACTIVE', 'wpmudev').'</strong>') . '</h3>';
				echo '<p>' . sprintf(__('Your last access grant expired %s.', 'wpmudev'), get_date_from_gmt(date('Y-m-d H:i:s', $access['expire']), get_option('date_format') . ' ' . get_option('time_format'))) . '</p>';
				?><input type="submit" class="wpmu-button" name="grant-access" value="<?php _e('Grant Access', 'wpmudev') ?>"<?php echo $disabled; ?>><?php
			}

			echo '<h3>' . __('Support Staff Logins:', 'wpmudev') . '</h3>';
			if ( isset($access['logins']) && is_array($access['logins']) && count($access['logins']) ) {
				echo '<ul id="wpmudev-login-log">';
				foreach ($access['logins'] as $timestamp => $staff) {
					echo '<li>' . get_date_from_gmt(date('Y-m-d H:i:s', $timestamp), get_option('date_format') . ' ' . get_option('time_format')) . ' - ' . esc_html($staff) . '</li>';
				}
				echo '</ul>';
			} else {
				echo '<p>' . __('No staff logins yet for this access grant.', 'wpmudev') . '</p>';
			}
		} else {
			echo '<h3>' . sprintf(__('Support access is %s', 'wpmudev'), '<strong class="active">'.__('INACTIVE', 'wpmudev').'</strong>') . '</h3>';
			?><input type="submit" class="wpmu-button" name="grant-access" value="<?php _e('Grant Access', 'wpmudev') ?>"<?php echo $disabled; ?>><?php
		}
		?>
		</form>
		</section>
	<?php
	break;
}