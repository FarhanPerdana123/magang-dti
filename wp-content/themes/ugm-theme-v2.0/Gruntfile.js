const sass = require("node-sass");

module.exports = function (grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON("package.json"),

    // configure sass for styling sheet -----------------------------------
    sass: {
      dist: {
        options: {
          implementation: sass,
          // sourceMap: true,
          outputStyle: "compressed"
        },
        files: {
          "assets/css/style.min.css": "assets/sass/style.scss",
          "assets/css/style-gutenberg.min.css": "assets/sass/style-gutenberg.scss"
        }
      }
    },

    // configure autoprefixer for css -----------------------------------
    autoprefixer: {
      dist: {
        options: {
          map: true,
          browsers: ["last 2 version", "ie 8"]
        },
        files: {
          "assets/css/style.min.css": "assets/css/style.min.css",
          "assets/css/style-gutenberg.min.css": "assets/css/style-gutenberg.min.css"
        }
      }
    },

    // configure watch task -----------------------------------
    watch: {
      scripts: {
        files: [
          "assets/sass/**/*.scss"
        ],
        tasks: ["sass:dist", "autoprefixer:dist"]
      },
      // add this extensions on your chrome to active livereload
      // https://chrome.google.com/webstore/detail/livereload/jnihajbhpnppcggbcgedagnkighmdlei
      livereload: {
        options: {
          livereload: true
        },
        files: ['**/*.css']
      }
    },

    purifycss: {
      options: {
        minify: true
      },
      target: {
        src: ['*.php', 'inc/widget/*.php', 'inc/*.php', 'page-templates/*.php', 'template-parts/*.php', 'assets/js/*.js'],
        css: ['assets/css/style.min.css'],
        dest: 'assets/css/style.min.css'
      },
    }
  });

  grunt.registerTask("default", ["watch"]);
  grunt.registerTask("build", ["sass:dist", "autoprefixer:dist"]);

  grunt.loadNpmTasks("grunt-sass");
  grunt.loadNpmTasks("grunt-contrib-watch");
  grunt.loadNpmTasks("grunt-autoprefixer");
  grunt.loadNpmTasks('grunt-purifycss');

};