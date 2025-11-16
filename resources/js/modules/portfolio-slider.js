import Glide from '@glidejs/glide';

// ------------------------------ helpers ------------------------------
function setDepthClasses(root, targetIdx) {
  const all = Array.from(root.querySelectorAll('.glide__slides > li'));
  if (!all.length) return;
  
  const n = all.length;
  const at = (i) => all[((i % n) + n) % n];
  
  // Clear all
  all.forEach(li => {
    const slide = li.querySelector('.slide');
    if (slide) {
      slide.classList.remove('is-active','is-prev','is-next');
      // Force browser to acknowledge removal
      void slide.offsetWidth;
    }
  });
  
  // Apply based on provided index
  const activeSlide = at(targetIdx)?.querySelector('.slide');
  const prevSlide = at(targetIdx-1)?.querySelector('.slide');
  const nextSlide = at(targetIdx+1)?.querySelector('.slide');
  
  if (activeSlide) activeSlide.classList.add('is-active');
  if (prevSlide) prevSlide.classList.add('is-prev');
  if (nextSlide) nextSlide.classList.add('is-next');
}

function restartRobot(el) {
  if (!el) return;
  // Hard reset animations on the elements that animate
  const parts = [
    el.querySelector('#bot-bounce'),
    el.querySelector('#robot-shadow'),
    el.querySelector('#antenna'),
    // profile bob/arms when walking may need a kick too:
    el.querySelector('#robot-svg-gallery'),
    el.querySelector('#arm-right'),
    el.querySelector('#arm-left'),
    el.querySelector('#arm-mid'),
  ].filter(Boolean);
  parts.forEach(node => { node.style.animation = 'none'; });
  // force reflow once
  // eslint-disable-next-line no-unused-expressions
  el.offsetWidth;
  parts.forEach(node => { node.style.animation = ''; });
}

function getTargetIndex(glide, e) {
  const n = glide._c.Sizes.length;
  const norm = (i) => ((i % n) + n) % n;

  if (e && typeof e.steps === 'number') return norm(glide.index + e.steps);
  if (e && typeof e.direction === 'string' && typeof e.steps === 'number') {
    return norm(glide.index + (e.direction === '>' ? +e.steps : -e.steps));
  }
  if (e && typeof e.movement === 'string') {
    if (e.movement === '>') return norm(glide.index + 1);
    if (e.movement === '<') return norm(glide.index - 1);
    const eq = e.movement.match(/^=(\d+)$/);
    if (eq) return norm(parseInt(eq[1], 10));
  }
  return norm(glide.index);
}

function mountWheelSwipe(glide, root, setPendingDir) {
  const track = root.querySelector('.glide__track');
  if (!track) return;

  let acc = 0;
  const STEP = 120;
  const COOLDOWN = 250;
  let cooling = false;

  const onWheel = (e) => {
    if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) return;
    e.preventDefault();
    if (cooling) return;

    acc += e.deltaX;

    if (acc <= -STEP) {
      acc = 0;
      setPendingDir('left');
      glide.go('<');
      cooling = true;
      setTimeout(() => (cooling = false), COOLDOWN);
    } else if (acc >= STEP) {
      acc = 0;
      setPendingDir('right');
      glide.go('>');
      cooling = true;
      setTimeout(() => (cooling = false), COOLDOWN);
    }
  };

  track.addEventListener('wheel', onWheel, { passive: false });
  glide.on('destroy', () => {
    track.removeEventListener('wheel', onWheel, { passive: false });
  });
}

function mountTouchDir(root, setPendingDir) {
  const track = root.querySelector('.glide__track');
  if (!track) return;

  let startX = 0, startY = 0;
  const THRESH = 18;

  const onStart = (e) => {
    const t = e.touches && e.touches[0];
    if (!t) return;
    startX = t.clientX;
    startY = t.clientY;
  };

  const onMove = (e) => {
    const t = e.touches && e.touches[0];
    if (!t) return;
    const dx = t.clientX - startX;
    const dy = t.clientY - startY;

    if (Math.abs(dx) > Math.abs(dy) && Math.abs(dx) > THRESH) {
      setPendingDir(dx < 0 ? 'right' : 'left');
    }
  };

  track.addEventListener('touchstart', onStart, { passive: true });
  track.addEventListener('touchmove',  onMove,  { passive: true });
}

// Palette utils (local to this file)
const hexFromInt = (i) => '#' + ((i >>> 0).toString(16).padStart(6, '0'));
const VANTA_BASE = { color: 0x007074, color2: 0xe45b31, backgroundColor: 0xe3d9cf };
const DISCO_PALETTES = [
  { color: 0xff3b3b, color2: 0x00e7ff, backgroundColor: 0x0d0221 },
  { color: 0x9dff00, color2: 0xff00e6, backgroundColor: 0x011627 },
  { color: 0xffd166, color2: 0x06d6a0, backgroundColor: 0x1a1a1a },
  { color: 0x845ec2, color2: 0xff6f91, backgroundColor: 0x101018 },
  { color: 0x00f5d4, color2: 0xf15bb5, backgroundColor: 0x0b132b },
];

// Compatible hook to desktop Vanta if available
const applyVantaOptions =
  (globalThis.applyVantaOptions) ||
  (window && (window.applyVantaOptions || window.__applyVantaOptions)) ||
  null;

if (!globalThis.__discoGateInit) {
  globalThis.__discoGateInit   = true;
  globalThis.__discoGateOpened = false;
  setTimeout(() => {
    globalThis.__discoGateOpened = true;
    window.dispatchEvent(new Event('discoGateOpened'));
  }, 5000); // 5s stall after page load
}

// ------------------------------ main ------------------------------
export function mountPortfolioSliders() {
  document.querySelectorAll('[data-portfolio-glide]').forEach(root => {
    const autoplay = parseInt(root.dataset.autoplay || '0', 10) || 0;

    const originals = Array.from(root.querySelectorAll('.glide__slides > li'))
      .filter(li => !li.classList.contains('glide__slide--clone'));
    originals.forEach((li, i) => { li.dataset.idx = String(i); });

    const n = originals.length;
    const pv3 = Math.min(3, n);
    const pv1 = 1;

    const glide = new Glide(root, {
      type: 'carousel',
      startAt: 0,
      perView: pv3,
      focusAt: 'center',
      gap: 50,
      peek: { before: 30,  after: 30  },
      autoplay,
      hoverpause: true,
      perTouch: 1,
      swipeThreshold: 40,
      dragThreshold: 10,
      animationTimingFunc: 'cubic-bezier(.22,.61,.36,1)',
      animationDuration: 1500,
      keyboard: true,
      breakpoints: {
        1280: { perView: pv3, gap: 50, peek: { before: 30,  after: 30  } },
        1024: { perView: pv3, gap: 50, peek: { before: 30,  after: 30  } },
        900:  { perView: pv3, gap: 50, peek: { before: 30,  after: 30  } },
        768:  { perView: pv1, gap: 50, peek: { before: 48,  after: 48  } },
        520:  { perView: pv1, gap: 50, peek: { before: 36,  after: 36  } },
      },
    });

    // Robot posture hooks (unchanged)
    const robotWrap = document.getElementById('robot-wrap');
    const setPose = (cls) => { if (robotWrap) { robotWrap.classList.remove('dir-left','dir-right','dir-neutral'); robotWrap.classList.add(cls); } };
    const startWalking = (dir) => { if (robotWrap) { setPose(dir === 'left' ? 'dir-left' : 'dir-right'); robotWrap.classList.add('walking'); } };
    const stopWalkingToNeutral = () => {
       if (!robotWrap) return;
       robotWrap.classList.remove('walking');
       setPose('dir-neutral');           // switch profile immediately
       // (Optional) poke layout for Safari so animations re-arm cleanly
       // eslint-disable-next-line no-unused-expressions
       robotWrap.offsetWidth;
    };

    // Limit the background/mini-globe behavior to the MOBILE slider block
    const isMobileSlider = !!root.closest('.home-mobile-slider');

    // ---------- Mobile disco loop (runs only on dance slide) ----------
    let mobileDiscoTimer = null;
    let mobilePalIdx = 0;

    function applyMobilePalette(pal, { lightText = false } = {}) {

      const miniHost = document.querySelector('[data-slide="globe"] .mini-globe');
      const ctl = miniHost && miniHost.__miniGlobeCtl;
      if (ctl) ctl.setPalette({ color: pal.color }); 
      const section = document.querySelector('.home-mobile-slider');
      if (section) section.style.backgroundColor = hexFromInt(pal.backgroundColor);
    }

    function startMobileDisco() {
      if (mobileDiscoTimer) return;
      mobilePalIdx = 0;
      applyMobilePalette(DISCO_PALETTES[mobilePalIdx % DISCO_PALETTES.length]);
      mobileDiscoTimer = setInterval(() => {
        mobilePalIdx++;
        applyMobilePalette(DISCO_PALETTES[mobilePalIdx % DISCO_PALETTES.length]);
      }, 1000);
    }

    function stopMobileDisco() {
      if (!mobileDiscoTimer) return;
      clearInterval(mobileDiscoTimer);
      mobileDiscoTimer = null;
      applyMobilePalette(VANTA_BASE);
    }

    // ---------- Dance state updater ----------
    const updateDanceState = () => {
      if (!isMobileSlider || !robotWrap) return;

      const curr = root.querySelector('.glide__slides > li.glide__slide--active');
      const onDanceSlide = !!(curr && curr.dataset && curr.dataset.slide === 'globe');
      const gateOpen = !!globalThis.__discoGateOpened;

      // Only dance if on the slide & the gate has opened
      const shouldDance = onDanceSlide && gateOpen;

      const wasDancing = robotWrap.classList.contains('dance');
      robotWrap.classList.toggle('dance', shouldDance);
      if (shouldDance && !wasDancing) restartRobot(robotWrap);

      const section = document.querySelector('.home-mobile-slider');
      if (section) section.classList.toggle('light', shouldDance);

      // Desktop Vanta + mobile palette loop
      const pal = shouldDance ? DISCO_PALETTES[0] : VANTA_BASE;
      if (typeof applyVantaOptions === 'function') applyVantaOptions(pal);
      if (shouldDance) startMobileDisco(); else stopMobileDisco();
    };

    // Wait 5s before dancing begins
    const onGateOpen = () => updateDanceState();
    window.addEventListener('discoGateOpened', onGateOpen);

    // ------------------------------ events ------------------------------
    const btnLeft  = root.querySelector('.glide__arrow--left');
    const btnRight = root.querySelector('.glide__arrow--right');
    let pendingDir = null;
    const markLeft  = () => { pendingDir = 'left';  };
    const markRight = () => { pendingDir = 'right'; };
    btnLeft?.addEventListener('pointerdown', markLeft,  { passive: true });
    btnLeft?.addEventListener('click',       markLeft,  { passive: true });
    btnRight?.addEventListener('pointerdown',markRight, { passive: true });
    btnRight?.addEventListener('click',      markRight, { passive: true });

    const nSlides = originals.length || 1;
    const norm = (i) => ((i % nSlides) + nSlides) % nSlides;
    const shortestDir = (curr, target) => {
      const f = (target - curr + nSlides) % nSlides;
      const b = (curr - target + nSlides) % nSlides;
      if (f === 0 && b === 0) return null;
      return f <= b ? 'right' : 'left';
    };
    const getDirection = (glide, e) => {
      if (pendingDir) { const d = pendingDir; pendingDir = null; return d; }
      if (e && typeof e.steps === 'number')     return e.steps > 0 ? 'right' : 'left';
      if (e && typeof e.direction === 'string') return e.direction === '>' ? 'right' : 'left';
      if (e && typeof e.movement === 'string') {
        if (e.movement === '>') return 'right';
        if (e.movement === '<') return 'left';
        const m = e.movement.match(/^=(\d+)$/);
        if (m) return shortestDir(glide.index, norm(parseInt(m[1], 10)));
      }
      const target = getTargetIndex(glide, e);
      return shortestDir(glide.index, target);
    };

    glide.on('mount.after', () => {
      setDepthClasses(root, glide.index);
      setPose('dir-neutral');
      updateDanceState();
    });

    glide.on('run', (e) => {
      // entering transition: ensure walking anim is live on WebKit
      restartRobot(robotWrap);
      startWalking(getDirection(glide, e));
    });

    glide.on('run.after', () => {
      setDepthClasses(root, glide.index);
      stopWalkingToNeutral();
      updateDanceState();
    });

    glide.on('resize', () => {
      setDepthClasses(root, glide.index);
      restartRobot(robotWrap);
      updateDanceState();
    });
    glide.on('destroy', () => {
      // make sure we stop the mobile loop if the slider is torn down
      const section = document.querySelector('.home-mobile-slider');
      if (section) section.style.backgroundColor = hexFromInt(VANTA_BASE.backgroundColor);
      if (mobileDiscoTimer) { clearInterval(mobileDiscoTimer); mobileDiscoTimer = null; }
      window.removeEventListener('discoGateOpened', onGateOpen);
    });

    const setPendingDirFromInput = (dir) => { pendingDir = dir; };
    mountWheelSwipe(glide, root, setPendingDirFromInput);
    mountTouchDir(root, setPendingDirFromInput);

    glide.mount();
  });
}

(() => {
  const g = document.querySelector('#robot-svg-gallery');
  const eyelidG = g?.querySelector('#eyelid');
  if (!eyelidG) return;
  const min = 2500, max = 6000;
  const schedule = () => {
    const delay = Math.floor(Math.random() * (max - min)) + min;
    setTimeout(() => {
      eyelidG.setAttribute('height', '4');   // close
      setTimeout(() => {
        eyelidG.setAttribute('height', '0'); // open
        schedule();
      }, 90);
    }, delay);
  };
  schedule();
})();
