(function ($) {
	define([
		'admin/models/result-model'
	], function (ResultModel) {
		// Condition Collection
		return Backbone.Collection.extend({
			"model": ResultModel,

			comparator: 'order',

			get_by_name: function (name) {
				name = name.toLowerCase();
				var found = false;
				this.each(function (model) {
					if (model.get("name").toLowerCase() == name) found = model;
				});
				return found;
			},

			model_index: function (model) {
				var index = this.indexOf(model);
				return index;
			},

			get_by_index: function (index) {
				var model = this.at(index);

				return model;
			},
			move_to: function (old_index, new_index) {
				if (old_index === new_index) return this;

				var self = this,
					model = this.findWhere({order: old_index});

				// Remove it
				this.remove(model, {silent: true});

				//add with new index
				this.add(model, {at: new_index, silent: true});

				//fix other model 'order'
				this.each(function (model, index) {
					self.get(model).set('order', index);
				});
				return this;

			}
		});
	});
})(jQuery);
