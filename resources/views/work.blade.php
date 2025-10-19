<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 80" shape-rendering="crispEdges">
  <!-- ===== Floor (optional) ===== -->
  <rect x="0" y="60" width="128" height="2" fill="#e5e7eb"/>

  <!-- ===== Desk (true side profile) ===== -->
  <g id="desk-side">
    <!-- Desktop seen from the side: a thin vertical slab -->
    <rect x="28" y="36" width="3" height="20" fill="#374151"/>
    <!-- Modest under-panel/leg to the floor for stability -->
    <rect x="29" y="56" width="1" height="6" fill="#4b5563"/>

    <!-- Keyboard seen from the side (thin bar just to the right of desktop) -->
    <rect id="keyboard" x="32" y="42" width="10" height="2" fill="#9ca3af"/>

    <!-- Monitor seen nearly flat from the side -->
    <!-- Slim screen + a tiny hint of depth line -->
    <rect x="12" y="20" width="3" height="16" fill="#111827"/>
    <rect x="15" y="21" width="1" height="14" fill="#374151"/>
    <!-- Stand and foot -->
    <rect x="13" y="36" width="1" height="5" fill="#6b7280"/>
    <rect x="10" y="41" width="7" height="2" fill="#6b7280"/>
  </g>

  <!-- ===== Chair (faces LEFT: backrest on the RIGHT of seat) ===== -->
  <g id="chair-left">
    <!-- Seat (bot sits fully on this; no bot legs) -->
    <rect x="58" y="46" width="24" height="6" fill="#6b7280"/>
    <!-- Backrest to the RIGHT = chair faces LEFT -->
    <rect x="82" y="28" width="4" height="22" fill="#4b5563"/>
    <!-- Post / base -->
    <rect x="70" y="52" width="2" height="10" fill="#4b5563"/>
    <rect x="64" y="62" width="14" height="2" fill="#4b5563"/>
  </g>

  <!-- ===== Robot (left profile, seated) ===== -->
  <!-- Positioned so hands reach toward the side-on keyboard -->
  <g id="bot-left-seated" transform="translate(78,14) scale(4)">
    <!-- Antenna -->
    <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
    <rect x="7" y="0" width="2" height="1" fill="#ef4444"/>

    <!-- Head (one ear on near/left side) -->
    <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
    <rect x="4" y="4" width="8" height="4" fill="#d1d5db"/>
    <rect x="8" y="5" width="2" height="2" fill="#9ca3af"/> <!-- near ear -->
    <rect x="4" y="5" width="2" height="2" fill="#60a5fa"/> <!-- eye looking left -->

    <!-- Neck -->
    <rect x="7" y="9" width="2" height="1" fill="#9ca3af"/>

    <!-- Body -->
    <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
    <rect x="5" y="11" width="6" height="2" fill="#d1d5db"/>

    <!-- Arms reaching left to the keyboard -->
    <!-- Keep #arm-mid for your existing animations -->
    <g id="arm-mid">
      <!-- Upper arm (shoulder) -->
      <rect x="7" y="11" width="1" height="2" fill="#9ca3af"/>
      <!-- Forearm extending LEFT toward keyboard -->
      <rect x="2" y="12" width="5" height="1" fill="#9ca3af"/>
    </g>

    <!-- Second arm slightly offset for two-hands-on-keys look -->
    <g id="arm-front">
      <rect x="6" y="11" width="1" height="2" fill="#9ca3af"/>
      <rect x="1" y="12" width="5" height="1" fill="#9ca3af"/>
    </g>

    <!-- No legs in seated pose -->
  </g>
</svg>
