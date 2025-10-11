import.meta.glob(['../images/**', '../fonts/**'])
import '../css/tailwind.css'
import '../css/app.scss'
import GLOBE from 'vanta/dist/vanta.globe.min'
import * as THREE from 'three'
import Glide from '@glidejs/glide'
import {mountPortfolioSliders} from './modules/portfolio-slider';
import { mountPixelRobot } from './modules/cursor-robot';

/* ---------- helpers  ---------- */
function waitForFrames(n) {
  return new Promise(resolve => {
    let i = 0
    const step = () => { if (++i < n) requestAnimationFrame(step); else resolve() }
    requestAnimationFrame(step)
  })
}

function findGroundObject(v) {
  const scene = v.scene
  let found = null
  scene.traverse(o => {
    if (found) return
    const isCandidate = (o.isMesh || o.type === 'LineSegments')
    if (!isCandidate) return
    const box = new THREE.Box3().setFromObject(o)
    const size = new THREE.Vector3()
    box.getSize(size)
    const thinY = size.y < Math.max(size.x, size.z) * 0.15
    const horizontalish = Math.abs(Math.abs(o.rotation.x) - Math.PI / 2) < 0.5 || Math.abs((o.up && o.up.y) || 1) > 0.8
    if (thinY && horizontalish) found = o
  })
  return found
}

function makeVirtualGround(v) {
  const plane = new THREE.Mesh(
    new THREE.PlaneGeometry(160, 160),
    new THREE.MeshBasicMaterial({ visible: false })
  )
  plane.rotation.x = -Math.PI / 2
  plane.position.set(0, -25, 0)
  plane.userData._halfW = 80
  plane.userData._halfH = 80
  v.scene.add(plane)
  return plane
}

function setupGroundHotspotsOn(ground, v, overlay) {
  if (!overlay) return
  const cam = v.camera
  const renderer = v.renderer

  let halfW = (ground.userData && ground.userData._halfW) || 50
  let halfH = (ground.userData && ground.userData._halfH) || 50
  const box = new THREE.Box3().setFromObject(ground)
  const s = new THREE.Vector3(); box.getSize(s)
  if (s.x > 0.001) halfW = s.x / 2
  if (s.z > 0.001) halfH = s.z / 2

  const HOTSPOTS = [
    { u: -0.7, v:  0.10, label: `
      <h2>Work Experience</h2>
      <ul>
        <li>Full Stack Web Developer</li>
        <li>8+ Years Professional Experience</li>
      </ul>
      <a class="btn-primary">Learn More</a>` 
    },
    { u: -0.2, v:  0.30, label: `
      <h2>Skills</h2>
      <ul>
        <li>Front-end: HTML, Javascript, Jquery, React</li>
        <li>Back-end: PHP, Laravel, SQL</li>
        <li>Software Development: python, Ruby, C, Assembly</li>
        <li>Wordpress: WPML, ACF, CPT</li>
        <li>Design: CSS, SCSS, Bootstrap, Typescript</li>
        <li>Marketing: Hubl, SEO</li>
      </ul>
      <a href="http://localhost:8888/lukemiller.io/index.php/portfolio/" class="btn-primary">See Portfolio</a>`
     },
    { u:  0.3, v: -0.10, label: `
      <h2>Education</h2>
      <ul>
        <li>Bachelor of Science:</li>
        <li>Applied Computer Science</li>
        <li>Oregon State University</li>
        <li>GPA: 4.0</li>
      </ul>
      ` 
    },
    { u:  -0.2, v: -0.5, label: `
      <h2>Contact</h2>
      <ul>
        <li><a href="mailto:info@lukemiller.io">info@lukemiller.io</a></li>
        <li><a target="_blank" href="https://www.linkedin.com/in/luke-miller96/">LinkedIn</a></li>
      </ul>
      ` 
    },
    { u:  0.1, v: .9, label: `
      <div class="secret"><a href="/mixes" class="btn-primary">fun</a></div>
      ` 
    },
  ]

  const nodes = HOTSPOTS.map(h => {
    const d = document.createElement('div')
    d.className = 'vanta-hotspot'
    const label = document.createElement('div')
    label.className = 'label'
    label.innerHTML = h.label
    d.appendChild(label)
    overlay.appendChild(d)
    return { ...h, el: d, local: new THREE.Vector3() }
  })

  nodes.forEach(n => {
    // make the dot accessible as a button
    n.el.setAttribute('role', 'button')
    n.el.setAttribute('tabindex', '0')
    n.el.setAttribute('aria-expanded', 'false')

    const open = () => {
      // close all others first
      nodes.forEach(m => {
        m.el.classList.remove('active')
        m.el.setAttribute('aria-expanded', 'false')
      })
      n.el.classList.add('active')
      n.el.setAttribute('aria-expanded', 'true')
    }

    const close = () => {
      n.el.classList.remove('active')
      n.el.setAttribute('aria-expanded', 'false')
    }

    // click toggles this one
    n.el.addEventListener('click', (e) => {
      e.stopPropagation()
      if (n.el.classList.contains('active')) {
        close()
      } else {
        open()
      }
    })

    // keyboard: Enter/Space to toggle
    n.el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault()
        if (n.el.classList.contains('active')) {
          close()
        } else {
          open()
        }
      }
    })
    n.el.addEventListener('mouseenter', () => n.el.classList.add('hover'))
    n.el.addEventListener('mouseleave', () => n.el.classList.remove('hover'))
  })

  // click outside closes all
  document.addEventListener('click', () => {
    nodes.forEach(n => {
      n.el.classList.remove('active')
      n.el.setAttribute('aria-expanded', 'false')
    })
  })

  // Esc closes active
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      nodes.forEach(n => {
        n.el.classList.remove('active')
        n.el.setAttribute('aria-expanded', 'false')
      })
    }
  })

  const canvasSize = new THREE.Vector2()
  const world = new THREE.Vector3()
  const projected = new THREE.Vector3()

  const tick = () => {
    renderer.getSize(canvasSize)
    overlay.style.width  = canvasSize.x + 'px'
    overlay.style.height = canvasSize.y + 'px'

    nodes.forEach(n => {
      n.local.set(n.u * halfW, 0, n.v * halfH)
      ground.localToWorld(world.copy(n.local))
      projected.copy(world).project(cam)
      const x = ( projected.x + 1) / 2 * canvasSize.x
      const y = (-projected.y + 1) / 2 * canvasSize.y
      const off = projected.z > 1 || projected.z < -1
      n.el.style.display = off ? 'none' : 'block'
      if (!off) n.el.style.transform = `translate(${x}px, ${y}px) translate(-50%, -50%)`
    })

    requestAnimationFrame(tick)
  }
  tick()
}

/* ---------- boot ---------- */
document.addEventListener('DOMContentLoaded', () => {
  const el = document.querySelector('#vanta-bg')
  if (!el) return

  window.THREE = THREE

  requestAnimationFrame(init)

  async function init() {
    const vanta = GLOBE({
      el,
      THREE,
      mouseControls: true,
      touchControls: true,
      gyroControls: false,
      minHeight: 400,
      minWidth: 400,
      scale: 1,
      scaleMobile: 1,
      color: 0x1a5521,
      color2: 0x0ea5a6,
      backgroundColor: 0xdaffcb,
      backgroundAlpha: 1,
    })

    // wait a few frames so Vanta finishes building the scene
    await waitForFrames(6)

    const ground = findGroundObject(vanta) || makeVirtualGround(vanta)
    const overlay = document.querySelector('#vanta-hotspots')
    setupGroundHotspotsOn(ground, vanta, overlay)
  }
})


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

document.addEventListener('DOMContentLoaded', () => {
  mountPixelRobot({ size: 64 }); // tweak size if you want a tinier bot
});