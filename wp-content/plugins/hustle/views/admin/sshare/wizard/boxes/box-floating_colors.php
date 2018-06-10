<div id="wph-wizard-floating-color-options">

    <label><?php _e( "BG & Icon colors", Opt_In::TEXT_DOMAIN ); ?></label>

    <div class="wpmudev-tabs">

        <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_lg">

            <li class="wpmudev-tabs-menu_item">

                <input type="radio" id="wph-wizard-disable-floating-customize-colors" name="customize_colors" data-attribute="customize_colors" value="0" {{_.checked(_.isFalse(customize_colors), true)}}>

                <label for="wph-wizard-disable-floating-customize-colors"><?php _e( "Default colors", Opt_In::TEXT_DOMAIN ); ?></label>

            </li>

            <li class="wpmudev-tabs-menu_item">

                <input type="radio" id="wph-wizard-enable-floating-customize-colors" name="customize_colors" data-attribute="customize_colors" value="1" {{_.checked(_.isTrue(customize_colors), true)}}>

                <label for="wph-wizard-enable-floating-customize-colors"><?php _e( "Custom colors", Opt_In::TEXT_DOMAIN ); ?></label>

            </li>

        </ul>

    </div>

    <div id="wph-wizard-sshare-floating-customize-colors-options" class="wpmudev-box-palette wpmudev-box-gray {{ ( _.isFalse(customize_colors) ) ? 'wpmudev-hidden' : '' }}">

        <div class="wpmudev-row" style="z-index: 2;">

            <# if ( icon_style !== 'flat' ) { #>

                <# if ( icon_style !== 'outline' ||
                        ( icon_style === 'outline' && ( service_type === 'custom' ) || ( service_type === 'native' && _.isFalse(click_counter) ) ) ) { #>

                    <div class="wpmudev-col col-12 col-xs-4 col-sm-12 col-lg-4">

                        <# if ( icon_style === 'outline' ) { #>

                            <label><?php _e( "Icon border", Opt_In::TEXT_DOMAIN ); ?></label>

                        <# } else { #>

                            <label><?php _e( "Icon BG", Opt_In::TEXT_DOMAIN ); ?></label>

                        <# } #>

                        <div class="wpmudev-picker" style="z-index: 3;"><input id="icon_bg_color" class="wpmudev-color_picker" type="text"  value="{{icon_bg_color}}" data-attribute="icon_bg_color" data-alpha="true" /></div>

                    </div>

                <# } #>

            <# } #>

            <#
                var icon_color_class = '';
                if ( icon_style !== 'flat' ) {
                    if ( (service_type === 'custom') || ( service_type === 'native' && _.isFalse(click_counter) ) ) {
                        icon_color_class = 'col-xs-8 col-sm-12 col-lg-8';
                    } else {
                        icon_color_class = 'col-xs-4 col-sm-12 col-lg-4';
                    }
                } else {
                    if ( service_type === 'native' && _.isTrue(click_counter) ) {
                        icon_color_class = 'col-xs-4 col-sm-12 col-lg-4';
                    }
                }
            #>

            <div class="wpmudev-col col-12 {{icon_color_class}}">

                <label><?php _e( "Icon color", Opt_In::TEXT_DOMAIN ); ?></label>

                <div class="wpmudev-picker" style="z-index: 2;"><input id="icon_color" class="wpmudev-color_picker" type="text"  value="{{icon_color}}" data-attribute="icon_color" data-alpha="true" /></div>

            </div>

            <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

                <#
                    var icon_border_class = '';
                    if ( icon_style !== 'flat' ) {
                        if ( ( icon_style !== 'outline' ) || ( icon_style === 'outline' && ( service_type === 'custom' ) ) || ( icon_style === 'outline' && ( service_type === 'native' && _.isFalse(click_counter) ) ) ) {
                            icon_border_class = 'col-xs-4 col-sm-12 col-lg-4';
                        } else {
                            icon_border_class = 'col-xs-8 col-sm-12 col-lg-8';
                        }
                    } else {
                        icon_border_class = 'col-xs-8 col-sm-12 col-lg-8';
                    }
                #>

                <div class="wpmudev-col col-12 {{icon_border_class}}">

                    <label><?php _e( "Counter border", Opt_In::TEXT_DOMAIN ); ?></label>

                    <div class="wpmudev-picker" style="z-index: 2;"><input id="floating_counter_border" class="wpmudev-color_picker" type="text"  value="{{floating_counter_border}}" data-attribute="floating_counter_border" data-alpha="true" /></div>

                </div>

            <# } #>

        </div>

    </div>

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12 {{ ( service_type === 'native' && _.isTrue(click_counter) ) ? 'col-xs-6 col-sm-12 col-lg-6' : '' }}">

            <label><?php _e( "Container BG", Opt_In::TEXT_DOMAIN ); ?></label>

            <div class="wpmudev-picker" style="z-index: 2;"><input id="floating_social_bg" class="wpmudev-color_picker" type="text"  value="{{floating_social_bg}}" data-attribute="floating_social_bg" data-alpha="true" /></div>

        </div>

        <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

            <div class="wpmudev-col col-12 col-xs-6 col-sm-12 col-lg-6">

                <label><?php _e( "Counter text", Opt_In::TEXT_DOMAIN ); ?></label>

                <div class="wpmudev-picker" style="z-index: 1;"><input id="floating_counter_color" class="wpmudev-color_picker" type="text"  value="{{floating_counter_color}}" data-attribute="floating_counter_color" data-alpha="true" /></div>

            </div>

        <# } #>

    </div>

</div>