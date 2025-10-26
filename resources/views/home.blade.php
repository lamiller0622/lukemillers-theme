{{--
  Template Name: Home Page
--}}
@extends('layouts.app')

@section('content')

@php
$slides = $slides ?? [
  [
    'title' => 'Work Experience',
    'image' => @asset('images/office.svg'),
    'url' => '/work',
    'cta' => 'Learn More', 
    'content' => '
          <ul>
            <li>Full Stack Web Developer</li>
            <li>8+ Years Professional Dev Experience</li>
          </ul>
          <a href="work" class="btn-primary">Learn More</a>'
  ],
  [
    'title' => 'Skills',
    'image' => @asset('images/gallery.svg'),
    'url' => '/work', 
    'cta' => 'See Portfolio', 
    'content' => '
          <ul>
            <li>Front-end: HTML, Javascript, Jquery, React</li>
            <li>Back-end: PHP, Laravel, SQL</li>
            <li>Software Development: python, Ruby, C, Assembly</li>
            <li>Wordpress: WPML, ACF, CPT</li>
            <li>Design: CSS, SCSS, Bootstrap, Typescript</li>
            <li>Marketing: Hubl, SEO</li>
          </ul>'
  ],
  [
    'title' => 'Education',
    'image' => @asset('images/teepee.svg'),
    'content' => '
          <ul>
            <li>Bachelor of Science:</li>
            <li>Applied Computer Science</li>
            <li>Oregon State University</li>
            <li>GPA: 4.0</li>
          </ul>'
  ],
  [
    'title' => 'Contact',
    'image' => @asset('images/house.svg'),
    'content' => '
          <ul>
            <li><a href="mailto:info@lukemiller.io">info@lukemiller.io</a></li>
            <li><a target="_blank" href="https://www.linkedin.com/in/luke-miller96/">LinkedIn</a></li>
          </ul>'
  ],
];
$autoplay = $autoplay ?? 0;
@endphp
  
<section class="relative min-h-[100dvh] hidden md:block">
  <div id="vanta-bg" class="absolute inset-0 z-0 pointer-events-none">
    <div id="vanta-hotspots" class="absolute inset-0 pointer-events-none"></div>
  </div>
  <div class="relative z-10 container mx-auto py-24 ">
    <h1 class="text-4xl font-semibold">Luke Miller</h1>
    <p class="mt-4 max-w-xl">Web Developer</p>
  </div>
</section>

<section class="home-mobile-slider md:hidden">
  <!-- <div id="vanta-portfolio" aria-hidden="true"></div> -->

  <div class="glide home-glide-portfolio" data-portfolio-glide data-autoplay="{{ (int) $autoplay }}">
    <div class="glide__track" data-glide-el="track">
      <ul class="glide__slides">
        @foreach($slides as $i => $s)
          <li class="glide__slide">
            <article class="slide">
              <div class="slide__content">
                <h3 class="slide__title">{{ $s['title'] }}</h3>
                @if(!empty($s['meta'])) <div class="label">{{ $s['content'] }} </div>@endif
              </div>
              @if(!empty($s['url']))
                <a class="btn-primary" href="{{ $s['url'] }}" target="_blank" rel="noopener">{{ $s['cta'] }}</a>
              @endif
              <img class="slide__image"
                       src="{{ $s['image'] }}"
                       alt="{{ $s['title'] }}"
                       loading="lazy" decoding="async" />
            </article>
          </li>
        @endforeach
      </ul>
    </div>
  </div>
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
          <rect x="5" y="5" width="2" height="2" fill="#60a5fa"/>
          <rect x="9" y="5" width="2" height="2" fill="#60a5fa"/>
          <defs>
            <!-- Blink mask for eyes -->
            <mask id="blink-mask">
              <!-- default: eyes visible (white) -->
              <rect x="0" y="0" width="16" height="16" fill="#fff"/>
              <!-- eyelid covers eyes during blink via CSS anim (we move this rect) -->
              <rect id="eyelid" x="4" y="5" width="8" height="0" fill="#000"/>
            </mask>
          </defs>
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
</section>



<div id="robot-cursor" aria-hidden="true">
  <!-- Pixel Robot SVG -->
  <svg id="robot-svg"
     viewBox="0 -3 16 19"   
     width="64" height="64"
     shape-rendering="crispEdges"
     xmlns="http://www.w3.org/2000/svg">
    <!-- BG transparent -->
    <defs>
      <!-- Blink mask for eyes -->
      <mask id="blink-mask">
        <!-- default: eyes visible (white) -->
        <rect x="0" y="0" width="16" height="16" fill="#fff"/>
        <!-- eyelid covers eyes during blink via CSS anim (we move this rect) -->
        <rect id="eyelid" x="4" y="5" width="8" height="0" fill="#000"/>
      </mask>
    </defs>

    <!-- Whole bot group (y bob animation) -->
    <g id="bot-static">
      <!-- Legs -->
      <rect x="6" y="14" width="2" height="2" fill="#6b7280"/>
      <rect x="8" y="14" width="2" height="2" fill="#6b7280"/>
      <!-- Neck (static post) -->
      <rect id="neck" x="7" y="9" width="2" height="1" fill="#9ca3af"/>
    </g>

    <!-- BOUNCING: head + body + arms + antenna move together -->
    <g id="bot-bounce">
      <!-- Antenna pole + bulb -->
      <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
      <rect id="antenna" x="7" y="0" width="2" height="1" fill="#ef4444"/>

      <!-- Head frame + face (with your blink mask) -->
      <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
      <rect x="4" y="4" width="8" height="4" fill="#e5e7eb" mask="url(#blink-mask)"/>
      <!-- Eyes -->
      <rect x="5" y="5" width="2" height="2" fill="#60a5fa"/>
      <rect x="9" y="5" width="2" height="2" fill="#60a5fa"/>

      <!-- Ears -->
      <rect x="2" y="5" width="1" height="2" fill="#9ca3af"/>
      <rect x="13" y="5" width="1" height="2" fill="#9ca3af"/>

      <!-- Body -->
      <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
      <rect x="5" y="11" width="6" height="2" fill="#e5e7eb"/>

      <!-- Arms (right arm waves) -->
      <g id="arm-left">
        <rect x="3"  y="11" width="1" height="2" fill="#9ca3af"/>
      </g>
      <g id="arm-right">
        <rect x="12" y="11" width="1" height="2" fill="#9ca3af"/>
        <rect x="13" y="10" width="1" height="1" fill="#9ca3af"/>
      </g>
    </g>
  </svg>
</div>

@endsection




