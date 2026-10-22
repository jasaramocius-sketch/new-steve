---
name: swiper-slider-migration
description: Use when migrating sliders from Slick.js to Swiper.js, fixing slider item widths, preventing FOUC on sliders, setting up product card sliders, category sliders, or product gallery sliders with thumbnails. Covers freeMode, grabCursor, swipeToSlide, centeredSlides, and consistent slide width.
---

# Swiper.js Slider Migration

## Key Rule: Slide Width Must Be Controlled by Swiper

**Never** set `width: auto !important` or `flex: 0 0 auto !important` on `.swiper-slide` — these override Swiper's inline width calculation based on `slidesPerView`.

**Correct approach**: Let Swiper handle width. Only add `flex-shrink: 0` to prevent slides from shrinking.

## Setup

### Files
- CSS: `public/assets/front/css/swiper-bundle.min.css` (local)
- JS: `public/assets/front/js/swiper-bundle.min.js` (local)
- Custom CSS: `public/assets/front/css/custom.css`
- Custom JS: `public/assets/front/js/script.js`

### HTML Structure
```html
<div class="swiper my-slider">
  <div class="swiper-wrapper">
    <div class="swiper-slide">...</div>
    <div class="swiper-slide">...</div>
  </div>
  <!-- Navigation -->
  <button class="swiper-button-prev"></button>
  <button class="swiper-button-next"></button>
</div>
```

**Important**: Remove Bootstrap `col-*` classes from `.swiper-slide` — they interfere with Swiper width.

## Product Card Slider (Featured / Best Selling)

```javascript
new Swiper('.featured-products.product-cards-slider', {
  slidesPerView: 4,
  spaceBetween: 24,
  freeMode: true,
  grabCursor: true,
  swipeToSlide: true,
  touchThreshold: 5,
  navigation: {
    prevEl: '.featured-prev',
    nextEl: '.featured-next',
  },
  breakpoints: {
    1200: { slidesPerView: 3 },
    992: { slidesPerView: 2 },
    576: { slidesPerView: 1 },
  },
});
```

### CSS for Product Cards
```css
.product-cards-slider .swiper-wrapper {
  display: flex;
}
.product-cards-slider .swiper-slide {
  padding: 0 12px;
  box-sizing: border-box;
  flex-shrink: 0;
  /* DO NOT add width: auto !important or flex: 0 0 auto !important */
}
.product-cards-slider .single-product .img-wrapper .product-img {
  width: 100%;
  height: 260px;
  object-fit: cover;
}
```

## Category Slider

```javascript
new Swiper('.home-cate-slider', {
  slidesPerView: 6,
  spaceBetween: 12,
  freeMode: true,
  grabCursor: true,
  navigation: {
    prevEl: '.cate-prev',
    nextEl: '.cate-next',
  },
  breakpoints: {
    1200: { slidesPerView: 6 },
    992: { slidesPerView: 4 },
    768: { slidesPerView: 3 },
    576: { slidesPerView: 2 },
  },
});
```

### CSS for Categories
```css
.home-cate-slider .category-image {
  height: 200px;
  width: 200px;
  margin-bottom: 20px;
  overflow: hidden;
}
.home-cate-slider .category-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.home-cate-slider .swiper-slide {
  padding: 0 6px;
  box-sizing: border-box;
  flex-shrink: 0;
}
```

## Product Gallery Slider (Nav + Thumb)

```javascript
// Nav slider (thumbnails at bottom)
var galleryNav = new Swiper('.details_slider_nav', {
  slidesPerView: 4,
  spaceBetween: 10,
  allowTouchMove: false,    // Prevent accidental swipe
  centeredSlides: true,     // Active thumbnail centered
  centeredSlidesBounds: true,
  breakpoints: {
    992: { slidesPerView: 3 },
    768: { slidesPerView: 2 },
  },
});

// Main image slider
var thumbSwiper = new Swiper('.details_slider_thumb', {
  slidesPerView: 1,
  spaceBetween: 0,
  effect: 'fade',
  fadeEffect: { crossFade: true },
  grabCursor: true,
  thumbs: { swiper: galleryNav },
});

// Manual centering for nav slider
function centerNavSlide(index) {
  var slide = galleryNav.slides[index];
  if (!slide) return;
  var offset = slide.offsetLeft - (galleryNav.el.offsetWidth - slide.offsetWidth) / 2;
  offset = Math.max(0, Math.min(offset, galleryNav.wrapperEl.scrollWidth - galleryNav.el.offsetWidth));
  galleryNav.wrapperEl.style.transition = 'transform 300ms ease';
  galleryNav.setTranslate(-offset);
}
galleryNav.on('click', function(swiper) {
  thumbSwiper.slideTo(swiper.clickedIndex);
});
```

## FOUC Prevention

```css
/* In app.blade.php <style> block */
.home-cate-slider:not(.swiper-initialized),
.product-cards-slider:not(.swiper-initialized),
.details_slider_thumb:not(.swiper-initialized),
.details_slider_nav:not(.swiper-initialized) {
  visibility: hidden;
  opacity: 0;
}
```

## Smooth Cursor Scroll

```css
/* Grab cursor for all sliders except gallery nav */
.swiper:not(.details_slider_nav):not(.details_slider_thumb) {
  cursor: grab;
}
.swiper:not(.details_slider_nav):not(.details_slider_thumb):active {
  cursor: grabbing;
}
```

## Common Pitfalls

1. **`width: auto !important` kills Swiper width** — Swiper sets width via inline style, `!important` overrides it
2. **Bootstrap `col-*` on swiper-slide** — remove them, they add conflicting `max-width`
3. **`freeMode` + `grabCursor`** — needed for smooth cursor-based scrolling
4. **`swipeToSlide: true`** — allows swiping through slides freely
5. **Gallery nav needs `allowTouchMove: false`** — prevents accidental swipe
6. **Slick CSS is dead code** — old `.slick-*` rules don't affect Swiper but should be cleaned up
