import Glide from '@glidejs/glide';

function setDepthClasses(root, activeIdx) {
  const lis = Array.from(root.querySelectorAll('.glide__slides > li'));
  if (!lis.length) return;

  const uniq = [...new Set(lis.map(li => li.dataset.idx))].map(Number).filter(n => !Number.isNaN(n));
  const n = uniq.length;
  if (!n) return;

  const idx = (i) => ((i % n) + n) % n;
  const a = idx(activeIdx);

  const addFor = (i, cls) => {
    const target = String(idx(i));
    lis.forEach(li => {
      if (li.dataset.idx === target) li.querySelector('.slide')?.classList.add(cls);
    });
  };

  lis.forEach(li => li.querySelector('.slide')?.classList.remove('is-active','is-prev','is-next','is-2prev','is-2next'));
  addFor(a, 'is-active');
  addFor(a - 1, 'is-prev');
  addFor(a + 1, 'is-next');
  if (n > 3) { addFor(a - 2, 'is-2prev'); addFor(a + 2, 'is-2next'); }
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
      gap: 20,
      peek: { before: 120, after: 120 },
      autoplay,
      hoverpause: true,
      perTouch: 1,
      swipeThreshold: 40,
      dragThreshold: 10,
      animationTimingFunc: 'cubic-bezier(.22,.61,.36,1)',
      animationDuration: 1500,
      keyboard: true,
      breakpoints: {
        1280: { perView: pv3, gap: 32, peek: { before: 100, after: 100 } },
        1024: { perView: pv3, gap: 28, peek: { before: 80,  after: 80  } },
        900:  { perView: pv3, gap: 28, peek: { before: 70,  after: 70  } },
        768:  { perView: pv1, gap: 22, peek: { before: 48,  after: 48  } },
        520:  { perView: pv1, gap: 18, peek: { before: 36,  after: 36  } },
      },
    });

    const robotWrap = document.getElementById('robot-wrap');
    const setPose = (cls) => { if (robotWrap) { robotWrap.classList.remove('dir-left','dir-right','dir-neutral'); robotWrap.classList.add(cls); } };
    const startWalking = (dir) => { if (robotWrap) { setPose(dir === 'left' ? 'dir-left' : 'dir-right'); robotWrap.classList.add('walking'); } };
    const stopWalkingToNeutral = () => { if (robotWrap) { robotWrap.classList.remove('walking'); setTimeout(() => setPose('dir-neutral'), 60); } };

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
      return (f || b) && f <= b ? 'right' : 'left';
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
    });

    glide.on('run', (e) => {
      const target = getTargetIndex(glide, e);
      setDepthClasses(root, target);
      startWalking(getDirection(glide, e));
    });

    glide.on('run.after', () => {
      setDepthClasses(root, glide.index);
      stopWalkingToNeutral();
    });

    glide.on('resize', () => setDepthClasses(root, glide.index));

    glide.mount();
  });
}
