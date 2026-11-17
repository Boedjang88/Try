(function() {
  window.fbq = window.fbq || function() {};
  window._fbq = window._fbq || window.fbq;
  window.dataLayer = window.dataLayer || [];
  window.gtag = window.gtag || function() {};

  if (typeof jQuery !== 'undefined') {
    if (!jQuery.fn.owlCarousel) {
      jQuery.fn.owlCarousel = function() { return this; };
    }
  }
})();
