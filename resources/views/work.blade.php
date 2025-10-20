{{--
  Template Name: Work Experience
--}}
@extends('layouts.app')

@section('content')


<section class="container">

  <svg class="absolute robot-typing" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 80" shape-rendering="crispEdges">
    <!-- ===== Floor ===== -->
    <rect x="0" y="60" width="128" height="2" fill="#e5e7eb"/>

    <!-- ===== Desk ===== -->
    <g id="desk-side" transform="translate(30,0)">
      <!-- Tabletop -->
      <rect x="34" y="45" width="18" height="4" fill="#b45309" stroke="#92400e" stroke-width="1"></rect>
      <rect x="34" y="49" width="18" height="3" fill="#a16207"></rect>

      <!-- Legs  -->
      <rect x="35" y="52" width="4" height="11" fill="#92400e"></rect>
      <rect x="47" y="52" width="4" height="11" fill="#92400e"></rect>

      <!-- Keyboard -->
      <rect  x="45" y="44" width="8" height="1" fill="#9ca3af"></rect>
      <rect id="keyboard" x="45" y="43" width="8" height="1" fill="#6b7280" opacity="0.6"></rect> 
    </g>
      <!-- Monitor  -->
    <g id="monitor" transform="translate(55,2)">
      <rect x="12" y="20" width="3" height="16" fill="#111827"/>
      <rect x="15" y="21" width="1" height="14" fill="#374151"/>
      <rect x="13" y="36" width="1" height="5" fill="#6b7280"/>
      <rect x="10" y="41" width="7" height="2" fill="#6b7280"/>
    </g>

    <!-- ===== Chair  ===== -->
    <g id="chair-left" transform="translate(30,0)">
      <!-- Seat  -->
      <rect x="58" y="46" width="24" height="6" fill="#4b5563"></rect>
      <!-- Backrest to the RIGHT = chair faces LEFT -->
      <rect x="82" y="28" width="4" height="22" fill="#4b5563"></rect>
      <!-- Post / base -->
      <rect x="70" y="52" width="2" height="10" fill="#4b5563"></rect>
      <rect x="64" y="62" width="14" height="2" fill="#4b5563"></rect>
    </g>

    <!-- ===== Robot  ===== -->
    <g id="bot-left-seated" transform="translate(85,18) scale(2)">
      <!-- Antenna -->
      <rect x="7" y="0" width="2" height="2" fill="#6b7280"></rect>
      <rect x="7" y="0" width="2" height="1" fill="#ef4444"></rect>

      <!-- Head  -->
      <rect x="3" y="3" width="10" height="6" fill="#9ca3af"></rect>
      <rect x="4" y="4" width="8" height="4" fill="#d1d5db"></rect>
      <rect x="8" y="5" width="2" height="2" fill="#9ca3af"></rect> 
      <rect x="4" y="5" width="2" height="2" fill="#60a5fa"></rect> 

      <!-- Neck -->
      <rect x="7" y="9" width="2" height="1" fill="#9ca3af"></rect>

      <!-- Body -->
      <rect x="4" y="10" width="8" height="4" fill="#9ca3af"></rect>
      <rect x="5" y="11" width="6" height="2" fill="#d1d5db"></rect>

      <!-- Arm -->
      <g id="arm-front">
        <rect x="-1" y="11" width="3" height="1" fill="#9ca3af"></rect>
      </g>
      <g id="arm-mid">
        <rect x="-0.5" y="11.5" width="3" height="1" fill="#9ca3af"></rect>
      </g>

      <!-- Legs -->
      <rect x="0" y="13" width="2" height="2" fill="#6b7280"></rect>
      <rect x="2" y="13" width="2" height="2" fill="#6b7280"></rect>
    </g>
  </svg>

</section>

@endsection

@push('scripts')

<script>
const host = document.querySelector('.robot-typing');
if (host) {
  const movers = host.querySelectorAll(
    '#bot-left-seated #arm-front, #bot-left-seated #arm-mid, #desk-side #keyboard'
  );

  let pausing = false;
  const pauseMs = 5000; // your 5s idle

  movers.forEach(el => {
    el.addEventListener('animationiteration', () => {
      if (pausing) return;            
      pausing = true;
      host.classList.add('paused');    
      setTimeout(() => {
        host.classList.remove('paused');
        pausing = false;
      }, pauseMs);
    }, { passive: true });
  });
}
</script>

@endpush