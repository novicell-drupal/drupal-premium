const fs = require('fs');
const glob = require('glob');
const path = require('path');
const postcss = require('postcss');
const babel = require("@babel/core");
const uglify = require("uglify-js").minify;
const argv = require('minimist')(process.argv.slice(2));
const postcssPlugins = require('./postcss.config').plugins;

const paths = argv.i.length > 0 ? argv.i.split(','): [];
const type = argv.type || '';

if (type === 'css' && paths.length > 0) {
  console.log('\x1b[33m%s\x1b[0m', 'Processing CSS files');
  const specifiedPaths = paths.paths;

  for (let filePath = 0; filePath < specifiedPaths.length; filePath++) {
    glob(specifiedPaths[filePath], function (err, files) {
      if (files.length === 0) console.log('Zero CSS files found in ' + specifiedPaths[filePath]);
      if (err) console.log(err);
      for (let file = 0; file < files.length; file++) {
        const fileName = path.basename(files[file]);
        fs.readFile(files[file], (err, css) => {
          postcss(postcssPlugins)
            .process(css, {from: fileName})
            .then(result => fs.writeFileSync(`${path.dirname(files[file])}/${path.basename(files[file], '.css')}.min.css`, result.css));
        });
      }
    });
  }
}

if (type === 'js' && paths.length > 0) {
  console.log('\x1b[33m%s\x1b[0m', 'Processing JS files');
  const specifiedPaths = paths.paths;

  for (let filePath = 0; filePath < specifiedPaths.length; filePath++) {
    glob(specifiedPaths[filePath], function (err, files) {
      if (files.length === 0) console.log('Zero JS files found in ' + specifiedPaths[filePath]);
      if (err) console.log(err);
      for (let file = 0; file < files.length; file++) {
        fs.readFile(files[file], (err, js) => {
          if (err) console.log(err);
          babel.transform(js, {
            "presets": [
              "@babel/preset-env"
            ],
          }, function (err, result) {
            if (result) {
              let minifiedCode = uglify(result.code).code;
              fs.writeFileSync(`${path.dirname(files[file])}/${path.basename(files[file], '.js')}.min.js`, minifiedCode);
            } else {
              console.log(err);
            }
          });
        });
      }
    });
  }
}