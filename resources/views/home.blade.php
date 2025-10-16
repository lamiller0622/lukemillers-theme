{{--
  Template Name: Home Page
--}}
@extends('layouts.app')

@section('content')
  
<section class="relative min-h-[100dvh]">
  <div id="vanta-bg" class="absolute inset-0 z-0 pointer-events-none">
    <div id="vanta-hotspots" class="absolute inset-0 pointer-events-none"></div>
  </div>

  <div class="relative z-10 container mx-auto py-24">
    <h1 class="text-4xl font-semibold">Luke Miller</h1>
    <p class="mt-4 max-w-xl">Web Developer</p>
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




