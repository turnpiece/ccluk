(function ($) {
	window.empty = function (what) { return "undefined" === typeof what ? true : !what; };
	window.count = function (what) { return "undefined" === typeof what ? 0 : (what && what.length ? what.length : 0); };
	window.stripslashes = function (what) {
		return (what + '')
		.replace(/\\(.?)/g, function (s, n1) {
			switch (n1) {
				case '\\':
					return '\\'
				case '0':
					return '\u0000'
				case '':
					return ''
				default:
					return n1
			}
		});
	};
	window.forminator_array_value_exists = function ( array, key ) {
		return ( !_.isUndefined( array[ key ] ) && ! _.isEmpty( array[ key ] ) );
	};
	window.decodeHtmlEntity = function(str) {
		if( typeof str === "undefined" ) return str;

		return str.replace( /&#(\d+);/g, function( match, dec ) {
			return String.fromCharCode( dec );
		});
	};
	window.encodeHtmlEntity = function(str) {
		if( typeof str === "undefined" ) return str;
		var buf = [];
		for ( var i=str.length-1; i>=0; i-- ) {
			buf.unshift( ['&#', str[i].charCodeAt(), ';'].join('') );
		}
		return buf.join('');
	};

	define([], function () {
		var Utils = {
			/*
			 * Returns if touch device ( using wp_is_mobile() )
			 */
			is_touch: function () {
				return Forminator.Data.is_touch;
			},

			/*
			 * Returns if window resized for browser
			 */
			is_mobile_size: function () {
				if ( window.screen.width <= 782 ) return true;

				return false;
			},

			/*
			 * Return if touch or windows mobile width
			 */
			is_mobile: function () {
				if( Forminator.Utils.is_touch() || Forminator.Utils.is_mobile_size() ) return true;

				return false;
			},

			/*
			 * Convert model to JSON
			 */
			model_to_json: function ( model ) {
				if ( !model ) return {};
				var raw = ( model && model.toJSON ? model.toJSON() : model ),
					data_str = JSON.stringify( raw ),
					json = JSON.parse( data_str )
				;

				return json;
			},

			/*
			 * Extend default underscore template with mustache style
			 */
			template: function( markup ) {
				// Each time we re-render the dynamic markup we initialize mustache style
				_.templateSettings = {
					evaluate : /\{\[([\s\S]+?)\]\}/g,
					interpolate : /\{\{([\s\S]+?)\}\}/g
				};

				return _.template( markup );
			},

			/*
			 * Extend default underscore template with PHP
			 */
			template_php: function( markup ) {
				var oldSettings = _.templateSettings,
				tpl = false;

				_.templateSettings = {
					interpolate : /<\?php echo (.+?) \?>/g,
					evaluate: /<\?php (.+?) \?>/g
				};

				tpl = _.template(markup);

				_.templateSettings = oldSettings;

				return function(data){
					_.each(data, function(value, key){
						data['$' + key] = value;
					});

					return tpl(data);
				};
			},

			/*
			 * Returns uniq ID
			 */
			get_unique_id: function ( pref ) {
				var prefix = pref || "field",
					time = ( new Date() ).getTime(),
					random = Math.floor( ( Math.random() * 999 ) + 1000 )
				;

				return prefix + "-" + time + "-" + random;
			},

			/*
			 * Returns if form has pagination field
			 */
			has_pagination: function ( wrappers ) {
				var hasPagination = false;
				wrappers.each( function ( wrapper ) {
					var fields = wrapper.get( 'fields' );
					fields.each( function ( field ) {
						if( "pagination" === field.get( 'type' ) ) {
							hasPagination = true;
						}
					});
				});

				return hasPagination;
			},


			/**
			 * Get fields as models
			 * @param wrappers
			 * @param excludeIds
			 * @returns {Array}
			 */
			get_fields_models: function ( wrappers, excludeIds ) {
				var fieldsModels = [];

				if( _.isUndefined( excludeIds ) ) {
					excludeIds = [];
				}

				// Loop all wrappers we have
				wrappers.each( function ( wrapper ) {
					var fields = wrapper.get( 'fields' );
					fields.each( function ( field ) {
						if(!_.contains(excludeIds, field.cid)) {
							fieldsModels.push(field);
						}

					});
				});

				return fieldsModels;
			},

			/*
			 * Returns builder fields from wrappers
			 */
			get_fields: function ( wrappers, disabledFields ) {
				var self = this,
					fieldsArray = []
				;

				if( _.isUndefined( disabledFields ) ) {
					disabledFields = [
						'address',
						'pagination',
						'postdata',
						'total',
						'upload',
						'product',
						'captcha'
					];
				}

				// Loop all wrappers we have
				wrappers.each( function ( wrapper ) {
					var fields = wrapper.get( 'fields' );
					fields.each( function ( field ) {

						// Check if field is disabled
						if( _.contains( disabledFields, field.get('type') ) ) return;

						var label;

						if( !_.isUndefined( field.get( 'field_label' ) ) && !_.isEmpty( field.get( 'field_label' ) ) ) {
							label = field.get( 'field_label' );
						} else {
							label = field.get( 'type' );
						}



						if(field.get('type') === 'name') {
							fieldsArray = fieldsArray.concat(Forminator.Utils.get_name_fields(field, label));
						} else {
							fieldsArray.push({
								element_id: field.get( 'element_id' ),
								label: label,
								values: Forminator.Utils.get_field_values( field ),
								hasOptions: Forminator.Utils.field_has_options( field ),
								isNumber: Forminator.Utils.field_has_number( field )
							});
						}


					});
				});

				return fieldsArray;
			},

			/**
			 * Get name_fields (support multiple name fields)
			 * @param field
			 * @param fieldLabel
			 * @returns {Array}
			 */
			get_name_fields: function (field, fieldLabel) {
				var fieldsArray = [];
				//handle multiple name
				if (field.get('multiple_name') === "true") {

					_.each([{
						attr: "prefix",
						label: "prefix_label",
						element_suffix: "prefix",
						hasOptions: true,
						values: [
							{label: "Mr.", value: "Mr"},
							{label: "Mrs.", value: "Mrs"},
							{label: "Ms.", value: "Ms"},
							{label: "Miss", value: "Miss"},
							{label: "Dr.", value: "Dr"},
							{label: "Prof.", value: "Prof"}
						],
						isNumber: false

					}, {
						attr: "fname",
						label: "fname_label",
						element_suffix: "first-name",
						hasOptions: false,
						values: false,
						isNumber: false
					}, {
						attr: "mname",
						label: "mname_label",
						element_suffix: "middle-name",
						hasOptions: false,
						values: false,
						isNumber: false
					},
						{
							attr: "lname",
							label: "lname_label",
							element_suffix: "last-name",
							hasOptions: false,
							values: false,
							isNumber: false
						}], function (attribute) {
						if (field.get(attribute.attr) === "true") {
							var label;
							if (!_.isUndefined(field.get(attribute.label)) && !_.isEmpty(field.get(attribute.label))) {
								label = fieldLabel + ' - ' + field.get(attribute.label);
							} else {
								label = fieldLabel + ' - ' + Forminator.l10n.name[attribute.label];
							}

							fieldsArray.push({
								element_id: field.get('element_id') + '-' + attribute.element_suffix,
								label: label,
								values: attribute.values,
								hasOptions: attribute.hasOptions,
								isNumber: attribute.isNumber,
							});
						}
					});
				} else {
					fieldsArray.push({
						element_id: field.get( 'element_id' ),
						label: fieldLabel,
						values: Forminator.Utils.get_field_values( field ),
						hasOptions: Forminator.Utils.field_has_options( field ),
						isNumber: Forminator.Utils.field_has_number( field )
					});
				}

				return fieldsArray;
			},

			/*
			 * Returns builder product fields
			 */
			get_products: function ( wrappers ) {
				var self = this,
					fieldsArray = []
				;

				// Loop all wrappers we have
				wrappers.each( function ( wrapper ) {
					var fields = wrapper.get( 'fields' );
					fields.each( function ( field ) {
						// Check if field is disabled
						if( field.get( 'type' ) !== "product" ) return;

						var label;

						if( !_.isUndefined( field.get( 'product_name' ) ) && !_.isEmpty( field.get( 'product_name' ) ) ) {
							label = field.get( 'product_name' );
						} else {
							label = field.get( 'type' );
						}

						fieldsArray.push({
							element_id: field.get( 'element_id' ),
							label: label
						});
					});
				});

				return fieldsArray;
			},

			/*
			 * Returns if field has options
			 */
			field_has_options: function ( field ) {
				if( field.get( 'type' ) === 'select' || field.get( 'type' ) === 'checkbox' ) return true;

				return false;
			},

			/*
			 * Returns if field has number
			 */
			field_has_number: function ( field ) {
				if( field.get( 'type' ) === 'number' || field.get( 'type' ) === 'phone' ) return true;

				return false;
			},

			/*
			 * Returns field values
			 */
			get_field_values: function ( field ) {
				var type = field.get( 'type' );

				if( type === 'select' || type === 'checkbox' ) return field.get( 'options' );

				return false;
			},

			/*
			 * Returns slug from title
			 */
			get_slug: function ( title ) {
				title = title.replace( ' ', '-' );
				title = title.replace( /[^-a-zA-Z0-9]/, '' );
				return title;
			},

			/*
			 * Returns slug from title
			 */
			sanitize_uri_string: function ( string ) {
				// Decode URI components
				var decoded = decodeURIComponent( string );

				// Replace interval with -
				decoded = decoded.replace( /-/g, ' ' );

				return decoded;
			},

			/*
			 * Return URL param value
			 */
			get_url_param: function ( param ) {
				var page_url = window.location.search.substring(1),
					url_params = page_url.split('&')
				;

				for ( var i = 0; i < url_params.length; i++ ) {
					var param_name = url_params[i].split('=');
					if ( param_name[0] === param ) {
						return param_name[1];
					}
				}

				return false;
			},

			append_select2: function ($el, model) {

				// SELECT2 wpmudev-ui
				$el.find( ".wpmudev-select, .wpmudev-form-field--select" ).each(function(){
					//check if its search allowable
					var allowSearch = $(this).data('allow-search');
					var select2Options = {
						allowClear: false,
						containerCssClass: "wpmudev-select2",
						dropdownCssClass: "wpmudev-select-dropdown"
					};
					if(allowSearch !== 1) {
						select2Options.minimumResultsForSearch = Infinity;
					}
					$( this ).wpmuiSelect(select2Options);
				});

				// SELECT2 forminator-ui
				$el.find( ".wpmudev-option--select" ).wpmuiSelect({
					allowClear: false,
					minimumResultsForSearch: Infinity,
					containerCssClass: "wpmudev-option--select2",
					dropdownCssClass: "wpmudev-option--select2-dropdown"
				});

				$el.find( ".forminator-select" ).wpmuiSelect({
					allowClear: false,
					containerCssClass: "forminator-select2",
					dropdownCssClass: "forminator-dropdown"
				});

			},
			/*
			 * Initialize Select 2
			 */
			init_select2: function(){

				setTimeout( function(){

					// SELECT2 wpmudev-ui
					$( ".wpmudev-select, .wpmudev-form-field--select" ).each(function(){
						//check if its search allowable
						var allowSearch = $(this).data('allow-search');
						var select2Options = {
							allowClear: false,
							containerCssClass: "wpmudev-select2",
							dropdownCssClass: "wpmudev-select-dropdown"
						};
						if(allowSearch !== 1) {
							select2Options.minimumResultsForSearch = Infinity;
						}
						$( this ).wpmuiSelect(select2Options);
					});

					// SELECT2 forminator-ui
					$( ".wpmudev-option--select" ).wpmuiSelect({
						allowClear: false,
						minimumResultsForSearch: Infinity,
						containerCssClass: "wpmudev-option--select2",
						dropdownCssClass: "wpmudev-option--select2-dropdown"
					});

					$( ".forminator-select" ).wpmuiSelect({
						allowClear: false,
                		containerCssClass: "forminator-select2",
                		dropdownCssClass: "forminator-dropdown"
					});

				}, 10 );

			}
		};

		var Popup = {
			$popup: {},
			_deferred: {},

			initialize: function () {

				if ( ! $( "#forminator-popup" ).length ) {

					$( "#wpmudev-main" )
					.append( '<div id="forminator-popup" class="wpmudev-modal">' +

						'<div class="wpmudev-modal-mask" aria-hidden="true"></div>' +

						'<div class="wpmudev-box">' +

							'<div class="wpmudev-box-header">' +

								'<div class="wpmudev-header--text">' +
									'<h2 class="wpmudev-subtitle">' + this.title + '</h2>' +
								'</div>' +

								'<div class="wpmudev-header--action">' +
									'<button id="forminator-popup-close" class="wpmudev-box--action"><span class="wpmudev-icon--close"></span></button>' +
								'</div>' +

							'</div>' +

							'<div class="wpmudev-box-section"></div>' +

						'</div>' +

					'</div>' );

				} else {
					this.$popup.remove();
					this.initialize();
				}

				this.$popup = $( "#forminator-popup" );

				this.$popup.find( "#wpmudev-box-body" ).empty();

			},

			open: function ( callback, data, className ) {
				this.data = data;
				this.title = '';

				if( typeof this.data !== "undefined" &&
					typeof this.data.title !== "undefined" ) {
					this.title = this.data.title;
				}

				this.initialize();

				var self = this,
					$content = this.$popup.find( ".wpmudev-box" ),
					close_click = function () {
						self.close();
						return false;
					}
				;

				// Add custom class
				if ( className ) {
					this.$popup
						.addClass( className )
					;
				}

				// Add closing event
				this.$popup.find( "#forminator-popup-close" ).on( "click", close_click );

				this.$popup.addClass( "wpmudev-modal-active" );
				$( "body" ).addClass( "wpmudev-modal-is_active" );

				setTimeout( function () {
					$content.addClass( "wpmudev-show" );
				});

				callback.apply( this.$popup.find( ".wpmudev-box-section" ).get(), data );

				// Add closing event
				this.$popup.find( ".forminator-delete-module-cancel" ).on( "click", close_click );

				this._deferred = new $.Deferred();
				return this._deferred.promise();

			},

			close: function ( result, callback ) {

				var $popup = $( "#forminator-popup" ),
					$content = $popup.find( ".wpmudev-box" );

				$content.removeClass( "wpmudev-show" ).addClass( "wpmudev-hide" );

				setTimeout(function() {
					$popup.removeClass( 'wpmudev-modal-active' );
					$( 'body' ).removeClass( 'wpmudev-modal-is_active' );
					$content.removeClass( 'wpmudev-hide' );

					if( callback ) {
						callback.apply();
					}
				}, 1000);

				this._deferred.resolve( this.$popup, result );

			}
		};

		var Notification = {
			$notification: {},
			_deferred: {},

			initialize: function () {

				if ( ! $( "#forminator-notification" ).length ) {

					$( "#wpmudev-main" )
						.append( '<div id="forminator-notification" class="wpmudev-notification">' +
							'<label class="wpmudev-label--' + this.type + '"><span>' + this.text + '</span></label>' +
							'</div>' );

				} else {
					this.$notification.remove();
					this.initialize();
				}

				this.$notification = $( "#forminator-notification" );
			},

			open: function ( type, text, closeTime ) {
				var self = this;

				this.type = type || 'notice';
				this.text = text;

				this.initialize();

				setTimeout( function () {
					self.$notification.addClass( "wpmudev-show" );
				});

				if( ! _.isUndefined( closeTime ) ) {
					setTimeout( function () {
						self.close();
					}, closeTime );
				}

				this._deferred = new $.Deferred();
				return this._deferred.promise();

			},

			close: function ( result ) {

				var $popup = $( "#forminator-notification" );

				$popup.removeClass( "wpmudev-show" ).addClass( "wpmudev-hide" );

				this._deferred.resolve( this.$popup, result );

			}
		};

		var Pagination = {
			step: 0,
			totalSteps: 0,
			hasStep: false,
			$el: false,
			init: function ($el) {
				this.step = 0;
				this.totalSteps = 0;
				this.hasStep = false;
				this.$el = $el;

				this.totalSteps = this.$el.find('.forminator-pagination').length;

				if (!this.totalSteps) return;

				if (this.hasStep && this.step > 0) {
					// Hide all parts except current one
					$el.find('.forminator-pagination').not('[data-step=' + this.step + ']').hide();
				} else {
					// Hide all parts except first one
					$el.find('.forminator-pagination').not('.forminator-pagination-start').hide();
				}

				this.render_navigation();
				this.render_footer_navigation();
				this.init_events();
				this.update_buttons();
				this.update_navigation();
			},

			init_events: function () {
				var self = this;
				this.$el.find('.forminator-pagination-prev').click(function (e) {
					e.preventDefault();
					self.handle_click('prev');
				});
				this.$el.find('.forminator-pagination-next').click(function (e) {
					e.preventDefault();
					self.handle_click('next');
				});
			},

			render_footer_navigation: function () {
				this.$el.append('<div class="forminator-pagination--footer">' +
					'<button class="forminator-button forminator-pagination-prev"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + Forminator.l10n.appearance.pagination_prev + '</span></button>' +
					'<button class="forminator-button forminator-pagination-next"><span class="forminator-button--mask" aria-label="hidden"></span><span class="forminator-button--text">' + Forminator.l10n.appearance.pagination_next + '</span></button>' +
					'</div>');
			},

			render_navigation: function () {
				var $navigation = this.$el.find('.forminator-pagination--nav');

				if (!$navigation.length) return;

				var steps = this.$el.find('.forminator-pagination').not('.forminator-pagination-start');

				steps.each(function () {
					var $step = $(this),
						label = $step.data('label'),
						step = $step.data('step') - 1
					;

					$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
						'<span class="forminator-step-text">' + label + '</span>' +
						'<span class="forminator-step-dot" aria-label="hidden"></span>' +
						'</li>'
					);
				});

				var finalSteps = this.$el.find('.forminator-pagination-start');

				finalSteps.each(function () {
					var $step = $(this),
						label = $step.data('label'),
						step = steps.length
					;

					$navigation.append('<li class="forminator-nav-step forminator-nav-step-' + step + '">' +
						'<span class="forminator-step-text">' + label + '</span>' +
						'<span class="forminator-step-dot" aria-label="hidden"></span>' +
						'</li>'
					);
				});
			},

			handle_click: function (type) {
				if (type === "prev" && this.step !== 0) {
					this.go_to(this.step - 1);
				} else if (type === "next") {
					this.go_to(this.step + 1);
				}

				this.update_buttons();
			},

			update_buttons: function () {
				if (this.step === 0) {
					this.$el.find('.forminator-pagination-prev').attr('disabled', true);
				} else {
					this.$el.find('.forminator-pagination-prev').removeAttr('disabled');
				}

				if (this.step === this.totalSteps) {
					//keep pagination content on last step before submit
					this.step--;
				}

				if (this.step === (this.totalSteps - 1)) {
					var submit_button_text = this.$el.find('.forminator-pagination-submit').html();
					this.$el.find('.forminator-pagination-next .forminator-button--text').html(submit_button_text);
				} else {
					this.$el.find('.forminator-pagination-next .forminator-button--text').html(Forminator.l10n.appearance.pagination_next);
				}

			},

			go_to: function (step) {
				this.step = step;

				if (step === this.totalSteps) return false;

				// Hide all parts
				this.$el.find('.forminator-pagination').hide();

				// Show desired page
				this.$el.find('[data-step=' + step + ']').show();
				this.update_navigation();
			},

			update_navigation: function () {
				// Update navigation
				this.$el.find('.forminator-step-current').removeClass('forminator-step-current');
				this.$el.find('.forminator-nav-step-' + this.step).addClass('forminator-step-current');
			}
		};

		return {
			Utils: Utils,
			Popup: Popup,
			Notification: Notification,
			Pagination: Pagination
		};
	});

})(jQuery);
