( function($){
	define([
		'admin/settings/multi-setting',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ){

		return MultiSetting.extend({
			multiple: false,

			events: {
				'click .wpmudev-add_answer': 'add_answer',
				'click .wpmudev-answer-toggle-container': 'toggle_answer',
				'click .wpmudev-remove-answer' : 'delete_answer',
				'click .wpmudev-preview--image': 'add_image',
				'click .wpmudev-url--input': 'add_image',
				'click .wpmudev-url--clear': 'clear_image',
				'change .wpmudev-answer-value': 'update_value',
				'change .wpmudev-quiz-results': 'update_result',
				'change .wpmudev-answer-toggle': 'update_toggle',
			},

			className: 'wpmudev-option',

			init: function ( options ) {
				this.app = Forminator.Data.application || false;
				this.results = options.results;
				this.question_index = options.question_index;
				this.answers = this.model.get( 'answers' );

				this.listenTo( Forminator.Events, "forminator:quiz:results:updated", this.render );
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
					results = false,
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-question-multiple-answer-tpl' ).html())
				;

				if ( this.app === "nowrong" ) {
					results = this.results.toJSON();
				}

				return $rowTpl({
					row: value.toJSON(),
					index: index,
					question_index: this.question_index,
					type: this.app,
					results: results
				});
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
				$mainTpl	= Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-question-answer-list-tpl' ).html() );

				return $mainTpl({
					childs: childs,
				});
			},

			add_answer: function ( e ) {
				e.preventDefault();

				// Init new condition
				new_answer = new Forminator.Models.Answer({});

				// Add condition to the collection
				this.answers.add( new_answer, { silent: true } );

				this.render();
			},

			delete_answer: function ( e ) {
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

			update_result: function ( e ) {
				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val(),
					slug = Forminator.Utils.get_slug( value )
				;

				answer.set( 'result', slug );
			},

			update_toggle: function ( e ) {
				e.preventDefault();

				var answer = this.get_model( $( e.target ) )
					value = $( e.target ).val()
				;
				this.answers.each(function (value, index) {
				    //set all the false
					value.set('toggle',false);
                });

                //turn off other toggle to off except this
				this.$el.find('.wpmudev-answer-toggle').not(e.target).prop('checked',false);
				if( $( e.target ).is( ':checked' ) ) {
					answer.set( 'toggle', true );
				} else {
					answer.set( 'toggle', false );
				}
			},

			toggle_answer: function ( e ) {
				var $row = $( e.target ).closest( '.wpmudev-answer' );

				$row.toggleClass( 'wpmudev-is_open');
			},

			add_image: function ( e ) {
				e.preventDefault();

				var self = this,
					media = wp.media({
					title: this.options.popup_label,
					button: {
						text: this.options.popup_button_label
					},
					multiple: false
				}).on( 'select', function() {
					var answer = self.get_model( $( e.target ) )
						image = media.state().get('selection').first().toJSON(),
						$row = $( e.target ).closest( '.wpmudev-answer' ),
						$preview = $row.find( ".wpmudev-browse--preview" ),
						$preview_image = $row.find( ".wpmudev-get_image" ),
						$preview_url = $row.find( ".wpmudev-url--input" );
					;

					if( image && image.url ){
						$preview.addClass( "wpmudev-has_image" );
						$preview_image.css( "background-image", "url({url})".replace( "{url}", image.url ) );
						$preview_url.val( "{url}".replace( "{url}", image.url ) );

						answer.set( 'image', image.url );
					}

				});

				media.open();
			},

			clear_image: function ( e ) {
				var answer = this.get_model( $( e.target ) ),
					$row = $( e.target ).closest( '.wpmudev-answer' ),
					$preview = $row.find( ".wpmudev-browse--preview" ),
					$preview_image = $row.find( ".wpmudev-get_image" ),
					$preview_url = $row.find( ".wpmudev-url--input" );
				;

				$preview.removeClass( "wpmudev-has_image" );
				$preview_image.css( "background-image", "" );
				$preview_url.val( '');

				answer.set( 'image', '' );
			},

			on_render: function () {
				var self = this;

				Forminator.Utils.init_select2();

				setTimeout( function () {
					// If latest answer empty, auto focus
					var $last_child = $( '.wpmudev-multianswer--list li:last-child').find( '.wpmudev-input' ),
						last_value = $last_child.val()
					;

					if( _.isEmpty( last_value ) ) {
						$last_child.focus();
					}

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

})( jQuery );
