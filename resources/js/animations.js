/**
 * SiedeStudios - Animations (CSS-based approach instead of GSAP)
 */

$(document).ready(function() {
  // Code window typing effect with simpler CSS-based implementation
  function initCodeTypingEffect() {
    const codeElement = document.querySelector('.code-content pre code');
    if (!codeElement) return;
    
    // Store original content with syntax highlighting
    const originalHTML = codeElement.innerHTML;
    
    // Get plain text content
    const textContent = codeElement.textContent || '';
    
    // Split into lines
    const lines = textContent.split('\n').filter(line => line.trim() !== '');
    
    // Create elements for each line
    const lineElements = [];
    
    // Clear the content
    codeElement.innerHTML = '';
    
    // Create cursor element
    const cursor = document.createElement('span');
    cursor.className = 'typing-cursor';
    cursor.textContent = '|';
    
    // Add CSS classes for the animation
    lines.forEach((line, index) => {
      const lineElement = document.createElement('div');
      lineElement.className = 'code-line';
      lineElement.style.display = 'block';
      lineElement.style.minHeight = '1.5em';
      lineElement.style.position = 'relative';
      lineElement.style.animationDelay = `${index * 0.3}s`;
      lineElement.style.opacity = '0';
      lineElement.classList.add('fade-in');
      
      // Apply simple syntax highlighting
      let highlightedText = line
        .replace(/\b(local|function|return|end)\b/g, '<span class="code-keyword">$1</span>')
        .replace(/\b(SiedeStudios[.:]\w+)\b/g, '<span class="code-variable">$1</span>')
        .replace(/"([^"]+)"/g, '<span class="code-string">"$1"</span>');
        
      lineElement.innerHTML = highlightedText;
      codeElement.appendChild(lineElement);
      lineElements.push(lineElement);
    });
    
    // Add cursor to the last line
    if (lineElements.length > 0) {
      lineElements[lineElements.length - 1].appendChild(cursor);
    }
  }
  
  // Utility function to check if element is in viewport
  function isElementInViewport(el) {
    if (typeof jQuery === "function" && el instanceof jQuery) {
      el = el[0];
    }
    
    const rect = el.getBoundingClientRect();
    
    return (
      rect.top <= (window.innerHeight || document.documentElement.clientHeight) * 0.9 &&
      rect.bottom >= 0
    );
  }
  
  // Add animation classes when elements come into view
  function animateElements() {
    $('.fade-in, .slide-in-bottom, .slide-in-left, .slide-in-right').each(function() {
      if (isElementInViewport($(this)) && !$(this).hasClass('animated')) {
        $(this).addClass('animated');
      }
    });
    
    $('.stagger-children > *').each(function(index) {
      if (isElementInViewport($(this).parent()) && !$(this).hasClass('animated')) {
        $(this).css('animation-delay', `${index * 0.1}s`).addClass('animated');
      }
    });
  }
  
  // Page transitions 
  function initPageTransitions() {
    // Initial page load animation
    const pageTransition = $('.page-transition');
    
    // Add class for CSS animation
    pageTransition.addClass('transition-in');
    
    // Page transition for internal links
    $('.main-nav a, .mobile-nav a, .footer-nav a').on('click', function(e) {
      // Only for internal links
      const href = $(this).attr('href');
      if (href.startsWith(window.location.origin) || href.startsWith('/')) {
        e.preventDefault();
        
        // Create exit animation
        pageTransition.addClass('transition-out');
        
        // Wait for animation to complete
        setTimeout(() => {
          window.location = href;
        }, 500);
      }
    });
  }
  
  // Initialize animations
  $(window).on('load', function() {
    //initCodeTypingEffect();
    animateElements();
    initPageTransitions();
    
    // Run animations on scroll
    $(window).on('scroll', animateElements);
  });
});