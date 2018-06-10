<div id="wph-wizard-services-icons-native" class="wpmudev-box-content last wph-wizard-services-icons-native {{ ( service_type === 'custom' ) ? 'wpmudev-hidden' : '' }}">

    <div class="wpmudev-box-right">

        <h4><strong><?php _e( "Pick social icons & set their default counter values", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <div class="wpmudev-social wpmudev-social-native">

            <div class="wpmudev-social-item {{ ( typeof social_icons.facebook == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-facebook-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="facebook" {{ ( typeof social_icons.facebook == 'undefined' ) ? '' : _.checked(social_icons.facebook.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-facebook-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-facebook">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/facebook"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.facebook === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.facebook == 'undefined' ) ? '0' : social_icons.facebook.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.twitter == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-twitter-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="twitter" {{ ( typeof social_icons.twitter == 'undefined' ) ? '' : _.checked(social_icons.twitter.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-twitter-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-twitter">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/twitter"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.twitter === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.twitter == 'undefined' ) ? '0' : social_icons.twitter.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.google == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-google-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="google" {{ ( typeof social_icons.google == 'undefined' ) ? '' : _.checked(social_icons.google.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-google-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-google">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/google"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.google === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.google == 'undefined' ) ? '0' : social_icons.google.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.pinterest == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-pinterest-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="pinterest" {{ ( typeof social_icons.pinterest == 'undefined' ) ? '' : _.checked(social_icons.pinterest.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-pinterest-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-pinterest">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/pinterest"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.pinterest === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.pinterest == 'undefined' ) ? '0' : social_icons.pinterest.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.reddit == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-reddit-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="reddit" {{ ( typeof social_icons.reddit == 'undefined' ) ? '' : _.checked(social_icons.reddit.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-reddit-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-reddit">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/reddit"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.reddit === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.reddit == 'undefined' ) ? '0' : social_icons.reddit.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.linkedin == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-linkedin-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="linkedin" {{ ( typeof social_icons.linkedin == 'undefined' ) ? '' : _.checked(social_icons.linkedin.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-linkedin-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-linkedin">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/linkedin"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.linkedin === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.linkedin == 'undefined' ) ? '0' : social_icons.linkedin.counter }}" >

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.vkontakte == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-vkontakte-native" class="toggle-checkbox wpmudev-social-item-native-enable" type="checkbox" data-id="vkontakte" {{ ( typeof social_icons.vkontakte == 'undefined' ) ? '' : _.checked(social_icons.vkontakte.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-vkontakte-native" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-vkontakte">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/vkontakte"); ?></div>

                </div>

                <input min="0" class="wpmudev-input_number {{ ( typeof social_icons.vkontakte === 'undefined' || click_counter === '0' ) ? 'wpmudev-hidden' : '' }}" type="number" value="{{ ( typeof social_icons.vkontakte == 'undefined' ) ? '0' : social_icons.vkontakte.counter }}" >

            </div>

        </div>

    </div>

</div><?php // #wph-wizard-services-icons ?>