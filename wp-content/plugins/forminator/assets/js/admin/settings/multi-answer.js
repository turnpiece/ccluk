(function($){
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( ToggleContainer, fieldsTpl ){

		return ToggleContainer.extend({
			multiple: false,

			events: {
				'click .wpmudev-add-answer': 'add_answer',
				'click .wpmudev-nav--action': 'open_menu',
				'click .wpmudev-action--add_field a': 'add_field',
				'click .wpmudev-action--kill_field a': 'kill_field',
				'click .wpmudev-action--kill_answer a' : 'kill_answer',
				'change .wpmudev-answer-value': 'update_value',
				'change .wpmudev-answer-placeholder': 'update_placeholder',
			},

			className: 'wpmudev-option',

			init: function () {
				this.answers = this.model.get( 'answers' );
			},

			render: function () {
				this.$el.html('');
				this.$el.append( this.get_label_html() );
				this.$el.append( this.get_field_html() );

				if( this.answers.length > 0 ) {
					this.$el.find( '.wpmudev-multianswer' ).addClass( 'wpmudev-has_answers' );
				}

				this.trigger( 'rendered', this.get_value() );

				this.on_render();
			},

			get_values_html: function () {
				var self = this;

				return this.answers.map( function ( answer, key ) {
					return self.get_value_html( answer, key );
				}).join(' ');
			},

			get_value_html: function ( value, index ) {
				var saved_value = this.get_saved_value(),
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-answer-list-row-tpl' ).html())
				;

				return $rowTpl({
					row: value.toJSON(),
					index: index
				});
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl	= Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-answer-list-tpl' ).html() )
				;

				return $mainTpl({
					childs: childs,
				});

			},

			open_menu: function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				var $target = $( e.target ),
					$container = $target.closest( ".wpmudev-answer" )
				;

				$container.find( ".wpmudev-answer--nav" ).toggleClass( "wpmudev-is_active" );
			},

			add_field: function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val()
				;

				answer.set( "use_extra", true );

				this.render();
			},

			kill_field: function ( e ) {
				e.preventDefault();
				e.stopPropagation();

				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val()
				;

				answer.set( "use_extra", false );
				answer.set( "extra", "" );

				this.render();
			},

			add_answer: function ( e ) {
				e.preventDefault();

				// Init new condition
				new_answer = new Forminator.Models.Answer({});

				// Add condition to the collection
				this.answers.add( new_answer, { silent: true } );

				this.render();
			},

			kill_answer: function ( e ) {
				e.preventDefault();

				var $button = $( e.target ),
					answer = this.get_model( $button )
				;

				// Delete condition
				this.answers.remove( answer, { silent: true });

				this.render();
			},

			get_index: function ( $row ) {
				return $row.closest( '.wpmudev-answer' ).data( 'index' );
			},

			get_model: function ( $row ) {
				var index = this.get_index( $row );
				return this.answers.get_by_index( index );
			},

			move_option: function ( item, index ) {
				var my_model = this.get_model( item ),
					my_index = this.answers.model_index( my_model );

				// Move new wrapper to correct place
				this.answers.move_to( index, my_index );

				this.render();
			},

			update_value: function ( e ) {
				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val()
				;

				answer.set( 'title', value );
			},

			update_placeholder: function ( e ) {
				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val()
				;

				answer.set( 'extra', value );
			},

			on_render: function () {
				var self = this;

				setTimeout( function () {
					// If latest answer empty, auto focus
					var $last_child = $( '.wpmudev-multianswer--list li:last-child').find( '.wpmudev-input' ),
						last_value = $last_child.val()
					;

					if( _.isEmpty( last_value ) ) {
						$last_child.focus();
					}

					// Sortable answers
					$( '.wpmudev-multianswer--list' ).sortable({
						handle: '.wpmudev-answer--move',
						//placeholder: 'placeholder-sortable',
						update: function( e, ui ) {
							self.move_option( ui.item, ui.item.index() );
						}
					});
				}, 100 );
			}
		});
	});

})(jQuery);
