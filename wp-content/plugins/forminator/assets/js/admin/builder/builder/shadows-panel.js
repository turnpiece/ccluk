( function ($) {
	define([
		'admin/builder/drag-drop',
		'admin/builder/builder/field',
		'admin/builder/builder/wrapper',
	], function( DragDrop, Field, Wrapper ) {
		var ShadowsPanel = Backbone.View.extend({
			initialize: function ( options ) {
				return this.render();
			},

			render: function () {
				this.$el.html('');

				var self = this,
					fields = Forminator.Data.fields
				;

				if( ! fields.length ) return;

				// Loop all existing fields
				_.each( fields, function ( field, key ) {
					var defaults = _.extend({
							type: field.type,
							options: field.options,
							cols: 12,
							conditions: new Forminator.Collections.Conditions()
						}, field.defaults ),
						model = new Forminator.Models.Fields( defaults ),
						view = new Field({
							model: model
						})
					;

					// Make it draggable
					view.dnd = new DragDrop( view, model, false, 'field', self.model );

					view.$el.attr("data-shadow", field.slug);

					self.$el.append( view.$el );
				});

			},

		});

		return ShadowsPanel;
	});
})( jQuery );
