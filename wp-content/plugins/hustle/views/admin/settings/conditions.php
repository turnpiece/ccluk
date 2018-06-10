<script id="wph-wizard-module-conditions" type="text/template">

	<div class="wph-conditions--side">

		<label><?php _e('Available conditions', Opt_In::TEXT_DOMAIN); ?></label>

		<div class="wph-conditions--items"></div>

	</div>

	<div class="wph-conditions--box">

		<label><?php _e("Conditions in-use", Opt_In::TEXT_DOMAIN ); ?></label>

		<div class="wph-conditions--items">

			<div class="wph-conditions--empty">

				<p><?php _e("No Conditions applied.", Opt_In::TEXT_DOMAIN); ?></p>

				<p><?php _e("Currently this {{type_name}} will be shown everywhere across your site.", Opt_In::TEXT_DOMAIN); ?></p>

			</div>

		</div>

	</div>

</script>

<script id="wph-wizard-module-conditions-handle" type="text/template">

	<div class="wph-conditions--item {{active_class}}" id="{{cid}}" data-id="{{id}}">{{label}}<div class="wph-conditions-icon {{icon_class}}"><?php $this->render( "general/icons/icon-plus", array() ); ?></div></div>

</script>

<script id="wph-wizard-module-conditions-item" type="text/template">

	<header>

		<label class="wph-condition--name">{{title}}</label>

		<label class="wph-condition--preview"><i class="wph-icon i-eye"></i>{{header}}</label>

		<!--<span class="dashicons-before wpoi-arrow-up"></span>-->

	</header>

	<section>{{{ body }}}</section>

</script>

<script id="wpoi-condition-shown_less_than" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the {{type_name}} if the user has only seen it less than a specific number of times.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<div class="wph-label--number wph-label--left">

			<label for="shown_less_than_value" class="wph-label--alt"><?php _e("Display {{type_name}} this often:", Opt_In::TEXT_DOMAIN); ?></label>

			<input type="number" id="shown_less_than_value" class="wpmudev-input_number inp-small" name="" data-attribute="less_than" min="1" max="999" maxlength="3" placeholder="10" value="{{less_than}}">

		</div>

	</div>

</script>

<script id="wpoi-condition-from_specific_ref" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the Pop-up if the user arrived via a specific referrer.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<label for="from_specific_ref_refs" class="wph-label--alt"><strong><?php _e('Referrers. Can be full URL or a pattern like ".example.com" (one per line):', Opt_In::TEXT_DOMAIN); ?></strong></label>

		<textarea class="wpmudev-textarea" name="" id="from_specific_ref_refs" data-attribute="refs" class="block">{{{refs}}}</textarea>

	</div>

</script>

<script id="wpoi-condition-not_from_specific_ref" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Hides the Pop-up if the user arrived via a specific referrer.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<label for="from_specific_ref_refs" class="wph-label--alt"><strong><?php _e('Referrers. Can be full URL or a pattern like ".example.com" (one per line):', Opt_In::TEXT_DOMAIN); ?></strong></label>

		<textarea class="wpmudev-textarea" name="" id="from_specific_ref_refs" data-attribute="refs" class="block">{{{refs}}}</textarea>

	</div>

</script>

<script id="wpoi-condition-on_specific_url" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the {{type_name}} if the user is on a certain URL.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<label for="on_specific_url_urls" class="wph-label--alt"><strong><?php _e("Show on these URLs (one per line):", Opt_In::TEXT_DOMAIN); ?></strong></label>

		<textarea class="wpmudev-textarea" name="" id="on_specific_url_urls" class="block" data-attribute="urls" >{{{ urls }}}</textarea>

		<label class="wpmudev-helper"><?php _e('URLs should not include "http://" or "https://"', Opt_In::TEXT_DOMAIN); ?></label>

	</div>

</script>

<script id="wpoi-condition-not_on_specific_url" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the {{type_name}} if the user is not on a certain URL.", Opt_In::TEXT_DOMAIN) ?></h4>

	</div>

	<div class="rule-form">

		<label for="not_on_specific_url_urls" class="wph-label--alt"><strong><?php _e("Not on these URLs (one per line):", Opt_In::TEXT_DOMAIN); ?></strong></label>

		<textarea class="wpmudev-textarea" name="" id="not_on_specific_url_urls_urls" data-attribute="urls" class="block">{{{ urls }}}</textarea>

		<label class="wpmudev-helper"><?php _e('URLs should not include "http://" or "https://"', Opt_In::TEXT_DOMAIN); ?></label>

	</div>

</script>

<script id="wpoi-condition-in_a_country" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the {{type_name}} if the user is in a certain country.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<label for="in_a_country_countries" class="wph-label--alt"><strong><?php _e("Included countries:", Opt_In::TEXT_DOMAIN); ?></strong></label>

		<select name="" class="js-wpoi-select none-wpmu" id="in_a_country_countries" data-val="countries" multiple="multiple" data-attribute="countries" placeholder="<?php esc_attr_e( 'Click here to select a country', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( _.keys( optin_vars.countries ), function( key ) { #><option value="{{key}}" > {{optin_vars.countries[key]}} </option><# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-not_in_a_country" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Shows the {{type_name}} if the user is not in a certain country.", Opt_In::TEXT_DOMAIN); ?></h4>

	</div>

	<div class="rule-form">

		<label for="not_in_a_country_countries" class="wph-label--alt"><strong><?php _e("Excluded countries:", Opt_In::TEXT_DOMAIN); ?></strong></label>

		<select name="" class="js-wpoi-select none-wpmu" id="not_in_a_country_countries" data-val="countries" multiple="multiple" data-attribute="countries" placeholder="<?php esc_attr_e( 'Click here to select a country', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( _.keys( optin_vars.countries ), function( key ) { #>

				<option value="{{key}}" > {{optin_vars.countries[key]}} </option>

			<# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-posts" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Show this module for", Opt_In::TEXT_DOMAIN); ?></h4>

		<div class="wpmudev-tabs">

			<ul class="wpmudev-tabs-menu">

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "except", "current" )}}">

					<input type="radio" value="except" data-attribute="filter_type" id="{{type}}-filter_type-posts-except" name="{{type}}-filter_type-posts" {{_.checked(filter_type, 'except')}}>

					<label for="{{type}}-filter_type-posts-except"><?php _e("All Posts Except", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "only", "current" )}}" >

					<input type="radio" value="only" data-attribute="filter_type" id="{{type}}-filter_type-posts-only" name="{{type}}-filter_type-posts" {{_.checked(filter_type, 'only')}}>

					<label for="{{type}}-filter_type-posts-only"><?php _e("Only These Posts", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

			</ul>

		</div>

	</div>

	<div class="rule-form">

		<select name="" class="js-wpoi-select none-wpmu" id="{{type}}-filter_type-posts" data-val="{{posts}}" multiple="multiple" data-attribute="posts" placeholder="<?php esc_attr_e( '', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( optin_vars.posts, function( post ) {  #><option value="{{post.id}}" {{_.selected( _.contains(posts, post.id.toString() ), true )}} > {{post.text}} </option><# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-post_type" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Show this module for", Opt_In::TEXT_DOMAIN); ?></h4>

		<div class="wpmudev-tabs">

			<ul class="wpmudev-tabs-menu">

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "except", "current" )}}">

					<input type="radio" value="except" data-attribute="filter_type" id="{{type}}-filter_type-{{post_type}}-except" name="{{type}}-filter_type-{{post_type}}" {{_.checked(filter_type, 'except')}}>

					<label for="{{type}}-filter_type-{{post_type}}-except"><?php _e("All {{post_type_label}} Except", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "only", "current" )}}" >

					<input type="radio" value="only" data-attribute="filter_type" id="{{type}}-filter_type-{{post_type}}-only" name="{{type}}-filter_type-{{post_type}}" {{_.checked(filter_type, 'only')}}>

					<label for="{{type}}-filter_type-{{post_type}}-only"><?php _e("Only These {{post_type_label}}", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

			</ul>

		</div>

	</div>

	<div class="rule-form">

		<select name="" class="js-wpoi-select none-wpmu" id="{{type}}-filter_type-{{post_type}}" data-val="{{selected_cpts}}" multiple="multiple" data-attribute="selected_cpts" placeholder="<?php esc_attr_e( '', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( optin_vars.post_types[post_type].data, function( post ) {  #><option value="{{post.id}}" {{_.selected( _.contains(selected_cpts, post.id.toString() ), true )}} > {{post.text}} </option><# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-pages" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Show this module for", Opt_In::TEXT_DOMAIN); ?></h4>

		<div class="wpmudev-tabs">

			<ul class="wpmudev-tabs-menu">

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "except", "current" )}}">

					<input type="radio" value="except" data-attribute="filter_type" id="{{type}}-filter_type-pages-except" name="{{type}}-filter_type-pages" {{_.checked(filter_type, 'except')}}>

					<label for="{{type}}-filter_type-pages-except"><?php _e("All Pages Except", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "only", "current" )}}" >

					<input type="radio" value="only" data-attribute="filter_type" id="{{type}}-filter_type-pages-only" name="{{type}}-filter_type-pages" {{_.checked(filter_type, 'only')}}>

					<label for="{{type}}-filter_type-pages-only"><?php _e("Only These Pages", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

			</ul>

		</div>

	</div>

	<div class="rule-form">

		<select name="" class="js-wpoi-select none-wpmu" id="{{type}}-filter_type-pages" data-val="{{pages}}" multiple="multiple" data-attribute="pages" placeholder="<?php esc_attr_e( '', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( optin_vars.pages, function( page ) {  #><option value="{{page.id}}" {{_.selected( _.contains(pages, page.id.toString() ), true )}} > {{page.text}} </option><# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-categories" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Show this module for", Opt_In::TEXT_DOMAIN); ?></h4>

		<div class="wpmudev-tabs">

			<ul class="wpmudev-tabs-menu">

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "except", "current" )}}">

					<input type="radio" value="except" data-attribute="filter_type" id="{{type}}-filter_type-categories-except" name="{{type}}-filter_type-categories" {{_.checked(filter_type, 'except')}}>

					<label for="{{type}}-filter_type-categories-except"><?php _e("All Categories Except", Opt_In::TEXT_DOMAIN); ?></label>

				</li>


				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "only", "current" )}}" >

					<input type="radio" value="only" data-attribute="filter_type" id="{{type}}-filter_type-categories-only" name="{{type}}-filter_type-categories" {{_.checked(filter_type, 'only')}}>

					<label for="{{type}}-filter_type-categories-only"><?php _e("Only These Categories", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

			</ul>

		</div>

	</div>

	<div class="rule-form">

		<select name="" class="js-wpoi-select none-wpmu" id="{{type}}-filter_type-categories" data-val="{{categories}}" multiple="multiple" data-attribute="categories" placeholder="<?php esc_attr_e( '', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( optin_vars.cats, function( cat ) {  #><option value="{{cat.id}}" {{_.selected( _.contains(categories, cat.id.toString() ), true )}} > {{cat.text}} </option><# }); #>

		</select>

	</div>

</script>

<script id="wpoi-condition-tags" type="text/template">

	<div class="rule-description">

		<h4><?php _e("Show this module for", Opt_In::TEXT_DOMAIN); ?></h4>

		<div class="wpmudev-tabs">

			<ul class="wpmudev-tabs-menu">

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "except", "current" )}}">

					<input type="radio" value="except" data-attribute="filter_type" id="{{type}}-filter_type-tags-except" name="{{type}}-filter_type-tags" {{_.checked(filter_type, 'except')}}>

					<label for="{{type}}-filter_type-tags-except"><?php _e("All Tags Except", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

				<li class="wpmudev-tabs-menu_item {{_.class( filter_type == "only", "current" )}}" >

					<input type="radio" value="only" data-attribute="filter_type" id="{{type}}-filter_type-tags-only" name="{{type}}-filter_type-tags" {{_.checked(filter_type, 'only')}}>

					<label for="{{type}}-filter_type-tags-only"><?php _e("Only These Tags", Opt_In::TEXT_DOMAIN); ?></label>

				</li>

			</ul>

		</div>

	</div>

	<div class="rule-form">

		<select name="" class="js-wpoi-select none-wpmu" id="{{type}}-filter_type-tags" data-val="{{tags}}" multiple="multiple" data-attribute="tags" placeholder="<?php esc_attr_e( '', Opt_In::TEXT_DOMAIN ); ?>" >

			<# _.each( optin_vars.tags, function( tag ) {  #><option value="{{tag.id}}" {{_.selected( _.contains(tags, tag.id.toString() ), true )}} > {{tag.text}} </option><# }); #>

		</select>

	</div>

</script>