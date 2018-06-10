Hustle.define("Slidein.Module", function() {
	    "use strict";
	    
	    /**
	     *      * Listing Page
	     *           */
	    (function(){
	    	        if( "hustle_page_hustle_slidein_listing" !== pagenow ) return;

	    	        var S_Listing = Hustle.get("Slidein.Listing");
	    	        var slidein_listing = new S_Listing();
	    	    }());
	    
	    
	    /**
	     *      * Edit or New page
	     *           */
	    (function(){
	    	        if( "hustle_page_hustle_slidein" !== pagenow ) return;

	    	        var View = Hustle.get("Slidein.View"),
	    	              Content_View = Hustle.get("Slidein.Content_View"),
	    	              Design_View = Hustle.get("Slidein.Design_View"),
	    	              Settings_View = Hustle.get("Slidein.Settings_View"),
								Conditions_View = Hustle.get("Settings.Conditions_View"),
	    	              View_Model = Hustle.get("Slidein.Models.Base"),
	    	              C_Model = Hustle.get("Slidein.Models.Content"),
	    	              D_Model = Hustle.get("Slidein.Models.Design"),
	    	              S_Model = Hustle.get("Slidein.Models.Display_Settings");
	    	            
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
													type: "slidein"
												})
	    	        	            })
	    	        	        });

	    	    }());
});
