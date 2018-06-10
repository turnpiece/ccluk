<?php
$close_icon = '<svg width="150" height="150" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="hustle-icon hustle-i_close"><path d="M91.667 75L150 16.667 133.333 0 75 58.333 16.667 0 0 16.667 58.333 75 0 133.333 16.667 150 75 91.667 133.333 150 150 133.333 91.667 75z" fill-rule="evenodd"/></svg>';

?>

<script id="wpmudev-hustle-modal-with-optin-tpl" type="text/template">

	<#
	var have_mc_group = !_.isEmpty( content.args ) && typeof content.args.group === 'object';
	if ( typeof content.form_elements !== 'object' && content.form_elements !== '') {
		content.form_elements = JSON.parse(content.form_elements);
	}
	#>

    <div class="hustle-modal hustle-modal-{{design.form_layout}} {{ ( (settings.animation_in !== '' && settings.animation_in !== 'no_animation') || (settings.animation_out !== '' && settings.animation_out !== 'no_animation') ) ? 'hustle-animated' : 'hustle-modal-static' }}">

        <div class="hustle-modal-close"><?php echo $close_icon; ?></div>

        <# if (content.after_successful_submission === "show_success") { #>

            <?php $this->render( "general/modals/optin-success", array() ); ?>

        <# } #>

        <div class="hustle-modal-body<# if ( ( design.form_layout === 'two' || design.form_layout === 'three' ) && design.feature_image_position !== 'left' ) { #> hustle-modal-image_right<# } #><# if ( design.form_layout === 'four' && design.feature_image_position !== 'left' ) { #> hustle-modal-image_right<# } #>">

            <# if ( design.form_layout === "four" ) { #>

                <aside>

            <# } #>

                <# if (
                    ( design.form_layout === "two" && _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ||
                    ( design.form_layout === "four" && _.isTrue(content.use_feature_image) && content.feature_image !== '' )
                ) { #>

                    <div class="hustle-modal-image hustle-modal-image_{{design.feature_image_fit}}<# if ( _.isTrue(content.feature_image_hide_on_mobile) ) { #> hustle-modal-mobile_hidden<# } #>">

                        <img src="{{content.feature_image}}"<# if (design.feature_image_fit === "contain" || design.feature_image_fit === "cover") { if ( design.feature_image_horizontal !== "custom" || design.feature_image_vertical !== "custom" ) { #> class="hustle-modal-image_{{design.feature_image_horizontal}}{{design.feature_image_vertical}}"<# } } #>>

                    </div>

                <# } #>

                <# if ( design.form_layout === "four" ) { #>

                    <div class="hustle-modal-optin_wrap">

                        <form class="hustle-modal-optin_form {{ ( _.isTrue(have_mc_group) ) ? 'hustle-modal-optin_groups' : '' }} {{ ( design.form_fields_proximity === 'separated' ) ? 'hustle-modal-optin_separated' : '' }}" role="form">

							<# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-optin_group">

							<# } #>

                                <# if ( typeof content.form_elements !== 'undefined' && !_.isEmpty( content.form_elements ) ) { #>
									<# _.each( content.form_elements, function( element, key ) {
										var element_type = element.type.toLowerCase();
										if ( element_type === 'name' || element_type === 'address' || element_type === 'phone' ) {
											var input_type = 'text';
										} else {
											var input_type = element_type;
										}
									#>
										<# if ( key !== 'submit' ) { #>
											<div class="hustle-modal-optin_field {{ ( design.form_fields_icon !== 'none' ) ? 'hustle-modal-field_with_icon' : '' }}">
                                                <input name="{{key}}" class="{{ ( _.isTrue( element.required ) ) ? 'required' : '' }}" type="{{input_type}}" data-error="Please, provide {{element.label.toLowerCase()}}.">
                                                <label>
                                                    <# if ( design.form_fields_icon !== "none" ) { #>
                                                        <span class="hustle-modal-optin_icon {{ ( design.form_fields_icon === 'animated' ) ? 'hustle-modal-optin_animated' : '' }}">
                                                            <# if ( element_type === 'email'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11" viewBox="0 0 14 11" preserveAspectRatio="none" class="hustle-icon hustle-i_email"><path fill-rule="evenodd" d="M.206 1.112L7 7l6.793-5.887c.132.266.207.564.207.88v7.015c0 1.1-.897 1.992-2.006 1.992H2.006C.898 11 0 10.1 0 9.008V1.992c0-.316.074-.615.206-.88zM.94.305C1.247.112 1.613 0 2.005 0h9.988c.392 0 .757.112 1.066.306L7 5.5.94.305z"/></svg>
                                                            <# } #>
                                                            <# if ( element_type === 'name'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="14" viewBox="0 0 11 14" preserveAspectRatio="none" class="hustle-icon hustle-i_user"><path fill-rule="evenodd" d="M1.632 6.785c.917 1.118 2.31 1.83 3.868 1.83 1.56 0 2.95-.712 3.868-1.83C10.376 7.533 11 8.787 11 10.8c0 2-2.75 2.5-5.5 2.5S0 12.8 0 10.8c0-2.013.624-3.267 1.632-4.015zM5.5 7C3.567 7 2 5.433 2 3.5S3.567 0 5.5 0 9 1.567 9 3.5 7.433 7 5.5 7z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'address'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="15" viewBox="0 0 10 15" preserveAspectRatio="none" class="hustle-icon hustle-i_pin"><path fill-rule="evenodd" d="M5 0c2.442 0 5 1 5 4.5S6.178 12.904 5 15C3.805 12.904 0 8 0 4.5S2.54 0 5 0zm0 6.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'phone'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" preserveAspectRatio="none" class="hustle-icon hustle-i_phone"><path fill-rule="evenodd" d="M9.947 13.855s-2.94-1.157-5.795-4.01C1.3 6.99.14 4.046.14 4.046c-.28-.605-.125-1.48.347-1.953L2.38.204c.314-.316.746-.258.964.13l1.63 2.91c.218.39.14.96-.177 1.276l-.614.613s.903 1.495 2.044 2.637c1.142 1.14 2.58 1.986 2.58 1.986l.613-.613c.316-.316.886-.394 1.274-.174l2.968 1.68c.388.22.448.652.132.968l-1.892 1.89c-.473.475-1.35.638-1.955.347z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'text'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" preserveAspectRatio="none" class="hustle-icon hustle-i_text"><path fill-rule="evenodd" d="M7 10.928v-9.25h3l1 2.25h1l-.188-3.01c-.034-.547-.5-.955-1.062-.915 0 0-1.875.175-4.75.175S1.25.003 1.25.003C.698-.04.222.37.188.917L0 3.927h1l1-2.25h3v9.25l-2 1v1l3-.25 3 .25v-1l-2-1z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'number'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" preserveAspectRatio="none" class="hustle-icon hustle-i_number"><path fill-rule="evenodd" d="M3 8V5H1c-.553 0-1-.444-1-1 0-.552.447-1 1-1h2V1c0-.553.444-1 1-1 .552 0 1 .447 1 1v2h3V1c0-.553.444-1 1-1 .552 0 1 .447 1 1v2h2c.553 0 1 .444 1 1 0 .552-.447 1-1 1h-2v3h2c.553 0 1 .444 1 1 0 .552-.447 1-1 1h-2v2c0 .553-.444 1-1 1-.552 0-1-.447-1-1v-2H5v2c0 .553-.444 1-1 1-.552 0-1-.447-1-1v-2H1c-.553 0-1-.444-1-1 0-.552.447-1 1-1h2zm2 0h3V5H5v3z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'url'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13" preserveAspectRatio="none" class="hustle-icon hustle-i_url"><path fill-rule="evenodd" d="M0 2.003C0 .897.895 0 1.994 0h12.012C15.106 0 16 .894 16 2.003v8.994C16 12.103 15.105 13 14.006 13H1.994C.894 13 0 12.106 0 10.997V2.003zm1 0v8.994c0 .557.445 1.003.994 1.003h12.012c.547 0 .994-.45.994-1.003V2.003C15 1.446 14.555 1 14.006 1H1.994C1.447 1 1 1.45 1 2.003zm7 5.33V9H7V4h2c.557 0 1 .447 1 1v1c0 .557-.447 1-1 1h-.2L10 9H9L8 7.333zM8 5h.495c.28 0 .505.232.505.5 0 .276-.214.5-.505.5H8V5zM4.5 8c.268 0 .5-.22.5-.49V4h1v3.5C6 8.326 5.328 9 4.5 9 3.666 9 3 8.328 3 7.5V4h1v3.51c0 .275.224.49.5.49zM12 8h1v1h-1c-.556 0-1-.448-1-1V4h1v4z"/></svg>
                                                            <# } #>
															<# if( _.isTrue( element.required ) ) { #>
															<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" preserveAspectRatio="none" class="hustle-icon hustle-i_warning" style="display:none"><path fill-rule="evenodd" d="M9 18c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9zm.25-3c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25S8 13.06 8 13.75 8.56 15 9.25 15zm-.018-4C8 11 7 3.5 9.232 3.5s1.232 7.5 0 7.5z"/></svg>
															<# } #>
                                                        </span>
                                                    <# } #>
                                                    <span class="hustle-modal-optin_placeholder">{{element.placeholder}}</span>
                                                </label>
											</div>
										<# } #>
									<# }); #>
								<# } #>

                            <# if ( _.isTrue( have_mc_group ) ) { #>

                                </div>

                            <# } #>

                            <# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-mc_title">

                                    <label>{{content.args.group.title}}</label>

                                </div>

                            <# } #>

                            <# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-optin_group">

                            <# } #>

								<# if ( _.isTrue( have_mc_group ) ) { #>

									<div class="hustle-modal-mc_groups hustle-modal-provider-args-container"></div>

								<# } #>

                                <# if ( typeof content.form_elements.submit !== 'undefined' && typeof content.form_elements.submit.label !== 'undefined' ) { #>

									<div class="hustle-modal-optin_button">

										<button type="submit">{{ content.form_elements.submit.label }}</button>

									</div>

								<# } #>

                            <# if ( _.isTrue( have_mc_group ) ) { #>

                                </div>

                            <# } #>

                        </form>

                    </div>

                <# } #>

            <# if ( design.form_layout === "four" ) { #>

                </aside>

            <# } #>

            <# if ( design.form_layout === "two" ) { #>

                <div class="hustle-modal-content">

            <# } #>

                <# if (
                    ( design.form_layout === "one" && (
                        ( _.isTrue(content.has_title) && (
                            content.title !== '' ||
                            content.sub_title !== ''
                        ) ) ||
                        content.main_content !== '' ||
                        ( _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ||
                        ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) )
                    ) ) ||
                    ( design.form_layout === "two" && (
                        ( _.isTrue(content.has_title) && (
                            content.title !== '' ||
                            content.sub_title !== ''
                        ) ) ||
                        content.main_content !== '' ||
                        ( _.isTrue(content.show_cta) && (
                            content.cta_label !== '' &&
                            content.cta_url !== ''
                        ) )
                    ) ) ||
                    ( design.form_layout === "three" && (
                        ( _.isTrue(content.has_title) && (
                            content.title !== '' ||
                            content.sub_title !== '' )
                        ) ||
                        content.main_content !== '' ||
                        ( _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ||
                        ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) )
                    ) ) ||
                    ( design.form_layout === "four" && (
                        ( _.isTrue(content.has_title) && (
                            content.title !== '' ||
                            content.sub_title !== '' )
                        ) ||
                        content.main_content !== '' ||
                        ( _.isTrue(content.show_cta) && (
                            content.cta_label !== '' &&
                            content.cta_url !== ''
                        ) )
                    ) )
                ) { #>

                <section class="<# if ( design.form_layout === 'one' && design.feature_image_position !== 'left' ) { if (design.feature_image_position === 'right') { #> hustle-modal-image_right<# } else if ( design.feature_image_position === 'above' ) { #> hustle-modal-image_above<# } else if ( design.feature_image_position === 'below' ) { #> hustle-modal-image_below<# } } #>">

                    <# if (
                        ( design.form_layout === "one" && _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ||
                        ( design.form_layout === "three" && _.isTrue(content.use_feature_image) && content.feature_image !== '' )
                    ) { #>

                        <div class="hustle-modal-image hustle-modal-image_{{design.feature_image_fit}}<# if ( _.isTrue(content.feature_image_hide_on_mobile) ) { #> hustle-modal-mobile_hidden<# } #>">

                            <img src="{{content.feature_image}}"<# if (design.feature_image_fit === "contain" || design.feature_image_fit === "cover") { if ( design.feature_image_horizontal !== "custom" || design.feature_image_vertical !== "custom" ) { #> class="hustle-modal-image_{{design.feature_image_horizontal}}{{design.feature_image_vertical}}"<# } } #>>

                        </div>

                    <# } #>

                    <# if (
                        ( _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) ||
						content.main_content !== '' ||
						( _.isTrue(content.show_gdpr) && content.show_gdpr !== '' ) ||
                        ( _.isTrue(content.show_cta) && content.cta_label !== '' )
                     ) { #>

                        <article>

                            <div class="hustle-modal-article">

                                <# if ( _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) { #>

                                    <hgroup>

                                        <# if ( content.title !== '' ) { #>
                                            <h1 class="hustle-modal-title">{{content.title}}</h1>
                                        <# } #>

                                        <# if ( content.sub_title !== '' ) { #>
                                            <h2 class="hustle-modal-subtitle">{{content.sub_title}}</h2>
                                        <# } #>

                                    </hgroup>

                                <# } #>

								{{{content.main_content}}}

								<# if ( _.isTrue(content.show_gdpr) && content.show_gdpr !== '' ) { #>
									<div class="hustle-gdpr-box">
										<label for="hustle-modal-gdpr" class="hustle-gdpr-checkbox">
											<input type="checkbox" id="hustle-modal-gdpr" class="hustle-modal-gdpr">
											<span aria-hidden="true"></span>
										</label>
										<div for="hustle-modal-gdpr" class="hustle-gdpr-content">{{{content.gdpr_message}}}</div>
									</div>
								<# } #>

                            	<# if ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) ) { #>

                                    <div class="hustle-modal-footer">

                            			<a target="_{{content.cta_target}}" href="{{content.cta_url}}" class="hustle-modal-cta">{{content.cta_label}}</a>

                                    </div>

                                <# } #>

                            </div>

                        </article>

                    <# } #>

                </section>

                <# } #>

                <# if ( design.form_layout !== "four" ) { #>

                    <# if ( design.form_layout === "three" ) { #>

                        <div class="hustle-modal-optin_wrap">

                    <# } else { #>

                        <footer>

                    <# } #>

                        <form class="hustle-modal-optin_form {{ ( _.isTrue(have_mc_group) ) ? 'hustle-modal-optin_groups' : '' }} {{ ( design.form_fields_proximity === 'separated' ) ? 'hustle-modal-optin_separated' : '' }}" role="form">

							<# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-optin_group">

                            <# } #>

                                <# if ( typeof content.form_elements !== 'undefined' && !_.isEmpty( content.form_elements ) ) { #>
									<# _.each( content.form_elements, function( element, key ) {
										var element_type = element.type.toLowerCase();
										if ( element_type === 'name' || element_type === 'address' || element_type === 'phone' ) {
											var input_type = 'text';
										} else {
											var input_type = element_type;
										}
									#>
										<# if ( key !== 'submit' ) { #>
											<div class="hustle-modal-optin_field {{ ( design.form_fields_icon !== 'none' ) ? 'hustle-modal-field_with_icon' : '' }}">
                                                <input name="{{key}}" class="{{ ( _.isTrue( element.required ) ) ? 'required' : '' }}" type="{{input_type}}" data-error="Please, provide {{element.label.toLowerCase()}}.">
                                                <label>
                                                    <# if ( design.form_fields_icon !== "none" ) { #>
                                                        <span class="hustle-modal-optin_icon{{ ( design.form_fields_icon === 'animated' ) ? ' hustle-modal-optin_animated' : '' }}">
                                                            <# if ( element_type === 'email'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11" viewBox="0 0 14 11" preserveAspectRatio="none" class="hustle-icon hustle-i_email"><path fill-rule="evenodd" d="M.206 1.112L7 7l6.793-5.887c.132.266.207.564.207.88v7.015c0 1.1-.897 1.992-2.006 1.992H2.006C.898 11 0 10.1 0 9.008V1.992c0-.316.074-.615.206-.88zM.94.305C1.247.112 1.613 0 2.005 0h9.988c.392 0 .757.112 1.066.306L7 5.5.94.305z"/></svg>
                                                            <# } #>
                                                            <# if ( element_type === 'name'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="14" viewBox="0 0 11 14" preserveAspectRatio="none" class="hustle-icon hustle-i_user"><path fill-rule="evenodd" d="M1.632 6.785c.917 1.118 2.31 1.83 3.868 1.83 1.56 0 2.95-.712 3.868-1.83C10.376 7.533 11 8.787 11 10.8c0 2-2.75 2.5-5.5 2.5S0 12.8 0 10.8c0-2.013.624-3.267 1.632-4.015zM5.5 7C3.567 7 2 5.433 2 3.5S3.567 0 5.5 0 9 1.567 9 3.5 7.433 7 5.5 7z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'address'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="15" viewBox="0 0 10 15" preserveAspectRatio="none" class="hustle-icon hustle-i_pin"><path fill-rule="evenodd" d="M5 0c2.442 0 5 1 5 4.5S6.178 12.904 5 15C3.805 12.904 0 8 0 4.5S2.54 0 5 0zm0 6.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'phone'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" preserveAspectRatio="none" class="hustle-icon hustle-i_phone"><path fill-rule="evenodd" d="M9.947 13.855s-2.94-1.157-5.795-4.01C1.3 6.99.14 4.046.14 4.046c-.28-.605-.125-1.48.347-1.953L2.38.204c.314-.316.746-.258.964.13l1.63 2.91c.218.39.14.96-.177 1.276l-.614.613s.903 1.495 2.044 2.637c1.142 1.14 2.58 1.986 2.58 1.986l.613-.613c.316-.316.886-.394 1.274-.174l2.968 1.68c.388.22.448.652.132.968l-1.892 1.89c-.473.475-1.35.638-1.955.347z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'text'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="13" viewBox="0 0 12 13" preserveAspectRatio="none" class="hustle-icon hustle-i_text"><path fill-rule="evenodd" d="M7 10.928v-9.25h3l1 2.25h1l-.188-3.01c-.034-.547-.5-.955-1.062-.915 0 0-1.875.175-4.75.175S1.25.003 1.25.003C.698-.04.222.37.188.917L0 3.927h1l1-2.25h3v9.25l-2 1v1l3-.25 3 .25v-1l-2-1z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'number'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" preserveAspectRatio="none" class="hustle-icon hustle-i_number"><path fill-rule="evenodd" d="M3 8V5H1c-.553 0-1-.444-1-1 0-.552.447-1 1-1h2V1c0-.553.444-1 1-1 .552 0 1 .447 1 1v2h3V1c0-.553.444-1 1-1 .552 0 1 .447 1 1v2h2c.553 0 1 .444 1 1 0 .552-.447 1-1 1h-2v3h2c.553 0 1 .444 1 1 0 .552-.447 1-1 1h-2v2c0 .553-.444 1-1 1-.552 0-1-.447-1-1v-2H5v2c0 .553-.444 1-1 1-.552 0-1-.447-1-1v-2H1c-.553 0-1-.444-1-1 0-.552.447-1 1-1h2zm2 0h3V5H5v3z"/></svg>
                                                            <# } #>

                                                            <# if ( element_type === 'url'  ) { #>
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="13" viewBox="0 0 16 13" preserveAspectRatio="none" class="hustle-icon hustle-i_url"><path fill-rule="evenodd" d="M0 2.003C0 .897.895 0 1.994 0h12.012C15.106 0 16 .894 16 2.003v8.994C16 12.103 15.105 13 14.006 13H1.994C.894 13 0 12.106 0 10.997V2.003zm1 0v8.994c0 .557.445 1.003.994 1.003h12.012c.547 0 .994-.45.994-1.003V2.003C15 1.446 14.555 1 14.006 1H1.994C1.447 1 1 1.45 1 2.003zm7 5.33V9H7V4h2c.557 0 1 .447 1 1v1c0 .557-.447 1-1 1h-.2L10 9H9L8 7.333zM8 5h.495c.28 0 .505.232.505.5 0 .276-.214.5-.505.5H8V5zM4.5 8c.268 0 .5-.22.5-.49V4h1v3.5C6 8.326 5.328 9 4.5 9 3.666 9 3 8.328 3 7.5V4h1v3.51c0 .275.224.49.5.49zM12 8h1v1h-1c-.556 0-1-.448-1-1V4h1v4z"/></svg>
                                                            <# } #>
															<# if( _.isTrue( element.required ) ) { #>
															<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" preserveAspectRatio="none" class="hustle-icon hustle-i_warning" style="display:none"><path fill-rule="evenodd" d="M9 18c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9zm.25-3c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25S8 13.06 8 13.75 8.56 15 9.25 15zm-.018-4C8 11 7 3.5 9.232 3.5s1.232 7.5 0 7.5z"/></svg>
															<# } #>
                                                        </span>
                                                    <# } #>
                                                    <span class="hustle-modal-optin_placeholder">{{element.placeholder}}</span>
                                                </label>
											</div>
										<# } #>
									<# }); #>
								<# } #>



							<# if ( _.isTrue( have_mc_group ) ) { #>

                                </div>

                            <# } #>

							<# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-mc_title">

                                    <label>{{content.args.group.title}}</label>

                                </div>

                            <# } #>

							<# if ( _.isTrue( have_mc_group ) ) { #>

                                <div class="hustle-modal-optin_group">

                            <# } #>

								<# if ( _.isTrue( have_mc_group ) ) { #>

									<div class="hustle-modal-mc_groups hustle-modal-provider-args-container"></div>

								<# } #>

								<# if ( typeof content.form_elements.submit !== 'undefined' && typeof content.form_elements.submit.label !== 'undefined' ) { #>

									<div class="hustle-modal-optin_button">

										<button type="submit">{{ content.form_elements.submit.label }}</button>

									</div>

								<# } #>

							<# if ( _.isTrue( have_mc_group ) ) { #>

                                </div>

							<# } #>

                        </form>

                    <# if ( design.form_layout === "three" ) { #>

                        </div>

                    <# } else { #>

                        </footer>

                    <# } #>

                <# } #>

            <# if ( design.form_layout === "two" ) { #>

                </div>

            <# } #>

        </div><?php // .hustle-modal-body ?>

    </div><?php // .hustle-modal ?>

</script>