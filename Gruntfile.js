module.exports = function (grunt) {

    // Project configuration.
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json')
        , phpdocumentor: {
            // Place here Task level options (i.e common to all your phpDocumentor targets)
            options: {
                command: 'run'
            }

            , all: {
                options: {
                    directory: './src',
                    target: 'docs'
                }
            }
            , display_help: {
                options: {
                    command: 'help'
                }
            }
        }
    });

    //load tasks
    //grunt.loadNpmTasks('grunt-bower-requirejs');
    grunt.loadNpmTasks('grunt-phpdocumentor');

    //register tasks
    grunt.registerTask('default', []);
    grunt.registerTask('generate:doc', ['phpdocumentor:all']);
    grunt.registerTask('all', ['phpdocumentor:all']);
};