<div id="wph-wizard-services-icons-custom" class="wpmudev-box-content last wph-wizard-services-icons-custom {{ ( service_type === 'native' ) ? 'wpmudev-hidden' : '' }}">

    <div class="wpmudev-box-right">

        <h4><strong><?php _e( "Pick social icons & set URLs for them", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

        <div class="wpmudev-social wpmudev-social-custom">

            <div class="wpmudev-social-item {{ ( typeof social_icons.facebook == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-facebook-custom" class="toggle-checkbox" type="checkbox" data-id="facebook" {{ ( typeof social_icons.facebook == 'undefined' ) ? '' : _.checked(social_icons.facebook.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-facebook-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-facebook">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/facebook"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.facebook === 'undefined' ? '' : social_icons.facebook.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.twitter == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-twitter-custom" class="toggle-checkbox" type="checkbox" data-id="twitter" {{ ( typeof social_icons.twitter == 'undefined' ) ? '' : _.checked(social_icons.twitter.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-twitter-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-twitter">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/twitter"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.twitter === 'undefined' ? '' : social_icons.twitter.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.google == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-google-custom" class="toggle-checkbox" type="checkbox" data-id="google" {{ ( typeof social_icons.google == 'undefined' ) ? '' : _.checked(social_icons.google.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-google-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-google">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/google"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.google === 'undefined' ? '' : social_icons.google.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.pinterest == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-pinterest-custom" class="toggle-checkbox" type="checkbox" data-id="pinterest" {{ ( typeof social_icons.pinterest == 'undefined' ) ? '' : _.checked(social_icons.pinterest.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-pinterest-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-pinterest">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/pinterest"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.pinterest === 'undefined' ? '' : social_icons.pinterest.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.reddit == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-reddit-custom" class="toggle-checkbox" type="checkbox" data-id="reddit" {{ ( typeof social_icons.reddit == 'undefined' ) ? '' : _.checked(social_icons.reddit.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-reddit-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-reddit">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/reddit"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.reddit === 'undefined' ? '' : social_icons.reddit.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.linkedin == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-linkedin-custom" class="toggle-checkbox" type="checkbox" data-id="linkedin" {{ ( typeof social_icons.linkedin == 'undefined' ) ? '' : _.checked(social_icons.linkedin.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-linkedin-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-linkedin">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/linkedin"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.linkedin === 'undefined' ? '' : social_icons.linkedin.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.vkontakte == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-vkontakte-custom" class="toggle-checkbox" type="checkbox" data-id="vkontakte" {{ ( typeof social_icons.vkontakte == 'undefined' ) ? '' : _.checked(social_icons.vkontakte.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-vkontakte-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-vkontakte">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/vkontakte"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.vkontakte === 'undefined' ? '' : social_icons.vkontakte.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.fivehundredpx == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-fivehundredpx-custom" class="toggle-checkbox" type="checkbox" data-id="fivehundredpx" {{ ( typeof social_icons.fivehundredpx == 'undefined' ) ? '' : _.checked(social_icons.fivehundredpx.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-fivehundredpx-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-fivehundredpx">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/fivehundredpx"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.fivehundredpx === 'undefined' ? '' : social_icons.fivehundredpx.link }}">

            </div>

			<div class="wpmudev-social-item {{ ( typeof social_icons.houzz == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-houzz-custom" class="toggle-checkbox" type="checkbox" data-id="houzz" {{ ( typeof social_icons.houzz == 'undefined' ) ? '' : _.checked(social_icons.houzz.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-houzz-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-houzz">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/houzz"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.houzz === 'undefined' ? '' : social_icons.houzz.link }}">

            </div>

            <div class="wpmudev-social-item {{ ( typeof social_icons.instagram == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-instagram-custom" class="toggle-checkbox" type="checkbox" data-id="instagram" {{ ( typeof social_icons.instagram == 'undefined' ) ? '' : _.checked(social_icons.instagram.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-instagram-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-instagram">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/instagram"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.instagram === 'undefined' ? '' : social_icons.instagram.link }}">

            </div>

			<div class="wpmudev-social-item {{ ( typeof social_icons.twitch == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-twitch-custom" class="toggle-checkbox" type="checkbox" data-id="twitch" {{ ( typeof social_icons.twitch == 'undefined' ) ? '' : _.checked(social_icons.twitch.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-twitch-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-twitch">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/twitch"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.twitch === 'undefined' ? '' : social_icons.twitch.link }}">

            </div>

			<div class="wpmudev-social-item {{ ( typeof social_icons.youtube == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-youtube-custom" class="toggle-checkbox" type="checkbox" data-id="youtube" {{ ( typeof social_icons.youtube == 'undefined' ) ? '' : _.checked(social_icons.youtube.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-youtube-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-youtube">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/youtube"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.youtube === 'undefined' ? '' : social_icons.youtube.link }}">

            </div>

			<div class="wpmudev-social-item {{ ( typeof social_icons.telegram == 'undefined' ) ? 'disabled' : '' }}">

                <div class="wpmudev-switch">

                    <input id="wph-sshares-telegram-custom" class="toggle-checkbox" type="checkbox" data-id="telegram" {{ ( typeof social_icons.telegram == 'undefined' ) ? '' : _.checked(social_icons.telegram.enabled, 'true') }}>

                    <label class="wpmudev-switch-design" for="wph-sshares-telegram-custom" aria-hidden="true"></label>

                </div>

                <div class="hustle-social-icon hustle-icon-rounded hustle-icon-telegram">

                    <div class="hustle-icon-container"><?php $this->render("general/icons/social/telegram"); ?></div>

                </div>

                <input class="wpmudev-input_text" type="text" placeholder="<?php _e( "Type URL here", Opt_In::TEXT_DOMAIN ); ?>" value="{{ typeof social_icons.telegram === 'undefined' ? '' : social_icons.telegram.link }}">

            </div>

        </div>

    </div>

</div><?php // #wph-wizard-services-icons ?>