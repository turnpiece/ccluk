<div id="wph-wizard-services-behaviour" class="wpmudev-box-content">

	<div class="wpmudev-box-right">

        <h4><strong><?php _e( "Icons and behavior", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <label class="wpmudev-helper"><?php _e( "Pick social icons you want to display and how they should behave. Default is an action that is native to the service. e.g. share on facebook, twitter etc. Custom allows you to add your profile links for these services.", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wpmudev-tabs">

            <ul class="wpmudev-tabs-menu wpmudev-tabs-menu wpmudev-tabs-menu_lg wpmudev-icons_behaviour-options">

                <li class="wpmudev-tabs-menu_item {{ ( service_type === 'native' ) ? 'current' : '' }}">

                    <input type="checkbox" data-attribute="service_type" value="native" >

                    <label><?php _e( "Default", Opt_In::TEXT_DOMAIN ); ?></label>

                </li>

                <li class="wpmudev-tabs-menu_item {{ ( service_type === 'custom' ) ? 'current' : '' }}">

                    <input type="checkbox" data-attribute="service_type" value="custom" >

                    <label><?php _e( "Custom", Opt_In::TEXT_DOMAIN ); ?></label>

                </li>

            </ul>

        </div>

        <div id="wpmudev-sshare-counter-options" class="wpmudev-switch-labeled {{ ( service_type === 'custom' ) ? 'wpmudev-hidden' : '' }}">

            <div class="wpmudev-switch">

                <input id="wph-sshares-counter" class="toggle-checkbox" type="checkbox" data-attribute="click_counter" {{ ( _.isTrue(click_counter) ) ? 'checked="checked"' : '' }} >

                <label class="wpmudev-switch-design" for="wph-sshares-counter" aria-hidden="true"></label>

            </div>

            <div class="wpmudev-switch-labels">

                <label class="wpmudev-switch-label" for="wph-sshares-counter"><?php _e( "Enable Click Counter", Opt_In::TEXT_DOMAIN ); ?></label>

                <label class="wpmudev-helper" for="wph-sshares-counter"><?php _e( "Shows number of times social icon has been clicked. Note, counter not linked to actual service.", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

        </div>

	</div>

</div><?php // #wph-wizard-services-behaviour ?>