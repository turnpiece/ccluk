Hustle.define("Slidein.Design_View", function($, doc, win){
	"use strict";
	return Hustle.View.extend(_.extend({}, Hustle.get("Mixins.Model_Updater"), {
		template: Optin.template("wpmudev-hustle-slidein-section-design-tpl"),
		target_container: $('#wpmudev-hustle-box-section-design'),
		use_email_collection: false,
		css_editor: false,
		stylables: {
			".hustle-modal .hustle-modal-close .hustle-icon ": "Close Icon",
			".hustle-modal .hustle-modal-body ": "Modal Container",
			".hustle-modal .hustle-modal-image ": "Image Container",
			".hustle-modal .hustle-modal-image .hustle-modal-feat_image, .hustle-modal .hustle-modal-image img ": "Modal Image",
			".hustle-modal .hustle-modal-optin_form ": "Form Container",
			".hustle-modal .hustle-modal-optin_form .hustle-modal-optin_field ": "Form Input Container",
			".hustle-modal .hustle-modal-optin_form .hustle-modal-optin_button ": "Form Button Container",
			".hustle-modal .hustle-modal-optin_form .hustle-modal-optin_field input ": "Form Input",
			".hustle-modal .hustle-modal-optin_form .hustle-modal-optin_button button ": "Form Button",
		},
				events: {
			"click .wpmudev-css-stylable": "insert_stylable_element"
		},
		init: function( opts ){
			if ( this.target_container.length ) {
				this.use_email_collection = opts.use_email_collection;
				return this.render();
			}
		},
		render: function(args){
			this.setElement( this.template( _.extend( {
				use_email_collection: this.use_email_collection,
				stylables: this.stylables,
			}, this.model.toJSON() ) ) );
			return this;
		},
		after_render: function() {
			if ( this.target_container.length ) {
				this.create_color_pickers();
				this.create_css_editor();
				this.hide_unwanted_options();
			}
		},
		create_color_pickers: function() {
						this.$(".wpmudev-color_picker").wpColorPicker({
								change: function(event, ui){
										var $this = $(this);
										$this.val( ui.color.toCSS()).trigger("change");
								}
						});
		},
		create_css_editor: function(){
			this.css_editor = ace.edit("hustle_custom_css");

			this.css_editor.getSession().setMode("ace/mode/css");
			this.css_editor.$blockScrolling = Infinity;
			this.css_editor.setTheme("ace/theme/hustle");
			this.css_editor.getSession().setUseWrapMode(true);
			this.css_editor.getSession().setUseWorker(false);
			this.css_editor.setShowPrintMargin(false);
			this.css_editor.renderer.setShowGutter(true);
			this.css_editor.setHighlightActiveLine(true);
		},
		hide_unwanted_options: function() {
			if ( this.model.get('form_layout') === 'one' && _.isTrue(this.use_email_collection) ) {
				this.$('#wpmudev-tabs-menu_item_above').show();
				this.$('#wpmudev-tabs-menu_item_below').show();
				this.$('#wpmudev-tabs-menu_item_above').prev().find('label').removeAttr("style");
			} else {
				this.$('#wpmudev-tabs-menu_item_above').hide();
				this.$('#wpmudev-tabs-menu_item_below').hide();
				this.$('#wpmudev-tabs-menu_item_above').prev().find('label').css({
					'border-right': '1px solid #E1E1E1',
					'border-radius': '0 10px 10px 0',
					'-moz-border-radius': '0 10px 10px 0',
					'-webkit-border-radius': '0 10px 10px 0',
				});
				
				// set feature_image_position to 'left' if not already 'right'.
				if ( this.model.get('feature_image_position') !== 'right') {
					this.$('ul.wpmudev-feature-image-position-options input[value="left"]').parent().click();
				}
			}
						// Overlay is only necessary for pop-ups, not slide-ins.
						this.$el.find('#popup_overlay_color').parents('.wpmudev-col').hide();
		},
		update_custom_css: function(){
			if( this.css_editor )
				this.model.set("custom_css", this.css_editor.getValue() );
		},
		insert_stylable_element: function(e){
			e.preventDefault();
			var $el = $(e.target),
				stylable = $el.data("stylable") + "{}";

			this.css_editor.navigateFileEnd();
			this.css_editor.insert(stylable);
			this.css_editor.navigateLeft(1);
			this.css_editor.focus();

		}

	} ) );

});
