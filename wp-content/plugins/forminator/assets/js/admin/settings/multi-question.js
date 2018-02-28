( function($){
	define([
		'admin/settings/toggle-container',
		'text!tpl/fields.html',
	], function( MultiSetting, fieldsTpl ){

		return MultiSetting.extend({
			multiple: false,

			events: {
				"click .wpmudev-add-question": "add_question",
				"click .wpmudev-delete-question": "delete_question",
				"change .wpmudev-question-title": "update_title",
			},

			className: 'wpmudev-multiqs',

			init: function () {
				this.questions = this.model.get( 'questions' );
			},

			on_render: function () {
				var self = this;

				if( this.questions.length ) {
					this.$el.addClass( 'wpmudev-have_qs' );
				} else {
					this.$el.removeClass( 'wpmudev-have_qs' );
				}

				setTimeout( function () {
					self.questions.each(function ( question, index ) {
						var answers = new Forminator.Settings.MultiQuestionAnswer({
							model: question,
							question_index: index,
							results: self.model.get( 'results' )
						});

						var $question = $( '[data-index=' + index + ']' );
						$question.find( '.wpmudev-multianswer--wrap' ).append( answers.el );
					});
				}, 50 );
			},

			get_field_html: function () {
				var childs = this.get_values_html(),
					$mainTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-question-multiple-tpl' ).html() );

				return $mainTpl({
					childs: childs,
				});
			},

			get_values_html: function () {
				var self = this;

				return this.questions.map( function ( question, key ) {
					return self.get_value_html( question, key );
				}).join(' ');
			},

			get_value_html: function ( value, index ){
				var saved_value = this.get_saved_value(),
					$rowTpl = Forminator.Utils.template( $( fieldsTpl ).find( '#settings-field-question-multiple-question-tpl' ).html() );
				;

				return $rowTpl({
					row: value.toJSON(),
					index: index,
				});
			},

			add_question: function ( e ) {
				e.preventDefault();

				// Init new condition
				new_questions = new Forminator.Models.Question({
					answers: new Forminator.Collections.Answers()
				});

				// Add condition to the collection
				this.questions.add( new_questions, { silent: true } );

				this.render();
			},

			delete_question: function ( e ) {
				e.preventDefault();

				var $button = $( e.target ),
					question = this.get_model( $button )
				;

				// Delete condition
				this.questions.remove( question, { silent: true } );

				this.render();
			},

			get_index: function ( $row ) {
				return $row.closest( '.wpmudev-multiqs--item' ).data( 'index' );
			},

			get_model: function ( $row ) {
				var index = this.get_index( $row );
				return this.questions.get_by_index( index );
			},

			update_title: function ( e ) {
				var question = this.get_model( $( e.target ) )
				value = $( e.target ).val()
				;

				question.set( 'title', value );
			},

		});

	});

})( jQuery );
