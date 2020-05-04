/**
 * Give Recurring Admin Subscription Synchronizer
 */

var Give_Recurring_Vars, Give_Sync_Vars;

jQuery(document).ready(function ($) {

	/**
	 *
	 * @type {{progressInterval: null, hasError: boolean, hasFinished: boolean, pollingPeriod: number, updatePeriod: number, lastData: null, lastUpdate: null, init: init, show_message: function, sync_subscription_details: sync_subscription_details, sync_subscription_transactions: sync_subscription_transactions}}
	 */
	var give_synchronizer = {

		progressInterval: null,
		hasError: false,
		hasFinished: false,
		pollingPeriod: 1000,
		updatePeriod: 250,
		lastData: null,
		lastUpdate: null,

		/**
		 * Initialize.
		 */
		init: function () {
			var body = $('body');
			// First sync the subscription details.
			body.on('sync_subscription_clicked', this.sync_subscription_details);
			// Sync transactions after details.
			body.on('subscription_details_synced', this.sync_subscription_transactions);

		},

		/**
		 * Show message.
		 *
		 * @param modal_id
		 * @param message
		 */
		show_message: function (modal_id, message) {
			var modal = $(modal_id);
			modal.find('.modal-body').append(message);
		},

		/**
		 * Sync subscription details.
		 *
		 * @param e
		 * @returns {boolean}
		 */
		sync_subscription_details: function (e) {

			give_synchronizer.disable_buttons();

			var data = {
				action: 'give_recurring_sync_subscription_details',
				subscription_id: Give_Sync_Vars.id,
				security: Give_Recurring_Vars.sync_subscription_details_nonce
			};

			var modal_id = e.modal_id;

			// Reload the modal when closed.
			$(modal_id).on('hidden.bs.modal', function () {
				location.reload();
			});

			give_synchronizer.show_message(modal_id, '<h3>' + Give_Recurring_Vars.sync_subscription_details + '</h3>');

			$.post(Give_Recurring_Vars.give_recurring_ajax_url, data, function (response) {

				// Show sync message.
				give_synchronizer.show_message(modal_id, response.html);

				// Don't proceed if there was an error.
				if (response.error) {
					return false;
				}

				var event = jQuery.Event('subscription_details_synced');
				event.log_id = response.log_id;
				event.subscription = {};
				event.subscription.id = data.subscription_id;
				event.modal_id = modal_id;

				$('body').trigger(event);
			});

			return false;
		},

		/**
		 * Sync subscription transactions.
		 *
		 * @param e
		 * @returns {boolean}
		 */
		sync_subscription_transactions: function (e) {

			var data = {
				action: 'give_recurring_sync_subscription_transactions',
				subscription_id: Give_Sync_Vars.id,
				log_id: e.log_id,
				security: Give_Recurring_Vars.sync_subscription_transactions_nonce
			};

			give_synchronizer.show_message(e.modal_id, '<h3>' + Give_Recurring_Vars.sync_subscription_transactions + '</h3>');

			// Call Synchronizer via AJAX.
			$.post(Give_Recurring_Vars.give_recurring_ajax_url, data, function (response) {
				give_synchronizer.show_message(e.modal_id, response.html);
				give_synchronizer.enable_buttons();
			});


			return false;
		},


		/**
		 * Enable buttons and loading animation.
		 */
		enable_buttons: function () {
			$('.give-active-sync-message').fadeOut();
			$('button.give-resync-button').prop('disabled', false);
		},

		/**
		 * Enable buttons and loading animation.
		 */
		disable_buttons: function () {
			$('.give-active-sync-message').fadeIn();
			$('button.give-resync-button').prop('disabled', true);
		}


	};

	give_synchronizer.init();

});