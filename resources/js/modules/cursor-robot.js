/**
 * Tiny pixel-robot cursor
 * - Follows pointer with easing
 * - Blinks occasionally
 * - Adds .cursor-hover + .robot-wave on interactive hovers
 * Returns an unmount() you can call to clean up.
 */
export function mountPixelRobot({
  hoverSelector   = 'a, button, [role="button"], .glide__arrow',
  size            = 64,
  ease            = 0.18,
  blinkEveryMin   = 2500,
  blinkEveryMax   = 6000,
} = {}) {
  // Respect device & a11y
  if (window.matchMedia('(pointer: coarse)').matches) return () => {};
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return () => {};

  const host   = document.getElementById('robot-cursor');
  const eyelid = document.getElementById('eyelid');
  if (!host || !eyelid) return () => {};

  // Size & reveal
  host.style.width  = `${size}px`;
  host.style.height = `${size}px`;
  document.documentElement.classList.add('robot-cursor-ready');
  document.body.classList.add('robot-cursor-ready');
  host.style.opacity = '1';

  // ---- Follow pointer ---------------------------------------------------
  let tx = window.innerWidth / 2;
  let ty = window.innerHeight / 2;
  let x  = tx;
  let y  = ty;
  let rafId;

  const updateTarget = (e) => {
    // Use clientX/Y so it’s viewport-relative (works with fixed positioning)
    tx = e.clientX;
    ty = e.clientY;
  };

  // Prefer pointer events; add mousemove as a harmless fallback
  window.addEventListener('pointermove', updateTarget, { passive: true });
  window.addEventListener('mousemove',   updateTarget, { passive: true });

  // Start at first real mouse position (optional; otherwise starts centered)
  const seedOnce = (e) => {
    updateTarget(e);
    window.removeEventListener('mousemove', seedOnce);
  };
  window.addEventListener('mousemove', seedOnce, { once: true, passive: true });

  const tick = () => {
    x += (tx - x) * ease;
    y += (ty - y) * ease;
    host.style.left = `${Math.round(x)}px`;   
    host.style.top  = `${Math.round(y)}px`;
    rafId = requestAnimationFrame(tick);
  };
  rafId = requestAnimationFrame(tick);

  // ---- Hover states (wave + subtle scale) -------------------------------
  const onOver = (e) => {
    if (!e.target.closest(hoverSelector)) return;
    document.documentElement.classList.add('cursor-hover', 'robot-wave');
  };
  const onOut = (e) => {
    if (!e.target.closest(hoverSelector)) return;
    document.documentElement.classList.remove('cursor-hover', 'robot-wave');
  };
  document.addEventListener('mouseover', onOver);
  document.addEventListener('mouseout',  onOut);

  // Optional: one-off wave on click
  const onDown = (e) => {
    if (!e.target.closest(hoverSelector)) return;
    document.documentElement.classList.add('robot-wave');
    setTimeout(() => document.documentElement.classList.remove('robot-wave'), 600);
  };
  document.addEventListener('mousedown', onDown);

  // ---- Blink loop -------------------------------------------------------
  let blinkTimer = null;
  const scheduleBlink = () => {
    const delay = Math.floor(Math.random() * (blinkEveryMax - blinkEveryMin)) + blinkEveryMin;
    blinkTimer = window.setTimeout(() => {
      eyelid.setAttribute('height', '4');      
      setTimeout(() => {
        eyelid.setAttribute('height', '0');    
        scheduleBlink();
      }, 90);
    }, delay);
  };
  scheduleBlink();

  // ---- Cleanup/unmount --------------------------------------------------
  const unmount = () => {
    cancelAnimationFrame(rafId);
    window.removeEventListener('pointermove', updateTarget);
    window.removeEventListener('mousemove', updateTarget);
    document.removeEventListener('mouseover', onOver);
    document.removeEventListener('mouseout',  onOut);
    document.removeEventListener('mousedown', onDown);
    if (blinkTimer) clearTimeout(blinkTimer);
    host.style.opacity = '0';
    document.documentElement.classList.remove('robot-cursor-ready', 'cursor-hover', 'robot-wave');
    document.body.classList.remove('robot-cursor-ready');
  };

  // Also auto-clean on pagehide (Safari/iOS friendly)
  window.addEventListener('pagehide', unmount, { once: true });

  return unmount;
}
