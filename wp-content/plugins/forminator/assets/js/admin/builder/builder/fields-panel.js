( function ($) {
	define([
		'admin/builder/drag-drop',
		'admin/builder/builder/field',
		'admin/models/fields-collection',
		'admin/builder/builder/wrapper'
	], function( DragDrop, Field, FieldsCollection, Wrapper ) {
		var FieldsPanel = Backbone.View.extend({
			className: "wpmudev-builder--form-wrappers",
			fields: false,
			wrappers: false,
			grid: false,

			initialize: function ( options ) {
				//this.listenTo( Forminator.Events, "dnd:models:updated", this.render );
				this.listenTo( Forminator.Events, "forminator:dnd:field:select", this.make_field_active );
				this.listenTo( Forminator.Events, "sidebar:settings:updated", this.settings_updated );
				this.listenTo( Forminator.Events, "sidebar:clone:field", this.clone_field );
				this.listenTo( Forminator.Events, "sidebar:delete:field", this.delete_field );

				return this.render();
			},

			render: function () {
				var self = this;

				this.$el.html('');

				var counter = 0;

				this.wrappers = this.model.get( "wrappers" );
				this.grid = {};

				// To-Do: refactor append to happen at once for optimisation
				// Initialize D&D Grid
				this.wrappers.each( function ( wrapper ) {

					var fields = wrapper.get( "fields" );

					var wrapper_object = new Wrapper({
						model: wrapper
					});

					self.$el.append( wrapper_object.$el );

					self.grid[ counter ] = new DragDrop( wrapper_object, wrapper, false, 'wrapper', self.model );

					self.grid[ counter ][ 'fields' ] = {};

					fields.each( function( model ) {

						var field = new Field({
							model: model,
							layout: self.model
						});

						wrapper_object.$el.append( field.$el );

						self.grid[ counter ][ 'fields' ][ model.get( 'id' ) ] = new DragDrop( field, model, wrapper, 'field', self.model );

					});

					counter++;

				});

				Forminator.Grid = this.grid;
			},

			clone_field: function ( field ) {
				// Clone field
				var wrapper = this.get_wrapper_by_field( field ),
					wrapper_index = this.wrappers.model_index( wrapper )
				;

				// Create new wrapper
				var my_model = field.clone_deep(),
					new_model = new Forminator.Models.Wrapper({
						"fields": new Forminator.Collections.Fields( my_model )
					})
				;

				//set new wrapper
				new_model.set( 'wrapper_id', Forminator.Utils.get_unique_id( 'wrapper' ) );

				// Update field to full width
				new_model.set( 'cols', 12 );

				// Set a new unique field id
				new_model.set( 'element_id', Forminator.Utils.get_unique_id( 'field' ) );

				// Add wrapper
				this.wrappers.add( new_model, { silent: true } );

				var my_index = this.wrappers.model_index( new_model );

				// Move new wrapper to correct place
				this.wrappers.move_to( wrapper_index + 1, my_index );

				Forminator.Events.trigger( "forminator:sidebar:open:settings", my_model );

				this.render();

				this.make_field_active( my_model.cid );
			},

			get_wrapper_by_field: function ( field_model ) {
				var wrapper_model = false;
				this.wrappers.each( function ( wrapper ) {
					wrapper.get( 'fields' ).each( function ( field ) {
						if( field.get_id() === field_model.get_id() ) {
							wrapper_model = wrapper;
						}
					});
				});

				return wrapper_model;
			},

			delete_field: function(field){

				// Delete field
				var wrapper = this.get_wrapper_by_field( field );

				if( wrapper === false ) return;

				var wrapper_fields = wrapper.get( 'fields' ),
					wrapper_index = this.wrappers.model_index( wrapper ),
					fields_count = wrapper.fields_count()
				;

				// Delete the field
				wrapper_fields.remove( field, { silent: true });

				if( fields_count > 1 ) {
					// We have multiple fields in row, update columns
					var remainingItemsCols = 12 / ( wrapper_fields.length );
					wrapper_fields.update_cols( remainingItemsCols );
				} else {
					// We have only one field, delete whole wrapper
					this.wrappers.remove( wrapper, { silent: true });
				}

				// Close settings
				$( '#wpmudev-sidebar-settings' ).find( '.wpmudev-sidebar' ).removeClass( 'wpmudev-is_active' );
				Forminator.Events.trigger( "forminator:sidebar:close:settings" );

				Forminator.Events.trigger( 'dnd:reload:fields' );
			},

			settings_updated: function ( field ) {
				field.trigger('forminator:field:settings:updated');

				//find other fields that use this field as condition
				var fields = Forminator.Utils.get_fields_models(this.wrappers,[field.cid]),
					fieldsCollection = new FieldsCollection(fields),
					related_fields = fieldsCollection.get_fields_related_to(field);

				_.each(related_fields, function(related_field){
					related_field.trigger('forminator:field:settings:updated');
				});

				this.make_field_active( field.cid );
			},

			make_field_active: function ( id ) {
				var $field = $( '#wpmudev-field-' + id + ' .wpmudev-form-field' );

				// Keep the field active after render
				$field.addClass( "wpmudev-is_active" );
				//scroll to activated field
				$('html, body').animate({
					scrollTop: ( $field.offset().top - ($(window).height() - $field.outerHeight(true)) / 2)
				}, 0);
			}

		});

		return FieldsPanel;
	});
})( jQuery );
