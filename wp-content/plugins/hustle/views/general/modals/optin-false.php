<?php
$close_icon = '<svg width="150" height="150" viewBox="0 0 150 150" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="hustle-icon hustle-i_close"><path d="M91.667 75L150 16.667 133.333 0 75 58.333 16.667 0 0 16.667 58.333 75 0 133.333 16.667 150 75 91.667 133.333 150 150 133.333 91.667 75z" fill-rule="evenodd"/></svg>';
?>

<script id="wpmudev-hustle-modal-without-optin-tpl" type="text/template">

<div class="hustle-modal hustle-modal-{{design.style}}<# if ( _.isTrue(content.use_feature_image) && _.isFalse(content.has_title) && content.main_content === '' && _.isFalse(content.show_cta) ) { #> hustle-modal-image_only<# } #> {{ ( (settings.animation_in !== '' && settings.animation_in !== 'no_animation') || (settings.animation_out !== '' && settings.animation_out !== 'no_animation') ) ? 'hustle-animated' : 'hustle-modal-static' }}">

    <# if ( design.style !== "cabriolet" ) { #>

        <div class="hustle-modal-close" aria-hidden="true"><?php echo $close_icon; ?></div>

    <# } #>

    <div class="hustle-modal-body<# if ( design.style === 'simple' && design.feature_image_position === 'right' ) { #> hustle-modal-image_{{design.feature_image_position}}<# } #>">

        <# if ( design.style === "simple" && ( _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ) { #>

            <div class="hustle-modal-image hustle-modal-image_{{design.feature_image_fit}}<# if ( _.isTrue(content.feature_image_hide_on_mobile) ) { #> hustle-modal-mobile_hidden<# } #>">

                <img src="{{content.feature_image}}"<# if (design.feature_image_fit === "contain" || design.feature_image_fit === "cover") { if ( design.feature_image_horizontal !== "custom" || design.feature_image_vertical !== "custom" ) { #> class="hustle-modal-image_{{design.feature_image_horizontal}}{{design.feature_image_vertical}}"<# } } #>>

            </div>

        <# } #>

        <# if ( design.style === "simple" && (
            ( _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) ||
            content.main_content !== '' ||
            ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) )
        ) ) { #>

            <div class="hustle-modal-content">

        <# } #>

            <# if (
                ( design.style === "simple" && _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) ||
                ( design.style === "minimal" && _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) ||
                ( design.style === "cabriolet" && ( _.isTrue(content.has_title) || _.isFalse(content.has_title) ) )
            ) { #>

                <header>

                    <# if ( _.isTrue(content.has_title) ) { #>
                        <# if ( content.title !== '' ) { #>
                            <h1 class="hustle-modal-title">{{content.title}}</h1>
                        <# } #>
                        <# if ( content.sub_title !== '' ) { #>
                            <h2 class="hustle-modal-subtitle">{{content.sub_title}}</h2>
                        <# } #>
                    <# } #>

                    <# if ( design.style === "cabriolet" ) { #>
                        <div class="hustle-modal-close"><?php echo $close_icon; ?></div>
                    <# } #>

                </header>

            <# } #>

            <# if (
                ( design.style === "simple" && ( content.main_content !== '' || ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) ) ) ) ||
                ( design.style === "minimal" && ( content.main_content !== '' || _.isTrue(content.use_feature_image) ) ) ||
                ( design.style === "cabriolet" && ( content.main_content !== '' || _.isTrue(content.use_feature_image) || ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) ) ) )
            ) { #>

                <section<# if ( design.style !== "simple" && design.feature_image_position === 'right' ) { #> class="hustle-modal-image_{{design.feature_image_position}}"<# } #>>

                    <# if ( design.style !== "simple" && ( _.isTrue(content.use_feature_image) && content.feature_image !== '' ) ) { #>

                        <div class="hustle-modal-image hustle-modal-image_{{design.feature_image_fit}}<# if ( _.isTrue(content.feature_image_hide_on_mobile) ) { #> hustle-modal-mobile_hidden<# } #>">

                            <img src="{{content.feature_image}}"<# if (design.feature_image_fit === "contain" || design.feature_image_fit === "cover") { if ( design.feature_image_horizontal !== "custom" || design.feature_image_vertical !== "custom" ) { #> class="hustle-modal-image_{{design.feature_image_horizontal}}{{design.feature_image_vertical}}"<# } } #>>

                        </div>

                    <# } #>

                    <# if (
						content.main_content !== '' ||
						( _.isTrue(content.show_gdpr) && content.show_gdpr !== '' ) ||
                        ( design.style !== "minimal" && ( content.main_content !== '' || _.isTrue(content.show_cta) ) )
                    ) { #>

                        <div class="hustle-modal-message">

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

                            <# if ( design.style !== "minimal" && ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) ) ) { #>

                                <div class="hustle-modal-footer">

									<a target="_{{content.cta_target}}" href="{{content.cta_url}}" class="hustle-modal-cta">{{content.cta_label}}</a>

                                </div>

                            <# } #>

                        </div>

                    <# } #>

                </section>

            <# } #>

        <# if ( design.style === "simple" && (
            ( _.isTrue(content.has_title) && ( content.title !== '' || content.sub_title !== '' ) ) ||
            content.main_content !== '' ||
            ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) )
        ) ) { #>

            </div>

        <# } #>

        <# if ( design.style === "minimal" && ((_.isTrue(content.show_gdpr) && content.gdpr_message !== '' ) || ( _.isTrue(content.show_cta) && ( content.cta_label !== '' && content.cta_url !== '' ) ) ) ) { #>

            <footer>

				<a target="_{{content.cta_target}}" href="{{content.cta_url}}" class="hustle-modal-cta">{{content.cta_label}}</a>

            </footer>

		<# } #>

    </div>

</div>

</script>