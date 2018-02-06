module.exports = function( grunt ) {
	// Project configuration.
	grunt.initConfig( {
		// Package
		pkg: grunt.file.readJSON( 'package.json' ),
		
		dirs: {
			ignore: [ 'build', 'node_modules', 'vendor' ].join( ',' ) 
		},

		// PHP Code Sniffer
		phpcs: {
			application: {
				dir: [ '.' ],
			},
			options: {
				bin: 'vendor/bin/phpcs',
				standard: 'phpcs.ruleset.xml',
				extensions: 'php',
				ignore: '<%= dirs.ignore %>',
			}
		},

		// PHPLint
		phplint: {
			options: {
				phpArgs: {
					'-lf': null
				}
			},
			all: [ '**/*.php' ]
		},
		
		// Check WordPress version
		checkwpversion: {
			options: {
				readme: 'readme.txt',
				plugin: 'orbis-tasks.php',
			},
			check: {
				version1: 'plugin',
				version2: 'readme',
				compare: '=='
			},
			check2: {
				version1: 'plugin',
				version2: '<%= pkg.version %>',
				compare: '=='
			}
		},
		
		// MakePOT
		makepot: {
			target: {
				options: {
					cwd: '',
					domainPath: 'languages',
					type: 'wp-plugin'
				}
			}
		}
	} );

	grunt.loadNpmTasks( 'grunt-phpcs' );
	grunt.loadNpmTasks( 'grunt-phplint' );
	grunt.loadNpmTasks( 'grunt-checkwpversion' );
	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Default task(s).
	grunt.registerTask( 'default', [ 'phplint', 'checkwpversion' ] );
	grunt.registerTask( 'pot', [ 'makepot' ] );
};
