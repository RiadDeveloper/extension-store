document.addEventListener('DOMContentLoaded', () => {
  const themeController = document.querySelector('.theme-controller');

  // Apply the saved theme or default to dark theme
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    document.body.classList.add(savedTheme);
    if (savedTheme === 'dark') {
      themeController.checked = true;
    }
  } else {
    // Apply default theme (dark) if no theme is saved
    document.body.classList.add('dark');
    themeController.checked = true;
    localStorage.setItem('theme', 'dark');
  }

  // Theme toggle event listener
  themeController.addEventListener('change', function () {
    if (this.checked) {
      document.body.classList.remove('light');
      document.body.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    } else {
      document.body.classList.remove('dark');
      document.body.classList.add('light');
      localStorage.setItem('theme', 'light');
    }
  });
});
