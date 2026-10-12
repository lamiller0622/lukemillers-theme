/**
 * Hamburger site menu.
 *
 * - Toggle button flips aria-expanded and the panel's .is-open class
 *   (CSS handles visibility so the closed menu is out of the a11y tree).
 * - Escape, outside click, and link activation all close it.
 * - Focus moves into the panel on open and back to the toggle on close.
 */
export function mountSiteMenu({
  toggleSelector = '#site-menu-toggle',
  panelSelector = '#site-menu',
} = {}) {
  const toggle = document.querySelector(toggleSelector);
  const panel = document.querySelector(panelSelector);
  if (!toggle || !panel) return null;

  let isOpen = false;

  const firstLink = () => panel.querySelector('a[href], button');

  const open = () => {
    if (isOpen) return;
    isOpen = true;

    panel.classList.add('is-open');

    toggle.setAttribute('aria-expanded', 'true');
    toggle.setAttribute('aria-label', 'Close menu');
    document.documentElement.classList.add('site-menu-open');
  };

  const close = ({ returnFocus = true } = {}) => {
    if (!isOpen) return;
    isOpen = false;

    panel.classList.remove('is-open');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.setAttribute('aria-label', 'Open menu');
    document.documentElement.classList.remove('site-menu-open');

    if (returnFocus) toggle.focus({ preventScroll: true });
  };

  toggle.addEventListener('click', (e) => {
    if (isOpen) {
      close();
    } else {
      open();
      // Only move focus for keyboard users; mouse users keep the robot cursor.
      if (e.detail === 0) firstLink()?.focus({ preventScroll: true });
    }
  });

  // Link activation closes the menu (the page navigates anyway).
  panel.addEventListener('click', (e) => {
    if (e.target.closest('a[href]')) close({ returnFocus: false });
  });

  // Outside press closes. Listen on pointerdown and test the target rather
  // than relying on click bubbling: other widgets (the home-page hotspots)
  // stop click propagation before it reaches the document.
  document.addEventListener('pointerdown', (e) => {
    if (!isOpen) return;
    if (e.target.closest(toggleSelector) || e.target.closest(panelSelector)) return;
    close({ returnFocus: false });
  });

  document.addEventListener('keydown', (e) => {
    if (!isOpen) return;

    if (e.key === 'Escape') {
      e.preventDefault();
      close();
      return;
    }

    // Keep Tab within the panel + toggle while open.
    if (e.key === 'Tab') {
      const focusables = [toggle, ...panel.querySelectorAll('a[href], button')];
      const first = focusables[0];
      const last = focusables[focusables.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  });

  return { open, close, isOpen: () => isOpen };
}
