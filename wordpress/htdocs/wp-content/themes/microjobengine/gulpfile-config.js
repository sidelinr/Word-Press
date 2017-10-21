// Configuration
const skinName = 'diplomat';
module.exports = {
  dev: {
    proxy: 'mje.dev',
    port: '8080'
  },
  js: {
    src: 'assets/js/**/*.js',
    mergeable: ['assets/js/mergeable/**/*.js'],
    vendorFileName: 'vendor.js',
    dest: 'assets/js'
  },
  style: {
    src: [
      'sass/**/*.scss',
      '!sass/skins/**/*.scss',
    ],
    dest: 'assets/css',
    vendorSrc: [
      'assets/css/vendors/chosen.css',
      'assets/css/vendors/cropper.min.css',
      'assets/css/vendors/customscrollbar.css',
      'assets/css/vendors/toastr.min.css',
    ],
    vendorFileName: 'vendor.css',
    skin: {
      src: 'sass/skins/' + skinName + '/main.scss',
      dest: 'skins/' + skinName + '/assets/css'
    }
  },
  zip: {
    src: [
      '**',
      '!gulpfile.babel.js',
      '!package.json',
      '!prepros.cfg',
      '!README.html',
      '!README.md',
      '!composer.json',
      '!yarn.lock',
      '!.*',
      '!.*/**',
      '!node_modules',
      '!node_modules/**',
      '!UnitTests',
      '!UnitTests/**',
      '!sass',
      '!sass/**',
      '!assets/css/vendors',
      '!assets/css/vendors/**',
      '!page-test.php'
    ],
    fileName: 'microjobengine-v{version}.zip',
    dest: './'
  },
  git: {
    tagName: 'MjE-v'
  },
  watch: ['**/*.php'] // handle files .html, .php change
};