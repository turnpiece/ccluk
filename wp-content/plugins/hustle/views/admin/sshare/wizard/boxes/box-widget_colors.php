<?php
$icons_behaviour = "native"; // You can use: "native", or "linked"
$has_counter = false;
$icons_style = "squared"; // You can use: "flat", "outline", "rounded", or "squared"

$float_icons_color = "custom";
?>

<div id="wph-wizard-widget-color-options">

    <label><?php _e( "BG & Icon colors", Opt_In::TEXT_DOMAIN ); ?></label>

    <div class="wpmudev-tabs">

        <ul class="wpmudev-tabs-menu wpmudev-tabs-menu_lg">

            <li class="wpmudev-tabs-menu_item">

                <input type="radio" id="wph-wizard-disable-widget-customize-colors" name="customize_widget_colors" data-attribute="customize_widget_colors" value="0" {{_.checked(_.isFalse(customize_widget_colors), true)}}>

                <label for="wph-wizard-disable-widget-customize-colors"><?php _e( "Default colors", Opt_In::TEXT_DOMAIN ); ?></label>

            </li>

            <li class="wpmudev-tabs-menu_item">

                <input type="radio" id="wph-wizard-enable-widget-customize-colors" name="customize_widget_colors" data-attribute="customize_widget_colors" value="1" {{_.checked(_.isTrue(customize_widget_colors), true)}}>

                <label for="wph-wizard-enable-widget-customize-colors"><?php _e( "Custom colors", Opt_In::TEXT_DOMAIN ); ?></label>

            </li>

        </ul>

    </div>

    <div id="wph-wizard-sshare-widget-customize-colors-options" class="wpmudev-box-palette wpmudev-box-gray {{ ( _.isFalse(customize_widget_colors) ) ? 'wpmudev-hidden' : '' }}">

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

                        <div class="wpmudev-picker" style="z-index: 3;"><input id="widget_icon_bg_color" class="wpmudev-color_picker" type="text"  value="{{widget_icon_bg_color}}" data-attribute="widget_icon_bg_color" data-alpha="true" /></div>

                    </div>

                <# } #>

            <# } #>

            <#
                var widget_icon_color_class = '';
                if ( icon_style !== 'flat' ) {
                    if ( (service_type === 'custom') || ( service_type === 'native' && _.isFalse(click_counter) ) ) {
                        widget_icon_color_class = 'col-xs-8 col-sm-12 col-lg-8';
                    } else {
                        widget_icon_color_class = 'col-xs-4 col-sm-12 col-lg-4';
                    }
                } else {
                    if ( service_type === 'native' && _.isTrue(click_counter) ) {
                        widget_icon_color_class = 'col-xs-4 col-sm-12 col-lg-4';
                    }
                }
            #>

            <div class="wpmudev-col col-12 {{widget_icon_color_class}}">

                <label><?php _e( "Icon color", Opt_In::TEXT_DOMAIN ); ?></label>

                <div class="wpmudev-picker" style="z-index: 2;"><input id="widget_icon_color" class="wpmudev-color_picker" type="text"  value="{{widget_icon_color}}" data-attribute="widget_icon_color" data-alpha="true" /></div>

            </div>

            <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

                <#
                    var widget_icon_border_class = '';
                    if ( icon_style !== 'flat' ) {
                        if ( ( icon_style !== 'outline' ) || ( icon_style === 'outline' && ( service_type === 'custom' ) ) || ( icon_style === 'outline' && ( service_type === 'native' && _.isFalse(click_counter) ) ) ) {
                            widget_icon_border_class = 'col-xs-4 col-sm-12 col-lg-4';
                        } else {
                            widget_icon_border_class = 'col-xs-8 col-sm-12 col-lg-8';
                        }
                    } else {
                        widget_icon_border_class = 'col-xs-8 col-sm-12 col-lg-8';
                    }
                #>

                <div class="wpmudev-col col-12 {{widget_icon_border_class}}">

                    <label><?php _e( "Counter border", Opt_In::TEXT_DOMAIN ); ?></label>

                    <div class="wpmudev-picker" style="z-index: 2;"><input id="widget_counter_border" class="wpmudev-color_picker" type="text"  value="{{widget_counter_border}}" data-attribute="widget_counter_border" data-alpha="true" /></div>

                </div>

            <# } #>

        </div>

    </div>

    <div class="wpmudev-row">

        <div class="wpmudev-col col-12 {{ ( service_type === 'native' && _.isTrue(click_counter) ) ? 'col-xs-6 col-sm-12 col-lg-6' : '' }}">

            <label><?php _e( "Container BG", Opt_In::TEXT_DOMAIN ); ?></label>

            <div class="wpmudev-picker" style="z-index: 2;"><input id="widget_bg_color" class="wpmudev-color_picker" type="text"  value="{{widget_bg_color}}" data-attribute="widget_bg_color" data-alpha="true" /></div>

        </div>

        <# if ( service_type === 'native' && _.isTrue(click_counter) ) { #>

            <div class="wpmudev-col col-12 col-xs-6 col-sm-12 col-lg-6">

                <label><?php _e( "Counter text", Opt_In::TEXT_DOMAIN ); ?></label>

                <div class="wpmudev-picker" style="z-index: 1;"><input id="widget_counter_color" class="wpmudev-color_picker" type="text"  value="{{widget_counter_color}}" data-attribute="widget_counter_color" data-alpha="true" /></div>

            </div>

        <# } #>

    </div>

</div>