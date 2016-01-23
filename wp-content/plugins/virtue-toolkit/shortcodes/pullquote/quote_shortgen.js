(function() {
	tinymce.create('tinymce.plugins.kadquote', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mcekadquote', function() {
				ed.windowManager.open({
					file: ajaxurl + '?action=kadquote_tinymce',
					width : 350 + parseInt(ed.getLang('button.delta_width', 0)), // size of our window
					height : 190 + parseInt(ed.getLang('button.delta_height', 0)), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});
			 
			// Register buttons
			ed.addButton('kadquote', {title : 'Insert Styled Quote', cmd : 'mcekadquote', image: url + '/img/quote.png' });
		},
		 
		getInfo : function() {
			return {
				longname : 'Insert Styled Quote',
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
	tinymce.PluginManager.add('kadquote', tinymce.plugins.kadquote);

})();