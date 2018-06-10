var Optin = Optin || {};
(function( $, doc ) {
	"use strict";
	$.each(['show', 'hide'], function (i, ev) {
		var el = $.fn[ev];
		$.fn[ev] = function () {
			this.trigger(ev);
			return el.apply(this, arguments);
		};
	});

	// posts/pages bounce in animation
	var $animation_elements = $('.inc_opt_inline_wrap');
	var $window = $(window);

	function check_if_in_view() {
		var window_height = $window.height();
		var window_top_position = $window.scrollTop();
		var window_bottom_position = (window_top_position + window_height);

		$.each($animation_elements, function() {
			var $element = $(this);
			var element_height = $element.outerHeight();
			var element_top_position = $element.offset().top;
			var element_bottom_position = (element_top_position + element_height);

			//check to see if this current container is within viewport
			if ((element_bottom_position >= window_top_position) &&
				(element_top_position <= window_bottom_position)) {
				$element.addClass('in-view');
			} else {
				$element.removeClass('in-view');
			}
		});
	}

	$(doc).on("hustle:module:displayed", _.debounce(on_display, 100, false));

	$window.on('scroll resize', _.debounce( check_if_in_view, 100, false ) );
	$window.trigger('scroll');
	
	$(document).on('blur', 'input, textarea, select', function(){
	    var $this = $(this);
	    if($this.is(':input[type=button], :input[type=submit], :input[type=reset]')) return;
	    if($this.val().trim() !== '') {
		    $this.parent().addClass('hustle-input-filled');
		} else{
			$this.parent().removeClass('hustle-input-filled');
		}
	});

	$(document).on('focus', '.wpoi-optin input.required', function(){
		$(this).next('label').find('i.wphi-font').removeClass('i-error');
	});
	
	/**
	 * Callback after all modules were displayed on the front end
	*/
	function on_display(e, data){
		// console.log('Module displayed!');
		// console.log(data);
		
		// start your custom js here:
	}
	
	Optin.apply_custom_size = function( data, $this ) {
		var content_data = data.content,
			design_data = data.design,
			style = design_data.style,
			layout = design_data.form_layout;
			
		// modal parts
		var $modal = $this.find('.hustle-modal'),
			$modal_body = $modal.find('.hustle-modal-body');
	
		// If the parent container is small, style accordingly.
		if ($modal_body.width() < 500) {
			$modal_body.addClass('hustle-size-small');
		} else {
			$modal_body.removeClass('hustle-size-small');
		}

		// custom size
		if ( _.isTrue( design_data.customize_size ) ) {
			$modal.css({
				'width': design_data.custom_width + 'px',
				'max-width': 'none'
			});
			
			// adjust
			if ( style === 'simple' && _.isFalse( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					modal_content = $modal.find('.hustle-modal-content');

				$modal_body.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
				});

				modal_content.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)',
					'overflow-y': 'auto'
				});
			}
			if ( style === 'minimal' && _.isFalse( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					modal_section = $modal.find('section'),
					modal_message = $modal.find('.hustle-modal-message');

				if ( _.isTrue( content_data.has_title ) && ( content_data.title !== '' || content_data.sub_title !== '' ) ) {
					var calc_header = $modal.find('header').outerHeight();
				} else {
					var calc_header = 0;
				}

				if ( _.isTrue( content_data.show_cta ) && ( content_data.cta_label !== '' && content_data.cta_url !== '' ) ) {
					var calc_footer = $modal.find('footer').innerHeight();
				} else {
					var calc_footer = 0;
				}
					
				modal_section.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_header + 'px - ' + calc_footer + 'px)'
				});

				modal_message.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_header + 'px - ' + calc_footer + 'px)',
					'overflow-y': 'auto'
				});
			}
			if ( style === 'cabriolet' && _.isFalse( content_data.use_email_collection ) ) {
				var calc_header = $modal.find('header').height() + 20, // add "20" for header margin.
					modal_section = $modal.find('section'),
					modal_message = $modal.find('.hustle-modal-message');
				
				modal_section.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_header + 'px)'
				});

				modal_message.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_header + 'px)',
					'overflow-y': 'auto'
				});
			}
			if ( layout === 'one' && _.isTrue( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					calc_footer = $modal.find('footer').height(),
					modal_image = $modal.find('.hustle-modal-image'),
					modal_section = $modal.find('section'),
					modal_article = $modal.find('article');

				modal_section.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
				});

				if (
					modal_section.hasClass('hustle-modal-image_above') ||
					modal_section.hasClass('hustle-modal-image_below')
				) {
					var avg_height = design_data.custom_height + calc_close + calc_footer;
						
					if (modal_section.height() < 250 ) {
						modal_section.css({
							'overflow-y': 'auto'
						});
					} else {
						modal_article.css({
							'height': 'calc(' + modal_section.height() + 'px - ' + modal_image.height() + 'px)',
							'overflow-y': 'auto'
						});
					}
				} else {
					modal_article.css({
						'max-height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)',
						'overflow-y': 'auto'
					});

					modal_image.css({
						'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
					});
				}
			}
			if ( layout === 'two' && _.isTrue( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					calc_footer = $modal.find('footer').height(),
					modal_body = $modal.find('.hustle-modal-body'),
					modal_section = $modal.find('section'),
					modal_article = $modal.find('article');

				modal_body.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
				});

				modal_section.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)'
				});

				modal_article.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_footer + 'px)',
					'overflow-y': 'auto'
				});
			}
			if ( layout === 'three' && _.isTrue( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					calc_image = $modal.find('.hustle-modal-image').height(),
					modal_article = $modal.find('article');

				$modal_body.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
				});

				modal_article.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_image + 'px)',
					'overflow-y': 'auto'
				});
			}
			if ( layout === 'four' && _.isTrue( content_data.use_email_collection ) ) {
				var calc_close = $modal.find('.hustle-modal-close').height() + 15, // add "15" for close margin
					calc_image = $modal.find('.hustle-modal-image').height(),
					calc_wrap = design_data.custom_height - calc_close - calc_image,
					optin_wrap = $modal.find('.hustle-modal-optin_wrap'),
					modal_article = $modal.find('article');

				$modal_body.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)'
				});

				modal_article.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px)',
					'overflow-y': 'auto'
				});

				optin_wrap.css({
					'height': 'calc(' + design_data.custom_height + 'px - ' + calc_close + 'px - ' + calc_image + 'px)',
					'overflow-y': 'auto'
				});

				if ( $modal.find('.hustle-modal-optin_form').innerHeight() > calc_wrap ) {
					optin_wrap.css({
						'align-items': 'flex-start'
					});
				}
			}
		}
	}
	
	
}(jQuery, document));
