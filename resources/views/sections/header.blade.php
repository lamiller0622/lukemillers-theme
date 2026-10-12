@php
  // The mixes players have their own full-screen layouts; no menu there.
  $hideMenu = is_page_template(['mixes.blade.php', 'video-mixes.blade.php']);

  // Hardcoded like the home-page hotspots (no WP menu is assigned).
  // Each entry's slug resolves to the page permalink so it works with
  // both pretty permalinks (production) and the /index.php/ prefix (local).
  $menuItems = [
    ['label' => 'Work Experience', 'slug' => 'work',      'icon' => 'office'],
    ['label' => 'Portfolio',       'slug' => 'portfolio', 'icon' => 'gallery'],
    ['label' => 'About',           'slug' => 'about',     'icon' => 'teepee'],
  ];

  $resolve = function ($slug) {
    $page = get_page_by_path($slug);
    return $page ? get_permalink($page) : home_url('/' . $slug . '/');
  };
@endphp

@unless ($hideMenu)
<header class="banner">
  <button
    type="button"
    class="site-menu-toggle"
    id="site-menu-toggle"
    aria-label="Open menu"
    aria-controls="site-menu"
    aria-expanded="false"
  >
    <span class="site-menu-toggle__bar" aria-hidden="true"></span>
    <span class="site-menu-toggle__bar" aria-hidden="true"></span>
    <span class="site-menu-toggle__bar" aria-hidden="true"></span>
  </button>

  <nav id="site-menu" class="site-menu" aria-label="Site">
    <ul class="site-menu__list">
      <li class="site-menu__item">
        <a class="site-menu__link site-menu__link--home" href="{{ home_url('/') }}">
          <span class="site-menu__icon site-menu__icon--home" aria-hidden="true">
            <svg viewBox="0 -3 16 19" shape-rendering="crispEdges" xmlns="http://www.w3.org/2000/svg">
              <rect x="6" y="14" width="2" height="2" fill="#6b7280"/>
              <rect x="8" y="14" width="2" height="2" fill="#6b7280"/>
              <rect x="7" y="9" width="2" height="1" fill="#9ca3af"/>
              <rect x="7" y="0" width="2" height="2" fill="#6b7280"/>
              <rect x="7" y="0" width="2" height="1" fill="#ef4444"/>
              <rect x="3" y="3" width="10" height="6" fill="#9ca3af"/>
              <rect x="4" y="4" width="8" height="4" fill="#e5e7eb"/>
              <rect x="5" y="5" width="2" height="2" fill="#60a5fa"/>
              <rect x="9" y="5" width="2" height="2" fill="#60a5fa"/>
              <rect x="2" y="5" width="1" height="2" fill="#9ca3af"/>
              <rect x="13" y="5" width="1" height="2" fill="#9ca3af"/>
              <rect x="4" y="10" width="8" height="4" fill="#9ca3af"/>
              <rect x="5" y="11" width="6" height="2" fill="#e5e7eb"/>
              <rect x="3" y="11" width="1" height="2" fill="#9ca3af"/>
              <rect x="12" y="11" width="1" height="2" fill="#9ca3af"/>
              <rect x="13" y="10" width="1" height="1" fill="#9ca3af"/>
            </svg>
          </span>
          <span class="site-menu__label">Home</span>
        </a>
      </li>
      @foreach ($menuItems as $item)
        <li class="site-menu__item">
          <a class="site-menu__link" href="{{ $resolve($item['slug']) }}">
            <img
              class="site-menu__icon"
              src="@asset('images/' . $item['icon'] . '.svg')"
              alt=""
              aria-hidden="true"
              width="40"
              height="40"
              decoding="async"
            />
            <span class="site-menu__label">{{ $item['label'] }}</span>
          </a>
        </li>
      @endforeach
    </ul>
  </nav>
</header>
@endunless
