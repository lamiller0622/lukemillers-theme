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
      'years' => 'Nov 2024 – Present',
      'stack' => ['Sage 10', 'Sage 11', 'Laravel', 'Bootstrap5','SCSS', 'Tailwind', 'Hubl', 'ACF', 'CPT', 'ACF', 'Elementor'],
      'highlights' => [
        'Convert Drag & Drop Themed Site (Elementor) to MVC Theme (Sage 10) for fadv.com.',
        'Engineered an advanced AJAX-powered resource center with multi-taxonomy filtering (resource type, industry, tags), full-text search via SearchWP, pagination with "Load More" functionality, shareable and trackable filter URLs, and intelligent caching using WordPress transients and WP Cron for optimized performance.',
        'Built a Sage 11 (Vite/Tailwind) microsite, adapting existing Sage 10 SCSS/Bootstrap modules from fadv.com for the new architecture.',
        'Built reusable ACF flexible content modules, allowing for 50+ unique product page creations with minimal development time',
        'Developed modular HubSpot landing page supporting dozens of monthly campaigns.',
        'Led technical integration of two company websites post-acquisition, consolidating tech stack and improving digital experience for both companies.',
        'Built responsive email template system with dynamic content personalization'
      ]
    ],
    [
      'role' => 'Web Developer',
      'company' => 'Palermo Law',
      'years' => '2018 – 2022, 2025 – Present',
      'stack' => ['PHP', 'WordPress', 'SCSS', 'Bootstrap 5', 'ACF', 'esbuild', 'GitHub Actions', 'WP Engine', 'GA4'],
      'highlights' => [
        'Inherited a WordPress theme with a bloated, agency-built stylesheet exceeding 35,000+ lines of manually edited compiled CSS added directly to the output file rather than maintained through source files. Audited and rebuilt the entire CSS architecture from scratch using a modular SCSS component system, reducing the stylesheet to under 3,500 lines while maintaining full visual fidelity.',
        'Consolidated 100+ files of near-identical location page templates, headers, menus, and sidebars into universal components, eliminating redundant template-specific overrides.',
        'Implemented proper dev environment with version control and CI/CD pipeline deploying to WP Engine staging and production.',
        'Improved Core Web Vitals significantly — FCP from 8.6s to 2.1s and TBT from 400ms to 110ms — through hero image refactoring, asset dequeuing, and deferred third-party script loading.',
        'Migrated hardcoded page data to ACF Options Page and CPT architecture, enabling dynamic content management across 9 office locations.',
      ]
    ],
    [
      'role' => 'Web Applications Developer',
      'company' => 'Sterling Check',
      'years' => 'Apr 2021 - Nov 2024',
      'stack' => ['Sage9','Laravel','PHP', 'SCSS', 'JavaScript'],
      'highlights' => [
        'Built high-level pages for company ( Homepage, Compliance Hub, About)',
        'Developed numerous custom Wordpress themes, modules, fields and templates to improve speed and quality of life of the web team.',
        'Converted hundreds of custom XD designs to pixel perfect web pages',
        'Created custom HubSpot templates & modules for marketing operations.',
        'Collaborated with digital marketers, UX/UI designers and SEO specialists on web projects.',
        'Manage 15+ websites for the company as a small team of 2.',
      ]
    ],
    [
      'role' => 'E-Commerce Web Developer',
      'company' => 'Cambridge Kitchens Mfg.',
      'years' => '2017 – 2018',
      'stack' => ['WooCommerce', 'Jquery', 'PHP', 'CSS', 'Photoshop'],
      'highlights' => [
        'Built a complicated, measurement-based E-Commerce store.',
        'Developed custom Scripts using JQuery to maximize allow for dynamic customizable options on the front-end.',
        'Optimized site with custom Wordpress page templates/functions.',
        'Photographed and Photoshopped every product for best online shop appearance.',
        'Calculated product sales margins, arranged company shipping and prepared online store billing.'
      ]
    ]
  ];

  $fn = fn($text) => trim(preg_replace('/[^a-z0-9]+/i','', $text), '_');
@endphp

<section class="workexp-wrap flex justify-center md:my-18 my-10 flex-col">
  <div class="monitor-wrap">
    <div class="monitor-frame">
      <div class="monitor-bezel">
        <div class="monitor-toolbar">
          <div class="dots">
            <span class="dot dot-close"></span>
            <span class="dot dot-min"></span>
            <span class="dot dot-max"></span>
            <button type="button" class="btn-primary"><a href="/"><span class="md:inline-block hidden">Return </span> Home</a></button>
          </div>
          <div class="title">Work Experience</div>
          <div class="actions">
            <button type="button" class="btn-primary" data-expand-all><span class="md:inline-block hidden">Expand All</span><span class="md:hidden">+</span></button>
            <button type="button" class="btn-primary" data-collapse-all><span class="md:inline-block hidden">Collapse All</span><span class="md:hidden">-</span></button>
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
                  <span class="fn">{{ $fn($job['company']) }}</span><span class="p">({{ $job['years'] }}):</span>
                  <!-- <span class="summary-meta">  <span class="cm"># {{ $job['role'] }} @ {{ $job['company'] }} ({{ $job['years'] }})</span></span> -->
                </summary>
                <div class="fn-body">
                  <!-- <div><span class="kw">company</span> <span class="op">=</span> <span class="str">'{{ $job['company'] }}'</span></div> -->
                  <div><span class="kw">role</span>    <span class="op">=</span> <span class="str">'{{ $job['role'] }}'</span></div>
                  <!-- <div><span class="kw">years</span>   <span class="op">=</span> <span class="str">'{{ $job['years'] }}'</span></div> -->
                  <div><span class="kw">stack</span>   <span class="op">=</span> <span class="p">[</span>{!! collect($job['stack'])->map(fn($s)=>"<span class=\"str\">'{$s}'</span>")->implode('<span class=\"p\">, </span>') !!}<span class="p">]</span></div>
                  <div><span class="kw">highlights</span> <span class="op">=</span> <span class="p">[</span></div>
                  @foreach($job['highlights'] as $h)
                    <div class="ps-6 mt-1"><span class="str">{{ $h }}</span><span class="p">{{ $loop->last ? '' : ',' }}</span></div>
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

      <div class="monitor-stand md:grid hidden">
        <div class="monitor-neck"></div>
        <div class="monitor-foot"></div>
      </div>
    </div>
  </div>
  <svg class="robot-typing absolute -bottom-10 md:bottom-auto" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 80" shape-rendering="crispEdges">
    <!-- ===== Floor ===== -->
    <rect x="0" y="60" width="128" height="200" fill="#d0d1c9"/>

    <!-- ===== Desk ===== -->
    <g id="robot-desk" transform="translate(60,32) scale(.5)">
      <g id="window" class="hidden">
        <rect width="15" height="18" x="75" y="-30" fill="rgb(0, 136, 206,.55)" stroke="#534f34" stroke-width="1"/>
        <rect width="15" height="18" x="75" y="-12" fill="rgb(0, 136, 206,.55)" stroke="#534f34" stroke-width="1"/>
        <rect width="15" height="18" x="90" y="-12" fill="rgb(0, 136, 206,.55)" stroke="#534f34" stroke-width="1"/>
        <rect width="15" height="18" x="90" y="-30" fill="rgb(0, 136, 206,.55)" stroke="#534f34" stroke-width="1"/>
      </g>
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
    </g>
  </svg>
</section>
@endsection

@push('scripts')
<script>
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

(() => {
  const wrap = document.querySelector('.monitor-wrap');
  if (!wrap) return;

  const screen  = wrap.querySelector('.monitor-screen');
  const expand  = wrap.querySelector('[data-expand-all]');
  const collapse = wrap.querySelector('[data-collapse-all]');
  if (!screen || (!expand && !collapse)) return;

  const blocks = () => Array.from(screen.querySelectorAll('.fn-block'));

  expand?.addEventListener('click', (e) => {
    e.preventDefault();
    blocks().forEach(d => { d.open = true; });
  });

  collapse?.addEventListener('click', (e) => {
    e.preventDefault();
    blocks().forEach(d => { d.open = false; });
  });
})();
</script>
@endpush
