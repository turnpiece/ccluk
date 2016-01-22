(function() {
	tinymce.create('tinymce.plugins.kadvimeo', {
		init : function(ed, url) {
			var t = this;
			// Register commands
			ed.addCommand('mcekadvimeo', function() {
				ed.windowManager.open({
					file: ajaxurl + '?action=kadvimeo_tinymce',
					width : 350 + ed.getLang('button.delta_width', 0), // size of our window
					height : 350 + ed.getLang('button.delta_height', 0), // size of our window
					inline : 1
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('kadvimeo', {title : 'Insert Vimeo', cmd : 'mcekadvimeo', image: url + '/img/vimeo.png' });
			
		},
		
		getInfo : function() {
			return {
				longname : 'Insert Vimeo Video',
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
	tinymce.PluginManager.add('kadvimeo', tinymce.plugins.kadvimeo);

})();