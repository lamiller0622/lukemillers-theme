import Glide from '@glidejs/glide';

function setDepthClasses(root, activeIdx) {
  const lis = Array.from(root.querySelectorAll('.glide__slides > li'));
  if (!lis.length) return;

  // derive total from unique data-idx
  const uniqIdx = [...new Set(lis.map(li => li.dataset.idx))].map(Number).filter(n => !Number.isNaN(n));
  const n = uniqIdx.length;
  if (!n) return;

  const idx = (i) => ((i % n) + n) % n;
  const a = idx(activeIdx);

  // helper to add a class to every (real or clone) slide with data-idx
  const addFor = (i, cls) => {
    const target = String(idx(i));
    lis.forEach(li => {
      if (li.dataset.idx === target) {
        const card = li.querySelector('.slide');
        if (card) card.classList.add(cls);
      }
    });
  };

  // clear classes from ALL cards first
  lis.forEach(li => {
    const card = li.querySelector('.slide');
    if (card) card.classList.remove('is-active','is-prev','is-next','is-2prev','is-2next');
  });

  addFor(a,     'is-active');
  addFor(a - 1, 'is-prev');
  addFor(a + 1, 'is-next');
  if (n > 3) {
    addFor(a - 2, 'is-2prev');
    addFor(a + 2, 'is-2next');
  }
}

// helper: figure out the target index from Glide's run event
function getTargetIndex(glide, e) {
  const n = glide._c.Sizes.length; // total (real) slides from Sizes component
  const norm = (i) => ((i % n) + n) % n;

  // Different Glide builds pass different shapes. Handle the common ones.
  if (e && typeof e.steps === 'number') {
    return norm(glide.index + e.steps);
  }
  if (e && typeof e.direction === 'string' && typeof e.steps === 'number') {
    return norm(glide.index + (e.direction === '>' ? +e.steps : -e.steps));
  }
  if (e && typeof e.movement === 'string') {
    const m = e.movement;
    if (m === '>')  return norm(glide.index + 1);
    if (m === '<')  return norm(glide.index - 1);
    const eq = m.match(/^=(\d+)$/);
    if (eq)        return norm(parseInt(eq[1], 10));
  }

  // fallback: current
  return norm(glide.index);
}

export function mountPortfolioSliders() {
  document.querySelectorAll('[data-portfolio-glide]').forEach(root => {
    const autoplay = parseInt(root.dataset.autoplay || '0', 10) || 0;

    // tag originals so clones inherit data-idx
    const originals = Array.from(root.querySelectorAll('.glide__slides > li'))
      .filter(li => !li.classList.contains('glide__slide--clone'));
    originals.forEach((li, i) => { li.dataset.idx = String(i); });

    const n  = originals.length;
    const pv3 = Math.min(3, n), pv2 = Math.min(2, n), pv1 = 1;

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
      animationDuration: 700,
      keyboard: true,
      breakpoints: {
        1280: { perView: pv3, gap: 32, peek: { before: 100, after: 100 } },
        1024: { perView: pv3, gap: 28, peek: { before: 80,  after: 80  } },
        900:  { perView: pv3, gap: 28, peek: { before: 70,  after: 70  } },
        768:  { perView: pv1, gap: 22, peek: { before: 48,  after: 48  } },
        520:  { perView: pv1, gap: 18, peek: { before: 36,  after: 36  } },
      },
    });

    // initial
    glide.on('mount.after', () => setDepthClasses(root, glide.index));

    // ⟵ NEW: set classes to the TARGET layout before animation starts
    glide.on('run', (e) => {
      const target = getTargetIndex(glide, e);
      setDepthClasses(root, target);
    });

    // final sanity set after the move completes
    glide.on('run.after', () => setDepthClasses(root, glide.index));

    glide.on('resize', () => setDepthClasses(root, glide.index));
    glide.mount();

    // keep your wheel prevent + IO eager image here (unchanged)…
  });
}
