<script id="wpoi-settings-display-triggers-tpl" type="text/template">

	<div class="tabs">

		<ul class="tabs-header">

			<li class="<# if( trigger === 'time' ){ #>current<# } #>">

				<label href="#wpoi-triggers-{{type}}-time" for="wpoi-{{type}}-appear_after_time">

					<input type="radio" class="wpoi-display-trigger-radio" name="wpoi-{{type}}-trigger" id="wpoi-{{type}}-trigger_time" data-attribute="trigger" value="time" {{_.checked(trigger, "time" )}} ><?php _e("Time", Opt_In::TEXT_DOMAIN) ?>

				</label>

			</li><!-- Time -->

			<li class="<# if( trigger === 'scrolled' || trigger === 'scroll' ){ #>current<# } #>" >

				<label href="#wpoi-triggers-{{type}}-scroll" for="wpoi-{{type}}-appear_after_scrolled">

					<input type="radio" name="wpoi-{{type}}-trigger" class="wpoi-display-trigger-radio" id="wpoi-{{type}}-trigger_scrolled"  data-attribute="trigger" value="scroll" {{_.checked(trigger, "scroll" )}} ><?php _e("Scroll", Opt_In::TEXT_DOMAIN) ?>

				</label>

			</li>

			<li class="<# if( trigger === 'click' ){ #>current<# } #>">

				<label href="#wpoi-triggers-{{type}}-click" for="wpoi-{{type}}-appear_after_click">

					<input type="radio" name="wpoi-{{type}}-trigger" class="wpoi-display-trigger-radio" id="wpoi-{{type}}-trigger_click" data-attribute="trigger" value="click" {{_.checked(trigger, "click" )}} ><?php _e("Click", Opt_In::TEXT_DOMAIN) ?>

				</label>

			</li>

			<li class="<# if( trigger === 'exit_intent' ){ #>current<# } #>">

				<label href="#wpoi-triggers-{{type}}-exit_intent" for="wpoi-{{type}}-appear_after_exit_intent">

					<input type="radio" name="wpoi-{{type}}-trigger" class="wpoi-display-trigger-radio" data-attribute="trigger" id="wpoi-{{type}}-trigger_exit_intent" value="exit_intent" {{_.checked(trigger, "exit_intent" )}} ><?php _e("Exit Intent", Opt_In::TEXT_DOMAIN) ?>

				</label>

			</li>

			<li class="<# if( trigger === 'adblock' ){ #>current<# } #>">

				<label href="#wpoi-triggers-{{type}}-adblock" for="wpoi-{{type}}-appear_after_adblock">

					<input type="radio" name="wpoi-{{type}}-trigger" class="wpoi-display-trigger-radio" data-attribute="trigger" id="wpoi-{{type}}-trigger_adblock" value="adblock" {{_.checked(trigger, "adblock" )}} ><?php _e("AdBlock Use", Opt_In::TEXT_DOMAIN) ?>

				</label>

			</li>

		</ul><!-- .tabs-header -->

		<div class="tabs-body">

			<div id="wpoi-triggers-{{type}}-time" class="tabs-content<# if( trigger === 'time' ){ #> current<# } #>">

				<div class="wph-label--radio">

					<label for="wpoi-{{type}}-trigger_on_time_immediately" class="wph-label--alt"><?php _e("Trigger immediately", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-trigger_on_time_immediately" value="immediately" name="wpoi-{{type}}-on_time" data-attribute="on_time" {{_.checked(on_time, "immediately" )}}>

						<label class="wpdui-fi wpdui-fi-check" for="wpoi-{{type}}-trigger_on_time_immediately"></label>

					</div>

				</div>

				<div class="wph-label--mix">

					<label for="wpoi-{{type}}-trigger_on_time_time" class="wph-label--alt"><?php _e("Trigger after", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-trigger_on_time_time" value="time" name="wpoi-{{type}}-on_time" data-attribute="on_time" {{_.checked(on_time, "time" )}}>

						<label class="wpdui-fi wpdui-fi-check" for="wpoi-{{type}}-trigger_on_time_time"></label>

					</div>

					<input class="wpmudev-input_number" type="number" min="0" step="1" value="{{on_time_delay}}" data-attribute="on_time_delay">

					<select data-attribute="on_time_unit" class="wpmuiSelect">

						<option {{_.selected(on_time_unit, "seconds")}} value="seconds"><?php _e("Seconds", Opt_In::TEXT_DOMAIN); ?></option>
						<option {{_.selected(on_time_unit, "minutes")}} value="minutes"><?php _e("Minutes", Opt_In::TEXT_DOMAIN) ?></option>
						<option {{_.selected(on_time_unit, "hours")}}  value="hours"><?php _e("Hours", Opt_In::TEXT_DOMAIN); ?></option>

					</select>

				</div>

			</div><!-- Time -->

			<div id="wpoi-triggers-{{type}}-scroll" class="tabs-content<# if( trigger === 'scrolled' || trigger === 'scroll' ){ #> current<# } #>">

				<div class="wph-label--mix">

					<label for="wpoi-{{type}}-appear-scrolled" class="wph-label--alt"><?php _e("Trigger after", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-appear-scrolled" value="scrolled" name="wpoi-{{type}}-appear" data-attribute="on_scroll" {{_.checked(on_scroll, "scrolled")}}>

						<label class="wpdui-fi wpdui-fi-check" for="wpoi-{{type}}-appear-scrolled"></label>

					</div>

					<input min="0" type="number" max="100" name="" value="{{on_scroll_page_percent}}"  data-attribute="on_scroll_page_percent" class="wpmudev-input_number">

					<label for="wpoi-{{type}}-appear-scrolled" class="wph-label--alt"><?php _e("% of the page has been scrolled", Opt_In::TEXT_DOMAIN); ?></label>

				</div>

				<div class="wph-label--radio">

					<label for="wpoi-{{type}}-appear-selector" class="wph-label--alt"><?php _e("Appear after user scrolled past a CSS selector", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-appear-selector" name="wpoi-{{type}}-appear" value="selector" data-attribute="on_scroll" {{_.checked(on_scroll, "selector")}}>

						<label class="wpdui-fi wpdui-fi-check" for="wpoi-{{type}}-appear-selector"></label>

					</div>

				</div>

				<input type="text" placeholder="only use .class or #ID selectors" value="{{on_scroll_css_selector}}" data-attribute="on_scroll_css_selector">

			</div><!-- Scroll -->

			<div id="wpoi-triggers-{{type}}-click" class="tabs-content<# if( trigger === 'click' ){ #> current<# } #>">

				<label for="wpoi-{{type}}-click-selector" class="wph-label--alt"><?php _e("Trigger after user clicks on existing element with this ID or Class", Opt_In::TEXT_DOMAIN); ?></label>

				<input type="text" id="wpoi-{{type}}-click-selector" value="{{on_click_element}}" data-attribute="on_click_element" placeholder="<?php esc_attr_e('only use .class or #ID selector', Opt_In::TEXT_DOMAIN); ?>">

			</div><!-- Click -->

			<div id="wpoi-triggers-{{type}}-exit_intent" class="tabs-content<# if( trigger === 'exit_intent' ){ #> current<# } #>">

				<div class="wph-label--toggle">

					<label for="wpoi-{{type}}-trigger-exit" class="wph-label--alt"><?php _e("Trigger when exit intent is detected", Opt_In::TEXT_DOMAIN); ?></label>

					<span class="toggle">

						<input id="wpoi-{{type}}-trigger-exit" class="toggle-checkbox" type="checkbox" data-attribute="on_exit_intent" value="1"  {{_.checked( _.isTrue( on_exit_intent ), true)}}  >
						<label class="toggle-label" for="wpoi-{{type}}-trigger-exit"></label>

					</span>

				</div>

				<div class="wph-label--toggle">

					<label for="wpoi-{{type}}-trigger-exit-once" class="wph-label--alt"><?php _e("Trigger once per session only", Opt_In::TEXT_DOMAIN); ?></label>

					<span class="toggle">

						<input id="wpoi-{{type}}-trigger-exit-once" class="toggle-checkbox" type="checkbox" data-attribute="on_exit_intent_per_session"  value="1" {{_.checked( _.isTrue( on_exit_intent_per_session ), true )}}  >
						<label class="toggle-label" for="wpoi-{{type}}-trigger-exit-once"></label>

					</span>

				</div>

			</div><!-- Exit Intent -->

			<div id="wpoi-triggers-{{type}}-adblock" class="tabs-content {{_.class( trigger === 'adblock' , 'current' )}}">

				<div class="wph-label--toggle">

					<label for="wpoi-{{type}}-trigger-on-adblock"><?php _e("Trigger when AdBlock is detected", Opt_In::TEXT_DOMAIN); ?></label>

					<span class="toggle">

						<input id="wpoi-{{type}}-trigger-on-adblock" class="toggle-checkbox" type="checkbox" data-attribute="on_adblock" value="1"  {{_.checked(on_adblock, true)}}  >
						<label class="toggle-label" for="wpoi-{{type}}-trigger-on-adblock"></label>

					</span>

				</div>

				<div class="wph-label--radio wpoi-popup-trigger-on-adblock-option" style="display: <# if( _.isTrue( on_adblock )  ){ #>block<# }else{ #>none<# } #>">

					<label for="wpoi-{{type}}-trigger-on-adblock-immediately"><?php _e("Trigger immediately", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-trigger-on-adblock-immediately" value="false" name="wpoi-{{type}}-trigger-on-adblock-delayed" data-attribute="on_adblock_delayed" {{_.checked(on_adblock_delayed, false )}}>

						<label for="wpoi-{{type}}-trigger-on-adblock-immediately" class="wpdui-fi wpdui-fi-check"></label>

					</div>

				</div>

				<div class="wph-label--mix wpoi-popup-trigger-on-adblock-option" style="display: <# if( _.isTrue( on_adblock )  ){ #>block<# }else{ #>none<# } #>">

					<label for="wpoi-{{type}}-trigger-on-adblock-delayed"><?php _e("Trigger after", Opt_In::TEXT_DOMAIN); ?></label>

					<div class="wph-input--radio">

						<input type="radio" id="wpoi-{{type}}-trigger-on-adblock-delayed" value="true" name="wpoi-{{type}}-trigger-on-adblock-delayed" data-attribute="on_adblock_delayed" {{_.checked(on_adblock_delayed, true )}}>

						<label class="wpdui-fi wpdui-fi-check" for="wpoi-{{type}}-trigger-on-adblock-delayed"></label>

					</div>

					<input min="0" type="number" name="" class="wpoi_trigger_on_adblock_timed_val" value="{{on_adblock_delayed_time}}"  data-attribute="on_adblock_delayed_time" class="wpmudev-input_number">

					<select data-attribute="on_adblock_delayed_unit" class="wpoi_trigger_on_adblock_delayed_unit wpmuiSelect">
						<option {{_.selected(on_adblock_delayed_unit, "seconds")}} value="seconds"><?php _e("Seconds", Opt_In::TEXT_DOMAIN); ?></option>
						<option {{_.selected(on_adblock_delayed_unit, "minutes")}} value="minutes"><?php _e("Minutes", Opt_In::TEXT_DOMAIN) ?></option>
						<option {{_.selected(on_adblock_delayed_unit, "hours")}}  value="hours"><?php _e("Hours", Opt_In::TEXT_DOMAIN); ?></option>
					</select>

				</div>

			</div><!-- AdBlock Use -->

		</div><!-- .tabs-body -->

	</div><!-- .tabs -->

</script>