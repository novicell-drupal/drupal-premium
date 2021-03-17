document.addEventListener('DOMContentLoaded', () => {
  const searchOverlayTriggers = document.querySelectorAll('.js-search-toggle');
  if (searchOverlayTriggers.length === 0) {
    return;
  }

  searchOverlayTriggers.forEach((current) => {
    current.addEventListener('click', () => {
      // Create a new event
      const event = new CustomEvent('searchToggle');
      // Dispatch the event
      document.dispatchEvent(event);
    });
  });
});
