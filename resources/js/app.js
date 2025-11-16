import.meta.glob(['../images/**', '../fonts/**'])
import '../css/tailwind.css'
import '../css/app.scss'
import GLOBE from 'vanta/dist/vanta.globe.min'
import * as THREE from 'three'
import Glide from '@glidejs/glide'
import { mountPortfolioSliders } from './modules/portfolio-slider';
import { mountPixelRobot } from './modules/cursor-robot';
import { initVantaHotspots } from './modules/vanta-globe';
import { mountMiniGlobe } from './modules/mini-globe';

document.addEventListener('DOMContentLoaded', () => {
  // Robot cursor
  window.__robotCtl = mountPixelRobot({
    hoverSelector: 'a, button, [role="button"], .glide__arrow, .vanta-hotspot .label a'
  });

  // Vanta background + hotspots (home page only)
  if (document.querySelector('#vanta-bg')) {
    initVantaHotspots({
      elSelector: '#vanta-bg',
      overlaySelector: '#vanta-hotspots',
      THREE,
      GLOBE,
    });
  }

  // Hero slider (if exists)
  const heroEl = document.querySelector('.glide.hero-reset');
  if (heroEl) {
    new Glide(heroEl, {
      type: 'carousel',
      perView: 1,
      focusAt: 'center',
      peek: { before: 120, after: 120 },
      gap: 24,
      animationDuration: 600,
      animationTimingFunc: 'cubic-bezier(.2,.65,.3,1)',
      hoverpause: true,
      autoplay: 3600
    }).mount();
  }

  // Portfolio sliders (if exist)
  if (document.querySelector('[data-portfolio-glide]')) {
    mountPortfolioSliders();
  }

  // Mini globe for home page
  const miniGlobeHost = document.querySelector('.home-glide-portfolio [data-slide="globe"] .mini-globe');
  if (miniGlobeHost) {
    if (!window.THREE) window.THREE = THREE;
    mountMiniGlobe(miniGlobeHost, { spin: 0.0035 });
  }
});