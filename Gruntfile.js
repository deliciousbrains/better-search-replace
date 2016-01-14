module.exports = function(grunt) {

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        makepot: {
            target: {
                options: {
					domainPath: '/languages/',
					mainFile: 'better-search-replace.php',
					potFilename: 'better-search-replace.pot',
					potHeaders: {
						poedit: true,
						'report-msgid-bugs-to': 'http://wordpress.org/support/plugin/better-search-replace',
						'last-translator': 'Expanded Fronts <support@expandedfronts.com>',
						'language-team': 'Expanded Fronts <support@expandedfronts.com>'
					},
					type: 'wp-plugin',
					updateTimestamp: true,
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-wp-i18n');

};
