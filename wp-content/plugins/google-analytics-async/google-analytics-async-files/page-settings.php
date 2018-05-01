<div class="wrap">
    <h2><?php _e( 'Google Analytics', $this->text_domain ) ?></h2>

    <?php
        global $google_analytics_async_dashboard;

        //Display status message
        if ( isset( $_GET['dmsg'] ) ) { ?>
            <div id="message" class="updated <?php echo (isset( $_GET['type'] ) && $_GET['type'] == 'error') ? 'error' : ''; ?>"><p><?php echo urldecode( $_GET['dmsg'] ); ?></p></div><?php
        }

        if ( 'network' == $network ): ?>
            <div id="ga-network-settings">
                <p><?php echo apply_filters('ga_login_main_description', __( 'Google Analytics is the enterprise-class web analytics solution that gives you rich insights into your website traffic and marketing effectiveness. Powerful, flexible and easy-to-use features now let you see and analyze your traffic data in an entirely new way. With Google Analytics, you\'re more prepared to write better-targeted ads, strengthen your marketing initiatives and create higher converting websites.', $this->text_domain )); ?></p>

                <p><?php  _e( 'To get going, just <a href="http://www.google.com/analytics/">sign up for Google Analytics</a>, set up a new account and log in with the button below to automatically configure basic settings. You may have to manually adjust settings if necessary.', $this->text_domain ); ?> <?php _e( 'Please keep in mind that it can take several hours before you see any stats.', $this->text_domain ); ?></p>

                <h3 class="title ga-basic-tracking"><?php _e( 'Basic Network Tracking Settings', $this->text_domain ) ?></h3>
                <form method="post" action="" class="control-modules">
                    <?php
                    if(!isset($accounts)) {
                        ?>
                            <p><?php echo __( 'Get access to google analytics account to automatically get tracking code for this website and enable access to network statistics inside WordPress Admin Dashboard.', $this->text_domain ).' '.__( 'You can do it in two ways:', $this->text_domain ); ?></p>
                            <p style="display: none;"><a href="<?php echo esc_url(add_query_arg('google_login', 1)); ?>" class="button button-secondary"><?php _e( 'Login with Google account', $this->text_domain ); ?></a></p>
                            <p class="button-holder"><?php _e( 'Easily <button class="button button-secondary open-module-options" data-module="code">get access code</button> or <button class="button button-secondary open-module-options" data-module="api_project">set up Google API project</button>', $this->text_domain ); ?></p>
                        <?php
                    }
                    else {
                        echo '<p><a href="'.esc_url(add_query_arg('google_logout', 1)).'" class="button button-secondary">'.__( 'Logout from Google account', $this->text_domain ).'</a></p>';

                        if($google_analytics_async_dashboard->google_login['logged_in'] == '1') {
                            ?>
                                <p><?php _e( 'Google is changing login method. Reauthentication is needed.', $this->text_domain ).' '.__( 'You can do it in two ways:', $this->text_domain ); ?></p>
                                <p class="button-holder"><?php _e( 'Easily <button class="button button-primary open-module-options" data-module="code">get access code</button> or <button class="button button-primary open-module-options" data-module="api_project">set up Google API project</button>', $this->text_domain ); ?></p>
                            <?php
                        }
                    }
                    if(!isset($accounts) || ($google_analytics_async_dashboard->google_login['logged_in'] == '1' && (!isset($google_api['verified']) || $google_api['verified'] != true))) {
                        ?>
                        <div data-module="api_project" class="sub-options">
                            <div class="postbox">
                                <ol>
                                    <li><span><?php _e( 'Google Client ID:', $this->text_domain ) ?></span> <input name="client_id" type="text" value="<?php echo isset($this->current_settings['google_api']['client_id']) ? $this->current_settings['google_api']['client_id'] : ''; ?>"/></li>
                                    <li><span><?php _e( 'Google Client Secret:', $this->text_domain ) ?></span> <input name="client_secret" type="text" value="<?php echo isset($this->current_settings['google_api']['client_secret']) ? $this->current_settings['google_api']['client_secret'] : ''; ?>"/></li>
                                    <li><span><?php _e( 'Google API key:', $this->text_domain ) ?></span> <input name="api_key" type="text" value="<?php echo isset($this->current_settings['google_api']['api_key']) ? $this->current_settings['google_api']['api_key'] : ''; ?>"/></li>
                                </ol>

                                <button type="submit" name="by_api" class="button button-primary"><?php _e( 'Authorize', $this->text_domain ) ?></button>
                            </div>
                        </div>
                        <div data-module="code" class="sub-options">
                            <div class="postbox">
                                <ol>
                                    <li><?php printf(__( 'Login and get access code <a target="_blank" href="%s">here</a>.', $this->text_domain ), $google_analytics_async_dashboard->google_client->createAuthUrl() ); ?></li>
                                    <li><?php _e( 'Input access code: ', $this->text_domain ) ?> <input name="code" type="text"/></li>
                                </ol>
                                <button type="submit" name="by_code" class="button button-primary"><?php _e( 'Authorize', $this->text_domain ) ?></button>
                            </div>
                        </div>

                        <p><?php echo apply_filters('ga_login_method_description', __( 'Access code it quicker solution but setting up Google API project is more suitable for high traffic website and will result in smoother experience for site admins (whole login process will be automatic, no access code copy pasting). You can read more about setting Google API project <a target="_blank" href="http://premium.wpmudev.org/project/google-analytics-for-wordpress-mu-sitewide-and-single-blog-solution/#product-usage">here</a> under "usage"')); ?></p>
                    <?php
                    }
                    ?>
                </form>

                <form method="post" action="">
                    <table  class="form-table ga-basic-tracking">

                        <tr class="ga-tracking-code" valign="top">
                            <th scope="row"><?php _e( 'Network-wide Tracking Code', $this->text_domain ); ?></th>
                            <td>
                                <input type="text" name="tracking_code" class="regular-text" value="<?php if ( !empty( $this->current_settings['track_settings']['tracking_code'] ) ) { echo $this->current_settings['track_settings']['tracking_code']; } ?>" />
                                <p class="description"><?php _e( 'Your Google Analytics tracking code. Ex: UA-XXXXX-X. The Network-wide tracking code will track your entire network of sub-sites.', $this->text_domain ); ?></p>
                            </td>
                        </tr>

                        <tr class="ga-stats" valign="top">
                            <th scope="row"><?php _e( 'Google Analytics Statistics inside WordPress Dashboard For All Sites In Network', $this->text_domain ); ?></th>
                            <td>
                                <?php
                                if(isset($accounts) ) {
                                    if(is_array($accounts) && count($accounts) > 0) {
                                        echo '<select name="google_analytics_account_id">';
                                            echo '<option value=""></option>';
                                        foreach($accounts as $account_id => $account_name) {
                                            echo '<option value="'.$account_id.'" '.((isset($this->current_settings['track_settings']['google_analytics_account_id']) && $this->current_settings['track_settings']['google_analytics_account_id'] == $account_id) ? 'selected' : '').'>'.$account_name.'</option>';
                                        }
                                        echo '</select>';
                                        echo '<p class="description">'.__( 'Choose correct Google Analytics profile to use for displaying statistics inside WordPress admin panel. Please make sure that this is a profile used for network wide tracking.', $this->text_domain ).'</p>';
                                    }
                                    else {
                                    if(isset($google_analytics_async_dashboard->error))
                                        echo '<p class="description">'.$google_analytics_async_dashboard->error.'</p>';
                                    else
                                        echo '<p class="description">'.__( 'You do not have any Google Analytics profiles to choose from. Please <a href="http://www.google.com/analytics/">create</a> new one.', $this->text_domain ).'</p>';
                                    }
                                }
                                else {
                                    echo '<p class="description">'.__( 'You need to login to google with the button above to enable this functionality.', $this->text_domain ).'</p>';
                                }

                            if(isset($this->current_settings['google_login_failure']))
                                echo '<p hidden>Last error: '.$this->current_settings['google_login_failure'].'</p>';
                                ?>
                            </td>
                        </tr>

                        <tr class="ga-admin-page-tracking" valign="top">
                            <th scope="row"><?php _e( 'Admin pages tracking', $this->text_domain ); ?></th>
                            <td>
                                <label><input type="radio" name="track_admin" value="1" <?php if ( !empty( $this->current_settings['track_settings']['track_admin'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ); ?></label>
                                <br />
                                <label><input type="radio" name="track_admin" value="0" <?php if ( empty( $this->current_settings['track_settings']['track_admin'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ); ?></label>
                            </td>
                        </tr>

    					<tr class="ga-domain-mapping" valign="top">
    						<th scope="row"><?php _e( 'Domain Mapping', $this->text_domain ); ?></th>
    						<td>
    							<label><input type="radio" name="domain_mapping" value="1" <?php if ( !empty( $this->current_settings['track_settings']['domain_mapping'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ) ?></label>
    							<br />
    							<label><input type="radio" name="domain_mapping" value="0" <?php if ( empty( $this->current_settings['track_settings']['domain_mapping'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ) ?></label>
    						</td>
    					</tr>

                    </table>

                    <h3 class="title ga-advanced-tracking"><?php _e( 'Advanced Network Tracking Settings', $this->text_domain ) ?></h3>

                    <table class="form-table ga-advanced-tracking">

                        <tr class="ga-anonymize-ip" valign="top">
                            <th scope="row"><?php _e( 'IP Anonymization', $this->text_domain ); ?></th>
                            <td>
                                <label><input type="radio" name="anonymize_ip" value="1" <?php if ( !empty( $this->current_settings['track_settings']['anonymize_ip'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ) ?></label>
                                <br />
                                <label><input type="radio" name="anonymize_ip" value="0" <?php if ( empty( $this->current_settings['track_settings']['anonymize_ip'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ) ?></label>
                                <p class="description"><?php _e( 'When enabled, the IP address of the visitor will be anonymized. You can read more about it <a href="https://support.google.com/analytics/answer/2763052?hl=en">here</a>.', $this->text_domain );?>
                            </td>
                        </tr>

                        <tr class="ga-display-advertising" valign="top">
                            <th scope="row"><?php _e( 'Support Display Advertising', $this->text_domain ); ?></th>
                            <td>
                                <label><label><input type="radio" name="display_advertising" value="1" <?php if ( !empty( $this->current_settings['track_settings']['display_advertising'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ) ?></label>
                                <br />
                                <label><input type="radio" name="display_advertising" value="0" <?php if ( empty( $this->current_settings['track_settings']['display_advertising'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ) ?></label>
                                <p class="description"><?php _e( 'This feature allows you to add demographics and interests reporting to Google Analytics. You can read more about it <a href="https://support.google.com/analytics/answer/3450482?hl=en&ref_topic=3413645&rd=1">here</a>. Please keep in mind that it requires <a href="https://support.google.com/analytics/answer/2700409">updating your privacy policy</a> on all sites in network.', $this->text_domain );?></p>
                            </td>
                        </tr>

                        <tr class="ga-tracking-method" valign="top">
                            <th scope="row"><?php _e( 'Tracking Method', $this->text_domain ); ?></th>
                            <td>
                                <label><input type="radio" name="track_method" value="universal" <?php if ( !isset($this->current_settings['track_settings']['track_method']) || $this->current_settings['track_settings']['track_method'] == 'universal') echo 'checked="checked"'; ?> /> <?php _e( 'Universal Analytics', $this->text_domain ) ?>
                                <span class="description"><?php _e( 'You can read more about this method <a href="https://support.google.com/analytics/answer/2790010?hl=en-GB&ref_topic=2790009">here</a>.', $this->text_domain ); ?></span></label>
                                <br />
                                <label><input type="radio" name="track_method" value="classic" <?php if (isset($this->current_settings['track_settings']['track_method']) && $this->current_settings['track_settings']['track_method'] == 'classic' ) echo 'checked="checked"'; ?> /> <?php _e( 'Classic Analytics', $this->text_domain ) ?></label>
                                <br />
                                <label><input type="radio" name="track_method" value="both" <?php if ( isset($this->current_settings['track_settings']['track_method']) && $this->current_settings['track_settings']['track_method'] == 'both' ) echo 'checked="checked"'; ?> /> <?php _e( 'Use both', $this->text_domain ) ?>
                                <span class="description"><?php _e( 'To use both, you need to have separate tracking code for universal analytics. Please add it here:', $this->text_domain ); ?></span>
                                <input type="text" name="tracking_code2" class="regular-text" value="<?php if ( isset($this->current_settings['track_settings']['tracking_code2']) && !empty( $this->current_settings['track_settings']['tracking_code2'] ) ) { echo $this->current_settings['track_settings']['tracking_code2']; } ?>" /></label>
                                <br />

                            </td>
                        </tr>

                        <tr class="ga-minimum-role-stats" valign="top">
                            <th scope="row">
                                <?php _e( 'Minimum role or capability to view Google Analytics Statistics inside WordPress Dashboard', $this->text_domain ); ?>
                            </th>
                            <td>
                                <?php _e( 'Select minimum role:', $this->text_domain ); ?>
                                <select name="minimum_role_capability_reports">
                                    <?php $current = isset($this->current_settings['track_settings']['minimum_role_capability_reports']) ? $this->current_settings['track_settings']['minimum_role_capability_reports'] : 'manage_options'; ?>
                                    <option value=""></option>
                                    <?php
                                    $roles = array(
                                        __( 'Super Administrator', $this->text_domain ) => 'manage_network_options',
                                        __( 'Administrator', $this->text_domain ) => 'manage_options',
                                        __( 'Editor', $this->text_domain ) => 'publish_pages',
                                        __( 'Author', $this->text_domain ) => 'publish_posts',
                                        __( 'Contributor', $this->text_domain ) => 'edit_posts',
                                        __( 'Subscriber', $this->text_domain ) => 'read'
                                    );
                                    foreach ($roles as $role => $capability) {
                                    ?>
                                        <option value="<?php echo $capability; ?>"<?php selected($current, $capability) ?>><?php echo $role; ?></option>
                                    <?php
                                    }
                                    ?>
                                </select>
                                <?php _e( '...or set custom capability:', $this->text_domain ); ?>
                                <?php $current = isset($this->current_settings['track_settings']['minimum_capability_reports']) ? $this->current_settings['track_settings']['minimum_capability_reports'] : ''; ?>
                                <input type="text" name="minimum_capability_reports" class="short-text" value="<?php echo $current; ?>" /><br/>
                                <?php $current = isset($this->current_settings['track_settings']['capability_reports_overwrite']) ? $this->current_settings['track_settings']['capability_reports_overwrite'] : 0; ?>
                                <?php _e( 'Allow site admins to overwrite this setting:', $this->text_domain ); ?> <select name="capability_reports_overwrite"><option value="0"<?php selected($current, '0') ?>><?php _e( 'No', $this->text_domain ); ?></option><option value="1"<?php selected($current, '1') ?>><?php _e( 'Yes', $this->text_domain ); ?></option></select>

                                <p class="description"><?php _e( 'Choose minimum role or set custom capability (leave blank to disable) required to see Google Analytics Statistics inside WordPress Dashboard.', $this->text_domain ); ?> <?php _e( 'You can also allow admistrators to overwrite this setting for the sites they control.', $this->text_domain ); ?></p>
                            </td>
                        </tr>

                        <?php
                        if ( function_exists('is_pro_site') ):
                            $levels = (array)get_site_option( 'psts_levels' );
                        ?>
                            <tr class="ga-minimum-pro-features" valign="top">
                                <th scope="row">
                                    <?php _e( 'Minimum Pro Site level for use of Google Analytics features', $this->text_domain ); ?>
                                </th>
                                <td>
                                    <select name="supporter_only">
                                        <?php $current = isset($this->current_settings['track_settings']['supporter_only']) ? $this->current_settings['track_settings']['supporter_only'] : 0; ?>
                                        <option value="0"<?php selected($current, 0) ?>><?php _e( 'Disable', $this->text_domain ); ?></option>
                                        <?php
                                        foreach ($levels as $level => $value) {
                                        ?>
                                            <option value="<?php echo $level; ?>"<?php selected($current, $level) ?>><?php echo $level . ': ' . esc_attr($value['name']); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                     <p class="description"><?php _e( 'Enable use of Google Analytics features for sites with selected minimum Pro Site level.', $this->text_domain ); ?></p>
                                </td>
                            </tr>
                            <tr class="ga-minimum-pro-stats" valign="top">
                                <th scope="row">
                                    <?php _e( 'Minimum Pro Site level for Google Analytics Statistics inside WordPress Dashboard', $this->text_domain ); ?>
                                </th>
                                <td>
                                    <select name="supporter_only_reports">
                                        <?php $current = isset($this->current_settings['track_settings']['supporter_only_reports']) ? $this->current_settings['track_settings']['supporter_only_reports'] : 0; ?>
                                        <option value="0"<?php selected($current, 0) ?>><?php _e( 'Disable', $this->text_domain ); ?></option>
                                        <?php
                                        foreach ($levels as $level => $value) {
                                        ?>
                                            <option value="<?php echo $level; ?>"<?php selected($current, $level) ?>><?php echo $level . ': ' . esc_attr($value['name']); ?></option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php _e( 'Enable network-wide tracking code based Google Analytics Statistics inside WordPress Dashboard for sites with selected minimum Pro Site level.', $this->text_domain ); ?></p>
                                </td>
                            </tr>
                        <?php
                        endif;
                        ?>

                    </table>

                    <p class="submit">
                        <?php wp_nonce_field('submit_settings_network'); ?>
                        <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
                    </p>

                </form>
            </div>

        <?php else: ?>

            <div id="ga-site-settings">
                <?php
                $initial_description = '
                <p>'.__( 'Google Analytics is the enterprise-class web analytics solution that gives you rich insights into your website traffic and marketing effectiveness. Powerful, flexible and easy-to-use features now let you see and analyze your traffic data in an entirely new way. With Google Analytics, you\'re more prepared to write better-targeted ads, strengthen your marketing initiatives and create higher converting websites.', $this->text_domain ).'</p>
                <p>'.__( 'To get going, just <a href="http://www.google.com/analytics/">sign up for Google Analytics</a>, set up a new account and log in with the button below to automatically configure basic settings. You may have to manually adjust settings if necessary.', $this->text_domain ).__( 'Please keep in mind that it can take several hours before you see any stats.', $this->text_domain ).'</p>';

                echo apply_filters('ga_initial_description',$initial_description);
                ?>

                <h3 class="title ga-basic-tracking"><?php _e( 'Basic Site Tracking Settings', $this->text_domain ) ?></h3>

                <form method="post" class="control-modules" action="">
                    <?php
                    $google_api = isset($this->network_settings['google_api']) ? $this->network_settings['google_api'] : array();

                    if(!isset($accounts)) {
                        ?>
                            <p><?php echo __( 'Get access to google analytics account to automatically get tracking code for this website and enable access to network statistics inside WordPress Admin Dashboard.', $this->text_domain ); ?></p>
                            <p style="display: none;"><a href="<?php echo esc_url(add_query_arg('google_login', 1)); ?>" class="button button-secondary"><?php _e( 'Login with Google account', $this->text_domain ); ?></a></p>
                            <?php if(isset($google_api['verified']) && $google_api['verified'] == true ) { ?>
                                <p><a href="<?php echo esc_url(add_query_arg('google_login', 2));?>" class="button button-secondary"><?php _e( 'Login with google account', $this->text_domain ); ?></a></p>
                            <?php } else { ?>
                                <p class="button-holder"><button class="button button-secondary open-module-options" data-module="code"><?php _e( 'Login with google account', $this->text_domain ); ?> <?php _e( 'and get access code', $this->text_domain ); ?></button></p>
                            <?php } ?>
                        <?php
                    }
                    else {
                        echo '<p><a href="'.esc_url(add_query_arg('google_logout', 1)).'" class="button button-secondary">'.__( 'Logout from Google account', $this->text_domain ).'</a></p>';

                        if(isset($this->settings['google_login']['logged_in']) && $this->settings['google_login']['logged_in'] == '1') {
                            if(isset($google_api['verified']) && $google_api['verified'] == true ) {
                                ?>
                                <p><a href="<?php echo esc_url(add_query_arg('google_login', 2));?>" class="button button-primary"><?php _e( 'Reauthenticate', $this->text_domain ); ?></a> <?php _e( 'Google is changing login method. Reauthentication is needed.', $this->text_domain ) ?></p>
                            <?php
                            } else {
                            ?>
                                <p class="button-holder"><button class="button button-primary open-module-options" data-module="code"><?php _e( 'Reauthenticate', $this->text_domain ); ?></button> <?php _e( 'Google is changing login method. Reauthentication is needed.', $this->text_domain ) ?></p>
                            <?php
                            }
                        }
                    }
                    if(!isset($accounts) || (isset($this->settings['google_login']['logged_in']) && $this->settings['google_login']['logged_in'] == '1' && (!isset($google_api['verified']) || $google_api['verified'] != true))) {
                        ?>
                        <div data-module="code" class="sub-options">
                            <div class="postbox">
                                <ol>
                                    <li><?php printf(__( 'Login and get access code <a target="_blank" href="%s">here</a>.', $this->text_domain ), $google_analytics_async_dashboard->google_client->createAuthUrl() ); ?></li>
                                    <li><?php _e( 'Input access code: ', $this->text_domain ) ?> <input name="code" type="text"/></li>
                                </ol>
                                <button type="submit" name="by_code" class="button button-primary"><?php _e( 'Authorize', $this->text_domain ) ?></button>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </form>
                <form method="post" action="">
                    <table class="form-table ga-basic-tracking">
                        <tr class="ga-tracking-code" valign="top">
                            <th scope="row"><?php _e( 'Site Tracking Code', $this->text_domain ); ?></th>
                            <td>
                                <label><input type="text" name="tracking_code" class="regular-text" value="<?php if ( !empty( $this->current_settings['track_settings']['tracking_code'] ) ) { echo $this->current_settings['track_settings']['tracking_code']; } ?>" /></label>
                                <br />
                                <p class="description"><?php _e( 'Your Google Analytics tracking code. Ex: UA-XXXXX-X. The Site tracking code will track this site. For more information on finding the tracking code, please visit <a href="https://support.google.com/analytics/answer/1032385?rd=1">this</a> site.', $this->text_domain ); ?></p>
                            </td>
                        </tr>

                        <tr class="ga-stats" valign="top" id="google-analytics-reports-settings">
                            <th scope="row"><?php _e( 'Custom Google Analytics Statistics inside WordPress Dashboard', $this->text_domain ); ?></th>
                            <td>
                                <?php
                                $network_settings = $this->get_options( null, 'network' );
                                if(isset($network_settings['google_login']['logged_in']) && isset($network_settings['track_settings']['google_analytics_account_id']) && $network_settings['track_settings']['google_analytics_account_id'])
                                    $network_stats_message = __( 'Currently .', $this->text_domain );
                                if(isset($accounts) ) {
                                    if(is_array($accounts) && count($accounts) > 0) {
                                        echo '<select name="google_analytics_account_id">';
                                            echo '<option value="0"></option>';
                                        foreach($accounts as $account_id => $account_name) {
                                            echo '<option value="'.$account_id.'" '.((isset($this->current_settings['track_settings']['google_analytics_account_id']) && $this->current_settings['track_settings']['google_analytics_account_id'] == $account_id) ? 'selected' : '').'>'.$account_name.'</option>';
                                        }
                                        echo '</select>';
                                        echo '<p class="description">'.__( 'Choose your own Google Analytics profile to use for displaying statistics inside WordPress admin panel.', $this->text_domain ).'</p>';
                                    }
                                    else {
                                        echo '<p class="description">'.__( 'You do not have any Google Analytics profiles to choose from. Please <a href="http://www.google.com/analytics/">create</a> new one.', $this->text_domain ).'</p>';
                                    }
                                }
                                else {
                                    echo '<p class="description">'.__( 'You need to login to google with the button above to use your profile for displaying statistics.', $this->text_domain ).'</p>';
                                }
                                ?>
                            </td>
                        </tr>

                    </table>

                    <div id="google-analytics-advanced-settings">
                        <h3 class="title ga-advanced-tracking"><?php _e( 'Advanced Site Tracking Settings', $this->text_domain ) ?></h3>
                        <table class="form-table ga-advanced-tracking">

                            <tr class="ga-display-advertising" valign="top">
                                <th scope="row"><?php _e( 'Support Display Advertising', $this->text_domain ); ?></th>
                                <td>
                                    <label><input type="radio" name="display_advertising" value="1" <?php if ( !empty( $this->current_settings['track_settings']['display_advertising'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ) ?></label>
                                    <br />
                                    <label><input type="radio" name="display_advertising" value="0" <?php if ( empty( $this->current_settings['track_settings']['display_advertising'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ) ?></label>
                                    <p class="description"><?php _e( 'This feature allows you to add demographics and interests reporting to Google Analytics. You can read more about it <a href="https://support.google.com/analytics/answer/3450482?hl=en&ref_topic=3413645&rd=1">here</a>. Please keep in mind that it requires <a href="https://support.google.com/analytics/answer/2700409">updating your privacy policy</a>.', $this->text_domain );?>
                                </td>
                            </tr>

                            <tr class="ga-anonymize-ip" valign="top">
                                <th scope="row"><?php _e( 'IP Anonymization', $this->text_domain ); ?></th>
                                <td>
                                    <label><input type="radio" name="anonymize_ip" value="1" <?php if ( !empty( $this->current_settings['track_settings']['anonymize_ip'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Enable', $this->text_domain ) ?></label>
                                    <br />
                                    <label><input type="radio" name="anonymize_ip" value="0" <?php if ( empty( $this->current_settings['track_settings']['anonymize_ip'] ) ) echo 'checked="checked"'; ?> /> <?php _e( 'Disable', $this->text_domain ) ?></label>
                                    <p class="description"><?php _e( 'When enabled, the IP address of the visitor will be anonymized. You can read more about it <a href="https://support.google.com/analytics/answer/2763052?hl=en">here</a>.', $this->text_domain );?>
                                </td>
                            </tr>

                            <tr class="ga-tracking-method" valign="top">
                                <th scope="row"><?php _e( 'Tracking Method', $this->text_domain ); ?></th>
                                <td>
                                    <label><input type="radio" name="track_method" value="universal" <?php if ( !isset($this->current_settings['track_settings']['track_method']) || $this->current_settings['track_settings']['track_method'] == 'universal') echo 'checked="checked"'; ?> /> <?php _e( 'Universal Analytics', $this->text_domain ) ?>
                                    <span class="description"><?php _e( 'You can read more about this method <a href="https://support.google.com/analytics/answer/2790010?hl=en-GB&ref_topic=2790009">here</a>.', $this->text_domain ); ?></span></label>
                                    <br />
                                    <label><input type="radio" name="track_method" value="classic" <?php if ( isset($this->current_settings['track_settings']['track_method']) && $this->current_settings['track_settings']['track_method'] == 'classic' ) echo 'checked="checked"'; ?> /> <?php _e( 'Classic Analytics', $this->text_domain ) ?></label>
                                    <br />
                                    <label><input type="radio" name="track_method" value="both" <?php if ( isset($this->current_settings['track_settings']['track_method']) && $this->current_settings['track_settings']['track_method'] == 'both' ) echo 'checked="checked"'; ?> /> <?php _e( 'Use both (beta)', $this->text_domain ) ?>
                                    <span class="description"><?php _e( 'To use both, you need to have separate tracking code for universal analytics. Please add it here:', $this->text_domain ); ?></span>
                                    <input type="text" name="tracking_code2" class="regular-text" value="<?php if ( isset($this->current_settings['track_settings']['tracking_code2']) && !empty( $this->current_settings['track_settings']['tracking_code2'] ) ) { echo $this->current_settings['track_settings']['tracking_code2']; } ?>" /></label>
                                </td>
                            </tr>

                            <?php
                            if(!is_multisite() || $this->network_settings['track_settings']['capability_reports_overwrite']) {
                            ?>
                                <tr class="ga-minimum-role-stats" valign="top">
                                    <th scope="row">
                                        <?php _e( 'Minimum role or capability to view Google Analytics Statistics inside WordPress Dashboard', $this->text_domain ); ?>
                                    </th>
                                    <td>
                                        <?php _e( 'Select minimum role:', $this->text_domain ); ?>
                                        <select name="minimum_role_capability_reports">
                                            <?php $current = isset($this->current_settings['track_settings']['minimum_role_capability_reports']) ? $this->current_settings['track_settings']['minimum_role_capability_reports'] : 'manage_options'; ?>
                                            <option value=""></option>
                                            <?php
                                            $roles = array(
                                                __( 'Administrator', $this->text_domain ) => 'manage_options',
                                                __( 'Editor', $this->text_domain ) => 'publish_pages',
                                                __( 'Author', $this->text_domain ) => 'publish_posts',
                                                __( 'Contributor', $this->text_domain ) => 'edit_posts',
                                                __( 'Subscriber', $this->text_domain ) => 'read'
                                            );
                                            foreach ($roles as $role => $capability) {
                                            ?>
                                                <option value="<?php echo $capability; ?>"<?php selected($current, $capability) ?>><?php echo $role; ?></option>
                                            <?php
                                            }
                                            ?>
                                        </select>
                                        <?php _e( '...or set custom capability:', $this->text_domain ); ?>
                                        <?php $current = isset($this->current_settings['track_settings']['minimum_capability_reports']) ? $this->current_settings['track_settings']['minimum_capability_reports'] : ''; ?>
                                        <input type="text" name="minimum_capability_reports" class="short-text" value="<?php echo $current; ?>" />
                                        <p class="description"><?php _e( 'Choose minimum role or set custom capability (leave blank to disable) required to see Google Analytics Statistics inside WordPress Dashboard.', $this->text_domain ); ?></p>
                                    </td>
                                </tr>
                            <?php
                            }
                            ?>
                        </table>
                    </div>

                    <p class="submit">
                        <?php wp_nonce_field('submit_settings'); ?>
                        <input type="submit" name="submit" class="button-primary" value="<?php _e( 'Save Changes', $this->text_domain ); ?>" />
                    </p>
                </form>
            </div>
        <?php endif; ?>
</div>

<script type="text/javascript">
    //Network admin login control
    jQuery('.control-modules .open-module-options').click(function(event) {
        event.preventDefault();

        var target = jQuery(this).attr('data-module');
        var position = jQuery(this).position();

        jQuery('.control-modules .sub-options').hide();
        jQuery('.control-modules .open-module-options').removeClass('active');

        jQuery('.control-modules .sub-options[data-module="'+target+'"]').show();
        jQuery(this).addClass('active');
    });
</script>