(function( $ ) {
	"use strict";
	Optin.View.Conditions = Optin.View.Conditions || {};

	var Condition_Base = Hustle.View.extend({
		condition_id: "",
		className: "wph-conditions--item wph-conditions--open",
		_template: Optin.template('wph-wizard-module-conditions-item'),
		template: false,
		_defaults: {
			type_name: "",
			condition_name: "",
			label: ""
		},
		_events:{
			'change input': 'change_input',
			'change textarea': 'change_input',
			'change select': 'change_input'
		},
		init: function( opts ){
			this.type = opts.type;
			this.id = this.type + "-" + this.condition_id;
			this.template =  ( typeof this.cpt !== 'undefined' )
				? Optin.template('wpoi-condition-post_type')
				: Optin.template('wpoi-condition-' + this.condition_id );

			/**
			 * Defines type_name and condition_name based on type and id so that it can be used in the template later on
			 *
			 * @type {Object}
			 * @private
			 */
			this._defaults = {
				type_name:   optin_vars.messages.settings[ this.type ] ? optin_vars.messages.settings[ this.type ] : this.type,
				condition_name: optin_vars.messages.conditions[ this.condition_id ] ? optin_vars.messages.conditions[ this.condition_id ] : this.condition_id
			};

			this.data = this.get_data();

			this.render();
			this.events = $.extend( true, {}, this.events, this._events );
			this.delegateEvents();
			if( this.on_init && _.isFunction( this.on_init ) )
				this.on_init.apply( this, arguments );
			return this;
		},
		get_data: function(){
			return _.extend( {}, this._defaults, this.defaults, this.model.get( this.condition_id ), {type: this.type } );
		},
		get_title: function(){
			return this.title.replace("{type_name}", this.data.type_name);
		},
		get_body: function(){
			return typeof this.body === "function" ? this.body.apply(this, arguments ) : this.body.replace("{type_name}", this.data.type_name );
		},
		get_header: function(){
			return "";
		},
		render: function(){
			this.$el.html('');

			var html = this._template(_.extend({}, {
					title: this.get_title(),
					body: this.get_body(),
					header: this.get_header()
				},
				this._defaults,
				{type: this.type}
			) );

			this.$el.html( html );

			$('.wph-conditions--box .wph-conditions--item:not(:last-child)')
				.removeClass( "wph-conditions--open" )
				.addClass( "wph-conditions--closed" );
			$('.wph-conditions--box .wph-conditions--item:not(:last-child) section').hide();
			
			if( this.rendered && typeof this.rendered === "function")
				this.rendered.apply(this, arguments);

			return this;
		},
		/**
		 * Updates attribute value into the condition hash
		 *
		 * @param attribute
		 * @param val
		 */
		update_attribute: function(attribute, val){
			this.data = this.model.get( this.condition_id  );
			this.data[ attribute ] = val;
			this.model.set(this.condition_id , this.data );

		},
		get_attribute: function(attribute){
			var data = this.model.get( this.condition_id );
			return data && data[ attribute ] ? data[ attribute ] : false;
		},
		refresh_label: function(){
			this.$el.find('.wph-condition--preview').html('');
			var html =  this.get_header();
			this.$el.find('.wph-condition--preview').html(html);;
		},
		/**
		 * Triggered on input change
		 *
		 * @param e
		 * @returns {*}
		 */
		change_input: function(e){
			var el = e.target,
				attribute = el.getAttribute("data-attribute"),
				$el = $(el),
				val = $el.is(".js-wpoi-select") ? $el.val() : e.target.value;

			// skip for input search
			if ( $el.is(".select2-search__field") ) return false;

			var updated = this.update_attribute( attribute, val );

			this.refresh_label();
			return updated;
		},
		/**
		 * Returns configs of condition
		 *
		 * @returns bool true
		 */
		get_configs: function(){
			return this.defaults || true;
		}
	});

	var reenable_scroll = function(e){
		/**
		 * reenable scrolling for the container
		 * select2 disables scrolling after select so we reenable it
		 */
		$(".wph-conditions--items").data("select2ScrollPosition", {});
	},
	either_all_or_others = function(e){
		var val = ["all"];
		if( e.params && e.params.args && e.params.args.data && e.params.args.data.id && "all" === e.params.args.data.id ){

		}else{
			val = $(this).val();
			if( val && -1 !== val.indexOf( "all" ) )
				val.splice( val.indexOf( "all" ), 1 );
			else
				val = ( val || [] ).concat( [e.params.args.data.id ] );

			if( !val || !val.length )
				val = [ e.params.args.data.id ];
		}

		$(this).val(val).trigger("change");
	},
	Toggle_Button_Toggler_Mixin = {
		events: {
			"change input[type='radio']": "set_current_li"
		},
		set_current_li: function(e){
			var $this = $(e.target),
				$li = $this.closest("li");

			$li.siblings().removeClass("current");
			$li.toggleClass( "current",  $this.is(":checked") );
		}
	};

	/**
	 * Posts
	 */
	Optin.View.Conditions.posts = Condition_Base.extend(_.extend( {}, Toggle_Button_Toggler_Mixin, {
		condition_id: "posts",
		title: optin_vars.messages.conditions.posts,
		label: optin_vars.messages.condition_labels.posts,
		defaults: {
			filter_type: "only", // except | only
			posts: []
		},
		on_init: function(){
			this.listenTo(this.model, "change", this.render );
			this.update_label();
		},
		get_header: function(){
			this.update_label();
			this.trigger("change:update_label", this);
			if( _.contains(  this.get_attribute( "posts" ), "all" ) )
				return this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_posts : optin_vars.messages.condition_labels.no_posts;

			if( this.get_attribute( "posts" ).length ) {
				return ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.only_on_these_posts : optin_vars.messages.condition_labels.except_these_posts ).replace("{number}",  this.get_attribute( "posts" ).length );
			} else {
				return ( this.get_attribute("filter_type") === "only" ) ? optin_vars.messages.condition_labels.no_posts : optin_vars.messages.condition_labels.all_posts;
			}
		},
		update_label: function(){
			if ( this.get_attribute( "posts" ).length && !_.contains(  this.get_attribute( "posts" ), "all" ) ) {
				this.label = ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.number_posts : optin_vars.messages.condition_labels.except_these_posts ).replace("{number}",  this.get_attribute( "posts" ).length ? this.get_attribute( "posts" ).length : 0 );
			} else {
				if( _.contains(  this.get_attribute( "posts" ), "all" ) ) {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_posts : optin_vars.messages.condition_labels.no_posts;
				} else {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.no_posts : optin_vars.messages.condition_labels.all_posts;
				}
			}
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select').wpmuiSelect({
				tags: "true",
				width : "100%",
				createTag: function(){ return false; }
			})
			.on('select2:selecting', either_all_or_others )
			.on('select2:selecting', reenable_scroll )
			.on('select2:unselecting', reenable_scroll);

		}
	}) );

	/**
	 * Pages
	 */
	Optin.View.Conditions.pages = Condition_Base.extend(_.extend( {}, Toggle_Button_Toggler_Mixin, {
		condition_id: "pages",
		title: optin_vars.messages.conditions.pages,
		label: optin_vars.messages.condition_labels.pages,
		defaults: {
			filter_type: "only", // except | only
			pages: []
		},
		on_init: function(){
			this.listenTo(this.model, "change", this.render );
			this.update_label();
		},
		get_header: function(){
			this.update_label();
			this.trigger("change:update_label", this);
			if( _.contains(  this.get_attribute( "pages" ), "all" ) )
				return this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_pages : optin_vars.messages.condition_labels.no_pages;

			if( this.get_attribute( "pages" ).length ) {
				return ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.only_on_these_pages : optin_vars.messages.condition_labels.except_these_pages ).replace("{number}",  this.get_attribute( "pages" ).length );
			} else {
				return ( this.get_attribute("filter_type") === "only" ) ? optin_vars.messages.condition_labels.no_pages : optin_vars.messages.condition_labels.all_pages;
			}
		},
		update_label: function(){
			if ( this.get_attribute( "pages" ).length && !_.contains(  this.get_attribute( "pages" ), "all" ) ) {
				this.label = ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.number_pages : optin_vars.messages.condition_labels.except_these_pages ).replace("{number}",  this.get_attribute( "pages" ).length ? this.get_attribute( "pages" ).length : 0 );
			} else {
				if( _.contains(  this.get_attribute( "pages" ), "all" ) ) {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_pages : optin_vars.messages.condition_labels.no_pages;
				} else {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.no_pages : optin_vars.messages.condition_labels.all_pages;
				}
			}
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select').wpmuiSelect({
					tags: "true",
					width : "100%",
					createTag: function(){ return false; }
				})
			.on('select2:selecting', either_all_or_others )
			.on('select2:selecting', reenable_scroll )
			.on('select2:unselecting', reenable_scroll);

		}
	}));

	/**
	 * Custom Post Types
	 */
	_.each( optin_vars.post_types, function( cpt_details, cpt ) {
		var cpt_name = cpt_details.label.toLowerCase();
		Optin.View.Conditions[cpt_details.label] = Condition_Base.extend(_.extend( {}, Toggle_Button_Toggler_Mixin, {
			condition_id: cpt_details.label,
			title: cpt_details.label,
			label: optin_vars.messages.condition_labels.posts,
			cpt: true,
			defaults: {
				filter_type: "only", // except | only
				selected_cpts: [],
				post_type: cpt,
				post_type_label: cpt_details.label,
			},
			on_init: function(){
				this.listenTo(this.model, "change", this.render );
				this.update_label();
			},
			get_header: function(){
				this.update_label();
				this.trigger("change:update_label", this);
				if( _.contains(  this.get_attribute( "selected_cpts" ), "all" ) )
					return this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all + " " + cpt_name : optin_vars.messages.condition_labels.no + " " + cpt_name;

				if( this.get_attribute( "selected_cpts" ).length ) {
					return ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.only_on_these_posts : optin_vars.messages.condition_labels.except_these_posts ).replace("{number}",  this.get_attribute( "selected_cpts" ).length ).replace("posts", cpt_name);
				} else {
					return ( this.get_attribute("filter_type") === "only" ) ? optin_vars.messages.condition_labels.no + " " + cpt_name : optin_vars.messages.condition_labels.all + " " + cpt_name;
				}
			},
			update_label: function(){
				if ( this.get_attribute( "selected_cpts" ).length && !_.contains(  this.get_attribute( "selected_cpts" ), "all" ) ) {
					this.label = ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.number_posts : optin_vars.messages.condition_labels.except_these_posts ).replace("{number}",  this.get_attribute( "selected_cpts" ).length ? this.get_attribute( "selected_cpts" ).length : 0 ).replace("posts", cpt_name);
				} else {
					if( _.contains(  this.get_attribute( "selected_cpts" ), "all" ) ) {
						this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all + " " + cpt_name : optin_vars.messages.condition_labels.no + " " + cpt_name;
					} else {
						this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.no + " " + cpt_name : optin_vars.messages.condition_labels.all + " " + cpt_name;
					}
				}
			},
			body: function(){
				return this.template( this.get_data() );
			},
			rendered: function(){
				this.$('.js-wpoi-select').wpmuiSelect({
					tags: "true",
					width : "100%",
					createTag: function(){ return false; }
				})
				.on('select2:selecting', either_all_or_others )
				.on('select2:selecting', reenable_scroll )
				.on('select2:unselecting', reenable_scroll);
			}
		}) );
	});

	/**
	 * Categories
	 */
	Optin.View.Conditions.categories = Condition_Base.extend(_.extend( {}, Toggle_Button_Toggler_Mixin, {
		condition_id: "categories",
		title: optin_vars.messages.conditions.categories,
		label: optin_vars.messages.condition_labels.categories,
		defaults: {
			filter_type: "only", // except | only
			categories: []
		},
		on_init: function(){
			this.listenTo(this.model, "change", this.render );
			this.update_label();
		},
		get_header: function(){
			this.update_label();
			this.trigger("change:update_label", this);

			if( _.contains(  this.get_attribute( "categories" ), "all" ) )
				return this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_categories : optin_vars.messages.condition_labels.no_categories;

			if( this.get_attribute( "categories" ).length ) {
				return ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.only_on_these_categories : optin_vars.messages.condition_labels.except_these_categories ).replace("{number}",  this.get_attribute( "categories" ).length );
			} else {
				return ( this.get_attribute("filter_type") === "only" ) ? optin_vars.messages.condition_labels.no_categories : optin_vars.messages.condition_labels.all_categories;
			}
		},
		update_label: function(){
			if ( this.get_attribute( "categories" ).length && !_.contains(  this.get_attribute( "categories" ), "all" ) ) {
				this.label = ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.number_categories : optin_vars.messages.condition_labels.except_these_categories ).replace("{number}",  this.get_attribute( "categories" ).length ? this.get_attribute( "categories" ).length : 0 );
			} else {
				if( _.contains(  this.get_attribute( "categories" ), "all" ) ) {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_categories : optin_vars.messages.condition_labels.no_categories;
				} else {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.no_categories : optin_vars.messages.condition_labels.all_categories;
				}
			}
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select').wpmuiSelect({
					tags: "true",
					width : "100%",
					createTag: function(){ return false; }
			})
			.on('select2:selecting', reenable_scroll )
			.on('select2:unselecting', reenable_scroll);
		}
	}));

	/**
	 * Tags
	 */
	Optin.View.Conditions.tags = Condition_Base.extend(_.extend( {}, Toggle_Button_Toggler_Mixin, {
		condition_id: "tags",
		title: optin_vars.messages.conditions.tags,
		label: optin_vars.messages.condition_labels.tags,
		defaults: {
			filter_type: "only", // except | only
			tags: []
		},
		on_init: function(){
			this.listenTo(this.model, "change", this.render );
			this.update_label();
		},
		get_header: function(){
			this.update_label();
			this.trigger("change:update_label", this);
			if( _.contains(  this.get_attribute( "tags" ), "all" ) )
				return this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_tags : optin_vars.messages.condition_labels.no_tags;

			if( this.get_attribute( "tags" ).length ) {
				return ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.only_on_these_tags : optin_vars.messages.condition_labels.except_these_tags ).replace("{number}",  this.get_attribute( "tags" ).length );
			} else {
				return ( this.get_attribute("filter_type") === "only" ) ? optin_vars.messages.condition_labels.no_tags : optin_vars.messages.condition_labels.all_tags;
			}
		},
		update_label: function(){
			if ( this.get_attribute( "tags" ).length && !_.contains(  this.get_attribute( "tags" ), "all" ) ) {
				this.label = ( this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.number_tags : optin_vars.messages.condition_labels.except_these_tags ).replace("{number}",  this.get_attribute( "tags" ).length ? this.get_attribute( "tags" ).length : 0 );
			} else {
				if( _.contains(  this.get_attribute( "tags" ), "all" ) ) {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.all_tags : optin_vars.messages.condition_labels.no_tags;
				} else {
					this.label =  this.get_attribute("filter_type") === "only" ? optin_vars.messages.condition_labels.no_tags : optin_vars.messages.condition_labels.all_tags;
				}
			}
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select').wpmuiSelect({
					tags: "true",
					width : "100%",
					createTag: function(){ return false; }
			})
			.on('select2:selecting', reenable_scroll )
			.on('select2:unselecting', reenable_scroll);
		}
	}));

	Optin.View.Conditions.only_on_not_found = Condition_Base.extend({
		condition_id: "only_on_not_found",
		title: optin_vars.messages.conditions.only_on_not_found,
		label: optin_vars.messages.condition_labels.only_on_not_found,
		body: optin_vars.messages.conditions_body.only_on_not_found
	});

	Optin.View.Conditions.visitor_logged_in = Condition_Base.extend({
		condition_id: "visitor_logged_in",
		disable: ['visitor_not_logged_in'],
		title: optin_vars.messages.conditions.visitor_logged_in,
		label: optin_vars.messages.condition_labels.visitor_logged_in,
		body: optin_vars.messages.conditions_body.visitor_logged_in
	});

	Optin.View.Conditions.visitor_not_logged_in = Condition_Base.extend({
		condition_id: "visitor_not_logged_in",
		disable: ['visitor_logged_in'],
		title: optin_vars.messages.conditions.visitor_not_logged_in,
		label: optin_vars.messages.condition_labels.visitor_not_logged_in,
		body: optin_vars.messages.conditions_body.visitor_not_logged_in
	});

	Optin.View.Conditions.shown_less_than = Condition_Base.extend({
		condition_id: "shown_less_than",
		title: optin_vars.messages.conditions.shown_less_than,
		label: optin_vars.messages.condition_labels.shown_less_than,
		defaults: {
			less_than: 1
		},
		body: function(){
			return this.template( this.get_data() );
		}
	});

	Optin.View.Conditions.only_on_mobile = Condition_Base.extend({
		condition_id: "only_on_mobile",
		disable: ['not_on_mobile'],
		title: optin_vars.messages.conditions.only_on_mobile,
		label: optin_vars.messages.condition_labels.only_on_mobile,
		body: optin_vars.messages.conditions_body.only_on_mobile
	});

	Optin.View.Conditions.not_on_mobile = Condition_Base.extend({
		condition_id: "not_on_mobile",
		disable: ['only_on_mobile'],
		title: optin_vars.messages.conditions.not_on_mobile,
		label: optin_vars.messages.condition_labels.not_on_mobile,
		body: optin_vars.messages.conditions_body.not_on_mobile
	});

	/**
	 * From a specific referrer
	 */
	Optin.View.Conditions.from_specific_ref = Condition_Base.extend({
		condition_id: "from_specific_ref",
		disable: ['not_from_specific_ref'],
		title: optin_vars.messages.conditions.from_specific_ref,
		label: optin_vars.messages.condition_labels.from_specific_ref,
		defaults: {
			refs: ""
		},
		body: function(){
			return this.template( this.get_data() );
		}
	});

	/**
	 * Not from a specific referrer
	 */
	Optin.View.Conditions.not_from_specific_ref = Condition_Base.extend({
		condition_id: "not_from_specific_ref",
		disable: ['from_specific_ref'],
		title: optin_vars.messages.conditions.not_from_specific_ref,
		label: optin_vars.messages.condition_labels.not_from_specific_ref,
		defaults: {
			refs: ""
		},
		body: function(){
			return this.template( this.get_data() );
		}
	});

	/**
	 * Not from an internal link
	 */
	Optin.View.Conditions.not_from_internal_link = Condition_Base.extend({
		condition_id: "not_from_internal_link",
		title: optin_vars.messages.conditions.not_from_internal_link,
		label: optin_vars.messages.condition_labels.not_from_internal_link,
		body: optin_vars.messages.conditions_body.not_from_internal_link
	});

	/**
	 * From a search engine
	 */
	Optin.View.Conditions.from_search_engine = Condition_Base.extend({
		condition_id: "from_search_engine",
		title: optin_vars.messages.conditions.from_search_engine,
		label: optin_vars.messages.condition_labels.from_search_engine,
		body: optin_vars.messages.conditions_body.from_search_engine
	});

	/**
	 * Site is not a Pro Site
	 */
	//Optin.View.Conditions.not_a_pro_site = Condition_Base.extend({
	//    condition_id: "not_a_pro_site",
	//    title: "Site is not a Pro Site",
	//    body: "Shows the Pop Up if the site is not a Pro Site."
	//});

	/**
	 * On specific URL
	 */
	Optin.View.Conditions.on_specific_url = Condition_Base.extend({
		condition_id: "on_specific_url",
		disable: ['not_on_specific_url'],
		title:  optin_vars.messages.conditions.on_specific_url,
		label:  optin_vars.messages.condition_labels.on_specific_url,
		defaults: {
			urls: ""
		},
		body: function(){
			return this.template( this.get_data() );
		}
	});

	/**
	 * Not on specific URL
	 */
	Optin.View.Conditions.not_on_specific_url = Condition_Base.extend({
		condition_id: "not_on_specific_url",
		disable: ['on_specific_url'],
		title: optin_vars.messages.conditions.not_on_specific_url,
		label: optin_vars.messages.condition_labels.not_on_specific_url,
		defaults: {
			urls: ""
		},
		body: function(){
			return this.template( this.get_data() );
		}
	});

	/**
	 * Visitor has commented before
	 */
	Optin.View.Conditions.visitor_has_commented = Condition_Base.extend({
		condition_id: "visitor_has_commented",
		disable: ['visitor_has_never_commented'],
		title: optin_vars.messages.conditions.visitor_has_commented,
		label: optin_vars.messages.condition_labels.visitor_has_commented,
		body: optin_vars.messages.conditions_body.visitor_has_commented
	});

	/**
	 * Visitor has never commented
	 */
	Optin.View.Conditions.visitor_has_never_commented = Condition_Base.extend({
		condition_id: "visitor_has_never_commented",
		disable: ['visitor_has_commented'],
		title: optin_vars.messages.conditions.visitor_has_never_commented,
		label: optin_vars.messages.condition_labels.visitor_has_never_commented,
		body: optin_vars.messages.conditions_body.visitor_has_never_commented
	});

	/**
	 * In a specific Country
	 */
	Optin.View.Conditions.in_a_country = Condition_Base.extend({
		condition_id: "in_a_country",
		disable: ['not_in_a_country'],
		title: optin_vars.messages.conditions.in_a_country,
		label: optin_vars.messages.condition_labels.in_a_country,
		defaults: {
			countries: ""
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select')
				.val( this.get_attribute( "countries" ) )
				.wpmuiSelect()
				.on('select2:selecting', reenable_scroll )
				.on('select2:unselecting', reenable_scroll);
		}
	});

	/**
	 * Not in a specific Country
	 */
	Optin.View.Conditions.not_in_a_country = Condition_Base.extend({
		condition_id: "not_in_a_country",
		disable: ['in_a_country'],
		title: optin_vars.messages.conditions.not_in_a_country,
		label: optin_vars.messages.condition_labels.not_in_a_country,
		defaults: {
			countries: ""
		},
		body: function(){
			return this.template( this.get_data() );
		},
		rendered: function(){
			this.$('.js-wpoi-select')
				.val( this.get_attribute( "countries" ) )
				.wpmuiSelect()
				.on('select2:selecting', reenable_scroll )
				.on('select2:unselecting', reenable_scroll);
		}
	});


}( jQuery ));
