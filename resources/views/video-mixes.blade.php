{{--
  Template Name: Video Mixes
--}}
@extends('layouts.app')

@push('head')
  <meta property="og:image" content="https://lukemiller.io/wp-content/uploads/2025/10/cropped-robot.png">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta property="og:title" content="Lukes Video Mixes">
  <meta property="og:type" content="video.other">
  <meta name="twitter:card" content="summary_large_image">
  <title>Video Mixes — Luke Miller</title>
@endpush

@section('content')

@php
$mixes = collect(get_field('mixes') ?: [])->map(fn($m) => [
  'slug'     => $m['slug'],
  'title'    => $m['title'],
  'artist'   => $m['artist'],
  'album'    => $m['album'],
  'videoUrl' => $m['audio_url'],
  'artwork'  => $m['artwork'],
  'chapters' => parse_audacity_labels($m['labels_raw']),
])->all();
@endphp

  <div id="vanta-bg-disc" aria-hidden="true"></div>

  <div class="wrap">
    <div class="mix-header">
      <label for="mixPicker" class="small" style="opacity:.8; margin-right: 1rem">Choose Mix</label>
      <select id="mixPicker" class="btn-primary"></select>
    </div>

    <h1 id="mixTitle">Video Mixes</h1>

    <div class="video-layout">
      <div class="video-col">
        <div class="video-wrapper">
          <video id="video" preload="metadata" controls playsinline></video>
        </div>

        <div class="controls">
          <button id="prev" class="btn-primary icon-btn">
            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 18l-6-6 6-6"></path>
            </svg>
            <span>Prev track</span>
          </button>
          <button id="next" class="btn-primary icon-btn">
            <span>Next track</span>
            <svg aria-hidden="true" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M9 6l6 6-6 6"></path>
            </svg>
          </button>
          <div class="time"><span id="cur">00:00</span> / <span id="dur">—:—</span></div>
        </div>

        <!-- <a id="downloadLink" href="#" download
           style="display:inline-block;margin-top:1rem;margin-bottom:1rem;text-decoration:none;border:1px solid #0002;padding:10px 12px;border-radius:10px">
          ⤓ Download
        </a> -->
      </div>

      <ul id="chapters" class="chapters"></ul>
    </div>
  </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.rings.min.js" defer></script>
<script>
  (function () {
    let vanta;
    const start = () => {
      const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
      if (prefersReduced) return;

      if (!vanta && window.VANTA && document.getElementById('vanta-bg-disc')) {
        vanta = VANTA.RINGS({
          el: "#vanta-bg-disc",
          mouseControls: true,
          touchControls: true,
          gyroControls: false,
          minHeight: 200.00,
          minWidth: 200.00,
          scale: 1.00,
          scaleMobile: 1.00,
          THREE: window.THREE,
          backgroundColor: 0x0a2f1e,
        });
      }
    };

    window.addEventListener('DOMContentLoaded', start);
    window.addEventListener('beforeunload', () => { if (vanta && vanta.destroy) vanta.destroy(); });
  })();
</script>
<script>
/* =========================
   MIX DATA
   ========================= */
const MIXES = {!! json_encode($mixes, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!};

/* =========================
   DOM
   ========================= */
const $ = s => document.querySelector(s);
const video   = $('#video');
const ul      = $('#chapters');
const cur     = $('#cur');
const dur     = $('#dur');
const prevBtn = $('#prev');
const nextBtn = $('#next');
const picker  = $('#mixPicker');
const titleEl = $('#mixTitle');
const dl      = $('#downloadLink');
const SCROLL_AFTER_INDEX = 5;

let VIDEO_URL = "";
let chapters = [];
let KEY = "";
let lastChapterIdx = -1;
let lastScrolledIdx = -1;

/* =========================
   Utils
   ========================= */
function scrollActiveIntoView(idx) {
  if (!ul) return;
  if (idx < SCROLL_AFTER_INDEX) return;
  if (idx === lastScrolledIdx) return;
  const li = ul.children[idx];
  if (!li) return;
  li.scrollIntoView({ block: 'center', behavior: 'smooth' });
  lastScrolledIdx = idx;
}

function fmt(t){
  t = Math.max(0, Math.floor(t));
  const h = Math.floor(t/3600), m = Math.floor((t%3600)/60), s = t%60;
  return h ? `${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
           : `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}
function chapterIndexAt(t){
  let idx = 0;
  for (let i=0;i<chapters.length;i++){ if (t+0.001 >= chapters[i][0]) idx=i; else break; }
  return idx;
}
function qs(name){ const v=new URLSearchParams(location.search).get(name); return v?decodeURIComponent(v):null; }
function setQS(name,val){ const u=new URL(location.href); u.searchParams.set(name,val); history.replaceState(null,'',u.toString()); }

/* =========================
   UI: chapter list
   ========================= */
function buildChapterList(){
  ul.innerHTML = '';
  chapters.forEach(([start, title])=>{
    const li = document.createElement('li');
    const b  = document.createElement('button'); b.className='jump'; b.textContent=fmt(start);
    b.addEventListener('click', ()=>{ video.currentTime = start + 0.01; video.play(); highlight(); });
    const span = document.createElement('span'); span.className='title'; span.textContent=title;
    li.appendChild(b); li.appendChild(span); ul.appendChild(li);
  });
}
function highlight(){
  const idx = chapterIndexAt(video.currentTime);
  [...ul.children].forEach((li,i)=>li.classList.toggle('active', i===idx));
  scrollActiveIntoView(idx);
}

/* =========================
   Media Session
   ========================= */
function seedBaseMetadata(mix){
  if (!('mediaSession' in navigator)) return;
  navigator.mediaSession.metadata = new MediaMetadata({
    title: mix.title,
    artist: mix.artist,
    album:  mix.album || "Video Mixes",
    artwork: [{ src: mix.artwork, sizes: "1024x1024", type: "image/jpeg" }]
  });
}
function updateMediaSessionForChapter(idx){
  if (!('mediaSession' in navigator)) return;
  const [start, title] = chapters[idx] || [0,""];
  const currentArtwork = (navigator.mediaSession.metadata && navigator.mediaSession.metadata.artwork) || undefined;
  navigator.mediaSession.metadata = new MediaMetadata({
    title: `${title} — ${titleEl?.textContent || ''}`,
    artist: (navigator.mediaSession.metadata && navigator.mediaSession.metadata.artist) || '',
    album:  (navigator.mediaSession.metadata && navigator.mediaSession.metadata.album)  || '',
    artwork: currentArtwork
  });
  try{
    navigator.mediaSession.setPositionState({
      duration: isFinite(video.duration) ? video.duration : 0,
      playbackRate: video.playbackRate || 1,
      position: video.currentTime || 0
    });
  }catch(_){}
}
function maybeUpdateChapterMetadata(){
  const idx = chapterIndexAt(video.currentTime);
  if (idx !== lastChapterIdx){ lastChapterIdx = idx; updateMediaSessionForChapter(idx); }
}

function bindRemoteControls(){
  if (!('mediaSession' in navigator)) return;
  navigator.mediaSession.setActionHandler('previoustrack', () => prevBtn.onclick());
  navigator.mediaSession.setActionHandler('nexttrack',     () => nextBtn.onclick());
  navigator.mediaSession.setActionHandler('seekto',        d => { video.currentTime = d.seekTime || video.currentTime; });
}

/* =========================
   Prev/Next
   ========================= */
prevBtn.onclick = () => {
  const idx = chapterIndexAt(video.currentTime);
  const target = Math.max(0, idx - 1);
  video.currentTime = chapters[target][0] + 0.01;
  video.play();
  highlight();
};
nextBtn.onclick = () => {
  const idx = chapterIndexAt(video.currentTime);
  const target = Math.min(chapters.length - 1, idx + 1);
  video.currentTime = chapters[target][0] + 0.01;
  video.play();
  highlight();
};

/* =========================
   Load / switch mix
   ========================= */
async function loadMix(slug, opts={autoplay:false, keepTime:false}){
  const mix = MIXES.find(m=>m.slug===slug) || MIXES[0];

  setQS('mix', mix.slug);
  if (picker) picker.value = mix.slug;
  if (titleEl) titleEl.textContent = mix.title;
  if (dl) dl.href = mix.videoUrl;

  VIDEO_URL = mix.videoUrl;
  chapters  = mix.chapters.slice();
  KEY = "vmix_progress_" + VIDEO_URL;
  lastChapterIdx = -1;
  lastScrolledIdx = -1;

  video.src = VIDEO_URL;

  buildChapterList();

  await new Promise(r => {
    if (video.readyState >= 1) r();
    else video.addEventListener('loadedmetadata', r, { once:true });
  });

  seedBaseMetadata(mix);
  bindRemoteControls();

  dur.textContent = fmt(video.duration || 0);
  const saved = Number(localStorage.getItem(KEY) || 0);
  if (!opts.keepTime && saved > 0 && saved < (video.duration || 1e9)) {
    video.currentTime = saved;
  }

  maybeUpdateChapterMetadata();

  if (opts.autoplay) {
    video.play().catch(()=>{});
  }
}

/* =========================
   Time listeners
   ========================= */
video.addEventListener('timeupdate', () => {
  cur.textContent = fmt(video.currentTime || 0);
  highlight();
  if (Math.floor(video.currentTime) % 2 === 0) {
    localStorage.setItem(KEY, String(Math.floor(video.currentTime)));
  }
  maybeUpdateChapterMetadata();
});
video.addEventListener('ratechange', () => {
  if (!('mediaSession' in navigator)) return;
  try {
    navigator.mediaSession.setPositionState({
      duration: isFinite(video.duration) ? video.duration : 0,
      playbackRate: video.playbackRate || 1,
      position: video.currentTime || 0
    });
  } catch(_) {}
});

/* =========================
   Init
   ========================= */
(function init(){
  if (picker) {
    picker.innerHTML = '';
    MIXES.forEach(m => { const o=document.createElement('option'); o.value=m.slug; o.textContent=m.title; picker.appendChild(o); });
    picker.addEventListener('change', ()=> loadMix(picker.value, {autoplay:true}));
  }

  const qsSlug = qs('mix');
  const initial = MIXES.find(m=>m.slug===qsSlug) || MIXES[0];
  if (picker) picker.value = initial.slug;
  loadMix(initial.slug, {keepTime:true});
})();
</script>
@endpush