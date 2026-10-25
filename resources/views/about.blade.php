{{--
  Template Name: About Page
--}}

@extends('layouts.app')

@section('content')

@php
  wp_localize_script('your-theme-handle', 'lukeChat', [
    'nonce' => wp_create_nonce('luke_chat_nonce'),
    'apiUrl' => home_url('/wp-json/luke/v1/chat'),
  ]);

  // Framed photos in the order they appear (wide, portrait, wide). The
  // lightbox cycles through the same list.
  $aboutPhotos = [
    ['src' => 'https://lukemiller.io/wp-content/uploads/2026/03/20f76b74-b50a-4167-8b5a-bb8d6d059f89.jpg', 'alt' => 'Luke',        'shape' => 'wide'],
    ['src' => 'https://lukemiller.io/wp-content/uploads/2026/03/IMG_0844-scaled.jpg',                       'alt' => 'Luke Miller', 'shape' => 'tall'],
    ['src' => 'https://lukemiller.io/wp-content/uploads/2026/03/IMG_8333.jpg',                              'alt' => 'Luke',        'shape' => 'wide-short'],
  ];
@endphp

<section class="about-page">
  <div class="about-page__floor" aria-hidden="true"></div>

  <div class="about-page__grid">

    {{-- Bio column --}}
    <div class="about-page__bio-col">
      <a class="btn-primary about-page__home" href="{{ home_url('/') }}">Return Home</a>

      <article class="about-bio">
        <h1 class="about-bio__title">{{ the_title() }}</h1>
        {{ the_content() }}
      </article>
    </div>

    {{-- Photos, chat and robot column --}}
    <div class="about-page__side-col">
      <div class="about-photos">
        @foreach ($aboutPhotos as $i => $photo)
          <button
            type="button"
            class="about-photos__frame about-photos__frame--{{ $photo['shape'] }}"
            data-lightbox-index="{{ $i }}"
            aria-label="Enlarge photo"
          >
            <img src="{{ $photo['src'] }}" alt="{{ $photo['alt'] }}" loading="lazy" decoding="async">
          </button>
        @endforeach
      </div>

      <div class="chat-container">
        <div class="chat-header">
          <h2>Ask a Question</h2>
        </div>

        <div class="chat-messages" id="chatMessages">
          <div class="message bot">
            <div class="message-content">
              Hey there! I'm Luke's robot speaking on his behalf. What would you like to know about Luke?
            </div>
          </div>
        </div>

        <div class="chat-input-container">
          <input
            type="text"
            id="chatInput"
            placeholder="Type your message..."
            autocomplete="off"
          />
          <button id="chatSend" type="button">Send</button>
        </div>
      </div>

      <div class="about-robot" aria-hidden="true">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="130" height="130" shape-rendering="crispEdges">
          <!-- Robot (upper body only, behind podium) -->
          <g id="bot-front">
            <!-- Antenna -->
            <rect x="11" y="2" width="2" height="2" fill="#6b7280"/>
            <rect class="about-robot__antenna" x="11" y="2" width="2" height="1" fill="#ef4444"/>

            <!-- Head -->
            <rect x="7" y="5" width="10" height="6" fill="#9ca3af"/>
            <rect x="8" y="6" width="8" height="4" fill="#d1d5db"/>

            <!-- Eyes -->
            <rect x="9" y="7" width="2" height="2" fill="#60a5fa"/>
            <rect x="13" y="7" width="2" height="2" fill="#60a5fa"/>

            <!-- Ears -->
            <rect x="6" y="7" width="1" height="2" fill="#9ca3af"/>
            <rect x="17" y="7" width="1" height="2" fill="#9ca3af"/>

            <!-- Neck -->
            <rect x="11" y="11" width="2" height="1" fill="#9ca3af"/>

            <!-- Body (partial, visible above podium) -->
            <rect x="8" y="12" width="8" height="2" fill="#9ca3af"/>
            <rect x="9" y="12" width="6" height="2" fill="#d1d5db"/>

            <!-- Arms resting on podium -->
            <rect x="6" y="13" width="2" height="2" fill="#9ca3af"/>
            <rect x="16" y="13" width="2" height="2" fill="#9ca3af"/>
          </g>

          <!-- Podium -->
          <g id="podium">
            <rect x="4" y="14" width="16" height="2" fill="#4b5563"/>
            <rect x="5" y="16" width="14" height="6" fill="#374151"/>
            <rect x="10" y="18" width="4" height="2" fill="#6b7280"/>
            <rect x="6" y="22" width="12" height="2" fill="#4b5563"/>
          </g>
        </svg>
      </div>
    </div>

  </div>

  {{-- Photo lightbox --}}
  <div class="about-lightbox" id="aboutLightbox" role="dialog" aria-modal="true" aria-label="Photo" hidden>
    <button type="button" class="about-lightbox__nav about-lightbox__nav--prev" id="aboutLightboxPrev" aria-label="Previous photo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
    <figure class="about-lightbox__stage">
      <img id="aboutLightboxImg" src="" alt="Luke">
      <figcaption class="about-lightbox__count" id="aboutLightboxCount"></figcaption>
    </figure>
    <button type="button" class="about-lightbox__nav about-lightbox__nav--next" id="aboutLightboxNext" aria-label="Next photo">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
    </button>
  </div>

</section>

@endsection

@push('scripts')
<script>
  const lukeChat = {
    nonce: '{{ wp_create_nonce('luke_chat_nonce') }}',
    apiUrl: '{{ home_url('/wp-json/luke/v1/chat') }}'
  };
</script>
<script>
(function() {
  const messages = document.getElementById('chatMessages');
  const input = document.getElementById('chatInput');
  const sendBtn = document.getElementById('chatSend');

  let conversationHistory = [];

  function addMessage(text, isBot = false) {
    const div = document.createElement('div');
    div.className = 'message ' + (isBot ? 'bot' : 'user');
    div.innerHTML = '<div class="message-content">' + text + '</div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }
  function addTypingIndicator() {
    const div = document.createElement('div');
    div.className = 'message bot typing';
    div.id = 'typingIndicator';
    div.innerHTML = '<div class="message-content">...</div>';
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }
  function removeTypingIndicator() {
    const typing = document.getElementById('typingIndicator');
    if (typing) typing.remove();
  }
  async function sendMessage() {
    const text = input.value.trim();
    if (!text) return;
    addMessage(text, false);
    input.value = '';
    input.disabled = true;
    sendBtn.disabled = true;
    addTypingIndicator();
    try {
      const response = await fetch(lukeChat.apiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: text,
          nonce: lukeChat.nonce,
          history: conversationHistory.slice(-10) // keeps the last 5 exchanges
        })
      });
      const data = await response.json();
      removeTypingIndicator();
      if (data.reply) {
        conversationHistory.push({ role: 'user', content: text });
        conversationHistory.push({ role: 'assistant', content: data.reply });
        addMessage(data.reply, true);
      } else if (data.message) {
        addMessage('Sorry, something went wrong: ' + data.message, true);
      }
    } catch (err) {
      removeTypingIndicator();
      addMessage('Sorry, I had trouble connecting. Please try again.', true);
    }
    input.disabled = false;
    sendBtn.disabled = false;
    input.focus();
  }
  sendBtn.addEventListener('click', sendMessage);
  input.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') sendMessage();
  });
})();
</script>
<script>
(function() {
  // Photo lightbox: click a frame to enlarge, arrows / keys to step, Escape or backdrop to close.
  const frames = Array.from(document.querySelectorAll('.about-photos__frame'));
  const box = document.getElementById('aboutLightbox');
  if (!frames.length || !box) return;

  const img = document.getElementById('aboutLightboxImg');
  const count = document.getElementById('aboutLightboxCount');
  const prev = document.getElementById('aboutLightboxPrev');
  const next = document.getElementById('aboutLightboxNext');
  const photos = frames.map(f => f.querySelector('img'));
  let current = -1;
  let lastFocus = null;

  function show(i) {
    current = (i + photos.length) % photos.length;
    img.src = photos[current].src;
    img.alt = photos[current].alt || 'Luke';
    count.textContent = (current + 1) + ' / ' + photos.length;
  }
  function open(i) {
    lastFocus = document.activeElement;
    show(i);
    box.hidden = false;
    document.body.classList.add('about-lightbox-open');
    next.focus();
  }
  function close() {
    box.hidden = true;
    current = -1;
    document.body.classList.remove('about-lightbox-open');
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  frames.forEach((f, i) => f.addEventListener('click', () => open(i)));
  prev.addEventListener('click', e => { e.stopPropagation(); show(current - 1); });
  next.addEventListener('click', e => { e.stopPropagation(); show(current + 1); });
  img.addEventListener('click', e => e.stopPropagation());
  box.addEventListener('click', close);
  window.addEventListener('keydown', e => {
    if (box.hidden) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowRight') show(current + 1);
    if (e.key === 'ArrowLeft') show(current - 1);
  });
})();
</script>
@endpush
