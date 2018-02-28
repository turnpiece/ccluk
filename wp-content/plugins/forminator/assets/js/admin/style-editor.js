(function ($) {
	define([
		'text!admin/templates/style-editor.html',
	], function( tpl ) {
		return Backbone.View.extend({
			editor: false,
			session: false,

			mainTpl: Forminator.Utils.template( $( tpl ).find( '#style-editor-main-tpl' ).html()),

			selectors: [
            { selector: ".forminator-form input ", label: "Text Input" },
			],

			events: {
            "click .wpmudev-css-stylable": "insert_selector"
        	},

			initialize: function ( options ) {
				this.options = options;
				this.selectors = options.selectors;
				return this.render();
			},

			render: function () {
				this.$el.html( this.mainTpl({
					selectors: this.selectors,
					custom_css: this.model.get( this.options.property ) || '',
					element_id: this.options.element_id
				}));

				this.start_editor();
			},

			start_editor: function () {
				var self = this;

				this.editor = ace.edit( this.$( '#' + this.options.element_id )[0] );
				this.session = this.editor.getSession();

				this.session.setUseWorker( false );
				this.editor.setShowPrintMargin( false );

				this.session.setMode( "ace/mode/css" );
				this.editor.setTheme( 'ace/theme/forminator' );
				this.editor.renderer.setShowGutter(true);
			   this.editor.setHighlightActiveLine(true);

				this.editor.on( 'change', function( e ) {
					//self.trigger( 'change' );
					self.model.set( self.options.property, self.editor.getValue() );
				});

				this.editor.focus();
			},

			insert_selector: function ( e ) {
				e.preventDefault();

				var $el = $( e.target ),
					selector = $el.data( "selector" ) + "{}";

				this.editor.navigateFileEnd();
				this.editor.insert( selector );
				this.editor.navigateLeft(1);
				this.editor.focus();
			}
		});
	});
})(jQuery);
