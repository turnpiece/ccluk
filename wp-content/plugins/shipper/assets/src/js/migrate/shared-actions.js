/**
 * Actions shared across pages
 */
;(function($) {

	/**
	 * Handles refresh link click
	 *
	 * @param {Object} e Event object
	 */
	function handle_refresh_click( e ) {
		if ( e && e.preventDefault ) e.preventDefault();

		$.post(
			ajaxurl,
			{ action: 'shipper_clear_cache' },
			function() { window.location.reload(); }
		);

		return false;
	}

	function boot() {
		$( document ).on( 'click', 'a[href="#refresh-locations"]', handle_refresh_click );

		/**
		 * Register work activator clicks
		 *
		 * On click, these get their icon replaced with loader indicator
		 */
		$( document ).on( 'click', '.shipper-work-activator', function() {
			$(this).find( 'i' )
				.replaceWith( '<i class="sui-icon-loader sui-loading"></i>' )
			;
		} );

		/**
		 * Dismiss notices
		 */
		$( document ).on( 'click', '.sui-can-dismiss .sui-notice-dismiss a', function ( e ) {
			if ( e && e.preventDefault ) e.preventDefault();
			if ( e && e.stopPropagation ) e.stopPropagation();

			var $notice = $( this ).closest( '.sui-can-dismiss' );
			$notice.remove();

			return false;
		} );
	}

	$(boot);

})(jQuery);
