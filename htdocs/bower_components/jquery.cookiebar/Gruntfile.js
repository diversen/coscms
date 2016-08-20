module.exports = function(grunt) {
  grunt.initConfig({

    // Minify the javascript
    cssmin: {
      prod: {
        files: {
          // Dest: Src
          'dist/jquery.cookiebar.min.css': ['jquery.cookiebar.css']
        }
      }
    },

    // Uglify the javascript
    uglify: {
      prod: {
        options: {
          mangle: true
        },
        files: [{
          src: ['jquery.cookiebar.js'],
          dest: 'dist/jquery.cookiebar.min.js',
        }]
      }
    },
  });

  //Load the tasks
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-contrib-uglify');

  // Define the main grunt task
  grunt.registerTask(
    'minify',
    'Minifies css and javascript files.', [
      'uglify:prod',
      'cssmin:prod'
    ]
  );
};