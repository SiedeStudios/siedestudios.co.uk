/**
 * SiedeStudios - Improved Header Functionality
 */

$(document).ready(function() {
  const header = $('.site-header');
  const mobileMenuToggle = $('.mobile-menu-toggle');
  const mobileNav = $('.mobile-nav');
  const body = $('body');
  
  // Mobile menu toggle functionality
  mobileMenuToggle.on('click', function() {
    console.log('Mobile menu toggle clicked'); // Debug logging
    const isExpanded = $(this).attr('aria-expanded') === 'true';
    
    $(this).toggleClass('active');
    $(this).attr('aria-expanded', !isExpanded);
    mobileNav.toggleClass('active');
    mobileNav.attr('aria-hidden', isExpanded);
    body.toggleClass('menu-open');
    
    // Prevent scrolling when menu is open
    if (!isExpanded) {
      body.css('overflow', 'hidden');
    } else {
      body.css('overflow', '');
    }
  });
  
  // Close mobile menu when a link is clicked
  $('.mobile-nav a').on('click', function() {
    mobileMenuToggle.removeClass('active');
    mobileMenuToggle.attr('aria-expanded', 'false');
    mobileNav.removeClass('active');
    mobileNav.attr('aria-hidden', 'true');
    body.removeClass('menu-open');
    body.css('overflow', '');
  });
  
  // Handle header style on scroll
  function handleHeaderScroll() {
    if ($(window).scrollTop() > 50) {
      header.addClass('scrolled');
    } else {
      header.removeClass('scrolled');
    }
  }
  
  // Header scroll effect
  $(window).on('scroll', handleHeaderScroll);
  
  // Initialize the header state on page load
  handleHeaderScroll();
  
  // Close menu on escape key
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && mobileNav.hasClass('active')) {
      mobileMenuToggle.removeClass('active');
      mobileMenuToggle.attr('aria-expanded', 'false');
      mobileNav.removeClass('active');
      mobileNav.attr('aria-hidden', 'true');
      body.removeClass('menu-open');
      body.css('overflow', '');
    }
  });
  
  // Ensure initialization - make sure mobile toggle works
  // This sometimes resolves initialization issues
  setTimeout(function() {
    mobileMenuToggle.off('click').on('click', function() {
      console.log('Mobile menu toggle clicked (reinit)'); // Debug logging
      const isExpanded = $(this).attr('aria-expanded') === 'true';
      
      $(this).toggleClass('active');
      $(this).attr('aria-expanded', !isExpanded);
      mobileNav.toggleClass('active');
      mobileNav.attr('aria-hidden', isExpanded);
      body.toggleClass('menu-open');
      
      // Prevent scrolling when menu is open
      if (!isExpanded) {
        body.css('overflow', 'hidden');
      } else {
        body.css('overflow', '');
      }
    });
  }, 500);
});