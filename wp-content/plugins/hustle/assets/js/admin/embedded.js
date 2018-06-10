Hustle.define("Embedded.Module", function() {

	"use strict";

	// Listings Page
	(function(){

		if( "hustle_page_hustle_embedded_listing" !== pagenow ) return;

		var E_Listing = Hustle.get("Embedded.Listing");
		var embedded_listing = new E_Listing();
		
	}());

	// Wizard Page
	(function(){

		if( "hustle_page_hustle_embedded" !== pagenow ) return;
		
		var View = Hustle.get("Embedded.View"),
			Content_View = Hustle.get("Embedded.Content_View"),
			Design_View = Hustle.get("Embedded.Design_View"),
			Settings_View = Hustle.get("Embedded.Settings_View"),
			Conditions_View = Hustle.get("Settings.Conditions_View"),
			View_Model = Hustle.get("Embedded.Models.Base"),
			C_Model = Hustle.get("Embedded.Models.Content"),
			D_Model = Hustle.get("Embedded.Models.Design"),
			S_Model = Hustle.get("Embedded.Models.Display_Settings");

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
					type: "embedded"
				})
			})
		});

	}());

} );
