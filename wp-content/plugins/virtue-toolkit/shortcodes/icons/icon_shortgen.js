(function() {
	tinymce.create('tinymce.plugins.kadicon', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mcekadicon', function() {
				ed.windowManager.open({
					 file: ajaxurl + '?action=kadicons_tinymce',
					width : 350 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
					height : 360 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});
			 
			// Register buttons
			ed.addButton('kadicon', {title : 'Insert Icon', cmd : 'mcekadicon', image: url + '/img/cool.png' });
		},
		 
		getInfo : function() {
			return {
				longname : 'Insert Icon',
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
	tinymce.PluginManager.add('kadicon', tinymce.plugins.kadicon);

})();