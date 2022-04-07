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
						'last-translator': 'Delicious Brains <mayday@bettersearchreplace.com>',
						'language-team': 'Delicious Brains <mayday@bettersearchreplace.com>'
					},
					type: 'wp-plugin',
					updateTimestamp: true,
				}
			}
		},
		cssmin: {
			target: {
				files: [{
					expand: true,
					cwd: 'assets/css',
					src: ['*.css', '!*.min.css'],
					dest: 'assets/css',
					ext: '.min.css'
				}]
			}
		},
		uglify: {
			my_target: {
				options: {
					sourceMap: true
				},
				files: {
					'assets/js/better-search-replace.min.js': ['assets/js/better-search-replace.js']
				}
			}
		},
		watch: {
			css: {
				files: [
					'assets/css/*.css',
					'!assets/css/*.min.css'
				],
				tasks: [
					'cssmin'
				]
			},
			js: {
				files: [
					'assets/js/*.js',
					'!assets/js/*.min.js',
					'!assets/js/*.min.js.map'
				],
				tasks: [
					'uglify'
				]
			}
		}
	});

	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

};
