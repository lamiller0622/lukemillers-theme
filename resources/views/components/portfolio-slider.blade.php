{{-- resources/views/components/portfolio-slider.blade.php --}}
@props([
  // Each item: ['title' => '', 'image' => '', 'url' => '', 'meta' => 'Stack • Year']
  'items' => [],
  // Optional: autoplay in ms (0 disables)
  'autoplay' => 0,
])

@php
  // Demo data if none passed in
  $demo = [
    [
      'title' => 'First Advantage',
      'image' => 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?q=80&w=1600&auto=format&fit=crop',
      'url'   => 'https://www.fadv.com/',
      'meta'  => 'Laravel • Tailwind • 2024',
    ],
    [
      'title' => 'Suffolk Divorce Mediation',
      'image' => 'https://images.unsplash.com/photo-1521791136064-7986c2920216?q=80&w=1600&auto=format&fit=crop',
      'url'   => '#',
      'meta'  => 'WordPress • ACF • 2025',
    ],
    [
      'title' => 'Palermo Law',
      'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?q=80&w=1600&auto=format&fit=crop',
      'url'   => '#',
      'meta'  => 'SEO • WPML • 2025',
    ],
    [
      'title' => 'Hauppauge Mediation',
      'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1600&auto=format&fit=crop',
      'url'   => '#',
      'meta'  => 'Sage/Blade • 2025',
    ],
    [
      'title' => 'Patchogue Mediation',
      'image' => 'https://images.unsplash.com/photo-1515879218367-8466d910aaa4?q=80&w=1600&auto=format&fit=crop',
      'url'   => '#',
      'meta'  => 'Elementor • 2024',
    ],
  ];

  $slides = count($items) ? $items : $demo;

  // Generate a unique id to allow multiple sliders on one page
  $uid = 'glide-portfolio-'.Str::random(6);
@endphp

<div id="{{ $uid }}" class="glide glide-portfolio" style="padding-bottom: 54px;">
  <div class="glide__track" data-glide-el="track">
    <ul class="glide__slides">
      @foreach($slides as $i => $s)
        <li class="glide__slide">
          <article class="slide">
            <img
              class="slide__image"
              src="{{ $s['image'] }}"
              alt="{{ $s['title'] }}"
              loading="lazy"
              decoding="async"
            />
            <div class="slide__overlay"></div>
            <div class="slide__badge">{{ str_pad($i+1, 2, '0', STR_PAD_LEFT) }}</div>
            <div class="slide__content">
              <h3 class="slide__title">{{ $s['title'] }}</h3>
              @if(!empty($s['meta']))
                <div class="slide__meta">{{ $s['meta'] }}</div>
              @endif
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

<script src="https://cdn.jsdelivr.net/npm/@glidejs/glide"></script>
<script>
  (function() {
    const root = document.getElementById(@json($uid));
    if (!root) return;

    // Init Glide
    const glide = new Glide(root, {
      type: 'carousel',
      startAt: 0,
      perView: 3,
      focusAt: 'center',
      gap: 40,
      peek: { before: 120, after: 120 },
      autoplay: {{ (int) $autoplay }},
      hoverpause: true,
      animationTimingFunc: 'cubic-bezier(.22,.61,.36,1)', // snappy
      animationDuration: 700,
      keyboard: true,
      breakpoints: {
        1280: { perView: 3, gap: 32, peek: { before: 100, after: 100 } },
        1024: { perView: 3, gap: 28, peek: { before: 80,  after: 80  } },
        900:  { perView: 2, gap: 28, peek: { before: 70,  after: 70  } },
        768:  { perView: 1, gap: 22, peek: { before: 48,  after: 48  } },
        520:  { perView: 1, gap: 18, peek: { before: 36,  after: 36  } },
      }
    });

    // Add 3D state classes for neighbors so the center card overlaps cleanly
    function setDepthClasses(activeIdx) {
      const list = root.querySelector('.glide__slides');
      const all = Array.from(list.querySelectorAll(':scope > li')).filter(li => !li.classList.contains('glide__slide--clone'));
      const cards = all.map(li => li.querySelector('.slide'));
      const n = all.length;
      if (!n) return;

      // Normalize to real index space (avoid clones)
      const a = ((activeIdx % n) + n) % n;

      cards.forEach(el => el.classList.remove('is-active','is-prev','is-next','is-2prev','is-2next'));

      const idx = (i) => ((i % n) + n) % n;
      cards[idx(a)].classList.add('is-active');
      cards[idx(a-1)].classList.add('is-prev');
      cards[idx(a+1)].classList.add('is-next');
      if (n > 3) {
        cards[idx(a-2)].classList.add('is-2prev');
        cards[idx(a+2)].classList.add('is-2next');
      }
    }

    glide.on(['mount.after','run.after','resize'], () => setDepthClasses(glide.index));
    glide.mount();

    // Small intersection-observer for eager image on center slide
    const io = ('IntersectionObserver' in window) ? new IntersectionObserver((entries) => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          const img = e.target;
          img.decoding = 'async';
          img.loading = 'eager';
          io.unobserve(img);
        }
      });
    }, { root: null, threshold: 0.6 }) : null;

    if (io) root.querySelectorAll('.slide__image').forEach(img => io.observe(img));
  })();
</script>
