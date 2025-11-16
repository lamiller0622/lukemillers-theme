import Glide from '@glidejs/glide';

// ------------------------------ helpers ------------------------------
function setDepthClasses(root, targetIdx) {
  const slides = root.querySelectorAll('.glide__slides > li .slide');
  const n = slides.length;
  
  slides.forEach((slide, i) => {
    slide.classList.remove('is-active', 'is-prev', 'is-next');
    const norm = ((targetIdx % n) + n) % n;
    if (i === norm) slide.classList.add('is-active');
    if (i === ((norm - 1 + n) % n)) slide.classList.add('is-prev');
    if (i === ((norm + 1) % n)) slide.classList.add('is-next');
  });
}

function restartRobot(el) {
  if (!el) return;
  const parts = el.querySelectorAll('#bot-bounce, #robot-shadow, #antenna, #robot-svg-gallery, #arm-right, #arm-left, #arm-mid');
  parts.forEach(node => { 
    node.style.animation = 'none';
    void el.offsetWidth;
    node.style.animation = '';
  });
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
  glide.on('destroy', () => track.removeEventListener('wheel', onWheel));
}

const hexFromInt = (i) => '#' + ((i >>> 0).toString(16).padStart(6, '0'));
const VANTA_BASE = { color: 0x007074, color2: 0xe45b31, backgroundColor: 0xe3d9cf };
const DISCO_PALETTES = [
  { color: 0xff3b3b, color2: 0x00e7ff, backgroundColor: 0x0d0221 },
  { color: 0x9dff00, color2: 0xff00e6, backgroundColor: 0x011627 },
  { color: 0xffd166, color2: 0x06d6a0, backgroundColor: 0x1a1a1a },
  { color: 0x845ec2, color2: 0xff6f91, backgroundColor: 0x101018 },
  { color: 0x00f5d4, color2: 0xf15bb5, backgroundColor: 0x0b132b },
];

if (!globalThis.__discoGateInit) {
  globalThis.__discoGateInit = true;
  globalThis.__discoGateOpened = false;
  setTimeout(() => {
    globalThis.__discoGateOpened = true;
    window.dispatchEvent(new Event('discoGateOpened'));
  }, 5000);
}

// ------------------------------ main ------------------------------
export function mountPortfolioSliders() {
  document.querySelectorAll('[data-portfolio-glide]').forEach(root => {
    const n = root.querySelectorAll('.glide__slides > li').length;
    const isMobile = root.closest('.home-mobile-slider');

    const glide = new Glide(root, {
      type: 'carousel',
      startAt: 0,
      perView: Math.min(3, n),
      focusAt: 'center',
      gap: 50,
      peek: { before: 30, after: 30 },
      autoplay: parseInt(root.dataset.autoplay || '0', 10) || 0,
      hoverpause: true,
      perTouch: 1,
      swipeThreshold: 40,
      dragThreshold: 10,
      animationTimingFunc: 'cubic-bezier(.22,.61,.36,1)',
      animationDuration: 1500,
      keyboard: true,
      breakpoints: {
        900: { perView: Math.min(3, n), gap: 50, peek: { before: 30, after: 30 } },
        768: { perView: 1, gap: 50, peek: { before: 48, after: 48 } },
        520: { perView: 1, gap: 50, peek: { before: 36, after: 36 } },
      },
    });

    const robotWrap = document.getElementById('robot-wrap');
    let pendingDir = null;
    
    const setPose = (cls) => {
      if (!robotWrap) return;
      robotWrap.classList.remove('dir-left', 'dir-right', 'dir-neutral');
      robotWrap.classList.add(cls);
    };
    
    const startWalking = (dir) => {
      if (!robotWrap) return;
      setPose(dir === 'left' ? 'dir-left' : 'dir-right');
      robotWrap.classList.add('walking');
    };
    
    const stopWalking = () => {
      if (!robotWrap) return;
      robotWrap.classList.remove('walking');
      setPose('dir-neutral');
      void robotWrap.offsetWidth;
    };
    
    const getDirection = (e) => {
      if (pendingDir) {
        const d = pendingDir;
        pendingDir = null;
        return d;
      }
      if (e?.direction === '>') return 'right';
      if (e?.direction === '<') return 'left';
      return 'right';
    };
    
    // Mobile disco
    let discoTimer = null;
    let palIdx = 0;
    
    const applyPalette = (pal) => {
      const miniGlobe = document.querySelector('[data-slide="globe"] .mini-globe');
      if (miniGlobe?.__miniGlobeCtl) miniGlobe.__miniGlobeCtl.setPalette({ color: pal.color });
      
      const section = document.querySelector('.home-mobile-slider');
      if (section) section.style.backgroundColor = hexFromInt(pal.backgroundColor);
      
      const applyVanta = window.applyVantaOptions || window.__applyVantaOptions;
      if (applyVanta) applyVanta(pal);
    };
    
    const startDisco = () => {
      if (discoTimer) return;
      palIdx = 0;
      applyPalette(DISCO_PALETTES[0]);
      discoTimer = setInterval(() => {
        palIdx = (palIdx + 1) % DISCO_PALETTES.length;
        applyPalette(DISCO_PALETTES[palIdx]);
      }, 1000);
    };
    
    const stopDisco = () => {
      if (discoTimer) clearInterval(discoTimer);
      discoTimer = null;
      applyPalette(VANTA_BASE);
    };
    
    const updateDance = () => {
      if (!isMobile || !robotWrap) return;
      
      const active = root.querySelector('.glide__slide--active');
      const onDance = active?.dataset?.slide === 'globe' && globalThis.__discoGateOpened;
      
      const wasDancing = robotWrap.classList.contains('dance');
      robotWrap.classList.toggle('dance', onDance);
      if (onDance && !wasDancing) restartRobot(robotWrap);
      
      document.querySelector('.home-mobile-slider')?.classList.toggle('light', onDance);
      
      onDance ? startDisco() : stopDisco();
    };

    // Arrow clicks
    const btnLeft = root.querySelector('.glide__arrow--left');
    const btnRight = root.querySelector('.glide__arrow--right');
    btnLeft?.addEventListener('click', () => { pendingDir = 'left'; }, { passive: true });
    btnRight?.addEventListener('click', () => { pendingDir = 'right'; }, { passive: true });

    // Trackpad swipe
    mountWheelSwipe(glide, root, (dir) => { pendingDir = dir; });

    // Events
    glide.on('mount.after', () => {
      setDepthClasses(root, glide.index);
      setPose('dir-neutral');
      updateDance();
    });
    
    glide.on('run', (e) => {
      restartRobot(robotWrap);
      startWalking(getDirection(e));
    });
    
    glide.on('run.after', () => {
      stopWalking();
      updateDance();
    });
    
    glide.on('resize', () => {
      setDepthClasses(root, glide.index);
      restartRobot(robotWrap);
      updateDance();
    });
    
    glide.on('destroy', () => {
      stopDisco();
      window.removeEventListener('discoGateOpened', updateDance);
    });

    window.addEventListener('discoGateOpened', updateDance);
    glide.mount();
  });
}

// Eyelid blink
(() => {
  const g = document.querySelector('#robot-svg-gallery');
  if (!g) return; // Add this check
  
  const eyelidG = g.querySelector('#eyelid');
  if (!eyelidG) return;
  
  const min = 2500, max = 6000;
  const blink = () => {
    setTimeout(() => {
      eyelidG.setAttribute('height', '4');
      setTimeout(() => {
        eyelidG.setAttribute('height', '0');
        blink();
      }, 90);
    }, 2500 + Math.random() * 3500);
  };
  blink();
})();