<div id="wph-wizard-content-gdpr" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "GDPR Checkbox", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<div class="wpmudev-switch-labeled">

			<div class="wpmudev-switch">

				<input id="wph-popup-gdpr-button" class="toggle-checkbox" type="checkbox" data-attribute="show_gdpr" {{_.checked(_.isTrue(show_gdpr), true)}}>

				<label class="wpmudev-switch-design" for="wph-popup-gdpr-button" aria-hidden="true"></label>

			</div>

			<label class="wpmudev-switch-label" for="wph-popup-gdpr-button"><?php _e( "Show GDPR Checkbox", Opt_In::TEXT_DOMAIN ); ?></label>

		</div>

	</div>

</div>

<div id="wph-wizard-content-gdpr-message" class="wpmudev-box-content {{ ( _.isFalse(show_gdpr) ) ? 'wpmudev-hidden' : '' }}">

	<div class="wpmudev-box-right">

        <div id="wph-wizard-content-gdpr-options">

		<?php wp_editor( "{{gdpr_message}}", "gdpr_message", array(
			'textarea_name' => 'gdpr_message',
			'editor_css' => '<style>
				#wp-gdpr_message-editor-tools { margin-bottom: 5px; }
				#wp-gdpr_message-editor-tools .wp-media-buttons .insert-media {
					margin: 0 5px 0 0;
					border: 1px solid #E1E1E1;
					border-radius: 5px;
					-moz-border-radius: 5px;
					-webkit-border-radius: 5px;
					background: #FAFAFA;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					color: #6E6E6E;
					font: 500 13px/24px "Roboto", "Open Sans", Arial, sans-serif;
					text-align: center;
				}
				#wp-gdpr_message-editor-tools .wp-media-buttons .insert-media:hover,
				#wp-gdpr_message-editor-tools .wp-media-buttons .insert-media:focus {
					border-color: #E1E1E1;
					background: #EEE;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					color: #6E6E6E;
				}
				#wp-gdpr_message-editor-tools .wp-media-buttons .button:active {
					top: auto;
					margin-top: 0;
					margin-bottom: 0;
					border-color: #E1E1E1;
					background: #EEE;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					transform: translateY(0);
					-moz-transform: translateY(0);
					-webkit-transform: translateY(0);
					color: #333;
				}
				#wp-gdpr_message-editor-tools .wp-media-buttons span.wp-media-buttons-icon {
					width: 16px;
					height: 16px;
					position: relative;
					vertical-align: text-bottom;
					top: 1px;
					color: #6E6E6E;
				}
				#wp-gdpr_message-editor-tools .wp-media-buttons .add_media span.wp-media-buttons-icon:before {
					font-size: 14px;
				}
				#wp-gdpr_message-editor-tools .wp-media-buttons .button:active span.wp-media-buttons-icon,
				#wp-gdpr_message-editor-tools .wp-media-buttons .button:active span.wp-media-buttons-icon:before {
					color: #333;
				}
				#wp-gdpr_message-editor-tools .wp-switch-editor {
					width: 58px;
					height: auto;
					top: 0;
					margin: 0 0 0 5px;
					padding: 0 5px;
					border: 1px solid #E1E1E1;
					border-radius: 5px;
					-moz-border-radius: 5px;
					-webkit-border-radius: 5px;
					background: #FAFAFA;
					color: #6E6E6E;
					font: 500 13px/26px "Roboto", "Open Sans", Arial, sans-serif;
					text-align: center;
					transition: .2s ease-in;
					-moz-transition: .2s ease-in;
					-webkit-transition: .2s ease-in;
				}
				#wp-gdpr_message-editor-tools .wp-switch-editor:hover,
				#wp-gdpr_message-editor-tools .wp-switch-editor:focus {
					border-color: #E1E1E1;
					background: #EEE;
					color: #6E6E6E;
				}
				#wp-gdpr_message-editor-tools .wp-switch-editor:first-child {
					margin: 0;
				}
				.html-active #wp-gdpr_message-editor-tools .switch-html,
				.tmce-active #wp-gdpr_message-editor-tools .switch-tmce {
					border-color: #E1E1E1;
					border-bottom-color: #E1E1E1;
					background: #EEE;
					color: #333;
				}
				#wp-gdpr_message-editor-container {
					border: 1px solid #E1E1E1;
					border-radius: 5px;
					-moz-border-radius: 5px;
					-webkit-border-radius: 5px;
				}
				#wp-gdpr_message-editor-container div.mce-panel { border-radius: 5px; }
				#wp-gdpr_message-editor-container div.mce-toolbar-grp {
					border-bottom: 1px solid #E1E1E1;
					border-radius: 5px 5px 0 0;
					-moz-border-radius: 5px 5px 0 0;
					-webkit-border-radius: 5px 5px 0 0;
					background: #FAFAFA;
				}
				#wp-gdpr_message-editor-container .mce-toolbar .mce-ico {
					color: #888;
				}
				#wp-gdpr_message-editor-container .mce-toolbar .mce-btn-group .mce-btn.mce-listbox {
					border: 1px solid #DDD;
					border-radius: 3px;
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
					background: #FAFAFA;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
				}
				#wp-gdpr_message-editor-container .mce-toolbar .mce-btn-group .mce-btn:focus,
				#wp-gdpr_message-editor-container .mce-toolbar .mce-btn-group .mce-btn:hover,
				#wp-gdpr_message-editor-container .qt-dfw:focus, .qt-dfw:hover {
					border-color: #888;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					color: #888;
				}
				#wp-gdpr_message-editor-container .mce-toolbar .mce-btn-group .mce-menubtn.mce-fixed-width button {
					border-radius: 3px;
					-moz-border-radius: 3px;
					-webkit-border-radius: 3px;
				}
				#wp-gdpr_message-editor-container .mce-toolbar .mce-btn-group .mce-menubtn.mce-fixed-width span {
					color: #6E6E6E;
					font-family: "Roboto", Arial, sans-serif;
					font-weight: 500;
					letter-spacing: -0.22px;
				}
				#wp-gdpr_message-editor-container .mce-panel .mce-btn i.mce-caret {
					border-top-color: #888;
				}
				#wp-gdpr_message-editor-container .mce-panel .mce-active i.mce-caret {
					border-bottom-color: #888;
				}
				#wp-gdpr_message-editor-container div.mce-statusbar {
					border-top: 1px solid #E1E1E1;
					border-radius: 0 0 5px 5px;
					-moz-border-radius: 0 0 5px 5px;
					-webkit-border-radius: 0 0 5px 5px;
					background: #FAFAFA;
				}
				#wp-gdpr_message-editor-container .quicktags-toolbar {
					border-bottom: 1px solid #E1E1E1;
					border-radius: 5px 5px 0 0;
					-moz-border-radius: 5px 5px 0 0;
					-webkit-border-radius: 5px 5px 0 0;
					background: #FAFAFA;
				}
				#wp-gdpr_message-editor-container .quicktags-toolbar .button {
					border-color: #E1E1E1;
					background: #FAFAFA;
					box-shadow: none;
					-moz-box-shadow: none;
					-webkit-box-shadow: none;
					color: #6E6E6E;
					letter-spacing: -0.22px;
					font-family: "Roboto", "Open Sans", Arial;
					font-weight: 500;
				}
				#wp-gdpr_message-editor-container .quicktags-toolbar .button:hover,
				#wp-gdpr_message-editor-container .quicktags-toolbar .button:focus {
					border-color: #E1E1E1;
					background: #EEE;
					color: #6E6E6E;
				}
				#wp-gdpr_message-editor-container .quicktags-toolbar .button:active {
					border: 1px solid #E1E1E1;
					background: #EEE;
					transform: translateY(0);
					color: #333;
					font-family: "Roboto", "Open Sans", Arial;
					font-weight: 500;
				}
				#wp-gdpr_message-editor-container textarea.wp-editor-area {
					border-radius: 0 0 5px 5px;
					-moz-border-radius: 0 0 5px 5px;
					-webkit-border-radius: 0 0 5px 5px;
				}
			</style>',
			'editor_height' => 130,
			'drag_drop_upload' => true,
        ) ); ?>

        		<label class="wpmudev-helper"><?php _e( "This is a checkbox for agreeing to privacy policy/terms of use according to GDPR EU laws.", Opt_In::TEXT_DOMAIN ); ?></label>

            </div>

		</div>

	</div>

</div><?php // #wph-wizard-content-gdpr ?>