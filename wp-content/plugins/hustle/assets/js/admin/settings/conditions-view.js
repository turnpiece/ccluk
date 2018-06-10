Hustle.define("Settings.Conditions_View", function( $ ) {
	"use strict";
	return Hustle.View.extend({
		template: Optin.template("wph-wizard-module-conditions"),
		handle_tpl: Optin.template("wph-wizard-module-conditions-handle"),
		className: "wph-conditions",
		events: {
			'click .wph-conditions--side .wph-conditions--item:not(.disabled)': 'toggle_condition',
			'click .wph-conditions--side .wph-conditions--item:not(.disabled) span': 'toggle_condition',
			'click .wph-conditions--item header': "toggle_panel"
		},
		init: function (opts) {
			this.type = opts.type;
			this.active_conditions = {};

			this.listenTo( this.model, "change", this.toggle_empty_message  );

			this.render();
		},
		render: function () {
			var conditions = this.model.toJSON();
			this.$el.html( this.template( { type_name: this.type } ) );
			_.each(Optin.View.Conditions, function (condition, id) {
				var handle = this.handle_tpl({
					label: this.get_label(id),
					id: id,
					cid: this.get_condition_cid(id),
					active_class: conditions[id] ? "added" : '',
					icon_class: conditions[id] ? "wph-condition-remove" : "wph-condition-add"
				});

				// add handle
				this.$(".wph-conditions--side .wph-conditions--items").append(handle);
			}, this);

			_.each(conditions, function (condition, id) {
				this.add_condition_panel(id);
			}, this);

			this.toggle_empty_message();
		},
		get_condition_cid: function (id) {
			id = id.replace(/ /g,'');
			return this.type + "_" + id;
		},
		get_label: function (id) {
			var type_name = optin_vars.messages.settings[this.type] ? optin_vars.messages.settings[this.type] : this.type;
			return optin_vars.messages.conditions[id] ? optin_vars.messages.conditions[id].replace("{type_name}", type_name) : id;
		},
		take_care_of_connected_conditions: function (this_condition) {
			/**
			 * Disable those conditions which can't go with this condition
			 */
			if (this_condition.disable && this_condition.disable.length) {
				_.each(this_condition.disable, function (disable_id, index) {
					var $disable_handle = this.$("#" + this.get_condition_cid(disable_id));
					$disable_handle.toggleClass("disabled");
				}, this);
			}
		},
		/**
		 * Adds condition to optin type
		 *
		 * @param id
		 * @param this_condition
		 * @returns {*|{}}
		 */
		add_condition: function (id, $handle) {
			var this_condition = this.add_condition_panel(id);
			/**
			 * Add condition element
			 */
			$handle.addClass("added");
			$handle.find("span").addClass("wpoi-remove");
			$handle.find("span").removeClass("wpoi-add");

			this.model.set( id,  this_condition.get_configs());
			return this.model.toJSON();

		},
		/**
		 * Removes conditon from optin type
		 * @param id
		 */
		remove_condition: function (id, this_condition, $handle) {
			this.take_care_of_connected_conditions(this_condition);
			
			this_condition.off("change:update_label");
			this_condition.remove();
			
			delete this.active_conditions[id];
			$handle.removeClass("added");
			$handle.find("span").removeClass("wpoi-remove");
			$handle.find("span").addClass("wpoi-add");

			this.model.unset(id);
		},
		/**
		 * Add condition pannel
		 *
		 * @param id
		 * @returns {*}
		 */
		add_condition_panel: function (id) {
			if ( typeof Optin.View.Conditions[id] === 'undefined' ) return;
			
			var this_condition = this.active_conditions[id] = new Optin.View.Conditions[id]({
				model: this.model,
				type: this.type
			});

			if(_.isEmpty( this.active_conditions ) )
				this.$(".wph-conditions--box .wph-conditions--items").html("");

			this.take_care_of_connected_conditions(this_condition);

			/**
			 * Append condition panel
			 */
			var me = this;
			this_condition.on("change:update_label", function() {
				me.trigger("change:update_view_label", me);
			});
			this.$(".wph-conditions--box .wph-conditions--items").append(this_condition.$el);
			return this_condition;
		},
		/**
		 * Toggles each of the conditions
		 *
		 * @param e
		 */
		toggle_condition: function (e) {
			e.stopPropagation();

			var id = this.$(e.target).data("id") || this.$(e.target).closest(".wph-conditions--item").data("id"),
				$handle = this.$('#' + this.get_condition_cid(id)),
				this_condition = this.active_conditions[id];

			if (this_condition) {
				this.remove_condition(id, this_condition, $handle);
				this.trigger("condition_removed", this, id, this_condition);
			} else {
				this.add_condition(id, $handle);
				this.trigger("condition_added", this, id, this_condition);
			}

			this.trigger("toggle_condition", this, id, this_condition);
		},
		toggle_empty_message: function(){

			if( this.model.isEmpty() )
				this.$(".wph-conditions--box .wph-conditions--items").addClass("wph-conditions-is_empty");
			else
				this.$(".wph-conditions--box .wph-conditions--items").removeClass("wph-conditions-is_empty");

			if( this.model.isEmpty() )
				this.$(".wph-conditions--empty").show();
			else
				this.$(".wph-conditions--empty").hide();

		},
		toggle_panel: _.debounce( function(e) {
			var $this = $(e.target),
				$panel = $this.closest(".wph-conditions--item"),
				$section = $panel.find( "section");

			$section.slideToggle(300, function(){
				$panel.toggleClass("wph-conditions--closed wph-conditions--open");
			});
		}, 300),
		/**
		 * Returns labels of aggregate conditions
		 *
		 * @returns {string}
		 */
		get_conditions_labels: function(){
			var labels = _.pluck( this.active_conditions, "label" ),
				tpl = Hustle.create_template("<span>{{label}}</span>");
			return labels.length
				? labels.map( function(label) { return tpl( {label: label} ); } ).join( ", " )
				: optin_vars.messages.condition_labels.everywhere;
		},
		/**
		 * Returns labels of aggregate conditions and default conditions
		 *
		 * @returns {string}
		 */
		get_all_conditions_labels: function(){
			var conditions = this.active_conditions;
			var default_conditions = {
				'posts': {
					'label' : optin_vars.messages.condition_labels.all_posts
				},
				'pages': {
					'label' : optin_vars.messages.condition_labels.all_pages
				},
				'categories': {
					'label' : optin_vars.messages.condition_labels.all_categories
				},
				'tags': {
					'label' : optin_vars.messages.condition_labels.all_tags
				}
			};
			var default_labels = [];
			
			// append defaults
			for ( var key in default_conditions ) {
				if ( typeof conditions[key] === 'undefined' ) {
					default_labels.push(default_conditions[key]['label']);
				}
			}
			var labels = _.pluck( conditions, "label" ),
				tpl = Hustle.create_template("<span>{{label}}</span>");
			labels = labels.concat(default_labels);
			
			return labels.length
				? labels.map( function(label) { return tpl( {label: label} ); } ).join( ", " )
				: optin_vars.messages.condition_labels.everywhere;
		}
	});
});
