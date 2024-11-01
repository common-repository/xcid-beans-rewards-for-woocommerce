module.exports = function (grunt) {
    'use strict';
    // Project configuration
    var gruntConfig = {
        // Metadata
        pkg: grunt.file.readJSON('package.json'),
        // Task configuration

        bump:{
            options: {
                files: ['xc-social-reward.php', 'package.json'],
                commit: false,
                globalReplace : true,
                createTag: false,
                push : false
            }
        },
        setPHPConstant: {
            php: {
                constant    : 'XC_SOCIAL_PLUGIN_VERSION',
                value       : '<%= pkg.version %>',
                file        : 'xc-social-reward.php'
            }
        },
        uglify: {
            dist: {
                expand: true,
                cwd: 'assets',
                src: '**/*.js',
                dest: 'dist/xcid-social-reward/assets/'
            }
        },
        cssmin: {
            options: {
                shorthandCompacting: false,
                roundingPrecision: -1
            },
            dist: {
                expand: true,
                cwd: 'assets',
                src: '**/*.css',
                dest: 'dist/xcid-social-reward/assets/'
            }
        },
        clean : {
            options: {
                force: true
            },
            remove_dist: ['dist/']
        },
        copy: {
            dist: {
                files: [
                    {expand: true, cwd: 'admin', src: ['**'], dest: 'dist/xcid-social-reward/admin/', flatten: true},
                    {expand: true, cwd: 'classes', src: ['**'], dest: 'dist/xcid-social-reward/classes/', flatten: false},
                    {expand: true, cwd: 'languages', src: ['**'], dest: 'dist/xcid-social-reward/languages/', flatten: true},
                    {expand: true, cwd: 'templates', src: ['**'], dest: 'dist/xcid-social-reward/templates/', flatten: true},
                    {src: ['xc-social-reward.php'], dest: 'dist/xcid-social-reward/xc-social-reward.php'},
                    {src: ['xc-twitter-login.php'], dest: 'dist/xcid-social-reward/xc-twitter-login.php'},
                    {src: ['xc-linkedin-login.php'], dest: 'dist/xcid-social-reward/xc-linkedin-login.php'}
                ]
            },
            documentation : {
                files: [
                    {expand: true, cwd: 'documentation', src: ['**'], dest: 'dist/full-package/documentation'}
                ]
            },
            full_package: {
                files: [
                    {src: ['dist/xcid-social-reward.<%= pkg.version %>.zip'], dest: 'dist/full-package/xcid-social-reward.<%= pkg.version %>.zip'}
                ]
            }
        },
        compress: {
            dist: {
                options: {
                    archive: 'dist/xcid-social-reward.<%= pkg.version %>.zip'
                },
                expand: true,
                cwd: 'dist/',
                src: ['**/*']
            },
            full_package: {
                options: {
                    archive: 'dist/full-package.zip'
                },
                expand: true,
                cwd: 'dist/full-package/',
                src: ['**/*']
            }
        }
    };

    grunt.initConfig(gruntConfig);

    grunt.registerTask('readpkg', 'Read in the package.json file', function () {
        gruntConfig.pkg =  grunt.file.readJSON('package.json')
    });

    // These plugins provide necessary tasks
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-cssmin');
    grunt.loadNpmTasks('grunt-contrib-copy');
    grunt.loadNpmTasks('grunt-contrib-compress');
    grunt.loadNpmTasks('grunt-contrib-clean');
    grunt.loadNpmTasks('grunt-bump');
    grunt.loadNpmTasks('grunt-php-set-constant');

    // Default task
    grunt.registerTask('default', [
        'clean',
        'uglify',
        'cssmin',
        'bump',
        'readpkg',
        'setPHPConstant',
        'copy:dist',
        'compress:dist',
        'copy:documentation',
        'copy:full_package',
        'compress:full_package'
    ]);
};

