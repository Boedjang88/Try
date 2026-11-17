# How it works

Deep mode: what the site's JavaScript is made of and what its start page did while we watched.

## Its JavaScript

Analysed 26 script(s) from https://www.uph.edu/id/: 16 module(s), 1568 endpoint(s), 6 route(s), bundled with babel/browserify.

Evidence ev_a622f7db156ee98eec8b6533a4463b0ebbae4d0d2964c6325116065330964d66.

```json
{
  "api_operations": [],
  "app_kind": "unknown",
  "bundlers": [
    "babel",
    "browserify"
  ],
  "edges": [
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#27"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#jQuery().on(load)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#runTransition@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#1",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#showSiteContent@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#10",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-ds-control .uph-ds__item).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#10",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#11",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#11",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#11",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-overlay).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#11",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-facilities .uph-facilities__box).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#11",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#12",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#destroyCarousel@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#12",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetScrollBar@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#12",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#13",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#14",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetFooter@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#14",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#15",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#15",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#15",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-gallery-list .uph-gallery__box).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#15",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-overlay).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#15",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-burger-menu).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-header-overlay).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.menu-item-has-children > a).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#destroyDropdown@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#destroyMobileMenu@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleHideMobileMenu@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetDropdown@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#16",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-hero-banner-btn).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-overlay).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-popup-video).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-popup-video__inner).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleCloseVideo@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#17",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-lecturer .uph-lecturer__box).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-lecturer .js-pagination button).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#destroyCarousel@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetScrollBar@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#18",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#19",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#19",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-places-list .uph-places__box).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#19",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#destroyCarousel@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#19",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetScrollBar@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#19",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#2",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-accordion .uph-accordion__ttl).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#2",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-overlay, .js-uph-popup-close).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-places__box, .uph-maps-c__item, .uph-maps-c__pin__i).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-popup-places__body, .uph-popup-lecturer__body).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(body).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleHidePopup@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleSetVhProperty@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#20",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(#p_search).on(input)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-section-browse-dropdown .uph-section-browse__dropdown__item).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-section-browse-list .uph-section-browse__input).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-section-browse__item).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#21",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#init@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "requires",
      "to": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#32"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$().on(keyup)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-section-contact-link).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-study-fee).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.js-uph-overlay, .js-uph-popup-close).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-places__box, .uph-maps-c__item, .uph-maps-c__pin__i).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(.uph-popup-contact-form__body).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#$(body).on(click)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#document.addEventListener(wpcf7mailsent)@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#error@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleHidePopup@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#22",
      "kind": "defines",
      "to": "fn:www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js#handleRunCarousel@1"
    },
    {
      "from": "bundle-module:www.uph.edu/wp-content
```

## Ten seconds of it

Watched https://www.uph.edu/id/ for 10000ms: 1 request outline(s), 8 console entr(y/ies).

Evidence ev_d9e6fa41465bf4cbad998671d85aa81a0826a35054affb02be7070214b472cb0.

```json
{
  "accessibility": null,
  "console": {
    "count": 8,
    "entries": [
      {
        "level": "log",
        "source": "console-api"
      },
      {
        "level": "info",
        "source": "console-api"
      },
      {
        "level": "info",
        "source": "console-api"
      },
      {
        "level": "info",
        "source": "console-api"
      },
      {
        "level": "warning",
        "source": "console-api"
      },
      {
        "level": "warning",
        "source": "console-api"
      },
      {
        "level": "warning",
        "source": "console-api"
      },
      {
        "level": "warning",
        "source": "console-api"
      }
    ],
    "has_text": false
  },
  "dom": {
    "attribute_names": [
      "action",
      "alt",
      "aria-atomic",
      "aria-label",
      "aria-live",
      "as",
      "async",
      "autocomplete",
      "charset",
      "class",
      "content",
      "data-ll-status",
      "data-menu",
      "data-src",
      "data-token",
      "data-wp-strategy",
      "defer",
      "disabled",
      "href",
      "hreflang",
      "http-equiv",
      "id",
      "lang",
      "media",
      "method",
      "name",
      "placeholder",
      "property",
      "rel",
      "required",
      "role",
      "selected",
      "sizes",
      "src",
      "style",
      "target",
      "title",
      "type",
      "value"
    ],
    "elements_by_name": {
      "a": 104,
      "body": 1,
      "br": 1,
      "button": 20,
      "div": 237,
      "form": 2,
      "h2": 13,
      "h4": 1,
      "h5": 14,
      "h6": 6,
      "head": 1,
      "html": 1,
      "i": 26,
      "img": 36,
      "input": 22,
      "label": 17,
      "li": 81,
      "link": 38,
      "meta": 34,
      "nav": 1,
      "noscript": 1,
      "option": 8,
      "p": 8,
      "script": 42,
      "select": 1,
      "span": 32,
      "style": 7,
      "title": 1,
      "ul": 18
    },
    "text_nodes": 209,
    "total_nodes": 1082,
    "truncated": false
  },
  "dropped_events": 0,
  "frame_origins": [
    "https://www.uph.edu"
  ],
  "network": {
    "request_count": 1,
    "requests": [
      {
        "initiator": "script",
        "method": "POST",
        "mime_type": "text/plain",
        "response_headers": {
          "credentials_present": [],
          "kept": {
            "access-control-allow-credentials": {
              "value_length": 4
            },
            "access-control-allow-origin": {
              "value_length": 19
            },
            "alt-svc": {
              "value_length": 46
            },
            "cache-control": "no-cache, no-store, must-revalidate",
            "content-length": "0",
            "content-security-policy-report-only": "script-src 'none'; form-action 'none'; frame-src 'none'; report-uri https://csp.withgoogle.com/csp/scaffolding/ascnsrsggc:201:0",
            "content-type": "text/plain",
            "cross-origin-opener-policy-report-only": {
              "value_length": 39
            },
            "cross-origin-resource-policy": "cross-origin",
            "date": {
              "value_length": 29
            },
            "expires": {
              "value_length": 29
            },
            "pragma": {
              "value_length": 8
            },
            "report-to": {
              "value_length": 141
            },
            "server": {
              "value_length": 6
            }
          }
        },
        "status": 204,
        "type": "Fetch",
        "url": "https://analytics.google.com/g/collect?v=2&tid=G-HDFHEX3LDS&gtm=45je6b90h2v9116189529za20oxf1&_p=1794281568947&gcd=13l3l3l3l1l1&npa=0&dma=0&_eu=AAAIACQC&cid=1372416832.1794281451&frm=0&ibt=1&ngs=1&pscdl=noapi&rcb=9&sr=800x600&uaa=x86&uab=64&uafvl=Not-A.Brand%3B99.0.0.0%7CGoogle%2520Chrome%3B157.0.8103.38%7CChromium%3B157.0.8103.38&uam=&uamb=0&uap=Linux&uapv=&uaw=0&ul=en-gb&_s=1&tag_exp=115616986~115938465~115938468~118897920~118897930~120213116~120385422~120412526~120420416~120469145~120469153~121246026~121246034~123814183&sid=1794281451&sct=1&seg=1&dl=https%3A%2F%2Fwww.uph.edu%2Fid%2F&dt=Universitas%20Pelita%20Harapan&en=page_view&tfd=5569"
      }
    ]
  },
  "origin": "https://www.uph.edu/id/",
  "resources": [
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2025/01/banner-mobile-min.jpg?x81484"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fbdo6-2.fna.fbcdn.net/v/t51.82787-15/870985915_18640383976042542_1809676227889335019_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=103&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiQ0xJUFMuYmVzdF9pbWFnZV91cmxnZW4uQzMifQ%3D%3D&_nc_ohc=NoWHU3eWK4oQ7kNvwG8Fk5o&_nc_oc=AdrPLgVgFWfx2xjhwsFsGKS6PvdZBjmTa4m0iHtFS25a_OnIIKBEhQnBrCTQ0XJpkV0&_nc_zt=23&_nc_ht=instagram.fbdo6-2.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQJXdsOp93CQNKfE5L6YGGYRSxCUuW2mHj0OB1Ybt4N2vgKRZIwpNXt_qaQksj-bgU3FFIrtX_Fc4w&oh=00_AQRpDkfCgP5axuz8uwsKwwAmCF7v2GoeuVHosnkPEsKTig&oe=6AF854E1"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fcgk4-4.fna.fbcdn.net/v/t51.82787-15/852830492_18637635208042542_8605466142742135462_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=111&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiQ0FST1VTRUxfSVRFTS5iZXN0X2ltYWdlX3VybGdlbi5DMyJ9&_nc_ohc=s-olHUgtQSYQ7kNvwHsINuN&_nc_oc=AdrWgyPEolF1W85nc6drOavzsyCO6G7Ul3UEPgo-C8njIctJ7ksHTEDhu47ySl3_zuw&_nc_zt=23&_nc_ht=instagram.fcgk4-4.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQLURIUCGGX9avJ40UcBxmTOh4bIGYlJio5EuaUMWo8JJUX5xg2wj7TRJsav8pZ26uJuksCQ5c-e0g&oh=00_AQSfIOyWEDwq46DimoY0c6G3-7cwg_n1dlTeXalNDZH63g&oe=6AF871D1"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/plugins/contact-form-7/includes/js/index.js?x81484&ver=6.1.2"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%201864%201230'%3E%3C/svg%3E"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/plugins/contact-form-7/includes/swv/js/index.js?x81484&ver=6.1.2"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/js/vendor.min.js?x81484"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20876%20776'%3E%3C/svg%3E"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fbdo6-2.fna.fbcdn.net/v/t51.82787-15/891215265_18643603834042542_8104080736972418974_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=103&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiQ0xJUFMuYmVzdF9pbWFnZV91cmxnZW4uQzMifQ%3D%3D&_nc_ohc=Ja3c8vTL23oQ7kNvwF5gCQl&_nc_oc=Adq19PcpwJAJfxet1PQKkXV0-izB9T3e2Y6j4lXgEhALWYIhRUDddhM8TZmSUBHG4ro&_nc_zt=23&_nc_ht=instagram.fbdo6-2.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQL-yCm33iGJXmQ9xPX97jtRst0BIjByHhVWKo9RCGpuv0DHncDsJMBEjsxpuyx73ma3NTfDK-hcug&oh=00_AQTK9Eu6XS30-xqAe5TPIj90tplyzM1OOAVf-Jt0_SIO2A&oe=6AF85AFF"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%201%201'%3E%3C/svg%3E"
    },
    {
      "mime_type": "application/x-font-ttf",
      "type": "Font",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/fonts/icons/UPH.ttf?l5d9l7"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/plugins/cf7-conditional-fields/js/scripts.js?x81484&ver=2.6.4"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fbdo6-2.fna.fbcdn.net/v/t51.82787-15/866565035_18639963295042542_1223957603252881535_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=109&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiRkVFRC5iZXN0X2ltYWdlX3VybGdlbi5DMyJ9&_nc_ohc=PrJ8VcdIe4QQ7kNvwHUSWxi&_nc_oc=AdpoD1Jz7CfuRjnnOboDFVyjbOaxhkz9bz2F0LtQPVeIghQb0YhG0rphu6yYS1SM6ww&_nc_zt=23&_nc_ht=instagram.fbdo6-2.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQJZ0C3eZ0YETVmqV2e1dQ58yEN9ZJM7vXsxeE7TqpqeZjKGrII0qhAKHbAX4y7tRxmQN00f0vZzcg&oh=00_AQSL7yCDrDvaJ0OK6Vuhq5wqcSBYQ0zQGMYINzuLbb57VA&oe=6AF8517B"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20818%20460'%3E%3C/svg%3E"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20813%20457'%3E%3C/svg%3E"
    },
    {
      "mime_type": "application/javascript",
      "type": "Script",
      "url": "https://www.uph.edu/id/?abbb8ea4eca5d4fc9ea3f059f9d0fc93=1&ver=139.20261110"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-includes/js/dist/hooks.min.js?x81484&ver=4d63a3d491d11ffd8ac6"
    },
    {
      "mime_type": "application/font-woff2",
      "type": "Font",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/fonts/HelveticaNeue-Bold.woff2"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-includes/js/jquery/jquery.min.js?x81484&ver=3.7.1"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/img/bg/bg-noise-mobile.svg"
    },
    {
      "mime_type": "image/png",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2026/08/Logo-UPH-WhiteHorizontalNEW.png?x81484"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2025/02/Hero-Banner-5-min.jpg?x81484"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%201876%201246'%3E%3C/svg%3E"
    },
    {
      "mime_type": "application/javascript",
      "type": "Script",
      "url": "https://js.lasso.link/lasso-performance.min.js?ver=139.20261110"
    },
    {
      "mime_type": "application/font-woff2",
      "type": "Font",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/fonts/HelveticaNeue.woff2"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-includes/js/dist/i18n.min.js?x81484&ver=5e580eb46a90c2b997e6"
    },
    {
      "mime_type": "application/javascript",
      "type": "Script",
      "url": "https://www.uph.edu/id/js/snippet.min.js?x81484&ver=139.20261110"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/plugins/sitepress-multilingual-cms/res/js/cookies/language-cookie.js?x81484&ver=484900"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js?x81484"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2025/02/Hero-Banner-2-min.jpg?x81484"
    },
    {
      "mime_type": "application/font-woff2",
      "type": "Font",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/fonts/HelveticaNeue-Light.woff2"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20378%20283'%3E%3C/svg%3E"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "data:image/svg+xml,%3Csvg%20xmlns='http://www.w3.org/2000/svg'%20viewBox='0%200%20378%20284'%3E%3C/svg%3E"
    },
    {
      "mime_type": "application/x-javascript",
      "type": "Script",
      "url": "https://www.uph.edu/wp-includes/js/jquery/jquery-migrate.min.js?x81484&ver=3.4.1"
    },
    {
      "mime_type": "image/svg+xml",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/img/bg/bg-noise.svg"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2025/02/Highlight-UPH-3-e1750231113335.jpg?x81484"
    },
    {
      "mime_type": "application/font-woff2",
      "type": "Font",
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/fonts/HelveticaNeue-Medium.woff2"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://www.uph.edu/wp-content/uploads/2025/01/banner-scaled-min.jpg?x81484"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fbdo6-3.fna.fbcdn.net/v/t51.82787-15/870731506_18641097448042542_3717639269563561975_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=105&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiQ0xJUFMuYmVzdF9pbWFnZV91cmxnZW4uQzMifQ%3D%3D&_nc_ohc=qy_GsGlTsfUQ7kNvwH16jtz&_nc_oc=AdrHvZ_P2U2bxfov_PKFyTtVzPOZjqQ68wPtiQci47Jf9x_luvWf0nprTc9qPOhmg38&_nc_zt=23&_nc_ht=instagram.fbdo6-3.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQIsgdFkGsaWcRmmEaZt7IqdDG2BpbBTvx2672luoiEyzuphQE7GU-CDrwmsFbmsb8c3yzng4aAhpg&oh=00_AQRfX7QaX5RPjU8hQRStt6FTIrgv2TFaFzJ2gtEe1d1hnQ&oe=6AF85A41"
    },
    {
      "mime_type": "image/jpeg",
      "type": "Image",
      "url": "https://instagram.fcgk4-4.fna.fbcdn.net/v/t51.82787-15/848014973_18635725384042542_2835523322476596164_n.jpg?stp=dst-jpg_e35_tt6&_nc_cat=102&_nc_map=urlgen_bucketless&ccb=7-5&_nc_sid=18de74&efg=eyJlZmdfdGFnIjoiRkVFRC5iZXN0X2ltYWdlX3VybGdlbi5DMyJ9&_nc_ohc=R0cj7R0khLoQ7kNvwG0-hYs&_nc_oc=Adqvjcgb3FL_8lu0S8fVyYXly-L1t2MuWFlZ16jKMjLQ3hJ18NDfBtpvkunKHJiMp0I&_nc_zt=23&_nc_ht=instagram.fcgk4-4.fna&edm=ANo9K5cEAAAA&_nc_gid=LZf1AO1qRCDV9oB-rIyNyw&_nc_tpa=Q5bMBQKDMW3mRpeFHZYpbn2trBN1XR8QSRg9JohYw2L1WJe5IqH4KOwd1LxELqGcnMRxSZipKfeq5fNDsQ&oh=00_AQQtswjJmCoSA6AQCsmIoitCWwdHXnAYdnl9fdtxrRlS9A&oe=6AF86AAD"
    },
    {
      "mime_type": "application/javascript",
      "type": "Script",
      "url": "https://js.codedrink.com/lasso-performance.min.js?ver=139.20261110"
    }
  ],
  "scripts": [
    {
      "length": 3249,
      "source_map_url": null,
      "url": "https://www.uph.edu/id/"
    },
    {
      "length": 87553,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-includes/js/jquery/jquery.min.js?x81484&ver=3.7.1"
    },
    {
      "length": 13577,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-includes/js/jquery/jquery-migrate.min.js?x81484&ver=3.4.1"
    },
    {
      "length": 352663,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/js/vendor.min.js?x81484"
    },
    {
      "length": 54484,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/themes/uph-revamp/assets/js/app.min.js?x81484"
    },
    {
      "length": 4776,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-includes/js/dist/hooks.min.js?x81484&ver=4d63a3d491d11ffd8ac6"
    },
    {
      "length": 9141,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-includes/js/dist/i18n.min.js?x81484&ver=5e580eb46a90c2b997e6"
    },
    {
      "length": 12512,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/plugins/contact-form-7/includes/swv/js/index.js?x81484&ver=6.1.2"
    },
    {
      "length": 13452,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/plugins/contact-form-7/includes/js/index.js?x81484&ver=6.1.2"
    },
    {
      "length": 36013,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/plugins/cf7-conditional-fields/js/scripts.js?x81484&ver=2.6.4"
    },
    {
      "length": 7462,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/plugins/w3-total-cache/pub/js/lazyload.min.js?x81484"
    },
    {
      "length": 290,
      "source_map_url": null,
      "url": "https://www.uph.edu/wp-content/plugins/sitepress-multilingual-cms/res/js/cookies/language-cookie.js?x81484&ver=484900"
    },
    {
      "length": 217782,
      "source_map_url": null,
      "url": "https://js.codedrink.com/lasso-performance.min.js?ver=139.20261110"
    },
    {
      "length": 217782,
      "source_map_url": null,
      "url": "https://js.lasso.link/lasso-performance.min.js?ver=139.20261110"
    },
    {
      "length": 432263,
      "source_map_url": null,
      "url": "https://connect.facebook.net/en_US/fbevents.js"
    },
    {
      "length": 435364,
      "source_map_url": null,
      "url": "https://www.googletagmanager.com/gtag/js?id=AW-880408228"
    },
    {
      "length": 383115,
      "source_map_url": null,
      "url": "https://www.googletagmanager.com/gtag/js?id=UA-124593360-1"
    },
    {
      "length": 184320,
      "source_map_url": null,
      "url": "https://connect.facebook.net/signals/plugins/openbridge3.js?v=2.9.439"
    },
    {
      "length": 550338,
      "source_map_url": null,
      "url": "https://connect.facebook.net/signals/config/1142888552481355?v=2.9.439&r=stable&domain=www.uph.edu&hme=1f127d11ce7bba675ee077e129c6ec7e07038113ad3e920301e60f2f865dc776&ex_m=119%2C243%2C172%2C27%2C82%2C83%2C163%2C78%2C77%2C11%2C182%2C102%2C20%2C154%2C142%2C46%2C85%2C90%2C184%2C111%2C150%2C177%2C8%2C4%2C5%2C7%2C6%2C3%2C103%2C116%2C162%2C72%2C186%2C110%2C185%2C212%2C264%2C37%2C84%2C284%2C283%2C282%2C14%2C25%2C28%2C40%2C62%2C118%2C68%2C10%2C73%2C112%2C113%2C114%2C120%2C145%2C38%2C36%2C147%2C148%2C149%2C144%2C143%2C173%2C86%2C176%2C174%2C175%2C57%2C69%2C67%2C138%2C19%2C180%2C52%2C334%2C335%2C336%2C333%2C32%2C17%2C31%2C18%2C33%2C34%2C35%2C55%2C164%2C87%2C127%2C22%2C24%2C51%2C47%2C49%2C48%2C95%2C104%2C108%2C125%2C161%2C165%2C53%2C126%2C29%2C26%2C134%2C79%2C43%2C167%2C166%2C168%2C158%2C156%2C30%2C42%2C66%2C124%2C179%2C80%2C21%2C129%2C93%2C76%2C23%2C97%2C98%2C131%2C96%2C152%2C151%2C155%2C109%2C178%2C181%2C41%2C350%2C372%2C257%2C239%2C70%2C240%2C238%2C375%2C366%2C59%2C258%2C122%2C146%2C92%2C136%2C61%2C54%2C56%2C128%2C135%2C141%2C140%2C65%2C74%2C169%2C88%2C89%2C130%2C44%2C39%2C60%2C63%2C115%2C183%2C1%2C139%2C16%2C137%2C12%2C2%2C64%2C105%2C15%2C75%2C71%2C133%2C101%2C100%2C188%2C189%2C106%2C107%2C9%2C117%2C187%2C58%2C159%2C99%2C91%2C81%2C132%2C121%2C170%2C50%2C160%2C0%2C94%2C153%2C157%2C171%2C45%2C123%2C13%2C190"
    },
    {
      "length": 572899,
      "source_map_url": null,
      "url": "https://www.googletagmanager.com/gtag/js?id=G-HDFHEX3LDS&cx=c&gtm=4e6b90h2"
    },
    {
      "length": 217405,
      "source_map_url": null,
      "url": "https://www.uph.edu/id/js/snippet.min.js?x81484&ver=139.20261110"
    },
    {
      "length": 217405,
      "source_map_url": null,
      "url": "https://www.uph.edu/id/?abbb8ea4eca5d4fc9ea3f059f9d0fc93=1&ver=139.20261110"
    },
    {
      "length": 447922,
      "source_map_url": null,
      "url": "https://www.googletagmanager.com/gtag/js?id=AW-969965463&cx=c&gtm=4e6b90h2"
    },
    {
      "length": 435375,
      "source_map_url": null,
      "url": "https://www.googletagmanager.com/gtag/js?id=AW-880408228&cx=c&gtm=4e6b90h2"
    },
    {
      "length": 7923,
      "source_map_url": null,
      "url": "https://googleads.g.doubleclick.net/pagead/viewthroughconversion/880408228/?random=1794281569290&cv=11&fst=1794281569290&bg=ffffff&guid=ON&async=1&en=gtag.config&gtm=45be6b61z8d_AW-880408228za208xecxf1&gcd=13l3l3l3l1l1&dma=0&tag_exp=115616985~11
```
