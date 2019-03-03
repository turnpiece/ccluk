/*! Google Maps Pro - v2.9.5
 * http://premium.wpmudev.org/project/wordpress-google-maps-plugin
 * Copyright (c) 2018; * Licensed GPLv2+ */
/*global window:false */
/*global document:false */
/*global _agm:false */
/*global _agmInfo:false */
/*global _agmFbnf:false */
/*global navigator:false */

jQuery(function () {
	var _attached = [];

	// IE fix
	if ( ! Array.prototype.indexOf ) {
		Array.prototype.indexOf = function (needle) {
			for ( var i = 0; i < this.length; i += 1 ) {
				if ( this[i] === needle ) {
					return i;
				}
			}
			return -1;
		};
	}

	function init_facebook_object () {
		window.FB.init({
			appId      : _agmFbnf.fb_app_id, // App ID
			status     : true, // check login status
			cookie     : true, // enable cookies to allow the server to access the session
			xfbml      : true  // parse XFBML
		});
	}

	function find_nearby_friends (marker, map) {
		var pos = marker.getPosition(),
			lat = pos.lat(),
			lng = pos.lng(),
			today = new Date(),
			start = new Date( today.getFullYear(), today.getMonth() - parseInt(_agmFbnf.months), today.getDate() ),
			fql_places =
				"SELECT author_uid, coords FROM location_post " +
					"WHERE author_uid IN " +
					"(SELECT uid2 FROM friend WHERE uid1=me()) " +
						"AND " +
					"timestamp > " + Math.round(start.getTime() / 1000) + " " +
						"AND " +
					"distance(latitude, longitude, '" + lat + "', '" + lng + "') < " + parseInt(_agmFbnf.radius),
			fql_people = "SELECT id, name, url, pic_square FROM profile WHERE id IN (SELECT author_uid FROM #fql_places)";

		window.FB.api(
			{
				"method": "fql.multiquery",
				"queries": {"fql_places": fql_places, "fql_people": fql_people}
			},
			function (data) {
				var places = data[0],
					people = data[1],
					_added = [];

				jQuery.each(places.fql_result_set, function () {
					var user = false;
					var place = this;

					if (_added.indexOf(place.author_uid) > -1) { return true; }

					jQuery.each(people.fql_result_set, function () {
						if (this.id !== place.author_uid) { return true; }
						user = this;
						return false;
					});

					if ( user ) {
						_added.push(user.id);
						var friend = new window.google.maps.Marker({
							title: user.name,
							map: map,
							icon: user.pic_square,
							draggable: false,
							clickable: true,
							position: new window.google.maps.LatLng(place.coords.latitude, place.coords.longitude)
						});

						var info = new window.google.maps.InfoWindow({
							content: '<a href="' + user.url + '" target="_blank">' + user.name + '</a>'
						});

						window.google.maps.event.addListener(friend, 'click', function() {
							info.open(map, friend);
						});
					}
				});
			}
		);
	}

	jQuery(document).bind(
		"agm_google_maps-user-adding_marker",
		function (e, marker, idx, map) {
			var friends = jQuery("<a href='#nearby-friends'>Nearby friends</a>"),
				class_name = "agm_google_maps-nearby_friends-" + idx + "-body";

			// Set info popup
			var content = marker._agmInfo.getContent();
			content += jQuery("<div />").append(friends.addClass(class_name)).html();
			marker._agmInfo.setContent(content);

			var selector = "#" + jQuery(map.getDiv()).attr("id") + " ." + class_name;
			if ( _attached.indexOf(selector) > -1 ) { return true; } // Already added

			_attached.push(selector);
			jQuery('body').on("click", selector, function () {
				window.FB.login(
					function(response) {
						if ( response.authResponse ) {
							find_nearby_friends(marker, map);
						}
					},
					{
						"scope": "user_photos,user_status,friends_photos,friends_status"
					}
				);
				return false;
			});
		}
	);

	if (undefined === window.FB) {
		window.fbAsyncInit = init_facebook_object;

		// Load the SDK Asynchronously
		(function(d){
			var js, id = 'facebook-jssdk', ref = d.getElementsByTagName('script')[0];
			if ( d.getElementById(id) ) { return; }
			js = d.createElement('script'); js.id = id; js.async = true;
			js.src = "//connect.facebook.net/en_US/all.js";
			ref.parentNode.insertBefore( js, ref );
		}( document ));
	} else {
		// Assume we're all set
	}
});
