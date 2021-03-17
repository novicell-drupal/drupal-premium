import 'core-js/stable';
import 'regenerator-runtime/runtime';
import NovicellLazyLoad from 'novicell-lazyload';
import debounce from 'lodash/debounce';

const lazy = new NovicellLazyLoad({
  includeWebp: false, // optional, false here since our image source doesn't support it//TBB
});

document.addEventListener('lazybeforeunveil', (event) => {
  lazy.lazyLoad(event);
}, true);

window.addEventListener('resize', debounce(() => {
  lazy.checkImages();
}, 150), false);
