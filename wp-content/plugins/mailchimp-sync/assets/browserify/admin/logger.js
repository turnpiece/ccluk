'use strict';

var items = [];

function error(msg) {
	log("Error: " + msg);
}

function success(msg) {
	log("Success: " + msg);
}

function log(msg) {
	var line = {
		time: new Date(),
		text: msg
	};

	items.push(line);
	m.redraw();
}

function scroll(element, initialized, context) {
	element.scrollTop = element.scrollHeight;
}

function render() {
	return m("div.log", { config: scroll }, [
		items.map( function( item ) {

			var timeString =
				("0" + item.time.getHours()).slice(-2)   + ":" +
				("0" + item.time.getMinutes()).slice(-2) + ":" +
				("0" + item.time.getSeconds()).slice(-2);

			return m("div", [
				m('span.time', timeString),
				m.trust(item.text )
			] )
		})
	]);
}

module.exports = {
	'error': error,
	'success': success,
	'log': log,
	'render': render
};
