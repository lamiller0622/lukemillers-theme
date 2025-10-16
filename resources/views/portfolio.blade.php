{{--
  Template Name: Portfolio
--}}
@extends('layouts.app')

@section('content')


@php
$slides = $slides ?? [
  [
    'title' => 'First Advantage',
    'lottie' => @asset('/fadv-lottie.json'),
    'image' => 'https://lukemiller.io/wp-content/fadv.gif',
    'url' => 'https://www.fadv.com', 
    'meta' => 'HR Tech • 2025'
  ],
  [
    'title' => 'David Casavant Archive',    
    'image' => 'https://lukemiller.io/wp-content/uploads/2025/10/dca.png',       
    'url' => 'https://shop.davidcasavantarchive.com/',                    
    'meta' => 'Fashion • 2025'
  ],
  [
    'title' => 'Palermo Law',     
    'lottie' => @asset('/palermo-lottie.json'),          
    'image' => 'https://lukemiller.io/wp-content/palermo.gif',      
    'url' => 'https://www.palermolawyers.com/',                    
    'meta' => 'Law • 2023'
  ],
  [
    'title' => 'Sterling',       
    'lottie' => @asset('/st-lottie.json'),          
    'image' => 'https://lukemiller.io/wp-content/sterling.gif', 
    'url' => 'https://www.sterlingcheck.com/',                    
    'meta' => 'HR • 2024'
  ],
  [
    'title' => 'Cambridge Kitchens', 
    'lottie' => @asset('/cambridge-lottie.json'),
    'image' => 'https://lukemiller.io/wp-content/cambridge.gif',       
    'url' => 'https://cambridgekitchens.com/',                    
    'meta' => 'Manufacturing • 2018'
  ],
  [
    'title' => 'Vanity Photo Booths',
    'lottie' => @asset('/vanity-lottie.json'),
    'image' => 'https://lukemiller.io/wp-content/vanity.gif',
    'url' => 'https://vanityphotobooths.com/', 
    'meta' => 'Entertainment • 2019'
  ],
  [
    'title' => 'EFL Clinic', 
    'image' => 'https://lukemiller.io/wp-content/uploads/2025/10/efl.png',       
    'url' => 'https://eflclinic.com/',                    
    'meta' => 'Healthcare • 2020'
  ],
  [
    'title' => 'Be @ Work',    
    'image' => 'https://lukemiller.io/wp-content/uploads/2025/10/bwork.png',       
    'url' => 'https://bworkco.com/',                    
    'meta' => 'Real Estate • 2021'
  ],
  [
    'title' => 'Suffolk Divorce Mediation',       
    'image' => 'https://lukemiller.io/wp-content/uploads/2025/10/sdm.png', 
    'url' => 'https://suffolkdivorcemediation.com/',                    
    'meta' => 'Law • 2022'
  ],
  [
    'title' => 'World Ship Society',               
    'image' => 'https://lukemiller.io/wp-content/uploads/2025/10/worldship.png',      
    'url' => 'https://worldshipny.com/',                    
    'meta' => 'Maritime • 2021'
  ],
];
$autoplay = $autoplay ?? 0;
@endphp

<section class="slider-center">
  <!-- <div id="vanta-portfolio" aria-hidden="true"></div> -->

  <div class="glide glide-portfolio" data-portfolio-glide data-autoplay="{{ (int) $autoplay }}">
    <div class="glide__track" data-glide-el="track">
      <ul class="glide__slides">
        @foreach($slides as $i => $s)
          <li class="glide__slide">
            <article class="slide">
              <div class="slide__media">
                @if(!empty($s['lottie']))
                  <lottie-player
                    class="slide__image"
                    src="{{ $s['lottie'] }}"
                    background="transparent"
                    speed="1"
                    loop
                    autoplay
                    aria-label="{{ $s['title'] }} animation">
                  </lottie-player>

                  @if(!empty($s['poster']))
                    <noscript>
                      <img class="slide__image" src="{{ $s['poster'] }}" alt="{{ $s['title'] }}">
                    </noscript>
                  @endif
                @else
                  <img class="slide__image"
                       src="{{ $s['image'] }}"
                       alt="{{ $s['title'] }}"
                       loading="lazy" decoding="async" />
                @endif
              </div>
              <div class="slide__overlay"></div>
              <!-- <div class="slide__badge">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</div> -->
              <div class="slide__content">
                <h3 class="slide__title">{{ $s['title'] }}</h3>
                @if(!empty($s['meta'])) <div class="slide__meta">{{ $s['meta'] }}</div> @endif
              </div>
              @if(!empty($s['url']))
                <a class="slide__link" href="{{ $s['url'] }}" target="_blank" rel="noopener"></a>
              @endif
            </article>
          </li>
        @endforeach
      </ul>
    </div>

    <div class="glide__arrows" data-glide-el="controls" aria-label="Slider controls">
      <button class="glide__arrow glide__arrow--left" data-glide-dir="<" aria-label="Previous">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M15 18l-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
      <button class="glide__arrow glide__arrow--right" data-glide-dir=">" aria-label="Next">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M9 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
      </button>
    </div>

    <!-- <div class="glide__bullets" data-glide-el="controls[nav]">
      @foreach($slides as $_)
        <button class="glide__bullet" data-glide-dir="={{ $loop->index }}" aria-label="Go to slide {{ $loop->index+1 }}"></button>
      @endforeach
    </div> -->
  </div>
  
</section>
<section class="floor">
  <div class="panelling">
    
  </div>
  <div class="wood-floor">
    <!-- <a class="signhome" href="/" aria-label="Return Home">
      <svg class="signhome__svg" width="240" viewBox="0 0 240 220" role="img" aria-hidden="true">
        <rect x="114" y="20" width="12" height="180" rx="6" fill="#111"/>
        <path d="M86 200c10 8 20-8 30 0s20-8 30 0 20-8 30 0" fill="none" stroke="#111" stroke-width="3" stroke-linecap="round"/>
        <g class="signhome__board">
          <path d="M222 70H72L32 100l40 30h150z" fill="#fff" stroke="#111" stroke-width="3" stroke-linejoin="round"/>
          <text x="65" y="108" font-family="ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto" font-size="20" font-weight="700" fill="#111">
            Return Home
          </text>
        </g>
      </svg>
    </a> -->
    <div id=robot-wrap>
      <svg id="robot-svg-gallery"
       viewBox="0 -3 16 19"   
       width="128" height="128"
       shape-rendering="crispEdges"
       xmlns="http://www.w3.org/2000/svg">

        <!-- ================= NEUTRAL ================= -->
        <g id="bot-front">
          <!-- Legs -->
          <rect x="6" y="14" width="2" height="2" fill="#6b7280"/>
          <rect x="8" y="14" width="2" height="2" fill="#6b7280"/>
          <!-- Neck -->
          <rect x="7" y="9" width="2" height="1" fill="#9ca3af"/>
          <!-- Antenna -->
          <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
          <rect x="7" y="0" width="2" height="1" fill="#ef4444"/>
          <!-- Head back  -->
          <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
          <rect x="4" y="4" width="8" height="4" fill="#d1d5db" mask="url(#blink-front)"/>
          <!-- Ears --> 
          <rect x="2" y="5" width="1" height="2" fill="#9ca3af"/> 
          <rect x="13" y="5" width="1" height="2" fill="#9ca3af"/>
          <!-- Body -->
          <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
          <rect x="5" y="11" width="6" height="2" fill="#d1d5db"/>
          <!-- Both arms visible from behind -->
          <g id="arm-left-back"><rect x="3"  y="11" width="1" height="2" fill="#9ca3af"/></g>
          <g id="arm-right-back"><rect x="12" y="11" width="1" height="2" fill="#9ca3af"/></g>
        </g>

        <!-- ================= RIGHT PROFILE ================= -->
        <g id="bot-right">
          <!-- Legs -->
          <rect x="6" y="14" width="2" height="2" fill="#6b7280"/>
          <rect x="8" y="14" width="2" height="2" fill="#6b7280"/>
          <!-- Neck -->
          <rect x="7" y="9" width="2" height="1" fill="#9ca3af"/>
          <!-- Antenna -->
          <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
          <rect x="7" y="0" width="2" height="1" fill="#ef4444"/>
          <!-- Head (one ear only, on the near/right side) -->
          <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
          <rect x="4" y="4" width="8" height="4" fill="#d1d5db"/>
          <!-- near ear only -->
          <rect x="6" y="5" width="2" height="2" fill="#9ca3af"/>
          <!-- Eye looking right -->
          <rect x="10" y="5" width="2" height="2" fill="#60a5fa"/>
          <!-- Body -->
          <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
          <rect x="5" y="11" width="6" height="2" fill="#d1d5db"/>
          <!-- arm -->
          <g id="arm-mid"><rect x="8" y="11" width="1" height="2" fill="#9ca3af"/></g>
        </g>

        <!-- ================= LEFT PROFILE ================= -->
        <g id="bot-left">
          <!-- Legs -->
          <rect x="6" y="14" width="2" height="2" fill="#6b7280"/>
          <rect x="8" y="14" width="2" height="2" fill="#6b7280"/>
          <!-- Neck -->
          <rect x="7" y="9" width="2" height="1" fill="#9ca3af"/>
          <!-- Antenna -->
          <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
          <rect x="7" y="0" width="2" height="1" fill="#ef4444"/>
          <!-- Head (one ear only, on the near/left side) -->
          <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
          <rect x="4" y="4" width="8" height="4" fill="#d1d5db"/>
          <!-- near ear only -->
          <rect x="8" y="5" width="2" height="2" fill="#9ca3af"/>
          <!-- eye looking left -->
          <rect x="4" y="5" width="2" height="2" fill="#60a5fa"/>
          <!-- Body -->
          <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
          <rect x="5" y="11" width="6" height="2" fill="#d1d5db"/>
          <!-- arm -->
          <g id="arm-mid"><rect x="7" y="11" width="1" height="2" fill="#9ca3af"/></g>
        </g>
      </svg>
    </div>
    <div class="container">
    <a class="relative" href="/">Return Home</a>
    </div>
  </div>
</section>

@endsection

@once
  @push('scripts')
    <script defer src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        // respect reduced motion
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
          document.querySelectorAll('lottie-player[autoplay]').forEach(p => p.pause());
        }
      });
    </script>
  @endpush
@endonce