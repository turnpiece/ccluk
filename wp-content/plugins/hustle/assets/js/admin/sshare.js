Hustle.define("SShare.Module", function($){
	"use strict";

	/**
	 * Listing Page
	 */
	(function(){
		if( "hustle_page_hustle_sshare_listing" !== pagenow ) return;

		var Listing = Hustle.get("SShare.Listing");
		var ss_listing = new Listing();
	}());


	/**
	 * Edit or New page
	 */
	(function(){
		if( "hustle_page_hustle_sshare" !== pagenow ) return;
		
		if ( parseInt(optin_vars.current.is_ss_limited) ) return;
		
		var View = Hustle.get("SShare.View"),
			Content_View = Hustle.get("SShare.Content_View"),
			Design_View = Hustle.get("SShare.Design_View"),
			Settings_View = Hustle.get("SShare.Settings_View"),
			Conditions_View = Hustle.get("Settings.Conditions_View"),
			View_Model = Hustle.get("SShare.Models.Base"),
			Content_Model = Hustle.get("SShare.Models.Content"),
			Design_Model = Hustle.get("SShare.Models.Design"),
			Display_Settings_Model = Hustle.get("SShare.Models.Display_Settings");
		
		var base_model = new View_Model( optin_vars.current.data || {}  ),
			content_model = new Content_Model( optin_vars.current.content || {}  ),
			design_model = new Design_Model( optin_vars.current.design || {}  ),
			settings_model = new Display_Settings_Model( optin_vars.current.settings || {}  );
		
		return new View({
			model: base_model,
			content_view: new Content_View({ model: content_model }),
			design_view: new Design_View({ 
				model: design_model,
				social_icons: content_model.get('social_icons'),
				service_type: content_model.get('service_type'),
				click_counter: content_model.get('click_counter'),
			}),
			settings_view: new Settings_View({
				model: settings_model,
				conditions_view: new Conditions_View({
					model: settings_model.get('conditions'),
					type: "social_sharing"
				})
			})
		});

	}());
});

