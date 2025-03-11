/**
 * SiedeStudios - Main JavaScript File
 * Entry point for Vite
 */

// Import Bootstrap
import './bootstrap.js';

// Import modules (which will now have access to global jQuery)
import './theme-toggle.js';
import './animations.js';
import './header.js';

// Add animation classes to elements that should animate
$(document).ready(function() {
  // Hero section animations - use CSS classes instead of GSAP
  $('.hero-title-top').addClass('slide-in-bottom');
  $('.hero-title-main').addClass('slide-in-bottom');
  $('.hero-subtitle').addClass('slide-in-bottom');
  $('.hero-cta').addClass('slide-in-bottom');
  $('.code-window').addClass('slide-in-right');
  $('.hero-shape').addClass('fade-in');
  
  // Service cards 
  $('.service-card').addClass('fade-in');
  
  // About section
  $('.about-image img').addClass('slide-in-right');
  $('.about-content h2').addClass('slide-in-left');
  $('.about-content .lead').addClass('slide-in-left');
  $('.about-content p').addClass('slide-in-left');
  $('.about-content .btn-text').addClass('slide-in-left');
  
  // Portfolio items
  $('.portfolio-item').addClass('fade-in');
  
  // CTA section
  $('.cta-content').addClass('fade-in');
  $('.cta-content h2, .cta-content p, .cta-content .btn').each(function(index) {
    $(this).css('animation-delay', `${index * 0.2}s`);
  });
  
  // Add staggered animations to lists
  $('.stagger-children').each(function() {
    $(this).children().each(function(index) {
      $(this).css('animation-delay', `${index * 0.1}s`);
    });
  });
  
  // Trigger animations after a slight delay to ensure everything is loaded
  setTimeout(function() {
    $('.fade-in, .slide-in-bottom, .slide-in-left, .slide-in-right, .scale-in').addClass('animated');
  }, 100);
  
  // Testimonial slider functionality
  function initTestimonialSlider() {
    const testimonials = $('.testimonial-item');
    const dots = $('.testimonial-dots .dot');
    const prevBtn = $('.testimonial-prev');
    const nextBtn = $('.testimonial-next');
    let currentIndex = 0;
    
    // Show only the current testimonial
    function showTestimonial(index) {
      testimonials.each(function(i) {
        $(this).css('display', i === index ? 'block' : 'none');
      });
      
      dots.each(function(i) {
        $(this).toggleClass('active', i === index);
      });
    }
    
    // Initial display
    showTestimonial(currentIndex);
    
    // Event listeners
    prevBtn.on('click', function() {
      currentIndex = (currentIndex === 0) ? testimonials.length - 1 : currentIndex - 1;
      showTestimonial(currentIndex);
    });
    
    nextBtn.on('click', function() {
      currentIndex = (currentIndex === testimonials.length - 1) ? 0 : currentIndex + 1;
      showTestimonial(currentIndex);
    });
    
    // Dot navigation
    dots.each(function(i) {
      $(this).on('click', function() {
        currentIndex = i;
        showTestimonial(currentIndex);
      });
    });
  }
  
  // Initialize the testimonial slider if testimonials exist on the page
  if ($('.testimonial-item').length > 0) {
    initTestimonialSlider();
  }
});

$(document).ready(function() {
  // Initialize animations for elements
  const animateElements = function() {
    $('.fade-in').each(function() {
      const element = $(this);
      if (isElementInViewport(element)) {
        element.addClass('animated');
      }
    });
    
    $('.slide-in-bottom').each(function() {
      const element = $(this);
      if (isElementInViewport(element)) {
        element.addClass('animated');
      }
    });
    
    $('.slide-in-left').each(function() {
      const element = $(this);
      if (isElementInViewport(element)) {
        element.addClass('animated');
      }
    });
    
    $('.slide-in-right').each(function() {
      const element = $(this);
      if (isElementInViewport(element)) {
        element.addClass('animated');
      }
    });
    
    $('.stagger-children').each(function() {
      const element = $(this);
      if (isElementInViewport(element)) {
        element.addClass('animated');
      }
    });
  };
  
  // Check if element is in viewport
  const isElementInViewport = function(el) {
    if (typeof jQuery === "function" && el instanceof jQuery) {
      el = el[0];
    }
    
    const rect = el.getBoundingClientRect();
    
    return (
      rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.9 &&
      rect.bottom >= 0
    );
  };
  
  // Run animations on page load
  animateElements();
  
  // Run animations on scroll
  $(window).on('scroll', function() {
    animateElements();
  });
  
  // Smooth scroll for anchor links
  $('a[href^="#"]').on('click', function(e) {
    e.preventDefault();
    
    const targetId = $(this).attr('href');
    
    // Skip if it's just "#"
    if (targetId === '#') return;
    
    const targetElement = $(targetId);
    
    if (targetElement.length) {
      const scrollTop = targetElement.offset().top - 100; // Offset for header
      
      $('html, body').animate({
        scrollTop: scrollTop
      }, 800, 'swing');
    }
  });
});