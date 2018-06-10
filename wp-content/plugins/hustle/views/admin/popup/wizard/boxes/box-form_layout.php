<div id="wph-wizard-content-form_layout" class="wpmudev-box-content">

	<div class="wpmudev-box-left">

		<h4><strong><?php _e( "Form layout", Opt_In::TEXT_DOMAIN ); ?></strong></h4>

	</div>

	<div class="wpmudev-box-right">

		<h4><?php _e( "Select form layout that is most suited for your opt-in", Opt_In::TEXT_DOMAIN ); ?></h4>

		<div class="wpmudev-box-layouts">

			<div class="wpmudev-box-layout_one {{ ( form_layout === 'one' ) ? 'active' : '' }}">

				<div class="wpmudev-box-layout_svg" for="wph-popup-layout_one">

					<svg xmlns="http://www.w3.org/2000/svg" width="60" height="36" viewBox="0 0 60 36" preserveAspectRatio="none">
						<g fill="none" fill-rule="evenodd">
							<rect width="58" height="34" x="1" y="1" stroke="#85929E" stroke-width="2" rx="3"/>
							<path fill="#85929E" fill-rule="nonzero" d="M13 12l4 5H9l4-5zm-3-1c-.552 0-1-.448-1-1s.448-1 1-1 1 .448 1 1-.448 1-1 1zM7 6.994v12.012c0-.004-.002-.006-.006-.006h12.012c-.004 0-.006.002-.006.006V6.994c0 .004.002.006.006.006H6.994C6.998 7 7 6.998 7 6.994zm-2 0C5 5.894 5.895 5 6.994 5h12.012C20.106 5 21 5.895 21 6.994v12.012c0 1.1-.895 1.994-1.994 1.994H6.994C5.894 21 5 20.105 5 19.006V6.994z"/>
							<path fill="#85929E" fill-opacity=".4" d="M23 7c0-.552.452-1 .993-1h30.014c.55 0 .993.444.993 1 0 .552-.452 1-.993 1H23.993C23.443 8 23 7.556 23 7zm0 4c0-.552.452-1 .993-1h30.014c.55 0 .993.444.993 1 0 .552-.452 1-.993 1H23.993c-.55 0-.993-.444-.993-1zm0 4c0-.552.452-1 .993-1h30.014c.55 0 .993.444.993 1 0 .552-.452 1-.993 1H23.993c-.55 0-.993-.444-.993-1zm0 4c0-.552.445-1 1-1h14c.552 0 1 .444 1 1 0 .552-.445 1-1 1H24c-.552 0-1-.444-1-1z"/>
							<g transform="translate(5 24)">
								<rect width="32" height="6" x=".5" y=".5" stroke="#2ECC71" rx="2"/>
								<rect width="14" height="7" x="36" fill="#2ECC71" rx="2"/>
							</g>
						</g>
					</svg>

				</div>

				<div class="wpmudev-input_radio">

                    <input type="radio" id="wph-popup-layout_one" name="form_layout" value="one" data-attribute="form_layout" {{_.checked( (form_layout === 'one') , true)}}>

                    <label for="wph-popup-layout_one" class="wpdui-fi wpdui-fi-check"></label>

                </div>

			</div><?php // .wpmudev-box-layout_one ?>

			<div class="wpmudev-box-layout_two {{ ( form_layout === 'two' ) ? 'active' : '' }}">

				<div class="wpmudev-box-layout_svg" for="wph-popup-layout_two">

					<svg xmlns="http://www.w3.org/2000/svg" width="60" height="36" viewBox="0 0 60 36" preserveAspectRatio="none">
						<g fill="none" fill-rule="evenodd">
							<path fill="#CED3D8" fill-opacity=".5" d="M38 2h21v32H38z"/>
							<rect width="58" height="34" x="1" y="1" stroke="#85929E" stroke-width="2" rx="3"/>
							<path fill="#85929E" fill-rule="nonzero" d="M46.754 19.193L50 14l5 8H41l4-5 1.754 2.193zM42 16c-.552 0-1-.448-1-1s.448-1 1-1 1 .448 1 1-.448 1-1 1z"/>
							<path fill="#85929E" fill-opacity=".4" d="M5 7c0-.552.44-1 .997-1h26.006c.55 0 .997.444.997 1 0 .552-.44 1-.997 1H5.997C5.447 8 5 7.556 5 7zm0 4c0-.552.44-1 .997-1h26.006c.55 0 .997.444.997 1 0 .552-.44 1-.997 1H5.997C5.447 12 5 11.556 5 11zm0 4c0-.552.44-1 .997-1h26.006c.55 0 .997.444.997 1 0 .552-.44 1-.997 1H5.997C5.447 16 5 15.556 5 15zm0 4c0-.552.445-1 1-1h14c.552 0 1 .444 1 1 0 .552-.445 1-1 1H6c-.552 0-1-.444-1-1z"/>
							<g transform="translate(5 24)">
								<rect width="16" height="6" x=".5" y=".5" stroke="#2ECC71" rx="2"/>
								<rect width="10" height="7" x="19" fill="#2ECC71" rx="2"/>
							</g>
						</g>
					</svg>

				</div>

				<div class="wpmudev-input_radio">

                    <input type="radio" id="wph-popup-layout_two" name="form_layout" value="two" data-attribute="form_layout" {{_.checked( (form_layout === 'two') , true)}}>

                    <label for="wph-popup-layout_two" class="wpdui-fi wpdui-fi-check"></label>

                </div>

			</div><?php // .wpmudev-box-layout_two ?>

			<div class="wpmudev-box-layout_three {{ ( form_layout === 'three' ) ? 'active' : '' }}">

				<div class="wpmudev-box-layout_svg" for="wph-popup-layout_three">

					<svg xmlns="http://www.w3.org/2000/svg" width="60" height="36" viewBox="0 0 60 36" preserveAspectRatio="none">
						<g fill="none" fill-rule="evenodd">
							<rect width="58" height="34" x="1" y="1" stroke="#85929E" stroke-width="2" rx="3"/>
							<path fill="#CED3D8" fill-opacity=".5" d="M34 2h24v32H34z"/>
							<path fill="#85929E" fill-rule="nonzero" d="M18 12l4 5h-8l4-5zm-3-1c-.552 0-1-.448-1-1s.448-1 1-1 1 .448 1 1-.448 1-1 1zM7.005 6.994v12.012c0-.004-.003-.006-.01-.006H29c-.008 0-.01.002-.01.006V6.994c0 .004.002.006.01.006H6.994c.008 0 .01-.002.01-.006zM6.995 5h22.01C30.108 5 31 5.895 31 6.994v12.012c0 1.1-.895 1.994-1.994 1.994H6.994C5.894 21 5 20.105 5 19.006V6.994C5 5.894 5.895 5 6.994 5z"/>
							<path fill="#85929E" fill-opacity=".4" d="M5 26c0-.552.45-1 1.003-1h23.994c.554 0 1.003.444 1.003 1 0 .552-.45 1-1.003 1H6.003C5.45 27 5 26.556 5 26zm0 4c0-.552.455-1 .992-1h18.016c.548 0 .992.444.992 1 0 .552-.455 1-.992 1H5.992C5.444 31 5 30.556 5 30z"/>
							<g transform="translate(37 13.5)">
								<rect width="17" height="3" x=".5" y=".5" stroke="#2ECC71" rx="1.5"/>
								<rect width="18" height="4" y="5" fill="#2ECC71" rx="2"/>
							</g>
						</g>
					</svg>

				</div>

				<div class="wpmudev-input_radio">

                    <input type="radio" id="wph-popup-layout_three" name="form_layout" value="three" data-attribute="form_layout" {{_.checked( (form_layout === 'three') , true)}}>

                    <label for="wph-popup-layout_three" class="wpdui-fi wpdui-fi-check"></label>

                </div>

			</div><?php // .wpmudev-box-layout_three ?>

			<div class="wpmudev-box-layout_four {{ ( form_layout === 'four' ) ? 'active' : '' }}">

				<div class="wpmudev-box-layout_svg" for="wph-popup-layout_four">

					<svg xmlns="http://www.w3.org/2000/svg" width="60" height="36" viewBox="0 0 60 36" preserveAspectRatio="none">
						<g fill="none" fill-rule="evenodd">
							<rect width="58" height="34" x="1" y="1" stroke="#85929E" stroke-width="2" rx="3"/>
							<path fill="#CED3D8" fill-opacity=".5" d="M34 2h24v32H34z"/>
							<path fill="#85929E" fill-rule="nonzero" d="M46 11l4 5h-8l4-5zm-3 0c-.552 0-1-.448-1-1s.448-1 1-1 1 .448 1 1-.448 1-1 1zm-3-4.006V18c0-.004-.002-.005-.006-.005h12.012c-.004 0-.006 0-.006.005V6.994c0 .004.002.006.006.006H39.994c.004 0 .006-.002.006-.006zM39.994 5h12.012C53.106 5 54 5.84 54 6.87v11.26c0 1.033-.895 1.87-1.994 1.87H39.994c-1.1 0-1.994-.84-1.994-1.87V6.87C38 5.836 38.895 5 39.994 5z"/>
							<path fill="#85929E" fill-opacity=".4" d="M5 20c0-.552.45-1 1.003-1h23.994c.554 0 1.003.444 1.003 1 0 .552-.45 1-1.003 1H6.003C5.45 21 5 20.556 5 20zm0-4c0-.552.45-1 1.003-1h23.994c.554 0 1.003.444 1.003 1 0 .552-.45 1-1.003 1H6.003C5.45 17 5 16.556 5 16zm0-4c0-.552.45-1 1.003-1h23.994c.554 0 1.003.444 1.003 1 0 .552-.45 1-1.003 1H6.003C5.45 13 5 12.556 5 12zm0 12c0-.552.455-1 .992-1h18.016c.548 0 .992.444.992 1 0 .552-.455 1-.992 1H5.992C5.444 25 5 24.556 5 24z"/>
							<g transform="translate(37 22)">
								<rect width="17" height="3" x=".5" y=".5" stroke="#2ECC71" rx="1.5"/>
								<rect width="18" height="4" y="5" fill="#2ECC71" rx="2"/>
							</g>
						</g>
					</svg>

				</div>

				<div class="wpmudev-input_radio">

                    <input type="radio" id="wph-popup-layout_four" name="form_layout" value="four" data-attribute="form_layout" {{_.checked( (form_layout === 'four') , true)}}>

                    <label for="wph-popup-layout_four" class="wpdui-fi wpdui-fi-check"></label>

                </div>

			</div><?php // .wpmudev-box-layout_four ?>

		</div>

	</div>

</div><?php // #wph-wizard-content-form_layout ?>