document.querySelector('.theme-controller').addEventListener('change', function () {
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
  
  // Apply the saved theme on window load
  window.onload = () => {
    const savedTheme = localStorage.getItem('theme');
    const themeController = document.querySelector('.theme-controller');
    if (savedTheme) {
      document.body.classList.add(savedTheme);
      if (savedTheme === 'dark') {
        themeController.checked = true;
      }
    } else {
      // Default theme
      document.body.classList.add('light');
    }
  };