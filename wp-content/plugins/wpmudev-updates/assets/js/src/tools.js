// the semi-colon before function invocation is a safety net against concatenated
// scripts and/or other plugins which may not be closed properly.
;// noinspection JSUnusedLocalSymbols
(function ($, window, document, undefined) {

	"use strict";

	// undefined is used here as the undefined global variable in ECMAScript 3 is
	// mutable (ie. it can be changed by someone else). undefined isn't really being
	// passed in so we can ensure the value of it is truly undefined. In ES5, undefined
	// can no longer be modified.

	// window and document are passed through as local variables rather than global
	// as this (slightly) quickens the resolution process and can be more efficiently
	// minified (especially when both are regularly referenced in your plugin).

	// Create the defaults once
	var pluginName = "wpmudevDashboardAdminToolsPage";

	// The actual plugin constructor
	function wpmudevDashboardAdminToolsPage(element, options) {
		this.element      = element;
		this.$el          = $(this.element);
		this.wpMediaFrame = null;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(wpmudevDashboardAdminToolsPage.prototype, {
		init: function () {
			this.attachEvents();
			$(window).trigger('hashchange');
			this.initJsTabsCheckbox();
			this.initBrandingMediaUploader();
		},
		attachEvents: function () {
			var self = this;
			this.$el.on('click', '.sui-tabs div[data-tabs=""] div', function () {
				var tabWrapper = $(this).closest('.sui-tabs');
				var index      = $(this).data('index');

				tabWrapper.find('div[data-tabs=""] div').removeClass('active');
				$(this).addClass('active');

				tabWrapper.find('div[data-panes=""] div').removeClass('active');
				tabWrapper.find('div[data-panes=""] div[data-index="' + index + '"]').addClass('active');
			});


			$(window).on('hashchange', function () {
				self.processHash();
			});

			this.$el.on('click', '.sui-notice-top .sui-notice-dismiss', function (e) {
				e.preventDefault();
				$(this).closest('.sui-notice-top').stop().slideUp('slow');
				return false;
			});

			this.$el.on('submit', 'form', function (e) {
				$(this).find('button[type="submit"]').addClass('sui-button-onload');

				return true;
			});

		},
		initJsTabsCheckbox: function () {

			var self = this;

			$( '.sui-side-tabs label.sui-tab-item input' ).each(function() {
				
				var $this 	   = $( this ),
					$label     = $this.parent( 'label' ),
					$data      = $this.data( 'tab-menu' ),
					$wrapper   = $this.closest( '.sui-side-tabs' ),
					$alllabels = $wrapper.find( '.sui-tabs-menu .sui-tab-item' ),
					$allinputs = $alllabels.find( 'input' )
					;

				$this.on( 'click', function( e ) {
					
					$alllabels.removeClass( 'active' );
					$allinputs.removeAttr( 'checked' );
					$wrapper.find( '.sui-tabs-content div[data-tab-content]' ).removeClass( 'active' );

					$label.addClass( 'active' );
					$this.attr( 'checked', 'checked' );

					if ( $wrapper.find( '.sui-tabs-content div[data-tab-content="' + $data + '"]' ).length ) {
						$wrapper.find( '.sui-tabs-content div[data-tab-content="' + $data + '"]' ).addClass( 'active' );
					}
				});
			});

			// update active and update checkbox
			this.$el.on('click', '.sui-side-tabs.js-tabs-checkbox div[data-tabs=""] div', function () {
				var tabWrapper   = $(this).closest('.js-tabs-checkbox');
				var checkboxName = tabWrapper.data('checkbox');
				var checkbox     = tabWrapper.find('input[type=checkbox][name=' + checkboxName + ']');

				tabWrapper.find('div[data-tabs=""] div').removeClass('active');
				$(this).addClass('active');

				var checked = $(this).data('checked');
				if (checked) {
					checkbox.attr('checked', 'checked');
				} else {
					checkbox.removeAttr('checked');
				}

				tabWrapper.trigger('change');
			});

			this.$el.find('.sui-side-tabs.js-tabs-checkbox').trigger('change');

		},
		initBrandingMediaUploader: function () {
			var self          = this;
			var mediaButton   = this.$el.find('.wp-browse-media');
			var clearImageBtn = this.$el.find('.js-clear-image');

			mediaButton.on('click', function (event) {
				event.preventDefault();

				// If the media frame already exists, reopen it.
				if (self.wpMediaFrame) {
					self.wpMediaFrame.open();
					return false;
				}

				// Create a new media frame
				self.wpMediaFrame = wp.media({
					title: mediaButton.data('frame-title'),
					button: {
						text: mediaButton.data('button-text')
					},
					multiple: false
				});

				// When an image is selected in the media frame...
				self.wpMediaFrame.on('select', function () {

					// Get media attachment details from the frame state
					var attachment = self.wpMediaFrame.state().get('selection').first().toJSON(),
					    input      = self.$el.find('#' + mediaButton.data('input-id')),
					    preview    = self.$el.find('#' + mediaButton.data('preview-id')),
					    wrapper    = self.$el.find('#' + mediaButton.data('upload-wrapper-id')),
					    text       = self.$el.find('#' + mediaButton.data('text-id'))
					;

					// Send the attachment URL to our custom image input field.
					preview.css('background-image', 'url(' + attachment.url + ')');
					// Send the attachment url to our input
					input.val(attachment.url);
					wrapper.addClass('sui-has_file');
					text.html(attachment.url);
				});

				self.wpMediaFrame.on('open', function () {
					if (self.$el.hasClass('wpmud')) {
						self.$el.removeClass('wpmud')
					}
				});

				self.wpMediaFrame.on('close', function () {
					if (!self.$el.hasClass('wpmud')) {
						self.$el.addClass('wpmud')
					}
				});

				// Finally, open the modal on click
				self.wpMediaFrame.open();
				return false;
			});

			clearImageBtn.on('click', function (event) {
				event.preventDefault();
				var input   = self.$el.find('#' + mediaButton.data('input-id')),
				    preview = self.$el.find('#' + mediaButton.data('preview-id')),
				    wrapper = self.$el.find('#' + mediaButton.data('upload-wrapper-id')),
				    text    = self.$el.find('#' + mediaButton.data('text-id'))
				;
				// Send the attachment URL to our custom image input field.
				preview.css('background-image', 'url()');
				// Send the attachment url to our input
				input.val('');
				wrapper.removeClass('sui-has_file');
				text.html('');
				return false;
			});
		},
		processHash: function () {
			var hash = location.hash;
			hash     = hash.replace(/^#/, '');

			this.$el.find('.sui-vertical-tabs li.sui-vertical-tab').removeClass('current');
			this.$el.find('.js-sidenav-content').hide();

			switch (hash) {
				case 'whitelabel':
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#whitelabel"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#whitelabel').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#whitelabel');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
				default:
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#analytics"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#analytics').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#analytics');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
			}
		},

	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new wpmudevDashboardAdminToolsPage(this, options));
			}
		});
	};

})(jQuery, window, document);
