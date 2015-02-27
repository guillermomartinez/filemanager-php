
module.exports = function(grunt) {
	// grunt.registerTask('default', ['less:compileCore']);

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		clean: {
      dist: 'dist'
    },
	 	less: {
			compileCore: {
				options: {
				  strictMath: true,
				  sourceMap: true,
				  outputSourceFiles: true,
				  sourceMapURL: '<%= pkg.name %>.css.map',
				  sourceMapFilename: 'dist/css/<%= pkg.name %>.css.map'
				},
				src: 'src/styles/bootstrap.less',
				dest: 'dist/css/<%= pkg.name %>.css'
			}
		},
		cssmin: {
      options: {
        compatibility: 'ie8',
        keepSpecialComments: '*',
        advanced: false
      },
      minifyCore: {
        src: 'dist/css/<%= pkg.name %>.css',
        dest: 'dist/css/<%= pkg.name %>.min.css'
      }      
    },
    copy: {
      fonts: {
      	expand: true,
        src: 'bower_components/bootstrap/dist/fonts/**',
        dest: 'dist/fonts/',
        flatten: true,
        filter: 'isFile'
      }
    },
	});

	// grunt.loadNpmTasks('grunt-contrib-less');
	require('load-grunt-tasks')(grunt, { scope: 'devDependencies' });
  require('time-grunt')(grunt);

  grunt.registerTask('less-compile', ['less:compileCore']);
  grunt.registerTask('dist-css', ['clean','less-compile', 'cssmin:minifyCore']);


};