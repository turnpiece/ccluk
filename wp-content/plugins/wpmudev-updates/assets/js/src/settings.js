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
	var pluginName = "wpmudevDashboardAdminSettingsPage";

	// The actual plugin constructor
	function wpmudevDashboardAdminSettingsPage(element, options) {
		this.element        = element;
		this.$el            = $(this.element);
		this.adminAddDialog = null;
		this.init();
	}

	// Avoid Plugin.prototype conflicts
	$.extend(wpmudevDashboardAdminSettingsPage.prototype, {
		init: function () {
			this.prepareAdminAddDialog();
			this.prepareAdminAddSearch();
			this.attachEvents();
			$(window).trigger('hashchange');
		},
		attachEvents: function () {
			var self = this;

			$(window).on('hashchange', function () {
				self.processHash();
			});

			this.$el.on('click', '.sui-notice-top .sui-notice-dismiss', function (e) {
				e.preventDefault();
				$(this).closest('.sui-notice-top').stop().slideUp('slow');
				return false;
			});

			this.$el.on('click', '.js-show-admin-add-modal', function (e) {
				e.preventDefault();
				self.adminAddDialog.show();
				return false;
			});

			this.$el.on('submit', 'form#form-admin-add', function (e) {
				var user_id = self.$el.find('#searchuser').val();
				if (!user_id) {
					return false;
				}
				$(this).find('button[type="submit"]').addClass('sui-button-onload');
				return true;
			});

			this.$el.on('submit', 'form', function (e) {
				if ($(this).attr('id') !== 'form-admin-add') {
					$(this).find('button[type="submit"]').addClass('sui-button-onload');
				}

				return true;
			});

			this.$el.on('click', '.js-remove-user-permisssions', function () {
				$(this).addClass('sui-button-onload');
			})

		},

		processHash: function () {
			var hash = location.hash;
			hash     = hash.replace(/^#/, '');

			this.$el.find('.sui-vertical-tabs li.sui-vertical-tab').removeClass('current');
			this.$el.find('.js-sidenav-content').hide();

			switch (hash) {
				case 'permissions':
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#permissions"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#permissions').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#permissions');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
				case 'membership':
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#membership"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#membership').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#membership');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
				case 'apikey':
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#apikey"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#apikey').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#apikey');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
				default:
					this.$el.find('.sui-vertical-tabs li.sui-vertical-tab a[href$="#general"]').closest('li.sui-vertical-tab').addClass('current');
					this.$el.find('.js-sidenav-content#general').show();
					this.$el.find('.sui-sidenav select.sui-mobile-nav').val('#general');
					this.$el.find('.sui-sidenav select.sui-mobile-nav').trigger('change');
					break;
			}
		},
		prepareAdminAddDialog: function () {
			if (this.$el.find('.sui-dialog#admin-add').length > 0) {
				var dialog          = document.getElementById('admin-add');
				this.adminAddDialog = new wpmudevDashboardAdminDialog(dialog, this.$el.find('.sui-wrap').get(0));
				return true;
			}
		},

		prepareAdminAddSearch: function () {
			var self       = this;
			var searchuser = this.$el.find('#searchuser');
			var hash       = searchuser.data('hash');

			var languageSearching     = searchuser.data('language-searching');
			var languageNoresults     = searchuser.data('language-noresults');
			var languageErrorLoading  = searchuser.data('language-error-load');
			var languageInputTooShort = searchuser.data('language-input-tooshort');

			searchuser.SUIselect2({
				allowClear: true,
				dropdownCssClass: 'sui-select-dropdown',
				dropdownParent: self.$el.find('.sui-dialog#admin-add'),
				ajax: {
					url: window.ajaxurl,
					type: "POST",
					data: function (params) {
						return {
							action: 'wdp-usersearch',
							hash: hash,
							q: params.term,
						};
					},
					processResults: function (data) {
						return {
							results: data.data
						};
					},
				},
				templateResult: function (result) {
					if (typeof result.id !== 'undefined' && typeof result.label !== 'undefined') {
						return $(result.label);
					}
					return result.text;
				},
				templateSelection: function (result) {
					return result.display || result.text;
				},
				language: {
					searching: function () {
						return languageSearching;
					},
					noResults: function () {
						return languageNoresults;
					},
					errorLoading: function () {
						return languageErrorLoading;
					},
					inputTooShort: function () {
						return languageInputTooShort;
					},
				}
			});
		}
	});

	// A really lightweight plugin wrapper around the constructor,
	// preventing against multiple instantiations
	$.fn[pluginName] = function (options) {
		return this.each(function () {
			if (!$.data(this, pluginName)) {
				$.data(this, pluginName, new wpmudevDashboardAdminSettingsPage(this, options));
			}
		});
	};

})(jQuery, window, document);
