<div id="wph-wizard-settings-position" class="wpmudev-box-content">

    <div class="wpmudev-box-full">

        <h4><strong><?php _e( "Position Floating Social in respect to", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <div class="wpmudev-tabs">

            <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_md wpmudev-floating-position">

                <li class="wpmudev-tabs-menu_item">
                    <input  id="wpmudev-sshare-content-location" type="radio" name="location_type" data-attribute="location_type" value="content" {{ _.checked( (location_type === 'content'), true ) }} >
                    <label for="wpmudev-sshare-content-location" ><?php _e( "Content text", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item">
                    <input id="wpmudev-sshare-screen-location" type="radio" name="location_type" data-attribute="location_type" value="screen" {{ _.checked( (location_type === 'screen'), true ) }}  >
                    <label for="wpmudev-sshare-screen-location" ><?php _e( "Screen", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

                <li class="wpmudev-tabs-menu_item">
                    <input id="wpmudev-sshare-selector-location" type="radio" name="location_type" data-attribute="location_type" value="selector" {{ _.checked( (location_type === 'selector'), true ) }} >
                    <label for="wpmudev-sshare-selector-location" ><?php _e( "CSS selector", Opt_In::TEXT_DOMAIN ); ?></label>
                </li>

            </ul>

        </div>

        <div class="wpmudev-box-gray">

            <div id="wpmudev-sshare-selector-location-options" class="wpmudev-row {{ ( location_type !== 'selector' ) ? 'wpmudev-hidden' : '' }}">

                <div class="wpmudev-col col-12">

                    <label><?php _e( "CSS Selector (Class or ID only)", Opt_In::TEXT_DOMAIN ); ?></label>

                    <input type="text" placeholder="<?php _e( 'please include . or # characters to identify your selector', Opt_In::TEXT_DOMAIN ); ?>" class="wpmudev-input_text">

                </div>

            </div>

            <div class="wpmudev-row">

                <div class="wpmudev-col">

                    <div class="wpmudev-tabs">

                        <ul class="wpmudev-tabs-menu wpmudev-floating-horizontal">

                            <li class="wpmudev-tabs-menu_item">
                                <input type="radio" value="left" id="wpmudev-sshare-location-align-x-left" name="location_align_x" data-attribute="location_align_x" {{ _.checked( ( location_align_x === 'left' ), true ) }}>
                                <label for="wpmudev-sshare-location-align-x-left"><?php _e( "Left", Opt_In::TEXT_DOMAIN ); ?></label>
                            </li>

                            <li class="wpmudev-tabs-menu_item">
                                <input type="radio" value="right" id="wpmudev-sshare-location-align-x-right" name="location_align_x" data-attribute="location_align_x" {{ _.checked( ( location_align_x === 'right' ), true ) }}>
                                <label for="wpmudev-sshare-location-align-x-right"><?php _e( "Right", Opt_In::TEXT_DOMAIN ); ?></label>
                            </li>

                        </ul>

                        <div class="wpmudev-tabs-content">

                            <div id="wpmudev-floating-horizontal-left" class="wpmudev-tabs-content_item {{ ( location_align_x === 'left' ) ? 'current' : '' }}">

                                <label><?php _e( "Left offset", Opt_In::TEXT_DOMAIN ); ?> (px)</label>

                                <input type="number" value="{{location_left}}" class="wpmudev-input_number" data-attribute="location_left">

                            </div>

                            <div id="wpmudev-floating-horizontal-right" class="wpmudev-tabs-content_item {{ ( location_align_x === 'right' ) ? 'current' : '' }}">

                                <label><?php _e( "Right offset", Opt_In::TEXT_DOMAIN ); ?> (px)</label>

                                <input type="number" value="{{location_right}}" class="wpmudev-input_number" data-attribute="location_right" >

                            </div>

                        </div>

                    </div>

                </div>

                <div class="wpmudev-col">

                    <div class="wpmudev-tabs">

                        <ul class="wpmudev-tabs-menu wpmudev-floating-vertical">

                            <li class="wpmudev-tabs-menu_item">
                                <input type="radio" value="top" id="wpmudev-sshare-location-align-y-top" name="location_align_y" data-attribute="location_align_y" {{ _.checked( ( location_align_y === 'top' ), true ) }}>
                                <label for="wpmudev-sshare-location-align-y-top"><?php _e( "Top", Opt_In::TEXT_DOMAIN ); ?></label>
                            </li>

                            <li class="wpmudev-tabs-menu_item">
                                <input type="radio" value="bottom" id="wpmudev-sshare-location-align-y-bottom" name="location_align_y" data-attribute="location_align_y" {{ _.checked( ( location_align_y === 'bottom' ), true ) }}>
                                <label for="wpmudev-sshare-location-align-y-bottom"><?php _e( "Bottom", Opt_In::TEXT_DOMAIN ); ?></label>
                            </li>

                        </ul>

                        <div class="wpmudev-tabs-content">

                            <div id="wpmudev-floating-vertical-top" class="wpmudev-tabs-content_item {{ ( location_align_y === 'top' ) ? 'current' : '' }}">

                                <label><?php _e( "Top offset", Opt_In::TEXT_DOMAIN ); ?> (px)</label>

                                <input type="number" value="{{location_top}}" class="wpmudev-input_number" data-attribute="location_top">

                            </div>

                            <div id="wpmudev-floating-vertical-bottom" class="wpmudev-tabs-content_item {{ ( location_align_y === 'bottom' ) ? 'current' : '' }}">

                                <label><?php _e( "Bottom offset", Opt_In::TEXT_DOMAIN ); ?> (px)</label>

                                <input type="number" value="{{location_bottom}}" class="wpmudev-input_number" data-attribute="location_bottom">

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div><?php // #wph-wizard-settings-position ?>