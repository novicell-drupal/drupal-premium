import debounce from 'lodash/debounce';

/**
 * Header scroll, change header layout based on scroll position.
 */
const headerScroll = () => {
  const header = document.getElementById('js-header');
  if (!header) {
    return;
  }

  if (document.body.classList.contains('user-logged-in')) {
    return;
  }

  const changeHeaderLayout = (currentTop) => {
    if (currentTop > 100 && document.body.clientWidth > 991) {
      header.classList.add('scroll');
    } else {
      header.classList.remove('scroll');
    }
  };

  changeHeaderLayout(window.pageYOffset);
  window.addEventListener('scroll', () => {
    changeHeaderLayout(window.pageYOffset);
  });
};

document.addEventListener('DOMContentLoaded', () => {
  headerScroll();

  window.addEventListener('resize', debounce(() => {
    headerScroll();
  }, 150), false);
});
