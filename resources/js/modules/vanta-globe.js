export function initVantaHotspots({
  elSelector = '#vanta-bg',
  overlaySelector = '#vanta-hotspots',
  THREE: THREEIn = null,
  GLOBE: GLOBEIn = null,
  vantaBase = { color: 0x007074, color2: 0xe45b31, backgroundColor: 0xe3d9cf },
  maxWaitFrames = 600
} = {}) {

  let waitFrames = 0;
  const waitUntilReady = (resolve) => {
    const el = document.querySelector(elSelector);
    const THREE = THREEIn || window.THREE;
    const GLOBE = GLOBEIn || window.GLOBE;

    if (el && THREE && GLOBE) {
      window.THREE = THREE;
      resolve({ el, THREE, GLOBE });
      return;
    }
    if (waitFrames++ > maxWaitFrames) return;
    requestAnimationFrame(() => waitUntilReady(resolve));
  };

  const start = () => new Promise(waitUntilReady).then(({ el, THREE, GLOBE }) => {
    const homeSection = el.closest('section');

    const findGroundObject = (v) => {
      let found = null;
      v.scene.traverse(o => {
        if (found || !(o.isMesh || o.type === 'LineSegments')) return;
        
        const box = new THREE.Box3().setFromObject(o);
        const size = new THREE.Vector3();
        box.getSize(size);
        
        const thinY = size.y < Math.max(size.x, size.z) * 0.15;
        const horizontalish = Math.abs(Math.abs(o.rotation.x) - Math.PI / 2) < 0.5 || 
                             Math.abs((o.up?.y) || 1) > 0.8;
        if (thinY && horizontalish) found = o;
      });
      return found;
    };

    const makeVirtualGround = (v) => {
      const plane = new THREE.Mesh(
        new THREE.PlaneGeometry(160, 160),
        new THREE.MeshBasicMaterial({ visible: false })
      );
      plane.rotation.x = -Math.PI / 2;
      plane.position.set(0, -25, 0);
      plane.userData._halfW = 80;
      plane.userData._halfH = 80;
      v.scene.add(plane);
      return plane;
    };

    const HOTSPOTS = [
      { bg: 'office', u: -0.7, v: 0.10, label: `
        <h2>Work Experience</h2>
        <ul>
          <li>Full Stack Web Developer</li>
          <li>8+ Years Professional Dev Experience</li>
        </ul>
        <a href="work" class="btn-primary">Learn More</a>` },
      { bg: 'teepee', u: -0.2, v: -0.5, label: `
        <h2>Education</h2>
        <ul>
          <li><b>Oregon State University</b></li>
          <li>Bachelor of Science: Applied Computer Science</li>
          <li><b>Nassau Community College</b></li>
          <li>Associate of Applied Science: Information Technology</li>
        </ul>` },
      { bg: 'gallery', u: -0.15, v: 0.35, label: `
        <h2>Skills</h2>
        <ul>
          <li>Front-end: HTML, JavaScript, jQuery, React, SCSS, TypeScript</li>
          <li>Back-end: Laravel, MySQL, PHP, Python, Ruby</li>
          <li>CMS/Frameworks: Shopify, WordPress (ACF, CPT, Elementor, Sage, WPML)</li>
          <li>Design: Figma, Photoshop</li>
          <li>Marketing: Email Development, GA4/GTM, HubL, SEO</li>
        </ul>
        <a href="/portfolio/" class="btn-primary">See Portfolio</a>` },
      { bg: 'house', u: 0.3, v: -0.10, label: `
        <h2>Contact</h2>
        <a class="btn-primary mr-4" href="mailto:info@lukemiller.io">Email</a>
        <a target="_blank" class="btn-primary" href="https://www.linkedin.com/in/luke-miller96/">LinkedIn</a>` },
      { id: 'secret', u: 0.1, v: 0.9, label: `
        <div class="secret"><a href="/mixes" class="btn-primary">fun</a></div>` },
      { id: 'disco', u: 0.85, v: -0.75, label: `
        <div class="disco-ball" aria-hidden="true"></div>` },
    ];

    const setupHotspots = (ground, v, overlay) => {
      if (!overlay) return;

      let halfW = ground.userData?._halfW || 50;
      let halfH = ground.userData?._halfH || 50;
      const box = new THREE.Box3().setFromObject(ground);
      const size = new THREE.Vector3();
      box.getSize(size);
      if (size.x > 0.001) halfW = size.x / 2;
      if (size.z > 0.001) halfH = size.z / 2;

      const nodes = HOTSPOTS.map(h => {
        const el = document.createElement('div');
        el.className = 'vanta-hotspot';
        el.style.pointerEvents = 'auto';
        el.setAttribute('role', 'button');
        el.setAttribute('tabindex', '0');
        el.setAttribute('aria-expanded', 'false');
        
        if (h.id) {
          el.dataset.id = h.id;
          if (h.id === 'disco') el.classList.add('is-disco');
          if (h.id === 'secret') el.classList.add('secret');
        }
        if (h.bg) el.dataset.bg = h.bg;

        const label = document.createElement('div');
        label.className = 'label';
        label.innerHTML = h.label;
        if (h.id === 'disco') label.style.pointerEvents = 'none';
        
        el.appendChild(label);
        overlay.appendChild(el);

        return { ...h, el, local: new THREE.Vector3() };
      });

      const robot = window.__robotCtl;
      const prefersReduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      nodes.forEach(n => {
        const open = () => {
          nodes.forEach(m => {
            m.el.classList.remove('active');
            m.el.setAttribute('aria-expanded', 'false');
          });
          n.el.classList.add('active');
          n.el.setAttribute('aria-expanded', 'true');
        };
        
        const close = () => {
          n.el.classList.remove('active');
          n.el.setAttribute('aria-expanded', 'false');
        };

        // Combined click handler
        n.el.addEventListener('click', (e) => {
          const link = e.target.closest('.label a[href]');
          
          // Handle link clicks with robot jump
          if (link && n.el.contains(link)) {
            e.preventDefault();
            e.stopPropagation();
            
            if (prefersReduce || !robot?.goto || !robot?.jumpInto) {
              window.location.assign(link.href);
              return;
            }

            n.el.classList.add('jump-target');
            const rect = n.el.getBoundingClientRect();
            const cx = rect.left + rect.width / 2;
            const cy = rect.top + rect.height / 2;

            document.documentElement.style.setProperty('--rx', `${Math.round(cx)}px`);
            document.documentElement.style.setProperty('--ry', `${Math.round(cy)}px`);

            robot.goto(cx, cy, { duration: 600 })
              .then(() => robot.jumpInto(n.el, { duration: 450 }))
              .then(() => window.location.assign(link.href))
              .finally(() => {
                n.el.classList.remove('jump-target');
                robot.release?.();
              });
            return;
          }

          // Handle hotspot open/close
          e.stopPropagation();
          n.el.classList.contains('active') ? close() : open();
        });

        n.el.addEventListener('keydown', (e) => {
          if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            n.el.classList.contains('active') ? close() : open();
          }
        });

        // Disco ball dance trigger
        const robotHost = document.getElementById('robot-cursor');
        if (n.id === 'disco' && robotHost) {
          const toggle = (add) => robotHost.classList.toggle('dance', add);
          n.el.addEventListener('mouseenter', () => toggle(true));
          n.el.addEventListener('mouseleave', () => toggle(false));
          n.el.addEventListener('focus', () => toggle(true));
          n.el.addEventListener('blur', () => toggle(false));
        }

        n.el.addEventListener('mouseenter', () => n.el.classList.add('hover'));
        n.el.addEventListener('mouseleave', () => n.el.classList.remove('hover'));
      });

      // Close all on outside click or Escape
      document.addEventListener('click', () => {
        nodes.forEach(n => {
          n.el.classList.remove('active');
          n.el.setAttribute('aria-expanded', 'false');
        });
      });
      
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          nodes.forEach(n => {
            n.el.classList.remove('active');
            n.el.setAttribute('aria-expanded', 'false');
          });
        }
      });

      // Hotspot positioning loop
      const canvasSize = new THREE.Vector2();
      const world = new THREE.Vector3();
      const projected = new THREE.Vector3();

      const tick = () => {
        v.renderer.getSize(canvasSize);
        overlay.style.width = `${canvasSize.x}px`;
        overlay.style.height = `${canvasSize.y}px`;

        nodes.forEach(n => {
          n.local.set(n.u * halfW, 0, n.v * halfH);
          ground.localToWorld(world.copy(n.local));
          projected.copy(world).project(v.camera);
          
          const x = (projected.x + 1) / 2 * canvasSize.x;
          const y = (-projected.y + 1) / 2 * canvasSize.y;
          const off = projected.z > 1 || projected.z < -1;
          
          n.el.style.display = off ? 'none' : 'block';
          if (!off) {
            n.el.style.setProperty('--x', `${x}px`);
            n.el.style.setProperty('--y', `${y}px`);
          }
        });

        requestAnimationFrame(tick);
      };
      tick();
    };

    // Init Vanta
    const VANTA_CONFIG = {
      mouseControls: true,
      touchControls: false,
      gyroControls: false,
      minHeight: 400,
      minWidth: 400,
      scale: 1,
      scaleMobile: 1,
    };
    
    const VANTA_BASE = { ...vantaBase, backgroundAlpha: 1 };
    let v = GLOBE({ el, THREE, ...VANTA_CONFIG, ...VANTA_BASE });

    // Wait for Vanta to stabilize, then setup hotspots
    setTimeout(() => {
      const ground = findGroundObject(v) || makeVirtualGround(v);
      const overlay = document.querySelector(overlaySelector);
      setupHotspots(ground, v, overlay);

      // Disco mode
      const DISCO_PALETTES = [
        { color: 0xff3b3b, color2: 0x00e7ff, backgroundColor: 0x0d0221 },
        { color: 0x9dff00, color2: 0xff00e6, backgroundColor: 0x011627 },
        { color: 0xffd166, color2: 0x06d6a0, backgroundColor: 0x1a1a1a },
        { color: 0x845ec2, color2: 0xff6f91, backgroundColor: 0x101018 },
        { color: 0x00f5d4, color2: 0xf15bb5, backgroundColor: 0x0b132b },
      ];

      let discoTimer = null;
      let discoIndex = 0;
      let inDisco = false;

      const applyVanta = (opts) => {
        if (v?.setOptions) {
          v.setOptions(opts);
        } else if (v?.destroy) {
          v.destroy();
          v = GLOBE({ el, THREE, ...VANTA_CONFIG, ...opts });
        }
      };

      const startDisco = () => {
        if (inDisco) return;
        inDisco = true;
        discoIndex = 0;
        discoTimer = setInterval(() => {
          applyVanta(DISCO_PALETTES[discoIndex % DISCO_PALETTES.length]);
          homeSection?.classList.add('light');
          discoIndex++;
        }, 1000);
      };

      const stopDisco = () => {
        if (!inDisco) return;
        inDisco = false;
        homeSection?.classList.remove('light');
        if (discoTimer) clearInterval(discoTimer);
        discoTimer = null;
        applyVanta(VANTA_BASE);
      };

      const robotHost = document.getElementById('robot-cursor');
      if (robotHost) {
        const check = () => robotHost.classList.contains('dance') ? startDisco() : stopDisco();
        const mo = new MutationObserver(check);
        mo.observe(robotHost, { attributes: true, attributeFilter: ['class'] });
        check();
      }
    }, 100);
  });

  start();
}