'use strict';

/**
 * User Model
 *
 * @param data
 * @constructor
 */
var User = function( data ) {
	this.id = data.ID;
	this.username = data.user_login;
	this.email = data.user_email;
};

module.exports = User;
