// Set variables for category and month options
const categories = document.querySelectorAll('.articles-overview--select-category [type="checkbox"]');
const monthsDropdown = document.querySelector('.js-months-dropdown');

// Show/hide articles based on chosen category and month
function showArticleItems() {
  const articles = document.querySelectorAll('.js-articles-list-item');
  const categoriesFilter = [];
  const categoriesList = document.querySelectorAll('.js-category-item-checkbox.active');
  const monthFilter = monthsDropdown.dataset.selected;
  for (let i = 0; i < categoriesList.length; i += 1) {
    categoriesFilter.push(categoriesList[i].value);
  }

  articles.forEach((article, index) => {
    const dataFilters = article.getAttribute('data-filters');
    const articleFilters = dataFilters.split(' ');
    let articleShow = true;
    if (monthFilter) {
      articleShow = articleFilters.indexOf(monthFilter) >= 0;
    }
    if (categoriesFilter.length > 0) {
      const categoryMatch = articleFilters.some((r) => categoriesFilter.indexOf(r) >= 0);
      articleShow = categoryMatch && articleShow;
    }
    if (articleShow) {
      article.classList.remove('hidden');
    } else {
      article.classList.add('hidden');
    }
  });
}

// Set active class on selected category
const categoryChange = (event) => {
  if (event.target.checked) {
    event.target.classList.add('active');
  } else {
    event.target.classList.remove('active');
  }
  showArticleItems();
};

// Add clicked categories to array
if (categories.length > 0) {
  for (let i = 0; i < categories.length; i += 1) {
    categories[i].addEventListener('change', (event) => categoryChange(event));
  }
}

// Show/hide month dropdown menu
if (monthsDropdown) {
  monthsDropdown.addEventListener('click', (e) => {
    monthsDropdown.classList.toggle('closed');
  });
}

// Set selected month
const months = document.querySelectorAll('.js-month-item');
if (months.length !== 0) {
  months.forEach((month, index) => {
    month.addEventListener('click', (event) => {
      event.preventDefault();
      monthsDropdown.dataset.selected = event.target.dataset.id;
      monthsDropdown.querySelector('.label').innerHTML = event.target.dataset.label;
      showArticleItems();
    });
  });
}
