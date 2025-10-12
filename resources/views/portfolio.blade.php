{{--
  Template Name: Portfolio
--}}
@extends('layouts.app')

@section('content')


@php
$slides = $slides ?? [
  [
    'title' => 'First Advantage',
    'image' => asset('images/portfolio/fa.png')->uri(),
    'url' => 'https://www.fadv.com', 
    'meta' => 'HR Tech • 2025'
  ],
  [
    'title' => 'David Casavant Archive',    
    'image' => asset('images/portfolio/dca.png')->uri(),       
    'url' => 'https://shop.davidcasavantarchive.com/',                    
    'meta' => 'Fashion • 2025'
  ],
  [
    'title' => 'Palermo Law',               
    'image' => asset('images/portfolio/plaw.png')->uri(),      
    'url' => 'https://www.palermolawyers.com/',                    
    'meta' => 'Law • 2023'
  ],
  [
    'title' => 'Sterling',       
    'image' => asset('images/portfolio/sterling.png')->uri(), 
    'url' => 'https://www.sterlingcheck.com/',                    
    'meta' => 'HR • 2024'
  ],
  [
    'title' => 'Cambridge Kitchens', 
    'image' => asset('images/portfolio/cambridge.png')->uri(),       
    'url' => 'https://cambridgekitchens.com/',                    
    'meta' => 'Manufacturing • 2018'
  ],
  [
    'title' => 'Vanity Photo Booths',
    'image' => asset('images/portfolio/vanity.png')->uri(),
    'url' => 'https://vanityphotobooths.com/', 
    'meta' => 'Entertainment • 2019'
  ],
  [
    'title' => 'EFL Clinic', 
    'image' => asset('images/portfolio/efl.png')->uri(),       
    'url' => 'https://eflclinic.com/',                    
    'meta' => 'Healthcare • 2020'
  ],
  [
    'title' => 'Be @ Work',    
    'image' => asset('images/portfolio/bwork.png')->uri(),       
    'url' => 'https://bworkco.com/',                    
    'meta' => 'Real Estate • 2021'
  ],
  [
    'title' => 'Suffolk Divorce Mediation',       
    'image' => asset('images/portfolio/sdm.png')->uri(), 
    'url' => 'https://suffolkdivorcemediation.com/',                    
    'meta' => 'Law • 2022'
  ],
  [
    'title' => 'World Ship Society',               
    'image' => asset('images/portfolio/worldship.png')->uri(),      
    'url' => 'https://worldshipny.com/',                    
    'meta' => 'Maritime • 2021'
  ],
];
$autoplay = $autoplay ?? 0;
@endphp

<section class="slider-center">
  <div id="vanta-portfolio" aria-hidden="true"></div>
  <div class="container">
    <a class="relative" href="/">Return Home</a>
  </div>

  <div class="glide glide-portfolio" data-portfolio-glide data-autoplay="{{ (int) $autoplay }}" style="padding-bottom: 54px;">
    <div class="glide__track" data-glide-el="track">
      <ul class="glide__slides">
        @foreach($slides as $i => $s)
          <li class="glide__slide">
            <article class="slide">
              <img class="slide__image"
                   src="{{ $s['image'] }}"
                   alt="{{ $s['title'] }}"
                   loading="lazy" decoding="async" />
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

    <div class="glide__bullets" data-glide-el="controls[nav]">
      @foreach($slides as $_)
        <button class="glide__bullet" data-glide-dir="={{ $loop->index }}" aria-label="Go to slide {{ $loop->index+1 }}"></button>
      @endforeach
    </div>
  </div>
</section>

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/three@0.134.0/build/three.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/vanta@latest/dist/vanta.dots.min.js"></script>
  <script>
    (function () {
      let vantaInstance;
      function initVanta() {
        const el = document.getElementById('vanta-portfolio');
        if (!el || !window.VANTA || !window.VANTA.DOTS) return;


        if (vantaInstance && vantaInstance.destroy) vantaInstance.destroy();

        vantaInstance = VANTA.DOTS({
          el,
          mouseControls: true,
          touchControls: true,
          gyroControls: false,
          minHeight: 200.00,
          minWidth: 200.00,
          scale: 1.00,
          scaleMobile: 1.00,
          color: 0x1a5521,           
          color2: 0x0ea5a6,          
          backgroundColor: 0xfafafa, 
          size: 2.0,
          spacing: 20.0,
        });
      }

      document.addEventListener('DOMContentLoaded', initVanta);
      window.addEventListener('resize', () => { if (vantaInstance?.resize) vantaInstance.resize(); });
      window.addEventListener('beforeunload', () => { if (vantaInstance?.destroy) vantaInstance.destroy(); });
    })();
  </script>
@endpush


@endsection