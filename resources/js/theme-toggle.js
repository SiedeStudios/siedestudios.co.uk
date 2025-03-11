/**
 * SiedeStudios - Theme Toggle Script
 */

// jQuery is already defined globally in bootstrap.js

$(document).ready(function() {
  const htmlElement = $('html');
  const themeToggleBtn = $('.theme-toggle-btn');
  
  // Check for saved theme preference or use user system preference
  const savedTheme = localStorage.getItem('theme');
  const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // Set initial theme
  if (savedTheme) {
    htmlElement.attr('data-theme', savedTheme);
  } else if (prefersDarkMode) {
    htmlElement.attr('data-theme', 'dark');
  }
  
  // Theme toggle click event
  themeToggleBtn.on('click', function() {
    const currentTheme = htmlElement.attr('data-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    // Update theme with fade transition
    $('body').css('opacity', 0.5);
    setTimeout(function() {
      htmlElement.attr('data-theme', newTheme);
      localStorage.setItem('theme', newTheme);
      $('body').css('opacity', 1);
    }, 100);
  });
});