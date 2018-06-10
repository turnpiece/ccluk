Hustle.define("Popup.Module", function() {
	"use strict";
	
	/**
	 * Listing Page
	 */
	(function(){
		if( "hustle_page_hustle_popup_listing" !== pagenow ) return;

		var P_Listing = Hustle.get("Popup.Listing");
		var popup_listing = new P_Listing();
	}());
	
	
	/**
	 * Edit or New page
	 */
	(function(){
		if( "hustle_page_hustle_popup" !== pagenow ) return;

		var View = Hustle.get("Pop_Up.View"),
			Content_View = Hustle.get("Pop_Up.Content_View"),
			Design_View = Hustle.get("Pop_Up.Design_View"),
			Settings_View = Hustle.get("Pop_Up.Settings_View"),
			Conditions_View = Hustle.get("Settings.Conditions_View"),
			View_Model = Hustle.get("Pop_Up.Models.Base"),
			C_Model = Hustle.get("Pop_Up.Models.Content"),
			D_Model = Hustle.get("Pop_Up.Models.Design"),
			S_Model = Hustle.get("Pop_Up.Models.Display_Settings");
			
		var base_model = new View_Model( optin_vars.current.data || {}  ),
			content_model = new C_Model( optin_vars.current.content || {}  ),
			design_model = new D_Model( optin_vars.current.design || {}  ),
			settings_model = new S_Model( optin_vars.current.settings || {}  );
		
		return new View({
			model: base_model,
			content_view: new Content_View({ 
				model: content_model,
				module_id: base_model.get('module_id')
			}),
			design_view: new Design_View({ 
				model: design_model,
				use_email_collection: content_model.get('use_email_collection')
			}),
			settings_view: new Settings_View({ 
				model: settings_model,
				conditions_view: new Conditions_View({
					model: settings_model.get('conditions'),
					type: "pop-up"
				})
			})
		});

	}());
});
