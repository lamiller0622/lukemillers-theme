{{--
  Template Name: Work Experience
--}}
@extends('layouts.app')

@section('content')
@php
  $experience = [
    [
      'role' => 'Web Applications Developer',
      'company' => 'First Advantage',
      'years' => 'Nov 2024–Present',
      'stack' => ['Sage 10', 'Bootstrap5', 'WPML', 'ACF', 'CPT'],
      'highlights' => [
        'Converted Drag & Drop Themed Site (Elementor) to MVC Theme (Sage 10).',
        'Converted custom company modules into flexible content modules for easy frontend page building using ACF.',
        'Built dynamic Hubspot templates and drag and drop modules for multiple marketing operation needs.'
      ]
    ],
    [
      'role' => 'Web Applications Developer',
      'company' => 'Sterling Check',
      'years' => 'Apr 2021-Nov 2024',
      'stack' => ['Sage9','Bootstrap5','Jquery','Tailwind'],
      'highlights' => [
        'Shop UX refresh & media perf',
        'Reusable flexible-content blocks'
      ]
    ],
    [
      'role' => 'Lead Developer',
      'company' => 'Palermo Law',
      'years' => '2023–Present',
      'stack' => ['WordPress','Sage 11','ACF','PHP','JS'],
      'highlights' => [
        'SEO practice-area system',
        'Vite/Tailwind component library'
      ]
    ],
  ];

  $fn = fn($text) => trim(preg_replace('/[^a-z0-9]+/i','', $text), '_');
@endphp

<section class="workexp-wrap flex justify-center my-24 flex-col">
  <!-- <div class="workexp-grid"> -->
    <!-- ===== LEFT: Monitor with scrollable code ===== -->
    <div class="monitor-wrap">
      <div class="monitor-frame">
        <div class="monitor-bezel">
          <div class="monitor-toolbar">
            <div class="dots">
              <span class="dot dot-close"></span>
              <span class="dot dot-min"></span>
              <span class="dot dot-max"></span>
            </div>
            <div class="title">Work Experience</div>
            <div class="actions">
              <button type="button" class="btn small" data-expand-all>Expand All</button>
              <button type="button" class="btn small" data-collapse-all>Collapse All</button>
            </div>
          </div>

          <div class="monitor-screen">
            <div class="code code-python ide">
              <div class="pane">
                <div class="file-preamble">
                  <span class="kw">#</span> <span class="cm">Luke Miller — Work Experience</span>
                </div>

                @foreach($experience as $job)
                <details class="fn-block" {{ $loop->first ? 'open' : '' }}>
                  <summary>
                    <span class="kw">company</span>
                    <span class="fn">{{ $fn($job['company']) }}</span><span class="p">():</span>
                    <!-- <span class="summary-meta">  <span class="cm"># {{ $job['role'] }} @ {{ $job['company'] }} ({{ $job['years'] }})</span></span> -->
                  </summary>
                  <div class="fn-body">
                    <!-- <div><span class="kw">company</span> <span class="op">=</span> <span class="str">'{{ $job['company'] }}'</span></div> -->
                    <div><span class="kw">role</span>    <span class="op">=</span> <span class="str">'{{ $job['role'] }}'</span></div>
                    <div><span class="kw">years</span>   <span class="op">=</span> <span class="str">'{{ $job['years'] }}'</span></div>
                    <div><span class="kw">stack</span>   <span class="op">=</span> <span class="p">[</span>{!! collect($job['stack'])->map(fn($s)=>"<span class=\"str\">'{$s}'</span>")->implode('<span class=\"p\">, </span>') !!}<span class="p">]</span></div>
                    <div><span class="kw">highlights</span> <span class="op">=</span> <span class="p">[</span></div>
                    @foreach($job['highlights'] as $h)
                      <div class="indent-1"><span class="str">'{{ $h }}'</span><span class="p">{{ $loop->last ? '' : ',' }}</span></div>
                    @endforeach
                    <div><span class="p">]</span></div>
                    <!-- <div class="return"><span class="kw">return</span> <span class="str">f"{'{'}role{'}'} @ {'{'}company{'}'} ({'{'}years{'}'})"</span></div> -->
                  </div>
                </details>
                @endforeach

                <span class="cursor"></span>
              </div>
            </div>
          </div>
        </div>

        <div class="monitor-stand">
          <div class="monitor-neck"></div>
          <div class="monitor-foot"></div>
        </div>
      </div>
    <!-- </div> -->

    
  </div>
  <svg class="robot-typing absolute" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 80" shape-rendering="crispEdges">
    <!-- ===== Floor ===== -->
    <rect x="0" y="60" width="128" height="2" fill="#e5e7eb"/>

    <!-- ===== Desk ===== -->
    <g id="desk-side" transform="translate(30,0)">
      <!-- Tabletop -->
      <rect x="34" y="45" width="18" height="4" fill="#b45309" stroke="#98410c" stroke-width="1"></rect>
      <rect x="34" y="49" width="18" height="3" fill="#a16207"></rect>

      <!-- Legs  -->
      <rect x="35" y="52" width="4" height="11" fill="#98410c"></rect>
      <rect x="47" y="52" width="4" height="11" fill="#98410c"></rect>

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
/* Robot idle pause (your original) */
const host = document.querySelector('.robot-typing');
if (host) {
  const movers = host.querySelectorAll('#bot-left-seated #arm-front, #bot-left-seated #arm-mid, #desk-side #keyboard');
  let pausing = false;
  const pauseMs = 5000;
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

/* Expand/Collapse All for <details> blocks inside the monitor */
const screenEl = document.querySelector('.monitor-screen');
if (screenEl) {
  const blocks = () => Array.from(screenEl.querySelectorAll('.fn-block'));
  screenEl.querySelector('[data-expand-all]')?.addEventListener('click', () => {
    blocks().forEach(d => d.open = true);
  });
  screenEl.querySelector('[data-collapse-all]')?.addEventListener('click', () => {
    blocks().forEach(d => d.open = false);
  });

  // Optional "accordion" mode (one open at a time). Enable by uncommenting:
  // screenEl.addEventListener('toggle', (e) => {
  //   if (!(e.target instanceof HTMLDetailsElement)) return;
  //   if (e.target.open) blocks().forEach(d => { if (d !== e.target) d.open = false; });
  // });
}
</script>
@endpush
