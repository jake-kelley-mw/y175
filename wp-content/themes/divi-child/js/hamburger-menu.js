(function() {
  'use strict';
  
  const hamburgerToggle = document.querySelector('.hamburger-toggle');
  const fullscreenMenu = document.querySelector('.fullscreen-menu');
  const body = document.body;
  
  if (!hamburgerToggle || !fullscreenMenu) return;
  
  function toggleMenu() {
    const isActive = fullscreenMenu.classList.toggle('active');
    hamburgerToggle.classList.toggle('active');
    hamburgerToggle.setAttribute('aria-expanded', isActive);
    
    if (isActive) {
      body.style.overflow = 'hidden';
    } else {
      body.style.overflow = '';
    }
  }
  
  function closeMenu() {
    fullscreenMenu.classList.remove('active');
    hamburgerToggle.classList.remove('active');
    hamburgerToggle.setAttribute('aria-expanded', 'false');
    body.style.overflow = '';
  }
  
  // Toggle menu on button click
  hamburgerToggle.addEventListener('click', toggleMenu);
  
  // Close menu when clicking a link
  const menuLinks = fullscreenMenu.querySelectorAll('a');
  menuLinks.forEach(function(link) {
    link.addEventListener('click', closeMenu);
  });
  
  // Close menu when clicking overlay
  fullscreenMenu.addEventListener('click', function(e) {
    if (e.target === fullscreenMenu) {
      closeMenu();
    }
  });
  
  // Close menu on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && fullscreenMenu.classList.contains('active')) {
      closeMenu();
    }
  });
})();