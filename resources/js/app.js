import.meta.glob(['../images/**', '../fonts/**'])
import '../css/tailwind.css'
import '../css/app.scss'
import GLOBE from 'vanta/dist/vanta.globe.min'
import * as THREE from 'three'
import Glide from '@glidejs/glide'
import {mountPortfolioSliders} from './modules/portfolio-slider';
import { mountPixelRobot } from './modules/cursor-robot';
import { initVantaHotspots } from './modules/vanta-globe';
import { mountMiniGlobe } from './modules/vanilla-globe';


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


export function hookMiniGlobeToGlide(glide, { rootSelector = '.glide-mobile' } = {}) {
  const mql = window.matchMedia('(max-width: 767.98px)');
  if (!mql.matches) return; // mobile only

  const root  = document.querySelector(rootSelector);
  const slides = root?.querySelectorAll('.glide__slide') || [];
  const globeIndex = Array.from(slides).findIndex(s => s.dataset.slide === 'globe');
  if (globeIndex < 0) return;

  const host = slides[globeIndex].querySelector('.mini-globe');
  if (!host) return;

  let globe = null;
  const ensure = () => {
    if (!globe) globe = mountMiniGlobe(host, { spin: 0.003 });
    if (glide.index === globeIndex) globe?.resume(); else globe?.pause();
  };

  glide.on('mount.after', ensure);
  glide.on('run.after', ensure);
  glide.on('destroy', () => { globe?.destroy(); globe = null; });

  // if user resizes out of mobile, clean up
  mql.addEventListener?.('change', (e) => {
    if (!e.matches) { globe?.destroy(); globe = null; }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  // mount after your portfolio slider exists in the DOM
  const host = document.querySelector('.home-glide-portfolio [data-slide="globe"] .mini-globe');
  if (host) {
    // make sure window.THREE exists; Vanta init usually sets it.
    if (!window.THREE) window.THREE = THREE;
    mountMiniGlobe(host, { spin: 0.0035 });
  }
});