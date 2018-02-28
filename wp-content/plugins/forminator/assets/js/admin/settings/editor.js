( function ($) {
	define([
		'admin/settings/text'
	], function( Text ) {

		return Text.extend({
			events: {
				'click .wpmudev-insert-content': 'insert_content',
				'click .wpmudev-vars--button': 'toggle_menu'
			},

			className: 'wpmudev-option forminator-field-wrap-editor',

			get_field_html: function () {
				var attr = {
					'cols': '40',
					'rows': '5',
					'class': 'forminator-field-singular wpmudev-textarea',
					'id': this.get_field_id(),
					'name': this.get_name()
				};

				if ( this.options.placeholder ) {
					attr.placeholder = this.options.placeholder;
				}
				var formData = ! _.isUndefined( this.options.enableFormData ) ? this.get_form_data() : '';

				return '<div class="wpmudev-vars">' +
					'<textarea ' + this.get_field_attr_html( attr ) + '>' + this.get_saved_value() + '</textarea>' +
					'<div class="wpmudev-vars--mask">' +
					'<div class="wpmudev-vars--innermask">' +
					'<button class="wpmudev-button wpmudev-vars--button">'+ Forminator.l10n.options.form_based_data +'</button>' +
					'<ul class="wpmudev-vars--dropdown">' +
					formData +
					this.get_utilities() +
					'</ul>' +
					'</div>' +
					'</div>' +
					'</div>';
			},

			toggle_dropdown: function () {
				this.$el.find( '.wpmudev-vars--dropdown').toggleClass( 'wpmudev-is_active' );
			},

			toggle_menu: function ( e ) {
				e.preventDefault();

				this.toggle_dropdown();
			},

			get_form_data: function () {
				var disabledFields = [
					'captcha',
					'product',
					'hidden',
					'pagination',
					'postdata',
					'total',
					'upload'
				];

				var fieldsArray = Forminator.Utils.get_fields( this.model.get( 'wrappers' ), disabledFields ),
					markup = '<li class="wpmudev-dropdown--option"><strong>' + Forminator.l10n.options.form_data + '</strong></li>'
				;

				_.each( fieldsArray, function( field, index ) {
					markup += '<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{' + field.element_id + '}">' + field.label + '</a></li>';
				});

				return markup;
			},

			get_utilities: function () {
				return '<li class="wpmudev-dropdown--option"><strong>' + Forminator.l10n.options.misc_data + '</strong></li>' +
				    '<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_up}">User IP Address</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{date_mdy}">Date (mm/dd/yyyy)</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{date_dmy}">Date (dd/mm/yyyy)</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_id}">Embed Post/Page ID</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_title}">Embed Post/Page Title</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{embed_url}">Embed URL</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_agent}">HTTP User Agent</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{http_refer}">HTTP Refer URL</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_name}">User Display Name</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_email}">User Email</a></li>' +
					'<li class="wpmudev-dropdown--option"><a class="wpmudev-insert-content" data-content="{user_login}">User Login</a></li>';
			},

			insert_content: function ( e ) {
				e.preventDefault();

				// If tinymce not defined, abort
				if( _.isUndefined( tinymce ) ) return;

				var content = $( e.target ).data( 'content' ),
					editor = tinymce.get( this.get_field_id() )
				;

				// Insert content to editor
				editor.insertContent( content );

				this.toggle_dropdown();
			},

			on_render: function () {
				var self = this;

				this.$el.attr( 'id', 'wrapper-' + this.get_field_id() );

				this.initialize_editor();
			},

			initialize_editor: function () {
				var self = this;

				if( _.isUndefined( window.wp.editor ) || _.isUndefined( tinymce ) ) {
					setTimeout( function () {
						self.initialize_editor();
					}, 100);
				} else {
					setTimeout( function () {
						// Remove any previous editor instance for the textarea
						window.wp.editor.remove( self.get_field_id() );

						// Initialize editor
						window.wp.editor.initialize( self.get_field_id(), {
							tinymce: true,
							quicktags: true
						});

						var editor = tinymce.get( self.get_field_id() );

						editor.on('change', function (e) {
							self.save_value( editor.getContent() );
							self.trigger( 'changed', editor.getContent() );
						});
						
						$('#'+self.get_field_id()).on('change', function(e){
							editor.setContent($(this).val());
							self.trigger( 'changed', editor.getContent() );
						});
						
					}, 100 );
				}
			}
		});
	});
})( jQuery );
