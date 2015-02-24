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
    },
    aws_s3: {
      pdf: {
        options: {
          accessKeyId: process.env.S3_KEY,
          secretAccessKey: process.env.S3_SECRET,
          bucket: 'proyecto-fin-carrera',
          differential: true
        },
        files: [
          {expand: true, cwd: 'dist', src: ['**'], dest: grunt.template.today('isoUtcDateTime')},
        ]
      }
    }
  });

  // These plugins provide necessary tasks.
  grunt.loadNpmTasks('grunt-latex');
  grunt.loadNpmTasks('grunt-contrib-watch');
  grunt.loadNpmTasks('grunt-aws-s3');

  // Default task. Runs latex twice to get cross-references and indexes right
  grunt.registerTask('default', ['latex:pdf', 'latex:pdf']);

};
