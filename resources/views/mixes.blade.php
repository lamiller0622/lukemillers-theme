{{--
  Template Name: Mixes
--}}
@extends('layouts.app')

@push('head')
  <meta property="og:image" content="https://pub-eb118f23ac7b48a3985b8985ef92286c.r2.dev/cover.png">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta property="og:title" content="Mixes 2024 (Reprise)">
  <meta property="og:description" content="Last year's mixes condensed.">
  <meta property="og:type" content="music.song">
  <meta name="twitter:card" content="summary_large_image">
  <title>Mixes 2024 (Reprise) — Luke Miller</title>
@endpush

@section('content')

@php
  $mixes = [
    [
      'slug'     => 'mixes-2024-reprise',
      'title'    => 'Mixes 2024 (Reprise)',
      'artist'   => 'Luke Miller',
      'album'    => 'Mixes 2024',
      'audioUrl' => 'https://lukemiller.io/wp-content/mixes_2024_faststart.m4a',
      'artwork'  => 'https://pub-eb118f23ac7b48a3985b8985ef92286c.r2.dev/cover.png',
      'chapters' => parse_audacity_labels(
        get_theme_file_path('resources/static/2024-Labels.txt')
      ),
    ],
    [
      'slug'     => 'take-it',
      'title'    => '2025 Mix (In Progress)',
      'artist'   => 'Luke Miller',
      'album'    => 'Mixes 2025',
      'audioUrl' => 'https://lukemiller.io/wp-content/Takeitmixcopy.m4a',
      'artwork'  => 'https://pub-eb118f23ac7b48a3985b8985ef92286c.r2.dev/cover.png',
      'chapters' => parse_audacity_labels(
        get_theme_file_path('resources/static/label1.txt')
      ),
    ],
  ];
@endphp

  <div id="vanta-bg-disc" aria-hidden="true"></div>

  <div class="wrap">
    <div class="player">

      <div class="mix-header">
        <label for="mixPicker" class="small" style="opacity:.8; margin-right: 1rem">Choose Mix</label>
        <select id="mixPicker" class="btn-primary" >
        </select>
      </div>

      <h1 id="mixTitle">Mixes</h1>

      <audio id="audio" preload="metadata" controls playsinline></audio>

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

      <a id="downloadLink" href="https://lukemiller.io/wp-content/mixes_2024_faststart.m4a" download
         style="display:inline-block;margin-top:1rem;margin-bottom:1rem;margin-left:auto;text-decoration:none;border:1px solid #0002;padding:10px 12px;border-radius:10px">
        ⤓ Download
      </a>
    </div>

    <ul id="chapters" class="chapters"></ul>

  </div>

  <!-- <div class="sticky-footer">You can keep your phone locked and skip chapters outside of webpage.</div> -->
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
const audio   = $('#audio');
const ul      = $('#chapters');
const cur     = $('#cur');
const dur     = $('#dur');
const prevBtn = $('#prev');
const nextBtn = $('#next');
const picker  = $('#mixPicker');
const titleEl = $('#mixTitle');
const dl      = $('#downloadLink');

let AUDIO_URL = "";
let chapters = [];
let KEY = "";
let lastChapterIdx = -1;

/* =========================
   Utils
   ========================= */
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
    b.addEventListener('click', ()=>{ audio.currentTime = start + 0.01; audio.play(); highlight(); });
    const span = document.createElement('span'); span.className='title'; span.textContent=title;
    li.appendChild(b); li.appendChild(span); ul.appendChild(li);
  });
}
function highlight(){
  const idx = chapterIndexAt(audio.currentTime);
  [...ul.children].forEach((li,i)=>li.classList.toggle('active', i===idx));
}

/* =========================
   Media Session
   ========================= */
function seedBaseMetadata(mix){
  if (!('mediaSession' in navigator)) return;
  // Keep the same static "album" naming style your old working snippet used
  navigator.mediaSession.metadata = new MediaMetadata({
    title: mix.title,
    artist: mix.artist,
    album:  mix.album || "Mixes",
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
      duration: isFinite(audio.duration) ? audio.duration : 0,
      playbackRate: audio.playbackRate || 1,
      position: audio.currentTime || 0
    });
  }catch(_){}
}
function maybeUpdateChapterMetadata(){
  const idx = chapterIndexAt(audio.currentTime);
  if (idx !== lastChapterIdx){ lastChapterIdx = idx; updateMediaSessionForChapter(idx); }
}

function bindRemoteControls(){
  if (!('mediaSession' in navigator)) return;
  // Delegate to buttons (like your old working code)
  navigator.mediaSession.setActionHandler('previoustrack', () => prevBtn.onclick());
  navigator.mediaSession.setActionHandler('nexttrack',     () => nextBtn.onclick());
  navigator.mediaSession.setActionHandler('seekto',        d => { audio.currentTime = d.seekTime || audio.currentTime; });
}

/* =========================
   Prev/Next (same as old)
   ========================= */
prevBtn.onclick = () => {
  const idx = chapterIndexAt(audio.currentTime);
  const target = Math.max(0, idx - 1);
  audio.currentTime = chapters[target][0] + 0.01;
  audio.play();
  highlight();
};
nextBtn.onclick = () => {
  const idx = chapterIndexAt(audio.currentTime);
  const target = Math.min(chapters.length - 1, idx + 1);
  audio.currentTime = chapters[target][0] + 0.01;
  audio.play();
  highlight();
};

/* =========================
   Load / switch mix
   ========================= */
async function loadMix(slug, opts={autoplay:false, keepTime:false}){
  const mix = MIXES.find(m=>m.slug===slug) || MIXES[0];

  // Update URL + UI
  setQS('mix', mix.slug);
  if (picker) picker.value = mix.slug;
  if (titleEl) titleEl.textContent = mix.title;
  if (dl) dl.href = mix.audioUrl;

  // Globals first, like old code
  AUDIO_URL = mix.audioUrl;
  chapters  = mix.chapters.slice();
  KEY = "mix_progress_" + AUDIO_URL;
  lastChapterIdx = -1;

  // Set the source (old ordering)
  audio.src = AUDIO_URL;

  // Build list immediately (old style)
  buildChapterList();

  await new Promise(r => {
    if (audio.readyState >= 1) r();
    else audio.addEventListener('loadedmetadata', r, { once:true });
  });

  // Set base metadata for THIS source, then bind handlers (iOS quirk)
  seedBaseMetadata(mix);
  bindRemoteControls();

  // Duration, progress restore
  dur.textContent = fmt(audio.duration || 0);
  const saved = Number(localStorage.getItem(KEY) || 0);
  if (!opts.keepTime && saved > 0 && saved < (audio.duration || 1e9)) {
    audio.currentTime = saved;
  }

  // Initialize chapter metadata now that time is set
  maybeUpdateChapterMetadata();

  if (opts.autoplay) {
    audio.play().catch(()=>{});
  }
}

/* =========================
   Time listeners
   ========================= */
audio.addEventListener('timeupdate', () => {
  cur.textContent = fmt(audio.currentTime || 0);
  highlight();
  if (Math.floor(audio.currentTime) % 2 === 0) {
    localStorage.setItem(KEY, String(Math.floor(audio.currentTime)));
  }
  maybeUpdateChapterMetadata();
});
audio.addEventListener('ratechange', () => {
  if (!('mediaSession' in navigator)) return;
  try {
    navigator.mediaSession.setPositionState({
      duration: isFinite(audio.duration) ? audio.duration : 0,
      playbackRate: audio.playbackRate || 1,
      position: audio.currentTime || 0
    });
  } catch(_) {}
});

/* =========================
   Init
   ========================= */
(function init(){
  // Populate picker
  if (picker) {
    picker.innerHTML = '';
    MIXES.forEach(m => { const o=document.createElement('option'); o.value=m.slug; o.textContent=m.title; picker.appendChild(o); });
    picker.addEventListener('change', ()=> loadMix(picker.value, {autoplay:true}));
  }

  // Load initial
  const qsSlug = qs('mix');
  const initial = MIXES.find(m=>m.slug===qsSlug) || MIXES[0];
  if (picker) picker.value = initial.slug;
  loadMix(initial.slug, {keepTime:true});
})();
</script>


@endpush
