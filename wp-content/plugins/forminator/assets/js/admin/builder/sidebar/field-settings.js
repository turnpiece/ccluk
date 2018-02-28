( function ($) {
	define([
		'admin/builder/sidebar/settings-factory',
		'text!admin/templates/builder-sidebar.html'
	], function( SettingsFactory, sidebarTpl ) {
		var SidebarSettings = Backbone.View.extend({
			tpl: Forminator.Utils.template( $( sidebarTpl ).find('#builder-sidebar-settings-tpl').html() ),

			className: 'wpmudev-sidebar',

			events: {
				"click .wpmudev-sidebar--header .wpmudev-breadcrumb--back": "destroy_settings",
				"click .wpmudev-sidebar--footer .wpmudev-done-field": "destroy_settings",
				"click .wpmudev-sidebar--footer .wpmudev-clone-field": "clone_field",
				"click .wpmudev-sidebar--footer .wpmudev-delete-field": "delete_field"
			},

			initialize: function (options) {
				this.field = options.field;
				this.field_settings = options.field_settings;
				return this.render();
			},

			render: function () {
				var self = this,
					tplData = {
					field: this.field,
					field_settings: this.field_settings
				};

				this.$el.html( this.tpl( tplData ) );

				// Init tabs
				this.$el.find( ".wpmudev-sidebar--section" ).tabs();

				// Render field General settings
				this.render_general_settings();

				if( this.field_settings.hide_advanced === "true" ) {
					// Remove Advanced tab
					this.$el.find( ".settings-advanced" ).remove();
					this.$el.find( "#wpmudev-settings--advanced" ).remove();
				} else {
					// Render field Advanced settings
					this.render_advanced_settings();
				}
			},

			render_general_settings: function () {
				var general_settings = new SettingsFactory({
					model: this.model,
					field: this.field,
					field_settings: this.field_settings,
					tab: 'General'
				});

				this.$el.find( '#wpmudev-settings--general' ).append( general_settings.$el );
			},

			render_advanced_settings: function () {
				var advanced_settings = new SettingsFactory({
					model: this.model,
					field: this.field,
					field_settings: this.field_settings,
					tab: 'Advanced'
				});

				this.$el.find( '#wpmudev-settings--advanced' ).append( advanced_settings.$el );
			},

			destroy_settings: function(e) {
				e.preventDefault();

				// Unselect all fields
				$( 'body' ).find( '.wpmudev-form-field' ).each( function() {
					$( this ).removeClass( "wpmudev-is_active" );
				});

				Forminator.Events.trigger( "forminator:sidebar:close:settings" );
			},

			cleanUp: function () {
				this.$el.off();
				this.remove();
			},

			clone_field: function ( e ) {
				e.preventDefault();

				Forminator.Events.trigger( "sidebar:clone:field", this.field );

			},

			delete_field: function ( e ) {
				e.preventDefault();

				Forminator.Events.trigger( "sidebar:delete:field", this.field );

			}
		});

		return SidebarSettings;
	});
})( jQuery );
