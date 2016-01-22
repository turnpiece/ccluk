(function() {
	tinymce.create('tinymce.plugins.kaddivider', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mcekaddivider', function() {
				ed.windowManager.open({
					file: ajaxurl + '?action=kaddivider_tinymce',
					width : 350 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
					height : 110 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});
			 
			// Register buttons
			ed.addButton('kaddivider', {title : 'Insert Divider', cmd : 'mcekaddivider', image: url + '/img/divider.png' });
		},
		 
		getInfo : function() {
			return {
				longname : 'Insert Divider',
				author : 'Benjamin Ritner',
				authorurl : 'http://kadencethemes.com',
				infourl : 'http://kadencethemes.com',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});
	 
	// Register plugin
	// first parameter is the button ID and must match ID elsewhere
	// second parameter must match the first parameter of the tinymce.create() function above
	tinymce.PluginManager.add('kaddivider', tinymce.plugins.kaddivider);

})();