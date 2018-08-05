;(function ($) {
	// page ID or "slug"
	window.SS_PAGES.snapshot_page_snapshot_pro_destinations = function () {

		$('#wps-destination-delete').on('click', function (e) {
			e.preventDefault();
			$('#wps-destination-warning').addClass('show');
		});

		$('.copy-to-clipboard').on('click', function (e) {
			e.preventDefault();
			var clipboard_target = $(this).data('clipboard-target');
			var field = $(clipboard_target);

			if ( ! field.length ) {
				return;
			}

			field.select();
			document.execCommand('copy');
		});

		$('.show-instructions').on('click', function (e) {
			var instructions = $($(this).data('instructions'));
			instructions.toggle();
		});

		var destinationType = jQuery('[name="snapshot-destination[type]"]').val();

		if( destinationType === 'dropbox' ){
			$form = $('#wps-destinations-wizard form');
			$dropboxToken = $('[name="snapshot-destination[tokens][access][authorization_token]"]');
			$submitButton = $('#wps-destinations-wizard form [type="submit"]');
			$forceAuthorize = $('[name="snapshot-destination[force-authorize]"]');


			$dropboxToken.on('input', function(event) {
				if($(this).val().length > 0){
					$submitButton.val($submitButton.data('authenticate-text'));
					$form.attr('target', '');
					$forceAuthorize.val('off');
				} else {
					$submitButton.val($submitButton.data('get-code-text'));
					$form.attr('target', '_blank');
					$forceAuthorize.val('on');
				}
			}).trigger('input');

			if( ( $forceAuthorize.val() === 'on' && $forceAuthorize.is(':checkbox') && $forceAuthorize.is(':checked') )
				|| ( $forceAuthorize.val() === 'on' && $forceAuthorize.is(':hidden') ) ) {
				$submitButton.val($submitButton.data('get-code-text'));
				$form.attr('target', '_blank');
				$forceAuthorize.val('on');
			} else if( $dropboxToken.length === 1 && $dropboxToken.val() !== '' ) {
				$submitButton.val($submitButton.data('authenticate-text'));
				$form.attr('target', '');
				$forceAuthorize.val('off');
			} else {
				$submitButton.val($submitButton.data('save-text'));
				$form.attr('target', '');
				$forceAuthorize.val('off');
			}
			var authorizationTokenChanged = false;
			$( '[name="snapshot-destination[tokens][access][authorization_token]"]', '#wps-destination-token-checkbox' ).on('input', function(event) {
				authorizationTokenChanged = true;
			});

			$forceAuthorize.filter(':checkbox').on('change', function(event) {
				if( ! $(this).is(':checked') ){
					if( authorizationTokenChanged ){
						$submitButton.val($submitButton.data('authenticate-text'));
					} else {
						$submitButton.val($submitButton.data('save-text'));
					}
					$form.attr('target', '');
					$forceAuthorize.val('off');
					$('#wps-destination-token-checkbox').css('display', 'none');
				} else {
					$submitButton.val($submitButton.data('get-code-text'));
					$form.attr('target', '_blank');
					$forceAuthorize.val('on');
					$('#wps-destination-token-checkbox').css('display', 'block');
				}
			}).trigger('change');
		}

	};

})(jQuery);
