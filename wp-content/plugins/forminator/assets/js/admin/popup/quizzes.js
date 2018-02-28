(function ($) {
	define([
		'text!tpl/dashboard.html',
	], function( popupTpl ) {
		return Backbone.View.extend({
			className: 'wpmudev-popup--quiz',

			popupTpl: Forminator.Utils.template( $( popupTpl ).find( '#forminator-quizzes-popup-tpl' ).html()),

			render: function() {
				this.$el.html( this.popupTpl({
					nowrongUrl: Forminator.Data.modules.quizzes.nowrong_url,
					knowledgeUrl: Forminator.Data.modules.quizzes.knowledge_url
				}));
			},
		});
	});
})(jQuery);
