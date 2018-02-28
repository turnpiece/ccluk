( function ($) {
	define([
		'admin/builder/builder/fields-panel',
		'admin/builder/builder/shadows-panel',
		'text!admin/templates/builder.html',
	], function( FieldsPanel, ShadowsPanel, builderTpl ) {
		var Builder = Backbone.View.extend({
			mainTpl: Forminator.Utils.template( $( builderTpl ).find( '#builder-main-tpl' ).html() ),
			buttonsTpl: Forminator.Utils.template( $( builderTpl ).find( '#builder-buttons-tpl' ).html() ),
			noFieldsTpl: Forminator.Utils.template( $( builderTpl ).find( '#builder-main-no-fields-tpl' ).html() ),

			events: {
				"click .forminator-save-changes": "save_changes",
				"click .forminator-save-layout": "save_layout",
				"click .wpmudev-button-cancel": "cancel_builder",
				"click .forminator-add-new-field": "show_fields",
				"change .wpmudev-input": "set_dirty"
			},

			$main: false,
			$shadows_container: false,
			fields: {},
			settings: {},
			dirty: false,
			is_edit: false,

			initialize: function ( options ) {
				this.wrappers = this.model.get( "wrappers" );
				this.settings = this.model.get( "settings" );

				// Reload fields when updated
				this.listenTo( Forminator.Events, "dnd:reload:fields", this.render );
				this.listenTo( Forminator.Events, "dnd:models:updated", this.render );

				// Initialize Sidebar
				if ( ! this.sidebar ) {
					this.sidebar = new Forminator.Views.Builder.Sidebar({
						"model": this.model,
						"el": '#wpmudev-sidebar .wpmudev-sidebar--wrap'
					});
				}

				return this.render();
			},

			render: function () {
				var formID = Forminator.Data.currentForm.formID || -1;
				this.$el.html( this.mainTpl() );
				this.$el.find( ".wpmudev-builder" ).append( this.buttonsTpl() );

				// Update Title
				if( formID === -1 ) {
					$('#wpmudev-header h1').html(Forminator.l10n.builder.builder_new_title);
				} else {
					this.is_edit = true;
					$('#wpmudev-header h1').html(Forminator.l10n.builder.builder_edit_title);
				}

				this.$main = this.$el.find( ".wpmudev-builder--form" );
				this.$shadows_container = this.$el.find( ".wpmudev-builder--shadow" );

				Forminator.Events.off( "forminator:add:field:click" );

				var self = this;
				$(window).off('beforeunload.forminator-leave-wizard-confirm');
				$(window).on('beforeunload.forminator-leave-wizard-confirm', function (e) {
					if (self.dirty) {
						return Forminator.l10n.popup.save_alert;
					}
				});

				// Render form fields
				this.render_fields();

				// Render shadow fields, eg. new elements to be dragged
				this.render_shadows();

				// Render shadow fields, eg. new elements to be dragged
				this.render_form_name();
			},

			render_fields: function () {
				// If no wrappers & fields then display placeholder message
				if( this.wrappers.length === 0 ) {
					// Show drop area placeholder
					this.show_placeholder();

					// Disable saving button
					this.disable_save();

					return;
				}

				// We have fields, enable saving
				this.enable_save();

				// Remove instance before init
				if( this.fields_panel ) {
					this.fields_panel.off();
					this.fields_panel.remove();
				}

				this.fields_panel = new FieldsPanel({
					model: this.model,
				});

				this.$main.append( this.fields_panel.$el );
			},

			disable_save: function () {
				this.$el.find('.forminator-save').attr('disabled', true);
			},

			enable_save: function () {
				this.$el.find( '.forminator-save' ).attr( 'disabled', false );
			},

			render_shadows: function () {
				// Remove instance before init
				if( this.shadows_panel ) {
					this.$shadows_container.html('');
					this.shadows_panel.off();
					this.shadows_panel.remove();
				}

				this.shadows_panel = new ShadowsPanel({
					el: this.$shadows_container,
					model: this.model,
				});

				this.$shadows_container.append( this.shadows_panel.$el );
			},

			render_form_name: function () {
				var form_name = new Forminator.Settings.Text({
					model: this.model,
					id: 'builder-form-name',
					name: 'formName',
					placeholder: Forminator.l10n.builder.form_name
				});

				this.$el.find( '.wpmudev-builder--form-name' ).append( form_name.el );
			},

			show_placeholder: function () {
				this.$main.append( this.noFieldsTpl() );
			},

			save_changes: function ( e ) {
				e.preventDefault();

				if( this.validate() ) {
					this.save(false, true);
				} else {
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},


			save_layout: function ( e ) {
				e.preventDefault();

				if( this.validate() ) {
					this.save(true, true);
				} else {
					$( 'html, body' ).animate( {scrollTop : 0}, 500 );
				}
			},

			save: function( redirect, preloader ) {
				// AJAX save
				var self = this,
					data = Forminator.Utils.model_to_json( this.model ),
					formName = Forminator.Data.currentForm.formName || this.model.get( 'formName' ) || '',
					formID = Forminator.Data.currentForm.formID || -1
				;

				if( preloader ) {
					this.$el.find( '.forminator-save' ).addClass( 'wpmudev-button-onload' );
				}

				$.post({
					"url": Forminator.Data.ajaxUrl,
					"data": {
						"action": "forminator_save_builder_fields",
						"formName": formName,
						"formID": formID,
						"data": data
					}
				})
				.success( function ( response ) {
					if ( formID === -1 ) {
						Forminator.Data.currentForm.formID = response.data;
					}

					//when its on edit mode, mark input non dirty after success save
					if(self.is_edit) {
						self.dirty = false;
					}

					if( preloader ) {
						setTimeout(function () {
							self.$el.find( '.forminator-save' ).removeClass( 'wpmudev-button-onload' );
						}, 500);

						if( ! redirect ) {
							var markup = _.template( '<strong>{{ formName }}</strong> {{ Forminator.l10n.options.been_saved }}' );

							Forminator.Notification.open( 'success', markup({
								formName: formName
							}), 4000 );
						}
					}

					if( redirect ) {
						Forminator.Events.trigger( "forminator:sidebar:close:settings" );
						Forminator.navigate( 'appearance', {trigger: true} );
					}
				})
				.error(function () {
					Forminator.Notification.open( 'error', Forminator.l10n.options.error_saving, 5000 );
				});
			},

			validate: function () {
				var has_error = false;
				if( _.isEmpty( this.model.get( 'formName' ) ) ) {
					this.$el.find( '#forminator-validate-name' ).show();
					has_error = true;
				} else {
					this.$el.find( '#forminator-validate-name' ).hide();
				}

				if( has_error ) {
					return false;
				} else {
					return true;
				}
			},

			cancel_builder: function ( e ) {
				e.preventDefault();

				// Go to listings page
				window.location.href = Forminator.Data.modules.custom_form.form_list_url;
			},

			show_fields: function(e) {
				e.preventDefault();

				$( "body.wp-admin" ).addClass( "wpmudev-disabled--mobile" );
				$( "#wpmudev-sidebar" ).addClass( "wpmudev-is_active wpmudev-show" );
			},
			set_dirty: function(){
				this.dirty = true;
			}
		});

		return Builder;
	});
})( jQuery );
