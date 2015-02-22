/*global module:false*/
module.exports = function(grunt) {

  // Project configuration.
  grunt.initConfig({
    latex: {
      options: {
        haltOnError: true,
        shellEscape: true
      },
      pdf: {
        options: {
          outputDirectory: 'dist'
        },
        src: ['archivos_memoria/main.tex']
      }
    },
    watch: {
      latex: {
        files: 'archivos_memoria/**/*.tex',
        tasks: ['default']
      }
    }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-latex');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task. Runs latex twice to get cross-references and indexes right
  grunt.registerTask('default', ['latex:pdf', 'latex:pdf']);

};
