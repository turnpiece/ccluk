(function ($) {
	define([
		'admin/models',
		'admin/views',
		'admin/settings',
		'text!admin/templates/builder.html',
		'text!admin/templates/appearance.html',
		'text!admin/templates/polls.html',
		'text!admin/templates/quizzes.html',
	], function ( Models, Views, Settings, builderTpl, appearanceTpl, pollTpl, quizzesTpl )  {
		_.extend(Forminator, Models);
		_.extend(Forminator, Views);
		_.extend(Forminator, Settings);

		var Application = new ( Backbone.Router.extend({
			app: false,
			data: false,
			layout: false,

			routes: {
				"" : "run",
				"appearance" : "run_appearance",
				"*path": "run"
			},

			events: {},

			init: function () {
				// Load Forminator Data only first time
				if( ! this.data ) {
					this.app = Forminator.Data.application || false;

					// Retrieve current data
					this.data = $.extend( true, {}, Forminator.Data.currentForm ) || {};

					// Create app model
					if( this.app === "builder" ) {
						// Custom Form model
						this.model = new Forminator.Models.Builder( this.data );
					} else if( this.app === "poll" ) {
						// Poll model
						this.model = new Forminator.Models.Poll( this.data );
					} else if( this.app === "knowledge" || this.app === "nowrong" ) {
						// Quizzes model
						this.model = new Forminator.Models.Quiz( this.data );
					} else {
						return false;
					}

				}
			},

			run: function () {
				this.init();

				// Determinate the module and load it
				if( this.app === "builder" ) {
					this.start_builder();
				} else if( this.app === "poll" ) {
					this.start_poll();
				} else if( this.app === "knowledge" || this.app === "nowrong" ) {
					this.start_quiz();
				}
			},

			run_appearance: function ( e ) {
				// If app is not builder fallback
				if( !this.app || this.app !== "builder" ) this.navigate( '', {trigger: true} );

				this.init();

				this.start_appearance();
			},

			start_builder: function () {
				$( ".wpmudev-form-wizard" ).empty().html( Forminator.Utils.template( $( builderTpl ).find('#builder-layout-tpl').html() ) );

				// If builder instance exist, off and remove
				if( this.builder ) {
					//if fields panel exist off and remove it
					if(this.builder.fields_panel) {
						this.builder.fields_panel.off();
						this.builder.fields_panel.remove();
					}
					this.builder.off();
					this.builder.remove();
				}

				this.builder = new Forminator.Views.Builder.Builder({
					"model": this.model,
					"el": '.wpmudev-form-builder'
				});


				this.update_builder_width();
			},

			start_appearance: function () {
				$( ".wpmudev-form-wizard" ).empty().html( Forminator.Utils.template( $( appearanceTpl ).find('#appearance-layout-tpl').html() ) );

				// If builder instance exist, remove
				if( this.appearance ) this.appearance.remove();

				// Init Appearance screen
				this.appearance = new Forminator.Views.Builder.Appearance({
					"model": this.model,
					"el": '.forminator-appearance-content'
				});

				this.update_builder_width();
			},

			start_poll: function () {
				$( ".wpmudev-poll-wizard" ).empty().html( Forminator.Utils.template( $( pollTpl ).find('#polls-layout-tpl').html() ) );

				// If builder instance exist, remove
				if( this.poll ) this.poll.remove();

				// Init Appearance screen
				this.poll = new Forminator.Views.Polls({
					"model": this.model,
					"el": '.forminator-polls-content'
				});
			},

			start_quiz: function () {
				$( ".wpmudev-quiz-wizard" ).empty().html( Forminator.Utils.template( $( quizzesTpl ).find('#quiz-layout-tpl').html() ) );

				// If builder instance exist, remove
				if( this.quiz ) this.quiz.remove();

				// Init Appearance screen
				this.quiz = new Forminator.Views.Quizzes({
					"model": this.model,
					"el": '.forminator-quizzes-content'
				});
			},

			update_builder_width: function () {
				var $builder = $( ".wpmudev-form-wizard" ),
					$title   = $( "#forminator-wizard-name" ),
					$sidebar = $( "#wpmudev-sidebar" )
				;

				// Set builder width to 100% each time we re-render
				$builder.css({ 'width': '100%' });
				var $sidebarWidth = $sidebar.width(),
					$builderWidth = $builder.width()
				;

				$builder.width( $builderWidth - $sidebarWidth + 20 );
				$title.width( $builderWidth - $sidebarWidth + 20 );
			}
		}));

		return Application;
	});
})(jQuery);
