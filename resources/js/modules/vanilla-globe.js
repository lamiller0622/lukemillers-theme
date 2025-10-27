// vanilla-mini-globe.js
export function mountMiniGlobe(hostEl, {
  color = 0x007074,   // teal
  accent = 0xe45b31,  // orange (not used in minimal build)
  bg = 0xe3d9cf,
  radius = 60,
  latStep = 15,
  lonStep = 15,
  spin = 0.0035
} = {}) {
  if (!hostEl) return null;
  const { THREE } = window; // you already import THREE elsewhere

  const w = hostEl.clientWidth || 300;
  const h = hostEl.clientHeight || 220;

  const scene = new THREE.Scene();
  scene.background = new THREE.Color(bg);

  const cam = new THREE.PerspectiveCamera(60, w / h, 0.1, 2000);
  cam.position.set(0, 0, radius * 2.1);

  const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
  renderer.setPixelRatio(Math.min(1.75, window.devicePixelRatio || 1));
  renderer.setSize(w, h, false);
  renderer.domElement.className = 'mini-globe-canvas';
  hostEl.innerHTML = '';
  hostEl.appendChild(renderer.domElement);

  const globe = new THREE.Group();
  scene.add(globe);

  const addLine = (pts) => {
    const geom = new THREE.BufferGeometry().setFromPoints(pts);
    const mat  = new THREE.LineBasicMaterial({ color, opacity:0.95, transparent:true });
    globe.add(new THREE.Line(geom, mat));
  };

  // Latitudes
  for (let lat = -90 + latStep; lat < 90; lat += latStep) {
    const phi = THREE.MathUtils.degToRad(lat);
    const y = radius * Math.sin(phi);
    const r = radius * Math.cos(phi);
    const pts = [];
    for (let a = 0; a <= 360; a += 3) {
      const t = THREE.MathUtils.degToRad(a);
      pts.push(new THREE.Vector3(Math.cos(t)*r, y, Math.sin(t)*r));
    }
    addLine(pts);
  }

  // Longitudes
  for (let lon = 0; lon < 360; lon += lonStep) {
    const a0 = THREE.MathUtils.degToRad(lon);
    const pts = [];
    for (let b = -90; b <= 90; b += 3) {
      const phi = THREE.MathUtils.degToRad(b);
      const rcp = radius * Math.cos(phi);
      pts.push(new THREE.Vector3(Math.cos(a0)*rcp, radius*Math.sin(phi), Math.sin(a0)*rcp));
    }
    addLine(pts);
  }

  // Rim
  {
    const pts = [];
    for (let a = 0; a <= 360; a += 2) {
      const t = THREE.MathUtils.degToRad(a);
      pts.push(new THREE.Vector3(Math.cos(t)*radius, 0, Math.sin(t)*radius));
    }
    addLine(pts);
  }

  // Resize
  const ro = new ResizeObserver(() => {
    const W = hostEl.clientWidth, H = hostEl.clientHeight;
    if (!W || !H) return;
    cam.aspect = W / H; cam.updateProjectionMatrix();
    renderer.setSize(W, H, false);
  });
  ro.observe(hostEl);

  // Animation
  let raf = 0, running = true;
  const tick = () => {
    if (running) globe.rotation.y += spin;
    renderer.render(scene, cam);
    raf = requestAnimationFrame(tick);
  };
  tick();

  return {
    pause(){ running = false; },
    resume(){ running = true; },
    destroy(){
      cancelAnimationFrame(raf);
      ro.disconnect();
      renderer.dispose();
      hostEl.innerHTML = '';
    }
  };
}
