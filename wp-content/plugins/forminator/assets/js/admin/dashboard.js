(function ($) {
	define([
	], function( TemplatesPopup ) {
		var Dashboard = Backbone.View.extend({
			el: '.wpmudev-dashboard-section',
			events: {
				"click .wpmudev-action-close": "dismiss_welcome"
			},
			initialize: function () {
				var notification = Forminator.Utils.get_url_param( 'notification' ),
					form_title = Forminator.Utils.get_url_param( 'title' )
				;

				if( notification ) {
					var markup = _.template( '<strong>{{ formName }}</strong> {{ Forminator.l10n.options.been_published }}' );

					Forminator.Notification.open( 'success', markup({
						formName: Forminator.Utils.sanitize_uri_string( form_title )
					}), 4000 );
				}

				return this.render();
			},
			dismiss_welcome: function( e ) {
				e.preventDefault();

				var $container = $( e.target ).closest( '.wpmudev-row' ),
					$welcome_box = $( e.target ).closest( '#forminator-dashboard-box--welcome' ),
					$nonce = $welcome_box.data( "nonce" )
				;

				$container.slideToggle( 300, function() {
					$.ajax({
						url: Forminator.Data.ajaxUrl,
						type: "POST",
						data: {
							action: "forminator_dismiss_welcome",
							_ajax_nonce: $nonce
						},
						complete: function( result ){
							$container.remove();
						}
					});
				});
			}
		});

		var DashView = new Dashboard();

		return DashView;
	});
})(jQuery);
