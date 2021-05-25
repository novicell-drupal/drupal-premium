/**
 * Nested imports.
 *
 * @see https://github.com/eriklharper/postcss-nested-import
 * Changed to support postcss 8.
 */

const fs = require('fs');
const postcss = require("postcss");
const postcssCustomProperties = require('postcss-custom-properties');
const cssnano = require('cssnano');

function parseImportPath(path) {
  const matches = path.trim().match(/^['"](.+?)['"](.*)/);
  return matches[1];
}

function readFile(file) {
  return new Promise((resolve, reject) => {
    // eslint-disable-next-line consistent-return
    fs.readFile(file, 'utf-8', (err, contents) => {
      if (err) {
        return reject(err);
      }
      resolve(contents);
    });
  });
}
/**
 * PostCSS Nested Import Plugin
 */
module.exports = (opts = {}) => {
  const postcssCustomPropertiesOpts = {};
  let cssnanoOpts = {};
  if ('cssnanoOptions' in opts) {
    cssnanoOpts = opts.cssnanoOptions;
  }

  if ('importFrom' in opts || 'preserve' in opts) {
    if ('importFrom' in opts) {
      postcssCustomPropertiesOpts.importFrom = opts.importFrom;
    }
    if ('preserve' in opts) {
      postcssCustomPropertiesOpts.preserve = opts.preserve;
    }
  }

  return {
    postcssPlugin: 'postcss-nested-import',
    async AtRule(atRule) {
      if (atRule.params && atRule.name === 'nestedimport') {
        const path = parseImportPath(atRule.params);
        if (path == null) {
          return;
        }
        const fileContents = await readFile(path);
        const parsedCustomProperties = await postcss([
          postcssCustomProperties(postcssCustomPropertiesOpts),
          cssnano(cssnanoOpts)
        ]).process(fileContents);
        atRule.replaceWith(parsedCustomProperties.css)
      }
    }
  }
}
module.exports.postcss = true;

