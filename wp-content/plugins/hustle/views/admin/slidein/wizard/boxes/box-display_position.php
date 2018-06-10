<?php
$can_scroll = false;
$can_close = false;
?>

<div id="wph-wizard-settings-display_position" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Slide-in position", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<label><?php _e( "Pick position for Slide-in to appear", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wpmudev-browser">

			<div class="wpmudev-browser-header">

				<div class="wpmudev-browser-button"></div>

				<div class="wpmudev-browser-button"></div>

				<div class="wpmudev-browser-button"></div>

			</div>

			<div class="wpmudev-browser-section">

				<div class="wpmudev-browser-row">

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="nw" type="radio" id="wph-slidein-browser-position-nw" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'nw'), true)}}>

							<label for="wph-slidein-browser-position-nw" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="n" type="radio" id="wph-slidein-browser-position-n" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'n'), true)}}>

							<label for="wph-slidein-browser-position-n" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="ne" type="radio" id="wph-slidein-browser-position-ne" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'ne'), true)}}>

							<label for="wph-slidein-browser-position-ne" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

				</div>

				<div class="wpmudev-browser-row">

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="w" type="radio" id="wph-slidein-browser-position-w" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'w'), true)}}>

							<label for="wph-slidein-browser-position-w" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="e" type="radio" id="wph-slidein-browser-position-e" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'e'), true)}}>

							<label for="wph-slidein-browser-position-e" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

				</div>

				<div class="wpmudev-browser-row">

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="sw" type="radio" id="wph-slidein-browser-position-sw" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'sw'), true)}}>

							<label for="wph-slidein-browser-position-sw" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="s" type="radio" id="wph-slidein-browser-position-s" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 's'), true)}}>

							<label for="wph-slidein-browser-position-s" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

					<div class="wpmudev-browser-button">

						<div class="wpmudev-input_radio">

							<input value="se" type="radio" id="wph-slidein-browser-position-se" name="display_position" data-attribute="display_position" {{_.checked(_.isTrue(display_position === 'se'), true)}}>

							<label for="wph-slidein-browser-position-se" class="wpdui-fi wpdui-fi-check"></label>

						</div>

					</div>

				</div>

			</div>

		</div>

    </div>

</div><?php // #wph-wizard-settings-display_position ?>