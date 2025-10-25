import.meta.glob(['../images/**', '../fonts/**'])
import '../css/tailwind.css'
import '../css/app.scss'
import GLOBE from 'vanta/dist/vanta.globe.min'
import * as THREE from 'three'
import Glide from '@glidejs/glide'
import {mountPortfolioSliders} from './modules/portfolio-slider';
import { mountPixelRobot } from './modules/cursor-robot';
import { initVantaHotspots } from './modules/vanta-globe';


document.addEventListener('DOMContentLoaded', () => {
  window.__robotCtl = mountPixelRobot({
    hoverSelector: 'a, button, [role="button"], .glide__arrow, .vanta-hotspot .label a'
  });

  initVantaHotspots({
     elSelector: '#vanta-bg',
     overlaySelector: '#vanta-hotspots',
    THREE,
    GLOBE,
   });
});

document.addEventListener('DOMContentLoaded', () => {
  const el = document.querySelector('.glide.hero-reset')
  if (!el) return

  new Glide(el, {
    type: 'carousel',
    perView: 1,
    focusAt: 'center',
    peek: { before: 120, after: 120 },
    gap: 24,
    animationDuration: 600,
    animationTimingFunc: 'cubic-bezier(.2,.65,.3,1)',
    hoverpause: true,
    autoplay: 3600
  }).mount()
})


document.addEventListener('DOMContentLoaded', () => {
  mountPortfolioSliders();
});
