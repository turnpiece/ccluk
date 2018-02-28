(function ($) {
	define([
		'admin/dashboard',
		'admin/popups',
		'admin/settings',
		'admin/builder/builder',
		'admin/builder/sidebar',
		'admin/builder/appearance',
		'admin/builder/builder/wrapper',
		'admin/polls/polls',
		'admin/quizzes/quizzes'
	], function( Dashboard, Popups, Settings, Builder, Sidebar, Appearance, Wrapper, Polls, Quizzes ) {
		return {
			"Views": {
				"Dashboard": Dashboard,
				"Popups": Popups,
				"Settings": Settings,
				"Builder": {
					"Builder": Builder,
					"Sidebar": Sidebar,
					"Wrapper": Wrapper,
					"Appearance": Appearance,
				},
				"Polls": Polls,
				"Quizzes": Quizzes
			}
		}
	});
})(jQuery);
