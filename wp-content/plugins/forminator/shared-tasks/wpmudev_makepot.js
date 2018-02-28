module.exports = function (grunt, wpmudev) {
	'use strict';

	grunt.registerTask('wpmudev_makepot', function () {
		var exclusions = wpmudev.files.not_meta(),
			files = []
		;
		grunt.file.expand({filter: 'isFile' }, wpmudev.files.get('all').concat(exclusions)).forEach(function (path) {
			files.push(path);
		});

		grunt.config.set('makepot', {
			wpmudev: {
				options: {
					domainPath: 'languages/',
					type: 'wp-plugin',
					include: files,
					potFilename: '<%= pkg.name %>.pot'
				},
				dest: '<%= pkg.name %>'
			}
		});

		grunt.task.run('makepot:wpmudev');
	});
};
