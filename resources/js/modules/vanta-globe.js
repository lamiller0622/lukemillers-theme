export async function mountVantaGlobe(el, options = {}) {
  const [{ default: GLOBE }, THREEmod] = await Promise.all([
    import('vanta/dist/vanta.globe.min'),
    import('three'),
  ])

  // Ensure THREE is available globally for Vanta internals
  const THREE = THREEmod.default || THREEmod
  if (typeof window !== 'undefined') {
    window.THREE = THREE
  }

  const effect = GLOBE({
    el,
    THREE,
    backgroundAlpha: 0, // transparent bg so your site bg shows
    mouseControls: true,
    touchControls: true,
    gyroControls: false,
    minHeight: 200,
    minWidth: 200,
    scale: 1,
    scaleMobile: 1,
    ...options,
  })

  return effect
}
