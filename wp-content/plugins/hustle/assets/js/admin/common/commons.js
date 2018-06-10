(function($){
	"use strict";
	//Hustle.Events.on("view.rendered", function(view){
	//  if( view instanceof Backbone.View)
	//      view.$(".wpmuiSelect").wpmuiSelect();
	//});
	
	Hustle.Events.on("modules.view.rendered", function(view){

		Hustle.Events.trigger("modules.view.select.render", view);

		if( view instanceof Backbone.View) {
			// Box: can show/hide content
			$('.wpmudev-box-close').each(function(){
				var $this = $(this),
					$action = $this.find('.wpmudev-box-action'),
					$content = $this.find('.wpmudev-box-body');

				$action.on('click', function(e){
					e.stopPropagation();
					$content.slideToggle();
				});
			});

			// Box: can close
			$('.wpmudev-box-close').each(function(){
				var $this = $(this),
					$close = $this.find('.wpmudev-i_close');

				$close.on('click', function(e){
					e.stopPropagation();
					$this.removeClass('wpmudev-show').addClass('wpmudev-hide');
				});
			});

			// Preview: stick to top
			(function (){
				function sticky_relocate(){
					var window_top = $(window).scrollTop();
					var div_top = $(".wpmudev-preview-anchor");

					if ( ! div_top.length ) return;

						div_top = div_top.offset().top;
					if (window_top > div_top) {
						$(".wpmudev-menu").addClass("wpmudev-preview-on");
						$(".wpmudev-preview").addClass("wpmudev-preview-sticky");
						$(".wpmudev-preview-anchor").height($(".wpmudev-preview").outerHeight());
					} else {
						$(".wpmudev-menu").removeClass("wpmudev-preview-on");
						$(".wpmudev-preview").removeClass("wpmudev-preview-sticky");
						$(".wpmudev-preview-anchor").height(0);
					}
				}
				$(function(){
					$(window).scroll(sticky_relocate);
					sticky_relocate();
				});
			}());

			// Modal: Add Another Service
			$('#wph-add-new-service-modal').each(function(){
				var $this = $(this),
					$open = $('#wph-add-another-service'),
					$close = $this.find('.wpmudev-i_close'),
					$cancel = $this.find('#wph-cancel-add-service'),
					$content = $this.find('.wpmudev-box-modal');

				// $open.on('click', function(e){
					// e.preventDefault();
					// e.stopPropagation();
					// $this.addClass('wpmudev-modal-active');
					// $('body').addClass('wpmudev-modal-is_active');

					// setTimeout(function(){
						// $content.addClass('wpmudev-show');
					// }, 100);
				// });

				$close.on('click', function(e){
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						$this.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 1000);
				});

				$cancel.on('click', function(e){
					e.preventDefault();
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						$this.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 1000);
				});
			});
			
			// Modal: Add Another Service
			// Convert Mailchimp groups instructions input in a div
			$("input#mailchimp_groups_instructions").each(function(){
				var $this = $(this);
				$this.val('<label class="wpmudev-label--notice">' + skbl + '</label>')
			});

			// Modal: Edit Form
			$('#wph-edit-form-modal').each(function(){
				var $this = $(this),
					$open = $('#wph-edit-form'),
					$close = $this.find('.wpmudev-i_close'),
					$cancel = $this.find('#wph-cancel-edit-form'),
					$content = $this.find('.wpmudev-box-modal');

				/*$open.on('click', function(e){
					e.preventDefault();
					e.stopPropagation();
					$this.addClass('wpmudev-modal-active');
					$('body').addClass('wpmudev-modal-is_active');

					setTimeout(function(){
						$content.addClass('wpmudev-show');
					}, 100);
				});*/

				$close.on('click', function(e){
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						$this.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 1000);
				});

				$cancel.on('click', function(e){
					e.preventDefault();
					e.stopPropagation();
					$content.removeClass('wpmudev-show').addClass('wpmudev-hide');

					setTimeout(function(){
						$this.removeClass('wpmudev-modal-active');
						$('body').removeClass('wpmudev-modal-is_active');
						$content.removeClass('wpmudev-hide');
					}, 1000);
				});
			});

			// Modal: Preview
			$('#wph-preview-modal').each(function(){
				var $this = $(this),
					$open = $('.wpmudev-preview'),
					$modal = $this.find('.hustle-modal'),
					$success = $this.find('.hustle-modal-success'),
					$close = $this.find('.hustle-modal-close .hustle-icon'),
					$cta = $this.find('.hustle-modal-cta'),
					$links = $this.find('a'),
					$submit = $this.find('button');

				$cta.on('click', function(e){
					e.preventDefault();
				});

				$links.on('click', function(e){
					e.preventDefault();
				});

				if ($success.length) {
					$submit.on('click', function(e){
						e.preventDefault();
						$success.addClass('hustle-modal-success_show');
						Hustle.Events.trigger("modules.view.preview.success", view);
					});
				} else {
					$submit.on('click', function(e){
						e.preventDefault();
					});
				}
			});

			// SAMPLE: Edit Form Field
			$('.wpmudev-table-body-row').each(function(){
				var $this = $(this),
					$plus = $this.find('.wpmudev-preview-item-manage');

				$plus.click(function(e){
					e.stopPropagation();
					$this.toggleClass('wpmudev-open');
				});
			});

			// HUSTLE MODAL: WITH OPT-IN
			// Layout 3
			$('.hustle-modal-three .hustle-modal-optin_form').each(function(){
				var $this = $(this),
					$fields = $this.find(".hustle-modal-optin_field"),
					$wrapper = $('<div class="hustle-modal-optin_half" />'),
					$fieldL = $fields.length;

				if ($this.hasClass('hustle-modal-optin_groups')){
					$this.find(".hustle-modal-optin_group").addClass("hustle-modal-half_enabled");
					for (var i = 0; i < $fieldL; i+=2){
						$fields.filter(':eq('+i+'),:eq('+(i+1)+')').wrapAll($wrapper);
					}
				}
			});
			// Layout 4
			$('.hustle-modal-four .hustle-modal-optin_form').each(function(){
				var $this = $(this),
					$fields = $this.find(".hustle-modal-optin_field"),
					$wrapper = $('<div class="hustle-modal-optin_half" />'),
					$fieldL = $fields.length;

				if ($this.hasClass('hustle-modal-optin_groups')){
					$this.find(".hustle-modal-optin_group").addClass("hustle-modal-half_enabled");
					for (var i = 0;i < $fieldL;i+=2){
						$fields.filter(':eq('+i+'),:eq('+(i+1)+')').wrapAll($wrapper);
					};
				} else {
					//$this.addClass("hustle-modal-half_enabled");
					//for (var i = 0;i < $fieldL;i+=2){
					//    $fields.filter(':eq('+i+'),:eq('+(i+1)+')').wrapAll($wrapper);
					//};
				}
			});
			
			console.log('view rendered');
		}

	});
	
	var admin_select_options = {
		allowClear: false,
		containerCssClass: "wpmudev-select2",
		dropdownCssClass: "wpmudev-select2-dropdown",
		tags: "true",
		width : "100%",
		createTag: function(){ return false; }
	};
		
	if ( pagenow.indexOf('listing') !== -1 ) {
		// disable select search on listing page
		admin_select_options.minimumResultsForSearch = -1;
	}
		
	var render_admin_select = function() {
		$('.wpmudev-select').wpmuiSelect( admin_select_options );
	};
	
	render_admin_select();
	
	//Changed by Paul Kevin
	//Had to separate this to make it work on the email fields modal
	//There were some JavaScripts that were conflicting with those on the email modal
	//This is called in assets/js/admin/views.js line 455 and 418. Also called above
	Hustle.Events.on("modules.view.select.render", function(view){
		
		//if( view instanceof Backbone.View) {
			render_admin_select();
		//}

	});
	
	
	
	
}(jQuery));
