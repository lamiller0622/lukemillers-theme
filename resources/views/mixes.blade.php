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
  <meta property="og:url" content="https://lamiller0622.github.io/lamiller0622.githublio/mixes.html">
  <meta name="twitter:card" content="summary_large_image">
  <title>Mixes 2024 (Reprise) — Luke Miller</title>
@endpush

@section('content')
  <div id="vanta-bg-disc" aria-hidden="true"></div>

  <div class="wrap">
    <div class="player">
      <h1>Mixes 2024 (Reprise)</h1>
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

      <a href="https://pub-eb118f23ac7b48a3985b8985ef92286c.r2.dev/mixes_2024_faststart.m4a" download
         style="display:inline-block;margin-top:1rem;margin-bottom:1rem;margin-left:auto;text-decoration:none;border:1px solid #0002;padding:10px 12px;border-radius:10px">
        ⤓ Download
      </a>
    </div>

    <ul id="chapters" class="chapters"></ul>
    <!-- <p class="small">Tip: Add this page to your Home Screen for quick access.</p> -->
  </div>

  <div class="sticky-footer">You can keep your phone locked and skip chapters outside of webpage.</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js" defer></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta@0.5.24/dist/vanta.rings.min.js" defer></script>
  <script>
    (function () {
      let vanta;
      const start = () => {
        // Respect reduced motion
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
            backgroundColor: 0x07132F,
          });
        }
      };

      // init when scripts are ready
      window.addEventListener('DOMContentLoaded', start);

      // cleanup on page unload (helps with PJAX, Turbolinks, etc.)
      window.addEventListener('beforeunload', () => { if (vanta && vanta.destroy) vanta.destroy(); });
    })();
  </script>
<script>
  const AUDIO_URL = "https://lukemiller.io/wp-content/mixes_2024_faststart.m4a";

  if ('mediaSession' in navigator) {
    navigator.mediaSession.metadata = new MediaMetadata({
      title: "Mixes 2024 (Reprise)",
      artist: "Luke Miller",
      album: "Mixes",
      artwork: [{ src: "https://pub-eb118f23ac7b48a3985b8985ef92286c.r2.dev/cover.png", sizes: "1024x1024", type: "image/jpeg" }],
    });
  }

  const chapters = [
    [0.000,"What Hurts the Most - Cascada"],
    [146.402,"Eusexua - FKA Twigs"],
    [185.017,"Bow Down Flawless Remix - Beyonce"],
    [393.037,"Ice Attack - Future"],
    [462.962,"Everything is Romantic - Charli XCX"],
    [537.143,"Sao Paolo - The Weeknd"],
    [742.713,"Eat Your Man - Dom Dolla & Nelly Furtado"],
    [866.638,"Take it Off - Kesha"],
    [941.552,"Cannibal - Kesha"],
    [1060.727,"Pretty Mess - Erika Jayne"],
    [1215.720,"Schiebe - Lady Gaga"],
    [1405.125,"Sorry - Madonna"],
    [1579.449,"Acapella - Kelis"],
    [1715.350,"Body Language (David Aude Remix) - Heidi Montag"],
    [1826.321,"Massive - Drake"],
    [1949.294,"All Your Children - Jamie XX"],
    [2034.674,"RIIVERDANCE - Beyonce"],
    [2138.954,"The Big Big Beat - Azealia Banks"],
    [2260.813,"BIZCOCHITO - Rosalia"],
    [2363.468,"El Apagon - Bad Bunny"],
    [2429.854,"EL CLuB - Bad Bunny"],
    [2488.252,"Paparazzi - Lady Gaga"],
    [2637.115,"VIRGO'S GROOVE - Beyonce"],
    [2789.557,"ENERGY - Beyonce"],
    [2889.286,"Angels in the Snow - Cher & Cyndi Lauper"],
    [2989.734,"Life (feat. Robyn) - Jamie XX"],
    [3159.704,"Rhythm is a Dancer - Snap!"],
    [3282.352,"Take My Breath - The Weeknd"],
    [3551.541,"Forbidden Love - Madonna"],
    [3798.512,"Jump - Madonna"],
    [3967.487,"Erotica (Live) - Madonna"],
    [4090.395,"Like a Virgin (Live) - Madonna"]
  ];

  const audio = document.getElementById('audio');
  const ul = document.getElementById('chapters');
  const cur = document.getElementById('cur');
  const dur = document.getElementById('dur');
  const prevBtn = document.getElementById('prev');
  const nextBtn = document.getElementById('next');
  const KEY = "mix_progress_" + AUDIO_URL;

  audio.src = AUDIO_URL;

  function fmt(t){
    t=Math.max(0,Math.floor(t));
    const h=Math.floor(t/3600), m=Math.floor((t%3600)/60), s=t%60;
    return h?`${h}:${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`
            :`${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
  }

  chapters.forEach(([start,title])=>{
    const li=document.createElement('li');
    const b=document.createElement('button'); b.className='jump'; b.textContent=fmt(start);
    b.addEventListener('click',()=>{ audio.currentTime=start+0.01; audio.play(); highlight(); });
    const span=document.createElement('span'); span.className='title'; span.textContent=title;
    li.appendChild(b); li.appendChild(span); ul.appendChild(li);
  });

  function chapterIndexAt(t){
    let idx=0;
    for(let i=0;i<chapters.length;i++){ if(t+0.001>=chapters[i][0]) idx=i; else break; }
    return idx;
  }
  function highlight(){
    const idx=chapterIndexAt(audio.currentTime);
    [...ul.children].forEach((li,i)=>li.classList.toggle('active',i===idx));
  }

  prevBtn.onclick=()=>{ const idx=chapterIndexAt(audio.currentTime); const target=Math.max(0,idx-1);
    audio.currentTime=chapters[target][0]+0.01; audio.play(); highlight(); };
  nextBtn.onclick=()=>{ const idx=chapterIndexAt(audio.currentTime); const target=Math.min(chapters.length-1,idx+1);
    audio.currentTime=chapters[target][0]+0.01; audio.play(); highlight(); };

  audio.addEventListener('loadedmetadata',()=>{
    dur.textContent=fmt(audio.duration||0);
    const saved=Number(localStorage.getItem(KEY)||0);
    if(saved>0 && saved<(audio.duration||1e9)){ audio.currentTime=saved; }
    maybeUpdateChapterMetadata();
  });
  audio.addEventListener('timeupdate',()=>{
    cur.textContent=fmt(audio.currentTime||0);
    highlight();
    if(Math.floor(audio.currentTime)%2===0){
      localStorage.setItem(KEY,String(Math.floor(audio.currentTime)));
    }
    maybeUpdateChapterMetadata();
  });

  if('mediaSession' in navigator){
    navigator.mediaSession.setActionHandler('previoustrack',()=>prevBtn.onclick());
    navigator.mediaSession.setActionHandler('nexttrack',()=>nextBtn.onclick());
    navigator.mediaSession.setActionHandler('seekto',(d)=>{ audio.currentTime=d.seekTime||audio.currentTime; });
  }

  let lastChapterIdx=-1;
  function updateMediaSessionForChapter(idx){
    if(!('mediaSession' in navigator)) return;
    const [start,title]=chapters[idx]||[0,""];
    const t=fmt(start);
    const mixTitle="Mixes 2024 (Reprise)";
    const artist="Luke Miller";
    const album="Mixes 2024";
    const currentArtwork=(navigator.mediaSession.metadata&&navigator.mediaSession.metadata.artwork)||undefined;
    navigator.mediaSession.metadata=new MediaMetadata({
      title: `${title} — ${mixTitle}`,
      artist, album, artwork: currentArtwork
    });
    try{
      navigator.mediaSession.setPositionState({
        duration:isFinite(audio.duration)?audio.duration:0,
        playbackRate:audio.playbackRate||1,
        position:audio.currentTime||0
      });
    }catch(_){}
  }
  function maybeUpdateChapterMetadata(){
    const idx=chapterIndexAt(audio.currentTime);
    if(idx!==lastChapterIdx){ lastChapterIdx=idx; updateMediaSessionForChapter(idx); }
  }
</script>
@endpush
