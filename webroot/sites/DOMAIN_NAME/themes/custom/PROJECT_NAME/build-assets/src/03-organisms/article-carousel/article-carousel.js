import Flickity from 'flickity';
import 'flickity/css/flickity.css';

Drupal.behaviors.articleCarousel = {
  attach(context) {
    const articlesCarousel = document.querySelectorAll('.js-articles-carousel:not(.loaded)');
    if (articlesCarousel.length === 0) {
      return;
    }

    for (let i = 0; i < articlesCarousel.length; i += 1) {
      const current = articlesCarousel[i];
      current.classList.add('loaded');
      setTimeout(() => {
        const currentFlkty = new Flickity(current, {
          cellAlign: 'left',
          contain: true,
          cellSelector: '.js-articles-list-item',
          groupCells: true,
        });
      });
    }
  },
};
