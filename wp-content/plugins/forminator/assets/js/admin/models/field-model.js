(function ($) {
	define( [
		'admin/models/base-model',
		'admin/models/conditions-collection'
	], function( BaseModel, ConditionsCollection ) {
		// Condition model
		return BaseModel.extend({
			// Model defaults
			defaults: {
				condition_action: "show",
				condition_rule: "any",
				conditions: false
			},

			initialize: function () {
				var args = arguments;

				if ( this.get( 'conditions' ) === false ) this.set( 'conditions', new ConditionsCollection() );

				if (args[0] && args[0]["conditions"]) {
					args["conditions"] = args[0]["conditions"] instanceof ConditionsCollection ? args[0]["conditions"] : new ConditionsCollection(args[0]["conditions"]);

					this.set("conditions", args.conditions);
				}
			},

			add_to: function ( collection, index, options ) {
				options = _.isObject( options ) ? options : {};

				var me = this,
					models = [],
					added = false
				;

				collection.each( function( each, i ) {
					if ( i == index ){
						models.push( me );
						added = true;
					}

					models.push( each );
				});

				if ( added ) {
					collection.reset( models, {silent: true} );
					collection.trigger( 'add', this, collection, _.extend( options, { index: index } ) );
				} 	else {
					collection.add( this, _.extend( options, { index: index } ) );
				}

			},

			get_id: function () {
				return this.cid;
			},

			/**
			 * get fields used on conditions
			 * @returns {Array}
			 */
			get_conditions_element_ids: function() {
				var conditions_element_ids = [];
				if( ! _.isUndefined( this.get( 'conditions' ) ) && this.get( 'conditions' ).length > 0 ) {
					var conditions = this.get('conditions');
					conditions.each(function(condition){
						conditions_element_ids.push(condition.get('element_id'));
					});
				}

				return conditions_element_ids;
			},

			clone_deep: function () {
				var cloned_model = new this.constructor(this.attributes);

				_.each(this.attributes, function (val, key) {
					if (Array.isArray(val)) {
						var attrsCopy = [];
						_.each(val, function (attrVal) {
							attrsCopy.push(_.clone(attrVal));
						});
						cloned_model.set(key, attrsCopy);
					}
				});

				cloned_model.set('conditions', new ConditionsCollection(cloned_model.get('conditions').toJSON()));

				return cloned_model;

			}
		});
	});
})(jQuery);
