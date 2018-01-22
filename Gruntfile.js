module.exports = function(grunt) {
  // Project configuration.
  grunt.initConfig({
    wp_readme_to_markdown: {
      target: {
        files: {
          'readme.md': 'readme.txt'
        },
      },
      options: {
        screenshot_url: 'https://ps.w.org/open-search-document/trunk/{screenshot}.png'
      },
    },
    replace: {
      dist: {
        options: {
          patterns: [
            {
              match: /^/,
              replacement: '[![WordPress](https://img.shields.io/wordpress/v/open-search-document.svg?style=flat-square)](https://wordpress.org/plugins/open-search-document/) [![WordPress plugin](https://img.shields.io/wordpress/plugin/v/open-search-document.svg?style=flat-square)](https://wordpress.org/plugins/open-search-document/changelog/) [![WordPress](https://img.shields.io/wordpress/plugin/dt/open-search-document.svg?style=flat-square)](https://wordpress.org/plugins/open-search-document/) \n\n'
            }
          ]
        },
        files: [
          {
            src: ['README.md'],
            dest: './'
          }
        ]
      }
    }
  });

  grunt.loadNpmTasks('grunt-wp-readme-to-markdown');
  grunt.loadNpmTasks('grunt-replace');

  // Default task(s).
  grunt.registerTask('default', ['wp_readme_to_markdown', 'replace']);
};
