'use strict';

var m = window.m = require('mithril');
var Wizard = require('./admin/wizard.js');
var FieldMapper = require('./admin/field-mapper.js');
var $ = window.jQuery;

// init wizard
var wizardContainer = document.getElementById('wizard');
if( wizardContainer ) {
	m.mount( wizardContainer , Wizard );
}

// init fieldmapper
new FieldMapper($('.mc4wp-sync-field-map'));

// update webhook url as secret key changes
var secretKeyInput = document.getElementById('webhook-secret-key-input');
var webhookUrlInput = document.getElementById('webhook-url-input');
var button = document.getElementById('webhook-generate-button');

/**
 * Generate a random alphanumeric string of the specified length
 *
 * @param {int} length
 *
 * @returns {string}
 */
function randomString(length) {
	var chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_';
	var result = '';
	for (var i = length; i > 0; --i) result += chars[Math.floor(Math.random() * chars.length)];
	return result;
}

// update the webhook url field with the value from the secret key field
function updateWebhookUrl() {
	var sanitized = secretKeyInput.value.replace(/\W+/g, "");
	if( sanitized != secretKeyInput.value ) { secretKeyInput.value = sanitized; }
	var format = webhookUrlInput.getAttribute('data-url-format');
	webhookUrlInput.value = format.replace('%s', secretKeyInput.value );
}

// set the secret key field to a random string of 20 chars
function setRandomSecret(e) {
	if( secretKeyInput.value ) {
		var sure = confirm( "Are you sure you want to set a new webhook secret? You will have to update your webhook URL in MailChimp." );
		if( ! sure ) {
			return;
		}
	}
	secretKeyInput.value = randomString(20);
	updateWebhookUrl();
}

$(secretKeyInput).keyup(updateWebhookUrl);
$(button).click(setRandomSecret);
