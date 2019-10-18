'use strict';

const m = require('mithril');
const logger = require('./logger.js');
const User = require('./user.js');
let started = false,
	running = false,
	done = false,
	userCount = 0,
	usersProcessed = 0,
	progress = 0,
	userBatch = [],
	settingsForm,
	hasUnsavedChanges = false;

function controller() {
	settingsForm = document.getElementById('settings-form');

	settingsForm.addEventListener('change', function() {
		hasUnsavedChanges = true;
	});
}

function askToStart() {
	const sure = confirm( "Are you sure you want to start synchronising all of your users? This can take a while if you have many users, please don't close your browser window." );
	if( sure ) {
		start();
	}
}

function start() {
	started = true;
	running = true;

	fetchTotalUserCount()
		.then(prepareBatch)
		.then(subscribeFromBatch);
}

function resume() {
	running = true;
	subscribeFromBatch();
}

function pause() {
	running = false;
}

function finish() {
	done = true;
	logger.log("Done");
}

function fetchTotalUserCount() {
	return m.request({
		method: "GET",
		url: `${ajaxurl}?action=mcs_wizard&mcs_action=get_user_count`,
	}).then(data => {
		logger.log("Found " + data + " users.");
		userCount = data;
	}).catch(err => {
		logger.log( "Error fetching user count. Error: " + err.code );
	});
}

function prepareBatch() {

	const url = ajaxurl + `?action=mcs_wizard&mcs_action=get_users&offset=${usersProcessed}&limit=100`;
	return m.request({
		method: "GET",
		url: url,
		type: User
	}).then(data => {
		userBatch = data;
		logger.log("Fetched " + userBatch.length + " users.");

		// finish if we didn't get any users
		if( userBatch.length === 0 ) {
			finish();
		}
	}).catch(err  => {
		logger.log( "Error fetching users. Error: " + err.code );
	});
}

function subscribeFromBatch() {
	if( ! running || done ) {
		return;
	}

	// do we have users left in this batch
	if( userBatch.length === 0 ) {
		return prepareBatch().then(subscribeFromBatch);
	}

	// Get next user
	let user = userBatch.shift();

	// Add line to log
	logger.log("Handling <strong> #" + user.id + " " + user.username + " &lt;" + user.email + "&gt;</strong>" );

	m.request({
		method: "GET",
		url: `${ajaxurl}?action=mcs_wizard&mcs_action=handle_user&user_id=${user.id}`,
	}).then(response => {
		response.success ? logger.success( response.message ) : logger.error( response.message );
	}).catch(err => {
		logger.error("Request error: " + err.code);
	}).finally(() => {
		usersProcessed++;
	}).then(updateProgress)
	  .then(subscribeFromBatch);
}

// calculate new progress & update progress bar.
function updateProgress() {
	// calculate % progress
	progress = Math.round( usersProcessed / userCount * 100 );

	// finish after processing all users
	if( usersProcessed >= userCount ) {
		finish();
	}
}

/**
 * View
 *
 * @returns {*}
 */
function view() {
	// Wizard isn't running, show button to start it
	if( ! started ) {
		return m('p', [
			m('input', { 
				type: 'button', 
				class: 'button', 
				value: 'Synchronise All', 
				onclick: askToStart, 
				disabled: hasUnsavedChanges
			}),
			hasUnsavedChanges ? m('span.help', ' — Please save your changes first.') : ''
		]);
	}

	// Show progress
	return [
		done ? '' : m("p",
				m("input", {
					type: 'button',
					class: 'button',
					value: ( running ? "Pause" : "Resume" ),
					onclick: ( running  ? pause : resume )
				})
		),
		m('div.progress-bar', [
			m( "div.value", {
				style: "width: "+ progress +"%"
			}),
			m( "div.text", ( done ? "Done!" : "Working: " + progress + "%" ))
		]),
		logger.render()
	];
}

module.exports = {
	'controller': controller,
	'view': view
};
