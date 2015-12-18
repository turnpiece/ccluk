<hr class="section-head-divider" />
<div class="wrap grid_container">
	<h1 class="section-header"><i class="wdvicon-th-list"></i><?php echo $page_title; ?></h1>
	<div class="listing-form-elements">
		<table cellpadding="0" cellspacing="0" border="0">
			<tbody>
				<tr>
				<?php /* if (!isset($data['membership']) || 'full' != $data['membership']) { ?>
					<td width="20%">
						<span class="free_projects">
							<input type="checkbox" id="toggle-free-projects" />
							&nbsp;
							<label for="toggle-free-projects"><?php _e('Free only', 'wpmudev'); ?></label>
						</span>
					</td>
				<?php } */ ?>
				<?php if ( 'theme' == $page_type ) { ?>
					<td width="20%">
						<span class="legacy_projects">
							<input type="checkbox" id="toggle-legacy-projects" />
							&nbsp;
							<label for="toggle-legacy-projects"><?php _e('Legacy Themes', 'wpmudev'); ?></label>
						</span>
					</td>
				<?php } ?>
					<td width="25%">
						<label><?php _e('Sort:', 'wpmudev'); ?>
						<select id="sort_projects">
							<option value="popularity"><?php _e('Popularity', 'wpmudev'); ?></option>
							<option value="released"><?php _e('Release date', 'wpmudev'); ?></option>
							<option value="updated"><?php _e('Recently updated', 'wpmudev'); ?></option>
							<option value="downloads"><?php _e('Downloads', 'wpmudev'); ?></option>
							<option value="alphabetical"><?php _e('Alphabetically', 'wpmudev'); ?></option>
						</select>
						</label>
					</td>

					<td width="25%">
						<label><?php _e('Instant Search:', 'wpmudev'); ?>
						<input type="text" id="filter_projects" placeholder="<?php _e('Search', 'wpmudev'); ?>" /><a href="#" id="clear_search" title="<?php _e('Clear Search', 'wpmudev'); ?>" class="search-btn"><i class="wdvicon-remove-sign wdvicon-large"></i></a>
						</label>
					</td>
					<td width="5%">
						<label><?php _e('Results:', 'wpmudev'); ?></label> <h1 id="results-count"></h1>
					</td>
				</tr>
			</tbody>
		</table>
	</div>
</div>

<?php
if ( $this->get_apikey() && ($data['membership'] == 'full' || is_numeric($data['membership'])) && isset($data['downloads']) && $data['downloads'] != 'enabled' ) {
	?><div class="info_error"><p><i class="wdvicon-info-sign"></i>&nbsp;<?php _e('This site is not enabled for one-click installations. You may <a href="http://premium.wpmudev.org/wp-admin/profile.php?page=wdpun">change which sites are enabled or upgrade to a higher membership level here &raquo;</a>', 'wpmudev'); ?></p></div><?php
}
?>

<?php if (!$this->get_apikey()) { ?>
	<div class="info_error"><p><i class="wdvicon-info-sign"></i>&nbsp;<?php printf(__('Please <a href="%s">register or enter your details</a> to enable one-click installations.', 'wpmudev'), $this->dashboard_url); ?></p></div>
<?php } ?>

<div style="display:none" id="_installed-placeholder"><span href="#" class="wpmu-button icon installed-activated"><i class="wdvicon-ok wdvicon-large"></i><?php echo (is_multisite() || $page_type == 'theme' || defined('WPMUDEV_NO_AUTOACTIVATE')) ? __('INSTALLED', 'wpmudev') : __('INSTALLED & ACTIVATED', 'wpmudev'); ?></span></div>
<div style="display:none" id="_install_error-placeholder">
	<span href="#" class="wpmu-button error">
		<span class="tooltip">
			<section>Error Details</section>
			<i class='wdvicon-question-sign'></i>
		</span>
		<i class="wdvicon-warning-sign wdvicon-large"></i><?php _e('ERROR', 'wpmudev'); ?>
	</span>
</div>

<?php if (!$this->_install_message_is_hidden()) { ?>
	<div style="display:none" id="_install_setup-wrapper">
		<a href="#" class="_install_setup-close"><i class='wdvicon-remove'></i> <?php _e('close', 'wpmudev'); ?></a>
		<div>
		<p class="intro">
			<?php _e("Hang on a minute... It looks like your WordPress site isn't configured to allow one-click installations of plugins and themes.", 'wpmudev'); ?>
		</p>
		<p>
			<?php _e('You may still install this plugin using the manual process (by you entering your FTP credentials in the next step), or you can easily set up your site to do it automatically from now on.', 'wpmudev'); ?>
		</p>
		<br class="clear" />
		</div>
		<div>
			<span class="target"><a href="#" class="wpmu-button icon"><i class="wdvicon-download-alt wdvicon-large"></i><?php _e('MANUAL INSTALL', 'wpmudev'); ?></a></span>
			<a href="#" class="wpmu-button install_instructions"><i class="wdvicon-question-sign wdvicon-large"></i><?php _e('Setup one-click installation', 'wpmudev'); ?></a>
		</div>
		<label><input type="checkbox" id="_install_hide_msg" name="install_hide_msg" /> <?php _e('hide this message in future', 'wpmudev'); ?></label>
	</div>

	<div style="display:none" id="_install_setup-auto_install-wrapper">
		<a href="#" class="_install_setup-close"><i class='wdvicon-remove'></i> <?php _e('close', 'wpmudev'); ?></a>
		<p><?php _e('You can set up one-click installations by adding these lines to your <code>wp-config.php</code> file and customizing:', 'wpmudev'); ?></p>
		<code>define('FTP_USER', 'username');</code><br />
		<code>define('FTP_PASS', 'password');</code><br />
		<code>define('FTP_HOST', '<?php echo preg_replace('/www\./', '', parse_url(admin_url(), PHP_URL_HOST)); ?>');</code>
		<br /><br />
		<a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/configuring-automatic-updates/" target="_blank" class="_install_setup-info"><i class='wdvicon-info-sign'></i> <?php _e('More information', 'wpmudev'); ?></a>
		<br /><br />
		<a href="#" class="wpmu-button manual_install_setup_done"><i class="wdvicon-ok wdvicon-large"></i><?php _e('Done', 'wpmudev'); ?></a>
	</div>
<?php } ?>

<div class="listings-container grid_container wpmudev-dash">
	<div class="listings">
		<div class="listing-divider"></div>
		<div class="listing-divider2"></div>
		<ul data-page_type="<?php echo $page_type; ?>">
		<?php if (isset($data['projects']) && is_array($data['projects'])) foreach ($data['projects'] as $project) { ?>
			<?php
			$incompatible = false;
			if ($page_type != $project['type']) continue;
			//skip multisite only products if not compatible
			if ($project['requires'] == 'ms' && !is_multisite())
				$incompatible = __('Requires Multisite', 'wpmudev');
			//skip buddypress only products if not active
			if ($project['requires'] == 'bp' && !defined( 'BP_VERSION' ))
				$incompatible = __('Requires BuddyPress', 'wpmudev');
			//skip lite products if full member
			if (isset($data['membership']) && $data['membership'] == 'full' && $project['paid'] == 'lite') continue;

			//installed?
			$installed = (isset($local_projects[$project['id']])) ? true : false;

			//skip showing Protected Content and old Membership if not installed
			if ( ! $installed && in_array( $project['id'], array( 140, 928907 ) ) ) continue;

			//activated?
			$active = $activate_url = $deactivate_url = $upfront_install = false;
			if ($installed) {
				if ($page_type == 'plugin') {
					if (is_multisite() && is_network_admin())
						$active = is_plugin_active_for_network($local_projects[$project['id']]['filename']);
					else
						$active = is_plugin_active($local_projects[$project['id']]['filename']);

					if ($active) {
						if ( !is_multisite() || current_user_can( 'manage_network_plugins' ) ) //only can activate if not multisite or have permissions in multisite
							$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;plugin=' . urlencode($local_projects[$project['id']]['filename']), 'deactivate-plugin_' . $local_projects[$project['id']]['filename'] );
					} else {
						if ( ( !is_multisite() || current_user_can( 'manage_network_plugins' ) ) && $this->project_compatible( $project['id'] ) ) //only can activate if not multisite or have permissions in multisite and project is compatible with this system
							$activate_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . urlencode($local_projects[$project['id']]['filename']), 'activate-plugin_' . $local_projects[$project['id']]['filename'] );
					}
				} else { //themes list
					if ( !is_multisite() ) { //only do theme config/activate stuff in single site
						$active = $local_projects[$project['id']]['filename'] == $current_theme;

						if ( !$active && current_user_can('switch_themes') && $this->project_compatible( $project['id'] ) && ( $this->is_legacy_theme( $project['id'] )  || ( $this->is_upfront_theme( $project['id'] ) && $this->is_upfront_installed() ) ) ) {
							$activate_url = wp_nonce_url( "themes.php?action=activate&amp;template=" . urlencode( $local_projects[$project['id']]['filename'] ) . "&amp;stylesheet=" . urlencode( $local_projects[$project['id']]['filename'] ), 'switch-theme_' . $local_projects[$project['id']]['filename'] );
						}
					}

					//is it upfront and parent not installed?
					if ( current_user_can('install_themes') && $this->is_upfront_theme( $project['id'] ) && ! $this->is_upfront_installed() ) {
						$upfront_install = $this->auto_install_url( $this->upfront );
					}
				}
			}

			$config_url = false;
			if ($active) {
				if (is_multisite() && is_network_admin())
					$config_url = empty($project['ms_config_url']) ? false : network_admin_url($project['ms_config_url']);
				else
					$config_url = empty($project['wp_config_url']) ? false : admin_url($project['wp_config_url']);
			}

			// Alright, if we came up short up there ^ , try to find a settings link hooked into the plugin action links
			if ($active && !$config_url && $page_type == 'plugin') {
				$all_links = apply_filters('plugin_action_links_' . plugin_basename($local_projects[$project['id']]['filename']), array(), $local_projects[$project['id']]['filename']);
				if (isset($all_links[0]) && 1 === count($all_links)) { // We have some links, and we have one link - which is hopefully the settings link
					$href = preg_replace('/^.*(https?:\/\/[^\'"]+)[\'"].*/', '\1', $all_links[0]);
					if (!empty($href)) $config_url = $href;
				}
			}

			$action_class = '';
			if ('plugin' == $project['type']) {
				$action_class = $this->_can_auto_download_project($project['type'])
					? ((is_multisite() && is_network_admin()) || defined('WPMUDEV_NO_AUTOACTIVATE') ? 'install_plugin' : 'install_and_activate_plugin')
					: ($this->_install_message_is_hidden() ? '' : 'install_setup')
				;
			} else {
				$action_class = $this->_can_auto_download_project($project['type'])
					? 'install_theme'
					: ($this->_install_message_is_hidden() ? '' : 'install_setup')
				;
			}

			$listing_class = '';
			if ($installed) $listing_class .= ' installed';
			if ($incompatible) $listing_class .= ' incompatible';
			if ( 'theme' == $page_type && $this->is_legacy_theme( $project['id'] ) ) $listing_class .= ' legacy';
			//for free members, add free class to free projects for styling
			if ((!isset($data['membership']) || 'full' != $data['membership']) && ($project['paid'] == 'free' || $project['paid'] == 'lite')) {
				$listing_class .= ' free_project';
			}
			?>
			<li class="listing-item<?php echo $listing_class; ?>" title="<?php _e('More Info &raquo;', 'wpmudev'); ?>"
				data-project_id="<?php echo $project['id']; ?>"
				data-released="<?php echo $project['released']; ?>"
				data-updated="<?php echo $project['updated']; ?>"
				data-downloads="<?php echo $project['downloads']; ?>"
				data-popularity="<?php echo $project['popularity']; ?>"
				data-paid="<?php echo esc_attr($project['paid']); ?>"
			>
				<div class="listing">
					<img src="<?php echo $project['thumbnail']; ?>" alt="<?php echo esc_attr($project['name']); ?>" width="100%">
					<h1><?php echo $project['name']; ?></h1>
					<p><?php echo substr($project['short_description'], 0, 120); ?>&hellip;  <a href="<?php echo $project['url']; ?>"><?php _e('Learn more', 'wpmudev'); ?></a></p>
					<span class="full-excerpt" style="display:none;"><?php echo esc_attr($project['short_description']); ?></span>
					<span class="project_tags" style="display:none;">
					<?php
						$project_tags = array();
						foreach ($tags as $tag) {
							if (in_array($project['id'], $tag['pids'])) $project_tags[] = $tag['name'];
						}
						if ($project_tags) echo join(', ', $project_tags);
					?>
					</span>
				</div>
				<div class="install_wrap">
					<span class="target">
					<?php
					if ($installed) {
						/* ?><span class="wpmu-button icon installed"><i class="wdvicon-ok wdvicon-large"></i><?php _e('INSTALLED', 'wpmudev'); ?></span><?php */
					} else if ($incompatible) {
						?><span class="wpmu-button icon button-incompatible"><i class="wdvicon-remove-sign wdvicon-large"></i><?php echo $incompatible; ?></span><?php
					} else if (!$this->get_apikey()) { //no api key yet
						?><a href="<?php echo $this->dashboard_url; ?>" class="wpmu-button icon button-disabled" title="<?php _e('Setup your WPMU DEV account to install', 'wpmudev'); ?>"><i class="wdvicon-download-alt wdvicon-large"></i><?php _e('INSTALL', 'wpmudev'); ?></a><?php
					} else if ($url = $this->auto_install_url($project['id'])) {
						?><a href="<?php echo $url; ?>" data-downloading="<?php esc_attr_e(__('DOWNLOADING...', 'wpmudev')); ?>" data-installing="<?php esc_attr_e(__('INSTALLING...', 'wpmudev')); ?>" class="wpmu-button icon <?php echo $action_class; ?>"><i class="wdvicon-download-alt wdvicon-large"></i><?php _e('INSTALL', 'wpmudev'); ?></a><?php
					} else if ($this->user_can_install($project['id'])) { //has permission, but it's not autoinstallable
						?><a href="<?php echo esc_url($project['url']); ?>" target="_blank" class="wpmu-button icon"><i class="wdvicon-download wdvicon-large"></i><?php _e('DOWNLOAD', 'wpmudev'); ?></a><?php
					} else { //needs to upgrade
						?><a href="<?php echo apply_filters('wpmudev_project_upgrade_url', esc_url('https://premium.wpmudev.org/wp-login.php?redirect_to=' . urlencode($project['url']) . '#signup'), (int)$project['id']); ?>" target="_blank" class="wpmu-button icon"><i class="wdvicon-arrow-up wdvicon-large"></i><?php _e('UPGRADE TO INSTALL', 'wpmudev'); ?></a><?php
					}
					?>
					</span>
					<?php if ($installed) { ?>
					<div class="action_links">
						<?php if ($deactivate_url) { ?>
						<a href="<?php echo $deactivate_url; ?>"><i class="wdvicon-off"></i><?php echo is_network_admin() ? __('Network Deactivate', 'wpmudev') : __('Deactivate', 'wpmudev'); ?></a>
						<?php } else if ($activate_url) { ?>
						<a href="<?php echo $activate_url; ?>"><i class="wdvicon-off"></i><?php echo is_network_admin() ? __('Network Activate', 'wpmudev') : __('Activate', 'wpmudev'); ?></a>
						<?php } ?>

						<?php if ($upfront_install) { ?>
							<a href="<?php echo esc_url($upfront_install); ?>"><i class="wdvicon-download-alt"></i><?php _e('Install Upfront Parent', 'wpmudev'); ?></a>
						<?php } //end if upfront ?>

						<?php if ($active && $config_url) { ?>
						<a href="<?php echo esc_url($config_url); ?>"><i class="wdvicon-cog"></i><?php _e('Configure', 'wpmudev'); ?></a>
						<?php } //end if config ?>

						<a href="<?php echo $this->server_url; ?>?action=help&id=<?php echo $project['id']; ?>&TB_iframe=true&width=640&height=800" class="thickbox" title="<?php printf( __('%s Installation & Use Instructions', 'wpmudev'), $project['name'] ); ?>"><i class="wdvicon-info-sign"></i><?php _e('Instructions', 'wpmudev'); ?></a>
					</div>
					<?php } //end if installed ?>
					<div class="listing-hr"></div>
				</div>
			</li>
		<?php } ?>
		</ul>
		<div id="no-results">
			<h1><?php _e('No Results', 'wpmudev'); ?></h1>
			<p><?php _e('Please change or <a href="#" title="Clear Filters">clear</a> your search filters above', 'wpmudev'); ?></p>
		</div>
	</div>
</div>

<div id="listing-details-container" class="listing-details-wrapper" style="display:none;">
	<div class="listing-details-container grid_container">
		<div class="listing-details-overlay grid_container">
			<div class="overlay-details">
				<div class="screenshot-container">
					<img />
				</div>
				<div class="screenshot-description">
					<span class="image-of">1 / 4</span>
					<br />
					<p class="screenshot-description"></p>
					<div class="screenshot-nav">
						<a href="#" class="faded"><i class="wdvicon-chevron-left wdvicon-large"></i></a><a href="#"><i class="wdvicon-chevron-right wdvicon-large"></i></a>
					</div>
				</div>
				<a class="symbol" href="#"><i class="wdvicon-remove wdvicon-large"></i></a>
			</div>
		</div>
		<div class="listing-details-content">
			<div class="listing-copy">
				<h1 id="listing-title"></h1>
				<h3 id="listing-excerpt"></h3>
				<div id="listing-description"></div>
				<div id="loading-details"><?php _e('Loading...', 'wpmudev'); ?></div>
				<div class="desc-links">
					<span id="listing-install">
						<a class="wpmu-button icon" href="#"><i class="wdvicon-download-alt wdvicon-large"></i> ACTION</a>
					</span> <span class="or-read-more"><?php _e('or', 'wpmudev'); ?> <a id="listing-readmore" href="#" target="_blank"><?php _e('Read more on WPMU DEV &raquo;', 'wpmudev'); ?></a></span>
				</div>
			</div>
			<div class="listing-screens">
				<span><a class="close-plugin-details" href="#"><?php _e('close plugin info', 'wpmudev'); ?> <i class="wdvicon-remove wdvicon-large"></i></a></span>
				<ul>
					<li>

						<div></div>
					</li>
				</ul>
			</div>
		</div>
	</div>
</div>