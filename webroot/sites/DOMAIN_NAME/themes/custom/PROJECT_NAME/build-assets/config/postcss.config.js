const path = require('path');
const config = require('../config');

const env = process.env.NODE_ENV || 'development';
const importFrom = [
  path.join(config.paths.src, '_base/_custom-media.css'),
];

const cssnanoOptions = {
  autoprefixer: false,
  discardComments: {
    removeAll: true,
  },
  mergeLonghand: true,
  colormin: false,
  zindex: false,
  discardUnused: {
    fontFace: false,
  },
};

module.exports = {
  env,
  map: env !== 'production' ? {
    inline: false,
  } : false,
  plugins: [
    /* eslint-disable global-require */
    require('stylelint')({
      ignoreFiles: [
        `${config.root_folder}/../../node_modules/**/*.css`,
      ],
    }),
    require('./postcss-plugins/postcss-nested-import')({
      importFrom,
      preserve: false,
      cssnanoOptions,
    }),
    require('postcss-preset-env')({
      importFrom,
      stage: 3,
      preserve: false,
      autoprefixer: {
        grid: true,
      },
      features: {
        'custom-media-queries': true,
      },
    }),
    require('postcss-nested'),
    require('cssnano')(cssnanoOptions),
    require('postcss-reporter')({
      clearReportedMessages: true,
      throwError: false,
    }),
    /* eslint-enable global-require */
  ],
};
