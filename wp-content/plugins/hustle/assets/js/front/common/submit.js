(function( $ ) {


	function validate_form( $form, is_test ){
		var requireds = $form.find(".required"),
			gdpr = $form.parents('.hustle-modal-body').find('.hustle-modal-gdpr'),
			$icon = $('<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18" preserveAspectRatio="none" class="hustle-icon hustle-i_warning"><path fill-rule="evenodd" d="M9 18c-4.97 0-9-4.03-9-9s4.03-9 9-9 9 4.03 9 9-4.03 9-9 9zm.25-3c.69 0 1.25-.56 1.25-1.25s-.56-1.25-1.25-1.25S8 13.06 8 13.75 8.56 15 9.25 15zm-.018-4C8 11 7 3.5 9.232 3.5s1.232 7.5 0 7.5z"/></svg>'),
			errors = [];
		
		// GDPR checkbox is present but not checked.
		if (gdpr.length > 0 && !gdpr.prop('checked')) {
			gdpr.next().addClass('hustle-modal-optin_error')
			errors.push(gdpr);
		}

		// Todo add error icon, ask Leigh where to put it
		$('.wpoi-field-error').remove();
		requireds.each(function(){

			var $this = $(this),
				error_class = $this.attr("name") + "_" + "error";
				
			/*if (!$this.next('label').find('.hustle-i_warning').length){
				$this.next('label').find('.hustle-modal-optin_icon').append($icon);
			}*/
				
			if ( is_test ){
				$this.next('label').find('.hustle-i_warning').show();
				$this.addClass('hustle-modal-optin_error');
				errors.push( $this );
				return errors;
			}

			if ( _.isEmpty( this.value ) || ( $this.is("[type='email']") && !this.value.trim().match( /^[\S]+\@[a-zA-Z0-9\-]+\.[\S]{2,}$/gi ) ) ){
				$this.next('label').find('.hustle-i_warning').show();
				$this.addClass('hustle-modal-optin_error');
				errors.push( $this );

			} else {
				$this.next('label').find('.hustle-i_warning').hide();
				$this.removeClass('hustle-modal-optin_error');
				$("." + error_class).remove();
			}

		});

		return errors.length === 0;
	}

	$(document).on("submit", 'form.hustle-modal-optin_form',function(e){
		e.preventDefault();
		
		var $form = $(e.target),
			$button = $form.find("button"),
			$modal = $form.closest( '.hustle-modal'),
			$modal_parent = $modal.parent(),
			module_id = $modal_parent.data( 'id'),
			type = $modal_parent.data('type'),
			module = Modules[ module_id ],
			self = this,
			is_test = _.isTrue( module.test_mode ),
			get_success_message = function(){
				return module.content.success_message.replace("{name}", module.module_name);
			},
			$failure = $("<span class='wpoi-submit-failure'>" + inc_opt.l10n.submit_failure +  "</span>")
			;


		$form.parent().find('.wpoi-submit-failure').remove();

		if( $form.data("sending") || !validate_form( $form, is_test ) ) return;

		$button.attr("disabled", true);
		$button.addClass("loading");
		$form.addClass("loading");

		$form.data("sending", true);

		$.ajax({
			type: "POST",
			url: inc_opt.ajaxurl,
			dataType: "json",
			data: {
				action: "module_form_submit",
				data: {
					form: $form.serialize(),
					module_id: module_id,
					page_type: inc_opt.page_type,
					page_id: inc_opt.page_id,
					uri: encodeURI( window.location.href ),
					type: type
				}
			},
			success: function(res){
				if ( res && res.success ) {
					if ( module.content.after_successful_submission === 'redirect' ) {
						window.location.replace( module.content.redirect_url );
					} else {
						var $success_msg = $modal.find(".hustle-modal-success");
						$success_msg.find(".hustle-modal-success_message").html(module.content.success_message);
						$success_msg.addClass('hustle-modal-success_show');
						
						if ( _.isTrue( module.content.auto_close_success_message ) ) {
							var on_success_time = parseInt( module.content.auto_close_time ),
								on_success_unit = module.content.auto_close_unit;

							if ( 'minutes' === on_success_unit ) {
								on_success_time *= 60;
							}

							on_success_time *= 1000;
							_.delay(function(){
								var modal_close = $modal.find('.hustle-modal-close .hustle-icon');

								if ( modal_close.length > 0 ) {
									modal_close.trigger("click");
								} else {
									$success_msg.removeAttr( 'style' );
								}
								$success_msg.removeClass('hustle-modal-success_show');
							}, on_success_time );
						}

					}

				} else {
					var message = '';
					if ( res.data ) {
						message = $.isArray( res.data ) ? res.data.pop() : res.data;
					} else {
						message = inc_opt.l10n.submit_failure;
					}
					$failure.html( message ? message : inc_opt.l10n.submit_failure );

					$form.append( $failure );
				}
			},
			error: function(){
				$form.append( $failure );
			},
			complete: function(){
				$button.attr("disabled", false);
				$form.removeClass("loading");
				$button.removeClass("loading");
				$form.data("sending", false);
			}
		});

	});

}(jQuery));
